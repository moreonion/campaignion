import Vue from 'vue'
import {clone} from '@/utils'

export default {
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
