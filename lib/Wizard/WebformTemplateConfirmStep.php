<?php

namespace Drupal\campaignion\Wizard;

class WebformTemplateConfirmStep extends ConfirmStep {
  public function submitStep($form, &$form_state) {
    if (isset($this->wizard->node->nid)) {
      if(isset($form_state['clicked_button']['#name'])) {
        $node = $this->wizard->node;
        switch($form_state['clicked_button']['#name']) {
          case 'finish':
            $node->status = 1;
            node_save($node);
            drupal_set_message(t('Template published successfully.'), 'status');
            $form_state['redirect'] = 'node/' . $node->nid;
            break;
          case 'draft':
            $node->status = 0;
            node_save($node);
            drupal_set_message(t('Template saved as draft.'), 'status');
            $form_state['redirect'] = 'node/' . $node->nid;
            break;
          case 'schedule':
            drupal_set_message(t('Schedule is not implemented yet.'), 'warning');
            break;
        }
      }
    } else {
      drupal_set_message(t('Where is my node? Did you fill out the first step?'), 'error');
      $form_state['redirect'] = ''; // stay on the page, do not redirect
    }
  }
}
