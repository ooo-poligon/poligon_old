<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/classes/general/posting.php");

class CPosting extends CPostingGeneral
{
	function GetList($aSort=Array(), $arFilter=Array())
	{
		global $DB;
		$this->LAST_ERROR = "";
		$arSqlSearch = Array();
		$arSqlSearch_h = Array();
		$strSqlSearch = "";
		if (is_array($arFilter))
		{
			foreach($arFilter as $key=>$val)
			{
				if (strlen($val)<=0 || $val=="NOT_REF") continue;
				switch(strtoupper($key))
				{
				case "ID":
					$arSqlSearch[] = GetFilterQuery("P.ID",$val,"N");
					break;
				case "TIMESTAMP_1":
					if($DB->IsDate($val))
						$arSqlSearch[] = "P.TIMESTAMP_X>=".$DB->CharToDateFunction($val, "SHORT");
					else
						$this->LAST_ERROR .= GetMessage("POST_WRONG_TIMESTAMP_FROM")."<br>";
					break;
				case "TIMESTAMP_2":
					if($DB->IsDate($val))
						$arSqlSearch[] = "P.TIMESTAMP_X<DATE_ADD(".$DB->CharToDateFunction($val, "SHORT").",INTERVAL 1 DAY)";
					else
						$this->LAST_ERROR .= GetMessage("POST_WRONG_TIMESTAMP_TILL")."<br>";
					break;
				case "DATE_SENT_1":
					if($DB->IsDate($val))
						$arSqlSearch[] = "P.DATE_SENT>=".$DB->CharToDateFunction($val, "SHORT");
					else
						$this->LAST_ERROR .= GetMessage("POST_WRONG_DATE_SENT_FROM")."<br>";
					break;
				case "DATE_SENT_2":
					if($DB->IsDate($val))
						$arSqlSearch[] = "P.DATE_SENT<DATE_ADD(".$DB->CharToDateFunction($val, "SHORT").",INTERVAL 1 DAY)";
					else
						$this->LAST_ERROR .= GetMessage("POST_WRONG_DATE_SENT_TILL")."<br>";
					break;
				case "STATUS":
					$arSqlSearch_h[] = GetFilterQuery("STATUS_TITLE, P.STATUS",$val);
					break;
				case "STATUS_ID":
					$arSqlSearch[] = GetFilterQuery("P.STATUS",$val,"N");
					break;
				case "SUBJECT":
					$arSqlSearch[] = GetFilterQuery("P.SUBJECT",$val);
					break;
				case "FROM":
					$arSqlSearch[] = GetFilterQuery("P.FROM_FIELD",$val,"Y",array("@","_","."));
					break;
				case "TO":
					$arSqlSearch[] = GetFilterQuery("P.SENT_BCC",$val,"Y",array("@","_","."));
					break;
				case "BODY_TYPE":
					$arSqlSearch[] = ($val=="html") ? "P.BODY_TYPE='html'" : "P.BODY_TYPE='text'";
					break;
				case "BODY":
					$arSqlSearch[] = GetFilterQuery("P.BODY",$val);
					break;
				case "AUTO_SEND_TIME_1":
					if($DB->IsDate($val, false, false, "FULL"))
						$arSqlSearch[] = "(P.AUTO_SEND_TIME is not null and P.AUTO_SEND_TIME>=".$DB->CharToDateFunction($val, "FULL")." )";
					elseif($DB->IsDate($val, false, false, "SHORT"))
						$arSqlSearch[] = "(P.AUTO_SEND_TIME is not null and P.AUTO_SEND_TIME>=".$DB->CharToDateFunction($val, "SHORT")." )";
					else
						$this->LAST_ERROR .= GetMessage("POST_WRONG_AUTO_FROM")."<br>";
					break;
				case "AUTO_SEND_TIME_2":
					if($DB->IsDate($val, false, false, "FULL"))
						$arSqlSearch[] = "(P.AUTO_SEND_TIME is not null and P.AUTO_SEND_TIME<=".$DB->CharToDateFunction($val, "FULL")." )";
					elseif($DB->IsDate($val, false, false, "SHORT"))
						$arSqlSearch[] = "(P.AUTO_SEND_TIME is not null and P.AUTO_SEND_TIME<=".$DB->CharToDateFunction($val, "SHORT")." )";
					else
						$this->LAST_ERROR .= GetMessage("POST_WRONG_AUTO_TILL")."<br>";
					break;
				}
			}
		}
		$sOrder = "";
		$sort_keys = array_keys($aSort);
		for($i=0; $i<count($sort_keys); $i++)
		{
			$ord = (strtoupper($aSort[$sort_keys[$i]]) <> "ASC"? "DESC":"ASC");
			switch(strtoupper($sort_keys[$i]))
			{
				case "ID":		$sOrder .= ", P.ID ".$ord; break;
				case "TIMESTAMP":	$sOrder .= ", P.TIMESTAMP_X ".$ord; break;
				case "SUBJECT":		$sOrder .= ", P.SUBJECT ".$ord; break;
				case "BODY_TYPE":	$sOrder .= ", P.BODY_TYPE ".$ord; break;
				case "STATUS":		$sOrder .= ", P.STATUS ".$ord; break;
				case "DATE_SENT":	$sOrder .= ", P.DATE_SENT ".$ord; break;
				case "AUTO_SEND_TIME":	$sOrder .= ", P.AUTO_SEND_TIME ".$ord; break;
				case "FROM_FIELD":	$sOrder .= ", P.FROM_FIELD ".$ord; break;
				case "TO_FIELD":	$sOrder .= ", P.TO_FIELD ".$ord; break;
			}
		}
		if($sOrder == "")
		{
			$sOrder = "P.ID DESC";
		}
		$strSqlOrder = " ORDER BY ".TrimEx($sOrder,",");

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT
				if(P.STATUS='S','".$DB->ForSql(GetMessage("POST_STATUS_SENT"))."',
				if(P.STATUS='P','".$DB->ForSql(GetMessage("POST_STATUS_PART"))."',
				if(P.STATUS='E','".$DB->ForSql(GetMessage("POST_STATUS_ERROR"))."', 
				if(P.STATUS='W','".$DB->ForSql(GetMessage("POST_STATUS_WAIT"))."', 
				'".$DB->ForSql(GetMessage("POST_STATUS_DRAFT"))."')))) as STATUS_TITLE
				,P.ID
				,P.STATUS
				,P.FROM_FIELD
				,P.TO_FIELD
				,P.EMAIL_FILTER
				,P.SUBJECT
				,P.BODY_TYPE
				,P.DIRECT_SEND
				,P.CHARSET
				,P.SUBSCR_FORMAT
				,".$DB->DateToCharFunction("P.TIMESTAMP_X")."	TIMESTAMP_X
				,".$DB->DateToCharFunction("P.DATE_SENT")."	DATE_SENT
			FROM b_posting P
			WHERE
			".$strSqlSearch."
		";
		if(count($arSqlSearch_h)>0)
		{
			$strSqlSearch_h = GetFilterSqlSearch($arSqlSearch_h);
			$strSql = $strSql." HAVING ".$strSqlSearch_h;
		}
		$strSql.=$strSqlOrder;
//		echo htmlspecialchars($strSql);
		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$res->is_filtered = (IsFiltered($strSqlSearch));
		return $res;
	}

	function Lock($ID=0)
	{
		global $DB;

		$uniq = COption::GetOptionString("main", "server_uniq_id", "");
		if(strlen($uniq)<=0)
		{
			$uniq = md5(uniqid(rand(), true));
			COption::SetOptionString("main", "server_uniq_id", $uniq);
		}
		$db_lock = $DB->Query("SELECT GET_LOCK('".$uniq."_post_".$ID."', 0) as L", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$ar_lock = $db_lock->Fetch();
		if($ar_lock["L"]=="1")
			return true;
		else
			return false;
	}
	function UnLock($ID=0)
	{
		global $DB;

		$uniq = COption::GetOptionString("main", "server_uniq_id", "");
		if(strlen($uniq)>0)
		{
			$db_lock = $DB->Query("SELECT RELEASE_LOCK('".$uniq."_post_".$ID."') as L", false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$ar_lock = $db_lock->Fetch();
			if($ar_lock["L"]=="0")
				return false;
			else
				return true;
		}
	}
}
?>