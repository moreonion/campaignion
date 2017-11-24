<template lang="html">
  <div>{{ text('Click Edit to see the filters.') }}</div>
</template>

<script>
export default {
  props: {
    redirect: Object,
    index: Number
  },
  methods: {
    text (text) {
      switch (text) {
        case 'Click Edit to see the filters.': return Drupal.t('Click Edit to see the filters.')
      }
    }
  }
}
</script>

<style lang="css">
</style>
