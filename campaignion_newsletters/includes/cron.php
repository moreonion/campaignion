<?php

use \Drupal\campaignion_newsletters\NewsletterList;
use \Drupal\campaignion_newsletters\QueueItem;

/**
 * Send items from the cron queue.
 */
function campaignion_newsletters_send_queue() {
  $lists = NewsletterList::listAll();
  $batchSize = variable_get('campaignion_newsletters_batch_size', 50);
  $items = QueueItem::oldest($batchSize);

  foreach ($items as $item) {
    $list = $lists[$item->list_id];
    $provider = $list->provider();

    $success = FALSE;
    if ($item->action == QueueItem::SUBSCRIBE) {
      $success = $provider->subscribe($list, $item->email);
    }
    elseif ($item->action == QueueItem::UNSUBSCRIBE) {
      $success = $provider->unsubscribe($list, $item->email);
    }
    if ($success) {
      $item->delete();
    }
  }
}

