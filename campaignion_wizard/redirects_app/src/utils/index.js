export function clone (obj) {
  return JSON.parse(JSON.stringify(obj))
}

export function dispatch (el, type) {
  const e = document.createEvent('Event')
  e.initEvent(type, true, true)
  el.dispatchEvent(e)
}

export function validateDestination (destination) {
  return (destination.length &&
    destination.match(/^(www\.|http:\/\/|https:\/\/|\/)/) &&
    destination.indexOf(' ') === -1) ||
    destination.match(/^node\//)
}

export default {
  clone,
  dispatch,
  validateDestination
}
