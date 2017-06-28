import Vue from 'vue'
import {clone, isEmptyMessage, composeFilterStr} from '@/utils'
import {emptySpec} from '@/utils/defaults'
import {find} from 'lodash'

export default {

  increment (state, payload) {
    state.count += payload.amount
  },

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

    // add attributeLabel property to filters
    for (let i = 0, j = state.specs.length; i < j; i++) {
      for (let ii = 0, jj = state.specs[i].filters.length; ii < jj; ii++) {
        var targetAttribute = find(targetAttributes, {name: state.specs[i].filters[ii].attributeName})
        state.specs[i].filters[ii].attributeLabel = targetAttribute && targetAttribute.label || state.specs[i].filters[ii].attributeName
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
        errors.push({type: 'filter', message: 'No filter selected'})
      } else {
        // Cycle through filters
        for (let ii = 0, jj = thisSpecsFilters.length; ii < jj; ii++) {
          if (!thisSpecsFilters[ii].value) {
            errors.push({type: 'filter', message: 'A filter value is missing'})
            break
          }
        }
      }

      if (thisSpec.type === 'message-template' && isEmptyMessage(thisSpec.message)) {
        errors.push({type: 'message', message: 'Message is empty'})
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
                errors.push({type: 'filter', message: 'This message won’t be sent. The same filter has been applied above.'})
                break
              case 'exclusion':
                errors.push({type: 'filter', message: 'This exclusion won’t be taken into account. The same filter has been applied above.'})
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

  updateFilterStrings (state) {
    for (let i = 0, j = state.specs.length; i < j; i++) {
      Vue.set(state.specs[i], 'filterStr', composeFilterStr(state.specs[i].filters))
    }
  }
}
