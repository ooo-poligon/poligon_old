<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");?><TABLE cellSpacing=0 cellPadding=0 align=right border=0>

<TR>
<TD><?$APPLICATION->IncludeFile("catalog/price_table.php", Array(
	"PRODUCT_ID"	=>	$_REQUEST["ID"],// ��� ������
	"PRICE_TYPE_OLD"	=>	"BASE",	// ��� "������" ����
	"PRICE_TYPE_NEW"	=>	"RETAIL",	// ��� "�����" ����
	"BASKET_PAGE"	=>	SITE_DIR."personal/basket.php",	// �������� �������
	"CACHE_TIME"	=>	"0",		// ����� ����������� ������ (������)
	)
);?></TD></TR></TABLE><?$APPLICATION->IncludeFile("iblock/catalog/element.php", Array(
	"IBLOCK_TYPE"	=>	"catalog",				// ��� ����-�����
	"IBLOCK_ID"	=>	"21",					// ����-����
	"ELEMENT_ID"	=>	$_REQUEST["ID"],				// ID ��������
	"SECTION_URL"	=>	"/catalog/phone/section.php?",		// URL ������� �� �������� � ���������� �������
	"LINK_IBLOCK_TYPE"	=>	"catalog",				// ��� ����-�����, �������� �������� ������� � ������� ���������
	"LINK_IBLOCK_ID"	=>	"22",				// ID ����-�����, �������� �������� ������� � ������� ���������
	"LINK_PROPERTY_SID"	=>	"PHONE_ID",			// �������� � ������� �������� �����
	"LINK_ELEMENTS_URL"	=>	"/catalog/accessory/byphone.php?",// URL �� �������� ��� ����� ������� ������ ��������� ���������
	"arrFIELD_CODE"	=>	Array(				// ����
					"NAME",
					"DETAIL_TEXT",
					"DETAIL_PICTURE"
				),
	"arrPROPERTY_CODE"	=>	Array(				// ��������
					"YEAR",
					"STANDBY_TIME",
					"TALKTIME",
					"WEIGHT",
					"STANDART",
					"SIZE",
					"BATTERY",
					"SCREEN",
					"MORE_PHOTO",
					"FORUM_TOPIC_ID"
				),
	"CACHE_TIME"	=>	"0",					// ����� ����������� (���.)
	"DISPLAY_PANEL"	=>	"Y",					// ��������� � �����. ������ ������ ��� ������� ����������
	)
);?> 
<HR class=tinyblue>
<?$APPLICATION->IncludeFile("forum/forum_pieces/reviews.php", Array(
	"FORUM_ID"	=>	"2",		// �����
	"PRODUCT_ID"	=>	$_REQUEST["ID"],// ��� �������� ���������
	"CACHE_TIME"	=>	"0",		// ����� ����������� ������ (������)
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>