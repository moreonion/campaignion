import { TrackerManager } from './tracker-manager'
import * as fragment from './fragment'

// enable debugging output
var debug = false

// default tracker
export const tracker = new TrackerManager(debug)

// some out-of-the-box prefixes
// TODO: this should be configurable
let prefixes = ['gtm']

/**
 * Listener to check the location hash string for events to trigger.
 */
window.addEventListener('load', (e) => {
  let hash = window.location.hash.substr(1)
  let items = fragment.consumeLocationHashForPrefixes(prefixes, hash)
  if (items['locationHash'] !== hash) {
    if (items['locationHash'].length) {
      window.location.hash = '#' + items['locationHash']
    } else {
      // use replaceState so we get rid of the superfluouse '#' when setting
      // window.location.hash to ''
      window.history.replaceState('', window.document.title, window.location.pathname + window.location.search)
    }
  }

  // publish events for all items, channel name is the prefix
  for (let item of items['items']) {
    tracker.publish(item.prefix, item)
  }
})

// re-exports
export { TrackerManager, fragment, debug }
