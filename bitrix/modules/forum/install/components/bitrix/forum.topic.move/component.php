<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (CModule::IncludeModule("forum")):
	if ($USER->IsAuthorized()):
// *****************************************************************************************
	$arResult = array();
	$arResult["TOPIC"] = array();
	$strErrorMessage = "";
	$strOKMessage = "";
	$bVarsFromForm = false;
	$arTopic = array();
	$arTopicID = array();
	$topics	= false;
	$GLOBALS['APPLICATION']->ResetException();
	$result = false;
	
// ****************************** Params ***************************************************
// ****************************** BASE *****************************************************
	$arParams["FID"] = intVal(empty($arParams["FID"]) ? $_REQUEST["FID"] : $arParams["FID"]);
	$arParams["TID"] = (empty($arParams["TID"]) ? $_REQUEST["TID"] : $arParams["TID"]);
	$arParams["newFID"] = intVal($_REQUEST["newFID"]);
// ****************************** URL ******************************************************
	$URL_NAME_DEFAULT = array(
			"index" => "",
			"topic_move" => "PAGE_NAME=MOVE&FID=#FID#&TID=#TID#",
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
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialchars($arParams["~URL_TEMPLATES_".strToUpper($URL)]);
	}
// ****************************** CACHE ****************************************************
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;
// ****************************** /Params ***************************************************

// *****************************************************************************************
	$arResult["FORUM"] = CForumNew::GetByID($arParams["FID"]);
	if (!$arResult["FORUM"])
	{
		LocalRedirect(ForumAddPageParams(
			CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_INDEX"], 
				array()), array("result" => "fid_is_lost")));
		die();
	}
// *****************************************************************************************
	ForumSetLastVisit($arParams["FID"]);
// *****************************************************************************************
	$arResult["CURRENT_PAGE"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_TOPIC_MOVE"], 
		array("FID" => $arParams["FID"], "TID" => $arParams["TID"]));
	$arResult["UserPermission"] = ForumCurrUserPermissions($arResult["FORUM"]["ID"]);
	if ($arResult["UserPermission"] < "Q")
		$APPLICATION->AuthForm(GetMessage("FM_NO_FPERMS"));
	$topics = ForumMessageExistInArray($arParams["TID"]);
	if (empty($topics))
	{
		LocalRedirect(ForumAddPageParams(
			CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], 
				array("FID" => $arParams["FID"])), array("result"=>"tid_is_empty")));
		die();
	}
// *************************** Actions *****************************************************
	if ((strToUpper($_REQUEST["action"])=="MOVE") && check_bitrix_sessid())
	{
		if (intVal($arParams["newFID"])<=0)
			$strErrorMessage = GetMessage("FM_EMPTY_DEST_FORUM").". \n";
		else 
		{
			$arResult["FORUM_NEW"] = CForumNew::GetByID($arParams["newFID"]);
			if ((ForumCurrUserPermissions($arParams["newFID"]) < "Q") && ($arResult["FORUM_NEW"]["ALLOW_MOVE_TOPIC"]!="Y"))
				$strErrorMessage = GetMessage("FM_NO_DEST_FPERMS").". \n";
			else 
				$result = CForumTopic::MoveTopic2Forum($topics, $arParams["newFID"], $_REQUEST["leaveLink"]);
		}
		
		if (!$result)
		{
			if ($GLOBALS['APPLICATION']->GetException())
			{
				$arErr = $GLOBALS['APPLICATION']->ERROR_STACK;
				if (is_array($arErr) && count($arErr) > 0)
				{
					foreach ($arErr as $res)
						$strErrorMessage .= $res["msg"]."\n";
				}
				$err = $GLOBALS['APPLICATION']->GetException();
				$strErrorMessage .= $err->GetString();
			}
			$bVarsFromForm = true;
		}
		else
		{
			BxClearCache(true, "/".SITE_ID."/forum/forum/".$arParams["FID"]."/");
			BxClearCache(true, "/".SITE_ID."/forum/forum/".$arParams["newFID"]."/");
			LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $arResult["FORUM_NEW"]["ID"])));
		}
	}
	
	$arFilter = array("@ID" => implode(",", $topics), "FORUM_ID" => $arParams["FID"]);
	if (!$USER->IsAdmin())
		$arFilter["PERMISSION_STRONG"] = true;
		
	$db_res = CForumTopic::GetListEx(array(), $arFilter);
	if ($db_res && ($res = $db_res->GetNext()))
	{
		do
		{
			$res["read"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"], 
				array("FID" => $res["FORUM_ID"], "TID" => $res["ID"], "MID" => "s"));
			$res["read_last_message"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"], 
				array("FID" => $res["FORUM_ID"], "TID" => $res["ID"], "MID" => intVal($res["LAST_MESSAGE_ID"])))."#message".$res["LAST_MESSAGE_ID"];
			$res["USER_START_HREF"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => intVal($res["USER_START_ID"])));
			$res["LAST_POSTER_HREF"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => intVal($res["LAST_POSTER_ID"])));
			
			$arTopic[] = $res;
			$arTopicID[] = $res["ID"];
		}while ($res = $db_res->GetNext());
		$arResult["TOPIC"] = $arTopic;
	}
	
// *****************************************************************************************
// *****************************************************************************************
// *****************************************************************************************
// *****************************************************************************************
	$arResult["ERROR_MESSAGE"] = $strErrorMessage;
	$arResult["OK_MESSAGE"] = $strOKMessage;
	$arParams["TID"] = implode(",", $arTopicID);
	$arResult["sessid"] = bitrix_sessid_post();
	$arResult["list"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $arResult["FORUM"]["ID"]));
// *****************************************************************************************
// *****************************************************************************************
		$arFilter = array();
		if (!$USER->IsAdmin())
		{
			$arFilter = array("LID" => SITE_ID, "PERMS" => array($USER->GetGroups(), 'A'), "ACTIVE" => "Y");
		}
		else 
		{
			if ($arParams["SHOW_FORUM_ANOTHER_SITE"] == "N")
				$arFilter = array("LID" => SITE_ID);
		}

		$cache = new CPHPCache;
		$cache_id = "forum_forums_".serialize($arFilter);
		$cache_path = "/".SITE_ID."/forum/forums/";
		if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
		{
			$res = $cache->GetVars();
			if (is_array($res["arForums"]))
				$arForums = $res["arForums"];
		}
		
		if (!is_array($arForums) || empty($arForums))
		{
			if ($arParams["CACHE_TIME"] > 0)
				$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
				
			$db_res = CForumNew::GetListEx(array("FORUM_GROUP_SORT"=>"ASC", "FORUM_GROUP_ID"=>"ASC", "SORT"=>"ASC", "NAME"=>"ASC"), $arFilter);
			if ($db_res && ($res = $db_res->GetNext()))
			{
				do 
				{
					$arForums[] = $res;
				} while ($res = $db_res->GetNext());
			}
			if ($arParams["CACHE_TIME"] > 0)
				$cache->EndDataCache(array("arForums"=>$arForums));
		}
		// ********************************************************************************
		$arResult["arForum"]["data"] = array();
		if ($db_res && ($res = $db_res->Fetch()))
		{
			do {
				if ($arParams["newFID"] == $res["ID"])
					$arResult["arForum"]["active"] = $res["ID"];
				$arResult["arForum"]["data"][] = $res;
			}while ($res = $db_res->Fetch());
		}
		
		if (is_array($arForums) && (count($arForums)>0))
		{
			foreach ($arForums as $res)
			{
				if ($arParams["FID"] == $res["ID"])
					$arResult["arForum"]["active"] = $res["ID"];
				$arResult["arForum"]["data"][] = $res;
			}
		}
// *****************************************************************************************
	if ($arParams["SET_NAVIGATION"] != "N")
	{
		if ($arParams["ADD_INDEX_NAV"] == "Y")
		{
			$APPLICATION->AddChainItem(GetMessage("F_INDEX"), CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_INDEX"], array()));
		}
		$APPLICATION->AddChainItem($arResult["FORUM"]["NAME"], $arResult["list"]);
		$APPLICATION->AddChainItem(GetMessage("FM_TITLE"));
	}
// *****************************************************************************************
if ($arParams["SET_TITLE"] != "N")
	$APPLICATION->SetTitle(GetMessage("FM_TITLE"));
// *****************************************************************************************
	$this->IncludeComponentTemplate();
// *****************************************************************************************
	else:
		$APPLICATION->AuthForm(GetMessage("FM_AUTH"));
	endif;
else:
	ShowError(GetMessage("F_NO_MODULE"));
endif;?>