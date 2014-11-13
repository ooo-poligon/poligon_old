<?
IncludeModuleLangFile(__FILE__);

class CPostingGeneral
{
	var $LAST_ERROR="";

	//get by ID
	function GetByID($ID)
	{
		global $DB;
		$ID = intval($ID);

		$strSql = "
			SELECT
				P.*
				,".$DB->DateToCharFunction("P.TIMESTAMP_X", "FULL")." AS TIMESTAMP_X
				,".$DB->DateToCharFunction("P.DATE_SENT", "FULL")." AS DATE_SENT
				,".$DB->DateToCharFunction("P.AUTO_SEND_TIME", "FULL")." AS AUTO_SEND_TIME
			FROM b_posting P
			WHERE P.ID=".$ID."
		";

		return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}

	//list of categories linked with message
	function GetRubricList($ID)
	{
		global $DB;
		$ID = intval($ID);

		$strSql = "
			SELECT
				R.ID
				,R.NAME
				,R.SORT
				,R.LID
				,R.ACTIVE
			FROM
				b_list_rubric R
				,b_posting_rubric PR
			WHERE
				R.ID=PR.LIST_RUBRIC_ID
				AND PR.POSTING_ID=".$ID."
			ORDER BY
				R.LID, R.SORT, R.NAME
		";

		return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}

	//list of user group linked with message
	function GetGroupList($ID)
	{
		global $DB;
		$ID = intval($ID);

		$strSql = "
			SELECT
				G.ID
				,G.NAME
			FROM
				b_group G
				,b_posting_group PG
			WHERE
				G.ID=PG.GROUP_ID
				AND PG.POSTING_ID=".$ID."
			ORDER BY
				G.C_SORT, G.ID
		";

		return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}

	// delete by ID
	function Delete($ID)
	{
		global $DB;
		$ID = intval($ID);

		$DB->StartTransaction();

		CPosting::DeleteFile($ID);

		$res = $DB->Query("DELETE FROM b_posting_rubric WHERE POSTING_ID='".$ID."'", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if($res)
			$res = $DB->Query("DELETE FROM b_posting_group WHERE POSTING_ID='".$ID."' ", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if($res)
			$res = $DB->Query("DELETE FROM b_posting WHERE ID='".$ID."' ", false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if($res)
			$DB->Commit();
		else
			$DB->Rollback();

		return $res;
	}

	function OnGroupDelete($group_id)
	{
		global $DB;
		$group_id = intval($group_id);

		return $DB->Query("DELETE FROM b_posting_group WHERE GROUP_ID=".$group_id, true);
	}

	function DeleteFile($ID, $file_id=false)
	{
		global $DB;

		$rsFile = CPosting::GetFileList($ID, $file_id);
		while($arFile = $rsFile->Fetch())
		{
			$rs = $DB->Query("DELETE FROM b_posting_file where POSTING_ID=".intval($ID)." AND FILE_ID=".intval($arFile["ID"]), false, "File: ".__FILE__."<br>Line: ".__LINE__);
			CFile::Delete(intval($arFile["ID"]));
		}
	}

	function SplitFileName($file_name)
	{
		$found = array();
		// exapmle(2).txt
		if(preg_match("/^(.*)\((\d+?)\)(\..+?)$/", $file_name, $found))
		{
			$fname = $found[1];
			$fext = $found[3];
			$index = $found[2];
		}
		// example(2)
		elseif(preg_match("/^(.*)\((\d+?)\)$/", $file_name, $found))
		{
			$fname = $found[1];
			$fext = "";
			$index = $found[2];
		}
		// example.txt
		elseif(preg_match("/^(.*)(\..+?)$/", $file_name, $found))
		{
			$fname = $found[1];
			$fext = $found[2];
			$index = 0;
		}
		// example
		else
		{
			$fname = $file_name;
			$fext = "";
			$index = 0;
		}
		return array($fname, $fext, $index);
	}

	function SaveFile($ID, $file)
	{
		global $DB;
		$ID = intval($ID);

		$arFileName = CPosting::SplitFileName($file["name"]);
		//Check if file with this name already exists
		$arSameNames = array();
		$rsFile = CPosting::GetFileList($ID);
		while($arFile = $rsFile->Fetch())
		{
			$arSavedName = CPosting::SplitFileName($arFile["ORIGINAL_NAME"]);
			if($arFileName[0] == $arSavedName[0] && $arFileName[1] == $arSavedName[1])
				$arSameNames[$arSavedName[2]] = true;
		}
		while(array_key_exists($arFileName[2], $arSameNames))
		{
			$arFileName[2]++;
		}
		if($arFileName[2] > 0)
		{
			$file["name"] = $arFileName[0]."(".($arFileName[2]).")".$arFileName[1];
		}
		//save file
		$file["MODULE_ID"] = "subscribe";
		$fid = intval(CFile::SaveFile($file, "subscribe", true, true));
		if(($fid > 0) && $DB->Query("INSERT INTO b_posting_file (POSTING_ID, FILE_ID) VALUES (".$ID." ,".$fid.")", false, "File: ".__FILE__."<br>Line: ".__LINE__))
		{
			return true;
		}
		else
		{
			$this->LAST_ERROR = GetMessage("class_post_err_att");
			return false;
		}
	}

	function GetFileList($ID, $file_id=false)
	{
		global $DB;
		$ID = intval($ID);
		$file_id = intval($file_id);

		$strSql = "
			SELECT
				F.ID
				,F.FILE_SIZE
				,F.ORIGINAL_NAME
				,F.SUBDIR
				,F.FILE_NAME
				,F.CONTENT_TYPE
			FROM
				b_file F
				,b_posting_file PF
			WHERE
				F.ID=PF.FILE_ID
				AND PF.POSTING_ID=".$ID."
			".($file_id>0?"AND PF.FILE_ID = ".$file_id:"")."
			ORDER BY F.ID
		";

		return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}

	//check fields before writing
	function CheckFields($arFields, $ID)
	{
		global $DB;
		$this->LAST_ERROR = "";
		$aMsg = array();

		if(is_set($arFields, "FROM_FIELD") && (strlen($arFields["FROM_FIELD"])<3 || !check_email($arFields["FROM_FIELD"])))
			$aMsg[] = array("id"=>"FROM_FIELD", "text"=>GetMessage("class_post_err_email"));
		if(!is_set($arFields, "DIRECT_SEND") || $arFields["DIRECT_SEND"]=="N")
			if(is_set($arFields, "TO_FIELD") && strlen($arFields["TO_FIELD"])<=0)
				$aMsg[] = array("id"=>"TO_FIELD", "text"=>GetMessage("class_post_err_to"));
		if(is_set($arFields, "SUBJECT") && strlen($arFields["SUBJECT"])<=0)
			$aMsg[] = array("id"=>"SUBJECT", "text"=>GetMessage("class_post_err_subj"));
		if(is_set($arFields, "BODY") && strlen($arFields["BODY"])<=0)
			$aMsg[] = array("id"=>"BODY", "text"=>GetMessage("class_post_err_text"));
		if(is_set($arFields, "AUTO_SEND_TIME") && $arFields["AUTO_SEND_TIME"]!==false && $DB->IsDate($arFields["AUTO_SEND_TIME"], false, false, "FULL")!==true)
			$aMsg[] = array("id"=>"AUTO_SEND_TIME", "text"=>GetMessage("class_post_err_auto_time"));

		if(!empty($aMsg))
		{
			$e = new CAdminException($aMsg);
			$GLOBALS["APPLICATION"]->ThrowException($e);
			$this->LAST_ERROR = $e->GetString();
			return false;
		}

		return true;
	}

	//relation with categories
	function UpdateRubrics($ID, $aRubric)
	{
		global $DB;
		$ID = intval($ID);

		$DB->Query("DELETE FROM b_posting_rubric WHERE POSTING_ID=".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$arID = array();
		if(is_array($aRubric))
			foreach($aRubric as $i)
				$arID[] = intval($i);
		if(count($arID)>0)
			$DB->Query("
				INSERT INTO b_posting_rubric (POSTING_ID, LIST_RUBRIC_ID)
				SELECT ".$ID.", ID
				FROM b_list_rubric
				WHERE ID IN (".implode(", ",$arID).")
				", false, "File: ".__FILE__."<br>Line: ".__LINE__
			);
	}

	//relation with user groups
	function UpdateGroups($ID, $aGroup)
	{
		global $DB;
		$ID = intval($ID);

		$DB->Query("DELETE FROM b_posting_group WHERE POSTING_ID='".$ID."'", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$arID = array();
		if(is_array($aGroup))
			foreach($aGroup as $i)
				$arID[] = intval($i);
		if(count($arID)>0)
			$DB->Query("
				INSERT INTO b_posting_group (POSTING_ID, GROUP_ID)
				SELECT ".$ID.", ID
				FROM b_group
				WHERE ID IN (".implode(", ",$arID).")
				", false, "File: ".__FILE__."<br>Line: ".__LINE__
			);
	}

	//Addition
	function Add($arFields)
	{
		global $DB;

		if(!$this->CheckFields($arFields, 0))
			return false;

		$ID = $DB->Add("b_posting", $arFields, Array("SENT_BCC","BCC_FIELD","BODY","ERROR_EMAIL"));
		if($ID > 0)
		{
			$this->UpdateRubrics($ID, $arFields["RUB_ID"]);
			$this->UpdateGroups($ID, $arFields["GROUP_ID"]);
		}
		return $ID;
	}

	//Update
	function Update($ID, $arFields)
	{
		global $DB, $USER;
		$ID = intval($ID);

		if(!$this->CheckFields($arFields, $ID))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_posting", $arFields);
		if($strUpdate!="")
		{
			$strSql = "UPDATE b_posting SET ".$strUpdate." WHERE ID=".$ID;
			$arBinds=Array(
				"BCC_FIELD"=>$arFields["BCC_FIELD"],
				"SENT_BCC"=>$arFields["SENT_BCC"],
				"BODY"=>$arFields["BODY"],
				"ERROR_EMAIL"=>$arFields["ERROR_EMAIL"],
				"BCC_TO_SEND"=>$arFields["BCC_TO_SEND"]
			);
			if(!$DB->QueryBind($strSql, $arBinds))
				return false;
		}
		if(is_set($arFields, "RUB_ID"))
			$this->UpdateRubrics($ID, $arFields["RUB_ID"]);
		if(is_set($arFields, "GROUP_ID"))
			$this->UpdateGroups($ID, $arFields["GROUP_ID"]);

		return true;
	}

	function GetEmails($post_arr)
	{
		$aEmail = array();

		//send to categories
		$aPostRub = array();
		$post_rub = CPostingGeneral::GetRubricList($post_arr["ID"]);
		while($post_rub_arr = $post_rub->Fetch())
			$aPostRub[] = $post_rub_arr["ID"];

		$subscr = CSubscription::GetList(
			array("ID"=>"ASC"),
			array("RUBRIC_MULTI"=>$aPostRub, "CONFIRMED"=>"Y", "ACTIVE"=>"Y",
				"FORMAT"=>$post_arr["SUBSCR_FORMAT"], "EMAIL"=>$post_arr["EMAIL_FILTER"])
		);
		while(($subscr_arr = $subscr->Fetch()))
			$aEmail[] = $subscr_arr["EMAIL"];

		//send to user groups
		$aPostGrp = array();
		$post_grp = CPostingGeneral::GetGroupList($post_arr["ID"]);
		while($post_grp_arr = $post_grp->Fetch())
			$aPostGrp[] = $post_grp_arr["ID"];

		if(count($aPostGrp)>0)
		{
			$user = CUser::GetList(
				($b="id"), ($o="asc"),
				array("GROUP_MULTI"=>$aPostGrp, "ACTIVE"=>"Y", "EMAIL"=>$post_arr["EMAIL_FILTER"])
			);
			while(($user_arr = $user->Fetch()))
				$aEmail[] = $user_arr["EMAIL"];
		}

		//from additional emails
		$BCC = $post_arr["BCC_FIELD"];
		if($post_arr["DIRECT_SEND"] == "Y")
			$BCC .= ($BCC <> ""? ",":"").$post_arr["TO_FIELD"];
		if($BCC <> "")
		{
			$BCC = str_replace("\r\n", "\n", $BCC);
			$BCC = str_replace("\n", ",", Trim($BCC));
			$aBcc = explode(",", $BCC);
			for($i=0; $i<count($aBcc); $i++)
				if(Trim($aBcc[$i]) <> "")
					$aEmail[] = Trim($aBcc[$i]);
		}

		$aEmail = array_unique($aEmail);

		return $aEmail;
	}

	function AutoSend($ID=false, $limit=false, $site_id=false)
	{
		//email count for one hit
		global $subscribe_current_emails_per_hit;
		$subscribe_current_emails_per_hit=intval($subscribe_current_emails_per_hit);

		if($ID===false)
		{
			//Here is cron job entry
			$cPosting = new CPosting;
			$rsPosts = CPosting::GetList(array("AUTO_SEND_TIME"=>"ASC", "ID"=>"ASC"), array("STATUS_ID"=>"P", "AUTO_SEND_TIME_2"=>ConvertTimeStamp(false, "FULL")));
			while($arPosts=$rsPosts->Fetch())
			{
				if($limit===true)
				{
					$maxcount = COption::GetOptionInt("subscribe", "subscribe_max_emails_per_hit") - $subscribe_current_emails_per_hit;
					if($maxcount <= 0)
						break;
				}
				else
				{
					$maxcount = 0;
				}
				$res = $cPosting->SendMessage($arPosts["ID"], 0, $maxcount);
			}
		}
		else
		{
			if($site_id && $site_id != SITE_ID)
			{
				return "CPosting::AutoSend(".$ID.($limit? ",true": ",false").",\"".$site_id."\");";
			}

			//Here is agent entry
			if($limit===true)
			{
 				$maxcount = COption::GetOptionInt("subscribe", "subscribe_max_emails_per_hit") - $subscribe_current_emails_per_hit;
				if($maxcount <= 0)
					return "CPosting::AutoSend(".$ID.",true".($site_id? ",\"".$site_id."\"": "").");";
			}
			else
			{
				$maxcount = 0;
			}

			$cPosting = new CPosting;
			$res = $cPosting->SendMessage($ID, COption::GetOptionString("subscribe", "posting_interval"), $maxcount);
			if($res=="CONTINUE")
				return "CPosting::AutoSend(".$ID.($limit? ",true": ",false").($site_id?",\"".$site_id."\"":"").");";
		}
		return "";
	}

	//Send message
	function SendMessage($ID, $timeout=0, $maxcount=0)
	{
		global $DB, $APPLICATION;
		global $subscribe_current_emails_per_hit;
		$eol = CEvent::GetMailEOL();
		$ID = intval($ID);
		$timeout = intval($timeout);
		$start_time = getmicrotime();

		@set_time_limit(0);
		$this->LAST_ERROR = "";

		$post = $this->GetByID($ID);
		if(!($post_arr = $post->Fetch()))
		{
			$this->LAST_ERROR .= GetMessage("class_post_err_notfound");
			return false;
		}
		if($post_arr["STATUS"] != "P")
		{
			$this->LAST_ERROR .= GetMessage("class_post_err_status")."<br>";
			return false;
		}
		if(CPosting::Lock($ID)===false)
		{
			if($e = $APPLICATION->GetException())
			{
				$this->LAST_ERROR .= GetMessage("class_post_err_lock")."<br>".$e->GetString();
				if(strpos($this->LAST_ERROR, "PLS-00201") !== false && strpos($this->LAST_ERROR, "'DBMS_LOCK'") !== false)
					$this->LAST_ERROR .= "<br>".GetMessage("class_post_err_lock_advice");
				$APPLICATION->ResetException();
				return false;
			}
			else
			{
				return "CONTINUE";
			}
		}

		if(is_string($post_arr["BCC_TO_SEND"]) && strlen($post_arr["BCC_TO_SEND"])>0)
			$aEmail = explode(",", $post_arr["BCC_TO_SEND"]);
		else
			$aEmail = array();
		if(is_string($post_arr["ERROR_EMAIL"]) && strlen($post_arr["ERROR_EMAIL"])>0)
			$aError =  explode(",", $post_arr["ERROR_EMAIL"]);
		else
			$aError = array();
		if(is_string($post_arr["SENT_BCC"]) && strlen($post_arr["SENT_BCC"])>0)
			$aSent =  explode(",", $post_arr["SENT_BCC"]);
		else
			$aSent = array();

		if(strlen($post_arr["CHARSET"]) > 0)
		{
			$post_arr["BODY"] = $APPLICATION->ConvertCharset($post_arr["BODY"], SITE_CHARSET, $post_arr["CHARSET"]);
			$post_arr["SUBJECT"] = $APPLICATION->ConvertCharset($post_arr["SUBJECT"], SITE_CHARSET, $post_arr["CHARSET"]);
			$post_arr["FROM_FIELD"] = $APPLICATION->ConvertCharset($post_arr["FROM_FIELD"], SITE_CHARSET, $post_arr["CHARSET"]);
		}
		//Preparing message header, text, subject
		$sBody = str_replace("\r\n","\n",$post_arr["BODY"]);
		if(COption::GetOptionString("main", "CONVERT_UNIX_NEWLINE_2_WINDOWS", "N") == "Y")
			$sBody = str_replace("\n", "\r\n", $sBody);

		if(COption::GetOptionString("subscribe", "allow_8bit_chars") <> "Y")
		{
			$sSubject = CMailTools::EncodeSubject($post_arr["SUBJECT"], $post_arr["CHARSET"]);
			$sFrom = CMailTools::EncodeHeaderFrom($post_arr["FROM_FIELD"], $post_arr["CHARSET"]);
		}
		else
		{
			$sSubject = $post_arr["SUBJECT"];
			$sFrom = $post_arr["FROM_FIELD"];
		}

		$bHasAttachments = false;
		if($post_arr["BODY_TYPE"]=="html" && COption::GetOptionString("subscribe", "attach_images")=="Y")
		{
			//MIME with attachments
			$tools = new CMailTools;
			$sBody = $tools->ReplaceImages($sBody);
			if(count($tools->aMatches) > 0)
			{
				$bHasAttachments = true;

				$sBoundary = "----------".uniqid("");
				$sHeader =
					'From: '.$sFrom.$eol.
					'MIME-Version: 1.0'.$eol.
					'Content-Type: multipart/related; boundary="'.$sBoundary.'"'.$eol.
					'Content-Transfer-Encoding: 8bit';

				$sBody =
					"--".$sBoundary.$eol.
					"Content-Type: ".($post_arr["BODY_TYPE"]=="html"? "text/html":"text/plain").($post_arr["CHARSET"]<>""? "; charset=".$post_arr["CHARSET"]:"").$eol.
					"Content-Transfer-Encoding: 8bit".$eol.$eol.
					$sBody.$eol;

				foreach($tools->aMatches as $attachment)
				{
					$aImage = @getimagesize($_SERVER["DOCUMENT_ROOT"].$attachment["SRC"]);
					if($aImage === false)
						continue;

					$filename = $_SERVER["DOCUMENT_ROOT"].$attachment["SRC"];
					$handle = fopen($filename, "rb");
					$file = fread($handle, filesize($filename));
					fclose($handle);

					$sBody .=
						$eol."--".$sBoundary.$eol.
						"Content-Type: ".(function_exists("image_type_to_mime_type")? image_type_to_mime_type($aImage[2]) : CMailTools::ImageTypeToMimeType($aImage[2]))."; name=\"".$attachment["DEST"]."\"".$eol.
						"Content-Transfer-Encoding: base64".$eol.
						"Content-ID: <".$attachment["ID"].">".$eol.$eol.
						chunk_split(base64_encode($file), 72, $eol);
				}
			}
		}
		$rsFile = CPosting::GetFileList($ID);
		$arFile = $rsFile->Fetch();
		if($arFile)
		{
			if(!$bHasAttachments)
			{
				$bHasAttachments = true;
				$sBoundary = "----------".uniqid("");
				$sHeader =
					"From: ".$sFrom.$eol.
					"MIME-Version: 1.0".$eol.
					"Content-Type: multipart/related; boundary=\"".$sBoundary."\"".$eol.
					"Content-Transfer-Encoding: 8bit";

				$sBody =
					"--".$sBoundary.$eol.
					"Content-Type: ".($post_arr["BODY_TYPE"]=="html"? "text/html":"text/plain").($post_arr["CHARSET"]<>""? "; charset=".$post_arr["CHARSET"]:"").$eol.
					"Content-Transfer-Encoding: 8bit".$eol.$eol.
					$sBody.$eol;
			}

			do {
				$sBody .=
					$eol."--".$sBoundary.$eol.
					"Content-Type: ".$arFile["CONTENT_TYPE"]."; name=\"".$arFile["ORIGINAL_NAME"]."\"".$eol.
					"Content-Transfer-Encoding: base64".$eol.
					"Content-Disposition: attachment; filename=\"".$arFile["ORIGINAL_NAME"]."\"".$eol.$eol;

				$filename = $_SERVER["DOCUMENT_ROOT"]."/".COption::GetOptionString("main", "upload_dir")."/".$arFile["SUBDIR"]."/".$arFile["FILE_NAME"];
				$handle = fopen($filename, "rb");
				$file = fread($handle, filesize($filename));
				fclose($handle);

				$sBody .= chunk_split(base64_encode($file), 72, $eol);
			} while ($arFile = $rsFile->Fetch());
		}

		if($bHasAttachments)
		{
			$sBody .= $eol."--".$sBoundary."--".$eol;
		}
		else
		{
			//plain message without MIME
			$sHeader =
				"From: ".$sFrom.$eol.
				"MIME-Version: 1.0".$eol.
				"Content-Type: ".($post_arr["BODY_TYPE"]=="html"? "text/html":"text/plain").($post_arr["CHARSET"]<>""? "; charset=".$post_arr["CHARSET"]:"").$eol.
				"Content-Transfer-Encoding: 8bit";
		}

		$mail_additional_parameters = trim(COption::GetOptionString("subscribe", "mail_additional_parameters"));
		if($post_arr["DIRECT_SEND"] == "Y")
		{
			//personal delivery
			$arEvents = array();
			$rsEvents = GetModuleEvents("subscribe", "BeforePostingSendMail");
			while($arEvent = $rsEvents->Fetch())
				$arEvents[]=$arEvent;
			$n = 1;
			foreach($aEmail as $email)
			{
				//Event part
				$arFields = array(
					"POSTING_ID" => $ID,
					"EMAIL" => $email,
					"SUBJECT" => $sSubject,
					"BODY" => $sBody,
					"HEADER" => $sHeader,
				);
				foreach($arEvents as $arEvent)
					$arFields = ExecuteModuleEvent($arEvent, $arFields);
				//Sending
				if($mail_additional_parameters!="")
					$result = @mail($arFields["EMAIL"], $arFields["SUBJECT"], $arFields["BODY"], $arFields["HEADER"], $mail_additional_parameters);
				else
					$result = @mail($arFields["EMAIL"], $arFields["SUBJECT"], $arFields["BODY"], $arFields["HEADER"]);
				//Result check and iteration
				if($result)
					$aSent[]=$email;
				else
					$aError[]=$email;

				if($timeout > 0 && getmicrotime()-$start_time >= $timeout)
					break;
				if($maxcount > 0 && $n >= $maxcount)
					break;
				$n++;
				$subscribe_current_emails_per_hit++;
			}
		}
		else
		{
			//BCC delivery
			$max_bcc_count = intval(COption::GetOptionString("subscribe", "max_bcc_count"));
			if($max_bcc_count<=0)
				$max_bcc_count = count($aEmail);
			while(count($aEmail)>0)
			{
				$aStep = array_splice($aEmail, 0, $max_bcc_count);
				$BCC = implode(",", $aStep);
				if($BCC<>"")
				{
					$sHeaderStep = $sHeader.$eol."Bcc: ".$BCC;
					if($mail_additional_parameters!="")
						$result = @mail($post_arr["TO_FIELD"], $sSubject, $sBody, $sHeaderStep, $mail_additional_parameters);
					else
						$result = @mail($post_arr["TO_FIELD"], $sSubject, $sBody, $sHeaderStep);
					if($result)
					{
						foreach($aStep as $email)
							$aSent[]=$email;
					}
					else
					{
						foreach($aStep as $email)
							$aError[]=$email;
						$this->LAST_ERROR .= GetMessage("class_post_err_mail")."<br>";
					}
				}
			}
		}

		//set status and delivered and error emails
		$aEmail = array_diff($aEmail, $aSent, $aError);
		if(count($aEmail)==0)
		{
			$STATUS=count($aError)==0?"S":"E";
			$DATE=$DB->GetNowFunction();
		}
		else
		{
			$STATUS="P";
			$DATE="null";
		}
		$arBinds = array();
		$arFields = array();
		$arFields["SENT_BCC"] = count($aSent)==0?false:implode(",", $aSent);
		$arBinds["SENT_BCC"] = $arFields["SENT_BCC"];
		$arFields["ERROR_EMAIL"] = count($aError)==0?false:implode(",", $aError);
		$arBinds["ERROR_EMAIL"] = $arFields["ERROR_EMAIL"];
		$arFields["BCC_TO_SEND"] = count($aEmail)==0?false:implode(",", $aEmail);
		$arBinds["BCC_TO_SEND"] = $arFields["BCC_TO_SEND"];

		$strUpdate = $DB->PrepareUpdate("b_posting", $arFields);
		$strSql = "UPDATE b_posting SET ".$strUpdate.", STATUS='".$STATUS."', DATE_SENT=".$DATE." WHERE ID=".$ID;

		CPosting::UnLock($ID);
		if(!$DB->QueryBind($strSql, $arBinds))
		{
			$this->LAST_ERROR .= GetMessage("class_post_err_stat")."<br>";
			return false;
		}
		return ($STATUS=="P"? "CONTINUE":true);
	}
	function ChangeStatus($ID, $status)
	{
		global $DB;
		$ID=intval($ID);
		$this->LAST_ERROR="";
		$strSql = "SELECT STATUS FROM b_posting WHERE ID=".$ID;
		$db_result = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if($db_result && ($arResult=$db_result->Fetch()))
		{
			if($arResult["STATUS"]==$status)
				return true;
			switch($arResult["STATUS"].$status)
			{
				case "DP":
					//BCC_TO_SEND fill
					$post = $this->GetByID($ID);
					if(!($post_arr = $post->Fetch()))
					{
						$this->LAST_ERROR .= GetMessage("class_post_err_notfound")."<br>";
						return false;
					}
					$arToSend = $this->GetEmails($post_arr);
					if(count($arToSend) > 0)
					{
						$arBinds = $arFields  = array(
							"BCC_TO_SEND" => implode(",", $arToSend),
						);
						$strUpdate = $DB->PrepareUpdate("b_posting", $arFields);
						$strSql = "UPDATE b_posting SET ".$strUpdate." WHERE ID=".$ID;
						if(!$DB->QueryBind($strSql, $arBinds))
						{
							$this->LAST_ERROR .= GetMessage("class_post_err_send");
							return false;
						}
						$strSql="UPDATE b_posting SET STATUS='".$status."', ERROR_EMAIL=null, SENT_BCC=null WHERE ID=".$ID;
					}
					else
					{
						$this->LAST_ERROR .= GetMessage("class_post_err_status4");
						return false;
					}
					break;
				case "PW":
				case "WP":
				case "PE":
				case "PS":
					$strSql="UPDATE b_posting SET STATUS='".$status."' WHERE ID=".$ID;
					break;
				case "EW"://This is the way to resend error e-mails
				case "EP":
					$strSql="UPDATE b_posting SET STATUS='".$status."', BCC_TO_SEND=ERROR_EMAIL, ERROR_EMAIL=null WHERE ID=".$ID;
					break;
				case "ED":
				case "SD":
				case "WD":
					//SENT_BCC = null
					//ERROR_EMAIL = null
					//BCC_TO_SEND = null
					$strSql="UPDATE b_posting SET STATUS='".$status."', SENT_BCC=null, ERROR_EMAIL=null, BCC_TO_SEND=null, DATE_SENT=null WHERE ID=".$ID;
					break;
				default:
					$this->LAST_ERROR=GetMessage("class_post_err_status2");
					return false;
			}
			$db_result=$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if($db_result===false)
			{
				$this->LAST_ERROR=GetMessage("class_post_err_status3");
				return false;
			}
			else
				return true;
		}
		else
		{
			$this->LAST_ERROR=GetMessage("class_post_err_notfound")."<br>";
			return false;
		}
	}
}

class CMailTools
{
	var $aMatches = array();
	var $pcre_backtrack_limit = false;

	function IsEightBit($str)
	{
		$len = strlen($str);
		for($i=0; $i<$len; $i++)
			if(ord(substr($str, $i, 1))>>7)
				return true;
		return false;
	}

	function EncodeMimeString($text, $charset)
	{
		if(!CMailTools::IsEightBit($text))
			return $text;

		$maxl = IntVal((76 - strlen($charset) + 7)*0.4);

		$res = "";
		$eol = CEvent::GetMailEOL();
		$len = strlen($text);
		for($i=0; $i<$len; $i=$i+$maxl)
		{
			if($i>0)
				$res .= $eol."\t";
			$res .= "=?".$charset."?B?".base64_encode(substr($text, $i, $maxl))."?=";
		}
		return $res;
	}

	function EncodeSubject($text, $charset)
	{
		return "=?".$charset."?B?".base64_encode($text)."?=";
	}

	function EncodeHeaderFrom($text, $charset)
	{
		$i = strlen($text);
		while($i > 0)
		{
			if(ord(substr($text, $i-1, 1))>>7)
				break;
			$i--;
		}
		if($i==0)
			return $text;
		else
			return "=?".$charset."?B?".base64_encode(substr($text, 0, $i))."?=".substr($text, $i);
	}

	function __replace_img($matches)
	{
		$src = $matches[3];
		if($src <> "")
		{
			if(array_key_exists($src, $this->aMatches))
			{
				$uid = $this->aMatches[$src]["ID"];
			}
			elseif(file_exists($_SERVER["DOCUMENT_ROOT"].$src) && is_array(@getimagesize($_SERVER["DOCUMENT_ROOT"].$src)))
			{
				$dest = basename($src);
				$uid = uniqid(md5($dest));
				$this->aMatches[$src] = array("SRC"=>$src, "DEST"=>$dest, "ID"=>$uid);
			}
			else
				return $matches[0];
			return $matches[1].$matches[2]."cid:".$uid.$matches[4].$matches[5];
		}
		return $matches[0];
	}

	function ReplaceImages($text)
	{
		if($this->pcre_backtrack_limit === false)
			$this->pcre_backtrack_limit = intval(ini_get("pcre.backtrack_limit"));
		$text_len = function_exists('mb_strlen')? mb_strlen($text, 'latin1'): strlen($text);
		$text_len++;
		if($this->pcre_backtrack_limit < $text_len)
		{
			@ini_set("pcre.backtrack_limit", $text_len);
			$this->pcre_backtrack_limit = intval(ini_get("pcre.backtrack_limit"));
		}
		$this->aMatches = array();
		$text = preg_replace_callback(
			"/(<img\s.*?src\s*=\s*)([\"']?)(.*?)(\\2)(\s.+?>|\s*>)/is",
			array(&$this, "__replace_img"),
			$text
		);
		return preg_replace_callback(
			"/(<td\s.*?background\s*=\s*)([\"']?)(.*?)(\\2)(\s.+?>|\s*>)/is",
			array(&$this, "__replace_img"),
			$text
		);
	}

	function ImageTypeToMimeType($type)
	{
		$aTypes = array(
			1 => "image/gif",
			2 => "image/jpeg",
			3 => "image/png",
			4 => "application/x-shockwave-flash",
			5 => "image/psd",
			6 => "image/bmp",
			7 => "image/tiff",
			8 => "image/tiff",
			9 => "application/octet-stream",
			10 => "image/jp2",
			11 => "application/octet-stream",
			12 => "application/octet-stream",
			13 => "application/x-shockwave-flash",
			14 => "image/iff",
			15 => "image/vnd.wap.wbmp",
			16 => "image/xbm",
		);
		if(!empty($aTypes[$type]))
			return $aTypes[$type];
		else
			return "application/octet-stream";
	}
}
?>