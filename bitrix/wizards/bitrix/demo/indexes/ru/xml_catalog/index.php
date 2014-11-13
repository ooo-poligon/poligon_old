<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Демонстрационная версия продукта «1С-Битрикс: Управление сайтом»");
$APPLICATION->SetPageProperty("NOT_SHOW_NAV_CHAIN", "Y");
$APPLICATION->SetTitle("Каталог товаров из 1C:Предприятие");
?>

<?$APPLICATION->IncludeComponent(
	"bitrix:catalog.section.list",
	"",
	Array(
		"IBLOCK_TYPE" => "xml_catalog", 
		"IBLOCK_ID" => "#SERVICE_IBLOCK_ID#", 
		"SECTION_ID" =>"", 
		"SECTION_URL" => "/e-store/xml_catalog/index.php?SECTION_ID=#SECTION_ID#", 
		"COUNT_ELEMENTS" => "Y", 
		"DISPLAY_PANEL" => "N", 
		"CACHE_TYPE" => "A", 
		"CACHE_TIME" => "3600" 
	)
);?> 
<hr />

<?$APPLICATION->IncludeComponent("bitrix:catalog.top", ".default", Array(
	"IBLOCK_TYPE"	=>	"xml_catalog",
	"IBLOCK_ID"	=>	"#SERVICE_IBLOCK_ID#",
	"ELEMENT_SORT_FIELD"	=>	"sort",
	"ELEMENT_SORT_ORDER"	=>	"asc",
	"ELEMENT_COUNT"	=>	"3",
	"LINE_ELEMENT_COUNT"	=>	"1",
	"PROPERTY_CODE"	=>	array(
		1	=>	"CML2_ARTICLE",
		2	=>	"CML2_BASE_UNIT",
		3	=>	"CML2_TRAITS",
		4	=>	"CML2_ATTRIBUTES",
		5	=>	"CML2_BAR_CODE",
	),
	"SECTION_URL"	=>	"/e-store/xml_catalog/index.php?SECTION_ID=#SECTION_ID#",
	"DETAIL_URL"	=>	"/e-store/xml_catalog/index.php?SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#",
	"BASKET_URL"	=>	"/personal/cart/",
	"ACTION_VARIABLE"	=>	"action",
	"PRODUCT_ID_VARIABLE"	=>	"id",
	"SECTION_ID_VARIABLE"	=>	"SECTION_ID",
	"CACHE_TYPE"	=>	"A",
	"CACHE_TIME"	=>	"3600",
	"DISPLAY_COMPARE"	=>	"N",
	"PRICE_CODE"	=>	array(
		0	=>	"Розничная",
	),
	"USE_PRICE_COUNT"	=>	"N",
	"SHOW_PRICE_COUNT"	=>	"1"
	)
);?> <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>