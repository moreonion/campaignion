<?php

namespace Drupal\campaignion_action\Redirects;

/**
 * API-Endpoint for editing redirects.
 */
class Endpoint {

  /**
   * @var object
   * The node that is being edited.
   */
  protected $node;

  /**
   * @var int
   * The number of the redirect set.
   */
  protected $delta;

  public function __construct($node, $delta) {
    $this->node = $node;
    $this->delta = $delta;
  }

  /**
   * Convert redirect data from API to model format.
   *
   * @return array
   *   - Move redirect content (subject, header, redirect, footer) into the main array.
   *   - Move filter configuration into the config sub-array.
   */
  protected function api2model($data) {
    $data += ['filters' => []];
    return $data;
  }

  /**
   * Convert redirect data from model to API format.
   *
   * @return array
   *   - Extract the filter configuration into the main filter array.
   */
  protected function model2api($data) {
    $data += ['filters' => []];
    return $data;
  }

  public function put($data) {
    $data += ['redirects' => []];
    $data = $data['redirects'];
    $old_redirects = Redirect::byNid($this->node->nid, $this->delta);
    $w = 0;
    $new_redirects = [];
    foreach ($data as $r) {
      $r = $this->api2model($r);
      if (isset($r['id']) && isset($old_redirects[$r['id']])) {
        $redirect = $old_redirects[$r['id']];
        $redirect->setData($r);
        unset($old_redirects[$redirect->id]);
      }
      else {
        $redirect = new Redirect($r);
      }
      $redirect->nid = $this->node->nid;
      $redirect->delta = $this->delta;
      $redirect->weight = $w++;
      $redirect->save();
      $new_redirects[] = $this->model2api($redirect->toArray());
    }
    // Old redirects that are still in there have been deleted.
    foreach ($old_redirects as $redirect) {
      $redirect->delete();
    }
    return ['redirects' => $new_redirects];
  }

  public function get() {
    $values = [];
    $redirects = Redirect::byNid($this->node->nid, $this->delta);
    if (!$redirects) {
      $redirects[] = new Redirect([
        'label' => '',
        'destination' => '',
      ]);
    }
    foreach ($redirects as $r) {
      $values[] = $this->model2api($r->toArray());
    }
    return ['redirects' => $values];
  }

}

