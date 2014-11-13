<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
{
	ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
	return;
}
/*************************************************************************
	Processing of received parameters
*************************************************************************/
if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600;

unset($arParams["IBLOCK_TYPE"]); //was used only for IBLOCK_ID setup with Editor
$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
$arParams["SECTION_ID"] = intval($arParams["SECTION_ID"]);
if(strlen($arParams["ELEMENT_SORT_FIELD"])<=0)
	$arParams["ELEMENT_SORT_FIELD"]="sort";
if($arParams["ELEMENT_SORT_ORDER"]!="desc")
	 $arParams["ELEMENT_SORT_ORDER"]="asc";
if(strlen($arParams["SECTION_SORT_FIELD"])<=0)
	$arParams["SECTION_SORT_FIELD"]="sort";
if($arParams["SECTION_SORT_ORDER"]!="desc")
	 $arParams["SECTION_SORT_ORDER"]="asc";
if(strlen($arParams["FILTER_NAME"])>0)
{
	global $$arParams["FILTER_NAME"];
	$arrFilter = ${$arParams["FILTER_NAME"]};
}
if(!is_array($arrFilter))
	$arrFilter=array();

$arParams["SECTION_URL"]=trim($arParams["SECTION_URL"]);
if(strlen($arParams["SECTION_URL"])<=0)
	$arParams["SECTION_URL"] = "section.php?IBLOCK_ID=#IBLOCK_ID#&SECTION_ID=#SECTION_ID#";
$arParams["DETAIL_URL"]=trim($arParams["DETAIL_URL"]);
if(strlen($arParams["DETAIL_URL"])<=0)
	$arParams["DETAIL_URL"] = "element.php?IBLOCK_ID=#IBLOCK_ID#&SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#";
$arParams["BASKET_URL"]=trim($arParams["BASKET_URL"]);
if(strlen($arParams["BASKET_URL"])<=0)
	$arParams["BASKET_URL"] = "/personal/basket.php";

$arParams["ACTION_VARIABLE"]=trim($arParams["ACTION_VARIABLE"]);
if(strlen($arParams["ACTION_VARIABLE"])<=0|| !ereg("^[A-Za-z_][A-Za-z01-9_]*$", $arParams["ACTION_VARIABLE"]))
	$arParams["ACTION_VARIABLE"] = "action";
$arParams["PRODUCT_ID_VARIABLE"]=trim($arParams["PRODUCT_ID_VARIABLE"]);
if(strlen($arParams["PRODUCT_ID_VARIABLE"])<=0|| !ereg("^[A-Za-z_][A-Za-z01-9_]*$", $arParams["PRODUCT_ID_VARIABLE"]))
	$arParams["PRODUCT_ID_VARIABLE"] = "id";
$arParams["SECTION_ID_VARIABLE"]=trim($arParams["SECTION_ID_VARIABLE"]);
if(strlen($arParams["SECTION_ID_VARIABLE"])<=0|| !ereg("^[A-Za-z_][A-Za-z01-9_]*$", $arParams["SECTION_ID_VARIABLE"]))
	$arParams["SECTION_ID_VARIABLE"] = "SECTION_ID";

$arParams["SET_TITLE"] = $arParams["SET_TITLE"]!="N";
$arParams["DISPLAY_PANEL"] = $arParams["DISPLAY_PANEL"]=="Y";
$arParams["DISPLAY_COMPARE"] = $arParams["DISPLAY_COMPARE"]=="Y";

$arParams["SECTION_COUNT"] = intval($arParams["SECTION_COUNT"]);
if($arParams["SECTION_COUNT"]<=0)
	$arParams["SECTION_COUNT"]=20;
$arParams["ELEMENT_COUNT"] = intval($arParams["ELEMENT_COUNT"]);
if($arParams["ELEMENT_COUNT"]<=0)
	$arParams["ELEMENT_COUNT"]=20;
$arParams["LINE_ELEMENT_COUNT"] = intval($arParams["LINE_ELEMENT_COUNT"]);
if($arParams["LINE_ELEMENT_COUNT"]<=0)
	$arParams["LINE_ELEMENT_COUNT"]=3;

if(!is_array($arParams["PROPERTY_CODE"]))
	$arParams["PROPERTY_CODE"] = array();
foreach($arParams["PROPERTY_CODE"] as $k=>$v)
	if($v==="")
		unset($arParams["PROPERTY_CODE"][$k]);
if(!is_array($arParams["PRICE_CODE"]))
	$arParams["PRICE_CODE"] = array();

$arParams["USE_PRICE_COUNT"] = $arParams["USE_PRICE_COUNT"]=="Y";
$arParams["SHOW_PRICE_COUNT"] = intval($arParams["SHOW_PRICE_COUNT"]);
if($arParams["SHOW_PRICE_COUNT"]<=0)
	$arParams["SHOW_PRICE_COUNT"]=1;

$arParams["PRICE_VAT_INCLUDE"] = $arParams["PRICE_VAT_INCLUDE"] !== "N";

$arParams["CACHE_FILTER"]=$arParams["CACHE_FILTER"]=="Y";
if(!$arParams["CACHE_FILTER"] && count($arrFilter)>0)
	$arParams["CACHE_TIME"] = 0;

/*************************************************************************
			Processing of the Buy link
*************************************************************************/
$strError = "";
if (array_key_exists($arParams["ACTION_VARIABLE"], $_REQUEST) && array_key_exists($arParams["PRODUCT_ID_VARIABLE"], $_REQUEST))
{
	$action = strtoupper($_REQUEST[$arParams["ACTION_VARIABLE"]]);
	$productID = intval($_REQUEST[$arParams["PRODUCT_ID_VARIABLE"]]);
	if (($action == "ADD2BASKET" || $action == "BUY") && $productID > 0)
	{
		if (CModule::IncludeModule("sale") && CModule::IncludeModule("catalog"))
		{
			if (Add2BasketByProductID($productID))
			{
				if ($action == "BUY")
					LocalRedirect($arParams["BASKET_URL"]);
				else
					LocalRedirect($APPLICATION->GetCurPageParam("", array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));
			}
			else
			{
				if ($ex = $GLOBALS["APPLICATION"]->GetException())
					$strError = $ex->GetString();
				else
					$strError = GetMessage("CATALOG_ERROR2BASKET").".";
			}
		}
	}
}
if(strlen($strError)>0)
{
	ShowError($strError);
	return;
}

$arResult["SECTIONS"]=array();

/*************************************************************************
			Work with cache
*************************************************************************/
$CACHE_ID = serialize($arrFilter).$USER->GetGroups().CDBResult::NavStringForCache($arParams["PAGE_ELEMENT_COUNT"]);
if($this->StartResultCache(false, $CACHE_ID))
{
	//This function returns array with prices description and access rights
	//in case catalog module n/a prices get values from element properties
	$arResult["PRICES"] = CIBlockPriceTools::GetCatalogPrices($arParams["IBLOCK_ID"], $arParams["PRICE_CODE"]);

	$arFilter = array(
		"ACTIVE"=>"Y",
		"GLOBAL_ACTIVE"=>"Y",
		"IBLOCK_ID"=>$arParams["IBLOCK_ID"],
		"IBLOCK_ACTIVE"=>"Y",
	);
	//ORDER BY
	$arSort = array(
		$arParams["SECTION_SORT_FIELD"] => $arParams["SECTION_SORT_ORDER"],
		"ID" => "ASC",
	);
	//EXECUTE
	$rsSections = CIBlockSection::GetList($arSort, $arFilter);
	while($arSection = $rsSections->GetNext())
	{
		if(strlen($arParams["SECTION_URL"]) > 0)
			$arSection["SECTION_PAGE_URL"] = htmlspecialchars(str_replace(
				array("#SERVER_NAME#", "#SITE_DIR#", "#IBLOCK_ID#", "#SECTION_ID#"),
				array(SITE_SERVER_NAME, SITE_DIR, $arSection["IBLOCK_ID"], $arSection["ID"]),
				$arParams["SECTION_URL"]
			));

		$arSection["PICTURE"] = CFile::GetFileArray($arSection["PICTURE"]);
		$arSection["DETAIL_PICTURE"] = CFile::GetFileArray($arSection["DETAIL_PICTURE"]);

		// list of the element fields that will be used in selection
		$arSelect = array(
			"ID",
			"NAME",
			"CODE",
			"IBLOCK_ID",
			"IBLOCK_SECTION_ID",
			"DETAIL_PAGE_URL",
			"DETAIL_TEXT",
			"DETAIL_TEXT_TYPE",
			"DETAIL_PICTURE",
			"PREVIEW_TEXT",
			"PREVIEW_TEXT_TYPE",
			"PREVIEW_PICTURE",
			"PROPERTY_*",
		);
		if($arParams["SHOW_DESCRIPTION"])
		{
			$arSelect[] = "PREVIEW_TEXT";
			$arSelect[] = "PREVIEW_TEXT_TYPE";
		}
		$arrFilter["ACTIVE"] = "Y";
		$arrFilter["IBLOCK_ID"] = $arParams["IBLOCK_ID"];
		$arrFilter["SECTION_ID"] = $arSection["ID"];
		$arrFilter["ACTIVE_DATE"] = "Y";
		$arrFilter["CHECK_PERMISSIONS"] = "Y";
		//PRICES
		if(!$arParams["USE_PRICE_COUNT"])
		{
			foreach($arResult["PRICES"] as $key => $value)
			{
				$arSelect[] = $value["SELECT"];
				$arFilter["CATALOG_SHOP_QUANTITY_".$value["ID"]] = $arParams["SHOW_PRICE_COUNT"];
			}
		}
		$arSort = array(
			$arParams["ELEMENT_SORT_FIELD"] => $arParams["ELEMENT_SORT_ORDER"],
			"ID" => "DESC",
		);
		//EXECUTE
		$rsElements = CIBlockElement::GetList($arSort, $arrFilter, false, array("nTopCount"=>$arParams["ELEMENT_COUNT"]), $arSelect);
		$arSection["ITEMS"] = array();
		while($obElement = $rsElements->GetNextElement())
		{
			$arItem = $obElement->GetFields();

			$arItem["PREVIEW_PICTURE"] = CFile::GetFileArray($arItem["PREVIEW_PICTURE"]);
			$arItem["DETAIL_PICTURE"] = CFile::GetFileArray($arItem["DETAIL_PICTURE"]);

			if(count($arParams["PROPERTY_CODE"]) > 0)
				$arItem["PROPERTIES"] = $obElement->GetProperties();

			$arItem["DISPLAY_PROPERTIES"] = array();
			foreach($arParams["PROPERTY_CODE"] as $pid)
			{
				$prop = &$arItem["PROPERTIES"][$pid];
				if((is_array($prop["VALUE"]) && count($prop["VALUE"])>0) ||
				(!is_array($prop["VALUE"]) && strlen($prop["VALUE"])>0))
				{
					$arItem["DISPLAY_PROPERTIES"][$pid] = CIBlockFormatProperties::GetDisplayValue($arItem, $prop, "catalog_out");
				}
			}

			if(strlen($arParams["DETAIL_URL"]) > 0)
				$arItem["DETAIL_PAGE_URL"] = htmlspecialchars(str_replace(
					array("#SERVER_NAME#", "#SITE_DIR#", "#IBLOCK_ID#", "#SECTION_ID#", "#ELEMENT_ID#"),
					array(SITE_SERVER_NAME, SITE_DIR, $arSection["IBLOCK_ID"], $arSection["ID"], $arItem["ID"]),
					$arParams["DETAIL_URL"]
				));

			if($arParams["USE_PRICE_COUNT"])
			{
				if(CModule::IncludeModule("catalog"))
				{
					$arItem["PRICE_MATRIX"] = CatalogGetPriceTableEx($arItem["ID"]);
					foreach($arItem["PRICE_MATRIX"]["COLS"] as $keyColumn=>$arColumn)
						$arItem["PRICE_MATRIX"]["COLS"][$keyColumn]["NAME_LANG"] = htmlspecialchars($arColumn["NAME_LANG"]);
				}
				else
				{
					$arItem["PRICE_MATRIX"] = false;
				}
				$arItem["PRICES"] = array();
			}
			else
			{
				$arItem["PRICE_MATRIX"] = false;
				$arItem["PRICES"] = CIBlockPriceTools::GetItemPrices($arParams["IBLOCK_ID"], $arResult["PRICES"], $arItem, $arParams['PRICE_VAT_INCLUDE']);
			}
			$arItem["CAN_BUY"] = CIBlockPriceTools::CanBuy($arParams["IBLOCK_ID"], $arResult["PRICES"], $arItem);

			$arItem["BUY_URL"] = htmlspecialchars($APPLICATION->GetCurPageParam($arParams["ACTION_VARIABLE"]."=BUY&".$arParams["PRODUCT_ID_VARIABLE"]."=".$arItem["ID"], array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));
			$arItem["ADD_URL"] = htmlspecialchars($APPLICATION->GetCurPageParam($arParams["ACTION_VARIABLE"]."=ADD2BASKET&".$arParams["PRODUCT_ID_VARIABLE"]."=".$arItem["ID"], array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));
			$arItem["COMPARE_URL"] = htmlspecialchars($APPLICATION->GetCurPageParam("action=ADD_TO_COMPARE_LIST&id=".$arItem["ID"], array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));

			$arSection["ITEMS"][]=$arItem;
		}
		$arResult["SECTIONS"][]=$arSection;
		if(count($arResult["SECTIONS"])>=$arParams["SECTION_COUNT"])
			break;
	}
	//echo "<pre>",htmlspecialchars(print_r($arResult,true)),"</pre>";
	$this->SetResultCacheKeys(array(
	));
	$this->IncludeComponentTemplate();
}

?>
