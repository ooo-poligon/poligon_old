<?
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_after.php");
?>
<?
$PATH_TO_DETAIL = "pers_order_detail.php";
$PATH_TO_LIST = "pers_order.php";
$PATH_TO_CANCEL = "pers_order_cancel.php";

$APPLICATION->IncludeFile("sale/sale_tmpl_1/pers_order_cancel.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog.php");
?>