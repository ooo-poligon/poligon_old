<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!\Bitrix\Main\Loader::includeModule("iblock"))
	return;

$boolCatalog = \Bitrix\Main\Loader::includeModule("catalog");

$arIBlockType = CIBlockParameters::GetIBlockTypes();

$arIBlock=array();
$rsIBlock = CIBlock::GetList(array("sort" => "asc"), array("TYPE" => $arCurrentValues["IBLOCK_TYPE"], "ACTIVE"=>"Y"));
while($arr=$rsIBlock->Fetch())
{
	$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
}



$arComponentParameters = array(
	"GROUPS" => Array(
		"SOURCE" => array(
				"NAME" => GetMessage("SOURCE"),
				"SORT" => "90",
			),
		),
	"PARAMETERS" => array(
		"IBLOCK_TYPE" => array(
			"PARENT" => "SOURCE",
			"NAME" => GetMessage("IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y",
		),
		"IBLOCK_ID" => array(
			"PARENT" => "SOURCE",
			"NAME" => GetMessage("IBLOCK_IBLOCK"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlock,
			"REFRESH" => "Y",
		),
		"USE_IBLOCK_ID" => array(
			"PARENT" => "SOURCE",
			"NAME" => GetMessage("USE_IBLOCK_ID"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y",
		),
		"CACHE_TIME" => array(),
		"USE_SECTION_ID" => array(
			"PARENT" => "SOURCE",
			"NAME" => GetMessage("USE_SECTION_ID"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y",
		),
		"USE_ELEMENT_ID" => array(
			"PARENT" => "SOURCE",
			"NAME" => GetMessage("USE_ELEMENT_ID"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y",
		),
		"ELEMENT_COUNT_ON_PAGE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("ELEMENT_COUNT_ON_PAGE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"ELEMENT_COUNT" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("ELEMENT_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"HEIGHT_WRAP" => array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("HEIGHT_WRAP"),
			"TYPE" => "STRING",
			"DEFAULT" => "350",
		),
		"OFFERS_SORT_FIELD" => array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("ELEMENT_SORT_FIELD"),
			"TYPE" => "LIST",
			"VALUES" => $arSort,
			"ADDITIONAL_VALUES" => "Y",
			"DEFAULT" => "sort",
		),
		"OFFERS_SORT_ORDER" => array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("ELEMENT_SORT_ORDER"),
			"TYPE" => "LIST",
			"VALUES" => $arAscDesc,
			"DEFAULT" => "asc",
			"ADDITIONAL_VALUES" => "Y",
		),
		"USE_ELEMENT_NAME" => array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("ELEMENT_NAME"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"USE_ELEMENT_PRICE" => array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("ELEMENT_PRICE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"REFRESH" => "Y",
		),
		"USE_FRACTIONAL_VALUE" => array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("USE_FRACTIONAL_VALUE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		/* для разных тип цен
		"PRICE_CODE" => array(
			"PARENT" => "PRICES",
			"NAME" => GetMessage("IBLOCK_PRICE_CODE"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"SIZE" => (count($arPrice) > 5 ? 8 : 3),
		),
		
		"USE_ELEMENT_ANOUNCE" => array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("ELEMENT_ANOUNCE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),*/
	),
);
if (0 < intval($arCurrentValues["IBLOCK_ID"]))
{
	if (isset($arCurrentValues["USE_IBLOCK_ID"]) && ($arCurrentValues["USE_IBLOCK_ID"] != "N"))
	{
		unset($arComponentParameters["PARAMETERS"]["USE_ELEMENT_ID"]);
		unset($arComponentParameters["PARAMETERS"]["USE_SECTION_ID"]);
		unset($arComponentParameters["PARAMETERS"]["INCLUDE_SUBSECTIONS"]);
	}
	if (isset($arCurrentValues["USE_SECTION_ID"]) &&($arCurrentValues["USE_SECTION_ID"] != "N"))
		{
		  	 $arComponentParameters["PARAMETERS"]["SECTION_ID"] = array(
					"PARENT" => "SOURCE",
					"ADDITIONAL_VALUES" => "Y",
					"NAME" => GetMessage("IBLOCK_SECTION_ID"),
					"TYPE" => "STRING",
					"MULTIPLE" => "Y",
					"DEFAULT" => '',
		   );
		   $arComponentParameters["PARAMETERS"]["INCLUDE_SUBSECTIONS"] = array(
					"PARENT" => "SOURCE",
					"NAME" => GetMessage("INCLUDE_SUBSECTIONS"),
					"TYPE" => "CHECKBOX",
					"DEFAULT" => "Y",
		   );
		unset($arComponentParameters["PARAMETERS"]["USE_ELEMENT_ID"]);
		unset($arComponentParameters["PARAMETERS"]["USE_IBLOCK_ID"]);
		}
	if (isset($arCurrentValues["USE_ELEMENT_ID"]) &&($arCurrentValues["USE_ELEMENT_ID"] != "N"))
	{
		   $arComponentParameters["PARAMETERS"]["ELEMENT_ID"] = array(
				"PARENT" => "SOURCE",
				"MULTIPLE" => "Y",
				"ADDITIONAL_VALUES" => "Y",
				"NAME" => GetMessage("IBLOCK_ELEMENT_ID"),
				"TYPE" => "STRING",
				"DEFAULT" => '',
	   );
 		unset($arComponentParameters["PARAMETERS"]["USE_SECTION_ID"]);
		unset($arComponentParameters["PARAMETERS"]["INCLUDE_SUBSECTIONS"]);
		unset($arComponentParameters["PARAMETERS"]["USE_IBLOCK_ID"]);
	}
}
if ($arCurrentValues["USE_ELEMENT_PRICE"] != "Y")
{
	unset($arComponentParameters["PARAMETERS"]["USE_FRACTIONAL_VALUE"]);
}
else
{
	   $arComponentParameters["PARAMETERS"]["USE_FRACTIONAL_VALUE"] = array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("USE_FRACTIONAL_VALUE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",

   );
}


?>