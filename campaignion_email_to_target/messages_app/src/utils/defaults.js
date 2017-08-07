export const VALID_SPECIFICATION_TYPES = ['message-template', 'exclusion']

export const OPERATORS = {
  '==': {
    label: Drupal.t('is'),
    phrase: Drupal.t('@attribute is @value')
  },
  '!=': {
    label: Drupal.t('is not'),
    phrase: Drupal.t('@attribute is not @value')
  },
  'regexp': {
    label: Drupal.t('matches'),
    phrase: Drupal.t('@attribute matches @value')
  }
}

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
