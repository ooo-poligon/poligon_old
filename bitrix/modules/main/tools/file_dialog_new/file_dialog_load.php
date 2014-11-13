<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");

if(!$USER->CanDoOperation('fileman_view_file_structure'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
IncludeModuleLangFile(__FILE__);
?>

<?
function _BXMakeFolderArray($site, $path, $mode, $subfolders)
{
	global $APPLICATION,$USER;

	if (!file_exists(replaceQ(CSite::GetSiteDocRoot($site).$path)) || !$USER->CanDoFileOperation('fm_view_listing', array($site, $path)))
	{
		?>
		arSubTreeItems = false;
		arFiles = false;
		<?
	}
	else
	{
		$arFilter = array();
		$arSort = array("name" => "asc");
		$arDirs = array();
		$arFiles = array();
		GetDirList(array($site, $path), $arDirs, $arFiles, $arFilter, $arSort, $mode,false,true);
		$ind = -1;

		foreach ($arDirs as $Dir)
		{
			$path_i = $path."/".replaceQ($Dir["NAME"]);
			$arPath_i = Array($site, $path_i);
			if (!$USER->CanDoFileOperation('fm_view_listing',$arPath_i))
				continue;
			$ind++;
			?>
			arSubTreeItems[<?=$ind?>] = {
					name : '<?echo replaceQ($Dir["NAME"]);?>',
					path : '<?=$path_i?>',
					<?if ($subfolders):
						$arSubDirs = array();
						$arSubFiles = array();
						GetDirList(array($site, $path."/".$Dir["NAME"]), $arSubDirs, $arSubFiles, $arFilter, $arSort, "D");
						?>subfolders: <? echo (count($arSubDirs) > 0) ? 'true' : 'false';?>,
					<?endif;?>
					abs_path : '<?echo ($Dir["ABS_PATH"]);?>',
					permission : {
						f_delete : <?echo ($USER->CanDoFileOperation('fm_delete_folder',$arPath_i) ? 'true' : 'false');?>,
						rename : <?echo ($USER->CanDoFileOperation('fm_rename_folder',$arPath_i) ? 'true' : 'false');?>
					},
					date : '<?echo replaceQ($Dir["DATE"]);?>',
					timestamp : '<?echo replaceQ($Dir["TIMESTAMP"]);?>',
					size : '0'
				};
			<?
		}

		if ($mode == 'F' || $mode == 'DF')
		{
			$indF = -1;
			foreach ($arFiles as $File)
			{
				$path_i = replaceQ($File["PATH"]);
				$arPath_i = Array($site, $File["ABS_PATH"]);
				if (!$USER->CanDoFileOperation('fm_view_file', $arPath_i))
					continue;
				$indF++;
				?>
				arFiles['<?echo $indF;?>'] = {
					name : '<?echo replaceQ($File["NAME"]);?>',
					abs_path : '<?echo replaceQ($File["ABS_PATH"]);?>',
					permission : {
						f_delete : <?echo ($USER->CanDoFileOperation('fm_delete_file',$arPath_i) ? 'true' : 'false');?>,
						rename : <?echo ($USER->CanDoFileOperation('fm_rename_file',$arPath_i) ? 'true' : 'false');?>
					},
					date : '<?echo replaceQ($File["DATE"]);?>',
					timestamp : '<?echo replaceQ($File["TIMESTAMP"]);?>',
					size : '<?echo replaceQ($File["SIZE"]);?>'
				};
				<?
			}
		}
		else
		{?>
			arFiles = false;
		<?}
	}

	$arPath = array($site, $path);
	?>
	arParentPermission = {
		new_folder : <?echo ($USER->CanDoFileOperation('fm_create_new_folder',$arPath) ? 'true' : 'false');?>,
		upload : <?echo ($USER->CanDoFileOperation('fm_upload_file',$arPath) ? 'true' : 'false');?>
	};
	<?
}

function _BXMakeMenuTypesArray($site,$path)
{
	global $APPLICATION,$USER;
	$armt = GetMenuTypes($site);

	$arAllItems = Array();
	$strSelected = "";

	foreach($armt as $key => $title)
	{
		if(!$USER->CanDoFileOperation('fm_view_file',Array($site, $path."/.".$key.".menu.php")))
			continue;
		$arItems = Array();

		if (!class_exists("CFileMan") || !method_exists(CFileMan,"GetMenuArray"))
			CModule::IncludeModule("fileman");
		if (!class_exists("CFileMan"))
			return;

		$res = CFileMan::GetMenuArray(replaceQ(CSite::GetSiteDocRoot($site).$path."/.".$key.".menu.php"));
		$aMenuLinksTmp = $res["aMenuLinks"];
		if(!is_array($aMenuLinksTmp))
			$aMenuLinksTmp = Array();
		$itemcnt = 0;
		for($j = 0; $j < count($aMenuLinksTmp); $j++)
		{
			$aMenuLinksItem = $aMenuLinksTmp[$j];
			$arItems[] = htmlspecialchars($aMenuLinksItem[0]);
		}
		$arAllItems[$key] = $arItems;
		if($strSelected == "")
			$strSelected = $key;
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
		$strItems .= "[";
		for($j=0; $j<count($arItems); $j++)
		{
			if($j>0)$strItems .= ",";
			$strItems.="'".AddSlashes($arItems[$j])."'";
		}
		$strItems .= "]";
	}
	?>
	window._arMenuList['<?=$path?>'] =
	{
		types : [<?=$strTypes?>],
		items : [<?=$strItems?>]
	};
	<?
}

if (isset($_GET['path']))
{
	$path = $_GET['path'];
	$path = $APPLICATION->UnJSEscape($path);
	$site = $_GET['site'];
	$mode = $_GET['mode'];
	$subfolders = ($_GET['subfolders'] == 'Y') ? true : false;
	?>
	<script>
	var arSubTreeItems = [];
	var arFiles = [];
	var full = true;
	<?_BXMakeFolderArray($site,$path, $mode, $subfolders);?>
	window._arTreeItems['<?=$path?>'] = arSubTreeItems;
	window._arFiles['<?=$path?>'] = arFiles;
	window._arPermissions['<? echo ($path == '' ? '/' : $path);?>'] = arParentPermission;
	<?if (isset($_GET['get_menu']) && $_GET['get_menu'] == 'Y')
	{
		_BXMakeMenuTypesArray($site,$path);
	}?>
	</script>
	<?
}

function replaceQ($str)
{
	$str = str_replace("'", "\\'", $str);
	$str = str_replace("//", "/", $str);
	return $str;
}

?>
<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>
