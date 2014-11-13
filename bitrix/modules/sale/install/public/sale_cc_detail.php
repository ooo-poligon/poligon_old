<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
?>
<?
$APPLICATION->IncludeFile("sale/sale_personal/cc_detail.php", array(
		"PATH_TO_LIST" => "sale_cc_list.php",
		"PATH_TO_SELF" => "sale_cc_detail.php"
	));?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>