<template>
  <div class="email-to-target-messages-widget e2tmw" data-interrupt-submit :data-has-unsaved-changes="unsavedChanges">
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
import {clone, dispatch, isEmptyMessage} from '@/utils'
import {isEqual, omit} from 'lodash'
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
    unsavedChanges () {
      for (let i = 0, j = this.$store.state.specs.length; i < j; i++) {
        if (!isEqual(omit(this.$store.state.specs[i], ['errors', 'filterStr']), omit(this.$store.state.initialData.specs[i], ['errors', 'filterStr']))) {
          return true
        }
      }
      if (!isEqual(omit(this.$store.state.defaultMessage, ['errors', 'filterStr']), omit(this.$store.state.initialData.defaultMessage, ['errors', 'filterStr']))) {
        return true
      }
      return false
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
        case 'service unavailable title': return Drupal.t('Service unavailable')
        case 'service unavailable': return Drupal.t('The service is temporarily unavailable.\rYour messages could not be saved.\rPlease try again or contact support if the issue persists.')
        case 'unsaved changes title': return Drupal.t('Unsaved changes')
        case 'unsaved changes': return Drupal.t('You have unsaved changes!\rYou will lose your changes if you go back.')
        case 'invalid data title': return Drupal.t('Invalid data')
        case 'invalid data': return Drupal.t('There are validation errors (see error notices).\rYour campaign might not work as you intended.')
        case 'OK': return Drupal.t('OK')
        case 'Cancel': return Drupal.t('Cancel')
        case 'Go back anyway': return Drupal.t('Go back anyway')
        case 'Stay on page': return Drupal.t('Stay on page')
        case 'Save anyway': return Drupal.t('Save anyway')
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
  },

  mounted () {
    const listener = e => {
      const leavePage = () => {
        dispatch(this.$el, 'resume-leave-page')
      }

      const stayOnPage = () => {
        dispatch(this.$el, 'cancel-leave-page')
      }

      const putData = () => {
        const messages = clone(this.$store.state.specs)
        messages.push(clone(this.$store.state.defaultMessage))
        const data = JSON.stringify({
          messageSelection: messages
        })
        this.$http.put(Drupal.settings.campaignion_email_to_target.endpoints.messages, data).then((response) => {
          // success
          leavePage()
        }, (response) => {
          // error
          stayOnPage()
          this.$alert(this.text('service unavailable'), this.text('service unavailable title'), {
            confirmButtonText: this.text('OK')
          })
        })
      }

      if (e.type === 'request-leave-page') {
        if (this.unsavedChanges) {
          this.$confirm(this.text('unsaved changes'), this.text('unsaved changes title'), {
            confirmButtonText: this.text('Go back anyway'),
            cancelButtonText: this.text('Stay on page'),
            type: 'warning'
          }).then(() => { leavePage() }, () => { stayOnPage() })
        } else {
          leavePage()
        }
        return
      }

      if (this.$store.state.hardValidation) {
        var validationFailed = false
        for (let i = 0, j = this.specs.length; i < j; i++) {
          if (this.$store.state.specs[i].errors && this.$store.state.specs[i].errors.length) {
            validationFailed = true
            break
          }
        }
        if (this.defaultMessageErrors && this.defaultMessageErrors.length) {
          validationFailed = true
        }
        if (validationFailed) {
          this.$confirm(this.text('invalid data'), this.text('invalid data title'), {
            confirmButtonText: this.text('Save anyway'),
            cancelButtonText: this.text('Cancel'),
            type: 'warning'
          }).then(() => { putData() }, () => { stayOnPage() })
          return
        }
      }

      putData()
    }

    this.$el.addEventListener('request-submit-page', listener)
    this.$el.addEventListener('request-leave-page', listener)
  }

}
</script>

<style lang="scss">
.e2tmw {
  *, *:before, *:after {
    box-sizing: border-box;
  }

  input, textarea {
    min-height: 1.5rem;
    border: 1px solid #aaa;
    border-radius: 3px;
  }

  ul, li {
    list-style: none;
    padding: 0;
    margin: 0;
  }

  .default-message .message-editor {
    width: 60%;
    display: inline-block;
    vertical-align: top;
  }
}
</style>
