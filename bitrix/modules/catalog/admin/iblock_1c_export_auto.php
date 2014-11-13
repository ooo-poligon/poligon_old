<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/iblock.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/include.php");

IncludeModuleLangFile(__FILE__);

set_time_limit(0);
$max_execution_time = 3000;
$LoadFromFile = "N";
$strError = "";

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	$arQPars = Split("&", $_SERVER["QUERY_STRING"]);

	if (count($arQPars) != 3)
		$strError .= GetMessage('IB1C_ERROR_WRONG_GET')."<br>";

	$arQVars = Split("=", $arQPars[0]);
	$sPostPrices = $arQVars[0];

	if ($sPostPrices != "PostPrices")
		$strError .= GetMessage('IB1C_ERROR_NO_PP')."<br>";

	$arQVars = Split("=", $arQPars[1]);
	$user = $arQVars[1];

	$arQVars = Split("=", $arQPars[2]);
	$pass = $arQVars[1];

	$arAuthResult = $USER->Login($user, $pass, "N");
	if (!$USER->IsAuthorized())
		$strError .= GetMessage('IB1C_ERROR_WRONG_AUTH')."<br>";

	//$CATALOG_RIGHT = $APPLICATION->GetGroupRight("catalog");
	//if ($CATALOG_RIGHT!="W")
	if (!$USER->CanDoOperation('catalog_export_exec'))
		$strError .= GetMessage('IB1C_ERROR_NO_RIGHTS')."<br>";

	if (strlen($strError)<=0)
	{
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/admin/templates/iblock_1c_export.php");
	}
}

ShowError($strError);
?>