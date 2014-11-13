<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
?>
<?
$APPLICATION->IncludeFile("sale/sale_personal/order_list.php", Array(
		"PATH_TO_DETAIL" => "sale_order_detail.php",
		"PATH_TO_COPY" => "index.php",
		"PATH_TO_CANCEL" => "sale_order_cancel.php",
		"PATH_TO_BASKET" => "basket.php"
	));
?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>