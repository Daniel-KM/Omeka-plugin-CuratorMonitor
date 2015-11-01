<fieldset id="fieldset-curator-monitor-config"><legend><?php echo __('Elements'); ?></legend>
    <?php $monitorElementSet = $this->monitor()->getElementSet(); ?>
    <p><?php echo __('To manage elements (repeatable or not, steppable or not, with list of terms or not...), go to %sSettings%s, then %sElement Sets%s, then %sMonitor%s.',
            '<a href="' . url('settings') . '">', '</a>',
            '<a href="' . url('element-sets') . '">', '</a>',
            '<a href="' . url('element-sets/edit/' . $monitorElementSet->id) . '">', '</a>'); ?></p>
</fieldset>
