// The Vue build version to load with the `import` command (runtime-only or
// standalone) has been set in webpack.dev.conf and webpack.test.conf with an alias.
import Vue from 'vue'
import App from './App.vue'
import axios from 'axios'
import store from './store'

import {
  Button,
  Dialog,
  Dropdown,
  DropdownItem,
  DropdownMenu,
  MessageBox,
  Option,
  Select
} from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css'
import locale from 'element-ui/lib/locale'

// Set language for element-ui.
try {
  locale.use(Drupal.settings.campaignion_vue.element_ui_strings)
} catch (e) {
  console.error('Could not load strings from Drupal.settings.campaignion_vue.element_ui_strings')
}

// Create a central event bus.
const bus = new Vue()
Vue.prototype.$bus = bus

// Register element-ui components.
Vue.use(Button)
Vue.use(Dialog)
Vue.use(Dropdown)
Vue.use(DropdownItem)
Vue.use(DropdownMenu)
Vue.use(Option)
Vue.use(Select)

Vue.prototype.$http = axios
Vue.prototype.$msgbox = MessageBox
Vue.prototype.$alert = MessageBox.alert
Vue.prototype.$confirm = MessageBox.confirm
Vue.prototype.$prompt = MessageBox.prompt

Vue.config.productionTip = false

/* eslint-disable no-new */
new Vue({
  el: '.email-to-target-messages-widget',
  render: (h) => h(App),
  store,
  components: { App }
})
