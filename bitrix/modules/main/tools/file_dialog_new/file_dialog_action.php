<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");

if(!$USER->CanDoOperation('fileman_view_file_structure'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
IncludeModuleLangFile(__FILE__);

function __FD_NewDir($path, $dirname, $site)
{
	global $USER;
	$path = Rel2Abs("/", $path);
	$arPath = Array($site, $path);
	$DOC_ROOT = CSite::GetSiteDocRoot($site);
	$abs_path = $DOC_ROOT.$path;
	$dirname = str_replace("/", "_", $dirname);

	//Check access to folder
	if (!$USER->CanDoFileOperation('fm_create_new_folder', $arPath))
		$strWarning = GetMessage("ACCESS_DENIED");
	else if(!is_dir($abs_path))
		$strWarning = GetMessage("FD_FOLDER_NOT_FOUND", array('#PATH#' => addslashes(htmlspecialchars($path))));
	else
	{
		if (strlen($dirname) > 0 && ($mess = CheckFileName($dirname)) !== true)
			$strWarning = $mess;
		else if(strlen($dirname) <= 0)
			$strWarning = GetMessage("FD_NEWFOLDER_ENTER_NAME");
		else
		{
			$pathto = Rel2Abs($path, $dirname);
			if(file_exists($DOC_ROOT.$pathto))
				$strWarning = GetMessage("FD_NEWFOLDER_EXISTS");
			else
				$strWarning = CFileMan::CreateDir(Array($site, $pathto));
		}
	}

	if (strlen($strWarning) > 0)
		echo 'window.action_warning = "'._replace($strWarning).'";';
	else
		echo 'window.action_status = true;';
}


function __FD_Remove($path, $site)
{
	global $USER;
	$path = Rel2Abs("/", $path);
	$arPath = Array($site, $path);
	$DOC_ROOT = CSite::GetSiteDocRoot($site);
	$abs_path = $DOC_ROOT.$path;
	$type = false;
	if (is_dir($abs_path))
		$type = 'folder';
	if (is_file($abs_path))
		$type = 'file';

	//Check access to folder or file
	if (!$type)
		$strWarning = GetMessage("FD_ELEMENT_NOT_FOUND", array('#PATH#' => addslashes(htmlspecialchars($path))));
	elseif (!$USER->CanDoFileOperation('fm_delete_'.$type, $arPath))
		$strWarning = GetMessage("ACCESS_DENIED");
	else
		$strWarning = CFileMan::DeleteEx($path);

	if (strlen($strWarning) > 0)
		echo 'window.action_warning = "'._replace($strWarning).'";';
	else
		echo 'window.action_status = true;';
}


function __FD_Rename($path, $old_name, $name)
{
	global $USER;
	$name = str_replace("/", "_", $name);
	$path = Rel2Abs("/", $path);
	$DOC_ROOT = CSite::GetSiteDocRoot($site);
	$abs_path = $DOC_ROOT.$path;
	$old_path = Rel2Abs($path, $old_name);
	$new_path = Rel2Abs($path, $name);
	$old_abs_path = $DOC_ROOT.$old_path;
	$new_abs_path = $DOC_ROOT.$new_path;
	$arPath1 = Array($site, $old_path);
	$arPath2 = Array($site, $new_path);
	

	$type = false;
	if (is_dir($old_abs_path))
		$type = 'folder';
	if (is_file($old_abs_path))
		$type = 'file';

	$ext1 = GetFileExtension($old_name);
	$ext2 = GetFileExtension($name);
	$ScriptExt = GetScriptFileExt();

	if (
		$type == 'file' &&
		!$USER->CanDoOperation('edit_php') &&
		(
			substr($old_name, 0, 1) == "." 
			||
			substr($name, 0, 1) == "." 
			||
			(
				in_array($ext1, $ScriptExt) &&
				!in_array($ext2, $ScriptExt)
			)
			||
			(
				in_array($ext2, $ScriptExt) &&
				!in_array($ext1, $ScriptExt)
			)
		)
	)
	{
		$strWarning = GetMessage("ACCESS_DENIED");
	}
	elseif (!$type)
		$strWarning = GetMessage("FD_ELEMENT_NOT_FOUND", array('#PATH#' => addslashes(htmlspecialchars($path))));
	elseif (!$USER->CanDoFileOperation('fm_rename_'.$type,$arPath1) || !$USER->CanDoFileOperation('fm_rename_'.$type,$arPath2))
		$strWarning = GetMessage("ACCESS_DENIED");
	else
	{
		if (strlen($name) > 0 && ($mess = CheckFileName($name)) !== true)
			$strWarning = $mess;
		else if(strlen($name) <= 0)
			$strWarning = GetMessage("FD_ELEMENT_ENTER_NAME");
		else
		{
			if(file_exists($DOC_ROOT.$new_path))
				$strWarning = GetMessage("FD_ELEMENT_EXISTS");
			elseif(!rename($old_abs_path, $new_abs_path))
				$strWarning = GetMessage("FD_RENAME_ERROR");
		}
	}

	if (strlen($strWarning) > 0)
		echo 'window.action_warning = "'._replace($strWarning).'";';
	else
		echo 'window.action_status = true;';
}


if (isset($_GET['path']) && isset($_GET['action']))
{
	$path = $APPLICATION->UnJSEscape($_GET['path']);
	$path = Rel2Abs("/", $path);
	$site = $_GET['site'];
	$action = $_GET['action'];

	if($action == 'new_dir')
	{
		$name = $APPLICATION->UnJSEscape($_GET['name']);
		?>
		<script>
		window.action_status = false;
		<?__FD_NewDir($path,$name,$site);?>
		</script>
		<?
	}
	elseif($action == 'remove')
	{
		?>
		<script>
		window.action_status = false;
		<?__FD_Remove($path,$site);?>
		</script>
		<?
	}
	elseif($action == 'rename')
	{
		$name = $APPLICATION->UnJSEscape($_GET['name']);
		$old_name = $APPLICATION->UnJSEscape($_GET['old_name']);
		?>
		<script>
		window.action_status = false;
		<?__FD_Rename($path, $old_name, $name);?>
		</script>
		<?
	}
}

function replaceQ($str)
{
	$str = str_replace("'", "\\'", $str);
	$str = str_replace("//", "/", $str);
	return $str;
}

function _replace($str)
{
	$str = str_replace("\n", " ", $str);
	$str = addslashes($str);
	return $str;
}

function CheckFileName($str)
{
	if (preg_match("/[^a-zA-Z0-9\s!#\$%&\(\)\[\]\{\}+\-\.;=@\^_\~]/i", $str))
		return GetMessage("FD_NAME_ERROR");
	return true;
}

?>
<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>
