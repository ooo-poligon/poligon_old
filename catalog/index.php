<?
if($_GET['SECTION_ID'] == 5106 or $_GET['SECTION_ID'] == 5615){
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: /catalog/index.php?SECTION_ID=5004");
	exit;	
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");
$APPLICATION->SetPageProperty("description", "продукция от европейских производителей реле, молниезащиты, программируемых таймеров, фотореле, термостатов, микросхем, микроконтроллеров, полупроводников");
?> 
<div align="left" width="960px"><?$APPLICATION->IncludeComponent(
	"bitrix:catalog",
	"catalog_noprice",
	Array(
		"AJAX_MODE" => "N", 
		"SEF_MODE" => "N", 
		"IBLOCK_TYPE" => "catalog", 
		"IBLOCK_ID" => "4", 
		"USE_FILTER" => "N", 
		"USE_COMPARE" => "N", 
		"SHOW_TOP_ELEMENTS" => "N", 
		"PAGE_ELEMENT_COUNT" => "15", 
		"LINE_ELEMENT_COUNT" => "1", 
		"ELEMENT_SORT_FIELD" => "sort", 
		"ELEMENT_SORT_ORDER" => "asc", 
		"LIST_PROPERTY_CODE" => array(0=>"article",1=>"val",2=>"number",3=>"pack",4=>"producer_full",5=>"producer_abbr",6=>"SPEC",7=>"srok",8=>"pdf",9=>"link",10=>"name",11=>"view",12=>"",13=>"PRICE_MATRIX"), 
		"INCLUDE_SUBSECTIONS" => "N", 
		"DETAIL_PROPERTY_CODE" => array(0=>"pdf",1=>"link",2=>"",3=>"",), 
		"BASKET_URL" => "/personal/cart/", 
		"ACTION_VARIABLE" => "action", 
		"PRODUCT_ID_VARIABLE" => "id", 
		"SECTION_ID_VARIABLE" => "SECTION_ID", 
		"DISPLAY_PANEL" => "N", 
		"CACHE_TYPE" => "A", 
		"CACHE_TIME" => "180", 
		"CACHE_FILTER" => "N", 
		"SET_TITLE" => "N", 
		"PRICE_CODE" => array(0=>"BASE",1=>""), 
		"USE_PRICE_COUNT" => "N",
		"SHOW_PRICE_COUNT" => "1", 
		"PRICE_VAT_INCLUDE" => "Y", 
		"PRICE_VAT_SHOW_VALUE" => "N", 
		"LINK_IBLOCK_TYPE" => "", 
		"LINK_IBLOCK_ID" => "", 
		"LINK_PROPERTY_SID" => "", 
		"LINK_ELEMENTS_URL" => "link.php?PARENT_ELEMENT_ID=#ELEMENT_ID#", 
		"DISPLAY_TOP_PAGER" => "N", 
		"DISPLAY_BOTTOM_PAGER" => "Y", 
		"PAGER_TITLE" => "Товары", 
		"PAGER_SHOW_ALWAYS" => "Y", 
		"PAGER_TEMPLATE" => "", 
		"PAGER_DESC_NUMBERING" => "Y", 
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "1800", 
		"AJAX_OPTION_SHADOW" => "Y", 
		"AJAX_OPTION_JUMP" => "N", 
		"AJAX_OPTION_STYLE" => "Y", 
		"AJAX_OPTION_HISTORY" => "N", 
		"VARIABLE_ALIASES" => Array(
			"SECTION_ID" => "SECTION_ID",
			"ELEMENT_ID" => "ELEMENT_ID"
		)
	)
);?></div>
 <?
if (!$APPLICATION->GetTitle()) 
	$APPLICATION->SetTitle("Каталог продукции — реле времени, реле контроля, пускатели, контакторы, таймеры,  электронные компоненты. ");
	
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>