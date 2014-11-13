<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
?>
<?
$APPLICATION->IncludeFile("sale/sale_personal/subscribe_cancel.php", array(
		"PATH_TO_CANCEL" => "sale_subscr_cancel.php",
		"PATH_TO_LIST" => "sale_subscr_list.php"
	));?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>