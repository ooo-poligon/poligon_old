<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");
if(!$USER->CanDoOperation('fileman_upload_files'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
if(CModule::IncludeModule("compression"))
	CCompress::Disable2048Spaces();

IncludeModuleLangFile(__FILE__);
	
$lang = (isset($_GET['lang'])) ? $_GET['lang'] : '';
$site = (isset($_GET['site'])) ? $_GET['site'] : '';
$path = (isset($_POST['path'])) ? $_POST['path'] : '';
$path = Rel2Abs("/", $path);
$filename = (isset($_POST['filename'])) ? $_POST['filename'] : '';
$filename = $APPLICATION->UnJSEscape($filename);
$filename = urldecode($filename);
$path = urldecode($path);

$documentRoot = CSite::GetSiteDocRoot($site);
	
?>
<HTML>
<HEAD></HEAD>
<BODY id="__uploader" style="margin:0px; background-color:#F4F4F4; font-family:Verdana;">
<script>
function returnProperties(obj,level)
{
	try
	{
		if (level==undefined)
			level = 0;
		space = '';
		for (j=0; j<=level; j++)
			space += '  ';

		var result = "";
		for (i in obj)
		{
			if (typeof obj[i] == 'object')
				result += space+i + " = {\n" + returnProperties(obj[i],level+1) + ", \n}\n";
			else
				result += space+i + " = " + obj[i] + "; \n";
		}
		return result;
	}
	catch(e)
	{
		return 'returnProperties error...';
	}
}
rp = returnProperties;


setTimeout(function ()
	{
		window.oBXDialogControls = self.parent.window.oBXDialogControls;
		window.oBXDialogWindow = self.parent.window.oBXDialogWindow;
		window.oBXFileDialog = self.parent.window.oBXFileDialog;
		window._arFiles = self.parent.window._arFiles;
		window.oWaitWindow = self.parent.window.oWaitWindow;
		window.oWaitWindow.Hide();
	}, 50
);

function urlencode(s)
{
	return escape(s).replace(new RegExp('\\+','g'), '%2B');
}

function __bx_fd_onsubmit()
{
	var local_path = document.getElementById("__bx_fd_load_file").value;
	local_path = local_path.replace(/\\/ig,"/");
	var fileName = document.getElementById("__bx_fd_server_file_name").value;
	
	//1. CHECK: If file name is empty
	if (fileName == "")
	{
		alert('<?=GetMessage('FD_EMPTY_NAME')?>');
		return false;
	}
	
	//1.5  CHECK: If file name is valid
	var new_fileName = fileName.replace(/[^a-zA-Z0-9\s!#\$%&\(\)\[\]\{\}+\-\.;=@\^_\~]/i, '');
	if (fileName !== new_fileName)
	{
		alert('<?=GetMessage('FD_NAME_ERROR')?>');
		return false;
	}
	
	//2. CHECK: If file extension is valid
	arExt = false;
	try
	{
		if (!oBXFileDialog.oConfig.allowAllFiles)
			arExt = oBXDialogControls.Filter.arFilters[oBXDialogWindow.filter];
	}
	catch(e)
	{
		arExt = false;
	}

	if (arExt !== false)
	{
		if (typeof(arExt) == 'object' && arExt.length > 0)
		{
			var fileExt = (fileName.lastIndexOf('.') != -1) ? fileName.substr(fileName.lastIndexOf('.')+1) : "";
			var res = false;
			for (var _i = 0; _i < arExt.length; _i++)
			{
				if (arExt[_i] == fileExt)
				{
					res = true;
					break;
				}
			}
			if (!res)
			{
				alert('<?=GetMessage('FD_INCORRECT_EXT')?>');
				return false;
			}
		}
	}
	
	//3. CHECK: If such file already exists
	var path = oBXDialogControls.dirPath.Get();
	for (var p in window._arFiles[path])
	{
		if (window._arFiles[path][p].name == fileName)
		{
			if (!confirm("<?=GetMessage('FD_LOAD_EXIST_CONFIRM')?>"))
				return false;
			document.getElementById('__bx_fd_rewrite').value = 'Y';
		}
	}
	
	//4. Set file name in hidden input
	document.getElementById('__bx_fd_upload_fname').value = urlencode(fileName);
	//5. Set path in hidden input
	document.getElementById('__bx_fd_upload_path').value = urlencode(path);
	
	oWaitWindow.Show();
}

</script>

<?
function uofGetFileExtension($fileName)
{
	$fileName = trim($fileName, ". \r\n\t");
	$arFileName = explode(".", $fileName);
	$fileExt = strtolower($arFileName[count($arFileName)-1]);
	return $fileExt;
}

function CheckFileName($str)
{
	if (preg_match("/[^a-zA-Z0-9\s!#\$%&\(\)\[\]\{\}+\-\.;=@\^_\~]/i", $str))
		return GetMessage("FD_NAME_ERROR");
	return true;
}

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	if (isset($_FILES["load_file"])
		&& isset($_FILES["load_file"]["tmp_name"])
		&& strlen($_FILES["load_file"]["tmp_name"]) > 0
		&& strlen($_FILES["load_file"]["name"]) > 0)
	{
		if(is_uploaded_file($_FILES["load_file"]["tmp_name"]))
		{
			$strWarning = '';
			
			if(strlen($filename) == 0)
				$filename = $_FILES["load_file"]["name"];
				
			$pathto = Rel2Abs($path, $filename);
			$pathto = urldecode($pathto);
			
			if (strlen($filename) > 0 && ($mess = CheckFileName($filename)) !== true)
				$strWarning = $mess;
			
			$fn = basename($pathto);
			if($APPLICATION->GetFileAccessPermission(array($site, $pathto)) > "R" &&
				($USER->IsAdmin() || (!in_array(uofGetFileExtension($fn), GetScriptFileExt()) && substr($fn, 0, 1) != ".")) &&
				strlen($strWarning) == 0
			)
			{
				if(!file_exists($documentRoot.$pathto) || $_REQUEST["rewrite"] == "Y")
				{
					//************************** Quota **************************//
					$bQuota = true;
					if(COption::GetOptionInt("main", "disk_space") > 0)
					{
						$bQuota = false;
						$quota = new CDiskQuota();
						if ($quota->checkDiskQuota(array("FILE_SIZE"=>filesize($_FILES["load_file"]["tmp_name"]))))
							$bQuota = true;
					}
					//************************** Quota **************************//
					if ($bQuota)
					{
						copy($_FILES["load_file"]["tmp_name"], $documentRoot.$pathto);
						@chmod($documentRoot.$pathto, BX_FILE_PERMISSIONS);
						if(COption::GetOptionInt("main", "disk_space") > 0)
						{
							CDiskQuota::updateDiskQuota("file", filesize($documentRoot.$pathto), "copy");
						}
						?>
						<script>
						setTimeout(function ()
							{
							<?
							if (isset($_POST['upload_and_open']) && $_POST['upload_and_open'] == "Y")
							{
								?>
								//2: Submit dialog
								oBXDialogControls.filePath.Set("<?=$filename?>");
								oBXFileDialog.SubmitFileDialog();
								<?
							}
							else
							{
								?>
								//1: Reload dialog
								window.oBXDialogWindow.loadFolderContent(window.oBXDialogControls.dirPath.Get(),true);
								oBXDialogControls.filePath.Set("<?=$filename?>");
								<?
							}
							?>
							}, 50
						);
						</script>
						<?
					}
					else 
					{
						$strWarning = $quota->LAST_ERROR;
					}
				}
				else
				{
					$strWarning = GetMessage("FD_LOAD_EXIST_ALERT");
				}
			}
			elseif (strlen($strWarning) == 0)
			{
				$strWarning = GetMessage("FD_LOAD_DENY_ALERT");
			}
			
			if (strlen($strWarning) > 0)
			{
				?><script>alert('<?=$strWarning?>');</script><?
			}
		}
	}
}
?>
<form name="frmLoad" action="file_dialog_upload.php?lang=<?=$lang?>&site=<?=$site?>" onsubmit="return __bx_fd_onsubmit();" method="post" enctype="multipart/form-data">
	<table style="width: 540px; height: 123px; font-size:70%">
		<tr height="0%">
			<td style="width:40%;" align="left">
				<?=GetMessage('FD_LOAD_FILE')?>:
			</td>
			<td style="width:60%; padding-top: 0px;" valign="top" align="left">
				<input id="__bx_fd_load_file" size="45" type="file" name="load_file">
			</td>
		</tr>
		<tr height="0%">
			<td style="width:40%;" align="left">
				<?=GetMessage("FD_FILE_NAME_ON_SERVER");?>
			</td>
			<td style="width:60%;" align="left">
				<input id="__bx_fd_server_file_name" style="width:100%;" type="text" name="load_file">
			</td>
		</tr>
		<tr height="100%">
			<td style="width:100%;" valign="top" align="left" colspan="2">
				<table style="font-size:100%"><tr>
				<td><input id="_bx_fd_upload_and_open" value="Y" type="checkbox" name="upload_and_open" checked="checked"></td>
				<td><label for="_bx_fd_upload_and_open"> <?=GetMessage("FD_UPLOAD_AND_OPEN");?></label></td>
				</tr></table>
			</td>
		</tr>
		<tr height="0%">
			<td style="width:100%; padding:0px 8px 5px 0px" valign="bottom" align="right" colSpan="2">
				<input  type="submit" id="__bx_fd_upload_but" value="<?=GetMessage("FD_BUT_LOAD");?>"></input>
				<input style="width:100px;" type="button" onclick="oBXFileDialog.Close()" value="<?=GetMessage("FD_BUT_CANCEL");?>"></input>
			</td>
		</tr>
	</table>
	<input type="hidden" name="MAX_FILE_SIZE" value="1000000000">
	<input type="hidden" name="lang" value="<?=htmlspecialchars($lang)?>">
	<input type="hidden" name="site" value="<?=htmlspecialchars($site)?>">
	<input id="__bx_fd_rewrite" type="hidden" name="rewrite" value="N">
	<input id="__bx_fd_upload_path" type="hidden" name="path" value="">
	<input id="__bx_fd_upload_fname" type="hidden" name="filename" value="">
</form>
<script>
document.getElementById("__bx_fd_load_file").onchange = function()
{
	var path = this.value.replace(/\\/ig,"/");
	document.getElementById("__bx_fd_server_file_name").value = path.substr(path.lastIndexOf("/")+1);
};
</script>
</BODY>
</HTML>