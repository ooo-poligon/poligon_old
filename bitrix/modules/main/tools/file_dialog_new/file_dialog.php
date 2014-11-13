<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
if(!$USER->CanDoOperation('fileman_view_file_structure'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

//****************************NEW FILE DIALOG*************************************
// PHP static class - /bitrix/modules/main/interface/admin_lib.php
// BXFileDialog  javascript class  - /bitrix/js/main/file_dialog.js

$startPath = $path = (isset($_GET['path'])) ? $_GET['path'] : '';
$lang = (isset($_GET['lang'])) ? $_GET['lang'] : '';
$DOC_ROOT = $_SERVER["DOCUMENT_ROOT"];
$abs_path = $DOC_ROOT.$path;
$fd_engine_js_src = '/bitrix/js/main/file_dialog_engine.js';
$fd_css_src = '/bitrix/themes/.default/file_dialog.css';
?>

<script>
/* Applying styles */
if (!window.fd_styles_link || !window.fd_styles_link.parentNode)
	window.fd_styles_link = jsUtils.loadCSSFile("<?=$fd_css_src.'?v='.@filemtime($_SERVER['DOCUMENT_ROOT'].$fd_css_src)?>");

var arSites = [];
<?
$arSites = Array();
$dbSitesList = CSite::GetList($b = "SORT", $o = "asc");
while($arSite = $dbSitesList->GetNext())
{
	$v = $arSite["NAME"] ? $arSite["NAME"] : $arSite["ID"];
	$arSites[$arSite["ID"]] = $v;
	echo 'arSites["'.$arSite["ID"].'"] = "'.$v.'";';
}
$siteCount = count($arSites);
$site = (isset($_GET['site']) && isset($arSites[$_GET['site']])) ? $_GET['site'] : key($arSites);
?>
BXSite = "<?=$site?>";
BXLang = "<?=$lang?>";
<?
__MakeFolderArray($site, ""); //Get dirlist for the root. Result: arTreeItems = .....
$arSelectOptions = __MakeMenuTypesArray($site, "");//Get menu. Result: arTreeItems = .....
?>

//*  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *
// FD_MESS - Array of messages for JS files
//*  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *
FD_MESS = {};
FD_MESS.FD_SAVE_TAB_TITLE = '<?=GetMessage('FD_SAVE_TAB_TITLE')?>';
FD_MESS.FD_OPEN_DIR = '<?=GetMessage('FD_OPEN_DIR')?>';
FD_MESS.FD_OPEN_TAB_TITLE = '<?=GetMessage('FD_OPEN_TAB_TITLE')?>';
FD_MESS.FD_CLOSE = '<?=GetMessage('FD_CLOSE')?>';
FD_MESS.FD_SORT_SIZE = '<?=GetMessage('FD_SORT_SIZE')?>';
FD_MESS.FD_SORT_DATE = '<?=GetMessage('FD_SORT_DATE')?>';
FD_MESS.FD_SORT_NAME = '<?=GetMessage('FD_SORT_NAME');?>';
FD_MESS.FD_SORT_TYPE = '<?=GetMessage('FD_SORT_TYPE');?>';
FD_MESS.FD_BUT_OPEN = '<?=GetMessage('FD_BUT_OPEN');?>';
FD_MESS.FD_BUT_SAVE = '<?=GetMessage('FD_BUT_SAVE')?>';
FD_MESS.FD_ALL_FILES = '<?=getMessage('FD_ALL_FILES')?>';
FD_MESS.FD_ALL_IMAGES = '<?=GetMessage('FD_ALL_IMAGES')?>';
FD_MESS.FD_BYTE = '<?=GetMessage('FD_BYTE')?>';
FD_MESS.FD_EMPTY_FILENAME = '<?=GetMessage('FD_EMPTY_FILENAME')?>';
FD_MESS.FD_INPUT_NEW_PUNKT_NAME = '<?=GetMessage('FD_INPUT_NEW_PUNKT_NAME')?>';
FD_MESS.FD_LAST_POINT = '<?=GetMessage('FD_LAST_POINT')?>';
FD_MESS.FD_NEWFOLDER_EXISTS = '<?=GetMessage('FD_NEWFOLDER_EXISTS')?>';
FD_MESS.FD_NEWFILE_EXISTS = '<?=GetMessage('FD_NEWFILE_EXISTS')?>';
FD_MESS.FD_RENAME = '<?=GetMessage('FD_RENAME')?>';
FD_MESS.FD_DELETE = '<?=GetMessage('FD_DELETE')?>';
FD_MESS.FD_CONFIRM_DEL_DIR = '<?=GetMessage('FD_CONFIRM_DEL_DIR')?>';
FD_MESS.FD_CONFIRM_DEL_FILE = '<?=GetMessage('FD_CONFIRM_DEL_FILE')?>';
FD_MESS.FD_EMPTY_NAME = '<?=GetMessage('FD_EMPTY_NAME')?>';
FD_MESS.FD_INCORRECT_NAME = '<?=GetMessage('FD_INCORRECT_NAME')?>';
FD_MESS.FD_LOADIND = '<?=GetMessage('FD_LOADING')?>...';

function OnLoad()
{
	window.oWaitWindow = new WaitWindow();
	window.oBXDialogTree = new BXDialogTree();
	window.oBXMenuHandling = new BXMenuHandling();
	window.oBXDialogControls = new BXDialogControls();
	window.oBXDialogWindow = new BXDialogWindow();
	window.oBXDialogTabs = new BXDialogTabs();

	if (oBXFileDialog.oConfig.operation == 'O' && oBXFileDialog.oConfig.showUploadTab)
	{
		oBXDialogTabs.AddTab('tab1',_ReplaceSpaceByNbsp('<?=GetMessage("FD_OPEN_TAB_TITLE")?>'),_Show_tab_OPEN, true);
		oBXDialogTabs.AddTab('tab2',_ReplaceSpaceByNbsp('<?=GetMessage("FD_LOAD_TAB_TITLE")?>'),_Show_tab_LOAD, false);
	}
	else if(oBXFileDialog.oConfig.operation == 'S' && oBXFileDialog.oConfig.showAddToMenuTab)
	{
		oBXDialogTabs.AddTab('tab1',_ReplaceSpaceByNbsp('<?=GetMessage("FD_SAVE_TAB_TITLE")?>'),_Show_tab_SAVE, true);
		oBXDialogTabs.AddTab('tab2',_ReplaceSpaceByNbsp('<?=GetMessage("FD_MENU_TAB_TITLE")?>'),_Show_tab_MENU, false);
		oBXDialogTabs.DisableTab('tab2',true);
		document.getElementById('add2menu_cont').style.display = 'block';
	}

	oBXDialogTabs.DisplayTabs();
	oBXDialogTree.Append();
	oBXFileDialog.SubmitFileDialog = SubmitFileDialog;
}

// Append file with File Dialog engine
if (!window.BXDialogTree)
	jsUtils.loadJSFile("<?=$fd_engine_js_src.'?v='.@filemtime($_SERVER['DOCUMENT_ROOT'].$fd_engine_js_src)?>", OnLoad);
else
	OnLoad();
</script>

<form id="file_dialog" name="file_dialog" onsubmit="return false;" style="padding:0px !important; margin:0px !important;">
<table class="bx-file-dialog" cellspacing="0">
<tr>
	<td style="height:27px !important; padding-top: 4px !important; vertical-align: middle !important;">
		<div id="__bx_fd_top_controls_container">
			<table style="width:100% !important;height:100% !important;" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<?if ($siteCount > 1)
					{
						$arSitesPP = Array();
						foreach ($arSites as $key => $val)
						{
							$arItem = Array(
								"ID" => $key,
								"TEXT" => '['.$key.'] '.$val,
								"ONCLICK" => "oBXDialogControls.SiteSelectorOnChange('".$key."')"
							);
							if ($key == $site)
								$arItem['ICON'] = 'checked';
							$arSitesPP[] = $arItem;
						}
						$u = new CAdminPopup("fd_site_list", "fd_site_list", $arSitesPP, array('zIndex' => 2500, 'dxShadow' => 0));
						$u->Show();
						?><td style="width:26px !important; padding: 0px 4px 0px 5px !important;">
						<div id="__bx_site_selector" bxvalue="<?=$site?>" onclick="oBXDialogControls.SiteSelectorOnClick(this);" class="fd_iconkit site_selector_div"><span><?=strtoupper($site)?></span></div>
						</td><?
					}?>
					<td style="width:320px !important; padding: 0px 2px 0px 2px !important;">
						<input class="fd_input" type="text" id="__bx_dir_path_bar"></input>
					</td>
					<td style="width:170px !important; padding: 0px 2px 0px 2px !important;">
						<img src="/bitrix/images/1.gif" class="fd_iconkit go_button" id="__bx_dir_path_go" title="<?=GetMessage("FD_GO_TO")?>"/>
						<img src="/bitrix/images/1.gif" __bx_disable="Y" class="fd_iconkit path_back_dis" title="<?=GetMessage("FD_GO_BACK")?>" id="__bx_dir_path_back"/>
						<img src="/bitrix/images/1.gif" __bx_disable="Y" class="fd_iconkit path_forward_dis" title="<?=GetMessage("FD_GO_FORWARD")?>" id="__bx_dir_path_forward"/>
						<img src="/bitrix/images/1.gif" class="fd_iconkit dir_path_up" title="<?=GetMessage("FD_GO_UP")?>" id="__bx_dir_path_up" />
						<img src="/bitrix/images/1.gif" class="fd_iconkit dir_path_root" title="<?=GetMessage("FD_GO_TO_ROOT")?>" id="__bx_dir_path_root" />
						<img src="/bitrix/images/1.gif" class="fd_iconkit new_dir" title="<?=GetMessage("FD_NEW_FOLDER")?>" id="__bx_new_dir" />
						<img src="/bitrix/images/1.gif" class="fd_iconkit refresh" title="<?=GetMessage("FD_REFRESH")?>" onclick="oBXDialogControls.RefreshOnclick(this);"/>
						<?
						$arSitesPP = Array();
						$arViews = Array(
							Array("ID" => 'list', "TEXT" => GetMessage("FD_VIEW_LIST"), "ONCLICK" => "oBXDialogControls.ViewSelector.OnChange('list')"),
							Array("ID" => 'detail', "TEXT" => GetMessage("FD_VIEW_DETAIL"), "ONCLICK" => "oBXDialogControls.ViewSelector.OnChange('detail')"),
							Array("ID" => 'preview', "TEXT" => GetMessage("FD_VIEW_PREVIEW"), "ONCLICK" => "oBXDialogControls.ViewSelector.OnChange('preview')")
						);
						$u = new CAdminPopup("fd_view_list", "fd_view_list", $arViews, array('zIndex' => 2500, 'dxShadow' => 0));
						$u->Show();
						?>
						<img onclick="oBXDialogControls.ViewSelector.OnClick();" src="/bitrix/images/1.gif" id="__bx_view_selector" class="fd_iconkit view_selector"  title="<?=GetMessage("FD_SELECT_VIEW")?>"/>
					</td>
					<td style="width:180px !important; padding: 0px 6px 0px 3px !important; text-align:right !important;" align="right">
						<?=GetMessage("FD_SORT_BY")?>:
						<select class="fd_select" id="__bx_sort_selector" title="<?=GetMessage("FD_SORT_BY")?>" style="font-size:11px !important;">
							<option value="name"><?=GetMessage("FD_SORT_BY_NAME")?></option>
							<option value="type"><?=GetMessage("FD_SORT_BY_TYPE")?></option>
							<option value="size"><?=GetMessage("FD_SORT_BY_SIZE")?></option>
							<option value="date"><?=GetMessage("FD_SORT_BY_DATE")?></option>
						</select>
					</td>
					<td style="width:20px !important; padding: 0px 6px 0px 3px !important;">
						<img src="/bitrix/images/1.gif" class="fd_iconkit sort_up" title="<?=GetMessage("FD_CHANGE_SORT_ORDER")?>" __bx_value="asc" id="__bx_sort_order" />
					</td>
				</tr>
			</table>
		</div>
	</td>
</tr>
<tr>
	<td style="vertical-align:top !important; height:398px !important; border: 0px solid #eda818 !important;">
		<div id="__bx_fd_tree_and_window" style="display:block">
			<table style="width:99% !important;height:250px !important;">
				<tr>
					<td style="width:25% !important; height:250px !important; padding:3px 3px 0px 3px !important;">
						<div id="__bx_treeContainer" class="fd_window"></div>
					</td>
					<td style="width:70% !important; height:250px !important; padding:3px 3px 0px 3px !important;">
						<div class="fd_window" style="width:100% !important; height:250px !important; overflow: hidden !important;">
							<div id="__bx_windowContainer"></div>
						</div>
					</td>
				</tr>
			</table>
		</div>
		<div id="__bx_fd_preview_and_panel" style="display:block;">
			<table style="width:100% !important;height:132px !important; padding:0px !important;" border="0">
				<tr>
					<td style="width:25% !important; height: 100% !important;">
							<div style="margin: 3px 8px 3px 5px;border:1px solid #C6C6C6"><div style="height:127px;">
							<div id="__bx_previewContainer" style="margin-left:20px !important;"></div>
							<div id="__bx_addInfoContainer" style="width:150px;padding-left:10px;"></div>
						</div></div> 
					</td>
					<td style="width:70% !important; vertical-align:top !important;">
						<div id="__save_container">
							<table style="width: 100% !important; height: 100% !important;" border="0">
								<tr>
									<td style="width:100% !important; vertical-align:top !important;" colspan="2">
										<input type="text" style="width:98% !important;margin-bottom:5px !important;" id="__bx_file_path_bar">
										<select style="width:98% !important; display:none; margin-bottom:5px !important;" id="__bx_file_filter"></select>
										<div id="__bx_page_title_cont" style="display:none;">
										<?=GetMessage('FD_PAGE_TITLE')?>:<br/>
										<input type="text" style="width:98% !important;" id="__bx_page_title1">
										</div>
									</td>
								</tr>
								<tr>
									<td style="width:50% !important; padding-right:11px !important; text-align:left !important; vertical-align:middle !important;">
										<table id="add2menu_cont" style="display:none"><tr>
											<td><input type="checkbox" id="__bx_fd_add_to_menu"></td>
											<td><label for="__bx_fd_add_to_menu"><?=GetMessage("FD_ADD_PAGE_2_MENU")?></label></td>
										</tr></table>
									</td>
									<td style="width:500% !important;padding-right:11px !important; text-align:right !important; vertical-align:bottom !important;">
										<input style="width:100px !important;" type="button" id="__bx_fd_submit_but" value="">
										<input style="width:100px !important;" type="button" onclick="oBXFileDialog.Close()" value="<?=GetMessage("FD_BUT_CANCEL");?>">
									</td>
								</tr>
							</table>
						</div>
					</td>
				</tr>
			</table>
		</div>
		<div id="__bx_fd_load" style="display:none;">
			<div id="__upload_container" style="width:100%; height:132px; padding-left:200px; border:0px solid #88f228;">
				<iframe id="__bx_iframe_upload" src="javascript:''" frameborder="0"></iframe>
			</div>
		</div>
		<div id="__bx_fd_container_add2menu" style="padding: 0px 0px 0px 16px; border: 0px solid black; display:none;width:735px;height:100%">
		<table style="width:722px !important;height:100% !important;" border="0">
			<tr>
				<td style="height:0%">
					<table class="fd_tab_title" cellpadding="0" cellspacing="0" border="0" style="width:95% !important; padding:0px !important;">
						<tr>
							<td class="icon"><div id="main_user_edit"></div></td>
							<td class="title"><?=GetMessage("FD_ADD_PAGE_2_MENU_TITLE")?></td>
						</tr>
						<tr>
							<td colspan="2" class="delimiter"></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td style="height:100% !important; vertical-align:top !important;">
					<table id="add2menu_table" cellpadding="0" cellspacing="0" border="0" style="display: none; width:450px !important; margin-left:190px !important;">
						<tr>
							<td style="width:200px !important; text-align:right !important;"><?=GetMessage("FD_FILE_NAME")?></td>
							<td style="width:250px !important;" id="__bx_fd_file_name"></td>
						</tr>
						<tr>
							<td align="right"><?=GetMessage("FD_PAGE_TITLE")?>:</td>
							<td><input type="text" id="__bx_page_title2" value=""></input></td>
						</tr>
						<tr>
							<td align="right"><?=GetMessage("FD_MENU_TYPE")?></td>
							<td>
								<select id="__bx_fd_menutype" name="menutype">
								<?
									for($i=0; $i<count($arSelectOptions); $i++)
										echo $arSelectOptions[$i];
								?>
								</select>
							</td>
						</tr>
						<tr id="e0">
							<td style="vertical-align:top !important; text-align:right !important;"><?=GetMessage("FD_MENU_POINT")?></td>
							<td>
								<input type="radio" name="itemtype" id="__bx_fd_itemtype_n" value="n" checked> <label for="__bx_fd_itemtype_n"><?=GetMessage("FD_ADD_NEW")?></label><br>
								<input type="radio" name="itemtype" id="__bx_fd_itemtype_e" value="e"> <label for="__bx_fd_itemtype_e"><?=GetMessage("FD_ATTACH_2_EXISTENT")?></label>
							</td>
						</tr>
						<tr id="__bx_fd_e1">
							<td align="right"><?=GetMessage("FD_NEW_ITEM_NAME")?></td>
							<td><input type="text" name="newp" id="__bx_fd_newp" value=""></td>
						</tr>
						<tr id="__bx_fd_e2">
							<td align="right"><?=GetMessage("FD_ATTACH_BEFORE")?></td>
							<td>
								<select name="newppos" id="__bx_fd_newppos"><?
									$arItems = $arAllItems[$strSelected];
									for($i=0; $i<count($arItems); $i++):
										?><option value="<?echo $i+1?>"><?echo $arItems[$i]?></option><?
									endfor;
									?><option value="0" selected><?=GetMessage("FD_LAST_POINT")?></option>
								</select>
							</td>
						</tr>
						<tr id="__bx_fd_e3" style="display:none;">
							<td  align="right"><?=GetMessage("FD_ATTACH_2_ITEM")?></td>
							<td>
								<select name="menuitem" id="__bx_fd_menuitem"><?
									$arItems = $arAllItems[$strSelected];
									for($i=0; $i<count($arItems); $i++):
									?><option value="<?echo $i+1?>"><?echo $arItems[$i]?></option><?
									endfor;
								?></select>
							</td>
						</tr>

						<tr>
							<td>
							</td>
							<td>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td style="height:0% !important; padding: 0px 0px 9px 0px !important; text-align:right !important;">
					<input style="width:100px !important;" type="button" id="__bx_fd_submit_but2" value=""></input>
					<input style="width:100px !important;" type="button" onclick="oBXFileDialog.Close()" value="<?=GetMessage("FD_BUT_CANCEL");?>"></input>
				</td>
			</tr>
		</table>
		</div>
	</td>
</tr>
<tr>
	<td id="__bx_tab_cont" style="height:53px !important; vertical-align: top !important;"></td>
</tr>
</table>


</form>
<div style="position:absolute; visibility: hidden; top: 500px; left: 300px" id="__bx_get_real_size_cont"></div>
<?
function __MakeFolderArray($site, $path)
{
	global $USER;
	$arPath = Array($site, '/');
	?>
window._arPermissions = {};window._arPermissions['/'] = {new_folder : <?echo ($USER->CanDoFileOperation('fm_create_new_folder',$arPath) ? 'true' : 'false');?>,upload : <?echo ($USER->CanDoFileOperation('fm_upload_file',$arPath) ? 'true' : 'false');?>};arTreeItems = [];
<?
	if ($USER->CanDoFileOperation('fm_view_listing', Array($site, $path)))
	{
		$arFilter = array("MIN_PERMISSION" => "R");
		$arSort = array("name" => "asc");
		$arDirs = array();
		$arFiles = array();
		//$mode = ($startPath == $path) ? "DF" : "D";
		$mode = "DF";
		GetDirList(array($site, $path), $arDirs, $arFiles, $arFilter, $arSort, $mode);
		$ind = -1;

		foreach ($arDirs as $Dir)
		{
			$path_i = $path."/".$Dir["NAME"];
			if (!$USER->CanDoFileOperation('fm_view_listing',array($site, $path_i)))
				continue;
			$ind++;
			$arSubDirs = array();
			$arSubFiles = array();
			GetDirList(array($site, $path."/".$Dir["NAME"]), $arSubDirs, $arSubFiles, $arFilter, $arSort, "D");
			?>
arTreeItems[<?=$ind?>] = {name : '<?echo str_replace("'", "\\'", $Dir["NAME"]);?>', path : '<?=$path_i?>', subfolders: <? echo (count($arSubDirs) > 0) ? 'true' : 'false';?>};
<?
		}
	}
}


function __MakeMenuTypesArray($site, $path)
{
	global $USER;
	?>window._arMenuList = {};<?
	$armt = GetMenuTypes($site);
	$arAllItems = Array();
	$arSelectOptions = Array();
	$strSelected = "";

	foreach($armt as $key => $title)
	{
		$menuname = $path."/.".$key.".menu.php";
		if(!$USER->CanDoFileOperation('fm_view_file', Array($site, $menuname)))
			continue;
		$arItems = Array();

		if (!class_exists("CFileMan") || !method_exists(CFileMan,"GetMenuArray"))
			CModule::IncludeModule("fileman");
		if (!class_exists("CFileMan"))
			return;
		$res = CFileMan::GetMenuArray($DOC_ROOT.$menuname);
		$aMenuLinksTmp = $res["aMenuLinks"];

		$itemcnt = 0;
		for($j=0; $j<count($aMenuLinksTmp); $j++)
		{
			$aMenuLinksItem = $aMenuLinksTmp[$j];
			$arItems[] = htmlspecialchars($aMenuLinksItem[0]);
		}
		$arAllItems[$key] = $arItems;
		if($strSelected == "")
			$strSelected = $key;
		$arSelectOptions[] = '<option value="'.htmlspecialchars($key).'">'.htmlspecialchars($title." [".$key."]").'</option>';
	}

	$arTypes = array_keys($arAllItems);
	$strTypes="";
	$strItems="";
	for($i=0; $i<count($arTypes); $i++)
	{
		if($i>0)
		{
			$strTypes .= ",";
			$strItems .= ",";
		}
		$strTypes.="'".AddSlashes($arTypes[$i])."'";
		$arItems = $arAllItems[$arTypes[$i]];
		$strItems .= "Array(";
		for($j=0; $j<count($arItems); $j++)
		{
			if($j>0)$strItems .= ",";
			$strItems.="'".AddSlashes($arItems[$j])."'";
		}
		$strItems .= ")";
	}
?>
window._arMenuList['<?=$path?>'] = {types : [<?=$strTypes?>], items : [<?=$strItems?>]};
<?
	return $arSelectOptions;
}
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>