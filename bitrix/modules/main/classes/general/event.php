<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2005 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/event.php");

global $BX_EVENT_SITE_PARAMS;
$BX_EVENT_SITE_PARAMS = Array();

class CAllEvent
{
	function fieldencode($s)
	{
		$ret_val=str_replace("%", "%2", $s);
		$ret_val=str_replace("&","%1", $ret_val);
		$ret_val=str_replace("=", "%3", $ret_val);
		return $ret_val;
	}

	function ExtractMailFields($str)
	{
		$ar=explode("&", $str);
		$newar=Array();
		while (list ($key, $val) = each ($ar))
		{
			$val=str_replace("%1","&",$val);
			$tar=explode("=", $val);
			$key=$tar[0];
			$val=$tar[1];
			$key=str_replace("%3","=",$key);
	 		$val=str_replace("%3","=",$val);
			$key=str_replace("%2","%",$key);
	 		$val=str_replace("%2","%",$val);
	 		if($key!="")
				$newar[$key]=$val;
		}
		return $newar;
	}

	function GetSiteFieldsArray($site_id)
	{
		global $BX_EVENT_SITE_PARAMS;
		if($site_id !== false && isset($BX_EVENT_SITE_PARAMS[$site_id]))
			return $BX_EVENT_SITE_PARAMS[$site_id];

		$SITE_NAME = COption::GetOptionString("main", "site_name", $GLOBALS["SERVER_NAME"]);
		$SERVER_NAME = COption::GetOptionString("main", "server_name", $GLOBALS["SERVER_NAME"]);
		$DEFAULT_EMAIL_FROM = COption::GetOptionString("main", "email_from", "admin@".$GLOBALS["SERVER_NAME"]);

		if(strlen($site_id)>0)
		{
			$dbSite = CSite::GetByID($site_id);
			if($arSite = $dbSite->Fetch())
			{
				$BX_EVENT_SITE_PARAMS[$site_id] = array(
					"SITE_NAME" => ($arSite["SITE_NAME"]<>''? $arSite["SITE_NAME"] : $SITE_NAME),
					"SERVER_NAME" => ($arSite["SERVER_NAME"]<>''? $arSite["SERVER_NAME"] : $SERVER_NAME),
					"DEFAULT_EMAIL_FROM" => ($arSite["EMAIL"]<>''? $arSite["EMAIL"] : $DEFAULT_EMAIL_FROM)
				);
				return $BX_EVENT_SITE_PARAMS[$site_id];
			}
		}

		return array(
			"SITE_NAME" => $SITE_NAME,
			"SERVER_NAME" => $SERVER_NAME,
			"DEFAULT_EMAIL_FROM" => $DEFAULT_EMAIL_FROM
		);
	}

	function ReplaceTemplate($str, $ar)
	{
		Reset($ar);
		while(list($key, $val) = each($ar))
		    $str=str_replace("#".$key."#", $val, $str);

		return $str;
	}

	function Is8Bit($str)
	{
		for($i=0; $i<strlen($str); $i++)
			if(ord($str[$i])>>7)
				return true;
		return false;
	}

	function EncodeMimeString($text, $charset)
	{
		if(!CEvent::Is8Bit($text))
			return $text;

		//$maxl = IntVal((76 - strlen($charset) + 7)*0.4);
		$res = "";
		$maxl = 40;
		$eol = CEvent::GetMailEOL();
		for($i=0; $i<strlen($text); $i=$i+$maxl)
		{
			if($i>0)
				$res .= $eol."\t";
			$res .= "=?".$charset."?B?".base64_encode(substr($text, $i, $maxl))."?=";
		}
		return $res;
	}

	function GetMailEOL()
	{
		static $eol = false;
		if($eol!==false)
			return $eol;

		if(strtoupper(substr(PHP_OS,0,3)=='WIN'))
			$eol="\r\n";
		elseif(strtoupper(substr(PHP_OS,0,3)!='MAC'))
			$eol="\n"; 	 //unix
		else
			$eol="\r";

		return $eol;
	}
}


class CAllEventMessage
{
	function CheckFields($arFields, $ID=false)
	{
		global $DB;
		$this->LAST_ERROR = "";
		$arMsg = Array();

		if(is_set($arFields, "EMAIL_FROM") && strlen($arFields["EMAIL_FROM"])<3)
		{
			$this->LAST_ERROR .= GetMessage("BAD_EMAIL_FROM")."<br>";
			$arMsg[] = array("id"=>"EMAIL_FROM", "text"=> GetMessage("BAD_EMAIL_FROM"));
		}
		if(is_set($arFields, "EMAIL_TO") && strlen($arFields["EMAIL_TO"])<3)
		{
			$this->LAST_ERROR .= GetMessage("BAD_EMAIL_TO")."<br>";
			$arMsg[] = array("id"=>"EMAIL_TO", "text"=> GetMessage("BAD_EMAIL_TO"));
		}

		if($ID===false && !is_set($arFields, "EVENT_NAME"))
		{
			$this->LAST_ERROR .= GetMessage(GetMessage("MAIN_BAD_EVENT_NAME_NA"))."<br>";
			$arMsg[] = array("id"=>"EVENT_NAME", "text"=> GetMessage("MAIN_BAD_EVENT_NAME_NA"));
		}
		if(is_set($arFields, "EVENT_NAME"))
		{
			$r = CEventType::GetListEx(array(), array("EVENT_NAME"=>$arFields["EVENT_NAME"]), array("type"=>"none"));
			if(!$r->Fetch())
			{
				$this->LAST_ERROR .= GetMessage("BAD_EVENT_TYPE")."<br>";
				$arMsg[] = array("id"=>"EVENT_NAME", "text"=> GetMessage("BAD_EVENT_TYPE"));
			}
		}

		if(
			($ID===false && !is_set($arFields, "LID")) ||
			(is_set($arFields, "LID")
			&& (
				(is_array($arFields["LID"]) && count($arFields["LID"])<=0)
				||
				(!is_array($arFields["LID"]) && strlen($arFields["LID"])<=0)
				)
			)
		)
		{
			$this->LAST_ERROR .= GetMessage("MAIN_BAD_SITE_NA")."<br>";
			$arMsg[] = array("id"=>"LID", "text"=> GetMessage("MAIN_BAD_SITE_NA"));
		}
		elseif(is_set($arFields, "LID"))
		{
			if(!is_array($arFields["LID"]))
				$arFields["LID"] = Array($arFields["LID"]);

			foreach($arFields["LID"] as $v)
			{
	    			$r = CSite::GetByID($v);
	    			if(!$r->Fetch())
	    			{
	    				$this->LAST_ERROR .= "'".$v."' - ".GetMessage("MAIN_EVENT_BAD_SITE")."<br>";
	    				$arMsg[] = array("id"=>"LID", "text"=> GetMessage("MAIN_EVENT_BAD_SITE"));
	    			}
			}
		}

		if(!empty($arMsg))
		{
			$e = new CAdminException($arMsg);
			$GLOBALS["APPLICATION"]->ThrowException($e);
		}

		if(strlen($this->LAST_ERROR)>0)
			return false;

		return true;
	}

	///////////////////////////////////////////////////////////////////
	// New event message template
	///////////////////////////////////////////////////////////////////
	function Add($arFields)
	{
		global $DB;

		unset($arFields["ID"]);

		if(!$this->CheckFields($arFields))
			return false;

		if(is_set($arFields, "ACTIVE") && $arFields["ACTIVE"]!="Y")
			$arFields["ACTIVE"]="N";

		$arLID = Array();
		if(is_set($arFields, "LID"))
		{
			if(is_array($arFields["LID"]))
				$arLID = $arFields["LID"];
			else
				$arLID[] = $arFields["LID"];

			$arFields["LID"] = false;
			$str_LID = "''";
			foreach($arLID as $v)
			{
				$arFields["LID"] = $v;
				$str_LID .= ", '".$DB->ForSql($v)."'";
			}
		}

		$ID = CDatabase::Add("b_event_message", $arFields, Array("MESSAGE"));

		if(count($arLID)>0)
		{
			$strSql = "DELETE FROM b_event_message_site WHERE EVENT_MESSAGE_ID=".$ID;
			$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

			$strSql =
				"INSERT INTO b_event_message_site(EVENT_MESSAGE_ID, SITE_ID) ".
				"SELECT ".$ID.", LID ".
				"FROM b_lang ".
				"WHERE LID IN (".$str_LID.") ";

			$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		}

		return $ID;
	}

	function Update($ID, $arFields)
	{
		global $DB;

		if(!$this->CheckFields($arFields, $ID))
			return false;

		if(is_set($arFields, "ACTIVE") && $arFields["ACTIVE"]!="Y")
			$arFields["ACTIVE"]="N";

		$arLID = Array();
		if(is_set($arFields, "LID"))
		{
			if(is_array($arFields["LID"]))
				$arLID = $arFields["LID"];
			else
				$arLID[] = $arFields["LID"];

			$arFields["LID"] = false;
			$str_LID = "''";
			foreach($arLID as $v)
			{
				$arFields["LID"] = $v;
				$str_LID .= ", '".$DB->ForSql($v)."'";
			}
		}

		$ID = IntVal($ID);
		$strUpdate = $DB->PrepareUpdate("b_event_message", $arFields);
		$strSql = "UPDATE b_event_message SET ".$strUpdate." WHERE ID=".$ID;

		$arBinds=Array();
		if(is_set($arFields, "MESSAGE"))
			$arBinds["MESSAGE"] = $arFields["MESSAGE"];

		$DB->QueryBind($strSql, $arBinds);

		if(count($arLID)>0)
		{
			$strSql = "DELETE FROM b_event_message_site WHERE EVENT_MESSAGE_ID=".$ID;
			$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

			$strSql =
				"INSERT INTO b_event_message_site(EVENT_MESSAGE_ID, SITE_ID) ".
				"SELECT ".$ID.", LID ".
				"FROM b_lang ".
				"WHERE LID IN (".$str_LID.") ";
			$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		}

		return true;
	}

	///////////////////////////////////////////////////////////////////
	// Query
	///////////////////////////////////////////////////////////////////
	function GetByID($ID)
	{
		return CEventMessage::GetList($o, $b, Array("ID"=>$ID));
	}

	function GetSite($event_message_id)
	{
		global $DB;
		$strSql = "SELECT L.*, MS.* FROM b_event_message_site MS, b_lang L WHERE L.LID=MS.SITE_ID AND MS.EVENT_MESSAGE_ID=".IntVal($event_message_id);
		return $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
	}

	function GetLang($event_message_id)
	{
		return CEventMessage::GetSite($event_message_id);
	}

	function Delete($ID)
	{
		global $DB;
		$ID = Intval($ID);

		$bCanDelete = true;
		$db_events = GetModuleEvents("main", "OnBeforeEventMessageDelete");
		while($arEvent = $db_events->Fetch())
			if(ExecuteModuleEvent($arEvent, $ID)===false)
			{
				$err = GetMessage("MAIN_BEFORE_DEL_ERR").' '.$arEvent['TO_NAME'];
				if($ex = $APPLICATION->GetException())
					$err .= ': '.$ex->GetString();
				$APPLICATION->throwException($err);
				return false;
			}

		@set_time_limit(600);

		//check module event for OnDelete
		$events = GetModuleEvents("main", "OnEventMessageDelete");
		while($arEvent = $events->Fetch())
			ExecuteModuleEvent($arEvent, $ID);

		$DB->Query("DELETE FROM b_event_message_site WHERE EVENT_MESSAGE_ID=".$ID, true);
		return $DB->Query("DELETE FROM b_event_message WHERE ID=".$ID, true);
	}
}

class CAllEventType
{
	function CheckFields($arFields = array(), $action = "ADD", $ID = array())
	{
		$arFilter = array();
		$res = array();
		$aMsg = array();
		//ID, LID, EVENT_NAME, NAME, DESCRIPTION, SORT
		if ($action == "ADD")
		{
			if (empty($arFields["EVENT_NAME"]))
				$aMsg[] = array("id"=>"EVENT_NAME_EMPTY", "text"=>GetMessage("EVENT_NAME_EMPTY"));
			
			if(!is_set($arFields, "LID") && is_set($arFields, "SITE_ID"))
				$arFields["LID"] = $arFields["SITE_ID"];
			if (is_set($arFields, "LID") && empty($arFields["LID"]))
				$aMsg[] = array("id"=>"LID_EMPTY", "text"=>GetMessage("LID_EMPTY"));
				
			if (empty($aMsg))
			{
				$db_res = CEventType::GetList(array("LID" => $arFields["LID"], "EVENT_NAME" => $arFields["EVENT_NAME"]));
				if ($db_res && $res = $db_res->Fetch())
					$aMsg[] = array("id"=>"EVENT_NAME_EXIST", "text"=>str_replace(
							array("#SITE_ID#", "#EVENT_NAME#"), 
							array($arFields["LID"], $arFields["EVENT_NAME"]),
							GetMessage("EVENT_NAME_EXIST")));
			}
		}
		elseif ($action == "UPDATE")
		{
			if (empty($ID) && (empty($ID["ID"]) || (empty($ID["EVENT_NAME"]))))
			{
				if (empty($ID))
					$aMsg[] = array("id"=>"EVENT_ID_EMPTY", "text"=>GetMessage("EVENT_ID_EMPTY"));
				else
					$aMsg[] = array("id"=>"EVENT_NAME_LID_EMPTY", "text"=>GetMessage("EVENT_ID_EMPTY"));
			}

			if (empty($aMsg) && is_set($arFields, "EVENT_NAME") && (is_set($arFields, "LID")))
			{
				if (is_set($arFields, "EVENT_NAME"))
					$arFilter["EVENT_NAME"] = $arFields["EVENT_NAME"];
				if (is_set($arFields, "LID"))
					$arFilter["LID"] = $arFields["LID"];
					
				if (!empty($arFilter) && (count($arFilter) < 2) && is_set($arFilter, "LID"))
				{
					unset($arFields["LID"]);
				}
				else 
				{
					$db_res = CEventType::GetList($arFilter);
					
					if ($db_res && $res = $db_res->Fetch())
					{
						if (($action == "UPDATE") &&
							((is_set($ID, "EVENT_NAME") && is_set($ID, "LID") && 
								(($res["EVENT_NAME"] != $ID["EVENT_NAME"]) || ($res["LID"] != $ID["LID"]))) ||
								(is_set($ID, "ID") && $res["ID"] != $ID["ID"]) ||
								(is_set($ID, "EVENT_NAME") && ($res["EVENT_NAME"] != $ID["EVENT_NAME"]))))
						{
							$aMsg[] = array("id"=>"EVENT_NAME_EXIST", "text"=>str_replace(
									array("#SITE_ID#", "#EVENT_NAME#"), 
									array($arFields["LID"], $arFields["EVENT_NAME"]),
									GetMessage("EVENT_NAME_EXIST")));
						}
					}
				}
			}
		}
		else 
			$aMsg[] = array("id"=>"ACTION_EMPTY", "text"=>GetMessage("ACTION_EMPTY"));
			
		if(!empty($aMsg))
		{
			$e = new CAdminException($aMsg);
			$GLOBALS["APPLICATION"]->ThrowException($e);
			return false;
		}
		return true;
	}
	
	function Update($arID = array(), $arFields = array())
	{
		global $DB;
		$strSql = "";
		$ID = array();
		$arFieldsTemplates = array();
		// обратиться к типу почтового события можно как по ID, так и по индексу (LID+EVENT_NAME)
		if (is_array($arID) && !empty($arID))
		{
			foreach ($arID as $key => $val)
			{
				if (in_array($key, array("ID", "LID", "EVENT_NAME")))
					$ID[$key] = $val;
			}
		}
		if (!empty($ID) && CEventType::CheckFields($arFields, "UPDATE", $ID))
		{
			foreach ($ID as $key => $val)
				$ID[$key] = $key."='".$DB->ForSql($val)."'";
				
			$db_res = CEventType::GetList($ID);
			$res = $db_res->Fetch();
			
			$arBinds = array();
			if (is_set($arFields, "DESCRIPTION"))
				$arBinds["DESCRIPTION"] = $arFields["DESCRIPTION"];
			unset($arFields["ID"]);
			return $DB->QueryBind(
				"UPDATE b_event_type SET ".$DB->PrepareUpdate("b_event_type", $arFields)." WHERE (".implode(") AND (", $ID).")",
				$arBinds, 
				false);
		}
		return false;
	}
	
	function Delete($arID)
	{
		global $DB;
		$ID = array();
		if (!is_array($arID))
			$arID = array("EVENT_NAME" => $arID);
		foreach ($arID as $k => $v)	
			$ID[] = $DB->ForSQL($k)."='".$DB->ForSQL($v)."'";
		if (!empty($ID))
		{
			return $DB->Query("DELETE FROM b_event_type WHERE ".implode(" AND ", $ID), true);
		}
		return false;
	}

	function GetList($arFilter=Array())
	{
		global $DB;
		$arSqlSearch = Array();
		$filter_keys = array_keys($arFilter);
		for($i=0; $i<count($filter_keys); $i++)
		{
			$val = $DB->ForSql($arFilter[$filter_keys[$i]]);
			if(strlen($val)<=0) continue;
			switch(strtoupper($filter_keys[$i]))
			{
				case "EVENT_NAME":
				case "TYPE_ID":
					$arSqlSearch[] = "ET.EVENT_NAME = '".$val."'";
					break;
				case "LID":
					$arSqlSearch[] = "ET.LID = '".$val."'";
					break;
				case "ID":
					$arSqlSearch[] = "ET.ID=".IntVal($val);
					break;
			}
		}

		$strSqlSearch = "";
		for($i=0; $i<count($arSqlSearch); $i++)
		{
			if($i>0)
				$strSqlSearch .= " AND ";
			else
				$strSqlSearch = " WHERE ";

			$strSqlSearch .= " (".$arSqlSearch[$i].") ";
		}

		$strSql =
			"SELECT ID, LID, EVENT_NAME, NAME, DESCRIPTION, SORT ".
			"FROM b_event_type ET ".$strSqlSearch." ORDER BY ID";
		$res = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		return $res;
	}

	function GetListEx($arOrder = array(), $arFilter = array(), $arParams = array())
	{
		global $DB;
		$arSqlSearch = array();
		$strSqlSearch = "";
		$arSqlOrder = array();
		$strSqlOrder = "";
		$filter_keys = array_keys($arFilter);
		for($i=0; $i<count($filter_keys); $i++)
		{
			$val = $DB->ForSql($arFilter[$filter_keys[$i]]);
			if(strlen($val)<=0) continue;
			$key = $filter_keys[$i];
			$key_res = CEventType::GetFilterOperation($key);
			$key = strToUpper($key_res["FIELD"]);
			$strNegative = $key_res["NEGATIVE"];
			$strOperation = $key_res["OPERATION"];
			switch($key)
			{
				case "EVENT_NAME":
				case "TYPE_ID":
					if ($strOperation == "LIKE")
						$val = "%".$val."%";
					$arSqlSearch[] = ($strNegative=="Y"?" #TABLE_ID#.EVENT_NAME  IS NULL OR NOT ":"")."(#TABLE_ID#.EVENT_NAME ".$strOperation." '".$val."' )";
					break;
				case "DESCRIPTION":
				case "NAME":
					if ($strOperation == "LIKE")
						$val = "%".$val."%";
					$arSqlSearch[] = ($strNegative=="Y"?" ET.".$key." IS NULL OR NOT ":"")."(ET.".$key." ".$strOperation." '".$val."' )";
					break;
				case "LID":
					$arSqlSearch[] = ($strNegative=="Y"?" ET.".$key." IS NULL OR NOT ":"")."(ET.".$key." ".$strOperation." '".$val."' )";
					break;
				case "ID":
					$arSqlSearch[] = ($strNegative=="Y"?" ET.".$key." IS NULL OR NOT ":"")."(ET.".$key." ".$strOperation." ".intVal($val)." )";
					break;
				case "MESSAGE_ID":
					$arSqlSearch[] = ($strNegative=="Y"?" ET.ID IS NULL OR NOT ":"")."(EM.ID ".$strOperation." ".intVal($val)." )";
					break;
			}
		}
		if (count($arSqlSearch) > 0)	
			$strSqlSearch = "WHERE (".implode(") AND (", $arSqlSearch).") ";
			
		if (is_array($arOrder) && count($arOrder) > 0)
		{
			foreach ($arOrder as $by=>$order)
			{
				$by = strtoupper($by); $order = strtoupper($order);
				$order != "ASC" ? "DESC" : "ASC";
				if (in_array($by, array("EVENT_NAME", "ID")))
					$arSqlOrder["EVENT_NAME"] = "EVENT_NAME ".$order;
			}
			if (empty($arSqlOrder))
				$arSqlOrder["EVENT_NAME"] = "EVENT_NAME DESC";
				
			if(count($arSqlOrder) > 0)
				$strSqlOrder = " ORDER BY ".implode(", ", $arSqlOrder);
		}
					
		$strSql = "	
			SELECT EM.EVENT_NAME AS ID, EM.EVENT_NAME
			FROM b_event_message EM
			LEFT JOIN b_event_type ET ON (ET.EVENT_NAME = EM.EVENT_NAME)
			".str_replace("#TABLE_ID#", "EM", $strSqlSearch)."
			UNION
			SELECT ET.EVENT_NAME AS ID, ET.EVENT_NAME
			FROM b_event_type ET
			LEFT JOIN b_event_message EM ON (ET.EVENT_NAME = EM.EVENT_NAME)
			".str_replace("#TABLE_ID#", "ET", $strSqlSearch)."
			".$strSqlOrder;
		$db_res = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		$db_res = new _CEventTypeResult($db_res, $arParams);
		return $db_res;
	}

	///////////////////////////////////////////////////////////////////
	// selecting type
	///////////////////////////////////////////////////////////////////
	function GetByID($ID, $LID)
	{
		global $DB;

		$strSql =
			"SELECT ET.* ".
			"FROM b_event_type ET ".
			"WHERE ET.EVENT_NAME = '".$DB->ForSql($ID)."' ".
			"	AND ET.LID = '".$DB->ForSql($LID)."'";

		$res = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		return $res;
	}
	
	function GetFilterOperation($key)
	{
		$strNegative = "N";
		if (substr($key, 0, 1)=="!")
		{
			$key = subStr($key, 1);
			$strNegative = "Y";
		}

		$strOrNull = "N";
		if (subStr($key, 0, 1)=="+")
		{
			$key = subStr($key, 1);
			$strOrNull = "Y";
		}

		if (subStr($key, 0, 2)==">=")
		{
			$key = subStr($key, 2);
			$strOperation = ">=";
		}
		elseif (subStr($key, 0, 1)==">")
		{
			$key = subStr($key, 1);
			$strOperation = ">";
		}
		elseif (subStr($key, 0, 2)=="<=")
		{
			$key = subStr($key, 2);
			$strOperation = "<=";
		}
		elseif (subStr($key, 0, 1)=="<")
		{
			$key = subStr($key, 1);
			$strOperation = "<";
		}
		elseif (subStr($key, 0, 1)=="@")
		{
			$key = subStr($key, 1);
			$strOperation = "IN";
		}
		elseif (subStr($key, 0, 1)=="~")
		{
			$key = subStr($key, 1);
			$strOperation = "LIKE";
		}
		elseif (subStr($key, 0, 1)=="%")
		{
			$key = subStr($key, 1);
			$strOperation = "QUERY";
		}
		else
		{
			$strOperation = "=";
		}

		return array("FIELD" => $key, "NEGATIVE" => $strNegative, "OPERATION" => $strOperation, "OR_NULL" => $strOrNull);
	}
}

class _CEventTypeResult extends CDBResult
{
	var $type = "type";
	var $LID = LANGUAGE_ID;
	var $SITE_ID = SITE_ID;
	
	function _CEventTypeResult($res, $arParams = array())
	{
		$this->type = empty($arParams["type"]) ? "type" : $arParams["type"];
		$this->LID = empty($arParams["LID"]) ? LANGUAGE_ID : $arParams["LID"];
		$this->SITE_ID = empty($arParams["SITE_ID"]) ? SITE_ID : $arParams["SITE_ID"];
		parent::CDBResult($res);
	}
	
	function Fetch()
	{
		global $DB;
		$arr = array();
		$arr_lid = array();
		$arr_lids = array();
		
		if($res = parent::Fetch())
		{
			if ($this->type != "none")
			{
				$db_res_ = CEventType::GetList(array("EVENT_NAME" => $res["EVENT_NAME"]));
				if ($db_res_ && $res_ = $db_res_->Fetch())
				{
					do
					{
						$arr[$res_["ID"]] = $res_;
						$arr_lid[] = $res_["LID"];
						$arr_lids[$res_["LID"]] = $res_;
					}while($res_ = $db_res_->Fetch());
				}
				$res["ID"] = array_keys($arr);
				$res["LID"] = $arr_lid;
				
				$res["NAME"] = empty($arr_lids[$this->LID]["NAME"]) ? $arr_lids["en"]["NAME"] : $arr_lids[$this->LID]["NAME"];
				$res["SORT"] = empty($arr_lids[$this->LID]["SORT"]) ? $arr_lids["en"]["SORT"] : $arr_lids[$this->LID]["SORT"];
				$res["DESCRIPTION"] = empty($arr_lids[$this->LID]["DESCRIPTION"]) ? $arr_lids["en"]["DESCRIPTION"] : $arr_lids[$this->LID]["DESCRIPTION"];
				$res["TYPE"] = $arr;
				if ($this->type != "type")
				{
					$arr = array();
					$db_res_ = CEventMessage::GetList(($sort = "sort"), ($by = "asc"), array("EVENT_NAME" => $res["EVENT_NAME"]));
					if ($db_res_ && $res_ = $db_res_->Fetch())
					{
						do
						{
							$arr[$res_["ID"]] = $res_;
						}while($res_ = $db_res_->Fetch());
					}
					$res["TEMPLATES"] = $arr;
				}
			}
		}
		return $res;
	}
}
?>