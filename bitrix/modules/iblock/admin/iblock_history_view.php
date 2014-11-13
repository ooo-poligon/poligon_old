<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002 Bitrix                  #
# http://www.bitrix.ru                       #
# mailto:admin@bitrix.ru                     #
##############################################
*/


require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/iblock.php");

IncludeModuleLangFile(__FILE__);
if (!CModule::IncludeModule("workflow")) die();

$arIBTYPE = CIBlockType::GetByIDLang($type, LANG);
if($arIBTYPE==false) die();

$iblock = CIBlock::GetByID($IBLOCK_ID);
if($arIBlock=$iblock->Fetch())
{
	$BlockPerm = CIBlock::GetPermission($IBLOCK_ID);
	if ($BlockPerm<"U")
	{
		$APPLICATION->SetTitle(str_replace("#ID#","$ID",GetMessage("IBLOCK_HISTORY_VIEW_PAGE_TITLE")));
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
		echo ShowError(GetMessage("IBLOCK_BAD_IBLOCK"));
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
		die();
	}
}

$ID = intval($ID);

$z = CIblockElement::GetByID($ID);
if (!$z->ExtractFields("str_")) :
	$APPLICATION->SetTitle(str_replace("#ID#","$ID",GetMessage("IBLOCK_HISTORY_VIEW_PAGE_TITLE")));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	?>
	<font class="text"><a href="/bitrix/admin/iblock_admin.php?type=<?echo htmlspecialchars($type)?>&amp;lang=<?=LANG?>"><?echo htmlspecialcharsex($arIBTYPE["NAME"])?></a> - <a href="iblock_element_admin.php?type=<?echo htmlspecialchars($type)?>&amp;lang=<?echo LANG?>&amp;IBLOCK_ID=<?echo $IBLOCK_ID?>"><?echo htmlspecialchars($arIBlock["NAME"])?></a><?
	if(strlen($filter_section)>0)
	{
		$nav = CIBlockSection::GetNavChain($IBLOCK_ID, $str_IBLOCK_SECTION_ID);
		while($nav->ExtractFields("nav_")):
			?> - <a href="iblock_element_admin.php?lang=<?echo LANG?>&amp;type=<?echo $type?>&amp;IBLOCK_ID=<?echo $IBLOCK_ID?>&amp;find_section_section=<?echo $find_section_section;?>"><?echo $nav_NAME?></a><?
		endwhile;
	}
	?> - <a href="iblock_history_list.php?ID=<?=$RID?>&amp;type=<?echo htmlspecialchars($type)?>&amp;lang=<?echo LANG?>&amp;IBLOCK_ID=<?echo $IBLOCK_ID?>&amp;find_section_section=<?echo $find_section_section?>"><?echo str_replace("#ID#","$RID",GetMessage("IBLOCK_HISTORY_PAGE_TITLE"))?></a></font>
	<?
	echo ShowError(GetMessage("IBLOCK_INCORRECT_HISTORY_ID"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
else :

$APPLICATION->SetTitle(str_replace("#ID#","$ID",GetMessage("IBLOCK_HISTORY_VIEW_PAGE_TITLE")));

/***************************************************************************
                               HTML форма
****************************************************************************/

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

?>
<br>
<font class="text"><a href="/bitrix/admin/iblock_admin.php?type=<?echo htmlspecialchars($type)?>&amp;lang=<?=LANG?>"><?echo htmlspecialcharsex($arIBTYPE["NAME"])?></a> - <a href="iblock_element_admin.php?type=<?echo htmlspecialchars($type)?>&amp;lang=<?echo LANG?>&amp;IBLOCK_ID=<?echo $IBLOCK_ID?>&amp;find_section_section=<?echo $find_section_section?>"><?echo htmlspecialchars($arIBlock["NAME"])?></a><?
if(strlen($filter_section)>0)
{
	$nav = CIBlockSection::GetNavChain($IBLOCK_ID, $str_IBLOCK_SECTION_ID);
	while($nav->ExtractFields("nav_")):
		?> - <a href="iblock_element_admin.php?lang=<?echo LANG?>&amp;type=<?echo $type?>&amp;IBLOCK_ID=<?echo $IBLOCK_ID?>&amp;find_section_section=<?echo $find_section_section;?>"><?echo $nav_NAME?></a><?
	endwhile;
}
?> - <a href="iblock_history_list.php?ID=<?=$RID?>&amp;type=<?echo htmlspecialchars($type)?>&amp;lang=<?echo LANG?>&amp;IBLOCK_ID=<?echo $IBLOCK_ID?>&amp;find_section_section=<?echo $find_section_section?>"><?echo str_replace("#ID#","$RID",GetMessage("IBLOCK_HISTORY_PAGE_TITLE"))?></a></font>
<br>&nbsp;
<?echo ShowError($strError)?>
<br>
<form>
<table border="0" cellspacing="0" cellpadding="1" class="tableborder" width="100%">
	<tr valign="top">
		<td width="100%">
			<table border="0" cellspacing="0" cellpadding="3" class="tablebody" width="100%">
				<tr>
					<td valign="top" align="right" class="tablebody" width="0%" nowrap colspan="2">
						<img src="/bitrix/images/1.gif" width="1" height="8"></td>
				</tr>
				<tr>
					<td valign="top" align="right" class="tablebody" nowrap><font class="tablebodytext"><?=GetMessage("IBLOCK_WF_STATUS")?></font></td>
					<td valign="top" align="left" class="tablebody" nowrap><font class="tablebodytext"><?
						echo SelectBox("WF_STATUS_ID", CWorkflowStatus::GetDropDownList("Y"), "", $str_WF_STATUS_ID);
					?></font></td>
				</tr>
				<tr>
					<td valign="top" align="right" class="tablebody" nowrap><font class="tablebodytext"><?echo GetMessage("IBLOCK_LAST_UPDATE")?></font></td>
					<td valign="top" align="left" class="tablebody" nowrap><font class="tablebodytext"><?echo $str_TIMESTAMP_X?><?
					if (intval($str_MODIFIED_BY)>0):
					?>&nbsp;&nbsp;&nbsp;[<a class="tablebodylink" href="user_edit.php?lang=<?=LANG?>&ID=<?=$str_MODIFIED_BY?>"><?echo $str_MODIFIED_BY?></a>]&nbsp;<?=$str_USER_NAME?><?
					endif;
					?></font></td>
				</tr>
				<tr>
					<td valign="top" width="50%" align="right" class="tablebody" nowrap><font class="tablebodytext"><?echo GetMessage("IBLOCK_ACTIVE")?></font></td>
					<td valign="top" width="50%" align="left" class="tablebody">
						<input type="checkbox" name="ACTIVE" value="Y"<?if($str_ACTIVE=="Y")echo " checked"?>>
					</td>
				</tr>
				<tr>
					<td valign="top" width="50%" align="right" class="tablebody" nowrap><font class="tablebodytext"><?echo GetMessage("IBLOCK_ACTIVE_PERIOD")?>(<?echo CLang::GetDateFormat("SHORT");?>)</font></td>
					<td valign="top" width="50%" align="left" class="tablebody" nowrap><font class="tablebodytext">
						<?echo CalendarPeriod("ACTIVE_FROM", $str_ACTIVE_FROM, "ACTIVE_TO", $str_ACTIVE_TO, "ff")?>
						</font>
					</td>
				</tr>
				<?if($arIBTYPE["SECTIONS"]=="Y"):?>
				<tr>
					<td valign="top" align="right" class="tablebody" nowrap><font class="tablebodytext"><?echo GetMessage("IBLOCK_SECTION")?></font></td>
					<td valign="top" align="left" class="tablebody">
					<?$l = CIBlockSection::GetTreeList(Array("IBLOCK_ID"=>$IBLOCK_ID));?>
					<select name="IBLOCK_SECTION_ID">
						<option value="0"><?echo GetMessage("IBLOCK_CONTENT")?></option>
					<?
						while($l->ExtractFields("l_")):
							?><option value="<?echo $l_ID?>"<?if($str_IBLOCK_SECTION_ID==$l_ID)echo " selected"?>><?echo str_repeat(".", $l_DEPTH_LEVEL)?><?echo $l_NAME?></option><?
						endwhile;
					?>
					</select>
					</td>
				</tr>
				<?endif?>
				<tr>
					<td valign="top" align="right" class="tablebody" nowrap><font class="tablebodytext"><font class="starrequired">*</font><?echo GetMessage("IBLOCK_NAME")?></font></td>
					<td valign="top" align="left" class="tablebody">
						<input type="text" name="NAME" size="50" maxlength="255" value="<?echo $str_NAME?>">
					</td>
				</tr>
				<tr>
					<td valign="top" align="right" class="tablebody" nowrap><font class="tablebodytext"><?echo GetMessage("IBLOCK_SORT")?></font></td>
					<td valign="top" align="left" class="tablebody">
						<input type="text" name="SORT" size="7" maxlength="10" value="<?echo $str_SORT?>">
					</td>
				</tr>

				<tr>
					<td valign="top" align="center" colspan="2" class="tablebody" nowrap><font class="tablebodytext">&nbsp;</font></td>
				</tr>
				<?
				$props = CIBlockElement::GetProperty($IBLOCK_ID, $ID, $by="sort", $order="asc", Array("ACTIVE"=>"Y"));
				while($props->ExtractFields("p_")):
					if($bVarsFromForm)
						$p_VALUE = htmlspecialchars($PROP[$p_ID]);
					else
					{
						if($ID<=0)
							$p_VALUE = $p_DEFAULT_VALUE;
					}
				?>
				<tr>
					<td valign="top" align="right" class="tablebody" nowrap><font class="tablebodytext"><?echo $p_NAME?>:</font></td>
					<td valign="top" align="left" class="tablebody" nowrap>
					<font class="tablebodytext">
						<?if($p_ROW_COUNT>1):?>
							<textarea cols="<?echo $p_COL_COUNT?>" rows="<?echo $p_ROW_COUNT?>" name="PROP[<?echo $p_ID?>]"><?echo $p_VALUE?></textarea>
						<?else:?>
							<input type="text" size="<?echo $p_COL_COUNT?>" name="PROP[<?echo $p_ID?>]" value="<?echo $p_VALUE?>">
						<?endif?>
					</font>
					</td>
				</tr>
				<?endwhile;?>
				<tr>
					<td valign="top" align="center" colspan="2" class="tablebody" nowrap><font class="tablebodytext">&nbsp;<br>&nbsp;</font></td>
				</tr>
				<tr>
					<td valign="top" align="center" colspan="2" class="tablebody" nowrap><font class="tablebodytext"><b><?echo GetMessage("IBLOCK_ELEMENT_PREVIEW")?></b></font></td>
				</tr>
				<tr>
					<td valign="top" align="center" colspan="2" class="tablebody" nowrap><font class="tablebodytext">&nbsp;</font></td>
				</tr>
				<tr>
					<td valign="top" align="right" class="tablebody" nowrap><font class="tablebodytext"><?echo GetMessage("IBLOCK_PICTURE")?></font></td>
					<td valign="top" align="left" class="tablebody"><font class="tablebodytext">
						<?echo CFile::InputFile("PREVIEW_PICTURE", 20, $str_PREVIEW_PICTURE);?><br>
						<?echo CFile::ShowImage($str_PREVIEW_PICTURE, 200, 200, "border=0", "", true)?>
						</font>
					</td>
				</tr>

				<?if(ereg('(MSIE|Internet Explorer) ([0-9]).([0-9])+', $_SERVER['HTTP_USER_AGENT'], $version) && IntVal($version[2])>=5 && COption::GetOptionString("iblock", "use_htmledit", "Y")=="Y" && CModule::IncludeModule("fileman")):?>
				<tr>
					<td valign="top" align="left" class="tablebody" colspan="2">
						<?CFileMan::AddHTMLEditorFrame("PREVIEW_TEXT", $str_PREVIEW_TEXT, "PREVIEW_TEXT_TYPE", $str_PREVIEW_TEXT_TYPE, 230);?>
					</td>
				</tr>
				<?else:?>
				<tr>
					<td valign="top" align="right" class="tablebody" nowrap><font class="tablebodytext"><?echo GetMessage("IBLOCK_DESC_TYPE")?></font></td>
					<td valign="top" align="left" class="tablebody">
						<font class="tablebodytext">
							<input type="radio" name="PREVIEW_TEXT_TYPE" value="text"<?if($str_PREVIEW_TEXT_TYPE!="html")echo " checked"?>> <?echo GetMessage("IBLOCK_DESC_TYPE_TEXT")?> / <input type="radio" name="PREVIEW_TEXT_TYPE" value="html"<?if($str_PREVIEW_TEXT_TYPE=="html")echo " checked"?>> <?echo GetMessage("IBLOCK_DESC_TYPE_HTML")?>
						</font>
					</td>
				</tr>
				<tr>
					<td valign="top" align="center" class="tablebody" colspan="2" width="100%">
						<textarea cols="60" rows="5" name="PREVIEW_TEXT" wrap="virtual"><?echo $str_PREVIEW_TEXT?></textarea>
					</td>
				</tr>
				<?endif?>
				<tr>
					<td valign="top" align="center" colspan="2" class="tablebody" nowrap><font class="tablebodytext">&nbsp;<br>&nbsp;</font></td>
				</tr>
				<tr>
					<td valign="top" align="center" colspan="2" class="tablebody" nowrap><font class="tablebodytext"><b><?echo GetMessage("IBLOCK_ELEMENT_DETAIL")?></b></font></td>
				</tr>
				<tr>
					<td valign="top" align="center" colspan="2" class="tablebody" nowrap><font class="tablebodytext">&nbsp;</font></td>
				</tr>
				<tr>
					<td valign="top" align="right" class="tablebody" nowrap><font class="tablebodytext"><?echo GetMessage("IBLOCK_PICTURE")?></font></td>
					<td valign="top" align="left" class="tablebody"><font class="tablebodytext">
						<?echo CFile::InputFile("DETAIL_PICTURE", 20, $str_DETAIL_PICTURE);?><br>
						<?echo CFile::ShowImage($str_DETAIL_PICTURE, 200, 200, "border=0", "", true)?>
						</font>
					</td>
				</tr>
				<?if(ereg('(MSIE|Internet Explorer) ([0-9]).([0-9])+', $_SERVER['HTTP_USER_AGENT'], $version) && IntVal($version[2])>=5 && COption::GetOptionString("iblock", "use_htmledit", "Y")=="Y" && CModule::IncludeModule("fileman")):?>
				<tr>
					<td valign="top" align="left" class="tablebody" colspan="2">
						<?CFileMan::AddHTMLEditorFrame("DETAIL_TEXT", $str_DETAIL_TEXT, "DETAIL_TEXT_TYPE", $str_DETAIL_TEXT_TYPE, 300);?>
					</td>
				</tr>
				<?else:?>
				<tr>
					<td valign="top" align="right" class="tablebody" nowrap><font class="tablebodytext"><?echo GetMessage("IBLOCK_DESC_TYPE")?></font></td>
					<td valign="top" align="left" class="tablebody">
						<font class="tablebodytext">
							<input type="radio" name="DETAIL_TEXT_TYPE" value="text"<?if($str_DETAIL_TEXT_TYPE!="html")echo " checked"?>> <?echo GetMessage("IBLOCK_DESC_TYPE_TEXT")?> / <input type="radio" name="DETAIL_TEXT_TYPE" value="html"<?if($str_DETAIL_TEXT_TYPE=="html")echo " checked"?>> <?echo GetMessage("IBLOCK_DESC_TYPE_HTML")?>
						</font>
					</td>
				</tr>
				<tr>
					<td valign="top" align="center" class="tablebody" colspan="2" width="100%">
						<textarea cols="60" rows="8" name="DETAIL_TEXT" wrap="virtual"><?echo $str_DETAIL_TEXT?></textarea>
					</td>
				</tr>
				<?endif?>
				<tr>
					<td class="tablebody" colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td class="tablebody" align="center" colspan="2"><font class="tableheadtext"><b><?=GetMessage("IBLOCK_COMMENTS")?></b></font></td>
				</tr>
				<tr>
					<td class="tablebody" colspan="2"><img src="/bitrix/images/1.gif" width="1" height="2"></td>
				</tr>
				<tr>
					<td valign="top" align="center" class="tablebody" nowrap colspan="2">
						<textarea name="WF_COMMENTS" class="textarea" cols="60" rows="10"><?echo $str_WF_COMMENTS?></textarea></td>
				</tr>
				<tr>
					<td valign="top" align="right" class="tablebody" width="0%" nowrap colspan="2">
						<img src="/bitrix/images/1.gif" width="1" height="8"></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<br>
</form>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
endif;
?>
