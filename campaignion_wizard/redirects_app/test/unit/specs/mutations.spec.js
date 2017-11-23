import mutations from '../../../src/store/mutations'

describe('mutations', () => {
  it('INCREMENT', () => {
    // mock state
    const state = { count: 0 }
    // apply mutation
    mutations.increment(state, {amount: 5})
    // assert result
    expect(state.count).to.equal(5)
  })
})
