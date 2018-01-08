<?php

namespace Drupal\campaignion_action\Redirects;

/**
 * Test the filter model class.
 */
class FilterTest extends \DrupalWebTestCase {

  /**
   * Cleanup after testing.
   */
  public function tearDown() {
    db_delete('campaignion_action_redirect_filter')->execute();
  }

  /**
   * Test creating a single filter.
   */
  public function testPutOneMessageOnEmptyNode() {
    $f = Filter::fromArray(['type'=> 'test', 'config' => 'something']);
    $this->assertEquals(['config' => 'something'], $f->config);
    $f->redirect_id = 1;
    $f->weight = 0;
    $f->save();
    $fs = Filter::byRedirectIds([1]);
    $this->assertCount(1, $fs);
    $this->assertEquals(['config' => 'something'], array_values($fs)[0]->config);
  }

}
