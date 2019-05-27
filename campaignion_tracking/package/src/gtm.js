// eslint-disable-next-line camelcase
let campaignion_tracking = window.campaignion_tracking
// eslint-disable-next-line camelcase
if (typeof campaignion_tracking === 'undefined') {
  console.debug('No campaignion_tracking found. Doing nothing.')
  return
}

if (campaignion_tracking.debug) {
  console.debug('campaignion_tracking_gtm')
}

let tracker = campaignion_tracking.tracker
if (typeof tracker === 'undefined') {
  console.debug('No TrackerManager found. Doing nothing.')
  return
}

export const codes = {
  cb: 'checkoutBegin',
  ce: 'checkoutEnd',
  ds: 'donationSuccess'
}

let subscription = tracker.subscribe('gtm', (e) => {
  console.debug('campaignion_tracking_gtm', 'handle_message', e)
  let events = e.codes.reduce((acc, code) => {
    if (codes[code]) {
      acc.push(codes[code])
    }
    return acc
  }, [])
  console.debug('campaignion_tracking_gtm', 'handle_events', events)
})
