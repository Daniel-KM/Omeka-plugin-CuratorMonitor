<?php
/**
 * Controller for Curator Monitor admin pages.
 *
 * @package CuratorMonitor
 */
class CuratorMonitor_IndexController extends Omeka_Controller_AbstractActionController
{
    protected $_browseRecordsPerPage = 100;
    protected $_autoCsrfProtection = true;

    /**
     * Initialize with the HistoryLogEntry table to simplify queries.
     */
    public function init()
    {
        $this->_helper->db->setDefaultModelName('HistoryLogEntry');
    }

    /**
     * Main view of the monitor.
     *
     * This administrative metadata will enable the project to keep accurate
     * statistics on progress, identify documents that are ready for the next
     * stage in processing, and select documents ready to be published at each
     * quarter without having to create a separate control database or system.
     */
    public function indexAction()
    {
        // Respect only GET parameters when browsing.
        $this->getRequest()->setParamSources(array('_GET'));

        // Inflect the record type from the model name.
        $pluralName = $this->view->pluralize($this->_helper->db->getDefaultModelName());

        $params = $this->getAllParams();
        $zendParams = array(
            'admin' => null, 'module' => null, 'controller' => null, 'action' => null,
        );
        $params = array_diff_key($params, $zendParams);

        // Set internal params.
        $elements = $this->view->monitor()->getStatusElements(true, true);
        if (empty($params['element'])) {
            $params['element'] = array_keys($elements);
        }
        // Check element and set it as array.
        else {
            if (isset($elements[$params['element']])) {
                $elements = array($params['element'] => $elements[$params['element']]);
                $params['element'] = (array) $params['element'];
            }
        }

        // A second check may be needed if there are no unique elements.
        if (empty($elements)) {
            $this->view->stats = null;
            return;
        }

        $this->view->params = $params;

        // The main query.
        $result = $this->_helper->db->countRecords($params);

        if (empty($result)) {
            $this->view->stats = null;
            return;
        }

        // Rebuild the result by element id / date if any / count, with a header.
        $stats = array();
        // Check if this is a detailed result.
        $listBy = array(
            'Date' => __('Date'),
            'Day' => __('Day'),
            'Week' => __('Week'),
            'Month' => __('Month'),
            'Quarter' => __('Quarter'),
            'Year' => __('Year'),
        );
        $byDates = array_intersect_key($listBy, $result[0]);

        if ($byDates) {
            // Fill all the values for all terms and all dates with "0". This
            // allows to get a value for the unused terms. They may be removed
            // in the view.
            // To quick the process, all the periods are build before.
            $periods = array();
            foreach ($result as $key => $row) {
                $byRow = array_intersect_key($row, $byDates);
                // The period need to have well formed to allow sort, even with
                // month, week and day, so prepare the test.
                $byFormat = array_map(function($value) {
                         return str_pad($value, 2, '0', STR_PAD_LEFT);
                    }, $byRow);
                $by = implode('-', $byFormat);
                $periods[$by] = $byRow;
            }
            ksort($periods);

            // Prepare the full array for response, with empty count.
            foreach ($elements as $elementId => $element) {
                $terms = array_fill_keys($element['terms'], '');
                foreach ($periods as $by => $row) {
                    $stats[$elementId][$by] = $row + $terms;
                }
            }

            // Combine the response with the list of results.
            foreach ($result as $key => $row) {
                // If there is no element id or text, this is a row without
                // value for a date, so skip it because it's already filled.
                if (!empty($row['element_id'])) {
                    $byRow = array_intersect_key($row, $byDates);
                    $byFormat = array_map(function($value) { return str_pad($value, 2, '0', STR_PAD_LEFT);}, $byRow);
                    $by = implode('-', $byFormat);
                    $stats[$row['element_id']][$by][$row['text']] = $row['Count'];
                }
            }
        }

        // Full synthesis / no period.
        else {
            $by = 'All';
            // Fill all the values for all terms with "0". This allows to get a
            // value for the unused terms. They may be removed in the view.
            foreach ($elements as $elementId => $element) {
                $stats[$elementId][$by] = array_fill_keys($element['terms'], 0);
            }
            // Convert the results in the new array.
            foreach ($result as $key => $row) {
                $stats[$row['element_id']][$by][$row['text']] = $row['Count'];
            }
        }
        // Reduce memory?
        unset($result);

        $this->view->byDates = $byDates;
        $this->view->stats = $stats;

        // Request for downloading.
        $isCsv = $this->_getParam('csvdownload');
        if ($isCsv) {
            // Prepare the download.
            $response = $this->getResponse();
            $response
                ->setHeader('Content-Disposition',
                    'attachment; filename=Omeka_Curator_Monitor_' . date('Ymd-His') . '.csv')
                ->setHeader('Content-type', 'text/csv');

            // The response is rendered via the "browse-csv" script.
            $this->render('index-csv');
        }
    }

    /**
     * This shows the search form for records by going to the correct URI.
     *
     * @return void
     */
    public function searchAction()
    {
        include_once dirname(dirname(__FILE__))
            . DIRECTORY_SEPARATOR . 'forms'
            . DIRECTORY_SEPARATOR . 'Search.php';
        $form = new CuratorMonitor_Form_Search();

        // Prepare the form to return result in the browse view with pagination.
        $form->setAction(url(array(
            'module' => 'curator-monitor',
            'controller' => 'index',
            'action' =>'index',
        )));
        // The browse method requires "get" to process the query.
        $form->setMethod('get');

        $this->view->form = $form;
    }

    /**
     * Update selected records into the next term.
     */
    public function stageAction()
    {
        $flashMessenger = $this->_helper->FlashMessenger;
        $elementId = $this->getParam('element');
        $term = $this->getParam('term');

        if (!empty($elementId) && !empty($term)) {
            $statusElement = get_view()->monitor()
                ->getStatusElement($elementId, true, true);
            $element = $statusElement['element'];
            if (!empty($statusElement)) {
                $key = array_search($term, $statusElement['terms']);
                if ($key < count($statusElement['terms']) - 1) {
                    $options = array();
                    $options['element'] = $element->id;
                    $options['term'] = $term;
                    $jobDispatcher = Zend_Registry::get('bootstrap')->getResource('jobs');
                    $jobDispatcher->setQueueName(CuratorMonitor_Job_Stage::QUEUE_NAME);
                    $jobDispatcher->sendLongRunning('CuratorMonitor_Job_Stage', $options);
                    $message = __('A background job process is launched to stage "%s" into "%s" for element "%s".',
                        $term, $statusElement['terms'][$key +1], $element->name)
                        . ' ' . __('This may take a while.');
                    $flashMessenger->addMessage($message, 'success');
                }
            }
        }

        if (!isset($options)) {
            $flashMessenger->addMessage(__('Stage cannot be done with element #%s and term "%s".',
                $elementId, $term), 'error');
        }

        return $this->redirect('curator-monitor');
    }
}
