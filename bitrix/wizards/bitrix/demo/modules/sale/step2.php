<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(!CModule::IncludeModule('sale'))
	return;
__IncludeLang(GetLangFileName(dirname(__FILE__)."/lang/", "/".basename(__FILE__)));

$siteID = $arParams["site_id"];
if(strlen($arParams["site_id"]) <= 0)
	$siteID = "s1";

$dbLocation = CSaleLocation::GetList(Array());
if(!$dbLocation->Fetch())//if there are no data in module
{
	//Locations
	include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/csv_data.php");
	$csvFile = new CCSVDataSale();
	$csvFile->LoadFile(dirname(__FILE__)."/data/ru/loc_ussr.csv");
	$csvFile->SetFieldsType("R");
	$csvFile->SetFirstHeader(false);
	$csvFile->SetDelimiter(",");

	$arRes = $csvFile->Fetch();
	$arLocation = Array();
	$arLocationMap = Array();

	if (is_array($arRes) && count($arRes)>0 && strlen($arRes[0])==2)
	{
		$DefLang = "ru";
		$arSysLangs = Array();
		$db_lang = CLangAdmin::GetList(($b="sort"), ($o="asc"), array("ACTIVE" => "Y"));
		while ($arLang = $db_lang->Fetch())
		{
			$arSysLangs[] = $arLang["LID"];
		}

		$DB->StartTransaction();					
		while ($arRes = $csvFile->Fetch())
		{
			$arArrayTmp = array();
			for ($ind = 1; $ind < count($arRes); $ind+=2)
			{
				if (in_array($arRes[$ind], $arSysLangs))
				{
					$arArrayTmp[$arRes[$ind]] = array(
							"LID" => $arRes[$ind],
							"NAME" => $arRes[$ind + 1]
						);

					if ($arRes[$ind] == $DefLang)
					{
						$arArrayTmp["NAME"] = $arRes[$ind + 1];
					}
				}
			}

			if (is_array($arArrayTmp) && strlen($arArrayTmp["NAME"])>0)
			{
				if (ToUpper($arRes[0])=="S")
				{
					$CurCountryID = CSaleLocation::AddCountry($arArrayTmp);
					$CurCountryID = IntVal($CurCountryID);
					if ($CurCountryID>0)
					{
						$LLL = CSaleLocation::AddLocation(array("COUNTRY_ID" => $CurCountryID));
					}
				}
				elseif (ToUpper($arRes[0])=="T" && $CurCountryID>0)
				{
					$city_id = 0;
					$LLL = 0;

					if ($city_id <= 0)
					{
						$city_id = CSaleLocation::AddCity($arArrayTmp);
						$city_id = IntVal($city_id);
						$arLocationMap[ToUpper($arArrayTmp["NAME"])] = $city_id;
					}
					
					if ($city_id > 0)
					{
						if (IntVal($LLL) <= 0)
						{
							$LLL = CSaleLocation::AddLocation(
								array(
									"COUNTRY_ID" => $CurCountryID,
									"CITY_ID" => $city_id
								));
							$arLocation[] = $LLL;
						}
					}
				}
			}
		}
		$DB->Commit();
		//Location group
		$locationGroupID = CSaleLocationGroup::Add(
				array(
				   "SORT" => 150,
				   "LOCATION_ID" => $arLocation,
				   "LANG" => array(
				      array("LID" => "ru", "NAME" => "Группа 1"),
				      array("LID" => "en", "NAME" => "Group 1")
				)
				)
			);
	}
	$arLocation4Delivery = Array();
	$arLocation4Delivery[] = Array("LOCATION_ID" => $locationGroupID, "LOCATION_TYPE"=>"G");
	foreach($arLocation as $val)
	{
		$arLocation4Delivery[] = Array("LOCATION_ID" => $val, "LOCATION_TYPE"=>"L");
	}

	CSaleDelivery::Add(
		Array(
			"NAME" => "По почте (зона 5)",
			"LID" => $siteID,
			"PERIOD_FROM" => 9,
			"PERIOD_TO" => 15,
			"PERIOD_TYPE" => "D",
			"WEIGHT_FROM" => 0,
			"WEIGHT_TO" => 4999,
			"ORDER_PRICE_FROM" => 0,
			"ORDER_PRICE_TO" => 0,
			"ORDER_CURRENCY" => "RUB",
			"ACTIVE" => "N",
			"PRICE" => "75.50",
			"CURRENCY" => "RUB",
			"SORT" => 100,
			"DESCRIPTION" => "Тарифы за услуги почтовых и курьерских служб изменяются в зависимости от веса с определенным шагом - обычно 500 или 1000 грамм.",
		   "LOCATIONS" => $arLocation4Delivery,
		)
	);

	CSaleDelivery::Add(
		Array(
			"NAME" => "Курьер",
			"LID" => $siteID,
			"PERIOD_FROM" => 3,
			"PERIOD_TO" => 7,
			"PERIOD_TYPE" => "D",
			"WEIGHT_FROM" => 0,
			"WEIGHT_TO" => 1999,
			"ORDER_PRICE_FROM" => 0,
			"ORDER_PRICE_TO" => 999,
			"ORDER_CURRENCY" => "RUB",
			"ACTIVE" => "N",
			"PRICE" => "15",
			"CURRENCY" => "RUB",
			"SORT" => 150,
			"DESCRIPTION" => "Заказ будет доставлен Вам в течение 3 - 10 рабочих дней после передачи его в курьерскую службу.",
			"LOCATIONS" => $arLocation4Delivery,
		)
	);
	CSaleDelivery::Add(
		Array(
			"NAME" => "Курьер",
			"LID" => $siteID,
			"PERIOD_FROM" => 7,
			"PERIOD_TO" => 15,
			"PERIOD_TYPE" => "D",
			"WEIGHT_FROM" => 2000,
			"WEIGHT_TO" => 0,
			"ORDER_PRICE_FROM" => 0,
			"ORDER_PRICE_TO" => 0,
			"ORDER_CURRENCY" => "RUB",
			"ACTIVE" => "N",
			"PRICE" => "55",
			"CURRENCY" => "RUB",
			"SORT" => 100,
			"DESCRIPTION" => "",
			"LOCATIONS" => $arLocation4Delivery,
		)
	);

	//delivery handler
	CSaleDeliveryHandler::Set("simple", 
		Array(
			"LID" => "",
			"ACTIVE" => "Y",
			"HID" => "simple",
			"NAME" => "Доставка курьером",
			"SORT" => 100,
			"DESCRIPTION" => "",
			"HANDLERS" => "/bitrix/modules/sale/delivery/delivery_simple.php",
			"SETTINGS" => "",
			"PROFILES" => "",
			"TAX_RATE" => 0,
			"CONFIG" => Array("price_".$locationGroupID => "100"),
		),
		"ALL"
	);

	CSaleDeliveryHandler::Set("cpcr", 
		Array(
			"LID" => "",
			"ACTIVE" => "Y",
			"HID" => "cpcr",
			"NAME" => "СПСР-Экспресс",
			"SORT" => 150,
			"DESCRIPTION" => "Срочная доставка почты",
			"HANDLERS" => "/bitrix/modules/sale/delivery/delivery_cpcr.php",
			"SETTINGS" => "8",
			"PROFILES" => "",
			"TAX_RATE" => 0,
		),
		"ALL"
	);

	CSaleDeliveryHandler::Set("russianpost", 
		Array(
			"LID" => "",
			"ACTIVE" => "Y",
			"HID" => "russianpost",
			"NAME" => "Почта России",
			"SORT" => 200,
			"DESCRIPTION" => "Доставка почтой",
			"HANDLERS" => "/bitrix/modules/sale/delivery/delivery_russianpost.php",
			"SETTINGS" => "23",
			"PROFILES" => "",
			"TAX_RATE" => 0,
		),
		"ALL"
	);

	CSaleDeliveryHandler::Set("ups", 
		Array(
			"LID" => "",
			"ACTIVE" => "Y",
			"HID" => "ups",
			"NAME" => "UPS",
			"SORT" => 200,
			"DESCRIPTION" => "международная доставка",
			"HANDLERS" => "/bitrix/modules/sale/delivery/delivery_ups.php",
			"SETTINGS" => "/bitrix/modules/sale/delivery/ups/ru_csv_zones.csv;/bitrix/modules/sale/delivery/ups/ru_csv_export.csv",
			"PROFILES" => "",
			"TAX_RATE" => 0,
		),
		"ALL"
	);

	//Tax
	$taxID = CSaleTax::Add(Array(
				"LID" => $siteID,
				"NAME" => "НДС",
				"CODE" => "NDS",
			)
		);

	$dbPerson = CSalePersonType::GetList(Array("SORT" => "DESC"));
	if($arPerson = $dbPerson->Fetch())
	{
		//Tax rate
		CSaleTaxRate::Add(
		  Array(
			"TAX_ID" => $taxID,
			"PERSON_TYPE_ID" => $arPerson["ID"],
			"VALUE" => 18,
			"CURRENCY" => "RUB",
			"IS_PERCENT" => "Y",
			"IS_IN_PRICE" => "Y",
			"APPLY_ORDER" => 100,
			"ACTIVE" => "Y",
			"TAX_LOCATION" => $arLocation4Delivery,
		  )
		);
	}

}
?>