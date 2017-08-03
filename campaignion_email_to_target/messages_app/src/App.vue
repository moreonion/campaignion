<template>
  <div id="app">
    <el-button @click="newSpec('message-template')">{{ text('Create message') }}</el-button>
    <el-button @click="newSpec('exclusion')">{{ text('Create exclusion') }}</el-button>
    <spec-list></spec-list>
    <section class="default-message">
      <message-editor :value="defaultMessage.message" @input="updateDefaultMessage" type="message-template">
        <legend slot="legend">{{ specs.length ? text('message to remaining targets') : text('default message') }}</legend>
      </message-editor>
      <ul class="spec-errors">
        <li v-for="error in defaultMessageErrors" class="spec-error">{{ error.message }}</li>
      </ul>
      <tokens-list :token-categories="tokenCategories"></tokens-list>
    </section>

    <spec-dialog></spec-dialog>
  </div>
</template>

<script>
import {mapState} from 'vuex'
import {isEmptyMessage} from '@/utils'
import SpecList from './components/SpecList'
import MessageEditor from './components/MessageEditor'
import TokensList from './components/TokensList'
import SpecDialog from './components/SpecDialog'

export default {

  name: 'app',

  components: {
    SpecList,
    MessageEditor,
    TokensList,
    SpecDialog
  },

  computed: {
    defaultMessageErrors () {
      if (isEmptyMessage(this.defaultMessage.message)) {
        return [{type: 'message', message: 'Message is empty'}]
      }
    },
    ...mapState([
      'specs',
      'defaultMessage',
      'tokenCategories'
    ])
  },

  methods: {
    text (text) {
      switch (text) {
        case 'Create message': return Drupal.t('Create message')
        case 'Create exclusion': return Drupal.t('Create exclusion')
        case 'message to remaining targets': return Drupal.t('Message to all remaining targets')
        case 'default message': return Drupal.t('Default message that will be sent to target(s)')
      }
    },
    newSpec (type) {
      this.$bus.$emit('newSpec', type)
    },
    updateDefaultMessage (val) {
      this.$store.commit({type: 'updateDefaultMessage', message: val})
    }
  },

  created () {
    this.$store.commit('initializeData', Drupal.settings.campaignion_email_to_target)
    this.$store.commit('validateSpecs')
  }

}
</script>

<style>
</style>
