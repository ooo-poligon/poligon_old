<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$forumPermissions = $APPLICATION->GetGroupRight("forum");
if ($forumPermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/include.php");
IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/prolog.php");

$strErrorMessage = "";
$bVarsFromForm = false;
$ID = IntVal($ID);

if ($REQUEST_METHOD=="POST" && strlen($Update)>0 && $forumPermissions=="W" && check_bitrix_sessid())
{
	if ($ID>0)
	{
		if (!CForumNew::CanUserUpdateForum($ID, $USER->GetUserGroupArray(), $USER->GetID()))
			$strErrorMessage .= GetMessage("FE_NO_PERMS2UPDATE").". \n";
	}
	else
	{
		if (!CForumNew::CanUserAddForum($USER->GetUserGroupArray(), $USER->GetID()))
			$strErrorMessage .= GetMessage("FE_NO_PERMS2ADD").". \n";
	}

	if (strlen($NAME)<=0)
		$strErrorMessage .= GetMessage("FE_NO_NAME").". \n";

	$SORT = IntVal($SORT);
	if ($SORT<=0) $SORT = 150;

	$SITES = array();
	$db_sites = CLang::GetList($lby="sort", $lorder="asc");
	while ($ar_sites = $db_sites->Fetch())
	{
		if (isset($SITE[$ar_sites["LID"]]) && $SITE[$ar_sites["LID"]]=="Y")
		{
			if (strlen($SITE_PATH[$ar_sites["LID"]])<=0)
			{
				$strErrorMessage .= str_replace("#SITE#", "[".$ar_sites["LID"]."] ".$ar_sites["NAME"]."", GetMessage("FE_NO_SITEPATH")).". \n";
			}
			else
			{
				$SITES[$ar_sites["LID"]] = $SITE_PATH[$ar_sites["LID"]];
			}
		}
	}

	if (!isset($SITES) || !is_array($SITES) || count($SITES)<=0)
		$strErrorMessage .= GetMessage("FE_NO_LANG").". \n";

	if (strlen($strErrorMessage)<=0)
	{
		if ($ALLOW_UPLOAD!="Y" && $ALLOW_UPLOAD!="A" && $ALLOW_UPLOAD!="F")
		{
			$ALLOW_UPLOAD = "N";
		}

		$arFields = Array(
			"NAME" => $NAME,
			"DESCRIPTION" => $DESCRIPTION,
			"SORT" => $SORT,
			"ACTIVE" => ($ACTIVE=="Y"?"Y":"N"),
			"ALLOW_HTML" => ($ALLOW_HTML=="Y"?"Y":"N"),
			"ALLOW_ANCHOR" => ($ALLOW_ANCHOR=="Y"?"Y":"N"),
			"ALLOW_BIU" => ($ALLOW_BIU=="Y"?"Y":"N"),
			"ALLOW_IMG" => ($ALLOW_IMG=="Y"?"Y":"N"),
			"ALLOW_LIST" => ($ALLOW_LIST=="Y"?"Y":"N"),
			"ALLOW_QUOTE" => ($ALLOW_QUOTE=="Y"?"Y":"N"),
			"ALLOW_CODE" => ($ALLOW_CODE=="Y"?"Y":"N"),
			"ALLOW_FONT" => ($ALLOW_FONT=="Y"?"Y":"N"),
			"ALLOW_SMILES" => ($ALLOW_SMILES=="Y"?"Y":"N"),
			"ALLOW_UPLOAD" => $ALLOW_UPLOAD,
			"ALLOW_NL2BR" => ($ALLOW_NL2BR=="Y"?"Y":"N"),
			"MODERATION" => ($MODERATION=="Y"?"Y":"N"),
			"ALLOW_MOVE_TOPIC" => ($ALLOW_MOVE_TOPIC == "Y"? "Y":"N"),
			"ORDER_BY" => $ORDER_BY,
			"ORDER_DIRECTION" => $ORDER_DIRECTION,
			"PATH2FORUM_MESSAGE" => $PATH2FORUM_MESSAGE,
			"ALLOW_UPLOAD_EXT" => $ALLOW_UPLOAD_EXT,
			"FORUM_GROUP_ID" => $FORUM_GROUP_ID,
			"ASK_GUEST_EMAIL" => $ASK_GUEST_EMAIL,
			"USE_CAPTCHA" => $USE_CAPTCHA,
			"SITES" => $SITES
		);

		$arFields["GROUP_ID"] = $GROUP;

		if (CModule::IncludeModule("statistic"))
		{
			$arFields["EVENT1"] = $EVENT1;
			$arFields["EVENT2"] = $EVENT2;
			$arFields["EVENT3"] = $EVENT3;
		}

		if ($ID>0)
		{
			$ID1 = CForumNew::Update($ID, $arFields);
			if (IntVal($ID1)<=0)
				$strErrorMessage .= GetMessage("FE_ERROR_UPDATE").". \n";
		}
		else
		{
			$ID = CForumNew::Add($arFields);
			$ID = IntVal($ID);
			if ($ID<=0)
				$strErrorMessage .= GetMessage("FE_ERROR_ADD").". \n";
		}
	}
	

	if (strlen($strErrorMessage) > 0)
		$bVarsFromForm = true;
	else
	{
		BXClearCache(true, "/".LANG."/forum/forum/");
		BXClearCache(true, "/".LANG."/forum/forums/");
		if (strlen($apply) <= 0)
			LocalRedirect("forum_admin.php?lang=".LANG."&".GetFilterParams("filter_", false));
	}
}

$sDocTitle = ($ID>0) ? eregi_replace("#ID#", "$ID", GetMessage("FE_PAGE_TITLE1")) : GetMessage("FE_PAGE_TITLE2");
$APPLICATION->SetTitle($sDocTitle);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
$str_ACTIVE = "Y";
$str_SORT = "150";
$str_ALLOW_HTML = "N";
$str_ALLOW_ANCHOR = "Y";
$str_ALLOW_BIU = "Y";
$str_ALLOW_IMG = "Y";
$str_ALLOW_LIST = "Y";
$str_ALLOW_QUOTE = "Y";
$str_ALLOW_CODE = "Y";
$str_ALLOW_FONT = "Y";
$str_ALLOW_SMILES = "Y";
$str_ALLOW_UPLOAD = "N";
$str_ASK_GUEST_EMAIL = "N";
$str_USE_CAPTCHA = "N";
$str_ALLOW_NL2BR = "N";
$str_MODERATION = "N";
$str_ALLOW_MOVE_TOPIC = "N";
$str_ORDER_BY = "P";
$str_ORDER_DIRECTION = "DESC";
$str_PATH2FORUM_MESSAGE = "/".LANG."/forum/read.php?FID=#FORUM_ID#&TID=#TOPIC_ID#&MID=#MESSAGE_ID##message#MESSAGE_ID#";
$str_EVENT1 = "forum";
$str_EVENT2 = "message";
$str_EVENT3 = "";
$str_SITES = array();

if ($ID > 0)
{
	$db_res = CForumNew::GetList(array(), array("ID" => $ID));
	$db_res->ExtractFields("str_", True);
	$str_SITES = CForumNew::GetSites($ID);
}

if ($bVarsFromForm)
{
	$DB->InitTableVarsForEdit("b_forum", "", "str_");

	$str_SITES = array();
	$db_sites = CLang::GetList($lby="sort", $lorder="asc");
	while ($ar_sites = $db_sites->Fetch())
	{
		if (isset($SITE[$ar_sites["LID"]]) && $SITE[$ar_sites["LID"]]=="Y")
		{
			$str_SITES[$ar_sites["LID"]] = $SITE_PATH[$ar_sites["LID"]];
		}
	}
}
?>

<script language="JavaScript">
<!--
function on_site_checkbox_click(Site)
{
	siteCheck = document.forum_edit["SITE["+Site+"]"];
	sitePath = document.forum_edit["SITE_PATH["+Site+"]"];
	if (siteCheck.checked)
	{
		if (sitePath.value.length<=0)
		{
			sitePath.value = "/"+Site+"/forum/index.php?PAGE_NAME=read&FID=#FORUM_ID#&TID=#TOPIC_ID#&MID=#MESSAGE_ID##message#MESSAGE_ID#";
		}
	}
}
//-->
</script>

<?
$aMenu = array(
	array(
		"TEXT" => GetMessage("FEN_2FLIST"),
		"LINK" => "/bitrix/admin/forum_admin.php?lang=".LANG."&".GetFilterParams("filter_", false),
		"ICON" => "btn_list",
	)
);

if ($ID > 0 && $forumPermissions == "W")
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
		"TEXT" => GetMessage("FEN_NEW_FORUM"),
		"LINK" => "/bitrix/admin/forum_edit.php?lang=".LANG."&".GetFilterParams("filter_", false),
		"ICON" => "btn_new",
	);

	$aMenu[] = array(
		"TEXT" => GetMessage("FEN_DELETE_FORUM"), 
		"LINK" => "javascript:if(confirm('".GetMessage("FEN_DELETE_FORUM_CONFIRM")."')) window.location='/bitrix/admin/forum_admin.php?action=delete&ID[]=".$ID."&lang=".LANG."&".bitrix_sessid_get()."';",
		"ICON" => "btn_delete",
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?
CAdminMessage::ShowMessage($strErrorMessage);
?>

<form method="POST" action="<?=$APPLICATION->GetCurPageParam()?>?" name="forum_edit">
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?=LANG ?>">
<input type="hidden" name="ID" value="<?=$ID ?>">
<?=bitrix_sessid_post()?>

<?
$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("FEN_TAB_FORUM"), "ICON" => "forum", "TITLE" => GetMessage("FEN_TAB_FORUM_DESCR")),
		array("DIV" => "edit2", "TAB" => GetMessage("FEN_TAB_SETTINGS"), "ICON" => "forum", "TITLE" => GetMessage("FEN_TAB_SETTINGS_DESCR")),
		array("DIV" => "edit3", "TAB" => GetMessage("FEN_TAB_ACCESS"), "ICON"=>"forum", "TITLE" => GetMessage("FEN_TAB_ACCESS_DESCR"))
	);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>

<?
$tabControl->BeginNextTab();
?>

	<tr>
		<td width="40%"><label for="ACTIVE"><?=GetMessage("ACTIVE");?>:</label></td>
		<td width="60%">
			<input type="checkbox" name="ACTIVE" id="ACTIVE" value="Y" <?=($str_ACTIVE=="Y" ? "checked='checked'" : "")?> />
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("FE_FORUM_GROUP");?>:</td>
		<td>
			<select name="FORUM_GROUP_ID">
				<option value="">(<?=GetMessage("FE_NOT_SET");?>)</option>
				<?
				$g = CForumGroup::GetListEx(array("SORT"=>"ASC", "ID"=>"ASC"), array("LID"=>LANG));
				while ($g->ExtractFields("g_")):
					?><option value="<?=$g_ID?>"<?if (IntVal($str_FORUM_GROUP_ID)==IntVal($g_ID)) echo " selected"?>><?=$g_NAME ?></option><?
				endwhile;
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><span class="required">*</span><?=GetMessage("SORT");?>:</td>
		<td>
			<input type="text" name="SORT" size="10" maxlength="10" value="<?=$str_SORT?>">
		</td>
	</tr>
	<tr>
		<td><span class="required">*</span><?=GetMessage("NAME");?>:</td>
		<td>
			<input type="text" name="NAME" size="40" maxlength="255" value="<?=$str_NAME?>">
		</td>
	</tr>
	<tr>
		<td valign="top"><?=GetMessage("DESCR");?>:</td>
		<td valign="top">
			<textarea name="DESCRIPTION" rows="3" cols="30"><?=$str_DESCRIPTION; ?></textarea>
		</td>
	</tr>

	<tr class="heading">
		<td colspan="2"><?=GetMessage("FE_SITES_PATHS");?></td>
	</tr>
	<?
	$l = CLang::GetList($lby="sort", $lorder="asc");
	while ($l->ExtractFields("l_"))
	{
		?>
		<tr>
			<td>
				<label for="SITE_<?=$l_LID?>_">[<?=$l_LID?>] <?=$l_NAME?></label>
				<input type="checkbox" name="SITE[<?=$l_LID?>]" id="SITE_<?=$l_LID?>_" value="Y"<?if (array_key_exists($l_LID, $str_SITES))echo " checked"?> OnClick="on_site_checkbox_click('<?=$l_LID?>')">
			</td>
			<td>
				<input type="text" name="SITE_PATH[<?=$l_LID?>]" size="40" maxlength="255" value="<?if (array_key_exists($l_LID, $str_SITES)) echo htmlspecialchars($str_SITES[$l_LID])?>">
			</td>
		</tr>
		<?
	}
	?>
	<tr>
		<td colspan="2">
			<?=GetMessage("FE_SAMPLE_SITEPATH");?>: /forum/index.php?PAGE_NAME=read&FID=#FORUM_ID#&TID=#TOPIC_ID#&MID=#MESSAGE_ID##message#MESSAGE_ID#
		</td>
	</tr>

<?
$tabControl->EndTab();
?>

<?
$tabControl->BeginNextTab();
?>
	<tr>
		<td>
			<label for="MODERATION"><?=GetMessage("MODERATION");?>:</label>
		</td>
		<td>
			<input type="checkbox" name="MODERATION" id="MODERATION" id="MODERATION" value="Y" <?=($str_MODERATION=="Y" ? "checked='checked'" : "")?> />
		</td>
	</tr>
<?/*?>	<tr>
		<td><?=GetMessage("ALLOW_MOVE_TOPIC");?>:</td>
		<td>
			<input type="checkbox" name="ALLOW_MOVE_TOPIC" id="ALLOW_MOVE_TOPIC" value="Y" <?if ($str_ALLOW_MOVE_TOPIC=="Y" ? "checked='checked'" : "")?>>
		</td>
	</tr><?*/?>
	<tr>
		<td>
			<label for="ASK_GUEST_EMAIL"><?=GetMessage("ASK_GUEST_EMAIL");?>:</label>
		</td>
		<td>
			<input type="checkbox" name="ASK_GUEST_EMAIL" id="ASK_GUEST_EMAIL" value="Y" <?=
				($str_ASK_GUEST_EMAIL=="Y" ? "checked='checked'" : "")?> />
		</td>
	</tr>
	<tr>
		<td><label for="USE_CAPTCHA"><?=GetMessage("FE_USE_CAPTCHA");?>:</label></td>
		<td>
			<input type="checkbox" name="USE_CAPTCHA" id="USE_CAPTCHA" value="Y" <?=($str_USE_CAPTCHA=="Y" ? "checked='checked'" : "")?> />
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("ORDER_BY");?>:</td>
		<td>
			<?=SelectBoxFromArray("ORDER_BY", $aSortTypes, $str_ORDER_BY)?>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("ORDER_DIRECTION");?>:</td>
		<td>
			<?=SelectBoxFromArray("ORDER_DIRECTION", $aSortDirection, $str_ORDER_DIRECTION)?>
		</td>
	</tr>
	<?if (CModule::IncludeModule("statistic")):?>
		<tr class="heading">
			<td colspan="2"><?=GetMessage("FORUM_EVENT_PARAMS");?></td>
		</tr>
		<tr>
			<td>event1:</td>
			<td><input type="text" name="EVENT1" maxlength="255" size="30" value="<?=$str_EVENT1;?>"></td>
		</tr>
		<tr>
			<td>event2:</td>
			<td><input type="text" name="EVENT2" maxlength="255" size="30" value="<?=$str_EVENT2;?>"><br><?=GetMessage("FORUM_EVENT12")?></td>
		</tr>
		<tr>
			<td>event3:</td>
			<td><input type="text" name="EVENT3" maxlength="255" size="30" value="<?=$str_EVENT3;?>"><br><?=GetMessage("FORUM_EVENT3")?></td>
		</tr>
	<?endif;?>
	<tr>
		<td><?=GetMessage("ALLOW_UPLOAD");?>:</td>
		<td>
			<select name="ALLOW_UPLOAD">
				<option value="N"<?if ($str_ALLOW_UPLOAD=="N" || strlen($str_ALLOW_UPLOAD)<=0) echo " selected";?>><?=GetMessage("FE_NOT");?></option>
				<option value="Y"<?if ($str_ALLOW_UPLOAD=="Y") echo " selected";?>><?=GetMessage("FE_IMAGEY");?></option>
				<option value="F"<?if ($str_ALLOW_UPLOAD=="F") echo " selected";?>><?=GetMessage("FE_FILEY");?></option>
				<option value="A"<?if ($str_ALLOW_UPLOAD=="A") echo " selected";?>><?=GetMessage("FE_ANY_FILEY");?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("FE_ALLOWED_EXTS");?>:</td>
		<td>
			<input type="text" name="ALLOW_UPLOAD_EXT" size="40" maxlength="255" value="<?=$str_ALLOW_UPLOAD_EXT ?>">
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?=GetMessage("PARSER_SETTINGS")?></td>
	</tr>
	<tr>
		<td><label for="ALLOW_SMILES"><?=GetMessage("ALLOW_SMILES");?>:</label></td>
		<td>
			<input type="checkbox" name="ALLOW_SMILES" id="ALLOW_SMILES" value="Y" <?=($str_ALLOW_SMILES=="Y" ? "checked='checked'" : "")?>>
		</td>
	</tr>
	<tr>
		<td width="50%"><label for="ALLOW_HTML"><?=GetMessage("ALLOW_HTML");?>:</label></td>
		<td width="50%">
			<input type="checkbox" name="ALLOW_HTML" id="ALLOW_HTML" value="Y" <?=($str_ALLOW_HTML=="Y" ? "checked='checked'" : "")?> <?
				?>onclick="document.getElementById('forum_allow_tags').style.display = (this.checked ? 'none' : '');<?
					?>document.getElementById('forum_allow_nl2br').style.display = (this.checked ? '' : 'none');" />
		</td>
	</tr>
<tbody id="forum_allow_nl2br" style="<?=(($str_ALLOW_HTML=="Y") ? "" : "display:none;")?>" >
	<tr>
		<td><label for="ALLOW_NL2BR"><?=GetMessage("ALLOW_NL2BR");?>:</label></td>
		<td>
			<input type="checkbox" name="ALLOW_NL2BR" id="ALLOW_NL2BR" value="Y" <?=($str_ALLOW_NL2BR=="Y" ? "checked='checked'" : "")?>>
		</td>
	</tr>
</tbody>
<tbody id="forum_allow_tags" style="<?=(($str_ALLOW_HTML=="Y") ? "display:none;" : "")?>" >
	<tr>
		<td><label for="ALLOW_ANCHOR"><?=GetMessage("ALLOW_ANCHOR");?> <small>(&lt;a&nbsp;href=...&gt;)</small>:</label></td>
		<td>
			<input type="checkbox" name="ALLOW_ANCHOR" id="ALLOW_ANCHOR" value="Y" <?=($str_ALLOW_ANCHOR=="Y" ? "checked='checked'" : "")?>>
		</td>
	</tr>
	<tr>
		<td><label for="ALLOW_BIU"><?=GetMessage("ALLOW_BIU");?> <small>(&lt;b&gt;&nbsp;&lt;u&gt;&nbsp;&lt;i&gt;)</small>:</label></td>
		<td>
			<input type="checkbox" name="ALLOW_BIU" id="ALLOW_BIU" value="Y" <?=($str_ALLOW_BIU=="Y" ? "checked='checked'" : "")?>>
		</td>
	</tr>
	<tr>
		<td><label for="ALLOW_IMG"><?=GetMessage("ALLOW_IMG");?> <small>(&lt;img&nbsp;src=...&gt;)</small>:</label></td>
		<td>
			<input type="checkbox" name="ALLOW_IMG" id="ALLOW_IMG" value="Y" <?=($str_ALLOW_IMG=="Y" ? "checked='checked'" : "")?>>
		</td>
	</tr>
	<tr>
		<td><label for="ALLOW_LIST"><?=GetMessage("ALLOW_LIST");?> <small>(&lt;ul&gt;&lt;li&gt;)</small>:</label></td>
		<td>
			<input type="checkbox" name="ALLOW_LIST" id="ALLOW_LIST" value="Y" <?=($str_ALLOW_LIST=="Y" ? "checked='checked'" : "")?>>
		</td>
	</tr>
	<tr>
		<td><label for="ALLOW_QUOTE"><?=GetMessage("ALLOW_QUOTE");?> <small>(&lt;quote&gt;)</small>:</label></td>
		<td>
			<input type="checkbox" name="ALLOW_QUOTE" id="ALLOW_QUOTE" value="Y" <?=($str_ALLOW_QUOTE=="Y" ? "checked='checked'" : "")?>>
		</td>
	</tr>
	<tr>
		<td><label for="ALLOW_CODE"><?=GetMessage("ALLOW_CODE");?> <small>(&lt;code&gt;)</small>:</label></td>
		<td>
			<input type="checkbox" name="ALLOW_CODE" id="ALLOW_CODE" value="Y" <?=($str_ALLOW_CODE=="Y" ? "checked='checked'" : "")?>>
		</td>
	</tr>
	<tr>
		<td><label for="ALLOW_FONT"><?=GetMessage("ALLOW_FONT");?> <small>(&lt;font&nbsp;color=...&gt;)</small>:</label></td>
		<td>
			<input type="checkbox" name="ALLOW_FONT" id="ALLOW_FONT" value="Y" <?=($str_ALLOW_FONT=="Y" ? "checked='checked'" : "")?>>
		</td>
	</tr>
</tbody>

<?
$tabControl->EndTab();
?>

<?
$tabControl->BeginNextTab();
?>

	<?
	if ($ID>0)
		$arPerm = CForumNew::GetAccessPermissions($ID, "TWO");
	else
		$arPerm = array();

	$groups = CGroup::GetList($by = "sort", $order = "asc", Array("ADMIN"=>"N"));
	while ($r = $groups->ExtractFields("g_")):
		if ($bVarsFromForm)
			$strSelected = $GROUP[$g_ID];
		else
			$strSelected = $arPerm[$g_ID];

		if (!in_array(strtoupper($strSelected), $aForumPermissions["reference_id"]) && $ID>0 && !$bVarsFromForm)
			$strSelected = "A";
		?>
		<tr>
			<td width="40%"><?=$g_NAME?>&nbsp;[<a  href="/bitrix/admin/group_edit.php?ID=<?=$g_ID?>&lang=<?=LANGUAGE_ID?>"><?=$g_ID?></a>]:</td>
			<td width="60%">
				<select name="GROUP[<?=$g_ID?>]">
				<?
				for ($fi=0; $fi<count($aForumPermissions["reference_id"]); $fi++)
				{
					?><option value="<?=$aForumPermissions["reference_id"][$fi]?>"<?if ($strSelected == $aForumPermissions["reference_id"][$fi]) echo " selected"?>><?=htmlspecialchars($aForumPermissions["reference"][$fi])?></option><?
				}
				?>
				</select>
			</td>
		</tr>
	<?endwhile?>

<?
$tabControl->EndTab();
?>

<?
$editable = True;
if ($ID > 0)
{
	if (!CForumNew::CanUserUpdateForum($ID, $USER->GetUserGroupArray(), $USER->GetID()))
		$editable = False;
}
else
{
	if (!CForumNew::CanUserAddForum($USER->GetUserGroupArray(), $USER->GetID()))
		$editable = False;
}

$tabControl->Buttons(
		array(
				"disabled" => (!$editable || $forumPermissions < "W"),
				"back_url" => "/bitrix/admin/forum_admin.php?lang=".LANG."&".GetFilterParams("filter_", false)
			)
	);
?>

<?
$tabControl->End();
?>

</form>

<br>
<?=BeginNote();?>
<span class="required">*</span><font class="legendtext"> - <?=GetMessage("REQUIRED_FIELDS")?>
<?=EndNote(); ?>		

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>