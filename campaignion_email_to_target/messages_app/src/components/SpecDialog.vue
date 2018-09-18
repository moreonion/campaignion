<template lang="html">
  <el-dialog
    :title="dialogTitle"
    :visible="visible"
    :close-on-click-modal="false"
    size="large"
    :before-close="dialogCancelHandler"
    >

    <section class="spec-label-field">
      <label for="spec-label">{{ text('spec label') }} <small>{{ text('seen only by you') }}</small></label>
      <input type="text" v-model="currentSpec.label" class="field-input" id="spec-label">
    </section>

    <filter-editor
      :fields="targetAttributes"
      :filters.sync="currentSpec.filters"
      :filter-default="{type: 'target-attribute'}"
      :operators="OPERATORS"
      >
    </filter-editor>

    <section class="spec-message-fields">
      <a href="#" @click="prefillMessage()" class="prefill-message" v-if="currentSpec.type == 'message-template'">{{ text('prefill') }}</a>
      <message-editor v-model="currentSpec.message" :type="currentSpec.type"></message-editor>
    </section>

    <tokens-list :token-categories="tokenCategories"></tokens-list>

    <section class="exclusion-warning" v-if="currentSpec.type === 'exclusion' && (currentSpecIndex > 0 || (currentSpecIndex === -1 && specs.length))">
      {{ text('exclusion warning') }}
    </section>

    <span slot="footer" :class="{'dialog-footer': true, 'dialog-alert': modalDirty}">
      <span v-if="modalDirty" class="dialog-alert-message">{{ text('unsaved changes') }}</span>
      <el-button @click="cancelButtonHandler()" class="js-modal-cancel">{{ text('Cancel') }}</el-button>
      <el-button type="primary" :disabled="currentSpecIsEmpty" @click="updateSpec" class="js-modal-save">{{ text('Done') }}</el-button>
    </span>

  </el-dialog>
</template>

<script>
import {clone} from '@/utils'
import {OPERATORS, emptySpec} from '@/utils/defaults'
import {mapState} from 'vuex'
import isEqual from 'lodash.isequal'
import omit from 'lodash.omit'
import animatedScrollTo from 'animated-scrollto'
import FilterEditor from './FilterEditor'
import MessageEditor from './MessageEditor'
import TokensList from './TokensList'

export default {

  components: {
    FilterEditor,
    MessageEditor,
    TokensList
  },

  data () {
    return {
      currentSpec: emptySpec('message-template'),
      visible: false,
      modalDirty: false,
      OPERATORS
    }
  },

  computed: {
    dialogTitle () {
      if (this.currentSpecIndex === null) {
        return ''
      } else if (this.currentSpecIndex === -1) {
        switch (this.currentSpec.type) {
          case 'message-template':
            return Drupal.t('Add specific Message')
          case 'exclusion':
            return Drupal.t('Add exclusion')
        }
      } else if (this.currentSpecIndex >= 0) {
        return Drupal.t('Edit @itemName', {'@itemName': this.currentSpec.label})
      }
    },
    currentSpecIsEmpty () {
      return this.currentSpecIndex !== null && isEqual(omit(this.currentSpec, ['id', 'errors', 'filterStr']), omit(emptySpec(this.currentSpec.type), ['id', 'errors', 'filterStr']))
    },
    ...mapState([
      'specs',
      'currentSpecIndex',
      'targetAttributes',
      'tokenCategories'
    ])
  },

  watch: {
    currentSpecIndex (val) {
      this.visible = val !== null
    }
  },

  methods: {
    text (text) {
      switch (text) {
        case 'spec label': return this.currentSpec.type === 'message-template' ? Drupal.t('Internal name for this message') : Drupal.t('Internal name for this exclusion')
        case 'seen only by you': return Drupal.t('(seen only by you)')
        case 'prefill': return Drupal.t('Prefill from default message')
        case 'exclusion warning': return Drupal.t('Keep in mind that the order of specific messages and exclusions is important. Targets matching this exclusion’s filters could receive specific messages if they also match their filters. Drag this exclusion to the top of the list if you want it to apply under any condition.')
        case 'unsaved changes': return Drupal.t('You have unsaved changes!')
        case 'Cancel': return this.modalDirty ? Drupal.t('Discard my changes') : Drupal.t('Cancel')
        case 'Done': return Drupal.t('Done')
      }
    },
    tryClose (options) {
      // any changes?
      if (this.currentSpecIndex !== -1 && isEqual(this.currentSpec, this.specs[this.currentSpecIndex]) ||
        this.currentSpecIndex === -1 && this.currentSpecIsEmpty ||
        (this.modalDirty && options && options.button === 'cancel')) {
        // no changes, allow to close modal
        return true
      } else {
        // there are unsaved changes, alert!
        this.modalDirty = true
        animatedScrollTo(
          this.$root.$el.querySelector('.el-dialog__wrapper'),
          this.$el.querySelector('.js-modal-cancel').offsetTop,
          400
        )
        return false
      }
    },
    dialogCancelHandler (done) {
      if (this.tryClose()) {
        this.close()
        done()
      }
    },
    cancelButtonHandler () {
      if (this.tryClose({button: 'cancel'})) {
        this.close()
      }
    },
    updateSpec () {
      this.$store.commit({type: 'updateSpec', spec: this.currentSpec})
      this.$store.commit('validateSpecs')
      this.close()
    },
    close () {
      this.modalDirty = false
      this.$store.commit('leaveSpec')
      this.$bus.$emit('closeSpecDialog')
    },
    prefillMessage () {
      if (!this.currentSpec.message) return
      for (var field in this.currentSpec.message) {
        if (this.currentSpec.message.hasOwnProperty(field)) {
          if (!this.currentSpec.message[field].trim()) {
            this.currentSpec.message[field] = this.$store.state.defaultMessage.message[field]
          }
        }
      }
    }
  },

  mounted () {
    this.$bus.$on('newSpec', type => {
      this.currentSpec = emptySpec(type)
      this.$store.commit('editNewSpec')
    })
    this.$bus.$on('editSpec', index => {
      this.currentSpec = clone(this.specs[index])
      this.$store.commit({type: 'editSpec', index})
    })
    this.$bus.$on('duplicateSpec', index => {
      const duplicate = clone(this.specs[index])
      duplicate.id = emptySpec(duplicate.type).id
      duplicate.label = Drupal.t('Copy of @messageName', {'@messageName': duplicate.label})
      this.currentSpec = duplicate
      this.$store.commit('editNewSpec')
    })
    document.addEventListener('keyup', e => {
      if (this.visible && !this.currentSpecIsEmpty && e.keyCode === 13 && document.activeElement.tagName.toLowerCase() !== 'textarea') {
        e.preventDefault()
        this.updateSpec()
      }
    })
  }

}
</script>

<style lang="scss">
.e2tmw {

  section {
    margin-bottom: 1rem;

    &.spec-message-fields { margin-bottom: 0; }
  }
}
</style>
