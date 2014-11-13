<?
$bCorrectPayment = True;
if(!($arOrder = CSaleOrder::GetByID($orderNumber)))
{
	$bCorrectPayment = False;
	$code = "200"; //неверные параметры
	$techMessage = "ID заказа неизвестен.";
}

if ($bCorrectPayment)
	CSalePaySystemAction::InitParamArrays($arOrder, $arOrder["ID"]);

$Sum = CSalePaySystemAction::GetParamValue("SHOULD_PAY");
$shopId = CSalePaySystemAction::GetParamValue("SHOP_ID");
$orderNumber = CSalePaySystemAction::GetParamValue("ORDER_ID");
$customerNumber = CSalePaySystemAction::GetParamValue("USER_ID");
$shopPassword = CSalePaySystemAction::GetParamValue("SHOP_KEY");

$strCheck = md5(implode(";", array($orderIsPaid, $orderSumAmount, $orderSumCurrencyPaycash, $orderSumBankPaycash, $shopId, $orderNumber, $customerNumber, $shopPassword)));
if ($bCorrectPayment && strtoUpper($md5) != strtoUpper($strCheck))
{
	$bCorrectPayment = False;
	$code = "1"; // ошибка авторизации
}


if($bCorrectPayment)
{
	if($action=="Check")
	{
		if(IntVal($arOrder["PRICE"]) == IntVal($orderSumAmount))
			$code = "0";
		else
		{
			$code = "100"; //неверные параметры	
			$techMessage = "Сумма заказа не верна.";
		}
	}
	elseif($action=="PaymentSuccess")
	{
		$strPS_STATUS_DESCRIPTION = "";
		$strPS_STATUS_DESCRIPTION .= "номер плательщика - ".$customerNumber."; ";
		$strPS_STATUS_DESCRIPTION .= "дата платежа - ".$paymentDatetime."";
		$strPS_STATUS_DESCRIPTION .= "код подтверждения платежа - ".$orderIsPaid."";
		$strPS_STATUS_MESSAGE = "";

		$arFields = array(
				"PS_STATUS" => "Y",
				"PS_STATUS_CODE" => "-",
				"PS_STATUS_DESCRIPTION" => $strPS_STATUS_DESCRIPTION,
				"PS_STATUS_MESSAGE" => $strPS_STATUS_MESSAGE,
				"PS_SUM" => $orderSumAmount,
				"PS_CURRENCY" => $orderSumCurrencyPaycash,
				"PS_RESPONSE_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG))),
			);

		// You can comment this code if you want PAYED flag not to be set automatically
		if (intval($arOrder["PRICE"]) == IntVal($orderSumAmount) && IntVal($orderIsPaid) == 1)
		{
			if($arOrder["PAYED"] == "Y")
				$code = "0";
			else
			{
				if (!CSaleOrder::PayOrder($arOrder["ID"], "Y", true, true))
				{
					$code = "1000";
					$techMessage = "Ошибка оплаты заказа.";
				}
				else
					$code = "0";
			}
		}
		else
		{
			$code = "200"; //неверные параметры
			$techMessage = "Сумма заказа не верна.";
		}
		
		if(CSaleOrder::Update($arOrder["ID"], $arFields))
			if(strlen($techMessage)<=0 && strlen($code)<=0)
				$code = "0";
	}
	else
	{
		$code = "200"; //неверные параметры
		$techMessage = "Не известен тип запроса.";
	}
}

$APPLICATION->RestartBuffer();
$dateISO = date("Y-m-d\TH:i:s").substr(date("O"), 0, 3).":".substr(date("O"), -2, 2);
header("Content-Type: text/xml");
header("Pragma: no-cache");
$text = "<"."?xml version=\"1.0\" encoding=\"windows-1251\"?".">\n";
$text .= "<response performedDatetime=\"".$dateISO."\">";
$text .= "<result code=\"".$code."\" action=\"".$action."\" shopId=\"".$shopId."\" orderNumber=\"".$orderNumber."\" techMessage=\"".$techMessage."\" />";
$text .= "</response>";
echo $text;
die();
?>