<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");
?><?$APPLICATION->IncludeFile("iblock/news/detail.php", Array(
	"ID"	=>	$_REQUEST["ID"],		// ID �������
	"IBLOCK_TYPE"	=>	"news",		// ��� ��������������� ����� (������������ ������ ��� ��������)
	"IBLOCK_ID"	=>	"1",			// ��� ��������������� �����
	"arrPROPERTY_CODE"	=>	Array(	// ��������
					"AUTHOR",
					"SOURCE"
				),
	"META_KEYWORDS"	=>	"KEYWORDS",	// ���������� �������� ����� �������� �� ��������
	"META_DESCRIPTION"	=>	"DESCRIPTION",// ���������� �������� �������� �� ��������
	"LIST_PAGE_URL"	=>	"",		// URL �������� ��������� ������ ��������� (�� ��������� - �� �������� ���������)
	"INCLUDE_IBLOCK_INTO_CHAIN"	=>	"N",	// �������� ��������/������ � ������� ���������
	"CACHE_TIME"	=>	"0",		// ����� ����������� (0 - �� ����������)
	"DISPLAY_PANEL"	=>	"Y",		// ��������� � �����. ������ ������ ��� ������� ����������
	)
);?><?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>