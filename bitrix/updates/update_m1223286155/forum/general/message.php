<?
##############################################
# Bitrix Site Manager Forum                  #
# Copyright (c) 2002-2007 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
IncludeModuleLangFile(__FILE__); 

class CAllForumMessage
{
	//---------------> Message add, update, delete
	function CanUserAddMessage($TID, $arUserGroups, $iUserID = 0)
	{
		$TID = IntVal($TID);
		$arTopic = CForumTopic::GetByID($TID);
		if ($arTopic)
		{
			// Esli prava na forum ne men'she redaktirovaniya, to mozhno dobavlyat'
			$FID = IntVal($arTopic["FORUM_ID"]);
			$strPerms = CForumNew::GetUserPermission($FID, $arUserGroups);
			if ($strPerms>="Y") return True;
			if (CForumUser::IsLocked($iUserID)) 
				return False;
			if ($strPerms<"I") 
				return False;

			$arForum = CForumNew::GetByID($FID);
			if ($arForum)
			{
				if ($arForum["ACTIVE"]!="Y") return False;
				if ($strPerms>="U") return True;

				if ($arTopic["STATE"]!="Y") return False;
				if ($arTopic["APPROVED"]!="Y") return False;
			}
			else
			{
				return False;
			}
		}
		else
		{
			return False;
		}
		return True;
	}

	function CanUserUpdateMessage($MID, $arUserGroups, $iUserID = 0)
	{
		$MID = IntVal($MID);
		$arMessage = CForumMessage::GetByID($MID);
		if ($arMessage)
		{
			$TID = IntVal($arMessage["TOPIC_ID"]);
			$arTopic = CForumTopic::GetByID($TID);
			if ($arTopic)
			{
				$FID = IntVal($arTopic["FORUM_ID"]);
				$strPerms = CForumNew::GetUserPermission($FID, $arUserGroups);
				if ($strPerms>="Y") 
					return True;
				if (CForumUser::IsLocked($iUserID)) 
					return False;
				if ($strPerms<"I") 
					return False;

				$arForum = CForumNew::GetByID($FID);
				if ($arForum)
				{
					if ($arForum["ACTIVE"]!="Y") return False;
					if ($strPerms>="U") return True;

					if ($arTopic["STATE"]!="Y") return False;

					$iUserID = IntVal($iUserID);
					if ($iUserID<=0 || IntVal($arMessage["AUTHOR_ID"])<=0 || IntVal($arMessage["AUTHOR_ID"])!=$iUserID) return False;
					// if user cann't to edit message that we must check last message
					if (COption::GetOptionString("forum", "USER_EDIT_OWN_POST", "N") != "Y")
					{
						$iCnt = CForumMessage::GetList(array(), array("TOPIC_ID"=>$TID, ">ID"=>$MID), True);
						if (IntVal($iCnt)>=1) return False;
					}
				}
				else
				{
					return False;
				}
			}
			else
			{
				return False;
			}
		}
		else
		{
			return False;
		}

		return True;
	}

	function CanUserDeleteMessage($MID, $arUserGroups, $iUserID = 0)
	{
		$MID = IntVal($MID);
		$arMessage = CForumMessage::GetByID($MID);
		if ($arMessage)
		{
			$FID = IntVal($arMessage["FORUM_ID"]);
			$strPerms = CForumNew::GetUserPermission($FID, $arUserGroups);
			if ($strPerms>="Y") 
				return True;
			if (CForumUser::IsLocked($iUserID)) 
				return False;


			$arForum = CForumNew::GetByID($FID);
			if ($arForum)
			{
				if ($arForum["ACTIVE"]!="Y") return False;
				if ($strPerms>="U") return True;
			}
		}
		return False;
	}

	function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		if ((is_set($arFields, "FORUM_ID") || $ACTION=="ADD") && IntVal($arFields["FORUM_ID"])<=0) return false;
		if ((is_set($arFields, "TOPIC_ID") || $ACTION=="ADD") && IntVal($arFields["TOPIC_ID"])<=0) return false;
		if ((is_set($arFields, "AUTHOR_NAME") || $ACTION=="ADD") && strlen($arFields["AUTHOR_NAME"])<=0) return false;
		if ((is_set($arFields, "POST_MESSAGE") || $ACTION=="ADD") && strlen($arFields["POST_MESSAGE"])<=0) return false;

		if ((is_set($arFields, "AUTHOR_ID") || $ACTION=="ADD") && IntVal($arFields["AUTHOR_ID"])<=0) $arFields["AUTHOR_ID"] = False;

		if (is_set($arFields, "POST_MESSAGE"))
		{
			$arFields["POST_MESSAGE_CHECK"] = md5($arFields["POST_MESSAGE"]);
			$ID = IntVal($ID);
			if (!is_set($arFields, "AUTHOR_NAME") || !is_set($arFields, "TOPIC_ID"))
			{
				if ($ID<=0) return False;
				$arMessage = CForumMessage::GetByID($ID);
				if (!is_set($arFields, "AUTHOR_NAME"))
					$arFields["AUTHOR_NAME"] = $arMessage["AUTHOR_NAME"];
				if (!is_set($arFields, "TOPIC_ID"))
					$arFields["TOPIC_ID"] = $arMessage["TOPIC_ID"];
			}
			$iCnt = CForumMessage::GetList(array(), array("TOPIC_ID"=>$arFields["TOPIC_ID"], "!ID"=>$ID, "AUTHOR_NAME"=>$arFields["AUTHOR_NAME"], "POST_MESSAGE_CHECK"=>$arFields["POST_MESSAGE_CHECK"]), True);
			if (IntVal($iCnt)>0) return False;
		}

		if ((is_set($arFields, "USE_SMILES") || $ACTION=="ADD") && $arFields["USE_SMILES"]!="Y" && $arFields["USE_SMILES"]!="N") $arFields["USE_SMILES"]="Y";
		if ((is_set($arFields, "NEW_TOPIC") || $ACTION=="ADD") && $arFields["NEW_TOPIC"]!="Y" && $arFields["NEW_TOPIC"]!="N") $arFields["NEW_TOPIC"]="N";
		if ((is_set($arFields, "APPROVED") || $ACTION=="ADD") && $arFields["APPROVED"]!="Y" && $arFields["APPROVED"]!="N") $arFields["APPROVED"]="Y";

		if (is_set($arFields, "ATTACH_IMG") && strlen($arFields["ATTACH_IMG"]["name"])<=0 && strlen($arFields["ATTACH_IMG"]["del"])<=0)
			unset($arFields["ATTACH_IMG"]);

		if (is_set($arFields, "ATTACH_IMG"))
		{
			$FORUM_ID_tmp = 0;
			if (is_set($arFields, "FORUM_ID"))
			{
				$FORUM_ID_tmp = IntVal($arFields["FORUM_ID"]);
			}
			else
			{
				if ($ID<=0) return False;
				if (!isset($arMessage) || !is_array($arMessage))
					$arMessage = CForumMessage::GetByID($ID);

				$FORUM_ID_tmp = IntVal($arMessage["FORUM_ID"]);
			}
			if ($FORUM_ID_tmp<=0) return False;

			$arForum = CForumNew::GetByID($FORUM_ID_tmp);
			if ($arForum)
			{
				if ($arForum["ALLOW_UPLOAD"]=="Y")
				{
					$res = CFile::CheckImageFile($arFields["ATTACH_IMG"], 0, 0, 0);
					if (strlen($res)>0) return False;
				}
				elseif ($arForum["ALLOW_UPLOAD"]=="F")
				{
					$res = CFile::CheckFile($arFields["ATTACH_IMG"], 0, false, $arForum["ALLOW_UPLOAD_EXT"]);
					if (strlen($res)>0) return False;
				}
				elseif ($arForum["ALLOW_UPLOAD"]=="A")
				{
				}
				else
				{
					unset($arFields["ATTACH_IMG"]);
				}
			}
			else
			{
				return false;
			}
		}
		
		if (intVal($arFields["TOPIC_ID"]) > 0)
		{
			$res = CForumTopic::GetById($arFields["TOPIC_ID"]);
			if (!$res)
			{
				return false;
			}
			elseif ($res["STATE"] == "L")
			{
				return false;
			}
		}
// ********************************* QUOTA ********************************* //
//		if ((!empty($arFields["POST_MESSAGE"])) && (COption::GetOptionInt("main", "disk_space") > 0))
//		{
//			$quota = new CDiskQuota();
//			if (!$quota->checkDiskQuota(strLen($arFields["POST_MESSAGE"])))
//				return false;
//		}
// ********************************* QUOTA ********************************* //

		return True;
	}

	function Delete($ID)
	{
		global $DB;
		$ID = IntVal($ID);

		$DB->StartTransaction();
		$arMessage = CForumMessage::GetByID($ID);
		if ($arMessage)
		{
			$AUTHOR_ID = IntVal($arMessage["AUTHOR_ID"]);
			$TOPIC_ID = IntVal($arMessage["TOPIC_ID"]);
			$FORUM_ID = IntVal($arMessage["FORUM_ID"]);

			$strSql = 
				"SELECT F.ID ".
				"FROM b_forum_message FM, b_file F ".
				"WHERE FM.ID = ".$ID." ".
				"	AND FM.ATTACH_IMG = F.ID ";
			$z = $DB->Query($strSql, false, "FILE: ".__FILE__." LINE:".__LINE__);
			while ($zr = $z->Fetch())
				CFile::Delete($zr["ID"]);

			$DB->Query("DELETE FROM b_forum_message WHERE ID = ".$ID);
			$iCnt = CForumMessage::GetList(array(), array("TOPIC_ID"=>$TOPIC_ID), True);
			if (IntVal($iCnt)<=0)
			{
				CForumTopic::Delete($TOPIC_ID);
				$TOPIC_ID = 0;
			}
			if ($TOPIC_ID>0)
			{
				CForumTopic::SetStat($TOPIC_ID);
			}
			if ($AUTHOR_ID>0)
			{
				CForumUser::SetStat($AUTHOR_ID);
			}
			CForumNew::SetStat($FORUM_ID);
		}
		else
		{
			return False;
		}
		$DB->Commit();

		if (CModule::IncludeModule("search"))
		{
			CSearch::Index("forum", $ID,
				array(
					"TITLE"=>"",
					"BODY"=>""
				)
			);
		}

		return true;
	}

	//---------------> Message list
	function GetByID($ID)
	{
		global $DB;

		$ID = IntVal($ID);
		if (CACHED_FORUM && isset($GLOBALS["FORUM_CACHE"]["MESSAGE"][$ID]) && is_array($GLOBALS["FORUM_CACHE"]["MESSAGE"][$ID]) && is_set($GLOBALS["FORUM_CACHE"]["MESSAGE"][$ID], "ID"))
		{
			return $GLOBALS["FORUM_CACHE"]["MESSAGE"][$ID];
		}
		else
		{
			$strSql = 
				"SELECT FM.*, ".$DB->DateToCharFunction("FM.POST_DATE", "FULL")." as POST_DATE
				FROM b_forum_message FM 
				WHERE FM.ID = ".$ID;
			$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($db_res)
			{
				if ((COption::GetOptionString("forum", "MESSAGE_HTML", "Y") == "Y") || (COption::GetOptionString("forum", "FILTER", "Y") == "Y"))
					$db_res = new _CMessageDBResult($db_res);
				if ($res = $db_res->Fetch())
				{
					$GLOBALS["FORUM_CACHE"]["MESSAGE"][$ID] = $res;
					return $res;
				}
			}
		}
		return False;
	}

	function GetByIDEx($ID, $arAddParams = array())
	{
		global $DB;
		$ID = IntVal($ID);
		if ($ID <= 0)
			return false;

		$arAddParams = (is_array($arAddParams) ? $arAddParams : array($arAddParams));
		$arAddParams["GET_TOPIC_INFO"] = ($arAddParams["GET_TOPIC_INFO"] == "Y" ? "Y" : "N");
		$arAddParams["GET_FORUM_INFO"] = ($arAddParams["GET_FORUM_INFO"] == "Y" ? "Y" : "N");
		$arSqlSelect = array();
		$arSqlFrom = array();
		if ($arAddParams["GET_TOPIC_INFO"] == "Y")
		{
			$arSqlSelect[] = CForumTopic::GetSelectFields(array("sPrefix" => "FT_", "sReturnResult" => "string"));
			$arSqlFrom[] =  "INNER JOIN b_forum_topic FT ON (FM.TOPIC_ID = FT.ID)";
		}
		if ($arAddParams["GET_FORUM_INFO"] == "Y")
		{
			$arSqlSelect[] = CForumNew::GetSelectFields(array("sPrefix" => "F_", "sReturnResult" => "string"));
			$arSqlFrom[] =  "INNER JOIN b_forum F ON (FM.FORUM_ID = F.ID)";
		}
		
		$strSql = 
			"SELECT FM.*, ".$DB->DateToCharFunction("FM.POST_DATE", "FULL")." as POST_DATE, 
				FU.SHOW_NAME, FU.DESCRIPTION, FU.NUM_POSTS, FU.SIGNATURE, FU.AVATAR, FU.RANK_ID, 
				".$DB->DateToCharFunction("FU.DATE_REG", "SHORT")." as DATE_REG, 
				U.EMAIL, U.PERSONAL_ICQ, U.LOGIN ".
				(!empty($arSqlSelect) ? ", ".implode(", ", $arSqlSelect) : "")."
			FROM b_forum_message FM 
				LEFT JOIN b_forum_user FU ON (FM.AUTHOR_ID = FU.USER_ID) 
				LEFT JOIN b_user U ON (FM.AUTHOR_ID = U.ID) 
				".implode(" ", $arSqlFrom)."
			WHERE FM.ID = ".$ID."";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if ((COption::GetOptionString("forum", "MESSAGE_HTML", "Y") == "Y") || (COption::GetOptionString("forum", "FILTER", "Y") == "Y"))
			$db_res = new _CMessageDBResult($db_res);
		if ($res = $db_res->Fetch())
		{
			if (is_array($res))
			{
				// Cache topic data for hits
				if ($arAddParams["GET_TOPIC_INFO"] == "Y" || $arAddParams["GET_FORUM_INFO"] == "Y")
				{
					$res["TOPIC_INFO"] = array();
					$res["FORUM_INFO"] = array();
					$res["MESSAGE_INFO"] = array();
					foreach ($res as $key => $val)
					{
						if (substr($key, 0, 3) == "FT_")
							$res["TOPIC_INFO"][substr($key, 3)] = $val;
						elseif (substr($key, 0, 2) == "F_")
							$res["FORUM_INFO"][substr($key, 2)] = $val;
						else
							$res["MESSAGE_INFO"][$key] = $val;
					}
					if (!empty($res["TOPIC_INFO"]))
					{
						$GLOBALS["FORUM_CACHE"]["TOPIC"][intVal($res["TOPIC_INFO"]["ID"])] = $res["TOPIC_INFO"];
						if (COption::GetOptionString("forum", "FILTER", "Y") == "Y")
						{
							$db_res_filter = new CDBResult;
							$db_res_filter->InitFromArray(array($res["TOPIC_INFO"]));
							$db_res_filter = new _CTopicDBResult($db_res_filter);
							if ($res_filter = $db_res_filter->Fetch())
								$GLOBALS["FORUM_CACHE"]["TOPIC_FILTER"][intVal($res["TOPIC_INFO"]["ID"])] = $res_filter;
						}
					}
					if (!empty($res["FORUM_INFO"]))
					{
						$GLOBALS["FORUM_CACHE"]["FORUM"][intVal($res["FORUM_INFO"]["ID"])] = $res["FORUM_INFO"];
					}
				}
			}
			return $res;
		}
		return False;
	}

	//---------------> Message utils
	function GetMessagePage($ID, $mess_per_page, $arUserGroups, $TID=false, $arAddParams = array())
	{
		$ID = IntVal($ID);
		$mess_per_page = IntVal($mess_per_page);
		
		$arAddParams = (is_array($arAddParams) ? $arAddParams : array($arAddParams));
		$arAddParams["ORDER_DIRECTION"] = ($arAddParams["ORDER_DIRECTION"] == "DESC" ? "DESC" : "ASC");
		if ($mess_per_page<=0) return 0;

		if (intVal($TID) <= 0)
		{
			$arMessage = CForumMessage::GetByID($ID);
		}
		else 
		{
			$arMessage = array("TOPIC_ID" => intVal($TID));
		}
		
		$arFilter = array("TOPIC_ID" => $arMessage["TOPIC_ID"]);
		if (CForumNew::GetUserPermission($arMessage["FORUM_ID"], $arUserGroups)<"Q")
		{
			$arFilter["APPROVED"] = "Y";
		}
		if ($arAddParams["ORDER_DIRECTION"] == "DESC")
			$arFilter[">ID"] = $ID;
		else
			$arFilter["<ID"] = $ID;
			
		$iCnt = CForumMessage::GetList(array("ID" => $arAddParams["ORDER_DIRECTION"]), $arFilter, True);
		$iCnt = IntVal($iCnt);
		return IntVal($iCnt/$mess_per_page) + 1;
	}

	function SendMailMessage($MID, $arFields = array(), $strLang = false, $mailTemplate = false)
	{
		global $USER;
		$MID = IntVal($MID);
		if ($MID <= 0)
			return False;

		if ($mailTemplate === false)
			$mailTemplate = "NEW_FORUM_MESSAGE";

		$arMessage = CForumMessage::GetByID($MID);
		if ($arMessage)
		{
			$TID = IntVal($arMessage["TOPIC_ID"]);
			$arTopic = CForumTopic::GetByID($TID);
			$FID = IntVal($arMessage["FORUM_ID"]);
			$arForum = CForumNew::GetByID($FID);

			if ($arTopic && $arForum)
			{
				if (!is_set($arFields, "FORUM_ID")) $arFields["FORUM_ID"] = $arMessage["FORUM_ID"];
				if (!is_set($arFields, "FORUM_NAME")) $arFields["FORUM_NAME"] = $arForum["NAME"];
				if (!is_set($arFields, "TOPIC_ID")) $arFields["TOPIC_ID"] = $arMessage["TOPIC_ID"];
				if (!is_set($arFields, "MESSAGE_ID")) $arFields["MESSAGE_ID"] = $arMessage["ID"];
				if (!is_set($arFields, "TOPIC_TITLE")) $arFields["TOPIC_TITLE"] = $arTopic["TITLE"];

				if (!is_set($arFields, "MESSAGE_DATE")) $arFields["MESSAGE_DATE"] = $arMessage["POST_DATE"];
				if (!is_set($arFields, "AUTHOR")) $arFields["AUTHOR"] = $arMessage["AUTHOR_NAME"];
				if (!is_set($arFields, "TAPPROVED")) $arFields["TAPPROVED"] = $arTopic["APPROVED"];
				if (!is_set($arFields, "MAPPROVED")) $arFields["MAPPROVED"] = $arMessage["APPROVED"];
				if (!is_set($arFields, "FROM_EMAIL")) $arFields["FROM_EMAIL"] = COption::GetOptionString("forum", "FORUM_FROM_EMAIL", "nomail@nomail.nomail");

				$arForumPaths = CForumNew::GetSites($FID);
				$arForumPathsCodes = array_keys($arForumPaths);
				for ($i = 0; $i < count($arForumPathsCodes); $i++)
				{
					$arForumPaths[$arForumPathsCodes[$i]] = 
						CForumNew::PreparePath2Message(
							$arForumPaths[$arForumPathsCodes[$i]], 
							array(
								"FORUM_ID"=>$arMessage["FORUM_ID"], 
								"TOPIC_ID"=>$arMessage["TOPIC_ID"], 
								"MESSAGE_ID"=>$arMessage["ID"]));
				}

				$event = new CEvent;
				$arFilter = array(
						"FORUM_ID" => $FID,
						"TOPIC_ID_OR_NULL" => $TID,
						"ACTIVE" => "Y",
						">=PERMISSION" => (($arTopic["APPROVED"]!="Y" || $arMessage["APPROVED"]!="Y") ? "Q" : "E"));
				if ($arMessage["NEW_TOPIC"] != "Y")
					$arFilter["NEW_TOPIC_ONLY"] = "N";
				if ($mailTemplate == "NEW_FORUM_MESSAGE")
					$arFilter["LAST_SEND_OR_NULL"] = $MID;

				$db_res = CForumSubscribe::GetListEx(array(), $arFilter);
				$str_IDs = "0";
				$currentUserID = false;
				while ($res = $db_res->Fetch())
				{
					// SUBSC_GET_MY_MESSAGE - Otpravka samomu sebe svoih sobstvennyh soobwenij. 
					if (($res["SUBSC_GET_MY_MESSAGE"] == "N") && ($res["USER_ID"] == $USER->GetId()))
						continue;
					
					// SUBSC_GROUP_MESSAGE  - Gruppirovka soobwenij. 
					if ($currentUserID == $res["USER_ID"])
						continue;
					$currentUserID = $res["USER_ID"];
					
					if (strlen($res["EMAIL"])>0)
					{
						$arFields_tmp = $arFields;
						if (!is_set($arFields_tmp, "PATH2FORUM"))
						{
							$arFields_tmp["PATH2FORUM"] = $arForumPaths[$res["SITE_ID"]];
							if (strlen($arFields_tmp["PATH2FORUM"])<=0)
							{
								$db_lang = CLang::GetByID($res["SITE_ID"]);
								$lpath = "/";
								if ($ar_lang = $db_lang->Fetch()) $lpath = $ar_lang["DIR"];
								$arFields_tmp["PATH2FORUM"] = $lpath.(COption::GetOptionString("forum", "REL_FPATH", ""))."forum/";
							}
						}

						if (!is_set($arFields_tmp, "MESSAGE_TEXT"))
						{
							if (!isset(${"parser_".$res["SITE_ID"]}))
								${"parser_".$res["SITE_ID"]} = new textParser($res["SITE_ID"]);
								
							$POST_MESSAGE_HTML = $arMessage["POST_MESSAGE"];
							if (COption::GetOptionString("forum", "FILTER", "Y") == "Y")
								$POST_MESSAGE_HTML = CFilterUnquotableWords::Filter($POST_MESSAGE_HTML);
							$arFields_tmp["MESSAGE_TEXT"] = ${"parser_".$res["SITE_ID"]}->convert4mail($POST_MESSAGE_HTML);
						}

						$arFields_tmp["RECIPIENT"] = $res["EMAIL"];
						$event->Send($mailTemplate, $res["SITE_ID"], $arFields_tmp, "N");
						$str_IDs .= ",".$res["ID"];
						if (strlen($str_IDs)>500)
						{
							CForumSubscribe::UpdateLastSend($MID, $str_IDs);
							$str_IDs = "0";
						}
					}
				}
				if (strlen($str_IDs)>1)
				{
					CForumSubscribe::UpdateLastSend($MID, $str_IDs);
				}
			}
		}
		else
		{
			return false;
		}

		return true;
	}
	
	function GetFirstUnreadEx($FID, $TID, $arUserGroups) // out-of-date function
	{
		$FID = IntVal($FID);
		$TID = IntVal($TID);
		if ($FID<=0) return false;

		$f_PERMISSION = CForumNew::GetUserPermission($FID, $arUserGroups);
		return CForumMessage::GetFirstUnread($FID, $TID, $f_PERMISSION);
	}
	
	function GetFirstUnread($FID, $TID, $PERMISSION) // out-of-date function
	{
		$FID = IntVal($FID);
		$TID = IntVal($TID);
		if ($FID<=0) return false;
		if (strlen($PERMISSION)<=0) return false;

		$MESSAGE_ID = 0;
		$TOPIC_ID = 0;

		$read_forum_cookie = COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_FORUM_0";
		if (strlen($_SESSION["first_read_forum_".$FID])<=0 || IntVal($_SESSION["first_read_forum_".$FID])<0)
		{
			if (isset($_COOKIE[$read_forum_cookie]) && strlen($_COOKIE[$read_forum_cookie])>0)
			{
				$arForumCookie = explode("/", $_COOKIE[$read_forum_cookie]);
				$i = 0;
				while ($i < count($arForumCookie))
				{
					if (IntVal($arForumCookie[$i])==$FID)
					{
						$iCurFirstReadForum = IntVal($arForumCookie[$i+1]);
						break;
					}
					$i += 2;
				}
			}

			$read_forum_cookie1 = COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_FORUM_".$FID;
			if (isset($_COOKIE[$read_forum_cookie1]) && IntVal($_COOKIE[$read_forum_cookie1])>0)
			{
				if ($iCurFirstReadForum<IntVal($_COOKIE[$read_forum_cookie1]))
				{
					$iCurFirstReadForum = IntVal($_COOKIE[$read_forum_cookie1]);
				}
			}

			$_SESSION["first_read_forum_".$FID] = IntVal($iCurFirstReadForum);
		}
		if (is_null($_SESSION["read_forum_".$FID]) || strlen($_SESSION["read_forum_".$FID])<=0)
		{
			$_SESSION["read_forum_".$FID] = "0";
		}

		$arFilter = array("FORUM_ID" => $FID);
		if (IntVal($_SESSION["first_read_forum_" . $FID])>0)
			$arFilter[">ID"] = IntVal($_SESSION["first_read_forum_" . $FID]);
		if ($_SESSION["read_forum_" . $FID]!="0")
		{
			$arFMIDsTmp = explode(",", $_SESSION["read_forum_" . $FID]);
			if (count($arFMIDsTmp)>950)
			{
				for ($i1 = 0; $i1<count($arFMIDsTmp); $i1++)
				{
					if (IntVal($_SESSION["first_read_forum_" . $FID]) < IntVal($arFMIDsTmp[$i1]))
					{
						$_SESSION["first_read_forum_" . $FID] = IntVal($arFMIDsTmp[$i1]);
					}
				}
				$_SESSION["read_forum_" . $FID] = "0";
				$arFilter[">ID"] = IntVal($_SESSION["first_read_forum_" . $FID]);
			}
			else
			{
				$arFilter["!@ID"] = $_SESSION["read_forum_" . $FID];
			}
		}
		if ($PERMISSION<="Q") $arFilter["APPROVED"] = "Y";
		if ($TID>0) $arFilter["TOPIC_ID"] = $TID;

		//$db_res = CForumMessage::GetList(array("ID"=>"ASC"), $arFilter, false, 1);
		$db_res = CForumMessage::QueryFirstUnread($arFilter);

		if ($res = $db_res->Fetch())
		{
			$MESSAGE_ID = $res["ID"];
			$TOPIC_ID = $res["TOPIC_ID"];
		}

		return array($TOPIC_ID, $MESSAGE_ID);
	}
}

class _CMessageDBResult extends CDBResult
{
	function _CMessageDBResult($res)
	{
		parent::CDBResult($res);
	}
	function Fetch()
	{
		global $DB;
		$arFields = array();
		if($res = parent::Fetch())
		{
			if (((strlen(trim($res["POST_MESSAGE_HTML"])) <= 0) && (COption::GetOptionString("forum", "MESSAGE_HTML", "Y") == "Y")) || 
				((strlen(trim($res["POST_MESSAGE_FILTER"])) <= 0) && (COption::GetOptionString("forum", "FILTER", "Y") == "Y"))):
				$arForum = CForumNew::GetByID($res["FORUM_ID"]);
				
				if ((COption::GetOptionString("forum", "FILTER", "Y") == "Y") && (strLen(trim($res["POST_MESSAGE_FILTER"])) <= 0))
				{
					$arFields["POST_MESSAGE_FILTER"] = CFilterUnquotableWords::Filter($res["POST_MESSAGE"]);
				}
				
				if ((COption::GetOptionString("forum", "MESSAGE_HTML", "Y") == "Y") && (strLen(trim($res["POST_MESSAGE_HTML"])) <= 0))
				{
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
							"SMILES" => $arForum["ALLOW_SMILES"],
							"UPLOAD" => $arForum["ALLOW_UPLOAD"],
							"NL2BR" => $arForum["ALLOW_NL2BR"],
							"SMILES" => (($res["USE_SMILES"] == "Y") ? $arForum["ALLOW_SMILES"] : "N")
							);
					$POST_MESSAGE_HTML = (is_set($arFields, "POST_MESSAGE_FILTER") ? $arFields["POST_MESSAGE_FILTER"] : $res["POST_MESSAGE"]);
					$arFields["POST_MESSAGE_HTML"] = $parser->convert($POST_MESSAGE_HTML, $allow);
				}
				$strUpdate = $DB->PrepareUpdate("b_forum_message", $arFields);
				$strSql = "UPDATE b_forum_message SET ".$strUpdate." WHERE ID = ".intVal($res["ID"]);
				if ($DB->QueryBind($strSql, $arFields, false, "File: ".__FILE__."<br>Line: ".__LINE__))
				{
					foreach ($arFields as $key => $val)
						$res[$key] = $val;
				}
			endif;
			
			if (!empty($res["FT_HTML"]) && COption::GetOptionString("forum", "FILTER", "Y") == "Y"):
				$arr = unserialize($res["FT_HTML"]);
				if (is_array($arr) && !empty($arr["TITLE"]))
				{
					foreach ($arr as $key => $val)
					{
						$res["FT_".$key] = $val;
					}
				}
			endif;
			
			if (!empty($res["F_HTML"]) && COption::GetOptionString("forum", "FILTER", "Y") == "Y"):
				$arr = unserialize($res["F_HTML"]);
				if (is_array($arr))
				{
					foreach ($arr as $key => $val)
					{
						$res["F_".$key] = $val;
					}
				}
				if (!empty($res["FT_TITLE"]))
					$res["F_TITLE"] = $res["FT_TITLE"];
			endif;
		}
		return $res;
	}
}
?>