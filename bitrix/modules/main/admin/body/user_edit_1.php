<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2005 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

require_once(dirname(__FILE__)."/../../include/prolog_admin_before.php");
$MAIN_RIGHT = $APPLICATION->GetGroupRight("main");
if($MAIN_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
if($MAIN_RIGHT=="P")
{
	$ID=$USER->GetID();
	if (intval($ID)<=0) $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/admin/user_edit.php");

/***************************************************************************
					   Обработка GET | POST
****************************************************************************/

$uid = $USER->GetID();
$editable = (($MAIN_RIGHT=="P" && $ID==$uid) || ($MAIN_RIGHT=="T" && $ID==$uid) || $MAIN_RIGHT=="W") ? true : false;

if (strlen($show_personal)>0) $_SESSION["SESS_USER_PERSONAL_INFO"] = $show_personal;
else $show_personal = $_SESSION["SESS_USER_PERSONAL_INFO"];
if (strlen($show_personal)<=0) $show_personal = "none";

if (strlen($show_work)>0) $_SESSION["SESS_USER_WORK_INFO"] = $show_work;
else $show_work = $_SESSION["SESS_USER_WORK_INFO"];
if (strlen($show_work)<=0) $show_work = "none";

if (strlen($show_admin)>0) $_SESSION["SESS_USER_ADMIN_NOTES"] = $show_admin;
else $show_admin = $_SESSION["SESS_USER_ADMIN_NOTES"];
if (strlen($show_admin)<=0) $show_admin = "none";

$strError="";
$ID=IntVal($ID);

if($REQUEST_METHOD=="POST" && (strlen($save)>0 || strlen($apply)>0 || $Update=="Y") && $editable && check_bitrix_sessid())
{
	$strError="";
	$user = new CUser;

	if($ID=="1")
	{
		$ACTIVE = "Y";
		$GROUP_ID[]=1;
	}

	$z = $DB->Query("SELECT WORK_LOGO, PERSONAL_PHOTO FROM b_user WHERE ID='$ID'", false, "FILE: ".__FILE__." LINE:".__LINE__);
	$zr = $z->Fetch();

	$arPERSONAL_PHOTO = $HTTP_POST_FILES["PERSONAL_PHOTO"];
	$arPERSONAL_PHOTO["old_file"] = $zr["PERSONAL_PHOTO"];
	$arPERSONAL_PHOTO["del"] = ${"PERSONAL_PHOTO_del"};

	$arWORK_LOGO = $HTTP_POST_FILES["WORK_LOGO"];
	$arWORK_LOGO["old_file"] = $zr["WORK_LOGO"];
	$arWORK_LOGO["del"] = ${"WORK_LOGO_del"};

	$arFields = Array(
		"NAME"					=> $NAME,
		"LAST_NAME"				=> $LAST_NAME,
		"EMAIL"					=> $EMAIL,
		"LOGIN"					=> $LOGIN,
		"PERSONAL_PROFESSION"	=> $PERSONAL_PROFESSION,
		"PERSONAL_WWW"			=> $PERSONAL_WWW,
		"PERSONAL_ICQ"			=> $PERSONAL_ICQ,
		"PERSONAL_GENDER"		=> $PERSONAL_GENDER,
//		"PERSONAL_BIRTHDATE"	=> $PERSONAL_BIRTHDATE,
		"PERSONAL_BIRTHDAY"		=> $PERSONAL_BIRTHDAY,
		"PERSONAL_PHOTO"		=> $arPERSONAL_PHOTO,
		"PERSONAL_PHONE"		=> $PERSONAL_PHONE,
		"PERSONAL_FAX"			=> $PERSONAL_FAX,
		"PERSONAL_MOBILE"		=> $PERSONAL_MOBILE,
		"PERSONAL_PAGER"		=> $PERSONAL_PAGER,
		"PERSONAL_STREET"		=> $PERSONAL_STREET,
		"PERSONAL_MAILBOX"		=> $PERSONAL_MAILBOX,
		"PERSONAL_CITY"			=> $PERSONAL_CITY,
		"PERSONAL_STATE"		=> $PERSONAL_STATE,
		"PERSONAL_ZIP"			=> $PERSONAL_ZIP,
		"PERSONAL_COUNTRY"		=> $PERSONAL_COUNTRY,
		"PERSONAL_NOTES"		=> $PERSONAL_NOTES,
		"WORK_COMPANY"			=> $WORK_COMPANY,
		"WORK_DEPARTMENT"		=> $WORK_DEPARTMENT,
		"WORK_POSITION"			=> $WORK_POSITION,
		"WORK_WWW"				=> $WORK_WWW,
		"WORK_PHONE"			=> $WORK_PHONE,
		"WORK_FAX"				=> $WORK_FAX,
		"WORK_PAGER"			=> $WORK_PAGER,
		"WORK_STREET"			=> $WORK_STREET,
		"WORK_MAILBOX"			=> $WORK_MAILBOX,
		"WORK_CITY"				=> $WORK_CITY,
		"WORK_STATE"			=> $WORK_STATE,
		"WORK_ZIP"				=> $WORK_ZIP,
		"WORK_COUNTRY"			=> $WORK_COUNTRY,
		"WORK_PROFILE"			=> $WORK_PROFILE,
		"WORK_LOGO"				=> $arWORK_LOGO,
		"WORK_NOTES"			=> $WORK_NOTES
		);

	if($MAIN_RIGHT=="W" && strlen($LID)>0)
	{
		$arFields["LID"] = $LID;
	}

	if($MAIN_RIGHT=="W" && is_set($_REQUEST, 'EXTERNAL_AUTH_ID'))
	{
		$arFields['EXTERNAL_AUTH_ID'] = $EXTERNAL_AUTH_ID;
	}

	if($USER->IsAdmin())
	{
		$arFields["ACTIVE"]=$ACTIVE;
		$arFields["GROUP_ID"]=$GROUP_ID;
		$arFields["ADMIN_NOTES"]=$ADMIN_NOTES;
	}

	if(strlen($NEW_PASSWORD)>0)
	{
		$arFields["PASSWORD"]=$NEW_PASSWORD;
		$arFields["CONFIRM_PASSWORD"]=$NEW_PASSWORD_CONFIRM;
	}

	if($ID>0)
	{
		$res = $user->Update($ID, $arFields, true);
	}
	else
	{
		$ID = $user->Add($arFields);
		$res = ($ID>0);
		$new="Y";
	}

	$strError .= $user->LAST_ERROR;

	if (strlen($strError)<=0)
	{
		if (is_array($profile_module_id) && count($profile_module_id)>0)
		{
			$db_opt_res = $DB->Query("SELECT ID FROM b_module");
			while ($opt_res = $db_opt_res->Fetch())
			{
				if (in_array($opt_res["ID"],$profile_module_id))
				{
					$mdir = $opt_res["ID"];
					if (file_exists($DOCUMENT_ROOT.BX_ROOT."/modules/".$mdir) && is_dir($DOCUMENT_ROOT.BX_ROOT."/modules/".$mdir))
					{
						$ofile = $DOCUMENT_ROOT.BX_ROOT."/modules/".$mdir."/options_user_settings_set.php";
						if (file_exists($ofile))
						{
							$MODULE_RIGHT = $APPLICATION->GetGroupRight($mdir);
							if ($MODULE_RIGHT>="R")
							{
								include($ofile);
								$res = $res && ${$mdir."_res"};
								if (!${$mdir."_res"}) $strError .= ${$mdir."WarningTmp"};
							}
						}
					}
				}
			}
		}

		if (strlen($strError)<=0)
		{
			if($user_info_event=="Y")
			{
        		if(!defined("ADMIN_SECTION") || ADMIN_SECTION !== true || strlen($user_info_event_lang)<=0)
        			$user_info_event_lang = LANG;

				if($new=="Y")
					$user->SendUserInfo($ID, $LID, GetMessage("ACCOUNT_INSERT"));
				else
					$user->SendUserInfo($ID, $LID, GetMessage("ACCOUNT_UPDATE"));
			}
			if(($MAIN_RIGHT=="W" || $MAIN_RIGHT=="T")&& strlen($save)>0)
				LocalRedirect($strRedirect_admin);
			elseif(($MAIN_RIGHT=="W" || $MAIN_RIGHT=="T")&& strlen($apply)>0)
				LocalRedirect($strRedirect."&ID=".$ID);
			elseif($new=="Y")
				LocalRedirect($strRedirect."&ID=".$ID);
		}
	}
}

$user = CUser::GetByID($ID);
if(!$user->ExtractFields("str_"))
{
	$ID=0;
	$str_ACTIVE="Y";
}
else
	$str_GROUP_ID=CUser::GetUserGroup($ID);

if(strlen($strError)>0)
{
	$DB->InitTableVarsForEdit("b_user", "", "str_");
	$str_GROUP_ID = $GROUP_ID;
}

if(!is_array($str_GROUP_ID)) $str_GROUP_ID=Array();

$isIE = true;
if (!IsIE())
{
	$isIE = false;
	$show_personal = "inline";
	$show_work = "inline";
	$show_admin = "inline";
}

?>
