<template lang="html">
  <draggable v-model="specs" element="ul" class="specs" :options="{handle: '.drag-handle'}">
    <li v-for="(spec, index) in specs" class="spec row">
      <div class="card">
        <span class="drag-handle"></span>
        <div class="spec-info">
          <div class="spec-label">
            <template v-if="spec.label">{{ spec.label }}</template>
            <spec-description v-else :spec="spec" :index="index"></spec-description>
          </div>
          <spec-description v-if="spec.label" :spec="spec" :index="index" class="spec-description"></spec-description>
        </div>

        <el-dropdown split-button trigger="click" @click="editSpec(index)" class="spec-actions">
          Edit
          <el-dropdown-menu slot="dropdown">
            <el-dropdown-item @click.native="duplicateSpec(index)">Duplicate</el-dropdown-item>
            <el-dropdown-item @click.native="removeSpec(index)">Delete</el-dropdown-item>
          </el-dropdown-menu>
        </el-dropdown>

      </div>
      <ul class="spec-errors">
        <li v-for="error in spec.errors" class="spec-error">{{ error.message }}</li>
      </ul>
    </li>
  </draggable>
</template>

<script>
import Draggable from 'vuedraggable'
import SpecDescription from './SpecDescription'

export default {

  components: {
    SpecDescription,
    Draggable
  },

  computed: {
    specs: {
      get () {
        return this.$store.state.specs
      },
      set (val) {
        this.$store.commit({type: 'updateSpecs', specs: val})
        this.$store.commit('validateSpecs')
      }
    }
  },

  methods: {
    editSpec (index) {
      this.$bus.$emit('editSpec', index)
      // this.$store.commit({type: 'editSpec', index})
    },
    duplicateSpec (index) {
      this.$bus.$emit('duplicateSpec', index)
      // this.$store.commit({type: 'duplicateSpec', index})
    },
    removeSpec (index) {
      this.$confirm(
        this.specs[index].label
          ? Drupal.t('Do you really want to remove "@itemName"?', {'@itemName': this.specs[index].label})
          : Drupal.t('Do you really want to remove this item?'),
        this.specs[index].type === 'message-template' ? Drupal.t('Remove message') : Drupal.t('Remove exclusion'),
        {
          confirmButtonText: 'Remove',
          cancelButtonText: 'Cancel',
          type: 'warning'
        }
      ).then(() => {
        this.$store.commit({type: 'removeSpec', index})
      }, () => {})
    }
  }
}
</script>

<style lang="css">
.drag-handle {
  display: inline-block;
  vertical-align: middle;
  width: 0.5rem;
  height: 1.3rem;
  margin-right: 0.5rem;
  border-left: 0.2rem solid #aaa;
  border-right: 0.2rem solid #aaa;
}
</style>
