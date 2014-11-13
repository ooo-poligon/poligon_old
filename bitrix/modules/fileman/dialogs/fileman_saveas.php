<?
if(!defined("FROMDIALOGS") || FROMDIALOGS!==true)
	die("");
IncludeModuleLangFile(__FILE__);

$site = CFileMan::__CheckSite($site);
$DOC_ROOT = CSite::GetSiteDocRoot($site);
?>
<script language="JavaScript">
<!--
	var cur_path = "<?echo $path?>";
	var menutype = null;
	var menuitem = null;
	var newp = null;
	var newppos = null;
	var arAllFiles = Array();

	function cl(ob)
	{
		var args = new Array();
		var arr = null;
		args["menutype"] = menutype;
		args["menuitem"] = menuitem;
		args["newp"] = newp;
		args["newppos"] = newppos;
		arr = showModalDialog("fileman_dialog.php?dtype=menuselect&lang=<?echo LANG?>&site=<?=$site?>&path="+escape(cur_path), args, "font-family:Verdana; font-size:12; dialogWidth:460px; dialogHeight:230px");
		if (arr != null)
		{
			menutype = arr["menutype"];
			menuitem = arr["menuitem"];
			newp = arr["newp"];
			newppos = arr["newppos"];
			menutext.value=arr["menutypename"] + "; "+"<?echo GetMessage("FILEMAN_D_SAVE_AS_ITEM")?>"+arr["menuitemname"]+"["+(arr["menuitem"]>0?arr["menuitem"]:arr["newppos"])+"] ";
		}
	}
//-->
</script>

<script language=javascript for=window event=onload>
<!--
	document.all("title").value = window.dialogArguments["title"];
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

	if(document.all("title").value.length<=0 && !confirm("<?echo GetMessage("FILEMAN_D_SAVE_AS_WITHOUT_TITLE")?>"))
		return ;

	for(i=0; i<arAllFiles.length; i++)
		if(arAllFiles[i]==filename.value)
			if(!confirm("<?echo GetMessage("FILEMAN_D_SAVE_AS_REWRITE")?>"))
				return;

	var arr = new Array();
	arr["path"] = cur_path;
	arr["title"] = document.all("title").value;
	arr["filename"] = filename.value;
	arr["menutype"] = menutype;
	if(menutype)
	{
		arr["menuitem"] = menuitem;
		if(menuitem<=0)
		{
			arr["newitemname"] = newp;
			arr["newitempos"] = newppos;
		}
	}

	window.returnValue = arr;
	window.close();
// -->
</script>
<center>
<table cellspacing=0 cellpadding="4" width="100%">
<tr>
	<td width="0%" nowrap><?echo GetMessage("FILEMAN_D_SAVE_AS_FOLDER")?></td>
	<td width="100%" valign="center">
		<table cellpadding="0" cellspacing="0" border="0"><tr>
		<td>
		<select style="width:300px" name="ddpath" onchange="filelist.location='fileman_dialog.php?dtype=browse_files&site=<?=$site?>&lang=<?echo LANG?>&path='+escape(this.value)"></select>
		&nbsp;&nbsp;
		</td>
		<td valign="center">
			<table cellspacing="0" cellpadding="0">
				<tr>
					<td><a onClick="if(ddpath.selectedIndex!=0)filelist.location='fileman_dialog.php?dtype=browse_files&lang=<?echo LANG?>&site=<?=$site?>&path='+escape(ddpath[ddpath.selectedIndex-1].value)"><img id="up" src="/bitrix/images/fileman/htmledit/up.gif" border="0" width="20" height="19" class="btndef" alt="<?echo GetMessage("FILEMAN_D_SAVE_AS_UP")?>" onMouseOut="this.className='btndef';" onMouseOver="this.className='btn';" onMouseDown="this.className='btnDown';"  onMouseUp="this.className='btn';"></a></td>
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
	menutype = null;
	menuitem = null;
	newp = null;
	newppos = null;
	menutext.value = "<?echo GetMessage("FILEMAN_D_SAVE_AS_NA")?>";

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
		<iframe name="filelist" src="fileman_dialog.php?dtype=browse_files&lang=<?echo LANG?>&site=<?=$site?>&path=<?echo $path?>" style="width:100%"  height="180"></iframe>
	</td>
</tr>
<tr>
	<td width="0%" nowrap><?echo GetMessage("FILEMAN_D_SAVE_AS_NAME")?></td>
	<td width="100%"><input type="text" style="width:100%" name="filename" value=""></td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("FILEMAN_D_SAVE_AS_PAGE_TITLE")?></td>
	<td><input type="text" style="width:100%" name="title" value=""></td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("FILEMAN_D_SAVE_AS_ADD_MENU")?></td>
	<td nowrap>
		<input type="text" id="menutext" style="background-color:menu; width:90%;" value="<?echo GetMessage("FILEMAN_D_SAVE_AS_NA")?>" readonly> <input type="button" value="..." onClick="cl()">
	</td>
</tr>
</table>
<br>
<BUTTON ID=Ok TYPE=SUBMIT>OK</BUTTON>&nbsp;<BUTTON ONCLICK="window.close();"><?=GetMessage("FILEMAN_CANCEL")?></BUTTON>
</center>
