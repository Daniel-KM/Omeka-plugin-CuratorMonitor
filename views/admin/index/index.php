<?php
$pageTitle = __('Curator Monitor');
echo head(array(
    'title' => $pageTitle,
    'bodyclass' => 'curator-monitor index',
));
?>
<div id="primary">
<?php echo flash(); ?>
    <h2><?php
        echo  __('Total published items: %d / %d', get_db()->getTable('Item')->count(array('public' => 1)), total_records('Item'));
    ?></h2>
    <div class="table-actions">
        <a href="<?php echo html_escape(url('curator-monitor/index/search')); ?>" class="add button small green"><?php echo __('Advanced Status'); ?></a>
    </div>
    <div><?php echo common('quick-filters'); ?></div>
    <div id="item-filters">
        <ul>
            <?php if (!empty($params['since'])): ?>
            <li><?php echo __('Since %s', $params['since']); ?></li>
            <?php endif; ?>
            <?php if (!empty($params['until'])): ?>
            <li><?php echo __('Until %s', $params['until']); ?></li>
            <?php endif; ?>
            <?php if (!empty($params['by'])): ?>
            <li><?php echo __('By %s', $byDates[ucfirst($params['by'])]); ?></li>
            <?php endif; ?>
            <?php if (!empty($params['user'])): ?>
            <li><?php
                $user = get_record_by_id('User', $params['user']);
                echo $user
                    ? __('User: %s', $user->username)
                    : __('User: #%s', $params['user']);
            ?></li>
            <?php endif; ?>
            <?php if (!empty($params['element'])): ?>
            <li><?php
                $statusElements = $this->monitor()->getStatusElements(true, null, true);
                if (count($params['element']) < count($statusElements)):
                    $list = array();
                    foreach ($params['element'] as $elementId):
                        $element = get_record_by_id('Element', $params['element']);
                        $list[] = $element
                            ? __('Element %s (%s)', $element->name, $element->set_name)
                            : __('Element #%s', $elementId);
                    endforeach;
                    echo implode('; ', $list);
                endif;
            ?></li>
            <?php endif; ?>
        </ul>
    </div>
<?php
if (!empty($results)):
$statusElements = $this->monitor()->getStatusElements();
foreach ($results as $elementId => $result):
    if (!isset($statusElements[$elementId]['element'])):
        continue;
    endif;
    $element = $statusElements[$elementId]['element'];
?>
<section class="ten columns alpha omega">
    <div class="panel">
        <h2><?php echo $element->name; ?></h2>
        <?php $published = 0; ?>
        <table id="curator-monitor-stats-<?php echo $element->id; ?>" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <?php
                    $browseHeadings = array();
                    $headers = array_keys(reset($result));
                    foreach ($headers as $header):
                        $browseHeadings[strlen($header) > 0 ? $header : __('Not Set')] = null;
                    endforeach;
                    echo browse_sort_links($browseHeadings, array('link_tag' => 'th scope="col"', 'list_tag' => ''));
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $key = 0;
                foreach ($result as $period => $row): ?>
                <tr class="curator-monitor-stat <?php echo ++$key%2 == 1 ? 'odd' : 'even'; ?>">
                    <?php
                        // Replace values "0" by empty string if wanted (default
                        // for "by" queries).
                        // $row = array_map(function($value) { return $value ?: '';}, $row);
                        echo '<td>' . implode('</td><td>', $row) . '</td>';
                    ?>
                </tr>
                <?php endforeach; ?>
                <tr>
                <?php foreach ($headers as $key => $header): ?>
                    <td>
                    <?php if (!in_array($header, $byDates)):
                        echo link_to_items_browse(__('Browse'),
                            array('advanced' => array(array('element_id' => $element->id, 'type' => 'is exactly', 'terms' => $header))),
                            array('class' => 'button small blue'));
                        if ($statusElements[$elementId]['steppable'] && $key < count($headers) - 1):
                            printf('<a href="%s" class="button small red">%s</a>',
                                html_escape(url('curator-monitor/index/stage', array('element' => $element->id, 'term' => $header))),
                                __('Stage'));
                        endif;
                    endif; ?>
                   </td>
                <?php endforeach; ?>
                </tr>
            </tbody>
        </table>
    </div>
</section>
<?php endforeach; ?>
<?php fire_plugin_hook('curator_monitor_stat_element', array('view' => $this)); ?>
<?php else: ?>
    <br class="clear" />
    <p><?php
        echo __('Your query returned no result.');
        $statusElements = $this->monitor()->getStatusElements(true, null, true);
        if (empty($statusElements)):
            echo ' ' . __('There is no element that can be used as a status (a Monitor element with terms and unrepeatable).');
        endif;
    ?></p>
<?php endif; ?>
</div>
<?php echo foot();
