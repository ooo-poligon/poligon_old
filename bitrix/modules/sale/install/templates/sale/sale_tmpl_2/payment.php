<?
$ORDER_ID = IntVal($_REQUEST["ORDER_ID"]);

if (CModule::IncludeModule("sale"))
{
	$db_order = CSaleOrder::GetList(Array("DATE_UPDATE"=>"DESC"), Array("LID"=>SITE_ID, "USER_ID"=>IntVal($USER->GetID()), "ID"=>IntVal($ORDER_ID)));
	if ($arOrder = $db_order->Fetch())
	{
		if ($arPaySys = CSalePaySystem::GetByID($arOrder["PAY_SYSTEM_ID"], $arOrder["PERSON_TYPE_ID"]))
		{
			$PAYER_NAME = "";
			$db_props = CSaleOrderProps::GetList(($by="SORT"), ($order="ASC"), Array("PERSON_TYPE_ID"=>$arOrder["PERSON_TYPE_ID"], "IS_PAYER"=>"Y"));
			if ($arProps = $db_props->Fetch())
			{
				$db_vals = CSaleOrderPropsValue::GetList(($by="SORT"), ($order="ASC"), Array("ORDER_ID"=>$ORDER_ID, "ORDER_PROPS_ID"=>$arProps["ID"]));
				if ($arVals = $db_vals->Fetch()) $PAYER_NAME = $arVals["VALUE"];
			}
			include($_SERVER["DOCUMENT_ROOT"].$arPaySys["PSA_ACTION_FILE"]);
		}
	}
}
?>