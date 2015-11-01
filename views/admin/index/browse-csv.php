<?php
// Delimiter is a tabulation, so no issues with specific characters, except
// multilines.
$delimiter = "\t";
// No enclosure is needed with a tabulation, because generally there is no
// tabulation in the database.
$enclosure = '';
$endOfLine = PHP_EOL;
$separator = $enclosure . $delimiter . $enclosure;

if (!empty($results)):
    foreach ($results as $elementId => $result):
        $element = get_record_by_id('Element', $elementId);
        if ($params['exportheaders']):
            $row = array();
            $row[] = __('Element');
            $headers = array_keys(reset($result));
            foreach ($headers as $header):
                $row[] = $header ?: __('Not Set');
            endforeach;
            echo $enclosure . implode($separator, $row) . $enclosure . $endOfLine;
        endif;

        foreach ($result as $period => $row):
            // Replace all empty string by a 0.
            $row = array_map(function($value) { return $value ?: 0;}, $row);
            array_unshift($row, $element ? $element->name : $elementId);
            // By construction, there is no multiline to convert.
            echo $enclosure . implode($separator, $row) . $enclosure . $endOfLine;
        endforeach;
    endforeach;
else:
    echo __('No matching logs found.');
endif;
