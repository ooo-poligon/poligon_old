<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Поиск");?>

<?$APPLICATION->IncludeComponent("bitrix:search.page", "search_noprice", Array(
	"RESTART"	=>	"Y",
	"CHECK_DATES"	=>	"N",
	"USE_TITLE_RANK"	=>	"Y",
	"arrWHERE"	=>	array(
		0	=>	"iblock_news",
		1	=>	"",
	),
	"arrFILTER"	=>	array(
		0	=>	"no",
		1	=>	"",
	),
	"SHOW_WHERE"	=>	"Y",
	"PAGE_RESULT_COUNT"	=>	"10",
	"AJAX_MODE"	=>	"N",
	"AJAX_OPTION_SHADOW"	=>	"Y",
	"AJAX_OPTION_JUMP"	=>	"N",
	"AJAX_OPTION_STYLE"	=>	"Y",
	"AJAX_OPTION_HISTORY"	=>	"N",
	"CACHE_TYPE"	=>	"A",
	"CACHE_TIME"	=>	"3600",
	"PAGER_TITLE"	=>	"Search results",
	"PAGER_SHOW_ALWAYS"	=>	"Y",
	"PAGER_TEMPLATE"	=>	"",
	"TAGS_SORT"	=>	"NAME",
	"TAGS_PAGE_ELEMENTS"	=>	"150",
	"TAGS_PERIOD"	=>	"",
	"TAGS_URL_SEARCH"	=>	"",
	"TAGS_INHERIT"	=>	"Y",
	"FONT_MAX"	=>	"50",
	"FONT_MIN"	=>	"10",
	"COLOR_NEW"	=>	"000000",
	"COLOR_OLD"	=>	"C8C8C8",
	"PERIOD_NEW_TAGS"	=>	"",
	"SHOW_CHAIN"	=>	"Y",
	"COLOR_TYPE"	=>	"Y",
	"WIDTH"	=>	"100%"
	)
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
