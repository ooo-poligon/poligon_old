<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$APPLICATION->SetTitle("");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_after.php");
?><?$APPLICATION->IncludeFile("iblock/catalog/element.php", Array(
	"ELEMENT_ID"	=>	$_REQUEST["ID"],// ID ��������
	"IBLOCK_TYPE"	=>	"catalog",	// ��� ����-�����
	"SECTION_URL"	=>	"catalog.php?",// URL ������� �� �������� � ���������� �������
	)
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog.php");?>
