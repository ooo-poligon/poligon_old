<?
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
?>
<?
$APPLICATION->IncludeFile("sale/sale_personal/order_cancel.php", array(
		"PATH_TO_DETAIL" => "sale_order_detail.php",
		"PATH_TO_LIST" => "index.php",
		"PATH_TO_CANCEL" => "sale_order_cancel.php"
	));?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>