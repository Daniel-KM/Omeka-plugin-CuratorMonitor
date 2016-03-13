<?php
/**
 * Manages quick search with "terms".
 *
 * @see ItemsController
 *
 * @package Omeka\Controller
 */
class CuratorMonitor_ItemsController extends Omeka_Controller_AbstractActionController
{
    protected $_autoCsrfProtection = true;

    protected $_browseRecordsPerPage = self::RECORDS_PER_PAGE_SETTING;

    public function init()
    {
        $this->_helper->db->setDefaultModelName('Item');
    }

    /**
     * Browse the items.  Encompasses search, pagination, and filtering of
     * request parameters.  Should perhaps be split into a separate
     * mechanism.
     *
     * @return void
     */
    public function browseAction()
    {
        //Must be logged in to view items specific to certain users
        if ($this->_getParam('user') && !$this->_helper->acl->isAllowed('browse', 'Users')) {
            $this->_setParam('user', null);
            // Zend re-reads from GET/POST on every getParams() so we need to
            // also remove these.
            unset($_GET['user'], $_POST['user']);
        }

        $terms = $this->getParam('terms') ?: array();
        $terms = array_filter($terms, function ($v) { return strlen($v) > 0; });
        if ($terms) {
            $advanced = $this->getParam('advanced') ?: array();

            $view = get_view();

            // Get all elements, even when they are not all used.
            $statusElements = $view->monitor()->getStatusElements();

            // Convert terms into an advanced items search.
            $filters = array();
            foreach ($terms as $elementId => $value) {
                if ($value == 'is-empty') {
                    $filters[$elementId] = array(
                        'element_id' => $elementId,
                        'type' =>'is empty',
                    );
                }
                elseif ($value == 'is-not-empty') {
                    $filters[$elementId] = array(
                        'element_id' => $elementId,
                        'type' =>'is not empty',
                    );
                }
                // Curator Monitor element.
                elseif (isset($statusElements[$elementId])) {
                    $filters[$elementId] = array(
                        'element_id' => $elementId,
                        'type' => 'is exactly',
                        'terms' => $value,
                    );
                }
                // Standard element added via the hook.
                else {
                    $filters[$elementId] = array(
                        'element_id' => $elementId,
                        'type' => 'contains',
                        'terms' => $value,
                    );
                }
            }

            if ($filters) {
                $filterIds = array_keys($filters);
                foreach ($advanced as $key => $value) {
                    if (in_array($value['element_id'], $filterIds)) {
                        unset($advanced[$key]);
                    }
                }
                $advanced += array_values($filters);

                unset($_GET['terms']);
                unset($_POST['terms']);
            }

            // Zend re-reads from GET/POST on every getParams() so we need to
            // also remove these.
            $_GET['advanced'] = $advanced;
            $_POST['advanced'] = $advanced;
        }

        parent::browseAction();
    }

    protected function _getBrowseDefaultSort()
    {
        return array('added', 'd');
    }
}
