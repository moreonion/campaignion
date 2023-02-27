<?php

/**
 * GTM Tracker tests.
 */
class GA4TrackerTest extends \DrupalUnitTestCase {

  /**
   * Test that adding the js file works.
   */
  public function testAddingJavascript() {
    $page = ['content' => []];
    campaignion_tracking_ga4_page_build($page);
    $this->assertNotEmpty($page['content']['#attached']['js']);
  }

}
