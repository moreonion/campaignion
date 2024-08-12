<?php

namespace Drupal\campaignion_m2t_send;

use Drupal\campaignion_email_to_target\Channel\Email;
use Drupal\campaignion_email_to_target\Component;
use Drupal\little_helpers\Services\Container;
use Drupal\little_helpers\Webform\Submission;

/**
 * Cron job for sending out M2T messages as emails.
 */
class SendMessagesCron {

  /**
   * An array of node type machine names to send data for.
   *
   * @var str[]
   */
  protected $nodeTypes;

  /**
   * Create a new cron-job instance based on config.
   */
  public function __construct($content_types) {
    $this->nodeTypes = $content_types;
  }

  /**
   * Fetch targets from the e2t service.
   *
   * @return array
   *   Targets with email address grouped by party name.
   */
  protected function getCurrentTargets(Submission $submission) {
    /** @var Drupal\campaignion_email_to_target\Api\Client */
    $client = Container::get()->loadService('campaignion_email_to_target.api.Client');
    return $client->getTargets('mp', ['postcode' => $submission->valueByKey('postcode')]);
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
  protected function processSubmission(Submission $submission) {
    $channel = new Email();
    $targets = NULL;
    $email = $submission->valueByKey('email');
    echo "Checking submission (nid={$submission->nid}, sid={$submission->sid}) by $email …\n";
    $components = $submission->webform->componentsByType('e2t_selector');
    foreach ($components as $cid => $component) {
      echo "\tComponent {$cid} (form_key={$component['form_key']}):\n";
      $component_o = Component::fromComponent($component);
      $values = array_filter(array_map('unserialize', $submission->valuesByCid($cid)), function ($m) {
        return !($m['sent'] ?? FALSE);
      });
      if (!$values) {
        echo "\t\tNo unsent messages found.\n";
        continue;
      }
      $cnt = count($values);
      echo "\t\t$cnt unsent messages found.\n";
      if (!$targets) {
        $targets = $this->getCurrentTargets($submission);
      }
      $values = array_filter(array_map(function ($m) use ($targets) {
        if ($new_target = $targets[0] ?? NULL) {
          return $this->replaceTarget($m, $new_target);
        }
        return NULL;
      }, $values));
      $cnt = count($values);
      echo "\t\t$cnt new targets found.\n";
      if ($values) {
        $values_to_send = array_map('serialize', $values);
        $component_o->sendEmails($values_to_send, $submission, $channel);
        foreach ($values_to_send as $no => $serialized) {
          db_update('webform_submitted_data')
            ->fields(['data' => $serialized])
            ->condition('nid', $submission->nid)
            ->condition('sid', $submission->sid)
            ->condition('cid', $cid)
            ->condition('no', $no)
            ->execute();
        }
      }
    }
  }

  /**
   * Main function of the cron-job.
   */
  public function run() {
    $q = db_select('field_data_field_email_to_target_options', 'o');
    $q->join('node', 'n', "o.entity_type='node' AND o.entity_id=n.nid");
    $nids = $q
      ->fields('o', ['entity_id'])
      ->condition('o.field_email_to_target_options_dataset_name', ['mp'])
      ->condition('n.type', $this->nodeTypes)
      ->execute()
      ->fetchCol();
    $nodes = entity_load('node', $nids);

    $submission_sql = <<<SQL
    SELECT sid
    FROM {webform_submissions}
    WHERE nid=:nid AND sid>:last_sid
    ORDER BY sid
    LIMIT 100;
    SQL;

    $count_processed = 0;
    foreach ($nodes as $node) {
      $args = [':last_sid' => 0, ':nid' => $node->nid];
      while ($sids = db_query($submission_sql, $args)->fetchCol()) {
        foreach ($sids as $sid) {
          if ($submission = Submission::load($node->nid, $sid, TRUE)) {
            $this->processSubmission($submission);
            $count_processed += 1;
          }
          if ($count_processed % 100 == 0) {
            echo "$count_processed submissions processed.\n";
          }
          $args[':last_sid'] = $sid;
        }
        gc_collect_cycles();
      }
    }
    echo "$count_processed submissions processed. done.\n";
  }

}
