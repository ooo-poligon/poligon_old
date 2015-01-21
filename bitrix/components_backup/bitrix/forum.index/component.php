<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
endif;
// *****************************************************************************************
	$arResult = array();
	$arFilter = array();
	$arGroup = array();
	$arGroupForum = array(0=>array("FORUM" => array()));
	$arForum = array();
	$countForums = 0;
	$arParams["WORD_LENGTH"] = intVal($arParams["WORD_LENGTH"]);
	$parser = new textParser(false, false, false, "light");
	$parser->MaxStringLen = $arParams["WORD_LENGTH"];
// ************************* Input params***************************************************************
// ************************* BASE **********************************************************************
// ************************* URL ***********************************************************************
	$URL_NAME_DEFAULT = array(
			"list" => "PAGE_NAME=list&FID=#FID#",
			"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#",
			"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
			"message_appr" => "PAGE_NAME=message_appr&FID=#FID#&TID=#TID#");

	if (empty($arParams["URL_TEMPLATES_MESSAGE"]) && !empty($arParams["URL_TEMPLATES_READ"]))
	{
		$arParams["URL_TEMPLATES_MESSAGE"] = $arParams["URL_TEMPLATES_READ"];
	}
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPageParam($URL_VALUE, array("PAGE_NAME", "FID", "TID", "UID", BX_AJAX_PARAM_ID));
		$arParams["~URL_TEMPLATES_".strToUpper($URL)] = $arParams["URL_TEMPLATES_".strToUpper($URL)];
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialchars($arParams["~URL_TEMPLATES_".strToUpper($URL)]);
	}
// *****************************************************************************************
	$arParams["FORUMS_PER_PAGE"] = intVal(intVal($arParams["FORUMS_PER_PAGE"]) > 0 ? $arParams["FORUMS_PER_PAGE"] : COption::GetOptionString("forum", "FORUMS_PER_PAGE", "10"));
	$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arParams["FID"] = (is_array($arParams["FID"]) && !empty($arParams["FID"]) ? $arParams["FID"] : array());
	$arParams["DATE_FORMAT"] = trim(empty($arParams["DATE_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")) : $arParams["DATE_FORMAT"]);
	$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	$arParams["WORD_LENGTH"] = intVal($arParams["WORD_LENGTH"]);
	$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N");
	
	$arParams["SHOW_FORUM_ANOTHER_SITE"] = ($arParams["SHOW_FORUM_ANOTHER_SITE"] == "Y" ? "Y" : "N");
	$arParams["SHOW_FORUMS_LIST"] = ($arParams["SHOW_FORUMS_LIST"] == "Y" ? "Y" : "N");
// **************************** CACHE ******************************************************
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;

	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
// *****************************************************************************************
// *************************/Input params***************************************************************



	ForumSetLastVisit(0);
	$arResult["index"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_INDEX"], array());
// *****************************************************************************************
	if ($_SERVER["REQUEST_METHOD"]=="GET" && $_GET["ACTION"]=="SET_BE_READ")
		ForumSetReadForum(false);
	
	
// *****************************************************************************************
	$arGroup = array();
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
		{
			$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
			$cache->EndDataCache(array("arGroup"=>$arGroup));
		}
	}
// *****************************************************************************************
	$arFilter = array("APPROVED" => "N");
	if (!$USER->IsAdmin())
	{
		$arFilter = array("LID" => SITE_ID, "PERMS" => array($USER->GetGroups(), 'A'), "ACTIVE" => "Y");
	}
	elseif ($arParams["SHOW_FORUM_ANOTHER_SITE"] == "N") 
	{
		$arFilter["LID"] = SITE_ID;
	}
	
	if (!$USER->IsAdmin() || ($USER->IsAdmin() && ($arParams["SHOW_FORUMS_LIST"] == "Y")))
	{
		$res = array();
		foreach ($arParams["FID"] as $key => $val)
		{
			if (intVal($val) > 0)
				$res[] = $val;
		}
		if (count($res) > 0)
		{
			$arFilter["@ID"] = $res;
		}
	}
// *****************************************************************************************
	CPageOption::SetOptionString("main", "nav_page_in_session", "N");
	$dbForum = CForumNew::GetListEx(array("FORUM_GROUP_SORT"=>"ASC", "FORUM_GROUP_ID"=>"ASC", "SORT"=>"ASC", "NAME"=>"ASC"), $arFilter);
	$dbForum->NavStart($arParams["FORUMS_PER_PAGE"], false);
	$arResult["NAV_RESULT"] = $dbForum;
	$arResult["NAV_STRING"] = $dbForum->GetPageNavStringEx($navComponentObject, GetMessage("F_FORUM"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
	
	$arResult["DrawAddColumn"] = "N";
	while ($res = $dbForum->GetNext())
	{
		$res["UserPermission"] = ForumCurrUserPermissions($res["ID"]);
		if ($res["UserPermission"] >= "Q"):
//			$res["mCnt"] = CForumMessage::GetList(array(), array("FORUM_ID"=>$res["ID"], "APPROVED"=>"N"), true);
			if (intVal($res["mCnt"]) <= 0)
				$res["mCnt"] = "";
			else 
				$res["message_appr"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE_APPR"], 
					array("FID" => $res["ID"], "TID" => "s"));
			$arResult["DrawAddColumn"] = "Y";
		endif;
	// *****************************************************************************************
		$res["topic_list"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $res["ID"]));
	// *****************************************************************************************
		$res["~NewMessage"] = NewMessageForum($res["ID"], $res["LAST_POST_DATE"]);
		$res["NewMessage"] = ($res["~NewMessage"] ? "Y" : "N");
	// *****************************************************************************************
		$res["message_list"] = "";
		if (strLen($res["TITLE"]) > 0)
		{
			$res["TITLE"] = $parser->wrap_long_words($res["TITLE"]);
			$res["message_list"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"], 
				array("FID" => $res["ID"], "TID" => intVal($res["TID"]), "MID" => intVal($res["MID"])))."#message".$res["MID"];
		}
	// *****************************************************************************************
		if (strLen($res["LAST_POSTER_NAME"])>0)
		{
			$res["LAST_POSTER_NAME"] = $parser->wrap_long_words($res["LAST_POSTER_NAME"]);
			if (intVal($res["LAST_POSTER_ID"]) > 0)
				$res["profile_view"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $res["LAST_POSTER_ID"]));
		}
		if (strLen(trim($res["LAST_POST_DATE"])) > 0)
		{
			$res["LAST_POST_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["LAST_POST_DATE"], CSite::GetDateFormat()));
		}
		$arGroupForum[intVal($res["FORUM_GROUP_ID"])]["FORUM"][] = $res;
	}
	
	foreach ($arGroupForum as $key=>$val)
	{
		if (is_array($arGroup[intVal($key)]) && (count($arGroup[intVal($key)]) > 0))
			$arGroupForum[intVal($key)] = array_merge($arGroup[intVal($key)], $val);
	}
	$arResult["FORUM"] = $arGroupForum;
	
	//******************************************************************************************
	if ($arParams["SET_TITLE"] != "N")
		$APPLICATION->SetTitle(GetMessage("F_TITLE"));
	if($arParams["DISPLAY_PANEL"] == "Y" && $USER->IsAuthorized())
		CForumNew::ShowPanel(0, 0, false);
// *****************************************************************************************
	$this->IncludeComponentTemplate();
// *****************************************************************************************
?>