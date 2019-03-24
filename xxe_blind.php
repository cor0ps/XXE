<?php
libxml_disable_entity_loader (false);
$xmlfile = file_get_contents('php://input');
echo $xmlfile;
$dom = new DOMDocument();
$dom->loadXML($xmlfile, LIBXML_NOENT | LIBXML_DTDLOAD);
?>
