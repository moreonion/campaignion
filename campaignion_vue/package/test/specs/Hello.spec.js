import assert from 'assert'

import Hello from '../../src/components/Hello.vue'

describe('Hello.vue', function () {
  it('provides data', function () {
    const result = Hello.data()
    assert.deepEqual(result, {text: 'hello'})
  })
})
