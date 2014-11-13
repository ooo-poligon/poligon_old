<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("sale"))
{
	ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
	return;
}

$arParams["COUNTRY"] = intval($arParams["COUNTRY"]);
$arParams["LOCATION_VALUE"] = intval($arParams["LOCATION_VALUE"]);

$arParams["AJAX_CALL"] = $arParams["AJAX_CALL"] == "Y" ? "Y" : "N"; 

if ($arParams["LOCATION_VALUE"] > 0)
{
	if ($arLocation = CSaleLocation::GetByID($arParams["LOCATION_VALUE"]))
	{
		$arParams["COUNTRY"] = $arLocation["COUNTRY_ID"];
		$arParams["CITY"] = $arParams["CITY_OUT_LOCATION"] == "Y" ? $arParams["LOCATION_VALUE"] : $arLocation["CITY_ID"];
	}
}


$arResult["COUNTRY_LIST"] = array();
$rsCountryList = CSaleLocation::GetCountryList(array("SORT" => "ASC"));
while ($arCountry = $rsCountryList->GetNext())
{
	$arResult["COUNTRY_LIST"][] = $arCountry;
}

$arResult["CITY_LIST"] = array();
if ($arParams["COUNTRY"] > 0)
{
	$rsLocationsList = CSaleLocation::GetList(
		array(
			"SORT" => "ASC",
            "COUNTRY_NAME_LANG" => "ASC",
            "CITY_NAME_LANG" => "ASC"
		),
		array(
			"COUNTRY_ID" => $arParams["COUNTRY"],
			"LID" => LANGUAGE_ID,
		),
		false,
		false,
		array(
			$arParams["CITY_OUT_LOCATION"] == "Y" ? "ID" : "CITY_ID",
			"CITY_NAME"
		)
	);
	
	while ($arCity = $rsLocationsList->GetNext())
	{
		$arResult["CITY_LIST"][] = array(
			"ID" => $arCity[$arParams["CITY_OUT_LOCATION"] == "Y" ? "ID" : "CITY_ID"],
			"CITY_NAME" => $arCity["CITY_NAME"],
		);
	}
}

$arParams["JS_CITY_INPUT_NAME"] = CUtil::JSEscape($arParams["CITY_INPUT_NAME"]);

$arTmpParams = array(
	"COUNTRY_INPUT_NAME" => $arParams["COUNTRY_INPUT_NAME"],
	"CITY_INPUT_NAME" => $arParams["CITY_INPUT_NAME"],
	"CITY_OUT_LOCATION" => $arParams["CITY_OUT_LOCATION"]
);

$arResult["JS_PARAMS"] = CUtil::PhpToJsObject($arTmpParams);

if ($arParams["AJAX_CALL"] != "Y")
{
	IncludeAJAX();
	$APPLICATION->AddHeadScript($this->GetPath() . '/templates/' . (strlen($componentTemplate) > 0 ? $componentTemplate : '.default') . '/proceed.js');
}

$this->IncludeComponentTemplate();
?>