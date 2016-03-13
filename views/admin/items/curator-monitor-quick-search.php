<h4 id="curator-monitor-quick-search"><?php echo __('Quick Search >'); ?></h4>
<div id="curator-monitor-quick-search-div" class="quick-search" style="display:none">
<?php
// From admin/themes/default/items/search-form.php.
// items_search_form() is not used, because this is a simplified query.

if (!empty($formActionUri)):
    $formAttributes['action'] = $formActionUri;
else:
    $formAttributes['action'] = url(array('module' => 'curator-monitor', 'controller' => 'items', 'action' => 'browse'));
endif;
$formAttributes['method'] = 'GET';

// If the form has been submitted, retain the number of search fields used and
// rebuild the form. This doesn't take care of advanced fields..
if (!empty($_GET['terms'])):
    $terms = $_GET['terms'];
else:
    $terms = array(array('field'=>'','type'=>'','value'=>''));
endif;

?>
<form <?php echo tag_attributes($formAttributes); ?>>
    <div id="search-narrow-by-fields" class="field">
<?php
// The output go to curator-monitor controller, where the query is standardized
// as an adavanced item search, and forwarded to items/search.
foreach ($statusTermsElements as $elementId => $statusElement): ?>
        <div class="three columns alpha">
            <?php echo $this->formLabel('terms-' . $elementId, $statusElement['name']); ?>
        </div>
        <div class="seven columns omega inputs">
            <div class="search-entry">
                <?php
                $options = array('' => __('Filter By'));
                $options[__('Status')] = array_combine($statusElement['terms'], $statusElement['terms']);
                $options[__('Contains')] = array(
                    'is-empty' => __('is empty'),
                    'is-not-empty' => __('is not empty'),
                );
                echo $this->formSelect(
                    "terms[$elementId]",
                    isset($terms[$elementId]) ? $terms[$elementId] : '',
                    array(
                        'title' => $statusElement['name'],
                        'id' => 'terms-' . $elementId,
                        'class' => 'advanced-search-type'
                    ),
                    $options
                ); ?>
            </div>
        </div>
<?php endforeach;
foreach ($statusNoTermElements as $elementId => $statusElement): ?>
        <div class="three columns alpha">
            <?php echo $this->formLabel('terms-' . $elementId, $statusElement['name']); ?>
        </div>
        <div class="seven columns omega inputs">
            <div class="search-entry">
                <?php
                $options = array('' => __('Filter By'));
                $options += array(
                    'is-empty' => __('is empty'),
                    'is-not-empty' => __('is not empty'),
                );
                echo $this->formSelect(
                    "terms[$elementId]",
                    isset($terms[$elementId]) ? $terms[$elementId] : '',
                    array(
                        'title' => $statusElement['name'],
                        'id' => 'terms-' . $elementId,
                        'class' => 'advanced-search-type'
                    ),
                    $options
                ); ?>
            </div>
        </div>
<?php endforeach;
        fire_plugin_hook('curator_monitor_items_browse_search', array('view' => $this, 'terms' => $terms)); ?>
    </div>
    <div class="offset-by-three three columns">
        <div id="quick-search-save">
            <input type="submit" class="submit big green button" name="submit_search" id="submit_search_advanced" value="<?php echo __('Quick Search'); ?>">
        </div>
    </div>
</form>
</div>
<br class="clear" />