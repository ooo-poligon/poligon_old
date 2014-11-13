<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (CModule::IncludeModule("forum")):
// *****************************************************************************************
	$arResult = array();
	$strErrorMessage = "";
	$strOKMessage = "";
	$arFilter = array();
	$bVarsFromForm = false;
	
// ************************* Input params***************************************************************
// ************************* BASE **********************************************************************
	$_REQUEST["search_template"] = trim($_REQUEST["search_template"]);
	$_REQUEST["search_field"] = trim(strtolower($_REQUEST["search_field"]));
// *****************************************************************************************
	$URL_NAME_DEFAULT = array(
		"list" => "PAGE_NAME=list&FID=#FID#",
		"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#",
		"topic_search" => "PAGE_NAME=topic_search");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialcharsEx($arParams["URL_TEMPLATES_".strToUpper($URL)]);
	}
	
	$PAGE_ELEMENTS = intVal(COption::GetOptionString("forum", "TOPICS_PER_PAGE", "10"));
	if (intVal($arParams["TOPICS_PER_PAGE"]) > 0)
		$PAGE_ELEMENTS = intVal($arParams["TOPICS_PER_PAGE"]);
// *************************/Input params***************************************************************

// ************************ Default params**************************************************************
	$arResult["TID"] = intVal($_REQUEST["TID"]);
	$arResult["TOPIC"] = array();
	$arResult["FORUM"] = array();

	$arResult["CURRENT_PAGE"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_TOPIC_SEARCH"], array());
	$arResult["sessid"] = bitrix_sessid_post();
	$arResult["SITE_CHARSET"] = SITE_CHARSET;
	
	$arResult["SELF_CLOSE"] = "N";
	$arResult["SHOW_RESULT"] = "N";
	
// ************************/Default params**************************************************************

	ForumSetLastVisit($arParams["FID"]);
	
// ************************* Actions *******************************************************************
	if ($arResult["TID"] > 0)
	{
		$arResult["SELF_CLOSE"] = "Y";
		$res = CForumTopic::GetByIDEx($_REQUEST["TID"], array("GET_FORUM_INFO" => "Y"));
		if (!empty($res) && $res["STATE"] != "L")
		{
			$arResult["TOPIC"] = $res["TOPIC_INFO"];
			$arResult["FORUM"] = $res["FORUM_INFO"];
			
			$arResult["TOPIC"]["~TITLE"] = $arResult["TOPIC"]["TITLE"];
			$arResult["TOPIC"]["TITLE"] = Cutil::JSEscape($arResult["TOPIC"]["TITLE"]);
			$arResult["TOPIC"]["LINK"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"], 
				array("FID" => $arResult["FORUM"]["ID"], "TID" => $arResult["TOPIC"]["ID"], "MID" => "s"));
			$arResult["FORUM"]["LINK"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], 
				array("FID" => $arResult["FORUM"]["ID"]));
				
				
		}
	}
	else 
	{
		$arResult["FORUM"]["data"] = array();
		$arResult["FORUM"]["active"] = intVal($_REQUEST["FID"]);
		if (!$USER->IsAdmin())
		{
			$arFilter["SITE_ID"] = SITE_ID;
			$arFilter["PERMS"] = array($USER->GetGroups(), "ALLOW_MOVE_TOPIC");
			$arFilter["ACTIVE"] = "Y";
		}
		$db_res = CForumNew::GetListEx(array("NAME"=>"ASC"), $arFilter);
		if ($db_res && ($res = $db_res->GetNext()))
		{
			do {
				$arResult["FORUM"]["data"][] = $res;
			}while ($res = $db_res->GetNext());
		}
		
		if (strlen($_REQUEST["search_template"])>0)
		{
			$arFilter = array();
			$arResult["TOPIC"] = array();
			if (($_REQUEST["search_field"] == "title") || ($_REQUEST["search_field"] == "description"))
				$arFilter[strToUpper($_REQUEST["search_field"])] = $_REQUEST["search_template"];
			else
				$arFilter["TITLE_ALL"] = $_REQUEST["search_template"];
			if (intVal($_REQUEST["FID"]) > 0)
				$arFilter["FORUM_ID"] = intVal($_REQUEST["FID"]);
			
			$db_res = CForumTopic::GetListEx(array("ID" => "DESC"), $arFilter);
			$db_res->NavStart($PAGE_ELEMENTS);
			$arResult["NAV_RESULT"] = $db_res;
			$arResult["NAV_STRING"] = $db_res->GetPageNavString(" ");
			if ($db_res && ($res = $db_res->GetNext()))
			{
				$arResult["SHOW_RESULT"] = "Y";
				do
				{
					$res["topic_id_search"] = ForumAddPageParams($arResult["CURRENT_PAGE"], array("TID" => $res["ID"]));
					$arResult["TOPIC"][] = $res;
				}while ($res = $db_res->GetNext());
			}
		}
	}
// *************************/Actions *******************************************************************

// *****************************************************************************************
	$APPLICATION->RestartBuffer();
	header("Pragma: no-cache");
	$this->IncludeComponentTemplate();
	die();
// *****************************************************************************************
else:
	ShowError(GetMessage("FMM_NO_MODULE"));
endif;?>