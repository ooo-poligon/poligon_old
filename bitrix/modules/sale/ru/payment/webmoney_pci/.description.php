<?
include(GetLangFileName(dirname(__FILE__)."/", "/webmoney_pci.php"));

$psTitle = GetMessage("SWMPP_DTITLE");
$psDescription = GetMessage("SWMPP_DDESCR");

$arPSCorrespondence = array(
		"ORDER_ID" => array(
				"NAME" => "����� ������",
				"DESCR" => "",
				"VALUE" => "",
				"TYPE" => ""
			),
		"SHOULD_PAY" => array(
				"NAME" => "����� � ������",
				"DESCR" => "",
				"VALUE" => "",
				"TYPE" => ""
			),
		"ACC_NUMBER" => array(
				"NAME" => "����� ��������",
				"DESCR" => "������� ���� ����� ������ ��������",
				"VALUE" => "",
				"TYPE" => ""
			),
		"TEST_MODE" => array(
				"NAME" => "�������� �����",
				"DESCR" => "test - ��� ��������� ������, ����� ������ ��������",
				"VALUE" => "",
				"TYPE" => ""
			),
		"PATH_TO_RESULT" => array(
				"NAME" => "���� � ������� ��������� ������ ��������� �������",
				"DESCR" => "���� �������� ������������ ����� �����",
				"VALUE" => "",
				"TYPE" => ""
			),
		"CNST_SECRET_KEY" => array(
				"NAME" => "������ � ������� WebMoney Transfer",
				"DESCR" => "������ �������� � ������� WebMoney Transfer",
				"VALUE" => "",
				"TYPE" => ""
			)
	);
?>