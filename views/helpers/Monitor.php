<?php
/**
 * Helpers for CuratorMonitor.
 *
 * @package CuratorMonitor
 */
class CuratorMonitor_View_Helper_Monitor extends Zend_View_Helper_Abstract
{
    protected $_elementSetName = 'Monitor';
    protected $_elementSet;
    protected $_statusElements;
    // Simple lists of ids as keys to simplify results.
    protected $_uniques;
    protected $_repetitives;
    protected $_withTerms;
    protected $_withoutTerms;

    /**
     * Get the helper.
     *
     * @return This view helper.
     */
    public function monitor()
    {
        return $this;
    }

    /**
     * Get the element set of the plugin.
     *
     * @return ElementSet
     */
    public function getElementSet()
    {
        if (empty($this->_elementSet)) {
            $this->_getStatusElements();
        }
        return $this->_elementSet;
    }

    /**
     * Get all elements of the element set with status data, by id.
     *
     * @param boolean|null $withTerms If null all elements are returned, else
     * returns all elements with or without terms.
     * @param boolean|null $unique If null all elements are returned else
     * returns all elements unique or repetitive.
     * @param boolean $onlyNames Returns only the name of elements.
     * @return array
     */
    public function getStatusElements($withTerms = null, $unique = null, $onlyNames = false)
    {
        $elements = $this->_getStatusElements();

        // With terms.
        if ($withTerms == true) {
            $elements = array_intersect_key($elements, $this->_withTerms);
        }
        elseif ($withTerms === false) {
            $elements = array_intersect_key($elements, $this->_withoutTerms);
        }

        // With terms.
        if ($unique == true) {
            $elements = array_intersect_key($elements, $this->_uniques);
        }
        elseif ($unique === false) {
            $elements = array_intersect_key($elements, $this->_repetitives);
        }

        // Only names.
        if ($onlyNames) {
            foreach ($elements as &$element) {
                $element = $element['name'];
            }
        }

        return $elements;
    }

    /**
     * Get all elements names of the element set, by id.
     *
     * @param boolean|null $withTerms If null all elements are returned, else
     * returns all elements with or without terms.
     * @param boolean|null $unique If null all elements are returned else
     * returns all elements unique or repetitive.
     * @return array
     */
    public function getStatusElementNamesById($withTerms = null, $unique = null)
    {
        return $this->getStatusElements($withTerms, $unique, true);
    }

    /**
     * Get one status element and check it for terms and unique.
     *
     * @param integer $elementId
     * @param boolean|null $withTerms If null all elements are returned, else
     * returns all elements with or without terms.
     * @param boolean|null $unique If null all elements are returned else
     * returns all elements unique or repetitive.
     * @param boolean $onlyNames Returns only the name of elements.
     * @return array
     */
    public function getStatusElement($elementId, $withTerms = null, $unique = null)
    {
        $elements = $this->getStatusElements($withTerms, $unique);
        if (isset($elements[$elementId])) {
            return $elements[$elementId];
        }
    }

    /**
     * Helper to get all status elements.
     *
     * @return array
     */
    protected function _getStatusElements()
    {
        if (empty($this->_elementSet)) {
            $this->_db = get_db();

            $elementSet = $this->_db->getTable('ElementSet')->findByName($this->_elementSetName);
            if (empty($elementSet)) {
                throw new Exception(__('The Curator Monitor Element Set has been removed or is unavailable.'));
            }
            $this->_elementSet = $elementSet;

            $elements = $elementSet->getElements();

            $this->_statusElements = array();
            $uniques = json_decode(get_option('curator_monitor_elements_unique'), true) ?: array();
            $repetitives = array();
            $withTerms = array();
            $withoutTerms = array();
            $tableVocab = $this->_db->getTable('SimpleVocabTerm');
            foreach ($elements as $element) {
                $this->_statusElements[$element->id] = array();
                $this->_statusElements[$element->id]['name'] = $element->name;
                $this->_statusElements[$element->id]['element'] = $element;
                $this->_statusElements[$element->id]['vocab'] = $tableVocab->findByElementId($element->id);
                $this->_statusElements[$element->id]['terms'] = !empty($this->_statusElements[$element->id]['vocab'])
                    ? explode(PHP_EOL, $this->_statusElements[$element->id]['vocab']->terms)
                    : array();
                $this->_statusElements[$element->id]['unique'] = !empty($uniques[$element->id]);
                $repetitives[$element->id] = empty($uniques[$element->id]);
                $withTerms[$element->id] = !empty($this->_statusElements[$element->id]['terms']);
                $withoutTerms[$element->id] = empty($this->_statusElements[$element->id]['terms']);
            }
            $this->_uniques = array_filter($uniques);
            $this->_repetitives = array_filter($repetitives);
            $this->_withTerms = array_filter($withTerms);
            $this->_withoutTerms = array_filter($withoutTerms);
        }

        return $this->_statusElements;
    }
}
