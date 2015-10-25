<?php if (!empty($stats)):
    // Delimiter is a tabulation, so no issues with speciic characters, except
    // multilines.
    $delimiter = "\t";
    // No enclosure is needed with a tabulation, because generally there is no
    // tabulation in the database.
    $enclosure = '';
    $endOfLine = PHP_EOL;
    $separator = $enclosure . $delimiter . $enclosure;
    foreach ($stats as $elementId => $stat):
        $element = get_record_by_id('Element', $elementId);
        if ($params['csvheaders']):
            $row = array();
            $row[] = __('Element');
            $headers = array_keys(reset($stat));
            foreach ($headers as $header):
                $row[] = $header ?: __('Not Set');
            endforeach;
            echo $enclosure . implode($separator, $row) . $enclosure . $endOfLine;
        endif;

        foreach ($stat as $period => $row):
            // Replace all empty string by a 0.
            $row = array_map(function($value) { return $value ?: 0;}, $row);
            array_unshift($row, $element ? $element->name : $elementId);
            echo $enclosure . implode($separator, $row) . $enclosure . $endOfLine;
        endforeach;
    endforeach;
else:
    echo __('No matching logs found.');
endif;
