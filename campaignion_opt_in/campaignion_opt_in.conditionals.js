/**
 * @file
 * Conditional operators for newsletter fields.
 */

(function ($) {

  "use strict";
  Drupal.webform = Drupal.webform || {};
  Drupal.webform.conditionalOperatorOptInEqual = function (element, existingValue, ruleValue) {
    if ($(element).closest('.webform-conditional-hidden').length > 0) {
      return false;
    }
    var checkbox = element.querySelector('.form-type-checkbox input');
    if (checkbox) {
      var uncheckedValue = checkbox.getAttribute('data-no-value');
      var isInverted = uncheckedValue === 'opt-in';
      var value = checkbox.checked ? checkbox.value : uncheckedValue;
      return ruleValue === 'checkbox' + (isInverted ? '-inverted:' : ':') + value;
    }
    var radio = element.querySelector('.form-type-radio input');
    if (radio) {
      var radioChecked = element.querySelector('.form-type-radio input:checked');
      var radioValue = radioChecked ? radioChecked.value : 'not-selected';
      return 'radios:' + radioValue === ruleValue;
    }
    return false;
  };
  Drupal.webform.conditionalOperatorOptInNotEqual = function (element, existingValue, ruleValue) {
    return !Drupal.webform.conditionalOperatorOptInEqual(element, existingValue, ruleValue);
  };

})(jQuery);
