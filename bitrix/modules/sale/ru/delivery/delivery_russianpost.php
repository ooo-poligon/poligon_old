<?
/*********************************************************************************
Delivery handler for Russian Post Service (http://www.russianpost.ru/)
It uses on-line calculator. Delivery only from Moscow.
Files:
 - russianpost/country.php - list of russianpost country ids
*********************************************************************************/

CModule::IncludeModule("sale");

IncludeModuleLangFile('/bitrix/modules/sale/delivery/delivery_russianpost.php');

define('DELIVERY_RUSSIANPOST_WRITE_LOG', 0); // flag 'write to log'. use CDeliveryRUSSIANPOST::__WriteToLog() for logging.
define('DELIVERY_RUSSIANPOST_CACHE_LIFETIME', 2592000); // cache lifetime - 30 days (60*60*24*30)

define('DELIVERY_RUSSIANPOST_CATEGORY_DEFAULT', 23); // default delivery type

define('DELIVERY_RUSSIANPOST_PRICE_TARIFF', 0.03); // price koefficient - 3%

define('DELIVERY_RUSSIANPOST_SERVER_POST_CATEGORY', 'viewPost');
define('DELIVERY_RUSSIANPOST_SERVER_POST_CATEGORY_NAME', 'viewPostName');
define('DELIVERY_RUSSIANPOST_SERVER_POST_PROFILE', 'typePost');
define('DELIVERY_RUSSIANPOST_SERVER_POST_PROFILE_NAME', 'typePostName');
define('DELIVERY_RUSSIANPOST_SERVER_POST_ZIP', 'postOfficeId');
define('DELIVERY_RUSSIANPOST_SERVER_POST_WEIGHT', 'weight');
define('DELIVERY_RUSSIANPOST_SERVER_POST_PRICE', 'value1');

define('DELIVERY_RUSSIANPOST_SERVER_POST_COUNTRY', 'countryCode');
define('DELIVERY_RUSSIANPOST_SERVER_POST_COUNTRY_NAME', 'countryCodeName');

define('DELIVERY_RUSSIANPOST_SERVER', 'fcr.russianpost.ru');
define('DELIVERY_RUSSIANPOST_SERVER_PORT', 80);
define('DELIVERY_RUSSIANPOST_SERVER_PAGE', '/autotarif/Autotarif.aspx');
define('DELIVERY_RUSSIANPOST_SERVER_METHOD', 'GET');

define('DELIVERY_RUSSIANPOST_VALUE_CHECK_STRING', '<span id="TarifValue">');
define(
	'DELIVERY_RUSSIANPOST_VALUE_CHECK_REGEXP_RUS', 
	'/<sup>\*<\/sup><\/td><td align="Right">*([0-9,]+)<\/td>/i'
);
define(
	'DELIVERY_RUSSIANPOST_VALUE_CHECK_REGEXP', 
	'/<span id="TarifValue">*([0-9,]+)<\/span>/i'
);

class CDeliveryRUSSIANPOST
{
	function Init()
	{
		if ($arCurrency = CCurrency::GetByID('RUR'))
		{
			$base_currency = 'RUR';
		}
		else
		{
			$base_currency = 'RUB';
		}
	
		return array(
			/* Basic description */
			"SID" => "russianpost",
			"NAME" => GetMessage('SALE_DH_RUSSIANPOST_NAME'),
			"DESCRIPTION" => GetMessage('SALE_DH_RUSSIANPOST_DESCRIPTION'),
			"DESCRIPTION_INNER" => GetMessage('SALE_DH_RUSSIANPOST_DESCRIPTION_INNER'),
			"BASE_CURRENCY" => $base_currency,
			
			"HANDLER" => __FILE__,
			
			/* Handler methods */
			"DBGETSETTINGS" => array("CDeliveryRUSSIANPOST", "GetSettings"),
			"DBSETSETTINGS" => array("CDeliveryRUSSIANPOST", "SetSettings"),
			"GETCONFIG" => array("CDeliveryRUSSIANPOST", "GetConfig"),
			
			"COMPABILITY" => array("CDeliveryRUSSIANPOST", "Compability"),
			"CALCULATOR" => array("CDeliveryRUSSIANPOST", "Calculate"),
			
			/* List of delivery profiles */
			"PROFILES" => array(
				"ground" => array(
					"TITLE" => GetMessage("SALE_DH_RUSSIANPOST_GROUND_TITLE"),
					"DESCRIPTION" => GetMessage("SALE_DH_RUSSIANPOST_GROUND_DESCRIPTION"),
					
					"RESTRICTIONS_WEIGHT" => array(0),
					"RESTRICTIONS_SUM" => array(0),
				),
				
				"avia" => array(
					"TITLE" => GetMessage("SALE_DH_RUSSIANPOST_AVIA_TITLE"),
					"DESCRIPTION" => GetMessage("SALE_DH_RUSSIANPOST_AVIA_DESCRIPTION"),
					
					"RESTRICTIONS_WEIGHT" => array(0),
					"RESTRICTIONS_SUM" => array(0),
				),
				
				"comb" => array(
					"TITLE" => GetMessage("SALE_DH_RUSSIANPOST_COMB_TITLE"),
					"DESCRIPTION" => GetMessage("SALE_DH_RUSSIANPOST_COMB_DESCRIPTION"),
					
					"RESTRICTIONS_WEIGHT" => array(0),
					"RESTRICTIONS_SUM" => array(0),
				),
				
				"rapid" => array(
					"TITLE" => GetMessage("SALE_DH_RUSSIANPOST_RAPID_TITLE"),
					"DESCRIPTION" => GetMessage("SALE_DH_RUSSIANPOST_RAPID_DESCRIPTION"),
					
					"RESTRICTIONS_WEIGHT" => array(0),
					"RESTRICTIONS_SUM" => array(0),
				),
			)
		);
	}
	
	function GetConfig()
	{
		$arConfig = array(
			"CONFIG_GROUPS" => array(
				"all" => GetMessage('SALE_DH_RUSSIANPOST_CONFIG_TITLE'),
			),
			
			"CONFIG" => array(
				"category" => array(
					"TYPE" => "DROPDOWN",
					"DEFAULT" => DELIVERY_RUSSIANPOST_CATEGORY_DEFAULT,
					"TITLE" => GetMessage('SALE_DH_RUSSIANPOST_CONFIG_CATEGORY'),
					"GROUP" => "all",
					"VALUES" => array(),
				),
			),
		);
		
		$arList = array(42, 43, 44, 23, 52, 12, 13, 30, 41, 50, 33, 26, 53, 36, 16, 51, 54);
		$cnt = count($arList);
		
		for ($i = 0; $i < $cnt; $i++)
		{
			$arConfig["CONFIG"]["category"]["VALUES"][$arList[$i]] = GetMessage('SALE_DH_RUSSIANPOST_CONFIG_CATEGORY_'.$arList[$i]);
		}
		
		return $arConfig; 
	}
	
	function GetSettings($strSettings)
	{
		return array(
			"category" => intval($strSettings)
		);
	}
	
	function SetSettings($arSettings)
	{
		//$category = intval($arSettings["category"]);
		//if ($category <= 0 || $category > 8) return DELIVERY_RUSSIANPOST_CATEGORY_DEFAULT;
		//else return $category;
		return intval($arSettings["category"]);
	}
	
	function __GetLocation($location, $bGetZIP = false)
	{
		$arLocation = CSaleLocation::GetByID($location);
		
		if ($bGetZIP)
		{
			$arLocation["IS_RUSSIAN"] = CDeliveryRUSSIANPOST::__IsRussian($arLocation) ? "Y" : "N";
			$arLocation["ZIP"] = array();

			if ($arLocation["IS_RUSSIAN"] == "Y")
			{
				$rsZIPList = CSaleLocation::GetLocationZIP($location);
				while ($arZIP = $rsZIPList->Fetch())
				{
					$arLocation["ZIP"][] = $arZIP["ZIP"];
				}
			}
		}
		
		return $arLocation;
	}
	
	function __GetCountry($arLocation)
	{
		static $arRUSSIANPOSTCountryList;
		
		if (!is_array($arRUSSIANPOSTCountryList))
		{
			require("russianpost/country.php");
		}
		
		foreach ($arRUSSIANPOSTCountryList as $country_id => $country_name)
		{
			if (
				strtoupper($arLocation["COUNTRY_NAME_ORIG"]) == $country_name
				|| strtoupper($arLocation["COUNTRY_SHORT_NAME"]) == $country_name
				|| strtoupper($arLocation["COUNTRY_NAME_LANG"]) == $country_name
				|| strtoupper($arLocation["COUNTRY_NAME"]) == $country_name
			)
			{
				return array(
					"ID" => $country_id,
					"NAME" => $country_name,
				);
			}
		}
	}
	
	function Calculate($profile, $arConfig, $arOrder, $STEP, $TEMP = false)
	{
		if ($STEP >= 3) 
			return array(
				"RESULT" => "ERROR",
				"TEXT" => GetMessage('SALE_DH_RUSSIANPOST_ERROR_CONNECT'),
			);

		if ($arOrder["WEIGHT"] <= 0) $arOrder["WEIGHT"] = 1;
		
		$arLocationFrom = CDeliveryRUSSIANPOST::__GetLocation($arOrder["LOCATION_FROM"]);
		$arLocationTo = CDeliveryRUSSIANPOST::__GetLocation($arOrder["LOCATION_TO"], true);

		$zip = COption::GetOptionString('sale', 'location_zip');
		if (strlen($zip) > 0)
			$arLocationFrom["ZIP"] = array(0 => $zip);
		
		if ($arLocationTo["IS_RUSSIAN"] == 'Y' && count($arLocationTo["ZIP"]) <= 0)
		{
			return array(
				"RESULT" => "ERROR",
				"TEXT" => GetMessage('SALE_DH_RUSSIANPOST_ERROR_NOZIP'),
			);
		}
	
		$cache_id = "russianpost"."|".$profile."|".$arConfig["category"]["VALUE"]."|".$arOrder["LOCATION_FROM"]."|".($arLocationTo["IS_RUSSIAN"] == 'Y' ? $arLocationTo["ZIP"][0] : $arOrder["LOCATION_TO"]);
		
		if (in_array($arConfig["category"]["VALUE"], array(23, 52, 12, 13, 26, 53, 16, 51)))
			$cache_id .= "|".ceil(CSaleMeasure::Convert($arOrder["WEIGHT"], "G", "KG")/20);
		else
			$cache_id .= "|".ceil(CSaleMeasure::Convert($arOrder["WEIGHT"], "G", "KG")/500);
		
		$obCache = new CPHPCache();
		if ($obCache->InitCache(DELIVERY_RUSSIANPOST_CACHE_LIFETIME, $cache_id, "/"))
		{
			$vars = $obCache->GetVars();
			$result = $vars["RESULT"];
			
			if (in_array($arConfig["category"]["VALUE"], array("26", "53", "36", "16", "51")))
				$result += $arOrder["PRICE"] * DELIVERY_RUSSIANPOST_PRICE_TARIFF;
			
			return array(
				"RESULT" => "OK",
				"VALUE" => $result
			);
		}
		
		$arQuery = array();	
		
		$arProfile = array("ground" => 1, "avia" => 2, "comb" => 3, "rapid" => 4);

		if ($arLocationTo["IS_RUSSIAN"] == "Y")
		{
			$arQuery[] = DELIVERY_RUSSIANPOST_SERVER_POST_CATEGORY."=".urlencode($arConfig["category"]["VALUE"]);
			$arQuery[] = DELIVERY_RUSSIANPOST_SERVER_POST_CATEGORY_NAME."=".urlencode(GetMessage("SALE_DH_RUSSIANPOST_CONFIG_CATEGORY_".$arConfig["category"]["VALUE"]));
			
			$arQuery[] = DELIVERY_RUSSIANPOST_SERVER_POST_PROFILE."=".urlencode($arProfile[$profile]);
			$arQuery[] = DELIVERY_RUSSIANPOST_SERVER_POST_PROFILE_NAME.'='.urlencode(GetMessage("SALE_DH_RUSSIANPOST_".strtoupper($profile)));
			$arQuery[] = DELIVERY_RUSSIANPOST_SERVER_POST_COUNTRY."=643";
			$arQuery[] = DELIVERY_RUSSIANPOST_SERVER_POST_COUNTRY_NAME.'='.urlencode($GLOBALS['APPLICATION']->ConvertCharset('Ðîññèéñêàÿ Ôåäåðàöèÿ', LANG_CHARSET, 'utf-8'));

			$arQuery[] = DELIVERY_RUSSIANPOST_SERVER_POST_WEIGHT."=".urlencode($arOrder["WEIGHT"]);
		
			if (in_array($arConfig["category"]["VALUE"], array("26", "53", "36", "16", "51")))
			{
				$arQuery[] = DELIVERY_RUSSIANPOST_SERVER_POST_PRICE."=".urlencode($arOrder["PRICE"]);
			}
			else
			{
				$arQuery[] = DELIVERY_RUSSIANPOST_SERVER_POST_PRICE."=0";
			}
			
			$arQuery[] = DELIVERY_RUSSIANPOST_SERVER_POST_ZIP."=".urlencode($arLocationTo["ZIP"][0]);
		}
		else
		{
			$arQuery[] = DELIVERY_RUSSIANPOST_SERVER_POST_CATEGORY."=".urlencode($arConfig["category"]["VALUE"]);
			$arQuery[] = DELIVERY_RUSSIANPOST_SERVER_POST_CATEGORY_NAME."=".urlencode(GetMessage("SALE_DH_RUSSIANPOST_CONFIG_CATEGORY_".$arConfig["category"]["VALUE"]));
			$arQuery[] = DELIVERY_RUSSIANPOST_SERVER_POST_PROFILE."=".urlencode($arProfile[$profile]);
			$arQuery[] = DELIVERY_RUSSIANPOST_SERVER_POST_PROFILE_NAME.'='.urlencode(GetMessage("SALE_DH_RUSSIANPOST_".strtoupper($profile)));
			$arCountry = CDeliveryRUSSIANPOST::__GetCountry($arLocationTo);
			
			$arQuery[] = DELIVERY_RUSSIANPOST_SERVER_POST_COUNTRY."=".urlencode($arCountry["ID"]);
			$arQuery[] = DELIVERY_RUSSIANPOST_SERVER_POST_COUNTRY_NAME."=".urlencode($GLOBALS['APPLICATION']->ConvertCharset($arCountry["NAME"], LANG_CHARSET, 'utf-8'));

			$arQuery[] = DELIVERY_RUSSIANPOST_SERVER_POST_WEIGHT."=".urlencode($arOrder["WEIGHT"]);
			$arQuery[] = DELIVERY_RUSSIANPOST_SERVER_POST_PRICE."=0";
			$arQuery[] = DELIVERY_RUSSIANPOST_SERVER_POST_ZIP."=0";
		}
		
		$data = QueryGetData(
			DELIVERY_RUSSIANPOST_SERVER, 
			DELIVERY_RUSSIANPOST_SERVER_PORT,
			DELIVERY_RUSSIANPOST_SERVER_PAGE,
			implode("&", $arQuery),
			$error_number = 0,
			$error_text = "",
			DELIVERY_RUSSIANPOST_SERVER_METHOD
		);
		
		$data = $GLOBALS['APPLICATION']->ConvertCharset($data, 'utf-8', LANG_CHARSET);
		
		CDeliveryRUSSIANPOST::__Write2Log($error_number.": ".$error_text);
		CDeliveryRUSSIANPOST::__Write2Log($data);
		
		if (strlen($data) <= 0)
		{
			return array(
				"RESULT" => "ERROR",
				"TEXT" => GetMessage('SALE_DH_RUSSIANPOST_ERROR_CONNECT'),
			);
		}
		
		if (strstr($data, DELIVERY_RUSSIANPOST_VALUE_CHECK_STRING))
		{
			$bResult = preg_match(
				$arLocationTo["IS_RUSSIAN"] == "Y" 
					? DELIVERY_RUSSIANPOST_VALUE_CHECK_REGEXP_RUS 
					: DELIVERY_RUSSIANPOST_VALUE_CHECK_REGEXP, 
				$data, 
				$matches
			);
			
			if ($arLocationTo["IS_RUSSIAN"] == "Y" && !$bResult)
			{
				$bResult = preg_match(
					$arLocationTo["IS_RUSSIAN"] == "Y" 
						? DELIVERY_RUSSIANPOST_VALUE_CHECK_REGEXP_RUS 
						: DELIVERY_RUSSIANPOST_VALUE_CHECK_REGEXP, 
					$data, 
					$matches
				);
			}
		
			if ($bResult)
			{
				$obCache->StartDataCache();
				
				$result = $matches[1];
				$result = str_replace(array(" ", ","), array("", "."), $result);
				$result = doubleval($result);
				
				$obCache->EndDataCache(
					array(
						"RESULT" => $result
					)
				);
				
				if (in_array($arConfig["category"]["VALUE"], array("26", "53", "36", "16", "51")))
				{
					$result += $arOrder["PRICE"] * DELIVERY_RUSSIANPOST_PRICE_TARIFF;
				}
				
				return array(
					"RESULT" => "OK",
					"VALUE" => $result,
				);
			}
			else
			{
				return array(
					"RESULT" => "ERROR",
					"TEXT" => GetMessage('SALE_DH_RUSSIANPOST_ERROR_RESPONSE'),
				);
			}
		}
		else
			return array(
				"RESULT" => "ERROR",
				"TEXT" => GetMessage('SALE_DH_RUSSIANPOST_ERROR_RESPONSE'),
			);
	}
	
	function Compability($arOrder)
	{
		$arLocationFrom = CSaleLocation::GetByID($arOrder["LOCATION_FROM"]);
	
		if (
			strtoupper($arLocationFrom["CITY_NAME_ORIG"]) == "ÌÎÑÊÂÀ" 
			|| strtoupper($arLocationFrom["CITY_SHORT_NAME"]) == "ÌÎÑÊÂÀ" 
			|| strtoupper($arLocationFrom["CITY_NAME_LANG"]) == "ÌÎÑÊÂÀ" 
			|| strtoupper($arLocationFrom["CITY_NAME_ORIG"]) == "MOSCOW" 
			|| strtoupper($arLocationFrom["CITY_SHORT_NAME"]) == "MOSCOW" 
			|| strtoupper($arLocationFrom["CITY_NAME_LANG"]) == "MOSCOW"
		)
		{
			$arLocationTo = CSaleLocation::GetByID($arOrder["LOCATION_TO"]);

			if (CDeliveryRUSSIANPOST::__IsRussian($arLocationTo))
			{
				//return array("ground", "avia", "comb", "rapid"); // online calculator for "comb" and "rapid" is unstable
				return array("ground", "avia");
			}
			else
			{
				return array("ground", "avia");
			}
		}
		else
		{
			return array();
		}
	} 
	
	function __IsRussian($arLocation)
	{
		return 
			(strtoupper($arLocation["COUNTRY_NAME_ORIG"]) == "ÐÎÑÑÈß"
			|| strtoupper($arLocation["COUNTRY_SHORT_NAME"]) == "ÐÎÑÑÈß" 
			|| strtoupper($arLocation["COUNTRY_NAME_LANG"]) == "ÐÎÑÑÈß"
			|| strtoupper($arLocation["COUNTRY_NAME_ORIG"]) == "RUSSIA" 
			|| strtoupper($arLocation["COUNTRY_SHORT_NAME"]) == "RUSSIA" 
			|| strtoupper($arLocation["COUNTRY_NAME_LANG"]) == "RUSSIA"
			|| strtoupper($arLocation["COUNTRY_NAME_ORIG"]) == "ÐÎÑÑÈÉÑÊÀß ÔÅÄÅÐÀÖÈß" 
			|| strtoupper($arLocation["COUNTRY_SHORT_NAME"]) == "ÐÎÑÑÈÉÑÊÀß ÔÅÄÅÐÀÖÈß"
			|| strtoupper($arLocation["COUNTRY_NAME_LANG"]) == "ÐÎÑÑÈÉÑÊÀß ÔÅÄÅÐÀÖÈß"
			|| strtoupper($arLocation["COUNTRY_NAME_ORIG"]) == "RUSSIAN FEDERATION" 
			|| strtoupper($arLocation["COUNTRY_SHORT_NAME"]) == "RUSSIAN FEDERATION"
			|| strtoupper($arLocation["COUNTRY_NAME_LANG"]) == "RUSSIAN FEDERATION");
	}
	
	function __Write2Log($data)
	{
		if (defined('DELIVERY_RUSSIANPOST_WRITE_LOG') && DELIVERY_RUSSIANPOST_WRITE_LOG === 1)
		{
			$fp = fopen(dirname(__FILE__)."/russianpost.log", "a");
			fwrite($fp, "\r\n==========================================\r\n");
			fwrite($fp, $data);
			fclose($fp);
		}
	}
}

AddEventHandler("sale", "onSaleDeliveryHandlersBuildList", array('CDeliveryRUSSIANPOST', 'Init')); 
?>