(function($) {

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

/**
 * Compare two donation product objects.
 *
 * Keys considered: 'name', 'price', 'id', 'quantity'
 *
 * Comparing with JSON.stringify will not work robustly as the order is not
 * guaranteed.
 *
 * @param {object} product1 first product
 * @param {object} product2 second product
 * @returns {Boolean}
 */
function productIsEqual (product1, product2) {
  for (const key of ['name', 'price', 'id', 'quantity']) {
    // If at least one product has the property and their values differ
    // they are NOT equal. They are otherwise.
    if ((Object.prototype.hasOwnProperty.call(product1, key) || Object.prototype.hasOwnProperty.call(product2, key)) &&
      product1[key] !== product2[key]
    ) {
      return false
    }
  }
  return true
}

/**
 * Attach the Drupal behavior.
 */
Drupal.behaviors.campaignion_tracking = {};
Drupal.behaviors.campaignion_tracking.attach = function(context, settings) {

  if (settings['campaignion_tracking'] && settings['campaignion_tracking']['context']) {
    var donation = settings.campaignion_tracking.context['donation'] || {};
    var webform = settings.campaignion_tracking.context['webform'] || {};
    var store = window.campaignion_tracking.tracker.loadFromStorage(undefined, webform['id']) || { sent: [] };

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
    if (!store.sent.includes('draftBegin') && webform['last_completed_step'] === 1) {
      store.sent.push('draftBegin');
      gracefulDispatch('webform', 'draftBegin', {}, settings.campaignion_tracking.context);
    }

    // Fire `draftContinue` after every following step of multi-step forms.
    if (!store.sent.includes('draftContinue' + webform['last_completed_step'])) {
      if (webform['last_completed_step'] > 1) {
        store.sent.push('draftContinue' + webform['last_completed_step']);
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

      // Fire `setDonationProduct` when the product is new or has changed.
      var prevProduct = store.product || {};
      if (!productIsEqual(product, prevProduct)) {
        var msg = {
          currencyCode: donation['currency_code'] || 'EUR',
          product: product,
        };
        gracefulDispatch('donation', 'setDonationProduct', msg, settings.campaignion_tracking.context);
        store.product = product;
      }

      // Assume the checkout begins when we are on the second step or if
      // there is only one page.
      if (!store.sent.includes('checkoutBegin')) {
        if (webform['current_step'] === 2 || webform['total_steps'] === 1) {
          store.sent.push('checkoutBegin');
          gracefulDispatch('donation', 'checkoutBegin', {}, settings.campaignion_tracking.context);
        }
      }

      // Assume the checkout ends on the last webform step.
      if (!store.sent.includes('checkoutEnd')) {
        if (webform['current_step'] === webform['total_steps']) {
          store.sent.push('checkoutEnd');
          gracefulDispatch('donation', 'checkoutEnd', {}, settings.campaignion_tracking.context);
        }
      }
    }

    window.campaignion_tracking.tracker.saveToStorage(undefined, webform['id'], store);
  }
};
})(jQuery);
