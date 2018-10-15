/**
 * @file
 * Conditional operators for newsletter fields.
 */

(function ($) {

  "use strict";

  var getValue = function (element, existingValue) {
    if (existingValue) {
      return existingValue[0];
    }
    if (element) {
      if ($(element).closest('.webform-conditional-hidden').length > 0) {
        return null;
      }
      var checkbox = element.querySelector('.form-type-checkbox input');
      if (checkbox) {
        var uncheckedValue = checkbox.getAttribute('data-no-value');
        var prefix = checkbox.getAttribute('data-prefix');
        var value = checkbox.checked ? checkbox.value : uncheckedValue;
        return prefix + ':' + value;
      }
      var radio = element.querySelector('.form-type-radio input');
      if (radio) {
        var radioChecked = element.querySelector('.form-type-radio input:checked');
        var radioValue = radioChecked ? radioChecked.value : 'not-selected';
        return 'radios:' + radioValue;
      }
    }
  };
  Drupal.webform = Drupal.webform || {};
  Drupal.webform.conditionalOperatorOptInEqual = function (element, existingValue, ruleValue) {
    return ruleValue === getValue(element, existingValue);
  };
  Drupal.webform.conditionalOperatorOptInNotEqual = function (element, existingValue, ruleValue) {
    return !Drupal.webform.conditionalOperatorOptInEqual(element, existingValue, ruleValue);
  };

})(jQuery);
