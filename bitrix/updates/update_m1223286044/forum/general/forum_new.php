<?
##############################################
# Bitrix Site Manager Forum                  #
# Copyright (c) 2002-2007 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
IncludeModuleLangFile(__FILE__); 
global $arForumPermsCache;
$arForumPermsCache = array();

/**********************************************************************/
/************** FORUM *************************************************/
/**********************************************************************/
class CAllForumNew
{
	//---------------> Forum insert, update, delete
	function CanUserViewForum($FID, $arUserGroups)
	{
		$FID = IntVal($FID);
		$strPerms = CForumNew::GetUserPermission($FID, $arUserGroups);
		if ($strPerms>="Y") return True;
		if ($strPerms<"E") return False;

		$arForum = CForumNew::GetByID($FID);
		if ($arForum)
		{
			if ($arForum["ACTIVE"]!="Y") return False;
		}
		else
		{
			return False;
		}
		return True;
	}

	function CanUserAddForum($arUserGroups, $iUserID = 0)
	{
		if (in_array(1, $arUserGroups))
		{
			return True;
		}
		return False;
	}

	function CanUserUpdateForum($FID, $arUserGroups, $iUserID = 0)
	{
		$FID = IntVal($FID);
		$strPerms = CForumNew::GetUserPermission($FID, $arUserGroups);
		if ($strPerms>="Y") 
			return True;

		return False;
	}

	function CanUserDeleteForum($FID, $arUserGroups, $iUserID = 0)
	{
		$FID = IntVal($FID);
		$strPerms = CForumNew::GetUserPermission($FID, $arUserGroups);
		if ($strPerms>="Y") 
			return True;

		return False;
	}

	function CheckFields($ACTION, &$arFields)
	{
		if ((is_set($arFields, "NAME") || $ACTION=="ADD") && strlen($arFields["NAME"])<=0) return false;

		if (!is_set($arFields, "SITES") && is_set($arFields, "LID"))
		{
			if (is_set($arFields, "PATH2FORUM_MESSAGE"))
				$arFields["SITES"] = Array($arFields["LID"] => $arFields["PATH2FORUM_MESSAGE"]);
			else
			{
				$db_lang = CLang::GetByID($arFields["LID"]);
				$lpath = "/";
				if ($ar_lang = $db_lang->Fetch()) $lpath = $ar_lang["DIR"];
				$rPart1 = $lpath.
					(COption::GetOptionString("forum", "REL_FPATH", "")).
					"forum/read.php?FID=#FORUM_ID#&TID=#TOPIC_ID#&MID=#MESSAGE_ID##message#MESSAGE_ID#";

				$arFields["SITES"] = Array($arFields["LID"] => $rPart1);
			}
		}

		if (is_set($arFields, "SITES") && is_array($arFields["SITES"]) && count($arFields["SITES"])>0)
		{
			foreach ($arFields["SITES"] as $key => $value)
			{
				if (strlen($key)<=0 || strlen($value)<=0)
					return False;
			}
		}

		if ((is_set($arFields, "SITES") || $ACTION=="ADD")
			&& (!is_array($arFields["SITES"]) || count($arFields["SITES"])<=0)) return false;

		if ((is_set($arFields, "SORT") || $ACTION=="ADD") && IntVal($arFields["SORT"])<=0) $arFields["SORT"] = 100;
		if ((is_set($arFields, "FORUM_GROUP_ID") || $ACTION=="ADD") && IntVal($arFields["FORUM_GROUP_ID"])<=0) $arFields["FORUM_GROUP_ID"] = false;

		if ((is_set($arFields, "ACTIVE") || $ACTION=="ADD") && $arFields["ACTIVE"]!="Y" && $arFields["ACTIVE"]!="N") $arFields["ACTIVE"]="N";

		if ((is_set($arFields, "ALLOW_HTML") || $ACTION=="ADD") && $arFields["ALLOW_HTML"]!="Y" && $arFields["ALLOW_HTML"]!="N") $arFields["ALLOW_HTML"]="N";
		if ((is_set($arFields, "ALLOW_ANCHOR") || $ACTION=="ADD") && $arFields["ALLOW_ANCHOR"]!="Y" && $arFields["ALLOW_ANCHOR"]!="N") $arFields["ALLOW_ANCHOR"]="Y";
		if ((is_set($arFields, "ALLOW_BIU") || $ACTION=="ADD") && $arFields["ALLOW_BIU"]!="Y" && $arFields["ALLOW_BIU"]!="N") $arFields["ALLOW_BIU"]="Y";
		if ((is_set($arFields, "ALLOW_IMG") || $ACTION=="ADD") && $arFields["ALLOW_IMG"]!="Y" && $arFields["ALLOW_IMG"]!="N") $arFields["ALLOW_IMG"]="Y";
		if ((is_set($arFields, "ALLOW_LIST") || $ACTION=="ADD") && $arFields["ALLOW_LIST"]!="Y" && $arFields["ALLOW_LIST"]!="N") $arFields["ALLOW_LIST"]="Y";
		if ((is_set($arFields, "ALLOW_QUOTE") || $ACTION=="ADD") && $arFields["ALLOW_QUOTE"]!="Y" && $arFields["ALLOW_QUOTE"]!="N") $arFields["ALLOW_QUOTE"]="Y";
		if ((is_set($arFields, "ALLOW_CODE") || $ACTION=="ADD") && $arFields["ALLOW_CODE"]!="Y" && $arFields["ALLOW_CODE"]!="N") $arFields["ALLOW_CODE"]="Y";
		if ((is_set($arFields, "ALLOW_FONT") || $ACTION=="ADD") && $arFields["ALLOW_FONT"]!="Y" && $arFields["ALLOW_FONT"]!="N") $arFields["ALLOW_FONT"]="Y";
		if ((is_set($arFields, "ALLOW_SMILES") || $ACTION=="ADD") && $arFields["ALLOW_SMILES"]!="Y" && $arFields["ALLOW_SMILES"]!="N") $arFields["ALLOW_SMILES"]="Y";
		if ((is_set($arFields, "ALLOW_UPLOAD") || $ACTION=="ADD") && $arFields["ALLOW_UPLOAD"]!="Y" && $arFields["ALLOW_UPLOAD"]!="N" && $arFields["ALLOW_UPLOAD"]!="F" && $arFields["ALLOW_UPLOAD"]!="A") $arFields["ALLOW_UPLOAD"]="N";

		if ((is_set($arFields, "ALLOW_NL2BR") || $ACTION=="ADD") && $arFields["ALLOW_NL2BR"]!="Y" && $arFields["ALLOW_NL2BR"]!="N") $arFields["ALLOW_NL2BR"]="N";
		if ((is_set($arFields, "ALLOW_KEEP_AMP") || $ACTION=="ADD") && $arFields["ALLOW_KEEP_AMP"]!="Y" && $arFields["ALLOW_KEEP_AMP"]!="N") $arFields["ALLOW_KEEP_AMP"]="N";

		if ((is_set($arFields, "MODERATION") || $ACTION=="ADD") && $arFields["MODERATION"]!="Y" && $arFields["MODERATION"]!="N") $arFields["MODERATION"]="N";
		if ((is_set($arFields, "ALLOW_MOVE_TOPIC") || $ACTION=="ADD") && $arFields["ALLOW_MOVE_TOPIC"]!="Y" && $arFields["ALLOW_MOVE_TOPIC"]!="N") $arFields["ALLOW_MOVE_TOPIC"]="N";
		if ((is_set($arFields, "ASK_GUEST_EMAIL") || $ACTION=="ADD") && $arFields["ASK_GUEST_EMAIL"]!="Y" && $arFields["ASK_GUEST_EMAIL"]!="N") $arFields["ASK_GUEST_EMAIL"]="N";

		if ((is_set($arFields, "USE_CAPTCHA") || $ACTION=="ADD") && $arFields["USE_CAPTCHA"]!="Y" && $arFields["USE_CAPTCHA"]!="N") $arFields["USE_CAPTCHA"]="N";

		return True;
	}

	function Update($ID, $arFields)
	{
		global $DB, $arForumDebugInfo;
		$ID = IntVal($ID);
		$arForum_prev = array();
		$arProcAuth = array();

		if (!CForumNew::CheckFields("UPDATE", $arFields))
			return false;

		if ($arFields["ACTIVE"] == "N")
			$arForum_prev = CForumNew::GetByID($ID);
	// ******************************** Prepare & update data ********************************
		if (is_set($arFields, "LAST_POSTER_NAME") && (COption::GetOptionString("forum", "FILTER", "Y") == "Y"))
		{
			$arr = array();
			$arr["LAST_POSTER_NAME"] = CFilterUnquotableWords::Filter($arFields["LAST_POSTER_NAME"]);
			$arFields["HTML"] = serialize($arr);
		}
		$strUpdate = $DB->PrepareUpdate("b_forum", $arFields);
		
		$strSql = "UPDATE b_forum SET ".$strUpdate." WHERE ID=".$ID;
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		
		if (is_set($arFields, "SITES") && is_array($arFields["SITES"]) && count($arFields["SITES"])>0)
		{
			$DB->Query("DELETE FROM b_forum2site WHERE FORUM_ID = ".$ID);
			foreach ($arFields["SITES"] as $key => $value)
			{
				$DB->Query("INSERT INTO b_forum2site (FORUM_ID, SITE_ID, PATH2FORUM_MESSAGE) VALUES(".$ID.", '".$DB->ForSql($key, 2)."', '".$DB->ForSql($value, 250)."')");
			}
		}
		
		if (is_set($arFields, "GROUP_ID") && is_array($arFields["GROUP_ID"]))
			CForumNew::SetAccessPermissions($ID, $arFields["GROUP_ID"]);
			
	// ******************************** Update statistic **********************************
	/* If forum became inactive than all statistics for users of this forum will be recalculated.*/
		if ($arFields["ACTIVE"]=="N" && $arForum_prev["ACTIVE"]=="Y")
		{
			$db_res_m = CForumMessage::GetList(array(), array("FORUM_ID"=>$ID, "!AUTHOR_ID"=>0));
			while ($ar_res_m = $db_res_m->Fetch())
			{
				if (!in_array(IntVal($ar_res_m["AUTHOR_ID"]), $arProcAuth))
				{
					CForumUser::SetStat(IntVal($ar_res_m["AUTHOR_ID"]));
					$arProcAuth[] = IntVal($ar_res_m["AUTHOR_ID"]);
				}
			}
			unset($arProcAuth);
		}
	//********************************** Update search module *****************************
		if (CModule::IncludeModule("search"))
		{
			if ($arFields["ACTIVE"]=="N" && $arForum_prev["ACTIVE"]=="Y")
			{
				CSearch::DeleteIndex("forum", false, $ID);
			}
			elseif (is_set($arFields, "GROUP_ID") && is_array($arFields["GROUP_ID"]))
			{
				$arGroups = CForumNew::GetAccessPermissions($ID);
				$arGPerm = Array();
				for ($i=0; $i<count($arGroups); $i++)
				{
					if ($arGroups[$i][1]>="E")
					{
						$arGPerm[] = $arGroups[$i][0];
						if ($arGroups[$i][0]==2) break;
					}
				}
				CSearch::ChangePermission("forum", $arGPerm, false, $ID);
			}
		}
	// ******************************** Cleaning cache ************************************
		unset($GLOBALS["FORUM_CACHE"]["FORUM"][$ID]);
		unset($GLOBALS["FORUM_CACHE"]["SITES"]);
		
		return $ID;
	}

	function Delete($ID)
	{
		global $DB;
		$ID = IntVal($ID);
		$bCanDelete = true;
		
		$db_events = GetModuleEvents("forum", "OnBeforeForumDelete");
		while ($arEvent = $db_events->Fetch())
		{
			if (ExecuteModuleEvent($arEvent, $ID)===false)
			{
				$bCanDelete = false;
				break;
			}
		}
		if (!$bCanDelete) 
			return False;

		$events = GetModuleEvents("forum", "OnForumDelete");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEvent($arEvent, $ID);

		if (CModule::IncludeModule("search"))
		{
			CSearch::DeleteIndex("forum", false, $ID);
		}

		$DB->StartTransaction();

	/* If forum became inactive than all statistics for users of this forum will be recalculated.*/
		$arProcAuth = array();
		$db_res_m = CForumMessage::GetList(array(), array("FORUM_ID"=>$ID, "!AUTHOR_ID"=>0));
		while ($ar_res_m = $db_res_m->Fetch())
		{
			if (!in_array(IntVal($ar_res_m["AUTHOR_ID"]), $arProcAuth))
			{
				$arProcAuth[] = IntVal($ar_res_m["AUTHOR_ID"]);
			}
		}

		unset($GLOBALS["FORUM_CACHE"]["FORUM"][$ID]);

		if (!$DB->Query("DELETE FROM b_forum_subscribe WHERE FORUM_ID=".$ID, true))
		{
			$DB->Rollback();
			return false;
		}
		if (!$DB->Query("DELETE FROM b_forum_message WHERE FORUM_ID=".$ID, true))
		{
			$DB->Rollback();
			return false;
		}
		if (!$DB->Query("DELETE FROM b_forum_topic WHERE FORUM_ID=".$ID, true))
		{
			$DB->Rollback();
			return false;
		}
		if (!$DB->Query("DELETE FROM b_forum_perms WHERE FORUM_ID=".$ID, true))
		{
			$DB->Rollback();
			return false;
		}
		if (!$DB->Query("DELETE FROM b_forum2site WHERE FORUM_ID=".$ID, true))
		{
			$DB->Rollback();
			return false;
		}
		if (!$DB->Query("DELETE FROM b_forum WHERE ID=".$ID, true))
		{
			$DB->Rollback();
			return false;
		}

	/* If forum became inactive than all statistics for users of this forum will be recalculated.*/
		for ($i = 0; $i < count($arProcAuth); $i++)
		{
			CForumUser::SetStat($arProcAuth[$i]);
		}

		$DB->Commit();

		return true;
	}

	//---------------> Array of sites (langs) where forum is available
	function GetSites($ID)
	{
		global $DB;
		
		$ID = IntVal($ID);
		if ($ID<=0)
			return false;
		if (CACHED_FORUM && isset($GLOBALS["FORUM_CACHE"]["SITES"][$ID]) && is_array($GLOBALS["FORUM_CACHE"]["SITES"][$ID]))
		{
			return $GLOBALS["FORUM_CACHE"]["SITES"][$ID];
		}
		else
		{
			$strSql = 
				"SELECT FS.FORUM_ID, FS.SITE_ID, FS.PATH2FORUM_MESSAGE 
				FROM b_forum2site FS 
				WHERE FS.FORUM_ID = ".$ID;
			$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$arRes = array();
			while ($res = $db_res->Fetch())
				$arRes[$res["SITE_ID"]] = $res["PATH2FORUM_MESSAGE"];
			$GLOBALS["FORUM_CACHE"]["SITES"][$ID] = $arRes;
			return $arRes;
		}
	}

	//---------------> Forum permissions
	function GetAccessPermissions($ID, $TYPE = "ONE")
	{
		$ID = IntVal($ID);
		$dbres = CForumNew::GetAccessPermsList(array(), array("FORUM_ID"=>$ID));
		$arRes = array();
		while ($res = $dbres->Fetch())
		{
			if ($TYPE == "ONE")
				$arRes[] = array($res["GROUP_ID"], $res["PERMISSION"]);
			else
				$arRes[$res["GROUP_ID"]] = $res["PERMISSION"];
		}

		return $arRes;
	}

	function GetAccessPermsList($arOrder = array("ID"=>"ASC"), $arFilter = array())
	{
		global $DB;
		$arSqlSearch = array();
		$strSqlSearch = "";
		$arSqlOrder = array();
		$strSqlOrder = "";

		if (!is_array($arFilter)) 
			$filter_keys = Array();
		else
			$filter_keys = array_keys($arFilter);

		for ($i = 0; $i < count($filter_keys); $i++)
		{
			$val = $arFilter[$filter_keys[$i]];

			$key = $filter_keys[$i];
			$key_res = CForumNew::GetFilterOperation($key);
			$key = strtoupper($key_res["FIELD"]);
			$strNegative = $key_res["NEGATIVE"];
			$strOperation = $key_res["OPERATION"];

			switch ($key)
			{
				case "ID":
				case "FORUM_ID":
				case "GROUP_ID":
					if (IntVal($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FP.".$key." IS NULL OR FP.".$key."<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FP.".$key." IS NULL OR NOT ":"")."FP.".$key." ".$strOperation." ".IntVal($val)." ";
					break;
				case "PERMISSION":
					if (strlen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FP.".$key." IS NULL OR LENGTH(FP.".$key.")<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FP.".$key." IS NULL OR NOT ":"")."FP.".$key." ".$strOperation." '".$DB->ForSql($val)."' ";
					break;
			}
		}
		if (count($arSqlSearch) > 0)
			$strSqlSearch = " AND (".implode(" AND ", $arSqlSearch).") ";

		foreach ($arOrder as $by=>$order)
		{
			$by = strtoupper($by); $order = strtoupper($order);
			if ($order!="ASC") $order = "DESC";

			if ($by == "FORUM_ID") $arSqlOrder[] = " FP.FORUM_ID ".$order." ";
			elseif ($by == "GROUP_ID") $arSqlOrder[] = " FP.GROUP_ID ".$order." ";
			elseif ($by == "PERMISSION") $arSqlOrder[] = " FP.PERMISSION ".$order." ";
			else
			{
				$arSqlOrder[] = " FP.ID ".$order." ";
				$by = "ID";
			}
		}
		DelDuplicateSort($arSqlOrder); 
		if (count($arSqlOrder) > 0)
			$strSqlOrder = " ORDER BY ".implode(", ", $arSqlOrder);
			
		$strSql = 
			"SELECT FP.ID, FP.FORUM_ID, FP.GROUP_ID, FP.PERMISSION 
			FROM b_forum_perms FP 
			WHERE 1 = 1 
			".$strSqlSearch."
			".$strSqlOrder;
		
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res;
	}

	// $USER->GetUserGroupArray()
	function GetUserPermission($ID, $arUserGroups)
	{
		global $DB, $aForumPermissions, $arForumPermsCache;
		$ID = IntVal($ID);

		if ($ID<=0) 
			return $aForumPermissions["reference_id"][0];

		if (CACHED_FORUM && isset($arForumPermsCache[$ID.implode("-", $arUserGroups)]) && in_array($arForumPermsCache[$ID.implode("-", $arUserGroups)], $aForumPermissions["reference_id"]))
		{
			return $arForumPermsCache[$ID.implode("-", $arUserGroups)];
		}
		else
		{
			if (in_array(1, $arUserGroups))
			{
				$arForumPermsCache[$ID.implode("-", $arUserGroups)] = $aForumPermissions["reference_id"][count($aForumPermissions["reference_id"])-1];
			}
			else
			{
				$strSql =
					"SELECT MAX(FP.PERMISSION) as P ".
					"FROM b_forum_perms FP ".
					"WHERE FP.FORUM_ID=".$ID." AND FP.GROUP_ID IN (".implode(",", $arUserGroups).")";
				$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				if ($r = $res->Fetch())
				{
					$arForumPermsCache[$ID.implode("-", $arUserGroups)] = $r["P"];
				}
			}
			if (!in_array($arForumPermsCache[$ID.implode("-", $arUserGroups)], $aForumPermissions["reference_id"]))
			{
				$arForumPermsCache[$ID.implode("-", $arUserGroups)] = $aForumPermissions["reference_id"][0];
			}
			return $arForumPermsCache[$ID.implode("-", $arUserGroups)];
		}
	}

	//---------------> Forum Utils
	function GetFilterOperation($key)
	{
		$strNegative = "N";
		if (substr($key, 0, 1)=="!")
		{
			$key = substr($key, 1);
			$strNegative = "Y";
		}

		if (substr($key, 0, 2)==">=")
		{
			$key = substr($key, 2);
			$strOperation = ">=";
		}
		elseif (substr($key, 0, 1)==">")
		{
			$key = substr($key, 1);
			$strOperation = ">";
		}
		elseif (substr($key, 0, 2)=="<=")
		{
			$key = substr($key, 2);
			$strOperation = "<=";
		}
		elseif (substr($key, 0, 1)=="<")
		{
			$key = substr($key, 1);
			$strOperation = "<";
		}
		elseif (substr($key, 0, 1)=="@")
		{
			$key = substr($key, 1);
			$strOperation = "IN";
		}
		elseif (substr($key, 0, 1)=="%")
		{
			$key = substr($key, 1);
			$strOperation = "LIKE";
		}
		else
		{
			$strOperation = "=";
		}

		return array("FIELD"=>$key, "NEGATIVE"=>$strNegative, "OPERATION"=>$strOperation);
	}
	
	function GetSelectFields($arAddParams = array())
	{
		global $DB;
		$arAddParams = (is_array($arAddParams) ? $arAddParams : array());
		$arAddParams["sPrefix"] = $DB->ForSql(empty($arAddParams["sPrefix"]) ? "F." : $arAddParams["sPrefix"]);
		$arAddParams["sTablePrefix"] = $DB->ForSql(empty($arAddParams["sTablePrefix"]) ? "F." : $arAddParams["sTablePrefix"]);
		$arAddParams["sReturnResult"] = ($arAddParams["sReturnResult"] == "string" ? "string" : "array");
		
		$res = array(
			$arAddParams["sPrefix"]."ID" => $arAddParams["sTablePrefix"]."ID",
			$arAddParams["sPrefix"]."NAME" => $arAddParams["sTablePrefix"]."NAME",
			$arAddParams["sPrefix"]."DESCRIPTION" => $arAddParams["sTablePrefix"]."DESCRIPTION",
			$arAddParams["sPrefix"]."SORT" => $arAddParams["sTablePrefix"]."SORT",
			$arAddParams["sPrefix"]."ACTIVE" => $arAddParams["sTablePrefix"]."ACTIVE",
			$arAddParams["sPrefix"]."MODERATION" => $arAddParams["sTablePrefix"]."MODERATION",
			$arAddParams["sPrefix"]."ALLOW_MOVE_TOPIC" => $arAddParams["sTablePrefix"]."ALLOW_MOVE_TOPIC",
			$arAddParams["sPrefix"]."TOPICS" => $arAddParams["sTablePrefix"]."TOPICS",
			$arAddParams["sPrefix"]."POSTS" => $arAddParams["sTablePrefix"]."POSTS",
			$arAddParams["sPrefix"]."LAST_POSTER_ID" => $arAddParams["sTablePrefix"]."LAST_POSTER_ID",
			$arAddParams["sPrefix"]."LAST_POSTER_NAME" => $arAddParams["sTablePrefix"]."LAST_POSTER_NAME",
			($arAddParams["sPrefix"] == $arAddParams["sTablePrefix"] ? "" : $arAddParams["sPrefix"]).
				"LAST_POST_DATE" => $DB->DateToCharFunction($arAddParams["sTablePrefix"]."LAST_POST_DATE", "FULL"),
			$arAddParams["sPrefix"]."LAST_MESSAGE_ID" => $arAddParams["sTablePrefix"]."LAST_MESSAGE_ID",
			($arAddParams["sPrefix"] == $arAddParams["sTablePrefix"] ? "" : $arAddParams["sPrefix"]).
				"MID" => $arAddParams["sTablePrefix"]."LAST_MESSAGE_ID ",
			$arAddParams["sPrefix"]."LAST_MESSAGE_ID" => $arAddParams["sTablePrefix"]."LAST_MESSAGE_ID",
			$arAddParams["sPrefix"]."ORDER_BY" => $arAddParams["sTablePrefix"]."ORDER_BY",
			$arAddParams["sPrefix"]."ORDER_DIRECTION" => $arAddParams["sTablePrefix"]."ORDER_DIRECTION",
			$arAddParams["sPrefix"]."ALLOW_HTML" => $arAddParams["sTablePrefix"]."ALLOW_HTML",
			$arAddParams["sPrefix"]."ALLOW_ANCHOR" => $arAddParams["sTablePrefix"]."ALLOW_ANCHOR",
			$arAddParams["sPrefix"]."ALLOW_BIU" => $arAddParams["sTablePrefix"]."ALLOW_BIU",
			$arAddParams["sPrefix"]."ALLOW_IMG" => $arAddParams["sTablePrefix"]."ALLOW_IMG",
			$arAddParams["sPrefix"]."ALLOW_LIST" => $arAddParams["sTablePrefix"]."ALLOW_LIST",
			$arAddParams["sPrefix"]."ALLOW_QUOTE" => $arAddParams["sTablePrefix"]."ALLOW_QUOTE",
			$arAddParams["sPrefix"]."ALLOW_CODE" => $arAddParams["sTablePrefix"]."ALLOW_CODE",
			$arAddParams["sPrefix"]."ALLOW_FONT" => $arAddParams["sTablePrefix"]."ALLOW_FONT",
			$arAddParams["sPrefix"]."ALLOW_SMILES" => $arAddParams["sTablePrefix"]."ALLOW_SMILES",
			$arAddParams["sPrefix"]."ALLOW_UPLOAD" => $arAddParams["sTablePrefix"]."ALLOW_UPLOAD",
			$arAddParams["sPrefix"]."EVENT1" => $arAddParams["sTablePrefix"]."EVENT1",
			$arAddParams["sPrefix"]."EVENT2" => $arAddParams["sTablePrefix"]."EVENT2",
			$arAddParams["sPrefix"]."EVENT3" => $arAddParams["sTablePrefix"]."EVENT3",
			$arAddParams["sPrefix"]."ALLOW_NL2BR" => $arAddParams["sTablePrefix"]."ALLOW_NL2BR",
			$arAddParams["sPrefix"]."ALLOW_UPLOAD_EXT" => $arAddParams["sTablePrefix"]."ALLOW_UPLOAD_EXT",
			$arAddParams["sPrefix"]."FORUM_GROUP_ID" => $arAddParams["sTablePrefix"]."FORUM_GROUP_ID",
			$arAddParams["sPrefix"]."ASK_GUEST_EMAIL" => $arAddParams["sTablePrefix"]."ASK_GUEST_EMAIL",
			$arAddParams["sPrefix"]."USE_CAPTCHA" => $arAddParams["sTablePrefix"]."USE_CAPTCHA",
			$arAddParams["sPrefix"]."HTML" => $arAddParams["sTablePrefix"]."HTML");

		if ($arAddParams["sReturnResult"] == "string")
		{
			$arRes = array();
			foreach ($res as $key => $val)
			{
				$arRes[] = $val.($key != $val ? " AS ".$key : "");
			}
			$res = implode(", ", $arRes);
		}
		return $res;
	}

	

	//---------------> Forum list
	function GetList($arOrder = Array("SORT"=>"ASC"), $arFilter = Array())
	{
		global $DB;
		$arSqlSearch = Array();
		$arSqlSearchFrom = Array();
		$strSqlSearchFrom = "";
		$strSqlSearch = "";
		$arSqlOrder = Array();
		$strSqlOrder = "";

		if (!is_array($arFilter)) 
			$filter_keys = Array();
		else
			$filter_keys = array_keys($arFilter);

		for ($i = 0; $i < count($filter_keys); $i++)
		{
			$val = $arFilter[$filter_keys[$i]];

			$key = $filter_keys[$i];
			$key_res = CForumNew::GetFilterOperation($key);
			$key = strtoupper($key_res["FIELD"]);
			$strNegative = $key_res["NEGATIVE"];
			$strOperation = $key_res["OPERATION"];

			switch ($key)
			{
				case "LID": 
				case "SITE_ID":
					$val = trim($val);
					if (strlen($val)>0)
					{
						$arSqlSearch[] = "F.ID = F2S.FORUM_ID AND ".($strNegative=="Y"?" NOT ":"")."(F2S.SITE_ID ".$strOperation." '".$DB->ForSql($val)."' )";
						$arSqlSearchFrom[] = "b_forum2site F2S";
					}
					break;
				case "ACTIVE":
				case "XML_ID":
					if (strlen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(F.".$key." IS NULL OR LENGTH(F.".$key.")<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" F.".$key." IS NULL OR NOT ":"")."(F.".$key." ".$strOperation." '".$DB->ForSql($val)."' )";
					break;
				case "ID":
				case "FORUM_GROUP_ID":
				case "TOPICS":
				case "POSTS":
					if ($strOperation == "IN")
					{
						if (is_array($val))
						{
							$val_int = array();
							foreach ($val as $v)
								$val_int[] = intVal($v);
							$val = implode(", ", $val_int);
						}
						$val = trim($val);
					}
					if (($strOperation == "IN" && strLen($val) <= 0) || intVal($val) <= 0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(F.".$key." IS NULL OR F.".$key."<=0)";
					elseif ($strOperation == "IN")
						$arSqlSearch[] = ($strNegative=="Y"?" NOT ":"")."(F.".$key." IN (".$DB->ForSql($val)."))";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" F.".$key." IS NULL OR NOT ":"")."(F.".$key." ".$strOperation." ".intVal($val)." )";
					break;
				case "TEXT":
					$arSqlSearch[] = " (".GetFilterQuery("F.NAME,F.DESCRIPTION", $DB->ForSql($val), "Y").") ";
					break;
				case "PERMS":
					if (is_array($val) && count($val)>1)
					{
						$arSqlSearch[] = "F.ID = FP.FORUM_ID AND FP.GROUP_ID IN (".$DB->ForSql($val[0]).") AND FP.PERMISSION > '".$DB->ForSql($val[1])."' ";
						$arSqlSearchFrom[] = "b_forum_perms FP";
					}
					break;
			}
		}

		if (count($arSqlSearch) > 0)
			$strSqlSearch = " AND (".implode(") AND (", $arSqlSearch).") ";
		if (count($arSqlSearchFrom) > 0)
			$strSqlSearchFrom = ", ".implode(", ", $arSqlSearchFrom);

		foreach ($arOrder as $by=>$order)
		{
			$by = strtoupper($by); $order = strtoupper($order);
			if ($order!="ASC") $order = "DESC";
			if ($by == "ID") $arSqlOrder[] = " F.ID ".$order." ";
			elseif ($by == "NAME") $arSqlOrder[] = " F.NAME ".$order." ";
			elseif ($by == "ACTIVE") $arSqlOrder[] = " F.ACTIVE ".$order." ";
			elseif ($by == "MODERATION") $arSqlOrder[] = " F.MODERATION ".$order." ";
			elseif ($by == "FORUM_GROUP_ID") $arSqlOrder[] = " F.FORUM_GROUP_ID ".$order." ";
			elseif ($by == "TOPICS") $arSqlOrder[] = " F.TOPICS ".$order." ";
			elseif ($by == "POSTS") $arSqlOrder[] = " F.POSTS ".$order." ";
			elseif ($by == "LAST_POST_DATE") $arSqlOrder[] = " F.LAST_POST_DATE ".$order." ";
			else
			{
				$arSqlOrder[] = " F.SORT ".$order." ";
				$by = "SORT";
			}
		}
		DelDuplicateSort($arSqlOrder); 
		if (count($arSqlOrder) > 0)
			$strSqlOrder = " ORDER BY ".implode(", ", $arSqlOrder);
		
		$strSql = 
			"SELECT F.ID, F.NAME, F.DESCRIPTION, F.ACTIVE, F.MODERATION, F.ALLOW_MOVE_TOPIC, '' as LID, 
				F.TOPICS, F.POSTS, F.LAST_POSTER_ID, F.LAST_POSTER_NAME, 
				".$DB->DateToCharFunction("F.LAST_POST_DATE", "FULL")." as LAST_POST_DATE, 
				F.LAST_MESSAGE_ID, F.LAST_MESSAGE_ID as MID, F.SORT, F.ORDER_BY, 
				F.ORDER_DIRECTION, F.ALLOW_HTML, F.ALLOW_ANCHOR, F.ALLOW_BIU, 
				F.ALLOW_IMG, F.ALLOW_LIST, F.ALLOW_QUOTE, F.ALLOW_CODE, 
				F.ALLOW_FONT, F.ALLOW_SMILES, F.ALLOW_UPLOAD, F.EVENT1, F.EVENT2, 
				F.EVENT3, F.ALLOW_NL2BR, '' as PATH2FORUM_MESSAGE, F.ALLOW_UPLOAD_EXT, 
				F.FORUM_GROUP_ID, F.ASK_GUEST_EMAIL, F.USE_CAPTCHA 
			FROM b_forum F 
				".$strSqlSearchFrom." 
			WHERE 1 = 1 
				".$strSqlSearch." 
			GROUP BY F.ID, F.NAME, F.DESCRIPTION, F.ACTIVE, F.MODERATION,  F.ALLOW_MOVE_TOPIC, 
				F.TOPICS, F.POSTS, F.LAST_POSTER_ID, F.LAST_POSTER_NAME, 
				F.LAST_POST_DATE, F.LAST_MESSAGE_ID, F.LAST_MESSAGE_ID, F.SORT, 
				F.ORDER_BY, F.ORDER_DIRECTION, F.ALLOW_HTML, F.ALLOW_ANCHOR, 
				F.ALLOW_BIU, F.ALLOW_IMG, F.ALLOW_LIST, F.ALLOW_QUOTE, 
				F.ALLOW_CODE, F.ALLOW_FONT, F.ALLOW_SMILES, F.ALLOW_UPLOAD, 
				F.EVENT1, F.EVENT2, F.EVENT3, F.ALLOW_NL2BR, 
				F.ALLOW_UPLOAD_EXT, F.FORUM_GROUP_ID, F.ASK_GUEST_EMAIL, F.USE_CAPTCHA 
			".$strSqlOrder;
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res;
	}

	function GetByID($ID)
	{
		global $DB;

		$ID = IntVal($ID);

		if (CACHED_FORUM && isset($GLOBALS["FORUM_CACHE"]["FORUM"][$ID]) && is_array($GLOBALS["FORUM_CACHE"]["FORUM"][$ID]) && is_set($GLOBALS["FORUM_CACHE"]["FORUM"][$ID], "ID"))
		{
			return $GLOBALS["FORUM_CACHE"]["FORUM"][$ID];
		}
		else
		{
		$strSql = 
			"SELECT F.ID, F.NAME, F.DESCRIPTION, F.ACTIVE, F.MODERATION, F.ALLOW_MOVE_TOPIC, '' as LID, 
				F.TOPICS, F.POSTS, F.LAST_POSTER_ID, F.LAST_POSTER_NAME, 
				".$DB->DateToCharFunction("F.LAST_POST_DATE", "FULL")." as LAST_POST_DATE, 
				F.LAST_MESSAGE_ID, F.LAST_MESSAGE_ID as MID, F.SORT, F.ORDER_BY, 
				F.ORDER_DIRECTION, F.ALLOW_HTML, F.ALLOW_ANCHOR, F.ALLOW_BIU, 
				F.ALLOW_IMG, F.ALLOW_LIST, F.ALLOW_QUOTE, F.ALLOW_CODE, 
				F.ALLOW_FONT, F.ALLOW_SMILES, F.ALLOW_UPLOAD, F.EVENT1, F.EVENT2, 
				F.EVENT3, F.ALLOW_NL2BR, '' as PATH2FORUM_MESSAGE, F.ALLOW_UPLOAD_EXT, 
				F.FORUM_GROUP_ID, F.ASK_GUEST_EMAIL, F.USE_CAPTCHA 
			FROM b_forum F 
			WHERE 
				F.ID = ".$ID;
			$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($db_res && $res = $db_res->Fetch())
			{
				$GLOBALS["FORUM_CACHE"]["FORUM"][$ID] = $res;
				return $res;
			}
		}
		return False;
	}

	function GetByIDEx($ID)
	{
		global $DB;

		$ID = IntVal($ID);
		
		$strSql = 
			"SELECT F.ID, F.NAME, F.DESCRIPTION, F.ACTIVE, F.MODERATION, F.ALLOW_MOVE_TOPIC, '' as LID, 
				F.TOPICS, F.POSTS, F.LAST_POSTER_ID, F.LAST_POSTER_NAME, 
				".$DB->DateToCharFunction("F.LAST_POST_DATE", "FULL")." as LAST_POST_DATE, 
				F.LAST_MESSAGE_ID, FM.TOPIC_ID as TID, F.LAST_MESSAGE_ID as MID, 
				FT.TITLE, F.SORT, '' as DIR, F.ORDER_BY, F.ORDER_DIRECTION, 
				F.ALLOW_HTML, F.ALLOW_ANCHOR, F.ALLOW_BIU, 
				F.ALLOW_IMG, F.ALLOW_LIST, F.ALLOW_QUOTE, F.ALLOW_CODE, 
				F.ALLOW_FONT, F.ALLOW_SMILES, F.ALLOW_UPLOAD, F.EVENT1, F.EVENT2, 
				F.EVENT3, F.ALLOW_NL2BR, '' as PATH2FORUM_MESSAGE, F.ALLOW_UPLOAD_EXT, 
				F.FORUM_GROUP_ID, F.ASK_GUEST_EMAIL, F.USE_CAPTCHA, F.HTML, FT.HTML AS TOPIC_HTML 
			FROM b_forum F 
				LEFT JOIN b_forum_group FG ON F.FORUM_GROUP_ID = FG.ID 
				LEFT JOIN b_forum_message FM ON F.LAST_MESSAGE_ID = FM.ID 
				LEFT JOIN b_forum_topic FT ON FM.TOPIC_ID = FT.ID 
			WHERE (F.ID=".$ID.")";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if ($db_res)
		{
			if (COption::GetOptionString("forum", "FILTER", "Y") == "Y")
				$db_res = new _CForumDBResult($db_res);
			if ($res = $db_res->Fetch())
				return $res;
		}
		return False;
	}

	//---------------> Forum labels
	function InitReadLabels($ID, $arUserGroups) // out-of-date function
	{
		$ID = IntVal($ID);
		if ($ID<=0) return false;

		$arForumCookie = array();
		$iCurFirstReadForum = 0;
		$read_forum_cookie = COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_FORUM_0";
		if (isset($_COOKIE[$read_forum_cookie]) && strlen($_COOKIE[$read_forum_cookie])>0)
		{
			$arForumCookie = explode("/", $_COOKIE[$read_forum_cookie]);
			$i = 0;
			while ($i < count($arForumCookie))
			{
				if (IntVal($arForumCookie[$i])==$ID)
				{
					$iCurFirstReadForum = IntVal($arForumCookie[$i+1]);
					break;
				}
				$i += 2;
			}
		}

		$read_forum_cookie1 = COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_FORUM_".$ID;
		if (isset($_COOKIE[$read_forum_cookie1]) && IntVal($_COOKIE[$read_forum_cookie1])>0)
		{
			if ($iCurFirstReadForum<IntVal($_COOKIE[$read_forum_cookie1]))
			{
				$iCurFirstReadForum = IntVal($_COOKIE[$read_forum_cookie1]);
			}
		}

		if (strlen($_SESSION["first_read_forum_".$ID])<=0 || IntVal($_SESSION["first_read_forum_".$ID])<0)
		{
			$_SESSION["first_read_forum_".$ID] = $iCurFirstReadForum;
		}

		if (is_null($_SESSION["read_forum_".$ID]) || strlen($_SESSION["read_forum_".$ID])<=0)
		{
			$_SESSION["read_forum_".$ID] = "0";
		}

		$arFilter = array("FORUM_ID" => $ID);
		$strPerms = CForumNew::GetUserPermission($ID, $arUserGroups);
		if ($strPerms<="Q") $arFilter["APPROVED"] = "Y";
		$db_res = CForumMessage::GetList(array("ID"=>"DESC"), $arFilter, false, 1);
		if ($res = $db_res->Fetch())
		{
			$i = 0;
			$strCookieVal = "";
			while ($i < count($arForumCookie))
			{
				if (IntVal($arForumCookie[$i])!=$ID)
				{
					if (strlen($strCookieVal)>0) $strCookieVal .= "/";
					$strCookieVal .= IntVal($arForumCookie[$i])."/".IntVal($arForumCookie[$i+1]);
				}
				$i += 2;
			}

			if (strlen($strCookieVal)>0) $strCookieVal = "/".$strCookieVal;
			$strCookieVal = $ID."/".IntVal($res["ID"]).$strCookieVal;

			//$GLOBALS["APPLICATION"]->set_cookie($read_forum_cookie, $strCookieVal, false, "/", false, false, "Y", "");
			$GLOBALS["APPLICATION"]->set_cookie("FORUM_0", $strCookieVal, false, "/", false, false, "Y", false);
		}
		return true;
	}

	function SetLabelsBeRead($ID, $arUserGroups) // out-of-date function
	{
		$ID = IntVal($ID);
		$_SESSION["read_forum_".$ID] = "0";

		$arFilter = array("FORUM_ID" => $ID);
		$strPerms = CForumNew::GetUserPermission($ID, $arUserGroups);
		if ($strPerms<="Q") $arFilter["APPROVED"] = "Y";
		$db_res = CForumMessage::GetList(array("ID"=>"DESC"), $arFilter, false, 1);
		$iCurFirstReadForum = 0;
		if ($res = $db_res->Fetch())
		{
			$iCurFirstReadForum = IntVal($res["ID"]);
		}
		$_SESSION["first_read_forum_".$ID] = $iCurFirstReadForum;

		$arForumCookie = array();
		$read_forum_cookie = COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_FORUM_0";
		if (isset($_COOKIE[$read_forum_cookie]) && strlen($_COOKIE[$read_forum_cookie])>0)
		{
			$arForumCookie = explode("/", $_COOKIE[$read_forum_cookie]);
		}

		$i = 0;
		$strCookieVal = "";
		while ($i < count($arForumCookie))
		{
			if (IntVal($arForumCookie[$i])!=$ID)
			{
				if (strlen($strCookieVal)>0) $strCookieVal .= "/";
				$strCookieVal .= IntVal($arForumCookie[$i])."/".IntVal($arForumCookie[$i+1]);
			}
			$i += 2;
		}

		if (strlen($strCookieVal)>0) $strCookieVal = "/".$strCookieVal;
		$strCookieVal = $ID."/".$iCurFirstReadForum.$strCookieVal;

		$_COOKIE[$read_forum_cookie] = $strCookieVal;
//		$GLOBALS["APPLICATION"]->set_cookie($read_forum_cookie, $strCookieVal, false, "/", false, false, "Y", "");
		$GLOBALS["APPLICATION"]->set_cookie("FORUM_0", $strCookieVal, false, "/", false, false, "Y", false);
		return True;
	}

	//---------------> Forum utils
	function SetStat($ID = 0)
	{
		global $DB, $arForumDebugInfo;

		$ID = IntVal($ID);

		$arFields = array();
		$arFields["TOPICS"] = CForumTopic::GetList(array(), array("FORUM_ID"=>$ID, "APPROVED"=>"Y"), True);

		$res = CForumMessage::GetListEx(array(), array("FORUM_ID"=>$ID, "APPROVED"=>"Y"), 4);
		$arFields["POSTS"] = $res["CNT"];

		$res = CForumMessage::GetByID($res["LAST_MESSAGE_ID"]);
		if ($res)
		{
			$arFields["LAST_POSTER_ID"] = ((IntVal($res["AUTHOR_ID"])>0) ? $res["AUTHOR_ID"] : False);
			$arFields["LAST_POSTER_NAME"] = $res["AUTHOR_NAME"];
			$arFields["LAST_POST_DATE"] = $res["POST_DATE"];
			$arFields["LAST_MESSAGE_ID"] = $res["ID"];
		}
		else
		{
			$arFields["LAST_POSTER_ID"] = False;
			$arFields["LAST_POSTER_NAME"] = False;
			$arFields["LAST_POST_DATE"] = False;
			$arFields["LAST_MESSAGE_ID"] = False;
		}

		CForumNew::Update($ID, $arFields);
	}

	function PreparePath2Message($strPath, $arVals = array())
	{
		if (strlen($strPath)<=0) return "";

		return str_replace(
			"//", "/", 
			str_replace(array("#TOPIC_ID#", "#TID#"), $arVals["TOPIC_ID"], 
				str_replace(array("#FORUM_ID#", "#FID#"), $arVals["FORUM_ID"], 
					str_replace(array("#MESSAGE_ID#", "#MID#"), $arVals["MESSAGE_ID"], $strPath)
				)
			)
		);
	}

	//---------------> Forum actions
	function OnGroupDelete($GROUP_ID)
	{
		global $DB;
		return $DB->Query("DELETE FROM b_forum_perms WHERE GROUP_ID=".IntVal($GROUP_ID), true);
	}

	function OnBeforeLangDelete($lang)
	{
		global $DB;
		$r = CForumNew::GetList(array(), array("LID"=>$lang));
		return ($r->Fetch()?false:true);
	}

	function OnPanelCreate() // out-of-date function
	{
		return false;
	}

	function ShowPanel($FID, $TID=0, $bGetIcons=false)
	{
		global $APPLICATION, $REQUEST_URI, $USER;

		if(!(($USER->IsAuthorized() || $APPLICATION->ShowPanel===true) && $APPLICATION->ShowPanel!==false))
			return;
		if (!CModule::IncludeModule("forum"))
			return;
		$arButtons = array();
				
		$module_permission = $APPLICATION->GetGroupRight("forum");
		if ($module_permission > "D")
		{
			$arButtons[] = array(
				"TEXT" => GetMessage("F_FORUMS_LIST"),
				"TITLE" => GetMessage("F_FORUMS_LIST_TITLE"),
				"IMAGE" => "/bitrix/images/forum/toolbar_button1.gif",
				"ACTION" => "jsUtils.Redirect(arguments, '/bitrix/admin/forum_admin.php')");

			if ($module_permission >= "W" && intVal($FID) > 0 && 
				CForumNew::CanUserUpdateForum($FID, $USER->GetUserGroupArray(), $USER->GetID()))
			{
				$arButtons[] = array(
					"TEXT" => GetMessage("F_FORUM_EDIT"),
					"TITLE" => GetMessage("F_FORUM_EDIT_TITLE"),
					"IMAGE" => "/bitrix/images/forum/toolbar_button2.gif",
					"ACTION" => "jsUtils.Redirect(arguments, '/bitrix/admin/forum_edit.php?ID=".intVal($FID)."')");
			}
		}
		if (!empty($arButtons))
		{
			$arButton = array(
				"SRC" => "/bitrix/images/forum/toolbar_button1.gif",
				"ALT" => GetMessage("F_FORUM_TITLE"),
				"TEXT" => GetMessage("F_FORUM"),
				"MAIN_SORT" => 300,
				"MENU" => $arButtons,
				"MODE" => 'configure');
			$APPLICATION->AddPanelButton($arButton);
		}
	}

	function ClearHTML($ID)
	{
		global $DB;
		$ID = IntVal($ID);
		$strSql = "UPDATE b_forum_message SET POST_MESSAGE_HTML='' WHERE FORUM_ID=".$ID;
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return true;
		
	}
}

/**********************************************************************/
/************** FORUM GROUP *******************************************/
/**********************************************************************/
class CAllForumGroup
{
	//---------------> User insert, update, delete
	function CanUserAddGroup($arUserGroups)
	{
		if (in_array(1, $arUserGroups)) return True;
		return False;
	}

	function CanUserUpdateGroup($ID, $arUserGroups)
	{
		if (in_array(1, $arUserGroups)) return True;
		return False;
	}

	function CanUserDeleteGroup($ID, $arUserGroups)
	{
		if (in_array(1, $arUserGroups)) return True;
		return False;
	}

	function CheckFields($ACTION, &$arFields)
	{
		$aMsg = array();

		if (is_set($arFields, "LANG") || $ACTION=="ADD")
		{
			$res = (is_array($arFields["LANG"]) ? $arFields["LANG"] : array());
			foreach ($res as $i => $val)
			{
				if (empty($res[$i]["LID"]) || empty($res[$i]["NAME"]))
				{
					unset($res[$i]);
				}
			}
			$db_lang = CLanguage::GetList(($b="sort"), ($o="asc"));
			while ($arLang = $db_lang->Fetch())
			{
				$bFound = False;
				foreach ($res as $i => $val) 
				{
					if ($res[$i]["LID"]==$arLang["LID"])
						$bFound = True;
				}
				if (!$bFound) 
				{
					$aMsg[] = array(
						"id"=>'FORUM_GROUP[LANG]['.$arLang["LID"].'][NAME]', 
						"text" => str_replace(
							array("#LID#", "#LID_NAME#"), 
							array($arLang["LID"], $arLang["NAME"])
							, GetMessage("FG_ERROR_EMPTY_LID")));
				}
			}
		}
		if(!empty($aMsg))
		{
			$e = new CAdminException(array_reverse($aMsg));
			$GLOBALS["APPLICATION"]->ThrowException($e);
			return false;
		}
		if ((is_set($arFields, "SORT") || $ACTION=="ADD") && IntVal($arFields["SORT"])<=0) $arFields["SORT"] = 150;
		return true;
	}

	function Delete($ID)
	{
		global $DB;
		$ID = IntVal($ID);

		$db_res = CForumNew::GetList(array(), array("FORUM_GROUP_ID" => $ID));
		if ($db_res->Fetch())
			return False;

		$DB->Query("DELETE FROM b_forum_group_lang WHERE FORUM_GROUP_ID = ".$ID, True);
		$DB->Query("DELETE FROM b_forum_group WHERE ID = ".$ID, True);

		return true;
	}

	function GetList($arOrder = array("SORT"=>"ASC"), $arFilter = array())
	{
		global $DB;
		$arSqlSearch = Array();
		$strSqlSearch = "";
		$arSqlOrder = Array();
		$strSqlOrder = "";

		if (!is_array($arFilter)) 
			$filter_keys = Array();
		else
			$filter_keys = array_keys($arFilter);

		for ($i = 0; $i < count($filter_keys); $i++)
		{
			$val = $arFilter[$filter_keys[$i]];

			$key = $filter_keys[$i];
			$key_res = CForumNew::GetFilterOperation($key);
			$key = strtoupper($key_res["FIELD"]);
			$strNegative = $key_res["NEGATIVE"];
			$strOperation = $key_res["OPERATION"];
			
			switch ($key)
			{
				case "ID":
				case "SORT":
					if (IntVal($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FR.".$key." IS NULL OR FR.".$key."<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FR.".$key." IS NULL OR NOT ":"")."(FR.".$key." ".$strOperation." ".IntVal($val)." )";
					break;
			}
		}
		if (!empty($arSqlSearch))
			$strSqlSearch = "WHERE (".implode(") AND (", $arSqlSearch).") ";
			
		foreach ($arOrder as $by=>$order)
		{
			$by = strtoupper($by); $order = strtoupper($order);
			if ($order!="ASC") $order = "DESC";

			if ($by == "ID") $arSqlOrder[] = " FR.ID ".$order." ";
			else
			{
				$arSqlOrder[] = " FR.SORT ".$order." ";
				$by = "SORT";
			}
		}
		DelDuplicateSort($arSqlOrder); 
		if (!empty($arSqlOrder))
			$strSqlOrder = " ORDER BY ".implode(", ", $arSqlOrder);

		$strSql = 
			"SELECT FR.ID, FR.SORT ".
			"FROM b_forum_group FR ".
			$strSqlSearch." ".
			$strSqlOrder." ";
			
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res;
	}

	function GetListEx($arOrder = array("SORT"=>"ASC"), $arFilter = array())
	{
		global $DB;
		$arSqlSearch = Array();
		$strSqlSearch = "";
		$arSqlOrder = Array();
		$strSqlOrder = "";

		if (!is_array($arFilter)) 
			$filter_keys = Array();
		else
			$filter_keys = array_keys($arFilter);

		for ($i = 0; $i < count($filter_keys); $i++)
		{
			$val = $arFilter[$filter_keys[$i]];

			$key = $filter_keys[$i];
			$key_res = CForumNew::GetFilterOperation($key);
			$key = strtoupper($key_res["FIELD"]);
			$strNegative = $key_res["NEGATIVE"];
			$strOperation = $key_res["OPERATION"];

			switch ($key)
			{
				case "ID":
				case "SORT":
					if (IntVal($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FR.".$key." IS NULL OR FR.".$key."<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FR.".$key." IS NULL OR NOT ":"")."(FR.".$key." ".$strOperation." ".IntVal($val)." )";
					break;
				case "LID":
					if (strlen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FRL.LID IS NULL OR LENGTH(FRL.LID)<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FRL.LID IS NULL OR NOT ":"")."(FRL.LID ".$strOperation." '".$DB->ForSql($val)."' )";
					break;
			}
		}
		if (!empty($arSqlSearch))
			$strSqlSearch = " WHERE (".implode(") AND (", $arSqlSearch).") ";

		foreach ($arOrder as $by=>$order)
		{
			$by = strtoupper($by); $order = strtoupper($order);
			if ($order!="ASC") $order = "DESC";

			if ($by == "ID") $arSqlOrder[] = " FR.ID ".$order." ";
			elseif ($by == "LID") $arSqlOrder[] = " FRL.LID ".$order." ";
			elseif ($by == "NAME") $arSqlOrder[] = " FRL.NAME ".$order." ";
			else
			{
				$arSqlOrder[] = " FR.SORT ".$order." ";
				$by = "SORT";
			}
		}
		DelDuplicateSort($arSqlOrder); 
		
		if (!empty($arSqlOrder))
			$strSqlOrder = "ORDER BY ".implode(", ", $arSqlOrder);
			
		$strSql = 
			"SELECT FR.ID, FR.SORT, FRL.LID, FRL.NAME, FRL.DESCRIPTION ".
			"FROM b_forum_group FR ".
			"	LEFT JOIN b_forum_group_lang FRL ON FR.ID = FRL.FORUM_GROUP_ID ".
			$strSqlSearch." ".
			$strSqlOrder." ";
			
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res;
	}

	function GetByID($ID)
	{
		global $DB;

		$ID = IntVal($ID);
		$strSql = 
			"SELECT FR.ID, FR.SORT ".
			"FROM b_forum_group FR ".
			"WHERE FR.ID = ".$ID."";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return False;
	}

	function GetByIDEx($ID, $strLang)
	{
		global $DB;
		$ID = IntVal($ID);
		
		$strSql = 
			"SELECT FR.ID, FRL.LID, FRL.NAME, FR.SORT, FRL.DESCRIPTION ".
			"FROM b_forum_group FR ".
			"	LEFT JOIN b_forum_group_lang FRL ON (FR.ID = FRL.FORUM_GROUP_ID AND FRL.LID = '".$DB->ForSql($strLang)."') ".
			"WHERE FR.ID = ".$ID."";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return False;
	}

	function GetLangByID($FORUM_GROUP_ID, $strLang)
	{
		global $DB;

		$FORUM_GROUP_ID = IntVal($FORUM_GROUP_ID);
		$strSql = 
			"SELECT FRL.ID, FRL.FORUM_GROUP_ID, FRL.LID, FRL.NAME, FRL.DESCRIPTION ".
			"FROM b_forum_group_lang FRL ".
			"WHERE FRL.FORUM_GROUP_ID = ".$FORUM_GROUP_ID." ".
			"	AND FRL.LID = '".$DB->ForSql($strLang)."' ";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return False;
	}
}

/**********************************************************************/
/************** FORUM SMILE *******************************************/
/**********************************************************************/
class CAllForumSmile
{
	//---------------> User insert, update, delete
	function CheckFields($ACTION, &$arFields)
	{
		if ((is_set($arFields, "TYPE") || $ACTION=="ADD") && $arFields["TYPE"]!="I" && $arFields["TYPE"]!="S") return False;
		if ((is_set($arFields, "IMAGE") || $ACTION=="ADD") && strlen($arFields["IMAGE"])<=0) return False;

		if ((is_set($arFields, "SORT") || $ACTION=="ADD") && IntVal($arFields["SORT"])<=0) $arFields["SORT"] = 150;

		if (is_set($arFields, "LANG") || $ACTION=="ADD")
		{
			for ($i = 0; $i<count($arFields["LANG"]); $i++)
			{
				if (!is_set($arFields["LANG"][$i], "LID") || strlen($arFields["LANG"][$i]["LID"])<=0) return false;
				if (!is_set($arFields["LANG"][$i], "NAME") || strlen($arFields["LANG"][$i]["NAME"])<=0) return false;
			}

			$db_lang = CLangAdmin::GetList(($b="sort"), ($o="asc"), array("ACTIVE" => "Y"));
			while ($arLang = $db_lang->Fetch())
			{
				$bFound = False;
				for ($i = 0; $i<count($arFields["LANG"]); $i++)
				{
					if ($arFields["LANG"][$i]["LID"]==$arLang["LID"])
						$bFound = True;
				}
				if (!$bFound) return false;
			}
		}

		return True;
	}

	function Delete($ID)
	{
		global $DB;
		$ID = IntVal($ID);

		$DB->Query("UPDATE b_forum_topic SET ICON_ID = NULL WHERE ICON_ID = ".$ID, True);

		$DB->Query("DELETE FROM b_forum_smile_lang WHERE SMILE_ID = ".$ID, True);
		$DB->Query("DELETE FROM b_forum_smile WHERE ID = ".$ID, True);

		return true;
	}

	function GetList($arOrder = array("SORT"=>"ASC"), $arFilter = array())
	{
		global $DB;
		$arSqlSearch = Array();
		$strSqlSearch = "";
		$arSqlOrder = Array();
		$strSqlOrder = "";
		
		if (!is_array($arFilter)) 
			$filter_keys = Array();
		else
			$filter_keys = array_keys($arFilter);

		for ($i = 0; $i < count($filter_keys); $i++)
		{
			$val = $arFilter[$filter_keys[$i]];

			$key = $filter_keys[$i];
			$key_res = CForumNew::GetFilterOperation($key);
			$key = strtoupper($key_res["FIELD"]);
			$strNegative = $key_res["NEGATIVE"];
			$strOperation = $key_res["OPERATION"];

			switch ($key)
			{
				case "ID":
				case "SORT":
					if (IntVal($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FR.".$key." IS NULL OR FR.".$key."<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FR.".$key." IS NULL OR NOT ":"")."(FR.".$key." ".$strOperation." ".IntVal($val)." )";
					break;
				case "TYPE":
					if (strlen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FR.TYPE IS NULL OR LENGTH(FR.TYPE)<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FR.TYPE IS NULL OR NOT ":"")."(FR.TYPE ".$strOperation." '".$DB->ForSql($val)."' )";
					break;
			}
		}
		if (!empty($arSqlSearch))
			$strSqlSearch = "WHERE (".implode(") AND (", $arSqlSearch).") ";

		foreach ($arOrder as $by=>$order)
		{
			$by = strtoupper($by); $order = strtoupper($order);
			if ($order!="ASC") $order = "DESC";

			if ($by == "ID") $arSqlOrder[] = " FR.ID ".$order." ";
			elseif ($by == "TYPE") $arSqlOrder[] = " FR.TYPE ".$order." ";
			else
			{
				$arSqlOrder[] = " FR.SORT ".$order." ";
				$by = "SORT";
			}
		}
		DelDuplicateSort($arSqlOrder); 
		if (!empty($arSqlOrder))
			$strSqlOrder = " ORDER BY ".implode(", ", $arSqlOrder);

		$strSql = 
			"SELECT FR.ID, FR.SORT, FR.TYPE, FR.TYPING, FR.IMAGE, FR.CLICKABLE, FR.DESCRIPTION, FR.IMAGE_WIDTH, FR.IMAGE_HEIGHT ".
			"FROM b_forum_smile FR ".
			$strSqlSearch." ".
			$strSqlOrder;
			
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res;
	}

	function GetListEx($arOrder = array("SORT"=>"ASC"), $arFilter = array())
	{
		global $DB;
		$arSqlSearch = Array();
		$strSqlSearch = "";
		$arSqlOrder = Array();
		$strSqlOrder = "";

		if (!is_array($arFilter)) 
			$filter_keys = Array();
		else
			$filter_keys = array_keys($arFilter);

		for ($i = 0; $i < count($filter_keys); $i++)
		{
			$val = $arFilter[$filter_keys[$i]];

			$key = $filter_keys[$i];
			$key_res = CForumNew::GetFilterOperation($key);
			$key = strtoupper($key_res["FIELD"]);
			$strNegative = $key_res["NEGATIVE"];
			$strOperation = $key_res["OPERATION"];

			switch ($key)
			{
				case "ID":
				case "SORT":
					if (IntVal($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FR.".$key." IS NULL OR FR.".$key."<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FR.".$key." IS NULL OR NOT ":"")."(FR.".$key." ".$strOperation." ".IntVal($val)." )";
					break;
				case "TYPE":
					if (strlen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FR.TYPE IS NULL OR LENGTH(FR.TYPE)<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FR.TYPE IS NULL OR NOT ":"")."(FR.TYPE ".$strOperation." '".$DB->ForSql($val)."' )";
					break;
				case "LID":
					if (strlen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FRL.LID IS NULL OR LENGTH(FRL.LID)<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FRL.LID IS NULL OR NOT ":"")."(FRL.LID ".$strOperation." '".$DB->ForSql($val)."' )";
					break;
			}
		}
		if (!empty($arSqlSearch))
			$strSqlSearch = " WHERE (".implode(") AND (", $arSqlSearch).") ";


		$arSqlOrder = Array();
		foreach ($arOrder as $by=>$order)
		{
			$by = strtoupper($by); $order = strtoupper($order);
			if ($order!="ASC") $order = "DESC";

			if ($by == "ID") $arSqlOrder[] = " FR.ID ".$order." ";
			elseif ($by == "LID") $arSqlOrder[] = " FRL.LID ".$order." ";
			elseif ($by == "NAME") $arSqlOrder[] = " FRL.NAME ".$order." ";
			elseif ($by == "TYPE") $arSqlOrder[] = " FR.TYPE ".$order." ";
			else
			{
				$arSqlOrder[] = " FR.SORT ".$order." ";
				$by = "SORT";
			}
		}
		DelDuplicateSort($arSqlOrder); 
		if (!empty($arSqlOrder))
			$strSqlOrder = " ORDER BY ".implode(", ", $arSqlOrder);
		
		$strSql = 
			"SELECT FR.ID, FR.SORT, FR.TYPE, FR.TYPING, FR.IMAGE, FR.CLICKABLE, ".
			"	FRL.LID, FRL.NAME, FR.DESCRIPTION, FR.IMAGE_WIDTH, FR.IMAGE_HEIGHT ".
			"FROM b_forum_smile FR ".
			"	LEFT JOIN b_forum_smile_lang FRL ON FR.ID = FRL.SMILE_ID ".
			$strSqlSearch." ".
			$strSqlOrder;
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res;
	}

	function GetByID($ID)
	{
		global $DB;

		$ID = IntVal($ID);
		$strSql = 
			"SELECT FR.ID, FR.SORT, FR.TYPE, FR.TYPING, FR.IMAGE, FR.CLICKABLE, ".
			"	FR.DESCRIPTION, FR.IMAGE_WIDTH, FR.IMAGE_HEIGHT ".
			"FROM b_forum_smile FR ".
			"WHERE FR.ID = ".$ID."";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return False;
	}

	function GetByIDEx($ID, $strLang)
	{
		global $DB;

		$ID = IntVal($ID);
		$strSql = 
			"SELECT FR.ID, FR.SORT, FR.TYPE, FR.TYPING, FR.IMAGE, FR.CLICKABLE, ".
			"	FRL.LID, FRL.NAME, FR.DESCRIPTION, FR.IMAGE_WIDTH, FR.IMAGE_HEIGHT ".
			"FROM b_forum_smile FR ".
			"	LEFT JOIN b_forum_smile_lang FRL ON (FR.ID = FRL.SMILE_ID AND FRL.LID = '".$DB->ForSql($strLang)."') ".
			"WHERE FR.ID = ".$ID."";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return False;
	}

	function GetLangByID($SMILE_ID, $strLang)
	{
		global $DB;

		$SMILE_ID = IntVal($SMILE_ID);
		$strSql = 
			"SELECT FRL.ID, FRL.SMILE_ID, FRL.LID, FRL.NAME ".
			"FROM b_forum_smile_lang FRL ".
			"WHERE FRL.SMILE_ID = ".$SMILE_ID." ".
			"	AND FRL.LID = '".$DB->ForSql($strLang)."' ";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return False;
	}
}

class _CForumDBResult extends CDBResult
{
	function _CForumDBResult($res)
	{
		parent::CDBResult($res);
	}
	function Fetch()
	{
		global $DB;
		if($res = parent::Fetch())
		{
			if (COption::GetOptionString("forum", "FILTER", "Y") == "Y"):
				if (strLen(trim($res["HTML"])) >0)
				{
					$arr = unserialize($res["HTML"]);
					if (is_array($arr) && (count($arr) >0))
						$res["LAST_POSTER_NAME"] = $arr["LAST_POSTER_NAME"];
				}
				if (strLen(trim($res["TOPIC_HTML"])) > 0)
				{
					$arr = unserialize($res["TOPIC_HTML"]);
					if (is_array($arr) && is_set($arr, "TITLE"))
						$res["TITLE"] = $arr["TITLE"];
				}
				
			endif;
		}
		return $res;
	}
}
?>