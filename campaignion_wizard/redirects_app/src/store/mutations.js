import Vue from 'vue'
import {clone} from '@/utils'

export default {
  initData (state, {redirects, defaultRedirectUrl}) {
    if (defaultRedirectUrl && (typeof redirects === 'undefined' || !redirects.length)) {
      state.defaultRedirect.destination = defaultRedirectUrl
      state.defaultRedirect.prettyDestination = defaultRedirectUrl
    } else {
      state.defaultRedirect = clone(redirects[redirects.length - 1])
      state.redirects = clone(redirects).slice(0, -1)
    }

    // Preserve initial state
    state.initialData.redirects = clone(state.redirects)
    state.initialData.defaultRedirect = clone(state.defaultRedirect)
  },

  editNewRedirect (state) {
    state.currentRedirectIndex = -1
  },

  editRedirect (state, {index}) {
    state.currentRedirectIndex = index
  },

  removeRedirect (state, {index}) {
    state.redirects.splice(index, 1)
  },

  leaveRedirect (state) {
    state.currentRedirectIndex = null
  },

  updateRedirect (state, {redirect}) {
    if (state.currentRedirectIndex === null) return
    if (state.currentRedirectIndex === -1) {
      state.redirects.push(redirect)
    } else {
      Vue.set(state.redirects, state.currentRedirectIndex, clone(redirect))
    }
  },

  updateDefaultRedirect (state, {destination, prettyDestination}) {
    state.defaultRedirect.destination = destination
    state.defaultRedirect.prettyDestination = prettyDestination
  },

  updateRedirects (state, {redirects}) {
    state.redirects = redirects
  }
}
