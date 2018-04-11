/**
 * @file
 * Conditional operators for newsletter fields.
 */

(function() {
  
  "use strict";
  Drupal.webform = Drupal.webform || {};
  Drupal.webform.conditionalOperatorOptInEqual = function (element, existingValue, ruleValue) {
    var checkbox = element.querySelector('.form-type-checkbox input');
    if (checkbox) {
      return checkbox.checked ? ruleValue === checkbox.value : ruleValue === '';
    }
    var radio = element.querySelector('.form-type-radio input:checked');
    if (radio) {
      // The radios use the same values as the conditional ruleValue.
      return element.querySelector('input:checked').value === ruleValue;
    }
    // If no radio is selected at the moment this counts as "no change".
    return ruleValue === '';
  };
  Drupal.webform.conditionalOperatorOptInNotEqual = function (element, existingValue, ruleValue) {
    return !Drupal.webform.conditionalOperatorOptInEqual(element, existingValue, ruleValue);
  };
  
})();
