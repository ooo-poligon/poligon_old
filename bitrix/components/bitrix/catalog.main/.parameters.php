<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;

$rsIBlockType = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
while ($arr=$rsIBlockType->Fetch())
{
	if($ar=CIBlockType::GetByIDLang($arr["ID"], LANGUAGE_ID))
		$arIBlockType[$arr["ID"]] = "[".$arr["ID"]."] ".$ar["NAME"];
}

	$arComponentParameters = array(
	"GROUPS" => array(
		),
	"PARAMETERS" => array(
		"IBLOCK_TYPE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y",
		),
		"IBLOCK_URL" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_LIST_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"DISPLAY_PANEL" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_NEWS_PANEL"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"CACHE_TIME"  =>  Array("DEFAULT"=>3600),
	),
);
?>
