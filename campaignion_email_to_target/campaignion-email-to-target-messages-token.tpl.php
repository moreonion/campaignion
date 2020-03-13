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
<p>Email to: <?php echo check_plain($message->to); ?> with subject line “<?php echo check_plain($message->subject); ?>”</p>

<?php echo $message->header; ?>
<?php echo $message->message; ?>
<?php echo $message->footer; ?>
<?php
  $first = FALSE;
endforeach;
