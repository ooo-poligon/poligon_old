<?
include(GetLangFileName(dirname(__FILE__)."/", "/webmoney_web.php"));

$psTitle = GetMessage("SWMWP_DTITLE");
$psDescription  = GetMessage("SWMWP_DDESCR");

$arPSCorrespondence = array(
		"SHOP_ACCT" => array(
				"NAME" => "����� R ��������",
				"DESCR" => "� ���� ����� R � 12 ����. ��� ���������� ����� �� ������������ ����������.",
				"VALUE" => "",
				"TYPE" => ""
			),
		"TEST_MODE" => array(
				"NAME" => "�������� �����",
				"DESCR" => "� ������ ������������: 0 - �������� ������; 1 - �� ��������; 2 - ����� 80% ��������, ��������� - �� ��������",
				"VALUE" => "",
				"TYPE" => ""
			),
		"CNST_SECRET_KEY" => array(
				"NAME" => "Secret Key",
				"DESCR" => "��������������� � ���������� ������� Web Merchant Interface",
				"VALUE" => "",
				"TYPE" => ""
			),
		"ORDER_ID" => Array(
				"NAME" => "ID ������",
 				"VALUE" => "",
 				"TYPE" => "ORDER",
			),
		"DATE_INSERT" => Array(
				"NAME" => "���� ������",
 				"VALUE" => "",
 				"TYPE" => "ORDER",
			),
		"SHOULD_PAY" => Array(
				"NAME" => "����� � ������",
				"DESCR" => "",
 				"VALUE" => "",
 				"TYPE" => "ORDER",
			),
		"RESULT_URL" => Array(
				"NAME" => "����� ��� ����������",
				"DESCR" => "URL (�� ���-����� ��������), �� ������� ����� ������ Web Merchant Interface �������� HTTP POST ���������� � ���������� ������� � ��� ���������� �����������. URL ������ ����� ������� http:// ��� https://",
 				"VALUE" => "",
 				"TYPE" => "ORDER",
			),
		"SUCCESS_URL" => Array(
				"NAME" => "����� ��� �������� ������",
				"DESCR" => "URL (�� ���-����� ��������), �� ������� ����� ��������� ��������-������� ���������� � ������ ��������� ���������� ������� � ������� Web Merchant Interface. URL ������ ����� ������� http:// ��� https://.",
 				"VALUE" => "",
 				"TYPE" => "ORDER",
			),
		"FAIL_URL" => Array(
				"NAME" => "����� ��� ������ ������",
				"DESCR" => "URL (�� ���-����� ��������), �� ������� ����� ��������� ��������-������� ���������� � ��� ������, ���� ������ � ������� Web Merchant Interface �� ��� �������� �� �����-�� ��������. URL ������ ����� ������� http:// ��� https://.",
 				"VALUE" => "",
 				"TYPE" => "ORDER",
			),

	);
?>