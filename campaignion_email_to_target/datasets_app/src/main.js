import Vue from 'vue'
import App from './App.vue'
import store from './store'
import {ClientTable} from 'vue-tables-2'
import {VTooltip} from 'v-tooltip'
import {TOOLTIP_CLASS} from '@/utils'

import {
  Button,
  Dialog,
  Dropdown,
  DropdownItem,
  DropdownMenu,
  MessageBox,
  Loading,
  Option,
  Select
} from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css'
import locale from 'element-ui/lib/locale'

// Set language for element-ui.
try {
  locale.use(Drupal.settings.campaignion_vue.element_ui_strings)
} catch {
  console.error('Could not load strings from Drupal.settings.campaignion_vue.element_ui_strings');
}

// Create a central event bus.
const bus = new Vue()
Vue.prototype.$bus = bus

Vue.use(ClientTable, {}, true)
VTooltip.options.defaultTemplate = `<div class="${TOOLTIP_CLASS}" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>`
VTooltip.options.delay = {show: 200, hide: 300}
Vue.directive('tooltip', VTooltip)

// Register element-ui components.
Vue.use(Button)
Vue.use(Dialog)
Vue.use(Dropdown)
Vue.use(DropdownItem)
Vue.use(DropdownMenu)
Vue.use(Option)
Vue.use(Select)

Vue.use(Loading.directive)

Vue.prototype.$loading = Loading.service
Vue.prototype.$msgbox = MessageBox
Vue.prototype.$alert = MessageBox.alert
Vue.prototype.$confirm = MessageBox.confirm
Vue.prototype.$prompt = MessageBox.prompt

Vue.config.productionTip = false

/* eslint-disable no-new */
new Vue({
  el: '.datasets-app',
  render: (h) => h(App),
  settings: Drupal.settings.campaignion_email_to_target,
  datasetField: document.querySelector('.datasets-app-selected-dataset'),
  store,
  components: { App }
})
