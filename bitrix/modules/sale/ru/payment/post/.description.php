<?
include(GetLangFileName(dirname(__FILE__)."/", "/post.php"));

$psTitle = GetMessage("SPPP_DTITLE");
$psDescription = GetMessage("SPPP_DDESCR");

$arPSCorrespondence = array(
		"POST_ADDRESS" => array(
				"NAME" => "����� ��������",
				"DESCR" => "����� ��� ��������",
				"VALUE" => "������, �������� ������, �����, �����, ���, �������",
				"TYPE" => ""
			),
		"PAYER_NAME" => array(
				"NAME" => "����������",
				"DESCR" => "��� �����������",
				"VALUE" => "PAYER_NAME",
				"TYPE" => "PROPERTY"
			)
	);
?>