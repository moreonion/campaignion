<?php

namespace Drupal\campaignion_manage;

class ContentListing {
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
        'class' => array('campaignion-manage-content-listing'),
      ),
      '#formObj' => $this,
    );
  }

  public function process(&$element, &$form_state) {
    $result = $this->query->execute();
    $columns = 3;

    $rows = array();

    $element['bulk_nid'] = array(
      '#type' => 'checkboxes',
      '#options' => array(),
    );
    $element['bulk_tnid'] = array(
      '#type' => 'checkboxes',
      '#options' => array(),
    );

    $tnode_count = 1;
    foreach ($result as $tnode) {
      $class = ($tnode_count++ % 2 == 0) ? 'even' : 'odd';
      $row = $this->nodeRow($tnode, TRUE, $element);
      $row['class'][] = $class;
      $rows[] = $row;
      if (count($tnode->translations) > 1) {
        $bigcellrow['data']['bigcell']['colspan'] = $columns;
        $bigcellrow['class'][] = 'node-translations';
        $bigcellrow['class'][] = $class;

        $innerrows = array();
        foreach ($tnode->translations as $lang => $node) {
          $innerrows[] = $this->nodeRow($node, FALSE, $element);
        }
        $bigcellrow['data']['bigcell']['data'] = array(
          '#theme' => 'table',
          '#rows' => $innerrows,
        );
        $rows[] = $bigcellrow;
      }
    }

    $element += array(
      '#rows' => $rows,
    );
  }

  protected function nodeRow($node, $tset, &$element) {
    $row['data']['bulk']['class'] = array('manage-bulk');
    $pfx = 'bulk_' . ($tset ? 'tnid' : 'nid');
    $row['data']['bulk']['data'] = array(
      '#type' => 'checkbox',
      '#title' => $tset ? t("Select this content and all it's translations for bulk operations") : t('Select this content for bulk operations.'),
      '#return_value' => $node->nid,
      '#default_value' => isset($element[$pfx]['#default_value'][$node->nid]),
    );
    $element[$pfx][$node->nid] = &$row['data']['bulk']['data'];
    $element[$pfx]['#options'][$node->nid] = $node->nid;
    $row['data']['content']['class'] = array('campaignion-manage');
    $row['data']['content']['data'] = array(
      '#theme' => 'campaignion_manage_node',
      '#node' => $node,
      '#translation_set' => $tset,
    );
    $row['data']['links']['class'] = array('manage-links');
    $row['data']['links']['data'] = array(
      '#theme' => 'links__ctools_dropbutton',
      '#links' => $this->nodeLinks($node),
      '#image' => TRUE,
    );
    if ($tset) {
      $row['no_striping'] = TRUE;
      if (isset($node->translations) && count($node->translations) > 1) {
        $row['class'][] = 'node-translation-set';
      }
    }
    return $row;
  }

  protected function nodeLinks($node) {
    $links = array();
    $edit_path_part = 'edit';

    // set path to wizard for action content types
    if (module_exists('campaignion_wizard')) {
      if (\Drupal\campaignion\Action\TypeBase::isAction($node->type)) {
        $edit_path_part = 'wizard';
      }
    }
    foreach (array($edit_path_part => t('Edit'), 'translate' => t('Translate'), 'view' => t('View page'), 'delete' => t('Delete')) as $path => $title) {
      $links[$path] = array(
        'href' => "node/{$node->nid}/$path",
        'title' => $title,
      );
    }
    return $links;
  }

  public function selectedIds(&$element, &$form_state) {
    $values = &drupal_array_get_nested_value($form_state['values'], $element['#array_parents']);
    $nids = array();
    foreach ($values['bulk_nid'] as $nid => $selected) {
      if ($selected) {
        $nids[$nid] = $nid;
      }
    }
    foreach ($values['bulk_tnid'] as $nid => $selected) {
      if ($selected) {
        $nids[$nid] = $nid;
      }
    }
    return array_keys($nids);
  }
}
