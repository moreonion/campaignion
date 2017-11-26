export function clone (obj) {
  return JSON.parse(JSON.stringify(obj))
}

export function dispatch (el, type) {
  const e = document.createEvent('Event')
  e.initEvent(type, true, true)
  el.dispatchEvent(e)
}

export function elementIndex (el) {
  var i = 0
  while ((el = el.previousSibling) != null) {
    i++
  }
  return i
}
