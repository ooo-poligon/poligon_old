<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
?>
<?
$APPLICATION->IncludeFile("sale/sale_personal/subscribe_list.php", array(
		"PATH_TO_CANCEL" => "sale_subscr_cancel.php"
	));?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>