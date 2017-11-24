<template>
<div class="redirect-app" data-interrupt-submit>
  <ElButton @click="newRedirect()">{{ text('Add redirect') }}</ElButton>
  <RedirectList/>
  <RedirectDialog/>
</div>
</template>

<script>
import RedirectList from './components/RedirectList'
import RedirectDialog from './components/RedirectDialog'

export default {
  name: 'app',

  components: {
    RedirectList,
    RedirectDialog
  },

  methods: {
    newRedirect () {
      this.$root.$emit('newRedirect')
    },
    text (text) {
      switch (text) {
        case 'Add redirect': return Drupal.t('Add personalized redirect')
      }
    }
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
