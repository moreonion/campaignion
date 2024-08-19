<?php

namespace Drupal\campaignion_m2t_send;

use Drupal\campaignion_action\Loader as ActionLoader;
use Drupal\campaignion_m2t_send\Submission;
use Drupal\campaignion_email_to_target\Action;
use Drupal\campaignion_email_to_target\Channel\Email;
use Drupal\campaignion_email_to_target\Component;
use Drupal\little_helpers\Services\Container;

/**
 * Cron job for sending out M2T messages as emails.
 */
class SendMessagesCron {

  /**
   * An array of nids for which sending is enabled.
   *
   * @var array
   */
  protected $enabledNodes;

  /**
   * Number of messages sent for each target.
   *
   * @var int[]
   */
  protected $sendHistory = [];

  /**
   * Message statistics.
   */
  protected $messageStats = [
    'sent' => 0,
    'withheld' => 0,
  ];

  /**
   * Create a new cron-job instance based on config.
   */
  public function __construct(array $enabled_nodes) {
    $this->enabledNodes = $enabled_nodes;
  }

  /**
   * Fetch targets from the e2t service.
   *
   * @return array
   *   Targets with email address grouped by party name.
   */
  protected function getCurrentTargets(Submission $submission, string $dataset) {
    /** @var Drupal\campaignion_email_to_target\Api\Client */
    $client = Container::get()->loadService('campaignion_email_to_target.api.Client');
    return array_filter($client->getTargets($dataset, ['postcode' => $submission->valueByKey('postcode')]), function ($target) {
      return (bool) ($target['email'] ?? NULL);
    });
  }

  /**
   * Replace the target in a message with a new target.
   *
   * @return array
   *   The message with the target replaced.
   */
  protected function replaceTarget($m, $target) {
    $m['target'] = $target;
    $m['sent'] = TRUE;
    $m['message']['toAddress'] = $target['email'];
    $m['message']['toName'] = trim("{$target['title']} {$target['first_name']} {$target['last_name']}");
    $m['message']['header'] = preg_replace('/Dear .*,/', $m['message']['header'], "Dear {$target['salutation']},");
    return $m;
  }

  /**
   * Check whether to send an additional email to the target or not.
   */
  protected function rateLimit($target) {
    $email = $target['email'];
    $count = $this->sendHistory[$email] ?? 0;
    if ($count === FALSE) {
      $this->messageStats['withheld'] += 1;
      return FALSE;
    }
    if (rand(0, 1) === 1) {
      $this->sendHistory[$email] = $count + 1;
      $this->messageStats['sent'] += 1;
      return TRUE;
    }
    else {
      $this->sendHistory[$email] = FALSE;
      $this->messageStats['withheld'] += 1;
      return FALSE;
    }
  }

  /**
   * Send the target emails for a submission using new data from the e2t-api.
   */
  protected function processSubmission(Submission $submission, Action $action) {
    $channel = new Email();
    $targets = $this->getCurrentTargets($submission, $action->getOptions()['dataset_name']);
    $data = array_filter(array_map(function ($d) use ($targets) {
      $m = unserialize($d->data);
      if (($new_target = $targets[0] ?? NULL) && $this->rateLimit($new_target)) {
        $d->new_data = serialize($this->replaceTarget($m, $new_target));
        $d->new_target = $new_target;
        return $d;
      }
      return NULL;
    }, $submission->m2t_unsent_messages));
    foreach ($data as $d) {
      $component_o = Component::fromComponent($submission->webform->component($d->cid));
      $component_o->sendEmails([$d->new_data], $submission, $channel);
      db_update('webform_submitted_data')
        ->fields(['data' => $d->new_data])
        ->condition('nid', $submission->nid)
        ->condition('sid', $submission->sid)
        ->condition('cid', $d->cid)
        ->condition('no', $d->no)
        ->execute();
      db_merge('campaignion_m2t_send')
        ->key(['nid' => $submission->nid, 'sid' => $submission->sid, 'cid' => $d->cid, 'no' => $d->no])
        ->fields(['sent_at' => time(), 'target_email' => $d->new_target['email']])
        ->execute();
    }
  }

  /**
   * Main function of the cron-job.
   */
  public function run() {
    module_load_include('inc', 'webform', 'includes/webform.submissions');
    $nids = array_keys(array_filter($this->enabledNodes));
    $nodes = entity_load('node', $nids);
    $actions = array_map(function ($node) {
      return ActionLoader::instance()->actionFromNode($node);
    }, $nodes);

    $submission_sql = <<<SQL
    SELECT s.sid
    FROM {webform_submissions} s
    WHERE s.sid IN (
      SELECT sid FROM {campaignion_m2t_send} WHERE nid=:nid AND sent_at IS NULL
    ) AND s.sid>:last_sid
    ORDER BY s.sid
    LIMIT 100
    SQL;

    $count_processed = 0;
    foreach (Submission::iterate($nodes, $submission_sql) as $submission) {
      $this->processSubmission($submission, $actions[$submission->nid]);
      $count_processed += 1;
    }
    $vars = [
      '@submissions' => $count_processed,
      '@sent' => $this->messageStats['sent'],
      '@withheld' => $this->messageStats['withheld']
    ];
    watchdog('campaignion_m2t_send', '@submissions submissions processed, @sent emails sent (@withheld emails not sent).', $vars, WATCHDOG_INFO);
  }

}
