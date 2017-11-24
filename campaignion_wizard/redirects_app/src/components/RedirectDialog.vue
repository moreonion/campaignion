<template lang="html">
  <ElDialog
    :title="dialogTitle"
    :visible="visible"
    :close-on-click-modal="false"
    size="large"
    :before-close="dialogCancelHandler"
    >

    <section class="pra-redirect-fields">
      <label :for="'pra-redirect-label-' + _uid">{{ text('Redirect label') }} <small>{{ text('seen only by you') }}</small></label>
      <input type="text" v-model="currentRedirect.label" class="field-input" :id="'pra-redirect-label-' + _uid">
      <label :for="'pra-redirect-destination-' + _uid">{{ text('Redirect destination') }} <small>{{ text('type a node title or ID or paste a URL') }}</small></label>
      <DestinationField
        :id="'pra-redirect-destination-' + _uid"
        :value="destination"
        :placeholder="text('Type to search nodes')"
        :show-dropdown-on-focus="true"
        data-key="values"
        label-key="label"
        url="http://foo.bar.com"
        :headers="{'Authorization': 'JWT foo.bar.3456ß8971230469827456.jklcnfgb'}"
        search-param="q"
        :count="20"
        @input="item => {destination = item}"
      />
      <p>{{ currentRedirect.destination }}</p>
    </section>
<!--
    <FilterEditor
      :fields="targetAttributes"
      :filters.sync="currentRedirect.filters"
      :filter-default="{type: 'target-attribute'}"
      :operators="OPERATORS"
    />
-->
    <span slot="footer" :class="{'pra-dialog-footer': true, 'pra-dialog-alert': modalDirty}">
      <span v-if="modalDirty" class="pra-dialog-alert-message">{{ text('unsaved changes') }}</span>
      <el-button @click="cancelButtonHandler()" class="js-modal-cancel">{{ text('Cancel') }}</el-button>
      <el-button type="primary" :disabled="currentRedirectIsEmpty" @click="updateRedirect" class="js-modal-save">{{ text('Done') }}</el-button>
    </span>

  </ElDialog>
</template>

<script>
import {clone} from '@/utils'
import {OPERATORS, emptyRedirect} from '@/utils/defaults'
import {mapState} from 'vuex'
import {isEqual, omit} from 'lodash'
import DestinationField from './DestinationField'

export default {
  components: {
    DestinationField
  },

  data () {
    return {
      currentRedirect: emptyRedirect(),
      modalDirty: false,
      OPERATORS
    }
  },

  computed: {
    dialogTitle () {
      if (this.currentRedirectIndex === -1) {
        return Drupal.t('Add personalized redirect')
      } else if (this.currentRedirectIndex >= 0) {
        if (this.currentRedirect.label) {
          return Drupal.t('Edit @itemName', {'@itemName': this.currentRedirect.label})
        } else {
          return Drupal.t('Edit personalized redirect')
        }
      }
    },
    currentRedirectIsEmpty () {
      return this.currentRedirectIndex !== null && isEqual(omit(this.currentRedirect, ['id', 'prettyDestination']), omit(emptyRedirect(), ['id', 'prettyDestination']))
    },
    visible () {
      return this.currentRedirectIndex !== null
    },
    destination: {
      get () {
        console.log('get destination')
        return {
          value: this.currentRedirect.destination,
          label: this.currentRedirect.prettyDestination
        }
      },
      set (val) {
        console.log('set destination')
        this.currentRedirect.destination = val.value
        this.currentRedirect.prettyDestination = val.label
      }
    },
    ...mapState([
      'redirects',
      'currentRedirectIndex'
    ])
  },

  methods: {
    text (text) {
      switch (text) {
        case 'Redirect label': return Drupal.t('Redirect label')
        case 'seen only by you': return Drupal.t('(seen only by you)')
        case 'Redirect destination': return Drupal.t('Redirect destination')
        case 'type a node title or ID or paste a URL': return Drupal.t('type a node title or ID or paste a URL')
        case 'Type to search nodes': return Drupal.t('Type to search nodes')
        case 'unsaved changes': return Drupal.t('You have unsaved changes!')
        case 'Cancel': return this.modalDirty ? Drupal.t('Discard my changes') : Drupal.t('Cancel')
        case 'Done': return Drupal.t('Done')
      }
    },
    tryClose (options) {
      // Any changes?
      if (this.currentRedirectIndex !== -1 && isEqual(this.currentRedirect, this.redirects[this.currentRedirectIndex]) ||
        this.currentRedirectIndex === -1 && this.currentRedirectIsEmpty ||
        (this.modalDirty && options && options.button === 'cancel')) {
        // No changes or force close via cancel button: allow to close modal.
        return true
      } else {
        // There are unsaved changes, alert!
        this.modalDirty = true
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
    updateRedirect () {
      // TODO: validate destination field!
      // TODO: save prettyDestination
      this.$store.commit({type: 'updateRedirect', redirect: this.currentRedirect})
      this.close()
    },
    close () {
      this.modalDirty = false
      this.$store.commit('leaveRedirect')
      this.$root.$emit('closeRedirectDialog')
    }
  },

  mounted () {
    this.$root.$on('newRedirect', () => {
      console.log('caught event on bus: new')
      this.currentRedirect = emptyRedirect()
      this.$store.commit('editNewRedirect')
    })
    this.$root.$on('editRedirect', index => {
      this.currentRedirect = clone(this.redirects[index])
      this.$store.commit({type: 'editRedirect', index})
    })
    this.$root.$on('duplicateRedirect', index => {
      const duplicate = clone(this.redirects[index])
      duplicate.id = emptyRedirect().id
      duplicate.label = Drupal.t('Copy of @redirectLabel', {'@redirectLabel': duplicate.label})
      this.currentRedirect = duplicate
      this.$store.commit('editNewRedirect')
    })
    document.addEventListener('keyup', e => {
      // Catch Enter key and save redirect.
      if (this.visible && !this.currentRedirectIsEmpty && e.keyCode === 13 && document.activeElement.tagName.toLowerCase() !== 'textarea') {
        e.preventDefault()
        this.updateRedirect()
      }
    })
  }
}
</script>

<style lang="css">
</style>
