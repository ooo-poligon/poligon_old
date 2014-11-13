<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
?>
<?$APPLICATION->IncludeFile("sale/sale_personal/cc_list.php", array(
	"PATH_TO_DETAIL" => "sale_cc_detail.php"
	));?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>