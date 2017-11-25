<template lang="html">
  <ul class="pra-redirect-info-filters">
    <li v-for="filter in redirect.filters">
      <template v-if="filter.type === 'opt-in'">{{ filter.value ? text('User has opted in') : text('User has not opted in') }}</template>
      <template v-else>{{ filterDescription(filter) }}</template>
    </li>
  </ul>
</template>

<script>
import {OPERATORS} from '@/utils/defaults'
import {find} from 'lodash'

export default {
  props: {
    redirect: Object,
    index: Number
  },
  methods: {
    text (text) {
      switch (text) {
        case 'User has opted in': return Drupal.t('Supporter has opted in')
        case 'User has not opted in': return Drupal.t('Supporter hasn’t opted in')
      }
    },
    filterDescription (filter) {
      const fieldLabel = find(this.$root.$options.settings.fields, {id: filter.field}).label
      return Drupal.t(OPERATORS[filter.operator].phrase, {'@attribute': fieldLabel, '@value': filter.value})
    }
  }
}
</script>

<style lang="css">
</style>
