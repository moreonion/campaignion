<?php

/**
 * @file
 * Display a list of translation links.
 *
 * Available variables:
 * - $links: An array of $link-arrays. Each $link in $links contains:
 *   - $link['li_attributes']: Attribute array for the list-item.
 *   - $link['renderable']: A renderable array for the link content.
 *
 * @see template_preprocess_campaignion_language_switcher()
 *
 * @ingroup themeable
 */
?>
<ul class="campaignion-language-switcher-<?php echo $provider; ?>">
<?php foreach ($links as $link): ?>
  <li<?php echo drupal_attributes($link['li_attributes']); ?>><?php echo render($link['renderable']); ?></li>
<?php endforeach; ?>
</ul>
