<?php

namespace Drupal\campaignion_newsletters;

class CronRunner {

  protected $sendBatchSize;
  protected $pollTime;

  /**
   * Create a CronRunner instance based on configuration variables.
   */
  public static function fromConfig() {
    $batch_size = variable_get('campaignion_newsletters_batch_size', 50);
    $poll_time = variable_get('campaignion_newsletters_poll_time', 10);
    return new static($batch_size, $poll_time);
  }

  /**
   * Instantiate and run sendQueue job.
   */
  public static function cronSendQueue() {
    static::fromConfig()->sendQueue();
  }

  /**
   * Instantiate and run poll job.
   */
  public static function cronPoll() {
    static::fromConfig()->poll();
  }

  public static function removeStaleLists() {
    $threshold = variable_get('campaignion_newsletters_last_list_poll', 0) - variable_get('campaignion_newsletters_list_expiry', 86400);
    $lists = NewsletterList::notUpdatedSince($threshold);
    foreach ($lists as $list) {
      $node_controller = entity_get_controller('node');

      $result = db_select('webform_component', 'c')
        ->fields('c', ['nid', 'cid'])
        ->condition('type', 'newsletter')
        ->execute();
      foreach ($result as $row) {
        $node = node_load($row->nid);
        $component = $node->webform['components'][$row->cid];
        if (!empty($component['extra']['lists'][$list->list_id])) {
          unset($component['extra']['lists'][$list->list_id]);
          //webform_component_update($component);
          //$node_controller->resetCache([$row->nid]);
          $left = count(array_filter($component['extra']['lists']));
          echo "Removed stale list from node#{$node->nid}({$node->type}) “{$node->title}” ($left lists left).\n";
        }
      }

      $num_subscriptions = db_select('campaignion_newsletters_subscriptions')
        ->condition('list_id', $list->list_id)
        ->countQuery()
        ->execute()
        ->fetchField();
      db_delete('campaignion_newsletters_subscriptions')
        ->condition('list_id', $list->list_id)
        ->execute();
      echo "- Deleted {$num_subscriptions} subscriptions.\n";

      $num_items = db_select('campaignion_newsletters_queue')
        ->condition('list_id', $list->list_id)
        ->countQuery()
        ->execute()
        ->fetchField();
      db_delete('campaignion_newsletters_queue')
        ->condition('list_id', $list->list_id)
        ->execute();
      echo "- Deleted {$num_items} queue items.\n";

      $list->delete();
      db_delete('campaignion_newsletters_lists')
        ->condition('list_id', $list->list_id)
        ->execute();
      echo "- Deleted list.\n";
    }
  }

  public function __construct($batch_size, $poll_time) {
    $this->sendBatchSize = $batch_size;
    $this->pollTime = $poll_time;
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

  /**
   * Generator to loop over all providers.
   */
  protected function getProviders() {
    $f = ProviderFactory::getInstance();
    foreach ($f->providers() as $key) {
      yield $f->providerByKey($key);
    }
  }

  /**
   * Let the provider(s) poll their subscriber lists for a given amount of time.
   */
  public function poll() {
    $end = microtime(TRUE) + $this->pollTime;

    foreach ($this->getProviders() as $provider) {
      $continue = TRUE;
      while ($continue && microtime(TRUE) < $end) {
        $poll = $provider->polling();
        $continue = $poll && $poll->poll();
      }
    }
  }

}
