<h4 id="curator-monitor-quick-filters"><?php echo __('Quick Filters >'); ?></h4>
<div id="curator-monitor-quick-filters-div" class="quick-filters" style="display:none">
<?php foreach ($statusTermsElements as $elementId => $statusElement): ?>
    <ul class="quick-filter-wrapper">
        <li><a href="#"><?php echo $statusElement['name']; ?></a>
        <ul class="dropdown">
            <li><span class="quick-filter-heading"><?php echo $statusElement['name']; ?></span></li>
            <li><span style="font-weight: bold; font-style: italic; padding-left: 4px; background-color:#fff;"><?php echo __('Status'); ?></span></li>
            <?php foreach ($statusElement['terms'] as $term): ?>
            <li><?php echo link_to_items_browse($term,
                    array('advanced' => array(array('element_id' => $elementId, 'type' => 'is exactly', 'terms' => $term)))); ?>
            </li>
            <?php endforeach; ?>
            <li><span style="font-weight: bold; font-style: italic; padding-left: 4px; background-color:#fff;"><?php echo __('Contains'); ?></span></li>
            <li><?php echo link_to_items_browse(__('is empty'),
                    array('advanced' => array(array('element_id' => $elementId, 'type' => 'is empty')))); ?>
            </li>
            <li><?php echo link_to_items_browse(__('is not empty'),
                    array('advanced' => array(array('element_id' => $elementId, 'type' => 'is not empty')))); ?>
            </li>
        </ul>
        </li>
    </ul>
<?php endforeach; ?>
<?php foreach ($statusNoTermElements as $elementId => $statusElement): ?>
    <ul class="quick-filter-wrapper">
        <li><a href="#"><?php echo $statusElement['name']; ?></a>
        <ul class="dropdown">
            <li><span class="quick-filter-heading"><?php echo $statusElement['name']; ?></span></li>
            <li><?php echo link_to_items_browse(__('is empty'),
                    array('advanced' => array(array('element_id' => $elementId, 'type' => 'is empty')))); ?>
            </li>
            <li><?php echo link_to_items_browse(__('is not empty'),
                    array('advanced' => array(array('element_id' => $elementId, 'type' => 'is not empty')))); ?>
            </li>
        </ul>
        </li>
    </ul>
<?php endforeach; ?>
<?php fire_plugin_hook('curator_monitor_items_browse_filter', array('view' => $this)); ?>
</div>
<br class="clear" />