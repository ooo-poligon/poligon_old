<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

CPageOption::SetOptionString("main", "nav_page_in_session", "N");

/*************************************************************************
	Processing of received parameters
*************************************************************************/
if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600;

$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
$arParams["SECTION_ID"] = intval($arParams["SECTION_ID"]);
$arParams["SECTION_CODE"] = trim($arParams["SECTION_CODE"]);

$arParams["ELEMENT_SORT_FIELD"] = trim($arParams["ELEMENT_SORT_FIELD"]);
if($arParams["ELEMENT_SORT_ORDER"]!="desc")
	 $arParams["ELEMENT_SORT_ORDER"]="asc";

if(strlen($arParams["FILTER_NAME"])<=0 || !ereg("^[A-Za-z_][A-Za-z01-9_]*$", $arParams["FILTER_NAME"]))
{
	$arrFilter = array();
}
else
{
	global $$arParams["FILTER_NAME"];
	$arrFilter = ${$arParams["FILTER_NAME"]};
	if(!is_array($arrFilter))
		$arrFilter = array();
}

if(!is_array($arParams["FIELD_CODE"]))
	$arParams["FIELD_CODE"] = array();
foreach($arParams["FIELD_CODE"] as $key=>$val)
	if(!$val)
		unset($arParams["FIELD_CODE"][$key]);
if(!is_array($arParams["PROPERTY_CODE"]))
	$arParams["PROPERTY_CODE"] = array();
foreach($arParams["PROPERTY_CODE"] as $key=>$val)
	if($val==="")
		unset($arParams["PROPERTY_CODE"][$key]);

$arParams["SECTION_URL"]=trim($arParams["SECTION_URL"]);
if(strlen($arParams["SECTION_URL"])<=0)
	$arParams["SECTION_URL"] = "section.php?SECTION_ID=#SECTION_ID#";
$arParams["DETAIL_URL"]=trim($arParams["DETAIL_URL"]);
if(strlen($arParams["DETAIL_URL"])<=0)
	$arParams["DETAIL_URL"] = "detail.php?SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#";

$arParams["PAGE_ELEMENT_COUNT"] = intval($arParams["PAGE_ELEMENT_COUNT"]);
if($arParams["PAGE_ELEMENT_COUNT"]<=0)
	$arParams["PAGE_ELEMENT_COUNT"]=20;
$arParams["LINE_ELEMENT_COUNT"] = intval($arParams["LINE_ELEMENT_COUNT"]);
if($arParams["LINE_ELEMENT_COUNT"]<=0)
	$arParams["LINE_ELEMENT_COUNT"]=3;

$arParams["ADD_SECTIONS_CHAIN"] = $arParams["ADD_SECTIONS_CHAIN"]!="N"; //Turn on by default
$arParams["SET_TITLE"] = $arParams["SET_TITLE"]!="N"; //Turn on by default
$arParams["DISPLAY_PANEL"] = $arParams["DISPLAY_PANEL"]=="Y"; //Turn off by default
$arParams["CACHE_FILTER"]=$arParams["CACHE_FILTER"]=="Y";
if(!$arParams["CACHE_FILTER"] && count($arrFilter)>0)
	$arParams["CACHE_TIME"] = 0;

$arParams["DISPLAY_TOP_PAGER"] = $arParams["DISPLAY_TOP_PAGER"]=="Y";
$arParams["DISPLAY_BOTTOM_PAGER"] = $arParams["DISPLAY_BOTTOM_PAGER"]!="N";
$arParams["PAGER_TITLE"] = trim($arParams["PAGER_TITLE"]);
$arParams["PAGER_SHOW_ALWAYS"] = $arParams["PAGER_SHOW_ALWAYS"]!="N";
$arParams["PAGER_TEMPLATE"] = trim($arParams["PAGER_TEMPLATE"]);
$arParams["PAGER_DESC_NUMBERING"] = $arParams["PAGER_DESC_NUMBERING"]=="Y";
$arParams["PAGER_DESC_NUMBERING_CACHE_TIME"] = intval($arParams["PAGER_DESC_NUMBERING_CACHE_TIME"]);

$arNavParams = array("nPageSize"=>$arParams["PAGE_ELEMENT_COUNT"], "bDescPageNumbering"=>$arParams["PAGER_DESC_NUMBERING"]);
$arNavigation = CDBResult::GetNavParams($arNavParams);
if($arNavigation["PAGEN"]==0 && $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"]>0)
	$arParams["CACHE_TIME"] = $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"];

$arParams["USE_PERMISSIONS"] = $arParams["USE_PERMISSIONS"]=="Y";
if(!is_array($arParams["GROUP_PERMISSIONS"]))
	$arParams["GROUP_PERMISSIONS"] = array(1);

$bUSER_HAVE_ACCESS = !$arParams["USE_PERMISSIONS"];
if($arParams["USE_PERMISSIONS"] && isset($GLOBALS["USER"]) && is_object($GLOBALS["USER"]))
{
	$arUserGroupArray = $GLOBALS["USER"]->GetUserGroupArray();
	foreach($arParams["GROUP_PERMISSIONS"] as $PERM)
	{
		if(in_array($PERM, $arUserGroupArray))
		{
			$bUSER_HAVE_ACCESS = true;
			break;
		}
	}
}

if($this->StartResultCache(false, array($arrFilter, $USER->GetGroups(), $arNavigation, $bUSER_HAVE_ACCESS)))
{
	if(!CModule::IncludeModule("iblock"))
	{
		$this->AbortResultCache();
		ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
		return;
	}
	$arSelect = array(
		$arParams["META_KEYWORDS"],
		$arParams["META_DESCRIPTION"],
	);
	$arFilter = array(
		"ACTIVE" => "Y",
		"GLOBAL_ACTIVE" => "Y",
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"IBLOCK_ACTIVE" => "Y",
	);

	if(strlen($arParams["SECTION_CODE"]) > 0)
		$arFilter["CODE"]=$arParams["SECTION_CODE"];
	else
		$arFilter["ID"]=$arParams["SECTION_ID"];

	$rsSection = CIBlockSection::GetList(Array(), $arFilter, false, $arSelect);
	$arResult = $rsSection->GetNext();

	//Check if have to show root elements
	if(!$arResult && (strlen($arParams["SECTION_CODE"]) < 1) && !$arParams["SECTION_ID"])
	{
		$arResult = array(
			"ID" => $arParams["SECTION_ID"],
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		);
	}

	if($arResult)
	{
		$arResult["PATH"] = array();
		if($arParams["ADD_SECTIONS_CHAIN"])
		{
			$rsPath = GetIBlockSectionPath($arResult["IBLOCK_ID"], $arResult["ID"]);
			while($arPath=$rsPath->GetNext())
			{
				if(strlen($arParams["SECTION_URL"]) > 0)
					$arPath["SECTION_PAGE_URL"] = str_replace(
						array("#SERVER_NAME#", "#SITE_DIR#", "#IBLOCK_ID#", "#SECTION_ID#", "#SECTION_CODE#"),
						array(SITE_SERVER_NAME, SITE_DIR, $arPath["IBLOCK_ID"], $arPath["ID"], $arPath["CODE"]),
						$arParams["SECTION_URL"]
					);
				$arResult["PATH"][] = $arPath;
			}
		}

		$arResult["USER_HAVE_ACCESS"] = $bUSER_HAVE_ACCESS;

		$arResult["PICTURE"] = CFile::GetFileArray($arResult["PICTURE"]);
		$arResult["DETAIL_PICTURE"] = CFile::GetFileArray($arResult["DETAIL_PICTURE"]);

		//SELECT
		$arSelect = array_merge($arParams["FIELD_CODE"], array(
			"ID",
			"CODE",
			"IBLOCK_ID",
			"NAME",
			"PREVIEW_PICTURE",
			"DETAIL_PICTURE",
			"DETAIL_PAGE_URL",
			"PREVIEW_TEXT_TYPE",
			"DETAIL_TEXT_TYPE",
		));
		$bGetProperty = count($arParams["PROPERTY_CODE"])>0;
		if($bGetProperty)
			$arSelect[]="PROPERTY_*";
		//WHERE
		$arrFilter["SECTION_ID"] = $arResult["ID"];
		$arrFilter["INCLUDE_SUBSECTIONS"] = "Y";
		$arrFilter["ACTIVE"] = "Y";
		$arrFilter["ACTIVE_DATE"] = "Y";
		$arrFilter["CHECK_PERMISSIONS"] = "Y";
		$arrFilter["IBLOCK_ID"] = $arResult["IBLOCK_ID"];
		//ORDER BY
		$arSort = array(
			$arParams["ELEMENT_SORT_FIELD"] => $arParams["ELEMENT_SORT_ORDER"],
			"ID" => "ASC",
		);
		//EXECUTE
		$rsElements = CIBlockElement::GetList($arSort, $arrFilter, false, $arNavParams, $arSelect);
		$arResult["ITEMS"] = array();

		while($obElement = $rsElements->GetNextElement())
		{
			$arItem = $obElement->GetFields();
			if($bGetProperty)
				$arItem["PROPERTIES"] = $obElement->GetProperties();
			$arItem["DISPLAY_PROPERTIES"]=array();
			foreach($arParams["PROPERTY_CODE"] as $pid)
			{
				$prop = &$arItem["PROPERTIES"][$pid];
				if((is_array($prop["VALUE"]) && count($prop["VALUE"])>0) ||
				   (!is_array($prop["VALUE"]) && strlen($prop["VALUE"])>0))
				{
					$arItem["DISPLAY_PROPERTIES"][$pid] = CIBlockFormatProperties::GetDisplayValue($arItem, $prop, "photo_out");
				}
			}
			$arItem["DETAIL_PAGE_URL"] = htmlspecialchars(str_replace(
				array("#SERVER_NAME#", "#SITE_DIR#", "#IBLOCK_ID#", "#SECTION_ID#", "#SECTION_CODE#", "#ELEMENT_ID#", "#ELEMENT_CODE#"),
				array(SITE_SERVER_NAME, SITE_DIR, $arItem["IBLOCK_ID"], $arResult["ID"], $arResult["CODE"], $arItem["ID"], $arItem["CODE"]),
				$arParams["DETAIL_URL"]
			));
			$arItem["PREVIEW_PICTURE"] = CFile::GetFileArray($arItem["PREVIEW_PICTURE"]);
			$arItem["DETAIL_PICTURE"] = CFile::GetFileArray($arItem["DETAIL_PICTURE"]);
			if(is_array($arItem["PREVIEW_PICTURE"]))
				$arItem["PICTURE"] = $arItem["PREVIEW_PICTURE"];
			elseif(is_array($arItem["DETAIL_PICTURE"]))
				$arItem["PICTURE"] = $arItem["DETAIL_PICTURE"];
			$arResult["ITEMS"][]=$arItem;
		}
		$arResult["NAV_STRING"] = $rsElements->GetPageNavStringEx($navComponentObject, $arParams["PAGER_TITLE"], $arParams["PAGER_TEMPLATE"], $arParams["PAGER_SHOW_ALWAYS"]);
		$arResult["NAV_CACHED_DATA"] = $navComponentObject->GetTemplateCachedData();
		$arResult["NAV_RESULT"] = $rsElements;
		//echo "<pre>",htmlspecialchars(print_r($arResult,true)),"</pre>";
		$this->SetResultCacheKeys(array(
			"ID",
			"IBLOCK_ID",
			"NAV_CACHED_DATA",
			$arParams["META_KEYWORDS"],
			$arParams["META_DESCRIPTION"],
			"NAME",
			"PATH",
		));
		$this->IncludeComponentTemplate();
	}
	else
	{
		$this->AbortResultCache();
		ShowError(GetMessage("PHOTO_SECTION_NOT_FOUND"));
		@define("ERROR_404", "Y");
	}
}

if(isset($arResult["ID"]))
{
	if($USER->IsAuthorized())
	{
		if($GLOBALS["APPLICATION"]->GetShowIncludeAreas() && CModule::IncludeModule("iblock"))
			$this->AddIncludeAreaIcons(CIBlock::ShowPanel($arResult["IBLOCK_ID"], 0, $arResult["ID"], $arParams["IBLOCK_TYPE"], true));
		if($arParams["DISPLAY_PANEL"] && CModule::IncludeModule("iblock"))
			CIBlock::ShowPanel($arResult["IBLOCK_ID"], 0, $arResult["ID"], $arParams["IBLOCK_TYPE"], false, $this->GetName());
	}

	$this->SetTemplateCachedData($arResult["NAV_CACHED_DATA"]);

	if(isset($arResult[$arParams["META_KEYWORDS"]]))
	{
		$val = $arResult[$arParams["META_KEYWORDS"]];
		if(is_array($val))
			$val = implode(" ", $val);
		$APPLICATION->SetPageProperty("keywords", $val);
	}

	if(isset($arResult[$arParams["META_DESCRIPTION"]]))
	{
		$val = $arResult[$arParams["META_DESCRIPTION"]];
		if(is_array($val))
			$val = implode(" ", $val);
		$APPLICATION->SetPageProperty("description", $val);
	}

	if($arParams["SET_TITLE"] && $arResult["NAME"])
	{
		$APPLICATION->SetTitle($arResult["NAME"]);
	}

	if($arParams["ADD_SECTIONS_CHAIN"])
	{
		foreach($arResult["PATH"] as $arPath)
		{
			$APPLICATION->AddChainItem($arPath["NAME"], $arPath["SECTION_PAGE_URL"]);
		}
	}
}

?>
