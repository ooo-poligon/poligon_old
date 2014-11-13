<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");
$FM_RIGHT = $APPLICATION->GetGroupRight("fileman");
if($FM_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?echo LANG_CHARSET?>">
<?echo '<link rel="stylesheet" type="text/css" href="/bitrix/themes/'.ADMIN_THEME_ID.'/compatible.css?'.SM_VERSION.'">'."\n";?>
<style>
<?
//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/interface/admin_styles.css");

if (strlen($APPLICATION->GetAdditionalCSS())>0)
{
	require($_SERVER["DOCUMENT_ROOT"].$APPLICATION->GetAdditionalCSS());
}
?>
</style>
</head>
<body link="#2B6DC1" vlink="#A1041B" alink="2B6DC1" onKeyPress="KeyPress()">
<?
$site = CFileMan::__CheckSite($site);
$DOC_ROOT = CSite::GetSiteDocRoot($site);

$path = Rel2Abs("/", $path);
$arParsedPath = CFileMan::ParsePath(Array($site, $path), true);
$abs_path = $DOC_ROOT.$path;
$bUploaded = false;
$file_name = "";
if($REQUEST_METHOD=="POST" && strlen($saveimg)>0 && check_bitrix_sessid())
{
	if($APPLICATION->GetFileAccessPermission(Array($site, $path))<"W")
    	$strWarning = GetMessage('FILEMAN_CAN_NOT_WRITE')."<br>";
	else
	{
		$file_name = CFileman::GetFileName($newfilename);
		if(strlen($file_name)<=0 || $file_name=="none")
			continue;

		if(!$USER->IsAdmin() && (in_array(CFileman::GetFileExtension($file_name), CFileMan::GetScriptFileExt()) || $file_name[0]=="."))
			$strWarning .= GetMessage("FILEMAN_UPLOAD_BAD_TYPE")."\n";
		elseif(file_exists($abs_path."/".$file_name))
	    	$strWarning = GetMessage("FILEMAN_FILE_EXIST")."<br>";
		else
		{
			if(copy($_FILES["imagefile"]["tmp_name"], $abs_path."/".$file_name))
			{
				$bUploaded=true;
				@chmod($abs_path."/".$file_name, BX_FILE_PERMISSIONS);
			}
		}
	}
}

if($bUploaded):
	?>
	<script language="JavaScript">
	<!--
	window.opener.strPath='<?echo $path?>';
	<?$size = getimagesize($abs_path."/".$file_name);?>
	opener.InsertImage("<?echo $path."/".$file_name?>", "<?echo $size[0]?>", "<?echo $size[1]?>");
	window.close();
	//-->
	</script>
	<?
else:
    ShowError($strWarning);
?>
<script>
<!--
window.focus();

function OnNameChange()
{
	if(imageupload.newfilename.value.length>0)
		imageupload.save.disabled=false;
	else
		imageupload.save.disabled=true;
}

function NewFileName()
{
    var str_filename;
    var filename;
    var str_file = document.imageupload.imagefile.value;
    filename = str_file.substr(str_file.lastIndexOf("\\")+1);
    document.imageupload.newfilename.value = filename;
	imageupload.preview.src=document.imageupload.imagefile.value;
	hiddenimg.src=document.imageupload.imagefile.value;
	OnNameChange();
}

function KeyPress()
{
	if(window.event.keyCode == 27)
		window.close();
}

function filelist_OnLoad(strDir)
{
	document.cookie = "lopendir=" + escape(strDir) + ";";// expires=Fri, 31 Dec 2009 23:59:59 GMT;";
	window.opener.strPath=strDir;
	imageupload.url.value=strDir+"/";
	imageupload.path.value=strDir;
	imageupload.bSelect.disabled=true;
}

function filelist_OnFileSelect(strPath)
{
	imageupload.url.value=strPath;
	imageupload.preview.src=strPath;
	imageupload.bSelect.disabled=false;
	hiddenimg.src=strPath;
}

<?if ($WF_CONVERT=="Y"):?>
function WF_OnFileSelect(strPath, strTemp)
{
	var src;
	src = "/bitrix/admin/workflow_get_file.php?cash=Y&did=<?echo $DOCUMENT_ID?>&wf_path=<?echo $WF_PATH?>&fname="+strPath;
	imageupload.url.value=strPath;
	imageupload.preview.src=src;
	imageupload.bSelect.disabled=false;
	hiddenimg.src=src;
}
<?endif;?>


function SelectImage(fname)
{
	opener.InsertImage(fname, imageupload.imgwidth.value, imageupload.imgheight.value);
	window.close();
}

function ShowSize(obj)
{
	imageupload.imgwidth.value=obj.width;
	imageupload.imgheight.value=obj.height;
	var W=obj.width, H=obj.height;
	if(W>100)
	{
		H=H*((100.0)/W);
		W=100;
	}

	if(H>100)
	{
		W=W*((100.0)/H);
		H=100;
	}


	imageupload.preview.width=W;
	imageupload.preview.height=H;
	//fs.innerHTML=Math.round(obj.fileSize/1024*1000)/1000;
	fs.innerHTML=Math.round(obj.fileSize);
}
//-->
</script>
<?
$sDocTitle = GetMessage("FILEMAN_IMAGE_LOADING");
$title = "<title".">".$sDocTitle."</title>";
echo $title;
?>
<img id=hiddenimg style="visibility:hidden; position: absolute; left:-1000; top: -1000px;" onerror="badimg = true;" onload="ShowSize(this)">
<form action="fileman_getimage.php" method="post" enctype="multipart/form-data" name="imageupload">
<input type="hidden" name="logical" value="<?=htmlspecialchars($logical)?>">
<?=bitrix_sessid_post()?>
<input type="hidden" name="saveimg" value="Y">
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td width="0%">
<iframe name="filelist" src="fileman_file_list.php?path=<?echo urlencode(isset($lopendir) ? $lopendir : $path)?>&site=<?=$site?>&lang=<?echo LANG?>" width="450" height="250"></iframe>
</td>
<td width="2%">&nbsp;</td>
<td valign="top" width="98%" align="center">
	<font class="tableheadtext"><?echo GetMessage('FILEMAN_PREVIEW')."<br>"?><hr size="1">
	<img src="/bitrix/images/1.gif" width="100" name="preview">
	<br><?echo GetMessage('FILEMAN_FILE_SIZE')?><span id="fs"></span>
	<table cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td align="right"><font class="tablebodytext"><?echo GetMessage('FILEMAN_WIDTH')?>&nbsp;</font></td>
			<td><input class="typeinput" type="text" size="3" name="imgwidth"></td>
		</tr>
		<tr>
			<td align="right"><font class="tablebodytext"><?echo GetMessage('FILEMAN_HEIGHT')?>&nbsp;</font></td>
			<td><input class="typeinput" type="text" size="3"  name="imgheight"></td>
		</tr>
	</table>
	</font>
</td>
</tr>
</table>
<?
if ($WF_CONVERT=="Y" && intval($DOCUMENT_ID)>0 && CModule::IncludeModule("workflow")):
	$doc_files = CWorkflow::GetFileList($DOCUMENT_ID);
	$doc_files->NavStart();
	if ($doc_files->SelectedRowsCount()>0):
?>
<br>
<table border="0" cellspacing="0" cellpadding="0" width="450">
	<tr>
		<td colspan="2" align="left"><font class="tableheadtext"><b><?echo GetMessage('FILEMAN_UPLOADED_FILES')?></b></font> </td>
	</tr>
	<tr>
		<td align="center" colspan="2" width="0%">
			<table border="0" cellspacing="0" cellpadding="0" class="tableborder" width="100%">
				<tr>
					<td>
						<table border="0" cellspacing="1" cellpadding="3">
							<tr>
								<td class="tablehead" align="center"><font class="tableheadtext">ID</font></td>
								<td class="tablehead" align="center" width="50%"><font class="tableheadtext"><?echo GetMessage("FILEMAN_FILENAME")?></font></td>
								<td class="tablehead" align="center"><font class="tableheadtext"><?echo GetMessage("FILEMAN_SIZE")?></font></td>
								<td class="tablehead" align="center"><font class="tableheadtext"><?echo GetMessage("FILEMAN_FILE_LOADED")?></font></td>
								<td class="tablehead" align="center" width="50%"><font class="tableheadtext"><?echo GetMessage("FILEMAN_UPLOADED_BY")?></font></td>
							</tr>
							<?
							while ($zr=$doc_files->GetNext()) :
								$ftype = GetFileType($zr["FILENAME"]);
								if ($ftype=="IMAGE") :
							?>
							<tr>
								<td class="tablebody"><font class="tablebodytext"><?=$zr["ID"]?></font></td>
								<td class="tablebody"><font class="tablebodytext"><a href="javascript:WF_OnFileSelect('<?=$zr["FILENAME"]?>')" ><?=$zr["FILENAME"]?></a></font></td>
								<td class="tablebody" align="right"><font class="tablebodytext"><?=$zr["FILESIZE"]?></font></td>
								<td class="tablebody" align="center" nowrap><font class="tablebodytext"><?=$zr["TIMESTAMP_X"]?></font></td>
								<td class="tablebody"><font class="tablebodytext">[<a target="_blank" class="tablebodylink" href="user_edit.php?ID=<?echo $zr["MODIFIED_BY"]?>&lang=<?=LANG?>"><?echo $zr["MODIFIED_BY"]?></a>]&nbsp;<?echo $zr["USER_NAME"]?></font></td>
							</tr>
							<?
								endif;
							endwhile;
							?>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<?
	endif;
endif;
?>
<br>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td colspan="2" align="left"><font class="tableheadtext"><b><?echo GetMessage('FILEMAN_SELECT_IMAGE')?></b></font></td>
	</tr>
	<tr>
		<td width="0%" align="right"><font class="tablebodytext">&nbsp;URL:&nbsp;</font></td>
		<td width="100%"><input class="typeinput" type="text" name="url" size="40" value=""><img src="/bitrix/images/1.gif" width="2" height="1" border=0 alt=""><input class="button" type="button" name="bSelect" onclick="SelectImage(imageupload.url.value)" value="<?echo GetMessage('FILEMAN_SELECT_IMAGE')?>"></font></td>
	</tr>
	<tr>
		<td colspan="2" nowrap align="center"></td>
	</tr>
	<tr>
		<td colspan="2" nowrap align="left"><font class="tableheadtext"><b><?echo GetMessage('FILEMAN_UPLOAD_IMAGE')?></b></font></td>
	</tr>
	<tr>
		<td nowrap align="right"><font class="tablebodytext">&nbsp;<?echo GetMessage('FILEMAN_FILE')?>&nbsp;</font></td>
		<td><input class="typeinput" type="file" name="imagefile" size="20" onChange="NewFileName();"><br></td>
	</tr>
	<tr>
		<td nowrap align="right"><font class="tablebodytext">&nbsp;<?echo GetMessage('FILEMAN_NEW_FILENAME')?>&nbsp;</font></td>
		<td>
		<input class="typeinput" type="text" name="newfilename" size="20" onchange="OnNameChange()">
		<input class="button" type="submit" name="save" value="<?echo GetMessage('FILEMAN_UPLOAD')?>" DISABLED></font></td>
	</tr>
	<tr>
		<td colspan="2" nowrap align="center"><input type="hidden" name="path" value="<?echo $path?>"></td>
	</tr>
	<tr>
		<td colspan="2" nowrap align="center"><br></td>
	</tr>
	<tr>
		<td colspan="2" nowrap align="center"><input class="button" type="button" name="<?echo GetMessage('FILEMAN_CANCEL')?>" value="<?echo GetMessage('FILEMAN_CLOSE_WINDOW')?>" onClick="window.close();"></td>
	</tr>
</table>
</form>
<?endif;?>
</body>
</html>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php")
?>
