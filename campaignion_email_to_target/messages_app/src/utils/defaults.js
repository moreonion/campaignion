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

export function messageObj () {
  return {
    subject: '',
    header: '',
    body: '',
    footer: ''
  }
}

export function emptySpec (type) {
  if (VALID_SPECIFICATION_TYPES.indexOf(type) === -1) return
  return {
    id: null,
    type: type,
    label: '',
    filters: [],
    message: messageObj(),
    errors: []
  }
}

export default {
  VALID_SPECIFICATION_TYPES,
  OPERATORS,
  messageObj,
  emptySpec
}
