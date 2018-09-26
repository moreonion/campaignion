<template lang="html">
  <el-dialog v-if="currentDataset"
    :title="dialogTitle"
    :visible="showEditDialog"
    :close-on-click-modal="false"
    size="large"
    :before-close="dialogCancelHandler"
    class="dsa-edit-dataset-dialog"
    >
    <div v-loading="showSpinner" class="dsa-edit-dataset-dialog-body-wrapper">
      <section class="dsa-dataset-meta">
        <div>
          <label for="dsa-dataset-title">{{ text('dataset title') }} <small>{{ text('only for internal use') }}</small></label>
          <input type="text" :value="currentDataset.title" @input="updateTitle" class="field-input" id="dsa-dataset-title" />
        </div>
        <div>
          <label for="dsa-dataset-description">{{ text('dataset description') }} <small>{{ text('only for internal use') }}</small></label>
          <input type="text" :value="currentDataset.description" @input="updateDescription" class="field-input" id="dsa-dataset-description">
        </div>
      </section>

      <v-client-table
        :data="contacts"
        :columns="tableColumns"
        :options="options"
        name="contactsTable"
        ref="contactsTable"
        class="dsa-contacts-table"
      >
        <span slot="beforeFilter" class="dsa-target-data ae-legend">{{ text('target data') }}</span>

        <span class="dsa-upload-data-wrapper" slot="afterFilter">
          <label for="dsa-updoad-data" @click="chooseFile" class="el-button">{{ text('upload dataset') }}</label>
          <input ref="fileInput" type="file" tabindex="-1" @change="processFile" id="dsa-updoad-data" accept=".csv, .CSV" />
        </span>

        <template slot="__error" scope="props">
          <span v-if="showContactErrors && props.row.__error" class="dsa-invalid-contact">✘</span>
        </template>

        <template slot="__delete" scope="props">
          <a href="#" class="dsa-delete-contact" @click.prevent.stop="deleteContact(props.row.id)">{{ text('delete') }}</a>
        </template>

        <template :slot="'h__' + attribute.key" scope="props" v-for="attribute in currentDataset.attributes">
          <span class="VueTables__heading" :title="attribute.description">{{ attribute.title }}</span>
        </template>

        <template slot="h____error" scope="props"></template>

        <template slot="h____delete" scope="props">
          <span class="VueTables__heading"></span>
        </template>
      </v-client-table>
      <el-button type="button" @click="addContact" class="dsa-add-contact">{{ text('add row') }}</el-button>
    </div>

    <EditValuePopup />

    <span slot="footer" :class="{'dialog-footer': true, 'dialog-alert': showUnsavedChangesWarning}">
      <el-button @click="chooseDataset()" class="dsa-choose-dataset" :disabled="datasetChanged || showSpinner">{{ text('choose dataset') }}</el-button>
      <span v-if="showUnsavedChangesWarning" class="dialog-alert-message">{{ text('unsaved changes') }}</span>
      <el-button :disabled="showSpinner" @click="cancelButtonHandler()" class="js-modal-cancel">{{ text('Cancel') }}</el-button>
      <el-button type="primary" :disabled="datasetIsEmpty || showSpinner" @click="saveDataset" class="js-modal-save">{{ text('Save') }}</el-button>
    </span>
  </el-dialog>
</template>

<script>
import EditValuePopup from '@/components/EditValuePopup'
import {mapState} from 'vuex'
import {INVALID_CONTACT_STRING} from '@/utils'
import {find} from 'lodash'
import animatedScrollTo from 'animated-scrollto'
import Papa from 'papaparse'

export default {
  components: {
    EditValuePopup
  },

  data () {
    return {
      options: {             // Options for vue-tables-2.
        sortable: [],        // None of the columns is sortable.
        perPage: 20,         // Initial records per page.
        perPageValues: [20], // Records per page options.
        texts: {
          count: Drupal.t('Showing {from} to {to} of {count} records|{count} records|One record'),
          filter: '',
          filterPlaceholder: Drupal.t('Filter targets'),
          limit: Drupal.t('Records per page:'),
          page: Drupal.t('Page:'),
          noResults: Drupal.t('No targets found.'),
          filterBy: Drupal.t('Filter by {column}'),
          loading: Drupal.t('Loading...'),
          defaultOption: Drupal.t('Select {column}'),
          columns: Drupal.t('Columns')
        }
      },
      showUnsavedChangesWarning: false, // True if there are unsaved changes and the user tried to cancel the dialog.
      showContactErrors: false          // Visibility of the error column for marking contacts where validation failed.
    }
  },

  computed: {
    /** @return {string} Dialog title containing the dataset name or saying 'new dataset'. */
    dialogTitle () {
      return this.datasetIsNew
        ? Drupal.t('Edit new dataset')
        : Drupal.t('Edit "@dataset"', {'@dataset': this.currentDataset.title})
    },

    /** @return {boolean} Is the dataset being edited a new one? */
    datasetIsNew () {
      return !this.currentDataset.key
    },

    /** @return {boolean} Is the dataset lacking the minimal content (a title and one contact)? */
    datasetIsEmpty () {
      return !this.currentDataset.title.length || !this.contacts.length
    },

    /** @return {boolean} Are the contacts valid? Check for __error properties. */
    contactsAreValid () {
      return !find(this.contacts, '__error')
    },

    ...mapState([
      'currentDataset',  /** {(Object|null)} The dataset being edited. */
      'contacts',        /** {Object[]} Array of contacts belonging to the current dataset. */
      'tableColumns',    /** {string[]} Array of column identifiers. */
      'standardColumns', /** {Object[]} Array of objects describing the standard columns. */
      'contactsTable',   /** {(Object|undefined)} vue-tables-2 state via vuex. */
      'showEditDialog',  /** {boolean} Visibility of the edit dataset dialog. */
      'showSpinner',     /** {boolean} Visibility of the loading spinner. */
      'datasetChanged'   /** {boolean} True if the user has made changes on the current dataset. */
    ])
  },

  watch: {
    showEditDialog (val) {
      if (val) {
        // Initialize dialog.
        this.showUnsavedChangesWarning = false
        this.showContactErrors = false
        if (this.$refs.contactsTable) {
          this.$refs.contactsTable.setPage(1)
          this.$refs.contactsTable.setFilter('')
        }
        if (this.$refs.fileInput) {
          this.$refs.fileInput.value = ''
        }
      }
    },

    contacts (contacts) {
      // In case a contact has been deleted, check if there are still contacts on the current page.
      // If there aren’t, go to the last page.
      if (contacts.length && this.contactsTable && this.contactsTable.page > Math.ceil(contacts.length / this.contactsTable.limit)) {
        this.$refs.contactsTable.setPage(Math.ceil(contacts.length / this.contactsTable.limit))
      }
    }
  },

  methods: {
    /**
     * Append a contact to the list, clear the table filter and show the last page.
     */
    addContact () {
      this.$store.commit('addContact')
      this.$nextTick(() => {
        this.$refs.contactsTable.setFilter('') // Clear the filter so the new row is sure to be displayed.
        this.$refs.contactsTable.setPage(Math.ceil(this.contacts.length / this.contactsTable.limit)) // Go to last page.
      })
    },

    /**
     * Delete a contact from the list (with confirmation).
     * @param {integer} id - The id of the contact to delete.
     */
    deleteContact (id) {
      this.$store.commit('leaveValue')
      this.$confirm(Drupal.t('Do you really want to remove this target?'), Drupal.t('Delete contact'), {
        confirmButtonText: Drupal.t('Delete'),
        cancelButtonText: Drupal.t('Cancel'),
        type: 'warning'
      }).then(() => {
        this.$store.commit('deleteContact', id)
      }, () => {})
    },

    /**
     * Handle input on the title field.
     * @param {Event} e - The native event.
     */
    updateTitle (e) {
      this.$store.commit('updateTitle', e.target.value)
    },

    /**
     * Handle input on the description field.
     * @param {Event} e - The native event.
     */
    updateDescription (e) {
      this.$store.commit('updateDescription', e.target.value)
    },

    /**
     * Handle click on the file input’s label.
     * Warn if there are contacts in the list.
     * @param {Event} e - The native event.
     */
    chooseFile (e) {
      if (this.contacts.length) {
        e.preventDefault()
        this.$confirm(this.text('upload warning'), this.text('Data will be lost'), {
          confirmButtonText: this.text('proceed'),
          cancelButtonText: Drupal.t('Cancel'),
          type: 'warning'
        }).then(() => {
          this.$refs.fileInput.click()
        }, () => {
        })
      }
    },

    /**
     * Parse CSV files. Show a spinner while processing.
     * Handle parsing errors. Load the parsed contacts to the contact list.
     * Validate the parsed contacts and filter the list for invalid rows.
     */
    processFile () {
      this.$store.commit('showSpinner', true)
      Papa.parse(this.$refs.fileInput.files[0], {
        header: true,
        skipEmptyLines: true,
        complete: ({data, errors, meta}) => {
          // clean up result
          if (errors &&
            errors.length === 1 &&
            errors[0].code === 'TooFewFields' &&
            errors.row === data.length - 1 &&
            Object.keys(data[data.length - 1]).length === 1) {
            data.pop()
          }
          // validate result
          if (!meta.fields) {
            this.$alert(Drupal.t('Your file seems to be crap.'), Drupal.t('Invalid data'))
            this.$store.commit('showSpinner', false)
            return
          }
          const missingCols = []
          for (var i = 0, j = this.standardColumns.length; i < j; i++) {
            if (meta.fields.indexOf(this.standardColumns[i].key) === -1) {
              missingCols.push(this.standardColumns[i].key)
            }
          }
          if (missingCols.length) {
            this.$alert(Drupal.t('The following columns are missing in the headers line: ') + missingCols.join(', '), Drupal.t('Invalid data'))
            this.$store.commit('showSpinner', false)
            return
          }
          if (data.length < 1) {
            this.$alert(Drupal.t('We want targets in the file!'), Drupal.t('Invalid data'))
            this.$store.commit('showSpinner', false)
            return
          }
          this.$store.commit('setContacts', data)
          this.$store.commit('validateContacts')
          this.$store.commit('showSpinner', false)
          this.$refs.contactsTable.setPage(1)
          if (this.contactsAreValid) {
            this.$refs.contactsTable.setFilter('')
          } else {
            this.$alert(Drupal.t('I filtered the table so you see only the invalid contacts. You can remove the filter after fixing your targets.'), Drupal.t('Some contacts are not valid.'))
            this.$refs.contactsTable.setFilter(INVALID_CONTACT_STRING)
            this.showContactErrors = true
          }
        },
        error: (error, file) => {
          this.$alert(Drupal.t('Your file seems to be crap.'), Drupal.t('Parsing error'))
          this.$store.commit('showSpinner', false)
          console.log('Parsing error:', error, file)
        }
      })
    },

    /**
     * Handle clicks on the Save button.
     * If there are changes in the dataset, check for invalid contacts and eventually filter the list
     * before saving the dataset and closing the dialog. If there aren’t any changes, set the selected
     * dataset and close the dialog.
     */
    saveDataset () {
      if (this.datasetChanged) {
        if (this.contactsAreValid) {
          this.$store.dispatch('saveDatasetAndContacts') // dialog is closed by action
        } else {
          this.$refs.contactsTable.setFilter(INVALID_CONTACT_STRING)
          this.showContactErrors = true
        }
      } else {
        this.$store.commit({type: 'setSelectedDataset', key: this.currentDataset.key})
        this.$store.commit('closeEditDialog')
      }
    },

    /**
     * Close the edit dataset dialog and open the dialog to select a dataset.
     */
    chooseDataset () {
      this.$store.commit('closeEditDialog')
      this.$store.commit('openSelectDialog')
    },

    /**
     * Handle cancelling of the dialog via the X button or the ESC key.
     * Show a warning about unsaved changes and scroll there, or close the dialog.
     * @param {function} done - Passed by element-ui dialog. Call done() to finish closing the dialog.
     */
    dialogCancelHandler (done) {
      if (this.datasetChanged) {
        this.showUnsavedChangesWarning = true
        animatedScrollTo(
          this.$root.$el.querySelector('.el-dialog__wrapper.dsa-edit-dataset-dialog'),
          this.$el.querySelector('.js-modal-cancel').offsetTop,
          400
        )
      } else {
        this.$store.commit('closeEditDialog')
        done()
      }
    },

    /**
     * Handle cancelling of the dialog via the Cancel button.
     * Show a warning about unsaved changes, or close the dialog.
     */
    cancelButtonHandler () {
      if (this.datasetChanged && !this.showUnsavedChangesWarning) {
        this.showUnsavedChangesWarning = true
      } else {
        this.$store.commit('closeEditDialog')
      }
    },

    text (text) {
      switch (text) {
        case 'dataset title': return Drupal.t('Name of your dataset')
        case 'dataset description': return Drupal.t('Description')
        case 'only for internal use': return Drupal.t('for internal use only')
        case 'upload dataset': return Drupal.t('Upload dataset (CSV)')
        case 'upload warning': return Drupal.t('The existing dataset will be replaced with the CSV data. The existing data will be removed.')
        case 'Data will be lost': return Drupal.t('Data will be lost')
        case 'proceed': return Drupal.t('Yes, proceed')
        case 'target data': return Drupal.t('The target data')
        case 'add row': return Drupal.t('Add a new target')
        case 'delete': return Drupal.t('Delete')
        case 'choose dataset': return Drupal.t('Choose a different dataset')
        case 'unsaved changes': return Drupal.t('You have unsaved changes!')
        case 'Cancel': return this.showUnsavedChangesWarning ? Drupal.t('Discard my changes') : Drupal.t('Cancel')
        case 'Save': return Drupal.t('Save')
      }
    }
  }
}
</script>

<style lang="css">
input#dsa-updoad-data {
  display: none;
}
.table-responsive {
  overflow-x: auto;
}
</style>
