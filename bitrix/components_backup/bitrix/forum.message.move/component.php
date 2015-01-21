<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("FM_NO_MODULE"));
	return 0;
elseif (!$GLOBALS["USER"]->IsAuthorized()):
	$APPLICATION->AuthForm(GetMessage("FM_AUTH"));
	return 0;
endif;
// ************************* Input params***************************************************
// ***************************** BASE ******************************************************
	$arParams["FID"] = intVal(empty($arParams["FID"]) ? $_REQUEST["FID"] : $arParams["FID"]);
	$arParams["TID"] = intVal(empty($arParams["TID"]) ? $_REQUEST["TID"] : $arParams["TID"]);
	$arParams["MID"] = empty($arParams["MID"]) ? $_REQUEST["MID"] : $arParams["MID"];
	$arParams["newTID"] = intVal($_REQUEST["newTID"]);
	$arParams["action"] = strToUpper($_REQUEST["ACTION"]);
	$arParams["newFID"] = intVal($_REQUEST["newFID"]);
// ***************************** URL *******************************************************
	$URL_NAME_DEFAULT = array(
		"index" => "",
		"list" => "PAGE_NAME=list&FID=#FID#",
		"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#",
		"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
		"topic_search" => "PAGE_NAME=topic_search");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~URL_TEMPLATES_".strToUpper($URL)] = $arParams["URL_TEMPLATES_".strToUpper($URL)];
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialchars($arParams["~URL_TEMPLATES_".strToUpper($URL)]);
	}
// ***************************** ADDITIONAL ************************************************
	$arParams["PATH_TO_SMILE"] = trim($arParams["PATH_TO_SMILE"]);
	$arParams["PATH_TO_ICON"] = trim($arParams["PATH_TO_ICON"]);
	$arParams["WORD_LENGTH"] = intVal($arParams["WORD_LENGTH"]);
	$arParams["IMAGE_SIZE"] = (intVal($arParams["IMAGE_SIZE"]) > 0 ? $arParams["IMAGE_SIZE"] : 300);
	$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "N" ? "N" : "Y");
	$arParams["ADD_INDEX_NAV"] = ($arParams["ADD_INDEX_NAV"] == "Y" ? "Y" : "N");
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
// *****************************/Params ****************************************************

// *************************Default params**************************************************************
	$arResult["MESSAGE"] = array();
	$arResult["TOPIC"] = array();
	$arResult["FORUM"] = array();
	$arResult["NEW_TOPIC"] = array(
		"TOPIC" => array(),
		"FORUM" => array());
	$arResult["VALUES"] = array();
	$strErrorMessage = "";
	$strOKMessage = "";
	$message = array();
	$bVarsFromForm = false;
// *****************************************************************************************
	$res = CForumTopic::GetByIDEx($arParams["TID"], array("GET_FORUM_INFO" => "Y"));
	if (!empty($res))
	{
		$arResult["TOPIC"] = $res["TOPIC_INFO"];
		$arResult["FORUM"] = $res["FORUM_INFO"];
		$arParams["FID"] = $arResult["FORUM"]["ID"];
	}
	else 
	{
		LocalRedirect(
			ForumAddPageParams(
				CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_LIST"], array("FID" => $arParams["FID"])), 
				array("result" => "tid_is_lost")));
		die();
	}
// *****************************************************************************************
	$message = ForumDataToArray($arParams["MID"]);
	if ($message)
	{
		$db_res = CForumMessage::GetListEx(array("ID"=>"ASC"), array("@ID" => implode(", ", $message), "TOPIC_ID" => $arParams["TID"]));
		if ($db_res && ($res = $db_res->GetNext()))
		{
			do 
			{
				$arResult["MESSAGE"][] = $res;
			}while ($res = $db_res->GetNext());
		}
	}
	if (count($arResult["MESSAGE"]) <= 0)
	{
		LocalRedirect(
			ForumAddPageParams(
				CComponentEngine::MakePathFromTemplate(
					$arParams["~URL_TEMPLATES_READ"], 
					array("FID" => $arResult["FORUM"]["ID"], "TID" => $arParams["TID"], "MID" => "s")),
				array("result" =>"mid_for_move_is_empty")));
		die();
	}

// ************** Only moderators can view this page ***************************************
	if (ForumCurrUserPermissions($arParams["FID"])<"Q")
		$APPLICATION->AuthForm(GetMessage("FM_NO_FPERMS"));
// *****************************************************************************************
	ForumSetLastVisit($arParams["FID"]);
// *****************************************************************************************
	if (intVal($_REQUEST["step"]) == 1)
	{
		if (check_bitrix_sessid())
		{
			switch ($arParams["action"])
			{
				case "MOVE_TO_TOPIC":
					if (ForumMoveMessage($arParams["FID"], $arParams["TID"], $message, $arParams["newTID"], array(), $strErrorMessage, $strOKMessage))
					{
						LocalRedirect(CComponentEngine::MakePathFromTemplate(
							$arParams["~URL_TEMPLATES_READ"], 
							array("FID" => $arResult["FORUM"]["ID"], "TID" => $arParams["newTID"], "MID" => "s")));
					}
					else
					{
						$bVarsFromForm = true;
					}
					break;
				case "MOVE_TO_NEW":
					$arFields = array(
						"TITLE"=>trim($_REQUEST["TITLE"]), 
						"DESCRIPTION"=>trim($_REQUEST["DESCRIPTION"]), 
						"ICON_ID"=>intVal($_REQUEST["ICON_ID"]),
						"TAGS" => $_REQUEST["TAGS"]);
					if (strLen($arFields["TITLE"]) > 0)
					{
						if (ForumMoveMessage($arParams["FID"], $arParams["TID"], $message, 0, $arFields, $strErrorMessage, $strOKMessage))
						{
							$res = CForumMessage::GetByID($message[0]);
							$arParams["TID"] = intVal($res["TOPIC_ID"]);
							LocalRedirect(CComponentEngine::MakePathFromTemplate(
								$arParams["~URL_TEMPLATES_READ"], 
								array("FID" => $arResult["FORUM"]["ID"], "TID" => $arParams["TID"], "MID" => "s")));
						}
					}
					else 
					{
						$strErrorMessage .= GetMessage('FM_ERR_NO_DATA').".\n";
						$bVarsFromForm = true;
					}
					break;
				default:
					$strErrorMessage .= "Unknown action.\n";
					$bVarsFromForm = true;
					break;					
			}
		}
		elseif (!check_bitrix_sessid())
		{
			$strErrorMessage .= "Bad sessid.\n";
			$bVarsFromForm = true;
		}
		
		if ($bVarsFromForm)
		{
			if (!empty($arParams["newTID"]))
			{
				$res = CForumTopic::GetByIDEx($arParams["newTID"]);
				$arResult["NEW_TOPIC"] = array(
					"TOPIC" => $res["TOPIC_INFO"],
					"FORUM" => $res["FORUM_INFO"]);
			}
			if (empty($strErrorMessage))
				$strErrorMessage = "Unknown Error";

			$arResult["VALUES"]["TITLE"] = htmlspecialcharsEx($_REQUEST["TITLE"]);
			$arResult["VALUES"]["DESCRIPTION"] = htmlspecialcharsEx($_REQUEST["DESCRIPTION"]);
			$arResult["VALUES"]["ICON_ID"] = intVal($_REQUEST["ICON_ID"]);
		}
	}
// *****************************************************************************************
	$arResult["TOPIC"]["read"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"], 
		array("FID" => $arResult["FORUM"]["ID"], "TID" => $arResult["TOPIC"]["ID"], "MID" => "s"));
	$arResult["FORUM"]["list"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], 
		array("FID" => $arResult["FORUM"]["ID"]));
	$arResult["topic_search"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_TOPIC_SEARCH"], array());
	$arResult["ERROR_MESSAGE"] = $strErrorMessage;
	$arResult["OK_MESSAGE"] = $strOKMessage;
	$arResult["sessid"] = bitrix_sessid_post();
	if (strLen($strErrorMessage) > 0)
	{
	}
	$arResult["ForumPrintIconsList"] = ForumPrintIconsList(7, "ICON_ID", intVal($_REQUEST["ICON_ID"]), GetMessage("FM_NO_ICON"), LANGUAGE_ID, $arParams["PATH_TO_ICON"]);
	
	$parser = new textParser(LANGUAGE_ID, $arParams["PATH_TO_SMILE"]);
	$parser->MaxStringLen = $arParams["WORD_LENGTH"];
	$parser->image_params["width"] = $arParams["IMAGE_SIZE"];
	$parser->image_params["height"] = $arParams["IMAGE_SIZE"];
	
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
	
	$arMessage = array();
	$cache = new CPHPCache;
	$cache_id = "forum_avatar_".$res["AVATAR"];
	$cache_path = "/".SITE_ID."/forum/avatar/".$res["AVATAR"]."/";
	foreach ($arResult["MESSAGE"] as $key => $res)
	{
		$res["profile_view"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $res["AUTHOR_ID"]));
		if (strLen($res["AVATAR"])>0)
		{
			// ******************************************************************************************
			if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
			{
				$cache_result = $cache->GetVars();
				if (is_array($cache_result["AVATAR"]) && (count($cache_result["AVATAR"]) > 0) && ($cache_result["AVATAR"]["ID"] == $res["AVATAR"]))
					$res["AVATAR"] = $cache_result["AVATAR"];
			}
			else
			{
				if ($arParams["CACHE_TIME"] > 0)
					$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
				$res["AVATAR"] = array("ID" => $res["AVATAR"]);
				$res["AVATAR"]["FILE"] = CFile::GetFileArray($res["AVATAR"]["ID"]);
				$res["AVATAR"]["HTML"] = CFile::ShowImage($res["AVATAR"]["FILE"]["SRC"], COption::GetOptionString("forum", "avatar_max_width", 90), COption::GetOptionString("forum", "avatar_max_height", 90), "border=\"0\" vspace=\"5\" hspace=\"5\"", "", true);
				
				if ($arParams["CACHE_TIME"] > 0)
					$cache->EndDataCache(array("AVATAR" => $res["AVATAR"]));
			}
			// *****************************************************************************************
		}
		
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
		
		$arResult["MESSAGE"][$key] = $res;
	}
	$arResult["PARSER"] = $parser;
// *****************************************************************************************
	if ($arParams["SET_NAVIGATION"] != "N")
	{
		if ($arParams["ADD_INDEX_NAV"] == "Y")
		{
			$APPLICATION->AddChainItem(GetMessage("FM_INDEX"), CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_INDEX"], array()));
		}
		$APPLICATION->AddChainItem($arResult["FORUM"]["NAME"], CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $arParams["FID"])));
		$APPLICATION->AddChainItem($arResult["TOPIC"]["TITLE"], $arResult["TOPIC"]["read"]);
	}
// *****************************************************************************************
	if ($arParams["SET_TITLE"] != "N")
		$APPLICATION->SetTitle(GetMessage("FM_TITLE_PAGE"));
// *****************************************************************************************
	$this->IncludeComponentTemplate();
// *****************************************************************************************
?>