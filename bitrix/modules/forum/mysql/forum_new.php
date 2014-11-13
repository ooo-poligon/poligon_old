<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/general/forum_new.php");

/**********************************************************************/
/************** FORUM *************************************************/
/**********************************************************************/
class CForumNew extends CAllForumNew
{
	function Add($arFields)
	{
		global $DB;

		if (!CForumNew::CheckFields("ADD", $arFields))
			return false;

		$arInsert = $DB->PrepareInsert("b_forum", $arFields);

		$strSql =
			"INSERT INTO b_forum(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$ID = IntVal($DB->LastID());

		if ($ID>0)
		{
			if (is_set($arFields, "SITES") 
				&& is_array($arFields["SITES"])
				&& count($arFields["SITES"])>0)
			{
				foreach ($arFields["SITES"] as $key => $value)
				{
					$DB->Query("INSERT INTO b_forum2site (FORUM_ID, SITE_ID, PATH2FORUM_MESSAGE) VALUES(".$ID.", '".$DB->ForSql($key, 2)."', '".$DB->ForSql($value, 250)."')");
				}
			}

			if (is_set($arFields, "GROUP_ID") && is_array($arFields["GROUP_ID"]))
			{
				CForumNew::SetAccessPermissions($ID, $arFields["GROUP_ID"]);
			}
		}
		return $ID;
	}

	function SetAccessPermissions($ID, $arGROUP_ID)
	{
		global $DB, $aForumPermissions;
		$ID = IntVal($ID);

		$DB->Query("DELETE FROM b_forum_perms WHERE FORUM_ID=".$ID);
		$arGroups = array_keys($arGROUP_ID);
		$arPerms = $arGROUP_ID;

		for ($i = 0; $i < count($arGroups); $i++)
		{
			if (!in_array(strtoupper($arPerms[$arGroups[$i]]), $aForumPermissions["reference_id"])) continue;

			$strSql = 
				"INSERT INTO b_forum_perms(FORUM_ID, GROUP_ID, PERMISSION) ".
				"SELECT ".$ID.", ID, '".$arPerms[$arGroups[$i]]."' ".
				"FROM b_group ".
				"WHERE ID = ".IntVal($arGroups[$i])." AND ID>1 ";

			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
	}

	function OnReindex($NS=Array(), $oCallback=NULL, $callback_method="")
	{
		global $DB;
		$arResult = array();

		$strNSJoin = "";
		$strFilter = "";
		$rownum=0;
		$search_message_count = intVal(COption::GetOptionString("forum", "search_message_count", 0));
		if($NS["MODULE"]=="forum" && intVal($NS["ID"])>0 && (intVal($NS["CNT"]) > 0))
			$strFilter = " AND (FM.ID>".intVal($NS["ID"]).") ";
		elseif($NS["MODULE"]=="forum" && intVal($NS["ID"])>0) // out of date
			$strFilter = " AND (FM.ID>=".intVal($NS["ID"]).") ";
		if($NS["SITE_ID"]!="")
		{
			$strNSJoin .= " INNER JOIN b_forum2site FS ON (FS.FORUM_ID=FT.FORUM_ID) ";
			$strFilter .= " AND FS.SITE_ID='".$DB->ForSQL($NS["SITE_ID"])."' ";
		}

		$strSql = 
			"SELECT FT.ID as TID, FM.ID as MID, FT.FORUM_ID, FT.TITLE, FT.TAGS, 
				FM.POST_MESSAGE, FM.AUTHOR_NAME, FM.NEW_TOPIC, 
				".$DB->DateToCharFunction("FM.POST_DATE")." as POST_DATE 
			FROM b_forum_message FM, b_forum_topic FT
			".$strNSJoin."
			WHERE (FM.TOPIC_ID = FT.ID)
			".$strFilter."
			ORDER BY FM.ID";
		if ($search_message_count > 0) 
			$strSql .= " LIMIT 0, ".$search_message_count;

		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		while ($res = $db_res->Fetch())
		{
			$rownum++;
			if (!is_array(${"arGPerm".$res["FORUM_ID"]}) || count(${"arGPerm".$res["FORUM_ID"]})<=0)
			{
				$arGroups = CForumNew::GetAccessPermissions($res["FORUM_ID"]);
				${"arGPerm".$res["FORUM_ID"]} = Array();
				for ($i=0; $i<count($arGroups); $i++)
				{
					if ($arGroups[$i][1]>="E")
					{
						${"arGPerm".$res["FORUM_ID"]}[] = $arGroups[$i][0];
						if($arGroups[$i][0]==2) break;
					}
				}
			}

			if (!is_array(${"arGSite".$res["FORUM_ID"]}) || count(${"arGSite".$res["FORUM_ID"]})<=0)
			{
				${"arGSite".$res["FORUM_ID"]} = CForumNew::GetSites($res["FORUM_ID"]);
				${"arGSiteCode".$res["FORUM_ID"]} = array_keys(${"arGSite".$res["FORUM_ID"]});
			}

			$arPathsTmp = array();
			foreach (${"arGSiteCode".$res["FORUM_ID"]} as $val_tmp)
			{
				$arPathsTmp[$val_tmp] = CForumNew::PreparePath2Message(${"arGSite".$res["FORUM_ID"]}[$val_tmp], array("FORUM_ID"=>$res["FORUM_ID"], "TOPIC_ID"=>$res["TID"], "MESSAGE_ID"=>$res["MID"]));
			}

			/* V LID peredavat' ves' massiv, kogda modul' poiska obnovitsya */
			$arResult_tmp = array(
				"ID" => $res["MID"],
				"LID" => $arPathsTmp,
				"DATE_CHANGE" => $res["POST_DATE"],
				"PARAM1" => $res["FORUM_ID"],
				"PARAM2" => $res["TID"],
				"PERMISSIONS" => ${"arGPerm".$res["FORUM_ID"]},
				"TITLE" => $res["TITLE"],
				"TAGS" => ($res["NEW_TOPIC"] == "Y" ? $res["TAGS"] : ""),
				"BODY" => GetMessage("AVTOR_PREF")." ".$res["AUTHOR_NAME"].". ".(textParser::killAllTags($res["POST_MESSAGE"]))
			);

			if (strlen(${"arGSite".$res["FORUM_ID"]}[${"arGSiteCode".$res["FORUM_ID"]}[0]])>0)
			{
				$arResult_tmp["URL"] = CForumNew::PreparePath2Message(${"arGSite".$res["FORUM_ID"]}[${"arGSiteCode".$res["FORUM_ID"]}[0]], array("FORUM_ID"=>$res["FORUM_ID"], "TOPIC_ID"=>$res["TID"], "MESSAGE_ID"=>$res["MID"]));
			}
			else
			{
				if (strlen(${"sPATH2FORUM_MESSAGE".${"arGSiteCode".$res["FORUM_ID"]}[0]})<=0)
				{
					$db_lang = CLang::GetByID(${"arGSiteCode".$res["FORUM_ID"]}[0]);
					${"sPATH2FORUM_MESSAGE".${"arGSiteCode".$res["FORUM_ID"]}[0]} = "/";
					if ($ar_lang = $db_lang->Fetch())
						${"sPATH2FORUM_MESSAGE".${"arGSiteCode".$res["FORUM_ID"]}[0]} = $ar_lang["DIR"];
				}
				$arResult_tmp["URL"] = ${"sPATH2FORUM_MESSAGE".${"arGSiteCode".$res["FORUM_ID"]}[0]}.(COption::GetOptionString("forum", "REL_FPATH", ""))."forum/read.php?FID=".$res["FORUM_ID"]."&TID=".$res["TID"]."&MID=".$res["MID"]."#message".$res["MID"];
			}

			if($oCallback)
			{
				$resCall = call_user_func(array($oCallback, $callback_method), $arResult_tmp);
				if(!$resCall)
					return $arResult_tmp["ID"];
			}
			else
			{
				$arResult[] = $arResult_tmp;
			}
		}

		if ($oCallback && ($search_message_count > 0) && ($rownum>=($search_message_count-1)))
			return $arResult_tmp["ID"];
		if($oCallback)
			return false;

		return $arResult;
	}
	
	function GetListEx($arOrder = Array("SORT"=>"ASC"), $arFilter = Array())
	{
		global $DB;
		$arSqlSearch = array();
		$orSqlSearch = array();
		$arSqlSelect = array();
		$arSqlFrom = array();
		$arSqlOrder = array();
		$strSqlSearch = "";
		$strSqlSelect = "";
		$strSqlSearchOR = "";
		$strSqlFrom = "";
		$strSqlOrder = "";
		
		if (!is_array($arFilter)) 
			$filter_keys = array();
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
					if (strlen($val)>0)
					{
						$arSqlSearch[] = ($strNegative=="Y"?" NOT ":"")."(F2S.SITE_ID ".$strOperation." '".$DB->ForSql($val)."' )";
						$arSqlFrom["F2S"] = "INNER JOIN b_forum2site F2S ON (F2S.FORUM_ID=F.ID)";
					}
					break;
				case "ACTIVE":
				case "XML_ID":
				case "ALLOW_MOVE_TOPIC":
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
						if (strToUpper($val[1]) == "ALLOW_MOVE_TOPIC")
							$arSqlSearch[] = "FP.GROUP_ID IN (".$DB->ForSql($val[0]).") AND ((FP.PERMISSION > 'M') OR (F.ALLOW_MOVE_TOPIC = 'Y'))";
						else
							$arSqlSearch[] = "FP.GROUP_ID IN (".$DB->ForSql($val[0]).") AND FP.PERMISSION > '".$DB->ForSql($val[1])."' ";
						$arSqlFrom["FP"] = "INNER JOIN b_forum_perms FP ON (F.ID = FP.FORUM_ID)";
					}
					break;
				case "APPROVED":
					if (strlen($val)>=0)
					{
						$arSqlFrom["FMM"] = "LEFT JOIN b_forum_message FMM ON (FMM.FORUM_ID=F.ID AND (FMM.APPROVED ".$strOperation." '".$DB->ForSql($val)."'))";
						$arSqlSelect["FMM"] = "count(FMM.ID) mCnt";
					}
					break;
			}
		}

		if (count($arSqlSearch) > 0)
			$strSqlSearch = " AND (".implode(") AND (", $arSqlSearch).") ";
		if (count($orSqlSearch) > 0)
			$strSqlSearchOR = " OR (".implode(") AND (", $orSqlSearch).") ";
		if (count($arSqlSelect) > 0)
			$strSqlSelect = ", ".implode(", ", $arSqlSelect); 
		if (count($arSqlFrom) > 0)
			$strSqlFrom = " ".implode(" ", $arSqlFrom); 
		foreach ($arOrder as $by=>$order)
		{
			$by = strtoupper($by); $order = strtoupper($order);
			if ($order!="ASC") $order = "DESC".($DB->type=="ORACLE"?" NULLS LAST":"");
			else $order = "ASC".($DB->type=="ORACLE"?" NULLS FIRST":"");

			if ($by == "ID") $arSqlOrder[] = " F.ID ".$order." ";
			elseif ($by == "NAME") $arSqlOrder[] = " F.NAME ".$order." ";
			elseif ($by == "ACTIVE") $arSqlOrder[] = " F.ACTIVE ".$order." ";
			elseif ($by == "MODERATION") $arSqlOrder[] = " F.MODERATION ".$order." ";
			elseif ($by == "FORUM_GROUP_ID") $arSqlOrder[] = " F.FORUM_GROUP_ID ".$order." ";
			elseif ($by == "FORUM_GROUP_SORT") $arSqlOrder[] = " FG.SORT ".$order." ";
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
				F.LAST_MESSAGE_ID, FM.TOPIC_ID as TID, F.LAST_MESSAGE_ID as MID, 
				FT.TITLE, F.SORT, '' as DIR, F.ORDER_BY, F.ORDER_DIRECTION, 
				F.ALLOW_HTML, F.ALLOW_ANCHOR, F.ALLOW_BIU, 
				F.ALLOW_IMG, F.ALLOW_LIST, F.ALLOW_QUOTE, F.ALLOW_CODE, 
				F.ALLOW_FONT, F.ALLOW_SMILES, F.ALLOW_UPLOAD, F.EVENT1, F.EVENT2, 
				F.EVENT3, F.ALLOW_NL2BR, '' as PATH2FORUM_MESSAGE, F.ALLOW_UPLOAD_EXT, 
				F.FORUM_GROUP_ID, F.ASK_GUEST_EMAIL, F.USE_CAPTCHA, F.HTML, FT.HTML AS TOPIC_HTML".$strSqlSelect."
			FROM b_forum F 
				LEFT JOIN b_forum_group FG ON F.FORUM_GROUP_ID = FG.ID 
				LEFT JOIN b_forum_message FM ON F.LAST_MESSAGE_ID = FM.ID 
				LEFT JOIN b_forum_topic FT ON FM.TOPIC_ID = FT.ID 
				".$strSqlFrom." 
			WHERE (1=1 ".$strSqlSearch.")
				".$strSqlSearchOR."
			GROUP BY F.ID, F.NAME, F.DESCRIPTION, F.ACTIVE, F.MODERATION, F.ALLOW_MOVE_TOPIC, 
				F.TOPICS, F.POSTS, F.LAST_POSTER_ID, F.LAST_POSTER_NAME, 
				F.LAST_POST_DATE, F.LAST_MESSAGE_ID, F.LAST_MESSAGE_ID, F.SORT, 
				F.ORDER_BY, F.ORDER_DIRECTION, F.ALLOW_HTML, F.ALLOW_ANCHOR, 
				F.ALLOW_BIU, F.ALLOW_IMG, F.ALLOW_LIST, F.ALLOW_QUOTE, 
				F.ALLOW_CODE, F.ALLOW_FONT, F.ALLOW_SMILES, F.ALLOW_UPLOAD, 
				F.EVENT1, F.EVENT2, F.EVENT3, F.ALLOW_NL2BR, 
				F.ALLOW_UPLOAD_EXT, F.FORUM_GROUP_ID, FM.TOPIC_ID, FT.TITLE, 
				FG.SORT, F.ASK_GUEST_EMAIL, F.USE_CAPTCHA, F.HTML, FT.HTML
			".$strSqlOrder;
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if (COption::GetOptionString("forum", "FILTER", "Y") == "N")
			return $db_res;
		$db_res = new _CForumDBResult($db_res);
		return $db_res;
	}
}

/**********************************************************************/
/************** FORUM GROUP *******************************************/
/**********************************************************************/
class CForumGroup extends CAllForumGroup
{
	function Add($arFields)
	{
		global $DB;

		if (!CForumGroup::CheckFields("ADD", $arFields))
			return false;

		$arInsert = $DB->PrepareInsert("b_forum_group", $arFields);

		$strSql =
			"INSERT INTO b_forum_group(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$ID = IntVal($DB->LastID());

		for ($i = 0; $i<count($arFields["LANG"]); $i++)
		{
			$arInsert = $DB->PrepareInsert("b_forum_group_lang", $arFields["LANG"][$i]);
			$strSql =
				"INSERT INTO b_forum_group_lang(FORUM_GROUP_ID, ".$arInsert[0].") ".
				"VALUES(".$ID.", ".$arInsert[1].")";
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $ID;
	}

	function Update($ID, $arFields)
	{
		global $DB;
		$ID = IntVal($ID);
		if ($ID<=0) return False;

		if (!CForumGroup::CheckFields("UPDATE", $arFields))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_forum_group", $arFields);
		$strSql = "UPDATE b_forum_group SET ".$strUpdate." WHERE ID = ".$ID;
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if (is_set($arFields, "LANG"))
		{
			$DB->Query("DELETE FROM b_forum_group_lang WHERE FORUM_GROUP_ID = ".$ID."");

			for ($i = 0; $i<count($arFields["LANG"]); $i++)
			{
				$arInsert = $DB->PrepareInsert("b_forum_group_lang", $arFields["LANG"][$i]);
				$strSql =
					"INSERT INTO b_forum_group_lang(FORUM_GROUP_ID, ".$arInsert[0].") ".
					"VALUES(".$ID.", ".$arInsert[1].")";
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
		}

		return $ID;
	}
}

/**********************************************************************/
/************** FORUM SMILE *******************************************/
/**********************************************************************/
class CForumSmile extends CAllForumSmile
{
	function Add($arFields)
	{
		global $DB;

		if (!CForumSmile::CheckFields("ADD", $arFields))
			return false;

		$arInsert = $DB->PrepareInsert("b_forum_smile", $arFields);

		$strSql =
			"INSERT INTO b_forum_smile(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$ID = IntVal($DB->LastID());

		for ($i = 0; $i<count($arFields["LANG"]); $i++)
		{
			$arInsert = $DB->PrepareInsert("b_forum_smile_lang", $arFields["LANG"][$i]);
			$strSql =
				"INSERT INTO b_forum_smile_lang(SMILE_ID, ".$arInsert[0].") ".
				"VALUES(".$ID.", ".$arInsert[1].")";
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $ID;
	}

	function Update($ID, $arFields)
	{
		global $DB;
		$ID = IntVal($ID);
		if ($ID<=0) return False;

		if (!CForumSmile::CheckFields("UPDATE", $arFields))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_forum_smile", $arFields);
		$strSql = "UPDATE b_forum_smile SET ".$strUpdate." WHERE ID = ".$ID;
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if (is_set($arFields, "LANG"))
		{
			$DB->Query("DELETE FROM b_forum_smile_lang WHERE SMILE_ID = ".$ID."");

			for ($i = 0; $i<count($arFields["LANG"]); $i++)
			{
				$arInsert = $DB->PrepareInsert("b_forum_smile_lang", $arFields["LANG"][$i]);
				$strSql =
					"INSERT INTO b_forum_smile_lang(SMILE_ID, ".$arInsert[0].") ".
					"VALUES(".$ID.", ".$arInsert[1].")";
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
		}

		return $ID;
	}
}
?>