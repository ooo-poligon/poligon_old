<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (CModule::IncludeModule("forum")):
	if ($USER->IsAuthorized()):
// *****************************************************************************************
	$arError = array();
	$arOK = array();
	$bAction = false;
	$message = array();
	$arFilter = array("APPROVED" => "N");
// *****************************************************************************************
	$arParams["FID"] = intVal(intVal($arParams["FID"]) <= 0 ? $_REQUEST["FID"] : $arParams["FID"]);
	$arParams["TID"] = intVal(intVal($arParams["TID"]) <= 0 ? $_REQUEST["TID"] : $arParams["TID"]);
	$arParams["action"] = strToUpper(trim($_REQUEST["ACTION"]));
	$PAGE_ELEMENTS = intVal(intVal($arParams["MESSAGES_PER_PAGE"]) > 0 ? $arParams["MESSAGES_PER_PAGE"] : 
		COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10"));
	$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
// *****************************************************************************************
	$URL_NAME_DEFAULT = array(
		"index" => "",
		"list" => "PAGE_NAME=list&FID=#FID#",
		"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#",
		"profile_view" => "PAGE_NAME=profile_view&UID=#UID#");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialchars($arParams["URL_TEMPLATES_".strToUpper($URL)]);
	}
// *****************************************************************************************
	if (ForumCurrUserPermissions($arParams["FID"])<"Q")
		$APPLICATION->AuthForm(GetMessage("FMA_NO_PERMS"));
		
	$arResult["FORUM"] = CForumNew::GetByID($arParams["FID"]);
	
	if (!$arResult["FORUM"])
	{
		LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_INDEX"], array()));
		die();
	}
	
	if ($arParams["TID"] > 0)
	{
		$arResult["TOPIC"] = CForumTopic::GetByID($arParams["TID"]);
		if (!$arResult["TOPIC"])
			$arParams["TID"] = 0;
	}
	
// *****************************************************************************************
	if (check_bitrix_sessid())
	{
		$message = (empty($_REQUEST["MID_ARRAY"]) ? $_REQUEST["MID"] : $_REQUEST["MID_ARRAY"]);
		$message = (empty($message) ? $_REQUEST["message_id"] : $message);
		if (!is_array($message))
			$message = explode(",", $message);
		$message = ForumMessageExistInArray($message);
		
		if (!$message)
			$strErrorMessage .= GetMessage("FMA_NO_MESSAGE").".\n";
		else 
		{
			$action = strToUpper($_REQUEST["ACTION"]);
			switch ($action)
			{
				case "DEL":
					if (ForumDeleteMessageArray($message, $strErrorMessage, $strOKMessage))
						$bAction = true;
				break;
				case "SHOW":
				case "HIDE":
					if (ForumModerateMessageArray($message, $action, $strErrorMessage, $strOKMessage))
						$bAction = true;
				break;
			}
			if ($action)
			{
				$res = CForumMessage::GetList(array("ID"=>"ASC"), $arFilter);
				if ($res <= 0)
					LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $arParams["FID"])));
			}

		}
	}
// *****************************************************************************************
	$arResult["ERROR_MESSAGE"] = $strErrorMessage;
	$arResult["OK_MESSAGE"] = $strOKMessage;
// *****************************************************************************************
	$arFilter = array();
	if ($arParams["TID"] > 0)	
		$arFilter["TOPIC_ID"] = $arParams["TID"];
	else 
		$arFilter["FORUM_ID"] = $arParams["FID"];
	$arFilter["APPROVED"] = "N";

	$db_Message = CForumMessage::GetListEx(array("ID"=>"ASC"), $arFilter);
	$db_Message->NavStart($PAGE_ELEMENTS, false);
	$arResult["NAV_RESULT"] = $db_Message;
	$arResult["NAV_STRING"] = $db_Message->GetPageNavStringEx($navComponentObject, GetMessage("FMA_TITLE_NAV"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arResult["list"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $arParams["FID"]));
	$arResult["read"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"], 
		array("FID" => $arParams["FID"], "TID" => $arParams["TID"], "MID" => "s"));
	$arResult["MESSAGE"] = array();
	$arResult["SHOW_RESULT"] = "N";
	
	if ($db_Message && ($res = $db_Message->GetNext()))
	{
		$parser = new textParser(LANGUAGE_ID, $arParams["PATH_TO_SMILE"]);
		$parser->MaxStringLen = $arParams["WORD_LENGTH"];
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
			"NL2BR" => $arResult["FORUM"]["ALLOW_NL2BR"]
		);
		
		$arResult["SHOW_RESULT"] = "Y";
		do
		{
			$res["profile_view"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $res["AUTHOR_ID"]));
		
			if (strLen($res["AVATAR"])>0)
				$res["AVATAR"] = CFile::ShowImage($res["AVATAR"], 90, 90, "border=0 vspace=5", "", true);
			$res["AUTHOR_NAME"] = $parser->wrap_long_words($res["AUTHOR_NAME"]);
			$res["DESCRIPTION"] = $parser->wrap_long_words($res["DESCRIPTION"]);
		
			if (COption::GetOptionString("forum", "FILTER", "Y")=="Y")
				$res["POST_MESSAGE_TEXT"] = $res["~POST_MESSAGE_FILTER"];
			else 
				$res["POST_MESSAGE_TEXT"] = $res["~POST_MESSAGE"];
				
			$res["POST_MESSAGE_TEXT"] = $parser->convert($res["POST_MESSAGE_TEXT"], $arAllow);
			
			$res["ATTACH_IMG"] = "";
			$res["~ATTACH_FILE"] = array();
			$res["ATTACH_FILE"] = array();
			if (intVal($res["~ATTACH_IMG"])>0 && ($arResult["FORUM"]["ALLOW_UPLOAD"]=="Y" || 
				$arResult["FORUM"]["ALLOW_UPLOAD"]=="F" || $arResult["FORUM"]["ALLOW_UPLOAD"]=="A"))
			{
				$res["~ATTACH_FILE"] = CFile::GetFileArray($res["~ATTACH_IMG"]);
				$res["ATTACH_IMG"] = CFile::ShowFile($res["~ATTACH_IMG"], 0, 
					$arParams["IMAGE_SIZE"], $arParams["IMAGE_SIZE"], true, "border=0", false);
				$res["ATTACH_FILE"] = $res["ATTACH_IMG"];
			}
			
			$res["AUTHOR_NAME"] = $parser->wrap_long_words($res["AUTHOR_NAME"]);
			$res["DESCRIPTION"] = $parser->wrap_long_words($res["DESCRIPTION"]);
		
			if (strLen($res["SIGNATURE"])>0)
			{
				$arAllow["SMILES"] = "N";
				$res["SIGNATURE"] = $parser->convert($res["~SIGNATURE"], $arAllow);
			}
			
			$arResult["MESSAGE"][] = $res;
		}while ($res = $db_Message->GetNext());
	}
	else 
	{
		LocalRedirect($arResult["list"]);
	}
// *****************************************************************************************
	$arResult["sessid"] = bitrix_sessid_post();
	$arResult["PARSER"] = $parser;
// *****************************************************************************************
	if ($arParams["SET_NAVIGATION"] != "N")
	{
		$APPLICATION->AddChainItem(GetMessage("FMA_INDEX"), CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_INDEX"], array()));
		$APPLICATION->AddChainItem($arResult["FORUM"]["NAME"], $arResult["list"]);
		if ($arParams["TID"] > 0)
			$APPLICATION->AddChainItem($arResult["TOPIC"]["TITLE"], $arResult["read"]);
	}
// *****************************************************************************************
	if ($arParams["SET_TITLE"] != "N")
		$APPLICATION->SetTitle(GetMessage("FMA_TITLE"));
// *****************************************************************************************
	$this->IncludeComponentTemplate();
// *****************************************************************************************
	else:
		$APPLICATION->AuthForm(GetMessage("FMA_AUTH"));
	endif;
else:
	ShowError(GetMessage("FMA_NO_MODULE"));
endif;?>