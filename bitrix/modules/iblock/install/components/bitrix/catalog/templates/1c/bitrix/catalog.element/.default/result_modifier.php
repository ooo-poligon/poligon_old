<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
/********************************
Get Prices from linked price list
********************************/
if($arParams["LINK_IBLOCK_ID"] && $arParams["LINK_PROPERTY_SID"])
{
	//SELECT
	$arSelect = array(
		"ID",
		"IBLOCK_ID",
		"XML_ID",
	);
	//WHERE
	$arID = array();
	$arMap = array();
	foreach($arResult["LINKED_ELEMENTS"] as $key=>$arItem)
	{
		$arID[] = $arItem["ID"];
		$arMap[$arItem["ID"]] = $key;
	}

	$arFilter = array(
		"ACTIVE" => "Y",
		"IBLOCK_ID" => $arParams["LINK_IBLOCK_ID"],
		"IBLOCK_LID" => SITE_ID,
		"IBLOCK_ACTIVE" => "Y",
		"ACTIVE_DATE" => "Y",
		"ACTIVE" => "Y",
		"CHECK_PERMISSIONS" => "Y",
		"ID" => $arID,
	);
	//ORDER BY
	$arSort = array(
		"ID" => "ASC",
	);
	//PRICES
	if(!$arParams["USE_PRICE_COUNT"])
	{
		foreach($arResult["CAT_PRICES"] as $key => $value)
		{
			$arSelect[] = $value["SELECT"];
			$arFilter["CATALOG_SHOP_QUANTITY_".$value["ID"]] = $arParams["SHOW_PRICE_COUNT"];
		}
	}

	$rsElements = CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);
	while($arElement = $rsElements->GetNext())
	{
		$ID = $arElement["ID"];
		$arItem = &$arResult["LINKED_ELEMENTS"][$arMap[$ID]];
	
		if($arParams["USE_PRICE_COUNT"])
		{
			if(CModule::IncludeModule("catalog"))
				$arItem["PRICE_MATRIX"] = CatalogGetPriceTableEx($arElement["ID"]);
			else
				$arItem["PRICE_MATRIX"] = false;
			$arItem["PRICES"] = array();
		}
		else
		{
			$arItem["PRICE_MATRIX"] = false;
			$arItem["PRICES"] = CIBlockPriceTools::GetItemPrices($arParams["LINK_IBLOCK_ID"], $arResult["CAT_PRICES"], $arElement);
		}
		$arItem["CAN_BUY"] = CIBlockPriceTools::CanBuy($arParams["LINK_IBLOCK_ID"], $arResult["CAT_PRICES"], $arElement);

		$arItem["BUY_URL"] = htmlspecialchars($GLOBALS["APPLICATION"]->GetCurPageParam($arParams["ACTION_VARIABLE"]."=BUY&".$arParams["PRODUCT_ID_VARIABLE"]."=".$arItem["ID"], array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));
		$arItem["ADD_URL"] = htmlspecialchars($GLOBALS["APPLICATION"]->GetCurPageParam($arParams["ACTION_VARIABLE"]."=ADD2BASKET&".$arParams["PRODUCT_ID_VARIABLE"]."=".$arItem["ID"], array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));
	}
}
?>
