<?php

namespace Drupal\campaignion_activity;

class ActivityTest extends \DrupalWebTestCase {
  function testCRUD_allData() {
    $activity = new ActivityBase([
      'created' => REQUEST_TIME,
      'type' => 'activity_test_type',
      'contact_id' => 21,
    ]);
    $activity->save();
    
    $loaded_activity = ActivityBase::load($activity->activity_id);
    $this->assertEqual($activity->activity_id, $loaded_activity->activity_id);
    
    $activity->delete();
    $loaded_activity = ActivityBase::load($activity->activity_id);
    $this->assertFalse($loaded_activity);
  }
}
