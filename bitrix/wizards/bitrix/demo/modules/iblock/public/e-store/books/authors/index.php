<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle(GetMessage("DEMO_IBLOCK_ESTORE_AUTHORS_TITLE"));
?>

 <?$APPLICATION->IncludeComponent("bitrix:news.list", ".default", Array(
	"IBLOCK_TYPE"	=>	"books",
	"IBLOCK_ID"	=>	"#IBLOCK.ID(XML_ID=books-authors)#",
	"NEWS_COUNT"	=>	"20",
	"SORT_BY1"	=>	"NAME",
	"SORT_ORDER1"	=>	"ASC",
	"SORT_BY2"	=>	"SORT",
	"SORT_ORDER2"	=>	"ASC",
	"FIELD_CODE"	=>	array(
		0	=>	"",
	),
	"PROPERTY_CODE"	=>	array(
		0	=>	"",
	),
	"DETAIL_URL"	=>	"detail.php?AUTHOR=#ELEMENT_ID#",
	"PREVIEW_TRUNCATE_LEN"	=>	"0",
	"ACTIVE_DATE_FORMAT"	=>	"d.m.Y",
	"DISPLAY_PANEL"	=>	"Y",
	"SET_TITLE"	=>	"Y",
	"INCLUDE_IBLOCK_INTO_CHAIN"	=>	"N",
	"HIDE_LINK_WHEN_NO_DETAIL"	=>	"N",
	"CACHE_TYPE"	=>	"A",
	"CACHE_TIME"	=>	"3600",
	"CACHE_FILTER"	=>	"N",
	"DISPLAY_TOP_PAGER"	=>	"N",
	"DISPLAY_BOTTOM_PAGER"	=>	"Y",
	"PAGER_TITLE"	=>	GetMessage("DEMO_IBLOCK_ESTORE_AUTHORS_PAGER_TITLE"),
	"PAGER_SHOW_ALWAYS"	=>	"N",
	"PAGER_DESC_NUMBERING"	=>	"N",
	"PAGER_DESC_NUMBERING_CACHE_TIME"	=>	"36000",
	"DISPLAY_DATE"	=>	"Y",
	"DISPLAY_NAME"	=>	"Y",
	"DISPLAY_PICTURE"	=>	"Y",
	"DISPLAY_PREVIEW_TEXT"	=>	"Y"
	)
);?> <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>