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
    $query = clone $this->query;
    $fields =& $query->getFields();
    $query->innerJoin('field_data_' . $this->referenceField, 'ref', 'ref.entity_id = n.nid');
    $query->innerJoin('node', 'cn', 'ref.' . $this->referenceColumn . ' = cn.nid');
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
    );
    $query->groupBy('cn.title');

    return $query->execute()->fetchAllKeyed();
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
    $query->innerJoin('field_data_' . $this->referenceField, 'ref', 'ref.entity_id = n.nid');
    $query->condition('ref.' . $this->referenceColumn, $values['nid']);
  }
  public function isApplicable($current) {
    return empty($current) && count($this->getOptions()) > 0;
  }
  public function defaults() {
    $options = $this->getOptions();
    return array('nid' => key($options));
  }
}

