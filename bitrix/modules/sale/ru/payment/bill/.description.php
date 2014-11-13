<?
include(GetLangFileName(dirname(__FILE__)."/", "/bill.php"));

$psTitle = GetMessage("SBLP_DTITLE");
$psDescription = GetMessage("SBLP_DDESCR");

$arPSCorrespondence = array(
		"DATE_INSERT" => array(
				"NAME" => "���� ������",
				"DESCR" => "���� ���������� ������",
				"VALUE" => "DATE_INSERT",
				"TYPE" => "ORDER"
			),

		"SELLER_NAME" => array(
				"NAME" => "�������� ��������-����������",
				"DESCR" => "�������� ��������-���������� (��������)",
				"VALUE" => "",
				"TYPE" => ""
			),
		"SELLER_ADDRESS" => array(
				"NAME" => "����� ��������-����������",
				"DESCR" => "����� ��������-���������� (��������)",
				"VALUE" => "",
				"TYPE" => ""
			),
		"SELLER_PHONE" => array(
				"NAME" => "������� ��������-����������",
				"DESCR" => "������� ��������-���������� (��������)",
				"VALUE" => "",
				"TYPE" => ""
			),
		"SELLER_INN" => array(
				"NAME" => "��� ��������-����������",
				"DESCR" => "��� ��������-���������� (��������)",
				"VALUE" => "",
				"TYPE" => ""
			),
		"SELLER_KPP" => array(
				"NAME" => "��� ��������-����������",
				"DESCR" => "��� ��������-���������� (��������)",
				"VALUE" => "",
				"TYPE" => ""
			),
		"SELLER_RS" => array(
				"NAME" => "��������� ���� ��������-����������",
				"DESCR" => "��������� ���� ��������-���������� (��������)",
				"VALUE" => "�/� ... � \"����\", �. �����",
				"TYPE" => ""
			),
		"SELLER_KS" => array(
				"NAME" => "����������������� ���� ��������-����������",
				"DESCR" => "����������������� ���� ��������-���������� (��������)",
				"VALUE" => "",
				"TYPE" => ""
			),
		"SELLER_BIK" => array(
				"NAME" => "��� ��������-����������",
				"DESCR" => "��� ��������-���������� (��������)",
				"VALUE" => "",
				"TYPE" => ""
			),
		"BUYER_NAME" => array(
				"NAME" => "�������� ��������-���������",
				"DESCR" => "�������� ��������-��������� (����������)",
				"VALUE" => "COMPANY_NAME",
				"TYPE" => "PROPERTY"
			),
		"BUYER_INN" => array(
				"NAME" => "��� ��������-���������",
				"DESCR" => "��� ��������-��������� (����������)",
				"VALUE" => "INN",
				"TYPE" => "PROPERTY"
			),
		"BUYER_ADDRESS" => array(
				"NAME" => "����� ��������-���������",
				"DESCR" => "����� ��������-��������� (����������)",
				"VALUE" => "ADDRESS",
				"TYPE" => "PROPERTY"
			),
		"BUYER_PHONE" => array(
				"NAME" => "������� ��������-���������",
				"DESCR" => "������� ��������-��������� (����������)",
				"VALUE" => "PHONE",
				"TYPE" => "PROPERTY"
			),
		"BUYER_FAX" => array(
				"NAME" => "���� ��������-���������",
				"DESCR" => "���� ��������-��������� (����������)",
				"VALUE" => "FAX",
				"TYPE" => "PROPERTY"
			),
		"BUYER_PAYER_NAME" => array(
				"NAME" => "���������� ���� ��������-���������",
				"DESCR" => "���������� ���� ��������-��������� (����������)",
				"VALUE" => "PAYER_NAME",
				"TYPE" => "PROPERTY"
			),

		"PATH_TO_STAMP" => array(
				"NAME" => "������",
				"DESCR" => "���� � ����������� ������ ���������� �� �����",
				"VALUE" => "",
				"TYPE" => ""
			)
	);
?>