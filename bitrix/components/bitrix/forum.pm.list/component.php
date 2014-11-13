<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
// ********************** FORUM.PM.LIST ****************************************************
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
elseif (!$USER->IsAuthorized()):
	$APPLICATION->AuthForm(GetMessage("PM_AUTH"));
	return 0;
endif;

if(!function_exists("GetUserName"))
{
	function GetUserName($USER_ID)
	{
		$ar_res = false;
		if (IntVal($USER_ID)>0)
		{
			$db_res = CUser::GetByID(IntVal($USER_ID));
			$ar_res = $db_res->Fetch();
		}

		if (!$ar_res)
		{
			$db_res = CUser::GetByLogin($USER_ID);
			$ar_res = $db_res->Fetch();
		}

		$USER_ID = IntVal($ar_res["ID"]);
		$f_LOGIN = htmlspecialcharsex($ar_res["LOGIN"]);

		$forum_user = CForumUser::GetByUSER_ID($USER_ID);
		if (($forum_user["SHOW_NAME"]=="Y") && (strlen(trim($ar_res["NAME"]))>0 || strlen(trim($ar_res["LAST_NAME"]))>0))
		{
			return trim(htmlspecialcharsex($ar_res["NAME"])." ". htmlspecialcharsex($ar_res["LAST_NAME"]));
		}
		else
			return $f_LOGIN;
	}
}

// ************************* Input params***************************************************************
// ************************* BASE **********************************************************************
	$arParams["pm_version"] = intVal(COption::GetOptionString("forum", "UsePMVersion", "2"));
	
	$arParams["FID"] = intVal(intVal($arParams["FID"]) <= 0 ? $_REQUEST["FID"] : $arParams["FID"]);
	$arParams["FID"] = intVal(intVal($arParams["FID"]) <= 0 ? 1 : $arParams["FID"]);
	if ($arParams["pm_version"] == 2 && ($arParams["FID"] > 1 && $arParams["FID"] < 4))
		$arParams["FID"] = 3;
	$arParams["UID"] = intVal($USER->GetId());
// ************************* URL ***********************************************************************
	$URL_NAME_DEFAULT = array(
		"pm_list" => "PAGE_NAME=pm_list&FID=#FID#",
		"pm_read" => "PAGE_NAME=pm_read&FID=#FID#&MID=#MID#",
		"pm_edit" => "PAGE_NAME=pm_edit&FID=#FID#&MID=#MID#&mode=#mode#",
		"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
		"pm_folder" => "PAGE_NAME=pm_folder");
			
	InitSorting();
	global $by, $order;
	
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~URL_TEMPLATES_".strToUpper($URL)] = $arParams["URL_TEMPLATES_".strToUpper($URL)];
		if (!empty($by))
		{
			$arParams["~URL_TEMPLATES_".strToUpper($URL)] = ForumAddPageParams($arParams["URL_TEMPLATES_".strToUpper($URL)], 
				array("by" => $by, "order" => $order), false, false);
		}
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialchars($arParams["~URL_TEMPLATES_".strToUpper($URL)]);
	}
// ************************* ADDITIONAL ****************************************************************
	$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arParams["PM_PER_PAGE"] = intVal($arParams["PM_PER_PAGE"] > 0 ? $arParams["PM_PER_PAGE"] : 20);
	$arParams["DATE_FORMAT"] = trim(empty($arParams["DATE_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")) : $arParams["DATE_FORMAT"]);
	$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "N" ? "N" : "Y");
	$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N");
// ************************* CACHE & TITLE *************************************************************
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;

	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
// *************************/Input params***************************************************************

// ************************* Default values ************************************************************
	switch (strToLower($_REQUEST["result"]))
	{
		case "delete":
			$strOK .= GetMessage("PM_OK_ALL_DELETE")."\n";
			break;
		case "move":
			$strOK .= GetMessage("PM_OK_ALL_MOVE")."\n";
			break;
		case "no_mid":
			$strError .= GetMessage("PM_ERR_NO_MID")."\n";
			break;
		case "no_perm":
			$strError .= GetMessage("PM_ERR_NO_PERM")."\n";
			break;
	}
	ForumSetLastVisit();
// *************************/Default values ************************************************************
// ***********Action************************************************************************
	$strError = "";
	$strOK = "";
	$message = (is_array($_REQUEST["message"]) && !empty($_REQUEST["message"]) ? $_REQUEST["message"] : array());
	$APPLICATION->ResetException();
	$APPLICATION->ThrowException(" ");
	$arResult["action"] = strToLower($_REQUEST["action"]);
	if (!empty($arResult["action"]) && check_bitrix_sessid())
	{
		if (empty($message))
		{
			$strError .= GetMessage("PM_ERR_NO_DATA")."\n";
		}
		else
		{
			$folder_id = 0;
			if ($arResult["action"] == "delete")
			{
				$folder_id = 4;
				foreach ($message as $MID) 
				{
					if (CForumPrivateMessage::CheckPermissions($MID))
					{
						if(!CForumPrivateMessage::Delete($MID, array("FOLDER_ID"=>4,)))
							$strError .= str_replace("#MID#", $MID, GetMessage("PM_ERR_DELETE"))."\n";
						else 
							$strOK .= str_replace("#MID#", $MID, GetMessage("PM_OK_DELETE"))."\n";
					}
					else 
					{
						$strError .= str_replace("#MID#", $MID, GetMessage("PM_ERR_DELETE_NO_PERM"))."\n";
					}
				}
			}
			elseif ($arResult["action"] == "copy" || $arResult["action"] == "move")
			{
				$folder_id = intVal($_REQUEST["folder_id"]);
				if ($folder_id <= 0)
				{
					$strError .= GetMessage("PM_ERR_MOVE_NO_FOLDER")."\n";
				}
				else
				{
					foreach ($message as $MID) 
					{
						if (CForumPrivateMessage::CheckPermissions($MID))
						{
							$arrVars = array(
								"FOLDER_ID" => intVal($folder_id),
								"USER_ID" => $USER->GetId(),
								"IS_READ" => "Y");

							if ($arResult["action"] == "move")
							{
								if (CForumPrivateMessage::Update($MID, $arrVars))
									$strOK .= str_replace("#MID#", $MID, GetMessage("PM_OK_MOVE"))."\n";
							}
							else 
							{
								if (CForumPrivateMessage::Copy($MID, $arrVars))
									$strOK .= str_replace("#MID#", $MID, GetMessage("PM_OK_COPY"))."\n";
							}
						}
						else 
						{
							$strError .= str_replace("#MID#", intVal($MID), GetMessage("PM_ERR_MOVE_NO_PERM"))."\n";
						}
					}
				}
				
				$err = $APPLICATION->GetException();
				$strError .= $err->GetString();
			}
		}
		BXClearCache(true, "/bitrix/forum/user/".intVal($USER->GetId())."/");
		if (strlen($strError) <= 0)
		{
			LocalRedirect(
				ForumAddPageParams(
					CComponentEngine::MakePathFromTemplate(
						$arParams["URL_TEMPLATES_PM_LIST"], 
						array("FID" => $arParams["FID"])
					), array("result" => $arResult["action"])));
		}
	}
	
// *****************************************************************************************
// *****************************************************************************************
	$arResult["count"] = CForumPrivateMessage::PMSize($USER->GetID(), COption::GetOptionInt("forum", "MaxPrivateMessages", 100));
	$arResult["count"] = round($arResult["count"]*100);
	$arResult["ERROR_MESSAGE"] = $strError;
	$arResult["OK_MESSAGE"] = $strOK;
	$arResult["sessid"] = bitrix_sessid_post();
	$arResult["FID"] = $arParams["FID"];
	$arResult["CURRENT_PAGE"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_LIST"], array("FID" => $arParams["FID"]));
	$arResult["version"] = $arParams["pm_version"];
	$arResult["MESSAGE"] = "N";
// *****************************************************************************************
// *****************************************************************************************
	$StatusUser = "AUTHOR";
	$InputOutput = "AUTHOR_ID";
	$SortingField = "AUTHOR_NAME";
	if (intVal($arParams["FID"]) <= 1)
	{
		$StatusUser = "SENDER";
		$InputOutput = "AUTHOR_ID";
		$SortingField = "AUTHOR_NAME";
	}
	elseif ((intVal($arParams["FID"]) > 1) && (intVal($arParams["FID"]) <=3))
	{
		$StatusUser = "RECIPIENT";
		$InputOutput = "RECIPIENT_ID";
		$SortingField = "RECIPIENT_NAME";
	}
		
	$arFilter = array(
		"USER_ID"=>$arParams["UID"], 
		"FOLDER_ID"=>$arParams["FID"]);
	if ($arParams["FID"] == 2) //If this is outbox folder
	{
		$arFilter = array("OWNER_ID" => $arParams["UID"]);
	}
	
	if (empty($by))
	{
		$by = "post_date";
		$order = "desc";
	}
	
	$dbrMessages = CForumPrivateMessage::GetListEx(array($by=>$order), $arFilter);
	$dbrMessages->NavStart($arParams["PM_PER_PAGE"]);
	$arResult["NAV_STRING"] = $dbrMessages->GetPageNavStringEx($navComponentObject, GetMessage("PM_TITLE_PAGES"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
	if(($dbrMessages) && ($arMsg = $dbrMessages->GetNext()))
	{
		$arResult["MESSAGE"] = array();
		$arResult["SortingEx"]["POST_SUBJ"] = SortingEx("post_subj");
		$arResult["SortingEx"]["AUTHOR_NAME"] = SortingEx(strToLower($SortingField));
		$arResult["SortingEx"]["POST_DATE"] = SortingEx("post_date");
		
		$arResult["StatusUser"] = "AUTHOR";
		$arResult["InputOutput"] = "AUTHOR_ID";
		
		if (intVal($arResult["FID"]) <= 1)
		{
			$arResult["StatusUser"] = "SENDER";
			$arResult["InputOutput"] = "AUTHOR_ID";
		}
		elseif ((intVal($arResult["FID"]) > 1) && (intVal($arResult["FID"]) <=3))
		{
			$arResult["StatusUser"] = "RECIPIENT";
			$arResult["InputOutput"] = "RECIPIENT_ID";
		}
		
		do
		{
			$arMsg["POST_SUBJ"] = wordwrap($arMsg["POST_SUBJ"], 100, " ", 1);
			$arMsg["SHOW_NAME"] = GetUserName($arMsg[$arResult["InputOutput"]]);
			$arMsg["pm_read"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_READ"], array("FID" => $arParams["FID"], "MID" => $arMsg["ID"]));
			$arMsg["pm_edit"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_EDIT"], array("FID" => $arParams["FID"], "mode" => "new", "MID" => 0, "UID" => $arMsg[$arResult["InputOutput"]]));
			$arMsg["profile_view"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $arMsg[$arResult["InputOutput"]]));
			$arMsg["POST_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arMsg["POST_DATE"], CSite::GetDateFormat()));
			$arMsg["checked"] = "";
			if (in_array($arMsg["ID"], $message))
				$arMsg["checked"] = " checked ";
			$arResult["MESSAGE"][] = $arMsg;
		}while($arMsg = $dbrMessages->GetNext());
	}
// *****************************************************************************************
	$arResult["SystemFolder"] = FORUM_SystemFolder;
	$resFolder = CForumPMFolder::GetList(array(), array("USER_ID" => $USER->GetID()));
	$arResult["UserFolder"] = "N";
	if (($resFolder) && ($resF = $resFolder->GetNext()))
	{
		$arResult["UserFolder"] = array();
		do
		{
			$arResult["UserFolder"][$resF["ID"]] = $resF;
		}
		while ($resF = $resFolder->GetNext());
	}
	$arResult["FolderName"] = ($arParams["FID"] > 4) ? $arResult["UserFolder"][$arParams["FID"]]["TITLE"] : GetMessage("PM_FOLDER_ID_".$arParams["FID"]);
// *****************************************************************************************
	$arResult["pm_folder"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_FOLDER"], array());
	if ($arParams["FID"] > 4)
	{
		$title = $arResult["UserFolder"][$arParams["FID"]]["TITLE"];
	}
	else 
	{
		$title = GetMessage("PM_FOLDER_ID_".$arParams["FID"]);
	}
		
	if ($arParams["SET_NAVIGATION"] != "N")
	{
		$APPLICATION->AddChainItem(GetMessage("PM_TITLE_NAV"), $arResult["pm_folder"]);
		$APPLICATION->AddChainItem($title);
	}
// *****************************************************************************************
	if ($arParams["SET_TITLE"] != "N")
		$APPLICATION->SetTitle(str_replace("#TITLE#", $title, GetMessage("PM_TITLE")));
	if($arParams["DISPLAY_PANEL"] == "Y" && $USER->IsAuthorized())
		CForumNew::ShowPanel(0, 0, false);
// *****************************************************************************************
	$this->IncludeComponentTemplate();
// *****************************************************************************************

?>