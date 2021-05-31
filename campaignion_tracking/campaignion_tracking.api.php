<?php

/**
 * @file
 * Document hooks invoked by the campaignion_tracking module.
 */

/**
 * Define snippets to load.
 */
function hook_campaignion_tracking_snippets() {
  $exports['snippet'] = <<<SNIPPET
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
  ga('create', 'UA-XXXXXX-YY', 'auto');
  ga('send', 'pageview');
SNIPPET;
  return $exports;
}

/**
 * Alter the snippets defined in hook_campaignion_tracking_snippets().
 *
 * @param array $snippets
 *   The snippets defined in the earlier hook invocation.
 */
function hook_campaignion_tracking_snippets_alter(array &$snippets) {
  unset($snippets['snippet']);
}
