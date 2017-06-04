<?php

namespace Drupal\campaignion_newsletters;

class CronRunner {

  protected $sendBatchSize;

  /**
   * Create a CronRunner instance based on configuration variables.
   */
  public static function fromConfig() {
    $batch_size = variable_get('campaignion_newsletters_batch_size', 50);
    return new static($batch_size);
  }

  /**
   * Instantiate and run sendQueue job.
   */
  public static function cronSendQueue() {
    static::fromConfig()->sendQueue();
  }

  public function __construct($batch_size) {
    $this->sendBatchSize = $batch_size;
  }

  /**
   * Send a batch of queue items to their respective provider.
   */
  public function sendQueue() {
    $lists = NewsletterList::listAll();
    $items = QueueItem::claimOldest($this->sendBatchSize);

    foreach ($items as $item) {
      $list = $lists[$item->list_id];
      $provider = $list->provider();
      $method = $item->action;

      try {
        $provider->{$method}($list, $item);
        $item->delete();
      }
      catch (ApiError $e) {
        $e->log();
        if ($e->isPersistent()) {
          // There is no point to items with persistent errors in the queue.
          $item->delete();
        }
      }
    }
  }

}
