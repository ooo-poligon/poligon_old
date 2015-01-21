<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
elseif (!CModule::IncludeModule("search")):
	ShowError(GetMessage("F_NO_SEARCH_MODULE"));
	return 0;
endif;
// ************************* Input params***************************************************************
// ************************* BASE **********************************************************************
		$q = trim($_REQUEST["q"]);
		$arResult["q"] = htmlspecialcharsEx($q);
		$arParams["FID"] = intVal(intVal($_REQUEST["FID"]) > 0 ? $_REQUEST["FID"] : $_REQUEST["FORUM_ID"]);
		$arParams["FID"] = intVal(intVal($arParams["FID"]) > 0 ? $arParams["FID"] : $_REQUEST["find_forum"]);
// ************************* URL ***********************************************************************
		$URL_NAME_DEFAULT = array(
			"index" => "",
			"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#",
			"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#");
		if (empty($arParams["URL_TEMPLATES_MESSAGE"]) && !empty($arParams["URL_TEMPLATES_READ"]))
			$arParams["URL_TEMPLATES_MESSAGE"] = $arParams["URL_TEMPLATES_READ"];
		foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
		{
			if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
				$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPageParam($URL_VALUE, array("PAGE_NAME", "FID", "TID", "UID", BX_AJAX_PARAM_ID));
			$arParams["~URL_TEMPLATES_".strToUpper($URL)] = $arParams["URL_TEMPLATES_".strToUpper($URL)];
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialchars($arParams["~URL_TEMPLATES_".strToUpper($URL)]);
		}
// ************************* ADDITIONAL ****************************************************************
	$arParams["FID_RANGE"] = (is_array($arParams["FID_RANGE"]) && !empty($arParams["FID_RANGE"]) ? $arParams["FID_RANGE"] : array());
	$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arParams["DATE_FORMAT"] = trim(empty($arParams["DATE_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")) : $arParams["DATE_FORMAT"]);
	$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "N" ? "N" : "Y");
	$arParams["ADD_INDEX_NAV"] = ($arParams["ADD_INDEX_NAV"] == "Y" ? "Y" : "N");
	$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N");
// ************************* CACHE & TITLE *************************************************************
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;

	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
// *************************/Input params***************************************************************

	$arFilter = array();
	$arForums = array();

	if (!$USER->IsAdmin())
		$arFilter = array("LID" => SITE_ID, "PERMS" => array($USER->GetGroups(), 'A'), "ACTIVE" => "Y");
	elseif ($arParams["SHOW_FORUM_ANOTHER_SITE"] == "N") 
		$arFilter = array("LID" => SITE_ID);
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
		$db_res = CForumNew::GetListEx(array("FORUM_GROUP_SORT"=>"ASC", "FORUM_GROUP_ID"=>"ASC", "SORT"=>"ASC", "NAME"=>"ASC"), $arFilter);
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
	$arResult["SHOW_FORUMS"] = "N";
	$arResult["FORUMS"] = array();
	if (is_array($arForums) && (count($arForums)>0))
	{
		$arResult["SHOW_FORUMS"] = "Y";
		foreach ($arForums as $res)
		{
			$res["Status"] = ((intVal($res["ID"]) == $arParams["FID"]) ? "selected" : "");
			$arResult["FORUMS"][$res["ID"]] = $res;
		}
	}
// *****************************************************************************************
	$arResult["SHOW_RESULT"] = "N";
	if (strLen($q)>0):
		$arResult["SHOW_RESULT"] = "Y";
		if ($arParams["FID"]<=0 || !in_array($arParams["FID"], array_keys($arResult["FORUMS"]))) 
			$arParams["FID"] = false;
		if ($_REQUEST["order"] == "date"):
			$arResult["order"]["active"] = "date";
			$aSort = array("DATE_CHANGE"=>"DESC");
		elseif($_REQUEST["order"] == "topic"):
			$arResult["order"]["active"] = "topic";
			$aSort = array("PARAM2"=>"DESC", "DATE_CHANGE"=>"ASC");
		else:
			$arResult["order"]["active"] = "relevance";
		    $aSort = array("RANK"=>"DESC", "DATE_CHANGE"=>"DESC");
		endif;
			
		$obSearch = new CSearch();
		$obSearch->Search(Array(
			"MODULE_ID" => "forum",
			"PARAM1" => (intVal($arParams["FID"]) > 0 ? $arParams["FID"] : (is_array($arParams["FID_RANGE"]) ? $arParams["FID_RANGE"] : false)),
			"SITE_ID" => SITE_ID,
			"QUERY" => $q
			), $aSort);
		$arResult["ERROR_MESSAGE"] = "";
		if ($obSearch->errorno!=0):
			$arResult["ERROR_MESSAGE"] = $obSearch->error;
		else:
			$PAGE_ELEMENTS = intVal(COption::GetOptionString("forum", "TOPICS_PER_PAGE", "10"));
			if (intVal($arParams["TOPICS_PER_PAGE"]) > 0)
				$PAGE_ELEMENTS = intVal($arParams["TOPICS_PER_PAGE"]);
			
			$obSearch->NavStart($PAGE_ELEMENTS, false);
			$arResult["NAV_STRING"] = $obSearch->GetPageNavStringEx($navComponentObject, GetMessage("FL_TOPIC_LIST"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
			$arResult["EMPTY"] = "Y";

			if ($res = $obSearch->GetNext())
			{
				$arResult["order"]["~relevance"] = $APPLICATION->GetCurPageParam(
					"q=".urlencode($q).(!empty($arParams["FID"]) ? "&FORUM_ID=".$arParams["FID"] : ""), 
					array("FORUM_ID", "q", "order", "s", BX_AJAX_PARAM_ID));
				$arResult["order"]["~topic"] = $APPLICATION->GetCurPageParam(
					"q=".urlencode($q).
					(!empty($arParams["FID"]) ? "&FORUM_ID=".$arParams["FID"] : "").
					"&order=topic", array("FORUM_ID", "q", "order", "s", BX_AJAX_PARAM_ID));
				$arResult["order"]["~date"] = $APPLICATION->GetCurPageParam(
					"q=".urlencode($q).
					(!empty($arParams["FID"]) ? "&FORUM_ID=".$arParams["FID"] : "").
					"&order=date", array("FORUM_ID", "q", "order", "s", BX_AJAX_PARAM_ID));
				$arResult["order"]["relevance"] = htmlspecialchars($arResult["order"]["~relevance"]);
				$arResult["order"]["topic"] = htmlspecialchars($arResult["order"]["~topic"]);
				$arResult["order"]["date"] = htmlspecialchars($arResult["order"]["~date"]);
				$arResult["EMPTY"] = "N";
				do
				{
					$res["URL"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"], 
						array("FID" => $res["PARAM1"], "TID"=>$res["PARAM2"], "MID" => "s"));
					if (intVal($res["ITEM_ID"]) > 0)
						$res["URL"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"], 
							array("FID" => $res["PARAM1"], "TID"=>$res["PARAM2"], "MID" => $res["ITEM_ID"]))."#message".$res["ITEM_ID"];
					$res["BODY_FORMATED"] = preg_replace("#\[/?(quote|b|i|u|code|url).*?\]#i", "", $res["BODY_FORMATED"]);
					$res["DATE_CHANGE"] = CForumFormat::DateFormat($arParams["DATE_FORMAT"], MakeTimeStamp($res["DATE_CHANGE"], CSite::GetDateFormat()));
					$arResult["TOPICS"][] = $res;
				}
				while ($res = $obSearch->GetNext());
			}
		endif;
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
		CForumNew::ShowPanel(0, 0, false);
// *****************************************************************************************
		$this->IncludeComponentTemplate();
// *****************************************************************************************

?>