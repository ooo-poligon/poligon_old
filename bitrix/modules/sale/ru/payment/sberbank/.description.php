<?
include(GetLangFileName(dirname(__FILE__)."/", "/sberbank.php"));

$psTitle = GetMessage("SSBP_DTITLE");
$psDescription = GetMessage("SSBP_DDESCR");

$arPSCorrespondence = array(
		"SELLER_PARAMS" => array(
				"NAME" => "��������� ���������� �������",
				"DESCR" => "��������� ���������� �������",
				"VALUE" => "��� XXXXXXXXXXXXX, ��� XXXXXXXXXX, \"��������\", �/�� XXXXXXXXXX � \"����\", �. �����, �/�� XXXXXXXXXX, ��� XXXXXXXXX",
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