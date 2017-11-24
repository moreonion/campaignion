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

export function emptyRedirect (type) {
  return {
    id: null,
    label: '',
    destination: '',
    prettyDestination: '',
    filters: []
  }
}

export default {
  OPERATORS,
  emptyRedirect
}
