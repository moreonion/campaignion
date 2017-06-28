import defaults from './defaults'

export function clone (obj) {
  return JSON.parse(JSON.stringify(obj))
}

export function isEmptyMessage (message) {
  return !((message.subject && message.subject.trim()) || (message.header && message.header.trim()) || (message.body && message.body.trim()) || (message.footer && message.footer.trim()))
}

export function composeFilterStr (filters) {
  var filterStr = ''
  filters.forEach((el, index) => {
    if (index) filterStr += ' and '
    filterStr += '<span class="filter-condition">' + el.attributeLabel + ' ' + defaults.OPERATORS.get(el.operator) + ' ' + (el.value || '<span class="value-missing">[&nbsp;please specify a value&nbsp;]</span>') + '</span>'
  })
  return filterStr || '<span class="filter-missing">[&nbsp;please add a filter&nbsp;]</span>'
}

export default {
  clone,
  isEmptyMessage,
  composeFilterStr,
  defaults
}
