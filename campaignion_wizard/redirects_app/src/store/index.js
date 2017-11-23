import Vue from 'vue'
import Vuex from 'vuex'
import state from './state'
import actions from './actions'
import mutations from './mutations'

Vue.use(Vuex)

const debug = process.env.NODE_ENV !== 'production'

export function createStore () {
  return new Vuex.Store({
    state: Object.assign({}, state),
    actions: Object.assign({}, actions),
    mutations: Object.assign({}, mutations),
    strict: debug
  })
}
