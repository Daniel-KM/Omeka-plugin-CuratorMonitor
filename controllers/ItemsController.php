<?php
/**
 * @package Omeka\Controller
 */
class CuratorMonitor_ItemsController extends Omeka_Controller_AbstractActionController
{
    /**
     * Browse the items.  Encompasses search, pagination, and filtering of
     * request parameters.  Should perhaps be split into a separate
     * mechanism.
     *
     * @return void
     */
    public function browseAction()
    {
        $terms = $this->getParam('terms');
        $terms = array_filter($terms, function ($v) { return strlen($v) > 0; });
        if ($terms) {
            $advanced = $this->getParam('advanced') ?: array();

            $view = get_view();

            // Get all elements, even when they are not all used.
            $statusElements = $view->monitor()->getStatusElements();

            // Convert terms into an advanced items search.
            foreach ($terms as $elementId => $value) {
                if ($value == 'is-empty') {
                    $advanced[] = array(
                        'element_id' => $elementId,
                        'type' =>'is empty',
                    );
                }
                elseif ($value == 'is-not-empty') {
                    $advanced[] = array(
                        'element_id' => $elementId,
                        'type' =>'is not empty',
                    );
                }
                // Curator Monitor element.
                elseif (isset($statusElements[$elementId])) {
                    $advanced[] = array(
                        'element_id' => $elementId,
                        'type' => 'is exactly',
                        'terms' => $value,
                    );
                }
                // Standard element added via the hook.
                else {
                    $advanced[] = array(
                        'element_id' => $elementId,
                        'type' => 'contains',
                        'terms' => $value,
                    );
                }
            }

            // Zend re-reads from GET/POST on every getParams() so we need to
            // also remove these.
            $_GET['advanced'] = $advanced;
            $_POST['advanced'] = $advanced;
        }
        return $this->forward('browse', 'items', 'default', array(
            'module' => null,
            'controller' => 'items',
            'action' => 'browse',
            'record_type' => 'Item',
        ));
    }
}
