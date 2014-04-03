<?php

namespace Drupal\campaignion_manage;

class SupporterListing {
  protected $query;
  public function __construct($query) {
    $this->query = $query;
  }
  /**
   * Build a renderable array based on the data-rows.
   *
   * @param rows result from the Query object
   * @return renderable array for output.
   */
  public function build(&$element, &$form_state) {
    $element += array(
      '#type' => 'campaignion_manage_listing',
      '#attributes' => array(
        'class' => array('campaignion-manage-supporter-listing'),
      ),
      '#formObj' => $this,
    );
  }

  public function process(&$element, &$form_state) {
    $result = $this->query->execute();
    $columns = 3;

    $rows = array();

    $element['bulk_id'] = array(
      '#type' => 'checkboxes',
      '#options' => array(),
    );

    $evenodd = 1;
    foreach ($result as $contact) {
      $class = ($evenodd++ % 2 == 0) ? 'even' : 'odd';
      $row = $this->renderRow($contact, $element);
      $row['class'][] = $class;
      $rows[] = $row;
    }

    $element += array(
      '#rows' => $rows,
    );
  }

  protected function renderRow($contact, &$element) {
    $row['data']['bulk']['class'] = array('manage-bulk');
    $pfx = 'bulk_id';
    $row['data']['bulk']['data'] = array(
      '#type' => 'checkbox',
      '#title' => t("Select this contact for bulk operations"),
      '#return_value' => $contact->contact_id,
      '#default_value' => isset($element[$pfx]['#default_value'][$contact->contact_id]),
    );
    $element[$pfx][$contact->contact_id] = &$row['data']['bulk']['data'];
    $element[$pfx]['#options'][$contact->contact_id] = $contact->contact_id;
    $row['data']['content']['class'] = array('manage-content');
    $row['data']['content']['data'] = array(
      '#theme' => 'campaignion_manage_contact',
      '#contact' => $contact,
    );
    $row['data']['links']['class'] = array('manage-links');
    $row['data']['links']['data'] = array(
      '#theme' => 'links__ctools_dropbutton',
      '#links' => $this->renderLinks($contact),
      '#image' => TRUE,
    );
    return $row;
  }

  protected function renderLinks($contact) {
    $links = array();
    foreach (array('edit' => t('Edit'), 'view' => t('View page'), 'delete' => t('Delete')) as $path => $title) {
      $links[$path] = array(
        'href' => "redhen/contact/{$contact->contact_id}/view/$path",
        'title' => $title,
      );
    }
    return $links;
  }

  public function selectedIds(&$element, &$form_state) {
    $values = &drupal_array_get_nested_value($form_state['values'], $element['#array_parents']);
    $ids = array();
    foreach ($values['bulk_id'] as $id => $selected) {
      if ($selected) {
        $ids[$id] = $id;
      }
    }
    return array_keys($ids);
  }
}
