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
    $row_default['data']['bulk']['data'] = array(
      '#type' => 'checkbox',
    );
    $row_default['data']['content']['data'] = array();
    $row_default['data']['links']['data'] = array(
      '#theme' => 'links__ctools_dropbutton',
      '#links' => array(),
      '#image' => TRUE,
    );
    $columns = count($row_default['data']);

    $rows = array();

    $tnode_count = 1;
    foreach ($result as $tnode) {
      $class = ($tnode_count++ % 2 == 0) ? 'even' : 'odd';
      $row = $row_default;
      $row['no_striping'] = TRUE;
      $row['data']['links']['data']['#links'] = $this->nodeLinks($tnode);
      $row['data']['bulk']['data'] += array(
        '#name' => 'bulk_tnid[]',
        '#attributes' => array('title' => "Select this content and all it's translations for bulk operations."),
        '#value' => $tnode->nid,
      );
      $row['data']['content']['data'] = array(
        '#markup' => 'Translation set: ' . $tnode->title,
      );
      $row['data']['content']['class'] = array('content');
      $row['class'][] = $class;
      if (count($tnode->translations) > 1) {
        $row['class'][] = 'node-translation-set';
        $rows[] = $row;

        $bigcellrow['data']['bigcell']['colspan'] = $columns;
        $bigcellrow['class'][] = 'node-translations';
        $bigcellrow['class'][] = $class;
        $innerRows = array();
        foreach ($tnode->translations as $lang => $node) {
          $row = $row_default;
          $row['data']['bulk']['data'] += array(
            '#name' => 'bulk_nid[]',
            '#attributes' => array('title' => "Select this content for bulk operations."),
            '#value' => $node->nid,
          );
          $row['data']['content']['data'] = array(
            '#markup' => 'Translation: ' . $node->title,
          );
          $row['data']['links']['data']['#links'] = $this->nodeLinks($node);
          $innerrows[] = $row;
        }
        $bigcellrow['data']['bigcell']['data'] = array(
          '#theme' => 'table',
          '#rows' => $innerrows,
        );
        $rows[] = $bigcellrow;
      } else {
        $rows[] = $row;
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
