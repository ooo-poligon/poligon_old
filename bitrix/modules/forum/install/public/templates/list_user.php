<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?$APPLICATION->IncludeFile("forum/forum_tmpl_1/list_user.php", array(
	"TOP"			=> 1000,
	"PAGE_ELEMENTS"	=> 15,
	));?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>