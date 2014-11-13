<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

global $APPLICATION;

$aMenuLinksAdd = $APPLICATION->IncludeComponent(
	"bitrix:menu.sections",
	"",
	Array(
		"ID" => $_REQUEST["news"],
		"IBLOCK_TYPE" => "news",
		"IBLOCK_ID" => "#IBLOCK.ID(XML_ID=content-news)#",
		"SECTION_URL" => "/content/news/index.php?SECTION_ID=#ID#",
		"CACHE_TIME" => "3600"
	)
);

$aMenuLinks = array_merge($aMenuLinks, $aMenuLinksAdd);
?>