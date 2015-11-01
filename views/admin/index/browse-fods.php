<?php
// This is a simple table, so php is used directly, without xml tools.
// See https://www.oasis-open.org/standards#opendocumentv1.2
// See https://en.wikipedia.org/wiki/OpenDocument_technical_specification

$dateTime = date('Y-m-d\TH:i:s') . strtok(substr(microtime(), 1), ' ');
$tableNames = array();
$headers = array();
$statusElements = $this->monitor()->getStatusElementNamesById();
$cells = 0;
foreach ($results as $elementId => $result):
    $tableNames[] = $statusElements[$elementId];
    $tableHeaders = array_keys(reset($result));
    $headers[] = $tableHeaders;
    $cells += ((count($result) + ($params['exportheaders'] ? 1 : 0)) * count($tableHeaders));
endforeach;

$options = array(
    'params' => $params,
    'tableNames' => $tableNames,
    'headers' => $headers,
    'values' => $results,
    'generator' => $generator,
    'user' => current_user(),
    'dateTime' => $dateTime,
    'cells' => $cells,
    'tableActive' => 0,
    'declaration' => false,
);

echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
?>
<office:document xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:presentation="urn:oasis:names:tc:opendocument:xmlns:presentation:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:config="urn:oasis:names:tc:opendocument:xmlns:config:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:rpt="http://openoffice.org/2005/report" xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:grddl="http://www.w3.org/2003/g/data-view#" xmlns:tableooo="http://openoffice.org/2009/table" xmlns:drawooo="http://openoffice.org/2010/draw" xmlns:calcext="urn:org:documentfoundation:names:experimental:calc:xmlns:calcext:1.0" xmlns:loext="urn:org:documentfoundation:names:experimental:office:xmlns:loext:1.0" xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0" xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0" xmlns:css3t="http://www.w3.org/TR/css3-text/" office:version="1.2" office:mimetype="application/vnd.oasis.opendocument.spreadsheet">
<?php echo $this->partial('ods/meta.php', 'curator-monitor', $options); ?>
<?php echo $this->partial('ods/settings.php', 'curator-monitor', $options); ?>
 <office:scripts/>
<?php echo $this->partial('ods/styles.php', 'curator-monitor', $options); ?>
<?php echo $this->partial('ods/content-body.php', 'curator-monitor', $options); ?>
</office:document>