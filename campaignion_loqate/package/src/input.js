/**
 * Handler for a single input element and it’s wrapper.
 */
class Input {
  constructor ($input, dependent = []) {
    this.valid = false
    this.lastValue = ''
    this.validated = false
    this.$input = $input
    this.$wrapper = $input.closest('.form-group')
    this.dependent = dependent
  }
  bind () {
    this.$input.on('change keyup', () => {
      this.unmarkIfChanged()
    })
    return this
  }
  unmark () {
    this.$input.removeClass('error')
    this.$wrapper.removeClass('field-error field-success')
    this.validated = false
  }
  markValid () {
    this.$wrapper.addClass('field-success')
  }
  markInvalid () {
    this.$input.addClass('error')
    this.$wrapper.addClass('field-error')
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
