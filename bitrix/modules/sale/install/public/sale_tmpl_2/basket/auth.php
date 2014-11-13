<?
define("NEED_AUTH", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
if (strlen($back_url)<=0) $back_url = "index.php";
LocalRedirect($back_url);
?>