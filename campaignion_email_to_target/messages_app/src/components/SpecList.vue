<template lang="html">
  <draggable
    v-model="specs"
    element="ul"
    class="specs"
    :options="{ handle: '.spec-handle', forceFallback: true }"
    @start="dragStart"
    @end="dragEnd"
    >
    <li v-for="(spec, index) in specs" :key="index" class="spec">
      <div class="spec-card">
        <span class="spec-handle"></span>
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
    },
    duplicateSpec (index) {
      this.$bus.$emit('duplicateSpec', index)
    },
    removeSpec (index) {
      const title = this.specs[index].type === 'message-template' ? Drupal.t('Remove message') : Drupal.t('Remove exclusion')
      const question = this.specs[index].label
        ? Drupal.t('Do you really want to remove "@itemName"?', {'@itemName': this.specs[index].label})
        : Drupal.t('Do you really want to remove this item?')
      this.$confirm(question, title, {
        confirmButtonText: 'Remove',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }).then(() => {
        this.$store.commit({type: 'removeSpec', index})
      }, () => {})
    },
    dragStart () {
      document.body.classList.add('dragging')
    },
    dragEnd () {
      document.body.classList.remove('dragging')
    }
  }
}
</script>

<style lang="scss">
.e2tmw {

  ul.specs {
    margin: 1rem 0;
  }

  li.spec {
    margin: 0.75rem 0;
  }

  .spec-card {
    display: inline-block;
    width: 60%;
    padding: 1rem;
    border: 1px solid #aaa;
    background-color: #fff;
  }

  .spec-handle {
    display: inline-block;
    vertical-align: middle;
    width: 0.5rem;
    height: 1.3rem;
    margin-right: 0.5rem;
    border-left: 0.2rem solid #aaa;
    border-right: 0.2rem solid #aaa;
    cursor: move;
  }

  .spec-info {
    display: inline-block;
    vertical-align: middle;
    width: calc(100% - 10rem);
    padding-right: 2rem;
  }

  .spec-label {
    font-weight: bold;
  }

  .spec-actions {
    display: inline-block;
    vertical-align: middle;
  }

  .spec-errors {
    display: inline-block;
    width: calc(40% - 4rem);
    margin-left: 1rem;
    padding-left: 0;
    list-style: none;
    color: red;
  }

  .sortable-ghost {
    .spec-card {
      background-color: #eee;
    }

    *:not(.spec-card) {
      visibility: hidden;
    }
  }

  .sortable-drag .spec-errors {
    visibility: hidden;
  }
}

body.dragging, body.dragging * {
  cursor: move;
}
</style>
