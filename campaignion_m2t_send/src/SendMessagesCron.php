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
    return $client->getTargets($dataset, ['postcode' => $submission->valueByKey('postcode')]);
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
   * Send the target emails for a submission using new data from the e2t-api.
   */
  protected function processSubmission(Submission $submission, Action $action, array $data) {
    $channel = new Email();
    $targets = $this->getCurrentTargets($submission, $action->getOptions()['dataset_name']);
    $email = $submission->valueByKey('email');
    echo "Checking submission (nid={$submission->nid}, sid={$submission->sid}) by $email …\n";
    $data = array_filter(array_map(function ($d) use ($targets) {
      $m = unserialize($d->data);
      if ($new_target = $targets[0] ?? NULL) {
        $d->new_data = serialize($this->replaceTarget($m, $new_target));
        return $d;
      }
      return NULL;
    }, $data));
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
      db_merge('webform_submitted_data')
        ->key(['nid' => $submission->nid, 'sid' => $submission->sid, 'cid' => $d->cid, 'no' => $d->no])
        ->fields(['sent_at' => time()])
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

    $data_sql = <<<SQL
    SELECT nid, sid, cid, no, data
    FROM {webform_submissions} s
      INNER JOIN {webform_component} c ON c.nid=s.nid AND c.type='e2t_selector'
      INNER JOIN {webform_submitted_data} d USING(nid, sid, cid)
      LEFT OUTER JOIN {campaignion_m2t_send} m USING(nid, sid, cid, no)
    WHERE m.sid IS NULL AND nid=:nid AND sid IN(:sids);
    SQL;

    $submission_sql = <<<SQL
    SELECT DISTINCT sid
    FROM {webform_submissions} s
      INNER JOIN {webform_component} c ON c.nid=s.nid AND c.type='e2t_selector'
      INNER JOIN {webform_submitted_data} d USING(nid, sid, cid)
      LEFT OUTER JOIN {campaignion_m2t_send} m USING(nid, sid, cid, no)
    WHERE m.sid IS NULL AND s.nid=:nid AND s.sid>:last_sid
    ORDER BY sid
    LIMIT 100
    SQL;

    $count_processed = 0;
    foreach ($nodes as $node) {
      $args = [':last_sid' => 0, ':nid' => $node->nid];
      while ($sids = db_query($submission_sql, $args)->fetchCol()) {
        $data_per_sid = [];
        foreach (db_query($data_sql, [':nid' => $node->nid, ':sids' => $sids]) as $data) {
          $data_per_sid[$data->sid][] = $data;
        }
        foreach (webform_get_submissions(['ws.sid' => $sids]) as $s) {
          $submission = new Submission($nodes[$s->nid], $s);
          $this->processSubmission($submission, $actions[$submission->nid], $data_per_sid[$submission->sid] ?? []);
          $count_processed += 1;
          if ($count_processed % 100 == 0) {
            echo "$count_processed submissions processed.\n";
          }
          $args[':last_sid'] = $s->sid;
        }
        gc_collect_cycles();
      }
    }
    echo "$count_processed submissions processed. done.\n";
  }

}
