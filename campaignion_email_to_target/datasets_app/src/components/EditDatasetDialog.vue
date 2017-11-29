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

    <span slot="footer" :class="{'dialog-footer': true, 'dialog-alert': modalDirty}">
      <el-button @click="chooseDataset()" class="dsa-choose-dataset" :disabled="datasetChanged || showSpinner">{{ text('choose dataset') }}</el-button>
      <span v-if="modalDirty" class="dialog-alert-message">{{ text('unsaved changes') }}</span>
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
      options: {
        sortable: [],
        perPage: 20,
        perPageValues: [20],
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
      modalDirty: false,
      showContactErrors: false
    }
  },

  computed: {
    dialogTitle () {
      return this.datasetIsNew
        ? Drupal.t('Edit new dataset')
        : Drupal.t('Edit "@dataset"', {'@dataset': this.currentDataset.title})
    },
    datasetIsNew () {
      return !this.currentDataset.key
    },
    datasetIsEmpty () {
      return !this.currentDataset.title.length || !this.contacts.length
    },
    contactsAreValid () {
      return !find(this.contacts, '__error')
    },
    ...mapState([
      'currentDataset',
      'contacts',
      'tableColumns',
      'standardColumns',
      'contactsTable',
      'showEditDialog',
      'showSpinner',
      'datasetChanged'
    ])
  },

  watch: {
    showEditDialog (val) {
      if (val) {
        // init dialog
        this.modalDirty = false
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
      // in case a contact has been deleted, check if there are still contacts on the current page
      // if there aren’t, go to the last page
      if (contacts.length && this.contactsTable && this.contactsTable.page > Math.ceil(contacts.length / this.contactsTable.limit)) {
        this.$refs.contactsTable.setPage(Math.ceil(contacts.length / this.contactsTable.limit))
      }
    }
  },

  methods: {
    addContact () {
      this.$store.commit('addContact')
      this.$nextTick(() => {
        this.$refs.contactsTable.setFilter('') // clear the filter so the new row is sure to be displayed
        this.$refs.contactsTable.setPage(Math.ceil(this.contacts.length / this.contactsTable.limit)) // go to last page
      })
    },

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

    updateTitle (e) {
      this.$store.commit('updateTitle', e.target.value)
    },

    updateDescription (e) {
      this.$store.commit('updateDescription', e.target.value)
    },

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

    saveDataset () {
      if (this.datasetChanged) {
        if (this.contactsAreValid) {
          this.$store.dispatch('saveDataset') // dialog is closed by action
        } else {
          this.$refs.contactsTable.setFilter(INVALID_CONTACT_STRING)
          this.showContactErrors = true
        }
      } else {
        this.$store.commit({type: 'setSelectedDataset', key: this.currentDataset.key})
        this.$store.commit('closeEditDialog')
      }
    },

    chooseDataset () {
      this.$store.commit('closeEditDialog')
      this.$store.commit('openSelectDialog')
    },

    dialogCancelHandler (done) {
      if (this.datasetChanged) {
        this.modalDirty = true // show warning
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

    cancelButtonHandler () {
      if (this.datasetChanged && !this.modalDirty) {
        this.modalDirty = true // show warning
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
        case 'Cancel': return this.modalDirty ? Drupal.t('Discard my changes') : Drupal.t('Cancel')
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
