<template lang="html">
  <section class="dsa-dataset-list">
    <input type="text" v-model="filter" :placeholder="text('filter placeholder')">
    <ul class="dsa-datasets">
      <li v-for="dataset in filteredDatasets" :key="dataset.key" @click="select(dataset)">
        {{ dataset.title }}
      </li>
    </ul>
  </section>
</template>

<script>
import {mapState} from 'vuex'

export default {
  data () {
    return {
      filter: ''
    }
  },

  computed: {
    filteredDatasets () {
      return this.datasets.filter(dataset => {
        return dataset.title.toLowerCase().indexOf(this.filter.toLowerCase()) > -1
      })
    },
    ...mapState([
      'datasets'
    ])
  },

  methods: {
    select (dataset) {
      this.$store.commit({
        type: 'selectDataset',
        dataset
      })
    },

    text (text) {
      switch (text) {
        case 'filter placeholder': return Drupal.t('Type to filter the list of datasets')
      }
    }
  }
}
</script>

<style lang="css">
</style>
