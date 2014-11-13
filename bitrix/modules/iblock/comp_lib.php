<?
IncludeModuleLangFile(__FILE__);

class CIBlockPriceTools
{
	function GetCatalogPrices($IBLOCK_ID, $arPriceCode)
	{
		global $USER;
		$arCatalogPrices = array();
		if(CModule::IncludeModule("catalog"))
		{
			$bFromCatalog = true;
			$arCatalogGroupCodesFilter = array();
			foreach($arPriceCode as $key => $value)
			{
				$value = trim($value);
				if(strlen($value) > 0)
					$arCatalogGroupCodesFilter[$value] = true;
			}
			$arCatalogGroupsFilter = array();
			$arCatalogGroups = CCatalogGroup::GetListArray();
			foreach($arCatalogGroups as $key => $value)
			{
				if(array_key_exists($value["NAME"], $arCatalogGroupCodesFilter))
				{
					$arCatalogGroupsFilter[] = $key;
					$arCatalogPrices[$value["NAME"]] = array(
						"ID" => htmlspecialchars($value["ID"]),
						"TITLE" => htmlspecialchars($value["NAME_LANG"]),
						"SELECT" => "CATALOG_GROUP_".$value["ID"],
					);
				}
			}
			$arPriceGroups = CCatalogGroup::GetGroupsPerms($USER->GetUserGroupArray(), $arCatalogGroupsFilter);
			foreach($arCatalogPrices as $name=>$value)
			{
				$arCatalogPrices[$name]["CAN_VIEW"]=in_array($value["ID"], $arPriceGroups["view"]);
				$arCatalogPrices[$name]["CAN_BUY"]=in_array($value["ID"], $arPriceGroups["buy"]);
			}
		}
		else
		{
			$bFromCatalog = false;
			$arPriceGroups = array(
				"view" => array(),
			);
			$rsProperties = CIBlock::GetProperties($IBLOCK_ID, array(), array("PROPERTY_TYPE"=>"N"));
			while($arProperty = $rsProperties->Fetch())
			{
				if($arProperty["MULTIPLE"]=="N" && in_array($arProperty["CODE"], $arPriceCode))
				{
					$arPriceGroups["view"][]=htmlspecialchars("PROPERTY_".$arProperty["CODE"]);
					$arCatalogPrices[$arProperty["CODE"]] = array(
						"ID"=>htmlspecialchars($arProperty["ID"]),
						"TITLE"=>htmlspecialchars($arProperty["NAME"]),
						"SELECT" => "PROPERTY_".$arProperty["ID"],
						"CAN_VIEW"=>true,
						"CAN_BUY"=>false,
					);
				}
			}
		}
		return $arCatalogPrices;
	}
	function GetItemPrices($IBLOCK_ID, $arCatalogPrices, $arItem, $bVATInclude = true)
	{
		global $USER;
		$arPrices = array();
		if(CModule::IncludeModule("catalog"))
		{
			foreach($arCatalogPrices as $key => $value)
			{
				if($value["CAN_VIEW"] && strlen($arItem["CATALOG_PRICE_".$value["ID"]]) > 0)
				{
					// get clear price without VAT.
					if ($arItem['CATALOG_VAT_INCLUDED'] == 'Y')
					{
						$arItem['CATALOG_PRICE_'.$value['ID']] /= (1 + $arItem['CATALOG_VAT'] * 0.01);
					}

					$arDiscounts = CCatalogDiscount::GetDiscount(
						$arItem["ID"],
						$arItem["IBLOCK_ID"],
						array($value["ID"]),
						$USER->GetUserGroupArray(),
						"N",
						SITE_ID,
						false
					);
					$discountPrice = CCatalogProduct::CountPriceWithDiscount(
						$arItem["CATALOG_PRICE_".$value["ID"]],
						$arItem["CATALOG_CURRENCY_".$value["ID"]],
						$arDiscounts
					);

					$vat_value_discount = $discountPrice * $arItem['CATALOG_VAT'] * 0.01;
					$vat_discountPrice = $discountPrice + $vat_value_discount;
					$vat_value = $arItem['CATALOG_PRICE_'.$value['ID']] * $arItem['CATALOG_VAT'] * 0.01;
					$vat_price = $arItem["CATALOG_PRICE_".$value["ID"]] + $vat_value;

					$arPrices[$key] = array(
						"ID" => $arItem["CATALOG_PRICE_ID_".$value["ID"]],

						"VALUE_NOVAT" => $arItem["CATALOG_PRICE_".$value["ID"]],
						"PRINT_VALUE_NOVAT" => FormatCurrency($arItem["CATALOG_PRICE_".$value["ID"]],$arItem["CATALOG_CURRENCY_".$value["ID"]]),

						"VALUE_VAT" => $vat_price,
						"PRINT_VALUE_VAT" => FormatCurrency($vat_price, $arItem["CATALOG_CURRENCY_".$value["ID"]]),

						"VATRATE_VALUE" => $vat_value,
						"PRINT_VATRATE_VALUE" => FormatCurrency($vat_value, $arItem["CATALOG_CURRENCY_".$value["ID"]]),

						"DISCOUNT_VALUE_NOVAT" => $discountPrice,
						"PRINT_DISCOUNT_VALUE_NOVAT" => FormatCurrency($discountPrice, $arItem["CATALOG_CURRENCY_".$value["ID"]]),

						"DISCOUNT_VALUE_VAT" => $vat_discountPrice,
						"PRINT_DISCOUNT_VALUE_VAT" => FormatCurrency($vat_discountPrice, $arItem["CATALOG_CURRENCY_".$value["ID"]]),

						'DISCOUNT_VATRATE_VALUE' => $vat_value_discount,
						'PRINT_DISCOUNT_VATRATE_VALUE' => FormatCurrency($vat_value_discount, $arItem["CATALOG_CURRENCY_".$value["ID"]]),

						"CURRENCY" => $arItem["CATALOG_CURRENCY_".$value["ID"]],
						"CAN_ACCESS" => $arItem["CATALOG_CAN_ACCESS_".$value["ID"]],
						"CAN_BUY" => $arItem["CATALOG_CAN_BUY_".$value["ID"]],
					);

					if ($bVATInclude)
					{
						$arPrices[$key]['VALUE'] = $arPrices[$key]['VALUE_VAT'];
						$arPrices[$key]['PRINT_VALUE'] = $arPrices[$key]['PRINT_VALUE_VAT'];
						$arPrices[$key]['DISCOUNT_VALUE'] = $arPrices[$key]['DISCOUNT_VALUE_VAT'];
						$arPrices[$key]['PRINT_DISCOUNT_VALUE'] = $arPrices[$key]['PRINT_DISCOUNT_VALUE_VAT'];
					}
					else
					{
						$arPrices[$key]['VALUE'] = $arPrices[$key]['VALUE_NOVAT'];
						$arPrices[$key]['PRINT_VALUE'] = $arPrices[$key]['PRINT_VALUE_NOVAT'];
						$arPrices[$key]['DISCOUNT_VALUE'] = $arPrices[$key]['DISCOUNT_VALUE_NOVAT'];
						$arPrices[$key]['PRINT_DISCOUNT_VALUE'] = $arPrices[$key]['PRINT_DISCOUNT_VALUE_NOVAT'];
					}
				}
			}
		}
		else
		{
			foreach($arCatalogPrices as $key => $value)
			{
				if($value["CAN_VIEW"])
				{
					$arPrices[$key] = array(
						"ID" => $arItem["PROPERTY_".$value["ID"]."_VALUE_ID"],
						"VALUE" => round(doubleval($arItem["PROPERTY_".$value["ID"]."_VALUE"]),2),
						"PRINT_VALUE" => round(doubleval($arItem["PROPERTY_".$value["ID"]."_VALUE"]),2)." ".$arItem["PROPERTY_".$value["ID"]."_DESCRIPTION"],
						"DISCOUNT_VALUE" => round(doubleval($arItem["PROPERTY_".$value["ID"]."_VALUE"]),2),
						"PRINT_DISCOUNT_VALUE" => round(doubleval($arItem["PROPERTY_".$value["ID"]."_VALUE"]),2)." ".$arItem["PROPERTY_".$value["ID"]."_DESCRIPTION"],
						"CURRENCY" => $arItem["PROPERTY_".$value["ID"]."_DESCRIPTION"],
						"CAN_ACCESS" => true,
						"CAN_BUY" => false,
					);
				}
			}
		}
		return $arPrices;
	}
	function CanBuy($IBLOCK_ID, $arCatalogPrices, $arItem)
	{
		$result = false;
		if(is_array($arItem["PRICE_MATRIX"]))
		{
			$result =  $arItem["PRICE_MATRIX"]["AVAILABLE"] == "Y";
		}
		else
		{
			foreach($arCatalogPrices as $code=>$arPrice)
			{
				if($arPrice["CAN_BUY"])
				{
					if($arItem["CATALOG_QUANTITY_TRACE"] != "Y"
						|| ($arItem["CATALOG_QUANTITY_TRACE"] == "Y" && IntVal($arItem["CATALOG_QUANTITY"]) > 0))
					{
						$result = true;
					}
				}
			}
		}
		return $result;
	}
}

class CIBlockParameters
{
	function GetFieldCode($name , $parent)
	{
		return array(
			"PARENT" => $parent,
			"NAME" => $name,
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => array(
				"ID" => GetMessage("IBLOCK_FIELD_ID"),
				"CODE" => GetMessage("IBLOCK_FIELD_CODE"),
				"XML_ID" => GetMessage("IBLOCK_FIELD_XML_ID"),
				"NAME" => GetMessage("IBLOCK_FIELD_NAME"),
				"TAGS" => GetMessage("IBLOCK_FIELD_TAGS"),
				"SORT"=> GetMessage("IBLOCK_FIELD_SORT"),
				"PREVIEW_TEXT" => GetMessage("IBLOCK_FIELD_PREVIEW_TEXT"),
				"PREVIEW_PICTURE" => GetMessage("IBLOCK_FIELD_PREVIEW_PICTURE"),
				"DETAIL_TEXT" => GetMessage("IBLOCK_FIELD_DETAIL_TEXT"),
				"DETAIL_PICTURE" => GetMessage("IBLOCK_FIELD_DETAIL_PICTURE"),
				"DATE_ACTIVE_FROM" => GetMessage("IBLOCK_FIELD_DATE_ACTIVE_FROM"),
				"ACTIVE_FROM" => GetMessage("IBLOCK_FIELD_ACTIVE_FROM"),
				"DATE_ACTIVE_TO" => GetMessage("IBLOCK_FIELD_DATE_ACTIVE_TO"),
				"ACTIVE_TO" => GetMessage("IBLOCK_FIELD_ACTIVE_TO"),
				"SHOW_COUNTER" => GetMessage("IBLOCK_FIELD_SHOW_COUNTER"),
				"SHOW_COUNTER_START" => GetMessage("IBLOCK_FIELD_SHOW_COUNTER_START"),
				"IBLOCK_TYPE_ID" => GetMessage("IBLOCK_FIELD_IBLOCK_TYPE_ID"),
				"IBLOCK_ID" => GetMessage("IBLOCK_FIELD_IBLOCK_ID"),
				"IBLOCK_CODE" => GetMessage("IBLOCK_FIELD_IBLOCK_CODE"),
				"IBLOCK_NAME" => GetMessage("IBLOCK_FIELD_IBLOCK_NAME"),
				"IBLOCK_EXTERNAL_ID" => GetMessage("IBLOCK_FIELD_IBLOCK_EXTERNAL_ID"),
				"DATE_CREATE" => GetMessage("IBLOCK_FIELD_DATE_CREATE"),
				"CREATED_BY" => GetMessage("IBLOCK_FIELD_CREATED_BY"),
				"CREATED_USER_NAME" => GetMessage("IBLOCK_FIELD_CREATED_USER_NAME"),
				"TIMESTAMP_X" => GetMessage("IBLOCK_FIELD_TIMESTAMP_X"),
				"MODIFIED_BY" => GetMessage("IBLOCK_FIELD_MODIFIED_BY"),
				"USER_NAME" => GetMessage("IBLOCK_FIELD_USER_NAME"),
			),
		);
	}
	function GetDateFormat($name, $parent)
	{
		$timestamp = mktime(7,30,45,2,22,2007);
		return array(
			"PARENT" => $parent,
			"NAME" => $name,
			"TYPE" => "LIST",
			"VALUES" => array(
				"d-m-Y" => CIBlockFormatProperties::DateFormat("d-m-Y", $timestamp),//"22-02-2007",
				"m-d-Y" => CIBlockFormatProperties::DateFormat("m-d-Y", $timestamp),//"02-22-2007",
				"Y-m-d" => CIBlockFormatProperties::DateFormat("Y-m-d", $timestamp),//"2007-02-22",
				"d.m.Y" => CIBlockFormatProperties::DateFormat("d.m.Y", $timestamp),//"22.02.2007",
				"m.d.Y" => CIBlockFormatProperties::DateFormat("m.d.Y", $timestamp),//"02.22.2007",
				"j M Y" => CIBlockFormatProperties::DateFormat("j M Y", $timestamp),//"22 Feb 2007",
				"M j, Y" => CIBlockFormatProperties::DateFormat("M j, Y", $timestamp),//"Feb 22, 2007",
				"j F Y" => CIBlockFormatProperties::DateFormat("j F Y", $timestamp),//"22 February 2007",
				"F j, Y" => CIBlockFormatProperties::DateFormat("F j, Y", $timestamp),//"February 22, 2007",
				"d.m.y g:i A" => CIBlockFormatProperties::DateFormat("d.m.y g:i A", $timestamp),//"22.02.07 1:30 PM",
				"d.m.y G:i" => CIBlockFormatProperties::DateFormat("d.m.y G:i", $timestamp),//"22.02.07 7:30",
				"d.m.Y H:i" => CIBlockFormatProperties::DateFormat("d.m.Y H:i", $timestamp),//"22.02.2007 07:30",
			),
			"DEFAULT" => $GLOBALS["DB"]->DateFormatToPHP(CSite::GetDateFormat("SHORT")),
			"ADDITIONAL_VALUES" => "Y",
		);
	}
	function AddPagerSettings(&$arComponentParameters, $pager_title, $bDescNumbering=true)
	{
		$arComponentParameters["GROUPS"]["PAGER_SETTINGS"] = array(
			"NAME" => GetMessage("T_IBLOCK_DESC_PAGER_SETTINGS"),
		);
		$arComponentParameters["PARAMETERS"]["DISPLAY_TOP_PAGER"] = Array(
			"PARENT" => "PAGER_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_TOP_PAGER"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		);
		$arComponentParameters["PARAMETERS"]["DISPLAY_BOTTOM_PAGER"] = Array(
			"PARENT" => "PAGER_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_BOTTOM_PAGER"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		);
		$arComponentParameters["PARAMETERS"]["PAGER_TITLE"] = Array(
			"PARENT" => "PAGER_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_PAGER_TITLE"),
			"TYPE" => "STRING",
			"DEFAULT" => $pager_title,
		);
		$arComponentParameters["PARAMETERS"]["PAGER_SHOW_ALWAYS"] = Array(
			"PARENT" => "PAGER_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_PAGER_SHOW_ALWAYS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		);
		$arComponentParameters["PARAMETERS"]["PAGER_TEMPLATE"] = Array(
			"PARENT" => "PAGER_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_PAGER_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		);
		if($bDescNumbering)
		{
			$arComponentParameters["PARAMETERS"]["PAGER_DESC_NUMBERING"] = Array(
				"PARENT" => "PAGER_SETTINGS",
				"NAME" => GetMessage("T_IBLOCK_DESC_PAGER_DESC_NUMBERING"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "N",
			);
			$arComponentParameters["PARAMETERS"]["PAGER_DESC_NUMBERING_CACHE_TIME"] = Array(
				"PARENT" => "PAGER_SETTINGS",
				"NAME" => GetMessage("T_IBLOCK_DESC_PAGER_DESC_NUMBERING_CACHE_TIME"),
				"TYPE" => "STRING",
				"DEFAULT" => "36000",
			);
		}
	}
}

class CIBlockFormatProperties
{
	function GetDisplayValue($arItem, $arProperty, $event1)
	{
		$arUserTypeFormat = false;
		if(strlen($arProperty["USER_TYPE"])>0)
		{
			$arUserType = CIBlockProperty::GetUserType($arProperty["USER_TYPE"]);
			if(array_key_exists("GetPublicViewHTML", $arUserType))
				$arUserTypeFormat = $arUserType["GetPublicViewHTML"];
		}

		static $CACHE = array("E"=>array(),"G"=>array());
		if($arUserTypeFormat)
		{
			if($arProperty["MULTIPLE"]=="N" || !is_array($arProperty["~VALUE"]))
				$arValues = array($arProperty["~VALUE"]);
			else
				$arValues = $arProperty["~VALUE"];
		}
		else
		{
			if(is_array($arProperty["VALUE"]))
				$arValues = $arProperty["VALUE"];
			else
				$arValues = array($arProperty["VALUE"]);
		}
		$arDisplayValue = array();
		foreach($arValues as $key => $val)
		{
			if($arUserTypeFormat)
			{
				$arDisplayValue[] = call_user_func_array($arUserTypeFormat,
					array(
						$arProperty,
						array("VALUE" => $val),
						array(),
					));
			}
			elseif(($arProperty["PROPERTY_TYPE"] == "E") && (intval($val) > 0))
			{
				if(!array_key_exists($val, $CACHE["E"]))
				{
					//USED TO GET "LINKED" ELEMENTS
					$arLinkFilter = array (
						"ID" => $val,
						"ACTIVE" => "Y",
						"ACTIVE_DATE" => "Y",
						"CHECK_PERMISSIONS" => "Y",
					);
					$rsLink = CIBlockElement::GetList(array(), $arLinkFilter, false, false, array("ID","IBLOCK_ID","NAME","DETAIL_PAGE_URL"));
					$CACHE["E"][$val] = $rsLink->GetNext();
				}
				if(is_array($CACHE["E"][$val]))
					$arDisplayValue[]='<a href="'.htmlspecialchars($CACHE["E"][$val]["DETAIL_PAGE_URL"]).'">'.$CACHE["E"][$val]["NAME"].'</a>';
			}
			elseif(($arProperty["PROPERTY_TYPE"] == "G") && (intval($val) > 0))
			{
				if(!array_key_exists($val, $CACHE["G"]))
				{
					//USED TO GET SECTIONS NAMES
					$arSectionFilter = array (
						"ID" => $val,
					);
					$rsSection = CIBlockSection::GetList(Array(), $arSectionFilter);
					$CACHE["G"][$val] = $rsSection->GetNext();
				}
				if(is_array($CACHE["G"][$val]))
					$arDisplayValue[]='<a href="'.htmlspecialchars($CACHE["G"][$val]["SECTION_PAGE_URL"]).'">'.$CACHE["G"][$val]["NAME"].'</a>';
			}
			elseif($arProperty["PROPERTY_TYPE"]=="L")
			{
				$arDisplayValue[] = $val;
			}
			elseif($arProperty["PROPERTY_TYPE"]=="F")
			{
				if($arFile = CFile::GetFileArray($val))
				{
					if(IsModuleInstalled("statistic"))
						$arDisplayValue[] =  '<a href="'.htmlspecialchars("/bitrix/redirect.php?event1=".urlencode($event1)."&event2=".urlencode($arFile["SRC"])."&event3=".urlencode($arFile["ORIGINAL_NAME"])."&goto=".urlencode($arFile["SRC"])).'">'.GetMessage('IBLOCK_DOWNLOAD').'</a>';
					else
						$arDisplayValue[] =  '<a href="'.htmlspecialchars($arFile["SRC"]).'">'.GetMessage('IBLOCK_DOWNLOAD').'</a>';
				}
			}
			else
			{
				$trimmed = trim($val);
				if(strpos($trimmed, "http")===0)
				{
					if(IsModuleInstalled("statistic"))
						$arDisplayValue[] =  '<a href="'.htmlspecialchars("/bitrix/redirect.php?event1=".urlencode($event1)."&event2=".urlencode($trimmed)."&event3=".urlencode($arItem["NAME"])."&goto=".urlencode($trimmed)).'">'.$trimmed.'</a>';
					else
						$arDisplayValue[] =  '<a href="'.htmlspecialchars($trimmed).'">'.$trimmed.'</a>';
				}
				elseif(strpos($trimmed, "www")===0)
				{
					if(IsModuleInstalled("statistic"))
						$arDisplayValue[] =  '<a href="'.htmlspecialchars("/bitrix/redirect.php?event1=".urlencode($event1)."&event2=".urlencode("http://".$trimmed)."&event3=".urlencode($arItem["NAME"])."&goto=".urlencode("http://".$trimmed)).'">'.$trimmed.'</a>';
					else
						$arDisplayValue[] =  '<a href="'.htmlspecialchars("http://".$val).'">'.$val.'</a>';
				}
				else
					$arDisplayValue[] = $val;
			}
		}
		if(count($arDisplayValue)==1)
			$arProperty["DISPLAY_VALUE"] = $arDisplayValue[0];
		elseif(count($arDisplayValue)>1)
			$arProperty["DISPLAY_VALUE"] = $arDisplayValue;
		else
			$arProperty["DISPLAY_VALUE"] = false;
		return $arProperty;
	}
	function DateFormat($format, $timestamp)
	{
		if(LANG=="en")
			return date($format, $timestamp);
		elseif(preg_match_all("/[FMlD]/", $format, $matches))
		{
			$ar = preg_split("/[FMlD]/", $format);
			$result = "";
			foreach($matches[0] as $i=>$match)
			{
				switch($match)
				{
					case "F":$match=GetMessage("T_IBLOCK_MONTH_".date("n", $timestamp));break;
					case "M":$match=GetMessage("T_IBLOCK_MON_".date("n", $timestamp));break;
					case "l":$match=GetMessage("T_IBLOCK_DAY_OF_WEEK_".date("w", $timestamp));break;
					case "D":$match=GetMessage("T_IBLOCK_DOW_".date("w", $timestamp));break;
				}
				$result .= date($ar[$i], $timestamp).$match;
			}
			$result .= date($ar[count($ar)-1], $timestamp);
			return $result;
		}
		else
			return date($format, $timestamp);
	}
}
?>
