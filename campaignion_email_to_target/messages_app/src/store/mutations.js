import Vue from 'vue'
import {clone, isEmptyMessage} from '@/utils'
import {emptySpec, messageObj} from '@/utils/defaults'
import {find} from 'lodash'

export default {
  initializeData (state, {messageSelection, targetAttributes, tokens, hardValidation}) {
    if (messageSelection && messageSelection.length) {
      // The default message is the last message in the messageSelection array and has no filters
      if (messageSelection[messageSelection.length - 1].filters.length === 0) {
        state.defaultMessage = Object.assign({}, messageSelection[messageSelection.length - 1])
        state.specs = clone(messageSelection).slice(0, -1)
      } else {
        state.defaultMessage = emptySpec('message-template')
        state.specs = clone(messageSelection)
      }
    } else {
      state.defaultMessage = emptySpec('message-template')
    }

    if (targetAttributes) state.targetAttributes = clone(targetAttributes)
    if (tokens) state.tokenCategories = clone(tokens)
    if (typeof hardValidation !== 'undefined') state.hardValidation = hardValidation

    for (let i = 0, j = state.specs.length; i < j; i++) {
      // add attributeLabel property to filters
      for (let ii = 0, jj = state.specs[i].filters.length; ii < jj; ii++) {
        var targetAttribute = find(targetAttributes, {name: state.specs[i].filters[ii].attributeName})
        state.specs[i].filters[ii].attributeLabel = targetAttribute && targetAttribute.label || state.specs[i].filters[ii].attributeName
      }
      // add empty message to exclusions saved without a message in version 1
      if (state.specs[i].type === 'exclusion' && typeof state.specs[i].message === 'undefined') {
        Vue.set(state.specs[i], 'message', messageObj())
      }
    }

    // preserve initial state
    state.initialData.specs = clone(state.specs)
    state.initialData.defaultMessage = clone(state.defaultMessage)
  },

  validateSpecs (state) {
    var errors
    var usedFilterSets = []

    function stringify (filter) {
      // Stringify filters for easier comparison.
      return [filter.type, filter.attributeName, filter.operator, filter.value].join('|')
    }

    for (let i = 0, j = state.specs.length; i < j; i++) {
      errors = []
      let thisSpec = state.specs[i]
      let thisSpecsFilters = thisSpec.filters

      if (!thisSpecsFilters.length) {
        errors.push({type: 'filter', message: Drupal.t('No filter selected')})
      } else {
        // Cycle through filters
        for (let ii = 0, jj = thisSpecsFilters.length; ii < jj; ii++) {
          if (!thisSpecsFilters[ii].value) {
            errors.push({type: 'filter', message: Drupal.t('A filter value is missing')})
            break
          }
        }
      }

      if (thisSpec.type === 'message-template' && isEmptyMessage(thisSpec.message)) {
        errors.push({type: 'message', message: Drupal.t('Message is empty')})
      }

      // Check this spec’s filters against the sets of filters used by preceding specs.
      // Skip this step for specs with other filter errors.
      if (!find(errors, {type: 'filter'})) {
        for (let ii = 0, jj = usedFilterSets.length; ii < jj; ii++) {
          let usedFilterSet = usedFilterSets[ii]
          if (usedFilterSet.length !== thisSpecsFilters.length) {
            continue
          }
          let found = 0
          for (let iii = 0, jjj = thisSpecsFilters.length; iii < jjj; iii++) {
            if (usedFilterSet.indexOf(stringify(thisSpecsFilters[iii])) !== -1) {
              found++
            }
          }
          if (found === thisSpec.filters.length) {
            switch (thisSpec.type) {
              case 'message-template':
                errors.push({type: 'filter', message: Drupal.t('This message won’t be sent. The same filter has been applied above.')})
                break
              case 'exclusion':
                errors.push({type: 'filter', message: Drupal.t('This exclusion won’t be taken into account. The same filter has been applied above.')})
                break
            }
            break
          }
        }
      }

      // Remember the filters used by this spec
      let filters = []
      for (let ii = 0, jj = thisSpecsFilters.length; ii < jj; ii++) {
        filters.push(stringify(thisSpecsFilters[ii]))
      }
      usedFilterSets.push(filters)

      Vue.set(thisSpec, 'errors', errors)
    }
  },

  editNewSpec (state) {
    state.currentSpecIndex = -1
  },

  editSpec (state, {index}) {
    state.currentSpecIndex = index
  },

  removeSpec (state, {index}) {
    state.specs.splice(index, 1)
  },

  leaveSpec (state) {
    state.currentSpecIndex = null
  },

  updateSpec (state, {spec}) {
    if (state.currentSpecIndex === null) return
    if (state.currentSpecIndex === -1) {
      state.specs.push(spec)
    } else {
      Vue.set(state.specs, state.currentSpecIndex, clone(spec))
    }
  },

  updateSpecs (state, {specs}) {
    state.specs = specs
  },

  updateDefaultMessage (state, {message}) {
    Vue.set(state.defaultMessage, 'message', message)
  }
}
