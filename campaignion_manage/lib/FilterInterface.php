<?php

namespace Drupal\campaignion_manage;

interface FilterInterface {
  /**
   * Insert additional form elements into the elements array.
   *
   * @param element An already prepared fieldset form element.
   * @param form_state The form_state array of the whole filterForm.
   * @param values The array of values from previous submissions (ie. use as default values!)
   */
  public function formElement(array &$element, array &$form_state, array &$values);
  /**
   * Get the a unique machineName for this kind of filter.
   *
   * @return a unique name for this kind of filter - should be form-key save.
   */
  public function machineName();
  /**
   * A human readable title for this filter. „Filter by …“
   *
   * @return string
   */
  public function title();
  /**
   * Apply all necessary conditions to the $query using the values from $values.
   *
   * @param BaseQuery the query for the to-be-filtered listing.
   * @param array Array of values from previous form submissions.
   */
  public function apply($query, array $values);
}
