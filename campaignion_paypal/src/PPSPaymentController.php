<?php

namespace Drupal\campaignion_paypal;

/**
 * Paypal PPS controller with campaignion integration.
 */
class PPSPaymentController extends \PayPalPaymentPPSPaymentMethodController {

  /**
   * Redirect the user to paypal using the payment context object.
   */
  public function execute(\Payment $payment) {
    $_SESSION['paypal_payment_pps_pid'] = $payment->pid;
    $payment->contextObj->redirect('paypal_payment_pps/redirect/' . $payment->pid);
  }

  /**
   * Column headers for webform data.
   */
  public function webformDataInfo() {
    $info['transaction_id'] = t('Transaction ID');
    return $info;
  }

  /**
   * Data for webform results.
   */
  public function webformData(\Payment $payment) {
    $status = $payment->getStatus();
    $data['transaction_id'] = $status->ipn->txn_id ?? NULL;
    return $data;
  }

}
