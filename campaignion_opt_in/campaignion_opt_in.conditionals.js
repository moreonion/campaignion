/**
 * @file
 * Conditional operators for newsletter fields.
 */

(function () {

  "use strict";
  Drupal.webform = Drupal.webform || {};
  Drupal.webform.conditionalOperatorOptInEqual = function (element, existingValue, ruleValue) {
    var checkbox = element.querySelector('.form-type-checkbox input');
    if (checkbox) {
      return checkbox.checked ? ruleValue === 'checkbox:opt-in' : ruleValue === 'checkbox:no-change';
      // TODO: How to recognise inverted checkbox???
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

})();
