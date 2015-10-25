<?php
$title = __('Curator Monitor | View Report');
$head = array(
    'title' => html_escape($title),
    'bodyclass' => 'curator-monitor search',
);
echo head($head);
?>
<div id="primary">
    <?php echo flash(); ?>
<?php if (total_records('HistoryLogEntry') > 0): ?>
    <div><?php echo common('quick-filters'); ?></div>
    <br class="clear" />
    <div><?php echo$form; ?></div>
<?php else: ?>
        <p><?php echo __('No entry have been logged.'); ?></p>
<?php endif; ?>
</div>
<?php echo foot();
