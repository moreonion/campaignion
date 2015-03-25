<?php

/**
 * Implements hook_campaignion_activity_type_info().
 *
 * … on behalf of the commend.module
 */
function comment_campaignion_activity_type_info() {
  $info['comment'] = 'Drupal\campaignion_activity\Comment';

  return $info;
}

/**
 * Implements hook_comment_insert().
 */
function campaignion_activity_comment_insert($comment) {
  if ($activity = \Drupal\campaignion_activity\Comment::fromComment($comment)) {
    $activity->save();
  }
}
