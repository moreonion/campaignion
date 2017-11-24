<template>
<div class="redirect-app" data-interrupt-submit>
  <ElButton @click="newRedirect()">{{ text('Add redirect') }}</ElButton>
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
      url="http://foo.bar.com"
      :headers="{'Authorization': 'JWT foo.bar.3456ß8971230469827456.jklcnfgb'}"
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
import {dispatch, validateDestination} from '@/utils'
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
    ...mapState([
      'defaultRedirect'
    ])
  },

  mounted () {
    // Handle events from interrupt-submit.js
    const listener = e => {
      if (e.type === 'request-leave-page') {
        // TODO User wants to go back - ask: lose data?
      } else if (e.type === 'request-submit-page') {
        if (!this.destinationIsValid) {
          dispatch(this.$root.$el, 'cancel-leave-page')
          this.showErrors = true
          return
        }
        // TODO persist data.
        // call dispatch(this.$root.$el, 'resume-leave-page') in callback after server responded ok
        // or cancel-leave-page + warning in case of http error
      }
      this.askingToLeave = true
    }
    this.$root.$el.addEventListener('request-submit-page', listener)
    this.$root.$el.addEventListener('request-leave-page', listener)
  },

  methods: {
    newRedirect () {
      this.$root.$emit('newRedirect')
    },
    text (text) {
      switch (text) {
        case 'Add redirect': return Drupal.t('Add personalized redirect')
        case 'Default redirect': return Drupal.t('Default redirect')
        case 'type a node title or ID or paste a URL': return Drupal.t('type a node title or ID or paste a URL')
        case 'Type to search nodes': return Drupal.t('Type to search nodes')
        case 'destination error': return Drupal.t('Please enter a valid URL or choose a node.')
      }
    }
  }
}
</script>

<style>
#app {
  font-family: 'Avenir', Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin-top: 60px;
}
</style>
