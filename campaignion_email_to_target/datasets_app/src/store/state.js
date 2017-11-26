const state = {
  contacts: [], // contacts while a dataset is being edited
  standardColumns: [], // these have to be in every dataset
  lockedColumns: [], // in non-custom datasets, users can’t change values in these
  validations: {}, // dictionary of regex strings, keyed by column name
  editValue: null // while a contact is being edited: { id: contact.id, row: Object, col: key for row, el: td element }
}

export default state
