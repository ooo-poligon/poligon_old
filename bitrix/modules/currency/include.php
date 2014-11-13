<?
global $DBType;

CModule::AddAutoloadClasses(
	"currency",
	array(
		"CCurrency" => $DBType."/currency.php",
		"CCurrencyLang" => $DBType."/currency_lang.php",
		"CCurrencyRates" => $DBType."/currency_rate.php",
	)
);

/*
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/".$DBType."/currency.php");
*/

Define("CURRENCY_CACHE_DEFAULT_TIME", 10800);

function CurrencyFormat($fSum, $strCurrency)
{
	if (!isset($fSum) || strlen($fSum)<=0)
		return "";

	$arCurFormat = CCurrencyLang::GetCurrencyFormat($strCurrency);

	if (!isset($arCurFormat["DECIMALS"]))
		$arCurFormat["DECIMALS"] = 2;
	$arCurFormat["DECIMALS"] = IntVal($arCurFormat["DECIMALS"]);

	if (!isset($arCurFormat["DEC_POINT"]))
		$arCurFormat["DEC_POINT"] = ".";
	if(!empty($arCurFormat["THOUSANDS_VARIANT"]))
	{
		if($arCurFormat["THOUSANDS_VARIANT"] == "N")
			$arCurFormat["THOUSANDS_SEP"] = "";
		elseif($arCurFormat["THOUSANDS_VARIANT"] == "D")
			$arCurFormat["THOUSANDS_SEP"] = ".";
		elseif($arCurFormat["THOUSANDS_VARIANT"] == "C")
			$arCurFormat["THOUSANDS_SEP"] = ",";
		elseif($arCurFormat["THOUSANDS_VARIANT"] == "S")
			$arCurFormat["THOUSANDS_SEP"] = chr(32);
	}
		
	if (!isset($arCurFormat["FORMAT_STRING"]))
		$arCurFormat["FORMAT_STRING"] = "#";

	$num = number_format($fSum, $arCurFormat["DECIMALS"], $arCurFormat["DEC_POINT"], $arCurFormat["THOUSANDS_SEP"]);

	return str_replace("#", $num, $arCurFormat["FORMAT_STRING"]);
}
?>