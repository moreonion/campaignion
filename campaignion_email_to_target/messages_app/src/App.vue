<template>
  <div id="app">
    <spec-list></spec-list>
    <img src="./assets/logo.png">
    <hello></hello>
  </div>
</template>

<script>
import Hello from './components/Hello'
import SpecList from './components/SpecList'

export default {
  name: 'app',
  components: {
    Hello,
    SpecList
  },

  created () {
    /* global Drupal */
    console.log(Drupal.settings.campaignion_email_to_target)
    this.$store.commit('initializeData', Drupal.settings.campaignion_email_to_target)
    this.$store.commit('validateSpecs')
    this.$store.commit('updateFilterStrings')
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
</style>
