<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");

$FM_RIGHT = $APPLICATION->GetGroupRight("fileman");
if($FM_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
IncludeModuleLangFile(__FILE__);

define("FROMDIALOGS", true);
?>


<form id="form1" name="form1" onsubmit="return false;">

<script>
function __OnLoad()
{
	try
	{
		OnLoad();
		pObj.floatDiv.focus();
	}
	catch (e)
	{
	}
}
var iNoOnSelectionChange = 1;
var iNoOnChange = 2;

function __OnSave()
{	
	var r = 0;
	if(OnSave)
		r = OnSave();

	if((r & 'NoOnSelectionChange') != 0)
		pObj.pMainObj.OnEvent("OnSelectionChange", ["always"]);

	pObj.Close();
}
</script>

<script type="text/javascript" src="/bitrix/admin/fileman_js.php?lang=<?=LANGUAGE_ID?>"></script>
<input type="hidden" name="logical" value="<?=htmlspecialchars($logical)?>">
<table height="100%" width="100%" border = "0"><tr><td valign="top">

<?if($name=="anchor"):?>

<script>
var pElement = null;
function OnLoad()
{
	pElement = pObj.pMainObj.GetSelectionObject();
	if(!pElement || !pElement.tagName)
	{
		pObj.Close();
		return;
	}

	oDialogTitle.innerHTML = '<?=GetMessage("FILEMAN_ED_LINK_TITLE")?>';

	var el = document.getElementById("anchor_value");
	if(pElement && pElement.getAttribute("__bxtagname")=="anchor")
	{
		var val = BXUnSerialize(pElement.getAttribute("__bxcontainer"));
		el.value = val.name;
	}
	else
		el.value = "";
	el.focus();
}

function OnSave()
{
	BXSelectRange(oPrevRange,pObj.pMainObj.pEditorDocument,pObj.pMainObj.pEditorWindow);
	pElement = pObj.pMainObj.GetSelectionObject();
	pObj.pMainObj.bSkipChanges = true;
	if(pElement && pElement.getAttribute("__bxtagname")=="anchor")
	{
		if(document.getElementById("anchor_value").value.length<=0)
			pObj.pMainObj.executeCommand('Delete');
		else
			pElement.setAttribute("__bxcontainer", BXSerialize({"name":document.getElementById("anchor_value").value}));
	}
	else
	{
		if(document.getElementById("anchor_value").value.length>0)
		{
			var tmp_id = Math.random().toString().substring(2);
			pObj.pMainObj.insertHTML('<img id="'+tmp_id+'" src="/bitrix/images/fileman/htmledit2/anchor.gif" width="20" height="20" __bxtagname="anchor" __bxcontainer="'+bxhtmlspecialchars(BXSerialize({'name':document.getElementById("anchor_value").value}))+'"/>');
			var pComponent = pObj.pMainObj.pEditorDocument.getElementById(tmp_id);
			pComponent.removeAttribute('id');
			if(pObj.pMainObj.pEditorWindow.getSelection)
				pObj.pMainObj.pEditorWindow.getSelection().selectAllChildren(pComponent);
		}
	}
	pObj.pMainObj.bSkipChanges = false;
	pObj.pMainObj.OnChange("anchor");
}
</script>
<div style="padding: 5px;">
<?echo GetMessage("FILEMAN_ED_ANCHOR_NAME")?>
<input type="text" size="30" value="" id="anchor_value" style="width: 180px;">
</div>


<?elseif($name=="link"):?>

<script>
var pElement = null;
function OnLoad()
{
	_Ch();
	pElement = BXFindParentByTagName(pObj.pMainObj.GetSelectionObject(), 'A');

	var arStFilter = ['A', 'DEFAULT'], i;
	var elStyles = document.getElementById("classname");
	var oOption = new Option("", "", false, false);
	elStyles.options.add(oOption);
	var arStyles;
	for(i=0; i<arStFilter.length; i++)
	{
		arStyles = pObj.pMainObj.oStyles.GetStyles(arStFilter[i]);
		for(var j=0; j<arStyles.length; j++)
		{
			if(arStyles[j].className.length<=0)
				continue;
			oOption = new Option(arStyles[j].className, arStyles[j].className, false, false);
			elStyles.options.add(oOption);
		}
	}

	var arAnchs = [], anc;
	var arImgs = pObj.pMainObj.pEditorDocument.getElementsByTagName('IMG');
	for(i=0; i<arImgs.length; i++)
	{
		if(arImgs[i].getAttribute("__bxtagname") && arImgs[i].getAttribute("__bxtagname")=="anchor")
		{
			anc = BXUnSerialize(arImgs[i].getAttribute("__bxcontainer"));
			arAnchs.push(anc.name);
		}
	}

	el = document.getElementById('url3');
	for(i=0; i<arAnchs.length; i++)
	{
		oOption = new Option(arAnchs[i], '#'+arAnchs[i], false, false);
		el.options.add(oOption);
	}
	
	var tip = 1;
	if(pElement)
		oDialogTitle.innerHTML = '<?=GetMessage("FILEMAN_ED_LE_TITLE")?>';
	else
		oDialogTitle.innerHTML = '<?=GetMessage("FILEMAN_ED_LN_TITLE")?>';

	
		
	if(pElement)
	{
		if(pElement.tagName.toLowerCase() == 'a')
		{
			oPrevRange = pObj.pMainObj.SelectElement(pElement);
			var href = pElement.getAttribute("href", 2), el, tip;
			if(href.substring(0, 7).toLowerCase() == 'mailto:')
			{
				tip = 4;
				document.getElementById("url4").value = href.substring(7);
			}
			else if(href.substr(0, 1) == '#')
			{
				tip = 3;
				el = document.getElementById("url3");
				var bF = false;
				for(i=0; i<el.options.length; i++)
				{
					if(el.options[i].value == href)
					{
						el.selectedIndex = i;
						bF = true;
						break;
					}
				}

				 if(!bF)
				 {
				 	tip = 1;
					document.getElementById("url1").value = href;
				 }
			}
			else if(href.substr(0, 20) == '/bitrix/redirect.php')
			{
				tip = 2;
				document.getElementById("fixstat").checked = true;
				var sParams = href.substring(20);
				__ExtrParam = function (p, s)
				{
					var pos = s.indexOf(p+'=');
					if(pos<0)
						return '';
					var pos2 = s.indexOf('&', pos+p.length+1);
					if(pos2<0)
						s = s.substring(pos+p.length+1);
					else
						s = s.substr(pos+p.length+1, pos2 - pos - 1 - p.length);
					return unescape(s);
				};

				document.getElementById("event1").value = __ExtrParam('event1', sParams);
				document.getElementById("event2").value = __ExtrParam('event2', sParams);
				document.getElementById("event3").value = __ExtrParam('event3', sParams);
				var url2 = __ExtrParam('goto', sParams);
				if(url2.substr(0, 7)=='http://')
				{
					document.getElementById("url2").value = url2.substring(7);
					document.getElementById("url_type").selectedIndex = 0;
				}
				else if(url2.substr(0, 6)=='ftp://')
				{
					document.getElementById("url2").value = url2.substring(6);
					document.getElementById("url_type").selectedIndex = 1;
				}
				else if(url2.substr(0, 8)=='https://')
				{
					document.getElementById("url2").value = url2.substring(8);
					document.getElementById("url_type").selectedIndex = 2;
				}
				else
				{
					document.getElementById("url2").value = url2;
					document.getElementById("url_type").selectedIndex = 3;
				}
			}
			else if(href.substring(0, 7) == 'http://')
			{
				tip = 2;
				document.getElementById("url2").value = href.substring(7);
				document.getElementById("url_type").selectedIndex = 0;
			}
			else if(href.substring(0, 6) == 'ftp://')
			{
				tip = 2;
				document.getElementById("url2").value = href.substring(6);
				document.getElementById("url_type").selectedIndex = 1;
			}
			else if(href.substring(0, 8) == 'https://')
			{
				tip = 2;
				document.getElementById("url2").value = href.substring(8);
				document.getElementById("url_type").selectedIndex = 2;
			}
			else
				document.getElementById("url1").value = href;

			if(pElement.className)
			{
				el = document.getElementById("classname");
				for(i=0; i<el.length; i++)
				{
					if(el[i].value==pElement.className)
					{
						el.selectedIndex = i;
						break;
					}
				}
			}

			if(pElement.target)
			{
				el = document.getElementById("target");
				var el2 = document.getElementById("targ_list");
				switch(pElement.target.toLowerCase())
				{
				case '_blank':
					el2.selectedIndex = 1;
					break;
				case '_parent':
					el2.selectedIndex = 2;
					break;
				case '_self':
					el2.selectedIndex = 3;
					break;
				case '_top':
					el2.selectedIndex = 4;
					break;
				}
				_ChTargL();
				el.value = pElement.target;
			}

			document.getElementById("id").value = pElement.id;
			document.getElementById("BXEditorDialog_title").value = pElement.title;

			el = document.getElementById('type');
			el.selectedIndex = tip-1;
		}
	}

	_Ch();

	if(el = document.getElementById('url'+tip))
		el.focus();
}

function OnSave()
{
	var href='', target='';

	switch(document.getElementById('type').selectedIndex)
	{
	case 0:
		href = document.getElementById('url1').value;
		break;
	case 1:
		href = document.getElementById('url2').value;
		if(document.getElementById("url_type").value.length>0)
			href = document.getElementById("url_type").value + href;
		if(document.getElementById("fixstat").checked)
			href = '/bitrix/redirect.php?event1='+escape(document.getElementById("event1").value)+'&event2='+escape(document.getElementById("event2").value)+'&event3='+escape(document.getElementById("event3").value)+'&goto='+escape(href);
		break;
	case 2:
		href = document.getElementById('url3').value;
		break;
	case 3:
		if(document.getElementById('url4').value.length>0)
			href = 'mailto:'+document.getElementById('url4').value;
		break;
	}
	
	BXSelectRange(oPrevRange,pObj.pMainObj.pEditorDocument,pObj.pMainObj.pEditorWindow);	
	pObj.pMainObj.bSkipChanges = true;
	//pObj.pMainObj.pEditorDocument.execCommand('Unlink', false, '');
	if(href.length>0)
	{
		var link = false, sRand = '#'+Math.random().toString().substring(2);
		//BXSelectRange(oPrevRange,pObj.pMainObj.pEditorDocument,pObj.pMainObj.pEditorWindow);	
		pObj.pMainObj.pEditorDocument.execCommand('CreateLink', false, sRand);

		if(document.evaluate)
			link = document.evaluate("//a[@href='"+sRand+"']", pObj.pMainObj.pEditorDocument.body, null, 9, null).singleNodeValue;
		else
		{
			var arLinks = pObj.pMainObj.pEditorDocument.getElementsByTagName('A');
			for(var i=0;i<arLinks.length;i++)
			{
				
				if(arLinks[i].getAttribute('href', 2) == sRand)
				{
					link = arLinks[i];
					break;
				}
			}
		}
		
		if(link)
		{
			link.href = href;
			SAttr(link, '__bxhref', href);
			SAttr(link, 'target', document.getElementById("target").value);
			SAttr(link, 'id', document.getElementById("id").value);
			SAttr(link, 'title', document.getElementById("BXEditorDialog_title").value);
			SAttr(link, 'className',  document.getElementById("classname").value);
		}
	}
	pObj.pMainObj.bSkipChanges = false;
	pObj.pMainObj.OnChange("link");
}

var pT = null;
function _Ch()
{
	var t = document.getElementById('type');
	if(pT)
		pT.style.display = 'none';
	pT = document.getElementById(t.value);
	pT.style.display = "block";
	var tr = document.getElementById('trg');
	//alert(t.value);
	
	if(t.value=='t1' || t.value=='t2')
	{
		tr.style.display = GetDisplStr(1);
		tr.parentNode.cells[1].style.display = GetDisplStr(1);
	}
	else
	{
		tr.style.display = GetDisplStr(0);
		tr.parentNode.cells[1].style.display = GetDisplStr(0);
	}

	_ChFix();
}

function _ChTargL()
{
	var t = document.getElementById('targ_list');
	var o = document.getElementById('target');
	if(t.value.length>0)
	{
		o.disabled = true;
		o.value = t.value;
	}
	else
	{
		o.value = '';
		o.disabled = false;
	}
}

function _ChFix()
{
	var el = document.getElementById("fixstat");
	document.getElementById("event1").disabled = (!el.checked);
	document.getElementById("event2").disabled = (!el.checked);
	document.getElementById("event3").disabled = (!el.checked);
	document.getElementById("events").disabled = (!el.checked);
}

function SetUrl(url)
{
	document.getElementById("url1").value = url;
}
</script>
<?echo GetMessage("FILEMAN_ED_LINK_TYPE")?>
<select id='type'>
	<option value='t1'><?echo GetMessage("FILEMAN_ED_LINK_TYPE1")?></option>
	<option value='t2'><?echo GetMessage("FILEMAN_ED_LINK_TYPE2")?></option>
	<option value='t3'><?echo GetMessage("FILEMAN_ED_LINK_TYPE3")?></option>
	<option value='t4'><?echo GetMessage("FILEMAN_ED_LINK_TYPE4")?></option>
</select>

<hr size="1">

<?
$APPLICATION->ShowFileSelectDialog(
		"OpenFileBrowserWindFile",
		array("FORM_NAME" => "form1", "FORM_ELEMENT_NAME" => "url1"),
		array("SITE" => $_GET["site"]),
		"php,html"
	);
?>

<table width="100%" id="t1" style="display:none;" border="0">
	<tr>
		<td width="50%" align="right"><?echo GetMessage("FILEMAN_ED_LINK_DOC")?></td>
		<td width="250">
			<input type="text" size="25" value="" id="url1">
			<input type="button" id="OpenFileBrowserWindFile_button" value="...">
		</td>
	</tr>
</table>

<table width="100%"  id="t2" style="display:none;" border="0">
	<tr>
		<td align="right" width="50%">URL:</td>
		<td width="250" >
			<table cellpadding="0" cellspacing="0" width="100%">
				<tr>
					<td>
						<select id='url_type'>
							<option value="http://">http://</option>
							<option value="ftp://">ftp://</option>
							<option value="https://">https://</option>
							<option value=""></option>
						</select>
					</td>
					<td>
						<input type="text" size="20" value="" id="url2">
					</td>
				</tr>
			</table>
		</td>
</tr>
<tr>
	<td align="right" valign="top"><?echo GetMessage("FILEMAN_ED_LINK_STAT")?></td>
	<td>
		<input type="checkbox" id="fixstat" value=""><br>
		<table cellpadding="0" cellspacing="0" id="events">
			<tr>
				<td valign="top">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				</td>
				<td>
					<table cellpadding="0" cellspacing="0">
						<tr><td>Event1:</td><td><input type="event1" id="event1" size="10" value=""></td></tr>
						<tr><td>Event2:</td><td><input type="event2" id="event2" size="10" value=""></td></tr>
						<tr><td>Event3:</td><td><input type="event3" id="event3" size="10" value=""></td></tr>
					</table>
				</td>
			</tr>
		</table>
	</td>
</tr>
</table>

<table width="100%"  id="t3" style="display:none;" border="0">
	<tr>
		<td align="right" width="211px">
			<?echo GetMessage("FILEMAN_ED_LINK_ACH")?>
		</td>
		<td>
			<select id="url3"></select>
		</td>
	</tr>
</table>

<table width="100%" id="t4" style="display:none;" border="0">
<tr>
	<td align="right" width="211px">EMail:</td>
	<td>
		<input type="text" size="25" value="" id="url4">
	</td>
</tr>
</table>


<table width="100%" border="0">
	<tr >
		<td id='trg' style="display:none;" align="right"><?echo GetMessage("FILEMAN_ED_LINK_WIN")?></td>
		<td width="50%">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td>
						<select onchange="_ChTargL()" id='targ_list'>
							<option value=""></option>
							<option value="_blank"><?echo GetMessage("FILEMAN_ED_LINK_WIN_BLANK")?></option>
							<option value="_parent"><?echo GetMessage("FILEMAN_ED_LINK_WIN_PARENT")?></option>
							<option value="_self"><?echo GetMessage("FILEMAN_ED_LINK_WIN_SELF")?></option>
							<option value="_top"><?echo GetMessage("FILEMAN_ED_LINK_WIN_TOP")?></option>
						</select>
					</td>
					<td><input type="text" size="7" id="target" value=""></td>
				</tr>
			</table>		
		</td>
	</tr>
	
	<tr>
		<td align="right"><?echo GetMessage("FILEMAN_ED_LINK_ATITLE")?></td>
		<td><input type="text" size="30" value="" id="BXEditorDialog_title"></td>
	</tr>
	<tr ><td align="right"><?echo GetMessage("FILEMAN_ED_STYLE")?></td><td>
		<select id='classname'>
		</select>
	</td></tr>
	<tr><td align="right">ID:</td><td><input type="text" size="30" value="" id="id"></td></tr>
</table>
<script>
document.getElementById("OpenFileBrowserWindFile_button").onclick = OpenFileBrowserWindFile;
document.getElementById("fixstat").onclick = _ChFix;
document.getElementById("type").onchange = _Ch;
document.getElementById("targ_list").onchange = _ChTargL;
</script>


<?elseif($name=="image"):?>

<script>
var pElement = null;
var prevsrc = '';

function _CHSize()
{
	var el = document.getElementById("preview");
	SAttr(el, "width", document.getElementById("width").value);
	SAttr(el, "height", document.getElementById("height").value);
}

function _Reload(bFirst)
{
	var el = document.getElementById("preview");
	if(prevsrc!=document.getElementById("src").value)
	{
		document.getElementById("preview").style.display="";
		el.removeAttribute("width");
		el.removeAttribute("height");
		prevsrc=document.getElementById("src").value;
		//SAttr(pElement, "src", document.getElementById("src").value);
		el.src=document.getElementById("src").value;
	}

	el.alt=document.getElementById("alt").value;
	el.border=document.getElementById("border").value;
	el.align=document.getElementById("align").value;
	el.hspace=document.getElementById("hspace").value;
	el.vspace=document.getElementById("vspace").value;
}

function _LPreview()
{
	document.getElementById("width").value=this.width;
	document.getElementById("height").value=this.height;
}

function OnLoad()
{
	pElement = pObj.pMainObj.GetSelectionObject();
	if(pElement.tagName.toUpperCase()!='IMG' || pElement.getAttribute("__bxtagname"))
	{
		pElement = null;
		document.getElementById("preview").onload = _LPreview;
		oDialogTitle.innerHTML = '<?=GetMessage("FILEMAN_ED_NEW_IMG")?>';
	}
	else
	{
		oDialogTitle.innerHTML = '<?=GetMessage("FILEMAN_ED_EDIT_IMG")?>';
		document.getElementById("width").value = GAttr(pElement, "width");
		document.getElementById("height").value = GAttr(pElement, "height");
		document.getElementById("src").value = GAttr(pElement, "src");
		document.getElementById("alt").value = GAttr(pElement, "alt");
		document.getElementById("border").value = GAttr(pElement, "border");
		document.getElementById("align").value = GAttr(pElement, "align");
		document.getElementById("hspace").value = GAttr(pElement, "hspace");
		document.getElementById("vspace").value = GAttr(pElement, "vspace");
		prevsrc = GAttr(pElement, "src");
		document.getElementById("preview").style.display="";
		document.getElementById("preview").onload = function ()
		{
			this.onload = _LPreview;
		}
		document.getElementById("preview").style.display="";
		document.getElementById("preview").src = prevsrc;
		document.getElementById("preview").alt=document.getElementById("alt").value;
		document.getElementById("preview").border=document.getElementById("border").value;
		document.getElementById("preview").align=document.getElementById("align").value;
		document.getElementById("preview").hspace=document.getElementById("hspace").value;
		document.getElementById("preview").vspace=document.getElementById("vspace").value;
	}
	document.getElementById("src").onchange = _Reload;
}

function OnSave()
{
	pObj.pMainObj.bSkipChanges = true;
	if(!pElement)
	{
		var tmpid = Math.random().toString().substring(2);
		var str = '<img id="'+tmpid+'" __bxsrc="'+bxhtmlspecialchars(document.getElementById("src").value)+'" />';
		//var str = '<img id="'+tmpid+'" />';
		BXSelectRange(oPrevRange,pObj.pMainObj.pEditorDocument,pObj.pMainObj.pEditorWindow);
		pObj.pMainObj.insertHTML(str);
		pElement = pObj.pMainObj.pEditorDocument.getElementById(tmpid);
		pElement.removeAttribute("id");
	}

	SAttr(pElement, "width", document.getElementById("width").value);
	SAttr(pElement, "height", document.getElementById("height").value);
	SAttr(pElement, "hspace", document.getElementById("hspace").value);
	SAttr(pElement, "vspace", document.getElementById("vspace").value);
	SAttr(pElement, "border", document.getElementById("border").value);
	SAttr(pElement, "align", document.getElementById("align").value);
	SAttr(pElement, "src", document.getElementById("src").value);
	SAttr(pElement, "__bxsrc", document.getElementById("src").value);
	SAttr(pElement, "alt", addslashes(document.getElementById("alt").value));
	SAttr(pElement, "alt", document.getElementById("alt").value);
	pObj.pMainObj.bSkipChanges = false;
	pObj.pMainObj.OnChange("image");
}

function SetUrl(filename,path,site)
{
	var url = path+'/'+filename;
	document.getElementById("src").value = url;
	if(document.getElementById("src").onchange)
		document.getElementById("src").onchange();
}
</script>
<?
if(strlen($str_FILENAME)>0)
{
	$APPLICATION->ShowFileSelectDialog(
		"OpenFileBrowserWindImage",
		array("FUNCTION_NAME" => "SetUrl"),
		array("PATH" => GetDirPath($str_FILENAME),"SITE" => $_GET["site"]),
		"image"
	);
}
else
{	
	$APPLICATION->ShowFileSelectDialog(
		"OpenFileBrowserWindImage",
		array("FUNCTION_NAME" => "SetUrl"),
		array("SITE" => $_GET["site"]),
		"image"
	);
}
?>
<table width="100%" id="t1" border="0">
	<tr>
		<td align="right" width="50%"><?echo GetMessage("FILEMAN_ED_IMG_PATH")?></td>
		<td width="50%">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td><input type="text" size="25" value="" id="src" name="src"></td>
					<td><input type="button" value="..." id="OpenFileBrowserWindImage_button" onclick="alert('!@#');"></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width="50%" align="right"><?echo GetMessage("FILEMAN_ED_IMG_ALT")?></td>
		<td width="50%"><input type="text" size="30" value="" id="alt"></td>
	</tr>
	<tr>
		<td width="50%" align="right">&nbsp;</td>
		<td width="50%">&nbsp;</td>
	</tr>
	<tr>
		<td width="50%" align="right" valign="top">
			<table width="100%">
				<tr><td align="right"><?echo GetMessage("FILEMAN_ED_IMG_W")?></td><td><input type="text" size="3" id="width"></td></tr>
				<tr><td align="right"><?echo GetMessage("FILEMAN_ED_IMG_H")?></td><td><input type="text" size="3" id="height"></td></tr>
				<tr><td align="right"><?echo GetMessage("FILEMAN_ED_IMG_HSp")?></td><td><input type="text" id="hspace" size="3"></td></tr>
				<tr><td align="right"><?echo GetMessage("FILEMAN_ED_IMG_HVp")?></td><td><input type="text" id="vspace" size="3"></td></tr>
				<tr><td align="right"><?echo GetMessage("FILEMAN_ED_IMG_BORD")?></td><td><input type="text" id="border" size="3"></td></tr>
				<tr><td align="right"><?echo GetMessage("FILEMAN_ED_IMG_AL")?></td><td>
					<select id="align">
						<option value=""></option>
						<!--
						<option value="absbottom">absbottom</option>
						<option value="absmiddle">absmiddle</option>
						<option value="baseline">baseline</option>
						-->
						<option value="bottom">bottom</option>
						<option value="left">left</option>
						<option value="middle">middle</option>
						<option value="right">right</option>
						<!--<option value="texttop">texttop</option>-->
						<option value="top">top</option>
					</select>
				</td></tr>
			</table>
		</td>
		<td width="50%"><?echo GetMessage("FILEMAN_ED_IMG_PREV")?>
		<div style="height:140px; width:180px; overflow: hidden; border: 1px #999999 solid; overflow-y: scroll; overflow-x: auto; color: #999999; background-color: #FFFFFF; padding: 3px">
			<img id="preview" style="display:none"/>
			text text text text text text text text text text text text text text text text
			text text text text text text text text text text text text text text text text
			text text text text text text text text text text text text text text text text
			text text text text text text text text text text text text text text text text
			text text text text text text text text text text text text text text text text
			text text text text text text text text text text text text text text text text
			text text text text text text text text text text text text text text text text
			text text text text text text text text text text text text text text text text
			text text text text text text text text text text text text text text text text
			text text text text text text text text text text text text text text text text
		</div>
		</td>
	</tr>
</table>
<script>
//attaching events					
document.getElementById("src").onchange = _Reload;
document.getElementById("OpenFileBrowserWindImage_button").onclick = OpenFileBrowserWindImage;
document.getElementById("width").onchange = _CHSize;
document.getElementById("width").onchange = _CHSize;
document.getElementById("height").onchange = _CHSize;
document.getElementById("hspace").onchange = _Reload;
document.getElementById("vspace").onchange = _Reload;
document.getElementById("border").onchange = _Reload;
document.getElementById("align").onchange = _Reload;
</script>

<?elseif($name=="table"):?>
<script>
var pElement = null;
function OnLoad()
{
	if(pObj.params.check_exists)
	{
		oDialogTitle.innerHTML = '<?=GetMessage("FILEMAN_ED_TABLE_PROP")?>';
		pElement = BXFindParentByTagName(pObj.pMainObj.GetSelectionObject(), 'TABLE');
	}
	else
	{
		oDialogTitle.innerHTML = '<?=GetMessage("FILEMAN_ED_NEW_TABLE")?>';
	}

	var arStFilter = ['TABLE', 'DEFAULT'], i;
	var elStyles = document.getElementById("classname");
	var oOption = new Option("", "", false, false);
	elStyles.options.add(oOption);
	var arStyles;
	for(i=0; i<arStFilter.length; i++)
	{
		arStyles = pObj.pMainObj.oStyles.GetStyles(arStFilter[i]);
		for(var j=0; j<arStyles.length; j++)
		{
			if(arStyles[j].className.length<=0)
				continue;
			oOption = new Option(arStyles[j].className, arStyles[j].className, false, false);
			elStyles.options.add(oOption);
		}
	}

	if(pElement)
	{
		document.getElementById("rows").value=pElement.rows.length;
		document.getElementById("rows").disabled = true;
		document.getElementById("cols").value=pElement.rows[0].cells.length;
		document.getElementById("cols").disabled = true;
		document.getElementById("cellpadding").value = GAttr(pElement, "cellPadding");
		document.getElementById("cellspacing").value = GAttr(pElement, "cellSpacing");
		document.getElementById("border").value = GAttr(pElement, "border");
		document.getElementById("align").value = GAttr(pElement, "align");
		document.getElementById("classname").value = GAttr(pElement, "className");
		var v = GAttr(pElement, "width");

		if(v.substr(-1, 1) == "%")
		{
			document.getElementById("width").value = v.substr(0, v.length-1);
			document.getElementById("width_unit").value = "%";
		}
		else
		{
			if(v.substr(-2, 2) == "px")
				v = v.substr(0, v.length-2);

		 	document.getElementById("width").value = v
		}

		v = GAttr(pElement, "height");
		if(v.substr(-1, 1) == "%")
		{
			document.getElementById("height").value = v.substr(0, v.length-1);
			document.getElementById("height_unit").value = "%";
		}
		else
		{
			if(v.substr(-1, 2) == "px")
				v = v.substr(0, v.length-2);

			document.getElementById("height").value = v
		}
	}
	else
	{
		document.getElementById("rows").value="2";
		document.getElementById("cols").value="3";
		document.getElementById("cellpadding").value="1";
		document.getElementById("cellspacing").value="1";
		document.getElementById("border").value="0";
	}
}

function OnSave()
{
	pObj.pMainObj.bSkipChanges = true;
	if(!pElement)
	{
		var tmpid = Math.random().toString().substring(2);
		var str = '<table id="'+tmpid+'"/>';
		BXSelectRange(oPrevRange,pObj.pMainObj.pEditorDocument,pObj.pMainObj.pEditorWindow);
		pObj.pMainObj.insertHTML(str);
		
		pElement = pObj.pMainObj.pEditorDocument.getElementById(tmpid);
		pElement.removeAttribute("id");

		var i, j, row, cell;
		for(i=0; i<document.getElementById("rows").value; i++)
		{
			row = pElement.insertRow(-1);
			for(j=0; j<document.getElementById("cols").value; j++)
			{
				cell = row.insertCell(-1);
				cell.innerHTML = '<br _moz_editor_bogus_node="on">';
				//cell.innerHTML = '&nbsp;';
			}
		}
	}
	else
	{
		if(pObj.pMainObj.bTableBorder)
			pObj.pMainObj.__ShowTableBorder(pElement, false);
	}

	SAttr(pElement, "width", (document.getElementById("width").value.length>0?document.getElementById("width").value+''+(document.getElementById("width_unit").value=='%'?'%':''):''));
	SAttr(pElement, "height", (document.getElementById("height").value.length>0?document.getElementById("height").value+''+(document.getElementById("height_unit").value=='%'?'%':''):''));
	SAttr(pElement, "border", document.getElementById("border").value);
	SAttr(pElement, "cellPadding", document.getElementById("cellpadding").value);
	SAttr(pElement, "cellSpacing", document.getElementById("cellspacing").value);
	SAttr(pElement, "align", document.getElementById("align").value);
	SAttr(pElement, 'className', document.getElementById("classname").value);

	pObj.pMainObj.OnChange("table");

	if(pObj.pMainObj.bTableBorder)
		pObj.pMainObj.__ShowTableBorder(pElement, true);
}

</script>

<table width="100%" id="t1" border="0">
	<tr>
		<td align="right"><?echo GetMessage("FILEMAN_ED_TBL_R")?></td>
		<td><input type="text" size="3" id="rows"></td>
		<td>&nbsp;</td>
		<td align="right"><?echo GetMessage("FILEMAN_ED_TBL_W")?></td>
		<td nowrap><input type="text" size="3" id="width"><select id="width_unit"><option value="px"><?echo GetMessage("FILEMAN_ED_TBL_WPX")?></option><option value="%"><?echo GetMessage("FILEMAN_ED_TBL_WPR")?></option></select></td>
	</tr>
	<tr>
		<td align="right"><?echo GetMessage("FILEMAN_ED_TBL_COL")?></td>
		<td><input type="text" size="3" id="cols"></td>
		<td>&nbsp;</td>
		<td align="right"><?echo GetMessage("FILEMAN_ED_TBL_H")?></td>
		<td nowrap><input type="text" size="3" id="height"><select id="height_unit"><option value="px"><?echo GetMessage("FILEMAN_ED_TBL_WPX")?></option><option value="%"><?echo GetMessage("FILEMAN_ED_TBL_WPR")?></option></td>
	</tr>
	<tr>
		<td colspan="5">&nbsp;</td>
	</tr>
	<tr>
		<td align="right" nowrap><?echo GetMessage("FILEMAN_ED_IMG_BORD")?></td>
		<td><input type="text" id="border" size="3"></td>
		<td>&nbsp;</td>
		<td align="right" nowrap>Cell padding:</td>
		<td><input type="text" id="cellpadding" size="3"></td>
	</tr>
	<tr>
		<td align="right"><?echo GetMessage("FILEMAN_ED_TBL_AL")?></td>
		<td>
			<select id="align">
				<option value=""></option>
				<option value="left">left</option>
				<option value="center">center</option>
				<option value="right">right</option>
			</select>
		</td>
		<td>&nbsp;</td>
		<td align="right" nowrap>Cell spacing:</td>
		<td><input type="text" id="cellspacing" size="3"></td>
	</tr>
	<tr>
		<td align="right"><?echo GetMessage("FILEMAN_ED_STYLE")?></td>
		<td colspan="4"><select id="classname"></select></td>
	</tr>
</table>

<?elseif($name=="pasteastext"):?>
<script>
function OnLoad()
{
	oDialogTitle.innerHTML = '<?=GetMessage("FILEMAN_ED_PASTE_TEXT")?>';
	document.getElementById("BXInsertAsText").focus();
}

function OnSave()
{
	BXSelectRange(oPrevRange,pObj.pMainObj.pEditorDocument,pObj.pMainObj.pEditorWindow);
	pObj.pMainObj.PasteAsText(document.getElementById("BXInsertAsText").value);
}
</script>

<table width="100%" id="t1" border="0">
	<tr>
		<td><?echo GetMessage("FILEMAN_ED_FF")?> "<?echo GetMessage("FILEMAN_ED_SAVE")?>":</td>
	</tr>
	<tr><td>
		<textarea id="BXInsertAsText" style="width:100%; height:200px"></textarea>
	</td></tr>
</table>

<?elseif($name=="pasteword"):?>
<script>
var pFrame = null;
function OnLoad()
{
	oDialogTitle.innerHTML = '<?=GetMessage("FILEMAN_ED_PASTE_WORD")?>';
	pFrame = document.getElementById("BXPasteAsWordNode_text");
	//alert(pFrame.nodeName);
	if(pFrame.contentDocument)
		pFrame.pDocument = pFrame.contentDocument;
	else
		pFrame.pDocument = pFrame.contentWindow.document;
	pFrame.pWindow = pFrame.contentWindow;	

	pFrame.pDocument.open();
	pFrame.pDocument.write('<html><head><style>BODY{margin:0px; padding:0px; border:0px;}</style></head><body></body></html>');
	pFrame.pDocument.close();
	
	if(pFrame.pDocument.addEventListener)
		pFrame.pDocument.addEventListener('keydown', dialog_OnKeyDown, false);
	else if (pFrame.pDocument.attachEvent)
		pFrame.pDocument.body.attachEvent('onpaste', dialog_OnPaste);

	if(BXIsIE())
	{		
		document.getElementById("BXPasteAsWordNode_ff").style.display = 'none';
		pFrame.pDocument.body.contentEditable = true;
		pFrame.pDocument.body.innerHTML = pObj.pMainObj.GetClipboardHTML();
		dialog_OnPaste();
	}
	else
		pFrame.pDocument.designMode='on';
	
}

function dialog_OnKeyDown(e)
{
	if (e.ctrlKey && !e.shiftKey && !e.altKey)
	{
		if (!BXIsIE())
		{
			switch (e.which) 
			{
				case 86: // "V" и "v" 	
				case 118:	
					dialog_OnPaste(e);
					break ;
			}
		}
	}
}

function dialog_OnPaste(e)
{
	this.pOnChangeTimer = setTimeout(dialog_cleanAndShow, 10);
}

function dialog_cleanAndShow()
{
	var removeFonts = document.getElementById('BXPasteAsWordNode_removeFonts').checked;
	var removeStyles = document.getElementById('BXPasteAsWordNode_removeStyles').checked;
	var removeIndents = document.getElementById('BXPasteAsWordNode_removeIndents').checked;
	clenedHtml = pObj.pMainObj.CleanWordText(pFrame.pDocument.body.innerHTML,[removeFonts,removeStyles,removeIndents]);
	dialog_showClenedHtml(clenedHtml);	
}

function dialog_showClenedHtml(html)
{
	taSourse = document.getElementById('BXPasteAsWordNode_sourse');
	taSourse.value = html;
}

function OnSave()
{
	var removeFonts = document.getElementById('BXPasteAsWordNode_removeFonts').checked;
	var removeStyles = document.getElementById('BXPasteAsWordNode_removeStyles').checked;
	var removeIndents = document.getElementById('BXPasteAsWordNode_removeIndents').checked;
	BXSelectRange(oPrevRange,pObj.pMainObj.pEditorDocument,pObj.pMainObj.pEditorWindow);
	pObj.pMainObj.PasteWord(pFrame.pDocument.body.innerHTML,[removeFonts,removeStyles,removeIndents]);
}
</script>

<table width="100%" id="BXPasteAsWordNode_t1" border="0">
	<tr id="BXPasteAsWordNode_ff">
		<td><?echo GetMessage("FILEMAN_ED_FF")?> "<?echo GetMessage("FILEMAN_ED_SAVE")?>":</td>
	</tr>
	<tr>
		<td><iframe id="BXPasteAsWordNode_text" src="javascript:''" style="width:100%; height:150px; border:1px solid #CCCCCC;"></iframe></td>
	</tr>
	<tr>
		<td><?echo GetMessage("FILEMAN_ED_HTML_AFTER_CLEANING")?></td>
	</tr>
	<tr>
		<td><textarea id="BXPasteAsWordNode_sourse" style="width:100%; height:100px; border:1px solid #CCCCCC;" disabled="disabled"></textarea></td>
	</tr>
	<tr>
		<td>
			<input id="BXPasteAsWordNode_removeFonts" type="checkbox" checked="checked"><label for="BXPasteAsWordNode_removeFonts"><?echo GetMessage("FILEMAN_ED_REMOVE_FONTS")?></label><br>
			<input id="BXPasteAsWordNode_removeStyles" type="checkbox" checked="checked"> <label for="BXPasteAsWordNode_removeStyles"><?echo GetMessage("FILEMAN_ED_REMOVE_STYLES")?></label><br>
			<input id="BXPasteAsWordNode_removeIndents" type="checkbox" checked="checked"> <label for="BXPasteAsWordNode_removeIndents"><?echo GetMessage("FILEMAN_ED_REMOVE_INDENTS")?></label>
		</td>
	</tr>
</table>

<script>
//attaching events
document.getElementById("BXPasteAsWordNode_removeFonts").onclick = dialog_cleanAndShow;
document.getElementById("BXPasteAsWordNode_removeStyles").onclick = dialog_cleanAndShow;
document.getElementById("BXPasteAsWordNode_removeIndents").onclick = dialog_cleanAndShow;
</script>	

<?elseif($name=="asksave"):?>

<script>
function OnLoad()
{
	oDialogTitle.innerHTML = '<?=GetMessage("FILEMAN_ED_EDITOR")?>';
	document.getElementById("asksave_b1").focus();
	document.getElementById("asksave_b1").onclick = function(){OnSave('save')};
	document.getElementById("asksave_b2").onclick = function(){OnSave('exit')};	
	document.getElementById("asksave_b3").onclick = OnSave;
	
	document.getElementById("buttonsSec").style.height = (BXIsIE()) ? 25 : 45;
}

function OnSave(t)
{
	if(t=='save')
	{
		if(pObj.params.savetype == 'saveas')
			pObj.params.window._BXSaveAs();
		else
		{
			pObj.pMainObj.SaveContent(true);
			pObj.pMainObj.pForm.submit();
		}
	}
	else if(t=='exit')
	{
		if(pObj.pMainObj.arConfig["sBackUrl"])
			pObj.params.window.location = pObj.pMainObj.arConfig["sBackUrl"];
	}

	pObj.Close()
}
</script>

<table height="100%" width="100%" id="t1" border="0">
	<tr>
		<td colspan="3">
			<table height="100%" width="100%" id="t1" border="0" style="border: 1px solid #000000; font-size:14px; background-color:#EFEFEF">
			<tr>
			<td></td>
			<td><?=GetMessage("FILEMAN_DIALOG_EXIT_ACHTUNG")?></td>
			</tr>
			</table>
		</td>
	</tr>
	<tr id="buttonsSec" valign="top">
		<td align="center"><input style="font-size:12px; height: 25px; width: 180px;" type="button" id="asksave_b1" value="<?echo GetMessage("FILEMAN_DIALOG_SAVE_BUT")?>"></td>
		<td align="center"><input style="font-size:12px; height: 25px; width: 180px;" type="button" id="asksave_b2" value="<?echo GetMessage("FILEMAN_DIALOG_EXIT_BUT")?>"></td>
		<td align="center"><input style="font-size:12px; height: 25px; width: 180px;" type="button" id="asksave_b3" value="<?echo GetMessage("FILEMAN_DIALOG_EDIT_BUT")?>"></td>
	</tr>	
</table>

<?elseif($name=="pageprops"):?>

<script>
var finput = false;
function OnLoad()
{
	oDialogTitle.innerHTML = '<?=GetMessage("FILEMAN_ED_EDITOR_PAGE_PROP")?>';
	var eddoc = pObj.params.document;
	document.getElementById('BX_dialog_title').value = eddoc.getElementById('title').value;
	document.getElementById("BX_more_prop_but").onclick = function(e) {AppendRow('', '');};


	//alert(eddoc.getElementById("maxind").value);
	var code, val, name, cnt = parseInt(eddoc.getElementById("maxind").value)+1;
	for(var i=0; i<cnt; i++)
	{
		code = eddoc.getElementById("CODE_"+i);
		val = eddoc.getElementById("VALUE_"+i);
		name = eddoc.getElementById("NAME_"+i);
		AppendRow(code.value, (val?val.value:null), (name?name.value:null));
	}

	if(finput)
		finput.focus();	
}

function AppendRow(code, value, name)
{
	var tbl = document.getElementById('pageprops_t1');

	var cnt = parseInt(document.getElementById("BX_dialog_maxind").value)+1;
	var r = tbl.insertRow(tbl.rows.length-1);
	var c = r.insertCell(-1);
	c.align="right";
	if(name)
		c.innerHTML = '<input type="hidden" id="BX_dialog_CODE_'+cnt+'" name="BX_dialog_CODE_'+cnt+'" value="'+bxhtmlspecialchars(code)+'">'+bxhtmlspecialchars(name)+':';
	else
	{
		c.innerHTML = '<input type="text" id="BX_dialog_CODE_'+cnt+'" name="BX_dialog_CODE_'+cnt+'" value="'+bxhtmlspecialchars(code)+'" size="30">:';
		if(!finput)
			finput = document.getElementById('CODE_'+cnt);
	}

	c = r.insertCell(-1);
	c.innerHTML = '<input type="text" name="BX_dialog_VALUE_'+cnt+'" id="BX_dialog_VALUE_'+cnt+'" value="'+bxhtmlspecialchars(value)+'" size="55">';

	if(!finput)
		finput = document.getElementById('BX_dialog_VALUE_'+cnt);

	document.getElementById("BX_dialog_maxind").value = cnt;
}

function OnSave()
{
	var eddoc = pObj.params.document;

	var edcnt = parseInt(eddoc.getElementById("maxind").value);
	var cnt = parseInt(document.getElementById("BX_dialog_maxind").value);

	for(var i=0; i<=edcnt; i++)
	{
		if(eddoc.getElementById("CODE_"+i).value != document.getElementById("BX_dialog_CODE_"+i).value)
			eddoc.getElementById("CODE_"+i).value = document.getElementById("BX_dialog_CODE_"+i).value;
		if(eddoc.getElementById("VALUE_"+i).value != document.getElementById("BX_dialog_VALUE_"+i).value)
			eddoc.getElementById("VALUE_"+i).value = document.getElementById("BX_dialog_VALUE_"+i).value;
	}

	for(i = edcnt+1; i<=cnt; i++)
	{
		pObj.params.window._MoreRProps(document.getElementById("BX_dialog_CODE_"+i).value, document.getElementById("BX_dialog_VALUE_"+i).value);
	}

	eddoc.getElementById("maxind").value = cnt;
	eddoc.getElementById('title').value = document.getElementById('BX_dialog_title').value;

	pObj.pMainObj.bNotSaved = true;

	return iNoOnSelectionChange;
}
</script>
<div style="width:100%; height:200px; overflow-y:scroll;">
<table width="100%" id="pageprops_t1" border="0">
	<tr>
		<td width="40%" align="right"><b><?echo GetMessage("FILEMAN_DIALOG_TITLE")?></b></td>
		<td width="60%"><input type="text" id="BX_dialog_title" value="" size="30"></td>
	</tr>
	<tr>
		<td align="right"></td>
		<td><input id="BX_more_prop_but" type="button" value="<?echo GetMessage("FILEMAN_DIALOG_MORE_PROP")?>"></td>
	</tr>
</table>
</div>
<input type="hidden" value="-1" id="BX_dialog_maxind">

<?elseif($name=="spellcheck"):?>

<script>
var pElement = null;
function OnLoad()
{
	oDialogTitle.innerHTML = '<?=GetMessage("FILEMAN_ED_SPELLCHECKING")?>';
	pElement = pObj.pMainObj.GetSelectionObject();
	var BXLang = pObj.params.BXLang;
	var usePspell = pObj.params.usePspell;
	var useCustomSpell = pObj.params.useCustomSpell;
	oBXSpellChecker = new BXSpellChecker(pObj.pMainObj,BXLang,usePspell,useCustomSpell);
	oBXSpellChecker.parseDocument();
	oBXSpellChecker.spellCheck();
}

window.closeDialog = function() 
{
	BXClearSelection(pObj.pMainObj.pEditorDocument,pObj.pMainObj.pEditorWindow);
	pObj.Close();
}

</script>
	<div id="BX_dialog_waitWin" style="display: block; text-align: center; vertical-align: middle;">
		<table border="0" width="100%" height="100%" style="vertical-align: middle">
			<tr><td height="60"></td></tr>
			<tr>
				<td align="center" valign="top">
					<img style="vertical-align: middle;" src="/bitrix/themes/.default/images/wait.gif" />
					<span style="vertical-align: middle;"><?echo GetMessage("FILEMAN_ED_WAIT_LOADING")?></span>
				</td>
			</tr>
		</table>
	</div>
	<div id="BX_dialog_okMessWin" style="display: none;">
		<table border="0" width="100%" height="100%">
			<tr>
				<td align="center">					
					<span style="vertical-align: middle;"><?echo GetMessage("FILEMAN_ED_SPELL_FINISHED")?></span>
					<br><br>
					<input id="BX_dialog_butClose" type="button" value="<?echo GetMessage("FILEMAN_ED_CLOSE")?>" style="width:150">
				</td>
			</tr>
		</table>	
	</div>
	<div id="BX_dialog_spellResultWin" style="display: none">
	<table width="380" border="0" align="center" cellpadding="0" cellspacing="0">
		<tr><td colspan="4" height="5"></td></tr>
		<tr>
			<td width="224" valign="top"><input id="BX_dialog_wordBox" type="text" style="width:100%;"></td>
			<td width="8"></td>
			<td width="140" valign="top"><input id="BX_dialog_butSkip" type="button" value="<?echo GetMessage("FILEMAN_ED_SKIP")?>" style="width:100%;"></td>
			<td width="8"></td>
		</tr>
		<tr><td colspan="4" height="7"></td></tr>
		<tr>
			<td rowspan="9" valign="top"><select id="BX_dialog_suggestionsBox" size="8" style="width:100%;"></select></td>
			<td></td>
			<td><input id="BX_dialog_butSkipAll" type="button" value="<?echo GetMessage("FILEMAN_ED_SKIP_ALL")?>" style="width:100%;"></td>
			<td></td>
		</tr>
		<tr height="5"><td colspan="2" height="5"></td></tr>
		<tr>
			<td></td>
			<td><input id="BX_dialog_butReplace" type="button" value="<?echo GetMessage("FILEMAN_ED_REPLACE")?>" style="width:100%;"></td>
			<td></td>
		</tr>
		<tr height="5"><td colspan="2" height="5"></td></tr>
		<tr>
			<td></td>
			<td><input id="BX_dialog_butReplaceAll" type="button" value="<?echo GetMessage("FILEMAN_ED_REPLACE_ALL")?>" style="width:100%;"></td>
			<td></td>
		</tr>
		<tr height="5"><td colspan="2" height="5"></td></tr>
		<tr>
			<td></td>
			<td><input id="BX_dialog_butAdd" type="button" value="<?echo GetMessage("FILEMAN_ED_ADD")?>" style="width:100%;"></td>
			<td></td>
		</tr>
		<tr height="5"><td colspan="2" height="5"></td></tr>
		<tr>
			<td></td>
			<td><input id="BX_dialog_butClose" type="button" value="<?echo GetMessage("FILEMAN_ED_CLOSE")?>" style="width:100%;" onClick="pObj.Close();"></td>
			<td></td>
		</tr>
	</table>
	</div>

<?elseif($name=="specialchar"):?>

<script>
var pElement = null;
function OnLoad()
{
	pElement = pObj.pMainObj.GetSelectionObject();
	oDialogTitle.innerHTML = '<?=GetMessage("FILEMAN_ED_EDITOR_SPES_CHAR")?>';
	var cancelBut = document.getElementById("cancelBut");
	cancelBut.style.display = "none";
	var saveBut = document.getElementById("saveBut");
	saveBut.style.display = "none";	
	
	arEntities = ['&iexcl;','&cent;','&pound;','&curren;','&yen;','&brvbar;','&sect;','&uml;','&copy;','&ordf;','&laquo;','&not;','&reg;','&macr;','&deg;','&plusmn;','&sup2;','&sup3;','&acute;','&micro;','&para;','&middot;','&cedil;','&sup1;','&ordm;','&raquo;','&frac14;','&frac12;','&frac34;','&iquest;','&Agrave;','&Aacute;','&Acirc;','&Atilde;','&Auml;','&Aring;','&AElig;','&Ccedil;','&Egrave;','&Eacute;','&Ecirc;','&Euml;','&Igrave;','&Iacute;','&Icirc;','&Iuml;','&ETH;','&Ntilde;','&Ograve;','&Oacute;','&Ocirc;','&Otilde;','&Ouml;','&times;','&Oslash;','&Ugrave;','&Uacute;','&Ucirc;','&Uuml;','&Yacute;','&THORN;','&szlig;','&agrave;','&aacute;','&acirc;','&atilde;','&auml;','&aring;','&aelig;','&ccedil;','&egrave;','&eacute;','&ecirc;','&euml;','&igrave;','&iacute;','&icirc;','&iuml;','&eth;','&ntilde;','&ograve;','&oacute;','&ocirc;','&otilde;','&ouml;','&divide;','&oslash;','&ugrave;','&uacute;','&ucirc;','&uuml;','&yacute;','&thorn;','&yuml;','&OElig;','&oelig;','&Scaron;','&scaron;','&Yuml;','&circ;','&tilde;','&ensp;','&emsp;','&thinsp;','&zwnj;','&zwj;','&lrm;','&rlm;','&ndash;','&mdash;','&lsquo;','&rsquo;','&sbquo;','&ldquo;','&rdquo;','&bdquo;','&dagger;','&Dagger;','&permil;','&lsaquo;','&rsaquo;','&euro;','&Alpha;','&Beta;','&Gamma;','&Delta;','&Epsilon;','&Zeta;','&Eta;','&Theta;','&Iota;','&Kappa;','&Lambda;','&Mu;','&Nu;','&Xi;','&Omicron;','&Pi;','&Rho;','&Sigma;','&Tau;','&Upsilon;','&Phi;','&Chi;','&Psi;','&Omega;','&alpha;','&beta;','&gamma;','&delta;','&epsilon;','&zeta;','&eta;','&theta;','&iota;','&kappa;','&lambda;','&mu;','&nu;','&xi;','&omicron;','&pi;','&rho;','&sigmaf;','&sigma;','&tau;','&upsilon;','&phi;','&chi;','&psi;','&omega;','&thetasym;','&upsih;','&piv;','&bull;','&hellip;','&prime;','&Prime;','&oline;','&frasl;','&weierp;','&image;','&real;','&trade;','&alefsym;','&larr;','&uarr;','&rarr;','&darr;','&harr;','&crarr;','&lArr;','&uArr;','&rArr;','&dArr;','&hArr;','&forall;','&part;','&exist;','&empty;','&nabla;','&isin;','&notin;','&ni;','&prod;','&sum;','&minus;','&lowast;','&radic;','&prop;','&infin;','&ang;','&and;','&or;','&cap;','&cup;','&int;','&there4;','&sim;','&cong;','&asymp;','&ne;','&equiv;','&le;','&ge;','&sub;','&sup;','&nsub;','&sube;','&supe;','&oplus;','&otimes;','&perp;','&sdot;','&lceil;','&rceil;','&lfloor;','&rfloor;','&lang;','&rang;','&loz;','&spades;','&clubs;','&hearts;','&diams;'];
	
	drawTable();	
}

function drawTable()
{
	var charCont = document.getElementById("charCont");
	var chTable = document.createElement("TABLE");
	var tBody = document.createElement("TBODY");
	chTable.appendChild(tBody);
	charCont.appendChild(chTable);

	var r,c,lEn = arEntities.length;
	var elEntity = document.createElement("span");

	for(var i=0; i<lEn; i++)
	{
		if (i%19 == 0)
		{
			r = document.createElement("TR");
			tBody.appendChild(r);
		}
		elEntity.innerHTML = arEntities[i];
		
		c = document.createElement("TD");
		c.id = 'e_'+i;
		c.innerHTML = elEntity.innerHTML;
		//c.className = "bxspecialcharnormal";
		
		setCellstyle(c,'normal');
		setCellEvents(c);
		r.appendChild(c);
	}
}

function setCellstyle(cellObj,mode){
	switch (mode)
	{
		case 'normal':
			cellObj.style.width = "17px";
			cellObj.style.height = "17px";
			cellObj.style.fontSize = "12px";
			cellObj.style.textAlign = "center";
			cellObj.style.verticalAlign = "middle";
			cellObj.style.border = "1px solid #ffffff";
			cellObj.style.backgroundColor = "#FFFFFF";
			break;
		case 'over':
			cellObj.style.width = "17px";
			cellObj.style.height = "17px";
			cellObj.style.fontSize = "12px";
			cellObj.style.textAlign = "center";
			cellObj.style.verticalAlign = "middle";
			cellObj.style.border = "#4B4B6F 1px solid";
			cellObj.style.backgroundColor = "#BFC6B8";
			break;
	}
}

function setCellEvents(cellObj){
	cellObj.onmouseover = function(){
		setCellstyle(this,'over');
		prevChar(this);
	}
	cellObj.onmouseout = function(){
		setCellstyle(this,'normal');
	}
	cellObj.onclick = function(){
		var entInd = cellObj.id.substring(2);
		BXSelectRange(oPrevRange,pObj.pMainObj.pEditorDocument,pObj.pMainObj.pEditorWindow);
		pObj.pMainObj.insertHTML(arEntities[entInd]);
		pObj.Close();
	}
}

function prevChar(cellObj)
{
	var charPrev = document.getElementById('charPrev');
	charPrev.innerHTML = cellObj.innerHTML;	
	charPrev.style.fontSize = "80px";
	charPrev.style.textAlign = "center";
	charPrev.style.verticalAlign = "middle";
	
	var charPrev = document.getElementById('entityName');
	var entInd = cellObj.id.substring(2);
	charPrev.innerHTML = arEntities[entInd].substr(1,arEntities[entInd].length-2);	
}

</script>
	<div id="charCont" style="width: 455; position: absolute; top: 25px; left: 5px">
	</div>
	<div id="charPrev" style="background-color: #FFFFFF; width: 120px; height: 120px; position: absolute; top: 25px; left: 465px"></div>
	<div id="entityName" style="font-size: 14; text-align: center; background-color: #FFFFFF; width: 120px; height: 20px; position: absolute; top: 130px; left: 465px"></div>
	<div id="saveBut_div" style="width: 120px; height: 20px; position: absolute; top: 258px; left: 465px;">
	<input type="button" value="<?echo GetMessage("FILEMAN_ED_CANC")?>" onclick="pObj.Close();"></div>

<?elseif($name=="settings"):?>
<script>
/*  ----------------------------------- SETTINGS --------------------------------------------*/
function OnLoad()
{
	oDialogTitle.innerHTML = '<?=GetMessage("FILEMAN_ED_SETTINGS")?>';
	// ************************ TAB #1: Toolbar settings ***********************************
	var oDiv = document.getElementById("__bx_set_1_toolbar");
	oDiv.innerHTML = '';
	window.temp_arToolbarSettings = copyObj(SETTINGS[pObj.pMainObj.name].arToolbarSettings);
	window.temp_arTaskbarSettings = copyObj(SETTINGS[pObj.pMainObj.name].arTaskbarSettings);
	_displayToolbarList(oDiv);
	oDiv = null
	// ************************ TAB #2: Taskbar settings ***********************************
	oDiv = document.getElementById("__bx_set_2_taskbar");
	oDiv.innerHTML = '';
	_displayTaskbarList(oDiv);
	
	// ************************ TAB #3: Additional Properties ***********************************
	oDiv = document.getElementById("__bx_set_3_add_props");
	oDiv.innerHTML = '';
	_displayAdditionalProps(oDiv);
	
	document.getElementById("restoreDefault").onclick = function(e){restoreSettings(pObj.pMainObj)};
}

function _displayToolbarList(oCont)
{
	var oTable = document.createElement("TABLE");
	oTable.width = "100%";
	_displayTitle(oTable,'<?=GetMessage("FILEMAN_ED_TLBR_DISP")?>');
	var _show;
	for(var sToolBarId in arToolbars)
		_displayToolbarRow(oTable,sToolBarId,SETTINGS[pObj.pMainObj.name].arToolbarSettings[sToolBarId].show);
		
	_displayTitle(oTable,'<?=GetMessage("FILEMAN_ED_DISP_SET")?>');
	oTr = oTable.insertRow(-1);
	oTr.className = "bxpropertysell";
	oTd = oTr.insertCell(-1);
	oTd.innerHTML = '<?=GetMessage("FILEMAN_ED_REM_TLBR")?>';
	oTd.align = "right";
	oTd = oTr.insertCell(-1);
	pCheckbox = pObj.pMainObj.CreateElement("INPUT", {'type':'checkbox', 'id': '__bx_rs_tlbrs'});
	oTd.appendChild(pCheckbox);
	oBXEditorUtils.setCheckbox(pCheckbox,pObj.pMainObj.RS_toolbars);
	oTd.align = "left";
	oCont.appendChild(oTable);
}

function _displayToolbarRow(oTb,toolbarId,_show)
{
	var oTr = oTb.insertRow(-1);
	oTr.className = "bxpropertysell";
	var oTd = oTr.insertCell(-1);
	oTd.innerHTML = arToolbars[toolbarId][0];
	oTd.align = "right";
	oTd.width = "60%";
	
	oTd = oTr.insertCell(-1);
	pCheckbox = pObj.pMainObj.CreateElement("INPUT", {'type':'checkbox', 'id': '__bx_'+toolbarId, '__bxid' : toolbarId});
	oTd.appendChild(pCheckbox);
	oBXEditorUtils.setCheckbox(pCheckbox,_show);
	if (toolbarId=="standart")
		pCheckbox.disabled = "disabled";
		
	pCheckbox.onchange = function(e) {window.temp_arToolbarSettings[this.getAttribute("__bxid")].show = this.checked;}
	oTd.align = "left"
	oTd.width = "40%";
}


function _displayTaskbarList(oCont)
{
	var oTable = document.createElement("TABLE");
	oTable.width = "100%";
	_displayTitle(oTable,'<?=GetMessage("FILEMAN_ED_TSKBR_DISP")?>');
	var _show;

	for(var k in ar_BXTaskbarS)
	{
		if (ar_BXTaskbarS[k].pMainObj.name==pObj.pMainObj.name)
			_displayTaskbarRow(oTable,ar_BXTaskbarS[k],SETTINGS[pObj.pMainObj.name].arTaskbarSettings[ar_BXTaskbarS[k].name]);
	}

	_displayTitle(oTable,'<?=GetMessage("FILEMAN_ED_DISP_SET")?>');
	oTr = oTable.insertRow(-1);
	oTr.className = "bxpropertysell";
	oTd = oTr.insertCell(-1);
	oTd.innerHTML = '<?=GetMessage("FILEMAN_ED_REM_TSKBR")?>';
	oTd.align = "right";
	oTd = oTr.insertCell(-1);
	pCheckbox = pObj.pMainObj.CreateElement("INPUT", {'type':'checkbox', 'id': '__bx_rs_tskbrs'});
	oTd.appendChild(pCheckbox);
	oBXEditorUtils.setCheckbox(pCheckbox,pObj.pMainObj.RS_taskbars);
	oTd.align = "left";
	oCont.appendChild(oTable);
}


function _displayTaskbarRow(oTb,oTaskbar,arSettings)
{
	_show = arSettings.show;
	//_alert(oTaskbar.name+": \n show: "+_show)
	var taskbarId = oTaskbar.name;
	var oTr = oTb.insertRow(-1);
	oTr.className = "bxpropertysell";
	var oTd = oTr.insertCell(-1);
	oTd.innerHTML = oTaskbar.title;
	oTd.align = "right";
	oTd.width = "60%";
	oTd = oTr.insertCell(-1);
	if (taskbarId != "BXComponentsTaskbar")
	{
		pCheckbox = pObj.pMainObj.CreateElement("INPUT", {'type':'checkbox', 'id': '__bx_'+taskbarId, '__bxid' : taskbarId});
		oTd.appendChild(pCheckbox);
		oBXEditorUtils.setCheckbox(pCheckbox,_show);
		pCheckbox.onchange = function(e) {window.temp_arTaskbarSettings[this.getAttribute("__bxid")].show = this.checked;}
	}
	else
	{
		pSelect = pObj.pMainObj.CreateElement("SELECT", {'id': '__bx_'+taskbarId, '__bxid' : taskbarId, 'className':'propselect'});
		var oOption = new Option('<?=GetMessage("FILEMAN_ED_ON")?>', "Y", false, false);
		pSelect.options.add(oOption);
		oOption = new Option('<?=GetMessage("FILEMAN_ED_OFF")?>', "N", false, false);
		pSelect.options.add(oOption);
		if (!_show)
			oOption.selected = true;
		
		oOption = new Option('<?=GetMessage("FILEMAN_ED_AUTO")?>', "A", false, false);
		pSelect.options.add(oOption);
		if (arSettings.auto==true)
			oOption.selected = true;
		oTd.appendChild(pSelect);
		pSelect.onchange = function(e)
			{
				if (this.value != 'A')
				{
					window.temp_arTaskbarSettings[this.getAttribute("__bxid")].show = (this.value=='Y') ? true : false;
					window.temp_arTaskbarSettings[this.getAttribute("__bxid")].auto = false;
				}
				else
					window.temp_arTaskbarSettings[this.getAttribute("__bxid")].auto = true;
			}
	}
	
	oTd.align = "left"
	oTd.width = "40%";
}


function _displayTitle(oTb, sTitle)
{
	var oTr = oTb.insertRow(-1);
	oTr.className = "heading_dialog";
	var oTd = oTr.insertCell(-1);
	oTd.colSpan = 2;
	oTd.innerHTML = sTitle;
}


function _displayAdditionalProps(oCont)
{
	var oTable = document.createElement("TABLE");
	oTable.width = "100%";
	_displayTitle(oTable,'');
	var _show;
	//for(var sToolBarId in arToolbars)
	//	_displayToolbarRow(oTable,sToolBarId,SETTINGS[pObj.pMainObj.name].arToolbarSettings[sToolBarId].show);
		
	//_displayTitle(oTable,'<?=GetMessage("FILEMAN_ED_DISP_SET")?>');
	oTr = oTable.insertRow(-1);
	oTr.className = "bxpropertysell";
	oTd = oTr.insertCell(-1);
	oTd.innerHTML = '<?=GetMessage("FILEMAN_ED_SHOW_TOOLTIPS")?>';
	oTd.align = "right";
	oTd.width = "60%";
	oTd = oTr.insertCell(-1);
	pCheckbox = pObj.pMainObj.CreateElement("INPUT", {'type':'checkbox', 'id': '__bx_show_tooltips'});
	oTd.appendChild(pCheckbox);
	oBXEditorUtils.setCheckbox(pCheckbox,pObj.pMainObj.showTooltips4Components);
	oTd.align = "left";
	oTd.width = "40%";
	oCont.appendChild(oTable);
}


function restoreSettings(pMainObj)
{
	SETTINGS[pObj.pMainObj.name].arToolbarSettings = arToolbarSettings_default;
	var postData = oBXEditorUtils.ConvertArray2Post(SETTINGS[pObj.pMainObj.name].arToolbarSettings,'tlbrset');
	__setConfiguration(pMainObj,"toolbars","POST",postData);
	BXRefreshToolbars(pMainObj);
	
	SETTINGS[pObj.pMainObj.name].arTaskbarSettings = arTaskbarSettings_default;
	var postData = oBXEditorUtils.ConvertArray2Post(temp_arTaskbarSettings,'tskbrset');
	__setConfiguration(pObj.pMainObj,"taskbars","POST",postData);
	BXRefreshTaskbars(pObj.pMainObj);
	
	pObj.Close();
}


function OnSave()
{
	if (!document.getElementById("__bx_rs_tlbrs").checked)
		temp_arToolbarSettings = arToolbarSettings_default;

	if (!document.getElementById("__bx_rs_tskbrs").checked)
		temp_arTaskbarSettings = arTaskbarSettings_default;
	
	var showTooltips4Components_new = (document.getElementById("__bx_show_tooltips").checked) ? true : false;
	if (showTooltips4Components_new != pObj.pMainObj.showTooltips4Components)
	{
		pObj.pMainObj.showTooltips4Components = showTooltips4Components_new;
		__setConfiguration(pObj.pMainObj,"tooltips","GET");
	}
	
	if (!compareObj(SETTINGS[pObj.pMainObj.name].arToolbarSettings,window.temp_arToolbarSettings) ||
		(document.getElementById("__bx_rs_tlbrs").checked != pObj.pMainObj.RS_toolbars))
	{
		pObj.pMainObj.RS_toolbars = document.getElementById("__bx_rs_tlbrs").checked;
		SETTINGS[pObj.pMainObj.name].arToolbarSettings = temp_arToolbarSettings;
		var postData = oBXEditorUtils.ConvertArray2Post(temp_arToolbarSettings,'tlbrset');
		__setConfiguration(pObj.pMainObj,"toolbars","POST",postData);
		BXRefreshToolbars(pObj.pMainObj);
	}

	if (!compareObj(SETTINGS[pObj.pMainObj.name].arTaskbarSettings,window.temp_arTaskbarSettings) || 
		(document.getElementById("__bx_rs_tskbrs").checked != pObj.pMainObj.RS_taskbars))
	{
		pObj.pMainObj.RS_taskbars = document.getElementById("__bx_rs_tskbrs").checked;
		SETTINGS[pObj.pMainObj.name].arTaskbarSettings = temp_arTaskbarSettings;
		var postData = oBXEditorUtils.ConvertArray2Post(temp_arTaskbarSettings,'tskbrset');
		__setConfiguration(pObj.pMainObj,"taskbars","POST",postData);
		BXRefreshTaskbars(pObj.pMainObj);
	}
}

</script>

<?
		$aTabs_dialog = array(
		array("DIV" => "__bx_set_1_toolbar", "TAB" => GetMessage("FILEMAN_ED_TOOLBARS"), "ICON" => "", "TITLE" => GetMessage("FILEMAN_ED_TOOLBARS_SETTINGS")),
		array("DIV" => "__bx_set_2_taskbar", "TAB" => GetMessage("FILEMAN_ED_TASKBARS"), "ICON" => "", "TITLE" => GetMessage("FILEMAN_ED_TASKBARS_SETTINGS")),
		array("DIV" => "__bx_set_3_add_props", "TAB" => GetMessage("FILEMAN_ED_ADDITIONAL_PROPS"), "ICON" => "", "TITLE" => GetMessage("FILEMAN_ED_ADDITIONAL_PROPS")),
		);
		//array("DIV" => "edit3", "TAB" => GetMessage("MAIN_TAB_RIGHTS"), "ICON" => "fileman_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS")),

		$tabControl_dialog = new CAdmintabControl_dialog("tabControl_dialog", $aTabs_dialog, false);
		//$tabControl_dialog = new CAdmintabControl("tabControl_dialog", $aTabs_dialog);
		$tabControl_dialog->Begin();
?>
	
		
<?$tabControl_dialog->BeginNextTab();?>
<div id="__bx_set_1_toolbar">&nbsp;</div>
<?$tabControl_dialog->BeginNextTab();?>
<div id="__bx_set_2_taskbar">&nbsp;</div>
<?$tabControl_dialog->BeginNextTab();?>
<div id="__bx_set_3_add_props">&nbsp;</div>
<?$tabControl_dialog->End();?>

<?elseif($name=="flash"):?>
<script>
/*  ----------------------------------- F L A S H --------------------------------------------*/
var prevsrc = "";
function OnLoad()
{
	oDialogTitle.innerHTML = '<?=GetMessage("FILEMAN_ED_FLASH")?>';
	pElement = pObj.pMainObj.GetSelectionObject();
		//_alert(pElement.getAttribute("__bxtagname"));
	
	if(pElement.getAttribute("__bxtagname") == "flash")
	{
		//document.getElementById("preview").onload = _LPreview;
		//oDialogTitle.innerHTML = 'Редактирование Flash-ролика';		
	}
	else
	{
		//pElement = null;
		//oDialogTitle.innerHTML = 'Вставка Flash-ролика';
	}
	
	// ************************ TAB #1: Base params *************************************
	var oDiv = document.getElementById("__bx_base_params");
	oDiv.style.padding = "5px";
	oDiv.innerHTML = 	'<table width="100%" border="0">'+
							'<tr>'+
								'<td align="right" width="40%"><?=GetMessage("FILEMAN_PATH_2_SWF")?></td>'+
								'<td width="60%" colspan="3">'+
									'<input type="text" size="30" value="" id="flash_src" name="src">'+
									'<input type="button" value="..." id="OpenFileBrowserWindFlash_button">'+
								'</td>'+
							'</tr>'+
							'<tr>'+
								'<td align="right"><?=GetMessage("FILEMAN_ED_IMG_W")?></td>'+
								'<td width="60px"><input type="text" size="3" id="flash_width"></td>'+
								'<td width="80px"align="right"><?=GetMessage("FILEMAN_ED_IMG_H")?></td>'+
								'<td width="130px"><input type="text" size="3" id="flash_height"></td>'+
							'</tr>'+
							'<tr>'+
								'<td align="right" valign="top"><?=GetMessage("FILEMAN_ED_IMG_PREV")?></td>'+
								'<td colspan="3">'+
									'<div id="flash_preview_cont" style="height:200px; width:95%; overflow: hidden; border: 1px #999999 solid; overflow-y: auto; overflow-x: auto;">'+
									'</div>'+
								'</td>'+
							'</tr>'+
						'</table>';
	
	//Attaching Events
	document.getElementById("OpenFileBrowserWindFlash_button").onclick = OpenFileBrowserWindFlash;
	document.getElementById("flash_src").onchange = _Flash_Reload;
	
	// ************************ TAB #2: Additional params ***********************************
	var oDiv = document.getElementById("__bx_additional_params");
	oDiv.style.padding = "5px";
	oDiv.innerHTML = 	'<table width="100%" border="0">'+
							'<tr>'+
								'<td align="right" width="40%" colspan="2"><?=GetMessage("FILEMAN_ED_ID")?></td>'+
								'<td width="60%" colspan="2">'+
									'<input type="text" size="30" value="" id="_flash_id">'+
								'</td>'+
							'</tr>'+
							'<tr>'+
								'<td align="right" colspan="2"><?=GetMessage("FILEMAN_ED_TITLE")?></td>'+
								'<td colspan="2">'+
									'<input type="text" size="30" value="" id="_flash_title">'+
								'</td>'+
							'</tr>'+
							'<tr>'+
								'<td align="right" colspan="2"><?=GetMessage("FILEMAN_ED_CLASSNAME")?></td>'+
								'<td colspan="2">'+
									'<input type="text" size="30" value="" id="_flash_classname">'+
								'</td>'+
							'</tr>'+
							'<tr>'+
								'<td align="right" colspan="2"><?=GetMessage("FILEMAN_ED_STYLE")?></td>'+
								'<td colspan="2">'+
									'<input type="text" size="30" value="" id="_flash_style">'+
								'</td>'+
							'</tr>'+
							'<tr>'+
								'<td align="right" colspan="2"><?=GetMessage("FILEMAN_ED_QUALITY")?></td>'+
								'<td colspan="2">'+
									'<select id="_flash_quality" style="width:100px">'+
										'<option value=""></option>'+
										'<option value="low">low</option>'+
										'<option value="medium">medium</option>'+
										'<option value="high">high</option>'+
										'<option value="autolow">autolow</option>'+
										'<option value="autohigh">autohigh</option>'+
										'<option value="best">best</option>'+
									'</select>'+
								'</td>'+
							'</tr>'+
							'<tr>'+
								'<td align="right" colspan="2"><?=GetMessage("FILEMAN_ED_WMODE")?></td>'+
								'<td colspan="2">'+
									'<select id="_flash_wmode" style="width:100px">'+
										'<option value=""></option>'+
										'<option value="window">window</option>'+
										'<option value="opaque">opaque</option>'+
										'<option value="transparent">transparent</option>'+
									'</select>'+
								'</td>'+
							'</tr>'+
							'<tr>'+
								'<td align="right" colspan="2"><?=GetMessage("FILEMAN_ED_SCALE")?></td>'+
								'<td colspan="2">'+
									'<select id="_flash_scale"style="width:100px">'+
										'<option value=""></option>'+
										'<option value="showall">showall</option>'+
										'<option value="noborder">noborder</option>'+
										'<option value="exactfit">exactfit</option>'+
									'</select>'+
								'</td>'+
							'</tr>'+
							'<tr>'+
								'<td align="right" colspan="2"><?=GetMessage("FILEMAN_ED_SALIGN")?></td>'+
								'<td colspan="2">'+
									'<select id="_flash_salign" style="width:100px">'+
										'<option value=""></option> '+
										'<option value="left">left</option> '+
										'<option value="top">top</option> '+
										'<option value="right">right</option> '+
										'<option value="bottom">bottom</option> '+
										'<option value="top left">left</option>'+
										'<option value="top right">top</option>'+
										'<option value="bottom left">right</option>'+
										'<option value="bottom right">bottom</option>'+
									'</select>'+
								'</td>'+
							'</tr>'+
							'<tr>'+
								'<td align="right" colspan="2"><?=GetMessage("FILEMAN_ED_AUTOPLAY")?></td>'+
								'<td colspan="2">'+
									'<input type="checkbox" value="" id="_flash_autoplay">'+
								'</td>'+
							'</tr>'+
							'<tr>'+
								'<td align="right" colspan="2"><?=GetMessage("FILEMAN_ED_LOOP")?></td>'+
								'<td colspan="2">'+
									'<input type="checkbox" value="" id="_flash_loop">'+
								'</td>'+
							'</tr>'+
							'<tr>'+
								'<td align="right" colspan="2"><?=GetMessage("FILEMAN_ED_SHOW_MENU")?></td>'+
								'<td colspan="2">'+
									'<input type="checkbox" value="" id="_flash_showmenu">'+
								'</td>'+
							'</tr>'+
						'</table>';
	
}

function _Flash_Reload(bFirst)
{
	var flash_preview = document.getElementById("flash_preview_iframe");
	if (flash_preview)
		flash_preview.parentNode.removeChild(flash_preview);
	
	var flash_src = document.getElementById("flash_src").value;
	var oPreviewCont = document.getElementById("flash_preview_cont");
	var pFrame = pObj.pMainObj.CreateElement("IFRAME", {id: "flash_preview_iframe", name: "edloader_"+name});
	pFrame.setAttribute("src", "javascript:''");
	pFrame.style.width = "100%";
	pFrame.style.height = "100%";
	pFrame = oPreviewCont.appendChild(pFrame);
	var pr_width = "150px";
	var pr_height = "150px";
	//_alert(flash_src+"\n"+jsUtils.urlencode(flash_src));
	pFrame.setAttribute("src", "fileman_flash_preview.php?path="+jsUtils.urlencode(flash_src)+"&width="+pr_width+"&height="+pr_height);
	
}

function SetUrl(filename,path,site)
{
	var url = path+'/'+filename;
	document.getElementById("flash_src").value = url;
	if(document.getElementById("flash_src").onchange)
		document.getElementById("flash_src").onchange();
}

function OnSave()
{
	pObj.pMainObj.bSkipChanges = true;
	if(true)
	{
		var _arParams = {
				src			:	document.getElementById("flash_src").value,		
				width		:	document.getElementById("flash_width").value,
				height		:	document.getElementById("flash_height").value,
				id			:	document.getElementById("_flash_id").value,
				title		:	document.getElementById("_flash_title").value,
				classname	:	document.getElementById("_flash_classname").value,
				style		:	document.getElementById("_flash_style").value,
				quality		:	document.getElementById("_flash_quality").value,
				wmode		:	document.getElementById("_flash_wmode").value,
				scale		:	document.getElementById("_flash_scale").value,
				salign		:	document.getElementById("_flash_salign").value,
				autoplay	:	(document.getElementById("_flash_autoplay").checked) ? true : false,
				loop		:	(document.getElementById("_flash_loop").checked) ? true : false,
				showmenu	:	(document.getElementById("_flash_showmenu").checked) ? true : false
			}

		var id = "__bx_flash_"+Math.random();
		var icon = "/bitrix/images/fileman/htmledit2/1.gif";
		var style = "border: black 1px dashed; background-color:#c0c0c0; background-image: url(/bitrix/images/fileman/htmledit2/flash.gif); background-position: center center;	background-repeat: no-repeat;";
		//var str = '<img id="'+id+'" src="'+icon+'" style="'+style+'" border="0" height="'+((_arParams.height>32) ? _arParams.height : 32)+'" width="'+((_arParams.width>32) ? _arParams.width : 32)+'" __bxtagname="flash" __bxcontainer="' + bxhtmlspecialchars(BXSerialize(_arParams)) + '" />';
		BXSelectRange(oPrevRange,pObj.pMainObj.pEditorDocument,pObj.pMainObj.pEditorWindow);
		//pObj.pMainObj.insertHTML(str);
		//pElement = pObj.pMainObj.pEditorDocument.getElementById(id);
		str = 	'<EMBED src="love.swf"'+
				'src = "'+_arParams.src+'" '+
				((_arParams.width) ? 'width = "'+_arParams.width+'" ' : '')+
				((_arParams.height) ? 'height = "'+_arParams.height+'" ' : '')+
				((_arParams.id) ? 'id = "'+_arParams.id+'" ' : '')+
				((_arParams.title) ? 'title = "'+_arParams.title+'" ' : '')+
				((_arParams.classname) ? 'className = "'+_arParams.classname+'" ' : '')+
				((_arParams.style) ? 'style = "'+_arParams.style+'" ' : '')+
				((_arParams.quality) ? 'quality = "'+_arParams.quality+'" ' : '')+
				((_arParams.wmode) ? 'wmode = "'+_arParams.wmode+'" ' : '')+
				((_arParams.scale) ? 'scale = "'+_arParams.scale+'" ' : '')+
				((_arParams.salign) ? 'salign = "'+_arParams.salign+'" ' : '')+
				((_arParams.autoplay) ? 'autoplay = "'+_arParams.autoplay+'" ' : '')+
				((_arParams.loop) ? 'loop = "'+_arParams.loop+'" ' : '')+
				((_arParams.showmenu) ? 'showmenu = "'+_arParams.showmenu+'" ' : '')+
				'TYPE = "application/x-shockwave-flash" '+
				'PLUGINSPAGE = "http://www.macromedia.com/go/getflashplayer" '+
				'></EMBED>';

		pObj.pMainObj.insertHTML(str);
		
		//var tmpid = Math.random().toString().substring(2);
		//var str = '<img id="'+tmpid+'" __bxsrc="'+bxhtmlspecialchars(document.getElementById("src").value)+'" />';
		//pElement.removeAttribute("id");
	}
	/*
	SAttr(pElement, "width", document.getElementById("width").value);
	SAttr(pElement, "height", document.getElementById("height").value);
	SAttr(pElement, "hspace", document.getElementById("hspace").value);
	SAttr(pElement, "vspace", document.getElementById("vspace").value);
	SAttr(pElement, "border", document.getElementById("border").value);
	SAttr(pElement, "align", document.getElementById("align").value);
	SAttr(pElement, "src", document.getElementById("src").value);
	SAttr(pElement, "__bxsrc", document.getElementById("src").value);
	SAttr(pElement, "alt", document.getElementById("alt").value);
*/
	pObj.pMainObj.bSkipChanges = false;
	//pObj.pMainObj.OnChange("image");	
	
	//_alert("-----"+returnProperties(_arParams));
}

</script>

<?

if(strlen($str_FILENAME)>0)
{
	$APPLICATION->ShowFileSelectDialog(
		"OpenFileBrowserWindFlash",
		array("FUNCTION_NAME" => "SetUrl"),
		array("PATH" => GetDirPath($str_FILENAME),"SITE" => $_GET["site"])
	);
}
else
{	
	$APPLICATION->ShowFileSelectDialog(
		"OpenFileBrowserWindFlash",
		array("FUNCTION_NAME" => "SetUrl"),
		array("SITE" => $_GET["site"])
	);
}

$aTabs_dialog = array(
array("DIV" => "__bx_base_params", "TAB"=>GetMessage("FILEMAN_ED_BASE_PARAMS"), "ICON" => "", "TITLE" => GetMessage("FILEMAN_ED_BASE_PARAMS")),
array("DIV" => "__bx_additional_params", "TAB"=>GetMessage("FILEMAN_ED_ADD_PARAMS"), "ICON" => "", "TITLE"=>GetMessage("FILEMAN_ED_ADD_PARAMS")),
);
$tabControl_dialog = new CAdmintabControl_dialog("tabControl_dialog", $aTabs_dialog, false);

$tabControl_dialog->Begin();?>
<?$tabControl_dialog->BeginNextTab();?>
<div id="__bx_base_params"></div>
<?$tabControl_dialog->BeginNextTab();?>
<div id="__bx_additional_params"></div>
<?$tabControl_dialog->End();?>

<?elseif($name=="snippet"):?>
<script>
/*  #################    S N I P P E T S  ##################  */
var prevsrc = "";
function OnLoad()
{
	oDialogTitle.innerHTML = '<?=GetMessage("FILEMAN_ED_ADD_SNIPPET")?>';
	var st = document.getElementById('__snippet_template');
	st.options[1].value = st.options[1].innerHTML = pObj.pMainObj.templateID;
	
	document.getElementById('saveBut').onclick = function(e)
	{
		__OnSave();
	}
}

function __OnSave()
{
	var name = document.getElementById("__snippet_name").value;
	var template = document.getElementById("__snippet_template").value;
	var code = document.getElementById("__snippet_code").value;
	
	//_alert(name+"\n"+title+"\n"+description+"\n"+template+"\n"+code);

	if (name=="" || code=="")
	{
		alert("<?=GetMessage("FILEMAN_ED_WRONG_PARAMS")?>");
		return;
	}

	if (template == "")
		template = ".default";
		
	checkFileName(___OnSave,name,template);
}

function ___OnSave(ok,fileName,templateId)
{
	if (!ok && !confirm("<?=GetMessage("FILEMAN_ED_FILE_EXISTS")?>"))
		return;
	
	saveSnippet(fileName,templateId);
}

function checkFileName(callback,fileName,templateId)
{
	var cfn_CHttpRequest = new JCHttpRequest();
	window.snippet_file_exists = false;
	cfn_CHttpRequest.Action = function(result)
	{
		try
		{
			setTimeout(function ()
				{
					callback.apply(obj,[!snippet_file_exists,fileName,templateId]);
				}, 5
			);
		}
		catch(e)
		{
			_alert('error: loadSnippets');
		}
	}
	
	try
	{
		cfn_CHttpRequest.Send('fileman_load_snippets.php?lang='+BXLang+'&site='+BXSite+'&templateID='+templateId+'&target=check&filename='+escape(fileName));
	}
	catch(e)
	{
		callback.apply(obj,[!snippet_file_exists,fileName,templateId]);
	}
}


function saveSnippet(fileName,templateId)
{
	var title = document.getElementById("__snippet_title").value;
	var description = document.getElementById("__snippet_description").value;
	
	if (title=="")
		title = fileName;
	
	var code = document.getElementById("__snippet_code").value;
	var postData = "filename="+escape(fileName)+"&code="+escape(code)+"&title="+escape(title)+"&description="+escape(description);
	
	var ss_CHttpRequest = new JCHttpRequest();
	ss_CHttpRequest.Action = function(result){}
	
	try
	{
		ss_CHttpRequest.Post('fileman_load_snippets.php?lang='+BXLang+'&site='+BXSite+'&templateID='+templateId+'&target=add',postData);
	}
	catch(e)
	{
		_alert("ERROR: !!!: saveSnippet");
	}
	
	var c = "sn_"+Math.round(Math.random()*1000000);
	pObj.params.BXSnippetsTaskbar.arSnippetsCodes[c] = fileName;
	
	__arEl = {
		name:fileName,
		title:title,
		tagname:'snippet',
		isGroup:false,
		icon:'/bitrix/images/fileman/htmledit2/snippet.gif',
		path:'',
		code:code,
		params:{
			c:				c,
			code:			pObj.params.BXSnippetsTaskbar.Remove__script__(code),
			description:	description
		}
	};
	
	arSnippets[fileName] = __arEl;
	
	pObj.params.BXSnippetsTaskbar.AddElement(__arEl,pObj.params.BXSnippetsTaskbar.pCellSnipp,__arEl.path);
	pObj.params.BXSnippetsTaskbar.AddSnippet_button();
	pObj.Close();
}

function OnSave(){}

</script>
<div style="width:100%; height:100%; padding: 10px">
<table class="add_snippet" border="0" cellpadding="2">
	<tr>
		<td width="40%" align="right"><?=GetMessage("FILEMAN_ED_NAME")?></td>
		<td width="60%"><input id="__snippet_name" type="text"></td>
	</tr>
	<tr>
		<td align="right"><?=GetMessage("FILEMAN_ED_TITLE")?></td>
		<td><input id="__snippet_title" type="text"></td>
	</tr>
	<tr>
		<td align="right"><?=GetMessage("FILEMAN_ED_DESCRIPTION")?></td>
		<td><textarea id="__snippet_description"></textarea></td>
	</tr>
	<tr>
		<td align="right"><?=GetMessage("FILEMAN_ED_TEMPLATE")?></td>
		<td>
			<select id="__snippet_template">
				<option value=".default">.default</option>
				<option value="111">222</option>
			</select>
		</td>
	</tr>
	<tr>
		<td colspan = "2"><?=GetMessage("FILEMAN_ED_CODE")?>:</td>
	</tr>
	<tr height="100%">
		<td colspan = "2">
			<textarea id="__snippet_code" style="height:100%"></textarea>
		</td>
	</tr>
</table>
</div>




<?elseif($name=="edit_hbf"):?>
<script>
//*********************** EDIT_HBF ************************//
function OnLoad()
{
	oDialogTitle.innerHTML = '<?=GetMessage("FILEMAN_ED_EDIT_HBF")?>';
	
	// ************************ TAB #1: HEAD *************************************
	var oDiv = document.getElementById("__bx_head");
	oDiv.style.padding = "5px";
	var newCell = titleTable = oDiv.getElementsByTagName("TABLE")[0].rows[1].insertCell(1);
	newCell.style.paddingRight = (BXIsIE() ? "12px" : "2px");
	var _insertDefaultImg = pObj.pMainObj.CreateElement("IMG", {"src": "/bitrix/images/fileman/htmledit2/insertDefault.gif", "width": 16,"height":16,"title": "<?=GetMessage("FILEMAN_ED_RESTORE")?>", "alt": "<?=GetMessage("FILEMAN_ED_RESTORE")?>"});
	_insertDefaultImg.onclick = function(e)
		{
			if (!confirm("<?=GetMessage("FILEMAN_ED_CONFIRM_HEAD")?>"))
				return;
			
			var oTA = document.getElementById("__bx_head_ta");
			oTA.value = String.fromCharCode(60)+'?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?'+String.fromCharCode(62)+'<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">'+"\n"+
			'<html>'+"\n"+
			'<head>'+"\n"+
			'<meta http-equiv="Content-Type" content="text/html; charset='+String.fromCharCode(60)+'?echo LANG_CHARSET;?'+String.fromCharCode(62)+'">'+"\n"+
			String.fromCharCode(60)+'?$APPLICATION->ShowMeta("keywords")?'+String.fromCharCode(62)+"\n"+
			String.fromCharCode(60)+'?$APPLICATION->ShowMeta("description")?'+String.fromCharCode(62)+"\n"+
			'<title>'+String.fromCharCode(60)+'?$APPLICATION->ShowTitle()?'+String.fromCharCode(62)+'</title>'+"\n"+
			String.fromCharCode(60)+'?$APPLICATION->ShowCSS();?'+String.fromCharCode(62)+"\n"+
			"</head>\n"+
			'<body>';
		};
	newCell.appendChild(_insertDefaultImg);
	
	oTA = document.createElement('TEXTAREA');
	oTA.id = "__bx_head_ta";
	oTA.style.width = "100%";
	oTA.style.height = "280px";
	oTA.value = pObj.pMainObj._head+pObj.pMainObj._body;
	oDiv.appendChild(oTA);

	// ************************ TAB #2: BODY ***********************************
	//var oDiv = document.getElementById("__bx_body");
	//oDiv.style.padding = "5px";
	
	var newCell = titleTable = oDiv.getElementsByTagName("TABLE")[0].rows[1].insertCell(1);
	newCell.style.paddingRight = (BXIsIE() ? "12px" : "2px");
	var _insertDefaultImg = pObj.pMainObj.CreateElement("IMG", {"src": "/bitrix/images/fileman/htmledit2/insertDefault.gif", "width": 16,"height":16,"title": "<?=GetMessage("FILEMAN_ED_INSERT_DEF")?>", "alt": "<?=GetMessage("FILEMAN_ED_INSERT_DEF")?>"});
	_insertDefaultImg.onclick = function(e)
		{
			if (!confirm("<?=GetMessage("FILEMAN_ED_CONFIRM_BODY")?>"))
				return;
			
			var oTA = document.getElementById("__bx_body_ta");
			oTA.value = "<body>";
		};
	//newCell.appendChild(_insertDefaultImg);
	
	oTA = document.createElement('TEXTAREA');
	oTA.id = "__bx_body_ta";
	oTA.style.width = "100%";
	oTA.style.height = "280px";
	oTA.value = pObj.pMainObj._body;
	//oDiv.appendChild(oTA);
	
	// ************************ TAB #3: Footer ***********************************
	var oDiv = document.getElementById("__bx_footer");
	oDiv.style.padding = "5px";
	
	var newCell = titleTable = oDiv.getElementsByTagName("TABLE")[0].rows[1].insertCell(1);
	newCell.style.paddingRight = (BXIsIE() ? "12px" : "2px");
	var _insertDefaultImg = pObj.pMainObj.CreateElement("IMG", {"src": "/bitrix/images/fileman/htmledit2/insertDefault.gif", "width": 16,"height":16,"title": "<?=GetMessage("FILEMAN_ED_INSERT_DEF")?>", "alt": "<?=GetMessage("FILEMAN_ED_INSERT_DEF")?>"});
	_insertDefaultImg.onclick = function(e)
		{
			if (!confirm("<?=GetMessage("FILEMAN_ED_CONFIRM_FOOTER")?>"))
				return;
			
			var oTA = document.getElementById("__bx_footer_ta");
			oTA.value = "</body>\n</html>";
		};
	newCell.appendChild(_insertDefaultImg);
	
	oTA = document.createElement('TEXTAREA');
	oTA.id = "__bx_footer_ta";
	oTA.style.width = "100%";
	oTA.style.height = "280px";
	oTA.value = pObj.pMainObj._footer;
	oDiv.appendChild(oTA);	
}


function OnSave()
{
	document.getElementById("__bx_head_ta").value.replace(/(^[\s\S]*?)(<body.*?>)/i, "");
	pObj.pMainObj._head = RegExp.$1;
	pObj.pMainObj._body = RegExp.$2;
	
	//pObj.pMainObj._head = document.getElementById("__bx_head_ta").value;
	//pObj.pMainObj._body = document.getElementById("__bx_body_ta").value;
	pObj.pMainObj._footer = document.getElementById("__bx_footer_ta").value;
	pObj.pMainObj.updateBody();
}


</script>
<?
$aTabs_dialog = array(
array("DIV" => "__bx_head", "TAB" => GetMessage("FILEMAN_ED_TOP_AREA"), "ICON" => "", "TITLE" => GetMessage("FILEMAN_ED_EDIT_HEAD")),
//array("DIV" => "__bx_body", "TAB" => "BODY", "ICON" => "", "TITLE" => GetMessage("FILEMAN_ED_EDIT_BODY")),
array("DIV" => "__bx_footer", "TAB" => GetMessage("FILEMAN_ED_BOTTOM_AREA"), "ICON" => "", "TITLE" => GetMessage("FILEMAN_ED_EDIT_FOOTER")),
);
$tabControl_dialog = new CAdmintabControl_dialog("tabControl_dialog", $aTabs_dialog, false);

$tabControl_dialog->Begin();?>
<?$tabControl_dialog->BeginNextTab();?>
<div id="__bx_head"></div>
<?//$tabControl_dialog->BeginNextTab();?>
<!--<div id="__bx_body"></div> -->
<?$tabControl_dialog->BeginNextTab();?>
<div id="__bx_footer"></div>
<?$tabControl_dialog->End();?>




<?endif;?>

</td></tr>
<?if($not_use_default!='Y'):?>
	<tr id="buttonsSec">
	<td align="center" valign="top">
	<div class="buttonCont">
		<input id="saveBut" type="button" value="<?echo GetMessage("FILEMAN_ED_SAVE")?>">
		<input id="cancelBut" type="button" value="<?echo GetMessage("FILEMAN_ED_CANC")?>" onclick="pObj.Close();">	
		<?if($name=="settings"):?>
			<input id="restoreDefault" type="button" value="<?echo GetMessage('FILEMAN_ED_RESTORE');?>" title="<?echo GetMessage('FILEMAN_ED_RESTORE');?>">	
		<?endif;?>
	</div>
	</td>
	</tr>
<?endif?>
</table>
<script>
<?if($not_use_default!='Y'):?>
	document.getElementById("buttonsSec").style.height = (BXIsIE()) ? 25 : 45;
	document.getElementById('saveBut').onclick = __OnSave;
<?endif?>		
__OnLoad();
</script>
</form>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>
