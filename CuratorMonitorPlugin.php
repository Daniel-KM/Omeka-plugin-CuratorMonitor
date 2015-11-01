<?php
/**
 * Curator Monitor
 *
 * @copyright Copyright 2015 Daniel Berthereau
 * @license https://www.cecill.info/licences/Licence_CeCILL_V2.1-en.html
 */

/**
 * The Curator Monitor plugin.
 *
 * @package Omeka\Plugins\CuratorMonitor
 */
class CuratorMonitorPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_elementSetName = 'Monitor';

    /**
     * @var array Hooks for the plugin.
     */
    protected $_hooks = array(
        'initialize',
        'install',
        'uninstall',
        'uninstall_message',
        'define_acl',
        'config_form',
        'config',
        'admin_head',
        'admin_element_sets_form_each',
        // No hook to save element set, but a hook is fired for each element.
        'after_save_element',
        'before_delete_element_set',
    );

    /**
     * @var array Filters for the plugin.
     */
    protected $_filters = array(
        'admin_navigation_main',
        'admin_items_form_tabs',
    );

    /**
     * @var array Options and their default values.
     */
    protected $_options = array(
        'curator_monitor_elements_unique' => array(),
        'curator_monitor_elements_steppable' => array(),
    );

    /**
     * Initialize this plugin.
     */
    public function hookInitialize()
    {
        // Add translation.
        add_translation_source(dirname(__FILE__) . '/languages');
    }

    /**
     * Install the plugin.
     */
    public function hookInstall()
    {
        // Load elements to add.
        require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'elements.php';

        $elementSet = get_record('ElementSet', array('name' => $elementSetMetadata['name']));
        if ($elementSet) {
            throw new Omeka_Plugin_Exception(__('An element set by the name "%s" already exists. You must delete that element set before to install this plugin.',
                $elementSetMetadata['name']));
        }

        // Process the installation.
        foreach ($elementSetMetadata['elements'] as &$element) {
            $element['name'] = $element['label'];
        }
        // Require the remove of the above reference to use the same name below.
        unset($element);
        $elements = $elementSetMetadata['elements'];
        unset($elementSetMetadata['elements']);

        $elementSet = insert_element_set($elementSetMetadata, $elements);

        if (!$elementSet) {
            throw new Omeka_Plugin_Exception(__('Unable to build the element set "%s".', $elementSetMetadata['name']));
        }

        // Add terms for simple vocabs and the flags "unique" and "steppable".
        $es = $elementSet->getElements();
        foreach ($es as $e) {
            foreach ($elements as $key => $element) {
                if ($element['name'] == $e->name) {
                    // Set / unset the flag for unique.
                    if (empty($element['unique'])) {
                        unset($this->_options['curator_monitor_elements_unique'][$e->id]);
                    }
                    // Add the flag.
                    else {
                        $this->_options['curator_monitor_elements_unique'][$e->id] = true;
                    }

                    // Set / unset the flag for process.
                    if (empty($element['steppable'])) {
                        unset($this->_options['curator_monitor_elements_steppable'][$e->id]);
                    }
                    // Add the flag.
                    else {
                        $this->_options['curator_monitor_elements_steppable'][$e->id] = true;
                    }

                    if (!empty($element['terms'])) {
                        $vocabTerm = new SimpleVocabTerm();
                        $vocabTerm->element_id = $e->id;
                        $vocabTerm->terms = implode(PHP_EOL, $element['terms']);
                        $vocabTerm->save();
                    }
                }
            }
        }

        $this->_options['curator_monitor_elements_unique'] = json_encode($this->_options['curator_monitor_elements_unique']);
        $this->_options['curator_monitor_elements_steppable'] = json_encode($this->_options['curator_monitor_elements_steppable']);

        $this->_installOptions();
    }

    /**
     * Uninstall the plugin.
     */
    public function hookUninstall()
    {
        $elementSet = $this->_db->getTable('ElementSet')
            ->findByName($this->_elementSetName);

        if (!empty($elementSet)) {
            $elements = $elementSet->getElements();
            foreach ($elements as $element) {
                $simpleVocabTerm = $this->_db->getTable('SimpleVocabTerm')
                    ->findByElementId($element->id);
                if ($simpleVocabTerm) {
                    $simpleVocabTerm->delete();
                }
                $element->delete();
            }
            $elementSet->delete();
        }

        $this->_uninstallOptions();
    }

    /**
     * Display the uninstall message.
     */
    public function hookUninstallMessage()
    {
        echo __('%sWarning%s: This will remove all the Monitor elements added by this plugin and permanently delete all element texts entered in those fields.%s', '<p><strong>', '</strong>', '</p>');
    }

    /**
     * Define the plugin's access control list.
     *
     * @param array $args Parameters supplied by the hook
     * @return void
     */
    public function hookDefineAcl($args)
    {
        $args['acl']->addResource('CuratorMonitor_Index');
    }

    /**
     * Shows plugin configuration page.
     *
     * @return void
     */
    public function hookConfigForm($args)
    {
        $view = get_view();
        echo $view->partial(
            'plugins/curator-monitor-config-form.php'
        );
    }

    /**
     * Processes the configuration form.
     *
     * @return void
     */
    public function hookConfig($args)
    {
        $post = $args['post'];
        foreach ($this->_options as $optionKey => $optionValue) {
            if (isset($post[$optionKey])) {
                set_option($optionKey, $post[$optionKey]);
            }
        }
    }

    /**
     * Hook for admin head.
     *
     * @return void
     */
    public function hookAdminHead()
    {
        queue_css_string('.last-change {font-size: 12px; font-style: italic;}');
    }

    /**
     * Hook to manage element set.
     *
     * @param array @args
     * @return void
     */
    public function hookAdminElementSetsFormEach($args)
    {
        $elementSet = $args['element_set'];
        if ($elementSet->name != $this->_elementSetName) {
            return;
        }

        $element = $args['element'];
        $view = $args['view'];

        $statusElement = $view->monitor()->getStatusElement($element->id);

        $html = '';

        // Add unique.
        $html .= $view->formLabel('elements[' . $element->id. '][unique]', __('Unrepeatable'));
        $html .= $view->formCheckbox('elements[' . $element->id. '][unique]',
            true, array('checked' => (boolean) $statusElement['unique']));

        // Add process.
        $html .= $view->formLabel('elements[' . $element->id. '][steppable]', __('Steps of a workflow'));
        $html .= $view->formCheckbox('elements[' . $element->id. '][steppable]',
            true, array('checked' => (boolean) $statusElement['steppable']));

        // Add vocabulary terms.
        $html .= $view->formLabel('elements[' . $element->id. '][terms]', __('Terms'));
        $html .= $view->formTextarea('elements[' . $element->id. '][terms]',
            implode(PHP_EOL, $statusElement['terms']),
            array('placeholder' => __('Ordered list of concise terms, one by line'), 'rows' => '5', 'cols' => '10'));

        echo $html;
    }

    /**
     * Hook used after save element.
     *
     * @param array $args
     */
    public function hookAfterSaveElement($args)
    {
        $record = $args['record'];

        // There is no post in this view.
        $view = get_view();

        $statusElements = $view->monitor()->getStatusElements();
        if (!isset($statusElements[$record->id])) {
            return;
        }

        // Update of an existing element.
        if (empty($args['insert'])) {
            // Get post values.
            $request = Zend_Controller_Front::getInstance()->getRequest();
            $elements = $request->getParam('elements');
            $postElement = $elements[$record->id];

            // Set / unset unique.
            $this->_setUnique($record, $postElement['unique']);

            // Set / unset process.
            $this->_setSteppable($record, $postElement['steppable']);

            // Set / unset terms.
            $this->_setTerms($record, $postElement['terms']);
        }
    }

    /**
     * Hook used before delete element set.
     *
     * @param array $args
     */
    public function hookBeforeDeleteElementSet($args)
    {
        $record = $args['record'];
        if ($record->name == $this->_elementSetName) {
            throw new Exception(__('The element set "%s" can be removed only when the plugin "Curator Monitor" is uninstalled.', $this->_elementSetName));
        }
    }

    /**
     * Add the Curator Monitor link to the admin main navigation.
     *
     * @param array $nav Navigation array.
     * @return array $filteredNav Filtered navigation array.
     */
    public function filterAdminNavigationMain($nav)
    {
        $nav[] = array(
            'label' => __('Curator Monitor'),
            'uri' => url('curator-monitor'),
            'resource' => 'CuratorMonitor_Index',
            'privilege' => 'index',
        );
        return $nav;
    }

    /**
     * Modify the Monitor tab in the admin->edit page.
     *
     * @todo Use the controller (see SimpleVocab). Currently, use a hack is fine
     * because the admin theme can't be really changed.
     *
     * @return array of tabs
     */
    public function filterAdminItemsFormTabs($tabs, $args)
    {
        $tab = $tabs[$this->_elementSetName];
        $record = $args['item'];
        $view = get_view();

        // This hack uses a simple preg_replace.
        $patterns = array();
        $replacements = array();

        // Indicate the last change for each element of the Monitor element set.
        $listElements = $view->monitor()->getStatusElementNamesById();
        $lastChanges = $this->_db->getTable('HistoryLogChange')
            ->getLastChanges($record, array_keys($listElements), true);

        // Add a message only for created/updated elements.
        foreach ($lastChanges as $change) {
            $pattern = sprintf('(<input type="submit" name="add_element_(%s)" .*? class="add-element">)',
                $change->element_id);
            $patterns[] = '/' . $pattern . '/';
            $replacement = '$1<p class="last-change">';
            switch ($change->type) {
                case HistoryLogChange::TYPE_CREATE:
                    $replacement .= __('Set by %s on %s', $change->displayUser(), $change->displayAdded());
                    break;
                case HistoryLogChange::TYPE_UPDATE:
                    $replacement .= __('Updated by %s on %s', $change->displayUser(), $change->displayAdded());
                    break;
                case HistoryLogChange::TYPE_DELETE:
                    $replacement .= __('Removed by %s on %s', $change->displayUser(), $change->displayAdded());
                    break;
                case HistoryLogChange::TYPE_NONE:
                default:
                    $replacement .= __('Logged by %s on %s', $change->displayUser(), $change->displayAdded());
                    break;
            }
            $replacement .= '</p>';
            $replacements[] = $replacement;
        }

        // Remove all buttons "Add element" and "Remove element" for non
        // repeatable elements.
        $listUnique = $view->monitor()->getStatusElementNamesById(true);
        $pattern =
            // This first part of pattern removes all listed buttons "Add element".
            '(' . sprintf('<input type="submit" name="add_element_(%s)" .*? class="add-element">',
                implode('|', array_keys($listUnique)))  . ')'
            // The second part allows to keep the dropdown.
            . '(.*?)'
            // The last part removes all listed buttons "Remove element".
            . '(' . '<div class="controls"><input type="submit" name="" value="Remove" class="remove-element red button"><\/div>' . ')';
        // The pattern is multiline.
        $patterns[] = '/' . $pattern . '/s';
        $replacements[] = '$3';

        $tab = preg_replace($patterns, $replacements, $tab);

        $tabs[$this->_elementSetName] = $tab;
        return $tabs;
    }

    /**
     * Set / unset a value in the list of unique fields.
     *
     * @param Record|integer $record
     * @param boolean $isUnique
     * @return void
     */
    protected function _setUnique($record, $isUnique)
    {
        $this->_setOptionInList($record, $isUnique, 'curator_monitor_elements_unique');
    }

    /**
     * Set / unset a value in the list of steppable fields.
     *
     * @param Record|integer $record
     * @param boolean $isSteppable
     * @return void
     */
    protected function _setSteppable($record, $isSteppable)
    {
        $this->_setOptionInList($record, $isSteppable, 'curator_monitor_elements_steppable');
    }

    /**
     * Set / unset a value in an option list.
     *
     * @param Record|integer $record
     * @param boolean $value
     * @param string $optionList
     * @return void
     */
    protected function _setOptionInList($record, $value, $optionList)
    {
        $recordId = (integer) (is_object($record) ? $record->id : $record);
        $value = (boolean) $value;
        $list = json_decode(get_option($optionList), true);
        // Set the flag as key.
        if ($value) {
            $list[$recordId] = true;
        }
        // Remove the flag.
        else {
            unset($list[$recordId]);
        }
        set_option($optionList, json_encode($list));
    }

    /**
     * Set / unset a list of terms for an element.
     *
     * @param Record|integer $record
     * @param array|string $terms
     * @return void
     */
    protected function _setTerms($record, $terms)
    {
        $recordId = (integer) (is_object($record) ? $record->id : $record);
        if (is_string($terms)) {
            $terms = explode(PHP_EOL, trim($terms));
        }

        $terms = array_map('trim', $terms);
        $terms = array_filter($terms, function($value) { return strlen($value) > 0; });
        $terms = array_unique($terms);
        $statusElement = get_view()->monitor()->getStatusElement($recordId);

        // Check if an update is needed.
        if ($statusElement['terms'] === $terms) {
            return;
        }

        $vocabTerm = $statusElement['vocab'];
        // Remove terms.
        if (empty($terms)) {
            if (!empty($vocabTerm)) {
                $vocabTerm->delete();
            }
        }

        // Update or create a simple vocab term.
        else {
            if (empty($vocabTerm)) {
                $vocabTerm = new SimpleVocabTerm();
                $vocabTerm->element_id = $recordId;
            }
            $vocabTerm->terms = implode(PHP_EOL, $terms);
            $vocabTerm->save();
        }
    }
}
