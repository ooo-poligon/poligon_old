<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Сравнение телефонов");
?>&nbsp;<?$APPLICATION->IncludeFile("iblock/catalog/compare_table.php", Array(
	"IBLOCK_TYPE"	=>	"catalog",		// Info-block type
	"IBLOCK_ID"	=>	"21",			// Info-block
	"NAME"	=>	"CATALOG_COMPARE_LIST",	// Unique name for the comparison list
	"arrFIELD_CODE"	=>	Array(		// Fields
					"NAME",
					"PREVIEW_PICTURE"
				),
	"arrPROPERTY_CODE"	=>	Array(		// Properties
					"YEAR",
					"STANDBY_TIME",
					"TALKTIME",
					"WEIGHT",
					"STANDART",
					"SIZE",
					"BATTERY"
				),
	"arrPRICE_CODE"	=>	Array("RETAIL"),	// Price types
	"BASKET_URL"	=>	"/personal/basket.php",// URL of the page with the customer cart
	"ELEMENT_SORT_FIELD"	=>	"shows",	// Field to sort elements
	"ELEMENT_SORT_ORDER"	=>	"desc",	// Sort order for elements
	"DISPLAY_ELEMENT_SELECT_BOX"	=>	"Y",	// Display element list
	"ELEMENT_SORT_FIELD_BOX"	=>	"name",	// Field to sort elements in select box
	"ELEMENT_SORT_ORDER_BOX"	=>	"asc",	// Sort order for elements
	"CACHE_TIME"	=>	"0",			// Cache time (sec.)
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>