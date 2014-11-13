<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle(GetMessage("DEMO_IBLOCK_CONTENT_LINKS_TITLE"));
?>
<h4><a href="/content/links/my/?edit=Y">GetMessage('DEMO_IBLOCK_CONTENT_LINKS_ADD_SITE')</a> | <a href="/content/links/my/">GetMessage('DEMO_IBLOCK_CONTENT_LINKS_MY_SITES')</a></h4>
 <?$APPLICATION->IncludeComponent("bitrix:catalog", ".default", Array(
	"SEF_MODE"	=>	"N",
	"IBLOCK_TYPE"	=>	"services",
	"IBLOCK_ID"	=>	"#IBLOCK.ID(XML_ID=services-links)#",
	"USE_FILTER"	=>	"N",
	"USE_REVIEW"	=>	"N",
	"USE_COMPARE"	=>	"N",
	"SECTION_SORT_FIELD"	=>	"name",
	"SECTION_SORT_ORDER"	=>	"asc",
	"SHOW_TOP_ELEMENTS"	=>	"N",
	"PAGE_ELEMENT_COUNT"	=>	"10",
	"LINE_ELEMENT_COUNT"	=>	"1",
	"ELEMENT_SORT_FIELD"	=>	"sort",
	"ELEMENT_SORT_ORDER"	=>	"asc",
	"LIST_PROPERTY_CODE"	=>	array(
		0	=>	"URL",
	),
	"DETAIL_PROPERTY_CODE"	=>	array(
		0	=>	"URL",
	),
	"BASKET_URL"	=>	"/personal/cart/",
	"ACTION_VARIABLE"	=>	"action",
	"PRODUCT_ID_VARIABLE"	=>	"id",
	"SECTION_ID_VARIABLE"	=>	"SECTION_ID",
	"DISPLAY_PANEL"	=>	"N",
	"CACHE_TYPE"	=>	"A",
	"CACHE_TIME"	=>	"3600",
	"CACHE_FILTER"	=>	"N",
	"SET_TITLE"	=>	"Y",
	"PRICE_CODE"	=>	array(
		0	=>	"",
	),
	"USE_PRICE_COUNT"	=>	"N",
	"SHOW_PRICE_COUNT"	=>	"1",
	"LINK_ELEMENTS_URL"	=>	"link.php?PARENT_ELEMENT_ID=#ELEMENT_ID#",
	"DISPLAY_TOP_PAGER"	=>	"N",
	"DISPLAY_BOTTOM_PAGER"	=>	"Y",
	"PAGER_TITLE"	=>	GetMessage("DEMO_IBLOCK_CONTENT_LINKS_PAGER_TITLE"),
	"PAGER_SHOW_ALWAYS"	=>	"N",
	"PAGER_DESC_NUMBERING"	=>	"N",
	"PAGER_DESC_NUMBERING_CACHE_TIME"	=>	"36000",
	"TOP_ELEMENT_COUNT"	=>	"9",
	"TOP_LINE_ELEMENT_COUNT"	=>	"3",
	"TOP_ELEMENT_SORT_FIELD"	=>	"sort",
	"TOP_ELEMENT_SORT_ORDER"	=>	"asc",
	"TOP_PROPERTY_CODE"	=>	array(
		0	=>	"",
	),
	"VARIABLE_ALIASES"	=>	array(
		"SECTION_ID"	=>	"SECTION_ID",
		"ELEMENT_ID"	=>	"LINK_ID",
	)
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>