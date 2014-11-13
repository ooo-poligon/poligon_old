<?
$module_id = "fileman";
$dicsRelPath = '/bitrix/modules/fileman/dictionaries';
$gzDicsRelPath = BX_PERSONAL_ROOT.'/tmp/dics';
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/admin/task_description.php");

if (!$USER->CanDoOperation('fileman_view_all_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

function isValidLang($lang)
{
	$rsLang = CLanguage::GetList($by="sort", $order="desc");
	$is_valid_lang = false;
	while ($arLang = $rsLang->Fetch())
	{
		if ($lang==$arLang["LID"])
		{
			$is_valid_lang = true;
			break;
		}
	}
	return $is_valid_lang;
}

if ($REQUEST_METHOD=="GET" && $USER->CanDoOperation('fileman_edit_all_settings') && strlen($RestoreDefaults)>0 && check_bitrix_sessid())
{
	COption::RemoveOption("fileman");
	$z = CGroup::GetList($v1="id",$v2="asc", array("ACTIVE" => "Y", "ADMIN" => "N"));
	while($zr = $z->Fetch())
		$APPLICATION->DelGroupRight($module_id, array($zr["ID"]));
}


global $MESS;
IncludeModuleLangFile(__FILE__);

//Default file extensions;
$script_files_default = "php,phtml,php3,pl,php4,cgi,shtml,ico";

if($REQUEST_METHOD=="POST" && strlen($Update)>0 && $USER->CanDoOperation('fileman_edit_all_settings') && check_bitrix_sessid())
{
	if($default_edit!="html" && $default_edit!="php")
		$default_edit="text";
	COption::SetOptionString($module_id, "default_edit", $default_edit);

	if($use_old_version!="Y")
		$use_old_version = "N";
	COption::SetOptionString($module_id, "use_old_version", $use_old_version);

	if($htmleditor_fullscreen!="Y")
		$htmleditor_fullscreen = "N";
	COption::SetOptionString($module_id, "htmleditor_fullscreen", $htmleditor_fullscreen);

	COption::SetOptionString($module_id, "show_untitled_styles", $show_untitled_styles);
	COption::SetOptionString($module_id, "render_styles_in_classlist", $render_styles_in_classlist);

	if(is_dir($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/FCKeditor'))
	{
		if($ext_html_editor!="not_pages" && $ext_html_editor!="not_php" && $ext_html_editor!="not_admin" && $ext_html_editor!="always")
		$ext_html_editor="";
		COption::SetOptionString($module_id, "ext_html_editor", $ext_html_editor);
	}
	
	//File extensions
	if ($USER->CanDoOperation('edit_php'))
	{
		COption::SetOptionString($module_id, "~script_files", $script_files);
		COption::SetOptionString($module_id, "~allowed_components", $allowed_components);
	}
	
	// LCA - limit component access
	COption::SetOptionString($module_id, "use_lca", ($use_lca == 'Y' ? 'Y' : 'N'));
	
	//Entities
	$str_ar_entities = implode(',',$ar_entities);
	COption::SetOptionString($module_id, "ar_entities", $str_ar_entities);
	
	$siteList_ID = unserialize($mSiteList);

	if(isset($dif_settings))
	{
		COption::SetOptionString($module_id, "different_set", "Y");

		$j = 0;
		while($j < count($siteList_ID))
		{
			COption::SetOptionInt($module_id, "num_menu_param", ${"num_".$siteList_ID[$j]["ID"]."_menu_param"}, false, $siteList_ID[$j]["ID"]);

			$menutypes = "";
			$armt = Array();
			for($i=0; $i<${"menutypes_".$siteList_ID[$j]["ID"]."_count"}; $i++)
			{
				if(strlen(${"menutypes_".$siteList_ID[$j]["ID"]."_".$i."_type"})>0)
					$armt[${"menutypes_".$siteList_ID[$j]["ID"]."_".$i."_type"}] = ${"menutypes_".$siteList_ID[$j]["ID"]."_".$i."_name"};
			}
			
			if(strlen(${"menutypes_".$siteList_ID[$j]["ID"]."_new_type"})>0 && $USER->CanDoOperation('fileman_edit_menu_types'))
				$armt[${"menutypes_".$siteList_ID[$j]["ID"]."_new_type"}] = ${"menutypes_".$siteList_ID[$j]["ID"]."_new_name"};

			SetMenuTypes($armt, $siteList_ID[$j]["ID"]);
		
			$arPT = Array();
			for($i=0; $i<${"propstypes_".$siteList_ID[$j]["ID"]."_count"}; $i++)
			{
				if(strlen(${"propstypes_".$siteList_ID[$j]["ID"]."_".$i."_type"})>0)
					$arPT[${"propstypes_".$siteList_ID[$j]["ID"]."_".$i."_type"}] = ${"propstypes_".$siteList_ID[$j]["ID"]."_".$i."_name"};
			}
			if(strlen(${"propstypes_".$siteList_ID[$j]["ID"]."_new_type"})>0)
				$arPT[${"propstypes_".$siteList_ID[$j]["ID"]."_new_type"}] = ${"propstypes_".$siteList_ID[$j]["ID"]."_new_name"};

			CFileMan::SetPropstypes($arPT, false, $siteList_ID[$j]["ID"]);
			$j++;
		}
	}
	else
	{
		COption::SetOptionString($module_id, "different_set", "N");
		COption::SetOptionInt($module_id, "num_menu_param", $num_menu_param);

		$armt = Array();
		$menutypes = "";
		for($i=0; $i<$menutypes_count; $i++)
		{
			if(strlen(${"menutypes_".$i."_type"})>0)
				$armt[${"menutypes_".$i."_type"}] = ${"menutypes_".$i."_name"};
		}
		if(strlen($menutypes_new_type)>0 && $USER->CanDoOperation('fileman_edit_menu_types'))
			$armt[$menutypes_new_type] = $menutypes_new_name;

		SetMenuTypes($armt, '');
		
		$propstypes = "";
		$arPT = Array();
		for($i=0; $i<$propstypes_count; $i++)
		{
			if(strlen(${"propstypes_".$i."_type"})>0)
				$arPT[${"propstypes_".$i."_type"}] = ${"propstypes_".$i."_name"};
		}
		if(strlen($propstypes_new_type)>0)
			$arPT[$propstypes_new_type] = $propstypes_new_name;

		CFileMan::SetPropstypes($arPT);

		$j = 0;
		while($j < count($siteList_ID))
		{
			COption::RemoveOption($module_id, "menutypes", $siteList_ID[$j]["ID"]);
			COption::RemoveOption($module_id, "propstypes", $siteList_ID[$j]["ID"]);
			COption::RemoveOption($module_id, "num_menu_param", $siteList_ID[$j]["ID"]);
			$j++;
		}

	}
	
	COption::SetOptionString($module_id, "show_inc_icons", (isset($_POST['show_inc_icons']) ? 'Y' : 'N'));
	COption::SetOptionString($module_id, "spell_check_first_client", (isset($_POST['spell_check_first_client']) ? 'Y' : 'N'));

	COption::SetOptionString($module_id, "hide_physical_struc", (isset($_POST['hide_physical_struc'])));

	if (isset($_POST['use_pspell']))
		COption::SetOptionString($module_id, "use_pspell", "Y");
	else
		COption::SetOptionString($module_id, "use_pspell", "N");


	if (isset($_POST['user_dics_path']) && $_POST['user_dics_path']!='')
		COption::SetOptionString($module_id, "user_dics_path", $_POST['user_dics_path']);
	else
		COption::SetOptionString($module_id, "user_dics_path","/bitrix/modules/fileman/u_dics");


	if (isset($_POST['use_separeted_dics']))
		COption::SetOptionString($module_id, "use_separeted_dics", "Y");
	else
		COption::SetOptionString($module_id, "use_separeted_dics", "N");

	COption::SetOptionString($module_id, "use_custom_spell", "N");


	//Handle dictionary loading
	if (isset($_POST['dic_lang']) && isset($_FILES['dic_aff']) && isset($_FILES['dic_base']) && $_FILES['dic_aff']['name'] != '' && 	$_FILES['dic_base']['name'] != '')
	{
		$dic_lang = $_POST['dic_lang'];
		if (isValidLang($dic_lang))
		{
			$lang_dir = $_SERVER['DOCUMENT_ROOT'].$dicsRelPath.'/'.$dic_lang;
			$dics_dir = $_SERVER['DOCUMENT_ROOT'].$dicsRelPath.'/'.$dic_lang.'/dics';

			if (!file_exists($lang_dir))
				mkdir($lang_dir);

				$source=$_FILES['dic_base']['tmp_name'];
				$target = $lang_dir.'/'.$dic_lang.'.dic';
				if (file_exists($target))
					unlink ($target);
				move_uploaded_file($source, $target);

				$source=$_FILES['dic_aff']['tmp_name'];
				$target = $lang_dir.'/'.$dic_lang.'.aff';
				if (file_exists($target))
					unlink ($target);
				move_uploaded_file($source, $target);

			if (!file_exists($dics_dir))
				mkdir($dics_dir);
			COption::SetOptionString($module_id, $dic_lang."_dic_indexed", "N");
		}
	}

	//Handle dictionary removing
	if (isset($_POST['del_dic']))
	{
		$lang_dir = $_SERVER['DOCUMENT_ROOT'].$dicsRelPath.'/'.$_POST['del_dic'];
		if (file_exists($lang_dir) && is_dir($lang_dir))
		{
			$dicDir = dir($lang_dir);
			while (false !== ($entry = $dicDir->read()))
			{
				$entry_path = $dicDir->path.'/'.$entry;
				if (is_dir($entry_path) && $entry=='dics')
				{
					//Removing files from 'dics' directory
					$dicsDir = dir($entry_path);
					while (false !== ($dic = $dicsDir->read()))
					{
						$dic_path = $dicsDir->path.'/'.$dic;
						if (is_file($dic_path))
							unlink ($dic_path);
					}
					$dicsDir->close();
					//removing 'dics' directory
					rmdir($entry_path);
				}
				elseif (is_file($entry_path))
				{
					unlink ($entry_path);
				}
			}
			$dicDir->close();
			rmdir($lang_dir);
		}
	}

	//Handle dictionary indexing
	if (isset($_POST['index_dic']))
	{
		$lang_dir = $_SERVER['DOCUMENT_ROOT'].$dicsRelPath.'/'.$_POST['index_dic'];
		if (file_exists($lang_dir) && is_dir($lang_dir))
		{
			$dicsDir = dir($lang_dir.'/dics');
			while (false !== ($dic = $dicsDir->read()))
			{
				$dic_path = $dicsDir->path.'/'.$dic;
				if (is_file($dic_path))
					unlink ($dic_path);
			}
			$dicsDir->close();

			require($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/fileman/admin/spell_createDictionary.php');

			$CD = new createDictionary();
			$lang = $_POST['index_dic'];
			$CD->init($lang,$lang_dir);
			if ($CD->create())
				COption::SetOptionString($module_id, $dic_lang."_dic_indexed", "Y");
		}
	}
}


if ($REQUEST_METHOD=="GET" && isset($_GET['load_dic']) && $USER->CanDoOperation('fileman_edit_all_settings'))
{
	if (isValidLang($_GET['load_dic']))
	{
		$l_id = $_GET['load_dic'];
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/tar_gz.php");
		$indexedDicPath = $_SERVER['DOCUMENT_ROOT'].$gzDicsRelPath.'/'.$l_id.'.tar.gz';
		$oArchiver = new CArchiver($indexedDicPath);

		if ($oArchiver->extractFiles($_SERVER['DOCUMENT_ROOT'].$dicsRelPath.'/'.$l_id))
			COption::SetOptionString($module_id, $l_id."_dic_indexed", "Y");
	}
}



$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "ICON" => "fileman_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
	array("DIV" => "edit2", "TAB" => GetMessage("MAIN_TAB_VISUAL_EDITOR"), "ICON" => "fileman_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_VIS_ED_SET")),
	array("DIV" => "edit3", "TAB" => GetMessage("MAIN_TAB_RIGHTS"), "ICON" => "fileman_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS")),
	);

	$siteList = array();
	$rsSites = CSite::GetList($by="sort", $order="asc", Array());
	$i = 0;
	while($arRes = $rsSites->Fetch())
	{
		$siteList[$i]["ID"] = $arRes["ID"];
		$siteList[$i]["NAME"] = $arRes["NAME"];
		$i++;
	}
	$siteCount = $i;

	unset($rsSites);
	unset($arRes);

	$tabControl = new CAdmintabControl("tabControl", $aTabs);
	$tabControl->Begin();

?>


<script>
	function SelectSite(id)
	{
		<?for($i = 0; $i < $siteCount; $i++):?>
		document.getElementById('<?= htmlspecialchars($siteList[$i]["ID"]);?>_Propery').style.display='none';
		<?endfor;?>
		document.getElementById(id+'_Propery').style.display='';
	}

	function hideSite()
	{
		<?for($i = 0; $i < $siteCount; $i++):?>
		document.getElementById('<?= htmlspecialchars($siteList[$i]["ID"]);?>_Propery').style.display='none';
		<?endfor;?>
	}

	function showCustomSpellSettings(id)
	{
		var checker = document.getElementById(id);
		var customSpellSettings = document.getElementById('customSpellSettings');
		if (checker.checked)
		{
			customSpellSettings.style.display = "block";
		}
		else
		{
			customSpellSettings.style.display = "none";
		}
	}
</script>

<form method="POST" enctype="multipart/form-data" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialchars($mid)?>&lang=<?echo LANG?>">
<?=bitrix_sessid_post()?>
<?$tabControl->BeginNextTab();?>
<tr>
	<td valign="top" width="40%"><?echo GetMessage("FILEMAN_OPTION_DEF_EDITOR")?></td>
	<td valign="top" width="60%">

	<select name="default_edit">
		<option value="text"><?echo GetMessage("FILEMAN_OPTION_EDITOR_TEXT")?></option>
		<option value="php"<?if(COption::GetOptionString($module_id, "default_edit", "text")=="php")echo " selected"?>><?echo GetMessage("FILEMAN_OPTION_EDITOR_PHP")?></option>
		<option value="html"<?if(COption::GetOptionString($module_id, "default_edit", "text")=="html")echo " selected"?>><?echo GetMessage("FILEMAN_OPTION_EDITOR_HTML")?></option>
	</select>
	</td>
</tr>
	<? if (COption::GetOptionString('main', 'distributive6', 'N') != 'Y'):?>
	<tr>
		<td valign="top"><label for="show_inc_icons"><?echo GetMessage("FILEMAN_OPTION_MENU_SHOW_INC")?></label></td>
		<td><input type="checkbox" name="show_inc_icons" id="show_inc_icons" size="5" value="Y" <?if(COption::GetOptionString($module_id, "show_inc_icons", "Y")=="Y")echo " checked"?>></td>
	</tr>
	<?endif;?>
	<? if ($USER->CanDoOperation('edit_php')):?>
	<tr>
		<td>
			<?echo GetMessage("FILEMAN_OPTION_SCRIPT_FILES")?>:
		</td>
		<td>
			<input type="text" name="script_files" id="script_files" size="40" value="<?echo COption::GetOptionString($module_id, "~script_files", $script_files_default);?>">
		</td>
	</tr>
	<tr>
		<td valign="top">
			<?echo GetMessage("FILEMAN_OPTION_ALLOWED_COMPONENTS")?>:
		</td>
		<td>
			<textarea cols="30" rows="4" name="allowed_components"><?echo COption::GetOptionString($module_id, "~allowed_components", '');?></textarea>
		</td>
	</tr>
	<?endif;?>
	<tr>
	<td valign="top" width="40%"><label for="hide_physical_struc"><?echo GetMessage("FILEMAN_HIDE_PHYSICAL_STRUC")?>:</label></td>
	<td valign="top" width="60%">
	<input type="checkbox" name="hide_physical_struc" id="hide_physical_struc" <? if(COption::GetOptionString($module_id, "hide_physical_struc", false) == true) echo " checked";?>>
	</td>
</tr>
	<tr>
		<td colspan=2>&nbsp;</td>
	</tr>
	<tr class="heading">
		<td colspan=2><? echo GetMessage("FILEMAN_OPTION_SPECIAL_SETTINGS")?></td>
	</tr>
	<tr>
		<td><? echo GetMessage("FILEMAN_OPTION_DIFFERENT_SET")?></td>
		<td><input type="checkbox" name="dif_settings" id="dif_settings_id" onClick="if(this.checked) {  document.getElementById('comPropery').style.display='none'; document.getElementById('site_select_id').disabled=false; SelectSite(document.getElementById('site_select_id').value);} else { document.getElementById('site_select_id').disabled=true; document.getElementById('comPropery').style.display=''; hideSite();}" <? if(COption::GetOptionString($module_id, "different_set", "N") == "Y") echo " checked";?>></td>
	</tr>
	<tr>
		<td><?echo GetMessage("FILEMAN_OPTION_FOR_SYTE")?></td>
		<td>
			<select name="site_select" id="site_select_id" onChange="SelectSite(this.value)" <? if(COption::GetOptionString($module_id, "different_set", "N") != "Y") echo " disabled"; ?>>
			<?
				for($i = 0; $i < $siteCount; $i++)
					echo "<option value=\"".htmlspecialchars($siteList[$i]["ID"])."\">".htmlspecialchars($siteList[$i]["NAME"])."</option>";
			?>
			</select>
		</td>
	</tr>
	<tr id="comPropery" <? if(COption::GetOptionString($module_id, "different_set", "N") == "Y") echo " style=\"display: none;\""; ?>>
		<td colspan="2">
		<table cellspacing="4"  cellpadding="0" width="100%">
		<tr>
			<td valign="top" width="40%" class="field-name" style="{padding: 4px;}"><?echo GetMessage("FILEMAN_OPTION_MENU_TYPES")?></td>
			<td valign="top" width="60%">
			<table cellpadding="0" cellspacing="2" border="0" width="100%">
			<tr class="heading">
				<td align="center" width="40%"><b><?echo GetMessage("FILEMAN_OPTION_MENU_TYPE")?></b></td>
				<td align="center" width="60%"><b><?echo GetMessage("FILEMAN_OPTION_MENU_NAME")?></b></td>
			</tr>
			<?
			$armt = GetMenuTypes('', "left=".GetMessage("FILEMAN_OPTION_LEFT_MENU_NAME").",top=".GetMessage("FILEMAN_OPTION_TOP_MENU_NAME"));
			
			
			$i = 0;
			foreach($armt as $key => $title):
				if ($USER->CanDoOperation('fileman_edit_menu_types')):
				?>
					<tr>
						<td><input type="text" name="menutypes_<?echo $i?>_type" value="<?echo htmlspecialchars($key)?>" style="width:100%"></td>
						<td><input type="text" name="menutypes_<?echo $i?>_name" value="<?echo htmlspecialchars($title)?>" style="width:100%"></td>
					</tr>
				<?else:?>
				<tr>
					<td style="padding-left: 5px">
					<?echo htmlspecialchars($key)?>
					<input type="hidden" name="menutypes_<?echo $i?>_type" value="<?echo htmlspecialchars($key)?>">
					</td>
					<td  style="padding-left: 5px">
					<?echo htmlspecialchars($title)?>
					<input type="hidden" name="menutypes_<?echo $i?>_name" value="<?echo htmlspecialchars($title)?>">
					</td>
				</tr>
				<?
				endif;
				$i++;
			endforeach;
			?>
			<input type="hidden" name="menutypes_count" value="<?echo $i?>">
			<?if ($USER->CanDoOperation('fileman_edit_menu_types')):?>
			<tr>
				<td><input type="text" name="menutypes_new_type" value="" style="width:100%"></td>
				<td><input type="text" name="menutypes_new_name" value="" style="width:100%"></td>
			</tr>
			<?endif;?>
			</table>
			</td>
		</tr>
		<tr>
			<td valign="top" class="field-name" style="{padding: 4px;}"><?echo GetMessage("FILEMAN_OPTION_MENU_PARAMS")?></td>
			<td><input type="text" name="num_menu_param" size="5" value="<?echo COption::GetOptionInt($module_id, "num_menu_param", 1, "")?>"></td>
		</tr>
		<tr>
			<td valign="top" class="field-name" style="{padding: 4px;}"><?echo GetMessage("FILEMAN_OPTION_PROPS_TYPES")?></td>
			<td valign="top">
			<table cellpadding="0" cellspacing="2" border="0" width="100%">
			<tr class="heading">
				<td align="center" width="40%"><b><?echo GetMessage("FILEMAN_OPTION_PROPS_TYPE")?></b></td>
				<td align="center" width="60%"><b><?echo GetMessage("FILEMAN_OPTION_PROPS_NAME")?></b></td>
			</tr>
			<?

			$i = 0;
			foreach (CFileMan::GetPropstypes('') as $key => $val)
			{
				?>
			<tr>
				<td><input type="text" name="propstypes_<?echo $i?>_type" value="<?echo htmlspecialchars($key)?>" style="width:100%"></td>
				<td><input type="text" name="propstypes_<?echo $i?>_name" value="<?echo htmlspecialchars($val)?>" style="width:100%"></td>
			</tr>
			<?
				$i++;
			}
			?>
			<input type="hidden" name="propstypes_count" value="<?echo $i+1;?>">
			<tr>
				<td><input type="text" name="propstypes_new_type" value="" style="width:100%"></td>
				<td><input type="text" name="propstypes_new_name" value="" style="width:100%"></td>
			</tr>
			</table>
			</td>
			</tr>
			</td>
		</tr>
		</table>
		</td>
	</tr>
	<input type="hidden" name="mSiteList" value="<?=htmlspecialchars(serialize($siteList))?>">
	<?
	for($j = 0; $j < $siteCount; $j++)
	{
	?>
	<tr id="<?= htmlspecialchars($siteList[$j]["ID"])?>_Propery" style="<? if(((COption::GetOptionString($module_id, "different_set", "N") == "Y") && ($j != 0)) || (COption::GetOptionString($module_id, "different_set", "N") == "N")) echo "display: none;"?>">
		<td colspan="2">
		<table cellspacing="4" cellpadding="0" width="100%">
		<tr>
			<td valign="top" width="40%" class="field-name" style="{padding: 4px;}"><?echo GetMessage("FILEMAN_OPTION_MENU_TYPES")?></td>
			<td valign="top" width="60%">
			<table cellpadding="0" cellspacing="2" border="0" width="100%">
			<tr class="heading">
				<td align="center" width="40%"><b><?echo GetMessage("FILEMAN_OPTION_MENU_TYPE")?></b></td>
				<td align="center" width="60%"><b><?echo GetMessage("FILEMAN_OPTION_MENU_NAME")?></b></td>
			</tr>
			<?
			$armt = GetMenuTypes($siteList[$j]["ID"], "left=".GetMessage("FILEMAN_OPTION_LEFT_MENU_NAME").",top=".GetMessage("FILEMAN_OPTION_TOP_MENU_NAME"));

			$i = 0;
			foreach($armt as $key => $title):
				if ($USER->CanDoOperation('fileman_edit_menu_types')):
				?>
			<tr>
				<td><input type="text" name="menutypes_<?= htmlspecialchars($siteList[$j]["ID"])?>_<?echo $i?>_type" value="<?echo htmlspecialchars($key)?>" style="width:100%"></td>
				<td><input type="text" name="menutypes_<?= htmlspecialchars($siteList[$j]["ID"])?>_<?echo $i?>_name" value="<?echo htmlspecialchars($title)?>" style="width:100%"></td>
			</tr>
				<?else:?>
			<tr>
				<td>
				<?echo htmlspecialchars($key)?>
				<input type="hidden" name="menutypes_<?= htmlspecialchars($siteList[$j]["ID"])?>_<?echo $i?>_type" value="<?echo htmlspecialchars($key)?>">
				</td>
				<td>
				<?echo htmlspecialchars($title)?>
				<input type="hidden" name="menutypes_<?= htmlspecialchars($siteList[$j]["ID"])?>_<?echo $i?>_name" value="<?echo htmlspecialchars($title)?>">
				</td>
			</tr>
				<?
				endif;
				$i++;
			endforeach;
			?>
			<input type="hidden" name="menutypes_<?= htmlspecialchars($siteList[$j]["ID"])?>_count" value="<?echo $i?>">
			<?if($USER->CanDoOperation('fileman_edit_menu_types')):?>
			<tr>
				<td><input type="text" name="menutypes_<?= htmlspecialchars($siteList[$j]["ID"])?>_new_type" value="" style="width:100%"></td>
				<td><input type="text" name="menutypes_<?= htmlspecialchars($siteList[$j]["ID"])?>_new_name" value="" style="width:100%"></td>
			</tr>
			<?endif;?>
			</table>
			</td>
		</tr>
		<tr>
			<td valign="top" class="field-name" style="{padding: 4px;}"><?echo GetMessage("FILEMAN_OPTION_MENU_PARAMS")?></td>
			<td><input type="text" name="num_<?= htmlspecialchars($siteList[$j]["ID"])?>_menu_param" size="5" value="<?echo COption::GetOptionInt($module_id, "num_menu_param", 1, $siteList[$j]["ID"])?>"></td>
		</tr>
		<tr>
			<td valign="top" class="field-name" style="{padding: 4px;}"><?echo GetMessage("FILEMAN_OPTION_PROPS_TYPES")?></td>
			<td valign="top">
			<table cellpadding="0" cellspacing="2" border="0" width="100%">
			<tr class="heading">
				<td align="center" width="40%"><b><?echo GetMessage("FILEMAN_OPTION_PROPS_TYPE")?></b></td>
				<td align="center" width="60%"><b><?echo GetMessage("FILEMAN_OPTION_PROPS_NAME")?></b></td>
			</tr>
			<?

			$i = 0;
			foreach (CFileMan::GetPropstypes($siteList[$j]["ID"]) as $key => $val)
			{?>
			<tr>
				<td><input type="text" name="propstypes_<?= htmlspecialchars($siteList[$j]["ID"])?>_<?echo $i?>_type" value="<?echo htmlspecialchars($key)?>" style="width:100%"></td>
				<td><input type="text" name="propstypes_<?= htmlspecialchars($siteList[$j]["ID"])?>_<?echo $i?>_name" value="<?echo htmlspecialchars($val)?>" style="width:100%"></td>
			</tr>
			<?
				$i++;
			}
			?>
			<input type="hidden" name="propstypes_<?= htmlspecialchars($siteList[$j]["ID"])?>_count" value="<?echo $i+1?>">
			<tr>
				<td><input type="text" name="propstypes_<?= htmlspecialchars($siteList[$j]["ID"])?>_new_type" value="" style="width:100%"></td>
				<td><input type="text" name="propstypes_<?= htmlspecialchars($siteList[$j]["ID"])?>_new_name" value="" style="width:100%"></td>
			</tr>
			</table>
			</td>
		</tr>
		</td>
		</tr>
		</table>
	</td>
	</tr>
	<? } ?>
<?$tabControl->BeginNextTab();?>
<? if (COption::GetOptionString('main', 'distributive6', 'N') != 'Y'):?>
<tr>
	<td valign="top"><label for="use_old_version"><?echo 	GetMessage("FILEMAN_OPTION_USE_OLD_VER")?></label></td>
	<td><input type="checkbox" name="use_old_version" onclick="document.getElementById('htmleditor_fullscreen').disabled = this.checked" id="use_old_version" value="Y" <?if(COption::GetOptionString($module_id, "use_old_version", "N")=="Y")echo " checked"?>></td>
</tr>
<?endif;?>
<tr>
	<td valign="top"><label for="show_untitled_styles"><?echo GetMessage("FILEMAN_OPTION_USE_ONLY_DEFINED_STYLES")?></label></td>
	<td><input type="checkbox" name="show_untitled_styles" id="show_untitled_styles" value="Y" <?if(COption::GetOptionString($module_id, "show_untitled_styles", "N")=="Y")echo " checked"?>></td>
</tr>
<tr>
	<td valign="top"><label for="render_styles_in_classlist"><?echo GetMessage("FILEMAN_OPTION_RENDER_CLASSLIST_STYLE")?>:</label></td>
	<td><input type="checkbox" name="render_styles_in_classlist" id="render_styles_in_classlist" value="Y" <?if(COption::GetOptionString($module_id, "render_styles_in_classlist", "N") == "Y") echo " checked"?>></td>
</tr>
<tr>
	<td valign="top"><label for="htmleditor_fullscreen"><?echo GetMessage("FILEMAN_OPT_FULLSCREEN")?></label></td>
	<td><input type="checkbox" <?if(COption::GetOptionString($module_id, "use_old_version", "N")=="Y")echo " disabled"?> name="htmleditor_fullscreen" id="htmleditor_fullscreen" value="Y" <?if(COption::GetOptionString($module_id, "htmleditor_fullscreen", "N")=="Y")echo " checked"?>></td>
</tr>

<?if(is_dir($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/FCKeditor')):?>
<tr>
	<td valign="top"><?echo GetMessage("FILEMAN_OPTION_USE_FCK")?></td>
	<td valign="top">

	<select name="ext_html_editor" onchange="document.getElementById('htmleditor_fullscreen').disabled = (this.value=='always')">
		<option value=""><?echo GetMessage("FILEMAN_OPTION_USE_FCK_NOT")?></option>
		<option value="not_pages"<?if(COption::GetOptionString($module_id, "ext_html_editor", "")=="not_pages")echo " selected"?>><?echo GetMessage("FILEMAN_OPTION_USE_FCK_NOT_PAGES")?></option>
		<option value="not_php"<?if(COption::GetOptionString($module_id, "ext_html_editor", "")=="not_php")echo " selected"?>><?echo GetMessage("FILEMAN_OPTION_USE_FCK_NOT_PHP")?></option>
		<option value="not_admin"<?if(COption::GetOptionString($module_id, "ext_html_editor", "")=="not_admin")echo " selected"?>><?echo GetMessage("FILEMAN_OPTION_USE_FCK_NOT_ADMIN")?></option>
		<option value="always"<?if(COption::GetOptionString($module_id, "ext_html_editor", "")=="always")echo " selected"?>><?echo GetMessage("FILEMAN_OPTION_USE_FCK_ALWAYS")?></option>
	</select>

	</td>
</tr>
<?endif;?>
	<tr class="heading">
		<td colspan="2"><? echo GetMessage("FILEMAN_EDITOR_CONVERT_SETTINGS");?></td>
	</tr>
		<td width="50%" valign="top"><label for='use_lca'><?echo GetMessage("FILEMAN_USE_LCA");?>:</td>
		<td  valign="top">
			<input type="checkbox" name="use_lca" id='use_lca' value="Y" <? if (COption::GetOptionString($module_id, "use_lca", 'N') == 'Y') echo 'checked';?>>
		</td>
	<tr>
		<td width="50%" valign="top"><?echo GetMessage("FILEMAN_ENTITIES_GROUPS");?>:</td>
		<td  valign="top">
			<?$ar_entities = explode(',',COption::GetOptionString($module_id, "ar_entities", 'umlya,greek,other')); ?>
			<table border="0" style="width:100%">
			<tr>
				<td><input type="checkbox" name="ar_entities[]" id='ent_umlya' value="umlya" <? if(in_array('umlya',$ar_entities)) echo 'checked';?>></td>
				<td><label for='ent_umlya'><?echo GetMessage("FILEMAN_ENTITIES_UMLYA");?></label></td>
			</tr>
			<tr>
				<td><input type="checkbox" name="ar_entities[]" id="ent_greek" value="greek" <? if(in_array('greek',$ar_entities)) echo 'checked';?>></td>
				<td><label for='ent_greek'><?echo GetMessage("FILEMAN_ENTITIES_GREEK");?></label></td>
			</tr>
			<tr>
				<td><input type="checkbox" name="ar_entities[]" value="other" id="ent_other" <? if(in_array('other',$ar_entities)) echo 'checked';?>></td>
				<td><label for='ent_other'><?echo GetMessage("FILEMAN_ENTITIES_OTHER");?></label></td>
			</tr>
			</table>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><? echo GetMessage("FILEMAN_OPTION_SPELL_SET");?></td>
	</tr>
	<tr>
		<td width="50%"><label for="spell_check_first_client"><?echo GetMessage("FILEMAN_OPTION_FIRST_SPELL_CLIENT");?></label></td>
		<td>
			<input type="checkbox" name="spell_check_first_client" id="spell_check_first_client" value="Y" <?echo (COption::GetOptionString($module_id, "spell_check_first_client", "Y")=="Y") ? 'checked' : '';?>>
		</td>
	</tr>

	<?
	if (function_exists('pspell_config_create')):
		$use_pspell_checked = (COption::GetOptionString($module_id, "use_pspell", "Y")=="Y") ? "checked" : "";
	
	?>
	<tr>
		<td valign="top"><label for="use_pspell"><?echo GetMessage("FILEMAN_OPTION_USE_PSPELL");?></label><br>
						 <a title="<?echo GetMessage("FILEMAN_OPTION_ADDISH_DICS_TITLE");?>" href="http://aspell.sourceforge.net/" target="blank"><?echo GetMessage("FILEMAN_OPTION_ADDISH_DICS");?></a><br>
		</td>
		<td>
			<input type="checkbox" name="use_pspell" id="use_pspell" value="Y" <?echo $use_pspell_checked;?>>
		</td>
	</tr>
	<tr>
		<td><? echo GetMessage("FILEMAN_OPTION_USER_DIC_DIR");?></td>
		<td>
			<input type="text" name="user_dics_path" style="width: 100%" value="<? echo COption::GetOptionString($module_id, "user_dics_path", "/bitrix/modules/fileman/u_dics")?>">
		</td>
	</tr>
	<tr>
		<td><label for="use_separeted_dics"><?echo GetMessage("FILEMAN_OPTION_USE_SEP_DICS");?></label></td>
		<td>
			<input type="checkbox" name="use_separeted_dics" id="use_separeted_dics" value="Y" <?echo (COption::GetOptionString($module_id, "use_separeted_dics", "Y")=="Y") ? "checked" : "";?>>
		</td>
	</tr>
	<?else:
			COption::SetOptionString($module_id, "use_pspell", "N");
	?>
	<tr>
		<td valign="top"><?echo GetMessage("FILEMAN_OPTION_USE_PSPELL");?><br>
						 <a title="<?echo GetMessage("FILEMAN_OPTION_INSTALL_PSPELL_TITLE");?>" href="http://php.net/manual/en/ref.pspell.php" target="blank"><?echo GetMessage("FILEMAN_OPTION_INSTALL_PSPELL");?></a><br>
						 <a title="<?echo GetMessage("FILEMAN_OPTION_ADDISH_DICS_TITLE");?>" href="http://aspell.sourceforge.net/" target="blank"><?echo GetMessage("FILEMAN_OPTION_ADDISH_DICS");?></a><br>
		</td>
		<td valign="top">
			<?echo GetMessage("FILEMAN_OPTION_NOT_INSTALLED");?>
		</td>
	</tr>
	<?endif;?>
<?$tabControl->BeginNextTab();?>
<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights2.php");?>
<?$tabControl->Buttons();?>
<script>
	function RestoreDefaults()
	{
		if(confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>'))
			window.location = "<?echo $APPLICATION->GetCurPage()?>?RestoreDefaults=Y&lang=<?echo LANG?>&mid=<?echo urlencode($mid)?>&<?=bitrix_sessid_get()?>";
	}
</script>
<input type="submit" <?if (!$USER->CanDoOperation('fileman_edit_all_settings')) echo "disabled" ?> name="Update" value="<?echo GetMessage("FILEMAN_OPTION_SAVE")?>">
<input type="reset" name="reset" onClick="document.getElementById('site_select_id').disabled=<? if(COption::GetOptionString($module_id, "different_set", "N") != "Y") echo "true"; else echo "false"; ?>; SelectSite('<?echo htmlspecialchars($siteList[0]["ID"])?>');" value="<?echo GetMessage("FILEMAN_OPTION_RESET")?>">
<input type="hidden" name="Update" value="Y">
<input <?if (!$USER->CanDoOperation('fileman_edit_all_settings')) echo "disabled" ?> type="button" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="RestoreDefaults();" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
<?$tabControl->End();?>
</form>