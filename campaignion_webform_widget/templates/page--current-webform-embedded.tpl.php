<?php if ($messages): ?>
  <div id="messages"><div class="section middle clearfix">
    <?php print $messages; ?>
  </div></div> <!-- /.section, /#messages -->
<?php endif; ?>

<?php if ($page['widget']): ?>
  <?php if ($is_thankyou_page): ?>
  <div id="headerwrap"><h1 class="title" id="page-title"><?php print $title; ?></h1></div>
  <?php endif; ?>
  <div class="widget">
    <?php print render($page['widget']); ?>
  </div>
<?php endif; ?>
