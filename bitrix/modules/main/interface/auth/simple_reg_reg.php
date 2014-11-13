<?
IncludeModuleLangFile($DOCUMENT_ROOT."/bitrix/modules/main/interface/auth/auth_form.php");
if(COption::GetOptionString("main", "new_user_registration", "N")=="Y"):?>
<p><font class="text"><?=GetMessage("AUTH_FOR_CONTROL")?></font></p>
<ul type="square" class="text">
<li><b><?=GetMessage("AUTH_GENERATE")?></b><br>
<font style="font-size:7px;">&nbsp;<br></font>
<table  border="0" cellspacing="5" cellpadding="0" class="tablebody">
<form name="rform" method="post" target="_top" action="<?echo $sDocPath."?simple_registration=yes".($s == ""? "":"&amp;$s");?>">
				<tr valign="middle"> 
					<td align="right"><font class="tableheadtext">E-Mail:</font></td>
					<td align="left"><input class="typeinput" type="text" name="USER_EMAIL" value="<?echo htmlspecialchars($USER_EMAIL<>""? $USER_EMAIL:$sf_EMAIL)?>" size="25" maxlength="255"></td>
				</tr>
				<tr valign="middle"> 
					<td align="right" nowrap class="tablebody"><font class="required">*</font><font  class="tableheadtext"><?=GetMessage("AUTH_LANG")?>:</font></td>
					<td align="left" class="tablebody">
					<?=CLang::SelectBox("USER_LID", $USER_LID);?>
					</td>
				</tr>
				<tr> 
					<td align="center" colspan="2"><font class="tablebodytext"><input type="submit" name="reg_button" value="<?=GetMessage("AUTH_GENERATE_BTN")?>" class="typesubmit"></font></td>
				</tr>
	<input type="hidden" name="AUTH_FORM" value="Y">
	<input type="hidden" name="TYPE" value="AUTO">
</form>
</table>
<font style="font-size:7px;">&nbsp;<br></font>
<?=GetMessage("AUTH_SEND_LOGIN")?><br>&nbsp;
	</li>
<li><b><a href="<? echo $sDocPath."?register=yes".($s<>""? "&amp;$s":"");?>"><?=GetMessage("AUTH_FILL_REGISTER_FORM")?></b></a><br>
<font style="font-size:7px;">&nbsp;<br></font>
	<?=GetMessage("AUTH_YOU_CAN_FILL_REGISTER")?></li>
</ul>
<?endif?>
