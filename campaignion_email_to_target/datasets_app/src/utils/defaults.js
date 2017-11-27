import {clone} from '@/utils'

// taken from https://gist.github.com/jed/982883
function uuid (a) { return a ? (a ^ Math.random() * 16 >> a / 4).toString(16) : ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, uuid) }

export function emptyDataset (state) {
  const attributes = clone(state.standardColumns)
  // prefix attribute keys
  for (var i = 0, j = attributes.length; i < j; i++) {
    attributes[i].key = state.contactPrefix + attributes[i].key
  }

  return {
    attributes,
    description: '',
    is_custom: true,
    key: null,
    title: '',
    uuid: uuid()
  }
}
