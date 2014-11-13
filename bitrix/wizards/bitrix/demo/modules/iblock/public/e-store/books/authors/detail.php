<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle(GetMessage("DEMO_IBLOCK_ESTORE_AUTHORS_DETAIL_TITLE"));
?>


 <?$APPLICATION->IncludeComponent(
	"bitrix:news.detail",
	".default",
	Array(
		"DISPLAY_DATE" => "N",
		"DISPLAY_NAME" => "N",
		"DISPLAY_PICTURE" => "Y",
		"DISPLAY_PREVIEW_TEXT" => "N",
		"IBLOCK_TYPE" => "books",
		"IBLOCK_ID" => "#IBLOCK.ID(XML_ID=books-authors)#",
		"ELEMENT_ID" => $_REQUEST["AUTHOR"],
		"FIELD_CODE" => Array(),
		"PROPERTY_CODE" => Array("BIRTHDATE",""),
		"IBLOCK_URL" => "index.php",
		"META_KEYWORDS" => "-",
		"META_DESCRIPTION" => "-",
		"DISPLAY_PANEL" => "Y",
		"SET_TITLE" => "Y",
		"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
		"ACTIVE_DATE_FORMAT" => "d.m.Y",
		"USE_PERMISSIONS" => "N",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600",
		"DISPLAY_TOP_PAGER" => "N",
		"DISPLAY_BOTTOM_PAGER" => "N",
		"PAGER_TITLE" => GetMessage("DEMO_IBLOCK_ESTORE_AUTHORS_DETAIL_PAGER_TITLE"),
		"PAGER_SHOW_ALWAYS" => "N",
		"PAGER_TEMPLATE" => ""
	)
);?>
<h3>GetMessage('DEMO_IBLOCK_ESTORE_AUTHORS_DETAIL_BOOKS')</h3>
 <?$APPLICATION->IncludeComponent(
	"bitrix:catalog.link.list",
	"",
	Array(
		"IBLOCK_TYPE" => "books",
		"IBLOCK_ID" => "#IBLOCK.ID(XML_ID=books-books)#",
		"LINK_PROPERTY_SID" => "AUTHORS",
		"ELEMENT_ID" => $_REQUEST["AUTHOR"],
		"ELEMENT_SORT_FIELD" => "name",
		"ELEMENT_SORT_ORDER" => "desc",
		"FILTER_NAME" => "arrFilter",
		"SECTION_URL" => "/e-store/books/index.php?SECTION_ID=#SECTION_ID#",
		"DETAIL_URL" => "/e-store/books/index.php?SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#",
		"BASKET_URL" => "/personal/cart/",
		"ACTION_VARIABLE" => "action",
		"PRODUCT_ID_VARIABLE" => "id",
		"SECTION_ID_VARIABLE" => "SECTION_ID",
		"DISPLAY_PANEL" => "N",
		"SET_TITLE" => "N",
		"PAGE_ELEMENT_COUNT" => "30",
		"PROPERTY_CODE" => Array("",""),
		"PRICE_CODE" => Array("BASE"),
		"USE_PRICE_COUNT" => "N",
		"SHOW_PRICE_COUNT" => "1",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600",
		"CACHE_FILTER" => "N",
		"DISPLAY_TOP_PAGER" => "N",
		"DISPLAY_BOTTOM_PAGER" => "Y",
		"PAGER_TITLE" => GetMessage("DEMO_IBLOCK_ESTORE_AUTHORS_DETAIL_BOOKS_PAGER_TITLE"),
		"PAGER_SHOW_ALWAYS" => "N",
		"PAGER_TEMPLATE" => "",
		"PAGER_DESC_NUMBERING" => "N",
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000"
	)
);?> <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>