<?
IncludeModuleLangFile($DOCUMENT_ROOT."/bitrix/modules/main/interface/auth/auth_form.php");
$store_password = COption::GetOptionString("main", "store_password", "N");
?>
<p><font class="text"><?=GetMessage("AUTH_IF_REGISTERED")?></font></p>
<ul type="square" class="text">
<li><b><?=GetMessage("AUTH_AUTH")?></b><br>
<font style="font-size:7px;">&nbsp;<br></font>
			<table  border="0" cellspacing="5" cellpadding="0" class="tablebody">
<form name="bform" method="post" target="_top" action="<?echo $sDocPath."?simple_registration=yes".($s == ""? "":"&amp;$s");?>">
<input type="hidden" name="AUTH_FORM" value="Y">
<input type="hidden" name="TYPE" value="AUTH">
				<tr valign="middle"> 
					<td align="right"><font class="tableheadtext"><?=GetMessage("AUTH_LOGIN")?>:</font></td>
					<td align="left"><input type="text" name="USER_LOGIN" maxlength="50" size="25" value="<?echo htmlspecialchars($last_login)?>" class="typeinput"></td>
				</tr>
				<tr> 
					<td align="right"> <font class="tableheadtext"><?=GetMessage("AUTH_PASSWORD")?>:</font> </td>
					<td align="left"><input type="password" name="USER_PASSWORD" maxlength="50" class="typeinput" size="25"></td>
				</tr>
				<?if ($store_password=="Y") :?>
				<tr> 
					<td align="center" class="tablebody" colspan="2"><font class="tableheadtext"><input type="checkbox" name="USER_REMEMBER" value="Y" id="USER_REMEMBER_F">&nbsp;<label for="USER_REMEMBER_F"><?=GetMessage("AUTH_REMEMBER_ME")?></label></font></td>
				</tr>
				<?endif;?>
				<tr> 
					<td class="tablebody" align="center" colspan="2"><font class="tablebodytext"><input type="submit" name="Login" value="<?=GetMessage("AUTH_AUTHORIZE")?>" class="typesubmit"></font></td>
				</tr>
</form>
			</table><br>
</li>
<li>
<a href="<?echo $sDocPath."?forgot_password=yes".($s<>""? "&amp;$s":"");?>"><b><?=GetMessage("AUTH_FORGOT_PASSWORD")?></b></a><br>
<font style="font-size:7px;">&nbsp;<br></font>
<?=GetMessage("AUTH_FILL_AUTH_1")?><a href="<?echo $sDocPath."?change_password=yes".($s<>""? "&amp;$s":"");?>"><?=GetMessage("AUTH_FILL_AUTH_2")?></a>.
</ul>