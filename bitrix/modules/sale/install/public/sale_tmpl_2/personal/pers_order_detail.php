<?
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_after.php");
?>
<?
$PATH_TO_LIST = "pers_order.php";
$PATH_TO_CANCEL = "pers_order_cancel.php";
$PATH_TO_PAYMENT = "payment.php";

$APPLICATION->IncludeFile("sale/sale_tmpl_2/pers_order_detail.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog.php");
?>