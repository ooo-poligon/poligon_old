<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle(GetMessage("DEMO_IBLOCK_CONTENT_NEWS_TITLE"));
?><?$APPLICATION->IncludeComponent("bitrix:news", ".default", Array(
	"SEF_MODE"	=>	"N",
	"IBLOCK_TYPE"	=>	"news",
	"IBLOCK_ID"	=>	"#IBLOCK.ID(XML_ID=content-news)#",
	"NEWS_COUNT"	=>	"5",
	"USE_SEARCH"	=>	"N",
	"USE_RSS"	=>	"Y",
	"USE_RATING"	=>	"N",
	"USE_CATEGORIES"	=>	"Y",
	"USE_REVIEW"	=>	"N",
	"USE_FILTER"	=>	"N",
	"SORT_BY1"	=>	"ACTIVE_FROM",
	"SORT_ORDER1"	=>	"DESC",
	"SORT_BY2"	=>	"SORT",
	"SORT_ORDER2"	=>	"ASC",
	"PREVIEW_TRUNCATE_LEN"	=>	"0",
	"LIST_ACTIVE_DATE_FORMAT"	=>	"d.m.Y",
	"LIST_FIELD_CODE"	=>	array(
		0	=>	"",
	),
	"LIST_PROPERTY_CODE"	=>	array(
		0	=>	"",
	),
	"META_KEYWORDS"	=>	"KEYWORDS",
	"META_DESCRIPTION"	=>	"DESCRIPTION",
	"DETAIL_ACTIVE_DATE_FORMAT"	=>	"d.m.Y",
	"DETAIL_FIELD_CODE"	=>	array(
		0	=>	"",
	),
	"DETAIL_PROPERTY_CODE"	=>	array(
		0	=>	"SOURCE",
	),
	"DETAIL_DISPLAY_TOP_PAGER"	=>	"N",
	"DETAIL_DISPLAY_BOTTOM_PAGER"	=>	"Y",
	"DETAIL_PAGER_TITLE"	=>	GetMessage("DEMO_IBLOCK_CONTENT_NEWS_DETAIL_PAGER_TITLE"),
	"DISPLAY_PANEL"	=>	"Y",
	"SET_TITLE"	=>	"Y",
	"INCLUDE_IBLOCK_INTO_CHAIN"	=>	"N",
	"USE_PERMISSIONS"	=>	"N",
	"CACHE_TYPE"	=>	"A",
	"CACHE_TIME"	=>	"3600",
	"CACHE_FILTER"	=>	"N",
	"DISPLAY_TOP_PAGER"	=>	"N",
	"DISPLAY_BOTTOM_PAGER"	=>	"Y",
	"PAGER_TITLE"	=>	GetMessage("DEMO_IBLOCK_CONTENT_NEWS_PAGER_TITLE"),
	"PAGER_SHOW_ALWAYS"	=>	"N",
	"PAGER_DESC_NUMBERING"	=>	"N",
	"PAGER_DESC_NUMBERING_CACHE_TIME"	=>	"36000",
	"NUM_NEWS"	=>	"20",
	"NUM_DAYS"	=>	"360",
	"YANDEX"	=>	"N",
	"CATEGORY_IBLOCK"	=>	array(
		0	=>	"#IBLOCK.ID(XML_ID=content-news)#",
	),
	"CATEGORY_CODE"	=>	"THEMES",
	"CATEGORY_ITEMS_COUNT"	=>	"4",
	"CATEGORY_THEME_#ID(XML_ID=content-news)#"	=>	"list",
	"VARIABLE_ALIASES"	=>	array(
		"SECTION_ID"	=>	"SECTION_ID",
		"ELEMENT_ID"	=>	"news",
	)
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>