<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
endif;
// *****************************************************************************************
	$strErrorMessage = "";
	$strOKMessage = "";
	$arError = array();
	$arNote = array();
	$bVarsFromForm = false;
	$arResult["SHOW_MESSAGE_FOR_AJAX"] = "N";
// ************************* Input params***************************************************************
// ************************* BASE **********************************************************************
	$arParams["FID"] = intVal(intVal($arParams["FID"]) <= 0 ? $_REQUEST["FID"] : $arParams["FID"]);
	$arParams["MID"] = (intVal($arParams["MID"]) <= 0 ? $_REQUEST["MID"] : $arParams["MID"]);
	$arParams["MESSAGE_TYPE"] = (empty($arParams["MESSAGE_TYPE"]) ? $_REQUEST["MESSAGE_TYPE"] : $arParams["MESSAGE_TYPE"]);
	$arParams["MESSAGE_TYPE"] = ($arParams["MESSAGE_TYPE"]!="EDIT" ? "NEW" : "EDIT");
// ************************* URL ***********************************************************************
	$URL_NAME_DEFAULT = array(
			"index" => "",
			"list" => "PAGE_NAME=list&FID=#FID#",
			"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#", 
			"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#", 
			"profile_view" => "PAGE_NAME=profile_view&UID=#UID#");
	if (empty($arParams["URL_TEMPLATES_MESSAGE"]) && !empty($arParams["URL_TEMPLATES_READ"]))
	{
		$arParams["URL_TEMPLATES_MESSAGE"] = $arParams["URL_TEMPLATES_READ"];
	}
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~URL_TEMPLATES_".strToUpper($URL)] = $arParams["URL_TEMPLATES_".strToUpper($URL)];
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialchars($arParams["URL_TEMPLATES_".strToUpper($URL)]);
	}
// ************************* ADDITIONAL ****************************************************************
	$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	$arParams["PATH_TO_SMILE"] = (empty($arParams["PATH_TO_SMILE"]) ? "/bitrix/images/forum/smile/" : $arParams["PATH_TO_SMILE"]);
	$arParams["PATH_TO_ICON"] = (empty($arParams["PATH_TO_ICON"]) ? "/bitrix/images/forum/icons/" : $arParams["PATH_TO_ICON"]);
	if ($arParams["AJAX_TYPE"] == "Y" || ($arParams["AJAX_TYPE"] == "A" && COption::GetOptionString("main", "component_ajax_on", "Y") == "Y"))
		$arParams["AJAX_TYPE"] = "Y";
	else
		$arParams["AJAX_TYPE"] = "N";
	$arParams["AJAX_CALL"] = ($_REQUEST["AJAX_CALL"] == "Y" ? "Y" : "N");
	$arParams["AJAX_CALL"] = (($arParams["AJAX_TYPE"] == "Y" && $arParams["AJAX_CALL"] == "Y") ? "Y" : "N");
	$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "N" ? "N" : "Y");
	$arParams["ADD_INDEX_NAV"] = ($arParams["ADD_INDEX_NAV"] == "Y" ? "Y" : "N");
	$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N");
// ************************* SET_TITLE *****************************************************************
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
// ************************* CACHE *********************************************************************
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;
// ************************/ Input params***************************************************************

// ************************ CHECK FATAL ERRORS *********************************************************
	$arResult["index"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_INDEX"], array());
// *****************************************************************************************************
	if ($arParams["MESSAGE_TYPE"]=="EDIT" && $arParams["MID"] > 0)
	{
		$arResult["MESSAGE"] = CForumMessage::GetByID($arParams["MID"]);
	}
	
	if ($arParams["MESSAGE_TYPE"]=="EDIT" && empty($arResult["MESSAGE"]))
	{
		$arError = array(
			"code" => "mid_is_lost",
			"title" => GetMessage("F_MID_IS_LOST"),
			"link" => $arResult["index"]);
	}
	elseif ($arParams["MESSAGE_TYPE"]=="EDIT" && !empty($arResult["MESSAGE"]))
	{
		$arParams["FID"] = intVal($arResult["MESSAGE"]["FORUM_ID"]);
		$arParams["TID"] = intVal($arResult["MESSAGE"]["TOPIC_ID"]);
		$arResult["TOPIC"] = CForumTopic::GetByID(intVal($arResult["MESSAGE"]["TOPIC_ID"]), array("NoFilter" => 'true'));
		if (empty($arResult["TOPIC"]))
		{
			$arError = array(
				"code" => "tid_is_lost",
				"title" => GetMessage("F_TID_IS_LOST"),
				"link" => $arResult["index"]);
		}
	}
	
	$arResult["FORUM"] = CForumNew::GetByID($arParams["FID"]);
	if (empty($arResult["FORUM"]))
	{
		$arError = array(
			"code" => "fid_is_lost",
			"title" => GetMessage("F_FID_IS_LOST"),
			"link" => $arResult["index"]);
	}
	else 
	{
		if ($arParams["MESSAGE_TYPE"]=="NEW" && !CForumTopic::CanUserAddTopic($arParams["FID"], $USER->GetUserGroupArray(), $USER->GetID()))
		{
			$arError = array(
				"code" => "rightsn_new",
				"title" => GetMessage("F_NO_NPERMS"),
				"link" => $arResult["index"]);
		}
		elseif ($arParams["MESSAGE_TYPE"]=="EDIT" && !CForumMessage::CanUserUpdateMessage($arParams["MID"], $USER->GetUserGroupArray(), intVal($USER->GetID())))
		{
			$arError = array(
				"code" => "rightsn_edit",
				"title" => GetMessage("F_NO_EPERMS"),
				"link" => $arResult["index"]);
		}
	}
// *****************************************************************************************
	if (!empty($arError))
	{
		if ($arParams["AJAX_CALL"] == "N")
			LocalRedirect(ForumAddPageParams($arError["link"], array("error" => $arError["code"])));
		elseif ($arParams["AJAX_CALL"] == "Y")
		{
			$res = array(
					"error" => $arError,
					"note" => $arNote,
					"id" => $arParams["MID"],
					"post" => ShowError($arError["title"]));
			if ($_REQUEST["CONVERT_DATA"] == "Y")
				array_walk($res, "htmlspecialcharsEx");
			$APPLICATION->RestartBuffer();
			?><?=CUtil::PhpToJSObject()?><?
			die();
		}
	}
// *****************************************************************************************
// *****************************************************************************************

	ForumSetLastVisit();
	
	$arResult["list"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $arParams["FID"], "TID" => intVal($arParams["TID"])));
	$arResult["read"] = CComponentEngine::MakePathFromTemplate(
		$arParams["URL_TEMPLATES_MESSAGE"], 
		array(
			"FID" => $arParams["FID"], 
			"TID" => intVal($arParams["TID"]), 
			"MID"=>((intVal($arParams["MID"]) > 0) ? intVal($arParams["MID"]) : "s")));
	$arResult["VIEW"] = ((strToUpper($_REQUEST["MESSAGE_MODE"]) == "VIEW" && $_SERVER["REQUEST_METHOD"] == "POST") ? "Y" : "N");
	
// **************************** ACTION *****************************************************
// *****************************************************************************************
	if ($_SERVER["REQUEST_METHOD"]=="POST" && $arResult["VIEW"] == "N")
	{
		if (check_bitrix_sessid())
		{
			$arFieldsG = array(
				"POST_MESSAGE" => $_REQUEST["POST_MESSAGE"],
				"USE_SMILES" => $_REQUEST["USE_SMILES"]);

			foreach (array(
				"AUTHOR_NAME", "AUTHOR_EMAIL",
				"TITLE", "TAGS", "DESCRIPTION", 
				"ICON_ID") as $res)
			{
				if (isset($_REQUEST[$res]))
					$arFieldsG[$res] = $_REQUEST[$res];
			}
			
			if (!empty($_FILES["ATTACH_IMG"]))
			{
				$arFieldsG["ATTACH_IMG"] = $_FILES["ATTACH_IMG"];
				if ($arParams["MESSAGE_TYPE"]=="EDIT")
					$arFieldsG["ATTACH_IMG"]["del"] = $_REQUEST["ATTACH_IMG_del"];
			}
			
			if ($arParams["MESSAGE_TYPE"] == "EDIT")
			{
				$arFieldsG["EDIT_ADD_REASON"] = $_REQUEST["EDIT_ADD_REASON"];
				$arFieldsG["EDITOR_NAME"] = $_REQUEST["EDITOR_NAME"];
				$arFieldsG["EDITOR_EMAIL"] = $_REQUEST["EDITOR_EMAIL"];
				$arFieldsG["EDIT_REASON"] = $_REQUEST["EDIT_REASON"];
			}
			
			$TID1 = ($arParams["MESSAGE_TYPE"]=="NEW") ? 0 : intVal($arParams["TID"]);
			$MID1 = ($arParams["MESSAGE_TYPE"]=="NEW") ? 0 : intVal($arParams["MID"]);
			$MID1 = intVal(ForumAddMessage($arParams["MESSAGE_TYPE"], $arParams["FID"], $TID1, $MID1, $arFieldsG, $strErrorMessage, $strOKMessage, false, $_POST["captcha_word"], 0, $_POST["captcha_code"]));
		}
		else
		{
			$strErrorMessage .= GetMessage("F_ERR_SESS_FINISH").".\n";
			$bVarsFromForm = true;
		}
		
		if (($MID1 > 0) && empty($strErrorMessage))
		{
			$arParams["MID"] = $MID1;
			
			$arResult["MESSAGE"] = array();
			$db_res = CForumMessage::GetList(array(), array("ID" => $MID1));
			if ($db_res && $res = $db_res->GetNext())
				$arResult["MESSAGE"] = $res;

			$addParams = array();
			if ($_REQUEST["TOPIC_SUBSCRIBE"]=="Y"||$_REQUEST["FORUM_SUBSCRIBE"]=="Y")
			{
				$addParams["sessid"] = bitrix_sessid();
				if ($_REQUEST["TOPIC_SUBSCRIBE"]=="Y")
					$addParams["TOPIC_SUBSCRIBE"] = "Y";
				if ($_REQUEST["FORUM_SUBSCRIBE"]=="Y")
					$addParams["FORUM_SUBSCRIBE"] = "Y";
			}
			BXClearCache(true, "/".SITE_ID."/forum/topic/".$arParams["TID"]."/");
			$arNote = array(
				"code" => strToLower($arParams["MESSAGE_TYPE"]),
				"title" => $strOKMessage, 
				"link" => ForumAddPageParams(
					CComponentEngine::MakePathFromTemplate(
						$arParams["~URL_TEMPLATES_MESSAGE"], 
						array("FID" => intVal($arParams["FID"]), "TID" => intVal($arResult["MESSAGE"]["TOPIC_ID"]), "MID" => intVal($arParams["MID"]))),
					$addParams));
			if ($arParams["AJAX_CALL"] == "N")
			{
				$url = ForumAddPageParams($arNote["link"], array("result" => $arNote["code"]));
				LocalRedirect($url);
			}
			else 
			{
				$arResult["SHOW_MESSAGE_FOR_AJAX"] = "Y";
			}
		}
		else 
		{
			$arResult["ERROR_MESSAGE"] = $strErrorMessage;
			$bVarsFromForm = true;
		}
	}
	
	/* View */
	/* now file preview is no exist */
	if ($arResult["VIEW"] == "Y" || $arResult["SHOW_MESSAGE_FOR_AJAX"] == "Y")
	{
		$arAllow = array(
			"HTML" => $arResult["FORUM"]["ALLOW_HTML"],
			"ANCHOR" => $arResult["FORUM"]["ALLOW_ANCHOR"],
			"BIU" => $arResult["FORUM"]["ALLOW_BIU"],
			"IMG" => $arResult["FORUM"]["ALLOW_IMG"],
			"LIST" => $arResult["FORUM"]["ALLOW_LIST"],
			"QUOTE" => $arResult["FORUM"]["ALLOW_QUOTE"],
			"CODE" => $arResult["FORUM"]["ALLOW_CODE"],
			"FONT" => $arResult["FORUM"]["ALLOW_FONT"],
			"SMILES" => $arResult["FORUM"]["ALLOW_SMILES"],
			"UPLOAD" => $arResult["FORUM"]["ALLOW_UPLOAD"],
			"NL2BR" => $arResult["FORUM"]["ALLOW_NL2BR"],
			"SMILES" => ($_POST["USE_SMILES"] == "Y" ? "Y" : "N"));
			
		$parser = new textParser(LANGUAGE_ID, $arParams["PATH_TO_SMILE"], $arParams["CACHE_TIME"]);
		if ($arResult["VIEW"] == "Y")
		{
			$bVarsFromForm = true;
			$arResult["POST_MESSAGE_VIEW"]  = $parser->convert($_POST["POST_MESSAGE"], $arAllow);
		}
		else 
		{
			if (empty($arResult["MESSAGE"]))
			{
				$db_res = CForumMessage::GetList(array(), array("ID" => $MID1));
				if ($db_res && $res = $db_res->GetNext())
					$arResult["MESSAGE"] = $res;
			}
			if (!empty($arResult["MESSAGE"]))
			{
				$res = $arResult["MESSAGE"];
				$res["POST_MESSAGE_TEXT"] = (COption::GetOptionString("forum", "FILTER", "Y")=="Y" ? $res["POST_MESSAGE_FILTER"] : $res["POST_MESSAGE"]);
				$res["POST_MESSAGE_TEXT"] = $parser->convert($res["POST_MESSAGE_TEXT"], $arAllow);
//				************************message attach img****************************************
				$res["ATTACH_IMG"] = "";
				if (intVal($res["~ATTACH_IMG"])>0 && ($arResult["FORUM"]["ALLOW_UPLOAD"]=="Y" || 
					$arResult["FORUM"]["ALLOW_UPLOAD"]=="F" || $arResult["FORUM"]["ALLOW_UPLOAD"]=="A"))
					$res["ATTACH_IMG"] = CFile::ShowFile($res["~ATTACH_IMG"], 0, 300, 300, true, "border=0", false);
					
				if (!empty($res["EDITOR_ID"]))
				{
					$res["EDITOR_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $res["EDITOR_ID"]));
				}
				
				if (strLen(trim($res["EDIT_DATE"])) > 0)
				{
					$res["EDIT_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["EDIT_DATE"], CSite::GetDateFormat()));
				}
				$arResult["MESSAGE"] = $res;
			}
		}
	}
	
// *****************************************************************************************
	/* For custom template only */	
	$arFormParams = array(
		"SEF_MODE" => $arParams["SEF_MODE"],
		"MESSAGE_TYPE" => $arParams["MESSAGE_TYPE"],
		"FID" => $arParams["FID"],
		"TID" => $arParams["TID"],
		"MID" => $arParams["MID"],
		"arForum" => $arResult["FORUM"],
		"bVarsFromForm" => $bVarsFromForm,
		"strErrorMessage" => $strErrorMessage,
		"strOKMessage" => $strOKMessage,
		"View" => ($arResult["VIEW"] == "Y"),
		"PAGE_NAME" => "topic_new");
	if ($bVarsFromForm)
	{
		$arFormParams["AUTHOR_NAME"] = $_POST["AUTHOR_NAME"];
		$arFormParams["AUTHOR_EMAIL"] = $_POST["AUTHOR_EMAIL"];
		$arFormParams["POST_MESSAGE"] = $_POST["POST_MESSAGE"];
		$arFormParams["USE_SMILES"] = $_POST["USE_SMILES"];
		$arFormParams["TITLE"] = $_POST["TITLE"];
		$arFormParams["TAGS"] = $_POST["TAGS"];
		$arFormParams["DESCRIPTION"] = $_POST["DESCRIPTION"];
		$arFormParams["ICON_ID"] = $_POST["ICON_ID"];
	}
	$arFormParams["PATH_TO_SMILE"] = $arParams["PATH_TO_SMILE"];
	$arFormParams["PATH_TO_ICON"] = $arParams["PATH_TO_ICON"];
	$arFormParams["CACHE_TIME"] = $arParams["CACHE_TIME"];
	$arFormParams["URL_TEMPLATES_LIST"] = $arParams["~URL_TEMPLATES_LIST"];
	$arFormParams["URL_TEMPLATES_READ"] = $arParams["~URL_TEMPLATES_MESSAGE"];
	$arResult["arFormParams"] = $arFormParams;
	/* For custom template only */	
	$arResult["DIE"] = ($bDie ? "Y" : "N");
	// *****************************************************************************************
	if ($arParams["SET_NAVIGATION"] == "Y")
	{
		if ($arParams["ADD_INDEX_NAV"] == "Y")
			$APPLICATION->AddChainItem(GetMessage("F_INDEX"), $arResult["index"]);
		$APPLICATION->AddChainItem(htmlspecialchars($arResult["FORUM"]["NAME"]), $arResult["list"]);
		if ($arParams["MESSAGE_TYPE"] == "EDIT")
			$APPLICATION->AddChainItem(htmlspecialchars($arResult["TOPIC"]["TITLE"]), $arResult["read"]);
	}
	// *****************************************************************************************
	if ($arParams["SET_TITLE"] != "N")
		$APPLICATION->SetTitle((($arParams["MESSAGE_TYPE"]=="NEW")?GetMessage("F_NTITLE"):GetMessage("F_ETITLE")));
	if($USER->IsAuthorized())
	{
		if($arParams["DISPLAY_PANEL"] == "Y")
			CForumNew::ShowPanel($arParams["FID"], $arParams["TID"], false);
	}
	// *****************************************************************************************
	$this->IncludeComponentTemplate();
	// *****************************************************************************************
	return array(
		"MESSAGE_TYPE" => $arParams["MESSAGE_TYPE"],
		"FORUM" => $arResult["FORUM"],
		"bVarsFromForm" => ($bVarsFromForm ? "Y" : "N"),
		"ERROR_MESSAGE" => $strErrorMessage,
		"OK_MESSAGE" => $strOKMessage);
?>