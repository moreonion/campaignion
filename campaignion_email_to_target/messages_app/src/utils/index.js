import defaults from './defaults'

export function clone (obj) {
  return JSON.parse(JSON.stringify(obj))
}

export function isEmptyMessage (message) {
  return !((message.subject && message.subject.trim()) || (message.header && message.header.trim()) || (message.body && message.body.trim()) || (message.footer && message.footer.trim()))
}

export function dispatch (el, type) {
  const e = document.createEvent('Event')
  e.initEvent(type, true, true)
  el.dispatchEvent(e)
}

export default {
  clone,
  isEmptyMessage,
  defaults
}
