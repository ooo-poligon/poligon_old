<?
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->IncludeFile("forum/forum_tmpl_1/move_message.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>