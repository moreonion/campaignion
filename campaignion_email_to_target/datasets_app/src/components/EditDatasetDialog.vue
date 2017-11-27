<template lang="html">
  <el-dialog
    :title="dialogTitle"
    :visible="showEditDialog"
    :close-on-click-modal="false"
    size="large"
    :before-close="dialogCancelHandler"
    >

    <v-client-table v-if="contacts.length" :data="contacts" :columns="columns" :options="options" name="contactsTable" class="dsa-contacts-table"/>
    <EditValuePopup />

    <span slot="footer" :class="{'dialog-footer': true, 'dialog-alert': modalDirty}">
      <span v-if="modalDirty" class="dialog-alert-message">{{ text('unsaved changes') }}</span>
      <el-button @click="cancelButtonHandler()" class="js-modal-cancel">{{ text('Cancel') }}</el-button>
      <el-button type="primary" :disabled="datasetIsEmpty" @click="saveDataset" class="js-modal-save">{{ text('Save') }}</el-button>
    </span>

  </el-dialog>
</template>

<script>
import EditValuePopup from './components/EditValuePopup'
import {mapState} from 'vuex'

export default {
  components: {
    EditValuePopup
  },

  data () {
    return {
      columns: ['email', 'first_name', 'last_name', 'salutation'],
      options: {},
      modalDirty: false
    }
  },

  computed: {
    dialogTitle () {
      // TODO Edit new dataset || Edit My Dataset Title
    },
    datasetIsEmpty () {
      // TODO length && label
    },
    ...mapState([
      'contacts',
      'showEditDialog'
    ])
  },

  methods: {
    dialogCancelHandler (done) {
      // TODO
      console.log('you clicked the x')
      done()
    },

    cancelButtonHandler () {
      // TODO
      console.log('cancel')
    },

    text (text) {
      switch (text) {
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
