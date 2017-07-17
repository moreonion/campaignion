import 'es6-promise/dist/es6-promise.auto.js';
import Vue from 'vue';
import Vuex from 'vuex';
import axios from 'axios';

import {
  Dropdown,
  DropdownItem,
  DropdownMenu,
  Option,
  Select
} from 'element-ui';

export default {
  Vue,
  Vuex,
  axios,
  element: {
    Dropdown,
    DropdownItem,
    DropdownMenu,
    Option,
    Select
  }
};
