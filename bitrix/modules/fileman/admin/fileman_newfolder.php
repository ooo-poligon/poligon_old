<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if (!$USER->CanDoOperation('fileman_admin_folders'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");

$addUrl = 'lang='.LANGUAGE_ID.($logical == "Y"?'&logical=Y':'');
$strWarning = "";
$strNotice = "";
$site = CFileMan::__CheckSite($site);
$DOC_ROOT = CSite::GetSiteDocRoot($site);
while (($l=strlen($path))>0 && $path[$l-1]=="/")
	$path = substr($path, 0, $l-1);
$path = Rel2Abs("/", $path);
$arPath = Array($site, $path);

if (!$USER->CanDoFileOperation('fm_create_new_folder',$arPath))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	
$arParsedPath = CFileMan::ParsePath(Array($site, $path), true, false, "", $logical == "Y");
$abs_path = $DOC_ROOT.$path;

$bMenuTypeExists = false;
$arMenuTypes = Array();
$armt = GetMenuTypes($site, "left=".GetMessage("FILEMAN_MENU_EDIT_LEFT_MENU").",top=".GetMessage("FILEMAN_MENU_EDIT_TOP_MENU"));
foreach($armt as $key => $title)
{
	if(!$USER->CanDoFileOperation('fm_edit_existent_file',Array($site, $path."/.".$key.".menu.php")))
		continue;
	$arMenuTypes[] = array($key, $title);
	if($key == $menutype)
		$bMenuTypeExists = true;
}

//�������� ����� �� ������ � ��� �����
if (!$USER->CanDoFileOperation('fm_create_new_folder',$arPath))
	$strWarning = '<img src="/bitrix/images/fileman/deny.gif" width="28" height="28" border="0" align="left" alt="">'.GetMessage("ACCESS_DENIED");
else if(!is_dir($abs_path))
	$strWarning = GetMessage("FILEMAN_FOLDER_NOT_FOUND");
else
{
	if($REQUEST_METHOD=="POST" && strlen($save)>0 && check_bitrix_sessid())
	{
		if(strlen($foldername)<=0)
		{
			$strWarning = GetMessage("FILEMAN_NEWFOLDER_ENTER_NAME");
		}
		elseif (($mess = CFileMan::CheckFileName($foldername)) !== true)
		{
			$strWarning = $mess;
		}
		else
		{
			$pathto = Rel2Abs($path, $foldername);
			if(file_exists($DOC_ROOT.$pathto))
				$strWarning = '!'.GetMessage("FILEMAN_NEWFOLDER_EXISTS");
			else
			{
				$strWarning = CFileMan::CreateDir(Array($site, $pathto));
				if(strlen($strWarning)<=0)
				{
					if($USER->CanDoFileOperation('fm_add_to_menu',$arPath) && 
					$USER->CanDoOperation('fileman_add_element_to_menu') &&
					$mkmenu=="Y" && $bMenuTypeExists)
					{
						$arParsedPathTmp = CFileMan::ParsePath(Array($site, $pathto), true, false, "", $logical == "Y");
						$menu_path = $arParsedPathTmp["PREV"]."/.".$menutype.".menu.php";
						if($USER->CanDoFileOperation('fm_view_file',Array($site, $menu_path)))
						{
							$res = CFileMan::GetMenuArray($DOC_ROOT.$menu_path);
							$aMenuLinksTmp = $res["aMenuLinks"];
							$sMenuTemplateTmp = $res["sMenuTemplate"];
							$aMenuLinksTmp[] = Array($menuname, $arParsedPathTmp["PREV"]."/".$arParsedPathTmp["LAST"]."/", Array(), Array(), "");
							CFileMan::SaveMenu(Array($site, $menu_path), $aMenuLinksTmp, $sMenuTemplateTmp);
						}
					}

					if(strlen($sectionname)>0)
						$APPLICATION->SaveFileContent($DOC_ROOT.$pathto."/.section.php", "<?\n\$sSectionName=\"".CFileMan::EscapePHPString($sectionname)."\";\n?>");
						
					if ($e = $APPLICATION->GetException())
						$strNotice = $e->msg;
					else
					{
						if($USER->CanDoFileOperation('fm_create_new_file',$arPath) && 
						$USER->CanDoOperation('fileman_admin_files') && 
						$mkindex=="Y")
						{
							if($toedit=="Y")
								LocalRedirect("/bitrix/admin/fileman_html_edit.php?".$addUrl."&site=".$site."&template=".Urlencode($template)."&path=".UrlEncode($pathto."/index.php").(strlen($back_url)<=0?"":"&back_url=".UrlEncode($back_url)).(strlen($gotonewpage)<=0?"":"&gotonewpage=".UrlEncode($gotonewpage)).(strlen($backnewurl)<=0?"":"&backnewurl=".UrlEncode($backnewurl)));
							else
								$APPLICATION->SaveFileContent($DOC_ROOT.$pathto."/index.php", CFileman::GetTemplateContent($template));
						}
					}
					if ($e = $APPLICATION->GetException())
						$strNotice = $e->msg;
					elseif (strlen($apply)<=0 && $strNotice == '')
					{
						if(strlen($back_url)>0)
							LocalRedirect("/".ltrim($back_url, "/"));
						else
						{
							$arPathtoParsed = CFileMan::ParsePath(Array($site, $pathto), false, false, "", $logical == "Y");
							LocalRedirect("/bitrix/admin/fileman_admin.php?".$addUrl."&site=".$site."&path=".UrlEncode($arPathtoParsed["PREV"]));
						}
					}
				}
			}
		}
	}
	else
	{
		$mkindex="Y";
		$toedit="Y";
	}
}


foreach ($arParsedPath["AR_PATH"] as $chainLevel)
{
	$adminChain->AddItem(
		array(
			"TEXT" => htmlspecialcharsex($chainLevel["TITLE"]),
			"LINK" => ((strlen($chainLevel["LINK"]) > 0) ? $chainLevel["LINK"] : ""),
		)
	);
}

$APPLICATION->SetTitle(GetMessage("FILEMAN_NEW_FOLDER_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if(strlen($strWarning)<=0)
	$filename = $arParsedPath["LAST"];

$aMenu = array(
	array(
		"TEXT" => GetMessage("FILEMAN_BACK"),
		"LINK" => "fileman_admin.php?".$addUrl."&site=".$site."&path=".UrlEncode($path)
	)
);

$context = new CAdminContextMenu($aMenu);
$context->Show();
?>
<?CAdminMessage::ShowMessage($strNotice);?>
<?CAdminMessage::ShowMessage($strWarning);?>

<?
if ($USER->CanDoFileOperation('fm_create_new_folder',$arPath))
{
	?>
	<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="fnew_folder">
	<input type="hidden" name="logical" value="<?=htmlspecialchars($logical)?>">
	<?echo GetFilterHiddens("filter_");?>
	<input type="hidden" name="site" value="<?= htmlspecialchars($site) ?>">
	<input type="hidden" name="path" value="<?= htmlspecialchars($path) ?>">
	<input type="hidden" name="save" value="Y">
	<input type="hidden" name="back_url" value="<?echo htmlspecialchars($back_url);?>">
	<input type="hidden" name="lang" value="<?echo LANG ?>">
	<input type="hidden" name="ID" value="<?echo $ID ?>">
	<?if($gotonewpage=="Y"):?><input type="hidden" name="gotonewpage" value="Y"><?endif?>
	<?if($backnewurl=="Y"):?><input type="hidden" name="backnewurl" value="Y"><?endif?>
	<?=bitrix_sessid_post()?>

	<?
	$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("FILEMAN_TAB1"), "ICON" => "fileman", "TITLE" => GetMessage("FILEMAN_TAB1_ALT")),
	);

	$tabControl = new CAdminTabControl("tabControl", $aTabs);
	$tabControl->Begin();
	$tabControl->BeginNextTab();
	
	$arTemplates = CFileman::GetFileTemplates();
	?>
	<tr>
		<td width="40%"><?echo GetMessage("FILEMAN_NEWFOLDER_NAME")?></td>
		<td width="60%"><input type="text" name="foldername" value="<?echo htmlspecialchars($foldername)?>" size="30" maxlength="255"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("FILEMAN_NEWFOLDER_SEACTION_NAME")?></td>
		<td><input type="text" name="sectionname" value="<?echo htmlspecialchars($sectionname)?>" size="30" maxlength="255"></td>
	</tr>

	<?if($USER->CanDoFileOperation('fm_add_to_menu',$arPath) && $USER->CanDoOperation('fileman_add_element_to_menu') ):?>
	<tr><td colspan="2">&nbsp;</td></tr>
	
	<tr>
		<td><?echo GetMessage("FILEMAN_NEWFOLDER_ADDMENU")?></td>
		<td><input type="checkbox" name="mkmenu" value="Y"<?if($mkmenu=="Y")echo " checked"?> onclick="document.fnew_folder.menuname.disabled=!this.checked;document.fnew_folder.menutype.disabled=!this.checked;if(this.checked && document.fnew_folder.sectionname.value.length!='' && document.fnew_folder.menuname.value=='') document.fnew_folder.menuname.value=document.fnew_folder.sectionname.value;fx1.disabled=!this.checked;fx2.disabled=!this.checked;"></td>
	</tr>
	<tr id="fx1"<?if($mkmenu!="Y")echo " disabled"?>>
		<td><?echo GetMessage("FILEMAN_NEWFOLDER_MENU")?></td>
		<td>
			<select name="menutype" <?if($mkmenu!="Y")echo " disabled"?>>
				<?for($i=0; $i<count($arMenuTypes); $i++):?>
				<option value="<?echo htmlspecialchars($arMenuTypes[$i][0])?>" <?if($menutype==$arMenuTypes[$i][0])echo " selected"?>><?echo htmlspecialchars("[".$arMenuTypes[$i][0]."] ".$arMenuTypes[$i][1])?></option>
				<?endfor;?>
			</select>
		</td>
	</tr>
	<tr id="fx2"<?if($mkmenu!="Y")echo " disabled"?>>
		<td><?echo GetMessage("FILEMAN_NEWFOLDER_MENUITEM")?></td>
		<td><input type="text" name="menuname" value="<?echo htmlspecialchars($menuname)?>"<?if($mkmenu!="Y")echo " disabled"?>></td>
	</tr>
	<?endif;?>
	
	<?if($USER->CanDoFileOperation('fm_create_new_file',$arPath) && $USER->CanDoOperation('fileman_admin_files')):?>
	<tr><td colspan="2">&nbsp;</td></tr>
	
	<tr>
		<td><?echo GetMessage("FILEMAN_NEWFOLDER_MAKE_INDEX")?></td>
		<td><input type="checkbox" name="mkindex" value="Y"<?if($mkindex=="Y")echo " checked"?> onclick="document.fnew_folder.toedit.disabled=!this.checked;document.fnew_folder.template.disabled=!this.checked;ff1.disabled=!this.checked;ff2.disabled=!this.checked;"></td>
	</tr>
	<tr id="ff1">
		<td><?echo GetMessage("FILEMAN_NEWFOLDER_INDEX_TEMPLATE")?></td>
		<td>
		<select name="template" <?if($mkindex!="Y")echo " disabled"?>>
			<?for($i=0; $i<count($arTemplates); $i++):?>
			<option value="<?echo htmlspecialchars($arTemplates[$i]["file"])?>"<?if($template==$arTemplates[$i]["file"])echo " selected"?>><?echo htmlspecialchars($arTemplates[$i]["name"])?></option>
			<?endfor;?>
		</select>
		</td>
	</tr>
	<tr id="ff2">
		<td><?echo GetMessage("FILEMAN_NEWFOLDER_INDEX_EDIT")?></td>
		<td><input type="checkbox" name="toedit" value="Y"<?if($toedit=="Y")echo " checked"?><?if($mkindex!="Y")echo " disabled"?>></td>
	</tr>
	<?endif;?>
	<?
	$tabControl->EndTab();
	$tabControl->Buttons(
		array(
			"disabled" => false,
			"back_url" => (strlen($back_url) > 0 ? $back_url : "fileman_admin.php?".$addUrl."&site=".$site."&path=".UrlEncode($path))
		)
	);
	$tabControl->End();
	?>
	</form>
	<br>
	<?echo BeginNote();?>
	<span class="required">*</span><font class="legendtext"> - <?echo GetMessage("REQUIRED_FIELDS")?>
	<?echo EndNote(); ?>
	<?
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
