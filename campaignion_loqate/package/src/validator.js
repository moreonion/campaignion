/* global Drupal, jQuery */

import { debounce } from '@moreonion/js-utils/src/utils'
import { Input } from './input'

var $ = jQuery

/**
 * Validator for a form containing bank_code and account input elements.
 *
 * The elements are assumed to be inside a fieldset.
 */
class Validator {
  constructor (client, settings) {
    this.client = client
    this.settings = $.extend({}, settings, {
      validateTimeout: 300,
    })
    this.validating = false
    this.disabledButtons = $()
  }
  bind (wrapper) {
    this.inputHolder = (new Input($('[name$="[holder]"]', wrapper))).bind()
    this.inputAccount = (new Input($('[name$="[account]"]', wrapper))).bind()
    this.inputSortCode = (new Input($('[name$="[bank_code]"]', wrapper), [this.inputAccount])).bind()
    let validate = debounce(() => {
      this.validate()
    }, this.settings.validateTimeout)
    $(wrapper).on('change keyup', validate)
    this.$form = $(wrapper).closest('form')
    return this
  }
  start () {
    this.validating = true
    let $buttons = this.$form.find('input[type="submit"], button[type="submit"]')
      .filter(function (index) {
        return !this.disabled && !this.formNoValidate
      })
    $buttons.prop('disabled', true)
    this.disabledButtons = this.disabledButtons.add($buttons)
  }
  stop () {
    this.validating = false
    if (this.inputHolder.valid && this.inputSortCode.valid && this.inputAccount.valid) {
      this.disabledButtons.prop('disabled', false)
      this.disabledButtons = $()
    }
  }
  async validate () {
    if (this.validating) {
      return
    }
    this.start()
    let sortCode = this.inputSortCode.value()
    let account = this.inputAccount.value()
    let sortCodeChanged = this.inputSortCode.hasChanged()
    let item = null
    try {
      if (this.inputHolder.hasChanged()) {
        this.inputHolder.setValid(this.inputHolder.value().length >= 4)
      }
      this.inputHolder.mark()

      if (sortCodeChanged) {
        item = await this.client.validateSortCode(sortCode)
        this.inputSortCode.setValid(!item.Error)
      }
      this.inputSortCode.mark()

      if (this.inputSortCode.valid && (this.inputAccount.hasChanged() || (sortCodeChanged && account))) {
        item = await this.client.validateAccount(sortCode, account)
        this.inputAccount.setValid(!item.Error)
      }
      this.inputAccount.mark()
    }
    catch (error) {
      console.error(error)
    }
    finally {
      this.stop()
    }
  }
}

export { Validator }
