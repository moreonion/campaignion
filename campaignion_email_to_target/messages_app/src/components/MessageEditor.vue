<template>
  <fieldset class="message-editor">
    <slot name="legend"></slot>
    <template v-if="type == 'message-template'">
      <div class="form-group">
        <label :for="'message-subject-' + _uid">{{ text('subject label') }} <a href="#" @click.prevent="toggleHelpText('subject')" class="show-help-text"><span>?</span></a></label>
        <input type="text" v-model="subject" data-token-insertable class="field-input" :id="'message-subject-' + _uid">
        <small v-if="helpText['subject']" class="help-text">{{ text('subject help') }}</small>
      </div>
      <div class="form-group">
        <label :for="'message-header-' + _uid">{{ text('header label') }} <a href="#" @click.prevent="toggleHelpText('header')" class="show-help-text"><span>?</span></a></label>
        <textarea rows="3" v-model="header" data-token-insertable class="field-input" :id="'message-header-' + _uid"></textarea>
        <small v-if="helpText['header']" class="help-text">{{ text('header help') }}</small>
      </div>
      <div class="form-group">
        <label :for="'message-body-' + _uid">{{ text('body label') }} <a href="#" @click.prevent="toggleHelpText('body')" class="show-help-text"><span>?</span></a></label>
        <textarea rows="6" v-model="body" data-token-insertable class="field-input" :id="'message-body-' + _uid"></textarea>
        <small v-if="helpText['body']" class="help-text">{{ text('body help') }}</small>
      </div>
      <div class="form-group">
        <label :for="'message-footer-' + _uid">{{ text('footer label') }} <a href="#" @click.prevent="toggleHelpText('footer')" class="show-help-text"><span>?</span></a></label>
        <textarea rows="3" v-model="footer" data-token-insertable class="field-input" :id="'message-footer-' + _uid"></textarea>
        <small v-if="helpText['footer']" class="help-text">{{ text('footer help') }}</small>
      </div>
    </template>
    <template v-if="type == 'exclusion'">
      <div class="form-group">
        <label :for="'message-body-' + _uid">{{ text('exclusion label') }} <a href="#" @click.prevent="toggleHelpText('body')" class="show-help-text"><span>?</span></a></label>
        <textarea rows="6" v-model="body" data-token-insertable class="field-input" :id="'message-body-' + _uid"></textarea>
        <small v-if="helpText['body']" class="help-text">{{ text('exclusion help') }}</small>
      </div>
    </template>
  </fieldset>
</template>

<script>
import {messageObj} from '@/utils/defaults'

export default {

  data () {
    return {
      subject: this.value.subject,
      header: this.value.header,
      body: this.value.body,
      footer: this.value.footer,
      helpText: {
        subject: false,
        header: false,
        body: false,
        footer: false
      }
    }
  },

  props: {
    value: {
      type: Object,
      default: () => messageObj()
    },
    type: String
  },

  watch: {
    subject: 'updateValue',
    header: 'updateValue',
    body: 'updateValue',
    footer: 'updateValue',
    value: {
      handler (val) {
        this.subject = val.subject
        this.header = val.header
        this.body = val.body
        this.footer = val.footer
      },
      deep: true
    }
  },

  methods: {
    text (text) {
      switch (text) {
        case 'subject label': return Drupal.t('Subject')
        case 'subject help': return Drupal.t('This is the subject line of the message that will be sent to the target.')
        case 'header label': return Drupal.t('Opening')
        case 'header help': return Drupal.t('This part of the message will not be editable by your supporters.')
        case 'body label': return Drupal.t('Body')
        case 'body help': return Drupal.t('This is the main part of the message that will be sent to the target. If you have chosen to make the message editable by your supporters, they will be able to edit this part of the message.')
        case 'footer label': return Drupal.t('Closing')
        case 'footer help': return Drupal.t('This part of the message will not be editable by your supporters.')
        case 'exclusion label': return Drupal.t('Message shown if no target is available')
        case 'exclusion help': return Drupal.t('This message is shown if this exclusion comes into effect and no targets are found for a supporter.')
      }
    },
    toggleHelpText (which) {
      this.helpText[which] = !this.helpText[which]
    },
    updateValue () {
      this.$emit('input', {
        subject: this.subject,
        header: this.header,
        body: this.body,
        footer: this.footer
      })
    }
  },

  mounted () {
    this.$bus.$on('closeSpecDialog', () => {
      for (let which in this.helpText) {
        if (this.helpText.hasOwnProperty(which)) {
          this.helpText[which] = false
        }
      }
    })
  }

}
</script>

<style lang="scss">
.e2tmw {
  fieldset.message-editor label {
    display: block;
  }
}
</style>
