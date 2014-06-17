<?php

namespace Drupal\campaignion_manage\Filter;

class ContentNodeReference extends Base implements FilterInterface {
  protected $query;
  protected $referenceField;
  protected $referenceColumn;
  protected $langs;

  public function __construct(\SelectQueryInterface $query, $reference_field, $reference_column) {
    $this->query           = $query;
    $this->referenceField  = $reference_field;
    $this->referenceColumn = $reference_column;
    $this->langs[] = $GLOBALS['language']->language;
    if (!empty($GLOBALS['user']->language)) {
      $this->langs[] = $GLOBALS['user']->language;
    }
    $this->langs[] = language_default()->language;
  }

  protected function getOptions() {
    $query = clone $this->query;
    $fields =& $query->getFields();
    $fields = array();
    $query->innerJoin('field_data_' . $this->referenceField, 'ref', 'ref.entity_id = n.nid OR ref.entity_id = n.tnid');
    $query->innerJoin('node', 'cn', 'ref.' . $this->referenceColumn . ' = cn.nid');
    $query->innerJoin('node', 'tn', 'tn.nid=cn.nid OR (cn.tnid<>0 AND tn.tnid=cn.tnid)');
    $query->addExpression('IF(tn.tnid = 0, tn.nid, tn.tnid)', 'tset_ref');
    $fields = array(
      'nid' => array(
        'field' => 'nid',
        'table' => 'tn',
        'alias' => 'nid',
      ),
      'title' => array(
        'field' => 'title',
        'table' => 'tn',
        'alias' => 'title',
      ),
      'language' => array(
        'field' => 'language',
        'table' => 'tn',
        'alias' => 'language',
      ),
    );
    $query->groupBy('tn.nid');
    $tset_result = array();
    // build result as a translation set structure with
    // array[orig_nid][language][nid, title, language, tset_ref]
    foreach ($query->execute()->fetchAll() as $set) {
      $tset_result[$set->tset_ref][$set->language] = $set;
    }
    $result = array();
    foreach ($tset_result as $orig_nid => $set) {
      $node = NULL;
      foreach ($this->langs as $langcode) {
        if (isset($set[$langcode])) {
          $node = $set[$langcode];
          break;
        }
      }
      if (!$node) {
        $node = array_shift($set);
      }
      $result[$node->nid] = $node->title;
    }
    return $result;
  }

  public function formElement(array &$form, array &$form_state, array &$values) {
    $form['nid'] = array(
      '#type'          => 'select',
      '#title'         => t('Node Reference'),
      '#options'       => $this->getOptions(),
      '#default_value' => isset($values) ? $values : NULL,
    );
  }
  public function title() { return t('Node Reference'); }
  public function apply($query, array $values) {
    $ref_nids = db_query(
      'SELECT tr.nid ' .
      '  FROM {node} n ' .
      '  INNER JOIN {node} tr ON IF(tr.tnid=0, tr.nid, tr.tnid) = IF(n.tnid=0, n.nid, n.tnid) ' .
      '    WHERE n.nid = :ref_nid ' ,
      array(':ref_nid' => $values['nid'])
    )->fetchCol();
    $query->innerJoin('field_data_' . $this->referenceField, 'ref', 'ref.entity_id = n.nid');
    $query->innerJoin('node', 'tr', 'IF(tr.tnid=0, tr.nid, tr.tnid) = IF(n.tnid=0, n.nid, n.tnid)');
    $query->condition('ref.' . $this->referenceColumn, $ref_nids, 'IN');
    $fields =& $query->getFields();
    foreach ($fields as &$field) {
      if ($field['table'] === 'n') {
	$field['table'] = 'tr';
      }
    }
  }
  public function isApplicable($current) {
    return empty($current) && count($this->getOptions()) > 0;
  }
  public function defaults() {
    $options = $this->getOptions();
    return array('nid' => key($options));
  }
}

