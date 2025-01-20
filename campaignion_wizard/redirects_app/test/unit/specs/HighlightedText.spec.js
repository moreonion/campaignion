import sinon from 'sinon'
import { render } from '@testing-library/vue'

import HighlightedText from '../../../src/components/HighlightedText.vue'

afterEach(() => {
  sinon.restore()
})

describe('HighlightedText', function () {
  describe('template', function () {
    it('renders an empty span with empty input.', function () {
      const wrapper = render(HighlightedText)
      assert.equal(wrapper.html(), '<span></span>')
    })
    it('renders the text unchanged without a search term.', function () {
      const wrapper = render(HighlightedText, { propsData: { text: 'foo' } })
      assert.equal(wrapper.html(), '<span>foo</span>')
    })
    it('highlights the search term.', function () {
      const wrapper = render(HighlightedText, {
        propsData: {
          text: 'Hello world!',
          search: 'o'
        }
      })
      assert.equal(wrapper.html(), '<span>Hell<strong>o</strong> w<strong>o</strong>rld!</span>')
    })
  })
})
