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
     * stage in workflow, and select documents ready to be published at each
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

        // Set internal params: list of all status elements.
        $statusElements = array();
        if (empty($params['element'])) {
            // Set default elements: unique, steppable or not and with terms.
            $statusElements = $this->view->monitor()->getStatusElements(true, null, true);
            $params['element'] = array_keys($statusElements);
        }
        // Check element and set it as array.
        else {
            // Check the element.
            $statusElement = $this->view->monitor()->getStatusElement($params['element'], true, null, true);
            if ($statusElement) {
                // Set it as array to simplify next process.
                $statusElements = array($params['element'] => $statusElement);
                $params['element'] = (array) $params['element'];
            }
        }

        // A second check may be needed if there are no unique elements.
        if (empty($statusElements)) {
            $this->view->results = array();
            return;
        }

        $this->view->params = $params;

        // The main query.
        $result = $this->_helper->db->countRecords($params);

        if (empty($result)) {
            $this->view->results = array();
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
            // To quick the process, all the periods are built before.
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
            foreach ($statusElements as $elementId => $element) {
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
            foreach ($statusElements as $elementId => $element) {
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
        $this->view->results = $stats;

        // Request for downloading.
        $export = $this->_getParam('export');

        if ($export) {
            $response = $this->getResponse();

            $this->view->generator = $this->_getGenerator();

            // Prepare the export if needed.
            switch ($export) {
                case 'csv':
                    $response
                        ->setHeader('Content-Disposition',
                            'attachment; filename=Omeka_Curator_Monitor_' . date('Ymd-His') . '.csv')
                        ->setHeader('Content-type', 'text/csv');
                    $this->render('browse-csv');
                    break;

                case 'ods':
                    // Check format.
                    $zipProcessor = $this->_getZipProcessor();
                    if (empty($zipProcessor)) {
                        $flashMessenger = $this->_helper->FlashMessenger;
                        $flashMessenger->addMessage(__('Your server cannot return ods zipped files.'), 'error');
                        $flashMessenger->addMessage(__('Try the format fods instead.'), 'success');
                        $export = null;
                        return;
                    }

                    $filename = $this->_prepareOds();
                    if (empty($filename)) {
                        $flashMessenger = $this->_helper->FlashMessenger;
                        $flashMessenger->addMessage(__('Cannot create the ods file. Check your temp directory and your rights.'), 'error');
                        $this->_helper->redirector('search');
                        return;
                    }

                    $this->_helper->viewRenderer->setNoRender();
                    $response
                        ->setHeader('Content-Disposition',
                           'attachment; filename=Omeka_Curator_Monitor_' . date('Ymd-His') . '.ods')
                        ->setHeader('Content-type', 'application/vnd.oasis.opendocument.spreadsheet');
                    $response->clearBody();
                    $response->setBody(file_get_contents($filename));
                    break;

                case 'fods':
                    $response
                        ->setHeader('Content-Disposition',
                            'attachment; filename=Omeka_Curator_Monitor_' . date('Ymd-His') . '.fods')
                        ->setHeader('Content-type', 'text/xml');
                    $this->render('browse-fods');
                    break;
            }
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
                // Only elements unique, steppable and with terms can be staged.
                ->getStatusElement($elementId, true, true, true);
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


    /**
     * Return the generator of the OpenDocument.
     *
     * @see HistoryLog_IndexController::_getGenerator()
     * @return string
     */
    protected function _getGenerator()
    {
        $iniReader = new Omeka_Plugin_Ini(PLUGIN_DIR);
        $path = basename(dirname(dirname(__FILE__)));
        $generator = sprintf('Omeka/%s - %s/%s [%s] (%s)',
            OMEKA_VERSION,
            $iniReader->getPluginIniValue($path, 'name'),
            $iniReader->getPluginIniValue($path, 'version'),
            $iniReader->getPluginIniValue($path, 'author'),
            $iniReader->getPluginIniValue($path, 'link'));
        return $generator;
    }

    /**
     * Prepare output as OpenDocument Spreadsheet (ods).
     *
     * @return string|null Filename of the ods. Null if error.
     */
    protected function _prepareOds()
    {
        $dateTime = date('Y-m-d\TH:i:s') . strtok(substr(microtime(), 1), ' ');
        $tableNames = array();
        $headers = array();
        $statusElements = $this->view->monitor()->getStatusElementNamesById();
        $cells = 0;
        foreach ($this->view->results as $elementId => $result):
            $tableNames[] = $statusElements[$elementId];
            $tableHeaders = array_keys(reset($result));
            $headers[] = $tableHeaders;
            $cells += ((count($result) + ($this->view->params['exportheaders'] ? 1 : 0)) * count($tableHeaders));
        endforeach;

        $options = array(
            'params' => $this->view->params,
            'tableNames' => $tableNames,
            'headers' => $headers,
            'values' => $this->view->results,
            'generator' => $this->view->generator,
            'user' => current_user(),
            'dateTime' => $dateTime,
            'cells' => $cells,
            'tableActive' => 0,
            'declaration' => true,
        );

        // Create a temp dir to build the ods.
        $tempDir = tempnam(sys_get_temp_dir(), 'ods');
        unlink($tempDir);
        mkdir($tempDir);
        // @chmod($tempDir, 0755);

        // Prepare the structure of the ods file via a temp dir.
        $sourceDir = dirname(dirname(__FILE__))
            . DIRECTORY_SEPARATOR . 'views'
            . DIRECTORY_SEPARATOR . 'scripts'
            . DIRECTORY_SEPARATOR . 'ods'
            . DIRECTORY_SEPARATOR . 'base';
        mkdir($tempDir . DIRECTORY_SEPARATOR . 'META-INF');
        // @chmod($tempDir . DIRECTORY_SEPARATOR . 'META-INF');
        mkdir($tempDir . DIRECTORY_SEPARATOR . 'Thumbnails');
        // @chmod($tempDir . DIRECTORY_SEPARATOR . 'Thumbnails');

        // Copy the default files.
        $defaultFiles = array(
            // OpenDocument requires that "mimetype" be the first file, without
            // compression in order to get the mime type without unzipping.
            'mimetype',
            'manifest.rdf',
            'META-INF' . DIRECTORY_SEPARATOR . 'manifest.xml',
            // TODO A thumbnail of the true content.
            'Thumbnails' . DIRECTORY_SEPARATOR . 'thumbnail.png',
        );
        foreach ($defaultFiles as $file) {
            $result = copy(
                $sourceDir . DIRECTORY_SEPARATOR . $file,
                $tempDir . DIRECTORY_SEPARATOR . $file);
            if (!$result) {
                return;
            }
            // @chmod($tempDir . DIRECTORY_SEPARATOR . $file, 0644);
        }

        // Prepare the other files.
        $xmlFiles = array(
            'meta.xml',
            'settings.xml',
            'styles.xml',
            'content.xml',
        );
        foreach ($xmlFiles as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            $filename = tempnam(sys_get_temp_dir(), $name);
            $xml = $this->view->partial('ods/' . $name . '.php', 'curator-monitor', $options);
            $result = file_put_contents($filename, $xml);
            if (!$result) {
                return;
            }
            $result = rename($filename, $tempDir . DIRECTORY_SEPARATOR . $file);
            if (!$result) {
                return;
            }
            // @chmod($tempDir . DIRECTORY_SEPARATOR . $file, 0644);
        }

        // Prepare the zip file.
        $filename = tempnam(sys_get_temp_dir(), 'OmekaOds');
        // No simple function to create a temp file with an extension.
        unlink($filename);
        $filename .= strtok(substr(microtime(), 2), ' ') . '.ods';

        // Get the zip processor.
        $zipProcessor = $this->_getZipProcessor();
        if (empty($zipProcessor)) {
            return;
        }

        switch ($zipProcessor) {
            case 'ZipArchive':
                // Create the zip.
                $zip = new ZipArchive();
                if ($zip->open($filename, ZipArchive::CREATE) !== true) {
                    return;
                }

                // Add all files.
                foreach ($defaultFiles as $file) {
                    $zip->addFile($tempDir . DIRECTORY_SEPARATOR . $file, $file);
                    $zip->setCompressionName($file, ZipArchive::CM_DEFLATE);
                }
                foreach ($xmlFiles as $file) {
                    $zip->addFile($tmpDir . DIRECTORY_SEPARATOR . $file, $file);
                    $zip->setCompressionName($file, ZipArchive::CM_DEFLATE);
                }

                // No compression for "mimetype" to be readable directly by the OS.
                $zip->setCompressionName('mimetype', ZipArchive::CM_STORE);

                // Zip the file.
                $result = $zip->close();
                if (empty($result)) {
                    return;
                }
                break;

            case '/usr/bin/zip':
            default:
                // Create the zip file with "mimetype" uncompressed.
                $cd = 'cd ' . escapeshellarg($tempDir);
                $cmd = $cd
                    . ' && ' . $zipProcessor . ' --quiet -X -0 ' . escapeshellarg($filename) . ' ' . escapeshellarg('mimetype');
                Omeka_File_Derivative_Strategy_ExternalImageMagick::executeCommand($cmd, $status, $output, $errors);
                if ($status != 0) {
                    return false;
                }

                // Add other files and compress them.
                $cmd = $cd
                    . ' && ' . $zipProcessor . ' --quiet -X -9 --exclude ' . escapeshellarg('mimetype') . ' --recurse-paths ' . escapeshellarg($filename) . ' ' . escapeshellarg('.');
                Omeka_File_Derivative_Strategy_ExternalImageMagick::executeCommand($cmd, $status, $output, $errors);
                if ($status != 0) {
                    return false;
                }
                break;
        }

        return $filename;
    }

    /**
     * Check if the server support zip and return the method used.
     *
     * @return boolean
     */
    protected function _getZipProcessor()
    {
        if (class_exists('ZipArchive') && method_exists('ZipArchive', 'setCompressionName')) {
            return 'ZipArchive';
        }

        // Test the zip command line via  the processor of ExternalImageMagick.
        try {
            $cmd = 'which zip';
            Omeka_File_Derivative_Strategy_ExternalImageMagick::executeCommand($cmd, $status, $output, $errors);
            return $status == 0 ? trim($output) : false;
        } catch (Exception $e) {
            return false;
        }
    }
}
