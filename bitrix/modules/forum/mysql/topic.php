<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/general/topic.php");

class CForumTopic extends CAllForumTopic
{
	function GetList($arOrder = Array("SORT"=>"ASC"), $arFilter = Array(), $bCount = false, $iNum = 0, $arAddParams = array())
	{
		global $DB;
		$arOrder = (is_array($arOrder) ? $arOrder : array());
		$arFilter = (is_array($arFilter) ? $arFilter : array());
		$arAddParams = (is_array($arAddParams) ? $arAddParams : array($arAddParams));
		$arSqlSearch = array();
		$arSqlSelect = array();
		$arSqlGroup = array();
		$arSqlOrder = array();
		$strSqlSearch = "";
		$strSqlSelect = "";
		$strSqlGroup = "";
		$strSqlOrder = "";
		$UseGroup = false;
		$arSqlSelectConst = CForumTopic::GetSelectFields();

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
				case "STATE":
				case "APPROVED":
				case "XML_ID":
					if (strlen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FT.".$key." IS NULL OR LENGTH(FT.".$key.")<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FT.".$key." IS NULL OR NOT ":"")."(FT.".$key." ".$strOperation." '".$DB->ForSql($val)."' )";
					break;
				case "ID":
				case "USER_START_ID":
				case "FORUM_ID":
					if (($strOperation!="IN") && (intVal($val) > 0))
						$arSqlSearch[] = ($strNegative=="Y"?" FT.".$key." IS NULL OR NOT ":"")."(FT.".$key." ".$strOperation." ".intVal($val)." )";
					elseif (($strOperation =="IN") && ((is_array($val) && (array_sum($val) > 0)) || (strlen($val) > 0) ))
					{
						if (is_array($val))
						{
							$val_int = array();
							foreach ($val as $v)
								$val_int[] = intVal($v);
							$val = implode(", ", $val_int);
						}
						$arSqlSearch[] = ($strNegative=="Y"?" NOT ":"")."(FT.".$key." IN (".$DB->ForSql($val).") )";
					}
					else 
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FT.".$key." IS NULL OR FT.".$key."<=0)";
					break;
				case "RENEW_TOPIC":
//					vhodnye parametry tipa array("TID"=>time); 
//					pri TID = 0 peredaetsya FORUM_LAST_VISIT
					$arSqlTemp = array();
					$strSqlTemp = $val[0];
					unset($val[0]);
					if (is_array($val) && !empty($val))
					{
						foreach ($val as $k => $v)
							$arSqlTemp[] = "(FT.ID=".intVal($k).") AND (FT.LAST_POST_DATE > ".$DB->CharToDateFunction($DB->ForSql($v), "FULL").")";
					$arSqlSearch[] = 
						"(FT.ID IN (".$DB->ForSql(implode(", ", array_keys($val))).") AND ((".implode(") OR (", $arSqlTemp).")))
							OR
						(FT.ID NOT IN (".$DB->ForSql(implode(", ", array_keys($val))).") AND (FT.LAST_POST_DATE > ".$DB->CharToDateFunction($DB->ForSql($strSqlTemp), "FULL")."))";
					}
					break;
				case "START_DATE":
				case "LAST_POST_DATE":
					if(strlen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FT.".$key." IS NULL)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FT.".$key." IS NULL OR NOT ":"")."(FT.".$key." ".$strOperation." ".$DB->CharToDateFunction($DB->ForSql($val), "FULL").")";
					break;
			}
		}
		if (count($arSqlSearch)>0)
			$strSqlSearch = " AND (".implode(") AND (", $arSqlSearch).")";
		if (count($arSqlSelect) > 0)
			$strSqlSelect = ", ".implode(", ", $arSqlSelect);
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
		foreach ($arOrder as $by=>$order)
		{
			$by = strtoupper($by); $order = strtoupper($order);
			if ($order!="ASC") $order = "DESC";

			if ($by == "ID") $arSqlOrder[] = " FT.ID ".$order." ";
			elseif ($by == "TITLE") $arSqlOrder[] = " FT.TITLE ".$order." ";
			elseif ($by == "STATE") $arSqlOrder[] = " FT.STATE ".$order." ";
			elseif ($by == "USER_START_ID") $arSqlOrder[] = " FT.USER_START_ID ".$order." ";
			elseif ($by == "USER_START_NAME") $arSqlOrder[] = " FT.USER_START_NAME ".$order." ";
			elseif ($by == "START_DATE") $arSqlOrder[] = " FT.START_DATE ".$order." ";
			elseif ($by == "POSTS") $arSqlOrder[] = " FT.POSTS ".$order." ";
			elseif ($by == "VIEWS") $arSqlOrder[] = " FT.VIEWS ".$order." ";
			elseif ($by == "APPROVED") $arSqlOrder[] = " FT.APPROVED ".$order." ";
			elseif ($by == "LAST_POST_DATE") $arSqlOrder[] = " FT.LAST_POST_DATE ".$order." ";
			elseif ($by == "FORUM_ID") $arSqlOrder[] = " FT.FORUM_ID ".$order." ";
			else
			{
				$arSqlOrder[] = " FT.SORT ".$order." ";
				$by = "SORT";
			}
		}
		DelDuplicateSort($arSqlOrder); 
		if (count($arSqlOrder) > 0)
			$strSqlOrder = " ORDER BY ".implode(", ", $arSqlOrder);

		if ($bCount || (is_array($arAddParams) && is_set($arAddParams, "bDescPageNumbering") && (intVal($arAddParams["nTopCount"])<=0)))
		{
			$strSql = 
				"SELECT COUNT(FT.ID) as CNT 
				FROM b_forum_topic FT
				WHERE 1 = 1 
				".$strSqlSearch;
			$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$iCnt = 0;
			if ($ar_res = $db_res->Fetch())
			{
				$iCnt = intVal($ar_res["CNT"]);
			}
			if ($bCount)
				return $iCnt;
		}

		$strSql = 
			"SELECT FT.ID, FT.TITLE, FT.TAGS, FT.DESCRIPTION, FT.VIEWS, FT.LAST_POSTER_ID, FT.HTML, 
				".$DB->DateToCharFunction("FT.START_DATE", "FULL")." as START_DATE, 
				FT.USER_START_NAME, FT.USER_START_ID, FT.POSTS, FT.LAST_POSTER_NAME, 
				".$DB->DateToCharFunction("FT.LAST_POST_DATE", "FULL")." as LAST_POST_DATE, 
				FT.LAST_MESSAGE_ID, FT.APPROVED, FT.STATE, FT.FORUM_ID, FT.TOPIC_ID, FT.ICON_ID, 
				FT.SORT".$strSqlSelect."
			FROM b_forum_topic FT 
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
		
		if ((COption::GetOptionString("forum", "FILTER", "Y") == "N") || 
			((is_array($arAddParams)) && (is_set($arAddParams, 'NoFilter') && ($arAddParams['NoFilter'] == true))))
			return $db_res;
		$db_res = new _CTopicDBResult($db_res);
		return $db_res;
	}

	function GetListEx($arOrder = Array("SORT"=>"ASC"), $arFilter = Array(), $bCount = false, $iNum = 0, $arAddParams = array())
	{
		global $DB, $USER;
		$arOrder = (is_array($arOrder) ? $arOrder : array());
		$arFilter = (is_array($arFilter) ? $arFilter : array());
		$arSqlSearch = array();
		$arSqlFrom = array();
		$arSqlSelect = array();
		$arSqlGroup = array();
		$arSqlOrder = array();
		$strSqlSearch = "";
		$strSqlFrom = "";
		$strSqlSelect = "";
		$strSqlGroup = "";
		$strSqlOrder = "";
		$UseGroup = false;
		$arSqlSelectConst = CForumTopic::GetSelectFields();
		$arSqlSelectConst = array_merge(
			$arSqlSelectConst, 
			array(
				"LAST_POST_DATE_ORIGINAL" => "FT.LAST_POST_DATE",
				"FT.LAST_MESSAGE_ID" => "FT.LAST_MESSAGE_ID",
				"FS.IMAGE" => "FS.IMAGE",
				"FT.APPROVED" => "FT.APPROVED",
				"FT.STATE" => "FT.STATE",
				"FT.FORUM_ID" => "FT.FORUM_ID",
				"FT.TOPIC_ID" => "FT.TOPIC_ID",
				"FT.ICON_ID" => "FT.ICON_ID",
				"FT.SORT" => "FT.SORT",
				"FORUM_NAME" => "F.NAME"));

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
				case "STATE":
				case "XML_ID":
				case "APPROVED":
					if (strlen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FT.".$key." IS NULL OR LENGTH(FT.".$key.")<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FT.".$key." IS NULL OR NOT ":"")."(FT.".$key." ".$strOperation." '".$DB->ForSql($val)."' )";
					break;
				case "ID":
				case "FORUM_ID":
				case "USER_START_ID":
					if (($strOperation!="IN")&&(intVal($val)>0))
						$arSqlSearch[] = ($strNegative=="Y"?" FT.".$key." IS NULL OR NOT ":"")."(FT.".$key." ".$strOperation." ".intVal($val)." )";
					elseif (($strOperation =="IN") && ((is_array($val) && (array_sum($val) > 0)) || (strlen($val) > 0) ))
					{
						if (is_array($val))
						{
							$val_int = array();
							foreach ($val as $v)
								$val_int[] = intVal($v);
							$val = implode(", ", $val_int);
						}
						$arSqlSearch[] = ($strNegative=="Y"?" NOT ":"")."(FT.".$key." IN (".$DB->ForSql($val).") )";
					}
					else 
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FT.".$key." IS NULL OR FT.".$key."<=0)";
					break;
				case "TITLE_ALL":
					$arSqlSearch[] = GetFilterQuery("FT.TITLE, FT.DESCRIPTION", $val);
					break;
				case "TITLE":
				case "DESCRIPTION":
					$arSqlSearch[] = GetFilterQuery("FT.".$key, $val);
					$arSqlSearch[] = GetFilterQuery("FT.".$key, $val);
					break;
				case "START_DATE":
				case "LAST_POST_DATE":
					if(strlen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FT.".$key." IS NULL)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FT.".$key." IS NULL OR NOT ":"")."(FT.".$key." ".$strOperation." ".$DB->CharToDateFunction($DB->ForSql($val), "FULL").")";
					break;
				case "USER_ID":
					$arSqlSelect["LAST_VISIT"] = $DB->DateToCharFunction("FUT.LAST_VISIT", "FULL");
					if(strlen($val)<=0)
						$arSqlFrom["FUT"] = " LEFT JOIN b_forum_user_topic FUT ON ((FT.ID = FUT.TOPIC_ID AND ".($strNegative=="Y"?"NOT":"")."(FUT.USER_ID IS NULL))";
					else
					{
						$arSqlFrom["FUT"] = "
						 LEFT JOIN b_forum_user_topic FUT ON (FT.ID = FUT.TOPIC_ID AND FUT.USER_ID=".intVal($val).")";
					}
					break;
				case "RENEW_TOPIC":
						if ((strlen($val)>0) && key_exists("FUT", $arSqlFrom))
						{
							$arSqlSearch[] = "
								((FT.LAST_POST_DATE ".$strOperation." ".$DB->CharToDateFunction($DB->ForSql($val), "FULL").") AND 
									(
										(LAST_VISIT IS NULL) OR
										(LAST_VISIT < ".$DB->CharToDateFunction($DB->ForSql($val), "FULL").")
									)
								)
								OR
								((FT.LAST_POST_DATE > FUT.LAST_VISIT) AND 
									(
										(LAST_VISIT IS NOT NULL) AND
										(LAST_VISIT > ".$DB->CharToDateFunction($DB->ForSql($val), "FULL").")
									)
								)";
						}
				break;
				case "PERMISSION_STRONG":
					$arSqlFrom["FP"] = "LEFT JOIN b_forum_perms FP ON (FP.FORUM_ID=FT.FORUM_ID)";
					$arSqlSearch[] = "FP.GROUP_ID IN (".$DB->ForSql(implode(",", $USER->GetUserGroupArray())).") AND (FP.PERMISSION IN ('Q','U','Y'))"; 
					$UseGroup = true;
					break;
			}
		}
		if (count($arSqlSearch)>0)
			$strSqlSearch = " AND (".implode(") AND (", $arSqlSearch).")";
		if (count($arSqlSelect) > 0)
		{
			$res = array();
			foreach ($arSqlSelect as $key => $val)
			{
				if (substr($key, 0, 1) == "!")
					$key = substr($key, 1);
				if ($key != $val)
					$res[] = $val." AS ".$key;
				else 
					$res[] = $val;
			}
			$strSqlSelect = ", ".implode(", ", $res);
		}
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

		foreach ($arOrder as $by => $order)
		{
			$by = strtoupper($by); $order = strtoupper($order);
			
			if ($order!="ASC") $order = "DESC";

			if ($by == "ID") $arSqlOrder[] = " FT.ID ".$order." ";
			elseif ($by == "TITLE") $arSqlOrder[] = " FT.TITLE ".$order." ";
			elseif ($by == "STATE") $arSqlOrder[] = " FT.STATE ".$order." ";
			elseif ($by == "USER_START_ID") $arSqlOrder[] = " FT.USER_START_ID ".$order." ";
			elseif ($by == "USER_START_NAME") $arSqlOrder[] = " FT.USER_START_NAME ".$order." ";
			elseif ($by == "START_DATE") $arSqlOrder[] = " FT.START_DATE ".$order." ";
			elseif ($by == "POSTS") $arSqlOrder[] = " FT.POSTS ".$order." ";
			elseif ($by == "VIEWS") $arSqlOrder[] = " FT.VIEWS ".$order." ";
			elseif ($by == "APPROVED") $arSqlOrder[] = " FT.APPROVED ".$order." ";
			elseif ($by == "LAST_POST_DATE") $arSqlOrder[] = " FT.LAST_POST_DATE ".$order." ";
			elseif ($by == "FORUM_ID") $arSqlOrder[] = " FT.FORUM_ID ".$order." ";
			elseif ($by == "FORUM_NAME") $arSqlOrder[] = " F_T.FORUM_NAME ".$order." ";
			else
			{
				$arSqlOrder[] = " FT.SORT ".$order." ";
				$by = "SORT";
			}
		}
		DelDuplicateSort($arSqlOrder); 
		if (count($arSqlOrder) > 0)
			$strSqlOrder = " ORDER BY ".implode(", ", $arSqlOrder);
		
		if ($bCount || (is_array($arAddParams) && is_set($arAddParams, "bDescPageNumbering") && (intVal($arAddParams["nTopCount"])<=0)))
		{
			$strSql = 
				"SELECT COUNT(FT.ID) as CNT 
				FROM b_forum_topic FT
				WHERE 1 = 1 
				".$strSqlSearch;
			$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$iCnt = 0;
			if ($ar_res = $db_res->Fetch())
			{
				$iCnt = intVal($ar_res["CNT"]);
			}
			if ($bCount)
				return $iCnt;
		}

		$strSql = 
			"SELECT FT.ID, FT.TITLE, FT.TAGS, FT.DESCRIPTION, FT.VIEWS, FT.LAST_POSTER_ID, FT.HTML, 
				".$DB->DateToCharFunction("FT.START_DATE", "FULL")." as START_DATE, 
				FT.USER_START_NAME, FT.USER_START_ID, FT.POSTS, FT.LAST_POSTER_NAME, 
				".$DB->DateToCharFunction("FT.LAST_POST_DATE", "FULL")." as LAST_POST_DATE, 
				FT.LAST_POST_DATE AS LAST_POST_DATE_ORIGINAL,
				FT.LAST_MESSAGE_ID, FS.IMAGE, '' as IMAGE_DESCR, 
				FT.APPROVED, FT.STATE, FT.FORUM_ID, FT.TOPIC_ID, FT.ICON_ID, FT.SORT,
				F.NAME as FORUM_NAME".$strSqlSelect."
			FROM b_forum_topic FT 
				LEFT JOIN b_forum F ON (FT.FORUM_ID = F.ID) 
				LEFT JOIN b_forum_smile FS ON (FT.ICON_ID = FS.ID) 
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
		
		if ((COption::GetOptionString("forum", "FILTER", "Y") == "N") || 
			((is_array($arAddParams)) && (is_set($arAddParams, 'NoFilter') && ($arAddParams['NoFilter'] == true))))
			return $db_res;
		$db_res = new _CTopicDBResult($db_res);
		return $db_res;
	}
}
?>