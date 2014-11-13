<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
endif;
if ($arParams["USE_RSS"] == "N"): // out-of-date params
	return 0;
endif;

$arResult["TYPE_RSS"] = array("RSS1" => "RSS .92", "RSS2" => "RSS 2.0", "ATOM" => "Atom .3");
// ************************* Input params***************************************************************
// ************************* BASE **********************************************************************
$arParams["TYPE_RANGE"] = (is_array($arParams["TYPE_RANGE"]) ? $arParams["TYPE_RANGE"] : array());
$res = array();
foreach ($arResult["TYPE_RSS"] as $key => $val)
{
	if (in_array($key, $arParams["TYPE_RANGE"]))
		$res[] = $key;
}
$arParams["TYPE_RANGE"] = $res;
// *************************/Input params***************************************************************

if (empty($arParams["TYPE_RANGE"])):
	ShowError(GetMessage("F_EMPTY_TYPE"));
	return 0;
endif;
// ************************* Input params***************************************************************
// ************************* BASE **********************************************************************
$arParams["MODE_TEMPLATE"] = (!empty($arParams["MODE_TEMPLATE"]) ? $arParams["MODE_TEMPLATE"] : false);
if (!$arParams["MODE_TEMPLATE"])
	$arParams["MODE_TEMPLATE"] = (strToLower($arParams["MODE"]) != "link" ? "rss" : "link");
$arParams["MODE_DATA"] = (in_array(strToLower($arParams["MODE_DATA"]), array("topic_new", "topic_read")) ? strToLower($arParams["MODE_DATA"]) : false);
if (!$arParams["MODE_DATA"])
	$arParams["MODE_DATA"] = (in_array(strToLower($arParams["MODE"]), array("topic_new", "topic_read")) ? strToLower($arParams["MODE"]) : "topic_new"); // no is not active
$arParams["MODE"] = $arParams["MODE_TEMPLATE"]; // for custom templates
$arParams["IID"] = (intVal($arParams["IID"]) <= 0) ? intVal($_REQUEST["IID"]) : intVal($arParams["IID"]);
// ************************* URL ***********************************************************************
	$URL_NAME_DEFAULT = array(
			"list" => "PAGE_NAME=list&FID=#FID#",
			"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#&MID=#MID#",
			"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#",
			"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
			"rss" => "PAGE_NAME=rss&TYPE=#TYPE#&MODE=#MODE#&IID=#IID#",
		);
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
// *************************/Input params***************************************************************
if ($arParams["MODE_TEMPLATE"] == "link")
{
	
	$arResult["rss_link"] = array();
	foreach ($arResult["TYPE_RSS"] as $key => $val)
	{
		if (in_array($key, $arParams["TYPE_RANGE"]))
		{
			$arResult["rss_link"][strToLower($key)] = array(
				"type" => strToLower($key), 
				"name" => $val,
				"link" => CComponentEngine::MakePathFromTemplate(
					$arParams["URL_TEMPLATES_RSS"], 
					array("TYPE" => strToLower($key), "MODE" => $arParams["MODE_DATA"], 
						"IID" => $arParams["IID"])));
		}
	}
	
	$this->IncludeComponentTemplate();
	
}
else 
{
// ************************* Input params***************************************************************
// ************************* BASE **********************************************************************
$arParams["COUNT"] = intVal(intVal($arParams["COUNT"]) > 0 ? $arParams["COUNT"] : 0);
if ($arParams["COUNT"] <= 0)
{
	$arParams["COUNT"] = intVal($arParams["MODE_DATA"] == "topic_new" ? COption::GetOptionString("forum", "TOPICS_PER_PAGE", "10") : 
		COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10"));
}
$arParams["TYPE"] = strToUpper(in_array(strToUpper($arParams["TYPE"]), array_keys($arResult["TYPE_RSS"])) ? $arParams["TYPE"] : "RSS2");
$arParams["MAX_FILE_SIZE"] = (intVal($arParams["MAX_FILE_SIZE"]) <= 0 ? 1000000000 : intVal($arParams["MAX_FILE_SIZE"])*1024*1024);
// ************************* ADDITIONAL ****************************************************************
$arParams["FID_RANGE"] = (is_array($arParams["FID_RANGE"]) ? $arParams["FID_RANGE"] : array());
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
$arParams["TN_TITLE"] = (strLen($arParams["TN_TITLE"]) > 0 ? $arParams["TN_TITLE"] : GetMessage("F_TN_TITLE"));
$arParams["TN_DESCRIPTION"] = (strLen($arParams["TN_DESCRIPTION"]) > 0 ? $arParams["TN_DESCRIPTION"] : GetMessage("F_TN_DESCRIPTION"));
// **************************** CACHE ******************************************************
if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;
// *************************/Input params***************************************************************



// ************************* Default values ************************************************************
$arFilter = array();
$arForum = array();
$arItems = array();
$arUserGroup = array();
$arResult["LANGUAGE_ID"] = LANGUAGE_ID;
$arResult["SERVER_NAME"] = (defined("SITE_SERVER_NAME") && strLen(SITE_SERVER_NAME) > 0) ? SITE_SERVER_NAME : COption::GetOptionString("main", "server_name", "");
$arResult["CHARSET"] = (defined("SITE_CHARSET") && strLen(SITE_CHARSET) > 0) ? SITE_CHARSET : "windows-1251";
$arResult["NOW"] = ($arParams["TYPE"] != "ATOM") ? date("r") : date("Y-m-d\TH:i:s").substr(date("O"), 0, 3).":".substr(date("O"), -2, 2);

$parser = new textParser();
$parser->MaxStringLen = 0; 
$bDesignMode = $GLOBALS["APPLICATION"]->GetShowIncludeAreas() && is_object($GLOBALS["USER"]) && $GLOBALS["USER"]->IsAdmin();
$arResult["DESIGN_MODE"] = ($bDesignMode ? "Y" : "N");
// *************************/Default values ************************************************************

// ************************* Cache ID ******************************************************************
	if (!$USER->IsAdmin())
	{
		$arUserGroup = $USER->GetUserGroupArray();
		if (!in_array(2, $arUserGroup))
			$arUserGroup[] = 2;
		sort($arUserGroup);
	}
	
	$arResult["TEMPLATE_ELEMENTS"] = array(
		"AUTHOR_NAME", "AUTHOR_LINK", "SIGNATURE", "DATE_REG", "AVATAR", 
		"POST_MESSAGE", "POST_LINK", "POST_DATE", "ATTACH_IMG", 
		"TITLE", "TOPIC_LINK", "TOPIC_DATE", "TOPIC_DESCRIPTION", 
		"NAME", "FORUM_LINK", "FORUM_DESCRIPTION");

	$arFields = array(
		"MODE" => $arParams["MODE_DATA"], "IID" => $arParams["IID"],
		"TYPE" => $arParams["TYPE"], "COUNT" => $arParams["COUNT"], 
		"FID_RANGE" => $arParams["FID_RANGE"], 
		"USER_GROUP" => $arUserGroup, 
		"LANGUAGE" => $arResult["LANGUAGE_ID"],
		"SERVER_NAME" => $arResult["SERVER_NAME"],
		"CHARSET" => $arResult["CHARSET"]);

// **************** Getting information ****************************************************
if(!$bDesignMode)
{
	$APPLICATION->RestartBuffer();
	header("Content-Type: text/xml");
	header("Pragma: no-cache");
}

if($this->StartResultCache(false, array($arFields, $bDesignMode), "/".SITE_ID."/forum/rss/".$arParams["TYPE"]."/".$arParams["MODE_DATA"]."/"))
{
	$cache = new CPHPCache;
	
	if (!$USER->IsAdmin())
		$arFilter = array("LID" => SITE_ID, "PERMS" => array(implode(",", $arUserGroup), 'A'), "ACTIVE" => "Y");
	if (!empty($arParams["FID_RANGE"]))
		$arFilter["@ID"] = $arParams["FID_RANGE"];
		
	$db_res = CForumNew::GetListEx(array("FORUM_GROUP_SORT"=>"ASC", "FORUM_GROUP_ID"=>"ASC", "SORT"=>"ASC", "NAME"=>"ASC"), $arFilter);
	if ($db_res && ($res = $db_res->GetNext()))
	{
		do 
		{
			$res["~FORUM_DESCRIPTION"] = $res["~DESCRIPTION"];
			$res["FORUM_DESCRIPTION"] = $res["DESCRIPTION"];
			$res["~FORUM_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_LIST"], array("FID" => $res["ID"]));
			$res["FORUM_LINK"] = htmlspecialcharsEx($res["~FORUM_LINK"]);
			$arForum[$res["ID"]] = $res;
		}while ($res = $db_res->GetNext());
	}
	
	$arFilter = array();
	if (!empty($arForum) && count($arForum) > 0)
	{
		if ((strToLower($arParams["MODE_DATA"]) == "topic_read") && (intVal($arParams["IID"]) > 0))
		{
			$arFilter = array(
				"TOPIC_ID" => $arParams["IID"],
				"APPROVED" => "Y",
				"@FORUM_ID" => implode(",", array_keys($arForum)), 
				"TOPIC" => "GET_TOPIC_INFO");
			
			$arResult["~TITLE"] = $arParams["TN_TITLE"];
			$arResult["~DESCRIPTION"] = $arParams["TN_DESCRIPTION"];
		}
		else
		{
			$arFilter = array(
				"@FORUM_ID" => implode(",", array_keys($arForum)), 
				"APPROVED" => "Y",
				"TOPIC" => "GET_TOPIC_INFO");

			if (intVal($arFields["IID"]) > 0)
			{
				$arFilter["FORUM_ID"] = $arFields["IID"];
			}
			$arFilter["NEW_TOPIC"] = "Y";
			
			$arResult["~TITLE"] = $arParams["TN_TITLE"];
			$arResult["~DESCRIPTION"] = $arParams["TN_DESCRIPTION"]; 
		}
		$arResult["TITLE"] = htmlspecialcharsEx($arResult["~TITLE"]);
		$arResult["DESCRIPTION"] = htmlspecialcharsEx($arResult["~DESCRIPTION"]);
		
		$db_res = CForumMessage::GetListEx(array("ID" => "DESC"), $arFilter, 0, $arParams["COUNT"]);
		if ($db_res && ($res = $db_res->Fetch()))
		{
			do 
			{
				foreach ($res as $key => $val)
				{
					$res[$key] = htmlspecialchars($val);
					$res["~".$key] = $val;
				}
				$res["AUTHOR_LINK"] = "";
				if (intVal($res["AUTHOR_ID"]) > 0)
				{
					$res["~AUTHOR_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PROFILE_VIEW"], 
						array("UID" => intVal($res["AUTHOR_ID"])));
				}
				$res["AUTHOR_LINK"] = htmlspecialchars($res["~AUTHOR_LINK"]);
			if (strLen($res["AVATAR"])>0)
			{
				// ******************************************************************************************
				
				$cache_id = "forum_avatar_".$res["AVATAR"];
				$cache_path = "/".SITE_ID."/forum/avatar/".$res["AVATAR"]."/";
				if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
				{
					$cache_result = $cache->GetVars();
					if (is_array($cache_result["AVATAR"]) && (count($cache_result["AVATAR"]) > 0) && ($cache_result["AVATAR"]["ID"] == $res["AVATAR"]))
						$res["AVATAR"] = $cache_result["AVATAR"];
				}
				else
				{
					// Don`t tuch this structure!!!!! It is need for cache
					$res["AVATAR"] = array("ID" => $res["AVATAR"]);
					$res["AVATAR"]["FILE"] = CFile::GetFileArray($res["AVATAR"]["ID"]);
					$res["AVATAR"]["HTML"] = CFile::ShowImage($res["AVATAR"]["FILE"]["SRC"], COption::GetOptionString("forum", "avatar_max_width", 90), COption::GetOptionString("forum", "avatar_max_height", 90), "border=\"0\" vspace=\"5\" hspace=\"5\"", "", true);
					
					if ($arParams["CACHE_TIME"] > 0)
					{
						$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
						$cache->EndDataCache(array("AVATAR" => $res["AVATAR"]));
					}
				}
				// *****************************************************************************************
				$res["AVATAR"] = CFile::ShowImage("http://".$arResult["SERVER_NAME"].$res["AVATAR"]["FILE"]["SRC"], 90, 90);
			}
				$res["POST_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"], 
					array("FID" => $res["FORUM_ID"], "TID" => $res["TOPIC_ID"], "MID" => $res["ID"]));
				if (intVal($res["ATTACH_IMG"])>0)
				{
					if ($arForum[$res["FORUM_ID"]]["ALLOW_UPLOAD"]=="Y" || $arForum[$res["FORUM_ID"]]["ALLOW_UPLOAD"]=="F" || $arForum[$res["FORUM_ID"]]["ALLOW_UPLOAD"]=="A")
					{
						$res["ATTACH"] = CFile::GetFileArray($res["ATTACH_IMG"]);

						if($res["ATTACH"]["FILE_SIZE"] <= $arParams["MAX_FILE_SIZE"] && strToLower(subStr($res["ATTACH"]["CONTENT_TYPE"], 0, 6)) == "image/")
							$res["ATTACH_IMG"] = CAllFile::ShowImage("http://".$arResult["SERVER_NAME"].$res["ATTACH"]["SRC"]);
						else
							$res["ATTACH_IMG"] = ' [ <a href="http://'.$arResult["SERVER_NAME"].$res["ATTACH"]["SRC"].'">'.GetMessage("FILE_DOWNLOAD").'</a> ] ';

					}
				}
				else 
				{
					$res["ATTACH_IMG"] = "";
				}
				
				$arDate = ParseDateTime($res["POST_DATE"], $arParams["DATE_TIME_FORMAT"]);
				if ($arParams["TYPE"] != "ATOM")
				{
					$date = date("r", mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
				}
				else 
				{
					$timeISO = mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]);
					$date = date("Y-m-d\TH:i:s", $timeISO).substr(date("O", $timeISO), 0, 3).":".substr(date("O", $timeISO), -2, 2);
				}
				$res["POST_DATE"] = $date;
				$res["POST_MESSAGE"] = $parser->convert_to_rss($res["POST_MESSAGE"]);
				
				// TOPIC DATA
				$topic = array("ID" => $res["TOPIC_ID"]);
				$topic["AUTHOR_NAME"] = $res["USER_START_NAME"];
				$topic["~AUTHOR_NAME"] = $res["~USER_START_NAME"];
				$topic["AUTHOR_ID"] = $res["USER_START_ID"];
				$topic["~AUTHOR_ID"] = $res["~USER_START_ID"];
				$topic["AUTHOR_LINK"] = "";
				if (intVal($topic["AUTHOR_ID"]) > 0)
					$topic["~AUTHOR_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PROFILE_VIEW"], array("UID" => intVal($topic["AUTHOR_ID"])));
				$topic["AUTHOR_LINK"] = htmlspecialchars($topic["~AUTHOR_LINK"]);
				
				$topic["TITLE"] = $res["TITLE"];
				$topic["~TITLE"] = $res["~TITLE"];
				$topic["TOPIC_DESCRIPTION"] = $res["TOPIC_DESCRIPTION"];
				$topic["~TOPIC_DESCRIPTION"] = $res["~TOPIC_DESCRIPTION"];
				$arDate = ParseDateTime($res["START_DATE"], $arParams["DATE_TIME_FORMAT"]);
				if ($arParams["TYPE"] != "ATOM")
				{
					$date = date("r", mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
				}
				else 
				{
					$timeISO = mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]);
					$date = date("Y-m-d\TH:i:s", $timeISO).substr(date("O", $timeISO), 0, 3).":".substr(date("O", $timeISO), -2, 2);
				}
				$topic["~START_DATE"] = $res["~START_DATE"];
				$topic["START_DATE"] = $date;
				$topic["~TOPIC_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_READ"], 
					array("FID" => $res["FORUM_ID"], "TID" => $res["TOPIC_ID"], "MID" => "s"));
				$topic["TOPIC_LINK"] = htmlspecialchars($topic["~TOPIC_LINK"]);
				unset($res["TITLE"]);
				unset($res["DESCRIPTION"]);
				if (is_array($arItems[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]]))
					$arItems[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]] = array_merge($arItems[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]], $topic);
				else 
					$arItems[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]] = $topic;
				
				if (!empty($arParams["TEMPLATE"]))
				{
					$text = $arParams["TEMPLATE"];
					foreach ($arParams["TEMPLATE_ELEMENTS"] as $element)
					{
						$replace = array();
						if (strLen($res[$element]) > 0)
							$replace = array($res[$element], $res["~".$element]);
						elseif (strLen($topic[$element]) > 0)
							$replace = array($topic[$element], $topic["~".$element]);
						else 
							$replace = array($arForum[$res["FORUM_ID"]][$element], $arForum[$res["FORUM_ID"]]["~".$element]);
							
						$text = str_replace(array("#".$res."#", "#~".$res."#"), $replace, $text);
					}
					$res["TEMPLATE"] = $text;
				}
				$arItems[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]]["MESSAGES"][$res["ID"]] = $res;
			}while ($res = $db_res->Fetch());
		}
		if (is_array($arItems) && (count($arItems) > 0))
		{
			foreach ($arItems as $key => $val)
				$arItems[$key] = array_merge($arForum[$key], $val);
		}
	}
	$arResult["DATA"] = $arItems;
	$arParams["TYPE"] = strToLower($arParams["TYPE"]);
	if($bDesignMode)
	{
		ob_start();
		$this->IncludeComponentTemplate();
		$contents = ob_get_contents();
		ob_end_clean();
		echo "<pre>",htmlspecialchars($contents),"</pre>";
	}
	else
	{
		$this->IncludeComponentTemplate();
	}
}
if(!$bDesignMode)
	die();
}
?>