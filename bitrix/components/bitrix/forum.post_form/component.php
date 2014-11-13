<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum"))
{
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
}
// *****************************************************************************************
	$arFilter = array();
// ****************************Input params*************************************************
// **************************** CUSTOM COMPONENT *******************************************
	if (!empty($arParams["arFormParams"]) && is_array($arParams["arFormParams"]))
	{
		$arParams["FID"] = $arParams["arFormParams"]["FID"];
		$arParams["TID"] = $arParams["arFormParams"]["TID"];
		$arParams["MID"] = $arParams["arFormParams"]["MID"];
		
		$arParams["URL_TEMPLATES_LIST"] = $arParams["arFormParams"]["URL_TEMPLATES_LIST"];
		$arParams["URL_TEMPLATES_READ"] = $arParams["arFormParams"]["URL_TEMPLATES_READ"];
		
		$arParams["PAGE_NAME"] = $arParams["arFormParams"]["PAGE_NAME"];
		$arParams["MESSAGE_TYPE"] = $arParams["arFormParams"]["MESSAGE_TYPE"];
		$arParams["FORUM"] = $arParams["arFormParams"]["arForum"];
		$arParams["bVarsFromForm"] = $arParams["arFormParams"]["bVarsFromForm"];
		
		$arParams["PATH_TO_SMILE"] = $arParams["arFormParams"]["PATH_TO_SMILE"];
		$arParams["PATH_TO_ICON"] = $arParams["arFormParams"]["PATH_TO_ICON"];
		$arParams["CACHE_TIME"] = $arParams["arFormParams"]["CACHE_TIME"];
	}
// **************************** BASE *******************************************************
	$arParams["FID"] = intVal(empty($arParams["FID"]) ? $_REQUEST["FID"] : $arParams["FID"]);
	$arParams["TID"] = intVal(empty($arParams["TID"]) ? $_REQUEST["TID"] : $arParams["TID"]);
	$arParams["MID"] = intVal(empty($arParams["MID"]) ? $_REQUEST["MID"] : $arParams["MID"]);
	
	$arParams["PAGE_NAME"] = (empty($arParams["PAGE_NAME"]) ? $_REQUEST["PAGE_NAME"] : $arParams["PAGE_NAME"]);
	$arParams["MESSAGE_TYPE"] = (in_array(strToUpper($arParams["MESSAGE_TYPE"]), array("REPLY", "EDIT", "NEW")) ? strToUpper($arParams["MESSAGE_TYPE"]):"NEW");
	$arParams["FORUM"] = (!empty($arParams["arForum"]) ? $arParams["arForum"] : (!empty($arParams["FORUM"]) ? $arParams["FORUM"] : array()));
	$arParams["bVarsFromForm"] = ($arParams["bVarsFromForm"] == "Y" || $arParams["bVarsFromForm"] === true ? "Y" : "N");
// **************************** URL ********************************************************
	if (empty($arParams["URL_TEMPLATES_MESSAGE"]) && !empty($arParams["URL_TEMPLATES_READ"]))
		$arParams["URL_TEMPLATES_MESSAGE"] = $arParams["URL_TEMPLATES_READ"];
	$URL_NAME_DEFAULT = array(
			"list" => "PAGE_NAME=list&FID=#FID#",
			"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#");
	if (empty($arParams["URL_TEMPLATES_MESSAGE"]) && !empty($arParams["URL_TEMPLATES_READ"]))
	{
		$arParams["URL_TEMPLATES_MESSAGE"] = $arParams["URL_TEMPLATES_READ"];
	}
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~URL_TEMPLATES_".strToUpper($URL)] = $arParams["URL_TEMPLATES_".strToUpper($URL)];
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialchars($arParams["~URL_TEMPLATES_".strToUpper($URL)]);
	}
// **************************** ADDITIONAL *************************************************
	$arParams["AJAX_TYPE"] = ($arParams["AJAX_TYPE"] == "Y" ? "Y" : "N");
	$arParams["AJAX_CALL"] = (($_REQUEST["AJAX_CALL"] == "Y" && $arParams["AJAX_TYPE"] == "Y") ? "Y" : "N");
	$arParams["SMILE_TABLE_COLS"] = (intval($arParams["SMILE_TABLE_COLS"]) > 0 ? intval($arParams["SMILE_TABLE_COLS"]) : 3);
// **************************** CACHE ******************************************************
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;

// *************************** Default params **********************************************
	$arResult["SHOW_SEARCH"] = (IsModuleInstalled("search") ? "Y" : "N");
	$arResult["IsAuthorized"] = ($USER->IsAuthorized() ? "Y" : "N");
	$arResult["UserPermission"] = ForumCurrUserPermissions($arParams["FID"]);
	
	$bShowForm = false;
	if ($arParams["MESSAGE_TYPE"] == "REPLY" && $arParams["TID"] > 0)
		$bShowForm = CForumMessage::CanUserAddMessage($arParams["TID"], $USER->GetUserGroupArray(), $USER->GetID());
	elseif ($arParams["MESSAGE_TYPE"] == "EDIT" && $arParams["MID"] > 0)
		$bShowForm = CForumMessage::CanUserUpdateMessage($arParams["MID"], $USER->GetUserGroupArray(), intVal($USER->GetID()));
	elseif ($arParams["MESSAGE_TYPE"] == "NEW" && $arParams["FID"] > 0)
		$bShowForm = CForumTopic::CanUserAddTopic($arParams["FID"], $USER->GetUserGroupArray(), $USER->GetID());
	$arResult["SHOW_POST_FORM"] = ($bShowForm ? "Y" : "N");
	
	if ($arResult["SHOW_POST_FORM"] != "Y")
		return 0;

	if (count($arParams["FORUM"]) <= 0)
	{
		$db_res = CForumNew::GetList(array(), array("ID" => $arParams["FID"]));
		if ($db_res && ($res = $db_res->GetNext()))
			$arParams["FORUM"] = $res;
	}
		
	$arResult["str_USE_SMILES"] = "Y";
	$arResult["str_AUTHOR_ID"] = intVal($USER->GetParam("USER_ID"));
	if ($arParams["MESSAGE_TYPE"] == "EDIT")
	{
		$arMessage = CForumMessage::GetByID($arParams["MID"]);
		if ($arMessage)
		{
			$arTopic = CForumTopic::GetByID(intVal($arMessage["TOPIC_ID"]), array("NoFilter" => 'true'));
			$arResult["str_AUTHOR_NAME"] = htmlspecialchars($arMessage["AUTHOR_NAME"]);
			$arResult["str_AUTHOR_EMAIL"] = htmlspecialchars($arMessage["AUTHOR_EMAIL"]);
			$arResult["str_TITLE"] = htmlspecialchars($arTopic["TITLE"]);
			$arResult["str_TAGS"] = htmlspecialchars($arTopic["TAGS"]);
			$arResult["str_DESCRIPTION"] = htmlspecialchars($arTopic["DESCRIPTION"]);
			$arResult["str_POST_MESSAGE"] = htmlspecialchars($arMessage["POST_MESSAGE"]);
			$arResult["str_ICON_ID"] = intVal($arTopic["ICON_ID"]);
			$arResult["str_USE_SMILES"] = ($arMessage["USE_SMILES"]=="Y") ? "Y" : "N";
			$arResult["str_AUTHOR_ID"] = intVal($arMessage["AUTHOR_ID"]);
			$arResult["str_ATTACH_IMG"] = $arMessage["ATTACH_IMG"];
			$arResult["EDITOR_NAME"] = htmlspecialchars($arMessage["EDITOR_NAME"]);
			$arResult["EDITOR_EMAIL"] = htmlspecialchars($arMessage["EDITOR_EMAIL"]);
			$arResult["EDIT_REASON"] = htmlspecialchars($arMessage["EDIT_REASON"]);
		}
	}
	
	if ($arParams["bVarsFromForm"] == "Y")
	{
		$arResult["str_AUTHOR_NAME"] = htmlspecialchars($_REQUEST["AUTHOR_NAME"]);
		$arResult["str_AUTHOR_EMAIL"] = htmlspecialchars($_REQUEST["AUTHOR_EMAIL"]);
		$arResult["str_TITLE"] = htmlspecialchars($_REQUEST["TITLE"]);
		$arResult["str_TAGS"] = htmlspecialchars($_REQUEST["TAGS"]);
		$arResult["str_DESCRIPTION"] = htmlspecialchars($_REQUEST["DESCRIPTION"]);
		$arResult["str_POST_MESSAGE"] = htmlspecialchars($_REQUEST["POST_MESSAGE"]);
		$arResult["str_ICON_ID"] = intVal($_REQUEST["ICON_ID"]);
		$arResult["str_USE_SMILES"] = ($_REQUEST["USE_SMILES"]=="Y" ? "Y" : "N");
		
		$arResult["EDITOR_NAME"] = htmlspecialchars($_REQUEST["EDITOR_NAME"]);
		$arResult["EDITOR_EMAIL"] = htmlspecialchars($_REQUEST["EDITOR_EMAIL"]);
		$arResult["EDIT_REASON"] = htmlspecialchars($_REQUEST["EDIT_REASON"]);
	}
			
	$arResult["list"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $arParams["FID"], "TID" => $arParams["TID"]));
	$arResult["read"] = CComponentEngine::MakePathFromTemplate(
			$arParams["URL_TEMPLATES_MESSAGE"], 
			array("FID" => $arParams["FID"], "TID" => $arParams["TID"], 
				"MID"=>((intVal($arParams["MID"]) > 0) ? intVal($arParams["MID"]) : "s")));

	$arResult["str_HEADER"] = GetMessage("FPF_EDIT_FORM");
	if ($arParams["MESSAGE_TYPE"]=="NEW")
		$arResult["str_HEADER"] = GetMessage("FPF_CREATE_IN_FORUM")." ".$arParams["FORUM"]["NAME"];
	elseif ($arParams["MESSAGE_TYPE"]=="REPLY")
		$arResult["str_HEADER"] = GetMessage("FPF_REPLY_FORM");

	$arResult["SHOW_PANEL_GUEST"] = "N";
	if (($arParams["MESSAGE_TYPE"]=="NEW" || $arParams["MESSAGE_TYPE"]=="REPLY") && $arResult["IsAuthorized"] == "N" || $arParams["MESSAGE_TYPE"]=="EDIT" && $arResult["str_AUTHOR_ID"]<=0)
	{
		$arResult["SHOW_PANEL_GUEST"] = "Y";
		$arResult["str_AUTHOR_NAME"] = (strlen($arResult["str_AUTHOR_NAME"])>0) ? $arResult["str_AUTHOR_NAME"] : GetMessage("FPF_GUEST");
		$arResult["str_AUTHOR_EMAIL"] = (strlen($arResult["str_AUTHOR_EMAIL"])>0) ? $arResult["str_AUTHOR_EMAIL"] : "";
	}
	
	$arResult["SHOW_PANEL_NEW_TOPIC"] = "N";
	$arResult["ForumPrintIconsList"] = "";
	if ($arParams["MESSAGE_TYPE"]=="NEW" || $arParams["MESSAGE_TYPE"]=="EDIT" && CForumTopic::CanUserUpdateTopic($arParams["TID"], $USER->GetUserGroupArray(), $USER->GetID()))
	{
		$arResult["SHOW_PANEL_NEW_TOPIC"] = "Y";
		$arResult["str_TITLE"] = (strlen($arResult["str_TITLE"]) > 0) ? $arResult["str_TITLE"] : "";
		$arResult["str_DESCRIPTION"] = (strlen($arResult["str_DESCRIPTION"])>0) ? $arResult["str_DESCRIPTION"] : "";
		$arResult["ForumPrintIconsList"] = ForumPrintIconsList(7, "ICON_ID", $arResult["str_ICON_ID"], GetMessage("FPF_NO_ICON"), LANGUAGE_ID, $arParams["PATH_TO_ICON"], $arParams["CACHE_TIME"]);
	}
	
	$arResult["ForumPrintSmilesList"] = "";
	if ($arParams["FORUM"]["ALLOW_SMILES"]=="Y")
	{
		$arResult["ForumPrintSmilesList"] = ForumPrintSmilesList($arParams["SMILE_TABLE_COLS"], LANGUAGE_ID, $arParams["PATH_TO_SMILE"], $arParams["CACHE_TIME"]);
	}
	$arResult["SHOW_PANEL_EDIT"] = "N";
	$arResult["SHOW_PANEL_EDIT_PANEL_GUEST"] = "N";
	$arResult["SHOW_PANEL_EDIT_ASK"] = "N";
	if ($arParams["MESSAGE_TYPE"] == "EDIT")
	{
		$arResult["SHOW_PANEL_EDIT"] = "Y";
		if (!$USER->IsAuthorized())
			$arResult["SHOW_PANEL_EDIT_PANEL_GUEST"] = "Y";
			
		if (ForumCurrUserPermissions($arParams["FID"]) > "Q")
			$arResult["SHOW_PANEL_EDIT_ASK"] = "Y";
	}
		
		
	$arResult["TRANSLIT"] = "N";
	if (LANGUAGE_ID=="ru")
	{
		$arResult["TRANSLIT"] = "Y";
	}
	$arResult["str_POST_MESSAGE"]=(strlen($arResult["str_POST_MESSAGE"])>0) ? $arResult["str_POST_MESSAGE"]: "";
	$arResult["SHOW_SUBSCRIBE"] = "N";
	if ($USER->IsAuthorized() && (ForumCurrUserPermissions($arParams["FID"]) > "E"))
	{
		$arResult["SHOW_SUBSCRIBE"] = "Y";
		$arFields = array(
			"USER_ID" => $USER->GetID(),
			"FORUM_ID" => $arParams["FID"],
			"SITE_ID" => LANG
			);
		$db_res = CForumSubscribe::GetList(array(), $arFields);
		$arResult["TOPIC_SUBSCRIBE"] = "N";
		$arResult["FORUM_SUBSCRIBE"] = "N";
		if ($db_res)
		{
			while ($res = $db_res->Fetch())
			{
				if (intVal($res["TOPIC_ID"]) <= 0)
				{
					$arResult["FORUM_SUBSCRIBE"] = "Y";
				}
				elseif($res["TOPIC_ID"] == $arParams["TID"]) 
				{
					$arResult["TOPIC_SUBSCRIBE"] = "Y";
				}
			}
		}
	}
	$arResult["SHOW_PANEL_ATTACH_IMG"] = "N";
	if ($arParams["FORUM"]["ALLOW_UPLOAD"]=="Y" || $arParams["FORUM"]["ALLOW_UPLOAD"]=="F" || $arParams["FORUM"]["ALLOW_UPLOAD"]=="A")
	{
		$arResult["SHOW_PANEL_ATTACH_IMG"] = "Y";
		$arResult["str_ATTACH_IMG_FILE"] = false;
		if (strlen($arResult["str_ATTACH_IMG"])>0)
		{
			$arResult["str_ATTACH_IMG_FILE"] = CFile::GetFileArray($arResult["str_ATTACH_IMG"]);
			if ($arResult["str_ATTACH_IMG_FILE"] !== false)
			{
				$arResult["str_ATTACH_IMG"] = CFile::ShowImage($arResult["str_ATTACH_IMG_FILE"]["SRC"], 200, 200, "border=0");
			}
		}
	}
	
	$arResult["CAPTCHA_CODE"] = "";
	if (!$USER->IsAuthorized() && $arParams["FORUM"]["USE_CAPTCHA"]=="Y")
	{
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/captcha.php");
		$cpt = new CCaptcha();
		$captchaPass = COption::GetOptionString("main", "captcha_password", "");
		if (strlen($captchaPass) <= 0)
		{
			$captchaPass = randString(10);
			COption::SetOptionString("main", "captcha_password", $captchaPass);
		}
		$cpt->SetCodeCrypt($captchaPass);
		$arResult["CAPTCHA_CODE"] = htmlspecialchars($cpt->GetCodeCrypt());
	}
	
	$arResult["SUBMIT"] = GetMessage("FPF_EDIT");
	if ($arParams["MESSAGE_TYPE"]=="NEW")
		$arResult["SUBMIT"] = GetMessage("FPF_SEND");
	elseif ($arParams["MESSAGE_TYPE"]=="REPLY")
		$arResult["SUBMIT"] = GetMessage("FPF_REPLY");
		
// *****************************************************************************************
	$arResult["FID"] = $arParams["FID"];
	$arResult["TID"] = $arParams["TID"];
	$arResult["MID"] = $arParams["MID"];
	$arResult["FORUM"] = $arParams["FORUM"];
	$arResult["TOPIC"] = $arTopic;
	$arResult["MESSAGE_TYPE"] = $arParams["MESSAGE_TYPE"];
	$arResult["PAGE_NAME"] = $arParams["PAGE_NAME"];
	$arResult["LANGUAGE_ID"] = LANGUAGE_ID;
	$arResult["VIEW"] = ($arParams["VIEW"] != "Y" ? "N" : "Y");
	$arResult["SHOW_CLOSE_ALL"] = "N";
	if ($arResult["FORUM"]["ALLOW_BIU"] == "Y" || $arResult["FORUM"]["ALLOW_FONT"] == "Y" || $arResult["FORUM"]["ALLOW_ANCHOR"] == "Y" || $arResult["FORUM"]["ALLOW_IMG"] == "Y" || $arResult["FORUM"]["ALLOW_QUOTE"] == "Y" || $arResult["FORUM"]["ALLOW_CODE"] == "Y" || $arResult["FORUM"]["ALLOW_LIST"] == "Y")
		$arResult["SHOW_CLOSE_ALL"] = "Y";
	$arResult["sessid"] = bitrix_sessid_post();
// *****************************************************************************************
	$this->IncludeComponentTemplate();
?>