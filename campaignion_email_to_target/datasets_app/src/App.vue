<template>
  <div id="app">
    <template v-if="livingInWizard">
      <div class="dsa-intro-text" v-html="introText"></div>
      <button type="button" @click="openDialog" :disabled="apiError || showSpinner">{{ buttonText }}</button>
    </template>
    <div v-if="apiError" class="dsa-has-error">{{ text('api error') }}</div>

    <SelectDatasetDialog />
    <EditDatasetDialog />

    <p>And this is the token: "{{ token }}"</p>
  </div>
</template>

<script>
import {mapState} from 'vuex'
import SelectDatasetDialog from '@/components/SelectDatasetDialog'
import EditDatasetDialog from '@/components/EditDatasetDialog'
import {clone} from '@/utils'

export default {
  name: 'app',

  components: {
    SelectDatasetDialog,
    EditDatasetDialog
  },

  data: function () {
    return {
      livingInWizard: !!this.$root.$options.datasetField
    }
  },

  computed: {
    introText () {
      return this.selectedDataset
        ? Drupal.t('You have chosen the dataset <strong>"@dataset".</strong> If you would like to edit the dataset or choose a different one click the "edit" button.', {'@dataset': this.selectedDataset.title})
        : Drupal.t('Click the button to choose a dataset.')
    },
    buttonText () {
      return this.selectedDataset
        ? Drupal.t('Edit your target dataset')
        : Drupal.t('Choose your target dataset')
    },
    token () {
      // TODO remove, also from template
      return this.$root.$options.settings.endpoints['e2t-api'].token
    },
    ...mapState([
      'selectedDataset',
      'apiError',
      'showSelectDialog',
      'showEditDialog',
      'showSpinner'
    ])
  },

  watch: {
    selectedDataset (dataset) {
      if (this.livingInWizard && dataset) {
        this.$root.$options.datasetField.value = dataset.key
      }
    },
    // don’t accidentially submit drupal form while dialogs are open
    showSelectDialog (val) {
      this.disableDrupalSubmits(val)
    },
    showEditDialog (val) {
      this.disableDrupalSubmits(val)
    }
  },

  methods: {
    openDialog () {
      if (this.selectedDataset && this.selectedDataset.is_custom) {
        this.$store.dispatch({type: 'loadContacts', dataset: this.selectedDataset})
      } else {
        this.$store.commit('openSelectDialog')
      }
    },

    disableDrupalSubmits (bool) {
      const inputs = document.querySelectorAll('input[type=submit]')
      for (var i = 0, j = inputs.length; i < j; i++) {
        inputs[i].disabled = bool
      }
    },

    text (text) {
      switch (text) {
        case 'api error': return Drupal.t('The email to target API couldn’t be reached. Please reload the page.')
      }
    }
  },

  created () {
    this.$store.commit({
      type: 'init',
      settings: clone(this.$root.$options.settings)
    })
    this.$store.dispatch({
      type: 'loadDatasets',
      selected: this.livingInWizard ? this.$root.$options.datasetField.value : undefined
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
