<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum"))
	return 0;
$this->IncludeComponentLang("action.php");

if ((strLen($action) > 0) && ($_REQUEST["MESSAGE_MODE"] != "VIEW") && check_bitrix_sessid())
{
	//*************************!Subscribe***************************************************
	if ($_REQUEST["TOPIC_SUBSCRIBE"] == "Y")
		ForumSubscribeNewMessagesEx($arParams["FID"], $arParams["TID"], "N", $strErrorMessage, $strOKMessage);
	if ($_REQUEST["FORUM_SUBSCRIBE"] == "Y")
		ForumSubscribeNewMessagesEx($arParams["FID"], 0, "N", $strErrorMessage, $strOKMessage);
	//*************************!Subscribe***************************************************
	if (strLen($action) > 0 && $action != "SUBSCRIBE")
	{
		$arFields = array();
		$url = false;
		$code = false;
		$message = (!empty($_REQUEST["MID_ARRAY"]) ? $_REQUEST["MID_ARRAY"] : $_REQUEST["MID"]);
		if ((empty($message) || $message == "s") && !empty($_REQUEST["message_id"]))
			$message = $_REQUEST["message_id"];

		switch ($action)
		{
			case "REPLY":
				$arFields = array(
						"FID" => $arParams["FID"],
						"TID" => $arParams["TID"],
						"POST_MESSAGE" => $_POST["POST_MESSAGE"],
						"AUTHOR_NAME" => $_POST["AUTHOR_NAME"],
						"AUTHOR_EMAIL" => $_POST["AUTHOR_EMAIL"],
						"USE_SMILES" => $_POST["USE_SMILES"],
						"ATTACH_IMG" => $_FILES["ATTACH_IMG"],
						"captcha_word" =>  $_POST["captcha_word"],
						"captcha_code" => $_POST["captcha_code"]);
				$url = CComponentEngine::MakePathFromTemplate(
						$arParams["~URL_TEMPLATES_MESSAGE"], 
							array("FID" => $arParams["FID"], "TID" => $arParams["TID"], "MID"=>"#result#"));
				break;
			case "VOTE4USER":
				$arFields = array(
					"UID" => $_GET["UID"],
					"VOTES" => $_GET["VOTES"],
					"VOTE" => (($_GET["VOTES_TYPE"]=="U") ? True : False));
				$url = CComponentEngine::MakePathFromTemplate(
					$arParams["~URL_TEMPLATES_MESSAGE"], 
					array("FID" => $arParams["FID"], 
						"TID" => $arParams["TID"], 
						"MID" => (intVal($_REQUEST["MID"]) > 0 ? $_REQUEST["MID"] : "s")
					));
				break;
			case "HIDE":
			case "SHOW":
			case "FORUM_MESSAGE2SUPPORT":
				$arFields = array("MID" => $message);
				$mid = (is_array($message) ? $message[0] : $message);
				$url = CComponentEngine::MakePathFromTemplate(
						$arParams["~URL_TEMPLATES_MESSAGE"], 
						array(
							"FID" => $arParams["FID"], 
							"TID" => $arParams["TID"], 
							"MID" => (!empty($mid) ? $mid : "s")
						));
				if ($action == "FORUM_MESSAGE2SUPPORT")
				{
					$url = "/bitrix/admin/ticket_edit.php?ID=#result#&amp;lang=".LANGUAGE_ID;
				}
				break;
			case "DEL":
				$arFields = array("MID" => $message);
				$url = CComponentEngine::MakePathFromTemplate(
						$arParams["~URL_TEMPLATES_MESSAGE"], 
						array(
							"FID" => $arParams["FID"], 
							"TID" => $arParams["TID"], 
							"MID" => "#MID#"
						));
				break;
			case "SET_ORDINARY":
			case "SET_TOP":
			case "STATE_Y":
			case "STATE_N":
				if ($action == "STATE_Y")
					$action = "OPEN";
				elseif ($action == "STATE_N")
					$action = "CLOSE";
				elseif ($action == "SET_ORDINARY")
					$action = "ORDINARY";
				else 
					$action = "TOP";
					
				$arFields = array("TID" => $arParams["TID"]);
				$url = CComponentEngine::MakePathFromTemplate(
					$arParams["~URL_TEMPLATES_MESSAGE"], 
					array("FID" => $arParams["FID"], 
						"TID" => $arParams["TID"], 
						"MID" => ($arParams["MID"] > 0 ? $arParams["MID"] : "s")));
				break;
			case "DEL_TOPIC":
					$arFields = array("TID" => $arParams["TID"]);
					$url = CComponentEngine::MakePathFromTemplate(
						$arParams["~URL_TEMPLATES_LIST"], 
						array("FID" => $arParams["FID"]));
				break;
			case "FORUM_SUBSCRIBE":
			case "TOPIC_SUBSCRIBE":
			case "FORUM_SUBSCRIBE_TOPICS":
				$arFields = array(
					"FID" => $arParams["FID"],
					"TID" => (($action=="FORUM_SUBSCRIBE")?0:$arParams["TID"]),
					"NEW_TOPIC_ONLY" => (($action=="FORUM_SUBSCRIBE_TOPICS")?"Y":"N"));
				$url = ForumAddPageParams(
						CComponentEngine::MakePathFromTemplate(
							$arParams["~URL_TEMPLATES_SUBSCR_LIST"], 
							array()
						), 
						array("FID" => $arParams["FID"], "TID" => $arParams["TID"]));
				break;
			case "MOVE":
				$tmp_message = ForumDataToArray($message);
				$url = CComponentEngine::MakePathFromTemplate(
						$arParams["~URL_TEMPLATES_MESSAGE_MOVE"], 
						array("FID" => $arParams["FID"], "TID" => $arParams["TID"], "MID" => implode(",", $tmp_message)));
				break;
			case "MOVE_TOPIC":
				$url = CComponentEngine::MakePathFromTemplate(
							$arParams["~URL_TEMPLATES_TOPIC_MOVE"], 
							array("FID" => $arParams["FID"], "TID" => $arParams["TID"]));
				break;
		}
		if ($action != "MOVE" && $action != "MOVE_TOPIC")
		{
			$res = ForumActions($action, $arFields, $strErrorMessage, $strOKMessage);
			if ($action == "DEL")
			{
				$arFields = CForumTopic::GetByID($arParams["TID"]);
				if (empty($arFields))
				{
					$url = CComponentEngine::MakePathFromTemplate(
						$arParams["~URL_TEMPLATES_LIST"], 
						array("FID" => $arParams["FID"]));
					$action = "del_topic";
				}
				else 
				{
					$res = intVal($message);
					if (is_array($message))
					{
						sort($message);
						$res = array_pop($message);
					}
					$arFilter = array("TOPIC_ID"=>$arParams["TID"]);
					if ($arResult["UserPermission"] < "Q") 
						$arFilter["APPROVED"] = "Y";
					$arFilter[">ID"] = $res;
					$db_res = CForumMessage::GetList(array("ID"=>"ASC"), $arFilter);
					if ($db_res && $res = $db_res->Fetch())
						$mid = $res["ID"];
					else
						$mid = "s";

					$url = str_replace("#MID#", $mid, $url);
				}
				$res = true;
			}
			elseif ($action == "REPLY")
			{
				$arParams["MID"] = intVal($res);
			}
			
			$url = str_replace("#result#", $res, $url);
		}
		else
			$res = true;
		$action = strToLower($action);
	}
	
	if (!$res)
	{
		$bVarsFromForm = true;
	}
	else 
	{
		BXClearCache(true, "/".SITE_ID."/forum/topic/".$arParams["TID"]."/");
		$arNote = array(
			"code" => $action,
			"title" => $strOKMessage, 
			"link" => $url);
	}
}
elseif ((strLen($action) > 0) && ($_REQUEST["MESSAGE_MODE"] != "VIEW") && !check_bitrix_sessid())
{
	$bVarsFromForm = true;
	$strErrorMessage = GetMessage("F_ERR_SESS_FINISH");
}
elseif($_POST["MESSAGE_MODE"] == "VIEW")
{
	$View = true;
	$bVarsFromForm = true;
}
?>