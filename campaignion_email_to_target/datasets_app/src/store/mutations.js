import {elementIndex} from '@/utils'
import {findIndex} from 'lodash'

export default {
  init (state, {settings}) {
    state.standardColumns = settings.standardColumns || []
    state.lockedColumns = settings.lockedColumns || state.standardColumns
    state.validations = settings.validations || {}
  },

  setDatasets (state, datasets) {
    state.datasets = datasets
  },

  openSelectDialog (state) {
    state.showSelectDialog = true
  },

  closeSelectDialog (state) {
    state.showSelectDialog = false
  },

  selectDataset (state, {dataset}) {
    state.showSelectDialog = false
    state.currentDataset = dataset
  },

  setApiError (state, val) {
    state.apiError = !!val
  },

  // TODO remove content generators
  generateContacts (state) {
    const arr = []
    //  ['email', 'first_name', 'last_name', 'salutation', 'title']
    for (var i = 0; i < 7000; i++) {
      arr.push({
        id: i,
        email: 'mail' + i + '@example-domain.com',
        first_name: 'first name - ' + i,
        last_name: 'last name - ' + i,
        salutation: 'hey mister ' + i,
        custom: 'Custom Field ' + i
      })
    }
    state.contacts = arr
  },

  'contactsTable/ROW_CLICK' (state, {row, event}) {
    // don’t open tooltip on another cell if it’s already open.
    if (state.editValue) return
    const el = event.target
    const cellIndex = elementIndex(el)
    const col = state.contactsTable.columns[cellIndex]
    // TODO check for lock in non-custom datasets
    // if (state.lockedColumns.indexOf(col) !== -1) return
    state.editValue = {
      id: row.id,
      row,
      col,
      el
    }
  },

  leaveValue (state) {
    state.editValue = null
  },

  updateValue (state, {value}) {
    if (!state.editValue) return
    const i = findIndex(state.contacts, {id: state.editValue.id})
    state.contacts[i][state.editValue.col] = value
    state.editValue = null
  }
}
