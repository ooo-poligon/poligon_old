<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
endif;
// *****************************************************************************************
	$arResult = array();
	$arFilter = array();
	$arrFilter = array();
	$arForums = array();
	global $by, $order, $FilterArr, $strError, $find_date1_DAYS_TO_BACK;
	extract($GLOBALS);
	if (is_array($_REQUEST)) 
		extract($_REQUEST, EXTR_SKIP);
// ************************* Input params***************************************************************
// ************************* BASE **********************************************************************
	$arParams["FID"] = intVal((intVal($arParams["FID"]) <= 0 ? $_REQUEST["FID"] : $arParams["FID"]));
// ************************* URL ***********************************************************************
	$URL_NAME_DEFAULT = array(
			"index" => "",
			"list" => "PAGE_NAME=list&FID=#FID#",
			"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#",
			"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#",
			"profile_view" => "PAGE_NAME=profile_view&UID=#UID#");
	if (empty($arParams["URL_TEMPLATES_MESSAGE"]) && !empty($arParams["URL_TEMPLATES_READ"]))
		$arParams["URL_TEMPLATES_MESSAGE"] = $arParams["URL_TEMPLATES_READ"];
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~URL_TEMPLATES_".strToUpper($URL)] = $arParams["URL_TEMPLATES_".strToUpper($URL)];
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialchars($arParams["~URL_TEMPLATES_".strToUpper($URL)]);
	}
// ************************* ADDITIONAL ****************************************************************
	$arParams["PAGEN"] = (intVal($arParams["PAGEN"]) <= 0 ? 1 : intVal($arParams["PAGEN"]));
	$arParams["MESSAGES_PER_PAGE"] = intVal(empty($arParams["MESSAGES_PER_PAGE"]) ? COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10") : $arParams["MESSAGES_PER_PAGE"]);
	$arParams["TOPICS_PER_PAGE"] = intVal(empty($arParams["TOPICS_PER_PAGE"]) ? COption::GetOptionString("forum", "TOPICS_PER_PAGE", "10") : $arParams["TOPICS_PER_PAGE"]);
	$arParams["FID_RANGE"] = (is_array($arParams["FID_RANGE"]) && !empty($arParams["FID_RANGE"]) ? $arParams["FID_RANGE"] : array());
	$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arParams["DATE_FORMAT"] = trim(empty($arParams["DATE_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")) : $arParams["DATE_FORMAT"]);
	$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	
	$arParams["WORD_LENGTH"] = intVal($arParams["WORD_LENGTH"]);
	$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "N" ? "N" : "Y");
	$arParams["ADD_INDEX_NAV"] = ($arParams["ADD_INDEX_NAV"] == "Y" ? "Y" : "N");
	$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N");
// **************************** CACHE ******************************************************
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;

	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
// *************************/Input params***************************************************************
		
// *****************************************************************************************
// *****************************************************************************************
	if (!function_exists("CheckLastTopicsFilter"))
	{
		function CheckLastTopicsFilter()
		{
			global $DB, $strError, $FilterArr, $MESS;
			foreach ($FilterArr as $s) global $$s;
			$str = "";
			if (strlen($find_date1)>0 && !$DB->IsDate($find_date1)) $str .= GetMessage("FL_INCORRECT_LAST_MESSAGE_DATE")."<br>";
			elseif (strlen($find_date2)>0 && !$DB->IsDate($find_date2)) $str .= GetMessage("FL_INCORRECT_LAST_MESSAGE_DATE")."<br>";
			$strError .= $str;
			if (strlen($str)>0) return false; else return true;
		}
	}
	$parser = new textParser(false, false, false, "light");
	$parser->MaxStringLen = $arParams["WORD_LENGTH"];
	$cache = new CPHPCache;
	$arResult["FORUM"] = array();
// *****************************************************************************************
	ForumSetLastVisit();
// *****************************************************************************************
// ******************Cached data*************************************************************
	if (!$USER->IsAdmin())
	{
		$arFilter["LID"] = SITE_ID;
		$arFilter["PERMS"] = array($USER->GetGroups(), 'A');
		$arFilter["ACTIVE"] = "Y";
	}
	if (!empty($arParams["FID_RANGE"]))
	{
		$arFilter["@FORUM_ID"] = $arParams["FID_RANGE"];
	}
	$cache_id = "forum_forums_listex_".serialize($arFilter);
	$cache_path = "/".SITE_ID."/forum/forums/";
	if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
	{
		$res = $cache->GetVars();
		if (is_array($res["arForums"]))
			$arForums = $res["arForums"];
	}
	if (!is_array($arForums) || (count($arForums) <= 0))
	{
		$db_res = CForumNew::GetListEx(array("FORUM_GROUP_SORT"=>"ASC", "FORUM_GROUP_ID"=>"ASC", "SORT"=>"ASC", "NAME"=>"ASC"), $arFilter);
		while ($res = $db_res->GetNext())
		{
			$arForums[$res["ID"]] = array("ID" => $res["ID"], "NAME" => $res["NAME"]);	
		}
			
		if ($arParams["CACHE_TIME"] > 0)
		{
			$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
			$cache->EndDataCache(array("arForums"=>$arForums));
		}
	}
// *****************************************************************************************
	if (is_array($arForums))
	{
		foreach ($arForums as $key => $val)
		{
			$arForums[$key]["LAST_VISIT"] = intVal($_SESSION["FORUM"]["LAST_VISIT_FORUM_0"]);
			if ($arForums[$key]["LAST_VISIT"] < intVal($_SESSION["FORUM"]["LAST_VISIT_FORUM_".intVal($key)]))
				$arForums[$key]["LAST_VISIT"] = intVal($_SESSION["FORUM"]["LAST_VISIT_FORUM_".intVal($key)]);
		}
		$arForumsID = array_keys($arForums);
	}

	// Topic list of forum
	if ($set_default=="Y")
	{
		$find_date1_DAYS_TO_BACK=1;
		$set_filter = "Y";
	}
	
	$FilterArr = Array(
		"find_date1",
		"find_date2",
		"find_forum"
		);
	if (strlen($set_filter)>0) 
		InitFilterEx($FilterArr,"LAST_TOPICS_LIST","set",false); 
	else 
		InitFilterEx($FilterArr,"LAST_TOPICS_LIST","get",false);
		
	if (strlen($del_filter)>0) 
		DelFilterEx($FilterArr,"LAST_TOPICS_LIST",false);
		
	extract($GLOBALS);
	
	if (CheckLastTopicsFilter())
	{
		if (intval($find_forum)>0) $arFilter["FORUM_ID"] = intval($find_forum);
		if (intval($find_date1)>0) $arFilter[">=LAST_POST_DATE"] = $find_date1;
		if (intval($find_date2)>0) $arFilter["<=LAST_POST_DATE"] = $find_date2;
	}
	$by = (strlen($by)<=0) ? "LAST_POST_DATE" : $by;
	$order = ($order!="asc") ? "desc" : "asc";
	
	if ($USER->IsAuthorized())
	{
		$arFilter["USER_ID"] = $USER->GetID();
		$arFilter[">RENEW_TOPIC"] = ConvertTimeStamp($_SESSION["FORUM"]["LAST_VISIT_FORUM_0"], "FULL");
	}
	else 
	{
		$arFilter[">LAST_POST_DATE"] = ConvertTimeStamp((time()-24*60*60*60), "FULL");
	}
// *****************************************************************************************
	$rsTopics = CForumTopic::GetListEx(array($by => $order, "POSTS" => "DESC"), $arFilter, false, $TOP);
	while ($arTopic = $rsTopics->Fetch())
	{
		$arTopic["LAST_POST_DATE_FORMATED"] = $arTopic["LAST_POST_DATE"];
		$arTopic["LAST_POST_DATE"] = intVal(MakeTimeStamp($arTopic["LAST_POST_DATE"]));
		if (!$USER->IsAuthorized() && is_array($_SESSION["FORUM"]["GUEST_TID"]))
		{
			if (intVal($_SESSION["FORUM"]["GUEST_TID"][$arTopic["ID"]]) > intVal($arForums[$arTopic["FORUM_ID"]]["LAST_VISIT"]))
			{
				$arForums[$arTopic["FORUM_ID"]]["LAST_VISIT"] = intVal($_SESSION["FORUM"]["GUEST_TID"][$arTopic["ID"]]);
			}
		}
		
		if (in_array($arTopic["FORUM_ID"], $arForumsID) && 
			(($arTopic["LAST_POST_DATE"] > 0) && ($arTopic["LAST_POST_DATE"] > $arForums[$arTopic["FORUM_ID"]]["LAST_VISIT"]))
			&& ($arTopic["STATE"] != "L"))
		{
			$arrTOPICS[] = array(
				"FORUM_ID"			=> $arTopic["FORUM_ID"],
				"ID"				=> $arTopic["ID"],
				"SORT"				=> $arTopic["SORT"],
				"STATE"				=> $arTopic["STATE"],
				"APPROVED"			=> $arTopic["APPROVED"],
				"IMAGE"				=> $arTopic["IMAGE"],
				"IMAGE_DESCR"		=> $arTopic["IMAGE_DESCR"],
				"TITLE"				=> $arTopic["TITLE"],
				"DESCRIPTION"		=> $arTopic["DESCRIPTION"],
				"USER_START_NAME"	=> $arTopic["USER_START_NAME"],
				"USER_START_ID"	=> $arTopic["USER_START_ID"],
				"POSTS"				=> $arTopic["POSTS"],
				"VIEWS"				=> $arTopic["VIEWS"],
				"LAST_POST_DATE"	=> $arTopic["LAST_POST_DATE_FORMATED"],
				"LAST_MESSAGE_ID"	=> $arTopic["LAST_MESSAGE_ID"],
				"LAST_POSTER_NAME"	=> $arTopic["LAST_POSTER_NAME"],
				"LAST_POSTER_ID"	=> $arTopic["LAST_POSTER_ID"],
				"START_DATE"	=> $arTopic["START_DATE"],
				);
		}
	}
	
	$rsTopics = new CDBResult;
	$rsTopics->InitFromArray($arrTOPICS);
	CPageOption::SetOptionString("main", "nav_page_in_session", "N");
	$rsTopics->NavStart($arParams["TOPICS_PER_PAGE"], false);
	$arResult["PAGE_NAME"] = "active";
	$arResult["NAV_RESULT"] = $rsTopics;
	$arResult["NAV_STRING"] = $rsTopics->GetPageNavStringEx($navComponentObject, GetMessage("FL_TOPIC_LIST"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arResult["SHOW_RESULT"] = "N";
	$arResult["find_forum"]["data"] = $arForums;
	$arResult["find_forum"]["active"] = $find_forum;
	$arResult["find_date1"] = CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "form1", "Y", "", "");
	$arResult["FORUMS"] = $arForums;
	$arResult["ERROR_MESSAGE"] = $strError;
	$arResult["OK_MESSAGE"] = "";
	
	if(intval($rsTopics->SelectedRowsCount())>0):
		$arResult["SHOW_RESULT"] = "Y";
		$arResult["SortingEx"]["TITLE"] = SortingEx("TITLE");
		$arResult["SortingEx"]["FORUM_ID"] = SortingEx("FORUM_ID");
		$arResult["SortingEx"]["USER_START_NAME"] = SortingEx("USER_START_NAME");
		$arResult["SortingEx"]["POSTS"] = SortingEx("POSTS");
		$arResult["SortingEx"]["VIEWS"] = SortingEx("VIEWS");
		$arResult["SortingEx"]["LAST_POST_DATE"] = SortingEx("LAST_POST_DATE");
		$arResult["arTopic"] = array();
		while ($arTopic = $rsTopics->GetNext()):
			$arTopic["TITLE"] = $parser->wrap_long_words($arTopic["TITLE"]);
			$arTopic["DESCRIPTION"] = $parser->wrap_long_words($arTopic["DESCRIPTION"]);
			$arTopic["USER_START_NAME"] = $parser->wrap_long_words($arTopic["USER_START_NAME"]);
			$arTopic["LAST_POSTER_NAME"] = $parser->wrap_long_words($arTopic["LAST_POSTER_NAME"]);
		
			$arTopic["read"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"], 
				array("FID" => $arTopic["FORUM_ID"], "TID"=>$arTopic["ID"], "MID" => "s"));
			$arTopic["read_unread"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"], 
				array("FID" => $arTopic["FORUM_ID"], "TID"=>$arTopic["ID"], "MID" => "unread_mid"));
			$arTopic["read_last_message"] =  CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"], 
				array("FID" => $arTopic["FORUM_ID"], "TID"=>$arTopic["ID"], "MID" => intVal($arTopic["LAST_MESSAGE_ID"]))).
					"#message".$arTopic["LAST_MESSAGE_ID"];
			
			$arTopic["image_prefix"] = ($arTopic["STATE"]!="Y") ? "closed_" : "";
			$arTopic["UserPermission"] = ForumCurrUserPermissions($arTopic["FORUM_ID"]);
			if ($arTopic["APPROVED"]!="Y" && ForumCurrUserPermissions($arTopic["FORUM_ID"])>="Q")
			{
				$arTopic["Status"] = "NA";
			}
			
			$mess_count = $arTopic["POSTS"]+1;
			if (ForumCurrUserPermissions($arParams["FID"])>="Q"):
				$mess_count = CForumMessage::GetList(array(), array("TOPIC_ID"=>$arTopic["ID"]), true);
			endif;
			$arTopic["ForumShowTopicPages"] = ForumShowTopicPages($mess_count, $arTopic["read"], "PAGEN_".$arParams["PAGEN"], 
				intVal($arParams["MESSAGES_PER_PAGE"]));
			$arTopic["START_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arTopic["START_DATE"], CSite::GetDateFormat()));
			$arTopic["LAST_POST_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arTopic["LAST_POST_DATE"], CSite::GetDateFormat()));
			$arTopic["DESCRIPTION"] = trim($arTopic["DESCRIPTION"]);
			$arTopic["list"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $arTopic["FORUM_ID"]));
			$arTopic["LAST_POSTER_HREF"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $arTopic["LAST_POSTER_ID"]));
			$arTopic["USER_START_HREF"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $arTopic["USER_START_ID"]));
			$arResult["TOPICS"][] = $arTopic;
		endwhile;
	endif;
// *****************************************************************************************
	$arResult["index"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_INDEX"], array());
	if ($arParams["SET_NAVIGATION"] != "N")
	{
		if ($arParams["ADD_INDEX_NAV"] == "Y")
			$APPLICATION->AddChainItem(GetMessage("F_INDEX"), $arResult["index"]);
		$APPLICATION->AddChainItem(GetMessage("F_TITLE"));
	}
// *****************************************************************************************
	if ($arParams["SET_TITLE"] != "N")
		$APPLICATION->SetTitle(GetMessage("F_TITLE"));
	if($arParams["DISPLAY_PANEL"] == "Y" && $USER->IsAuthorized())
		CForumNew::ShowPanel($arParams["FID"], $arParams["TID"], false);
// *****************************************************************************************
	$this->IncludeComponentTemplate();
// *****************************************************************************************

?>