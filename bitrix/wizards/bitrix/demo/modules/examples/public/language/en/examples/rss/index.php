<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("RSS Import");
?><?$APPLICATION->IncludeComponent(
	"bitrix:rss.show",
	"",
	Array(
		"SITE" => "www.bitrixsoft.com", 
		"PORT" => "80", 
		"PATH" => "/sitemanager/news/rss.php", 
		"QUERY_STR" => "", 
		"OUT_CHANNEL" => "N", 
		"NUM_NEWS" => "10", 
		"CACHE_TIME" => "3600" 
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>