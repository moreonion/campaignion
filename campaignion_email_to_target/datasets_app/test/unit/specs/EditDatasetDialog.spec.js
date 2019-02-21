import assert from 'assert'

import EditDatasetDialog from '@/components/EditDatasetDialog'

describe('EditDatasetDialog', function () {
  describe('computed', function () {
    describe('contentColumns', function () {
      const contentColumns = EditDatasetDialog.computed.contentColumns.bind({
        tableColumns: ['__error', 'a__strange_field_name', 'first_name', 'last_name', '__foo', 'bar__', '__delete']
      })
      it('returns an array of all column keys not starting with a double underscore.', function () {
        assert.deepEqual(contentColumns(), ['a__strange_field_name', 'first_name', 'last_name', 'bar__'])
      })
    })
  })

  describe('methods', function () {
    describe('isValidValue', function () {
      const isValidValue = EditDatasetDialog.methods.isValidValue.bind({
        validations: {first_name: 'Eva', last_name: '\\S+'},
        maxFieldLengths: {last_name: 3, email: 5}
      })
      it('returns true if the regex matches.', function () {
        assert.equal(isValidValue('first_name', 'Eva...'), true)
      })
      it('returns true if the length is fine.', function () {
        assert.equal(isValidValue('email', '12345'), true)
      })
      it('returns true if the regex matches and the length is fine.', function () {
        assert.equal(isValidValue('last_name', 'Lee'), true)
      })
      it('returns false if the regex test fails.', function () {
        assert.equal(isValidValue('first_name', 'Heidi'), false)
      })
      it('returns false if the value is too long.', function () {
        assert.equal(isValidValue('email', 'heidi@hotmail.com'), false)
      })
      it('returns true if there is no test for this column.', function () {
        assert.equal(isValidValue('title', 'Sir'), true)
      })
    })
  })
})
