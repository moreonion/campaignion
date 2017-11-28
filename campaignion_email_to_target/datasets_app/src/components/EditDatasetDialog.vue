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
      <span class="dsa-target-data">{{ text('target data') }}</span>

      <v-client-table
        v-if="contacts.length"
        :data="contacts"
        :columns="tableColumns"
        :options="options"
        name="contactsTable"
        ref="contactsTable"
        class="dsa-contacts-table"
      >
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
      <button type="button" @click="addContact" class="dsa-add-contact">{{ text('add row') }}</button>
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

export default {
  components: {
    EditValuePopup
  },

  data () {
    return {
      options: {
        sortable: [],
        perPage: 20,
        perPageValues: [20]
      },
      modalDirty: false,
      showContactErrors: false,
      warnedBeforeUpload: false
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
      if (this.contacts.length && !this.warnedBeforeUpload) {
        e.preventDefault()
        this.$confirm(this.text('upload warning'), Drupal.t('Data will be lost'), {
          confirmButtonText: this.text('proceed'),
          cancelButtonText: Drupal.t('Cancel'),
          type: 'warning'
        }).then(() => {
          console.log(this.$refs.fileInput)
        }, () => {
        })
      }
    },

    processFile () {

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
        // TODO scroll to bottom?
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
</style>
