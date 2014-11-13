<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$APPLICATION->SetTitle("");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_after.php");
?><?$APPLICATION->IncludeFile("iblock/catalog/element.php", Array(
	"ELEMENT_ID"	=>	$_REQUEST["ID"],// ID элемента
	"IBLOCK_TYPE"	=>	"catalog",	// Тип инфо-блока
	"SECTION_URL"	=>	"catalog.php?",// URL ведущий на страницу с содержимым раздела
	)
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog.php");?>
