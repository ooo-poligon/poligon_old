<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
endif;
// *****************************************************************************************
	global $by, $order;
	$strErrorMessage = "";
	$strOkMessage = "";
	
	$parser = new textParser(false, false, false, "light");
	$parser->MaxStringLen = $arParams["WORD_LENGTH"];
	$arResult["TID"] = (empty($_REQUEST["TID_ARRAY"]) ? $_REQUEST["TID"] : $_REQUEST["TID_ARRAY"]);
	$ACTION = $_REQUEST["ACTION"];
// ************************* Input params***************************************************************
// ************************* BASE **********************************************************************
	if (intVal($arParams["FID"]) <= 0)
		$arParams["FID"] = $arParams["DEFAULT_FID"];
	$arParams["FID"] = intVal($arParams["FID"]);
	$GLOBALS["FID"] = $arParams["FID"];
	$arParams["USE_DESC_PAGE"] = ($arParams["USE_DESC_PAGE"] == "N" ? "N" : "Y");
// ************************* URL ***********************************************************************
	$URL_NAME_DEFAULT = array(
			"index" => "",
			"list" => "PAGE_NAME=list&FID=#FID#",
			"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#",
			"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#",
			"message_appr" => "PAGE_NAME=message_appr&FID=#FID#&TID=#TID#",
			"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
			"topic_new" => "PAGE_NAME=topic_new&FID=#FID#",
			"subscr_list" => "PAGE_NAME=subscr_list&FID=#FID#",
			"topic_move" => "PAGE_NAME=topic_move&FID=#FID#&TID=#TID#",
			"rss" => "PAGE_NAME=rss&TYPE=#TYPE#&MODE=#MODE#&IID=#IID#");
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
	$arParams["PAGEN"] = (intVal($arParams["PAGEN"]) <= 0 ? 1 : intVal($arParams["PAGEN"]));
	$arParams["TOPICS_PER_PAGE"] = intVal($arParams["TOPICS_PER_PAGE"] > 0 ? $arParams["TOPICS_PER_PAGE"] : COption::GetOptionString("forum", "TOPICS_PER_PAGE", "10"));
	$arParams["MESSAGES_PER_PAGE"] = intVal($arParams["MESSAGES_PER_PAGE"] > 0 ? $arParams["MESSAGES_PER_PAGE"] : COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10"));
	$arParams["DATE_FORMAT"] = trim(empty($arParams["DATE_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")) : $arParams["DATE_FORMAT"]);
	$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "N" ? "N" : "Y");
	$arParams["ADD_INDEX_NAV"] = ($arParams["ADD_INDEX_NAV"] == "Y" ? "Y" : "N"); // don`t showed in params
	$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N");
// **************************** CACHE ******************************************************
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;	
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
// ******************************************************************************************
// *****************************************************************************************
	if (!CForumNew::CanUserViewForum($arParams["FID"], $USER->GetUserGroupArray()))
		$APPLICATION->AuthForm(GetMessage("F_NO_FPERMS"));
	if (!empty($_REQUEST["result"]))
	{
		switch ($_REQUEST["result"])
		{
			case "not_approve":
			case "tid_not_approved":
				$strOkMessage .= GetMessage("F_TOPIC_NOT_APPROVED")."\n";
				break;
			case "tid_is_lost":
				$strOkMessage .= GetMessage("F_TOPIC_IS_LOST")."\n";
				break;
			case "del_topic":
				$strOkMessage .= GetMessage("F_TOPIC_IS_DEL")."\n";
				break;
		}
	}
// *****************************************************************************************
	CPageOption::SetOptionString("main", "nav_page_in_session", "N");
	ForumSetLastVisit($arParams["FID"]);

	$arResult["CURRENT_PAGE"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $arParams["FID"]));
	$arResult["index"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_INDEX"], array());
	$arResult["topic_new"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_TOPIC_NEW"], array("FID" => $arParams["FID"]));
// ******************Cached data*************************************************************
	$arResult["FORUM"] = array();
	$cache = new CPHPCache;
	$cache_id = "forum_forum_".$arParams["FID"];
	$cache_path = "/".SITE_ID."/forum/forum/".$arParams["FID"]."/";
	if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
	{
		$res = $cache->GetVars();
		if (is_array($res["arForum"]) && (count($res["arForum"]) > 0) && ($res["arForum"]["ID"] == $arParams["FID"]))
			$arResult["FORUM"] = $res["arForum"];
	}
	else
	{
		$db_res = CForumNew::GetList(array(), array("ID" => $arParams["FID"]));
		if ($db_res && ($res = $db_res->GetNext()))
			$arResult["FORUM"] = $res;
		if ($arParams["CACHE_TIME"] > 0)
		{
			$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
			$cache->EndDataCache(array("arForum"=>$arResult["FORUM"]));
		}
	}
	if (!$arResult["FORUM"] || (count($arResult["FORUM"]) <= 0))
	{
		LocalRedirect($arResult["index"]);
	}
// *****************************************************************************************
	$arParams["IsAdmin"] = $GLOBALS["USER"]->IsAdmin() ? "Y" : "N";
	$arResult["sessid"] = bitrix_sessid_get();
	$arResult["UserPermission"] = ForumCurrUserPermissions($arParams["FID"]);
	$arResult["CanUserAddTopic"] = CForumTopic::CanUserAddTopic($arParams["FID"], $USER->GetUserGroupArray(), $USER->GetID(), $arResult["FORUM"]);
// *****************************************************************************************
	if (check_bitrix_sessid() && (strLen($ACTION) > 0))
	{
		switch ($ACTION)
		{
			case "FORUM_SUBSCRIBE":
			case "FORUM_SUBSCRIBE_TOPICS":
				if (ForumSubscribeNewMessagesEx($arParams["FID"], 0, (($ACTION=="FORUM_SUBSCRIBE_TOPICS")?"Y":"N"), $strErrorMessage, $strOkMessage))
				{
					LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_SUBSCR_LIST"], array("FID" => $arParams["FID"])));
				}
			break;
			case "SET_BE_READ":
				ForumSetReadForum($arParams["FID"]);
			break;
			case "SET_ORDINARY":
			case "SET_TOP":
				if ($ACTION == "SET_ORDINARY")
				{
					$ACTION = "ORDINARY";
					$sort = "150";
				}
				else 
				{
					$ACTION = "TOP";
					$sort = "100";
				}
				if (ForumTopOrdinaryTopic($arResult["TID"], $ACTION, $strErrorMessage, $strOkMessage))
				{
					LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $arParams["FID"])));
				}
				break;
			case "MOVE_TOPIC":
				$topic = explode(",", $arResult["TID"]);
				$topic = ForumMessageExistInArray($topic);
				if ($topic)
				{
					
					LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_TOPIC_MOVE"], 
						array("FID" => $arParams["FID"], "TID" => implode(",", $topic))));
				}
				else 
				{
					$strErrorMessage .= GetMessage("F_ACT_NO_TOPICS").".\n";
				}
			break;
			case "DEL_TOPIC":
				ForumDeleteTopic($arResult["TID"], $strErrorMessage, $strOkMessage);
			break;
			case "STATE_Y":
			case "STATE_N":
				if ($ACTION == "STATE_Y")
				{
					$ACTION = "OPEN";
					$state = "Y";
				}
				else 
				{
					$ACTION = "CLOSE";
					$state = "N";
				}
				if (ForumOpenCloseTopic($arResult["TID"], $ACTION, $strErrorMessage, $strOkMessage))
				{
					LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $arParams["FID"])));
				}
			break;
		}
	}
	elseif (!check_bitrix_sessid() && (strLen($ACTION) > 0))
	{
		$strErrorMessage .= GetMessage("F_ERR_SESS_FINISH").".\n";
	}
// *****************************************************************************************
	$arResult["ERROR_MESSAGE"] = $strErrorMessage;
	$arResult["OK_MESSAGE"] = $strOkMessage;
// *****************************************************************************************
	InitSorting();
	if (!$by)
	{
		ForumGetTopicSort($by, $order, $arResult["FORUM"]);
	}
	$arResult["SortingEx"]["TITLE"] = SortingEx("TITLE");
	$arResult["SortingEx"]["USER_START_NAME"] = SortingEx("USER_START_NAME");
	$arResult["SortingEx"]["POSTS"] = SortingEx("POSTS");
	$arResult["SortingEx"]["VIEWS"] = SortingEx("VIEWS");
	$arResult["SortingEx"]["LAST_POST_DATE"] = SortingEx("LAST_POST_DATE");
	
	$arFilter = array("FORUM_ID"=>$arParams["FID"]);
	if ($USER->IsAuthorized())
		$arFilter["USER_ID"] = $USER->GetID();
	if ($arResult["UserPermission"]<"Q")
		$arFilter["APPROVED"] = "Y";

	$db_res = CForumTopic::GetListEx(
		array("SORT"=>"ASC", $by=>$order), 
		$arFilter,
		false, false, 
		array("bDescPageNumbering"=>(($arParams["USE_DESC_PAGE"] == "Y") ? true : false), "nPageSize"=>$arParams["TOPICS_PER_PAGE"], "bShowAll" => false));
	$db_res->NavStart($arParams["TOPICS_PER_PAGE"], false);
// *****************************************************************************************
	$arResult["NAV_RESULT"] = $db_res;
	$arResult["NAV_STRING"] = $db_res->GetPageNavStringEx($navComponentObject, GetMessage("F_TOPIC_LIST"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arResult["Topics"] = array();
	while ($res = $db_res->GetNext())
	{
		$res["TopicStatus"] = "OLD";
		$res["read"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"], 
			array("FID" => $res["FORUM_ID"], "TID" => $res["ID"],  "MID" => "s"));
		if ($res["APPROVED"]!="Y" && $arResult["UserPermission"] >= "Q")
		{
			$res["TopicStatus"] = "NA";
		}
		elseif ($res["STATE"] == "L")
		{
			$res["TopicStatus"] = "MOVED";
			$res["read"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"], 
				array("FID" => $res["FORUM_ID"], "TID" => $res["TOPIC_ID"],  "MID" => "s"));
		}
		else
		{
			$NewMessage = NewMessageTopic($res["FORUM_ID"], $res["ID"], $res["LAST_POST_DATE"], $res["LAST_VISIT"]);
			if ($NewMessage)
			{
				$res["TopicStatus"] = "NEW";
				$res["read_last_unread"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"], 
					array("FID" => $res["FORUM_ID"], "TID" => $res["ID"], "MID" => "unread_mid"));
			}
		}
		$res["read_last_message"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"], 
			array("FID" => $res["FORUM_ID"], "TID" => $res["ID"], "MID" => intVal($res["LAST_MESSAGE_ID"])));
		if ($res["STATE"] == "L")
			$res["read_last_message"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"], 
				array("FID" => $res["FORUM_ID"], "TID" => $res["TOPIC_ID"],  "MID" => "s"));
		if (intVal($res["USER_START_ID"]) > 0)
			$res["USER_START_HREF"] = CComponentEngine::MakePathFromTemplate(
				$arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $res["USER_START_ID"]));
		if (intVal($res["LAST_POSTER_ID"]) > 0)
		{
			$res["LAST_POSTER_HREF"] = CComponentEngine::MakePathFromTemplate(
				$arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $res["LAST_POSTER_ID"]));
			// Only for custom component
			$res["author_profile"] = CComponentEngine::MakePathFromTemplate(
				$arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $res["LAST_POSTER_ID"]));
		}
		// ********************************************************************
		$res["numMessages"] = $res["POSTS"]+1;
		// ********************************************************************
		if($arResult["UserPermission"] >= "Q"):
			$pageInfo = CForumMessage::GetList(array(), array("TOPIC_ID"=>$res["ID"]), "cnt_not_approved");
			$res["mCnt"] = $pageInfo["CNT_NOT_APPROVED"];
			$res["numMessages"] = $pageInfo["CNT"];
			if(intVal($res["mCnt"]) > 0)
			{
				$res["mCntURL"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE_APPR"], array("FID" => $res["FORUM_ID"], "TID" => $res["ID"]));
			}
		endif;
		$res["pages"] = ForumShowTopicPages($res["numMessages"], $res["read"], 
			"PAGEN_".$arParams["PAGEN"], intVal($arParams["MESSAGES_PER_PAGE"]));
		
		// ********************************************************************
		$res["TITLE"] = $parser->wrap_long_words($res["TITLE"]);
		$res["DESCRIPTION"] = $parser->wrap_long_words($res["DESCRIPTION"]);
		$res["USER_START_NAME"] = $parser->wrap_long_words($res["USER_START_NAME"]);
		$res["LAST_POSTER_NAME"] = $parser->wrap_long_words($res["LAST_POSTER_NAME"]);
		if (strLen(trim($res["LAST_POST_DATE"])) > 0)
		{
			$res["LAST_POST_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["LAST_POST_DATE"], CSite::GetDateFormat()));
		}
		if (strLen(trim($res["START_DATE"])) > 0)
		{
			$res["START_DATE"] = CForumFormat::DateFormat($arParams["DATE_FORMAT"], MakeTimeStamp($res["START_DATE"], CSite::GetDateFormat()));
		}
		$arResult["Topics"][] = $res;
	}
	
// *****************************************************************************************
	if ($arParams["SET_NAVIGATION"] == "Y")
	{
		if ($arParams["ADD_INDEX_NAV"] == "Y")
			$APPLICATION->AddChainItem(GetMessage("F_INDEX"), CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_INDEX"], array()));
		$APPLICATION->AddChainItem($arResult["FORUM"]["NAME"]);
	}
// *****************************************************************************************
	if ($arParams["SET_TITLE"] == "Y")
		$APPLICATION->SetTitle($arResult["FORUM"]["NAME"]);
	if($USER->IsAuthorized())
	{
		if($arParams["DISPLAY_PANEL"] == "Y")
			CForumNew::ShowPanel($arParams["FID"], $arParams["TID"], false);
	}
// *****************************************************************************************
	$this->IncludeComponentTemplate();
// *****************************************************************************************
?>