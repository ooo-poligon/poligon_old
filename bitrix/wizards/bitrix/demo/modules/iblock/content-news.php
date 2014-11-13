<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

//Library
include_once(dirname(__FILE__)."/iblock_tools.php");
__IncludeLang(GetLangFileName(dirname(__FILE__)."/lang/", "/".basename(__FILE__)));

//Parameters
if(!is_array($arParams)) $arParams = array();
if(strlen($arParams["site_id"]) <= 0)
	$arParams["site_id"] = "s1";

//Install themes iblock
DEMO_IBlock_ImportXML("010_services_services-themes_ru.xml", $arParams["site_id"], false);

//Import XML
if($IBLOCK_ID = DEMO_IBlock_ImportXML("020_news_content-news_ru.xml", $arParams["site_id"], false))
{
	//Create directory and copy files
	$search = array(
		"#IBLOCK.ID(XML_ID=content-news)#",
	);
	$replace = array(
		$IBLOCK_ID,
	);
	DEMO_IBlock_CopyFiles("/public/content/news/", "/content/news/", false, $search, $replace);

	//Add menu item
	DEMO_IBlock_AddMenuItem("/content/.left.menu.php", Array(
		GetMessage("DEMO_IBLOCK_CONTENT_NEWS_MENU"),
		"/content/news/",
		Array(),
		Array(),
		"",
	));
}
?>