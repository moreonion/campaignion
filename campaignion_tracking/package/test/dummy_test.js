/* global suite test setup teardown suiteSetup suiteTeardown */

import { strict as assert } from 'assert'

suite('The truth', () => {
  test('is valid', () => {
    let theTruth = 1
    assert(theTruth === 1)
  })
})
