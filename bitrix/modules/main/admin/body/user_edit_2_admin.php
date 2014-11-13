<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2005 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

IncludeModuleLangFile(__FILE__);
?>
<a name="tb"></a>
<?echo ShowError($strError);?>
<SCRIPT LANGUAGE="JavaScript">
<!--
function SectionClick(id)
{
	var div = document.getElementById('user_div_'+id);
	document.cookie = "user_div_"+id+"="+(div.style.display != 'none'? 'N':'Y')+"; expires=Thu, 31 Dec 2020 23:59:59 GMT; path=<?echo BX_ROOT?>/admin/;";
	div.style.display = (div.style.display != 'none'? 'none':'block');
}
//-->
</SCRIPT>

<form method="POST" name="form1" action="<?echo $APPLICATION->GetCurPage()?>?" enctype="multipart/form-data">
<?=bitrix_sessid_post()?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?echo LANG?>">
<input type="hidden" name="ID" value=<?echo $ID?>>
<table border="0" cellpadding="3" width="100%" cellspacing="1" class="edittable">
	<?if($ID>0):?>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?echo GetMessage('LAST_UPDATE')?></font></td>
		<td><font class="tablebodytext"><?echo $str_TIMESTAMP_X?></font></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?echo GetMessage('LAST_LOGIN')?></font></td>
		<td><font class="tablebodytext"><?echo $str_LAST_LOGIN?></font></td>
	</tr>
	<?endif;?>
	<?if($ID!='1' && ($MAIN_RIGHT=="R" || $MAIN_RIGHT=="W")):?>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?echo GetMessage('ACTIVE')?></font></td>
		<td><input type="checkbox" name="ACTIVE" value="Y"<?if($str_ACTIVE=="Y")echo " checked"?>></td>
	</tr>
	<?endif;?>
	<tr valign="top">
		<td width="40%" align="right"><font class="tablefieldtext"><?echo GetMessage('NAME')?></font></td>
		<td width="60%"><input type="text" class="typeinput" name="NAME" size="30" maxlength="50" value="<? echo $str_NAME?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?echo GetMessage('LAST_NAME')?></font></td>
		<td><input type="text" class="typeinput" name="LAST_NAME" size="30" maxlength="50" value="<? echo $str_LAST_NAME?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><font class="star required">*</font><? echo GetMessage('EMAIL')?></font></td>
		<td><input type="text" class="typeinput" name="EMAIL" size="30" maxlength="50" value="<? echo $str_EMAIL?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><font class="star required">*</font><?echo GetMessage('LOGIN')?></font></td>
		<td><input type="text" class="typeinput" name="LOGIN" size="30" maxlength="50" value="<? echo $str_LOGIN?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?echo GetMessage('NEW_PASSWORD')?></font></td>
		<td><input class="typeinput" type="password" name="NEW_PASSWORD" size="30" maxlength="50" value="<? echo $NEW_PASSWORD ?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?echo GetMessage('NEW_PASSWORD_CONFIRM')?></font></td>
		<td><input class="typeinput" type="password" name="NEW_PASSWORD_CONFIRM" size="30" maxlength="50" value="<? echo $NEW_PASSWORD_CONFIRM ?>"></td>
	</tr>
	<?if($MAIN_RIGHT=="W" || $MAIN_RIGHT=="R"):?>
		<?
		$rExtAuth = CUser::GetExternalAuthList();
		if($arExtAuth = $rExtAuth->GetNext()):
		?>
		<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?echo GetMessage("MAIN_USERED_AUTH_TYPE")?></font></td>
		<td><font class="tablebodytext">
			<select class="typeselect" name="EXTERNAL_AUTH_ID">
				<option value=""><?echo GetMessage("MAIN_USERED_AUTH_INT")?></option>
				<?do{?>
				<option value="<?=$arExtAuth['ID']?>"<?if($str_EXTERNAL_AUTH_ID==$arExtAuth['ID'])echo ' selected';?>><?=$arExtAuth['NAME']?></option>
				<?}while($arExtAuth = $rExtAuth->GetNext());?>
			</select>
		</font></td>
		</tr>
		<?endif?>
	<?if(defined("ADMIN_SECTION") && ADMIN_SECTION===true):?>
		<tr valign="top">
			<td align="right"><font class="tablefieldtext"><?echo GetMessage("MAIN_DEFAULT_SITE")?></font></td>
			<td><font class="tablefieldtext"><?=CSite::SelectBox("LID", $str_LID);?></font></td>
		</tr>
	<?endif?>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><? echo GetMessage('INFO_FOR_USER')?></font></td>
		<td><input type="checkbox" name="user_info_event" value="Y"<?if($user_info_event=="Y")echo " checked"?>>
		</td>
	</tr>
	<? endif; ?>
	<?if($MAIN_RIGHT=="R" || $MAIN_RIGHT=="W"):?>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?echo GetMessage('GROUPS');?></font></td>
		<td><font class="tablebodytext"><?
			$by="c_sort";
			$order="asc";
			$groups = CGroup::GetList($by="sort", $order="asc", Array("ANONYMOUS"=>"N"));
			while($r = $groups->ExtractFields("g_"))
			{
				if ($g_ID!=2) :
					?><input type="checkbox" name="GROUP_ID[]" value="<?echo $g_ID?>"<?if(in_array($g_ID, $str_GROUP_ID))echo " checked"?>><?
					echo $g_NAME." [<a class=\"tablebodylink\" href=\"/bitrix/admin/group_edit.php?ID=".intval($g_ID)."&lang=".LANGUAGE_ID."\">".intval($g_ID)."</a>]";
					echo "<br>";
				endif;
			}
			?></font></td>
	</tr>
	<?endif;?>
	<tr valign="top">
		<td class="tablehead" colspan="2"><font class="tableheadtext"><a title="<?=GetMessage("USER_SHOW_HIDE")?>" href="javascript:void(0)" OnClick="javascript:SectionClick('personal')"><b><?=GetMessage("USER_PERSONAL_INFO")?></b></a></font></td>
	</tr>
	<tr>
		<td colspan="2">
<div id="user_div_personal" style="display:<?echo ($_COOKIE["user_div_personal"]=="Y"? "block":"none")?>">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr valign="top">
		<td align="right" width="40%"><font class="tablefieldtext"><?=GetMessage('USER_PROFESSION')?></font></td>
		<td width="60%"><input type="text" class="typeinput" name="PERSONAL_PROFESSION" size="30" maxlength="255" value="<?=$str_PERSONAL_PROFESSION?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_WWW')?></font></td>
		<td><input type="text" class="typeinput" name="PERSONAL_WWW" size="30" maxlength="255" value="<?=$str_PERSONAL_WWW?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_ICQ')?></font></td>
		<td><input type="text" class="typeinput" name="PERSONAL_ICQ" size="30" maxlength="255" value="<?=$str_PERSONAL_ICQ?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_GENDER')?></font></td>
		<td><?
			$arr = array(
				"reference"=>array(GetMessage("USER_MALE"),GetMessage("USER_FEMALE")), "reference_id"=>array("M","F"));
			echo SelectBoxFromArray("PERSONAL_GENDER", $arr, $str_PERSONAL_GENDER, GetMessage("USER_DONT_KNOW"));
			?></td>
   <?if(false):?>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?echo GetMessage("USER_BIRTHDAY").":"?></font></td>
		<td><font class="tableheadtext"><?echo CalendarDate("PERSONAL_BIRTHDATE", $str_PERSONAL_BIRTHDATE, "form1", "15")?></font></td>
	</tr>
   <?endif?>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?echo GetMessage("USER_BIRTHDAY_DT")." (".CLang::GetDateFormat("SHORT")."):"?></font></td>
		<td><font class="tableheadtext"><?echo CalendarDate("PERSONAL_BIRTHDAY", $str_PERSONAL_BIRTHDAY, "form1", "15")?></font></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage("USER_PHOTO")?></font></td>
		<td><font class="tablebodytext"><?
		echo CFile::InputFile("PERSONAL_PHOTO", 20, $str_PERSONAL_PHOTO);
		if (strlen($str_PERSONAL_PHOTO)>0):
			?><br><?
			echo CFile::ShowImage($str_PERSONAL_PHOTO, 150, 150, "border=0", "", true);
		endif;
		?></font></td>
	<tr valign="top">
		<td class="tablehead" colspan="2" align="center"><font class="tableheadtext"><?=GetMessage("USER_PHONES")?></font></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_PHONE')?></font></td>
		<td><input type="text" class="typeinput" name="PERSONAL_PHONE" size="30" maxlength="255" value="<?=$str_PERSONAL_PHONE?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_FAX')?></font></td>
		<td><input type="text" class="typeinput" name="PERSONAL_FAX" size="30" maxlength="255" value="<?=$str_PERSONAL_FAX?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_MOBILE')?></font></td>
		<td><input type="text" class="typeinput" name="PERSONAL_MOBILE" size="30" maxlength="255" value="<?=$str_PERSONAL_MOBILE?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_PAGER')?></font></td>
		<td><input type="text" class="typeinput" name="PERSONAL_PAGER" size="30" maxlength="255" value="<?=$str_PERSONAL_PAGER?>"></td>
	</tr>
	<tr valign="top">
		<td class="tablehead" colspan="2" align="center"><font class="tableheadtext"><?=GetMessage("USER_POST_ADDRESS")?></font></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_COUNTRY')?></font></td>
		<td><?echo SelectBoxFromArray("PERSONAL_COUNTRY", GetCountryArray(), $str_PERSONAL_COUNTRY, GetMessage("USER_DONT_KNOW"));?></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_STATE')?></font></td>
		<td><input type="text" class="typeinput" name="PERSONAL_STATE" size="30" maxlength="255" value="<?=$str_PERSONAL_STATE?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_CITY')?></font></td>
		<td><input type="text" class="typeinput" name="PERSONAL_CITY" size="30" maxlength="255" value="<?=$str_PERSONAL_CITY?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_ZIP')?></font></td>
		<td><input type="text" class="typeinput" name="PERSONAL_ZIP" size="30" maxlength="255" value="<?=$str_PERSONAL_ZIP?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage("USER_STREET")?></font></td>
		<td><textarea name="PERSONAL_STREET" class="typearea" cols="40" rows="3"><?echo $str_PERSONAL_STREET?></textarea></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_MAILBOX')?></font></td>
		<td><input type="text" class="typeinput" name="PERSONAL_MAILBOX" size="30" maxlength="255" value="<?=$str_PERSONAL_MAILBOX?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage("USER_NOTES")?></font></td>
		<td><textarea name="PERSONAL_NOTES" class="typearea" cols="40" rows="5"><?echo $str_PERSONAL_NOTES?></textarea></td>
	</tr>
</table>
</div>
		</td>
	</tr>
	<tr valign="top">
		<td class="tablehead" colspan="2"><font class="tableheadtext"><a title="<?=GetMessage("USER_SHOW_HIDE")?>" href="javascript:void(0)" OnClick="javascript: SectionClick('work')"><b><?=GetMessage("USER_WORK_INFO")?></b></a></font></td>
	</tr>
	<tr>
		<td colspan="2">
<div id="user_div_work" style="display:<?echo ($_COOKIE["user_div_work"]=="Y"? "block":"none")?>">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr valign="top">
		<td align="right" width="40%"><font class="tablefieldtext"><?=GetMessage('USER_COMPANY')?></font></td>
		<td width="60%"><input type="text" class="typeinput" name="WORK_COMPANY" size="30" maxlength="255" value="<?=$str_WORK_COMPANY?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_WWW')?></font></td>
		<td><input type="text" class="typeinput" name="WORK_WWW" size="30" maxlength="255" value="<?=$str_WORK_WWW?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_DEPARTMENT')?></font></td>
		<td><input type="text" class="typeinput" name="WORK_DEPARTMENT" size="30" maxlength="255" value="<?=$str_WORK_DEPARTMENT?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_POSITION')?></font></td>
		<td><input type="text" class="typeinput" name="WORK_POSITION" size="30" maxlength="255" value="<?=$str_WORK_POSITION?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage("USER_WORK_PROFILE")?></font></td>
		<td><textarea name="WORK_PROFILE" class="typearea" cols="40" rows="5"><?echo $str_WORK_PROFILE?></textarea></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage("USER_LOGO")?></font></td>
		<td><font class="tablebodytext"><?
			echo CFile::InputFile("WORK_LOGO", 20, $str_WORK_LOGO);
			if (strlen($str_WORK_LOGO)>0):
				?><br><?
				echo CFile::ShowImage($str_WORK_LOGO, 150, 150, "border=0", "", true);
			endif;
			?></font></td>
	</tr>
	<tr valign="top">
		<td class="tablehead" colspan="2" align="center"><font class="tableheadtext"><?=GetMessage("USER_PHONES")?></font></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_PHONE')?></font></td>
		<td><input type="text" class="typeinput" name="WORK_PHONE" size="30" maxlength="255" value="<?=$str_WORK_PHONE?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_FAX')?></font></td>
		<td><input type="text" class="typeinput" name="WORK_FAX" size="30" maxlength="255" value="<?=$str_WORK_FAX?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_PAGER')?></font></td>
		<td><input type="text" class="typeinput" name="WORK_PAGER" size="30" maxlength="255" value="<?=$str_WORK_PAGER?>"></td>
	</tr>
	<tr valign="top">
		<td class="tablehead" colspan="2" align="center"><font class="tableheadtext"><?=GetMessage("USER_POST_ADDRESS")?></font></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_COUNTRY')?></font></td>
		<td><?echo SelectBoxFromArray("WORK_COUNTRY", GetCountryArray(), $str_WORK_COUNTRY, GetMessage("USER_DONT_KNOW"));?></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_STATE')?></font></td>
		<td><input type="text" class="typeinput" name="WORK_STATE" size="30" maxlength="255" value="<?=$str_WORK_STATE?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_CITY')?></font></td>
		<td><input type="text" class="typeinput" name="WORK_CITY" size="30" maxlength="255" value="<?=$str_WORK_CITY?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_ZIP')?></font></td>
		<td><input type="text" class="typeinput" name="WORK_ZIP" size="30" maxlength="255" value="<?=$str_WORK_ZIP?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage("USER_STREET")?></font></td>
		<td><textarea name="WORK_STREET" class="typearea" cols="40" rows="3"><?echo $str_WORK_STREET?></textarea></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_MAILBOX')?></font></td>
		<td><input type="text" class="typeinput" name="WORK_MAILBOX" size="30" maxlength="255" value="<?=$str_WORK_MAILBOX?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage("USER_NOTES")?></font></td>
		<td><textarea name="WORK_NOTES" class="typearea" cols="40" rows="5"><?echo $str_WORK_NOTES?></textarea></td>
	</tr>
</table>
</div>
		</td>
	</tr>
	<?
	$db_opt_res = $DB->Query("SELECT ID FROM b_module");
	while ($opt_res = $db_opt_res->Fetch())
	{
		$mdir = $opt_res["ID"];
		if (file_exists($DOCUMENT_ROOT.BX_ROOT."/modules/".$mdir) && is_dir($DOCUMENT_ROOT.BX_ROOT."/modules/".$mdir))
		{
			$ofile = $DOCUMENT_ROOT.BX_ROOT."/modules/".$mdir."/options_user_settings.php";
			if (file_exists($ofile))
			{
				$MODULE_RIGHT = $APPLICATION->GetGroupRight($mdir);
				if ($MODULE_RIGHT>="R")
				{
					include($ofile);
				}
			}
		}
	}
	?>

	<?if($USER->IsAdmin()):?>

	<tr valign="top">
		<td class="tablehead" colspan="2"><font class="tableheadtext"><a title="<?=GetMessage("USER_SHOW_HIDE")?>" href="javascript:void(0)" OnClick="javascript: SectionClick('admin')"><b><?=GetMessage("USER_ADMIN_NOTES")?></b></a></font></td>
	</tr>
	<tr>
		<td colspan="2"><div id="user_div_admin" style="display:<?echo ($_COOKIE["user_div_admin"]=="Y"? "block":"none")?>">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr valign="top">
				<td align="center" colspan="2"><textarea name="ADMIN_NOTES" class="typearea" cols="70" rows="10"><?echo $str_ADMIN_NOTES?></textarea></td>
			</tr>
		</table></div></td>
	</tr>

	<?endif;?>
</table>
<p align="left"><?
if ($MAIN_RIGHT!="P") :
?><input <?echo ($editable) ? "" : "disabled"?> class="button" type="submit" name="save" value="<?echo (($ID > 0)?GetMessage('MAIN_SAVE'):GetMessage('MAIN_ADD'))?>">&nbsp;<?
endif;
?><input <?echo ($editable) ? "" : "disabled"?> class="button" type="submit" name="apply" value="<?=GetMessage("MAIN_APPLY")?>">&nbsp;<input class="button" type="reset" value="<?echo GetMessage('MAIN_RESET');?>"></p>
</form>
<?echo BeginNote();?>
<font class="star required">*</font><font class="legendtext"> - <?echo GetMessage("REQUIRED_FIELDS")?></font>
<?echo EndNote();?>
