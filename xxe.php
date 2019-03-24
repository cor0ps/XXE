<?php
    libxml_disable_entity_loader (false);
    $xml_file=file_get_contents('php://input');
    $dom = new DOMDocument();
    $dom->loadXML($xml_file, LIBXML_NOENT | LIBXML_DTDLOAD);
    $creds = simplexml_import_dom($dom);
    #$user=$creds->user;
    #$pass=$creds->pass;
    #echo "You are logged in $user";
    echo $creds;
?>
