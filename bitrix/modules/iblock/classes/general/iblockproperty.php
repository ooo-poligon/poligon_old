<?
global $IBLOCK_CACHE_PROPERTY;
$IBLOCK_CACHE_PROPERTY = Array();
IncludeModuleLangFile(__FILE__);

class CAllIBlockProperty
{
	///////////////////////////////////////////////////////////////////
	// Delete by property ID
	///////////////////////////////////////////////////////////////////
	function Delete($ID)
	{
		global $DB, $APPLICATION;
		$ID = IntVal($ID);

		$APPLICATION->ResetException();
		$db_events = GetModuleEvents("iblock", "OnBeforeIBlockPropertyDelete");
		while($arEvent = $db_events->Fetch())
			if(ExecuteModuleEvent($arEvent, $ID)===false)
			{
				$err = GetMessage("MAIN_BEFORE_DEL_ERR").' '.$arEvent['TO_NAME'];
				if($ex = $APPLICATION->GetException())
					$err .= ': '.$ex->GetString();
				$APPLICATION->throwException($err);
				return false;
			}

		$events = GetModuleEvents("iblock", "OnIBlockPropertyDelete");
		while($arEvent = $events->Fetch())
			ExecuteModuleEvent($arEvent, $ID);

		if(!CIBlockPropertyEnum::DeleteByPropertyID($ID, true))
			return false;

		$rsProperty = CIBlockProperty::GetByID($ID);
		$arProperty = $rsProperty->Fetch();
		if($arProperty["VERSION"] == 2)
		{
			if($arProperty["PROPERTY_TYPE"]=="F")
			{
				if($arProperty["MULTIPLE"]=="Y")
				{
					$strSql = "
						SELECT	VALUE
						FROM	b_iblock_element_prop_m".$arProperty["IBLOCK_ID"]."
						WHERE	IBLOCK_PROPERTY_ID=".$ID."
					";
				}
				else
				{
					$strSql = "
						SELECT	PROPERTY_".$ID." VALUE
						FROM	b_iblock_element_prop_s".$arProperty["IBLOCK_ID"]."
						WHERE	PROPERTY_".$ID." is not null
					";
				}
				$res = $DB->Query($strSql);
				while($arr = $res->Fetch())
					CFile::Delete($arr["VALUE"]);
			}
			if(!$DB->Query("DELETE FROM b_iblock_section_element WHERE ADDITIONAL_PROPERTY_ID=".IntVal($ID), true))
				return false;
			$strSql = "
				DELETE
				FROM	b_iblock_element_prop_m".$arProperty["IBLOCK_ID"]."
				WHERE	IBLOCK_PROPERTY_ID=".$ID."
			";
			if(!$DB->Query($strSql))
				return false;
			$arSql = CIBlockProperty::DropColumnSQL("b_iblock_element_prop_s".$arProperty["IBLOCK_ID"], array("PROPERTY_".$ID,"DESCRIPTION_".$ID));
			foreach($arSql as $strSql)
				if(!$DB->Query($strSql))
					return false;
		}
		else
		{
			$res = $DB->Query("SELECT EP.VALUE FROM b_iblock_property P, b_iblock_element_property EP WHERE P.ID=".$ID." AND P.ID=EP.IBLOCK_PROPERTY_ID AND P.PROPERTY_TYPE='F'");
			while($arr = $res->Fetch())
				CFile::Delete($arr["VALUE"]);
			if(!$DB->Query("DELETE FROM b_iblock_section_element WHERE ADDITIONAL_PROPERTY_ID=".IntVal($ID), true))
				return false;
			if(!$DB->Query("DELETE FROM b_iblock_element_property WHERE IBLOCK_PROPERTY_ID=".IntVal($ID), true))
				return false;
		}
		return $DB->Query("DELETE FROM b_iblock_property WHERE ID=".IntVal($ID), true);
	}
	///////////////////////////////////////////////////////////////////
	// Update
	///////////////////////////////////////////////////////////////////
	function Add($arFields)
	{
		global $DB;
		if(is_set($arFields, "ACTIVE") && $arFields["ACTIVE"]!="Y")
			$arFields["ACTIVE"]="N";
		if($arFields["SEARCHABLE"]!="Y")
			$arFields["SEARCHABLE"]="N";
		if($arFields["FILTRABLE"]!="Y")
			$arFields["FILTRABLE"]="N";
		if(is_set($arFields, "MULTIPLE") && $arFields["MULTIPLE"]!="Y")
			$arFields["MULTIPLE"]="N";
		if(is_set($arFields, "LIST_TYPE") && $arFields["LIST_TYPE"]!="C")
			$arFields["LIST_TYPE"]="L";

		if(!$this->CheckFields(&$arFields))
		{
			$Result = false;
			$arFields["RESULT_MESSAGE"] = &$this->LAST_ERROR;
		}
		else
		{
			$arFields["VERSION"] = CIBlockElement::GetIBVersion($arFields["IBLOCK_ID"]);
			unset($arFields["ID"]);
			if($arFields["USER_TYPE"]!="")
			{
				$arUserType = CIBlockProperty::GetUserType($arFields["USER_TYPE"]);
				if(array_key_exists("ConvertToDB", $arUserType))
				{
					$arValue=array("VALUE"=>$arFields["DEFAULT_VALUE"]);
					$arValue = call_user_func_array($arUserType["ConvertToDB"], array($arFields, $arValue));
					$arFields["DEFAULT_VALUE"] = $arValue["VALUE"];
				}
			}
			$ID = $DB->Add("b_iblock_property", $arFields, Array(), "iblock");

			if(is_set($arFields, "VALUES"))
				$this->UpdateEnum($ID, $arFields["VALUES"]);

			if($arFields["VERSION"]==2)
			{
			 	if($this->_Add($ID, $arFields))
				{
					$Result = $ID;
					$arFields["ID"] = &$ID;
				}
				else
				{
					$this->LAST_ERROR = GetMessage("IBLOCK_PROPERTY_ADD_ERROR",array("#ID#"=>$ID,"#CODE#"=>"14"));
					$Result = false;
					$arFields["RESULT_MESSAGE"] = &$this->LAST_ERROR;
				}
			}
			else
			{
				$Result = $ID;
				$arFields["ID"] = &$ID;
			}
		}

		$arFields["RESULT"] = &$Result;

		$events = GetModuleEvents("iblock", "OnAfterIBlockPropertyAdd");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEvent($arEvent, &$arFields);

		return $Result;
	}
	///////////////////////////////////////////////////////////////////
	// This one called before any Update or Add
	///////////////////////////////////////////////////////////////////
	function CheckFields($arFields, $ID=false)
	{
		global $DB, $APPLICATION;
		$this->LAST_ERROR = "";
		if(strlen($arFields["NAME"])<=0)
			$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_SECTION_NAME")."<br>";

		if(is_set($arFields, "CODE") && ereg("[0-9]", substr($arFields["CODE"], 0, 1)))
			$this->LAST_ERROR .= GetMessage("IBLOCK_CODE_FIRST_LETTER")."<br>";

		if($ID===false && !is_set($arFields, "IBLOCK_ID"))
			$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_BLOCK_ID")."<br>";

		if(is_set($arFields, "IBLOCK_ID"))
		{
			$r = CIBlock::GetByID($arFields["IBLOCK_ID"]);
			if(!$r->Fetch())
				$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_BLOCK_ID")."<br>";
		}

		$arUserType = CIBlockProperty::GetUserType($arFields["USER_TYPE"]);
		if(array_key_exists("CheckFields", $arUserType))
		{
			$value=array("VALUE"=>$arFields["DEFAULT_VALUE"]);
			$arError = call_user_func_array($arUserType["CheckFields"],array($arFields,$value));
			if(is_array($arError) && count($arError)>0)
				$this->LAST_ERROR .= implode("<br>", $arError)."<br>";
		}

		$APPLICATION->ResetException();
		if($ID===false)
			$db_events = GetModuleEvents("iblock", "OnBeforeIBlockPropertyAdd");
		else
		{
			$arFields["ID"] = $ID;
			$db_events = GetModuleEvents("iblock", "OnBeforeIBlockPropertyUpdate");
		}

		while($arEvent = $db_events->Fetch())
		{
			$bEventRes = ExecuteModuleEvent($arEvent, &$arFields);
			if($bEventRes===false)
			{
				if($err = $APPLICATION->GetException())
					$this->LAST_ERROR .= $err->GetString()."<br>";
				else
				{
					$APPLICATION->ThrowException("Unknown error");
					$this->LAST_ERROR .= "Unknown error.<br>";
				}
				break;
			}
		}

		if(strlen($this->LAST_ERROR)>0)
			return false;

		return true;
	}

	///////////////////////////////////////////////////////////////////
	// Update method
	///////////////////////////////////////////////////////////////////
	function Update($ID, $arFields)
	{
		global $DB;

		if(is_set($arFields, "ACTIVE") && $arFields["ACTIVE"]!="Y")
			$arFields["ACTIVE"]="N";
		if(is_set($arFields, "SEARCHABLE") && $arFields["SEARCHABLE"]!="Y")
			$arFields["SEARCHABLE"]="N";
		if(is_set($arFields, "FILTRABLE") && $arFields["FILTRABLE"]!="Y")
			$arFields["FILTRABLE"]="N";
		if(is_set($arFields, "MULTIPLE") && $arFields["MULTIPLE"]!="Y")
			$arFields["MULTIPLE"]="N";
		if(is_set($arFields, "LIST_TYPE") && $arFields["LIST_TYPE"]!="C")
			$arFields["LIST_TYPE"]="L";

		if(!$this->CheckFields(&$arFields, $ID))
		{
			$Result = false;
			$arFields["RESULT_MESSAGE"] = &$this->LAST_ERROR;
		}
		elseif(!$this->_Update($ID, $arFields))
		{
			$Result = false;
			$arFields["RESULT_MESSAGE"] = &$this->LAST_ERROR;
		}
		else
		{
			if($arFields["USER_TYPE"]!="")
			{
				$arUserType = CIBlockProperty::GetUserType($arFields["USER_TYPE"]);
				if(array_key_exists("ConvertToDB", $arUserType))
				{
					$arValue=array("VALUE"=>$arFields["DEFAULT_VALUE"]);
					$arValue = call_user_func_array($arUserType["ConvertToDB"], array($arFields, $arValue));
					$arFields["DEFAULT_VALUE"] = $arValue["VALUE"];
				}
			}

			unset($arFields["ID"]);
			unset($arFields["VERSION"]);
			unset($arFields["TIMESTAMP_X"]);

			$strUpdate = $DB->PrepareUpdate("b_iblock_property", $arFields);
			if(strlen($strUpdate) > 0)
			{
				$strSql = "UPDATE b_iblock_property SET ".$strUpdate." WHERE ID=".$ID;
				$DB->Query($strSql);
			}

			if(is_set($arFields, "VALUES"))
				$this->UpdateEnum($ID, $arFields["VALUES"]);

			global $BX_IBLOCK_PROP_CACHE;
			if(is_set($arFields, "IBLOCK_ID"))
				UnSet($BX_IBLOCK_PROP_CACHE[$arFields["IBLOCK_ID"]]);
			else
				$BX_IBLOCK_PROP_CACHE = Array();

			$Result = true;
		}

		$arFields["ID"] = $ID;
		$arFields["RESULT"] = &$Result;

		$events = GetModuleEvents("iblock", "OnAfterIBlockPropertyUpdate");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEvent($arEvent, &$arFields);

		return $Result;
	}


	///////////////////////////////////////////////////////////////////
	// Get property information by ID
	///////////////////////////////////////////////////////////////////
	function GetByID($ID, $IBLOCK_ID=false, $IBLOCK_CODE=false)
	{
		global $DB;
		$cond = "";
		if($IBLOCK_CODE && $IBLOCK_ID)
			$cond = " AND (B.ID IN (".IntVal($IBLOCK_ID).") OR B.CODE IN ('".$DB->ForSql($IBLOCK_CODE)."')) ";
		elseif($IBLOCK_CODE)
			$cond = " AND B.CODE IN ('".$DB->ForSql($IBLOCK_CODE)."') ";
		elseif($IBLOCK_ID)
			$cond = " AND B.ID IN (".IntVal($IBLOCK_ID).") ";

		$strSql =
			"SELECT BP.* ".
			"FROM b_iblock_property BP, b_iblock B ".
			"WHERE BP.IBLOCK_ID=B.ID ".
			$cond.
			(is_numeric(substr($ID, 0, 1))
			?
				"	AND BP.ID=".IntVal($ID)
			:
				"	AND UPPER(BP.CODE)=UPPER('".$DB->ForSql($ID)."') "
			);

		$res = new CIBlockPropertyResult($DB->Query($strSql));
		return $res;
	}

	function GetPropertyArray($ID, $IBLOCK_ID, $bCached=true)
	{
		global $DB;

		$block_id = false;
		$block_code = false;
		if(is_array($IBLOCK_ID))
		{
			foreach($IBLOCK_ID as $k=>$v)
			{
				if(is_numeric($v))
				{
					if($block_id)
						$block_id .= ", ";
					else
						$block_id = "";

					$block_id .= IntVal($v);
				}
				elseif(strlen($v)>0)
				{
					if($block_code)
						$block_code .= ", ";
					else
						$block_code = "";

					$block_code .= "'".$DB->ForSQL($v, 200)."'";
				}
			}
		}
		elseif(is_numeric($IBLOCK_ID))
			$block_id = IntVal($IBLOCK_ID);
		elseif(strlen($IBLOCK_ID)>0)
			$block_code = "'".$DB->ForSQL($IBLOCK_ID, 200)."'";

		global $IBLOCK_CACHE_PROPERTY;
		if($bCached && is_set($IBLOCK_CACHE_PROPERTY, $ID."|".$block_id."|".$block_code))
			return $IBLOCK_CACHE_PROPERTY[$ID."|".$block_id."|".$block_code];

		if($block_code && $block_id)
			$cond = " AND (B.ID IN (".$block_id.") OR B.CODE IN (".$block_code.")) ";
		elseif($block_code)
			$cond = " AND B.CODE IN (".$block_code.") ";
		elseif($block_id)
			$cond = " AND B.ID IN (".$block_id.") ";

		$strSql =
			"SELECT BP.* ".
			"FROM b_iblock_property BP, b_iblock B ".
			"WHERE BP.IBLOCK_ID=B.ID ".
			$cond.
			(substr(strtoupper($ID), -6)=='_VALUE'?
				(is_numeric(substr($ID, 0, 1))?
					"	AND BP.ID=".IntVal($ID)
				:
					"	AND ((UPPER(BP.CODE)=UPPER('".$DB->ForSql($ID)."') AND BP.PROPERTY_TYPE!='L') OR (UPPER(BP.CODE)=UPPER('".$DB->ForSql(substr($ID, 0, strlen($ID)-6))."') AND BP.PROPERTY_TYPE='L'))"
				)
			:
				(is_numeric(substr($ID, 0, 1))?
					"	AND BP.ID=".IntVal($ID)
				:
					"	AND UPPER(BP.CODE)=UPPER('".$DB->ForSql($ID)."') "
				)
			);

		$res = $DB->Query($strSql);
		if($arr = $res->Fetch())
		{
			$arr["ORIG_ID"]=$arr["ID"];	//it saves original (digital) id
			$arr["IS_CODE_UNIQUE"]=true;	//boolean check for global code uniquess
			$arr["IS_VERSION_MIXED"]=false;	//boolean check if varios versions of ibformation block properties
			while($arr2 = $res->Fetch())
			{
				$arr["IS_CODE_UNIQUE"]=false;
				$arr["IS_VERSION_MIXED"]=$arr["VERSION"]!=$arr2["VERSION"];
			}
			if(substr(strtoupper($ID), -6)=='_VALUE' && $arr["PROPERTY_TYPE"]=="L" && strtoupper($arr["CODE"])==strtoupper(substr($ID, 0, strlen($ID)-6)))
				$arr["ID"] = substr($ID, 0, strlen($ID)-6);
			else
				$arr["ID"] = $ID;
		}

		$IBLOCK_CACHE_PROPERTY[$ID."|".$block_id."|".$block_code] = $arr;
		return $arr;
	}

	function GetPropertyEnum($PROP_ID, $arOrder = Array("SORT"=>"asc"), $arFilter = Array())
	{
		global $DB;

		$arSqlSearch = Array();
		$filter_keys = array_keys($arFilter);
		for($i=0; $i<count($filter_keys); $i++)
		{
			$val = $DB->ForSql($arFilter[$filter_keys[$i]]);
			switch(strtoupper($filter_keys[$i]))
			{
			case "ID":
				$arSqlSearch[] = "BPE.ID=".IntVal($val);
				break;
			case "IBLOCK_ID":
				$arSqlSearch[] = "BP.IBLOCK_ID=".IntVal($val);
				break;
			case "VALUE":
				$arSqlSearch[] = "BPE.VALUE LIKE '".$val."'";
				break;
			case "EXTERNAL_ID": case "XML_ID":
				$arSqlSearch[] = "BPE.XML_ID LIKE '".$val."'";
				break;
			}
		}


		$strSqlSearch = "";
		for($i=0; $i<count($arSqlSearch); $i++)
			$strSqlSearch .= " AND (".$arSqlSearch[$i].") ";

		$arSqlOrder = Array();
		foreach($arOrder as $by=>$order)
		{
			$by = strtolower($by);
			$order = strtolower($order);
			if ($order!="asc") $order = "desc";

			if ($by == "value")		$arSqlOrder[] = " BPE.VALUE ".$order." ";
			elseif ($by == "id")	$arSqlOrder[] = " BPE.ID ".$order." ";
			elseif ($by == "external_id")	$arSqlOrder[] = " BPE.XML_ID ".$order." ";
			else
			{
				$arSqlOrder[] = " BPE.SORT ".$order." ";
				$by = "sort";
			}
		}

		$strSqlOrder = "";
		DelDuplicateSort($arSqlOrder); for ($i=0; $i<count($arSqlOrder); $i++)
		{
			if($i==0)
				$strSqlOrder = " ORDER BY ";
			else
				$strSqlOrder .= ",";

			$strSqlOrder .= $arSqlOrder[$i];
		}

		$strSql =
			"SELECT BPE.*, BPE.XML_ID as EXTERNAL_ID ".
			"FROM b_iblock_property_enum BPE, b_iblock_property BP ".
			"WHERE BPE.PROPERTY_ID=BP.ID ".
			(is_numeric(substr($PROP_ID, 0, 1))
			?
				"	AND BP.ID=".IntVal($PROP_ID)
			:
				"	AND BP.CODE='".$DB->ForSql($PROP_ID)."' "
			)." ".
			$strSqlSearch.
			$strSqlOrder;

		$res = $DB->Query($strSql);

		return $res;
	}

	function UpdateEnum($ID, $arVALUES)
	{
		global $DB;
		$ID = IntVal($ID);
		if(CACHED_b_iblock_property_enum!==false) $GLOBALS["CACHE_MANAGER"]->CleanDir("b_iblock_property_enum");

		if(!is_array($arVALUES) || count($arVALUES)<1)
		{
			CIBlockPropertyEnum::DeleteByPropertyID($ID);
			return true;
		}

		$db_res = $this->GetPropertyEnum($ID);
		while($res = $db_res->Fetch())
		{
			$VALUE = $arVALUES[$res["ID"]];
			$VAL = (is_array($VALUE)?$VALUE["VALUE"]:$VALUE);
			UnSet($arVALUES[$res["ID"]]);
			if(strlen($VAL)<=0)
			{
				$strSql =
					"DELETE FROM b_iblock_property_enum ".
					"WHERE ID=".$res["ID"];
			}
			else
			{
				$strSql =
					"UPDATE b_iblock_property_enum SET ".
					(is_set($VALUE, "DEF") ? " DEF = '".($VALUE["DEF"]=="Y"?"Y":"N")."', ":"").
					(is_set($VALUE, "SORT") && IntVal($VALUE["SORT"])>0 ? " SORT = '".IntVal($VALUE["SORT"])."', ":"").
					(is_set($VALUE, "XML_ID") ? " XML_ID = '".$DB->ForSQL($VALUE["XML_ID"], 200)."',":"").
					"	VALUE='".$DB->ForSQL($VAL, 255)."' ".
					"WHERE ID=".$res["ID"];
			}

			$DB->Query($strSql);
		}

		foreach($arVALUES as $id=>$VALUE)
		{
			$VAL = (is_array($VALUE)?$VALUE["VALUE"]:$VALUE);
			if(strlen($id)>0 && strlen($VAL)>0)
			{
				$strSql =
					"INSERT INTO b_iblock_property_enum(PROPERTY_ID, ".(is_set($VALUE, "DEF")?"DEF, ":"")."".(is_set($VALUE, "SORT") && IntVal($VALUE["SORT"])>0 ?"SORT, ":"")."VALUE, XML_ID) ".
					"VALUES(".$ID.", ".(is_set($VALUE, "DEF") ? " '".($VALUE["DEF"]=="Y"?"Y":"N")."', ":"")."".
					(is_set($VALUE, "SORT") && IntVal($VALUE["SORT"]) ? " ".IntVal($VALUE["SORT"]).", ":"")." '".
					$DB->ForSQL($VAL, 255)."', '".(is_set($VALUE["XML_ID"]) && strlen($VALUE["XML_ID"])>0 ? $DB->ForSQL($VALUE["XML_ID"], 200) : md5(uniqid("")))."')";
				$DB->Query($strSql);
			}
		}
	}

	function GetUserType($USER_TYPE=false)
	{
		static $CACHE=false;
		if(!is_array($CACHE))
		{
			$CACHE = array();
			$db_events = GetModuleEvents("iblock", "OnIBlockPropertyBuildList");
			while($arEvent = $db_events->Fetch())
			{
				$res = ExecuteModuleEvent($arEvent);
				$CACHE[$res["USER_TYPE"]] = $res;
			}
		}
		if($USER_TYPE!==false)
		{
			if(array_key_exists($USER_TYPE, $CACHE))
				return $CACHE[$USER_TYPE];
			else
				return array();
		}
		else
			return $CACHE;
	}
}

global $BX_IBLOCK_ENUM_CACHE;
$BX_IBLOCK_ENUM_CACHE=array();

class CAllIBlockPropertyEnum
{
	function GetList($arOrder=Array("SORT"=>"ASC", "VALUE"=>"ASC"), $arFilter=Array())
	{
		global $DB;
		$arSqlSearch = Array();
		$filter_keys = array_keys($arFilter);
		for($i=0; $i<count($filter_keys); $i++)
		{
			$val = $arFilter[$filter_keys[$i]];
			$key = $filter_keys[$i];
			if($key[0]=="!")
			{
				$key = substr($key, 1);
				$bInvert = true;
			}
			else
				$bInvert = false;

			$key = strtoupper($key);
			switch($key)
			{
			case "CODE":
				$arSqlSearch[] = CIBlock::FilterCreate("P.CODE", $val, "string", $bInvert);
				break;
			case "IBLOCK_ID":
				$arSqlSearch[] = CIBlock::FilterCreate("P.IBLOCK_ID", $val, "number", $bInvert);
				break;
			case "DEF":
				$arSqlSearch[] = CIBlock::FilterCreate("BEN.DEF", $val, "string_equal", $bInvert);
				break;
			case "EXTERNAL_ID":
				$arSqlSearch[] = CIBlock::FilterCreate("BEN.XML_ID", $val, "string_equal", $bInvert);
				break;
			case "VALUE": case "XML_ID": case "TMP_ID":
				$arSqlSearch[] = CIBlock::FilterCreate("BEN.".$key, $val, "string", $bInvert);
				break;
			case "PROPERTY_ID":
				if(is_numeric(substr($val, 0, 1)))
					$arSqlSearch[] = CIBlock::FilterCreate("P.ID", $val, "number", $bInvert);
				else
					$arSqlSearch[] = CIBlock::FilterCreate("P.CODE", $val, "string", $bInvert);
				break;
			case "ID": case "SORT":
				$arSqlSearch[] = CIBlock::FilterCreate("BEN.".$key, $val, "number", $bInvert);
				break;
			}
		}

		$strSqlSearch = "";
		for($i=0; $i<count($arSqlSearch); $i++)
			if(strlen($arSqlSearch[$i])>0)
				$strSqlSearch .= " AND  (".$arSqlSearch[$i].") ";

		$arSqlOrder = Array();
		foreach($arOrder as $by=>$order)
		{
			$by = strtolower($by);
			$order = strtolower($order);
			if ($order!="asc")
				$order = "desc";

			if ($by == "id")				$arSqlOrder[] = " BEN.ID ".$order." ";
			elseif ($by == "property_id")	$arSqlOrder[] = " BEN.PROPERTY_ID ".$order." ";
			elseif ($by == "value")			$arSqlOrder[] = " BEN.VALUE ".$order." ";
			elseif ($by == "xml_id")		$arSqlOrder[] = " BEN.XML_ID ".$order." ";
			elseif ($by == "external_id")	$arSqlOrder[] = " BEN.XML_ID ".$order." ";
			elseif ($by == "property_sort")	$arSqlOrder[] = " BP.SORT ".$order." ";
			elseif ($by == "property_code")	$arSqlOrder[] = " BP.CODE ".$order." ";
			elseif ($by == "def")			$arSqlOrder[] = " BEN.DEF ".$order." ";
			else
			{
				$arSqlOrder[] = "  BEN.SORT ".$order." ";
				$by = "soft";
			}
		}

		$arSqlOrder = array_unique($arSqlOrder);
		$strSqlOrder = "";
		DelDuplicateSort($arSqlOrder); for ($i=0; $i<count($arSqlOrder); $i++)
		{
			if($i==0)
				$strSqlOrder = " ORDER BY ";
			else
				$strSqlOrder .= ",";

			$strSqlOrder .= $arSqlOrder[$i];
		}

		$strSql = "
			SELECT
				BEN.*,
				BEN.XML_ID as EXTERNAL_ID,
				P.NAME as PROPERTY_NAME,
				P.CODE as PROPERTY_CODE,
				P.SORT as PROPERTY_SORT
			FROM
				b_iblock_property_enum BEN,
				b_iblock_property P
			WHERE
				BEN.PROPERTY_ID=P.ID
			$strSqlSearch
			$strSqlOrder
			";
		//echo "<pre>".htmlspecialchars($strSql)."</pre>";
		$rs = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		return $rs;
	}

	function Add($arFields)
	{
		global $DB;
		if(CACHED_b_iblock_property_enum!==false) $GLOBALS["CACHE_MANAGER"]->CleanDir("b_iblock_property_enum");

		if(is_set($arFields, "DEF") && $arFields["DEF"]!="Y")
			$arFields["DEF"]="N";

		if(is_set($arFields, "EXTERNAL_ID"))
			$arFields["XML_ID"] = $arFields["EXTERNAL_ID"];

		if(!is_set($arFields, "XML_ID"))
			$arFields["XML_ID"] = md5(uniqid(""));

		if(strlen($arFields["VALUE"])<=0)
			return false;

		if(method_exists($DB, "NextID"))
		{
			$ID = $DB->NextID("sq_b_iblock_property_enum");
			$arFields["ID"] = $ID;
		}

		$arInsert = $DB->PrepareInsert("b_iblock_property_enum", $arFields);
		$strSql =
			"INSERT INTO b_iblock_property_enum(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";

		$DB->Query($strSql);

		if(method_exists($DB, "LastID"))
			$ID = $DB->LastID();

		return $ID;
	}

	function Update($ID, $arFields)
	{
		global $DB;
		$ID = IntVal($ID);
		if(CACHED_b_iblock_property_enum!==false) $GLOBALS["CACHE_MANAGER"]->CleanDir("b_iblock_property_enum");

		if(is_set($arFields, "EXTERNAL_ID"))
			$arFields["XML_ID"] = $arFields["EXTERNAL_ID"];

		if(is_set($arFields, "DEF") && $arFields["DEF"]!="Y")
			$arFields["DEF"]="N";
		if(is_set($arFields, "VALUE") && strlen($arFields["VALUE"])<=0)
		{
			return false;
		}

		$strUpdate = $DB->PrepareUpdate("b_iblock_property_enum", $arFields);
		$strSql = "UPDATE b_iblock_property_enum SET ".$strUpdate." WHERE ID=".$ID;
		$DB->Query($strSql);

		return true;
	}

	function DeleteByPropertyID($PROPERTY_ID, $bIgnoreError=false)
	{
		if(CACHED_b_iblock_property_enum!==false) $GLOBALS["CACHE_MANAGER"]->CleanDir("b_iblock_property_enum");
		return $GLOBALS["DB"]->Query("DELETE FROM b_iblock_property_enum WHERE PROPERTY_ID=".IntVal($PROPERTY_ID), $bIgnoreError);
	}

	function Delete($ID)
	{
		if(CACHED_b_iblock_property_enum!==false) $GLOBALS["CACHE_MANAGER"]->CleanDir("b_iblock_property_enum");
		$GLOBALS["DB"]->Query("DELETE FROM b_iblock_property_enum WHERE ID=".IntVal($ID));
		return true;
	}

	function GetByID($ID)
	{
		global $BX_IBLOCK_ENUM_CACHE, $DB, $CACHE_MANAGER;
		$ID=intval($ID);

		if(array_key_exists($ID, $BX_IBLOCK_ENUM_CACHE))
			return $BX_IBLOCK_ENUM_CACHE[$ID];

		if(CACHED_b_iblock_property_enum===false)
		{
			$rs = $GLOBALS["DB"]->Query("SELECT * from b_iblock_property_enum WHERE ID=".$ID);
			$BX_IBLOCK_ENUM_CACHE[$ID] = $rs->Fetch();
			return $BX_IBLOCK_ENUM_CACHE[$ID];
		}
		else
		{
			$bucket_size = intval(CACHED_b_iblock_property_enum_bucket_size);
			if($bucket_size<=0) $bucket_size = 10;

			$bucket = intval($ID/$bucket_size);
			if($CACHE_MANAGER->Read(CACHED_b_iblock_property_enum, $cache_id="b_iblock_property_enum".$bucket, "b_iblock_property_enum"))
				$arEnums = $CACHE_MANAGER->Get($cache_id);
			else
			{
				$arEnums = array();
				$rs = $DB->Query("
					SELECT *
					FROM b_iblock_property_enum
					WHERE ID between ".($bucket*$bucket_size)." AND ".(($bucket+1)*$bucket_size-1)
				);
				while($ar = $rs->Fetch())
					$arEnums[$ar["ID"]]=$ar;
				$CACHE_MANAGER->Set($cache_id, $arEnums);
			}
			$max = ($bucket+1)*$bucket_size;
			for($i=$bucket*$bucket_size;$i<$max;$i++)
			{
				if(array_key_exists($i, $arEnums))
					$BX_IBLOCK_ENUM_CACHE[$i]=$arEnums[$i];
				else
					$BX_IBLOCK_ENUM_CACHE[$i]=false;
			}
			return $BX_IBLOCK_ENUM_CACHE[$ID];
		}
	}
}
?>
