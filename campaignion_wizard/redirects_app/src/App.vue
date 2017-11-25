<template>
<div class="redirect-app" data-interrupt-submit :data-has-unsaved-changes="unsavedChanges">
  <ElButton @click="newRedirect()" class="pra-add-redirect">{{ text('Add redirect') }}</ElButton>
  <RedirectList/>

  <section class="pra-default-redirect">
    <label :for="'pra-default-redirect-destination-' + _uid">{{ text('Default redirect') }} <small>{{ text('type a node title or ID or paste a URL') }}</small></label>
    <DestinationField
      :id="'pra-default-redirect-destination-' + _uid"
      :class="{'pra-has-error': showErrors && !destinationIsValid}"
      :value="destination"
      :placeholder="text('Type to search nodes')"
      :show-dropdown-on-focus="true"
      data-key="values"
      label-key="label"
      :url="$root.$options.settings.endpoints.nodes"
      :headers="{}"
      search-param="q"
      :count="20"
      @input="item => {destination = item}"
    />
    <div v-if="showErrors && !destinationIsValid" class="pra-error-message">{{ text('destination error') }}</div>
  </section>

  <RedirectDialog/>
</div>
</template>

<script>
import {mapState} from 'vuex'
import {isEqual} from 'lodash'
import {clone, dispatch, validateDestination} from '@/utils'
import api from '@/utils/api'
import RedirectList from './components/RedirectList'
import RedirectDialog from './components/RedirectDialog'
import DestinationField from './components/DestinationField'

export default {
  name: 'app',

  components: {
    RedirectList,
    RedirectDialog,
    DestinationField
  },

  data () {
    return {
      showErrors: false
    }
  },

  computed: {
    destination: {
      // destination and prettyDestination translated for the DestinationField
      // component as value and label
      get () {
        return {
          value: this.defaultRedirect.destination,
          label: this.defaultRedirect.prettyDestination
        }
      },
      set (val) {
        this.$store.commit({
          type: 'updateDefaultRedirect',
          destination: val.value,
          prettyDestination: val.label
        })
      }
    },
    destinationIsValid () {
      return validateDestination(this.defaultRedirect.destination)
    },
    unsavedChanges () {
      if (this.redirects.length !== this.initialData.redirects.length) return true
      if (!isEqual(this.defaultRedirect, this.initialData.defaultRedirect)) return true
      for (let i = 0, j = this.redirects.length; i < j; i++) {
        if (!isEqual(this.redirects[i], this.initialData.redirects[i])) return true
      }
      return false
    },
    ...mapState([
      'redirects',
      'defaultRedirect',
      'initialData'
    ])
  },

  created () {
    // Shortcut to settings.
    this.$root.$options.settings = Drupal.settings.campaignion_wizard[this.$root.$options.drupalContainer.id]
  },

  mounted () {
    // Initialize data
    this.$store.commit({
      type: 'initData',
      redirects: this.$root.$options.settings.redirects,
      defaultRedirectUrl: this.$root.$options.settings.default_redirect_url
    })
    // Handle events from interrupt-submit.js
    const listener = e => {
      const leavePage = () => {
        dispatch(this.$root.$el, 'resume-leave-page')
      }

      const stayOnPage = () => {
        dispatch(this.$root.$el, 'cancel-leave-page')
      }

      if (e.type === 'request-leave-page') {
        // User clicked 'back' button.
        // Forget about unsaved changes if the app is hidden.
        if (this.appIsHidden()) {
          leavePage()
          return
        }
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
      } else if (e.type === 'request-submit-page') {
        // User clicked one of the submit buttons.
        // If nothing has changed, just submit.
        if (!this.unsavedChanges) {
          leavePage()
          return
        }
        // Validate destination field (only if the app is visible).
        if (!this.destinationIsValid && !this.appIsHidden()) {
          stayOnPage()
          this.showErrors = true
          return
        }
        this.persistData().then(() => {
          leavePage()
        }, () => {
          stayOnPage()
          this.$alert(this.text('service unavailable'), this.text('service unavailable title'), {
            confirmButtonText: this.text('OK')
          })
        })
      }
    }
    this.$root.$el.addEventListener('request-submit-page', listener)
    this.$root.$el.addEventListener('request-leave-page', listener)
  },

  methods: {
    appIsHidden () {
      return this.$root.$options.drupalContainer.style.display === 'none'
    },
    newRedirect () {
      this.$root.$emit('newRedirect')
    },
    persistData () {
      return new Promise((resolve, reject) => {
        var redirects = clone(this.redirects)
        redirects.push(clone(this.defaultRedirect))
        api.postData({
          url: this.$root.$options.settings.endpoints.redirects,
          data: {redirects},
          headers: {}
        }).then(() => {
          resolve()
        }, error => {
          reject(error)
        })
      })
    },
    text (text) {
      switch (text) {
        case 'Add redirect': return Drupal.t('Add personalized redirect')
        case 'Default redirect': return Drupal.t('Default redirect')
        case 'type a node title or ID or paste a URL': return Drupal.t('type a node title or ID or paste a URL')
        case 'Type to search nodes': return Drupal.t('Type to search nodes')
        case 'destination error': return Drupal.t('Please enter a valid URL or choose a node.')
        case 'service unavailable title': return Drupal.t('Service unavailable')
        case 'service unavailable': return Drupal.t('The service is temporarily unavailable.\rYour redirects could not be saved.\rPlease try again or contact support if the issue persists.')
        case 'unsaved changes title': return Drupal.t('Unsaved changes')
        case 'unsaved changes': return Drupal.t('You have unsaved changes!\rYou will lose your changes if you go back.')
        case 'OK': return Drupal.t('OK')
        case 'Cancel': return Drupal.t('Cancel')
        case 'Go back anyway': return Drupal.t('Go back anyway')
        case 'Stay on page': return Drupal.t('Stay on page')
      }
    }
  }
}
</script>

<style>
</style>
