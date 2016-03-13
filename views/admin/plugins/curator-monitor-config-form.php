<fieldset id="fieldset-curator-monitor-elements"><legend><?php echo __('Elements'); ?></legend>
    <?php $monitorElementSet = $this->monitor()->getElementSet(); ?>
    <p><?php echo __('To manage elements (repeatable or not, steppable or not, with list of terms or not...), go to %sSettings%s, then %sElement Sets%s, then %sMonitor%s.',
            '<a href="' . url('settings') . '">', '</a>',
            '<a href="' . url('element-sets') . '">', '</a>',
            '<a href="' . url('element-sets/edit/' . $monitorElementSet->id) . '">', '</a>'); ?></p>
    <div class="field">
        <div class="two columns alpha">
            <?php echo $this->formLabel('curator_monitor_display_remove',
                __('Display Remove Checkbox')); ?>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation">
                <?php
                echo __('If set, a checkbox will be displayed the next time in the page above to remove any existing element of the Monitor Element Set.');
                echo '<br />';
                echo __('Warning: All data of the selected fields will be removed and will not be recoverable easily.');
                echo ' ' . __('So, check first if your backups are up to date and working.');
                ?>
            </p>
            <?php echo $this->formCheckbox('curator_monitor_display_remove', true,
                array('checked' => false)); ?>
        </div>
    </div>
</fieldset>
<fieldset id="fieldset-curator-monitor-admin-display"><legend><?php echo __('Specific admin display'); ?></legend>
    <div class="field">
        <div class="two columns alpha">
            <?php echo $this->formLabel('curator_monitor_admin_items_browse', __('Items view')); ?>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation">
                <?php echo __('If checked, these filters will be added in the items/browse view.'); ?>
            </p>
            <div class="input-block">
                <ul style="list-style-type: none;">
                <?php
                    $currentStatus = json_decode(get_option('curator_monitor_admin_items_browse')) ?: array();
                    $statusElements = $this->monitor()->getStatusElements(null, null, null, true);
                    foreach ($statusElements as $elementId => $elementName) {
                        echo '<li>';
                        echo $this->formCheckbox('curator_monitor_admin_items_browse[]', $elementId,
                            array('checked' => in_array($elementId, $currentStatus) ? 'checked' : ''));
                        echo $elementName;
                        echo '</li>';
                    }
                ?>
                </ul>
            </div>
        </div>
    </div>
</fieldset>
