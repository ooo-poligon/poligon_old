<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!IsModuleInstalled("forum"))
{
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
}
/*
topic/
messages/
pm/
user/
users/
rss/
search/
*/
$arDefaultUrlTemplates404 = array(
	"active" => "topic/new/",
	"help" => "help/",
	"index" => "index.php",
	"list" => "forum#FID#/",
	"message" => "messages/forum#FID#/topic#TID#/message#MID#/",
	"message_appr" => "messages/approve/forum#FID#/topic#TID#/",
	"message_move" => "messages/move/forum#FID#/topic#TID#/message#MID#/",
	"message_send" => "user/#UID#/send/#TYPE#/",
	"pm_list" => "pm/folder#FID#/",
	"pm_edit" => "pm/folder#FID#/message#MID#/user#UID#/#mode#/",
	"pm_read" => "pm/folder#FID#/message#MID#/",
	"pm_search" => "pm/search/",
	"pm_folder" => "pm/folders/",
	"profile" => "user/#UID#/edit/",
	"profile_view" => "user/#UID#/",
	"read" => "forum#FID#/topic#TID#/",
	"rules" => "rules.php",
	"rss" => "rss/#TYPE#/#MODE#/#IID#/",
	"search" => "search/",
	"subscr_list" => "subscribe/",
	"topic_move" => "topic/move/forum#FID#/topic#TID#/",
	"topic_new" => "topic/add/forum#FID#/",
	"topic_search" => "topic/search/",
	"user_list" => "users/",
	"user_post" => "user/#UID#/post/#mode#/",
);

$arDefaultVariableAliases404 = array();
$arDefaultVariableAliases = array();
$arComponentVariables = array("FID", "TID", "MID", "IID", "UID", "ACTION", "mode", "MODE", "TYPE", "COUNT", "FORUM_RANGE");
$componentPage = "";
$arResult = array();
if ((($_REQUEST["auth"]=="yes") || ($_REQUEST["register"] == "yes")) && $USER->IsAuthorized())
	LocalRedirect($APPLICATION->GetCurPageParam("", array("login", "logout", "register", "forgot_password", "change_password", "backurl", "auth")));

if ($arParams["SEF_MODE"] == "Y")
{
	$arVariables = array();
	$arComponentPage = array_keys($arDefaultUrlTemplates404);

	$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);
	$componentPage = CComponentEngine::ParseComponentPath(
		$arParams["SEF_FOLDER"],
		$arUrlTemplates,
		$arVariables);

	if (empty($componentPage) || (!array_key_exists($componentPage, $arDefaultUrlTemplates404)))
	{
		if (array_key_exists($_REQUEST["PAGE_NAME"], $arDefaultUrlTemplates404))
			$componentPage = $_REQUEST["PAGE_NAME"];
		else 
			$componentPage = "index";
	}
//	CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);
	CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, array(), $arVariables);
	foreach ($arUrlTemplates as $url => $value)
		$arResult["URL_TEMPLATES_".strToUpper($url)] = $arParams["SEF_FOLDER"].$arUrlTemplates[$url];
}
else
{
	$arComponentVariables = array("FID", "TID", "MID", "ACTION", "UID", "PAGE_NAME", "mode", "TYPE");
//	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases, $VARIABLE_ALIASES);
//	CComponentEngine::InitComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);
	CComponentEngine::InitComponentVariables(false, $arComponentVariables, array(), $arVariables);
	if (array_key_exists($arVariables["PAGE_NAME"], $arDefaultUrlTemplates404))
		$componentPage = $arVariables["PAGE_NAME"];
	else
		$componentPage = "index";
		
	$arVariables["PAGE_NAME"] = $componentPage;
	
	foreach ($arDefaultUrlTemplates404 as $key => $value)
	{
		$arUrlTemplates[$key] = "";
		$arResult["URL_TEMPLATES_".strToUpper($url)] = "";
	}
}
	if (($_REQUEST["auth"] == "yes") || ($_REQUEST["register"] == "yes"))
		$componentPage = "auth";
	elseif ($componentPage == "message")
		$componentPage = "read";
		
$arResult = array_merge(
	array(
		"SEF_MODE" => $arParams["SEF_MODE"],
		"SEF_FOLDER" => $arParams["SEF_FOLDER"],
		"URL_TEMPLATES" => $arUrlTemplates, 
		"VARIABLES" => $arVariables, 
		"ALIASES" => $arVariableAliases,
		"PAGE_NAME" => $componentPage,
		"FID" => ($componentPage == "index") ? $arParams["FID"] : $arVariables["FID"],
		"TID" => $arVariables["TID"],
		"MID" => $arVariables["MID"],
		"UID" => $arVariables["UID"],
		"IID" => $arVariables["IID"],
		"ACTION" => $arVariables["ACTION"],
		"TYPE" => $arVariables["TYPE"],
		"mode" => $arVariables["mode"],
		"MODE" => $arVariables["MODE"],
		"SET_TITLE" => $arParams["SET_TITLE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"SET_NAVIGATION" => $arParams["SET_NAVIGATION"],
		"DATE_FORMAT" => $arParams["DATE_FORMAT"],
		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
		"FORUMS_PER_PAGE" => $arParams["FORUMS_PER_PAGE"],
		"TOPICS_PER_PAGE" => $arParams["TOPICS_PER_PAGE"],
		"MESSAGES_PER_PAGE" => $arParams["MESSAGES_PER_PAGE"],
		"PATH_TO_AUTH_FORM" => $arParams["PATH_TO_AUTH_FORM"],
		"PATH_TO_SMILE" => $arParams["PATH_TO_SMILE"],
		"PATH_TO_ICON" => $arParams["PATH_TO_ICON"],
		"SHOW_FORUM_ANOTHER_SITE" => $arParams["SHOW_FORUM_ANOTHER_SITE"],
		"SHOW_FORUMS_LIST" => $arParams["SHOW_FORUMS_LIST"],
		"HELP_CONTENT" => $arParams["HELP_CONTENT"],
		"RULES_CONTENT" => $arParams["RULES_CONTENT"],
		),
	$arResult);

// BASE 
//$arParams["FID"]
//$arParams["TID"] - topic id
//$arParams["MID"] - message id || message id (pm)
//$arParams["UID"] - user id
//$arParams["HELP_CONTENT"]
//$arParams["RULES_CONTENT"]
$arParams["TIME_INTERVAL_FOR_USER_STAT"] = intVal($arParams["TIME_INTERVAL_FOR_USER_STAT"]/60);
$arParams["USE_DESC_PAGE_TOPIC"] = ($arParams["USE_DESC_PAGE_TOPIC"] == "N" ? "N" : "Y");
$arParams["RSS_FID_RANGE"] = (!is_array($arParams["RSS_FID_RANGE"]) ? array() : $arParams["RSS_FID_RANGE"]);
//
// URL
//$arParams["SEF_MODE"]
//$arParams["SEF_FOLDER"]

// ADDITIONAL
//$arParams["DATE_FORMAT"],
//$arParams["DATE_TIME_FORMAT"],
//$arParams["FORUMS_PER_PAGE"],
//$arParams["TOPICS_PER_PAGE"],
//$arParams["MESSAGES_PER_PAGE"],

//$arParams["PATH_TO_SMILE"]
//$arParams["PATH_TO_ICON"]
//$arParams["PATH_TO_AUTH_FORM"]


//$arParams["USER_PROPERTY"] - user property
//$arParams["SHOW_FORUM_ANOTHER_SITE"]
//$arParams["SHOW_FORUMS_LIST"]
$arParams["SHOW_TAGS"] = (is_set($arParams["SHOW_TAGS"]) ? $arParams["SHOW_TAGS"] : "Y");


$arParams["SEND_MAIL"] = (in_array($arParams["SEND_MAIL"], array("A", "E", "U", "Y")) ? $arParams["SEND_MAIL"] : "E");
$arParams["SEND_ICQ"] = (in_array($arParams["SEND_ICQ"], array("A", "E", "U", "Y")) ? $arParams["SEND_ICQ"] : "A");

//$arParams["SHOW_FORUM_ANOTHER_SITE"]

//$arParams["SHOW_FORUMS_LIST"]
//$arParams["SHOW_USER_STATUS"]
//$arParams["FORUMS_ANOTHER"]

$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "N" ? "N" : "Y"); // add items into chain item
$arParams["ADD_INDEX_NAV"] = ($arParams["ADD_INDEX_NAV"] == "Y" ? "Y" : "N"); // 
$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N"); // add buttons unto top panel
$arParams["USE_RSS"] = ($arParams["USE_RSS"] == "Y" ? "Y" : "N"); 
$arParams["AJAX_MODE"] = ($arParams["AJAX_MODE"] == "Y" ? "Y" : "N"); 
$arParams["AJAX_TYPE"] = (($arParams["AJAX_TYPE"] == "Y" && $arParams["AJAX_MODE"] == "N") ? "Y" : "N"); 
// CACHE & TITLE
//$arParams["CACHE_TIME"]
//$arParams["CACHE_TYPE"]
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");

$arParams["SHOW_ADD_MENU"] = ($arParams["TMPLT_SHOW_BOTTOM"] == "SET_BE_READ" ? "N" : "Y");
if (!$GLOBALS["USER"]->IsAuthorized() && COption::GetOptionString("forum", "USE_COOKIE", "N") == "N")
{
	$arParams["SHOW_ADD_MENU"] = "N";
	$arParams["TMPLT_SHOW_BOTTOM"] = "";
}
$this->IncludeComponentTemplate($componentPage);
?>