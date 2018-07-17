import 'es6-promise/dist/es6-promise.auto.js';

// Add these modules to 'externals' in your app’s webpack.prod.conf.js
import Vue from 'vue';
import Vuex from 'vuex';
import axios from 'axios';
import {
  Button,
  Col,
  Collapse,
  CollapseItem,
  Dialog,
  Dropdown,
  DropdownItem,
  DropdownMenu,
  Form,
  FormItem,
  Input,
  MessageBox,
  Option,
  Popover,
  Radio,
  RadioGroup,
  Row,
  Select,
  Switch
} from 'element-ui';
import CollapseTransition from 'element-ui/lib/transitions/collapse-transition';
import elementLocale from 'element-ui/lib/locale';
import draggable from 'vuedraggable';

export default {
  Vue,
  Vuex,
  axios,
  element: {
    Button,
    Col,
    Collapse,
    CollapseItem,
    CollapseTransition,
    Dialog,
    Dropdown,
    DropdownItem,
    DropdownMenu,
    Form,
    FormItem,
    Input,
    MessageBox,
    Option,
    Popover,
    Radio,
    RadioGroup,
    Row,
    Select,
    Switch
  },
  elementLocale,
  draggable
};
