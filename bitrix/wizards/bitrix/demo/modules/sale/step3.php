<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(!CModule::IncludeModule('sale'))
	return;
__IncludeLang(GetLangFileName(dirname(__FILE__)."/lang/", "/".basename(__FILE__)));

$siteID = $arParams["site_id"];
if(strlen($arParams["site_id"]) <= 0)
	$siteID = "s1";
$defLang = "ru";
$dbSite = CSite::GetByID($siteID);
if($arSite = $dbSite->Fetch())
{
	$defLang = $arSite["LANGUAGE_ID"];
}

$dbLocation = CSaleLocation::GetList(Array("SORT" => "ASC", "COUNTRY_NAME_LANG" => "ASC", "CITY_NAME_LANG" => "ASC"), Array("LID" => $defLang));
if($arLocation = $dbLocation->Fetch())//if there are no data in module
{
	do
	{
		$arLocationMap[ToUpper($arLocation["CITY_NAME"])] = $arLocation["CITY_ID"];
	}
	while($arLocation = $dbLocation->Fetch());
	print_r($arLocationMap);
	$DB->StartTransaction();
        include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/csv_data.php");
	$csvFile = new CCSVDataSale();
	$csvFile->LoadFile(dirname(__FILE__)."/data/ru/zip_ussr.csv");
	$csvFile->SetFieldsType("R");
	$csvFile->SetFirstHeader(false);
	$csvFile->SetDelimiter(";");

	while ($arRes = $csvFile->Fetch())
	{
		$CITY = ToUpper($arRes[1]);
		
		if (array_key_exists($CITY, $arLocationMap))
		{
			$ID = $arLocationMap[$CITY];
		}
		else
		{
			$ID = 0;
		}
		
		if ($ID)
		{
			CSaleLocation::AddLocationZIP($ID, $arRes[2]);
		}
	}

	$DB->Commit();	
}
?>