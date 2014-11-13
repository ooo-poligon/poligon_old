<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle(GetMessage("DEMO_IBLOCK_ESTORE_XMLCAT_TITLE"));
?>
<?if(IsModuleInstalled('sale')):?>
<div align="right"><?$APPLICATION->IncludeComponent(
	"bitrix:sale.basket.basket.line",
	"",
	Array(
		"PATH_TO_BASKET" => "/personal/cart/",
		"PATH_TO_PERSONAL" => "/personal/",
		"SHOW_PERSONAL_LINK" => "N"
	)
);?></div>
<?endif?>
 <?$APPLICATION->IncludeComponent("bitrix:catalog", ".default", Array(
	"IBLOCK_TYPE"	=>	"xmlcatalog",
	"IBLOCK_ID"	=>	"#IBLOCK.ID(XML_ID=FUTURE-1C-CATALOG)#",
	"USE_FILTER"	=>	"Y",
	"USE_REVIEW"	=>	"N",
	"USE_COMPARE"	=>	"Y",
	"BASKET_URL"	=>	"/personal/cart/",
	"ACTION_VARIABLE"	=>	"action",
	"PRODUCT_ID_VARIABLE"	=>	"id",
	"SECTION_ID_VARIABLE"	=>	"SECTION_ID",
	"SEF_MODE"	=>	"N",
	"SEF_FOLDER"	=>	"/e-store/xml_catalog/",
	"CACHE_TYPE"	=>	"A",
	"CACHE_TIME"	=>	"3600",
	"CACHE_FILTER"	=>	"N",
	"DISPLAY_PANEL"	=>	"Y",
	"SET_TITLE"	=>	"Y",
	"FILTER_NAME"	=>	"FILTER",
	"FILTER_FIELD_CODE"	=>	array(
		0	=>	"NAME",
	),
	"FILTER_PROPERTY_CODE"	=>	array(
		0	=>	"CML2_ARTICLE",
	),
	"COMPARE_NAME"	=>	"CATALOG_COMPARE_LIST",
	"COMPARE_FIELD_CODE"	=>	array(
	),
	"COMPARE_PROPERTY_CODE"	=>	array(
		0	=>	"CML2_ARTICLE",
		1	=>	"CML2_BASE_UNIT",
		2	=>	"CML2_TRAITS",
		3	=>	"CML2_ATTRIBUTES",
		4	=>	"CML2_BAR_CODE",
	),
	"DISPLAY_ELEMENT_SELECT_BOX"	=>	"N",
	"ELEMENT_SORT_FIELD_BOX"	=>	"name",
	"ELEMENT_SORT_ORDER_BOX"	=>	"asc",
	"COMPARE_ELEMENT_SORT_FIELD"	=>	"sort",
	"COMPARE_ELEMENT_SORT_ORDER"	=>	"asc",
	"PAGE_ELEMENT_COUNT"	=>	"5",
	"LINE_ELEMENT_COUNT"	=>	"1",
	"ELEMENT_SORT_FIELD"	=>	"sort",
	"ELEMENT_SORT_ORDER"	=>	"asc",
	"LIST_PROPERTY_CODE"	=>	array(
		0	=>	"CML2_ARTICLE",
		1	=>	"CML2_BASE_UNIT",
		2	=>	"CML2_TRAITS",
		3	=>	"CML2_ATTRIBUTES",
		4	=>	"CML2_BAR_CODE",
		5	=>	"",
	),
	"SECTION_SORT_FIELD"	=>	"sort",
	"SECTION_SORT_ORDER"	=>	"asc",
	"SHOW_TOP_ELEMENTS"	=>	"Y",
	"TOP_ELEMENT_COUNT"	=>	"3",
	"TOP_LINE_ELEMENT_COUNT"	=>	"1",
	"TOP_ELEMENT_SORT_FIELD"	=>	"id",
	"TOP_ELEMENT_SORT_ORDER"	=>	"desc",
	"TOP_PROPERTY_CODE"	=>	array(
		0	=>	"CML2_ARTICLE",
		1	=>	"CML2_BASE_UNIT",
		2	=>	"CML2_TRAITS",
		3	=>	"CML2_ATTRIBUTES",
		4	=>	"",
	),
	"DETAIL_PROPERTY_CODE"	=>	array(
		0	=>	"CML2_ARTICLE",
		1	=>	"CML2_BASE_UNIT",
		2	=>	"CML2_TRAITS",
		3	=>	"CML2_ATTRIBUTES",
		4	=>	"CML2_BAR_CODE",
		5	=>	"",
	),
	"LINK_IBLOCK_TYPE"	=>	"",
	"LINK_IBLOCK_ID"	=>	"",
	"LINK_PROPERTY_SID"	=>	"",
	"LINK_ELEMENTS_URL"	=>	"",
	"DISPLAY_TOP_PAGER"	=>	"N",
	"DISPLAY_BOTTOM_PAGER"	=>	"Y",
	"PAGER_TITLE"	=>	GetMessage("DEMO_IBLOCK_ESTORE_XMLCAT_PAGER_TITLE"),
	"PAGER_SHOW_ALWAYS"	=>	"N",
	"PAGER_TEMPLATE"	=>	"",
	"PAGER_DESC_NUMBERING"	=>	"N",
	"PAGER_DESC_NUMBERING_CACHE_TIME"	=>	"36000",
	"PRICE_CODE"	=>	array(
		0	=>	GetMessage("DEMO_IBLOCK_ESTORE_XMLCAT_PRICE_CODE"),
	),
	"USE_PRICE_COUNT"	=>	"N",
	"SHOW_PRICE_COUNT"	=>	"1",
	"VARIABLE_ALIASES"	=>	array(
		"SECTION_ID"	=>	"SECTION_ID",
		"ELEMENT_ID"	=>	"ELEMENT_ID",
	)
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>