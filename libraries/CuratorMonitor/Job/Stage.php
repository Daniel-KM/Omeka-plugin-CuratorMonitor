<?php
/**
 * Stage the selected element of selected or all matching records to next term.
 */
class CuratorMonitor_Job_Stage extends Omeka_Job_AbstractJob
{
    const QUEUE_NAME = 'curator_monitor_stage';

    public function perform()
    {
        $this->_db = get_db();

        $element = $this->_options['element'];
        $statusElement = get_view()->monitor()
            ->getStatusElement($element, true, true, true);

        if (empty($statusElement)) {
            throw new RuntimeException(__('Element "%s" is not a workflow, has no vocabulary or is a repeatable field.', $element));
        }

        $element = $statusElement['element'];

        $term = $this->_options['term'];
        if (!in_array($term, $statusElement['terms'])) {
            throw new RuntimeException(__('The term "%s" is not in the vocabulary of element %s.', $term, $element->name));
        }

        $key = array_search($term, $statusElement['terms']);
        if ($key >= count($statusElement['terms']) - 1) {
            $this->_log(__('The term "%s" is the last one of the vocabulary of element %s.', $term, $element->name));
            return;
        }

        // All is fine.
        $newTerm = $statusElement['terms'][$key + 1];
        $elementSet = $element->getElementSet();
        $elementTexts[$elementSet->name][$element->name][] = array(
            'text' => $newTerm,
            'html' => false,
        );
        $metadata = array(
            Builder_Item::OVERWRITE_ELEMENT_TEXTS => true,
        );

        if (empty($this->_options['records'])) {
            $records = get_records('Item', array(
                'advanced' => array(array(
                    'element_id' => $element->id,
                    'type' => 'is exactly',
                    'terms' => $term,
                )),
            ), 0);
        }
        // There is a list of records, so check them.
        else {
            $records = array();
            foreach ($this->_options['records'] as $key => $record) {
                if (is_numeric($record)) {
                    $record = get_record_by_id('Item', $record);
                    if (empty($record)) {
                        $this->_log(__('Record #%d does not exist and has been skipped.', $record));
                        continue;
                    }
                }
                // Check the stage.
                $flag = true;
                $ets = $record->getElementTexts($elementSet->name, $element->name);
                foreach ($ets as $et) {
                    if ($et->text === $newTerm) {
                        $this->_log(__('Record #%d is already staged to "%s" and has been skipped.', $record, $newTerm));
                        $flag = false;
                        break;
                    }
                }
                if ($flag) {
                    $records[] = $record;
                }
            }
        }

        $count = count($records);
        foreach ($records as $key => $record) {
            $record = update_item($record, $metadata, $elementTexts);
            $this->_log(__('Element #%d ("%s") of record #%d staged to "%s" (%d/%d).',
                $element->id, $element->name, $record->id, $newTerm, $key + 1, $count));
            release_object($record);
        }

        $this->_log(__('%d records staged to "%s" for element "%s" (#%d).',
            $count, $newTerm, $element->name, $element->id));
    }

    /**
     * Log a message with generic info.
     *
     * @param string $msg The message to log
     * @param int $priority The priority of the message
     */
    protected function _log($msg, $priority = Zend_Log::INFO)
    {
        $prefix = "[CuratorMonitor][Stage]";
        _log("$prefix $msg", $priority);
    }
}
