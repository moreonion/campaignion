export const VALID_SPECIFICATION_TYPES = ['message-template', 'exclusion']
// TODO: synchronize types with backend: 'message'?

export const OPERATORS = new Map([
  ['==', 'is'],
  ['!=', 'is not'],
  ['regexp', 'matches']
])

export function defaultMessageObj () {
  return {
    subject: '',
    header: '',
    body: '',
    footer: ''
  }
}

export function exclusionMessageObj () {
  return {
    body: ''
  }
}

export function emptySpec (type) {
  if (VALID_SPECIFICATION_TYPES.indexOf(type) === -1) return
  var spec = {
    id: null,
    type: type,
    label: '',
    filters: [],
    filterStr: '', // Verbal expression of a specification’s filters
    errors: []
  }
  if (type === 'message-template') {
    spec.message = defaultMessageObj()
  } else if (type === 'exclusion') {
    spec.message = exclusionMessageObj()
  }
  return spec
}

export default {
  VALID_SPECIFICATION_TYPES,
  OPERATORS,
  defaultMessageObj,
  exclusionMessageObj,
  emptySpec
}
