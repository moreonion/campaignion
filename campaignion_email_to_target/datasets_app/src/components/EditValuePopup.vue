<template lang="html">
  <div v-if="editValue" :class="{
    'dsa-edit-value-popup': true,
    'dsa-has-error': showError && !valid
  }">
    <input type="text" v-model="value" @keydown.enter.stop="save" @keydown.esc.stop="cancel" ref="input" />
    <button type="button" @click="save">{{ text('save') }}</button>
    <button type="button" @click="cancel">{{ text('cancel') }}</button>
    <div v-if="showError && !valid" class="dsa-edit-value-error">
      {{ text('Please enter a valid value.') }}
    </div>
  </div>
</template>

<script>
import {mapState} from 'vuex'
import Popper from 'popper.js/dist/umd/popper'
import {dispatch} from '@/utils'

var popper = {}

export default {
  data: function () {
    return {
      value: '',
      validator: new RegExp(''),
      showError: false,
      popper: {},
      clickHandler: e => {
        // close the editing tooltip if the user clicks somewhere else
        // do nothing if no value is being edited
        if (!this.editValue) return
        // do nothing if the user clicked inside the tooltip
        if (this.$el.tagName && this.$el.contains(e.target)) return
        // do nothing if user clicked on the cell that’s being edited
        if (e.target.classList.contains('dsa-edited')) return
        this.highlightCell(false)
        if (this.changed) this.flashCell()
        this.$store.commit({ type: 'leaveValue' })
      }
    }
  },

  computed: {
    changed () {
      return !!this.editValue && (this.value !== this.editValue.row[this.editValue.col])
    },
    valid () {
      return this.validator.test(this.value)
    },
    ...mapState([
      'editValue',
      'validations'
    ])
  },

  watch: {
    editValue (val) {
      if (val) {
        // initialize
        this.showError = false
        this.validator = new RegExp(this.validations[val.col])
        this.value = val.row[val.col]
        this.highlightCell(true)
        this.$nextTick(() => {
          popper = new Popper(val.el, this.$el, {placement: 'top'})
          this.$refs.input.focus()
        })
      } else {
        popper.destroy()
      }
    }
  },

  mounted () {
    document.addEventListener('click', this.clickHandler)
  },

  beforeDestroy () {
    document.removeEventListener('click', this.clickHandler)
  },

  methods: {
    save () {
      if (this.valid) {
        const nextCell = this.editValue.el.nextSibling
        this.highlightCell(false)
        this.$store.commit({
          type: 'updateValue',
          value: this.value
        })
        this.$nextTick(() => {
          // if the next field is blank, edit it
          if (nextCell && !nextCell.textContent && !(nextCell.children[0] && nextCell.children[0].classList.contains('dsa-delete-contact'))) {
            nextCell.classList.add('dsa-edited') // for click handler
            dispatch(nextCell, 'click')
          }
        })
      } else {
        this.showError = true
      }
    },

    cancel () {
      this.highlightCell(false)
      this.$store.commit({ type: 'leaveValue' })
    },

    highlightCell (highlight) {
      if (highlight) {
        this.editValue.el.classList.add('dsa-edited')
      } else {
        this.editValue.el.classList.remove('dsa-edited')
      }
    },

    flashCell () {
      const el = this.editValue.el
      el.classList.add('dsa-flash')
      setTimeout(function () {
        el.classList.remove('dsa-flash')
      }, 1000)
    },

    text (text) {
      switch (text) {
        case 'Please enter a valid value.': return Drupal.t('Please enter a valid value.')
        case 'save': return Drupal.t('Save')
        case 'cancel': return Drupal.t('Cancel')
      }
    }
  }
}
</script>

<style lang="css">
</style>
