<?php

namespace Drupal\campaignion_manage\Filter;

class ContentNodeReference extends Base implements FilterInterface {
  protected $query;
  protected $referenceField;
  protected $referenceColumn;

  public function __construct(\SelectQueryInterface $query, $reference_field, $reference_column) {
    $this->query           = $query;
    $this->referenceField  = $reference_field;
    $this->referenceColumn = $reference_column;
  }

  protected function getOptions() {
    $language = $default_lang = language_default()->language;
    if (!empty($GLOBALS['user']->language)) {
      $language = $GLOBALS['user']->language;
    }
    $query = clone $this->query;
    $fields =& $query->getFields();
    $query->innerJoin('field_data_' . $this->referenceField, 'ref', 'ref.entity_id = n.nid OR ref.entity_id = n.tnid');
    $query->innerJoin('node', 'cn', 'ref.' . $this->referenceColumn . ' = cn.nid');
    $query->addExpression('IF(cn.tnid = 0, cn.nid, cn.tnid)', 'tset_ref');
    $fields = array(
      'nid' => array(
        'field' => 'nid',
        'table' => 'cn',
        'alias' => 'nid',
      ),
      'title' => array(
        'field' => 'title',
        'table' => 'cn',
        'alias' => 'title',
      ),
      'language' => array(
        'field' => 'language',
        'table' => 'cn',
        'alias' => 'language',
      ),
    );
    $query->groupBy('cn.title');
    $tset_result = array();
    // build result as a translation set structure with
    // array[orig_nid][language][nid, title, language, tset_ref]
    foreach ($query->execute()->fetchAll() as $set) {
      $tset_result[$set->tset_ref][$set->language] = $set;
    }
    $result = array();
    foreach ($tset_result as $orig_nid => $set) {
      if (isset($set[$language])) {
        // if the referenced node exists in the user language, take this one
        $result[$set[$language]->nid] = $set[$language]->title;
      }
      elseif (isset($set[$default_lang])) {
        // if the referenced node exists in the site default language, take
        // this one
        $result[$set[$default_lang]->nid] = $set[$default_lang]->title;
      }
      else {
        // the referenced node is neither in user nor in site default language
        // available, let's take the first we can get
        $first = array_shift($set);
        $result[$first->nid] = $first->title;
      }
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

