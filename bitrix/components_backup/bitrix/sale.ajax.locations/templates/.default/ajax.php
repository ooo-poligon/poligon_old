<?
define("STOP_STATISTICS", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->IncludeComponent(
	'bitrix:sale.ajax.locations', 
	'', 
	array(
		"AJAX_CALL" => "Y", 
		"COUNTRY" => intval($_REQUEST["COUNTRY"]),
		"COUNTRY_INPUT_NAME" => $_REQUEST["COUNTRY_INPUT_NAME"],
		"CITY_INPUT_NAME" => $_REQUEST["CITY_INPUT_NAME"],
		"CITY_OUT_LOCATION" => $_REQUEST["CITY_OUT_LOCATION"],
		//"ZIP" => trim($_REQUEST["ZIP"]), 
	));

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
?>