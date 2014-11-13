<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("PM_NO_MODULE"));
	return 0;
elseif (!$USER->IsAuthorized()):
	$APPLICATION->AuthForm(GetMessage("PM_AUTH"));	
	return 0;
endif;
// *****************************************************************************************
	$strError = "";
	$strOK = "";
	$strNote = "";
	$APPLICATION->ResetException();
	$APPLICATION->ThrowException(" ");

// ************************* Input params***************************************************************
// ************************* BASE **********************************************************************
	$arParams["FID"] = intVal(intVal($arParams["FID"]) <= 0 ? $_REQUEST["FID"] : $_REQUEST["FID"]);
	$arParams["mode"] = $_REQUEST["mode"];
	$action = strToLower($_REQUEST["action"]);
	$version = COption::GetOptionString("forum", "UsePMVersion", "2");

	switch (strToLower($_REQUEST["result"]))
	{
		case "create":
		case "save":
			$strNote = GetMessage("PM_SUCC_CREATE");
			break;
		case "delete":
			$strNote = GetMessage("PM_SUCC_DELETE");
			break;
		case "remove":
			$strNote = GetMessage("PM_SUCC_REMOVE");
			break;
		case "saved":
		case "update":
			$strNote = GetMessage("PM_SUCC_SAVED");
			break;
	}
// ************************* URL ***********************************************************************
	$URL_NAME_DEFAULT = array(
		"pm_folder" => "PAGE_NAME=pm_folder",
		"pm_list" => "PAGE_NAME=pm_list&FID=#FID#",
		"profile_view" => "PAGE_NAME=profile_view&UID=#UID#");
		
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
	$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "N" ? "N" : "Y");
// ************************* CACHE & TITLE *************************************************************
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
// *************************/Input params***************************************************************

// ************************* Default params*************************************************************
$arResult["CURRENT_PAGE"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_FOLDER"], array());
$arResult["ERROR_MESSAGE"] = "";
$arResult["POST_VALUES"] = array();

$arResult["create_new_folder"] = ForumAddPageParams($arResult["CURRENT_PAGE"], array("mode" => "new"));
$arParams["version"] = intVal(COption::GetOptionString("forum", "UsePMVersion", "2"));
// *****************************************************************************************
	ForumSetLastVisit();
// *************************/Default params*************************************************************

// ************************* Action ********************************************************************
	if (!empty($action))
	{
		switch($action)
		{
			case "update":
				$db_res = CForumPMFolder::GetList(array(), array("ID"=>$arParams["FID"]));
				if (!($db_res && ($res = $db_res->GetNext())))
				{
					$arError[] = array(
						"code" => "bad_fid",
						"title" => GetMessage("PM_NOT_FOLDER"));
				}
				elseif (!CForumPMFolder::CheckPermissions($arParams["FID"]))
				{
					$arError[] = array(
						"code" => "bad_permission",
						"title" => GetMessage("PM_NOT_RIGHT"));
				}
				elseif (!CForumPMFolder::Update($arParams["FID"], array("TITLE"=>$_REQUEST["FOLDER_TITLE"])))
				{
					$str = "";
					if (($err = $APPLICATION->GetException()) && $err)
						$str = $err->GetString();
					$arError[] = array(
						"code" => "not_updated",
						"title" => $str);
				}
				break;
			case "save":
				$_REQUEST["FOLDER_TITLE"] = trim($_REQUEST["FOLDER_TITLE"]);
				if (empty($_REQUEST["FOLDER_TITLE"]))
				{
					$arError[] = array(
						"code" => "empty_data",
						"title" => GetMessage("PM_NOT_FOLDER_TITLE"));
				}
				elseif (!CForumPMFolder::Add($_REQUEST["FOLDER_TITLE"]))
				{
					$str = "";
					
					if ($APPLICATION->GetException())
					{
						$err = $APPLICATION->GetException();
						$str = $err->GetString();
					}
					$arError[] = array(
						"code" => "not_add",
						"title" => $str);
				}
				break;
			case "delete":
			case "remove":
				$remMes = true;
				if (!CForumPMFolder::CheckPermissions($arParams["FID"]))
				{
					$arError[] = array(
						"code" => "bad_permission",
						"title" => GetMessage("PM_NOT_RIGHT"));
				}
				else
				{
					$arFilter = array("FOLDER_ID"=>$arParams["FID"], "USER_ID"=>$USER->GetId());
					if ($version == "2" && ($arParams["FID"] == 2 || $arParams["FID"] == 3))
					{
						$arFilter = array("OWNER_ID"=>$USER->GetId());
					}
					elseif ($version != "2" && $arParams["FID"] == 2)
					{
						$arFilter = array("FOLDER_ID"=>2, "USER_ID"=>$USER->GetId(), "OWNER_ID"=>$USER->GetId());
					}
					
					$arMessage = CForumPrivateMessage::GetListEx(array(), $arFilter);
					while ($res = $arMessage->GetNext())
					{
						if(!CForumPrivateMessage::Delete($res["ID"]))
						{
							$arError[] = array(
								"code" => "bad_delete_".$res["ID"],
								"title" => GetMessage("PM_NOT_DELETE"));
						}
					}
					if (empty($arError) && $action == "delete" && !CForumPMFolder::Delete($arParams["FID"]))
					{
						$arError[] = array(
							"code" => "not_delete",
							"title" => GetMessage("PM_NOT_DELETE"));
					}
				}
				break;
		}
		if (empty($arError))
		{
			LocalRedirect(ForumAddPageParams($arResult["CURRENT_PAGE"], array("res" => $action), false, false));
		}
		else 
		{
			$arRes = array();
			foreach ($arError as $res)
				$arRes[] = (empty($res["title"]) ? $res["code"] : $res["title"]);
			$arResult["ERROR_MESSAGE"] = implode("<br />", $arRes);
		}
	}
// *************************/Action ********************************************************************

// ************************* Page **********************************************************************
	$arResult["count"] = CForumPrivateMessage::PMSize($USER->GetID(), COption::GetOptionInt("forum", "MaxPrivateMessages", 100));
	$arResult["count"] = round($arResult["count"]*100);
	InitSorting();
	global $by, $order;
	$arResult["SortingExTitle"] = SortingEx("title");
	$arResult["SortingExCount"] = SortingEx("count");
	$arResult["FORUM_SystemFolder"] = FORUM_SystemFolder;
	$arResult["SYSTEM_FOLDER"] = array();
	$arResult["USER_FOLDER"] = array();
// *****************************************************************************************
	$arResult["sessid"] = bitrix_sessid_post();
	$arResult["FID"] = $arParams["FID"];
 	$arResult["action"] = $arParams["mode"]=="new" ? "save" : "update";
// *****************************************************************************************
	if ($arParams["mode"] == "edit" || $arParams["mode"] == "new")
	{
		if (intVal($arParams["FID"]) > 0)
		{
	 		$db_res = CForumPMFolder::GetList(array(), array("ID"=>$arParams["FID"]));
	 		if ($db_res && ($res = $db_res->GetNext()))
	 		{
	 			$arResult["POST_VALUES"]["FOLDER_TITLE"] = $res["TITLE"];
	 		}
		}
		if (!empty($arError))
		{
			$arResult["POST_VALUES"]["FOLDER_TITLE"] = htmlspecialcharsEx($_REQUEST["FOLDER_TITLE"]);
		}
	}
// *****************************************************************************************
	if (($arParams["mode"] == "edit") || ($arParams["mode"] == "new"))
	{
		$title = ($arParams["mode"] == "edit" ? GetMessage("PM_TITLE_EDIT") : GetMessage("PM_TITLE_NEW"));
 		$title = str_replace("#TITLE#", $arResult["POST_VALUES"]["FOLDER_TITLE"], $title);
	}
	else 
	{
	 	$title = GetMessage("PM_TITLE_LIST");
		for ($ii = 1; $ii <= FORUM_SystemFolder; $ii++)
		{
			$arResult["SYSTEM_FOLDER"][$ii]["cnt"] = "";
			$arFilter = ($ii == 2 ? array("FOLDER_ID"=>$ii, "USER_ID"=>$USER->GetId(), "OWNER_ID"=>$USER->GetId()) : array("FOLDER_ID"=>$ii, "USER_ID"=>$USER->GetId()));
			$db_res = CForumPrivateMessage::GetList(array(), $arFilter, true);
			if ($db_res && ($res = $db_res->GetNext()))
				$arResult["SYSTEM_FOLDER"][$ii]["cnt"] = intVal($res["CNT"]);
			$arResult["SYSTEM_FOLDER"][$ii]["pm_list"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_LIST"], 
				array("FID" => $ii));
			$arResult["SYSTEM_FOLDER"][$ii]["remove"] = ForumAddPageParams($arResult["CURRENT_PAGE"], 
				array("action" => "remove", "FID" => $ii));
		}
		$arResult["SHOW_USER_FOLDER"] = "N";
		$db_res = CForumPMFolder::GetList(array($by=>$order), array("USER_ID"=>$USER->GetId()));
		if ($db_res && ($res = $db_res->GetNext()))
		{
			$arResult["SHOW_USER_FOLDER"] = "Y";
				do
				{
					$res["pm_list"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_LIST"], array("FID" => $res["ID"]));
					$res["CNT"] = intVal($res["CNT"]);
					$res["delete"] =  ForumAddPageParams($arResult["CURRENT_PAGE"] , array("action" => "delete", "FID" => $res["ID"]));
					$res["remove"] = ForumAddPageParams($arResult["CURRENT_PAGE"] , array("action" => "remove", "FID" => $res["ID"]));
					$res["edit"] = ForumAddPageParams($arResult["CURRENT_PAGE"] , array("mode" => "edit", "FID" => $res["ID"]));
					$arResult["USER_FOLDER"][] = $res;
				}
				while ($res = $db_res->GetNext());
		}
	}
	
// *****************************************************************************************
	if ($arParams["SET_NAVIGATION"] != "N")
	{
		$APPLICATION->AddChainItem(GetMessage("PM_PM"), CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_LIST"], array("FID" => 1)));
		if (($arParams["mode"] == "edit") || ($arParams["mode"] == "new"))
		{
			$APPLICATION->AddChainItem(GetMessage("PM_TITLE_LIST"), $arResult["CURRENT_PAGE"]);
			$APPLICATION->AddChainItem($arResult["POST_VALUES"]["FOLDER_TITLE"]);
		}
		else 
		{
			$APPLICATION->AddChainItem($title);
		}
	} 
// *****************************************************************************************
	if ($arParams["SET_TITLE"] != "N")
	{
		$APPLICATION->SetTitle($title);
	}
// *****************************************************************************************
		$this->IncludeComponentTemplate();
// *****************************************************************************************

?>