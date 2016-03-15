<div class="wizard-head" id="<?php echo drupal_html_id('wizard-head'); ?>">
  <h1 class="page-title"><?php echo drupal_get_title(); ?></h1>
  <?php echo render($form['buttons']); ?>
</div>
<?php echo render($form['trail']); ?>
<div id="wizard-main"><?php
  hide($form['wizard_secondary']);
  echo drupal_render_children($form, element_children($form, TRUE));
?></div>
<?php echo render($form['wizard_secondary']); ?>
