<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
endif;
	$arForums = array();
	$arGroup = array();
	$arGroupForum = array();

// ************************* Input params***************************************************************
// ************************* BASE **********************************************************************
	$arParams["FID"] = intVal(empty($arParams["FID"]) ? $_REQUEST["FID"] : $arParams["FID"]);
	$arParams["TID"] = intVal(empty($arParams["TID"]) ? $_REQUEST["TID"] : $arParams["TID"]);
	
	$arParams["PAGE_NAME"] = trim(empty($arParams["PAGE_NAME"]) ? $_REQUEST["PAGE_NAME"] : $arParams["PAGE_NAME"]);
	$arParams["PAGE_NAME"] = trim(empty($arParams["PAGE_NAME"]) ? $arParams["index"] : $arParams["PAGE_NAME"]);
// ************************* URL ***********************************************************************
	$URL_NAME_DEFAULT = array(
			"index" => "",
			"list" => "PAGE_NAME=list&FID=#FID#",
			"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
			"subscr_list" => "PAGE_NAME=subscr_list",
			"active" => "PAGE_NAME=active",
			"search" => "PAGE_NAME=search",
			"help" =>"PAGE_NAME=help",
			"rules" =>"PAGE_NAME=rules",
			"user_list" => "PAGE_NAME=user_list",
			"pm_list" => "PAGE_NAME=pm_list&FID=#FID#",
			"pm_edit" => "PAGE_NAME=pm_edit&FID=#FID#&MID=#MID#&mode=#mode#",
			"pm_folder" => "PAGE_NAME=pm_folder");
		
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~URL_TEMPLATES_".strToUpper($URL)] = $arParams["URL_TEMPLATES_".strToUpper($URL)];
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialchars($arParams["~URL_TEMPLATES_".strToUpper($URL)]);
	}
	$arParams["PATH_TO_AUTH_FORM"] = trim($arParams["PATH_TO_AUTH_FORM"]);
// ************************* ADDITIONAL ****************************************************************
	$arParams["pm_version"] = intVal(COption::GetOptionString("forum", "UsePMVersion", "2"));	
	$arParams["FID_RANGE"] = (is_array($arParams["FID_RANGE"]) && !empty($arParams["FID_RANGE"]) ? $arParams["FID_RANGE"] : array());
	$arParams["SHOW_FORUM_ANOTHER_SITE"] = ($arParams["SHOW_FORUM_ANOTHER_SITE"] == "Y" ? "Y" : "N");
	$arParams["AJAX_TYPE"] = ($arParams["AJAX_TYPE"] == "Y" ? "Y" : "N");
// ************************* CACHE & TITLE *************************************************************
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;

// *************************/Input params***************************************************************
	$cache = new CPHPCache;
	$arTopic = array();
	if (intVal($arParams["TID"]) > 0)
	{
		$cache_id = "forum_topic_".$arParams["TID"];
		$cache_path = "/".SITE_ID."/forum/topic/".$arParams["TID"]."/";
		if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
		{
			$res = $cache->GetVars();
			if (is_array($res["arTopic"]))
				$arTopic = $res["arTopic"];
		}
		
		if (!is_array($arTopic) || empty($arTopic))
		{
			if ($arParams["CACHE_TIME"] > 0)
				$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
			$arTopic = CForumTopic::GetByID($arParams["TID"]);
			if ($arParams["CACHE_TIME"] > 0)
				$cache->EndDataCache(array("arTopic"=>$arTopic));
		}
		if (is_array($arTopic))
		{
			$arParams["FID"] = intVal($arTopic["FORUM_ID"]);
			$arResult["TOPIC"] = $arTopic;
		}
	}
// *****************************************************************************************
	$arResult["index"] 	= CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_INDEX"], array());
	$arResult["active"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_ACTIVE"], array());
	$arResult["list"] 	= CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $arParams["FID"]));
	$arResult["search"] = ForumAddPageParams(CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_SEARCH"], array()), array("FID" => $arParams["FID"]));
	$arResult["help"] 	= CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_HELP"], array());
	$arResult["rules"] 	= CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_RULES"], array());
	$arResult["profile_view"] 	= CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $USER->GetID()));
	$arResult["user_list"] 		= CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_USER_LIST"], array());
	$arResult["subscr_list"] 	= CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_SUBSCR_LIST"], array());
	$arResult["pm_list"] 		= CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_LIST"], array("FID"=>1));
	$arResult["pm_list_outcoming"] 		= CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_LIST"], array("FID"=>2));
	$arResult["pm_list_outbox"] 		= CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_LIST"], array("FID"=>3));
	$arResult["pm_list_recycled"] 		= CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_LIST"], array("FID"=>4));
	$arResult["pm_edit"] 		= CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_EDIT"], array("FID" => "1", "MID" => 0, "UID" => 0, "mode" => "new"));
	$arResult["pm_folder"] 		= CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_FOLDER"], array());
// *****************************************************************************************
// *****************************************************************************************
	$arResult["FID"] = intVal($arParams["FID"]);
	$arResult["PAGE_NAME"] = $arParams["PAGE_NAME"];
	$arResult["TID"] = intVal($arParams["TID"]);
	$arResult["sSection"] = strToUpper($arParams["PAGE_NAME"]);
	$arResult["IndexForForm"] = rand(0, 99999);
	$arResult["sessid_get"] = bitrix_sessid_get();
	$arResult["sessid_post"] = bitrix_sessid_post();
	$arResult["IsAuthorized"] = "N";
	
	if ($USER->IsAuthorized())
	{
		$arResult["IsAuthorized"] = "Y";
	}
	
	$arResult["SHOW_SEARCH"] = "N";
	if (IsModuleInstalled("search"))
	{
		$arResult["SHOW_SEARCH"] = "Y";
	}
	

	if ($arResult["IsAuthorized"] == "Y")
	{
		$arUserPM = array();
		$arResult["UID"] = intVal($USER->GetID());
		$arResult["~FULL_NAME"] = trim($USER->GetFullName());
		$arResult["FULL_NAME"] = htmlspecialcharsEx($arResult["~FULL_NAME"]);
		$arResult["~LOGIN"] = trim($USER->GetLogin());
		$arResult["LOGIN"] = htmlspecialcharsEx($arResult["~LOGIN"]);
		$arResult["UNREAD_PM"] = "";
		
		$cache_id = "forum_user_pm_".$arResult["UID"];
		$cache_path = "/bitrix/forum/user/".$arResult["UID"]."/";
		if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
		{
			$res = $cache->GetVars();
			if (is_array($res["arUserPM"]))
				$arUserPM = $res["arUserPM"];
		}
		
		if (!is_array($arUserPM) || empty($arUserPM))
		{
			if ($arParams["CACHE_TIME"] > 0)
				$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
			$arUserPM = CForumPrivateMessage::GetNewPM();
			if ($arParams["CACHE_TIME"] > 0)
				$cache->EndDataCache(array("arUserPM"=>$arUserPM));
		}
		
		
		if (intVal($arUserPM["UNREAD_PM"]) > 0)
		{
			$arResult["UNREAD_PM"] = " (".intVal($arUserPM["UNREAD_PM"]).")";
		}
		if (($arResult["sSection"]=="LIST" || $arResult["sSection"]=="READ"))
		{
			$arResult["~forum_subscribe"] = $APPLICATION->GetCurPageParam("ACTION=FORUM_SUBSCRIBE", array("ACTION", "login", "register", "logout", BX_AJAX_PARAM_ID))."&".bitrix_sessid_get();
			$arResult["forum_subscribe"] = htmlspecialchars($arResult["~forum_subscribe"]);
			$arResult["~forum_subscribe_this_topic"] = $APPLICATION->GetCurPageParam("ACTION=TOPIC_SUBSCRIBE", array("ACTION", "login", "register", "logout", BX_AJAX_PARAM_ID))."&".bitrix_sessid_get();
			$arResult["forum_subscribe_this_topic"] = htmlspecialchars($arResult["~forum_subscribe_this_topic"]);
			$arResult["~forum_subscribe_topics"] = $APPLICATION->GetCurPageParam("ACTION=FORUM_SUBSCRIBE_TOPICS", array("ACTION", "login", "register", "logout", BX_AJAX_PARAM_ID))."&".bitrix_sessid_get();
			$arResult["forum_subscribe_topics"] = htmlspecialchars($arResult["~forum_subscribe_topics"]);
		}
	}
	
	$arResult["new_user_registration"] = (COption::GetOptionString("main", "new_user_registration", "N")=="Y") ? "Y" : "N";
	$arResult["store_password"] = COption::GetOptionString("main", "store_password", "Y") == "Y" ? "Y" : "N";
	
	
	if ($arResult["IsAuthorized"] == "Y")
	{
		$arResult["UserPermission"] = ForumCurrUserPermissions($arParams["FID"]);
	
		if ((($arResult["sSection"] == "READ")||($arResult["sSection"] == "LIST")||($arResult["sSection"] == "APPR_MESSAGE"))  
			&& ($arResult["IsAuthorized"] == "Y") && (ForumCurrUserPermissions($arParams["FID"]) >= "Q")):
			 
			if ($arResult["sSection"] == "READ"):
				$arResult["PIN"]["value"] = (intVal($arTopic["SORT"])!=150)?"SET_ORDINARY":"SET_TOP";
				$arResult["PIN"]["text"] = (intVal($arTopic["SORT"])!=150)?GetMessage("FMI_TOPIC_UNPIN1"):GetMessage("FMI_TOPIC_PIN1");
				$arResult["OPEN"]["value"] = (($arTopic["STATE"]!="Y")?"STATE_Y":"STATE_N");
				$arResult["OPEN"]["text"] = (($arTopic["STATE"]!="Y")?GetMessage("FMI_TOPIC_OPEN1"):GetMessage("FMI_TOPIC_CLOSE1"));
			endif;
			
			$arResult["CanUserDeleteTopic"] = "N";
			if (($arParams["TID"] > 0) && (CForumTopic::CanUserDeleteTopic($arParams["TID"], $USER->GetUserGroupArray(), $USER->GetID())))
				$arResult["CanUserDeleteTopic"] = "Y";
			if (($arParams["FID"] > 0) && ($arResult["sSection"] == "LIST") && ForumCurrUserPermissions($arParams["FID"]) >= "U") 
				$arResult["CanUserDeleteTopic"] = "Y";
		endif;
	
		$resFolder = array();
		if (subStr($arResult["sSection"], 0, 3) == "PM_")
			$arResult["sSection"] = "PM";
		if ($USER->IsAuthorized() && ($arResult["sSection"]=="PM"))
		{
			$db_res = CForumPMFolder::GetList(array(), array("USER_ID" => $USER->GetId()));
			if ($db_res && $res = $db_res->GetNext())
			{
				do 
				{
					$res["pm_list"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_LIST"], array("FID"=>$res["ID"]));
					$resFolder[] = $res;
				}while ($res = $db_res->GetNext());
			}
		}
		$arResult["FOLDER_USER"] = $resFolder;
	}

	$arResult["backurl_encode"] = urlencode($APPLICATION->GetCurPageParam("", array("login", "logout", "register", "forgot_password", "change_password", BX_AJAX_PARAM_ID)));
	$arResult["backurl_ecode"] = htmlspecialchars($APPLICATION->GetCurPageParam("", array("login", "logout", "register", "forgot_password", "change_password", BX_AJAX_PARAM_ID)));
	
	if (strLen(trim($arParams["PATH_TO_AUTH_FORM"])) <= 0)
	{
		$arResult["AUTH"]["LOGIN"] = htmlspecialchars($APPLICATION->GetCurPageParam("auth=yes&backurl=".$arResult["backurl_encode"], array("login", "logout", "register", "forgot_password", "change_password", BX_AJAX_PARAM_ID)));
	}
	else 
	{
		$arResult["AUTH"]["LOGIN"] = htmlspecialchars(
			ForumAddPageParams(
				$arParams["PATH_TO_AUTH_FORM"], 
				array(
					"auth" => "yes",
					"backurl" => $arResult["backurl_encode"]
				), false, false));
	}
	// For custom components before 6.0.0 Don`t delete
	if ($arResult["IsAuthorized"] == "Y")
		$arResult["backurl"] = htmlspecialchars($APPLICATION->GetCurPageParam("logout=yes", array("login", "logout", "register", "forgot_password", "change_password", BX_AJAX_PARAM_ID)));
	else
		$arResult["backurl"] = urlencode($APPLICATION->GetCurPageParam("", array("login", "logout", "register", "forgot_password", "change_password", BX_AJAX_PARAM_ID)));

	
	$arResult["AUTH"]["REGISTER"] = htmlspecialchars($APPLICATION->GetCurPageParam("register=yes&backurl=".$arResult["backurl_encode"], array("login", "logout", "register", "forgot_password", "change_password", BX_AJAX_PARAM_ID)));
	
	$arResult["AUTH"]["LOGOUT"] = htmlspecialchars($APPLICATION->GetCurPageParam("logout=yes", array("login", "logout", "register", "forgot_password", "change_password", BX_AJAX_PARAM_ID)));
	
	$arResult["AUTH"]["USER_LOGIN"] = htmlspecialchars($_COOKIE[COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_LOGIN"]);
	
// **************** Forums list for fast access ********************************************
	if ($arParams["SHOW_FORUMS_LIST"] != "N")
	{
		$cache = new CPHPCache;
		$cache_id = "forum_group_".LANGUAGE_ID;
		$cache_path = "/bitrix/forum/group/".LANGUAGE_ID."/";
		if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
		{
			$res = $cache->GetVars();
			if (is_array($res["arGroup"]))
				$arGroup = $res["arGroup"];
		}
		if (!is_array($arGroup) || empty($arGroup))
		{
			if ($arParams["CACHE_TIME"] > 0)
				$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
				
			$db_res = CForumGroup::GetListEx(array(), array("LID" => LANGUAGE_ID));
			if ($db_res && ($res = $db_res->GetNext()))
			{
				do 
				{
					$arGroup[intVal($res["ID"])] = $res;
					$arGroup[intVal($res["ID"])]["FORUM"] = array();
				}while ($res = $db_res->GetNext());
			}
			if ($arParams["CACHE_TIME"] > 0)
				$cache->EndDataCache(array("arGroup"=>$arGroup));
		}
		
		if (!$USER->IsAdmin())
		{
			$arFilter = array("LID" => SITE_ID, "PERMS" => array($USER->GetGroups(), 'A'), "ACTIVE" => "Y");
		}
		else 
		{
			if ($arParams["SHOW_FORUM_ANOTHER_SITE"] == "N")
				$arFilter = array("LID" => SITE_ID);
		}
		if (!empty($arParams["FID_RANGE"]))
			$arFilter["@ID"] = $arParams["FID_RANGE"];

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
		$arResult["SHOW_FORUMS_LIST"] = "N";
		$arResult["FORUMS"] = array();
		if (is_array($arForums) && (count($arForums)>0))
		{
			$arResult["SHOW_FORUMS_LIST"] = "Y";
			foreach ($arForums as $res)
			{
				$res["Status"] = ((intVal($res["ID"]) == $arParams["FID"]) ? "selected" : "");
				$res["LINK"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $res["ID"]));
				$arGroupForum[intVal($res["FORUM_GROUP_ID"])]["FORUM"][] = $res;
			}
		}
		foreach ($arGroupForum as $key=>$val)
		{
			if (is_array($arGroup[intVal($key)]) && (count($arGroup[intVal($key)]) > 0))
				$arGroupForum[intVal($key)] = array_merge($arGroup[intVal($key)], $val);
		}
		$arResult["FORUMS"] = $arGroupForum;
	}
// *****************************************************************************************
	$this->IncludeComponentTemplate();
// *****************************************************************************************
?>