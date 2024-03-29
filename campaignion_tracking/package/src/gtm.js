/**
 * Machine readable name.
 *
 * Use for referencing tracker specifics like storage keys, channel names, ...
 */
export const name = 'gtm'

/**
 * Tracker for Google Tag Manager.
 *
 * Implements behaviour to dispatch events to GTM.
 */
export class GTMTracker {
  /**
   * Constructor.
   *
   * @param {TrackerManager} tracker the shared tracker manager object
   * @param {object} dataLayer the GTM datalayer to use (default `window.dataLayer`)
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
      this.printDebug('campaignion_tracking_gtm', 'handle_form', e)

      this.printDebug('campaignion_tracking_gtm', 'handle_event', e.name, e.data, e.context)

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
   * Utility function to print to `console.debug`.
   *
   * Print only if debug is set to a truthy value.
   *
   * @param  {...any} args arguments to print
   */
  printDebug (...args) {
    if (this.debug) {
      console.debug('[campaignion_tracking]', '(gtm)', ...args)
    }
  }

  saveToStorage (key = 'default', data) {
    this.tracker.saveToStorage('campaignion_tracking:gtm:', key, data)
  }

  loadFromStorage (key = 'default') {
    return this.tracker.loadFromStorage('campaignion_tracking:gtm:', key)
  }

  removeFromStorage (key = 'default') {
    return this.tracker.removeFromStorage('campaignion_tracking:gtm:', key)
  }

  /**
   * Dispatch to my handlers.
   *
   * The handler are named according to the `eventName`, prefixed with
   * `handle_`. The handlers are responsible for the actual sending of valid
   * events to GTM.
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
   * Hook to allow users to manipulate GTM data.
   *
   * Maybe some projects/sites want different values for some GTM fields.
   * Provide a way to allow for that customizations.
   *
   * @param {String} eventName the campaignion_tracking event name
   * @param {object} gtmData the data which will get sent to GTM data layer
   * @param {object} context the tracking context
   */
  callChangeHook (eventName, gtmData, context) {
    // maybe also inject tracker
    if (typeof window.campaignion_tracking_change_msg === 'function') {
      gtmData = window.campaignion_tracking_change_msg('gtm', eventName, gtmData, context)
    }
    return gtmData
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

    const submissionData = {
      event: 'submission',
      webform: {
        nid: eventData.nid || null,
        sid: eventData.sid || null,
        title: eventData.title || null,
      }
    }
    // Allow others to modify the data being sent to GTM.
    const submissionDataFinal = this.callChangeHook(eventName, submissionData, this._context)
    this.dataLayer.push(submissionDataFinal)
    this.printDebug('(event)', submissionDataFinal)

    if (eventData.optins.length > 0) {
      let optinData = {
        ...submissionData,
        event: 'optin',
      }
      for (const channel of eventData.optins) {
        optinData[channel.channel] = channel.value
      }
      // Allow others to modify the data being sent to GTM.
      optinData = this.callChangeHook(eventName, optinData, this._context)
      this.dataLayer.push(optinData)
      this.printDebug('(event)', optinData)
    }
  }

  /**
   * Handle "draftBegin".
   *
   * Event data: { nid, title, step }
   *
   * @param {String} eventName the event name
   * @param {object} eventData data of the event
   * @param {object} context context of the event
   */
  handle_draftBegin (eventName, eventData, context) {
    this.printDebug('(handle)', eventName, eventData, context)
    this.updateContext(context)

    let data = {
      event: 'actionBegin',
      webform: {
        nid: context.node.nid || null,
        title: context.node.title || null,
        step: 1,
      }
    }
    // Allow others to modify the data being sent to GTM.
    data = this.callChangeHook(eventName, data, this._context)
    this.dataLayer.push(data)
    this.printDebug('(event)', data)
  }

  /**
   * Handle "draftContinue".
   *
   * Event data: { nid, title, step }
   *
   * @param {String} eventName the event name
   * @param {object} eventData data of the event
   * @param {object} context context of the event
   */
  handle_draftContinue (eventName, eventData, context) {
    this.printDebug('(handle)', eventName, eventData, context)
    this.updateContext(context)

    let data = {
      event: 'actionContinue',
      webform: {
        nid: context.node.nid || null,
        title: context.node.title || null,
        step: context.webform.last_completed_step || null,
      }
    }
    // Allow others to modify the data being sent to GTM.
    data = this.callChangeHook(eventName, data, this._context)
    this.dataLayer.push(data)
    this.printDebug('(event)', data)
  }

  /**
   * Handle "setDonationProduct".
   *
   * Remove a (donation) product when one was added before. Thus we have a
   * "cart" with only 1 slot for 1 donation.
   *
   * Event data: { product, currencyCode }
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
    const currentProduct = this._context.donation.product || {}
    const newProduct = eventData.product || {}

    const addData = {
      event: 'addToCart',
      ecommerce: {
        add: {
          products: [newProduct]
        }
      }
    }
    if (currencyCode) {
      addData.ecommerce.currencyCode = currencyCode
    }
    const removeData = {
      event: 'removeFromCart',
      ecommerce: {
        remove: {
          products: [currentProduct]
        }
      }
    }
    // Only push a remove if we can assume we have pushed a valid product before.
    const pushRemove = Object.prototype.hasOwnProperty.call(currentProduct, 'price')
    let data = {
      addData: addData,
      removeData: removeData,
      pushRemove: pushRemove
    }
    // Allow others to modify the data being sent to GTM.
    data = this.callChangeHook(eventName, data, this._context)

    if (data.pushRemove) {
      this.dataLayer.push(data.removeData)
      this.printDebug('(event)', data.removeData)
    }
    this.dataLayer.push(data.addData)
    this.printDebug('(event)', data.addData)

    this._context.donation.product = newProduct
    this.saveToStorage('context', this._context)
  }

  /**
   * Handle "checkoutBegin".
   *
   * Event data: { product, currencyCode }
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
    let data = {
      event: 'checkoutBegin',
      ecommerce: {
        checkout: {
          actionField: { step: 1 }, // begin == 1
          products: [product]
        }
      }
    }
    if (currencyCode) {
      data.ecommerce.currencyCode = currencyCode
    }
    // Allow others to modify the data being sent to GTM.
    data = this.callChangeHook(eventName, data, this._context)
    this.dataLayer.push(data)
    this.printDebug('(event)', data)
  }

  /**
   * Handle "checkoutEnd".
   *
   * Event data: { product, currencyCode }
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
    let data = {
      event: 'checkoutEnd',
      ecommerce: {
        checkout: {
          actionField: { step: 2 }, // end == 2
          products: [product]
        }
      }
    }
    if (currencyCode) {
      data.ecommerce.currencyCode = currencyCode
    }
    // Allow others to modify the data being sent to GTM.
    data = this.callChangeHook(eventName, data, this._context)
    this.dataLayer.push(data)
    this.printDebug('(event)', data)
  }

  /**
   * Handle "donationSuccess".
   *
   * Google requires a transaction ID. If the event data passed to this
   * method does not include a `tid`, a random ID will be added.
   *
   * Event data: { tid, revenue, product, currencyCode }
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
    let revenue = eventData.revenue || null
    if (revenue === null) {
      revenue = parseFloat(product.price || 0) * parseInt(product.quantity || 1)
    }
    let data = {
      event: 'purchase',
      ecommerce: {
        purchase: {
          actionField: {
            id: transactionID, // required
            revenue: String(revenue)
          },
          products: [product]
        }
      }
    }
    if (currencyCode) {
      data.ecommerce.currencyCode = currencyCode
    }
    // Allow others to modify the data being sent to GTM.
    data = this.callChangeHook(eventName, data, this._context)
    this.dataLayer.push(data)
    this.printDebug('(event)', data)
    // Remember sent transactions ids.
    sentTransactionIDs.push(transactionID)
    this.saveToStorage('sentTransactionIDs', sentTransactionIDs)
    // We are finished with this donation: clean up.
    this.removeFromStorage('context')
  }
}
