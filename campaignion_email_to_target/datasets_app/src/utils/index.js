import Vue from 'vue'

export const INVALID_CONTACT_STRING = 'has:error'

/**
 * Deep-copy an object.
 * @param {Object} obj - The object to clone.
 * @return {Object} The cloned object.
 */
export function clone (obj) {
  return JSON.parse(JSON.stringify(obj))
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
 * Get the index of an element inside its container.
 * @param {HTMLElement} el The element in question.
 * @return {integer} The element’s index.
 */
export function elementIndex (el) {
  var i = 0
  while ((el = el.previousSibling) != null) {
    i++
  }
  return i
}

/**
 * Test a list of contacts against a set of regular expressions.
 * If `index` is passed, validate only that row.
 * Add an `__error: INVALID_CONTACT_STRING` property to invalid contacts.
 * @param {Object[]} contacts - The array of contacts to validate.
 * @param {Object} validations - Validations for each column. Dictionary of regex strings, keyed by column name.
 * @param {integer} index - Optional. The row to validate.
 * @return {boolean} Are the contacts valid?
 */
export function validateContacts (contacts, validations, index) {
  // if index is passed, validate only that row
  var from, to
  var valid = true
  if (typeof index !== 'undefined') {
    from = to = index
  } else {
    from = 0
    to = contacts.length - 1
  }
  for (let i = from, j = to; i <= j; i++) {
    for (let field in validations) {
      if (validations.hasOwnProperty(field) && typeof contacts[i][field] !== 'undefined') {
        if (new RegExp(validations[field]).test(contacts[i][field]) === false) {
          valid = false
          Vue.set(contacts[i], '__error', INVALID_CONTACT_STRING)
        }
      }
    }
  }
  // If only one row was checked (after editing that row), remove error mark from this row.
  // While we need to find errors in a whole dataset (CSV upload), they are fixed only row by row.
  // So we can optimize performance by not removing errors in the loop.
  if (valid && typeof index !== 'undefined') {
    Vue.delete(contacts[index], '__error')
  }
  return valid
}
