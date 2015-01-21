<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("FSL_NO_MODULE"));
	return 0;
elseif (!$USER->IsAuthorized()):
	$APPLICATION->AuthForm(GetMessage("FSL_AUTH"));
	return 0;
endif;

	$strErrorMessage = "";
	$strOKMessage = "";
	$bVarsFromForm = false;
	$bUserFound = false;
// ************************* Input params***************************************************************
// ************************* BASE **********************************************************************
	$arParams["UID"] = intVal($_REQUEST["UID"]);
	if (!$USER->IsAdmin() || ($arParams["UID"] <= 0))
	{
		$arParams["UID"] = intVal($USER->GetID());
	}
	$arParams["SID"] = intVal($_REQUEST["SID"]);
	$arParams["ACTION"] = strToUpper($_REQUEST["ACTION"]);
// ************************* URL ***********************************************************************
	if (empty($arParams["URL_TEMPLATES_MESSAGE"]) && !empty($arParams["URL_TEMPLATES_READ"]))
		$arParams["URL_TEMPLATES_MESSAGE"] = $arParams["URL_TEMPLATES_READ"];
	$URL_NAME_DEFAULT = array(
			"list" => "PAGE_NAME=list&FID=#FID#",
			"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#",
			"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#",
			"subscr_list" => "PAGE_NAME=subscr_list",
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
// ************************* ADDITIONAL ****************************************************************
	// Data and data-time format
	$arParams["TOPICS_PER_PAGE"] = intVal($arParams["TOPICS_PER_PAGE"] > 0 ? $arParams["TOPICS_PER_PAGE"] : COption::GetOptionString("forum", "TOPICS_PER_PAGE", "10"));
	$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "N" ? "N" : "Y");
	$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N");
// *****************************************************************************************************
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
// *************************/Input params***************************************************************

// *****************************************************************************************
	ForumSetLastVisit($FID);
	$db_res = CForumUser::GetList(array(), array("USER_ID" => $arParams["UID"], "SHOW_ABC" => ""));
	if ($db_res && ($res = $db_res->GetNext()))
	{
		$bUserFound = true;
		$arResult["USER"] = $res;
	}
	else 
	{
		$strErrorMessage .= str_replace("#UID#", $arParams["UID"], GetMessage("FSL_NO_DUSER"));
	}
	
	if (($arParams["ACTION"]=="DEL") && ($arParams["SID"]>0) && check_bitrix_sessid())
	{
		if (CForumSubscribe::CanUserDeleteSubscribe($arParams["SID"], $USER->GetUserGroupArray(), $USER->GetID()))
		{
			if (CForumSubscribe::Delete($arParams["SID"]))
				$strOKMessage = GetMessage("FSL_SUCC_DELETE").". \n";
		}
		else
		{
			$strErrorMessage .= GetMessage("FSL_NO_SPERMS").". \n";
		}
	}
// *****************************************************************************************
	$arResult["CURRENT_PAGE"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_SUBSCR_LIST"], array());
	$arResult["ERROR_MESSAGE"] = $strErrorMessage;
	$arResult["OK_MESSAGE"] = $strOKMessage;
// *****************************************************************************************
	$arResult["sessid"] = bitrix_sessid_get();
	$arResult["SHOW_SUBSCRIBE_LIST"] = "N";
	$arResult["SUBSCRIBE_LIST"] = array();
	$db_res = CForumSubscribe::GetList(array("FORUM_ID"=>"ASC", "TOPIC_ID"=>"ASC", "START_DATE"=>"ASC"), array("USER_ID"=>$arParams["UID"]));
	$db_res->NavStart($arParams["TOPICS_PER_PAGE"]);
	$arResult["NAV_RESULT"] = $db_res;
	$arResult["NAV_STRING"] = $db_res->GetPageNavStringEx($navComponentObject, GetMessage("F_SUBSCRIBE"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
	if ($db_res && ($res = $db_res->GetNext()))
	{
		$arResult["SHOW_SUBSCRIBE_LIST"] = "Y";
		do
		{
			$res["FORUM_INFO"] = htmlspecialcharsEx(CForumNew::GetByID($res["FORUM_ID"]));
			$res["TOPIC_INFO"] = htmlspecialcharsEx(CForumTopic::GetByID($res["TOPIC_ID"]));
			if (strLen(trim($res["START_DATE"])) > 0)
				$res["START_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["START_DATE"], CSite::GetDateFormat()));;
			$res["SUBSCRIBE_TYPE"] = "N";
			if (intVal($res["TOPIC_ID"])>0)
			{
				$res["SUBSCRIBE_TYPE"] = "TOPIC";
				$res["read"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"], 
					array("FID" => $res["FORUM_ID"], "TID" => $res["TOPIC_ID"], "MID" => "s"));
			}
			else
			{
				if ($res["NEW_TOPIC_ONLY"]=="Y")
					$res["SUBSCRIBE_TYPE"] = "NEW_TOPIC_ONLY";
				else
					$res["SUBSCRIBE_TYPE"] = "ALL_MESSAGES";
			}
			$res["LAST_SEND"] = intVal($res["LAST_SEND"]);
			$res["list"] =  CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $res["FORUM_ID"]));
			$res["read_last_send"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"], 
				array("FID" => $res["FORUM_ID"], "TID" => intVal($res["TOPIC_ID"]), "MID" => intVal($res["LAST_SEND"]))).
					"#message".intVal($res["LAST_SEND"]);
			
			$res["subscr_delete"] = ForumAddPageParams($arResult["CURRENT_PAGE"], 
							array("SID" => $res["ID"], "ACTION" => "DEL"))."&amp;".bitrix_sessid_get();
			
			$arResult["SUBSCRIBE_LIST"][] = $res;
		}while ($res = $db_res->GetNext());
	}
// *****************************************************************************************
	if ($arParams["SET_NAVIGATION"] != "N")
	{
		if ($bUserFound == true)
		{
			$APPLICATION->AddChainItem($arResult["USER"]["SHOW_ABC"], CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $arParams["UID"])));
		}
		else 
		{
			$APPLICATION->AddChainItem(htmlspecialcharsEx($USER->GetFullName()), CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $USER->GetID())));
		}
	}
// *****************************************************************************************
	if ($arParams["SET_TITLE"] != "N")
		$APPLICATION->SetTitle(GetMessage("FSL_TITLE"));

	if($USER->IsAuthorized())
	{
		if($arParams["DISPLAY_PANEL"] == "Y")
			CForumNew::ShowPanel(0, 0, false);
	}
// *****************************************************************************************
	$this->IncludeComponentTemplate();
// *****************************************************************************************
?>