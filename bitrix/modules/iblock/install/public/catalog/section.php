<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");?><?$APPLICATION->IncludeFile("iblock/catalog/element_filter.php", Array(
	"IBLOCK_TYPE"	=>	"catalog",	// ��� ����-�����
	"IBLOCK_ID"	=>	"21",		// ����-����
	"arrFIELD_CODE"	=>	Array("NAME"),	// ����
	"arrPROPERTY_CODE"	=>	Array(	// ��������
					"YEAR",
					"WEIGHT",
					"STANDART",
					"BATTERY"
				),
	"arrPRICE_CODE"	=>	Array("RETAIL"),// ���� ���
	"CURRENCY_CODE"	=>	"\$",		// ��� ������
	"SAVE_IN_SESSION"	=>	"N",		// ��������� �������� ����� ������� � ������
	"FILTER_NAME"	=>	"arrFilter",	// ��� ���������� ������� ��� ����������
	"LIST_HEIGHT"	=>	"5",		// ������ ������� �������������� ������
	"TEXT_WIDTH"	=>	"24",		// ������ ������������ ��������� ����� �����
	"NUMBER_WIDTH"	=>	"5",		// ������ ����� ����� ��� �������� ����������
	"CACHE_TIME"	=>	"0",		// ����� ����������� (���.)
	)
);?> <BR><?$APPLICATION->IncludeFile("iblock/catalog/compare_list.php", Array(
	"IBLOCK_TYPE"	=>	"catalog",			// ��� ����-�����
	"IBLOCK_ID"	=>	"21",				// ����-����
	"COMPARE_URL"	=>	"/catalog/phone/compare.php",// URL �������� � �������� ���������
	"NAME"	=>	"CATALOG_COMPARE_LIST",		// ���������� ��� ��� ������ ���������
	"ELEMENT_SORT_FIELD"	=>	"sort",		// �� ������ ���� ��������� ��������
	"ELEMENT_SORT_ORDER"	=>	"asc",		// ������� ���������� ��������� � �������
	"CACHE_TIME"	=>	"0",				// ����� ����������� (���.)
	)
);?> <BR><?$APPLICATION->IncludeFile("iblock/catalog/section.php", Array(
	"IBLOCK_TYPE"	=>	"catalog",		// ��� ����-�����
	"IBLOCK_ID"	=>	"21",			// ����-����
	"SECTION_ID"	=>	$_REQUEST["SECTION_ID"],// ID �������
	"PAGE_ELEMENT_COUNT"	=>	"30",		// ���������� ��������� �� ��������
	"LINE_ELEMENT_COUNT"	=>	"2",		// ���������� ��������� ��������� � ����� ������ �������
	"ELEMENT_SORT_FIELD"	=>	"sort",		// �� ������ ���� ��������� ��������
	"ELEMENT_SORT_ORDER"	=>	"asc",		// ������� ���������� ��������� � �������
	"arrPROPERTY_CODE"	=>	Array(		// ��������
					"YEAR",
					"STANDBY_TIME",
					"TALKTIME",
					"WEIGHT",
					"STANDART",
					"SIZE",
					"BATTERY"
				),
	"PRICE_CODE"	=>	"RETAIL",		// ��� ����
	"BASKET_URL"	=>	"/personal/basket.php",	// URL ������� �� �������� � �������� ����������
	"FILTER_NAME"	=>	"arrFilter",		// ��� ������� �� ���������� ������� ��� ���������� ���������
	"CACHE_FILTER"	=>	"N",			// ���������� ��� ������������ �������
	"CACHE_TIME"	=>	"0",			// ����� ����������� (���.)
	"DISPLAY_PANEL"	=>	"Y",			// ��������� � �����. ������ ������ ��� ������� ����������
	)
);?> <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>