<?
IncludeModuleLangFile(__FILE__);

class CIBlockPropertyDateTime
{
	function GetUserTypeDescription()
	{
		return array(
			"PROPERTY_TYPE"		=>"S",
			"USER_TYPE"		=>"DateTime",
			"DESCRIPTION"		=>GetMessage("IBLOCK_PROP_DATETIME_DESC"),
			"GetPublicViewHTML"	=>array("CIBlockPropertyDateTime","GetPublicViewHTML"),
			"GetAdminListViewHTML"	=>array("CIBlockPropertyDateTime","GetAdminListViewHTML"),
			"GetPropertyFieldHtml"	=>array("CIBlockPropertyDateTime","GetPropertyFieldHtml"),
			//optional handlers
			"CheckFields"		=>array("CIBlockPropertyDateTime","CheckFields"),
			"ConvertToDB"		=>array("CIBlockPropertyDateTime","ConvertToDB"),
			"ConvertFromDB"		=>array("CIBlockPropertyDateTime","ConvertFromDB"),
		);
	}

	function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
	{
		if(strlen($value["VALUE"])>0)
		{
			if(!CheckDateTime($value["VALUE"]))
				$value = CIBlockPropertyDateTime::ConvertFromDB($arProperty, $value);
			return str_replace(" ", "&nbsp;", htmlspecialcharsex($value["VALUE"]));
		}
		else
			return '';
	}

	function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
	{
		if(strlen($value["VALUE"])>0)
		{
			if(!CheckDateTime($value["VALUE"]))
				$value = CIBlockPropertyDateTime::ConvertFromDB($arProperty, $value);
			return str_replace(" ", "&nbsp;", htmlspecialcharsex($value["VALUE"]));
		}
		else
			return '&nbsp;';
	}

	//PARAMETERS:
	//$arProperty - b_iblock_property.*
	//$value - array("VALUE","DESCRIPTION") -- here comes HTML form value
	//strHTMLControlName - array("VALUE","DESCRIPTION")
	//return:
	//safe html
	function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
	{
		return  CAdminCalendar::CalendarDate($strHTMLControlName["VALUE"], $value["VALUE"], 20).
			($arProperty["WITH_DESCRIPTION"]=="Y"?
				'&nbsp;<input type="text" size="20" name="'.$strHTMLControlName["DESCRIPTION"].'" value="'.htmlspecialchars($value["DESCRIPTION"]).'">'
				:''
			);
	}

	//PARAMETERS:
	//$arProperty - b_iblock_property.*
	//$value - array("VALUE",["DESCRIPTION"]) -- here comes HTML form value
	//return:
	//array of error messages
	function CheckFields($arProperty, $value)
	{
		$arResult = array();
		if(strlen($value["VALUE"])>0 && !CheckDateTime($value["VALUE"]))
			$arResult[] = GetMessage("IBLOCK_PROP_DATETIME_ERROR");
		return $arResult;
	}

	//PARAMETERS:
	//$arProperty - b_iblock_property.*
	//$value - array("VALUE",["DESCRIPTION"]) -- here comes HTML form value
	//return:
	//DB form of the value
	function ConvertToDB($arProperty, $value)
	{
		if(strlen($value["VALUE"])>0)
			$value["VALUE"] = CDatabase::FormatDate($value["VALUE"], CLang::GetDateFormat("FULL"), "YYYY-MM-DD HH:MI:SS");
		return $value;
	}

	function ConvertFromDB($arProperty, $value)
	{
		if(strlen($value["VALUE"])>0)
		{
			$value["VALUE"] = CDatabase::FormatDate($value["VALUE"], "YYYY-MM-DD HH:MI:SS", CLang::GetDateFormat("FULL"));
			$value["VALUE"] = str_replace(" 00:00:00", "", $value["VALUE"]);
		}
		return $value;
	}
}

AddEventHandler("iblock", "OnIBlockPropertyBuildList", array("CIBlockPropertyDateTime", "GetUserTypeDescription"));
?>
