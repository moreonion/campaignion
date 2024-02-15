import defaults from './defaults'

/**
 * Deep-copy an object.
 * @param {Object} obj - The object to clone.
 * @return {Object} The cloned object.
 */
export function clone (obj) {
  return JSON.parse(JSON.stringify(obj))
}

/**
 * Escape characters that have a meaning in regular expressions.
 * @param {string} str - The string to process.
 * @return {string} The string with escaped special characters.
 */
export function escapeRegExp (str) {
  return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, '\\$&') // eslint-disable-line no-useless-escape
}

/**
 * Comply to RFC 3986 when encoding URI components.
 * Encode also !, ', (, ) and *.
 * @param {string} str - The URI component to encode.
 * @return {string} The encoded URI component.
 */
export function fixedEncodeURIComponent (str) {
  return encodeURIComponent(str).replace(/[!'()*]/g, c => '%' + c.charCodeAt(0).toString(16))
}

/**
 * Check if a message is empty.
 * @param {Object} message - The message object to validate.
 * @return {boolean} true only if all of the message fields are empty or contain only whitespace characters.
 */
export function isEmptyMessage (message) {
  return !((message.subject && message.subject.trim()) || (message.header && message.header.trim()) || (message.body && message.body.trim()) || (message.footer && message.footer.trim()))
}

/**
 * Validate the url property’s value.
 * Valid values are absolute or relative urls or expressions starting with `node/`.
 * @param {string} destination - The expression to validate.
 * @return {boolean} Is it valid?
 */
export function validateDestination (destination) {
  return !!(destination.length &&
    (destination.match(/^(www\.|http:\/\/|https:\/\/|\/)/) ||
    destination.match(/^node\//)) &&
    destination.indexOf(' ') === -1)
}

/**
 * Dispatch a custom JavaScript event.
 * @param {HTMLElement} el DOM element to dispatch the event on.
 * @param {string} type Event name.
 */
export function dispatch (el, type) {
  const e = document.createEvent('Event')
  e.initEvent(type, true, true)
  el.dispatchEvent(e)
}

/**
 * Prepare a url for appending a GET param key-value pair.
 * @param {string} url - The url to prepare.
 * @return {string} The url with either a ? or a & at the end.
 */
export function paramReadyUrl (url) {
  if (!url.match(/\?[^=]+=[^&]*/)) {
    // there’s no parameter. replace trailing ? or / or /? with ?
    return url.replace(/[/?]$|(?:\/)\?$/, '') + '?'
  } else {
    // parameter present in the string. ensure trailing &
    return url.replace(/[&]$/, '') + '&'
  }
}

export default {
  clone,
  escapeRegExp,
  defaults,
  fixedEncodeURIComponent,
  isEmptyMessage,
  paramReadyUrl,
  validateDestination
}
