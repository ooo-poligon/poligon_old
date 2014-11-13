<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?$APPLICATION->IncludeFile("forum/forum_tmpl_1/active.php", array(
	"TOP"			=> 1000,
	"PAGE_ELEMENTS"	=> 50,
	));?>
<br>
<?$APPLICATION->ShowBanner("ARTICLE_BOTTOM");?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>