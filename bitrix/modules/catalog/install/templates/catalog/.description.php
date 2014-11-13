<?
IncludeTemplateLangFile(__FILE__);

$sSectionName = GetMessage("CMTT_TITLE");

$arPrTypesList = array();
$arPrTypesListCode = array();
if (CModule::IncludeModule("catalog"))
{
	$db_res = CCatalogGroup::GetListEx(array("SORT" => "ASC"), array(), false, false, array("ID", "NAME", "BASE", "NAME_LANG"));
	while ($ar_res = $db_res->Fetch())
	{
		$arPrTypesList[$ar_res["ID"]] = $ar_res["NAME"];
		$arPrTypesListCode[$ar_res["NAME"]] = "[".$ar_res["NAME"]."] ".$ar_res["NAME_LANG"];
		if ($ar_res["BASE"]=="Y")
			$arPrTypesList[$ar_res["ID"]] .= " [B]";
	}
}

$arYesNoArray = Array();
$arYesNoArray["N"] = GetMessage("CTCD_DESC_YNA_NO");
$arYesNoArray["Y"] = GetMessage("CTCD_DESC_YNA_YES");

$arTemplateDescription =
	Array(
		"price.php" =>
			Array(
				"NAME" => GetMessage("CTCD_PRICE_TABLE"),
				"DESCRIPTION" => GetMessage("CTCD_PRICE_TABLE_DESCR"),
				"ICON"	=> "/bitrix/images/catalog/components/catalog_price.gif",
				"PARAMS" =>
					Array(
						"ID" => Array(
							"NAME" => GetMessage("TCD_PARAM_PRODUCT_ID_NAME"),
							"TYPE" => "STRING",
							"MULTIPLE" => "N",
							"DEFAULT" => "={\$GLOBALS[\"ID\"]}",
							"COLS"=>25
						),
						"ACTION_VARIABLE" => array(
							"NAME"		=> GetMessage("CTCD_VAR_ACTION_NAME"),
							"TYPE"		=> "STRING",
							"DEFAULT"	=> "action"
							),
						"ID_VARIABLE" => array(
							"NAME"		=> GetMessage("CTCD_VAR_PRODUCT_NAME"),
							"TYPE"		=> "STRING",
							"DEFAULT"	=> "ID"
							),
						"PRICE_CODE"	=> array(
							"NAME"		=> GetMessage("CTCD_PRICE_CODE"),
							"TYPE"				=> "LIST",
							"MULTIPLE"			=> "Y",
							"ADDITIONAL_VALUES" => "N",
							"VALUES"			=> $arPrTypesListCode
						),
						"BASKET_URL" => array(
							"NAME"		=> GetMessage("TCD_PARAM_BASKET_PAGE_NAME"),
							"TYPE"		=> "STRING",
							"DEFAULT"	=> "/personal/basket.php"
						),
						"USE_PRICE_COUNT" => array(
							"NAME"		=> GetMessage("CTCD_USE_PRICE_COUNT"),
							"TYPE"		=> "LIST",
							"VALUES"=>$arYesNoArray,
							"DEFAULT"	=> "N",
							"ADDITIONAL_VALUES"=>"N"
						),
						"SHOW_PRICE_COUNT" => array(
							"NAME"		=> GetMessage("CTCD_SHOW_PRICE_COUNT"),
							"TYPE"		=> "STRING",
							"DEFAULT"	=> "1"
						),
						"CACHE_TIME" => Array("NAME"=>GetMessage("TCD_PARAM_CACHE_TIME_NAME"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"600", "COLS"=>25)
						)
				),
		"multi_price_table.php" =>
			Array(
				"NAME" => GetMessage("CTCD_QUANTITY_TABLE"),
				"DESCRIPTION" => GetMessage("CTCD_QUANTITY_TABLE_DESCR"),
				"ICON"	=> "/bitrix/images/catalog/components/catalog_price_mult.gif",
				"PARAMS" =>
					Array(
						"ID" => Array("NAME"=>GetMessage("TCD_PARAM_PRODUCT_ID_NAME"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"={\$GLOBALS[\"ID\"]}", "COLS"=>25),
						"BASKET_PAGE_TEMPLATE" => Array("NAME"=>GetMessage("TCD_PARAM_BASKET_PAGE_NAME"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"basket.php", "COLS"=>25),
						"ACTION_VARIABLE" => Array("NAME"=>GetMessage("CTCD_VAR_ACTION_NAME"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "VALUES"=>"action"),
						"PRODUCT_ID_VARIABLE" => Array("NAME"=>GetMessage("CTCD_VAR_PRODUCT_NAME"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "VALUES"=>"PRODUCT_ID"),
						"CACHE_TIME" => Array("NAME"=>GetMessage("TCD_PARAM_CACHE_TIME_NAME"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"600", "COLS"=>25)
						)
				),
		"price_table.php" =>
			Array(
				"NAME" => GetMessage("TCD_NAME_SMALL"),
				"DESCRIPTION" => GetMessage("TCD_NAME_SMALL_DESCR"),
				"ICON"	=> "/bitrix/images/catalog/components/catalog_prices.gif",
				"PARAMS" =>
					Array(
						"PRODUCT_ID" => Array("NAME"=>GetMessage("TCD_PARAM_PRODUCT_ID_NAME"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"={\$GLOBALS[\"ID\"]}", "COLS"=>25),
						"PRICE_TYPE_OLD" => Array("NAME"=>GetMessage("TCD_PARAM_PRICE_TYPE_OLD_NAME"), "TYPE"=>"LIST", "MULTIPLE"=>"N", "VALUES"=>$arPrTypesList),
						"PRICE_TYPE_NEW" => Array("NAME"=>GetMessage("TCD_PARAM_PRICE_TYPE_NEW_NAME"), "TYPE"=>"LIST", "MULTIPLE"=>"N", "VALUES"=>$arPrTypesList),
						"BASKET_PAGE" => Array("NAME"=>GetMessage("TCD_PARAM_BASKET_PAGE_NAME"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"basket.php", "COLS"=>25),
						"CACHE_TIME" => Array("NAME"=>GetMessage("TCD_PARAM_CACHE_TIME_NAME"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"600", "COLS"=>25)
						)
				)
		);
?>