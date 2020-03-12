<?php

namespace Drupal\campaignion_email_to_target;

use Drupal\campaignion_action\Loader;
use Drupal\little_helpers\Webform\Submission;

/**
 * Implementation for token replacements.
 */
abstract class Tokens {

  /**
   * Generate replacements for submission tokens.
   */
  public static function submissionTokens(array $tokens, Submission $submission) {
    $replacements = [];
    if (!empty($tokens['email-to-target-messages'])) {
      $action = Loader::instance()->actionFromNode($submission->node);
      $channel = $action->channel();
      $components = $submission->webform->componentsByType('e2t_selector');
      $messages = [];
      foreach ($components as $cid => $component) {
        $data = $submission->valuesByCid($cid);
        foreach ($data as $serialized) {
          $mail = unserialize($serialized);
          $message = new Message($mail['message']);
          $email = $channel->prepareEmail($message, $submission);
          if ($email) {
            $email['to'] = $message->to;
            $messages[] = $email;
          }
        }
      }
      $t = 'campaignion_email_to_target_all_messages_mail';
      $theme_d = ['messages' => $messages, 'submission' => $submission];
      $text = theme([$t, $t . '_' . $submission->node->nid], $theme_d);
      $replacements[$tokens['email-to-target-messages']] = $text;
    }
    return $replacements;
  }

  /**
   * Generate replacements for message tokens.
   */
  public static function messageTokens(array $tokens, array $data) {
    $replacements = [];
    $my_data['contact'] = $data;
    foreach ($my_data['contact'] as $key => $sub_array) {
      if (is_array($sub_array)) {
        $my_data[$key] = $sub_array;
      }
    }

    foreach ($tokens as $name => $original) {
      if (strpos($name, '.') === FALSE) {
        $name = 'contact.' . $name;
      }
      $value = drupal_array_get_nested_value($my_data, explode('.', $name));
      if (!is_null($value)) {
        $replacements[$original] = (string) $value;
      }
      else {
        watchdog('campaignion_email_to_target', 'No data for token "%original" in dataset.', ['%original' => $original], WATCHDOG_ERROR);
      }
    }
    return $replacements;
  }

}
