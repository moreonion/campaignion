<template>
  <div id="app">
    <img src="./assets/logo.png">
    <hello></hello>
    <p>And this is the token: "{{ token }}"</p>

    <v-client-table v-if="contacts.length" :data="contacts" :columns="columns" :options="options" name="contactsTable" class="dsa-contacts-table"/>
    <EditValuePopup />

    <button @click="loadDatasets" type="button" name="button">Load datasets</button>
    <button @click="generateContacts" type="button" name="button">Generate contacts</button>

  </div>
</template>

<script>
import {mapState} from 'vuex'
import Hello from './components/Hello'
import EditValuePopup from './components/EditValuePopup'
import api from '@/utils/api'
import {clone} from '@/utils'

export default {
  name: 'app',
  components: {
    Hello,
    EditValuePopup
  },
  data: function () {
    return {
      livingInWizard: !!this.$root.$options.datasetField,
      columns: ['email', 'first_name', 'last_name', 'salutation'],
      options: {}
    }
  },
  computed: {
    token () {
      return this.$root.$options.settings.endpoints['e2t-api'].token
    },
    ...mapState([
      'contacts'
    ])
  },
  methods: {
    loadDatasets () {
      api.getDatasets().then((data) => {
        console.log(data)
      })
    },
    generateContacts () {
      this.$store.commit({
        type: 'generateContacts'
      })
    }
  },
  created () {
    this.$store.commit({
      type: 'init',
      settings: clone(this.$root.$options.settings)
    })
  }
}
</script>

<style>
#app {
  font-family: 'Avenir', Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin-top: 60px;
}

li.VuePagination__pagination-item {
    display: inline-block;
    list-style-type: none;
    margin-right: 1rem;
}

td {
    padding: 0 0.7rem;
    white-space: nowrap;
}
td.dsa-edited {
  background-color: yellow;
}

.dsa-flash {
  -webkit-animation-name: flash-animation;
  -webkit-animation-duration: 1s;
  animation-name: flash-animation;
  animation-duration: 1s;
}

@-webkit-keyframes flash-animation {
  0% { background: transparent; }
  10% { background: red; }
  100% { background: transparent; }
}

@keyframes flash-animation {
  0% { background: transparent; }
  10% { background: red; }
  100% { background: transparent; }
}
</style>
