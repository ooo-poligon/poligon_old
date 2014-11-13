<?
require_once "{$_SERVER['DOCUMENT_ROOT']}/classes/Mysql.class.php";

$db = new Mysql();
$db->insert("SET NAMES UTF8");
$query = "SELECT elem.NAME, prod.QUANTITY, art.VALUE
	FROM `b_iblock_element` `elem`
		LEFT JOIN `b_catalog_product` `prod` ON `elem`.`ID` = `prod`.`ID` 
		LEFT JOIN `b_iblock_element_property` `art` ON (`art`.IBLOCK_ELEMENT_ID = `elem`.ID AND `art`.`IBLOCK_PROPERTY_ID` = 16)
WHERE 1 
AND elem.IBLOCK_ID = 4";
$onstoreArr = $db->select_array($query);

$sxe = new SimpleXMLElement("<store></store>");
foreach($onstoreArr as $device){
	$item = $sxe->addChild('item');
	$item->addAttribute('name', $device['NAME']);
	$item->addAttribute('article', $device['VALUE']);
	$item->addAttribute('quantity', $device['QUANTITY']);
}
header("Content-type: text/xml;");
echo $sxe->asXML();
