<?
IncludeModuleLangFile($DOCUMENT_ROOT."/bitrix/modules/main/interface/auth/auth_form.php");
ShowMessage($arAuthResult);
?>	
<form method="POST" action="<?echo $sDocPath.(($s=DeleteParam(array("register"))) == ""? "?register=yes":"?$s&register=yes")?>" name="bform">
<input type="hidden" name="AUTH_FORM" value="Y">
<input type="hidden" name="TYPE" value="REGISTRATION">
<table border="0" cellspacing="0" cellpadding="1" class="tableborder">
	<tr> 
		<td> 
			<table border="0" cellspacing="0" cellpadding="4" class="tablebody">
				<tr> 
					<td width="100%" valign="middle" colspan="2" class="tablebody"> 
						<table width="100%%" border="0" cellpadding="3" cellspacing="0">
							<tr> 
								<td class="tablehead"><font class="tabletitletext"><b><?=GetMessage("AUTH_REGISTER")?></b></font></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr valign="middle"> 
					<td align="right" nowrap width="1%" class="tablebody"><font class="starrequired">*</font><font class="tableheadtext"><?=GetMessage("AUTH_NAME")?>:</font></td>
					<td align="left" width="99%" class="tablebody"><input type="text" name="USER_NAME" size="30" maxlength="50" value="<?echo htmlspecialchars($USER_NAME)?>"></td>
				</tr>
				<tr valign="middle"> 
					<td align="right" nowrap class="tablebody"><font class="starrequired">*</font><font class="tableheadtext"><?=GetMessage("AUTH_LAST_NAME")?>:</font></td>
					<td align="left" class="tablebody"><input type="text" name="USER_LAST_NAME" maxlength="50" size="30" value="<?echo htmlspecialchars($USER_LAST_NAME)?>"></td>
				</tr>
				<tr valign="middle"> 
					<td align="right" nowrap class="tablebody"><font class="starrequired">*</font><font class="tableheadtext"><?=GetMessage("AUTH_LOGIN_MIN")?>:</font></td>
					<td align="left" class="tablebody"><input type="text" name="USER_LOGIN" size="30" maxlength="50" value="<?echo htmlspecialchars($USER_LOGIN)?>"></td>
				</tr>
				<tr valign="middle"> 
					<td align="right" nowrap class="tablebody"><font class="starrequired">*</font><font class="tableheadtext"><?=GetMessage("AUTH_PASSWORD_MIN")?>:</font></td>
					<td align="left" class="tablebody"><input type="password" name="USER_PASSWORD" size="30" maxlength="50" value="<?echo htmlspecialchars($USER_PASSWORD)?>"></td>
				</tr>
				<tr valign="middle"> 
					<td align="right" nowrap class="tablebody"><font class="starrequired">*</font><font  class="tableheadtext"><?=GetMessage("AUTH_CONFIRM")?>:</font></td>
					<td align="left" class="tablebody"><input type="password" name="USER_CONFIRM_PASSWORD" size="30" maxlength="50" value="<?echo htmlspecialchars($USER_CONFIRM_PASSWORD)?>"></td>
				</tr>
				<tr valign="middle"> 
					<td align="right" nowrap class="tablebody"><font class="starrequired">*</font><font  class="tableheadtext">E-Mail:</font></td>
					<td align="left" class="tablebody"><input type="text" name="USER_EMAIL" size="30" maxlength="255" value="<?echo htmlspecialchars(strlen($sf_EMAIL)>0? $sf_EMAIL:$USER_EMAIL)?>"></td>
				</tr>
				<tr valign="middle"> 
					<td align="right" nowrap class="tablebody"><font class="starrequired">*</font><font  class="tableheadtext"><?=GetMessage("AUTH_LANG")?>:</font></td>
					<td align="left" class="tablebody">
					<?=CLang::SelectBox("USER_LID", $USER_LID);?>
					</td>
				</tr>
				<tr> 
					<td nowrap align="right" class="tablebody"><font  class="tablebodytext">&nbsp;</font></td>
					<td nowrap class="tablebody" align="right"><input type="Submit" name="Register" value="<?=GetMessage("AUTH_REGISTER")?>"></td>
				</tr>
			</table>
		</td>
	</tr>
</table>

<p><font class="starrequired">*</font><font class="text"><?=GetMessage("AUTH_REQ")?></font></p>

<p><font class="text">
<a href="<? echo $sDocPath.($s == ""? "?login=yes":"?$s&login=yes")?>"><b><?=GetMessage("AUTH_AUTH")?></b></a>
</font></p> 

</form>

<script>
<!--
document.bform.USER_NAME.focus();
// -->
</script>