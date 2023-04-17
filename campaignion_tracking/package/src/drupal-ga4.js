import * as ga4 from './ga4.js'

/**
 * Check if we want debugging output.
 *
 * Parse the value as int, so we can disable debugging by setting to "0".
 * `sessionStorage` only stores strings.
 */
const debug = !!parseInt(sessionStorage.getItem('campaignion_debug'))

// ensure window.dataLayer
window.dataLayer = window.dataLayer || []

// common tracker manager, listener, ga4
const tracker = window.campaignion_tracking.tracker
let ga4Tracker = null
if (typeof tracker === 'undefined') {
  console.log('No Tracker found')
}
else {
  ga4Tracker = new ga4.GA4Tracker(tracker, window.dataLayer, debug)
  window.campaignion_tracking_ga4 = { tracker: ga4Tracker }
}

// re-exports
export { ga4, ga4Tracker }
