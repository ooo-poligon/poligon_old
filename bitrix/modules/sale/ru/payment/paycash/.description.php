<?
include(GetLangFileName(dirname(__FILE__)."/", "/paycash.php"));

$psTitle = GetMessage("SPCP_DTITLE");
$psDescription = GetMessage("SPCP_DDESCR");

$arPSCorrespondence = array(
		"SHOP_ACCOUNT" => array(
				"NAME" => "��� ��������",
				"DESCR" => "��� ��������, ������� ������� �� ������",
				"VALUE" => "",
				"TYPE" => ""
			),
		"SHOP_KEY_ID" => array(
				"NAME" => "��� �����",
				"DESCR" => "��� �����, ������� ������� �� ������",
				"VALUE" => "",
				"TYPE" => ""
			),
		"SHOP_KEY" => array(
				"NAME" => "����",
				"DESCR" => "����, ������� ������� �� ������",
				"VALUE" => "",
				"TYPE" => ""
			)
	);
?>