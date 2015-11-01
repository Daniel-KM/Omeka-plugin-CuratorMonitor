<?php
/**
 * Curator Monitor search and report generation form.
 *
 * @package CuratorMonitor
 */
class CuratorMonitor_Form_Search extends Omeka_Form
{
    /**
     * Construct the report generation form.
     *
     * @return void
     */
    public function init()
    {
        parent::init();

        try {
            $collectionOptions = $this->_getCollectionOptions();
            $userOptions = $this->_getUserOptions();
            $byOptions = $this->_getByOptions();
            $elementOptions = $this->_getElementOptions();
            $exportOptions = $this->_getexportOptions();
        } catch (Exception $e) {
            throw $e;
        }

        // Item.
        $this->addElement('text', 'item', array(
            'label' => __('Item'),
            'description' => __("The item or range of items to search status for."),
            'value' => '',
            'validators' => array(
                'digits',
            ),
            'required' => false,
        ));

        // Collection.
        $this->addElement('select', 'collection', array(
            'label' => __('Collection'),
            'description' => __("The collection whose items' status will be retrieved."),
            'value' => '',
            'validators' => array(
                'digits',
            ),
            'required' => false,
            'multiOptions' => $collectionOptions,
        ));

        // User(s).
        $this->addElement('select', 'user', array(
            'label' => __('User(s)'),
            'description' => __('All users whose edits will be retrieved.'),
            'value' => '',
            'validators' => array(
                'digits',
            ),
            'required' => false,
            'multiOptions' => $userOptions,
        ));

        // Elements.
        $this->addElement('select', 'element', array(
            'label' => __('Element'),
            'description' => __('Limit response to the selected unrepeatable status.'),
            'value' => '',
            'validators' => array(
                'digits',
            ),
            'required' => false,
            'multiOptions' => $elementOptions,
        ));

        // Date by.
        $this->addElement('select', 'by', array(
            'label' => __('By Period'),
            'description' => __("The group of dates to compute status."),
            'value' => '',
            'validators' => array(
                'alnum',
            ),
            'required' => false,
            'multiOptions' => $byOptions,
        ));

        // Date since.
        $this->addElement('text', 'since', array(
            'label' => __('Start Date'),
            'description' => __('The earliest date from which to retrieve status.'),
            'value' => 'YYYY-MM-DD',
            'style' => 'max-width: 120px;',
            'required' => false,
            'validators' => array(
                array(
                    'Date',
                    false,
                    array(
                        'format' => 'yyyy-mm-dd',
                    )
                )
            )
        ));

        // Date until.
        $this->addElement('text', 'until', array(
            'label' => __('End Date'),
            'description' => __('The latest date, included, which to retrieve status.'),
            'value' => 'YYYY-MM-DD',
            'style' => 'max-width: 120px;',
            'required' => false,
            'validators' => array(
                array(
                    'Date',
                    false,
                    array(
                        'format' => 'yyyy-mm-dd',
                    )
                )
            )
        ));

        // Output.
        $this->addElement('radio', 'export', array(
            'label' => __('Output'),
            'value' => '',
            'validators' => array(
                'alnum',
            ),
            'required' => false,
            'multiOptions' => $exportOptions,
        ));

        $this->addElement('checkbox', 'export-headers', array(
            'label' => __('Include headers'),
            'value' => true,
            'required' => false,
        ));

        if (version_compare(OMEKA_VERSION, '2.2.1') >= 0) {
            $this->addElement('hash', 'curator_monitor_token');
        }

        // Button for submit.
        $this->addElement('submit', 'submit-search', array(
            'label' => __('Report'),
        ));

        // TODO Add decorator as in "items/search-form.php" for scroll.

        // Display Groups.
        $this->addDisplayGroup(array(
            'by',
            'since',
            'until',
            'item',
            'collection',
            'user',
            'element',
            'export',
            'export-headers'
        ), 'fields');

        $this->addDisplayGroup(array(
                'submit-search'
            ),
            'submit_buttons'
        );
    }

    /**
     * Retrieve Collections as selectable option list.
     *
     * @return array $collections An associative array of the collection IDs and
     * titles.
     */
    protected function _getCollectionOptions()
    {
        return get_table_options('Collection', __('All Collections'));
    }

    /**
     * Retrieve Omeka Admin Users as selectable option list
     *
     * @return array $users  An associative array of the user ids and usernames
     * of all omeka users with admin privileges.
     */
    protected function _getUserOptions()
    {
        $options = array(
            '' => __('All Users'),
        );

        try {
            $acl = get_acl();
            $roles = $acl->getRoles();
            foreach ($roles as $role) {
                $users = get_records('User', array(
                        'role' => $role,
                    ), '0');
                foreach ($users as $user) {
                    $options[$user->id] = $user->name . ' (' . $role . ')';
                }
            }
        } catch (Exception $e) {
            throw ($e);
        }

        return $options;
    }

    /**
     * Retrieve possible "by" dates as selectable option list.
     *
     * @see Table_HistoryLogChange
     * @return array $options An associative array of the "by" dates.
     */
    protected function _getByOptions()
    {
        return array(
            '' => __(' (All Dates (Synthesis)'),
            'date' => __('Date'),
            // 'dayname' => __('Day Name'),
            'day' => __('Day'),
            // 'dayofmonth' => __('Day of Month'),
            // 'dayofweek' => __('Day of Week'),
            // 'dayofyear' => __('Day of Year'),
            // 'hour' => __('Hour'),
            // 'minute' => __('Minute'),
            'month' => __('Month'),
            // 'monthname' => __('Month Name'),
            'quarter' => __('Quarter'),
            // 'second' => __('Second'),
            'week' => __('Week'),
            // 'weekday' => __('Day of Week'),
            // 'weekofyear' => __('Week of Year'),
            'year' => __('Year'),
            // 'yearweek' => __('Week of Year'),
        );
    }

    /**
     * Retrieve possible elements as a selectable option list.
     *
     * @todo Add deleted elements that are used in old entries.
     *
     * @return array $options An associative array of the elements.
     */
    protected function _getElementOptions()
    {
        $elements = get_view()->monitor()->getStatusElementNamesById(true, null, true);
        return array('' => 'All Monitor Status') + $elements;
    }

    /**
     * Retrieve possible exports as a selectable option list.
     *
     * @return array $options An associative array of the format.
     */
    protected function _getexportOptions()
    {
        $options = array(
            '' => __('Normal display'),
            'csv' => __('csv (with tabulations)'),
            'fods' => __('fods (Flat OpenDocument Spreadsheet)'),
        );

        return $options;
    }
}
