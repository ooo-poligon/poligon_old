<?
IncludeModuleLangFile(__FILE__);

class CIBlockPropertyXmlID
{
	function GetUserTypeDescription()
	{
		return array(
			"PROPERTY_TYPE"		=>"S",
			"USER_TYPE"		=>"ElementXmlID",
			"DESCRIPTION"		=>GetMessage("IBLOCK_PROP_XMLID_DESC"),
			"GetPublicViewHTML"	=>array("CIBlockPropertyXmlID","GetPublicViewHTML"),
			"GetAdminListViewHTML"	=>array("CIBlockPropertyXmlID","GetAdminListViewHTML"),
			"GetPropertyFieldHtml"	=>array("CIBlockPropertyXmlID","GetPropertyFieldHtml"),
			//optional handlers
			//"CheckFields"		=>array("CIBlockPropertyXmlID","CheckFields"),
			//"ConvertToDB"		=>array("CIBlockPropertyXmlID","ConvertToDB"),
			//"ConvertFromDB"	=>array("CIBlockPropertyXmlID","ConvertFromDB"),
		);
	}

	function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
	{
		static $cache = array();
		if(strlen($value["VALUE"])>0)
		{
			if(!array_key_exists($value["VALUE"], $cache))
			{
				$db_res = CIBlockElement::GetList(
					array(),
					array("=XML_ID"=>$value["VALUE"], "SHOW_HISTORY"=>"Y"),
					false,
					false,
					array("ID", "IBLOCK_TYPE_ID", "IBLOCK_ID", "NAME", "DETAIL_PAGE_URL")
				);
				$ar_res = $db_res->GetNext();
				if($ar_res)
					$cache[$value["VALUE"]] = '<a href="'.htmlspecialchars($ar_res["DETAIL_PAGE_URL"]).'">'.$ar_res["NAME"].'</a>';
				else
					$cache[$value["VALUE"]] = htmlspecialchars($value["VALUE"]);
			}
			return $cache[$value["VALUE"]];
		}
		else
		{
			return '';
		}
	}

	function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
	{
		static $cache = array();
		if(strlen($value["VALUE"])>0)
		{
			if(!array_key_exists($value["VALUE"], $cache))
			{
				$db_res = CIBlockElement::GetList(
					array(),
					array("=XML_ID"=>$value["VALUE"], "SHOW_HISTORY"=>"Y"),
					false,
					false,
					array("ID", "IBLOCK_TYPE_ID", "IBLOCK_ID", "NAME")
				);
				$ar_res = $db_res->GetNext();
				if($ar_res)
					$cache[$value["VALUE"]] = htmlspecialchars($ar_res['NAME']).
					' [<a href="'.
					'/bitrix/admin/iblock_element_edit.php?'.
					'type='.urlencode($ar_res['IBLOCK_TYPE_ID']).
					'&amp;IBLOCK_ID='.$ar_res['IBLOCK_ID'].
					'&amp;ID='.$ar_res['ID'].
					'&amp;lang='.LANGUAGE_ID.
					'" title="'.GetMessage("IBEL_A_EL_EDIT").'">'.$ar_res['ID'].'</a>]';
				else
					$cache[$value["VALUE"]] = htmlspecialchars($value["VALUE"]);
			}
			return $cache[$value["VALUE"]];
		}
		else
		{
			return '&nbsp;';
		}
	}

	//PARAMETERS:
	//$arProperty - b_iblock_property.*
	//$value - array("VALUE","DESCRIPTION") -- here comes HTML form value
	//strHTMLControlName - array("VALUE","DESCRIPTION")
	//return:
	//safe html
	function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
	{
		$ar_res = false;
		if(strlen($value["VALUE"]))
		{
			$db_res = CIBlockElement::GetList(
				array(),
				array("=XML_ID"=>$value["VALUE"], "SHOW_HISTORY"=>"Y"),
				false,
				false,
				array("ID", "IBLOCK_ID", "NAME")
			);
			$ar_res = $db_res->GetNext();
		}

		if(!$ar_res)
			$ar_res = array("NAME" => "");

		return  '<input name="'.htmlspecialchars($strHTMLControlName["VALUE"]).'" id="'.htmlspecialchars($strHTMLControlName["VALUE"]).'" value="'.htmlspecialcharsex($value["VALUE"]).'" size="20" type="text">'.
			'<input type="button" value="..." onClick="jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang='.LANG.'&amp;n='.htmlspecialchars($strHTMLControlName["VALUE"]).'&amp;get_xml_id=Y\', 600, 500);">'.
			'&nbsp;<span id="sp_'.htmlspecialchars($strHTMLControlName["VALUE"]).'" >'.$ar_res['NAME'].'</span>';
	}
}

AddEventHandler("iblock", "OnIBlockPropertyBuildList", array("CIBlockPropertyXmlID", "GetUserTypeDescription"));
?>
