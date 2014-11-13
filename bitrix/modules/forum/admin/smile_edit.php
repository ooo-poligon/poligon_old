<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
IncludeModuleLangFile(__FILE__);
$forumPermissions = $APPLICATION->GetGroupRight("forum");
if ($forumPermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/include.php");
	//COption::SetOptionString("forum", "REL_FPATH", $NEW_REL_FPATH);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/prolog.php");

$ID = IntVal($ID);

$db_lang = CLangAdmin::GetList(($b="sort"), ($o="asc"));
$langCount = 0;
while ($arLang = $db_lang->Fetch())
{
	$arSysLangs[$langCount] = $arLang["LID"];
	$arSysLangNames[$langCount] = htmlspecialchars($arLang["NAME"]);
	$langCount++;
}


$strErrorMessage = "";
$bInitVars = false;
if ((strlen($save)>0 || strlen($apply)>0) && $REQUEST_METHOD=="POST" && $forumPermissions=="W" && check_bitrix_sessid())
{
	$SORT = IntVal($SORT);
	if ($SORT<=0) $SORT = 150;

	if ($TYPE!="S" && $TYPE!="I")
		$strErrorMessage .= GetMessage("ERROR_NO_TYPE").". \n";

	for ($i = 0; $i<count($arSysLangs); $i++)
	{
		${"NAME_".$arSysLangs[$i]} = Trim(${"NAME_".$arSysLangs[$i]});
		if (strlen(${"NAME_".$arSysLangs[$i]})<=0)
			$strErrorMessage .= GetMessage("ERROR_NO_NAME")." [".$arSysLangs[$i]."] ".$arSysLangNames[$i].". \n";
	}

	if ($ID<=0 && (!is_set($_FILES, "IMAGE1") || strlen($_FILES["IMAGE1"]["name"])<=0))
		$strErrorMessage .= GetMessage("ERROR_NO_IMAGE").". \n";

	$strFileName = $_FILES["IMAGE1"]["name"];
	if (strlen($strErrorMessage)<=0)
	{
		$arOldSmile = false;
		if ($ID>0) $arOldSmile = CForumSmile::GetByID($ID);

		if (is_set($_FILES, "IMAGE1") && strlen($_FILES["IMAGE1"]["name"])>0)
		{
			if ($iFileSize === false)
				$iFileSize = COption::GetOptionString("forum", "file_max_size", 50000);
			
			$res = CFile::CheckImageFile($_FILES["IMAGE1"], $iFileSize, 0, 0);
			if (strLen($res)>0) 
				$strErrorMessage .= $res.". \n";
			
			if (strlen($strErrorMessage)<=0)
			{
				$strDirName = $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/forum/";
				if ($TYPE=="I") $strDirName .= "icon";
				else $strDirName .= "smile";
				$strDirName .= "/";

				CheckDirPath($strDirName);

				if (file_exists($strDirName.$strFileName) 
					&& (!$arOldSmile
						|| $arOldSmile["TYPE"] != $TYPE
						|| $arOldSmile["IMAGE"] != $strFileName
					))
					$strErrorMessage .= GetMessage("ERROR_EXISTS_IMAGE").". \n";
				else
				{
					if (!@copy($_FILES["IMAGE1"]["tmp_name"], $strDirName.$strFileName))
						$strErrorMessage .= GetMessage("ERROR_COPY_IMAGE").". \n";
					else
					{
						@chmod($strDirName.$strFileName, BX_FILE_PERMISSIONS);
						$imgArray = @getimagesize($strDirName.$strFileName);
						if (is_array($imgArray))
						{
							$iIMAGE_WIDTH = $imgArray[0];
							$iIMAGE_HEIGHT = $imgArray[1];
						}
						else
						{
							$iIMAGE_WIDTH = 0;
							$iIMAGE_HEIGHT = 0;
						}
					}
					if ($arOldSmile && ($arOldSmile["TYPE"]!=$TYPE || $arOldSmile["IMAGE"]!=$strFileName) && strlen($arOldSmile["IMAGE"])>0)
					{
						$strDirNameOld = $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/forum/";
						if ($arOldSmile["TYPE"]=="I") $strDirNameOld .= "icon";
						else $strDirNameOld .= "smile";
						$strDirNameOld .= "/".$arOldSmile["IMAGE"];
						@unlink($strDirNameOld);
					}
				}
			}

			if (strlen($strFileName)<=0)
				$strErrorMessage .= GetMessage("ERROR_NO_IMAGE").". \n";
		}
		elseif ($arOldSmile && $arOldSmile["TYPE"]!=$TYPE)
		{
			$strDirNameOld = $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/forum/";
			if ($arOldSmile["TYPE"]=="I") $strDirNameOld .= "icon";
			else $strDirNameOld .= "smile";
			$strDirNameOld .= "/".$arOldSmile["IMAGE"];

			$strDirName = $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/forum/";
			if ($TYPE=="I") $strDirName .= "icon";
			else $strDirName .= "smile";
			$strDirName .= "/".$arOldSmile["IMAGE"];

			if (!@copy($strDirNameOld, $strDirName))
				$strErrorMessage .= GetMessage("ERROR_COPY_IMAGE").". \n";
			else
			{
				CheckDirPath($strDirName);
				@unlink($strDirNameOld);
			}
		}
	}

	if (strlen($strErrorMessage)<=0)
	{
		$arFields = array(
			"SORT" => $SORT,
			"TYPE" => $TYPE,
			"TYPING" => $TYPING,
			"DESCRIPTION" => $DESCRIPTION
			);

		if (strlen($strFileName)>0)
		{
			$arFields["IMAGE"] = $strFileName;
			$arFields["IMAGE_WIDTH"] = $iIMAGE_WIDTH;
			$arFields["IMAGE_HEIGHT"] = $iIMAGE_HEIGHT;
		}

		for ($i = 0; $i<count($arSysLangs); $i++)
		{
			$arFields["LANG"][] = array(
				"LID" => $arSysLangs[$i],
				"NAME" => ${"NAME_".$arSysLangs[$i]}
				);
		}

		if ($ID>0)
		{
			$ID1 = CForumSmile::Update($ID, $arFields);
			if (IntVal($ID1)<=0)
				$strErrorMessage .= GetMessage("ERROR_EDIT_SMILE").". \n";
		}
		else
		{
			$ID = CForumSmile::Add($arFields);
			if (IntVal($ID)<=0)
				$strErrorMessage .= GetMessage("ERROR_ADD_SMILE").". \n";
		}
		BXClearCache(true, "/".LANG."/forum/smilesList/");
		BXClearCache(true, "/".LANG."/forum/iconsList/");
		BXClearCache(true, "/".LANG."/forum/smiles/");
	}

	if (strlen($strErrorMessage)>0) $bInitVars = True;

	if (strlen($save)>0 && strlen($strErrorMessage)<=0)
		LocalRedirect("forum_smile.php?lang=".LANG."&".GetFilterParams("filter_", false));
}

$str_SORT = 150;

if ($ID > 0)
{
	$db_smile = CForumSmile::GetList(array(), array("ID" => $ID));
	$db_smile->ExtractFields("str_", True);
	$f_IMAGE = $str_IMAGE;
	$f_IMAGE_WIDTH = $str_IMAGE_WIDTH;
	$f_IMAGE_HEIGHT = $str_IMAGE_HEIGHT;
	$f_TYPE = $str_TYPE;
}

if ($bInitVars)
{
	$DB->InitTableVarsForEdit("b_forum_smile", "", "str_");
}

$sDocTitle = ($ID>0) ? eregi_replace("#ID#", "$ID", GetMessage("FORUM_EDIT_RECORD")) : GetMessage("FORUM_NEW_RECORD");
$APPLICATION->SetTitle($sDocTitle);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
?>

<?
$aMenu = array(
	array(
		"TEXT" => GetMessage("FSN_2FLIST"),
		"LINK" => "/bitrix/admin/forum_smile.php?lang=".LANG."&".GetFilterParams("filter_", false),
		"ICON" => "btn_list",
	)
);

if ($ID > 0 && $forumPermissions == "W")
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
		"TEXT" => GetMessage("FSN_NEW_SMILE"),
		"LINK" => "/bitrix/admin/forum_smile_edit.php?lang=".LANG."&".GetFilterParams("filter_", false),
		"ICON" => "btn_new",
	);

	$aMenu[] = array(
		"TEXT" => GetMessage("FSN_DELETE_SMILE"), 
		"LINK" => "javascript:if(confirm('".GetMessage("FSN_DELETE_SMILE_CONFIRM")."')) window.location='/bitrix/admin/forum_smile.php?action=delete&ID[]=".$ID."&lang=".LANG."&".bitrix_sessid_get()."#tb';",
		"ICON" => "btn_delete",
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?CAdminMessage::ShowMessage($strErrorMessage);?>

<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="fform" enctype="multipart/form-data">
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?echo LANG ?>">
<input type="hidden" name="ID" value="<?echo $ID ?>">
<?=bitrix_sessid_post()?>

<?
$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("FSN_TAB_SMILE"), "ICON" => "forum", "TITLE" => GetMessage("FSN_TAB_SMILE_DESCR"))
	);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>

<?
$tabControl->BeginNextTab();
?>

	<?if ($ID > 0):?>
	<tr>
		<td width="40%"><?echo GetMessage("FORUM_CODE")?>:</td>
		<td width="60%"><?echo $ID ?></td>
	</tr>
	<?endif;?>

	<tr>
		<td width="40%"><?echo GetMessage("FORUM_SORT")?>:</td>
		<td width="60%">
			<input type="text" name="SORT" value="<?echo $str_SORT ?>" size="10">
		</td>
	</tr>

	<tr>
		<td><?echo GetMessage("FORUM_TYPE")?>:</td>
		<td>
			<select name="TYPE">
				<option value="S" <?if ($str_TYPE=="S") echo "selected";?>><?echo GetMessage("FSE_SMILE");?></option>
				<option value="I" <?if ($str_TYPE=="I") echo "selected";?>><?echo GetMessage("FSE_ICON");?></option>
			</select>
		</td>
	</tr>

	<tr>
		<td valign="top"><?echo GetMessage("FORUM_TYPING")?>:<br><small><?echo GetMessage("FORUM_TYPING_NOTE")?></small></td>
		<td valign="top">
			<input type="text" name="TYPING" value="<?echo $str_TYPING ?>" size="40">
		</td>
	</tr>

	<tr>
		<td><?echo GetMessage("FORUM_IMAGE")?>:<br><small><?echo GetMessage("FORUM_IMAGE_NOTE")?></small></td>
		<td>
			<input type="file" name="IMAGE1" size="30">
			<?
			if (strlen($f_IMAGE)>0)
			{
				?><br><img src="/bitrix/images/forum/<?echo ($f_TYPE=="I")?"icon":"smile" ?>/<?echo $f_IMAGE?>" border="0" <?echo (IntVal($f_IMAGE_WIDTH)>0) ? "width=\"".$f_IMAGE_WIDTH."\"" : "" ?> <?echo (IntVal($f_IMAGE_WIDTH)>0) ? "height=\"".$f_IMAGE_HEIGHT."\"" : "" ?>><?
			}
			?>&nbsp;
			/bitrix/images/forum/<?echo ($f_TYPE=="I")?"icon":"smile" ?>/<?echo $f_IMAGE?>
		</td>
	</tr>

	<?
	for ($i = 0; $i < count($arSysLangs); $i++):
		$arSmileLang = CForumSmile::GetLangByID($ID, $arSysLangs[$i]);
		$str_NAME = htmlspecialchars($arSmileLang["NAME"]);
		$str_DESCRIPTION = htmlspecialchars($arSmileLang["DESCRIPTION"]);
		if ($bInitVars)
		{
			$str_NAME = htmlspecialchars(${"NAME_".$arSysLangs[$i]});
			$str_DESCRIPTION = htmlspecialchars(${"DESCRIPTION_".$arSysLangs[$i]});
		}
		?>
		<tr class="heading">
			<td colspan="2">[<?echo $arSysLangs[$i];?>] <?echo $arSysLangNames[$i];?></td>
		</tr>
		<tr>
			<td>
				<span class="required">*</span><?=GetMessage("FORUM_IMAGE_NAME")?>:
			</td>
			<td>
				<input type="text" name="NAME_<?=$arSysLangs[$i] ?>" value="<?=$str_NAME ?>" size="40">
			</td>
		</tr>
	<?endfor;?>

<?
$tabControl->EndTab();
?>

<?
$tabControl->Buttons(
		array(
				"disabled" => ($forumPermissions < "W"),
				"back_url" => "/bitrix/admin/forum_smile.php?lang=".LANG."&".GetFilterParams("filter_", false)
			)
	);
?>

<?
$tabControl->End();
?>

</form>
<?require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>