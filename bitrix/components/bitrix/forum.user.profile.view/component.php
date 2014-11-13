<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
// ********************  UTIL FUNCTIONS  ************************
if (!function_exists("ForumUrlExtractTmp"))
{
	function ForumUrlExtractTmp($s)
	{
		$x = 0;
		while (strpos(",}])>.", substr($s, -1, 1))!==false)
		{
			$s2 = substr($s, -1, 1);
			$s = substr($s, 0, strlen($s)-1);
		}
		return "<a href=\"".$s."\" target=\"_blank\">".$s."</a>".$s2;
	}
}
if (!function_exists("ForumNumberRusEnding"))
{
	function ForumNumberRusEnding($num)
	{
		if (LANGUAGE_ID=="ru")
		{
			if (strlen($num)>1 && substr($num, strlen($num)-2, 1)=="1")
			{
				return "ют";
			}
			else
			{
				$c = IntVal(substr($num, strlen($num)-1, 1));
				if ($c==0 || ($c>=5 && $c<=9))
					return "ют";
				elseif ($c==1)
					return "";
				else
					return "р";
			}
		}
		else
		{
			if (IntVal($num)>1)
				return "s";
			return "";
		}
	}	
}
// ********************  END UTIL FUNCTIONS  ************************
if (CModule::IncludeModule("forum")):
// *****************************************************************************************
	if (strLen($arParams["UID"]) <= 0)
		$arParams["UID"] = $_REQUEST["UID"];
	$UID = $arParams["UID"];
		
	$FID = intVal($_REQUEST["FID"]);
	$TID = intVal($_REQUEST["TID"]);
	$MID = intVal($_REQUEST["MID"]);
	
	$strErrorMessage = "";
	$strOKMessage = "";
	$bUserFound = False;
// *****************************************************************************************
	$arResult["UID"] = $UID;
	$arResult["FID"] = $FID;
	$arResult["TID"] = $TID;
	$arResult["MID"] = $MID;
	$arResult["IsAuthorized"] = $USER->IsAuthorized() ? "Y" : "N";
	$arResult["IsAdmin"] = $USER->IsAdmin() ? "Y" : "N";
// *****************************************************************************************
	if ($_REQUEST["result"] == "message_send")
		$strOKMessage .= GetMessage("F_OK_MESSAGE_SEND")."\n";
// *****************************************************************************************
	$URL_NAME_DEFAULT = array(
			"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#",
			"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#",
			"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
			"profile" => "PAGE_NAME=profile&UID=#UID#",
			"pm_edit" => "PAGE_NAME=pm_edit&FID=#FID#&MID=#MID#&UID=#UID#&mode=#mode#",
			"message_send" => "PAGE_NAME=message_send&TYPE=#TYPE#&UID=#UID#",
			"user_post" => "PAGE_NAME=user_post&UID=#UID#&mode=#mode#",
		);
	if (empty($arParams["URL_TEMPLATES_MESSAGE"]) && !empty($arParams["URL_TEMPLATES_READ"]))
	{
		$arParams["URL_TEMPLATES_MESSAGE"] = $arParams["URL_TEMPLATES_READ"];
	}
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialchars($arParams["URL_TEMPLATES_".strToUpper($URL)]);
	}
// *****************************************************************************************
	$arParams["FID_RANGE"] = (is_array($arParams["FID_RANGE"]) && !empty($arParams["FID_RANGE"]) ? $arParams["FID_RANGE"] : array());
	$arParams["DATE_TIME_FORMAT"] = trim($arParams["DATE_TIME_FORMAT"]);
	$arParams["DATE_FORMAT"] = trim($arParams["DATE_FORMAT"]);
	if(strlen($arParams["DATE_TIME_FORMAT"])<=0)
		$arParams["DATE_TIME_FORMAT"] = $GLOBALS["DB"]->DateFormatToPHP(CSite::GetDateFormat("FULL"));
	if(strlen($arParams["DATE_FORMAT"])<=0)
		$arParams["DATE_FORMAT"] = $GLOBALS["DB"]->DateFormatToPHP(CSite::GetDateFormat("SHORT"));
// *****************************************************************************************
	$parser = new textParser(false, false, false, "light");
	$parser->MaxStringLen = $arParams["WORD_LENGTH"];
// *****************************************************************************************
	ForumSetLastVisit();
// *****************************************************************************************
	if (intVal($UID)>0)
	{
		$db_res = CUser::GetByID(intVal($UID));
		if ($ar_res = $db_res->Fetch())
		{
			$UID = intVal($UID);
			$bUserFound = True;
			while (list($key, $val) = each($ar_res))
			{
				$arResult["~f_".$key] = $val;
				${"f_".$key} = htmlspecialcharsex(trim($val));
				$arResult["f_".$key] = $parser->wrap_long_words(htmlspecialcharsex(trim($val)));
				if (($key == "PERSONAL_BIRTHDAY") && (strLen($arResult["f_".$key]) > 0))
				{
					$arResult["f_".$key."_FORMATED"] = CForumFormat::FormatDate($val, CLang::GetDateFormat("SHORT"), $arParams["DATE_FORMAT"]);
				}
			}
		}
	}
	
	if (!$bUserFound)
	{
		$db_res = CUser::GetByLogin($UID);
		if ($ar_res = $db_res->Fetch())
		{
			while (list($key, $val) = each($ar_res))
			{
				$arResult["~f_".$key] = $val;
				${"f_".$key} = htmlspecialcharsex(trim($val));
				$arResult["f_".$key] = $parser->wrap_long_words(htmlspecialcharsex(trim($val)));
			}
			$UID = intVal($f_ID);
			$bUserFound = True;
		}
	}
	
// *****************************************************************************************
	$arResult["CURRENT_PAGE"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $UID));
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
		$arResult[$URL] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_".strToUpper($URL)], array("FID" => $FID, "TID" => $TID, "MID" => $MID, "UID" => $UID, "mode"=>"new"));
	$arResult["message_mail"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE_SEND"], array("UID" => $UID, "TYPE"=>"mail"));
	$arResult["SHOW_ICQ"] = (COption::GetOptionString("forum", "SHOW_ICQ_CONTACT", "N") != "Y") ? "N" : ($arParams["SEND_ICQ"] > "A" ? "Y" : "N");
	$arResult["SHOW_MAIL"] = $arParams["SEND_MAIL"] > "A" ? "Y" : "N";
	$arResult["message_icq"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE_SEND"], array("UID" => $UID, "TYPE"=>"icq"));
	$arResult["user_post_lta"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_USER_POST"], array("UID" => $UID, "mode"=>"lta"));
	$arResult["user_post_lt"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_USER_POST"], array("UID" => $UID, "mode"=>"lt"));
	$arResult["user_post_all"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_USER_POST"], array("UID" => $UID, "mode"=>"all"));
// *****************************************************************************************
	if (!$bUserFound)
	{
		if (strLen($UID) <= 0)
			$strErrorMessage .= GetMessage("F_NO_UID").". \n";
		else 
			$strErrorMessage .= str_replace("#UID#", htmlspecialcharsEx($UID), GetMessage("F_NO_DUSER")).". \n";
	}
	
	// ********************  VOTINGS  ************************
	if ($_GET["VOTE_USER"]=="Y" && $UID>0 && $bUserFound && $USER->IsAuthorized())
	{
		ForumVote4User($UID, $_GET["VOTES"], (strlen($_GET["CANCEL_VOTE"])>0 ? True : False), $strErrorMessage, $strOKMessage);
	}
	// ********************  END OF VOTINGS  *****************
// *****************************************************************************************
		
	$arResult["ERROR_MESSAGE"] = $strErrorMessage;
	$arResult["OK_MESSAGE"] = $strOKMessage;
// *****************************************************************************************
	$arResult["SHOW_BACK_URL"] = "N";
	if ($FID>0 || $TID>0 || $MID>0)
	{
		$arResult["SHOW_BACK_URL"] = "Y";
	}
	
	$arResult["SHOW_USER_INFO"] = "N";
	if ($bUserFound)
	{
		$arResult["SHOW_USER_INFO"] = "Y";
		$ar_forum_user = CForumUser::GetByUSER_ID($UID);
		if ($ar_forum_user)
		{
			while (list($key, $val) = each($ar_forum_user))
			{
				${"fu_".$key} = htmlspecialcharsEx($val);
				$arResult["fu_".$key] = $parser->wrap_long_words(htmlspecialcharsEx($val));
			}
		}
		
		if (strLen($arResult["fu_DATE_REG"]) > 0)
		{
			$arResult["fu_DATE_REG_FORMATED"] = CForumFormat::DateFormat($arParams["DATE_FORMAT"], MakeTimeStamp($arResult["fu_DATE_REG"], CSite::GetDateFormat()));
		}
		if (strLen($arResult["fu_LAST_VISIT"]) > 0)
		{
			$arResult["fu_LAST_VISIT_FORMATED"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arResult["fu_LAST_VISIT"], CSite::GetDateFormat()));
		}
		
		if (($fu_SHOW_NAME=="Y") && (strLen(trim($f_NAME))>0 || strLen(trim($f_LAST_NAME))>0))
			$arResult["SHOW_NAME"] = htmlspecialcharsEx(trim($arResult["~f_NAME"]." ".$arResult["~f_LAST_NAME"]));
		else
			$arResult["SHOW_NAME"] = $arResult["f_LOGIN"];
			
		$arResult["SHOW_EDIT_PROFILE"] = "N";
		if ($USER->IsAuthorized() && (intVal($USER->GetID())==$UID || $USER->IsAdmin()))
		{
			$arResult["SHOW_EDIT_PROFILE"] = "Y";
			$arResult["SHOW_EDIT_PROFILE_TITLE"] = (intVal($USER->GetID())!=$UID) ? GetMessage("F_EDIT_THIS_PROFILE") : GetMessage("F_EDIT_YOUR_PROFILE");
			$arResult["SHOW_EDIT_PROFILE_TITLE_BOTTOM"] = ((intVal($USER->GetID())!=$UID) ? GetMessage("F_TO_CHANGE2") : GetMessage("F_TO_CHANGE3"))." ".GetMessage("F_TO_CHANGE4");
		}
	
		// ********************  VOTINGS  ************************
		$arResult["SHOW_VOTES"] = "N";
		if (COption::GetOptionString("forum", "SHOW_VOTES", "Y")=="Y"
			&& $USER->IsAuthorized()
			&& ($USER->IsAdmin() || intVal($USER->GetParam("USER_ID"))!=$UID))
		{
			$strNotesText = "";
			$bCanVote = False;
			$bCanUnVote = False;
			if ($USER->IsAdmin()) $bCanVote = True;
	
			$arUserRank = CForumUser::GetUserRank(intVal($USER->GetParam("USER_ID")));
	
			$arUserPoints = CForumUserPoints::GetByID(intVal($USER->GetParam("USER_ID")), $UID);
			if ($arUserPoints)
			{
				$bCanUnVote = True;
				$strNotesText .= str_replace("#POINTS#", $arUserPoints["POINTS"], str_replace("#END#", ForumNumberRusEnding($arUserPoints["POINTS"]), GetMessage("F_ALREADY_VOTED1"))).". \n";
	
				if (intVal($arUserPoints["POINTS"])<intVal($arUserRank["VOTES"])
					&& !$USER->IsAdmin())
				{
					$bCanVote = True;
					$strNotesText .= str_replace("#POINTS#", (intVal($arUserRank["VOTES"])-intVal($arUserPoints["POINTS"])), str_replace("#END#", ForumNumberRusEnding((intVal($arUserRank["VOTES"])-intVal($arUserPoints["POINTS"]))), GetMessage("F_ALREADY_VOTED3")));
				}
				elseif ($USER->IsAdmin())
				{
					$strNotesText .= GetMessage("F_ALREADY_VOTED_ADMIN");
				}
			}
			else
			{
				if (intVal($arUserRank["VOTES"])>0 || $USER->IsAdmin())
				{
					$bCanVote = True;
					$strNotesText .= GetMessage("F_NOT_VOTED");
					if (!$USER->IsAdmin())
					{
						$strNotesText .= str_replace("#POINTS#", $arUserRank["VOTES"], str_replace("#END#", ForumNumberRusEnding($arUserRank["VOTES"]), GetMessage("F_NOT_VOTED1"))).". \n";
					}
					elseif ($USER->IsAdmin())
					{
						$strNotesText .= GetMessage("F_ALREADY_VOTED_ADMIN");
					}
				}
			}
			$arResult["bCanVote"] = $bCanVote;
			$arResult["bCanUnVote"] = $bCanUnVote;
			$arResult["titleVote"] = $strNotesText;
	
			if (strlen($strNotesText)>0 || $bCanVote || $bCanUnVote)
			{
				if ($USER->IsAdmin() && $bCanVote)
				{
					$arResult["VOTES"] = intVal($arUserRank["VOTES"]);
				}
				$arResult["SHOW_VOTES"] = "Y";
			}
		}
		// ********************  END OF VOTINGS  ************************
		if (strlen($f_PERSONAL_WWW)>0 && $f_PERSONAL_WWW!="http://")
		{
			$strBValueTmp = substr($f_PERSONAL_WWW, 0, 6);
			if ($strBValueTmp!="http:/" && $strBValueTmp!="https:" && $strBValueTmp!="ftp://")
				$f_PERSONAL_WWW = "http://".$f_PERSONAL_WWW;

			$arResult["f_PERSONAL_WWW"] = "<a href=\"".$f_PERSONAL_WWW."\" target=\"_blank\">".$arResult["f_PERSONAL_WWW"]."</a>";
		}
	
		if (strlen($f_WORK_WWW)>0 && $f_WORK_WWW!="http://")
		{
			$strBValueTmp = substr($f_WORK_WWW, 0, 6);
			if ($strBValueTmp!="http:/" && $strBValueTmp!="https:" && $strBValueTmp!="ftp://")
				$f_WORK_WWW = "http://".$f_WORK_WWW;

			$arResult["f_WORK_WWW"] = "<a href=\"".$f_WORK_WWW."\" target=\"_blank\">".$arResult["f_WORK_WWW"]."</a>";
		}
		
		if ($f_PERSONAL_GENDER=="M")
			$arResult["f_PERSONAL_GENDER"] = GetMessage("F_SEX_MALE");
		elseif ($f_PERSONAL_GENDER=="F")
			$arResult["f_PERSONAL_GENDER"] = GetMessage("F_SEX_FEMALE");
			
		$arResult["f_PERSONAL_LOCATION"] = GetCountryByID($f_PERSONAL_COUNTRY);
		if (strlen($arResult["f_PERSONAL_LOCATION"])>0 && strlen($arResult["f_PERSONAL_CITY"])>0)
			$arResult["f_PERSONAL_LOCATION"] .= ", ";
		$arResult["f_PERSONAL_LOCATION"] .= $arResult["f_PERSONAL_CITY"];
		
		$arResult["f_WORK_LOCATION"] = GetCountryByID($arResult["f_WORK_COUNTRY"]);
		if (strlen($arResult["f_WORK_LOCATION"])>0 && strlen($arResult["f_WORK_CITY"])>0)
			$arResult["f_WORK_LOCATION"] .= ", ";
		$arResult["f_WORK_LOCATION"] .= $arResult["f_WORK_CITY"];
		
		$arResult["fu_INTERESTS"] = preg_replace("'((http|https|ftp):\/\/[^ \t\r\n\"Р-пр-џ]+)'ies", "ForumUrlExtractTmp('\\1')", $arResult["fu_INTERESTS"]);
		$arResult["fu_INTERESTS"] = preg_replace("'(^|([\s\(\[\"<]+))([=-a-zA-Z0-9~][-_a-zA-Z0-9.+~\x02]*@([^Р-пр-џ\s\x01]+\.)+([-_A-Za-z0-9\x02]+))'is", '\1<a href="mailto:\3">\3</a>', $arResult["fu_INTERESTS"]);
		
		$arResult["fu_AVATAR"] = "";
		if (strlen($fu_AVATAR)>0):
			$arResult["fu_AVATAR_FILE"] = CFile::GetFileArray($fu_AVATAR);
			if ($arResult["fu_AVATAR_FILE"] !== false)
				$arResult["fu_AVATAR"] = CFile::ShowImage($arResult["fu_AVATAR_FILE"]["SRC"], COption::GetOptionString("forum", "avatar_max_width", 90), COption::GetOptionString("forum", "avatar_max_height", 90), "border=0", "", true);
		endif;
		$arResult["f_PERSONAL_PHOTO"] = "";
		if (strlen($f_PERSONAL_PHOTO)>0):
			$arResult["f_PERSONAL_PHOTO_FILE"] = CFile::GetFileArray($f_PERSONAL_PHOTO);
			if ($arResult["f_PERSONAL_PHOTO_FILE"] !== false)
				$arResult["f_PERSONAL_PHOTO"] = CFile::ShowImage($arResult["f_PERSONAL_PHOTO_FILE"]["SRC"], 200, 200, "border=0 alt=\"\"", "", true);
		endif;
		
		
// *****************************************************************************************
		// Getting User rank
		$ForumsPerms = array("Q"=>GetMessage("LU_USER_Q"), "U"=>GetMessage("LU_USER_U"), "Y"=>GetMessage("LU_USER_Y"));
		$UserPermStr = "";
		$arFilter = array();
		if (!$USER->IsAdmin())
			$arFilter["ACTIVE"] = "Y";
			
		if (COption::GetOptionString("forum", "SHOW_VOTES", "Y")=="Y")
		{
			$arRank = CForumUser::GetUserRank($UID, LANG_ADMIN_LID);
		}
		
		$db_res = CForumNew::GetList(array(), $arFilter);
		if ($db_res && ($res = $db_res->GetNext()))
		{
			$arUserGroup = CUser::GetUserGroup($arParams["UID"]);
			if (!in_array(2, $arUserGroup)) 
				$arUserGroup[] = 2;
			
			do 
			{
				$UserPerm = CForumNew::GetUserPermission($res["ID"], $arUserGroup);
				if (array_key_exists($UserPerm, $ForumsPerms))
					$UserPermStr = $ForumsPerms[$UserPerm];
				elseif (COption::GetOptionString("forum", "SHOW_VOTES", "Y")=="Y")
				{
					$UserPermStr = $arRank["NAME"];
				}
				$UserPermStr = strLen(trim($UserPermStr)) > 0 ? $UserPermStr : GetMessage("LU_USER_USER");
			}while ($res = $db_res->GetNext());
		}
		$arResult["USER_RANK"] = $UserPermStr;
// *****************************************************************************************
		
		$arResult["SHOW_RANK"] = "N";
		if (COption::GetOptionString("forum", "SHOW_VOTES", "Y")=="Y")
		{
			$arResult["SHOW_RANK"] = "Y";
			$arRank["NAME"] = $arResult["USER_RANK"];
			$arResult["arRank"] = $arRank;
			$arResult["SHOW_POINTS"] = "N";
			if ($USER->IsAuthorized() && ($USER->IsAdmin() || intVal($USER->GetParam("USER_ID"))==$UID))
			{
				$arResult["SHOW_POINTS"] = "Y";
				$arResult["USER_POINTS"] = (intVal($arRank["VOTES"])>0 ? intVal($arRank["VOTES"]) : GetMessage("F_NO_VOTES"));
			}
		}
		
		$arFilter = array("AUTHOR_ID"=>$UID);
		if (!empty($arParams["FID_RANGE"]))
		{
			$arFilter["@FORUM_ID"] = $arParams["FID_RANGE"];
		}
		
		$arTopic = CForumUser::UserAddInfo(array("LAST_POST"=>"DESC"), $arFilter, "topics");
		if ($arTopic && ($res = $arTopic->GetNext()))
		{
			$res["TITLE"] = $parser->wrap_long_words($res["TITLE"]);
			$res["DESCRIPTION"] = $parser->wrap_long_words($res["DESCRIPTION"]);
			$res["LAST_POST_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["LAST_POST_DATE"], CSite::GetDateFormat()));
			$arResult["arTopic"] = $res;
			$arResult["arTopic"]["read"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"], 
					array("FID" => $res["FORUM_ID"], "TID" => $res["TOPIC_ID"], "MID" => intVal($res["LAST_POST"]))).
						"#message".intVal($res["LAST_POST"]);
		}
		else 
		{
			$arResult["arTopic"] = "N";
		}
	}
	// ********************* User properties ***************************************************
	$arResult["USER_PROPERTIES"] = array("SHOW" => "N");
	if (!empty($arParams["USER_PROPERTY"]))
	{
		$arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("USER", $UID, LANGUAGE_ID);
		if (count($arParams["USER_PROPERTY"]) > 0)
		{
			foreach ($arUserFields as $FIELD_NAME => $arUserField)
			{
				if (!in_array($FIELD_NAME, $arParams["USER_PROPERTY"]))
					continue;
				$arUserField["EDIT_FORM_LABEL"] = strLen($arUserField["EDIT_FORM_LABEL"]) > 0 ? $arUserField["EDIT_FORM_LABEL"] : $arUserField["FIELD_NAME"];
				$arUserField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arUserField["EDIT_FORM_LABEL"]);
				$arUserField["~EDIT_FORM_LABEL"] = $arUserField["EDIT_FORM_LABEL"];
				$arResult["USER_PROPERTIES"]["DATA"][$FIELD_NAME] = $arUserField;
			}
		}
		if (!empty($arResult["USER_PROPERTIES"]["DATA"]))
			$arResult["USER_PROPERTIES"]["SHOW"] = "Y";
		$arResult["bVarsFromForm"] = $bVarsFromForm;
	}
	// ******************** /User properties ***************************************************
// *****************************************************************************************
	if ($arParams["SET_NAVIGATION"] != "N")
	{
		$APPLICATION->AddChainItem($arResult["SHOW_NAME"]);
	}
// *****************************************************************************************
	if ($arParams["SET_TITLE"] != "N")
		$APPLICATION->SetTitle($arResult["SHOW_NAME"]);
// *****************************************************************************************
	$this->IncludeComponentTemplate();
else:
	ShowError(GetMessage("F_NO_MODULE"));
endif;?>