<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("sale"))
{
	ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
	return;
}

$ORDER_ID = IntVal($_REQUEST["ORDER_ID"]);

$dbOrder = CSaleOrder::GetList(
	array("DATE_UPDATE" => "DESC"),
	array(
			"LID" => SITE_ID,
			"USER_ID" => IntVal($GLOBALS["USER"]->GetID()),
			"ID" => $ORDER_ID
		)
);
if ($arOrder = $dbOrder->Fetch())
{
	$dbPaySysAction = CSalePaySystemAction::GetList(
			array(),
			array(
					"PAY_SYSTEM_ID" => $arOrder["PAY_SYSTEM_ID"],
					"PERSON_TYPE_ID" => $arOrder["PERSON_TYPE_ID"]
				),
			false,
			false,
			array("ACTION_FILE", "PARAMS", "ENCODING")
		);

	if ($arPaySysAction = $dbPaySysAction->Fetch())
	{
		if (strlen($arPaySysAction["ACTION_FILE"]) > 0)
		{
			$PAYER_NAME = "";
			$GLOBALS["SALE_INPUT_PARAMS"] = array();

			$dbUser = CUser::GetByID($arOrder["USER_ID"]);
			if ($arUser = $dbUser->GetNext())
				$GLOBALS["SALE_INPUT_PARAMS"]["USER"] = $arUser;

			$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"] = $arOrder;
			$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"] = DoubleVal($arOrder["PRICE"]) - DoubleVal($arOrder["SUM_PAID"]);

			$arDateInsert = explode(" ", $arOrder["DATE_INSERT"]);
			if (is_array($arDateInsert) && count($arDateInsert) > 0)
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT_DATE"] = $arDateInsert[0];
			else
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT_DATE"] = $arOrder["DATE_INSERT"];

			$arCurOrderProps = array();
			$dbOrderPropVals = CSaleOrderPropsValue::GetList(
					array(),
					array("ORDER_ID" => $ORDER_ID),
					false,
					false,
					array("ID", "CODE", "VALUE", "ORDER_PROPS_ID", "PROP_TYPE", "PROP_IS_PAYER")
				);
			while ($arOrderPropVals = $dbOrderPropVals->Fetch())
			{
				$arCurOrderPropsTmp = CSaleOrderProps::GetRealValue(
						$arOrderPropVals["ORDER_PROPS_ID"],
						$arOrderPropVals["CODE"],
						$arOrderPropVals["PROP_TYPE"],
						$arOrderPropVals["VALUE"],
						LANGUAGE_ID
					);
				foreach ($arCurOrderPropsTmp as $key => $value)
				{
					$arCurOrderProps[$key] = $value;
				}

				if ($arOrderPropVals["PROP_IS_PAYER"] == "Y")
					$PAYER_NAME = $arOrderPropVals["VALUE"];
			}

			$GLOBALS["SALE_INPUT_PARAMS"]["PROPERTY"] = $arCurOrderProps;
			$GLOBALS["SALE_CORRESPONDENCE"] = CSalePaySystemAction::UnSerializeParams($arPaySysAction["PARAMS"]);

			$pathToAction = $_SERVER["DOCUMENT_ROOT"].$arPaySysAction["ACTION_FILE"];

			$pathToAction = str_replace("\\", "/", $pathToAction);
			while (substr($pathToAction, strlen($pathToAction) - 1, 1) == "/")
				$pathToAction = substr($pathToAction, 0, strlen($pathToAction) - 1);

			if (file_exists($pathToAction))
			{
				if (is_dir($pathToAction))
				{
					if (file_exists($pathToAction."/payment.php"))
						include($pathToAction."/payment.php");
				}
				else
				{
					include($pathToAction);
				}
			}
			if(strlen($arPaySysAction["ENCODING"]) > 0)
			{
				define("BX_SALE_ENCODING", $arPaySysAction["ENCODING"]);
				AddEventHandler("main", "OnEndBufferContent", "ChangeEncoding");
				function ChangeEncoding($content)
				{
					global $APPLICATION;
					header("Content-Type: text/html; charset=".BX_SALE_ENCODING);
					$content = $APPLICATION->ConvertCharset($content, SITE_CHARSET, BX_SALE_ENCODING);
					$content = str_replace("charset=".SITE_CHARSET, "charset=".BX_SALE_ENCODING, $content);
				}
			}

		}
	}
}
?>