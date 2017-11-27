<template lang="html">
  <el-dialog
    :title="text('Choose your dataset')"
    :visible="showSelectDialog"
    :close-on-click-modal="false"
    size="medium"
    :before-close="dialogCancelHandler"
    >

    <template slot="title">
      <span class="el-dialog__title">{{ text('Choose your dataset') }}</span>
      <button type="button">{{ text('Add new dataset') }}</button>
    </template>

    <p class="dsa-hint">{{ text('hint') }}</p>

    <DatasetList />

  </el-dialog>
</template>

<script>
import {mapState} from 'vuex'
import DatasetList from '@/components/DatasetList'

export default {
  components: {
    DatasetList
  },

  data () {
    return {
    }
  },

  computed: {
    ...mapState([
      'showSelectDialog'
    ])
  },

  methods: {
    dialogCancelHandler (done) {
      this.$store.commit('closeSelectDialog')
      done()
    },

    text (text) {
      switch (text) {
        case 'Choose your dataset': return Drupal.t('Choose your dataset')
        case 'Add new dataset': return Drupal.t('Add new dataset')
        case 'hint': return Drupal.t('Click on the dataset you would like to choose for this action')
      }
    }
  }
}
</script>

<style lang="css">
</style>
