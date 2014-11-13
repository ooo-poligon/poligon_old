<?
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_after.php");
?>
<?
$PATH_TO_LIST = "profiles.php";
$PATH_TO_SELF = "profile_detail.php";

$APPLICATION->IncludeFile("sale/sale_tmpl_1/profile_detail.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog.php");
?>