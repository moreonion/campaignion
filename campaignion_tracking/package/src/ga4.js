/**
 * Machine readable name.
 *
 * Use for referencing tracker specifics like storage keys, channel names, ...
 */
export const name = 'ga4'

/**
 * Tracker for Google Tag Manager.
 *
 * Implements behaviour to dispatch events to Google Analytics.
 */
export class GA4Tracker {
  /**
   * Constructor.
   *
   * @param {TrackerManager} tracker the shared tracker manager object
   * @param {object} dataLayer the datalayer to use (default `window.dataLayer`)
   * @param {Boolean} debug set to true for debugging
   */
  constructor (tracker, dataLayer = window.dataLayer, debug = false) {
    this.debug = debug
    this.dataLayer = dataLayer
    this.tracker = tracker

    // load from session, set defaults
    const defaultContext = {
      node: {},
      donation: { currencyCode: null, product: null },
      webform: { sid: null }
    }
    this._context = this.loadFromStorage('context') || defaultContext

    this.printDebug('init')

    if (typeof this.tracker === 'undefined') {
      this.printDebug('No TrackerManager found. Doing nothing.')
      return
    }

    if (typeof this.dataLayer === 'undefined') {
      this.printDebug('No datalayer found. Doing nothing.')
      return
    }

    /**
     * Callback for subscribed events.
     *
     * This handles incoming data/events.
     *
     * TODO: validate event
     * TODO: sanitize data
     *
     * @param {object} e Tracking event
     */
    this._dispatch = e => {
      this.printDebug('campaignion_tracking_ga4', 'handle_form', e)

      this.printDebug('campaignion_tracking_ga4', 'handle_event', e.name, e.data, e.context)

      // dispatch to my handlers
      this.dispatch(e.name, e.data, e.context)
    }

    /**
     * Subscribe to messages of the `webform` tracking channel.
     */
    this.webformSubscription = this.tracker.subscribe('webform', this._dispatch)

    /**
     * Subscribe to messages of the `donation` tracking channel.
     */
    this.donationSubscription = this.tracker.subscribe('donation', this._dispatch)
  }

  /**
   * Copy gtag function as required by GA4.
   */
  gtag () {
    this.dataLayer.push(arguments)
  }

  /**
   * Utility function to print to `console.debug`.
   *
   * Print only if debug is set to a truthy value.
   *
   * @param  {...any} args arguments to print
   */
  printDebug (...args) {
    if (this.debug) {
      console.debug('[campaignion_tracking]', '(ga4)', ...args)
    }
  }

  saveToStorage (key = 'default', data) {
    this.tracker.saveToStorage('campaignion_tracking:ga4:', key, data)
  }

  loadFromStorage (key = 'default') {
    return this.tracker.loadFromStorage('campaignion_tracking:ga4:', key)
  }

  removeFromStorage (key = 'default') {
    return this.tracker.removeFromStorage('campaignion_tracking:ga4:', key)
  }

  /**
   * Dispatch to my handlers.
   *
   * The handler are named according to the `eventName`, prefixed with
   * `handle_`. The handlers are responsible for the actual sending of valid
   * events to Google Analytics.
   *
   * @param {String} eventName the name of the event
   * @param {object} eventData data of the event
   * @param {object} context context of the event, e.g. site title, ...
   */
  dispatch (eventName = '', eventData = {}, context = {}) {
    if (typeof this['handle_' + eventName] === 'function') {
      this['handle_' + eventName](eventName, eventData, context)
    }
    else {
      this.printDebug('no handler for event name:', eventName)
    }
  }

  /**
   * Hook to allow users to manipulate GA4 data.
   *
   * Maybe some projects/sites want different values for some event fields.
   * Provide a way to allow for that customizations.
   *
   * @param {String} eventName the campaignion_tracking event name
   * @param {object} ga4Data the data which will get sent to the data layer
   * @param {object} context the tracking context
   */
  callChangeHook (eventName, ga4Data, context) {
    // maybe also inject tracker
    if (typeof window.campaignion_tracking_change_msg === 'function') {
      ga4Data = window.campaignion_tracking_change_msg('ga4', eventName, ga4Data, context)
    }
    return ga4Data
  }

  /**
   * Maintains a context between event handling.
   *
   * You can provide context with a first event and call this method during
   * handling.
   * When another event arrives it can also enrich this context and read former
   * context data as well.
   *
   * This context is saved in the browser storage to persist between page
   * loads. It is reset on donationSuccess.
   *
   * @param {context} context Tracking context data.
   */
  updateContext (context = {}) {
    // Check if context exists
    if (context.donation) {
      Object.assign(this._context.donation, context.donation)
    }
    if (context.node) {
      Object.assign(this._context.node, context.node)
    }
    if (context.webform) {
      Object.assign(this._context.webform, context.webform)
    }
    this.saveToStorage('context', this._context)
  }

  /**
   * Handle "submission".
   *
   * Event data: { nid, sid, title }
   *
   * @param {String} eventName the event name
   * @param {object} eventData data of the event
   * @param {object} context context of the event
   */
  handle_submission (eventName, eventData, context) {
    this.printDebug('(handle)', eventName, eventData, context)
    this.updateContext(context)

    let submissionData = {
      event: 'submission',
      params: {
        nid: eventData.nid || null,
        sid: eventData.sid || null,
        title: eventData.title || null,
      }
    }
    // Allow others to modify the data being sent to Google Analytics.
    submissionData = this.callChangeHook(eventName, submissionData, this._context)
    this.gtag('event', submissionData.event, submissionData.params)
    this.printDebug('(event)', submissionData)

    if (eventData.optins.length > 0) {
      let optinData = {
        event: 'optin',
        params: {}
      }
      for (const channel of eventData.optins) {
        optinData.params[channel.channel] = channel.value
      }
      // Allow others to modify the data being sent to Google Analytics.
      optinData = this.callChangeHook(eventName, optinData, this._context)
      this.gtag('event', optinData.event, optinData.params)
      this.printDebug('(event)', optinData)
    }
  }

  /**
   * Handle "draftBegin".
   *
   * Event data: { nid, title, type, completedStep }
   *
   * @param {String} eventName the event name
   * @param {object} eventData data of the event
   * @param {object} context context of the event
   */
  handle_draftBegin (eventName, eventData, context) {
    this.printDebug('(handle)', eventName, eventData, context)
    this.updateContext(context)

    let data = {
      event: 'begin_form',
      params: {
        nid: context.node.nid || null,
        title: context.node.title || null,
        type: context.node.type || null,
        completedStep: context.webform.last_completed_step || null,
      }
    }
    // Allow others to modify the data being sent to Google Analytics.
    data = this.callChangeHook(eventName, data, this._context)
    this.gtag('event', data.event, data.params)
    this.printDebug('(event)', data)
  }

  /**
   * Handle "draftContinue".
   *
   * Event data: { nid, title, completedStep }
   *
   * @param {String} eventName the event name
   * @param {object} eventData data of the event
   * @param {object} context context of the event
   */
  handle_draftContinue (eventName, eventData, context) {
    this.printDebug('(handle)', eventName, eventData, context)
    this.updateContext(context)

    let data = {
      event: 'continue_form',
      params: {
        nid: context.node.nid || null,
        title: context.node.title || null,
        completedStep: context.webform.last_completed_step || null,
      }
    }
    // Allow others to modify the data being sent to Google Analytics.
    data = this.callChangeHook(eventName, data, this._context)
    this.gtag('event', data.event, data.params)
    this.printDebug('(event)', data)
  }

  /**
   * Handle "setDonationProduct".
   *
   * Remove a (donation) product when one was added before. Thus we have a
   * "cart" with only 1 slot for 1 donation.
   *
   * Event data: { currency, value, items}
   *
   * @param {String} eventName the event name
   * @param {object} eventData data of the event
   * @param {object} context context of the event
   */
  handle_setDonationProduct (eventName, eventData, context) {
    this.printDebug('(handle)', eventName, eventData, context)
    this.updateContext(context)
    if (eventData.currencyCode) {
      this._context.donation.currencyCode = eventData.currencyCode
    }
    const currencyCode = this._context.donation.currencyCode || null
    const currentRevenue = this._context.donation.revenue || null
    const currentProduct = this._context.donation.product || {}
    const newProduct = eventData.product || {}
    const newRevenue = eventData.revenue || parseFloat(newProduct.price || 0) * parseInt(newProduct.quantity || 1)

    const addData = {
      event: 'add_to_cart',
      params: {
        currency: currencyCode,
        value: newRevenue,
        items: [{
          item_name: newProduct.name,
          item_id: newProduct.id,
          price: newProduct.price,
          item_variant: newProduct.variant,
          quantity: newProduct.quantity,
        }],
      }
    }
    const removeData = {
      event: 'remove_from_cart',
      params: {
        currency: currencyCode,
        value: currentRevenue,
        items: [{
          item_name: currentProduct.name,
          item_id: currentProduct.id,
          price: currentProduct.price,
          item_variant: currentProduct.variant,
          quantity: currentProduct.quantity,
        }],
      }
    }
    // Only push a remove if we can assume we have pushed a valid product before.
    const pushRemove = Object.prototype.hasOwnProperty.call(currentProduct, 'price')
    let data = {
      addData: addData,
      removeData: removeData,
      pushRemove: pushRemove
    }
    // Allow others to modify the data being sent to Google Analytics.
    data = this.callChangeHook(eventName, data, this._context)

    if (data.pushRemove) {
      this.gtag('event', data.removeData.event, data.removeData.params)
      this.printDebug('(event)', data.removeData)
    }
    this.gtag('event', data.addData.event, data.addData.params)
    this.printDebug('(event)', data.addData)

    this._context.donation.revenue = newRevenue
    this._context.donation.product = newProduct
    this.saveToStorage('context', this._context)
  }

  /**
   * Handle "checkoutBegin".
   *
   * Event data: { currency, value, items }
   *
   * @param {String} eventName the event name
   * @param {object} eventData data of the event
   * @param {object} context context of the event
   */
  handle_checkoutBegin (eventName, eventData, context) {
    this.printDebug('(handle)', eventName, eventData, context)
    this.updateContext(context)
    const product = eventData.product || this._context.donation.product || {}
    const currencyCode = eventData.currencyCode || this._context.donation.currencyCode || null
    let revenue = eventData.revenue || this._context.donation.revenue || null
    if (revenue === null) {
      revenue = parseFloat(product.price || 0) * parseInt(product.quantity || 1)
    }
    let data = {
      event: 'begin_checkout',
      params: {
        currency: currencyCode,
        value: revenue,
        items: [{
          item_name: product.name,
          item_id: product.id,
          price: product.price,
          item_variant: product.variant,
          quantity: product.quantity,
        }],
      }
    }
    // Allow others to modify the data being sent to Google Analytics.
    data = this.callChangeHook(eventName, data, this._context)
    this.gtag('event', data.event, data.params)
    this.printDebug('(event)', data)
  }

  /**
   * Handle "checkoutEnd".
   *
   * Event data: { currency, value, items }
   *
   * @param {String} eventName the event name
   * @param {object} eventData data of the event
   * @param {object} context context of the event
   */
  handle_checkoutEnd (eventName, eventData, context) {
    this.printDebug('(handle)', eventName, eventData, context)
    this.updateContext(context)
    const product = eventData.product || this._context.donation.product || {}
    const currencyCode = eventData.currencyCode || this._context.donation.currencyCode || null
    let revenue = eventData.revenue || this._context.donation.revenue || null
    if (revenue === null) {
      revenue = parseFloat(product.price || 0) * parseInt(product.quantity || 1)
    }
    let data = {
      event: 'add_shipping_info',
      params: {
        currency: currencyCode,
        value: revenue,
        items: [{
          item_name: product.name,
          item_id: product.id,
          price: product.price,
          item_variant: product.variant,
          quantity: product.quantity,
        }],
      }
    }
    // Allow others to modify the data being sent to Google Analytics.
    data = this.callChangeHook(eventName, data, this._context)
    this.gtag('event', data.event, data.params)
    this.printDebug('(event)', data)
  }

  /**
   * Handle "donationSuccess".
   *
   * Google requires a transaction ID. If the event data passed to this
   * method does not include a `tid`, a random ID will be added.
   *
   * Event data: { tid, currency, value, items }
   *
   * @param {String} eventName the event name
   * @param {object} eventData data of the event
   * @param {object} context context of the event
   */
  handle_donationSuccess (eventName, eventData, context) {
    this.printDebug('(handle)', eventName, eventData, context)
    this.updateContext(context)
    const product = eventData.product || this._context.donation.product || {}
    const currencyCode = eventData.currencyCode || this._context.donation.currencyCode || null
    // Ensure a transaction ID.
    const transactionID = eventData.tid || Math.floor(Math.random() * 2 ** 64)
    const sentTransactionIDs = this.loadFromStorage('sentTransactionIDs') || []
    // Do nothing if the transaction was already sent.
    if (sentTransactionIDs.indexOf(transactionID) >= 0) {
      this.printDebug('(handle)', 'already sent TID', eventName, eventData, context)
      return
    }
    let revenue = eventData.revenue || this._context.donation.revenue || null
    if (revenue === null) {
      revenue = parseFloat(product.price || 0) * parseInt(product.quantity || 1)
    }
    let data = {
      event: 'purchase',
      params: {
        transaction_id: transactionID,
        currency: currencyCode,
        value: revenue,
        items: [{
          item_name: product.name,
          item_id: product.id,
          price: product.price,
          item_variant: product.variant,
          quantity: product.quantity,
        }],
      }
    }
    // Allow others to modify the data being sent to Google Analytics.
    data = this.callChangeHook(eventName, data, this._context)
    this.gtag('event', data.event, data.params)
    this.printDebug('(event)', data)
    // Remember sent transactions ids.
    sentTransactionIDs.push(transactionID)
    this.saveToStorage('sentTransactionIDs', sentTransactionIDs)
    // We are finished with this donation: clean up.
    this.removeFromStorage('context')
  }
}
