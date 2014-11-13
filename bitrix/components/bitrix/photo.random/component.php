<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/*************************************************************************
	Processing of received parameters
*************************************************************************/
if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 180;

if(!is_array($arParams["IBLOCKS"]))
	$arParams["IBLOCKS"] = array($arParams["IBLOCKS"]);

$arIBlockFilter = array();
foreach($arParams["IBLOCKS"] as $IBLOCK_ID)
{
	$IBLOCK_ID=intval($IBLOCK_ID);
	if($IBLOCK_ID>0)
		$arIBlockFilter[]=$IBLOCK_ID;
}

if(count($arIBlockFilter)<=0)
{
	if(!CModule::IncludeModule("iblock"))
	{
		ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
		return;
	}
	$rsIBlocks = GetIBlockList($arParams["IBLOCK_TYPE"]);
	if($arIBlock = $rsIBlocks->GetNext())
		$arIBlockFilter[]=$arIBlock["ID"];
}

unset($arParams["IBLOCK_TYPE"]);
$arParams["PARENT_SECTION"] = intval($arParams["PARENT_SECTION"]);
$arParams["IBLOCKS"] = $arIBlockFilter;

if(count($arIBlockFilter)>0 && $this->StartResultCache(false, $USER->GetGroups()))
{
	if(!CModule::IncludeModule("iblock"))
	{
		$this->AbortResultCache();
		ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
		return;
	}
	//SELECT
	$arSelect = array(
		"ID",
		"IBLOCK_ID",
		"CODE",
		"IBLOCK_SECTION_ID",
		"NAME",
		"PREVIEW_PICTURE",
		"DETAIL_PICTURE",
		"DETAIL_PAGE_URL",
	);
	//WHERE
	$arFilter = array(
		"IBLOCK_ID" => $arParams["IBLOCKS"],
		"ACTIVE_DATE" => "Y",
		"ACTIVE"=>"Y",
		"CHECK_PERMISSIONS"=>"Y",
	);
	if($arParams["PARENT_SECTION"]>0)
	{
		$arFilter["SECTION_ID"] = $arParams["PARENT_SECTION"];
		$arFilter["INCLUDE_SUBSECTIONS"] = "Y";
	}
	//ORDER BY
	$arSort = array(
		"RAND"=>"ASC",
	);
	//EXECUTE
	$rsIBlockElement = CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);
	if($arResult = $rsIBlockElement->GetNext())
	{
		if(strlen($arParams["DETAIL_URL"]) > 0)
		{
			if(strstr($arParams["DETAIL_URL"], "#SECTION_CODE#")!==false)
			{
				$arSectionFilter = array(
					"ID"=>$arResult["IBLOCK_SECTION_ID"],
					"IBLOCK_ID"=>$arResult["IBLOCK_ID"],
					"ACTIVE" => "Y",
				);
				$rsSection = CIBlockSection::GetList(Array(),$arSectionFilter);
				if($arSection = $rsSection->GetNext())
					$arResult["IBLOCK_SECTION_CODE"] = $arSection["CODE"];
				else
					$arResult["IBLOCK_SECTION_CODE"] = "";
			}
			$arResult["DETAIL_PAGE_URL"] = htmlspecialchars(str_replace(
				array("#SERVER_NAME#", "#SITE_DIR#", "#IBLOCK_ID#", "#SECTION_ID#", "#SECTION_CODE#", "#ELEMENT_ID#", "#ELEMENT_CODE#"),
				array(SITE_SERVER_NAME, SITE_DIR, $arResult["IBLOCK_ID"], $arResult["IBLOCK_SECTION_ID"], $arResult["IBLOCK_SECTION_CODE"], $arResult["ID"], $arResult["CODE"]),
				$arParams["DETAIL_URL"]
			));
		}
		$arResult["PICTURE"] = CFile::GetFileArray($arResult["PREVIEW_PICTURE"]);
		if(!is_array($arResult["PICTURE"]))
			$arResult["PICTURE"] = CFile::GetFileArray($arResult["DETAIL_PICTURE"]);

		$this->SetResultCacheKeys(array(
		));
		$this->IncludeComponentTemplate();
	}
	else
	{
		$this->AbortResultCache();
	}
}
?>
