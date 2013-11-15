<?php

namespace Drupal\campaignion_manage;

class ContentListing {
  /**
   * Build a renderable array based on the data-rows.
   *
   * @param rows result from the Query object
   * @return renderable array for output.
   */
  public function build($result) {
    $columns = 3;

    $rows = array();

    $tnode_count = 1;
    foreach ($result as $tnode) {
      $class = ($tnode_count++ % 2 == 0) ? 'even' : 'odd';
      $row = $this->nodeRow($tnode, TRUE);
      $row['class'][] = $class;
      $rows[] = $row;
      if (count($tnode->translations) > 1) {
        $bigcellrow['data']['bigcell']['colspan'] = $columns;
        $bigcellrow['class'][] = 'node-translations';
        $bigcellrow['class'][] = $class;

        $innerrows = array();
        foreach ($tnode->translations as $lang => $node) {
          $innerrows[] = $this->nodeRow($node, FALSE);
        }
        $bigcellrow['data']['bigcell']['data'] = array(
          '#theme' => 'table',
          '#rows' => $innerrows,
        );
        $rows[] = $bigcellrow;
      }
    }

    return array(
      '#theme' => 'table',
      '#attributes' => array(
        'class' => array('campaignion-manage-content-listing'),
      ),
      '#rows' => $rows,
    );
  }

  protected function nodeRow($node, $tset = TRUE) {
    $row['data']['bulk']['class'] = array('manage-bulk');
    $row['data']['bulk']['data'] = array(
      '#type' => 'checkbox',
      '#name' => 'bulk_' . ($tset ? 'tnid' : 'nid'),
      '#title' => $tset ? t("Select this content and all it's translations for bulk operations") : t('Select this content for bulk operations.'),
      '#value' => $node->nid,
    );
    $row['data']['content']['class'] = array('manage-content');
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
    foreach (array('edit' => t('Edit'), 'translate' => t('Translate'), 'view' => t('View page'), 'delete' => t('Delete')) as $path => $title) {
      $links[$path] = array(
        'href' => "node/{$node->nid}/$path",
        'title' => $title,
      );
    }
    return $links;
  }
}
