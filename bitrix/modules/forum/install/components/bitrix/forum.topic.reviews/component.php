<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
elseif (!CModule::IncludeModule("iblock")):
 	ShowError(GetMessage("F_NO_MODULE_IBLOCK"));
	return 0;
elseif (intVal($arParams["FORUM_ID"]) <= 0):
 	ShowError(GetMessage("F_ERR_FID_EMPTY"));
	return 0;
elseif (intVal($arParams["ELEMENT_ID"]) <= 0):
 	ShowError(GetMessage("F_ERR_EID_EMPTY"));
	return 0;
endif;

// ************************* Input params***************************************************************
// ************************* BASE **********************************************************************
	$arParams["FORUM_ID"] = intVal($arParams["FORUM_ID"]);
	$arParams["ELEMENT_ID"] = intVal(intVal($arParams["ELEMENT_ID"])<=0 ? $GLOBALS["ID"] : $arParams["ELEMENT_ID"]);
	$arParams["POST_FIRST_MESSAGE"] = ($arParams["POST_FIRST_MESSAGE"] == "Y" ? "Y" : "N");
	$arParams["POST_FIRST_MESSAGE_TEMPLATE"] = trim($arParams["POST_FIRST_MESSAGE_TEMPLATE"]);
	if (empty($arParams["POST_FIRST_MESSAGE_TEMPLATE"]))
		$arParams["POST_FIRST_MESSAGE_TEMPLATE"] = "#IMAGE# \n [url=#LINK#]#TITLE#[/url]\n\n#BODY#";
// ************************* URL ***********************************************************************
	$URL_NAME_DEFAULT = array(
			"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#&MID=#MID#",
			"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
			"detail" => "PAGE_NAME=detail&SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (empty($arParams["URL_TEMPLATES_".strToUpper($URL)]))
			continue;
		$arParams["~URL_TEMPLATES_".strToUpper($URL)] = $arParams["URL_TEMPLATES_".strToUpper($URL)];
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialchars($arParams["~URL_TEMPLATES_".strToUpper($URL)]);
	}
	// PATH To Element 
	$arParams["URL_TEMPLATES_DETAIL"] = (empty($arParams["URL_TEMPLATES_DETAIL"]) ? false : $arParams["URL_TEMPLATES_DETAIL"]);
	if (!$arParams["URL_TEMPLATES_DETAIL"])
	{
		$arParams["URL_TEMPLATES_DETAIL"] = (!empty($_REQUEST["SEF_APPLICATION_CUR_PAGE_URL"]) ? $_REQUEST["SEF_APPLICATION_CUR_PAGE_URL"] : $APPLICATION->GetCurPageParam());
	}
// ************************* ADDITIONAL ****************************************************************
$arParams["IMAGE_SIZE"] = (intVal($arParams["IMAGE_SIZE"]) > 0 ? $arParams["IMAGE_SIZE"] : 300);
$arParams["MESSAGES_PER_PAGE"] = intVal($arParams["MESSAGES_PER_PAGE"] > 0 ? $arParams["MESSAGES_PER_PAGE"] : COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10"));
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")):$arParams["DATE_TIME_FORMAT"]);
$arParams["USE_CAPTCHA"] = ($arParams["USE_CAPTCHA"] == "Y" ? "Y" : "N");
$arParams["PREORDER"] = ($arParams["PREORDER"] == "Y" ? "Y" : "N");
$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N");
// **************************** CACHE ******************************************************
if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;	
// *************************/Input params***************************************************************

// ************************* Default values ************************************************************
$cache = new CPHPCache;
$arError = array();
$arNote = array();
// *************************/Default values ************************************************************

// ************************* Check Main Information ****************************************************
// FORUM
$arForum = array();
$cache_id = "forum_forum_with_path_".$arParams["FORUM_ID"];
$cache_path = "/".SITE_ID."/forum/forum/".$arParams["FORUM_ID"]."/";
if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	$res = $cache->GetVars();
	if (is_array($res["arForum"]) && (count($res["arForum"]) > 0) && ($res["arForum"]["ID"] == $arParams["FORUM_ID"]))
		$arForum = $res["arForum"];
}

if (!is_array($arForum) || $arForum["ID"] != $arParams["FORUM_ID"])
{
	$db_res = CForumNew::GetList(array(), array("ID" => $arParams["FORUM_ID"]));
	if ($db_res && ($res = $db_res->GetNext()))
	{
		$arForum = $res;
	}
	$arForum["SITES"] = CForumNew::GetSites($arParams["FORUM_ID"]);
	if ($arParams["CACHE_TIME"] > 0)
	{
		$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
		$cache->EndDataCache(array("arForum"=>$arForum));
	}
}
// *****************************************************************************************
$arResult["FORUM"] = $arForum;
// *****************************************************************************************
// ELEMENT 
$arIblock = array();
$cache_id = "forum_iblock_".$arParams["ELEMENT_ID"];
$cache_path = "/".SITE_ID."/forum/iblock/".$arParams["ELEMENT_ID"]."/";
if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	$res = $cache->GetVars();
	if (is_array($res["arIblock"]) && (count($res["arIblock"]) > 0) && ($res["arIblock"]["PRODUCT"]["ID"] == $arParams["ELEMENT_ID"]))
		$arIblock = $res["arIblock"];
}
if (!is_array($arIblock) || ($arIblock["PRODUCT"]["ID"] != $arParams["ELEMENT_ID"]))
{
	$db_res = CIBlockElement::GetList(array(), array("ID" => $arParams["ELEMENT_ID"]));
	if ($res = $db_res->GetNextElement())
	{
		$arIblock["PRODUCT"] = $res->GetFields();
		$arIblock["PRODUCT_PROPS"] = $res->GetProperties();
	}
	if ($arParams["CACHE_TIME"] > 0)
	{
		$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
		$cache->EndDataCache(array("arIblock"=>$arIblock));
	}
}
// *****************************************************************************************
$arResult["ELEMENT"] = $arIblock;
// *****************************************************************************************
if (empty($arResult["FORUM"])):
 	ShowError(str_replace("#FORUM_ID#", $arParams["FORUM_ID"], GetMessage("F_ERR_FID_IS_NOT_EXIST")));
	return 0;
elseif (empty($arResult["ELEMENT"])):
 	ShowError(str_replace("#ELEMENT_ID#", $arParams["ELEMENT_ID"], GetMessage("F_ERR_EID_IS_NOT_EXIST")));
	return 0;
endif;

// *************************1. ACTIONS *****************************************************************
if ($_SERVER["REQUEST_METHOD"]=="POST" && $_POST["save_product_review"] == "Y")
{
	$FORUM_TOPIC_ID = 0;
	$arProperties = array();
	$needProperty = array();
	$strErrorMessage = "";
		
	// 1.1. Check gross errors message data
	if (!check_bitrix_sessid())
	{
		$arError[] = array(
			"code" => "session time is up",
			"title" => GetMessage("F_ERR_SESSION_TIME_IS_UP"));
	}
	// 1.2 Check Post Text
	elseif (strLen($_POST["REVIEW_TEXT"]) < 3)
	{
		$arError[] = array(
			"code" => "post is empty",
			"title" => GetMessage("F_ERR_NO_REVIEW_TEXT"));
	}
	// 1.3 Check Permission
	elseif (ForumCurrUserPermissions($arParams["FORUM_ID"]) <= "E")
	{
		$arError[] = array(
			"code" => "access denied",
			"title" => GetMessage("F_ERR_NOT_RIGHT_FOR_ADD"));
	}
	// 1.4 Check Captcha
	elseif (!$GLOBALS["USER"]->IsAuthorized() && $arParams["USE_CAPTCHA"]=="Y" && $arResult["FORUM"]["USE_CAPTCHA"] != "Y")
	{
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/captcha.php");

		$cpt = new CCaptcha();
		if (strlen($_POST["captcha_code"]) > 0)
		{
			$captchaPass = COption::GetOptionString("main", "captcha_password", "");
			if (!$cpt->CheckCodeCrypt($_POST["captcha_word"], $_POST["captcha_code"], $captchaPass))
			{
				$arError[] = array(
					"code" => "bad captcha",
					"title" => GetMessage("POSTM_CAPTCHA"));
			}
		}
		else
		{
			if (!$cpt->CheckCode($_POST["captcha_word"], 0))
				$arError[] = array(
					"code" => "captcha is empty",
					"title" => GetMessage("POSTM_CAPTCHA"));
		}
	}
	
	
	if (empty($arError))
	{
	// 1.4 Add iblock properties
		$needProperty = array();
		$PRODUCT_IBLOCK_ID = intVal($arResult["ELEMENT"]["PRODUCT"]["IBLOCK_ID"]);
		$PRODUCT_NAME = Trim($arResult["ELEMENT"]["PRODUCT"]["~NAME"]);
		$FORUM_TOPIC_ID = 0;
		$FORUM_MESSAGE_CNT = 0;
		
		if (is_set($arResult["ELEMENT"]["PRODUCT_PROPS"], "FORUM_TOPIC_ID"))
			$FORUM_TOPIC_ID = intVal($arResult["ELEMENT"]["PRODUCT_PROPS"]["FORUM_TOPIC_ID"]["VALUE"]);
		else 
			$needProperty[] = "FORUM_TOPIC_ID";
			
		if (is_set($arResult["ELEMENT"]["PRODUCT_PROPS"], "FORUM_MESSAGE_CNT"))
			$FORUM_MESSAGE_CNT = intVal($arResult["ELEMENT"]["PRODUCT_PROPS"]["FORUM_MESSAGE_CNT"]["VALUE"]);
		else 
			$needProperty[] = "FORUM_MESSAGE_CNT";

		if (!empty($needProperty))
		{
			$obProperty = new CIBlockProperty;
			$res = true;
			foreach ($needProperty as $nameProperty)
			{
				$sName = $nameProperty;
				if ($sName == "FORUM_TOPIC_ID")
					$sName = GetMessage("F_FORUM_TOPIC_ID");
				elseif ($sName == "FORUM_MESSAGE_CNT")
					$sName = GetMessage("F_FORUM_MESSAGE_CNT");
				$res = $obProperty->Add(array(
					"IBLOCK_ID" => $PRODUCT_IBLOCK_ID,
					"ACTIVE" => "Y",
					"PROPERTY_TYPE" => "N",
					"MULTIPLE" => "N",
					"NAME" => $sName,
					"CODE" => $nameProperty));

				if($res)
					${strToUpper($nameProperty)} = 0;

				BXClearCache(true, "/".SITE_ID."/forum/iblock/".$arParams["ELEMENT_ID"]."/");
			}
		}
	// 1.5 Set NULL for topic_id if it was deleted
		if ($FORUM_TOPIC_ID > 0)
		{
			$arTopic = CForumTopic::GetByID($FORUM_TOPIC_ID);
			if (!$arTopic || !is_array($arTopic) || count($arTopic) <= 0 || $arTopic["FORUM_ID"] != $arParams["FORUM_ID"])
			{
				CIBlockElement::SetPropertyValues($arParams["ELEMENT_ID"], $PRODUCT_IBLOCK_ID, 0, "FORUM_TOPIC_ID");
				$FORUM_TOPIC_ID = 0;
			}
		}
	// 1.6 Create New topic and add messages
		$DB->StartTransaction();
		$MID = 0;
		$TID = 0;
		if ($FORUM_TOPIC_ID <= 0)
		{
			if ($arParams["POST_FIRST_MESSAGE"] == "Y")
			{
	// 1.6.a Create New topic
				$arUserStart = array(
					"ID" => intVal($arResult["ELEMENT"]["PRODUCT"]["~CREATED_BY"]),
					"NAME" => GetMessage("F_GUEST"));
				if ($arUserStart["ID"] > 0)
				{
					$db_res = CForumUser::GetListEx(array(), array("USER_ID" => $arResult["ELEMENT"]["PRODUCT"]["~CREATED_BY"]));
					if ($db_res && $res = $db_res->Fetch())
					{
						$sName = "";
						if ($res["SHOW_NAME"]=="Y")
							$sName = trim($res["NAME"]." ".$res["LAST_NAME"]);
						if (empty($sName))
							$sName = trim($res["LOGIN"]);
						$arUserStart["NAME"] = $sName;
					}
				}
				if (empty($arUserStart["NAME"]))
					$arUserStart["NAME"] = GetMssage("F_GUEST");
					
				$arFields = Array(
					"TITLE"			=> $arResult["ELEMENT"]["PRODUCT"]["~NAME"],
					"TAGS"			=> $arResult["ELEMENT"]["PRODUCT"]["~TAGS"],
					"FORUM_ID"		=> $arParams["FORUM_ID"],
					"USER_START_ID"	=> $arUserStart["ID"],
					"USER_START_NAME" => $arUserStart["NAME"],
					"LAST_POSTER_NAME" => $arUserStart["NAME"],
					"APPROVED" => ($arResult["FORUM"]["MODERATION"] == "Y" ? "N" : "Y"));
				
				$TID = CForumTopic::Add($arFields);
	// 1.6.b Add post as new message 
				if (intVal($TID)<=0)
				{
					$arError[] = array(
						"code" => "topic is not created",
						"title" => GetMessage("F_ERR_ADD_TOPIC"));
				}
				else 
				{
					$sImage = "";
					if (intVal($arResult["ELEMENT"]["PRODUCT"]["PREVIEW_PICTURE"]) > 0)
					{
						$arImage = CFile::GetFileArray($arResult["ELEMENT"]["PRODUCT"]["PREVIEW_PICTURE"]);
						if (!empty($arImage))
						{
							if ($arResult["FORUM"]["ALLOW_IMG"] == "Y")
								$sImage = "[IMG]".$arImage["SRC"]."[/IMG]";
							else 
								$sImage = $arImage["SRC"];
						}
					}

					$arFields = Array(
						"POST_MESSAGE" => str_replace(
							array("#IMAGE#", "#TITLE#", "#BODY#", "#LINK#"),
							array(
							$sImage, 
							$arResult["ELEMENT"]["PRODUCT"]["~NAME"], 
							$arResult["ELEMENT"]["PRODUCT"]["~PREVIEW_TEXT"], 
							str_replace(
								array("#ELEMENT_ID#", "#ELEMENT_CODE#", "#SECTION_ID#"), 
								array($arResult["ELEMENT"]["PRODUCT"]["ID"], $arResult["ELEMENT"]["PRODUCT"]["CODE"], $arResult["ELEMENT"]["PRODUCT"]["IBLOCK_SECTION_ID"]), 
								$arParams["URL_TEMPLATES_DETAIL"])), 
							$arParams["POST_FIRST_MESSAGE_TEMPLATE"]),
						"AUTHOR_ID" => $arUserStart["ID"],
						"AUTHOR_NAME" => $arUserStart["NAME"],
						"FORUM_ID" => $arParams["FORUM_ID"],
						"TOPIC_ID" => $TID,
						"APPROVED" => ($arResult["FORUM"]["MODERATION"] == "Y" ? "N" : "Y"),
						"NEW_TOPIC" => "Y",
						"PARAM1" => "IB", 
						"PARAM2" => intVal($arParams["ELEMENT_ID"]));

					
					$MID = CForumMessage::Add($arFields, false, array("SKIP_INDEXING" => "Y", "SKIP_STATISTIC" => "Y"));
					
					
					if (intVal($MID) <= 0)
					{
						$arError[] = array(
							"code" => "message is not added",
							"title" => GetMessage("F_ERR_ADD_MESSAGE"));
						CForumTopic::Delete($TID);
						$TID = 0;
					}
				}
	// 1.6.c Add comments
				if ($TID > 0 && $MID > 0 && empty($arError))
				{
					$arFieldsG = array(
						"POST_MESSAGE" => $_POST["REVIEW_TEXT"],
						"AUTHOR_NAME" => $_POST["REVIEW_AUTHOR"],
						"AUTHOR_EMAIL" => $_POST["REVIEW_EMAIL"],
						"USE_SMILES" => $_POST["REVIEW_USE_SMILES"],
						"ATTACH_IMG" => $_FILES["REVIEW_ATTACH_IMG"]);
					$MID = ForumAddMessage("REPLY", $arParams["FORUM_ID"], $TID, 0, $arFieldsG, $strErrorMessage, $strOKMessage, false, $_POST["captcha_word"], 0, $_POST["captcha_code"]);
				}
			}
			else
			{
	// 1.6.0.a Sipmly add message & create new topic
				$arFieldsG = array(
					"POST_MESSAGE" => $_POST["REVIEW_TEXT"],
					"AUTHOR_NAME" => $_POST["REVIEW_AUTHOR"],
					"AUTHOR_EMAIL" => $_POST["REVIEW_EMAIL"],
					"USE_SMILES" => $_POST["REVIEW_USE_SMILES"],
					"ATTACH_IMG" => $_FILES["REVIEW_ATTACH_IMG"],
					"TITLE" => $PRODUCT_NAME);
				$MID = ForumAddMessage("NEW", $arParams["FORUM_ID"], 0, 0, $arFieldsG, $strErrorMessage, $strOKMessage, false, $_POST["captcha_word"], 0, $_POST["captcha_code"]);
				if ($MID > 0 && empty($strErrorMessage))
				{
					$res = CForumMessage::GetByID($MID);
					if (!empty($res) && is_array($res))
						$TID = intVal($res["TOPIC_ID"]);
				}
			}
			
			$FORUM_TOPIC_ID = $TID;
		}
		else 
		{
			$arFieldsG = array(
				"POST_MESSAGE" => $_POST["REVIEW_TEXT"],
				"AUTHOR_NAME" => trim($_POST["REVIEW_AUTHOR"]),
				"AUTHOR_EMAIL" => $_POST["REVIEW_EMAIL"],
				"USE_SMILES" => $_POST["REVIEW_USE_SMILES"],
				"ATTACH_IMG" => $_FILES["REVIEW_ATTACH_IMG"]);
			$MID = ForumAddMessage("REPLY", $arParams["FORUM_ID"], $FORUM_TOPIC_ID, 0, $arFieldsG, $strErrorMessage, $strOKMessage, false, $_POST["captcha_word"], 0, $_POST["captcha_code"]);
		}
		
		if ($MID <= 0)
		{
			$arError[] = array(
				"code" => "message is not added",
				"title" => (empty($strErrorMessage) ? GetMessage("F_ERR_ADD_MESSAGE") : $strErrorMessage));
		}

	// 1.7 Update Iblock Property
		if ($MID > 0 && empty($arError))
		{
			if ($TID > 0)
				CIBlockElement::SetPropertyValues($arParams["ELEMENT_ID"], $PRODUCT_IBLOCK_ID, intVal($TID), "FORUM_TOPIC_ID");
			$FORUM_MESSAGE_CNT = CForumMessage::GetList(array(), array("TOPIC_ID" => $FORUM_TOPIC_ID, "APPROVED" => "Y"), true);
			CIBlockElement::SetPropertyValues($arParams["ELEMENT_ID"], $PRODUCT_IBLOCK_ID, intVal($FORUM_MESSAGE_CNT), "FORUM_MESSAGE_CNT");
	// 1.8 Commit
			$DB->Commit();
			$strOKMessage = GetMessage("COMM_COMMENT_OK");
			
			BXClearCache(true, "/".SITE_ID."/forum/iblock/".$arParams["ELEMENT_ID"]."/");
			BXClearCache(true, "/".SITE_ID."/forum/messages/".$arParams["FORUM_ID"]."/".intVal($FORUM_TOPIC_ID)."/");
			
			// SUBSCRIBE
			if ($_REQUEST["TOPIC_SUBSCRIBE"] == "Y")
				ForumSubscribeNewMessagesEx($arParams["FORUM_ID"], $FORUM_TOPIC_ID, "N", $strErrorMessage, $strOKMessage);
			if ($_REQUEST["FORUM_SUBSCRIBE"] == "Y")
				ForumSubscribeNewMessagesEx($arParams["FORUM_ID"], 0, "N", $strErrorMessage, $strOKMessage);
				
			if ($_REQUEST["TOPIC_SUBSCRIBE"] == "Y" || $_REQUEST["FORUM_SUBSCRIBE"] == "Y")
				BXClearCache(true, "/bitrix/forum/user/".$GLOBALS["USER"]->GetID()."/subscribe/");
			$arResult["FORUM_TOPIC_ID"] = intVal($FORUM_TOPIC_ID);
				
		}
		else 
		{
			$DB->Rollback();
		}
	}

	if (empty($arError))
	{
		$strURL = (!empty($_REQUEST["back_page"]) ? $_REQUEST["back_page"] : $APPLICATION->GetCurPageParam("", array("MID", "SEF_APPLICATION_CUR_PAGE_URL", BX_AJAX_PARAM_ID)));
		$strURL = ForumAddPageParams($strURL, array("MID" => $MID))."#message".$MID;
	
		LocalRedirect($strURL);
	}
}
// *****************************************************************************************
$strErrorMessage = "";
foreach ($arError as $res)
	$strErrorMessage .= (empty($res["title"]) ? $res["code"] : $res["title"]);
$arResult["ERROR_MESSAGE"] = $strErrorMessage;
$arResult["OK_MESSAGE"] = $strOKMessage;
// ************************* Input params***************************************************************
// ************************* URL ***********************************************************************
if (empty($arParams["~URL_TEMPLATES_READ"]) && !empty($arResult["FORUM"]["SITES"][SITE_ID]))
	$arParams["~URL_TEMPLATES_READ"] = $arResult["FORUM"]["SITES"][SITE_ID];
elseif (empty($arParams["~URL_TEMPLATES_READ"]))
	$arParams["~URL_TEMPLATES_READ"] = $APPLICATION->GetCurPage()."?PAGE_NAME=read&FID=#FID#&TID=#TID#&MID=#MID#";

$arParams["~URL_TEMPLATES_READ"] = str_replace(
		array("#FORUM_ID#", "#TOPIC_ID#", "#MESSAGE_ID#"),
		array("#FID#", "#TID#", "#MID#"),
		$arParams["~URL_TEMPLATES_READ"]);
$arParams["URL_TEMPLATES_READ"] = htmlspecialcharsEx($arParams["~URL_TEMPLATES_READ"]);
// ************************* ADDITIONAL ****************************************************************
$arParams["USE_CAPTCHA"] = $arForum["USE_CAPTCHA"] == "Y" ? "Y" : $arParams["USE_CAPTCHA"];
// *************************/Input params***************************************************************

// *****************************************************************************************
$arResult["FORUM_TOPIC_ID"] = 0;
if (is_set($arResult["ELEMENT"]["PRODUCT_PROPS"], "FORUM_TOPIC_ID"))
	$arResult["FORUM_TOPIC_ID"] = intVal($arResult["ELEMENT"]["PRODUCT_PROPS"]["FORUM_TOPIC_ID"]["VALUE"]);
$arResult["USER"] = array();
$arResult["MESSAGES"] = array();
// *****************************************************************************************

// ***************** 3. Get inormation about USER ******************************************
if ($GLOBALS["USER"]->IsAuthorized())
{
	$arUser = array();
	$cache_id = "forum_user_info_".intVal($GLOBALS["USER"]->GetID()).$arParams["FORUM_ID"];
	$cache_path = "/bitrix/forum/user/".intVal($GLOBALS["USER"]->GetID())."/";
	if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
	{
		$res = $cache->GetVars();
		if (is_array($res["arUser"]) && (count($res["arUser"]) > 0) && ($res["arUser"]["USER_ID"] == $GLOBALS["USER"]->GetID()))
			$arUser = $res["arUser"];
	}
	if (!is_array($arUser) || empty($arUser))
	{
		$arUser = CForumUser::GetByUSER_ID($GLOBALS["USER"]->GetID());
		$arUser["PERMISSION"] = ForumCurrUserPermissions($arParams["FORUM_ID"]);
		
		if ($arParams["CACHE_TIME"] > 0)
		{
			$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
			$cache->EndDataCache(array("arUser"=>$arUser));
		}
	}
// *****************************************************************************************
	$arResult["USER"] = $arUser;
// *****************************************************************************************
	// User Subscribes
	if ($arResult["USER"]["PERMISSION"] > "E")
	{
		$arUserSubscribe = array();
		$cache_id = "forum_user_subscribe_".intVal($GLOBALS["USER"]->GetID())."_".$arParams["FORUM_ID"];
		$cache_path = "/bitrix/forum/user/".intVal($GLOBALS["USER"]->GetID())."/subscribe/";
		
		if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
		{
			$res = $cache->GetVars();
			$arUserSubscribe = $res["arUserSubscribe"];
		}
		
		if (!is_array($arUserSubscribe) || intVal($arUserSubscribe["USER_ID"]) != intVal($GLOBALS["USER"]->GetID()))
		{
			$arFields = array(
				"USER_ID" => $GLOBALS["USER"]->GetID(),
				"FORUM_ID" => $arParams["FORUM_ID"]);
			$db_res = CForumSubscribe::GetList(array(), $arFields);
			if ($db_res && ($res = $db_res->Fetch()))
			{
				do
				{
					$arUserSubscribe[] = $res;
				} while ($res = $db_res->Fetch());
			}
			
			$arUserSubscribe = array(
				"USER_ID" => intVal($GLOBALS["USER"]->GetID()), 
				"DATA" => $arUserSubscribe);
			
			if ($arParams["CACHE_TIME"] > 0)
			{
				$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
				$cache->EndDataCache(array("arUserSubscribe"=>$arUserSubscribe));
			}
		}
// *****************************************************************************************
		$arResult["USER"]["SUBSCRIBE"] = $arUserSubscribe["DATA"];
// *****************************************************************************************
	}
	if (is_array($arResult["USER"]["SUBSCRIBE"]))
	{
		foreach ($arResult["USER"]["SUBSCRIBE"] as $res)
		{
			if (intVal($res["TOPIC_ID"]) <= 0)
				$arResult["USER"]["FORUM_SUBSCRIBE"] = "Y";
			elseif(intVal($res["TOPIC_ID"]) == intVal($arResult["FORUM_TOPIC_ID"]))
				$arResult["USER"]["TOPIC_SUBSCRIBE"] = "Y";
		}
	}
	
	$strUserName = "";
	if ($arUser["SHOW_NAME"]=="Y")
		$strUserName = trim($GLOBALS["USER"]->GetFullName());
	if (strLen($strUserName)<=0)
		$strUserName = trim($GLOBALS["USER"]->GetLogin());
// *****************************************************************************************
	$arResult["USER"]["SHOWED_NAME"] = $strUserName;
// *****************************************************************************************
}
else 
{
	$strUserPermission = "";
	$cache_id = "forum_guest_permission_on_forum_".$arParams["FORUM_ID"];
	$cache_path = "/bitrix/forum/user/guest/permission/";
	if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
	{
		$res = $cache->GetVars();
			$strUserPermission = $res["strUserPermission"];
	}
	if (empty($strUserPermission))
	{
		$strUserPermission = ForumCurrUserPermissions($arParams["FORUM_ID"]);
		if ($arParams["CACHE_TIME"] > 0)
		{
			$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
			$cache->EndDataCache(array("strUserPermission"=>$strUserPermission));
		}
	}
// *****************************************************************************************
	$arResult["USER"]["PERMISSION"] = $strUserPermission;
// *****************************************************************************************
	$arResult["USER"]["SHOWED_NAME"] = GetMessage("F_GUEST");
// *****************************************************************************************
}

// ***************** 4. Get message list ***************************************************
if ($arResult["FORUM_TOPIC_ID"] > 0 && $arResult["USER"]["PERMISSION"] > "A")
{	
	$page_number = $GLOBALS["NavNum"] + 1;
	$arMessages = array();
	$ar_cache_id = array(
		$arParams["FORUM_ID"], $arParams["ELEMENT_ID"], $arResult["FORUM_TOPIC_ID"],
		$arParams["MESSAGES_PER_PAGE"], $arParams["DATE_TIME_FORMAT"], $arParams["PREORDER"],
		$_REQUEST["MID"], $_GET["PAGEN_".$page_number]);
	$cache_id = "forum_message_".serialize($ar_cache_id);
	$cache_path = "/".SITE_ID."/forum/messages/".$arParams["FORUM_ID"]."/".intVal($arResult["FORUM_TOPIC_ID"])."/";
	
	if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
	{
		$res = $cache->GetVars();
		if (is_array($res["arMessages"]))
		{
			$arMessages = $res["arMessages"];
			$arResult["NAV_RESULT"] = $db_res;
			if (is_array($res["Nav"]))
			{
				$arResult["NAV_RESULT"] = $res["Nav"]["NAV_RESULT"];
				$arResult["NAV_STRING"] = $res["Nav"]["NAV_STRING"];
			}
		}
			
	}
	if (empty($arMessages))
	{
		$parser = new textParser(LANGUAGE_ID, $arParams["PATH_TO_SMILE"], $arParams["CACHE_TIME"]);
		$parser->image_params["width"] = $arParams["IMAGE_SIZE"];
		$parser->image_params["height"] = $arParams["IMAGE_SIZE"];
		
		
		$arAllow = array(
			"HTML" => $arForum["ALLOW_HTML"],
			"ANCHOR" => $arForum["ALLOW_ANCHOR"],
			"BIU" => $arForum["ALLOW_BIU"],
			"IMG" => $arForum["ALLOW_IMG"],
			"LIST" => $arForum["ALLOW_LIST"],
			"QUOTE" => $arForum["ALLOW_QUOTE"],
			"CODE" => $arForum["ALLOW_CODE"],
			"FONT" => $arForum["ALLOW_FONT"],
			"SMILES" => $arForum["ALLOW_SMILES"],
			"UPLOAD" => $arForum["ALLOW_UPLOAD"],
			"NL2BR" => $arForum["ALLOW_NL2BR"]);

		$arOrder = array("ID" => (($arParams["PREORDER"] == "N") ? "DESC" : "ASC"));
		$db_res = CForumMessage::GetList($arOrder, array("TOPIC_ID"=>$arResult["FORUM_TOPIC_ID"], "APPROVED" => "Y","FORUM_ID"=>$arParams["FORUM_ID"], "!PARAM1" => "IB"));
			
		if ($db_res)
		{
			CPageOption::SetOptionString("main", "nav_page_in_session", "N");
			$MID = intVal($_REQUEST["MID"]);
			unset($_GET["MID"]);
			unset($GLOBALS["MID"]);
			if (intVal($MID) > 0)
			{
				$page_number = CForumMessage::GetMessagePage($MID, $arParams["MESSAGES_PER_PAGE"], $GLOBALS["USER"]->GetUserGroupArray(), $arResult["FORUM_TOPIC_ID"], array("ORDER_DIRECTION" => $arOrder["ID"]));
				$db_res->NavStart($arParams["MESSAGES_PER_PAGE"], false, $page_number);
			}
			else 
			{
				$db_res->NavStart($arParams["MESSAGES_PER_PAGE"], false);
			}
			$arResult["NAV_RESULT"] = $db_res;
			$arResult["NAV_STRING"] = $db_res->GetPageNavStringEx($navComponentObject, GetMessage("NAV_OPINIONS"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
			
			while ($res = $db_res->GetNext())
			{
				if (COption::GetOptionString("forum", "FILTER", "Y")=="Y")
					$res["POST_MESSAGE_TEXT"] = $res["~POST_MESSAGE_FILTER"];
				else 
					$res["POST_MESSAGE_TEXT"] = $res["~POST_MESSAGE"];
				// For quote JS	
				$res["FOR_JS"]["AUTHOR_NAME"] = Cutil::JSEscape($res["AUTHOR_NAME"]);
				$res["FOR_JS"]["POST_MESSAGE_TEXT"] = Cutil::JSEscape(htmlspecialchars($res["POST_MESSAGE_TEXT"]));
				$arAllowMessage = $arAllow;
				$arAllowMessage["SMILES"] = ($res["USE_SMILES"] != "N") ? $arAllowMessage["SMILES"] : "N";
				$res["POST_MESSAGE_TEXT"] = $parser->convert($res["POST_MESSAGE_TEXT"], $arAllowMessage);
				if (strLen(trim($res["POST_DATE"])) > 0)
					$res["POST_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["POST_DATE"], CSite::GetDateFormat()));
				
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
				
				$arMessages[] = $res;
				
			} while ($res = $db_res->GetNext());
		}
		
		if ($arParams["CACHE_TIME"] > 0)
		{
			$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
			$cache->EndDataCache(array(
				"arMessages"=>$arMessages, 
				"Nav" => array(
					"NAV_RESULT" => $arResult["NAV_RESULT"],
					"NAV_STRING" => $arResult["NAV_STRING"])));
		}
	}
	else 
	{
		$GLOBALS["NavNum"]++;
	}
	
	$arResult["MESSAGES"] = $arMessages;
	// Link to forum
	if (!empty($arResult["MESSAGES"]))
	{
		$arResult["read"] = CComponentEngine::MakePathFromTemplate(
			$arParams["URL_TEMPLATES_READ"], 
			array(
				"FID" => $arParams["FORUM_ID"], 
				"TID" => $arResult["FORUM_TOPIC_ID"],
				"MID" => "s"));
	}
}

$arResult["SHOW_POST_FORM"] = (($arResult["USER"]["PERMISSION"] > "I" || ($arResult["PERMISSION"] > "E" && count($arResult["MESSAGES"]) > 0)) ? "Y" : "N");
if ($arResult["SHOW_POST_FORM"] == "Y")
{
	// Author name
	$arResult["~REVIEW_AUTHOR"] = $arResult["USER"]["SHOWED_NAME"];
	$arResult["~REVIEW_USE_SMILES"] = ($arResult["FORUM"]["ALLOW_SMILES"] == "Y" ? "Y" : "N");
	
	if (!empty($arError))
	{
		$arResult["~REVIEW_AUTHOR"] = $_POST["REVIEW_AUTHOR"];
		$arResult["~REVIEW_EMAIL"] = $_POST["REVIEW_EMAIL"];
		$arResult["~REVIEW_TEXT"] = $_POST["REVIEW_TEXT"];
		$arResult["~REVIEW_USE_SMILES"] = ($_POST["REVIEW_USE_SMILES"] == "Y" ? "Y" : "N");
	}
	$arResult["REVIEW_AUTHOR"] = htmlspecialcharsEx($arResult["~REVIEW_AUTHOR"]);
	$arResult["REVIEW_EMAIL"] = htmlspecialcharsEx($arResult["~REVIEW_EMAIL"]);
	$arResult["REVIEW_TEXT"] = htmlspecialcharsEx($arResult["~REVIEW_TEXT"]);
	$arResult["REVIEW_USE_SMILES"] = $arResult["~REVIEW_USE_SMILES"];

	// Form Info
	$arResult["SHOW_PANEL_ATTACH_IMG"] = (in_array($arResult["FORUM"]["ALLOW_UPLOAD"], array("A", "F", "Y")) ? "Y" : "N");
	$arResult["TRANSLIT"] = (LANGUAGE_ID=="ru" ? "Y" : " N");
	$arResult["ForumPrintSmilesList"] = ($arResult["FORUM"]["ALLOW_SMILES"] == "Y" ? 
		ForumPrintSmilesList(3, LANGUAGE_ID, $arParams["PATH_TO_SMILE"], $arParams["CACHE_TIME"]) : "");

	$arResult["CAPTCHA_CODE"] = "";
	if ($arParams["USE_CAPTCHA"] == "Y" && !$GLOBALS["USER"]->IsAuthorized())
	{
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/captcha.php");
		$cpt = new CCaptcha();
		$captchaPass = COption::GetOptionString("main", "captcha_password", "");
		if (strLen($captchaPass) <= 0)
		{
			$captchaPass = randString(10);
			COption::SetOptionString("main", "captcha_password", $captchaPass);
		}
		$cpt->SetCodeCrypt($captchaPass);
		$arResult["CAPTCHA_CODE"] = htmlspecialchars($cpt->GetCodeCrypt());
	}
}

$arResult["LANGUAGE_ID"] = LANGUAGE_ID;
$arResult["SHOW_CLOSE_ALL"] = "N";
if ($arResult["FORUM"]["ALLOW_BIU"] == "Y" || $arResult["FORUM"]["ALLOW_FONT"] == "Y" || $arResult["FORUM"]["ALLOW_ANCHOR"] == "Y" || $arResult["FORUM"]["ALLOW_IMG"] == "Y" || $arResult["FORUM"]["ALLOW_QUOTE"] == "Y" || $arResult["FORUM"]["ALLOW_CODE"] == "Y" || $arResult["FORUM"]["ALLOW_LIST"] == "Y")
	$arResult["SHOW_CLOSE_ALL"] = "Y";

/* For custom template */
$arResult["IS_AUTHORIZED"] = $GLOBALS["USER"]->IsAuthorized();
$arResult["PERMISSION"] = $arResult["USER"]["PERMISSION"];
$arResult["SHOW_NAME"] = $arResult["USER"]["SHOWED_NAME"];
$arResult["sessid"] = bitrix_sessid_post();
$arResult["SHOW_SUBSCRIBE"] = ($arResult["USER"]["ID"] > 0 && $arResult["USER"]["PERMISSION"] > "E" ? "Y" : "N");
$arResult["TOPIC_SUBSCRIBE"] = $arResult["USER"]["TOPIC_SUBSCRIBE"];
$arResult["FORUM_SUBSCRIBE"] = $arResult["USER"]["FORUM_SUBSCRIBE"];
$arResult["SHOW_LINK"] = (empty($arResult["read"]) ? "N" : "Y");
$arResult["SHOW_POSTS"]	= (empty($arResult["MESSAGES"]) ? "N" : "Y");
$arResult["PARSER"] = $parser;
/* For custom template */
// *****************************************************************************************
if($USER->IsAuthorized())
{
	if($arParams["DISPLAY_PANEL"] == "Y")
		CForumNew::ShowPanel($arParams["FORUM_ID"], 0);
}
// *****************************************************************************************
$this->IncludeComponentTemplate();
// *****************************************************************************************
?>