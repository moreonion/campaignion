const state = {
  datasets: [],
  currentDataset: null, // dataset that is being edited
  selectedDataset: null, // valid dataset selected for the action
  contacts: [], // contacts while a dataset is being edited
  contactPrefix: '', // identifies a contact attribute
  columns: [], // actual columns in the current dataset
  tableColumns: [], // columns that are shown in the table
  standardColumns: [], // these have to be in every dataset
  validations: {}, // dictionary of regex strings, keyed by column name
  editValue: null, // while a contact is being edited: { id: contact.id, row: Object, col: key for row, el: td element }
  showSelectDialog: false,
  showEditDialog: false,
  showSpinner: false,
  datasetChanged: false,
  apiError: false
}

export default state
