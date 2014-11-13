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
<form method="POST" name="form1" action="<?echo $APPLICATION->GetCurPage()?>?" enctype="multipart/form-data">
<?if ($isIE):?>
<SCRIPT LANGUAGE="JavaScript">
<!--
function Personal_Click()
{
	v = document.all("tr_1").style.display;
	if (v!="none")
	{
		document.form1.show_personal.value="none";
		document.all("tr_1").style.display = "none";
		document.all("tr_2").style.display = "none";
	}
	else
	{
		document.form1.show_personal.value="inline";
		document.all("tr_1").style.display = "inline";
		document.all("tr_2").style.display = "inline";
	}
}

function Work_Click()
{
	v = document.all("tr_3").style.display;
	if (v!="none")
	{
		document.form1.show_work.value="none";
		document.all("tr_3").style.display = "none";
		document.all("tr_4").style.display = "none";
	}
	else
	{
		document.form1.show_work.value="inline";
		document.all("tr_3").style.display = "inline";
		document.all("tr_4").style.display = "inline";
	}
}

function Admin_Click()
{
	v = document.all("tr_6").style.display;
	if (v!="none")
	{
		document.form1.show_admin.value="none";
		document.all("tr_6").style.display = "none";
		document.all("tr_7").style.display = "none";
	}
	else
	{
		document.form1.show_admin.value="inline";
		document.all("tr_6").style.display = "inline";
		document.all("tr_7").style.display = "inline";
	}
}
//-->
</SCRIPT>
<?endif;?>
<?=bitrix_sessid_post()?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?echo LANG?>">
<input type="hidden" name="ID" value=<?echo $ID?>>
<input type="hidden" name="show_personal" value="<?=$show_personal?>">
<input type="hidden" name="show_work" value="<?=$show_personal?>">
<input type="hidden" name="show_admin" value="<?=$show_admin?>">
<table border="0" cellspacing="0" cellpadding="1" class="tableborder" width="98%" align="left">
	<tr valign="top">
		<td>
			<table border="0" cellspacing="0" cellpadding="3" class="tablebody" width="100%">
				<tr>
					<td class="tablebody">&nbsp;</td>
				</tr>
				<tr>
					<td  class="tablebody" ><table border="0" cellspacing="0" cellpadding="3" class="tablebody" width="100%">
				<?if($ID>0):?>
				<tr>
					<td valign="top" align="right" class="tablebody" nowrap><font class="tablebodytext"><?echo GetMessage('LAST_UPDATE')?></font></td>
					<td valign="top" align="left" class="tablebody" nowrap><font class="tablebodytext"><?echo $str_TIMESTAMP_X?></font></td>
				</tr>
				<tr>
					<td valign="top" align="right" class="tablebody" nowrap><font class="tablebodytext"><?echo GetMessage('LAST_LOGIN')?></font></td>
					<td valign="top" align="left" class="tablebody" nowrap><font class="tablebodytext"><?echo $str_LAST_LOGIN?></font></td>
				</tr>
				<?endif;?>
				<?if($ID!='1' && ($MAIN_RIGHT=="R" || $MAIN_RIGHT=="W")):?>
				<tr>
					<td valign="top" align="right" class="tablebody" nowrap><font class="tablebodytext"><?echo GetMessage('ACTIVE')?></font></td>
					<td valign="top" align="left" class="tablebody">
						<input type="checkbox" name="ACTIVE" value="Y"<?if($str_ACTIVE=="Y")echo " checked"?>>
					</td>
				</tr>
				<?endif;?>
				<tr>
					<td valign="top" align="right" class="tablebody" nowrap><font class="tablebodytext"><?echo GetMessage('NAME')?></font></td>
					<td valign="top" align="left" class="tablebody">
						<input type="text" name="NAME" size="30" maxlength="50" value="<? echo $str_NAME?>">
					</td>
				</tr>
				<tr>
					<td valign="top" align="right" class="tablebody" nowrap><font class="tablebodytext"><?echo GetMessage('LAST_NAME')?></font></td>
					<td valign="top" align="left" class="tablebody">
						<input type="text" name="LAST_NAME" size="30" maxlength="50" value="<? echo $str_LAST_NAME?>">
					</td>
				</tr>
				<tr>
					<td valign="top" align="right" class="tablebody" nowrap><font class="tablebodytext"><font class="starrequired">*</font><? echo GetMessage('EMAIL')?></font></td>
					<td valign="top" align="left" class="tablebody">
						<input type="text" name="EMAIL" size="30" maxlength="50" value="<? echo $str_EMAIL?>">
					</td>
				</tr>
				<tr>
					<td valign="top" align="right" class="tablebody" nowrap><font class="tablebodytext"><font class="starrequired">*</font><?echo GetMessage('LOGIN')?></font></td>
					<td valign="top" align="left" class="tablebody">
						<input type="text" name="LOGIN" size="30" maxlength="50" value="<? echo $str_LOGIN?>">
					</td>
				</tr>
				<tr>
					<td valign="top" align="right" class="tablebody" nowrap><font class="tablebodytext"><?echo GetMessage('NEW_PASSWORD')?></font></td>
					<td valign="top" align="left" class="tablebody">
						<input type="password" name="NEW_PASSWORD" size="30" maxlength="50" value="<? echo $NEW_PASSWORD ?>">
					</td>
				</tr>
				<tr>
					<td valign="top" align="right" class="tablebody" nowrap><font class="tablebodytext"><?echo GetMessage('NEW_PASSWORD_CONFIRM')?></font></td>
					<td valign="top" align="left" class="tablebody">
						<input type="password" name="NEW_PASSWORD_CONFIRM" size="30" maxlength="50" value="<? echo $NEW_PASSWORD_CONFIRM ?>">
					</td>
				</tr>
				<?if($MAIN_RIGHT=="W" || $MAIN_RIGHT=="R") : ?>
					<?if(defined("ADMIN_SECTION") && ADMIN_SECTION===true):?>
					<tr valign="top">
						<td align="right"><font class="tablefieldtext"><?echo GetMessage("MAIN_DEFAULT_SITE")?></font></td>
						<td><font class="tablefieldtext"><?=CSite::SelectBox("LID", $str_LID);?></font></td>
					</tr>
					<?endif?>
				<tr>
					<td valign="top" align="right" class="tablebody" nowrap><font class="tablebodytext"><? echo GetMessage('INFO_FOR_USER')?></font></td>
					<td valign="top" align="left" class="tablebody">
						<input type="checkbox" name="user_info_event" value="Y"<?if($user_info_event=="Y")echo " checked"?>>
					</td>
				</tr>
				<? endif; ?>
				<?if($MAIN_RIGHT=="R" || $MAIN_RIGHT=="W"):?>
				<tr>
					<td valign="top" align="right" class="tablebody" nowrap><font class="tablebodytext"><?echo GetMessage('GROUPS');?></font></td>
					<td valign="top" align="left" class="tablebody" nowrap>
						<font class="tablebodytext"><?
						$by="sort";
						$order="asc";
						$groups = CGroup::GetList($by, $order, Array("ANONYMOUS"=>"N"));
						while($r = $groups->ExtractFields("g_"))
						{
							if ($g_ID!=2) :
								?><input type="checkbox" name="GROUP_ID[]" value="<?echo $g_ID?>"<?if(in_array($g_ID, $str_GROUP_ID))echo " checked"?>><?
								echo $g_NAME." [<a class=\"tablebodylink\" href=\"/bitrix/admin/group_edit.php?ID=".intval($g_ID)."&lang=".LANGUAGE_ID."\">".intval($g_ID)."</a>]";
								echo "<br>";
							endif;
						}
						?>
					</font></td>
				</tr>
				<?endif;?>
				</table></td></tr>
				<tr>
					<td class="tablebody">&nbsp;</td>
				</tr>
				<tr>
					<td class="tablehead" align="center"><font class="tableheadtext"><a title="<?=GetMessage("USER_SHOW_HIDE")?>" href="javascript:void(0)" OnClick="javascript: Personal_Click()"><?=GetMessage("USER_PERSONAL_INFO")?></a></font></td>
				</tr>
				<tr id="tr_2" style="display: <?=$show_personal?>">
					<td class="tablebody" >&nbsp;</td>
				</tr>
				<tr id="tr_1" style="display: <?=$show_personal?>">
					<td align="center"><table cellspacing=0 cellpadding=2>
                    		<tr valign="center">
                    			<td align="right" class="tablebody" nowrap width="40%"><font class="tablebodytext">
                    				<?=GetMessage('USER_PROFESSION')?>
                    			</font></td>
                    			<td align="left" class="tablebody" width="60%">
                    				<input type="text" name="PERSONAL_PROFESSION" size="30" maxlength="255" value="<?=$str_PERSONAL_PROFESSION?>">
       						  	</td>
		       				  </tr>
        	            		<tr valign="center">
                    				<td align="right" class="tablebody" nowrap><font class="tablebodytext">
                   						<?=GetMessage('USER_WWW')?>
               						</font></td>
                    				<td align="left" class="tablebody">
                    					<input type="text" name="PERSONAL_WWW" size="30" maxlength="255" value="<?=$str_PERSONAL_WWW?>">
       						  		</td>
       				  			</tr>
                    		<tr valign="center">
                    				<td align="right" class="tablebody" nowrap><font class="tablebodytext">
                    						<?=GetMessage('USER_ICQ')?>
                    						</font></td>
                    				<td align="left" class="tablebody">
                    						<input type="text" name="PERSONAL_ICQ" size="30" maxlength="255" value="<?=$str_PERSONAL_ICQ?>">
       						  </td>
       				  </tr>
                    		<tr valign="center">
                    				<td align="right" class="tablebody" nowrap><font class="tablebodytext">
                    						<?=GetMessage('USER_GENDER')?>
                    						</font></td>
                    				<td align="left" class="tablebody"><?
									$arr = array(
										"reference"=>array(GetMessage("USER_MALE"),GetMessage("USER_FEMALE")), "reference_id"=>array("M","F"));
									echo SelectBoxFromArray("PERSONAL_GENDER", $arr, $str_PERSONAL_GENDER, GetMessage("USER_DONT_KNOW"));
									?>
       						  </td>
       				  </tr>
                    		<tr valign="center">
                    				<td align="right" width="0%"><font class="tablebodytext"><?echo GetMessage("USER_BIRTHDAY")?></font></td>
                    				<td align="left" width="0%" nowrap><font class="tableheadtext"><?echo CalendarDate("PERSONAL_BIRTHDATE", $str_PERSONAL_BIRTHDATE, "form1", "15")?></font></td>
       				  </tr>
                      <?if(false):?>
							<tr valign="center">
								<td align="right" width="0%"><font class="tablebodytext"><?echo GetMessage("USER_BIRTHDAY_DT")." (".CLang::GetDateFormat("SHORT").")"?></font></td>
								<td align="left" width="0%" nowrap><font class="tableheadtext"><?echo CalendarDate("PERSONAL_BIRTHDAY", $str_PERSONAL_BIRTHDAY, "form1", "15")?></font></td>
							</tr>
                      <?endif?>
                    		<tr valign="top">
                    				<td align="right" class="tablebody" nowrap><font class="tablebodytext">
                    						<?=GetMessage("USER_PHOTO")?>
                    						</font></td>
                    				<td  class="tablebody"><font class="tablebodytext"><?echo CFile::InputFile("PERSONAL_PHOTO", 20, $str_PERSONAL_PHOTO);?>
                                    						<?if (strlen($str_PERSONAL_PHOTO)>0):?>
                                    						<br>
                                    						<?echo CFile::ShowImage($str_PERSONAL_PHOTO, 150, 150, "border=0", "", true)?>
                                    						<?endif;?>
                    						</font> </td>
       				  </tr>
                    		<tr>
                    				<td class="tablebody"  align="center" colspan="2">
                            						<font class="tableheadtext">
                            						<?=GetMessage("USER_PHONES")?>
                            						</font><br>
                            						</td>
       				  </tr>
                    		<tr valign="center">
                    				<td align="right" class="tablebody" nowrap><font class="tablebodytext">
                    						<?=GetMessage('USER_PHONE')?>
                    						</font></td>
                    				<td align="left" class="tablebody">
                    						<input type="text" name="PERSONAL_PHONE" size="30" maxlength="255" value="<?=$str_PERSONAL_PHONE?>">
       						  </td>
       				  </tr>
                    		<tr valign="center">
                    				<td align="right" class="tablebody" nowrap><font class="tablebodytext">
                    						<?=GetMessage('USER_FAX')?>
                    						</font></td>
                    				<td align="left" class="tablebody">
                    						<input type="text" name="PERSONAL_FAX" size="30" maxlength="255" value="<?=$str_PERSONAL_FAX?>">
       						  </td>
       				  </tr>
                    		<tr valign="center">
                    				<td align="right" class="tablebody" nowrap><font class="tablebodytext">
                    						<?=GetMessage('USER_MOBILE')?>
                    						</font></td>
                    				<td align="left" class="tablebody">
                    						<input type="text" name="PERSONAL_MOBILE" size="30" maxlength="255" value="<?=$str_PERSONAL_MOBILE?>">
       						  </td>
       				  </tr>
                    		<tr valign="center">
                    				<td align="right" class="tablebody" nowrap><font class="tablebodytext">
                    						<?=GetMessage('USER_PAGER')?>
                    						</font></td>
                    				<td align="left" class="tablebody">
                    						<input type="text" name="PERSONAL_PAGER" size="30" maxlength="255" value="<?=$str_PERSONAL_PAGER?>">
       						  </td>
       				  </tr>
                    		<tr>
                    				<td class="tablebody"  align="center" colspan="2">
                            						<font class="tableheadtext">
                            						<?=GetMessage("USER_POST_ADDRESS")?>
                            						</font><br>
                            						</td>
       				  </tr>
                    		<tr valign="center">
                    				<td align="right" class="tablebody" nowrap><font class="tablebodytext">
                    						<?=GetMessage('USER_COUNTRY')?>
                    						</font></td>
                    				<td align="left" class="tablebody"><?
									echo SelectBoxFromArray("PERSONAL_COUNTRY", GetCountryArray(), $str_PERSONAL_COUNTRY, GetMessage("USER_DONT_KNOW"));
									?>
       						  </td>
       				  </tr>
                    		<tr valign="center">
                    				<td align="right" class="tablebody" nowrap><font class="tablebodytext">
                    						<?=GetMessage('USER_STATE')?>
                    						</font></td>
                    				<td align="left" class="tablebody">
                    						<input type="text" name="PERSONAL_STATE" size="30" maxlength="255" value="<?=$str_PERSONAL_STATE?>">
       						  </td>
       				  </tr>
                    		<tr valign="center">
                    				<td align="right" class="tablebody" nowrap><font class="tablebodytext">
                    						<?=GetMessage('USER_CITY')?>
                    						</font></td>
                    				<td align="left" class="tablebody">
                    						<input type="text" name="PERSONAL_CITY" size="30" maxlength="255" value="<?=$str_PERSONAL_CITY?>">
       						  </td>
       				  </tr>
                    		<tr valign="center">
                    				<td align="right" class="tablebody" nowrap><font class="tablebodytext">
                    						<?=GetMessage('USER_ZIP')?>
                    						</font></td>
                    				<td align="left" class="tablebody">
                    						<input type="text" name="PERSONAL_ZIP" size="30" maxlength="255" value="<?=$str_PERSONAL_ZIP?>">
       						  </td>
       				  </tr>
                    		<tr>
                    				<td valign="top" align="right" class="tablebody" width="0%" nowrap> <font class="tablebodytext">
                    						<?=GetMessage("USER_STREET")?>
                    						</font></td>
                    				<td valign="top" align="left" class="tablebody" width="0%" nowrap>
                    						<textarea name="PERSONAL_STREET" class="textarea" cols="40" rows="3"><?echo $str_PERSONAL_STREET?></textarea>
       						  </td>
       				  </tr>
                    		<tr valign="center">
                    				<td align="right" class="tablebody" nowrap><font class="tablebodytext">
                    						<?=GetMessage('USER_MAILBOX')?>
                    						</font></td>
                    				<td align="left" class="tablebody">
                    						<input type="text" name="PERSONAL_MAILBOX" size="30" maxlength="255" value="<?=$str_PERSONAL_MAILBOX?>">
       						  </td>
       				  </tr>
                    		<tr>
                    				<td class="tablebody"  colspan="2">&nbsp;</td>
       				  </tr>
                    		<tr>
                    				<td valign="top" align="right" class="tablebody" nowrap> <font class="tablebodytext">
                    						<?=GetMessage("USER_NOTES")?>
                    						</font></td>
                    				<td valign="top" align="left" class="tablebody" nowrap>
                    						<textarea name="PERSONAL_NOTES" class="textarea" cols="40" rows="5"><?echo $str_PERSONAL_NOTES?></textarea>
       						  </td>
       				  </tr>
           		  </table></td>
				</tr>
				<tr>
					<td class="tablebody" >&nbsp;</td>
				</tr>
				<tr>
					<td class="tablehead"  align="center"><font class="tableheadtext"><a title="<?=GetMessage("USER_SHOW_HIDE")?>" href="javascript:void(0)" OnClick="javascript: Work_Click()"><?=GetMessage("USER_WORK_INFO")?></a></font></td>
				</tr>
				<tr id="tr_4" style="display: <?=$show_work?>">
					<td class="tablebody" >&nbsp;</td>
				</tr>
				<tr id="tr_3" style="display: <?=$show_work?>">
					<td align="center" ><table cellspacing=0 cellpadding=2>
							<tr valign="center">
								<td align="right" class="tablebody" nowrap><font class="tablebodytext"><?=GetMessage('USER_COMPANY')?></font></td>
								<td align="left" class="tablebody">
									<input type="text" name="WORK_COMPANY" size="30" maxlength="255" value="<?=$str_WORK_COMPANY?>">
								</td>
							</tr>
							<tr valign="center">
								<td align="right" class="tablebody" nowrap><font class="tablebodytext"><?=GetMessage('USER_WWW')?></font></td>
								<td align="left" class="tablebody">
									<input type="text" name="WORK_WWW" size="30" maxlength="255" value="<?=$str_WORK_WWW?>">
								</td>
							</tr>
							<tr valign="center">
								<td align="right" class="tablebody" nowrap><font class="tablebodytext"><?=GetMessage('USER_DEPARTMENT')?></font></td>
								<td align="left" class="tablebody">
									<input type="text" name="WORK_DEPARTMENT" size="30" maxlength="255" value="<?=$str_WORK_DEPARTMENT?>">
								</td>
							</tr>
							<tr valign="center">
								<td align="right" class="tablebody" nowrap><font class="tablebodytext"><?=GetMessage('USER_POSITION')?></font></td>
								<td align="left" class="tablebody">
									<input type="text" name="WORK_POSITION" size="30" maxlength="255" value="<?=$str_WORK_POSITION?>">
								</td>
							</tr>
							<tr>
								<td valign="top" align="right" class="tablebody" width="0%" nowrap>
									<font class="tablebodytext"><?=GetMessage("USER_WORK_PROFILE")?></font></td>
								<td valign="top" align="left" class="tablebody" width="0%" nowrap>
									<textarea name="WORK_PROFILE" class="textarea" cols="40" rows="5"><?echo $str_WORK_PROFILE?></textarea></td>
							</tr>
							<tr valign="top">
								<td align="right" class="tablebody" nowrap><font class="tablebodytext"><?=GetMessage("USER_LOGO")?></font></td>
								<td  class="tablebody"><font class="tablebodytext"><?echo CFile::InputFile("WORK_LOGO", 20, $str_WORK_LOGO);?><?if (strlen($str_WORK_LOGO)>0):?><br><?echo CFile::ShowImage($str_WORK_LOGO, 150, 150, "border=0", "", true)?><?endif;?></font>
								</td>
							</tr>
							<tr>
								<td class="tablebody" colspan="2" align="center"><font class="tableheadtext"><?=GetMessage("USER_PHONES")?></font></td>
							</tr>
							<tr valign="center">
								<td align="right" class="tablebody" nowrap><font class="tablebodytext"><?=GetMessage('USER_PHONE')?></font></td>
								<td align="left" class="tablebody">
									<input type="text" name="WORK_PHONE" size="30" maxlength="255" value="<?=$str_WORK_PHONE?>">
								</td>
							</tr>
							<tr valign="center">
								<td align="right" class="tablebody" nowrap><font class="tablebodytext"><?=GetMessage('USER_FAX')?></font></td>
								<td align="left" class="tablebody">
									<input type="text" name="WORK_FAX" size="30" maxlength="255" value="<?=$str_WORK_FAX?>">
								</td>
							</tr>
							<tr valign="center">
								<td align="right" class="tablebody" nowrap><font class="tablebodytext"><?=GetMessage('USER_PAGER')?></font></td>
								<td align="left" class="tablebody">
									<input type="text" name="WORK_PAGER" size="30" maxlength="255" value="<?=$str_WORK_PAGER?>">
								</td>
							</tr>
							<tr>
								<td class="tablebody" colspan="2" align="center"><font class="tableheadtext"><?=GetMessage("USER_POST_ADDRESS")?></font><br></td>
							</tr>
							<tr valign="center">
								<td align="right" class="tablebody" nowrap><font class="tablebodytext"><?=GetMessage('USER_COUNTRY')?></font></td>
								<td align="left" class="tablebody"><?
									echo SelectBoxFromArray("WORK_COUNTRY", GetCountryArray(), $str_WORK_COUNTRY, GetMessage("USER_DONT_KNOW"));
									?></td>
							</tr>
							<tr valign="center">
								<td align="right" class="tablebody" nowrap><font class="tablebodytext"><?=GetMessage('USER_STATE')?></font></td>
								<td align="left" class="tablebody">
									<input type="text" name="WORK_STATE" size="30" maxlength="255" value="<?=$str_WORK_STATE?>">
								</td>
							</tr>
							<tr valign="center">
								<td align="right" class="tablebody" nowrap><font class="tablebodytext"><?=GetMessage('USER_CITY')?></font></td>
								<td align="left" class="tablebody">
									<input type="text" name="WORK_CITY" size="30" maxlength="255" value="<?=$str_WORK_CITY?>">
								</td>
							</tr>
							<tr valign="center">
								<td align="right" class="tablebody" nowrap><font class="tablebodytext"><?=GetMessage('USER_ZIP')?></font></td>
								<td align="left" class="tablebody">
									<input type="text" name="WORK_ZIP" size="30" maxlength="255" value="<?=$str_WORK_ZIP?>">
								</td>
							</tr>
							<tr>
								<td valign="top" align="right" class="tablebody" nowrap>
									<font class="tablebodytext"><?=GetMessage("USER_STREET")?></font></td>
								<td valign="top" align="left" class="tablebody" nowrap>
									<textarea name="WORK_STREET" class="textarea" cols="40" rows="3"><?echo $str_WORK_STREET?></textarea></td>
							</tr>
							<tr valign="center">
								<td align="right" class="tablebody" nowrap><font class="tablebodytext"><?=GetMessage('USER_MAILBOX')?></font></td>
								<td align="left" class="tablebody">
									<input type="text" name="WORK_MAILBOX" size="30" maxlength="255" value="<?=$str_WORK_MAILBOX?>">
								</td>
							</tr>
							<tr>
								<td class="tablebody" colspan="2">&nbsp;</td>
							</tr>
							<tr>
								<td valign="top" align="right" class="tablebody" nowrap>
									<font class="tablebodytext"><?=GetMessage("USER_NOTES")?></font></td>
								<td valign="top" align="left" class="tablebody" nowrap>
									<textarea name="WORK_NOTES" class="textarea" cols="40" rows="5"><?echo $str_WORK_NOTES?></textarea></td>
							</tr>
						</table>
				  </td>
				</tr>
				<?if($USER->IsAdmin()):?>
				<tr>
					<td class="tablebody">&nbsp;</td>
				</tr>
				<tr>
					<td class="tablehead" align="center"><font class="tableheadtext"><a title="<?=GetMessage("USER_SHOW_HIDE")?>" href="javascript:void(0)" OnClick="javascript: Admin_Click()"><?=GetMessage("USER_ADMIN_NOTES")?></a></font></td>
				</tr>
				<tr id="tr_6" style="display: <?=$show_admin?>">
					<td class="tablebody">&nbsp;</td>
				</tr>
				<tr id="tr_7" style="display: <?=$show_admin?>">
					<td valign="top" align="center" class="tablebody" nowrap>
						<textarea name="ADMIN_NOTES" class="textarea" cols="50" rows="10"><?echo $str_ADMIN_NOTES?></textarea></td>
				</tr>
				<?endif;?>

				<tr>
					<td class="tablebody">&nbsp;</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<br clear="all"><br>
<?
$uid = $USER->GetID();
$disabled = (($MAIN_RIGHT=="P" && $ID==$uid) || ($MAIN_RIGHT=="T" && $ID==$uid) || $MAIN_RIGHT=="W") ? "" : "disabled";

if ($MAIN_RIGHT!="P") :
?><input <?echo ($editable) ? "" : "disabled"?> type="submit" name="save" value="<?echo (($ID > 0)?GetMessage('MAIN_SAVE'):GetMessage('MAIN_ADD'))?>">&nbsp;<?
endif;
?><input <?echo ($editable) ? "" : "disabled"?> type="submit" name="apply" value="<?=GetMessage("MAIN_APPLY")?>">&nbsp;<input type="reset" value="<?echo GetMessage('MAIN_RESET');?>"></p>
</form>
