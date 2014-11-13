<?
IncludeTemplateLangFile(__FILE__);

$arTemplateDescription = array(
	".separator" => array(
		"NAME" => GetMessage("SPCD1_AFFILIATES"),
		"DESCRIPTION" => "",
		"SEPARATOR" => "Y"
	),
	"affiliate.php" => array(
		"NAME" => GetMessage("SPCD1_REPORT"),
		"DESCRIPTION" => GetMessage("SPCD1_MONEYS"),
		"ICON" => "/bitrix/images/sale/components/sale_account.gif",
		"PARAMS" => array(
			"REGISTER_PAGE" => array("NAME" => GetMessage("SPCD1_REGISTER"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"register.php", "COLS"=>25),
		)
	),
	"shop.php" => array(
		"NAME" => GetMessage("SPCD1_PROGR"),
		"DESCRIPTION" => GetMessage("SPCD1_PROGR"),
		"ICON" => "/bitrix/images/sale/components/sale_account.gif",
		"PARAMS" => array(
			"REGISTER_PAGE" => array("NAME" => GetMessage("SPCD1_REGISTER"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"register.php", "COLS"=>25),
		)
	),
	"tech.php" => array(
		"NAME" => GetMessage("SPCD1_TECH"),
		"DESCRIPTION" => GetMessage("SPCD1_TECH_ALT"),
		"ICON" => "/bitrix/images/sale/components/sale_account.gif",
		"PARAMS" => array(
			"REGISTER_PAGE" => array("NAME" => GetMessage("SPCD1_REGISTER"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"register.php", "COLS"=>25),
			"SHOP_NAME" => array("NAME" => GetMessage("SPCD1_SHOP_NAME"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"", "COLS"=>25),
			"SHOP_URL" => array("NAME" => GetMessage("SPCD1_SHOP_URL"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"", "COLS"=>25),
			"AFF_REG_PAGE" => array("NAME" => GetMessage("SPCD1_AFF_REG_PAGE"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"/affiliate/register.php", "COLS"=>25),
		)
	),
	"plans.php" => array(
		"NAME" => GetMessage("SPCD1_PLANS"),
		"DESCRIPTION" => GetMessage("SPCD1_PLANS_ALT"),
		"ICON" => "/bitrix/images/sale/components/sale_account.gif",
		"PARAMS" => array()
	),
	"register.php" => array(
		"NAME" => GetMessage("SPCD1_REGISTER_AFF"),
		"DESCRIPTION" => GetMessage("SPCD1_REGISTER_AFF"),
		"ICON" => "/bitrix/images/sale/components/sale_account.gif",
		"PARAMS" => array(
			"REDIRECT_PAGE" => array("NAME" => GetMessage("SPCD1_REGISTER_PAGE"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>'={$_REQUEST["REDIRECT_PAGE"]}', "COLS"=>25),
		)
	),
);
?>