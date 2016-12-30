<?php

namespace Drupal\campaignion_newsletters_mailchimp\Rest;

/**
 * MailChimp specific REST-client.
 *
 * Extends the generic REST-client with paging and error handling capabilities.
 */
class MailChimpClient extends Client {

  public function getPaged($path, $query = [], $options = [], $size = 10, $list_key = NULL) {
    $items = [];
    $query['count'] = $size;
    if (!$list_key) {
      $list_key = strtr(substr($path, strrpos($path, '/') + 1), '-', '_');
    }
    $offset = 0;
    $next_page = TRUE;
    while ($next_page)  {
      $result = $this->get($path, ['offset' => $offset] + $query, $options);
      $new_items = $result[$list_key];
      $items = array_merge($items, $new_items);

      // Only fetch the next page if there is more items than our next offset.
      $offset += $size;
      $next_page = $new_items && ($result['total_items'] > $offset);
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
