<?
global $DOCUMENT_ROOT;
IncludeModuleLangFile($DOCUMENT_ROOT."/bitrix/modules/main/interface/auth/auth_form.php");
$store_password = COption::GetOptionString("main", "store_password", "Y");
ShowMessage($arAuthResult);
?>	
<form name="form_auth" method="post" target="_top" action="<?echo $sDocPath.(($s=DeleteParam(array("logout", "login"))) == ""? "?login=yes":"?$s&login=yes");?>">
<?foreach($GLOBALS["HTTP_POST_VARS"] as $vname=>$vvalue):
if($vname=="USER_LOGIN")continue;
?>
<input type="hidden" name="<?echo htmlspecialchars($vname)?>" value="<?echo htmlspecialchars($vvalue)?>">
<?endforeach?>
<input type="hidden" name="AUTH_FORM" value="Y">
<input type="hidden" name="TYPE" value="AUTH">
<p><font class="text"><?=GetMessage("AUTH_PLEASE_AUTH")?></font></p>
<table border="0" cellspacing="0" cellpadding="1" class="tableborder">
	<tr valign="top" align="center">
		<td>
			<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tablebody">
				<tr valign="middle"> 
					<td class="tablebody" colspan="2">
						<table width="100%%" border="0" cellpadding="3" cellspacing="0">
							<tr> 
								<td class="tablehead" align="center"><font class="tabletitletext"><b><?=GetMessage("AUTH_AUTH")?></b></font></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr valign="middle"> 
					<td align="right" class="tablebody"><font class="tableheadtext"><?=GetMessage("AUTH_LOGIN")?>:</font></td>
					<td align="left"  class="tablebody"><input type="text" name="USER_LOGIN" maxlength="50" size="20" value="<?echo htmlspecialchars($last_login)?>" class="typeinput"></td>
				</tr>
				<tr> 
					<td align="right" class="tablebody"><font class="tableheadtext"><?=GetMessage("AUTH_PASSWORD")?>:</font></td>
					<td align="left" class="tablebody"><input type="password" name="USER_PASSWORD" maxlength="50" class="typeinput" size="20"></td>
				</tr>
				<?if ($store_password=="Y") :?>
				<tr> 
					<td align="center" class="tablebody" colspan="2"><font class="tableheadtext"><input type="checkbox" name="USER_REMEMBER" value="Y" id="USER_REMEMBER_F">&nbsp;<label for="USER_REMEMBER_F"><?=GetMessage("AUTH_REMEMBER_ME")?></label></font></td>
				</tr>
				<?endif;?>
				<tr> 
					<td class="tablebody" align="center" colspan="2"><font class="tablebodytext"><input type="submit" name="Login" value="<?=GetMessage("AUTH_AUTHORIZE")?>" class="typesubmit"></font></td>
				</tr>
			</table>
		</td>
	</tr>
</table>

<p>
<font class="text">
<a href="<?echo $sDocPath."?forgot_password=yes".($s<>""? "&amp;$s":"");?>"><b><?=GetMessage("AUTH_FORGOT_PASSWORD_2")?></b></a>
<br><?=GetMessage("AUTH_GO")?> <a href="<?echo $sDocPath."?forgot_password=yes".($s<>""? "&amp;$s":"");?>"><?=GetMessage("AUTH_GO_AUTH_FORM")?></a>
<br><?=GetMessage("AUTH_MESS_1")?> <a href="<?echo $sDocPath."?change_password=yes".($s<>""? "&amp;$s":"");?>"><?=GetMessage("AUTH_CHANGE_FORM")?></a>
</font>
</p>
</form>
<script>
<!--
<? if (strlen($last_login)>0) : ?>
try{document.form_auth.USER_PASSWORD.focus();}catch(e){}
<? else : ?>
try{document.form_auth.USER_LOGIN.focus();}catch(e){}
<? endif; ?>
// -->
</script>
<?if(COption::GetOptionString("main", "new_user_registration", "Y")=="Y"):?>
<br>
<p><font class="text"><?=GetMessage("AUTH_FIRST_ONE")?><?=GetMessage("AUTH_REG_FORM")?>:</font></p>
<form method="POST" action="<?echo $sDocPath.(($s=DeleteParam(array("authorize_registration"))) == ""? "?authorize_registration=yes":"?$s&authorize_registration=yes")?>" name="bform">
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
					<td align="left" width="99%" class="tablebody"><input type="text" name="USER_NAME" size="30" maxlength="50" value="<?echo ($TYPE=="REGISTRATION") ? htmlspecialchars($USER_NAME) : ""?>"></td>
				</tr>
				<tr valign="middle"> 
					<td align="right" nowrap class="tablebody"><font class="starrequired">*</font><font class="tableheadtext"><?=GetMessage("AUTH_LAST_NAME")?>:</font></td>
					<td align="left" class="tablebody"><input type="text" name="USER_LAST_NAME" maxlength="50" size="30" value="<?echo ($TYPE=="REGISTRATION") ? htmlspecialchars($USER_LAST_NAME) : ""?>"></td>
				</tr>
				<tr valign="middle"> 
					<td align="right" nowrap class="tablebody"><font class="starrequired">*</font><font class="tableheadtext"><?=GetMessage("AUTH_LOGIN_MIN")?>:</font></td>
					<td align="left" class="tablebody"><input type="text" name="USER_LOGIN" size="30" maxlength="50" value="<?echo ($TYPE=="REGISTRATION") ? htmlspecialchars($USER_LOGIN) : ""?>"></td>
				</tr>
				<tr valign="middle"> 
					<td align="right" nowrap class="tablebody"><font class="starrequired">*</font><font class="tableheadtext"><?=GetMessage("AUTH_PASSWORD_MIN")?>:</font></td>
					<td align="left" class="tablebody"><input type="password" name="USER_PASSWORD" size="30" maxlength="50" value="<?echo ($TYPE=="REGISTRATION") ? htmlspecialchars($USER_PASSWORD) : ""?>"></td>
				</tr>
				<tr valign="middle"> 
					<td align="right" nowrap class="tablebody"><font class="starrequired">*</font><font  class="tableheadtext"><?=GetMessage("AUTH_CONFIRM")?>:</font></td>
					<td align="left" class="tablebody"><input type="password" name="USER_CONFIRM_PASSWORD" size="30" maxlength="50" value="<?echo ($TYPE=="REGISTRATION") ? htmlspecialchars($USER_CONFIRM_PASSWORD) : ""?>"></td>
				</tr>
				<tr valign="middle"> 
					<td align="right" nowrap class="tablebody"><font class="starrequired">*</font><font  class="tableheadtext">E-Mail:</font></td>
					<td align="left" class="tablebody"><input type="text" name="USER_EMAIL" size="30" maxlength="255" value="<?echo htmlspecialchars((strlen($sf_EMAIL)>0 && $TYPE=="REGISTRATION")? $sf_EMAIL:$USER_EMAIL)?>"></td>
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
</form>
<?endif;?>