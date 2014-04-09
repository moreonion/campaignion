<?php

namespace Drupal\campaignion\Wizard;

abstract class NodeWizard extends \Drupal\oowizard\Wizard {
  public $node;
  public $parameters;

  public function __construct($parameters = array(), $node = NULL, $type = NULL, $user = NULL) {
    $this->parameters = $parameters;
    foreach ($this->steps as &$class) {
      if ($class[0] != '\\') {
        $class = '\\' . __NAMESPACE__ . '\\' . $class;
      }
    }

    $this->user = $user ? $user : $GLOBALS['user'];
    $this->node = $node ? $node : $this->prepareNode($type);
    parent::__construct($user);
    $this->formInfo['path'] = $node ? "node/{$node->nid}/wizard/%step" : "wizard/{$this->node->type}";

    drupal_set_title(t('Create ' . node_type_get_name($this->node)));
    $this->formInfo += array(
      'show return' => TRUE,
      'return path' => 'node/' . $this->node->nid,
    );
  }

  public function wizardForm() {
    $form = parent::wizardForm() + array(
      'wizard_head' => array(),
      'wizard_advanced' => array(),
    );
    $form['wizard_head']['trail'] = $this->trail();

    return $form;
  }

  public function prepareNode($type) {
    $node = (object) array('type' => $type);
    $node->uid  = $this->user->uid;
    $node->name = $this->user->name;
    $node->language = LANGUAGE_NONE;
    $node->title = '';
    return $node;
  }

  public function trailItems() {
    $trail = array();
    $accessible = TRUE;
    foreach ($this->stepHandlers as $urlpart => $step) {
      $is_current = $urlpart == $this->currentStep;
      $trail[] = array(
        'url' => strtr($this->formInfo['path'], array('%step' => $urlpart)),
        'title' => $step->getTitle(),
        'accessible' => $accessible = ($accessible && (!$is_current || $this->node->status) && $step->checkDependencies()),
        'current' => $urlpart == $this->currentStep,
      );
    }
    return $trail;
  }
}
