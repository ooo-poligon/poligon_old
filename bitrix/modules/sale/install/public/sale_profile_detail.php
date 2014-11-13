<?
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
?>
<?
$APPLICATION->IncludeFile("sale/sale_personal/profile_detail.php", array(
		"PATH_TO_LIST" => "sale_profile_list.php",
		"PATH_TO_SELF" => "sale_profile_detail.php"
	));?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>