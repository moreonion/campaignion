<?php

namespace Drupal\campaignion_email_to_target\Api;

/**
 * Test the API-client class.
 */
class ClientTest extends \DrupalUnitTestCase {

  /**
   * Reset static cache.
   */
  public function tearDown() {
    drupal_static_reset(Client::class . '.datasets');
  }

  /**
   * Create an instrumented Api object that doesn’t actually send requests.
   */
  protected function instrumentedApi() {
    $api = $this->getMockBuilder(Client::class)
      ->setConstructorArgs(['http://mock', 'pk', 'sk'])
      ->setMethods(['send'])
      ->getMock();
    return $api;
  }

  /**
   * Test that loading a dataset twice sends only one request due to caching.
   */
  public function testRequestingDatasetTwice() {
    $api = $this->instrumentedApi();
    $api->expects($this->once())
      ->method('send')
      ->will($this->returnValue(['key' => 'test', 'attributes' => []]));
    $api->getDataset('test');
    $api->getDataset('test');
  }

  /**
   * Loading a dataset from two Client objects sends only one request (caching).
   */
  public function testRequestingDatasetTwiceOnTwoObjects() {
    $api = $this->instrumentedApi();
    $api->expects($this->once())
      ->method('send')
      ->will($this->returnValue(['key' => 'test', 'attributes' => []]));
    $api->getDataset('test');

    $api2 = $this->instrumentedApi();
    $api2->expects($this->never())->method('send');
    $api2->getDataset('test');
  }

}
