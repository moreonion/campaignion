<template lang="html">
  <ul class="specs">
    <li v-for="(spec, $index) in specs" class="spec row">
      <div class="col-md-9 col-lg-8 col-xl-6">
        <div class="card">
          <span dragula-handle></span>
          <div class="spec-info">
            <div class="spec-label">
              <template v-if="spec.label">{{ spec.label }}</template>
              <template v-else>{{ filterStrPrefix(spec.type) }} all {{ $index !== 0 ? 'remaining ' : null}}targets where <span v-html="spec.filterStr"></span></template>
            </div>
            <div v-if="spec.label" class="spec-description">{{ filterStrPrefix(spec.type) }} all {{ $index !== 0 ? 'remaining ' : null}}targets where <span v-html="spec.filterStr"></span></div>
          </div>
          <dropdown class="spec-actions">
            <button type="button" class="btn" @click="editSpec($index)">Edit</button>
            <button type="button" class="btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <span class="sr-only">Toggle Dropdown</span>
            </button>
            <div name="dropdown-menu" class="dropdown-menu">
              <a class="dropdown-item" href="#" @click="duplicateSpec($index)">Duplicate</a>
              <a class="dropdown-item" href="#" @click="removeSpec(spec)">Delete</a>
            </div>
          </dropdown>
        </div>
      </div>
      <div class="spec-notice col-md-3 col-lg-4 col-xl-6">
        <ul class="spec-errors">
          <li v-for="error in spec.errors" class="spec-error">{{ error.message }}</li>
        </ul>
      </div>
    </li>
  </ul>
</template>

<script>
export default {
  components: {

  },
  computed: {
    specs () {
      return this.$store.state.specs
    }
  },
  methods: {
    editSpec (index) {

    },
    duplicateSpec (index) {

    },
    removeSpec (spec) {

    },
    filterStrPrefix (type) {
      switch (type) {
        case 'message-template': return 'Send this mail to'
        case 'exclusion': return 'Exclude'
      }
    }
  }
}
</script>

<style lang="css">
</style>
