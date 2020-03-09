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

<?php foreach ($messages as $message) : ?>
<p>Email was sent to: <?php echo htmlspecialchars($message['to']); ?></p>

<div>
<?php echo $message['message']; ?>
</div>

<hr>

<?php endforeach;
