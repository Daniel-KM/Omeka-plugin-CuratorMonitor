<?php
/**
 * Omeka
 *
 * @copyright Copyright 2007-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * Show the currently-active filters for a search/browse.
 *
 * @package Omeka\View\Helper
 */
class CuratorMonitor_View_Helper_ItemSearchFilters extends Omeka_View_Helper_ItemSearchFilters
{
    /**
     * Get a list of the currently-active filters for item browse/search.
     *
     * @param array $params Optional array of key-value pairs to use instead of
     *  reading the current params from the request.
     * @return string HTML output
     */
    public function itemSearchFilters(array $params = null)
    {
        $html = parent::itemSearchFilters($params);
        if (is_admin_theme()) {
            $html .= $this->view->itemAdvancedFilters($params);
        }
        return $html;
    }
}
