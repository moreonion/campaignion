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
 * Escape characters that have a meaning in regular expressions.
 * @param {string} str - The string to process.
 * @return {string} The string with escaped special characters.
 */
export function escapeRegExp (str) {
  return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, '\\$&') // eslint-disable-line no-useless-escape
}

export default {
  fixedEncodeURIComponent,
  escapeRegExp
}
