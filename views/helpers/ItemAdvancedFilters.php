<?php
/**
 * Omeka
 *
 * @copyright Copyright 2007-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * Add buttons to filter by date (year) and status fields of Curator Monitor.
 *
 * @package Omeka\View\Helper
 */
class CuratorMonitor_View_Helper_ItemAdvancedFilters extends Zend_View_Helper_Abstract
{
    /**
     * Add buttons to filter by date (year) and status fields of Curator Monitor.
     *
     * @param array $params Optional array of key-value pairs to use instead of
     *  reading the current params from the request.
     * @return string HTML output
     */
    public function itemAdvancedFilters(array $params = null)
    {
        $current = json_decode(get_option('curator_monitor_admin_items_browse'), true) ?: array();
        if (!empty($current['filter']['Monitor'])) {
            $statusTermsElements = $this->view->monitor()->getStatusElements(null, null, true);
            $statusTermsElements = array_intersect_key($statusTermsElements, $current['filter']['Monitor']);
            $statusNoTermElements = $this->view->monitor()->getStatusElements(null, null, false);
            $statusNoTermElements = array_intersect_key($statusNoTermElements, $current['filter']['Monitor']);
            return $this->view->partial(
                'items/curator-monitor-advanced-filters.php',
                array(
                    'statusTermsElements' => $statusTermsElements,
                    'statusNoTermElements' => $statusNoTermElements,
            ));
        }
    }
}
