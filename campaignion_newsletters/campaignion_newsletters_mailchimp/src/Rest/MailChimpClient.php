<?php

namespace Drupal\campaignion_newsletters_mailchimp\Rest;

/**
 * MailChimp specific REST-client.
 *
 * Extends the generic REST-client with paging and error handling capabilities.
 */
class MailChimpClient extends Client {

  public function getPaged($path, $query = [], $options = [], $size = 10) {
    $items = [];
    $query['count'] = $size;
    $list_key = strtr(substr($path, strrpos($path, '/') + 1), '-', '_');
    $page = 0;
    while ($new_items = $this->get($path, ['offset' => $size * $page++] + $query, $options)[$list_key])  {
      $items = array_merge($items, $new_items);
    }
    return $items;
  }

  protected function send($path, $query = [], $data = NULL, $options = []) {
    try {
      return parent::send($path, $query, $data, $options);
    }
    catch (HttpError $e) {
      if ($new_e = ApiError::fromHttpError($e, $options['method'], $path)) {
        throw $new_e;
      }
      throw $e;
    }
  }

}
