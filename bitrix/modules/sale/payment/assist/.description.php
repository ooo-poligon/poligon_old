<?
include(GetLangFileName(dirname(__FILE__)."/", "/assist.php"));

$psTitle = "Assist";
$psDescription = GetMessage("SASP_DDESCRIPTION");

$arPSCorrespondence = array(
		"SHOP_IDP" => array(
				"NAME" => GetMessage("SASP_DSHOP_IDP_NAME"),
				"DESCR" => GetMessage("SASP_DSHOP_IDP_DESCR"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"SHOP_LOGIN" => array(
				"NAME" => GetMessage("SASP_DSHOP_LOGIN_NAME"),
				"DESCR" => GetMessage("SASP_DSHOP_LOGIN_DESCR"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"SHOP_PASSWORD" => array(
				"NAME" => GetMessage("SASP_DSHOP_PASSWORD_NAME"),
				"DESCR" => GetMessage("SASP_DSHOP_PASSWORD_DESCR"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"FIRST_NAME" => array(
				"NAME" => GetMessage("SASP_DFIRST_NAME_NAME"),
				"DESCR" => GetMessage("SASP_DFIRST_NAME_NAME"),
				"VALUE" => "FIRST_NAME",
				"TYPE" => "PROPERTY"
			),
		"MIDDLE_NAME" => array(
				"NAME" => GetMessage("SASP_DMIDDLE_NAME_NAME"),
				"DESCR" => GetMessage("SASP_DMIDDLE_NAME_NAME"),
				"VALUE" => "MIDDLE_NAME",
				"TYPE" => "PROPERTY"
			),
		"LAST_NAME" => array(
				"NAME" => GetMessage("SASP_DLAST_NAME_NAME"),
				"DESCR" => GetMessage("SASP_DLAST_NAME_NAME"),
				"VALUE" => "LAST_NAME",
				"TYPE" => "PROPERTY"
			),
		"EMAIL" => array(
				"NAME" => GetMessage("SASP_DEMAIL_NAME"),
				"DESCR" => GetMessage("SASP_DEMAIL_NAME"),
				"VALUE" => "EMAIL",
				"TYPE" => "PROPERTY"
			),
		"ADDRESS" => array(
				"NAME" => GetMessage("SASP_DADDRESS_NAME"),
				"DESCR" => GetMessage("SASP_DADDRESS_NAME"),
				"VALUE" => "ADDRESS",
				"TYPE" => "PROPERTY"
			),
		"PHONE" => array(
				"NAME" => GetMessage("SASP_DPHONE_NAME"),
				"DESCR" => GetMessage("SASP_DPHONE_NAME"),
				"VALUE" => "PHONE",
				"TYPE" => "PROPERTY"
			),
		"PAYMENT_CardPayment" => array(
				"NAME" => GetMessage("SASP_DPAYMENT_CardPayment_NAME"),
				"DESCR" => GetMessage("SASP_DPAYMENT_D_NAME"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"PAYMENT_WalletPayment" => array(
				"NAME" => GetMessage("SASP_DPAYMENT_WalletPayment_NAME"),
				"DESCR" => GetMessage("SASP_DPAYMENT_D_NAME"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"PAYMENT_WebMoneyPayment" => array(
				"NAME" => GetMessage("SASP_DPAYMENT_WebMoneyPayment_NAME"),
				"DESCR" => GetMessage("SASP_DPAYMENT_D_NAME"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"PAYMENT_EPortPayment" => array(
				"NAME" => GetMessage("SASP_DPAYMENT_EPortPayment_NAME"),
				"DESCR" => GetMessage("SASP_DPAYMENT_D_NAME"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"PAYMENT_KreditPilotPayment" => array(
				"NAME" => GetMessage("SASP_DPAYMENT_KreditPilotPayment_NAME"),
				"DESCR" => GetMessage("SASP_DPAYMENT_D_NAME"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"PAYMENT_PayCashPayment" => array(
				"NAME" => GetMessage("SASP_DPAYMENT_PayCashPayment_NAME"),
				"DESCR" => GetMessage("SASP_DPAYMENT_D_NAME"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"DEMO" => array(
				"NAME" => GetMessage("SASP_DDEMO_NAME"),
				"DESCR" => GetMessage("SASP_DDEMO_NAME"),
				"VALUE" => "AS000",
				"TYPE" => ""
			)
	);
?>
