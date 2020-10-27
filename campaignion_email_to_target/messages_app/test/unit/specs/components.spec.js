import mutations from '@/store/mutations'
import initialState from '@/store/state'
import testData from '../fixtures/example-data'
import Vue from 'vue'
import { shallowMount } from '@vue/test-utils'
import SpecDialog from '@/components/SpecDialog'

describe('components', function () {
  var state

  beforeEach(function () {
    state = Object.assign({}, initialState)
    mutations.initializeData(state, testData)
  })

  describe('SpecDialog', () => {
    Vue.config.ignoredElements = [
      'el-dialog', 'el-button', 'el-dropdown', 'el-dropdown-menu', 'el-dropdown-item'
    ]
    const bus = new Vue()

    it('duplicates a spec', () => {
      const wrapper = shallowMount(SpecDialog, {
        mocks: {
          $store: {
            state: state,
            commit: function () {}
          },
          $bus: bus
        }
      })
      var s = state.specs[0]
      bus.$emit('duplicateSpec', 0)

      expect(wrapper.vm.currentSpec.label).to.equal(`Copy of ${s.label}`)
      expect(wrapper.vm.currentSpec.type).to.equal(s.type)
      expect(wrapper.vm.currentSpec.message).to.deep.equal(s.message)
      expect(wrapper.vm.currentSpec.id, 'id should be resetted').to.be.null
      for (let filter of wrapper.vm.currentSpec.filters) {
        expect(filter.id, 'filter ids should be resetted').to.be.null
      }
    })
  })
})
