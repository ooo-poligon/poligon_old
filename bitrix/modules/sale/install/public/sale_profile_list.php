<?
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
?>
<?$APPLICATION->IncludeFile("sale/sale_personal/profile_list.php", array(
		"PATH_TO_DETAIL" => "sale_profile_detail.php"
	));?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>