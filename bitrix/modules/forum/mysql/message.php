<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/general/message.php");

class CForumMessage extends CAllForumMessage
{
	function Add($arFields, $strUploadDir = false, $arParams = array())
	{
		global $DB;
		
		if ($strUploadDir===false) $strUploadDir = "forum";

		if (!CForumMessage::CheckFields("ADD", $arFields))
			return false;

		$arParams["SKIP_STATISTIC"] = ($arParams["SKIP_STATISTIC"] == "Y" ? "Y" : "N");
		$arParams["SKIP_INDEXING"] = ($arParams["SKIP_INDEXING"] == "Y" ? "Y" : "N");

		if (is_set($arFields, "POST_MESSAGE"))
		{
			$POST_MESSAGE_HTML = false;
			$arForum = CForumNew::GetByID($arFields["FORUM_ID"]);
			$POST_MESSAGE_HTML = $arFields["POST_MESSAGE"];
			$parser = new textParser(LANGUAGE_ID);
			$allow = array(
					"HTML" => $arForum["ALLOW_HTML"],
					"ANCHOR" => $arForum["ALLOW_ANCHOR"],
					"BIU" => $arForum["ALLOW_BIU"],
					"IMG" => $arForum["ALLOW_IMG"],
					"LIST" => $arForum["ALLOW_LIST"],
					"QUOTE" => $arForum["ALLOW_QUOTE"],
					"CODE" => $arForum["ALLOW_CODE"],
					"FONT" => $arForum["ALLOW_FONT"],
					"SMILES" => ($arFields["USE_SMILES"]!="Y") ? "N" : $arForum["ALLOW_SMILES"],
					"UPLOAD" => $arForum["ALLOW_UPLOAD"],
					"NL2BR" => $arForum["ALLOW_NL2BR"]
					);
			if (COption::GetOptionString("forum", "FILTER", "Y") == "Y")
			{
				$POST_MESSAGE_HTML = CFilterUnquotableWords::Filter($POST_MESSAGE_HTML);
				$arFields["POST_MESSAGE_FILTER"] = $POST_MESSAGE_HTML;
			}
			if (COption::GetOptionString("forum", "MESSAGE_HTML", "Y") == "Y")
				$POST_MESSAGE_HTML = $parser->convert($POST_MESSAGE_HTML, $allow);
			$arFields["POST_MESSAGE_HTML"] = $POST_MESSAGE_HTML;
		}

		if (is_array($arFields["ATTACH_IMG"]))
		{
			$arFields["ATTACH_IMG"]["MODULE_ID"] = "forum";
			$res = CFile::SaveFile($arFields["ATTACH_IMG"], $strUploadDir, true, true);
			if($res!==false && strlen($strUploadDir)>0)
				$arFields["ATTACH_IMG"] = $res;
			else 
				unset($arFields["ATTACH_IMG"]);
		}
		
		$arInsert = $DB->PrepareInsert("b_forum_message", $arFields, $strUploadDir);

		$strDatePostField = "";
		$strDatePostValue = "";
		if (!is_set($arFields, "POST_DATE"))
		{
			$strDatePostField = ", POST_DATE";
			$strDatePostValue = ", ".$DB->GetNowFunction()."";
		}

		$strSql =
			"INSERT INTO b_forum_message(".$arInsert[0].$strDatePostField.") ".
			"VALUES(".$arInsert[1].$strDatePostValue.")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$ID = intVal($DB->LastID());

// *********************************** QUOTA ********************************************************
		$_SESSION["SESS_RECOUNT_DB"] = "Y"; 
// *********************************** QUOTA ********************************************************
		if ($arParams["SKIP_STATISTIC"] == "Y" && $arParams["SKIP_INDEXING"] == "Y")
			return $ID;

		$arMessage = CForumMessage::GetByID($ID);

		if ($arParams["SKIP_STATISTIC"] != "Y" && (is_set($arFields, "APPROVED") && $arFields["APPROVED"]=="Y" || $arMessage["APPROVED"]=="Y"))
		{
			if (is_set($arFields, "AUTHOR_ID") && intVal($arFields["AUTHOR_ID"])>0)
			{
				CForumUser::SetStat($arFields["AUTHOR_ID"]);
			}
			CForumTopic::SetStat($arFields["TOPIC_ID"]);
			CForumNew::SetStat($arFields["FORUM_ID"]);
		}

		if ($arParams["SKIP_INDEXING"] != "Y" && CModule::IncludeModule("search"))
		{
			if (is_set($arFields, "APPROVED") && $arFields["APPROVED"]=="Y" || $arMessage["APPROVED"]=="Y")
			{
				// Reindex
				$arForum = CForumNew::GetByID($arMessage["FORUM_ID"]);
				$arTopic = CForumTopic::GetByID($arMessage["TOPIC_ID"]);
				$arGroups = CForumNew::GetAccessPermissions($arMessage["FORUM_ID"]);
				$arGPerm = Array();
				for ($i = 0; $i < count($arGroups); $i++)
				{
					if ($arGroups[$i][1]>="E")
					{
						$arGPerm[] = $arGroups[$i][0];
						if ($arGroups[$i][0]==2) break;
					}
				}
				$arForumSite_tmp = CForumNew::GetSites($arMessage["FORUM_ID"]);
				$arForumSiteCode_tmp = array_keys($arForumSite_tmp);
				foreach ($arForumSiteCode_tmp as $arForumSiteCode_tmp_val)
				{
					$arForumSite_tmp[$arForumSiteCode_tmp_val] = CForumNew::PreparePath2Message($arForumSite_tmp[$arForumSiteCode_tmp_val], array("FORUM_ID"=>$arMessage["FORUM_ID"], "TOPIC_ID"=>$arMessage["TOPIC_ID"], "MESSAGE_ID"=>$arMessage["ID"]));
				}

				/* V LID peredavat' ves' massiv, kogda modul' poiska obnovitsya */
				$arSearchInd = array(
					"SITE_ID" => $arForumSite_tmp,
					"LAST_MODIFIED" => $arMessage["POST_DATE"],
					"PARAM1" => $arMessage["FORUM_ID"],
					"PARAM2" => $arMessage["TOPIC_ID"],
					"PERMISSIONS" => $arGPerm,
					"TITLE" => $arTopic["TITLE"],
					"TAGS" => (($arMessage["NEW_TOPIC"] == "Y") ? $arTopic["TAGS"] : ""),
					"BODY" => GetMessage("AVTOR_PREF")." ".$arMessage["AUTHOR_NAME"].". ".(textParser::killAllTags($arMessage["POST_MESSAGE"]))
					);

				if (strlen($arForumSite_tmp[$arForumSiteCode_tmp[0]])>0)
				{
					$arSearchInd["URL"] = CForumNew::PreparePath2Message($arForumSite_tmp[$arForumSiteCode_tmp[0]], array("FORUM_ID"=>$arMessage["FORUM_ID"], "TOPIC_ID"=>$arMessage["TOPIC_ID"], "MESSAGE_ID"=>$arMessage["ID"]));
				}
				else
				{
					$db_lang = CLang::GetByID($arForumSiteCode_tmp[0]);
					$lpath = "/";
					if ($ar_lang = $db_lang->Fetch()) $lpath = $ar_lang["DIR"];
					$arSearchInd["URL"] = $lpath.(COption::GetOptionString("forum", "REL_FPATH", ""))."forum/read.php?FID=".$arMessage["FORUM_ID"]."&TID=".$arMessage["TOPIC_ID"]."&MID=".$arMessage["ID"]."#message".$arMessage["ID"];
				}
				CSearch::Index("forum", $ID, $arSearchInd);
			}
		}
		return $ID;
	}

	function Update($ID, $arFields, $skip_counts = false, $strUploadDir = false)
	{
		global $DB;
		$ID = intVal($ID);
		$strDatePost = "";
		
		if (!CForumMessage::CheckFields("UPDATE", $arFields, $ID) || empty($arFields))
			return false;
		
		if ($strUploadDir===false) $strUploadDir = "forum";

		if ((!$skip_counts) || (CModule::IncludeModule("search")))
		{
			$arMessage_prev = CForumMessage::GetByID($ID);
		}

		if 	(is_set($arFields, "POST_MESSAGE") || is_set($arFields, "FORUM_ID"))
		{
			$arFields["POST_MESSAGE_HTML"] = '';
			$arFields["POST_MESSAGE_FILTER"] = '';
		}
		
		if (is_set($arFields, "POST_DATE") && (strLen(trim($arFields["POST_DATE"])) <= 0))
		{
			$strDatePost = ", POST_DATE=".$DB->GetNowFunction();
			unset($arFields["POST_DATE"]);
		}
		
		if (is_set($arFields, "EDIT_DATE") && (strLen(trim($arFields["EDIT_DATE"])) <= 0))
		{
			$strDatePost = ", EDIT_DATE=".$DB->GetNowFunction();
			unset($arFields["EDIT_DATE"]);
		}
		
		if (is_array($arFields["ATTACH_IMG"]))
		{
			$arFields["ATTACH_IMG"]["MODULE_ID"] = "forum";
			$res = CFile::SaveFile($arFields["ATTACH_IMG"], $strUploadDir, true, true);
			if($res!==false && strlen($strUploadDir)>0)
				$arFields["ATTACH_IMG"] = $res;
			else 
				unset($arFields["ATTACH_IMG"]);
		}

		$strUpdate = $DB->PrepareUpdate("b_forum_message", $arFields, $strUploadDir);
		$strSql = "UPDATE b_forum_message SET ".$strUpdate.$strDatePost." WHERE ID = ".$ID;
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		
// *********************************** QUOTA ********************************************************
		$_SESSION["SESS_RECOUNT_DB"] = "Y"; 
// *********************************** QUOTA ********************************************************
		
		unset($GLOBALS["FORUM_CACHE"]["MESSAGE"][$ID]);
		
		if ((!$skip_counts) || (CModule::IncludeModule("search")))
		{
			$arMessage = CForumMessage::GetByID($ID);
			if (!$skip_counts)
			{
				if (intVal($arMessage["AUTHOR_ID"])>0)
					CForumUser::SetStat($arMessage["AUTHOR_ID"]);
				if (intVal($arMessage_prev["AUTHOR_ID"])>0 && intVal($arMessage_prev["AUTHOR_ID"])!=intVal($arMessage["AUTHOR_ID"]))
					CForumUser::SetStat($arMessage_prev["AUTHOR_ID"]);
				if (intVal($arMessage_prev["TOPIC_ID"])>0 && intVal($arMessage_prev["TOPIC_ID"])!=intVal($arMessage["TOPIC_ID"]))
					CForumTopic::SetStat($arMessage_prev["TOPIC_ID"]);
				CForumTopic::SetStat($arMessage["TOPIC_ID"]);
				if (intVal($arMessage_prev["FORUM_ID"])>0 && intVal($arMessage_prev["FORUM_ID"])!=intVal($arMessage["FORUM_ID"]))
					CForumNew::SetStat($arMessage_prev["FORUM_ID"]);
				CForumNew::SetStat($arMessage["FORUM_ID"]);
			}
			
			if (CModule::IncludeModule("search"))
			{
				if (is_set($arFields, "APPROVED") && $arFields["APPROVED"]=="N")
				{
					// Delete index
					CSearch::Index("forum", $ID,
						array(
							"TITLE"=>"",
							"TAGS"=>"",
							"BODY"=>""
						)
					);
				}
				elseif (is_set($arFields, "APPROVED") && $arFields["APPROVED"]=="Y" || $arMessage["APPROVED"]=="Y")
				{
					// Reindex
					$arForum = CForumNew::GetByID($arMessage["FORUM_ID"]);
					$arTopic = CForumTopic::GetByID($arMessage["TOPIC_ID"]);
					$arGroups = CForumNew::GetAccessPermissions($arMessage["FORUM_ID"]);
					$arGPerm = Array();
					for ($i = 0; $i < count($arGroups); $i++)
					{
						if ($arGroups[$i][1]>="E")
						{
							$arGPerm[] = $arGroups[$i][0];
							if ($arGroups[$i][0]==2) break;
						}
					}
					$arForumSite_tmp = CForumNew::GetSites($arMessage["FORUM_ID"]);
					$arForumSiteCode_tmp = array_keys($arForumSite_tmp);
					foreach ($arForumSiteCode_tmp as $arForumSiteCode_tmp_val)
					{
						$arForumSite_tmp[$arForumSiteCode_tmp_val] = CForumNew::PreparePath2Message($arForumSite_tmp[$arForumSiteCode_tmp_val], array("FORUM_ID"=>$arMessage["FORUM_ID"], "TOPIC_ID"=>$arMessage["TOPIC_ID"], "MESSAGE_ID"=>$arMessage["ID"]));
					}
	
					/* V LID peredavat' ves' massiv, kogda modul' poiska obnovitsya */
					$arSearchInd = array(
						"LID" => $arForumSite_tmp,
						"LAST_MODIFIED" => $arMessage["POST_DATE"],
						"PARAM1" => $arMessage["FORUM_ID"],
						"PARAM2" => $arMessage["TOPIC_ID"],
						"PERMISSIONS" => $arGPerm,
						"TITLE" => $arTopic["TITLE"],
						"TAGS" => ($arMessage["NEW_TOPIC"] == "Y" ? $arTopic["TAGS"] : ""),
						"BODY" => GetMessage("AVTOR_PREF")." ".$arMessage["AUTHOR_NAME"].". ".(textParser::killAllTags($arMessage["POST_MESSAGE"]))
						);
	
					if (strlen($arForumSite_tmp[$arForumSiteCode_tmp[0]])>0)
					{
						$arSearchInd["URL"] = CForumNew::PreparePath2Message($arForumSite_tmp[$arForumSiteCode_tmp[0]], array("FORUM_ID"=>$arMessage["FORUM_ID"], "TOPIC_ID"=>$arMessage["TOPIC_ID"], "MESSAGE_ID"=>$arMessage["ID"]));
					}
					else
					{
						$db_lang = CLang::GetByID($arForumSiteCode_tmp[0]);
						$lpath = "/";
						if ($ar_lang = $db_lang->Fetch()) $lpath = $ar_lang["DIR"];
						$arSearchInd["URL"] = $lpath.(COption::GetOptionString("forum", "REL_FPATH", ""))."forum/read.php?FID=".$arMessage["FORUM_ID"]."&TID=".$arMessage["TOPIC_ID"]."&MID=".$arMessage["ID"]."#message".$arMessage["ID"];
					}
					
					CSearch::Index("forum", $ID, $arSearchInd, True);
				}
			}
		}

		return $ID;
	}
	
	function GetList($arOrder = Array("ID"=>"ASC"), $arFilter = Array(), $bCount = false, $iNum = 0, $arAddParams = array())
	{
		global $DB;
		$arSqlSearch = array();
		$arSqlOrder = array();
		$strSqlSearch = "";
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
				case "PARAM1":
				case "AUTHOR_NAME":
				case "POST_MESSAGE_CHECK":
				case "APPROVED":
					if (strlen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FM.".$key." IS NULL OR LENGTH(FM.".$key.")<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FM.".$key." IS NULL OR NOT ":"")."(FM.".$key." ".$strOperation." '".$DB->ForSql($val)."' )";
					break;
				case "PARAM2":
				case "ID":
				case "AUTHOR_ID":
				case "FORUM_ID":
				case "TOPIC_ID":
				case "ATTACH_IMG":
					if (($strOperation!="IN") && (intVal($val) > 0))
						$arSqlSearch[] = ($strNegative=="Y"?" FM.".$key." IS NULL OR NOT ":"")."(FM.".$key." ".$strOperation." ".intVal($val)." )";
					elseif (($strOperation =="IN") && ((is_array($val) && (array_sum($val) > 0)) || (strlen($val) > 0) ))
					{
						if (is_array($val))
						{
							$val_int = array();
							foreach ($val as $v)
								$val_int[] = intVal($v);
							$val = implode(", ", $val_int);
						}
						$arSqlSearch[] = ($strNegative=="Y"?" NOT ":"")."(FM.".$key." IN (".$DB->ForSql($val).") )";
					}
					else 
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FM.".$key." IS NULL OR FM.".$key."<=0)";
					break;
				case "EDIT_DATE":
				case "POST_DATE":
					if (strlen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FM.".$key." IS NULL OR LENGTH(FM.".$key.")<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FM.".$key." IS NULL OR NOT ":"")."(FM.".$key." ".$strOperation." ".$DB->CharToDateFunction($DB->ForSql($val), "FULL")." )";
					break;
				case "PERMISSION":
					if ((is_array($val)) && (count($val)>0))
					{
						$return = array();
						foreach ($val as $value)
						{
							$str = array();
							foreach ($value as $k => $v)
							{
								$k_res = CForumNew::GetFilterOperation($k);
								$k = strToUpper($k_res["FIELD"]);
								$strNegative = $k_res["NEGATIVE"];
								$strOperation = $k_res["OPERATION"];
								switch ($k)
								{
									case "TOPIC_ID":
									case "FORUM_ID":
										if (intVal($v)<=0)
											$str[] = ($strNegative=="Y"?"NOT":"")."(FM.".$k." IS NULL OR FM.".$k."<=0)";
										else
											$str[] = ($strNegative=="Y"?" FM.".$k." IS NULL OR NOT ":"")."(FM.".$k." ".$strOperation." ".intVal($v)." )";
										break;
									case "APPROVED":
										if (strlen($v)<=0)
											$str[] = ($strNegative=="Y"?"NOT":"")."(FM.APPROVED IS NULL OR LENGTH(FM.APPROVED)<=0)";
										else
											$str[] = ($strNegative=="Y"?" FM.APPROVED IS NULL OR NOT ":"")."FM.APPROVED ".$strOperation." '".$DB->ForSql($v)."' ";
										break;
								}
							}
							$return[] = implode(" AND ", $str);
						}
						if (count($return)>0)
							$arSqlSearch[] = "(".implode(") OR (", $return).")";
					}
					break;
			}
		}
		if (count($arSqlSearch) > 0)
			$strSqlSearch = " AND (".implode(") AND (", $arSqlSearch).") ";
			
		if ($bCount || (is_array($arAddParams) && is_set($arAddParams, "bDescPageNumbering") && (intVal($arAddParams["nTopCount"])<=0)))
		{
			if ($bCount === "cnt_not_approved")
			{
				$strSql = 
					"SELECT 
						COUNT(FM.ID) as CNT, 
						SUM(CASE WHEN FM.APPROVED!='Y' THEN 1 ELSE 0 END) as CNT_NOT_APPROVED
					FROM b_forum_message FM 
					WHERE 1 = 1 
					".$strSqlSearch;
				$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				if ($ar_res = $db_res->Fetch())
					return $ar_res;
			}
			else
			{
				$strSql = 
					"SELECT 
						COUNT(FM.ID) as CNT 
					FROM b_forum_message FM 
					WHERE 1 = 1 
					".$strSqlSearch;
				$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$iCnt = 0;
				if ($ar_res = $db_res->Fetch())
					$iCnt = intVal($ar_res["CNT"]);
				if ($bCount)
					return $iCnt;
			}
		}

		foreach ($arOrder as $by=>$order)
		{
			$by = strtoupper($by); $order = strtoupper($order);
			if ($order!="ASC") $order = "DESC";
			if ($by == "AUTHOR_NAME") $arSqlOrder[] = " FM.AUTHOR_NAME ".$order." ";
			elseif ($by == "EDIT_DATE") $arSqlOrder[] = " FM.EDIT_DATE ".$order." ";
			elseif ($by == "POST_DATE") $arSqlOrder[] = " FM.POST_DATE ".$order." ";
			elseif ($by == "FORUM_ID") $arSqlOrder[] = " FM.FORUM_ID ".$order." ";
			elseif ($by == "TOPIC_ID") $arSqlOrder[] = " FM.TOPIC_ID ".$order." ";
			elseif ($by == "NEW_TOPIC") $arSqlOrder[] = " FM.NEW_TOPIC ".$order." ";
			elseif ($by == "APPROVED") $arSqlOrder[] = " FM.APPROVED ".$order." ";
			else
			{
				$arSqlOrder[] = " FM.ID ".$order." ";
				$by = "ID";
			}
		}
		DelDuplicateSort($arSqlOrder);
		if(count($arSqlOrder) > 0)
			$strSqlOrder = " ORDER BY ".implode(", ", $arSqlOrder);
		
		$strSql = 
			"SELECT FM.ID, 
				FM.AUTHOR_ID, FM.AUTHOR_NAME, FM.AUTHOR_EMAIL, FM.AUTHOR_IP, 
				FM.USE_SMILES, FM.POST_MESSAGE, FM.POST_MESSAGE_HTML, FM.POST_MESSAGE_FILTER,
				FM.FORUM_ID, FM.TOPIC_ID, FM.ATTACH_HITS, FM.ATTACH_TYPE, FM.ATTACH_FILE, FM.NEW_TOPIC,
				FM.APPROVED, FM.POST_MESSAGE_CHECK, FM.GUEST_ID, FM.AUTHOR_REAL_IP, FM.ATTACH_IMG, FM.XML_ID, 
				".$DB->DateToCharFunction("FM.POST_DATE", "FULL")." as POST_DATE,
				FM.EDITOR_ID, FM.EDITOR_NAME, FM.EDITOR_EMAIL, FM.EDIT_REASON,
				".$DB->DateToCharFunction("FM.EDIT_DATE", "FULL")." as EDIT_DATE, FM.PARAM1, FM.PARAM2
			FROM b_forum_message FM 
			WHERE 1 = 1 
			".$strSqlSearch."
			".$strSqlOrder;

		$iNum = intVal($iNum);
		if (($iNum>0) || (is_array($arAddParams) && (intVal($arAddParams["nTopCount"])>0)))
		{
			$iNum = ($iNum > 0) ? $iNum : intVal($arAddParams["nTopCount"]);
			$strSql .= " LIMIT 0,".$iNum;
		}
		
		if (!$iNum && is_array($arAddParams) && is_set($arAddParams, "bDescPageNumbering") && (intVal($arAddParams["nTopCount"])<=0))
		{
			$db_res =  new CDBResult();
			$db_res->NavQuery($strSql, $iCnt, $arAddParams);
		}
		else 
		{
			$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		
		if ((COption::GetOptionString("forum", "MESSAGE_HTML", "Y") == "N") && (COption::GetOptionString("forum", "FILTER", "Y") == "N"))
			return $db_res;
		$db_res = new _CMessageDBResult($db_res);
		return $db_res;
	}

	function GetListEx($arOrder = Array("ID"=>"ASC"), $arFilter = Array(), $bCount = false, $iNum = 0, $arAddParams = array())
	{
		global $DB;
		$arSqlSearch = array();
		$arSqlOrder = array();
		$arSqlFrom = array();
		$arSqlSelect = array();
		$arSqlGroup = array();
		$strSqlSearch = "";
		$strSqlOrder = "";
		$strSqlFrom = "";
		$strSqlSelect = "";
		$strSqlGroup = "";
		$UseGroup = false;
		$UseDistinct = false;
		$arSqlSelectConst = array(
			"FM.ID" => "FM.ID", 
			"FM.AUTHOR_ID" => "FM.AUTHOR_ID", 
			"FM.AUTHOR_NAME" => "FM.AUTHOR_NAME", 
			"FM.AUTHOR_EMAIL" => "FM.AUTHOR_EMAIL", 
			"FM.AUTHOR_IP" => "FM.AUTHOR_IP", 
			"FM.AUTHOR_REAL_IP" => "FM.AUTHOR_REAL_IP", 
			"FM.USE_SMILES" => "FM.USE_SMILES", 
			"FM.POST_MESSAGE" => "FM.POST_MESSAGE", 
			"FM.POST_MESSAGE_HTML" => "FM.POST_MESSAGE_HTML", 
			"FM.POST_MESSAGE_FILTER" => "FM.POST_MESSAGE_FILTER", 
			"FM.APPROVED" => "FM.APPROVED", 
			"FU.SHOW_NAME" => "FU.SHOW_NAME", 
			"FU.DESCRIPTION" => "FU.DESCRIPTION", 
			"FU.NUM_POSTS" => "FU.NUM_POSTS", 
			"FU.SIGNATURE" => "FU.SIGNATURE", 
			"FM.FORUM_ID" => "FM.FORUM_ID", 
			"FM.TOPIC_ID" => "FM.TOPIC_ID", 
			"FM.NEW_TOPIC" => "FM.NEW_TOPIC", 
			"POST_DATE" => $DB->DateToCharFunction("FM.POST_DATE","FULL"), 
			"DATE_REG" => $DB->DateToCharFunction("FU.DATE_REG","SHORT"), 
			"FM.GUEST_ID" => "FM.GUEST_ID", 
			"FU.AVATAR" => "FU.AVATAR", 
			"FM.EDITOR_ID" => "FM.EDITOR_ID", 
			"FM.EDITOR_NAME" => "FM.EDITOR_NAME", 
			"FM.EDITOR_EMAIL" => "FM.EDITOR_EMAIL", 
			"FM.EDIT_REASON" => "FM.EDIT_REASON", 
			"EDIT_DATE" => $DB->DateToCharFunction("FM.EDIT_DATE","FULL"), 
			"U.EMAIL" => "U.EMAIL", 
			"U.PERSONAL_ICQ" => "U.PERSONAL_ICQ", 
			"FM.ATTACH_IMG" => "FM.ATTACH_IMG", 
			"U.LOGIN" => "U.LOGIN", 
			"FU.RANK_ID" => "FU.RANK_ID", 
			"U.PERSONAL_WWW" => "U.PERSONAL_WWW", 
			"U.PERSONAL_GENDER" => "U.PERSONAL_GENDER", 
			"U.PERSONAL_CITY" => "U.PERSONAL_CITY", 
			"U.PERSONAL_COUNTRY" => "U.PERSONAL_COUNTRY",
			"FM.PARAM1" => "FM.PARAM1",
			"FM.PARAM2" => "FM.PARAM2",
		);

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
				case "PARAM1":
				case "AUTHOR_NAME":
				case "POST_MESSAGE_CHECK":
				case "APPROVED":
				case "NEW_TOPIC":
				case "POST_MESSAGE":
					if ($strOperation == "LIKE")
						$val = "%".$val."%";
					if (strlen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FM.".$key." IS NULL OR LENGTH(FM.".$key.")<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FM.".$key." IS NULL OR NOT ":"")."(FM.".$key." ".$strOperation." '".$DB->ForSql($val)."' )";
					break;
				case "PARAM2":
				case "ID":
				case "AUTHOR_ID":
				case "FORUM_ID":
				case "TOPIC_ID":
				case "ATTACH_IMG":
					if (($strOperation!="IN") && (intVal($val) > 0))
						$arSqlSearch[] = ($strNegative=="Y"?" FM.".$key." IS NULL OR NOT ":"")."(FM.".$key." ".$strOperation." ".intVal($val)." )";
					elseif (($strOperation =="IN") && ((is_array($val) && (array_sum($val) > 0)) || (strlen($val) > 0) ))
					{
						if (is_array($val))
						{
							$val_int = array();
							foreach ($val as $v)
								$val_int[] = intVal($v);
							$val = implode(", ", $val_int);
						}
						$arSqlSearch[] = ($strNegative=="Y"?" NOT ":"")."(FM.".$key." IN (".$DB->ForSql($val).") )";
					}
					else 
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FM.".$key." IS NULL OR FM.".$key."<=0)";
					break;
				case "POINTS_TO_AUTHOR_ID":
					if (intVal($val) > 0)
					{
						$arSqlSelect["FR.POINTS"] = "FR.POINTS";
						$arSqlSelect["FR.DATE_UPDATE"] = "FR.DATE_UPDATE";
						$arSqlFrom["FR"] = "LEFT JOIN b_forum_user_points FR ON ((FM.AUTHOR_ID = FR.TO_USER_ID) AND (FR.FROM_USER_ID=".intVal($val)."))";
					}
					break;
				case "POST_DATE":
					if (strlen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FM.".$key." IS NULL OR LENGTH(FM.".$key.")<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FM.".$key." IS NULL OR NOT ":"")."(FM.".$key." ".$strOperation." ".$DB->CharToDateFunction($DB->ForSql($val), "FULL").")";
					break;
				case "USER_ID":
//					$arSqlSelect["LAST_VISIT"] = $DB->DateToCharFunction("FUT.LAST_VISIT", "FULL");
					if(intVal($val) > 0)
					{
						$arSqlFrom["FUT"] = "
						 LEFT JOIN b_forum_user_topic FUT ON (FT.ID = FUT.TOPIC_ID AND FUT.USER_ID=".intVal($val).")";
					}
					break;
				case "NEW_MESSAGE":
						if ((strLen($val) > 0) && (intVal($arFilter["USER_ID"]) > 0))
						{
							$arSqlFrom["FUT"] = "LEFT JOIN b_forum_user_topic FUT ON (FM.TOPIC_ID = FUT.TOPIC_ID AND FUT.USER_ID=".intVal($arFilter["USER_ID"]).")";
							$arSqlSearch[] = "
								((FM.POST_DATE ".$strOperation." ".$DB->CharToDateFunction($DB->ForSql($val), "FULL").") AND 
									(
										(FUT.LAST_VISIT IS NULL) OR
										(FUT.LAST_VISIT < ".$DB->CharToDateFunction($DB->ForSql($val), "FULL").")
									)
								)
								OR
								((FM.POST_DATE > FUT.LAST_VISIT) AND 
									(
										(FUT.LAST_VISIT IS NOT NULL) AND
										(FUT.LAST_VISIT > ".$DB->CharToDateFunction($DB->ForSql($val), "FULL").")
									)
								)";
						}
				break;
				case "USER_GROUP":
					if (!empty($val))
					{
						if (!is_array($val))
							$val = explode(",", $val);
						if (!in_array(2, $val))
							$val[] = 2;
						$val = implode(",", $val);
						$arSqlFrom["FP"] = "LEFT JOIN b_forum_perms FP ON (FP.FORUM_ID=FM.FORUM_ID)";
						$arSqlSearch[] = "FP.GROUP_ID IN (".$DB->ForSql($val).") AND ((FP.PERMISSION IN ('E','I','M') AND FM.APPROVED='Y') OR (FP.PERMISSION IN ('Q','U','Y')))"; 
						$UseDistinct = true;
					}
				break;
				case "TOPIC":
						$arSqlFrom["FT"] = "
							 LEFT JOIN b_forum_topic FT ON (FT.ID = FM.TOPIC_ID)";
						$arSqlSelect[] = "FT.TITLE";
						$arSqlSelect[] = "FT.DESCRIPTION AS TOPIC_DESCRIPTION";
						$arSqlSelect[] = $DB->DateToCharFunction("FT.START_DATE", "FULL")." as START_DATE";
						$arSqlSelect[] = "FT.USER_START_NAME";
						$arSqlSelect[] = "FT.USER_START_ID";
				break;
				case "TOPIC_TITLE":
				case "TITLE":
					$arSqlFrom["FT"] = "
						 LEFT JOIN b_forum_topic FT ON (FT.ID = FM.TOPIC_ID)";
					$key = "TITLE";
					if ($strOperation == "LIKE")
						$val = "%".$val."%";
					if (strlen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FT.".$key." IS NULL OR LENGTH(FT.".$key.")<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FT.".$key." IS NULL OR NOT ":"")."(FT.".$key." ".$strOperation." '".$DB->ForSql($val)."' )";
				break;
			}
		}
		foreach ($arOrder as $by=>$order)
		{
			$by = strtoupper($by); $order = strtoupper($order);
			if ($order!="ASC") $order = "DESC";

			if ($by == "AUTHOR_NAME") $arSqlOrder[] = " FM.AUTHOR_NAME ".$order." ";
			elseif ($by == "POST_DATE") $arSqlOrder[] = " FM.POST_DATE ".$order." ";
			elseif ($by == "FORUM_ID") $arSqlOrder[] = " FM.FORUM_ID ".$order." ";
			elseif ($by == "TOPIC_ID") $arSqlOrder[] = " FM.TOPIC_ID ".$order." ";
			elseif ($by == "NEW_TOPIC") $arSqlOrder[] = " FM.NEW_TOPIC ".$order." ";
			elseif ($by == "APPROVED") $arSqlOrder[] = " FM.APPROVED ".$order." ";
			elseif (($by == "SORT") || ($by == "NAME"))
			{
				$arSqlFrom["F"] = "
				 LEFT JOIN b_forum F ON (F.ID = FM.FORUM_ID)";
				 $arSqlOrder[] = " F.".$by." ".$order." ";
			}
			else
			{
				$arSqlOrder[] = " FM.ID ".$order." ";
				$by = "ID";
			}
		}
		DelDuplicateSort($arSqlOrder); 
		if(count($arSqlOrder) > 0)
			$strSqlOrder = " ORDER BY ".implode(", ", $arSqlOrder);
		
		if (count($arSqlSearch) > 0)
			$strSqlSearch = " AND (".implode(") AND (", $arSqlSearch).") ";
		if (count($arSqlSelect) > 0)
			$strSqlSelect = ", ".implode(", ", $arSqlSelect);
		if (count($arSqlFrom) > 0)
			$strSqlFrom .= implode(" ", $arSqlFrom);
		if ($UseGroup)
		{
			$arSqlGroup = $arSqlSelectConst;
			foreach ($arSqlSelect as $key => $val)
			{
				if (substr($key, 0, 1) != "!")
					$arSqlGroup[$key] = $val;
			}
			$strSqlGroup = " GROUP BY ".implode(", ", $arSqlGroup);
		}
		
		if ($bCount || (is_array($arAddParams) && is_set($arAddParams, "bDescPageNumbering") && (intVal($arAddParams["nTopCount"])<=0)))
		{
			$strSql = 
				"SELECT 
					COUNT(".($UseDistinct ? " DISTINCT " : "")."FM.ID) as CNT, 
					MAX(FM.ID) AS LAST_MESSAGE_ID
				FROM b_forum_message FM 
				".$strSqlFrom."
				WHERE 1 = 1 
				".$strSqlSearch;
				
			if ($bCount === 3)
				$strSql .= "GROUP BY FM.TOPIC_ID";
			$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($bCount === 3)
				return $db_res;
			$iCnt = 0; $iLAST_MESSAGE_ID = 0;
			if ($ar_res = $db_res->Fetch())
			{
				$iCnt = intVal($ar_res["CNT"]);
				$iLAST_MESSAGE_ID = intVal($ar_res["LAST_MESSAGE_ID"]);
			}
			if ($bCount === 4)
				return array("CNT" => $iCnt, "LAST_MESSAGE_ID" => $iLAST_MESSAGE_ID);
				
			if ($bCount)
				return $iCnt;
		}
		
		
		$strSql = 
			"SELECT ".($UseDistinct ? " DISTINCT " : "")." FM.ID, FM.AUTHOR_ID, FM.AUTHOR_NAME, FM.AUTHOR_EMAIL, 
				FM.AUTHOR_IP, FM.AUTHOR_REAL_IP, FM.USE_SMILES, FM.POST_MESSAGE, FM.POST_MESSAGE_HTML, FM.POST_MESSAGE_FILTER, 
				FM.APPROVED, FU.SHOW_NAME, FU.DESCRIPTION, FU.NUM_POSTS, 
				FU.SIGNATURE, FM.FORUM_ID, FM.TOPIC_ID, FM.NEW_TOPIC, 
				".$DB->DateToCharFunction("FM.POST_DATE", "FULL")." as POST_DATE, 
				".$DB->DateToCharFunction("FU.DATE_REG", "SHORT")." as DATE_REG, 
				FM.GUEST_ID, FU.AVATAR, U.EMAIL, U.PERSONAL_ICQ, FM.ATTACH_IMG, 
				U.LOGIN, FU.RANK_ID, U.PERSONAL_WWW, U.PERSONAL_GENDER, 
				U.PERSONAL_CITY, U.PERSONAL_COUNTRY, 
				FM.EDITOR_ID, FM.EDITOR_NAME, FM.EDITOR_EMAIL, FM.EDIT_REASON,
				".$DB->DateToCharFunction("FM.EDIT_DATE", "FULL")." as EDIT_DATE,
				FM.PARAM1, FM.PARAM2".$strSqlSelect." 
			FROM b_forum_message FM 
				LEFT JOIN b_forum_user FU ON FM.AUTHOR_ID = FU.USER_ID 
				LEFT JOIN b_user U ON FM.AUTHOR_ID = U.ID 
				".$strSqlFrom."
			WHERE 1 = 1
			".$strSqlSearch."
			".$strSqlGroup."
			".$strSqlOrder;
		$iNum = intVal($iNum);
		if (($iNum>0) || (is_array($arAddParams) && (intVal($arAddParams["nTopCount"])>0)))
		{
			$iNum = ($iNum > 0) ? $iNum : intVal($arAddParams["nTopCount"]);
			$strSql .= " LIMIT 0,".$iNum;
		}
		if (!$iNum && is_array($arAddParams) && is_set($arAddParams, "bDescPageNumbering") && (intVal($arAddParams["nTopCount"])<=0))
		{
			$db_res =  new CDBResult();
			$db_res->NavQuery($strSql, $iCnt, $arAddParams);
		}
		else 
		{
			$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		if ((COption::GetOptionString("forum", "MESSAGE_HTML", "Y") == "N") && (COption::GetOptionString("forum", "FILTER", "Y") == "N"))
			return $db_res;
		$db_res = new _CMessageDBResult($db_res);
		return $db_res;
	}
	
	function QueryFirstUnread($arFilter) // out-of-date function
	{
		$db_res = CForumMessage::GetList(array("ID"=>"ASC"), $arFilter, false, 1);
		return $db_res;
	}
}
?>