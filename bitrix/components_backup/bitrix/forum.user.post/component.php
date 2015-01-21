<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
endif;
	$arForums = array();
	$arTopics = array();
	$arTopicNeeded = array();
	$arUserGroup = array();
	$main = array();
	$arFilterFromForm = array();
	$FilterMess = array();
	$arForum_posts = array();
	global $APPLICATION, $DB, $date_create_DAYS_TO_BACK, $date_create, $date_create1;
	$APPLICATION->ResetException();
// ************************* Input params***************************************************************
// ************************* BASE **********************************************************************
	$arParams["UID"] = intVal(intVal($arParams["UID"]) > 0 ? $arParams["UID"] : $_REQUEST["UID"]);
	$arParams["mode"] = strToLower((strLen($arParams["mode"]) <= 0) ? $_REQUEST["mode"] : $arParams["mode"]);
	$arParams["mode"] = (in_array($arParams["mode"], array("all", "lt", "lta")) ? $arParams["mode"] : "all");
// ************************* URL ***********************************************************************
	$URL_NAME_DEFAULT = array(
			"list" => "PAGE_NAME=list&FID=#FID#",
			"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#",
			"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#",
			"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
			"user_list" => "user_list.php");
	if (empty($arParams["URL_TEMPLATES_MESSAGE"]) && !empty($arParams["URL_TEMPLATES_READ"]))
	{
		$arParams["URL_TEMPLATES_MESSAGE"] = $arParams["URL_TEMPLATES_READ"];
	}
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPageParam($URL_VALUE, array("PAGE_NAME", "FID", "TID", "UID", "mode", BX_AJAX_PARAM_ID));
		$arParams["~URL_TEMPLATES_".strToUpper($URL)] = $arParams["URL_TEMPLATES_".strToUpper($URL)];
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialchars($arParams["~URL_TEMPLATES_".strToUpper($URL)]);
	}
// ************************* ADDITIONAL ****************************************************************
	$arParams["FID_RANGE"] = (is_array($arParams["FID_RANGE"]) && !empty($arParams["FID_RANGE"]) ? $arParams["FID_RANGE"] : array());
	$arParams["MESSAGES_PER_PAGE"] = intVal((intVal($arParams["MESSAGES_PER_PAGE"]) > 0) ? $arParams["MESSAGE_PER_PAGE"] : COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10"));		
	$arParams["DATE_FORMAT"] = trim(empty($arParams["DATE_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")) : $arParams["DATE_FORMAT"]);
	$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arParams["PATH_TO_SMILE"] = trim($arParams["PATH_TO_SMILE"]);
	$arParams["WORD_LENGTH"] = intVal($arParams["WORD_LENGTH"]);
	$arParams["IMAGE_SIZE"] = (intVal($arParams["IMAGE_SIZE"]) > 0 ? $arParams["IMAGE_SIZE"] : 300);
	$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "N" ? "N" : "Y");
// **************************** CACHE ******************************************************
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;

	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");

// *************************/Input params***************************************************************
// *****************************************************************************************
// *****************************************************************************************


	$ForumsPerms = array("Q"=>GetMessage("LU_USER_Q"), "U"=>GetMessage("LU_USER_U"), "Y"=>GetMessage("LU_USER_Y"), "user"=>GetMessage("LU_USER_USER"));

// ************ Filter *********************************************************************
	if (!empty($_REQUEST["set_filter"]))
	{
		InitFilterEx(array("date_create", "date_create1"),"USER_LIST","set",false); 
		if (intVal($_REQUEST["fid"]) > 0)
			$arFilterFromForm["fid"] = $_REQUEST["fid"];
		
		if (!empty($date_create) && $DB->IsDate($date_create))
			$arFilterFromForm["date_create"] = $date_create;
		elseif (!empty($date_create))
			$APPLICATION->ThrowException(GetMessage("LU_INCORRECT_LAST_MESSAGE_DATE"), "BAD_DATE_FROM");
			
		if (!empty($date_create1) && $DB->IsDate($date_create1)) 
			$arFilterFromForm["date_create1"] = $date_create1;
		elseif (!empty($date_create1))
			$APPLICATION->ThrowException(GetMessage("LU_INCORRECT_LAST_MESSAGE_DATE"), "BAD_DATE_TO");
			
		if (!empty($_REQUEST["topic"]))
			$arFilterFromForm["topic"] = $_REQUEST["topic"];
		if (!empty($_REQUEST["message"]))
			$arFilterFromForm["message"] = $_REQUEST["message"];
	}
	elseif (!empty($_REQUEST["del_filter"]))
	{
		DelFilterEx(array("date_create", "date_create1"),"USER_LIST",false);
		unset($_REQUEST["fid"]);
		unset($_REQUEST["topic"]);
		unset($_REQUEST["message"]);
	}
	else
		InitFilterEx(array("date_create", "date_create1"),"USER_LIST","get",false);
	
// ************/Filter *********************************************************************
	$arResult["user_list"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_USER_LIST"], array());
	$arResult["SHOW_RESULT"] = "N";
	$arResult["USER"] = array();
// ******** User info **********************************************************************
	if ($arParams["UID"] > 0)
	{
		$db_res = CForumUser::GetList(array(),array("USER_ID"=>$arParams["UID"], "SHOW_ABC" => ""));
		if ($db_res && ($res = $db_res->GetNext()))
		{
			$res["~profile_view"] = CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $res["USER_ID"]));
			$res["profile_view"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $res["USER_ID"]));
			if (strLen(trim($res["AVATAR"])) > 0)
				$res["AVATAR"] =  CFile::ShowImage($res["AVATAR"], COption::GetOptionString("forum", "avatar_max_width", 90), COption::GetOptionString("forum", "avatar_max_height", 90), "border=\"0\" vspace=\"5\" hspace=\"5\"", "", true);
			if (strLen(trim($res["DATE_REG"])) > 0)
			{
				$res["DATE_REG"] = CForumFormat::DateFormat($arParams["DATE_FORMAT"], MakeTimeStamp($res["DATE_REG"], CSite::GetDateFormat()));
			}
			$arResult["USER"] = $res;
			
		}
		$arUserGroup = CUser::GetUserGroup($arParams["UID"]);
		if (!in_array(2, $arUserGroup)) 
			$arUserGroup[] = 2;
		sort($arUserGroup);
	}
	if (empty($arResult["USER"]) || empty($arUserGroup))
		LocalRedirect($arResult["user_list"]);
		
// *****************************************************************************************
	// getting list forums
	$arFilter = array();
	if (!$USER->IsAdmin())
	{
		$arFilter["PERMS"] = array($USER->GetGroups(), 'A');
		$arFilter["ACTIVE"] = "Y";
		$arFilter["SITE_ID"] = SITE_ID;
	}
	if (!empty($arParams["FID_RANGE"]))
	{
		$arFilter["@ID"] = $arParams["FID_RANGE"];
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
		$db_res = CForumNew::GetList(array("SORT"=>"ASC", "NAME"=>"ASC"), $arFilter);
		if ($db_res && ($res = $db_res->GetNext()))
		{
			do 
			{
				$arForums[$res["ID"]] = $res;
			} while ($res = $db_res->GetNext());
		}
		
		if ($arParams["CACHE_TIME"] > 0)
		{
			$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
			$cache->EndDataCache(array("arForums"=>$arForums));
		}
	}
	if (is_array($arForums) && (count($arForums)>0))
	{
		$tmp = array();
		foreach ($arForums as $res)
		{
			$res["ALLOW"] = array(
				"HTML" => $res["ALLOW_HTML"],
				"ANCHOR" => $res["ALLOW_ANCHOR"],
				"BIU" => $res["ALLOW_BIU"],
				"IMG" => $res["ALLOW_IMG"],
				"LIST" => $res["ALLOW_LIST"],
				"QUOTE" => $res["ALLOW_QUOTE"],
				"CODE" => $res["ALLOW_CODE"],
				"FONT" => $res["ALLOW_FONT"],
				"SMILES" => $res["ALLOW_SMILES"],
				"UPLOAD" => $res["ALLOW_UPLOAD"],
				"NL2BR" => $res["ALLOW_NL2BR"]
				);				
			$res["list"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $res["ID"]));
			$tmp[$res["ID"]] = $res;
		}
		$arForums = $tmp;
	}
// *****************************************************************************************
// getting list topics
if (!empty($arForums))
{
	CPageOption::SetOptionString("main", "nav_page_in_session", "N");
	if (($arParams["mode"] == "lta") || ($arParams["mode"] == "lt"))
	{
		$arFilter["@FORUM_ID"] = array_keys($arForums);
		$arFilter = ($arParams["mode"] == "lta") ? array("USER_START_ID"=>$arParams["UID"]) : array("AUTHOR_ID"=>$arParams["UID"]);
// *****************************************************************************************
		// set filters
		if (is_set($arFilterFromForm, "fid"))
			$arFilter["FORUM_ID"] = $arFilterFromForm["fid"];
		if (is_set($arFilterFromForm, "date_create"))
			$arFilter[">=POST_DATE"] = $arFilterFromForm["date_create"];
		if (is_set($arFilterFromForm, "date_create1"))
			$arFilter["<=POST_DATE"] = $arFilterFromForm["date_create1"];
		if (is_set($arFilterFromForm, "topic"))
			$arFilter["%TOPIC_TITLE"] = $arFilterFromForm["topic"];
		if (is_set($arFilterFromForm, "message"))
			$arFilter["%POST_MESSAGE"] = $arFilterFromForm["message"];
			
		$db_res = CForumUser::UserAddInfo(array("FORUM_ID"=>"ASC"), $arFilter, "topics");
		$db_res->NavStart($arParams["MESSAGES_PER_PAGE"],false);
		$arResult["NAV_RESULT"] = $db_res;
		$arResult["NAV_STRING"] = $db_res->GetPageNavStringEx($navComponentObject, GetMessage("LU_TITLE_POSTS"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
		if ($db_res && ($res = $db_res->GetNext()) && !empty($arForums))
		{
			do
			{
				$arForum_posts[$res["FORUM_ID"]] += intVal($res["COUNT_MESSAGE"]); // this string needs to 
				$res["ID"] = $res["TOPIC_ID"];
				$res["read"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"], 
					array("FID" => $res["FORUM_ID"], "TID" => $res["TOPIC_ID"], "MID" => "s"));
				$arTopics[$res["TOPIC_ID"]] = $res;
				$FilterMess[] = $res["FIRST_POST"];
			}while ($res = $db_res->GetNext());
		}
	}
	$arFilter = array("AUTHOR_ID"=>$arParams["UID"], "@FORUM_ID" => array_keys($arForums));
	if (!$USER->IsAdmin())
		$arFilter["USER_GROUP"] = $USER->GetUserGroupArray(); 
	if (count($FilterMess)>0)
		$arFilter["@ID"] = implode(", ", $FilterMess);
		
	// set filter
	if (is_set($arFilterFromForm, "fid"))
		$arFilter["FORUM_ID"] = $arFilterFromForm["fid"];
	if (is_set($arFilterFromForm, "date_create"))
		$arFilter[">=POST_DATE"] = $arFilterFromForm["date_create"];
	if (is_set($arFilterFromForm, "date_create1"))
		$arFilter["<=POST_DATE"] = $arFilterFromForm["date_create1"];
	if (is_set($arFilterFromForm, "topic"))
		$arFilter["%TOPIC_TITLE"] = $arFilterFromForm["topic"];
	if (is_set($arFilterFromForm, "message"))
		$arFilter["%POST_MESSAGE"] = $arFilterFromForm["message"];
	$db_res = CForumMessage::GetListEx(
		array("SORT"=>"ASC", "NAME"=>"ASC", "TOPIC_ID" => "ASC", "ID"=>"ASC"), 
		$arFilter, false, false,
		array("bDescPageNumbering"=>false, "nPageSize"=>$arParams["MESSAGES_PER_PAGE"], "bShowAll" => false));
	$db_res->NavStart($arParams["MESSAGES_PER_PAGE"],false);
	if (empty($arResult["NAV_RESULT"]))
	{
		$arResult["NAV_RESULT"] = $db_res;
		$arResult["NAV_STRING"] = $db_res->GetPageNavStringEx($navComponentObject, GetMessage("LU_TITLE_POSTS"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
	}
	$parser = new textParser(LANGUAGE_ID, $arParams["PATH_TO_SMILE"]);
	$parser->MaxStringLen = $arParams["WORD_LENGTH"];
	$parser->image_params["width"] = $arParams["IMAGE_SIZE"];
	$parser->image_params["height"] = $arParams["IMAGE_SIZE"];
	
	if ($db_res && ($res = $db_res->GetNext()))
	{
		do
		{
			$arAllow = $arForums[$res["FORUM_ID"]]["ALLOW"];
			$arAllow["SMILES"] = ($res["USE_SMILES"]!="Y") ? "N" : $arForums[$res["FORUM_ID"]]["ALLOW_SMILES"];
			$res["POST_MESSAGE_TEXT"] = (COption::GetOptionString("forum", "FILTER", "Y")=="Y") ? $res["~POST_MESSAGE_FILTER"] : $res["~POST_MESSAGE"];
			$res["POST_MESSAGE_TEXT"] = $parser->convert($res["POST_MESSAGE_TEXT"], $arAllow);
				
			$res["ATTACH_IMG"] = "";
			$res["~ATTACH_FILE"] = array();
			$res["ATTACH_FILE"] = array();
			if (intVal($res["¨ATTACH_IMG"])>0 && in_array($arResult["FORUM"]["ALLOW_UPLOAD"], array("A", "F", "Y")))
			{
				$res["~ATTACH_FILE"] = CFile::GetFileArray($res["~ATTACH_IMG"]);
				$res["ATTACH_IMG"] = CFile::ShowFile($res["~ATTACH_IMG"], 0, 
					$arParams["IMAGE_SIZE"], $arParams["IMAGE_SIZE"], true, "border=0", false);
				$res["ATTACH_FILE"] = $res["ATTACH_IMG"];
			}
			
			$res["read"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"], 
				array("FID" => $res["FORUM_ID"], "TID" => $res["TOPIC_ID"], "MID" => intVal($res["ID"])))."#message".intVal($res["ID"]);
			if (strLen(trim($res["POST_DATE"])) > 0)
			{
				$res["POST_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["POST_DATE"], CSite::GetDateFormat()));
			}
			$res["AUTHOR_NAME"] = $parser->wrap_long_words($res["AUTHOR_NAME"]);
			$res["DESCRIPTION"] = $parser->wrap_long_words($res["DESCRIPTION"]);
			if (!array_key_exists($res["TOPIC_ID"], $arTopics))
				$arTopicNeeded[$res["TOPIC_ID"]] = $res["TOPIC_ID"];
			$main[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]]["MESSAGES"][$res["ID"]] = $res;
		}while ($res = $db_res->GetNext());
	}
	if (!empty($arTopicNeeded))
	{
		$db_res = CForumUser::UserAddInfo(array(), array("@TOPIC_ID" => implode(",", array_keys($arTopicNeeded))), false, false, false);
		if ($db_res && ($res = $db_res->GetNext()))
		{
			do 
			{
				$arTopics[$res["TOPIC_ID"]] = $res;
			}while ($res = $db_res->GetNext());
		}
	}
	
	foreach ($main as $forum_id => $forum)
	{
		$UserPermStr = "";
		$UserPerm = CForumNew::GetUserPermission($forum_id, $arUserGroup);
		if (array_key_exists($UserPerm, $ForumsPerms))
			$UserPermStr = $ForumsPerms[$UserPerm];
		elseif (COption::GetOptionString("forum", "SHOW_VOTES", "Y")=="Y")
		{
			$arUserRank = CForumUser::GetUserRank($arParams["UID"], LANGUAGE_ID);
			$UserPermStr = $arUserRank["NAME"];
		}
		if ((strLen(trim($UserPermStr)) <= 0) && ($arParams["SHOW_DEFAULT_RANK"] == "Y"))
		{
			$UserPermStr = $ForumsPerms["user"];
		}
		$main[$forum_id]["NUM_POSTS_ALL"] = $arForum_posts[$forum_id];
		$main[$forum_id]["USER_PERM"] = $UserPerm;
		$main[$forum_id]["USER_PERM_STR"] = $UserPermStr;
		$main[$forum_id] = array_merge($arForums[$forum_id], $main[$forum_id]);
		foreach ($main[$forum_id]["TOPICS"] as $topic_id => $topic)
		{
			$arTopics[$topic_id]["TITLE"] = $parser->wrap_long_words($arTopics[$topic_id]["TITLE"]);
			$arTopics[$topic_id]["DESCRIPTION"] = $parser->wrap_long_words($arTopics[$topic_id]["DESCRIPTION"]);
			$arTopics[$topic_id]["read"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"], 
				array("FID" => $arTopics[$topic_id]["FORUM_ID"], "TID" => $arTopics[$topic_id]["TOPIC_ID"], "MID" => "s"));
			$main[$forum_id]["TOPICS"][$topic_id] = array_merge($arTopics[$topic_id], $main[$forum_id]["TOPICS"][$topic_id]);
		}
	}
	}
// *****************************************************************************************
	$arResult["FORUMS_ALL"] = $arForums;
	if (!empty($main))
	{
		$arResult["FORUMS"] = $main;
		$arResult["SHOW_RESULT"] = "Y";
	}
	if (strToLower($arParams["mode"]) == "lta")
		$Title = GetMessage("LU_TITLE_LTA");
	elseif (strToLower($arParams["mode"]) == "lt")
		$Title = GetMessage("LU_TITLE_LT");
	else 
		$Title = GetMessage("LU_TITLE_ALL");
	
	if ($arParams["SET_NAVIGATION"] != "N")
		$APPLICATION->AddChainItem($arResult["USER"]["SHOW_ABC"], $arResult["USER"]["~profile_view"]);
	if ($arParams["SET_TITLE"] != "N")
		$APPLICATION->SetTitle($arResult["USER"]["SHOW_ABC"]." (".$Title.")");
		
	if ($APPLICATION->GetException())
	{
		$err = $APPLICATION->GetException();
		$arResult["ERROR_MESSAGE"] = $err->GetString();
	}
	$arResult["PARSER"] = $parser;
// *****************************************************************************************
	$this->IncludeComponentTemplate();
// *****************************************************************************************
?>