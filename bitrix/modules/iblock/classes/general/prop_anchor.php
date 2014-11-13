<?
IncludeModuleLangFile(__FILE__);

class CIBlockPropertyAnchor
{
	function GetUserTypeDescription()
	{
		return array(
			"PROPERTY_TYPE"		=>"S",
			"USER_TYPE"		=>"Anchor",
			"DESCRIPTION"		=>GetMessage("IBLOCK_PROP_ANCHOR_DESC"),
			"GetPropertyFieldHtml"	=>array("CIBlockPropertyAnchor","GetPropertyFieldHtml"),
			"ConvertToDB"		=>array("CIBlockPropertyAnchor","ConvertToDB"),
			"ConvertFromDB"		=>array("CIBlockPropertyAnchor","ConvertFromDB"),
		);
	}
	
	function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
	{
		global $APPLICATION;
		$value_original = $value;
		$Error = false;
		if (strToUpper($strHTMLControlName["MODE"]) == "EDIT_FORM")
		{
			?>
			<select id='type'>
				<option value='t1'><?=GetMessage("IBLOCK_PROP_LINK_TYPE1")?></option>
				<option value='t2'><?=GetMessage("IBLOCK_PROP_LINK_TYPE2")?></option>
				<option value='t3'><?=GetMessage("IBLOCK_PROP_LINK_TYPE3")?></option>
				<option value='t4'><?=GetMessage("IBLOCK_PROP_LINK_TYPE4")?></option>
			</select>&nbsp;<input type="button" name="view_content" value="..."><br><br>
			<input type="checkbox" name="show_type" value="in_body">&nbsp;<?=GetMessage("IBLOCK_PROP_SHOW_TYPE")?>
			<?
		}
		else 
		{
		}
		$return = ob_get_contents();
		ob_end_clean();
		return  $return;
	}
	
	function ConvertToDB($arProperty, $value)
	{
		$result = array();
		$control = array();
		if (is_array($value["VALUE"]))
		{
			foreach ($value["VALUE"] as $key => $val)
			{
				$key = strToUpper(trim($key));
				if (subStr($key, 0, strLen("FIELDNAME")) == "FIELDNAME")
				{
					$control[intVal(subStr($key, strLen("FIELDNAME")))]["FIELDNAME"] = trim($val);
				}
				elseif (subStr($key, 0, strLen("VALUE")) == "VALUE")
				{
					$control[intVal(subStr($key, strLen("VALUE")))]["VALUE"] = trim($val);
				}
			}
			foreach ($control as $key => $val)
			{
				if (strLen($val["FIELDNAME"]) > 0)
					$result[$val["FIELDNAME"]] = $val["VALUE"];
			}
			if ((count($result) <= 0) && is_set($value["VALUE"], "READY"))
			{
				$result = $value["VALUE"]["READY"];
			}
		}
		else 
		{
			$result = unserialize($value["VALUE"]);
			if (!is_array($result))
				$result = false;
		}
		return array("VALUE" => serialize($result));
	}

	function ConvertFromDB($arProperty, $value)
	{
		$control = array();
		$result = array();
		$temp = array();
		if (!is_array($value["VALUE"]))
		{
			$temp = unserialize($value["VALUE"]);
		}
		else 
		{
			foreach ($value["VALUE"] as $key => $val)
			{
				$key = strToUpper(trim($key));
				if (subStr($key, 0, strLen("FIELDNAME")) == "FIELDNAME")
				{
					$control[intVal(subStr($key, strLen("FIELDNAME")))]["FIELDNAME"] = trim($val);
				}
				elseif (subStr($key, 0, strLen("VALUE")) == "VALUE")
				{
					$control[intVal(subStr($key, strLen("VALUE")))]["VALUE"] = trim($val);
				}
			}
			foreach ($control as $key => $val)
			{
				if (strLen($val["FIELDNAME"]) > 0)
					$temp[$val["FIELDNAME"]] = $val["VALUE"];
			}
		}
		if (is_array($temp) && (count($temp) > 0))
		{
			foreach ($temp as $key => $val)
			{
				if (strLen(trim($key)) <= 0)
					continue;
				$result[$key] = $val;
			}
		}
		return array("VALUE" => array("READY" => $result));
	}
}

//AddEventHandler("iblock", "OnIBlockPropertyBuildList", array("CIBlockPropertyAnchor", "GetUserTypeDescription"));
?>
