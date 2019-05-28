/**
 * Machine readable name.
 *
 * Use for referencing tracker specifics like storage keys, channel names, ...
 */
export const name = 'gtm'

/**
 * Mapping of codes to eventNames.
 *
 * This also is a whitelist of which events should *also* be recognized as
 * codes.
 */
export const codes = {
  ds: 'donationSuccess'
}

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
   * @param {Boolean} debug set to true for debugging
   */
  constructor (tracker, debug = false) {
    this.debug = debug
    this.tracker = tracker

    this.printDebug('init')

    if (typeof this.tracker === 'undefined') {
      this.printDebug('No TrackerManager found. Doing nothing.')
    }

    /**
     * Subscribe to messages of the `donation` tracking channel.
     *
     * TODO: validate event, sanitize data
     */
    this.donationSubscription = this.tracker.subscribe('donation', (e) => {
      this.printDebug('campaignion_tracking_gtm', 'handle_donation', e)

      this.printDebug('campaignion_tracking_gtm', 'handle_event', e.name, e.data, e.context)

      // dispatch to my handlers
      this.dispatch(e.name, e.data, e.context)
    })
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
    } else {
      this.printDebug('no handler for event name:', eventName)
    }
  }

  /**
   * Handle "checkoutBegin".
   *
   * @param {String} eventName the event name
   * @param {object} eventData data of the event
   * @param {object} context context of the event
   */
  handle_checkoutBegin (eventName, eventData, context) { // eslint-disable-line camelcase
    this.printDebug('(handle)', eventName, eventData, context)
  }

  /**
   * Handle "checkoutEnd".
   *
   * @param {String} eventName the event name
   * @param {object} eventData data of the event
   * @param {object} context context of the event
   */
  handle_checkoutEnd (eventName, eventData, context) { // eslint-disable-line camelcase
    this.printDebug('(handle)', eventName, eventData, context)
  }

  /**
   * Handle "donationSuccess".
   *
   * @param {String} eventName the event name
   * @param {object} eventData data of the event
   * @param {object} context context of the event
   */
  handle_donationSuccess (eventName, eventData, context) { // eslint-disable-line camelcase
    this.printDebug('(handle)', eventName, eventData, context)
  }
}
