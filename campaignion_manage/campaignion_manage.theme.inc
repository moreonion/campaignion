<?php

function theme_campaignion_manage_node($variables) {
  $node = $variables['node'];
  $title = $node->title;
  $status_translated = ($node->status == 1) ? t('Published') : t('Draft');
  $status_css = 'status-' . (($node->status == 1) ? 'published' : 'draft');
  $type_translated = t($node->type);
  $content_type_name = node_type_get_name($node);
  $user = user_load($node->uid);
  $author = $user->name;

  $content = <<<EOT
<span class="icon"></span>
<div class="heading">
<span class="title">{$title}</span>
<span class="status {$status_css}">{$status_translated}</span>
</div>
<div class="additional-info">
<span class="type">{$content_type_name}</span>
<span class="author">{$author}</span>
</div>
EOT;

  return $content;
}
