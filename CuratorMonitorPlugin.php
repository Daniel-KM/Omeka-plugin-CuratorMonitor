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

        // Process.
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

        // Add terms for simple vocabs and the flag "unique".
        $es = $elementSet->getElements();
        foreach ($es as $e) {
            foreach ($elements as $key => $element) {
                if ($element['name'] == $e->name) {
                    if (!empty($element['terms'])) {
                        $terms = new SimpleVocabTerm();
                        $terms->element_id = $e->id;
                        $terms->terms = implode(PHP_EOL, $element['terms']);
                        $terms->save();
                    }
                    // Set / unset the flag for unique.
                    if (!empty($element['unique'])) {
                        $this->_options['curator_monitor_elements_unique'][$e->id] = true;
                    }
                    // Remove the flag.
                    else {
                        unset($this->_options['curator_monitor_elements_unique'][$e->id]);
                    }
                }
            }
        }

        $this->_options['curator_monitor_elements_unique'] = json_encode($this->_options['curator_monitor_elements_unique']);

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
        $listUnique = $view->monitor()->getStatusElementNamesById(null, true);
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
}
