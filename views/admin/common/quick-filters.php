<style type="text/css" media="all">
<!--
ul.quick-filter-wrapper li ul li {width: 220px;}
ul.quick-filter-wrapper li ul li span {width: 206px;}
-->
</style>
<ul class="quick-filter-wrapper">
    <li><a href="#" tabindex="0"><?php echo __('Quick Filter'); ?></a>
    <ul class="dropdown">
        <li><span class="quick-filter-heading"><?php echo __('Quick Filter') ?></span></li>
        <li><a href="<?php echo url('curator-monitor'); ?>"><?php echo __('View All') ?></a></li>
        <li><span style="font-weight: bold; font-style: italic; padding-left: 4px; background-color:#fff;"><?php echo __('By Periods'); ?></span></li>
        <?php foreach (array(
                'date' => __('Date'),
                'day' => __('Day'),
                'week' => __('Week'),
                'month' => __('Month'),
                'quarter' => __('Quarter'),
                'year' => __('Year'),
            ) as $query => $label): ?>
        <li><a href="<?php echo url('curator-monitor', array('by' => $query)); ?>"><?php echo $label; ?></a></li>
        <?php endforeach; ?>
        <li><span style="font-weight: bold; font-style: italic; padding-left: 4px; background-color:#fff;"><?php echo __('Status'); ?></span></li>
        <?php
        $statusElements = get_view()->monitor()->getStatusElementNamesById(true, null, true);
        foreach ($statusElements as $query => $statusElement): ?>
        <li><a href="<?php echo url('curator-monitor', array('element' => $query)); ?>"><?php echo $statusElement; ?></a></li>
        <?php endforeach; ?>
        <li><span style="font-weight: bold; font-style: italic; padding-left: 4px; background-color:#fff;"><?php echo __('Date'); ?></span></li>
        <li><a href="<?php echo url('curator-monitor', array('added' => date('Y-m-d', time()))); ?>"><?php echo __('Today'); ?></a></li>
        <li><a href="<?php echo url('curator-monitor', array('since' => date('Y-m-d', strtotime('monday this week')))); ?>"><?php echo __('This Week'); ?></a></li>
        <li><a href="<?php echo url('curator-monitor', array('since' => date('Y-m', time()) . '-01')); ?>"><?php echo __('This Month'); ?></a></li>
        <li><a href="<?php echo url('curator-monitor', array('since' => date('Y', time()) . '-01-01')); ?>"><?php echo __('This Year'); ?></a></li>
        <li><a href="<?php echo url('curator-monitor', array('added' => date('Y-m-d', strtotime('-1 day')))); ?>"><?php echo __('Yesterday'); ?></a></li>
        <li><a href="<?php echo url('curator-monitor', array(
            'since' => date('Y-m-d', strtotime('monday last week')),
            'until' => date('Y-m-d', strtotime('sunday last week')),
        )); ?>"><?php echo __('Last Week'); ?></a></li>
        <li><a href="<?php echo url('curator-monitor', array(
            'since' => date('Y-m', strtotime('1 month ago')) . '-01',
            'until' => date('Y-m-d', strtotime('last day of 1 month ago')),
        )); ?>"><?php echo __('Last Month'); ?></a></li>
        <li><a href="<?php echo url('curator-monitor', array(
            'since' => date('Y', strtotime('1 year ago')) . '-01-01',
            'until' => date('Y', strtotime('1 year ago')) . '-12-31',
        )); ?>"><?php echo __('Last Year'); ?></a></li>
    </ul>
    </li>
</ul>
