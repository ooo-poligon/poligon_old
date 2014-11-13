<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);

$arParams["ELEMENT_COUNT"] = intval($arParams["ELEMENT_COUNT"]);

if (empty($arParams["OFFERS_SORT_FIELD"]))
	$arParams["OFFERS_SORT_FIELD"] = "sort";
if (!preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $arParams["OFFERS_SORT_ORDER"]))
	$arParams["OFFERS_SORT_ORDER"] = "asc";

if (empty($arParams["ELEMENT_COUNT"]))
	$arParams["ELEMENT_COUNT"] = "";

if(CModule::IncludeModule('iblock')&&CModule::IncludeModule("catalog")) {
	$cache = new CPageCache();
	$arSort= Array($arParams["OFFERS_SORT_FIELD"]=>$arParams["OFFERS_SORT_ORDER"]);
	$arSelect = Array("ID","NAME","DETAIL_PAGE_URL","DETAIL_PICTURE","PREVIEW_TEXT");		
	$arFilter = Array("IBLOCK_TYPE"=>$arParams["IBLOCK_TYPE"],"IBLOCK_ID"=>$arParams["IBLOCK_ID"], "ACTIVE"=>"Y");
	if(!empty($arParams["SECTION_ID"]))
	{
		if($arParams["INCLUDE_SUBSECTIONS"]=="Y")
		{
			$arFilter = Array("IBLOCK_TYPE"=>$arParams["IBLOCK_TYPE"],"IBLOCK_ID"=>$arParams["IBLOCK_ID"],"SECTION_ID"=>$arParams["SECTION_ID"], "INCLUDE_SUBSECTIONS" => "Y", "ACTIVE"=>"Y");
		}
		else
		{
			$arFilter = Array("IBLOCK_TYPE"=>$arParams["IBLOCK_TYPE"],"IBLOCK_ID"=>$arParams["IBLOCK_ID"],"SECTION_ID"=>$arParams["SECTION_ID"], "ACTIVE"=>"Y");
		}
	}
	
	if(!empty($arParams["ELEMENT_ID"]))
	{
		$arFilter = Array("IBLOCK_TYPE"=>$arParams["IBLOCK_TYPE"],"IBLOCK_ID"=>$arParams["IBLOCK_ID"],"ID"=>$arParams["ELEMENT_ID"], "ACTIVE"=>"Y");
	}

		
	$res =  CIBlockElement :: GetList ($arSort, $arFilter, false,false, $arSelect);
	$current_count_element=0;
	$res_el = array();
	while($ob = $res->GetNextElement())
		{
			$current_count_element++;
			if($arParams["ELEMENT_COUNT"]!="")
			{
				if($current_count_element>$arParams["ELEMENT_COUNT"])
				{
					break;
				}
			}
			$arFields = $ob->GetFields();
			$res_el["ID"]=$arFields["ID"];
			$res_el["NAME"]=$arFields["NAME"];
			$res_el["DETAIL_PAGE_URL"]=$arFields["DETAIL_PAGE_URL"];
			$res_el["DETAIL_PICTURE"]=$arFields["DETAIL_PICTURE"];
			$res_el["PRICE"] = CPrice::GetBasePrice($arFields["ID"]);
			$res_el["ANOUNCE"] = $arFields["PREVIEW_TEXT"];
			array_push($arResult, $res_el);
		}
	$this->IncludeComponentTemplate();
	$cache->EndDataCache();
}


?>