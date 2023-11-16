import Vue from 'vue'
import App from './App.vue'
import {createStore} from './store'

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
} catch {
  console.error('Could not load strings from Drupal.settings.campaignion_vue.element_ui_strings')
}

// Register element-ui components.
Vue.use(Button)
Vue.use(Dialog)
Vue.use(Dropdown)
Vue.use(DropdownItem)
Vue.use(DropdownMenu)
Vue.use(Option)
Vue.use(Select)

Vue.prototype.$msgbox = MessageBox
Vue.prototype.$alert = MessageBox.alert
Vue.prototype.$confirm = MessageBox.confirm
Vue.prototype.$prompt = MessageBox.prompt

Vue.config.productionTip = false

const containers = document.querySelectorAll('.personalized-redirects-widget')
containers.forEach(drupalContainer => {
  // Don’t replace the container with the app, because Drupal
  // conditional fields are in control of the container.
  // To check if the app is visible or not, use the drupalContainer option.
  const el = document.createElement('div')
  drupalContainer.appendChild(el)
  /* eslint-disable no-new */
  new Vue({
    el,
    drupalContainer,
    settings: {},
    render: (h) => h(App),
    store: createStore(),
    components: {App}
  })
})
