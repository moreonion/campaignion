<?php

/**
 * Implements hook_campaignion_action_info().
 */
function webform_component_paymethod_select_campaignion_action_info() {
  $info['webform_payment'] = 'Drupal\campaignion\Activity\WebformPayment';
}

/**
 * Implements hook_payment_status_change().
 */
function campaignion_activity_payment_status_change(Payment $payment, PaymentStatusItem $previous_status_item) {
  $statusChangedToSuccess = $payment->getStatus()->status == PAYMENT_STATUS_SUCCESS && $previous_status_item->status != PAYMENT_STATUS_SUCCESS;
  $hasContextObj = isset($payment->context_data['context']) && $payment->context_data['context'] instanceof \Drupal\webform_component_paymethod_select\WebformPaymentContext;
  if (!$statusChangedToSuccess || !$hasContextObj)
    return;

  if (!($activity = Drupal\campaignion\Activity\WebformPayment::byPayment($payment))) { 
    $activity = Drupal\campaignion\Activity\WebformPayment::fromPayment($payment);
    $activity->save();
  }
}
