<?
define("STOP_STATISTICS", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->IncludeComponent(
	'bitrix:sale.ajax.delivery.calculator', 
	'input', 
	array(
		"AJAX_CALL" => "Y",
		"STEP" => intval($_REQUEST["STEP"]),
		"DELIVERY" => $_REQUEST["DELIVERY"],
		"PROFILE" => $_REQUEST["PROFILE"],
		"ORDER_WEIGHT" => doubleval($_REQUEST["WEIGHT"]),
		"ORDER_PRICE" => doubleval($_REQUEST["PRICE"]),
		"LOCATION_TO" => intval($_REQUEST["LOCATION"]),
		"CURRENCY" => $_REQUEST["CURRENCY"],
		"INPUT_NAME" => $_REQUEST["INPUT_NAME"],
		"TEMP" => $_REQUEST["TEMP"]
	));	

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
?>