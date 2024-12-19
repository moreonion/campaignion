<docs>
HighlightedText component.
Puts matching parts of a text in <strong> tags to highlight them.
</docs>

<template>
 <span>
 <template v-for="part in getParts(text, search)">
  <strong v-if="part.highlight">{{ part.text }}</strong>
  <template v-else>{{ part.text }}</template>
 </template>
 </span>
</template>

<script>
import { escapeRegExp } from '../utils'

export default {
  props: {
    text: { default: null },
    search: String
  },
  methods: {
    getParts (text, search) {
      if (!text || !search) {
        return [{text, highlight: false}]
      }
      const escapedSearch = escapeRegExp(search)
      const matchRegexp = new RegExp('^' + escapedSearch + '$', 'i')
      return text.split(new RegExp('(?=' + escapedSearch + ')|(?<=' + escapedSearch + ')', 'gi')).map((part) => ({
        text: part,
        highlight: matchRegexp.test(part)
      }))
    },
  },
}
</script>
