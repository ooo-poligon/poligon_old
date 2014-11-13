<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");
$FM_RIGHT = $APPLICATION->GetGroupRight("fileman");
if($FM_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
IncludeModuleLangFile(__FILE__);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?echo LANG_CHARSET?>">
<style>
<?
//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/interface/admin_styles.css");
?>
BODY   {margin-left:20; margin-right:10; font-family:Arial; font-size:12px; background:#e2dfda;}
BODY IFRAME  {background:#FFFFFF;}
BUTTON {width:5em}
TABLE  {font-family:Arial; font-size:11px}
SELECT  {font-family:Arial; font-size:11px}
INPUT  {font-family:Arial; font-size:11px; height: 20px;}
P      {text-align:center}
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
		elseif(copy($_FILES["imagefile"]["tmp_name"], $abs_path."/".$file_name))
		{
			$bUploaded=true;
			@chmod($abs_path."/".$file_name, BX_FILE_PERMISSIONS);
		}
	}
}

if($bUploaded):
	?>
	<script language="JavaScript">
	<!--
	//alert(window.top.opener);
	window.top.opener.SetUrl('<?echo AddSlashes($path."/".$file_name)?>') ;
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
}

function NewFileName()
{
    var str_filename;
    var filename;
    var str_file = document.imageupload.imagefile.value;
    filename = str_file.substr(str_file.lastIndexOf("\\")+1);
    document.imageupload.newfilename.value = filename;
    if(imageupload.preview)
    {
		imageupload.preview.src=document.imageupload.imagefile.value;
		hiddenimg.src=document.imageupload.imagefile.value;
    }
}

function KeyPress(e)
{
	if(window.event)
		e = window.event;
	if(e.keyCode == 27)
		window.close();
	/*
	if(e.keyCode == 13)
		__OnSave();
	*/
}

function filelist_OnLoad(strDir)
{
	document.cookie = "lopendir=" + escape(strDir) + ";";// expires=Fri, 31 Dec 2009 23:59:59 GMT;";
	//window.opener.strPath=strDir;
	imageupload.url.value=strDir+"/";
	imageupload.path.value=strDir;
	imageupload.bSelect.disabled=true;
}

function filelist_OnFileSelect(strPath)
{
	imageupload.url.value=strPath;
	if(imageupload.preview)
		imageupload.preview.src=strPath;
	hiddenimg.src=strPath;
}

<?if ($WF_CONVERT=="Y"):?>
function WF_OnFileSelect(strPath, strTemp)
{
	var src;
	src = "/bitrix/admin/workflow_get_file.php?cash=Y&did=<?echo $DOCUMENT_ID?>&wf_path=<?echo $WF_PATH?>&fname="+strPath;
	imageupload.url.value=strPath;
	if(imageupload.preview)
		imageupload.preview.src=src;
	imageupload.bSelect.disabled=false;
	hiddenimg.src=src;
}
<?endif;?>


function SelectImage(fname)
{
	window.top.opener.SetUrl( fname ) ;
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


	if(imageupload.preview)
	{
		imageupload.preview.width=W;
		imageupload.preview.height=H;
	}
}

function _Chn()
{
	var r1 = document.getElementById("r1");
	var d1 = document.getElementById("d1");
	var d2 = document.getElementById("d2");
	if(r1.checked && d1.style.display == 'none')
	{
		d1.style.display = 'block';
		d2.style.display = 'none';
 	}
	else if(!r1.checked && d2.style.display == 'none')
	{
		d1.style.display = 'none';
		d2.style.display = 'block';
	}
}

function _OnOKB()
{
	var r1 = document.getElementById("r1");
	if(r1.checked)
		SelectImage(document.getElementById("url").value);
	else
		document.getElementById("imageupload").submit();
}
//-->
</script>
<?
$sDocTitle = GetMessage("FILEMAN_IMAGE_LOADING");
$title = "<title".">".$sDocTitle."</title>";
echo $title;
?>
<img id=hiddenimg style="visibility:hidden; position: absolute; left:-1000; top: -1000px;" onerror="badimg = true;" onload="ShowSize(this)">
<form target="_self" action="fileman_file_browser.php" method="post" enctype="multipart/form-data" name="imageupload" id="imageupload">
<input type="hidden" name="logical" value="<?=htmlspecialchars($logical)?>">
<?=bitrix_sessid_post()?>
<input type="hidden" name="saveimg" value="Y">
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<? if (strlen($site) > 0):?>
	<input type="hidden" name="site" value="<?=$site?>">
<? endif;?>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td width="0%">
<iframe name="filelist" src="fileman_file_list.php?path=<?echo urlencode(isset($lopendir) ? $lopendir : $path)?>&site=<?=urlencode($site)?>&lang=<?echo LANG?>&type=<?=urlencode($type)?>" width="450" height="250"></iframe>
</td>
<?if($type=="image"):?>
<td width="2%">&nbsp;</td>
<td valign="top" width="98%" align="center">
	<font class="tableheadtext"><?echo GetMessage('FILEMAN_PREVIEW')."<br>"?><hr size="1">
	<img src="/bitrix/images/1.gif" width="100" name="preview">
	<table cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td align="right"><font class="tablebodytext"><?echo GetMessage('FILEMAN_WIDTH')?>&nbsp;</font></td>
			<td><input class="typeinput" type="text" size="5" name="imgwidth" readonly></td>
		</tr>
		<tr>
			<td align="right"><font class="tablebodytext"><?echo GetMessage('FILEMAN_HEIGHT')?>&nbsp;</font></td>
			<td><input class="typeinput" type="text" size="5"  name="imgheight" readonly></td>
		</tr>
	</table>
	</font>
</td>
<?endif?>
</tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="3">
	<tr>
		<td align="left" width="0%" valign="top"><input type="radio" id=r1 name="r" checked onclick="_Chn()"></td><td width="100%"><font class="tableheadtext"><label for="r1"><?echo GetMessage("FILEMAN_FILE_BROWSER_S")?></label></font></td>
	</tr>
	<tr>
		<td align="left" valign="top"><input type="radio" id=r2 name="r" onclick="_Chn()"></td><td><font class="tableheadtext"><label for="r2"><?echo GetMessage("FILEMAN_FILE_BROWSER_L")?></label></font></td>
	</tr>
	<tr>
		<td colspan="2">
		<div id=d1>
			<table width="100%" border="0" cellspacing="0" cellpadding="1">
			<tr>
				<td width="150" align="right"><font class="tablebodytext"><?echo GetMessage("FILEMAN_FILE_BROWSER_FN")?></font></td>
				<td><input type="text" name="url" id="url" size="40" value=""></font></td>
			<tr>
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
			</table>
		</div>
		</td>
	</tr>

	<tr>
		<td colspan="2">
		<div id=d2 style="display:none">
			<table width="100%" border="0" cellspacing="0" cellpadding="1">
			<tr>
				<td align="right"><font class="tablebodytext"><?echo GetMessage('FILEMAN_FILE')?></font></td>
				<td><input type="file" name="imagefile" size="28" onChange="NewFileName();"><br></td>
			</tr>
			<tr>
				<td width="150" align="right"><font class="tablebodytext"><?echo GetMessage("FILEMAN_FILE_BROWSER_NEW_FN")?></font></td>
				<td><input type="text" name="newfilename" size="40" value=""></font></td>
			</tr>
			</table>
		</div>
		</td>
	<tr>
		<td colspan="2" nowrap align="center">&nbsp;<input type="hidden" name="path" value="<?echo $path?>"></td>
	</tr>
	<tr>
		<td colspan="2" nowrap align="center">&nbsp;<br></td>
	</tr>
	<tr>
		<td colspan="2" nowrap align="center">
			<input type="button" name="SAVE" value="<?echo GetMessage("FILEMAN_FILE_BROWSER_NEW_SAVE")?>" onclick="_OnOKB()">
			<input type="button" name="<?echo GetMessage('FILEMAN_CANCEL')?>" value="<?echo GetMessage('FILEMAN_CLOSE_WINDOW')?>" onClick="window.close();">
		</td>
	</tr>
</table>
</form>
<?endif;?>
</body>
</html>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php")
?>
