<?
/**********************************************************************
Delivery handler for CPCR delivery service (http://www.cpcr.ru/)
It uses on-line calculator. Calculation only to Russia.
Files: 
cpcr/cities.php - cache of cpcr ids for cities
cpcr/locations.php - list of cpcr ids for countries.
**********************************************************************/

CModule::IncludeModule("sale");

IncludeModuleLangFile('/bitrix/modules/sale/delivery/delivery_cpcr.php');

define('DELIVERY_CPCR_WRITE_LOG', 1); // flag 'write to log'. use CDeliveryCPCR::__WriteToLog() for logging.
define('DELIVERY_CPCR_CACHE_LIFETIME', 2592000); // cache lifetime - 30 days (60*60*24*30)

define('DELIVERY_CPCR_CATEGORY_DEFAULT', 8); // default category for delivered goods

define('DELIVERY_CPCR_PRICE_TARIFF', 0.0025); // price koefficient - 0.25%

define('DELIVERY_CPCR_COUNTRY_DEFAULT', '209|0'); // default country - Russia
define('DELIVERY_CPCR_CITY_DEFAULT', '40|0'); // default city - Moscow

define('DELIVERY_CPCR_SERVER', 'cpcr.ru'); // server name to send data
define('DELIVERY_CPCR_SERVER_PORT', 80); // server port
define('DELIVERY_CPCR_SERVER_PAGE', '/cgi-bin/post.pl?TariffCompute'); // server page url
define('DELIVERY_CPCR_SERVER_METHOD', 'POST'); // data send method

define('DELIVERY_CPCR_SERVER_POST_FROM_REGION', 'FromRegion'); // query variable name for "from" region id
define('DELIVERY_CPCR_SERVER_POST_FROM_CITY_NAME', 'FromCityName'); // query variable name for "from" city name
define('DELIVERY_CPCR_SERVER_POST_FROM_CITY', 'FromCity'); // query variable name for "from" city id
define('DELIVERY_CPCR_SERVER_POST_WEIGHT', 'Weight'); // query variable name for order weight
define('DELIVERY_CPCR_SERVER_POST_CATEGORY', 'Nature'); // query variable name for order goods category
define('DELIVERY_CPCR_SERVER_POST_PRICE', 'Amount'); // query variable name for order price
define('DELIVERY_CPCR_SERVER_POST_TO_COUNTRY', 'Country'); // query variable name for "to" country id
define('DELIVERY_CPCR_SERVER_POST_TO_REGION', 'ToRegion'); // query variable name for "to" region id
define('DELIVERY_CPCR_SERVER_POST_TO_CITY_NAME', 'ToCityName'); // query variable name for "to" city name
define('DELIVERY_CPCR_SERVER_POST_TO_CITY', 'ToCity'); // query variable name for "to" city id

define('DELIVERY_CPCR_VALUE_CHECK_STRING', '<td class="vydacha1"><b>»того (руб) </b></td>'); // first check string - to determine whether delivery price is in response
define(
	'DELIVERY_CPCR_VALUE_CHECK_REGEXP', 
	'/<td class="vydacha1"><b>»того\s\(руб\)\s<\/b><\/td>\n<td class="vydacha2">*([0-9,\s]+)р.<\/td>/i'
); // second check string - regexp to parse final price from response

class CDeliveryCPCR
{
	function Init()
	{
		// fix a possible currency bug
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
			"SID" => "cpcr", // unique string identifier
			"NAME" => GetMessage('SALE_DH_CPCR_NAME'), // handler public title
			"DESCRIPTION" => GetMessage('SALE_DH_CPCR_DESCRIPTION'), // handler public dedcription
			"DESCRIPTION_INNER" => GetMessage('SALE_DH_CPCR_DESCRIPTION_INNER'), // handler private description for admin panel
			"BASE_CURRENCY" => $base_currency, // handler base currency
			
			"HANDLER" => __FILE__, // handler path - don't change it if you do not surely know what you are doin
			
			/* Handler methods */
			"DBGETSETTINGS" => array("CDeliveryCPCR", "GetSettings"), // callback for method for conversion of string representation to handler settings
			"DBSETSETTINGS" => array("CDeliveryCPCR", "SetSettings"), // callback for method for conversion of handler settings to  string representation
			"GETCONFIG" => array("CDeliveryCPCR", "GetConfig"), // callback method to get handler settings list
			
			"COMPABILITY" => array("CDeliveryCPCR", "Compability"), // callback method to check whether handler is compatible with current order
			"CALCULATOR" => array("CDeliveryCPCR", "Calculate"), // callback method to calculate delivery price
			
			/* List of delivery profiles */
			"PROFILES" => array(
				"simple" => array(
					"TITLE" => GetMessage("SALE_DH_CPCR_SIMPLE_TITLE"),
					"DESCRIPTION" => GetMessage("SALE_DH_CPCR_SIMPLE_DESCRIPTION"),
					
					"RESTRICTIONS_WEIGHT" => array(0),
					"RESTRICTIONS_SUM" => array(0),
				),
				"super" => array(
					"TITLE" => GetMessage("SALE_DH_CPCR_SUPER_TITLE"),
					"DESCRIPTION" => GetMessage("SALE_DH_CPCR_SUPER_DESCRIPTION"),
					
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
				"all" => GetMessage('SALE_DH_CPCR_CONFIG_TITLE'),
			),
			
			"CONFIG" => array(
				"category" => array(
					"TYPE" => "DROPDOWN",
					"DEFAULT" => DELIVERY_CPCR_CATEGORY_DEFAULT,
					"TITLE" => GetMessage('SALE_DH_CPCR_CONFIG_CATEGORY'),
					"GROUP" => "all",
					"VALUES" => array(),
				),
			),
		);
		
		for ($i = 1; $i < 9; $i++)
		{
			$arConfig["CONFIG"]["category"]["VALUES"][$i] = GetMessage('SALE_DH_CPCR_CONFIG_CATEGORY_'.$i);
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
		$category = intval($arSettings["category"]);
		if ($category <= 0 || $category > 8) return DELIVERY_CPCR_CATEGORY_DEFAULT;
		else return $category;
	}
	
	function __GetLocation($location)
	{
		static $arCPCRCountries;
		static $arCPCRCity;
		
		$arLocation = CSaleLocation::GetByID($location);
		
		$arReturn = array();
		
		if (!is_array($arCPCRCountries))
		{
			require ("cpcr/locations.php");
		}
		
		foreach ($arCPCRCountries as $country_id => $country_title)
		{
			if (
				$country_title == $arLocation["COUNTRY_NAME_ORIG"]
				||
				$country_title == $arLocation["COUNTRY_SHORT_NAME"]
				||
				$country_title == $arLocation["COUNTRY_NAME_LANG"]
			)
			{
				$arReturn["COUNTRY"] = $country_id;
				break;
			}
		}
		
		$arReturn["CITY"] = $arLocation["CITY_NAME_LANG"];
		
		if (!is_array($arCPCRCity))
		{
			require ("cpcr/cities.php");
		}
		
		if (is_set($arCPCRCity, $arLocation["CITY_ID"]))
		{
			$arReturn["CITY_ID"] = $arCPCRCity[$arLocation["CITY_ID"]];
		}
		
		$arReturn["ORIGINAL"] = array(
			"ID" => $arLocation["ID"],
			"COUNTRY_ID" => $arLocation["COUNTRY_ID"],
			"CITY_ID" => $arLocation["CITY_ID"],
		);
		
		return $arReturn;
	}
	
	function __CorrectLocations($CITY_ID, $CPCR_ID)
	{
		static $arCPCRCity;
		
		if (!is_array($arCPCRCity))
			require ("cpcr/cities.php");
		
		$arCPCRCity[$CITY_ID] = $CPCR_ID;
		
		if ($fp = fopen(dirname(__FILE__)."/cpcr/cities.php", "w"))
		{
			fwrite($fp, '<'."?\r\n");
			fwrite($fp, '$'."arCPCRCity = array();\r\n");
			foreach ($arCPCRCity as $key => $value)
			{
				fwrite($fp, '$'."arCPCRCity['".intval($key)."'] = '".htmlspecialchars($value)."';\r\n");
			}
			fwrite($fp, '?'.'>');
			fclose($fp);
		}
	}
	
	function Calculate($profile, $arConfig, $arOrder, $STEP, $TEMP = false)
	{
		if ($STEP >= 3) 
			return array(
				"RESULT" => "ERROR",
				"TEXT" => GetMessage('SALE_DH_CPCR_ERROR_CONNECT'),
			);

		$arOrder["WEIGHT"] = CSaleMeasure::Convert($arOrder["WEIGHT"], "G", "KG");
		if ($arOrder["WEIGHT"] <= 0) $arOrder["WEIGHT"] = 0.001; // weight must not be null - let it be 1 gramm

		$arLocationFrom = CDeliveryCPCR::__GetLocation($arOrder["LOCATION_FROM"]);
		$arLocationTo = CDeliveryCPCR::__GetLocation($arOrder["LOCATION_TO"]);

		if ($TEMP)
		{
			$arTemp = unserialize($TEMP);
			if (is_array($arTemp))
			{
				foreach ($arTemp as $key => $value)
				{
					if ($key == DELIVERY_CPCR_SERVER_POST_FROM_CITY)
						$arLocationFrom["CITY_ID"] = $value;
					else
						$arLocationTo["CITY_ID"] = $value;
				}
			}
		}
	
		// caching is dependent from category, locations "from" & "to" and from weight interval
		$cache_id = "cpcr"."|".$arConfig["category"]['VALUE']."|".$arLocationFrom["ORIGINAL"]["COUNTRY_ID"]."|".$arLocationFrom["ORIGINAL"]["CITY_ID"]."|".$arLocationTo["ORIGINAL"]["COUNTRY_ID"]."|".$arLocationTo["ORIGINAL"]["CITY_ID"];
		
		if ($arOrder["WEIGHT"] <= 0.5) $cache_id .= "|0"; // first interval - up to 0.5 kg
		elseif ($arOrder["WEIGHT"] <= 1) $cache_id .= "|1"; //2nd interval - up to 1 kg
		else $cache_id .= "|".ceil($arOrder["WEIGHT"]); // other intervals - up to next natural number
	
		$obCache = new CPHPCache();
		
		if ($obCache->InitCache(DELIVERY_CPCR_CACHE_LIFETIME, $cache_id, "/"))
		{
			// cache found
			
			$vars = $obCache->GetVars();
			$result = $vars["RESULT"];
			
			if ($profile == "super") $result *= 1.7; // "super express profile - add coefficient"
			$result += $arOrder["PRICE"] * DELIVERY_CPCR_PRICE_TARIFF; // price addition
			
			return array(
				"RESULT" => "OK",
				"VALUE" => $result
			);
		}
		
		// format HTTP query request data
		$arQuery = array();
		
		if (is_set($arLocationFrom["REGION"]))
			$arQuery[] = DELIVERY_CPCR_SERVER_POST_FROM_REGION."=".urlencode($arLocationFrom["REGION"]);
		else
			$arQuery[] = DELIVERY_CPCR_SERVER_POST_FROM_REGION."=".urlencode(DELIVERY_CPCR_CITY_DEFAULT);
		
		if (is_set($arLocationFrom["CITY_ID"]))
			$arQuery[] = DELIVERY_CPCR_SERVER_POST_FROM_CITY."=".urlencode($arLocationFrom["CITY_ID"]);
		else
			$arQuery[] = DELIVERY_CPCR_SERVER_POST_FROM_CITY_NAME."=".urlencode($GLOBALS['APPLICATION']->ConvertCharset($arLocationFrom["CITY"], LANG_CHARSET, 'windows-1251'));

		$arQuery[] = DELIVERY_CPCR_SERVER_POST_WEIGHT."=".urlencode($arOrder["WEIGHT"]);
		$arQuery[] = DELIVERY_CPCR_SERVER_POST_CATEGORY."=".urlencode($arConfig["category"]["VALUE"]);
		//$arQuery[] = DELIVERY_CPCR_SERVER_POST_PRICE."=".urlencode($arOrder["PRICE"]);
		// price coefficient will be added later - to make caching independent from price
		$arQuery[] = DELIVERY_CPCR_SERVER_POST_PRICE."=0";
		$arQuery[] = DELIVERY_CPCR_SERVER_POST_TO_COUNTRY."=".urlencode($arLocationTo["COUNTRY"]);
		
		if (is_set($arLocationTo["REGION"]))
			$arQuery[] = DELIVERY_CPCR_SERVER_POST_TO_REGION."=".urlencode($arLocationTo["REGION"]);
		else
			$arQuery[] = DELIVERY_CPCR_SERVER_POST_TO_REGION."=".urlencode(DELIVERY_CPCR_CITY_DEFAULT);
		
		if (is_set($arLocationTo["CITY_ID"]))
			$arQuery[] = DELIVERY_CPCR_SERVER_POST_TO_CITY."=".urlencode($arLocationTo["CITY_ID"]);
		else
			$arQuery[] = DELIVERY_CPCR_SERVER_POST_TO_CITY_NAME."=".urlencode($GLOBALS['APPLICATION']->ConvertCharset($arLocationTo["CITY"], LANG_CHARSET, 'windows-1251'));
		
		//$query_string = $GLOBALS['APPLICATION']->ConvertCharset(implode("&", $arQuery), LANG_CHARSET, 'windows-1251');
		$query_string = implode("&", $arQuery);
		
		// get data from server
		$data = QueryGetData(
			DELIVERY_CPCR_SERVER, 
			DELIVERY_CPCR_SERVER_PORT,
			DELIVERY_CPCR_SERVER_PAGE,
			$query_string,
			$error_number = 0,
			$error_text = "",
			DELIVERY_CPCR_SERVER_METHOD
		);
		
		$data = $GLOBALS["APPLICATION"]->ConvertCharset($data, 'windows-1251', LANG_CHARSET);
		
		CDeliveryCPCR::__Write2Log($error_number.": ".$error_text);
		CDeliveryCPCR::__Write2Log($data);
		
		if (strlen($data) <= 0)
		{
			return array(
				"RESULT" => "ERROR",
				"TEXT" => GetMessage('SALE_DH_CPCR_ERROR_CONNECT'),
			);
		}

		if (strstr($data, DELIVERY_CPCR_VALUE_CHECK_STRING))
		{
			// first check string found
			
			if (preg_match(
				DELIVERY_CPCR_VALUE_CHECK_REGEXP.BX_UTF_PCRE_MODIFIER, 
				$data, 
				$matches
			))
			{
				// final price found
				$obCache->StartDataCache();
				$result = $matches[1];
				$result = preg_replace('/\s/', '', $result);
				$result = str_replace(',', '.', $result);
				$result = doubleval($result);
				$obCache->EndDataCache(
					array(
						"RESULT" => $result
					)
				);
				
				if ($profile == "super") $result *= 1.7; // "super express profile - add coefficient"
				$result += $arOrder["PRICE"] * DELIVERY_CPCR_PRICE_TARIFF; // price addition
				
				return array(
					"RESULT" => "OK",
					"VALUE" => $result,
				);
			}
			else
			{
				return array(
					"RESULT" => "ERROR",
					"TEXT" => GetMessage('SALE_DH_CPCR_ERROR_RESPONSE'),
				);
			}
		}

		
		$arTemp = array();
		
		// try to get right locations from server response
		$arTestStr = array(
			DELIVERY_CPCR_SERVER_POST_FROM_CITY => '<input type="radio" name="FromCity" value="',
			DELIVERY_CPCR_SERVER_POST_TO_CITY => '<input type="radio" name="ToCity" value="'
		);
		
		foreach ($arTestStr as $str_id => $teststr)
		{
			$index = strpos($data, $teststr);
			if ($index !== false)
			{
				$index_next_quote = strpos($data, '"', $index+strlen($teststr));
				$index_start = $index + strlen($teststr);
				$arTemp[$str_id] = substr($data, $index_start, $index_next_quote - $index_start);
			}
		}

		if (count($arTemp) > 0)
		{
			// if new location ids're found - correct them and go to next step
			foreach ($arTemp as $key => $value)
			{
				CDeliveryCPCR::__CorrectLocations(
					$key == DELIVERY_CPCR_SERVER_POST_FROM_CITY ? 
						$arLocationFrom["ORIGINAL"]["CITY_ID"] : 
						$arLocationTo["ORIGINAL"]["CITY_ID"],
					$value
				);
			}

			return array(
				"RESULT" => "NEXT_STEP",
				"TEXT" => GetMessage('SALE_DH_CPCR_NEXT_STEP'),
				"TEMP" => serialize($arTemp),
			);
		}

		return array(
			"RESULT" => "ERROR",
			"TEXT" => GetMessage('SALE_DH_CPCR_ERROR_RESPONSE'),
		);
	}
	
	function Compability($arOrder)
	{
		$arLocationFrom = CDeliveryCPCR::__GetLocation($arOrder["LOCATION_FROM"]);
		$arLocationTo = CDeliveryCPCR::__GetLocation($arOrder["LOCATION_TO"]);
	
		//echo '<pre>';
		//print_r($arLocationFrom);
		//print_r($arLocationTo);
		//echo '</pre>';
	
		// delivery only from russia and to russia
		if (
			$arLocationFrom["COUNTRY"] != DELIVERY_CPCR_COUNTRY_DEFAULT 
			|| 
			$arLocationTo["COUNTRY"] != DELIVERY_CPCR_COUNTRY_DEFAULT
		) 
			return array();
		else 
			return array("simple", "super");
	} 
	
	function __Write2Log($data)
	{
		if (defined('DELIVERY_CPCR_WRITE_LOG') && DELIVERY_CPCR_WRITE_LOG === 1)
		{
			$fp = fopen(dirname(__FILE__)."/cpcr.log", "a");
			fwrite($fp, "\r\n==========================================\r\n");
			fwrite($fp, $data);
			fclose($fp);
		}
	}
}

AddEventHandler("sale", "onSaleDeliveryHandlersBuildList", array('CDeliveryCPCR', 'Init')); 
?>