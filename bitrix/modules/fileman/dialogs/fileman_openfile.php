<?
if(!defined("FROMDIALOGS") || FROMDIALOGS!==true)
	die("");
IncludeModuleLangFile(__FILE__);

$site = CFileMan::__CheckSite($site);
$DOC_ROOT = CSite::GetSiteDocRoot($site);

if(strlen($path)>0)
{
	$path = Rel2Abs("/", $path);
	$arDirPath = explode("/", $path);
	$path = "";
	for($i=0; $i<count($arDirPath); $i++)
	{
		if(strlen($arDirPath[$i])<=0)
			continue;
		if(!is_dir($DOC_ROOT.$path."/".$arDirPath[$i]))
			break;
		$path .= "/".$arDirPath[$i];
	}
}
?>
<script language="JavaScript">
<!--
	var cur_path = "<?echo $path?>";
	var arAllFiles = Array();
//-->
</script>

<script language=javascript for=window event=onload>
<!--
	document.all("filename").value = window.dialogArguments["filename"];
// -->
</script>
<script language=javascript for=Ok event=onclick>
<!--
	if(filename.value.length<=0)
	{
		alert("<?echo GetMessage("FILEMAN_D_SAVE_AS_ERROR")?>");
		return ;
	}

	var arr = new Array();
	arr["filename"] = cur_path+"/"+filename.value;

	window.returnValue = arr;
	window.close();
// -->
</script>
<center>
<table cellspacing=0 cellpadding="4" width="100%">
<tr>
	<td width="0%" nowrap><?echo GetMessage("FILEMAN_OPENFILE_FOLDER")?></td>
	<td width="100%" valign="center">
		<table cellpadding="0" cellspacing="0" border="0"><tr>
		<td>
		<select style="width:300px" name="ddpath" onchange="filelist.location='fileman_dialog.php?dtype=browse_files&lang=<?echo LANG?>&site=<?=$site?>&path='+escape(this.value)"></select>
		&nbsp;&nbsp;
		</td>
		<td valign="center">
			<table cellspacing="0" cellpadding="0">
				<tr>
					<td><a onClick="if(ddpath.selectedIndex!=0)filelist.location='fileman_dialog.php?dtype=browse_files&site=<?=$site?>&lang=<?echo LANG?>&path='+escape(ddpath[ddpath.selectedIndex-1].value)"><img id="up" src="/bitrix/images/fileman/htmledit/up.gif" border="0" width="20" height="19" class="btndef" alt="<?echo GetMessage("FILEMAN_D_SAVE_AS_UP")?>" onMouseOut="this.className='btndef';" onMouseOver="this.className='btn';" onMouseDown="this.className='btnDown';"  onMouseUp="this.className='btn';"></a></td>
					<td><a onClick="filelist.location=filelist.location+'&'"><img class="btndef" id="up" src="/bitrix/images/fileman/htmledit/refresh.gif" border="0" width="22" height="19" alt="<?echo GetMessage("FILEMAN_D_SAVE_AS_REFRESH")?>" onMouseOut="this.className='btndef';" onMouseOver="this.className='btn';" onMouseDown="this.className='btnDown';"  onMouseUp="this.className='btn';"></a></td>
				</tr>
			</table></td>
		</tr></table>
	</td>
</tr>
</table>
<script language="JavaScript">
<!--
function filelist_OnFileSelect(file)
{
	filename.value = file;
}

function filelist_OnLoad(path, files)
{
	arAllFiles = files;
	cur_path = path;
	while(ddpath.length>0)ddpath.remove(0);

	var p = 1;
	var n = 0;
	var w = false;
	var allp = "";
	var oOption = document.createElement("OPTION");
	ddpath.options.add(oOption);
	oOption.innerText = "<?echo GetMessage("FILEMAN_D_SAVE_AS_ROOT")?>";
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
}
//-->
</script>
<table cellspacing=0 cellpadding="3" width="100%">
<tr>
	<td colspan="2">
		<iframe name="filelist" src="fileman_dialog.php?dtype=browse_files&site=<?=$site?>&lang=<?echo LANGUAGE_ID?>&path=<?echo $path?>" style="width:100%"  height="180"></iframe>
	</td>
</tr>
<tr>
	<td width="0%" nowrap><?echo GetMessage("FILEMAN_D_SAVE_AS_NAME")?></td>
	<td width="100%"><input type="text" style="width:100%" name="filename" value=""></td>
</tr>
</table>
<br>
<BUTTON ID=Ok TYPE=SUBMIT>OK</BUTTON>&nbsp;<BUTTON ONCLICK="window.close();"><?=GetMessage("FILEMAN_CANCEL")?></BUTTON>
</center>
