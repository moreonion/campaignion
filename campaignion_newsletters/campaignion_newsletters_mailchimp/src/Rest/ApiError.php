<?php

namespace Drupal\campaignion_newsletters_mailchimp\Rest;

class ApiError extends \Drupal\campaignion_newsletters\ApiError {

  public static function fromHttpError(HttpError $e, $verb, $path) {
    if ($data = drupal_json_decode($e->result->data)) {
      $code = $e->getCode();
      $msg = "Got @code for %verb %path: @title - @detail %errors";
      $vars = [
        '%verb' => $verb,
        '%path' => $path,
        '@title' => $data['title'],
        '@detail' => $data['detail'],
        '%errors' => '',
      ];
      if ($data['errors']) {
        $errors = [];
        foreach ($data['errors'] as $error) {
          $errors[] = "{$error['field']}: {$error['message']}";
        }
        $vars['%errors'] = '{ ' . implode(",\n", $errors) . ' }';
      }
      return new static('campaignion_newsletters', $msg, $vars, $code, NULL, $e);
    }
  }

  public function isPersistent() {
    $code = $this->getCode();
    return in_array($code, [400]);
  }

}
