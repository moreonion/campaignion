<?php

namespace Drupal\campaignion_manage\Filter;

class Base {
  public function defaults() { return array(); }
  public function isApplicable($current) {
    // By default filters can only be used once.
    return empty($current);
  }
}
