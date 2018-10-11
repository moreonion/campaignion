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
        return checkbox.checked ? 'checkbox:opt-in' : 'checkbox:no-change';
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
  Drupal.webform.conditionalOperatorNewsletterEqual = function (element, existingValue, ruleValue) {
    return getValue(element, existingValue) === ruleValue;
  };
  Drupal.webform.conditionalOperatorNewsletterNotEqual = function (element, existingValue, ruleValue) {
    return !Drupal.webform.conditionalOperatorNewsletterEqual(element, existingValue, ruleValue);
  };

})(jQuery);
