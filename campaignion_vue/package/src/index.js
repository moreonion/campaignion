import 'es6-promise/dist/es6-promise.auto.js';

// Add these modules to 'externals' in your app’s webpack.prod.conf.js
import Vue from 'vue';
import Vuex from 'vuex';
import axios from 'axios';
import {
  Dialog,
  Dropdown,
  DropdownItem,
  DropdownMenu,
  MessageBox,
  Option,
  Select
} from 'element-ui';
import elementLocale from 'element-ui/lib/locale';

export default {
  Vue,
  Vuex,
  axios,
  element: {
    Dialog,
    Dropdown,
    DropdownItem,
    DropdownMenu,
    MessageBox,
    Option,
    Select
  },
  elementLocale
};
