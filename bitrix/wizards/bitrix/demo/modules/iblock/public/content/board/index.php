<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle(GetMessage("DEMO_IBLOCK_CONTENT_BOARD_TITLE"));
?>
<h4><a href="/content/board/my/?edit=Y">GetMessage('DEMO_IBLOCK_CONTENT_BOARD_ADD_MESSAGE')</a> | <a href="/content/board/my/">GetMessage('DEMO_IBLOCK_CONTENT_BOARD_MY_MESSAGES')</a></h4>
 <?$APPLICATION->IncludeComponent("bitrix:catalog", ".default", Array(
	"IBLOCK_TYPE"	=>	"services",
	"IBLOCK_ID"	=>	"#IBLOCK.ID(XML_ID=services-board)#",
	"USE_FILTER"	=>	"N",
	"USE_REVIEW"	=>	"N",
	"USE_COMPARE"	=>	"N",
	"BASKET_URL"	=>	"/personal/cart/",
	"ACTION_VARIABLE"	=>	"action",
	"PRODUCT_ID_VARIABLE"	=>	"id",
	"SECTION_ID_VARIABLE"	=>	"SECTION_ID",
	"SEF_MODE"	=>	"N",
	"CACHE_TYPE"	=>	"A",
	"CACHE_TIME"	=>	"3600",
	"CACHE_FILTER"	=>	"N",
	"DISPLAY_PANEL"	=>	"Y",
	"SET_TITLE"	=>	"Y",
	"DISPLAY_TOP_PAGER"	=>	"N",
	"DISPLAY_BOTTOM_PAGER"	=>	"Y",
	"PAGER_TITLE"	=>	GetMessage("DEMO_IBLOCK_CONTENT_BOARD_PAGER_TITLE"),
	"PAGER_SHOW_ALWAYS"	=>	"N",
	"PAGER_DESC_NUMBERING"	=>	"N",
	"PAGER_DESC_NUMBERING_CACHE_TIME"	=>	"36000",
	"SECTION_SORT_FIELD"	=>	"sort",
	"SECTION_SORT_ORDER"	=>	"asc",
	"SHOW_TOP_ELEMENTS"	=>	"N",
	"PAGE_ELEMENT_COUNT"	=>	"5",
	"LINE_ELEMENT_COUNT"	=>	"1",
	"ELEMENT_SORT_FIELD"	=>	"sort",
	"ELEMENT_SORT_ORDER"	=>	"asc",
	"LIST_PROPERTY_CODE"	=>	array(
		0	=>	"URL",
		1	=>	"PHONE",
	),
	"DETAIL_PROPERTY_CODE"	=>	array(
		0	=>	"URL",
		1	=>	"PHONE",
	),
	"LINK_ELEMENTS_URL"	=>	"link.php?PARENT_ELEMENT_ID=#ELEMENT_ID#",
	"USE_PRICE_COUNT"	=>	"N",
	"SHOW_PRICE_COUNT"	=>	"1",
	"VARIABLE_ALIASES"	=>	array(
		"SECTION_ID"	=>	"SECTION_ID",
		"ELEMENT_ID"	=>	"ID",
	)
	)
);?>
<br />

<br />
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>