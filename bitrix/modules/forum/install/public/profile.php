<?
Define("NEED_AUTH", True);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
?>
<?
$APPLICATION->IncludeFile("forum/forum_tmpl_1/profile.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>