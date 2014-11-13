<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (CModule::IncludeModule("forum")):
// *****************************************************************************************
	global $APPLICATION;
	$APPLICATION->ResetException();
	$bVarsFromForm = false;
	$bUserFound = false;
	$userRec = array();
	$userSend = array();
	$ShowName = "";
	$ShowMyName = "";
	$NAME = false;
	$EMAIL = false;
	$arParams["TYPE"] = strToUpper(empty($arParams["TYPE"]) ? $_REQUEST["TYPE"] : $arParams["TYPE"]);
	$arParams["TYPE"] = ($arParams["TYPE"]!="ICQ") ? "MAIL" : "ICQ";
	$arParams["SEND_MAIL"] = empty($arParams["SEND_MAIL"]) ? "E" : $arParams["SEND_MAIL"];
	$arParams["SEND_ICQ"] = empty($arParams["SEND_ICQ"]) ? "A" : $arParams["SEND_ICQ"];
	$arParams["UID"] = intVal(empty($arParams["UID"]) ? $_REQUEST["UID"] : $arParams["UID"]);
// *****************************************************************************************
	if ($arParams["SEND_".strToUpper($arParams["TYPE"])] > "A")
	{
		if (($arParams["SEND_".strToUpper($arParams["TYPE"])] <= "E") && (!$USER->IsAuthorized()))
			$GLOBALS["APPLICATION"]->AuthForm($arParams["TYPE"] == "MAIL" ? GetMessage("F_NO_AUTH_MAIL") : GetMessage("F_NO_AUTH_ICQ"));
	// *****************************************************************************************
		ForumSetLastVisit();
	// ***************Search user-recipient*****************************************************
		$URL_NAME_DEFAULT = array(
				"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
			);
		foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
		{
			if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
				$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialchars($arParams["URL_TEMPLATES_".strToUpper($URL)]);
		}
		$arResult["profile_view"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $arParams["UID"]));
	// *******Search USER***********************************************************************
		$db_userX = CUser::GetByID($arParams["UID"]);
		if ($db_userX && ($userRec = $db_userX->GetNext()))
		{
			$bUserFound = True;
			$db_res = CForumUser::GetByUSER_ID($arParams["UID"]);
			if ($db_res)
			{
				while (list($key, $val) = each($db_res))
					$userRec[$key] = htmlspecialchars($val);
			}
		
			if ($userRec["SHOW_NAME"] == "Y")
				$ShowName = trim($userRec["NAME"]." ".$userRec["LAST_NAME"]);
			if (empty($ShowName))
				$ShowName = $userRec["LOGIN"];
		}
		
		if ($USER->IsAuthorized())
		{
			$db_userY = CUser::GetByID($USER->GetID());
			if ($db_userY)
				$userSend = $db_userY->GetNext();
			$db_res = CForumUser::GetByUSER_ID($USER->GetID());
			if ($db_res)
			{
				while (list($key, $val) = each($db_res))
					$userSend[$key] = htmlspecialchars($val);
			}
			
			if ($userSend["SHOW_NAME"] == "Y")
				$ShowMyName = htmlspecialchars($USER->GetFullName());
			if (empty($ShowMyName))
				$ShowMyName = htmlspecialchars($USER->GetLogin());
				
			$NAME = $ShowMyName;
			$EMAIL = ($arParams["TYPE"]=="ICQ") ? $userSend["PERSONAL_ICQ"] : $USER->GetEmail();
		}
	// *****************************************************************************************
	// ****************Action*******************************************************************
		if ($_SERVER["REQUEST_METHOD"]=="POST" && $_POST["ACTION"]=="SEND" && $bUserFound)
		{
			$NAME = trim(empty($NAME) ? $_POST["NAME"] : $NAME);
			$EMAIL = trim(empty($EMAIL) ? $_POST["EMAIL"] : $EMAIL);
			
			// Use captcha
			if (($arParams["SEND_".strToUpper($arParams["TYPE"])] < "Y") && !$USER->IsAuthorized())
			{
				include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/captcha.php");
				$cpt = new CCaptcha();
				if (strlen($_REQUEST["captcha_code"]) > 0)
				{
					$captchaPass = COption::GetOptionString("main", "captcha_password", "");
					if (!$cpt->CheckCodeCrypt($_REQUEST["captcha_word"], $_REQUEST["captcha_code"], $captchaPass))
						$GLOBALS['APPLICATION']->ThrowException(GetMessage("F_BAD_CAPTCHA"), "BAD_CAPTCHA");
				}
				else
				{
					if (!$cpt->CheckCode($_REQUEST["captcha_word"], 0))
						$GLOBALS['APPLICATION']->ThrowException(GetMessage("F_BAD_CAPTCHA"), "NO_CAPTCHA");
				}
			}
			
			if (empty($NAME))
				$GLOBALS['APPLICATION']->ThrowException(GetMessage("F_NO_NAME"), "NO_NAME");
		
			if (empty($EMAIL))
				$GLOBALS['APPLICATION']->ThrowException(GetMessage("F_NO_EMAIL1")." ".(($arParams["TYPE"]=="ICQ") ? GetMessage("F_NO_EMAIL2") : GetMessage("F_NO_EMAIL3")), ($arParams["TYPE"]=="ICQ" ? "NO_ICQ" : "NO_MAIL"));
			elseif ($arParams["TYPE"]!="ICQ" && !check_email($EMAIL))
				$GLOBALS['APPLICATION']->ThrowException(GetMessage("F_BAD_EMAIL"), "BAD_MAIL");
			
			if (empty($_POST["SUBJECT"]))
				$GLOBALS['APPLICATION']->ThrowException(GetMessage("F_NO_SUBJECT"), "NO_SUBJECT");
			if (empty($_POST["MESSAGE"]))
				$GLOBALS['APPLICATION']->ThrowException(GetMessage("F_NO_MESSAGE"), "NO_MESSAGE");
			if ($arParams["TYPE"]=="ICQ" && strlen($userRec["PERSONAL_ICQ"])<=0)
				$GLOBALS['APPLICATION']->ThrowException(GetMessage("F_NO_ICQ_NUM"), "NO_ICQ");
			if ($arParams["TYPE"]=="MAIL" && strlen($userRec["EMAIL"])<=0)
				$GLOBALS['APPLICATION']->ThrowException(GetMessage("F_NO_EMAIL_D"), "NO_MAIL_D");
		
			if (!$GLOBALS['APPLICATION']->GetException())
			{
				if ($arParams["TYPE"]=="ICQ")
				{
					$body   = "From ".$NAME." (UIN ".$EMAIL.")\n";
					$body  .= ($USER->IsAuthorized() ? GetMessage("F_MESS_AUTH") : GetMessage("F_MESS_NOAUTH"))."\n";
					$body  .= "<br>-----<br>\n";
					$body  .= $_POST["SUBJECT"]."\n";
					$body  .= "<br>-----<br>\n";
					$body  .= $_POST["MESSAGE"]."\n";
					$headers  = "Content-Type: text/plain; charset=windows-1254\n";
					$headers .= "From: $NAME\nX-Mailer: System33r";
	//				@mail($x_PERSONAL_ICQ."@pager.mirabilis.com", $_POST["SUBJECT"], $body, $headers);
				}
				else
				{
					$event = new CEvent;
					$arFields = Array(
						"FROM_NAME" => $NAME,
						"FROM_EMAIL" => $EMAIL,
						"TO_NAME" => $ShowName,
						"TO_EMAIL" => $userRec["EMAIL"],
						"SUBJECT" => $_POST["SUBJECT"],
						"MESSAGE" => $_POST["MESSAGE"],
						"MESSAGE_DATE" => date("d.m.Y H:i:s"),
						"AUTH" => ($USER->IsAuthorized() ? GetMessage("F_MESS_AUTH") : GetMessage("F_MESS_NOAUTH"))
					);
					$event->Send("NEW_FORUM_PRIV", SITE_ID, $arFields);
				}
				LocalRedirect(ForumAddPageParams($arResult["profile_view"], array("result" => "message_send")));
			}
			else 
			{
				$bVarsFromForm = true;
			}
		}
		elseif (!$bUserFound)
			$GLOBALS['APPLICATION']->ThrowException(str_replace("#UID#", $arParams["UID"], GetMessage("F_NO_DUSER")), "NO_USER");
		
	// *****************************************************************************************
		$arResult["IsAuthorized"] = $USER->IsAuthorized() ? "Y" : "N";
		
		if ($bUserFound)
		{
	// *****************************************************************************************
			$arResult["ShowName"] = $ShowName;
			if ($USER->IsAuthorized())
			{
				$arResult["ShowMyName"] = $ShowMyName;
				$arResult["AuthorContacts"] = $arParams["TYPE"]=="ICQ" ? $userSend["PERSONAL_ICQ"] : $USER->GetEmail();
			}
			elseif (($arParams["SEND_".strToUpper($arParams["TYPE"])] < "Y") && !$USER->IsAuthorized())
			{
				include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/captcha.php");
				$cpt = new CCaptcha();
				$captchaPass = COption::GetOptionString("main", "captcha_password", "");
				if (strlen($captchaPass) <= 0)
				{
					$captchaPass = randString(10);
					COption::SetOptionString("main", "captcha_password", $captchaPass);
				}
				$cpt->SetCodeCrypt($captchaPass);
				$arResult["CAPTCHA_CODE"] = htmlspecialchars($cpt->GetCodeCrypt());
			}
			if ($bVarsFromForm)
			{
				$arResult["AuthorName"] = htmlspecialcharsEx($_REQUEST["NAME"]);
				$arResult["AuthorMail"] = htmlspecialcharsEx($_REQUEST["EMAIL"]);
				$arResult["MailSubject"] = htmlspecialcharsEx($_REQUEST["SUBJECT"]);
				$arResult["MailMessage"] = htmlspecialcharsEx($_REQUEST["MESSAGE"]);
			}
				
		}
	}
	else 
	{
		if ($arParams["TYPE"] != "ICQ")	
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("F_NO_ACCESS"), "NO_ACCESS");
		else 
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("F_NO_ICQ"), "NO_ACCESS");
			
	}
	if ($GLOBALS['APPLICATION']->GetException())
	{
		$err = $GLOBALS['APPLICATION']->GetException();
		$arResult["ERROR_MESSAGE"] = $err->GetString();
	}
	$arResult["SHOW_USER"] = $bUserFound ? "Y" : "N";
// *****************************************************************************************
	if ($arParams["SET_NAVIGATION"] != "N")
		$APPLICATION->AddChainItem($ShowName, $arResult["profile_view"]);
// *****************************************************************************************
	if ($arParams["SET_TITLE"] != "N")
		$APPLICATION->SetTitle(GetMessage("F_TITLE"));
// *****************************************************************************************
	$this->IncludeComponentTemplate();
// *****************************************************************************************
else:
	ShowError(GetMessage("F_NO_MODULE"));
endif;?>