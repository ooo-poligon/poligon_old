<?
/*
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2007 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
*/
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/event.php");

class CEvent extends CAllEvent
{
	function Send($event, $lid, $arFields, $Duplicate = "Y", $message_id="")
	{
		global $DB;
		$flds = "";
		if ($Duplicate!="N") $Duplicate = "Y";

		$events = GetModuleEvents("main", "OnBeforeEventAdd");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEvent($arEvent, &$event, &$lid, &$arFields);

		$keys = array_keys($arFields);
		for($i=0; $i<count($keys); $i++)
			$flds .= "&".CEvent::fieldencode($keys[$i])."=".CEvent::fieldencode($arFields[$keys[$i]]);

		if($flds!="")
			$flds=substr($flds, 1);

		$message_sql = (intval($message_id)<=0) ? "null" : intval($message_id);

		if(is_array($lid))
			$lid = implode(",", $lid);

		$strSql =
			"INSERT INTO b_event(EVENT_NAME, LID, MESSAGE_ID, C_FIELDS, DATE_INSERT, DUPLICATE) ".
			"VALUES('".$DB->ForSQL($event)."', '".$DB->ForSql($lid, 201)."', ".$message_sql.", '".$DB->ForSQL($flds)."', now(), '".$Duplicate."')";

		if(CACHED_b_event!==false)
			@unlink($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/managed_cache/".$DB->type."/b_event");

		$DB->Query($strSql);

		return $DB->LastID();
	}

	function CheckEvents()
	{
		if((defined("DisableEventsCheck") && DisableEventsCheck===true) || (defined("BX_CRONTAB_SUPPORT") && BX_CRONTAB_SUPPORT===true && BX_CRONTAB!==true))
			return;
		global $DB;

		$uniq = COption::GetOptionString("main", "server_uniq_id", "");
		if(strlen($uniq)<=0)
		{
			$uniq = md5(uniqid(rand(), true));
			COption::SetOptionString("main", "server_uniq_id", $uniq);
		}

		if(CACHED_b_event!==false && file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/managed_cache/".$DB->type."/b_event"))
			return "";

		$strSql=
			"SELECT 'x' ".
			"FROM b_event ".
			"WHERE SUCCESS_EXEC='N' ".
			"LIMIT 1";

		$db_result_event = $DB->Query($strSql);
		if($db_result_event->Fetch())
		{
			$db_lock = $DB->Query("SELECT GET_LOCK('".$uniq."_event', 0) as L");
			$ar_lock = $db_lock->Fetch();
			if($ar_lock["L"]=="0")
				return "";
		}
		else
		{
			if(CACHED_b_event!==false)
				@fclose(@fopen($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/managed_cache/".$DB->type."/b_event","w"));
			return "";
		}

		$bulk = intval(COption::GetOptionString("main", "mail_event_bulk", 5));
		if($bulk <= 0)
			$bulk = 5;

		$strSql =
			"SELECT ID, C_FIELDS, EVENT_NAME, MESSAGE_ID, LID, DATE_FORMAT(DATE_INSERT, '%d.%m.%Y %H:%i:%s') as DATE_INSERT, DUPLICATE ".
			"FROM b_event ".
			"WHERE SUCCESS_EXEC='N' ".
			"ORDER BY ID ".
			"LIMIT ".$bulk;
		$db_result_event = $DB->Query($strSql);

		$eol = CEvent::GetMailEOL();
		$cnt=0;
		while($db_result_event_array = $db_result_event->Fetch())
		{
			$ar = CEvent::ExtractMailFields($db_result_event_array["C_FIELDS"]);

			$strSites = $db_result_event_array["LID"];
			$arSites = explode(",", $strSites);
			$strSites = "";
			foreach($arSites as $strSite)
			{
				if($strSites!="")
					$strSites .= ",";
				$strSites .= "'".$DB->ForSql($strSite, 2)."'";
			}

			$strSql = "SELECT CHARSET FROM b_lang WHERE LID IN (".$strSites.") ORDER BY DEF DESC, SORT";
			$dbCharset = $DB->Query($strSql, false, "FILE: ".__FILE__."<br>LINE: ".__LINE__);
			$arCharset = $dbCharset->Fetch();
			$charset = $arCharset["CHARSET"];

			$strWhere = "";
			$MESSAGE_ID = intval($db_result_event_array["MESSAGE_ID"]);
			if($MESSAGE_ID>0)
			{
				$strSql = "SELECT 'x' FROM b_event_message M WHERE M.ID=$MESSAGE_ID";
				$z = $DB->Query($strSql);
				if ($z->Fetch()) $strWhere = "WHERE M.ID=$MESSAGE_ID and M.ACTIVE='Y'";
			}

			$strSql =
					"SELECT DISTINCT ID, SUBJECT, MESSAGE, EMAIL_FROM, EMAIL_TO, BODY_TYPE, BCC ".
					"FROM b_event_message M ".
					($strWhere==""?
						", b_event_message_site MS ".
						"WHERE M.ID=MS.EVENT_MESSAGE_ID ".
						"	AND M.ACTIVE='Y' ".
						"	AND M.EVENT_NAME='".$DB->ForSql($db_result_event_array["EVENT_NAME"])."' ".
						"	AND MS.SITE_ID IN (".$strSites.") "
					:
						$strWhere
					);

			$db_mail_result = $DB->Query($strSql);

			$bSuccess=false;
			$bFail=false;
			$bWas=false;
			while($db_mail_result_array = $db_mail_result->Fetch())
			{
				$strSqlMLid =
					"SELECT MS.SITE_ID ".
					"FROM b_event_message_site MS ".
					"WHERE MS.EVENT_MESSAGE_ID = ".$db_mail_result_array["ID"]."  ".
					"	AND MS.SITE_ID IN (".$strSites.")";

				$dbr_mlid = $DB->Query($strSqlMLid);
				if($ar_mlid = $dbr_mlid->Fetch())
					$arFields = $ar + CEvent::GetSiteFieldsArray($ar_mlid["SITE_ID"]);
				else
					$arFields = $ar + CEvent::GetSiteFieldsArray(false);

				$events = GetModuleEvents("main", "OnBeforeEventSend");
				while ($arEvent = $events->Fetch())
					ExecuteModuleEvent($arEvent, &$arFields, &$db_mail_result_array);

				$email_from = CEvent::ReplaceTemplate($db_mail_result_array["EMAIL_FROM"], $arFields);
				$email_to = CEvent::ReplaceTemplate($db_mail_result_array["EMAIL_TO"], $arFields);
				$message = CEvent::ReplaceTemplate($db_mail_result_array["MESSAGE"], $arFields);
				$subject = CEvent::ReplaceTemplate($db_mail_result_array["SUBJECT"], $arFields);
				$bcc = CEvent::ReplaceTemplate($db_mail_result_array["BCC"], $arFields);

				$email_from = Trim($email_from, "\r\n");
				$email_to = Trim($email_to, "\r\n");
				$subject = Trim($subject, "\r\n");
				$bcc = Trim($bcc, "\r\n");

				if(COption::GetOptionString("main", "convert_mail_header", "Y")=="Y")
				{
					$email_from = CEvent::EncodeMimeString($email_from, $charset);
					$email_to = CEvent::EncodeMimeString($email_to, $charset);
					$subject = CEvent::EncodeMimeString($subject, $charset);
				}

				//если есть желающие получать всю почту, добавим их...
				if ($db_result_event_array["DUPLICATE"]=="Y")
				{
					$all_bcc = COption::GetOptionString("main", "all_bcc", "");
					$bcc .= (strlen($all_bcc)>0?(strlen($bcc)>0?",":"").$all_bcc:"");
				}

				if(COption::GetOptionString("main", "send_mid", "N")=="Y")
					$message .= ($db_mail_result_array["BODY_TYPE"]=="html"?"<br><br>":"\n\n")."MID #".$db_result_event_array["ID"].".".$db_mail_result_array["ID"]." (".$db_result_event_array["DATE_INSERT"].")\n";

				$message = str_replace("\r\n", "\n", $message);//удалить эту строку при возникновении проблем с новыми строками в письмах

				if (COption::GetOptionString("main", "CONVERT_UNIX_NEWLINE_2_WINDOWS", "N")=="Y")
					$message = str_replace("\n", "\r\n", $message);

				$header = "";
				if(COption::GetOptionString("main", "fill_to_mail", "N")=="Y")
					$header = "To: $email_to".$eol;

				$header=
					"From: $email_from".$eol.
					$header.
					"Reply-To: $email_from".$eol.
					"X-Priority: 3 (Normal)".$eol.
					"X-MID: ".$db_result_event_array["ID"].".".$db_mail_result_array["ID"]." (".$db_result_event_array["DATE_INSERT"].")".$eol.
					"X-EVENT_NAME: ".$db_result_event_array["EVENT_NAME"].$eol.
					(strpos($bcc, "@")!==false?"BCC:$bcc".$eol:"").
					($db_mail_result_array["BODY_TYPE"]=="html"
					?
						"Content-Type: text/html; charset=".$charset.$eol
					:
						"Content-Type: text/plain; charset=".$charset.$eol
					).
					"Content-Transfer-Encoding: 8bit";
/*
echo "header = ".$header."\n";
echo "email_to = ".$email_to."\n";
echo "subject = ".$subject."\n";
echo "message = ".$message."\n";
*/
				if(defined("ONLY_EMAIL") && $email_to!=ONLY_EMAIL)
					$bSuccess=true;
				elseif(@mail($email_to, $subject, $message, $header))
					$bSuccess=true;
				else
					$bFail=true;

				$bWas=true;
			}

			/*
			'0' - нет шаблонов (не нужно было ничего отправлять)
			'Y' - все отправлены
			'F' - все не смогли быть отправлены
			'P' - частично отправлены
			*/
			$DB->Query("UPDATE b_event SET DATE_EXEC = now(), SUCCESS_EXEC = '".($bWas?($bSuccess && $bFail?'P':($bFail?'F':'Y')):'0')."' WHERE ID = ".$db_result_event_array["ID"]);

			$cnt++;
			if($cnt > $bulk)
				break;
		}
		//$DB->UnLockTables();
		$DB->Query("SELECT RELEASE_LOCK('".$uniq."_event')");
	}

	function CleanUpAgent()
	{
		global $DB;
		$period = abs(intval(COption::GetOptionString("main", "mail_event_period", 14)));
		$DB->Query("DELETE FROM b_event WHERE DATE_EXEC <= DATE_ADD(now(), INTERVAL -".$period." DAY)");
		return "CEvent::CleanUpAgent();";
	}
}

///////////////////////////////////////////////////////////////////
//Класс шаблонов сообщений
///////////////////////////////////////////////////////////////////
class CEventMessage extends CAllEventMessage
{
	function err_mess()
	{
		return "<br>Class: CEventMessage<br>File: ".__FILE__;
	}

	function GetList(&$by, &$order, $arFilter=Array())
	{
		$err_mess = (CEventMessage::err_mess())."<br>Function: GetList<br>Line: ";
		global $DB, $USER;
		$arSqlSearch = Array();
		$strSqlSearch = "";
		$bIsLang = false;
		if (is_array($arFilter))
		{
			foreach ($arFilter as $key => $val)
			{
				if(is_array($val))
				{
					if(count($val) <= 0)
						continue;
				}
				else
				{
					if( (strlen($val) <= 0) || ($val === "NOT_REF") )
						continue;
				}
				$match_value_set = array_key_exists($key."_EXACT_MATCH", $arFilter);
				$key = strtoupper($key);
				switch($key)
				{
				case "ID":
					$arSqlSearch[] = GetFilterQuery("M.ID",$val,"N");
					break;
				case "TYPE":
					$arSqlSearch[] = GetFilterQuery("M.EVENT_NAME, T.NAME",$val);
					break;
				case "EVENT_NAME":
				case "TYPE_ID":
					$arSqlSearch[] = GetFilterQuery("M.EVENT_NAME",$val,"N");
					break;
				case "TIMESTAMP_1":
					$arSqlSearch[] = "M.TIMESTAMP_X >= FROM_UNIXTIME('".MkDateTime(FmtDate($val,"D.M.Y"),"d.m.Y")."')";
					break;
				case "TIMESTAMP_2":
					$arSqlSearch[] = "M.TIMESTAMP_X <= FROM_UNIXTIME('".MkDateTime(FmtDate($val,"D.M.Y")." 23:59:59","d.m.Y")."')";
					break;
				case "LID":
				case "LANG":
				case "SITE_ID":
					if (is_array($val)) $val = implode(" | ",$val);
					$arSqlSearch[] = GetFilterQuery("MS.SITE_ID",$val,"N");
					$bIsLang = true;
					break;
				case "ACTIVE":
					$arSqlSearch[] = ($val=="Y") ? "M.ACTIVE = 'Y'" : "M.ACTIVE = 'N'";
					break;
				case "FROM":
					$arSqlSearch[] = GetFilterQuery("M.EMAIL_FROM", $val);
					break;
				case "TO":
					$arSqlSearch[] = GetFilterQuery("M.EMAIL_TO", $val);
					break;
				case "BCC":
					$arSqlSearch[] = GetFilterQuery("M.BCC", $val);
					break;
				case "SUBJECT":
					$arSqlSearch[] = GetFilterQuery("M.SUBJECT", $val);
					break;
				case "BODY_TYPE":
					$arSqlSearch[] = ($val=="text") ? "M.BODY_TYPE = 'text'" : "M.BODY_TYPE = 'html'";
					break;
				case "BODY":
					$arSqlSearch[] = GetFilterQuery("M.MESSAGE", $val);
					break;
				}
			}
		}

		if ($by == "id")							$strSqlOrder = " ORDER BY M.ID ";
		elseif ($by == "active")					$strSqlOrder = " ORDER BY M.ACTIVE ";
		elseif ($by == "event_name")				$strSqlOrder = " ORDER BY M.EVENT_NAME ";
		elseif ($by == "to")						$strSqlOrder = " ORDER BY M.TO ";
		elseif ($by == "bcc")						$strSqlOrder = " ORDER BY M.BCC ";
		elseif ($by == "body_type")					$strSqlOrder = " ORDER BY M.BODY_TYPE ";
		elseif ($by == "lid" || $by == "site_id")	$strSqlOrder = " ORDER BY M.LID ";
		elseif ($by == "subject")					$strSqlOrder = " ORDER BY M.SUBJECT ";
		else
		{
			$strSqlOrder = " ORDER BY M.ID ";
			$by = "id";
		}

		if ($order!="asc")
		{
			$strSqlOrder .= " desc ";
			$order = "desc";
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql =
			"SELECT M.ID, M.EVENT_NAME, M.ACTIVE, M.LID, M.LID as SITE_ID, M.EMAIL_FROM, M.EMAIL_TO, M.SUBJECT, M.MESSAGE, M.BODY_TYPE, M.BCC, ".
				$DB->DateToCharFunction("M.TIMESTAMP_X").
			" TIMESTAMP_X,	if(T.ID is null, M.EVENT_NAME, concat('[ ',T.EVENT_NAME,' ] ',ifnull(T.NAME,'')))	EVENT_TYPE ".
			"FROM b_event_message M ".
			($bIsLang?" LEFT JOIN b_event_message_site MS ON (M.ID = MS.EVENT_MESSAGE_ID)":"")." ".
			"	LEFT JOIN b_event_type T ON (T.EVENT_NAME = M.EVENT_NAME and T.LID = '".LANGUAGE_ID."') ".
			"WHERE ".
			$strSqlSearch.
			$strSqlOrder;

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		$res->is_filtered = (IsFiltered($strSqlSearch));
		return $res;
	}
}

class CEventType extends CAllEventType
{
	function Add($arFields)
	{
		global $DB;

		if(!is_set($arFields, "LID") && is_set($arFields, "SITE_ID"))
			$arFields["LID"] = $arFields["SITE_ID"];

		if (CEventType::CheckFields($arFields))
		{
			$arInsert = $DB->PrepareInsert("b_event_type", $arFields);

			$strSql =
				"INSERT INTO b_event_type(".$arInsert[0].") ".
				"VALUES(".$arInsert[1].")";

			$DB->Query($strSql);
			return $DB->LastID();
		}
		return false;
	}
}
?>
