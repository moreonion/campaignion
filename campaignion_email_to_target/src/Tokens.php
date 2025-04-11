<?php

namespace Drupal\campaignion_email_to_target;

use Drupal\little_helpers\Webform\Submission;

/**
 * Implementation for token replacements.
 */
abstract class Tokens {

  /**
   * Recursively flatten an array.
   * @param array $data The array to flatten
   * @param mixed $sep The separator used to concatenate keys.
   * @return array
   */
  public static function arrayFlatten(array $data, $sep = '.', $prefix = '') {
    $result = [];
    foreach ($data as $key => $value) {
      if (is_array($value)) {
        $result += self::arrayFlatten($value, $sep, $prefix . $key . $sep);
      }
      else {
        $result[$prefix . $key] = $value;
      }
    }
    return $result;
  }

  /**
   * Normalize target data for token like usage.
   *
   * @param array $data
   * @return array
   */
  public static function normalizeTargetData(array $data) {
    return self::arrayFlatten($data);
  }

  /**
   * Generate replacements for submission tokens.
   */
  public static function submissionTokens(array $tokens, Submission $submission) {
    $replacements = [];
    if (!empty($tokens['email-to-target-messages']) || !empty($tokens['target-messages'])) {
      $components = $submission->webform->componentsByType('e2t_selector');
      $messages = [];
      foreach ($components as $cid => $component) {
        $data = $submission->valuesByCid($cid);
        foreach ($data as $serialized) {
          $mail = unserialize($serialized);
          $message = new Message($mail['message']);
          $messages[] = $message;
        }
      }
      $themehook = 'campaignion_target_messages_token';
      $text = theme([$themehook, $themehook . '_' . $submission->node->nid], [
        'messages' => $messages,
        'submission' => $submission,
      ]);
      foreach (['target-messages', 'email-to-target-messages'] as $token) {
        if ($replacement = $tokens[$token] ?? NULL) {
          $replacements[$replacement] = $text;
        }
      }
    }
    return $replacements;
  }

  /**
   * Generate replacements for message tokens.
   */
  public static function messageTokens(array $tokens, array $data) {
    $replacements = [];
    $my_data = self::normalizeTargetData($data);
    foreach ($tokens as $name => $original) {
      if (!is_null($value = $my_data[$name] ?? NULL)) {
        $replacements[$original] = (string) $value;
      }
      else {
        watchdog('campaignion_email_to_target', 'No data for token "%original" in dataset.', ['%original' => $original], WATCHDOG_ERROR);
      }
    }
    return $replacements;
  }

}
