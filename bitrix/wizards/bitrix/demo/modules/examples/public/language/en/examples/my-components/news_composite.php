<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("News");?><?$APPLICATION->IncludeComponent("demo:news", ".default", Array(
	"IBLOCK_TYPE"	=>	"news",
	"IBLOCK_ID"	=>	"#IBLOCK.ID(XML_ID=content-news)#",
	"NEWS_COUNT"	=>	"5",
	"SORT_BY1"	=>	"ACTIVE_FROM",
	"SORT_ORDER1"	=>	"DESC",
	"SORT_BY2"	=>	"SORT",
	"SORT_ORDER2"	=>	"ASC",
	"SEF_MODE"	=>	"N",
	"SEF_FOLDER"	=>	"",
	"CACHE_TYPE"	=>	"A",
	"CACHE_TIME"	=>	"3600",
	"SET_TITLE"	=>	"Y",
	"ADD_SECTIONS_CHAIN"	=>	"N",
	"VARIABLE_ALIASES"	=>	array(
		"SECTION_ID"	=>	"SECTION_ID",
		"ELEMENT_ID"	=>	"NEWS_ID",
	)
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>