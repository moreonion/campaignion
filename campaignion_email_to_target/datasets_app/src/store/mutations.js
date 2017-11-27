import Vue from 'vue'
import {emptyDataset} from '@/utils/defaults'
import {clone, elementIndex} from '@/utils'
import {findIndex} from 'lodash'

var idCounter = 0
function newId () {
  return 'new' + idCounter++
}

function filterTableColumns (columns, isCustom) {
  const cols = columns.map(col => col.key)
  if (isCustom) {
    cols.push('__delete')
  }
  return cols
}

export default {
  init (state, {settings}) {
    state.contactPrefix = settings.contactPrefix || 'contact.'
    state.standardColumns = settings.standardColumns || []
    state.validations = settings.validations || {}
  },

  setDatasets (state, datasets) {
    state.datasets = datasets
  },

  updateOrAddDataset (state, {dataset}) {
    const i = findIndex(state.datasets, {key: dataset.key})
    if (i > -1) {
      Vue.set(state.datasets, i, dataset)
    } else {
      state.datasets.push(dataset)
    }
  },

  setSelectedDataset (state, {key}) {
    const i = findIndex(state.datasets, {key})
    state.selectedDataset = clone(state.datasets[i])
  },

  openSelectDialog (state) {
    state.showSelectDialog = true
  },

  closeSelectDialog (state) {
    state.showSelectDialog = false
  },

  editDataset (state, {dataset, contacts}) {
    // Get the columns from the dataset: only columns prefixed with 'contact.'
    const columns = []
    var attribute
    for (var i = 0, j = dataset.attributes.length; i < j; i++) {
      if (dataset.attributes[i].key.indexOf(state.contactPrefix) === 0) {
        attribute = clone(dataset.attributes[i])
        attribute.key = attribute.key.substr(state.contactPrefix.length)
        columns.push(attribute)
      }
    }
    state.currentDataset = clone(dataset)
    state.columns = columns
    state.tableColumns.splice(0, state.tableColumns.length, ...filterTableColumns(state.columns, state.currentDataset.is_custom)) // don’t replace with a new array to keep table binding
    state.contacts.splice(0, state.contacts.length, ...contacts) // don’t replace with a new array to keep table binding
    state.datasetChanged = false
    state.showEditDialog = true
  },

  editNewDataset (state) {
    state.currentDataset = emptyDataset(state)
    state.columns = clone(state.standardColumns)
    state.tableColumns.splice(0, state.tableColumns.length, ...filterTableColumns(state.columns, state.currentDataset.is_custom)) // don’t replace with a new array to keep table binding
    state.contacts.splice(0, state.contacts.length) // don’t replace with a new array to keep table binding
    state.datasetChanged = false
    state.showEditDialog = true
  },

  closeEditDialog (state) {
    state.showEditDialog = false
  },

  showSpinner (state, bool) {
    state.showSpinner = !!bool
  },

  setApiError (state, val) {
    state.apiError = !!val
  },

  addContact (state) {
    if (!state.currentDataset.is_custom) return
    const newContact = {
      id: newId() // we need ids to identify rows when they are clicked. these pseudo ids are removed before POSTing.
    }
    for (var i = 0, j = state.columns.length; i < j; i++) {
      newContact[state.columns[i].key] = ''
    }
    state.contacts.push(newContact)
    state.datasetChanged = true
  },

  deleteContact (state, id) {
    const i = findIndex(state.contacts, {id})
    state.contacts.splice(i, 1)
    state.datasetChanged = true
  },

  'contactsTable/ROW_CLICK' (state, {row, event}) {
    // don’t open tooltip on another cell if it’s already open.
    if (state.editValue) return
    const el = event.target
    // return if it’s the __delete cell (but not the link)
    if (el.children[0] && el.children[0].classList.contains('dsa-delete-contact')) return
    const cellIndex = elementIndex(el)
    const col = state.contactsTable.columns[cellIndex]
    // TODO v2: in non-custom datasets, check if column may be edited
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
    state.datasetChanged = true
  },

  updateTitle (state, title) {
    state.currentDataset.title = title
    state.datasetChanged = true
  },

  updateDescription (state, description) {
    state.currentDataset.description = description
    state.datasetChanged = true
  }
}
