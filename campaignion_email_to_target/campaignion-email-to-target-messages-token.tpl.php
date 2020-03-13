<?php

/**
 * @file
 * Displays all messages sent to targets.
 *
 * Available variables:
 * - $messages: An array of mails sent to targets.
 * - $submission: The submission of the email to target action.
 */
?>
<?php
$first = TRUE;
foreach ($messages as $message): ?>
<?php if (!$first): ?>
  <hr>
<?php endif; ?>
<div class="e2t-message">
<h3>Email to: <?php echo check_plain($message->to); ?> with subject line “<?php echo check_plain($message->subject); ?>”</h3>
<p><?php echo $message->header; ?></p>
<?php echo _filter_autop($message->message); ?>
<p><?php echo $message->footer; ?></p>
</div>
<?php
  $first = FALSE;
endforeach;
