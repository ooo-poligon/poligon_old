<?
if(!defined("FROMDIALOGS") || FROMDIALOGS!==true)
	die("");

$site = CFileMan::__CheckSite($site);
$DOC_ROOT = CSite::GetSiteDocRoot($site);
if($site!==false)
{
	$res = CSite::GetByID($site);
	$arSite = $res->Fetch();
}

IncludeModuleLangFile(__FILE__);
?>
<script language="JavaScript">
<!--
	var cur_path = "<?echo $path?>";
	var site_url = "<?=$arSite["SITE_URL"]?>";
	var menutype = null;
	var menuitem = null;
	var newp = null;
	var newppos = null;
	var arAllFiles = Array();
//-->
</script>

<script language=javascript for=Ok event=onclick>
<!--
	if(itemtype[0].checked)
	{
		if(filename.value.length<=0)
		{
			alert("<?echo GetMessage("FILEMAN_D_INSIMG_ERROR")?>");
			return ;
		}
		OK(filename.value);
	}
	else if(itemtype[1].checked)
	{
		if(fileupload.frm.imgfile.value<=0)
		{
			alert("<?echo GetMessage("FILEMAN_D_INSIMG_ERROR")?>");
			return ;
		}
		fileupload.frm.submit();
	}
	else
	{
		var str_file = "/bitrix/admin/workflow_get_file.php?did=<?echo $DOCUMENT_ID?>&site=<?=$site?>&wf_path=&fname="+escape(wf_filename[wf_filename.selectedIndex].value);
		OK(str_file);
	}
// -->
</script>
<script language="JavaScript">
<!--
var file="";
function filelist_OnFileSelect(selected_file)
{
	file = selected_file;
	fileupload.frm.path.value = cur_path+"/"+file;
	filename.value = cur_path+"/"+file;
	preview.src=site_url+cur_path+"/"+file;
	hiddenimg.src=site_url+cur_path+"/"+file;
}

function filelist_OnLoad(path, files, site_name)
{
	menutype = null;
	menuitem = null;
	newp = null;
	newppos = null;

	document.cookie = "lopendir=" + escape(path) + ";";
	arAllFiles = files;
	cur_path = path;
	while(ddpath.length>0)ddpath.remove(0);

	var p = 1;
	var n = 0;
	var w = false;
	var allp = "";
	var oOption = document.createElement("OPTION");
	ddpath.options.add(oOption);
	oOption.innerText = "<?echo GetMessage("FILEMAN_D_INSIMG_ROOT")?>";
	oOption.value = "";
	if(path.indexOf("/")>-1)
	{
		while(true)
		{
			n++;
			p = path.indexOf("/", p)+1;
			allp = (p>0?path.substring(0, p-1):path);

			var namep = ""
			for(i=0; i<n; i++)
				namep = namep + ".&nbsp;.&nbsp;";
			namep = namep + allp.substring(allp.lastIndexOf("/")+1);

			oOption = document.createElement("OPTION");
			ddpath.options.add(oOption);
			oOption.innerHTML = namep;
			oOption.value = allp;
			if(p<1)break;
		}
	}
	ddpath.selectedIndex = ddpath.length-1;
	fileupload.frm.path.value = cur_path + "/" + file;
	filename.value = cur_path + "/" + file;
}

function OK(okpath)
{
	var arr = new Array();
	arr["path"] = okpath;
	arr["height"] = parseInt(imgheight.value);
	arr["width"] = parseInt(imgwidth.value);
	window.returnValue = arr;
	window.close();
}

function chitemtype()
{
	if(itemtype[0].checked)
	{
		load.style.display="inline";
		upload.style.display="none";
		wf.style.display="none";
	}
	else if(itemtype[1].checked)
	{
		upload.style.display="inline";
		load.style.display="none";
		wf.style.display="none";
	}
	else
	{
		WF_File();
		wf.style.display="inline";
		upload.style.display="none";
		load.style.display="none";
	}
}

function ShowSize(obj)
{
	imgwidth.value=obj.width;
	imgheight.value=obj.height;
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

	if(W>100)W=100;

	preview.width=W;
	preview.height=H;
}

<?if($WF_CONVERT=="Y"):?>
function WF_File()
{
	var str_file;
	var str_file = "/bitrix/admin/workflow_get_file.php?did=<?echo $DOCUMENT_ID?>&fname="+escape(wf_filename[wf_filename.selectedIndex].value);
	preview.src=str_file;
	hiddenimg.src=str_file;
}
<?endif?>

function NewFileName()
{
    var str_file;
    var str_file = fileupload.frm.imgfile.value;
    file = str_file.substr(str_file.lastIndexOf("\\")+1);
    fileupload.frm.path.value = cur_path+"/" + file;
	preview.src=fileupload.frm.imgfile.value;
	hiddenimg.src=fileupload.frm.imgfile.value;
}
//-->
</script>
<center>
<table cellspacing=0 cellpadding="4" width="100%">
<tr>
	<td width="0%" nowrap><?echo GetMessage("FILEMAN_D_INSIMG_FOLDER")?></td>
	<td width="100%" valign="center">
		<table cellpadding="0" cellspacing="0" border="0"><tr>
		<td>
		<select style="width:300px" name="ddpath" onchange="filelist.location='fileman_dialog.php?dtype=browse_files&site=<?=$site?>&lang=<?echo LANG?>&path='+escape(this.value)"></select>
		&nbsp;&nbsp;
		</td>
		<td valign="center">
			<table cellspacing="0" cellpadding="0">
				<tr>
					<td><a onClick="if(ddpath.selectedIndex!=0)filelist.location='fileman_dialog.php?dtype=browse_files&site=<?=$site?>&lang=<?echo LANG?>&path='+escape(ddpath[ddpath.selectedIndex-1].value)"><img id="up" src="/bitrix/images/fileman/htmledit/up.gif" border="0" width="20" height="19" class="btndef" alt="<?echo GetMessage("FILEMAN_D_INSIMG_UP")?>" onMouseOut="this.className='btndef';" onMouseOver="this.className='btn';" onMouseDown="this.className='btnDown';"  onMouseUp="this.className='btn';"></a></td>
					<td><a onClick="filelist.location=filelist.location+'&'"><img class="btndef" id="up" src="/bitrix/images/fileman/htmledit/refresh.gif" border="0" width="22" height="19" alt="<?echo GetMessage("FILEMAN_D_INSIMG_REFRESH")?>" onMouseOut="this.className='btndef';" onMouseOver="this.className='btn';" onMouseDown="this.className='btnDown';"  onMouseUp="this.className='btn';"></a></td>
				</tr>
			</table></td>
		</tr></table>
	</td>
</tr>
</table>
<img id=hiddenimg style="visibility:hidden; position: absolute; left:-10000; top: -10000px;" onerror="badimg = true;" onload="ShowSize(this)">
<table cellspacing=0 cellpadding="3" width="100%">
<tr>
	<td colspan="2">
		<table cellspacing=0 cellpadding="2" width="100%">
		<tr><td><iframe name="filelist" src="fileman_dialog.php?dtype=browse_files&lang=<?=LANGUAGE_ID?>&site=<?=$site?>&path=<?echo urlencode(isset($lopendir) ? $lopendir : $path)?>" style="width:100%"  height="180"></iframe></td>
		<td valign="top" width="100">
			<table cellpadding="0" cellspacing="0" border="1" width="120">
				<tr height="120"><td align="center" valign="middle"><img src="/bitrix/images/1.gif" width="100" name="preview"></td></tr>
				<tr><td>
				<table>
				<tr>
					<td align="right" width="0%" nowrap><font class="tablebodytext"><?echo GetMessage('FILEMAN_WIDTH')?>&nbsp;</font></td>
					<td width="100%"><input class="typeinput" type="text" size="3" name="imgwidth"></td>
				</tr>
				<tr>
					<td align="right" nowrap><font class="tablebodytext"><?echo GetMessage('FILEMAN_HEIGHT')?>&nbsp;</font></td>
					<td><input class="typeinput" type="text" size="3"  name="imgheight"></td>
				</tr>
				</table>
			</table>
		</td></tr></table>
	</td>
</tr>
<tr>
	<td colspan="2"><input type="radio" name="itemtype" value="n" onclick="chitemtype()" checked> <span onmousedown="itemtype[0].checked=true;chitemtype();"><?echo GetMessage("FILEMAN_D_INSIMG_OPEN_FROM_SITE")?></span><br></td>
</tr>
<tr>
	<td colspan="2"><input type="radio" name="itemtype" value="e" onclick="chitemtype()"> <span onmousedown="itemtype[1].checked=true;chitemtype();"><?echo GetMessage("FILEMAN_D_INSIMG_UPLOAD_TO_SERV")?></span></td>
</tr>
<?
$strWFList = "";
if ($WF_CONVERT=="Y" && intval($DOCUMENT_ID)>0 && CModule::IncludeModule("workflow")):
	$doc_files = CWorkflow::GetFileList($DOCUMENT_ID);

	while ($zr=$doc_files->GetNext())
	{
		$ftype = GetFileType($zr["FILENAME"]);
		if ($ftype=="IMAGE")
			$strWFList .= '<option value="'.$zr["FILENAME"].'">['.$zr["ID"].'] '.$zr["FILENAME"].' ('.$zr["FILESIZE"].')</option>';
			//$zr["TIMESTAMP_X"]
	}

	if(strlen($strWFList)>0):
		$strWFList = '<select name="wf_filename" onchange="WF_File();">'.$strWFList.'</select>';
	?>
		<tr>
			<td colspan="2"><input type="radio" name="itemtype" value="w" onclick="chitemtype()"> <span onmousedown="itemtype[2].checked=true;chitemtype();"><?echo GetMessage("FILEMAN_D_INSIMG_FROM_WF")?></span></td>
		</tr>
	<?
	endif;
endif;
?>


<tr height="68" valign="top">
<td width="0%">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
<td width="100%">
	<div id="load">
	<table width="100%" cellpadding="3" cellspacing="0" border="0">
		<tr>
			<td width="0%" nowrap><?echo GetMessage("FILEMAN_D_INSIMG_FILENAME")?></td>
			<td width="100%"><input type="text" style="width:100%" name="filename" value=""></td>
		</tr>
	</table>
	</div>
	<div id="wf" style="display:none">
	<?if(strlen($strWFList)>0):?>
	<table width="100%" cellpadding="3" cellspacing="0" border="0">
		<tr>
			<td width="0%" nowrap><?echo GetMessage("FILEMAN_D_INSIMG_FILENAME")?></td>
			<td width="100%"><?=$strWFList?></td>
		</tr>
	</table>
	<?endif?>
	</div>
	<div id="upload" style="display:none">
	<iframe name="fileupload" style="width:100%" height="60" FRAMEBORDER="0"></iframe>
	<script language="JavaScript">
	<!--
	function InitF()
	{
		fileupload.document.write('<HTML><HEAD>');
		fileupload.document.write('<STYLE TYPE="text/css">');
		fileupload.document.write('BODY {margin-left:0; margin-top:0; font-family:Arial; font-size:12px; background:menu}');
		fileupload.document.write('BUTTON {width:5em}');
		fileupload.document.write('TABLE  {font-family:Arial; font-size:12px}');
		fileupload.document.write('</STYLE>');
		fileupload.document.write('<meta http-equiv="Content-Type" content="text/html; charset=<?echo LANG_CHARSET?>">');
		fileupload.document.write('</HEAD>');
		fileupload.document.write('<BODY  scroll="NO">');
		fileupload.document.write('<table width="100%" cellpadding="3" cellspacing="0" border="0">');
		fileupload.document.write('<form action="fileman_dialog.php?dtype=insimg_upload&lang=<?=LANGUAGE_ID?>&site=<?=$site?>" id="frm" method="POST" enctype="multipart/form-data">');
		fileupload.document.write('<tr><td width="0%" nowrap><?echo GetMessage("FILEMAN_D_INSIMG_UPLOAD_FILEPATH")?></td>');
		fileupload.document.write('<td width="100%"><input type="file" class=typeinput style="width:100%" name="imgfile" value="" onchange="parent.NewFileName();"></td>');
		fileupload.document.write('</tr><tr>');
		fileupload.document.write('<td width="0%" nowrap><?echo GetMessage("FILEMAN_D_INSIMG_UPLOAD_FILENAME")?></td>');
		fileupload.document.write('<td width="100%"><input type="text" class=typeinput style="width:100%" name="path" value=""><input type="hidden" name="saveimg" value="Y"></td>');
		fileupload.document.write('</tr></form></table></body></html>');
	}
	InitF();
	//-->
	</script>
	</div>
</td></tr>
</table>
<BUTTON ID=Ok TYPE=SUBMIT>OK</BUTTON>&nbsp;<BUTTON ONCLICK="window.close();"><?=GetMessage("FILEMAN_CANCEL")?></BUTTON>
</center>
