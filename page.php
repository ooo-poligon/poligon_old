<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Новая страница");
?>Text here....<?$APPLICATION->IncludeComponent("bitrix:menu", "topnav_new", Array(
	"ROOT_MENU_TYPE"	=>	"topnav_new",
	"MAX_LEVEL"	=>	"1",
	"CHILD_MENU_TYPE"	=>	"",
	"USE_EXT"	=>	"N"
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>