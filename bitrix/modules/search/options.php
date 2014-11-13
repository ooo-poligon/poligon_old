<?
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
IncludeModuleLangFile(__FILE__);

$arAllOptions = Array(
	Array("max_file_size", GetMessage("SEARCH_OPTIONS_REINDEX_MAX_SIZE")." ", Array("text", "")),
	Array("include_mask", GetMessage("SEARCH_OPTIONS_MASK_INC")." ", Array("text", 60)),
	Array("exclude_mask", GetMessage("SEARCH_OPTIONS_MASK_EXC")." ", Array("text", 60)),
	Array("use_stemming", GetMessage("SEARCH_OPTIONS_USE_STEMMING")." ", Array("checkbox", "N")),
	Array("letters", GetMessage("SEARCH_OPTIONS_LETTERS")." ", Array("text", 60)),
	Array("max_result_size", GetMessage("SEARCH_OPTIONS_MAX_RESULT_SIZE")." ", Array("text", 60)),
	Array("page_tag_property", GetMessage("SEARCH_OPTIONS_PAGE_PROPERTY")." ", Array("text", "tags")),
	Array("use_tf_cache", GetMessage("SEARCH_OPTIONS_USE_TF_CACHE")." ", Array("checkbox", "N")),
);
$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "ICON" => "search_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

if($REQUEST_METHOD=="POST" && strlen($Update.$Apply.$RestoreDefaults)>0 && check_bitrix_sessid())
{
	if(strlen($RestoreDefaults)>0)
	{
		COption::RemoveOption("search");
	}
	else
	{
		$old_use_tf_cache = COption::GetOptionString("search", "use_tf_cache");
		$old_max_result_size = COption::GetOptionInt("search", "max_result_size");
		foreach($arAllOptions as $arOption)
		{
			$name=$arOption[0];
			$val=$_REQUEST[$name];
			if($arOption[2][0]=="checkbox" && $val!="Y")
				$val="N";
			COption::SetOptionString("search", $name, $val, $arOption[1]);
		}
		if(
			$old_use_tf_cache != COption::GetOptionString("search", "use_tf_cache")
			|| $old_max_result_size != COption::GetOptionInt("search", "max_result_size")
		)
		{
			global $DB;
			$DB->Query("TRUNCATE TABLE b_search_content_freq");
		}
	}
	if(strlen($Update)>0 && strlen($_REQUEST["back_url_settings"])>0)
		LocalRedirect($_REQUEST["back_url_settings"]);
	else
		LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($mid)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($_REQUEST["back_url_settings"])."&".$tabControl->ActiveTabParam());
}

$aMenu = array(
	array(
		"TEXT"=>GetMessage("SEARCH_OPTIONS_REINDEX"),
		"LINK"=>"search_reindex.php?lang=".LANGUAGE_ID,
		"TITLE"=>GetMessage("SEARCH_OPTIONS_REINDEX_TITLE"),
	),
	array(
		"TEXT"=>GetMessage("SEARCH_OPTIONS_SITEMAP"),
		"LINK"=>"search_sitemap.php?lang=".LANGUAGE_ID,
		"TITLE"=>GetMessage("SEARCH_OPTIONS_SITEMAP_TITLE"),
	)
);
$context = new CAdminContextMenu($aMenu);
$context->Show();

$tabControl->Begin();
?><form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($mid)?>&amp;lang=<?=LANGUAGE_ID?>"><?
$tabControl->BeginNextTab();
	foreach($arAllOptions as $arOption):
		$val = COption::GetOptionString("search", $arOption[0]);
		$type = $arOption[2];
	?>
		<tr>
			<td valign="top" width="50%"><?if($type[0]=="checkbox")
							echo "<label for=\"".htmlspecialchars($arOption[0])."\">".$arOption[1]."</label>";
						else
							echo $arOption[1];?></td>
			<td valign="top" width="50%">
					<?if($type[0]=="checkbox"):?>
						<input type="checkbox" name="<?echo htmlspecialchars($arOption[0])?>" id="<?echo htmlspecialchars($arOption[0])?>" value="Y"<?if($val=="Y")echo" checked";?>>
					<?elseif($type[0]=="text"):?>
						<input type="text" size="<?echo $type[1]?>" maxlength="255" value="<?echo htmlspecialchars($val)?>" name="<?echo htmlspecialchars($arOption[0])?>">
					<?elseif($type[0]=="textarea"):?>
						<textarea rows="<?echo $type[1]?>" cols="<?echo $type[2]?>" name="<?echo htmlspecialchars($arOption[0])?>"><?echo htmlspecialchars($val)?></textarea>
					<?endif?>
			</td>
		</tr>
	<?endforeach?>
<?$tabControl->Buttons();?>
	<input type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>" title="<?=GetMessage("MAIN_OPT_SAVE_TITLE")?>">
	<input type="submit" name="Apply" value="<?=GetMessage("MAIN_OPT_APPLY")?>" title="<?=GetMessage("MAIN_OPT_APPLY_TITLE")?>">
	<?if(strlen($_REQUEST["back_url_settings"])>0):?>
		<input type="button" name="Cancel" value="<?=GetMessage("MAIN_OPT_CANCEL")?>" title="<?=GetMessage("MAIN_OPT_CANCEL_TITLE")?>" onclick="window.location='<?echo htmlspecialchars(CUtil::addslashes($_REQUEST["back_url_settings"]))?>'">
		<input type="hidden" name="back_url_settings" value="<?=htmlspecialchars($_REQUEST["back_url_settings"])?>">
	<?endif?>
	<input type="submit" name="RestoreDefaults" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>')" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
	<?=bitrix_sessid_post();?>
<?$tabControl->End();?>
</form>
