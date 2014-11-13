<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle(GetMessage("DEMO_IBLOCK_ESTORE_PAID_DETAIL_TITLE"));
?><?$APPLICATION->IncludeComponent("bitrix:news.detail", ".default", Array(
	"IBLOCK_TYPE"	=>	"paid",
	"IBLOCK_ID"	=>	"#IBLOCK.ID(XML_ID=paid-articles)#",
	"ELEMENT_ID"	=>	$_REQUEST["ID"],
	"FIELD_CODE"	=>	array(
		0	=>	"",
		1	=>	"",
	),
	"PROPERTY_CODE"	=>	array(
		0	=>	"AUTHOR",
		1	=>	"",
	),
	"IBLOCK_URL"	=>	"index.php",
	"CACHE_TYPE"	=>	"A",
	"CACHE_TIME"	=>	"3600",
	"META_KEYWORDS"	=>	"-",
	"META_DESCRIPTION"	=>	"-",
	"DISPLAY_PANEL"	=>	"Y",
	"SET_TITLE"	=>	"Y",
	"INCLUDE_IBLOCK_INTO_CHAIN"	=>	"N",
	"ADD_SECTIONS_CHAIN"	=>	"Y",
	"ACTIVE_DATE_FORMAT"	=>	"d.m.Y",
	"USE_PERMISSIONS"	=>	"Y",
	"GROUP_PERMISSIONS"	=>	array(
		0	=>	"1",
		1	=>	"15",
	),
	"DISPLAY_TOP_PAGER"	=>	"N",
	"DISPLAY_BOTTOM_PAGER"	=>	"Y",
	"PAGER_TITLE"	=>	GetMessage("DEMO_IBLOCK_ESTORE_PAID_DETAIL_PAGER_TITLE"),
	"PAGER_TEMPLATE"	=>	"",
	"DISPLAY_DATE"	=>	"Y",
	"DISPLAY_NAME"	=>	"N",
	"DISPLAY_PICTURE"	=>	"Y",
	"DISPLAY_PREVIEW_TEXT"	=>	"Y"
	)
);?> <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>