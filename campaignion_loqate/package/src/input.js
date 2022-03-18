/* globals: Drupal */

/**
 * Handler for a single input element and it’s wrapper.
 */
class Input {
  constructor ($input, msg, dependent = []) {
    this.$input = $input
    this.$wrapper = $input.closest('.form-group')
    this.dependent = dependent
    this.formId = $input.closest('form').attr('id')
    this.errorMessage = msg

    this.valid = false
    this.lastValue = ''
    this.validated = false
  }
  bind () {
    this.$input.on('change keyup', () => {
      this.unmarkIfChanged()
    })
    return this
  }
  unmark () {
    const validator = Drupal.myClientsideValidation.validators[this.formId]
    const $wrapper = $('#clientsidevalidation-' + this.formId + '-errors')
    const errors = validator.errorsFor(this.$input.get(0))
    validator.addWrapper(errors).remove()
    // Hide container if it’s empty.
    if ($wrapper.length && !$wrapper.find(validator.settings.errorElement).length) {
      $wrapper.hide()
    }
    this.validated = false
  }
  markValid () {
    this.unmark()
  }
  markInvalid () {
    const validator = Drupal.myClientsideValidation.validators[this.formId]
    const errors = {}
    errors[this.$input.attr('name')] = this.errorMessage
    // Needed so jQuery validate will find the element when removing errors.
    validator.currentElements.push(this.$input.get(0))
    // Trigger validation error.
    validator.showErrors(errors)
  }
  setValid (valid) {
    this.valid = valid
    this.lastValue = this.value()
    this.validated = true
  }
  mark () {
    if (this.validated) {
      this.valid ? this.markValid() : this.markInvalid()
    }
  }
  hasChanged () {
    return this.value() != this.lastValue
  }
  unmarkIfChanged () {
    if (this.hasChanged()) {
      this.unmark()
      this.dependent.forEach(function (other) {
        other.unmark()
      })
    }
    else {
      this.mark()
    }
  }
  value () {
    return this.$input.val()
  }
}

export { Input }
