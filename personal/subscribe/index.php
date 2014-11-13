<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Рассылки");
?><?$APPLICATION->IncludeComponent("bitrix:subscribe.index", "template1", Array(
	"SHOW_COUNT"	=>	"N",
	"SHOW_HIDDEN"	=>	"N",
	"PAGE"	=>	"#SITE_DIR#personal/subscribe/subscr_edit.php",
	"CACHE_TYPE"	=>	"A",
	"CACHE_TIME"	=>	"3600",
	"SET_TITLE"	=>	"Y"
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>