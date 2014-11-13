<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");?><?$APPLICATION->IncludeComponent(
	"demo:news.detail",
	".default",
	Array(
		"DISPLAY_DATE" => "Y",
		"DISPLAY_NAME" => "N",
		"DISPLAY_PICTURE" => "Y",
		"IBLOCK_TYPE" => "news",
		"IBLOCK_ID" => "#IBLOCK.ID(XML_ID=content-news)#",
		"ELEMENT_ID" => $_REQUEST["ID"],
		"IBLOCK_URL" => "news_list.php",
		"DISPLAY_PANEL" => "N",
		"SET_TITLE" => "Y",
		"ADD_SECTIONS_CHAIN" => "N",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600"
	)
);?> <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>