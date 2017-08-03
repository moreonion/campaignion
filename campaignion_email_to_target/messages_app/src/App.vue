<template>
  <div id="app">
    <el-button @click="newSpec('message-template')">Create message</el-button>
    <el-button @click="newSpec('exclusion')">Create exclusion</el-button>
    <spec-list></spec-list>
    <spec-dialog></spec-dialog>
  </div>
</template>

<script>
import SpecDialog from './components/SpecDialog'
import SpecList from './components/SpecList'

export default {
  name: 'app',
  components: {
    SpecDialog,
    SpecList
  },

  methods: {
    newSpec (type) {
      this.$bus.$emit('newSpec', type)
    }
  },

  created () {
    /* global Drupal */
    console.log(Drupal.settings.campaignion_email_to_target)
    this.$store.commit('initializeData', Drupal.settings.campaignion_email_to_target)
    this.$store.commit('validateSpecs')
  }
}
</script>

<style>
</style>
