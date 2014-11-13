<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
IncludeTemplateLangFile(__FILE__);
$sSectionName = GetMessage("T_CURRENCY_DESC_NAME");
if (CModule::IncludeModule("currency"))
{
	$rsCurrency = CCurrency::GetList(($by="SORT"), ($order="ASC"));
	while($arr=$rsCurrency->Fetch()) $arCurrency[$arr["CURRENCY"]] = "[".$arr["CURRENCY"]."]";
}

$arShowCB["N"] = GetMessage("T_CURRENCY_NO");
$arShowCB["Y"] = GetMessage("T_CURRENCY_YES");

$arTemplateDescription = array();

/**************************************************************************************
		Компонент для отображения содержимогоtop каталога на главной странице
**************************************************************************************/

$arTemplateDescription["show_rates.php"] = array(
	"NAME"			=> GetMessage("CURRENCY_SHOW_RATES_TEMPLATE_NAME"),
	"DESCRIPTION"	=> GetMessage("CURRENCY_SHOW_RATES_TEMPLATE_DESCRIPTION"),
	"ICON"	=> "/bitrix/images/currency/components/show_rates.gif",
	"PARAMS" => array(
		"arrCURRENCY_FROM" => array(
			"NAME" => GetMessage("CURRENCY_FROM"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "N",
			"VALUES" => $arCurrency
			),
		"CURRENCY_BASE" => array(
			"NAME" => GetMessage("CURRENCY_BASE"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"ADDITIONAL_VALUES" => "N",
			"VALUES" => $arCurrency
			),
		"RATE_DAY" => array(
			"NAME"		=> GetMessage("CURRENCY_RATE_DAY"),
			"TYPE"		=> "DATE",
			),
		"SHOW_CB" => array(
			"NAME"		=> GetMessage("T_CURRENCY_CBRF"),
			"TYPE"		=> "LIST",
			"MULTIPLE" => "N",
			"ADDITIONAL_VALUES" => "N",
			"VALUES" => $arShowCB

			),
		"CACHE_TIME" => array(
			"NAME"		=> GetMessage("CATALOG_CACHE_TIME"),
			"TYPE"		=> "STRING",
			"DEFAULT"	=> "0"
			),
		)
	);

?>