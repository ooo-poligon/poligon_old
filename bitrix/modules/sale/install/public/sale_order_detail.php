<?
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
?>
<?
$APPLICATION->IncludeFile("sale/sale_personal/order_detail.php", array(
		"PATH_TO_LIST" => "index.php",
		"PATH_TO_CANCEL" => "sale_order_cancel.php",
		"PATH_TO_PAYMENT" => "sale_payment.php"
	));?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>