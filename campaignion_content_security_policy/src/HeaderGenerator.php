<?php

namespace Drupal\campaignion_content_security_policy;

/**
 * Service for setting the configured Content-Security-Policy headers.
 */
class HeaderGenerator {

  /**
   * Array of trusted frame-ancestors.
   *
   * @var array
   */
  protected $sources;

  /**
   * Create a new instance based on the textarea value.
   *
   * @param string|null $trusted_sources_str
   *   The string trusted source URLs as stored in the Drupal variable. This
   *   might be NULL when the module is enabled initally and before the caches
   *   are cleared.
   */
  public static function fromConfig($trusted_sources_str) {
    $trusted_sources_str = $trusted_sources_str ?? "'self'\n";
    $trusted_sources = array_filter(array_map('trim', explode("\n", $trusted_sources_str)));
    return new static($trusted_sources);
  }

  /**
   * Create a new instance.
   */
  public function __construct(array $trusted_sources) {
    $this->sources = $trusted_sources;
  }

  /**
   * Add the headers for this request.
   */
  public function addHeaders() {
    $header_urls = implode(' ', $this->sources);
    drupal_add_http_header('Content-Security-Policy', "frame-ancestors $header_urls");
  }

}
