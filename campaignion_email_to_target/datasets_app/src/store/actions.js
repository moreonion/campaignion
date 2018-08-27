import api from '@/utils/api'
import {clone} from '@/utils'

export default {
  /**
   * Load datasets.
   * Show the spinner in the App component while loading.
   * Set `datasets` in the store. If a preselected dataset is passed,
   * also set `selectedDataset`.
   * @param {Object} context - The vuex context.
   * @param {Object} payload - The action’s payload.
   * @param {string} payload.selected - The identifier of the currently selected dataset.
   */
  loadDatasets (context, {selected}) {
    context.commit('showSpinner', true)
    api.getDatasets().then(data => {
      context.commit('showSpinner', false)
      context.commit('setDatasets', data.data)
      if (selected) {
        context.commit({
          type: 'setSelectedDataset',
          key: selected
        })
      }
    }, () => {
      context.commit('setApiError', true)
      context.commit('showSpinner', false)
    })
  },

  /**
   * Load the list of contacts for a given dataset.
   * Show the spinner in the App component while loading.
   * Close the selecting dialog, open the editing dialog and store the contacts with vuex.
   * @param {Object} context - The vuex context.
   * @param {Object} payload - The action’s payload.
   * @param {Object} payload.dataset - The dataset of which we want to load the contacts.
   */
  loadContacts (context, {dataset}) {
    context.commit('showSpinner', true)
    context.commit('closeSelectDialog')
    api.getContacts(dataset.key).then(data => {
      context.commit({type: 'editDataset', dataset, contacts: clone(data.data)})
      context.commit('showSpinner', false)
    }, err => {
      console.error(err)
      alert(Drupal.t('An error occurred while loading the contacts. If the error persists, please contact support.'))
      context.commit('showSpinner', false)
    })
  },

  /**
   * Save the current dataset and its list of contacts.
   * Show the spinner in the App component while saving.
   * For a new dataset, generate a key. Remove dummy ids from contacts.
   * @param {Object} context - The vuex context.
   */
  saveDataset (context) {
    const dataset = clone(context.state.currentDataset)
    const contacts = clone(context.state.contacts)
    const isNewDataset = !!dataset.uuid
    if (!dataset.is_custom) return
    if (isNewDataset) {
      // construct the key
      var slug = dataset.title.replace(/[\s,.;/?!:@=&"'<>#%{}|\\^~[\]`()*]+/g, '-') // strip ugly characters
      slug = slug.replace(/(^-|-$)/g, '') // trim dashes
      slug = slug.replace(/(-+)/g, '-') // remove multiple dashes
      slug = encodeURIComponent(slug) // encode remaining bad characters
      dataset.key = slug + '--' + dataset.uuid
      delete dataset.uuid
    }
    // Remove dummy ids needed to identify contacts when editing their fields.
    for (var i = 0, j = contacts.length; i < j; i++) {
      if (typeof contacts[i].id === 'string' && contacts[i].id.indexOf('new') === 0) {
        delete contacts[i].id
      }
    }
    context.commit('showSpinner', true)
    api.saveDataset(dataset, isNewDataset).then(success => {
      context.commit({type: 'updateOrAddDataset', dataset: clone(success.data)})
      const key = success.data.key
      api.saveContacts(key, contacts, isNewDataset).then(success => {
        context.commit({type: 'setSelectedDataset', key})
        context.commit('closeEditDialog')
        context.commit('showSpinner', false)
      }, err => {
        console.error(err)
        alert(Drupal.t('An error occurred while saving. If the error persists, please contact support.'))
        context.commit('showSpinner', false)
      })
    }, err => {
      console.error(err)
      alert(Drupal.t('An error occurred while saving. If the error persists, please contact support.'))
      context.commit('showSpinner', false)
    })
  }
}
