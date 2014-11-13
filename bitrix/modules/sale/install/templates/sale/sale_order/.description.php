<?
IncludeTemplateLangFile(__FILE__);

$arPersonTypes = array();
$arPropsGroups = array();
if (CModule::IncludeModule("sale"))
{
	$dbPersonType = CSalePersonType::GetList(
			array("SORT" => "ASC", "NAME" => "ASC"),
			array(),
			false,
			false,
			array("ID", "LID", "NAME", "SORT")
		);
	while ($arPersonType = $dbPersonType->Fetch())
	{
		$arPersonTypes[$arPersonType["ID"]] = "[".$arPersonType["ID"]."] ".$arPersonType["NAME"]." (".$arPersonType["LID"].")";
	}

	$arFilter = array();
	if (IntVal($GLOBALS["PERSON_TYPE"]) > 0)
		$arFilter["PERSON_TYPE_ID"] = IntVal($GLOBALS["PERSON_TYPE"]);

	$dbPropsGroup = CSaleOrderPropsGroup::GetList(
			array("SORT" => "ASC", "NAME" => "ASC"),
			$arFilter,
			false,
			false,
			array("ID", "PERSON_TYPE_ID", "NAME", "SORT")
		);
	while ($arPropsGroup = $dbPropsGroup->Fetch())
	{
		$arPropsGroups[$arPropsGroup["ID"]] = "[".$arPropsGroup["ID"]."] ".$arPropsGroup["NAME"]." (".$arPropsGroup["PERSON_TYPE_ID"].")";
	}
}

$arTemplateDescription =
	Array(
		".separator" =>
			Array(
				"NAME" => GetMessage("STO_ORDER_PROC"),
				"DESCRIPTION" => "",
				"SEPARATOR" => "Y"
			),
		"order_full.php" =>
			Array(
				"NAME" => GetMessage("STO_ORDER_PROC"),
				"DESCRIPTION" => GetMessage("STO_ORDER_DESCR"),
				"ICON" => "/bitrix/images/sale/components/order_full.gif",
				"PARAMS" =>
					Array(
						"ORDER_PAGE" => Array("NAME"=>GetMessage("TSD_PARAM_ORDER_PAGE_NAME"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"order.php", "COLS"=>25),
						"BASKET_PAGE" => Array("NAME"=>GetMessage("STO_ORDER_BASKET"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"basket.php", "COLS"=>25),
						"PERSONAL_PAGE" => Array("NAME"=>GetMessage("STO_ORDER_PERS"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"index.php", "COLS"=>25),
						"ALLOW_PAY_FROM_ACCOUNT" => Array("NAME"=>GetMessage("STO_ALLOW_PAY_FROM_ACCOUNT"), "TYPE"=>"LIST", "VALUES"=>array("Y" => GetMessage("TSTO_YES"), "N" => GetMessage("TSTO_NO")), "DEFAULT"=>"Y", "MULTIPLE"=>"N", "ADDITIONAL_VALUES"=>"N")
						)
				),
		"order_2.php" =>
			Array(
				"NAME" => GetMessage("STO_2ORDER"),
				"DESCRIPTION" => GetMessage("STO_2ORDER_DESCR"),
				"ICON" => "/bitrix/images/sale/components/order_2.gif",
				"PARAMS" =>
					Array(
						"ORDER_PAGE" => Array("NAME"=>GetMessage("TSD_PARAM_ORDER_PAGE_NAME"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"order.php", "COLS"=>25),
						"BASKET_PAGE" => Array("NAME"=>GetMessage("STO_ORDER_BASKET"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"basket.php", "COLS"=>25),
						"PERSONAL_PAGE" => Array("NAME"=>GetMessage("STO_ORDER_PERS"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"index.php", "COLS"=>25),
						"PERSON_TYPE" => Array("NAME"=>GetMessage("STO_2ORDER_PTYPE"), "TYPE"=>"LIST", "VALUES"=>$arPersonTypes, "DEFAULT"=>"", "MULTIPLE"=>"N", "ADDITIONAL_VALUES"=>"N", "REFRESH" => "Y"),
						"FIRST_STEP_GROUP" => Array("NAME"=>GetMessage("STO_2ORDER_1PROPS"), "TYPE"=>"LIST", "VALUES"=>$arPropsGroups, "DEFAULT"=>"", "MULTIPLE"=>"N", "ADDITIONAL_VALUES"=>"N"),
						"SECOND_STEP_GROUP" => Array("NAME"=>GetMessage("STO_2ORDER_2PROPS"), "TYPE"=>"LIST", "VALUES"=>$arPropsGroups, "DEFAULT"=>"", "MULTIPLE"=>"N", "ADDITIONAL_VALUES"=>"N"),
						"ALLOW_PAY_FROM_ACCOUNT" => Array("NAME"=>GetMessage("STO_ALLOW_PAY_FROM_ACCOUNT"), "TYPE"=>"LIST", "VALUES"=>array("Y" => GetMessage("TSTO_YES"), "N" => GetMessage("TSTO_NO")), "DEFAULT"=>"Y", "MULTIPLE"=>"N", "ADDITIONAL_VALUES"=>"N")
						)
				),
		"order_1.php" =>
			Array(
				"NAME" => GetMessage("STO_1ORDER"),
				"DESCRIPTION" => GetMessage("STO_1ORDER_DESCR"),
				"ICON" => "/bitrix/images/sale/components/order_1.gif",
				"PARAMS" =>
					Array(
						"ORDER_PAGE" => Array("NAME"=>GetMessage("TSD_PARAM_ORDER_PAGE_NAME"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"order.php", "COLS"=>25),
						"BASKET_PAGE" => Array("NAME"=>GetMessage("STO_ORDER_BASKET"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"basket.php", "COLS"=>25),
						"PERSONAL_PAGE" => Array("NAME"=>GetMessage("STO_ORDER_PERS"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"index.php", "COLS"=>25),
						"PERSON_TYPE" => Array("NAME"=>GetMessage("STO_2ORDER_PTYPE"), "TYPE"=>"LIST", "VALUES"=>$arPersonTypes, "DEFAULT"=>"", "MULTIPLE"=>"N", "ADDITIONAL_VALUES"=>"N", "REFRESH" => "N"),
						"ALLOW_PAY_FROM_ACCOUNT" => Array("NAME"=>GetMessage("STO_ALLOW_PAY_FROM_ACCOUNT"), "TYPE"=>"LIST", "VALUES"=>array("Y" => GetMessage("TSTO_YES"), "N" => GetMessage("TSTO_NO")), "DEFAULT"=>"Y", "MULTIPLE"=>"N", "ADDITIONAL_VALUES"=>"N")
						)
				)

		);
?>