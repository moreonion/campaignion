<?php

namespace Drupal\campaignion_manage\BulkOp;

interface BulkOpBatchInterface {
  /**
   * Process the current .
   *
   * @param parameter1 parameter 1 defined in the structure for batch_set()
   * @param parameter2 parameter 1 defined in the structure for batch_set()
   * @param context    the context provided by drupal batch processing
   */
  public function batchApply($parameter1, $parameter2, &$context);
  /**
   * @param success    is set to 1 when there were no fatal errors
   * @param results    array extracted form $context['results']; can be set by batchApply
   * @param operations 
   */
  public function batchFinish($success, $results, $operations);
}