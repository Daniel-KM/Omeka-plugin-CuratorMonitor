<?php
// Manage all upgrade processes (make main file lighter).

if (version_compare($oldVersion, '2.3', '<')) {
    set_option('curator_monitor_elements_default', json_encode($this->_options['curator_monitor_elements_default']));
}

if (version_compare($oldVersion, '2.3.1', '<')) {
    $this->_addNewElements();
}

if (version_compare($oldVersion, '2.4.1', '<')) {
    $adminItemsBrowse = get_option('curator_monitor_admin_items_browse');
    $adminItemsBrowse['search'] = $adminItemsBrowse['filter'];
    set_option('curator_monitor_admin_items_browse', $adminItemsBrowse);
}

if (version_compare($oldVersion, '2.4.2', '<')) {
    $this->_addNewElements();

    $defaultTerms = json_decode(get_option('curator_monitor_elements_default'), true) ?: array();
    $elementTable = $db->getTable('Element');
    foreach (array('Publish Record', 'Publish Images', 'Publish Transcription') as $elementName) {
        $element = $elementTable->findByElementSetNameAndElementName($this->_elementSetName, $elementName);
        if ($element) {
            unset($defaultTerms[$element->id]);
        }
    }
    set_option('curator_monitor_elements_default', json_encode($defaultTerms));
}

if (version_compare($oldVersion, '2.4.3', '<')) {
    $this->_addNewElements();
}

if (version_compare($oldVersion, '2.4.4', '<')) {
    $this->_updateVocab(array('Metadata Status'));
    $this->_removeOldElements(array('Publish Metadata'));
}

if (version_compare($oldVersion, '2.4.5', '<')) {
    $this->_updateVocab(array('Metadata Status'));
}
