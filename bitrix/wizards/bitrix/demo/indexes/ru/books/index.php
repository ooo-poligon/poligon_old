<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Демонстрационная версия продукта «1С-Битрикс: Управление сайтом»");
$APPLICATION->SetPageProperty("NOT_SHOW_NAV_CHAIN", "Y");
$APPLICATION->SetTitle("Каталог книг");
?> <?$APPLICATION->IncludeComponent(
	"bitrix:catalog.section.list",
	"",
	Array(
		"IBLOCK_TYPE" => "books", 
		"IBLOCK_ID" => "#SERVICE_IBLOCK_ID#", 
		"SECTION_ID" => $_REQUEST["SECTION_ID"], 
		"SECTION_URL" => "/e-store/books/index.php?SECTION_ID=#SECTION_ID#", 
		"COUNT_ELEMENTS" => "Y", 
		"DISPLAY_PANEL" => "N", 
		"CACHE_TYPE" => "A", 
		"CACHE_TIME" => "3600" 
	)
);?>
<hr />

<?$APPLICATION->IncludeComponent(
	"bitrix:catalog.top",
	"",
	Array(
		"IBLOCK_TYPE" => "books", 
		"IBLOCK_ID" => "#SERVICE_IBLOCK_ID#", 
		"ELEMENT_SORT_FIELD" => "sort", 
		"ELEMENT_SORT_ORDER" => "asc", 
		"SECTION_URL" => "/e-store/books/index.php?SECTION_ID=#SECTION_ID#", 
		"DETAIL_URL" => "/e-store/books/index.php?SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#", 
		"BASKET_URL" => "/personal/cart/", 
		"ACTION_VARIABLE" => "action", 
		"PRODUCT_ID_VARIABLE" => "id", 
		"SECTION_ID_VARIABLE" => "SECTION_ID", 
		"DISPLAY_COMPARE" => "N", 
		"ELEMENT_COUNT" => "3", 
		"LINE_ELEMENT_COUNT" => "1", 
		"PROPERTY_CODE" => Array(), 
		"PRICE_CODE" => Array("RETAIL"), 
		"USE_PRICE_COUNT" => "N", 
		"SHOW_PRICE_COUNT" => "1", 
		"CACHE_TYPE" => "A", 
		"CACHE_TIME" => "3600" 
	)
);?>
 <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>