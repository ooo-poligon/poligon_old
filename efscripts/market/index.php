<?php 
error_reporting (E_ALL);
$url = "http://poligon.info/efscripts/market/custom.xml.php?SECTION_ID=160";
$content = file_get_contents($url);
$xml = simplexml_load_string($content);
foreach($xml->offer as $offer) 1;
	//file_put_contents("images/TELE/".basename($offer->picture[0]), file_get_contents($offer->picture[0]));
	
// Load the XML source
$xml = dom_import_simplexml($xml);
//new DOMDocument;
//$xml->('collection.xml');

$xsl = new DOMDocument;
$xsl->load('index.xsl');

// Configure the transformer
$proc = new XSLTProcessor;
$proc->importStyleSheet($xsl); // attach the xsl rules

$result = $proc->transformToDoc($xml);
header("Content-type: text/html; charset=UTF-8;");
echo $result->saveHtml();
?>