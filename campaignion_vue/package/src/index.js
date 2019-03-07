import 'es6-promise/dist/es6-promise.auto.js';

// Add these modules to 'externals' in your app’s webpack.prod.conf.js
import Vue from 'vue';
import Vuex from 'vuex';
import axios from 'axios';
import {
  Button,
  Dialog,
  Dropdown,
  DropdownItem,
  DropdownMenu,
  MessageBox,
  Loading,
  Option,
  Radio,
  RadioGroup,
  Select
} from 'element-ui';
import elementLocale from 'element-ui/lib/locale';
import draggable from 'vuedraggable';

export default {
  Vue,
  Vuex,
  axios,
  element: {
    Button,
    Dialog,
    Dropdown,
    DropdownItem,
    DropdownMenu,
    MessageBox,
    Loading,
    Option,
    Radio,
    RadioGroup,
    Select
  },
  elementLocale,
  draggable
};
