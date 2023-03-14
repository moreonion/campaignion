(function($) {
Drupal.behaviors.campaignion_tracking = {};
Drupal.behaviors.campaignion_tracking.attach = function(context, settings) {

  /**
   * Dispatch to campaignion_tracking.tracker.
   *
   * Nothing happens if the tracker is not available.
   */

  function gracefulDispatch (type, name, data, trackingContext) {
    // Build an msg object expected by campaignion_tracking:
    // It has a "name" for the event, "data" for the event and a
    // "context" for additional data.
    // NB: the event handler of the tracker might expect a certain data
    // structure.
    var msg = {
      'name': name,
      'data': data,
      'context': trackingContext
    }
    window.campaignion_tracking.tracker.publish(type, msg)
  }

  if (settings['campaignion_tracking'] && settings['campaignion_tracking']['context']) {
    var sent = window.campaignion_tracking.tracker.loadFromStorage(undefined, 'sent') || [];
    var donation = settings.campaignion_tracking.context['donation'] || {};
    var webform = settings.campaignion_tracking.context['webform'] || {};

    /**
     * Webform related events.
     *
     * The events are only fired if a correct campaignion_tracking context is
     * available. They are only fired once for the Drupal context to prevent the
     * same messages being dispatch multiple times (behaviours can be called
     * multiple times).
     *
     * Events:
     * - 'draftBegin'
     * - 'draftContinue'
     */

    // Fire `draftBegin` after completing the first step of multi-step forms.
    if (!sent.includes('draftBegin') && webform['last_completed_step'] === 1) {
      sent.push('draftBegin');
      gracefulDispatch('webform', 'draftBegin', {}, settings.campaignion_tracking.context);
    }

    // Fire `draftContinue` after every following step of multi-step forms.
    if (!sent.includes('draftContinue' + webform['last_completed_step'])) {
      if (webform['last_completed_step'] > 1) {
        sent.push('draftContinue' + webform['last_completed_step']);
        gracefulDispatch('webform', 'draftContinue', {}, settings.campaignion_tracking.context);
      }
    }


    /**
     * Donation related events.
     *
     * The events are only fired if a correct campaignion_tracking context is
     * available. They are only fired once for the Drupal context to prevent the
     * same messages being dispatch multiple times (behaviours can be called
     * multiple times).
     *
     * Events:
     * - 'setDonationProduct'
     * - 'checkoutBegin'
     * - 'checkoutEnd'
     */
    if (donation['amount'] && donation['interval'] && donation['currency_code']) {
      var product = {
        name: donation['name'],
        id: donation['id'],
        price: String(donation['amount']),
        variant: String(donation['interval']),
        quantity: 1
      };

      var msg = {
        currencyCode: donation['currency_code'] || 'EUR',
        product: product
      };

      gracefulDispatch('donation', 'setDonationProduct', msg, settings.campaignion_tracking.context);

      // Assume the checkout begins when we are on the second step or if
      // there is only one page.
      if (!sent.includes('checkoutBegin')) {
        if (webform['current_step'] === 2 || webform['total_steps'] === 1) {
          sent.push('checkoutBegin');
          gracefulDispatch('donation', 'checkoutBegin', {}, settings.campaignion_tracking.context);
        }
      }

      // Assume the checkout ends on the last webform step.
      if (!sent.includes('checkoutEnd')) {
        if (webform['current_step'] === webform['total_steps']) {
          sent.push('checkoutEnd');
          gracefulDispatch('donation', 'checkoutEnd', {}, settings.campaignion_tracking.context);
        }
      }
    }

    window.campaignion_tracking.tracker.saveToStorage(undefined, 'sent', sent);
  }
};
})(jQuery);
