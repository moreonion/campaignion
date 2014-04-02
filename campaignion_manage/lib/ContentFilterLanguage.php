<?php

namespace Drupal\campaignion_manage;

class ContentFilterLanguage implements FilterInterface {
  protected $query;

  public function __construct(\SelectQuery $query) {
    $this->query = $query;
  }

  public function formElement(array &$form, array &$form_state, array &$values) {
    $query = clone $this->query;
    $fields =& $query->getFields();
    $fields = array(
      'language' => array(
        'field' => 'language',
        'table' => 'n',
        'alias' => 'language',
      ),
    );
    $query->groupBy('n.language');
    $langs_in_use = $query->execute()->fetchCol();
    $options = array();
    if (in_array('', $langs_in_use)) {
      $options[''] = t('Language neutral');
    }
    $lang_list = language_list();
    foreach ($langs_in_use as $lang) {
      if (isset($lang_list[$lang])) {
        $options[$lang] = $lang_list[$lang]->native;
      }
    }
    $form['language'] = array(
      '#type' => 'select',
      '#title' => t('Language'),
      '#options' => $options,
      '#default_value' => isset($values) ? $values : NULL,
    );
  }
  public function machineName() { return 'language'; }
  public function title() { return t('Language'); }
  public function apply($query, array $values) {
    $query->getQuery()->condition('n.language', $values['language']);
  }
  public function nrOfInstances() {
    return 1;
  }
}
