<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle(GetMessage("DEMO_IBLOCK_CONTENT_FAQ_TITLE"));
?><?$APPLICATION->IncludeComponent(
	"bitrix:catalog.section.list",
	"",
	Array(
		"IBLOCK_TYPE" => "services",
		"IBLOCK_ID" => "#IBLOCK.ID(XML_ID=services-faq)#",
		"SECTION_ID" => $_REQUEST["SECTION_ID"],
		"SECTION_SORT_FIELD" => "sort",
		"SECTION_SORT_ORDER" => "asc",
		"SECTION_URL" => "index.php?ID=#SECTION_ID#",
		"DISPLAY_PANEL" => "N",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600"
	)
);?>
<br />
 <?$APPLICATION->IncludeComponent(
	"bitrix:news",
	"",
	Array(
		"SEF_MODE" => "N",
		"IBLOCK_TYPE" => "services",
		"IBLOCK_ID" => "#IBLOCK.ID(XML_ID=services-faq)#",
		"NEWS_COUNT" => "20",
		"USE_SEARCH" => "Y",
		"USE_RSS" => "Y",
		"USE_RATING" => "N",
		"USE_CATEGORIES" => "N",
		"USE_REVIEW" => "N",
		"USE_FILTER" => "N",
		"SORT_BY1" => "TIMESTAMP_X",
		"SORT_ORDER1" => "DESC",
		"SORT_BY2" => "SORT",
		"SORT_ORDER2" => "ASC",
		"PREVIEW_TRUNCATE_LEN" => "",
		"LIST_ACTIVE_DATE_FORMAT" => "d.m.Y",
		"LIST_FIELD_CODE" => Array("",""),
		"LIST_PROPERTY_CODE" => Array("",""),
		"HIDE_LINK_WHEN_NO_DETAIL" => "Y",
		"META_KEYWORDS" => "-",
		"META_DESCRIPTION" => "-",
		"DETAIL_ACTIVE_DATE_FORMAT" => "d.m.Y",
		"DETAIL_FIELD_CODE" => Array("",""),
		"DETAIL_PROPERTY_CODE" => Array("",""),
		"DETAIL_DISPLAY_TOP_PAGER" => "N",
		"DETAIL_DISPLAY_BOTTOM_PAGER" => "Y",
		"DETAIL_PAGER_TITLE" => GetMessage("DEMO_IBLOCK_CONTENT_FAQ_DETAIL_PAGER_TITLE"),
		"DETAIL_PAGER_TEMPLATE" => "",
		"DISPLAY_PANEL" => "N",
		"SET_TITLE" => "Y",
		"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
		"USE_PERMISSIONS" => "N",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600",
		"CACHE_FILTER" => "N",
		"DISPLAY_TOP_PAGER" => "N",
		"DISPLAY_BOTTOM_PAGER" => "Y",
		"PAGER_TITLE" => GetMessage("DEMO_IBLOCK_CONTENT_FAQ_PAGER_TITLE"),
		"PAGER_SHOW_ALWAYS" => "N",
		"PAGER_TEMPLATE" => "",
		"PAGER_DESC_NUMBERING" => "N",
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
		"NUM_NEWS" => "20",
		"NUM_DAYS" => "30",
		"YANDEX" => "N",
		"VARIABLE_ALIASES" => Array(
			"SECTION_ID" => "ID",
			"ELEMENT_ID" => "ELEMENT_ID"
		)
	)
);?>
<br />
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>