<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");

$FM_RIGHT = $APPLICATION->GetGroupRight("fileman");
if($FM_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

//$APPLICATION->SetAdditionalCSS("/bitrix/modules/fileman/fileman_admin.css");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
IncludeModuleLangFile(__FILE__);

define("FROMDIALOGS", true);
?>
<HTML>
<HEAD>
<STYLE TYPE="text/css">
BODY   {margin-left:10; margin-right:10; font-family:Arial; font-size:12px; background:#e2dfda;}
IFRAME {background:#FFFFFF;}
BUTTON {width:5em}
TABLE  {font-family:Arial; font-size:11px}
SELECT  {font-family:Arial; font-size:11px}
INPUT  {font-family:Arial; font-size:11px; height: 20px;}
P      {text-align:center}
<?
if (strlen($APPLICATION->GetAdditionalCSS())>0)
{
	require($_SERVER["DOCUMENT_ROOT"].$APPLICATION->GetAdditionalCSS());
}
?>
</STYLE>

<script>
function OpenFileBrowserWind(type)
{
	var width = 510;
	var height = 520;
	if(type == 'image')
		width = 600;
	pWnd = window.open('/bitrix/admin/fileman_file_browser.php?lang=<?=LANG?><? if (strlen($_GET["site"]) > 0) echo "&site=".$_GET["site"]; ?>&type='+type, '_BImg', 'height='+width+',width='+height+',toolbar=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes,alwaysRaised=yes,dialog=yes');
	pWnd.resizeTo(width, height);
	pWnd.moveTo((screen.width - width)/2, (screen.height - height)/2);
}

function __OnLoad()
{
	if(window.OnLoad)
		window.OnLoad();
	document.onkeypress = KeyPress;
}

var iNoOnSelectionChange = 1;
var iNoOnChange = 2;

function __OnSave()
{
	var r = 0;
	if(window.OnSave)
		r = window.OnSave();

	if((r & 'NoOnSelectionChange') != 0)
		window.dialogArguments.pMainObj.OnEvent("OnSelectionChange", ["always"]);

	window.close();
}

function KeyPress(e)
{
	if(window.event)
		e = window.event;
	if(e.keyCode == 27)
		window.close();
	if(e.keyCode == 13)
	{
		if(e.target)
			e.targetElement = e.target;
		else if(e.srcElement)
			e.targetElement = e.srcElement;
		if(e.targetElement.tagName && e.targetElement.tagName.toUpperCase()=='INPUT' && (e.targetElement.type.toUpperCase()=='SUBMIT' || e.targetElement.type.toUpperCase()=='BUTTON'))
			return;
		__OnSave();
	}
}

var pMainObj = window.dialogArguments.pMainObj;
</script>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?echo LANG_CHARSET?>">
<script type="text/javascript" src="/bitrix/admin/fileman_js.php?lang=<?=LANGUAGE_ID?>"></script>
<?
$arr = Array("common.js", "controls.js");
for($i=0; $i<count($arr); $i++):
?>
<script type="text/javascript" src="/bitrix/admin/htmleditor2/<?=$arr[$i]?>?v=<?=@filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/htmleditor2/'.$arr[$i])?>"></script>
<?
endfor;
?>
</HEAD>
<BODY id="bod" onload="__OnLoad()">
<form id="form1" name="form1" onsubmit="return false;">
<input type="hidden" name="logical" value="<?=htmlspecialchars($logical)?>">
<table height="100%" width="100%"><tr height="90%"><td valign="top">

<?if($name=="anchor"):?>

<script>
var pElement = null;
function OnLoad()
{
	pElement = window.dialogArguments.pMainObj.GetSelectionObject();
	if(!pElement || !pElement.tagName)
	{
		window.close();
		return;
	}

	document.title = '<?=GetMessage("FILEMAN_ED_LINK_TITLE")?>';

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
	var obj = window.dialogArguments.pMainObj;
	pElement = obj.GetSelectionObject();
	pMainObj.bSkipChanges = true;
	if(pElement && pElement.getAttribute("__bxtagname")=="anchor")
	{
		if(document.getElementById("anchor_value").value.length<=0)
			obj.executeCommand('Delete');
		else
			pElement.setAttribute("__bxcontainer", BXSerialize({"name":document.getElementById("anchor_value").value}));
	}
	else
	{
		if(document.getElementById("anchor_value").value.length>0)
		{
			var tmp_id = Math.random().toString().substring(2);
			obj.insertHTML('<img id="'+tmp_id+'" src="/bitrix/images/fileman/htmledit2/anchor.gif" width="20" height="20" __bxtagname="anchor" __bxcontainer="'+bxhtmlspecialchars(BXSerialize({'name':document.getElementById("anchor_value").value}))+'"/>');
			var pComponent = obj.pEditorDocument.getElementById(tmp_id);
			pComponent.removeAttribute('id');
			if(obj.pEditorWindow.getSelection)
				obj.pEditorWindow.getSelection().selectAllChildren(pComponent);
		}
	}
	pMainObj.bSkipChanges = false;
	pMainObj.OnChange("anchor");
}
</script>
<?echo GetMessage("FILEMAN_ED_ANCHOR_NAME")?> <input type="text" size="30" value="" id="anchor_value">

<?elseif($name=="link"):?>

<script>
alert('wwwwwwwwwwwwwwwwww');
var pElement = null;
function OnLoad()
{
	_Ch();
	pElement = BXFindParentByTagName(window.dialogArguments.pMainObj.GetSelectionObject(), 'A');

	var arStFilter = ['A', 'DEFAULT'], i;
	var elStyles = document.getElementById("classname");
	var oOption = new Option("", "", false, false);
	elStyles.options.add(oOption);
	var arStyles;
	for(i=0; i<arStFilter.length; i++)
	{
		arStyles = pMainObj.oStyles.GetStyles(arStFilter[i]);
		for(var j=0; j<arStyles.length; j++)
		{
			if(arStyles[j].className.length<=0)
				continue;
			oOption = new Option(arStyles[j].className, arStyles[j].className, false, false);
			elStyles.options.add(oOption);
		}
	}

	var arAnchs = [], anc;
	var arImgs = pMainObj.pEditorDocument.getElementsByTagName('IMG');
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
		document.title = '<?=GetMessage("FILEMAN_ED_LE_TITLE")?>';
	else
		document.title = '<?=GetMessage("FILEMAN_ED_LN_TITLE")?>';

	if(pElement)
	{
		if(pElement.tagName.toLowerCase() == 'a')
		{
			pMainObj.SelectElement(pElement);

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
	alert('OnSave()');
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

	pMainObj.bSkipChanges = true;
	pMainObj.pEditorDocument.execCommand('Unlink', false, '');
	alert(href.length);
	if(href.length>0)
	{
		var link = false, sRand = '#'+Math.random().toString().substring(2);
		pMainObj.pEditorDocument.execCommand('CreateLink', false, sRand);
		if(document.evaluate)
			link = document.evaluate("//a[@href='"+sRand+"']", pMainObj.pEditorDocument.body, null, 9, null).singleNodeValue;
		else
		{
			var arLinks = pMainObj.pEditorDocument.getElementsByTagName('A');
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

	pMainObj.bSkipChanges = false;
	pMainObj.OnChange("link");
}

var pT = null;
function _Ch()
{	
	var t = document.getElementById('type');
	if(pT)
		pT.style.display = 'none';
	pT = document.getElementById(t.value);
	pT.style.display = GetDisplStr(1);
	var tr = document.getElementById('trg');
	if(t.value=='t1' || t.value=='t2')
		tr.style.display = GetDisplStr(1);
	else
		tr.style.display = GetDisplStr(0);

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
<select id='type' onchange="_Ch()">
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
		<td align="right" width="50%"><?echo GetMessage("FILEMAN_ED_LINK_DOC")?></td>
		<td width="50%">
			<table cellpadding="0" cellspacing="0">
				<tr>
				<td><input type="text" size="25" value="" id="url1"></td>
				<td><input type="button" value="..." onclick="OpenFileBrowserWindFile();"></td>
				</tr>
			</table>
		</td>
	</tr>
</table>

<table width="100%"  id="t2" style="display:none;">
<tr><td align="right" width="50%" >URL:</td><td width="50%" ><table cellpadding="0" cellspacing="0" width="100%"><tr><td>
	<select id='url_type'><option value="http://">http://</option><option value="ftp://">ftp://</option><option value="https://">https://</option><option value=""></option></select>
	</td><td><input type="text" size="20" value="" id="url2"></td></tr></table></td>
</tr>
<tr>
	<td align="right" valign="top"><?echo GetMessage("FILEMAN_ED_LINK_STAT")?></td>
	<td>
		<input type="checkbox" id="fixstat" onclick="_ChFix()" value=""><br>
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

<table width="100%"  id="t3" style="display:none;">
<tr><td width="50%" align="right"><?echo GetMessage("FILEMAN_ED_LINK_ACH")?></td><td width="50%">
<select id="url3">
</select>
</td></tr>
</table>

<table width="100%" id="t4" style="display:none;">
<tr>
<td align="right" width="50%">EMail:</td>
<td width="50%">
	<input type="text" size="25" value="" id="url4">
</td></tr>
</table>


<table width="100%">
	<tr id='trg' style="display:none;">
		<td align="right"><?echo GetMessage("FILEMAN_ED_LINK_WIN")?></td>
		<td>
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
		<td width="50%" align="right"><?echo GetMessage("FILEMAN_ED_LINK_ATITLE")?></td>
		<td width="50%"><input type="text" size="30" value="" id="BXEditorDialog_title"></td>
	</tr>
	<tr><td align="right"><?echo GetMessage("FILEMAN_ED_STYLE")?></td><td>
		<select id='classname'>
		</select>
	</td></tr>
	<tr><td align="right">ID:</td><td><input type="text" size="30" value="" id="id"></td></tr>
</table>


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
	//alert('!');
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
	pElement = window.dialogArguments.pMainObj.GetSelectionObject();
	if(pElement.tagName.toUpperCase()!='IMG' || pElement.getAttribute("__bxtagname"))
	{
		pElement = null;
		document.getElementById("preview").onload = _LPreview;
	}
	else
	{
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
	pMainObj.bSkipChanges = true;
	if(!pElement)
	{
		var tmpid = Math.random().toString().substring(2);
		var str = '<img id="'+tmpid+'" __bxsrc="'+bxhtmlspecialchars(document.getElementById("src").value)+'" />';
		//var str = '<img id="'+tmpid+'" />';
		pMainObj.insertHTML(str);
		pElement = pMainObj.pEditorDocument.getElementById(tmpid);
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
	SAttr(pElement, "alt", document.getElementById("alt").value);

	pMainObj.bSkipChanges = false;
	pMainObj.OnChange("image");
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
					<td><input type="text" size="25" value="" id="src" name="src" onchange="_Reload()"></td>
					<td><input type="button" value="..." onclick="OpenFileBrowserWindImage();"></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width="50%" align="right"><?echo GetMessage("FILEMAN_ED_IMG_ALT")?></td>
		<td width="50%"><input type="text" size="30" value="" id="alt" onchange="_Reload()"></td>
	</tr>
	<tr>
		<td width="50%" align="right">&nbsp;</td>
		<td width="50%">&nbsp;</td>
	</tr>
	<tr>
		<td width="50%" align="right" valign="top">
			<table width="100%">
				<tr><td align="right"><?echo GetMessage("FILEMAN_ED_IMG_W")?></td><td><input type="text" size="3" id="width" onchange="_CHSize()"></td></tr>
				<tr><td align="right"><?echo GetMessage("FILEMAN_ED_IMG_H")?></td><td><input type="text" size="3" id="height" onchange="_CHSize()"></td></tr>
				<tr><td align="right"><?echo GetMessage("FILEMAN_ED_IMG_HSp")?></td><td><input type="text" id="hspace" size="3" onchange="_Reload()"></td></tr>
				<tr><td align="right"><?echo GetMessage("FILEMAN_ED_IMG_HVp")?></td><td><input type="text" id="vspace" size="3" onchange="_Reload()"></td></tr>
				<tr><td align="right"><?echo GetMessage("FILEMAN_ED_IMG_BORD")?></td><td><input type="text" id="border" size="3" onchange="_Reload()"></td></tr>
				<tr><td align="right"><?echo GetMessage("FILEMAN_ED_IMG_AL")?></td><td>
					<select id="align" onchange="_Reload()">
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

<?elseif($name=="table"):?>
<script>
var pElement = null;
function OnLoad()
{
	if(window.dialogArguments.check_exists)
		pElement = BXFindParentByTagName(window.dialogArguments.pMainObj.GetSelectionObject(), 'TABLE');

	var arStFilter = ['TABLE', 'DEFAULT'], i;
	var elStyles = document.getElementById("classname");
	var oOption = new Option("", "", false, false);
	elStyles.options.add(oOption);
	var arStyles;
	for(i=0; i<arStFilter.length; i++)
	{
		arStyles = pMainObj.oStyles.GetStyles(arStFilter[i]);
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
	pMainObj.bSkipChanges = true;
	if(!pElement)
	{
		var tmpid = Math.random().toString().substring(2);
		var str = '<table id="'+tmpid+'"/>';
		pMainObj.insertHTML(str);
		pElement = pMainObj.pEditorDocument.getElementById(tmpid);
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
		if(this.pMainObj.bTableBorder)
			this.pMainObj.__ShowTableBorder(pElement, false);
	}

	SAttr(pElement, "width", (document.getElementById("width").value.length>0?document.getElementById("width").value+''+(document.getElementById("width_unit").value=='%'?'%':''):''));
	SAttr(pElement, "height", (document.getElementById("height").value.length>0?document.getElementById("height").value+''+(document.getElementById("height_unit").value=='%'?'%':''):''));
	SAttr(pElement, "border", document.getElementById("border").value);
	SAttr(pElement, "cellPadding", document.getElementById("cellpadding").value);
	SAttr(pElement, "cellSpacing", document.getElementById("cellspacing").value);
	SAttr(pElement, "align", document.getElementById("align").value);
	SAttr(pElement, 'className', document.getElementById("classname").value);

	pMainObj.OnChange("table");

	if(this.pMainObj.bTableBorder)
		this.pMainObj.__ShowTableBorder(pElement, true);
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
	document.getElementById("text").focus();
}

function OnSave()
{
	pMainObj.PasteAsText(document.getElementById("text").value);
}
</script>

<table width="100%" id="t1" border="0">
	<tr>
		<td><?echo GetMessage("FILEMAN_ED_FF")?> "<?echo GetMessage("FILEMAN_ED_SAVE")?>":</td>
	</tr>
	<tr><td>
		<textarea id="text" style="width:100%; height:200px"></textarea>
	</td></tr>
</table>

<?elseif($name=="pasteword"):?>
<script>
var pFrame = null;
function OnLoad()
{
	pFrame = document.getElementById("text");
	if(pFrame.contentDocument)
		pFrame.pDocument = pFrame.contentDocument;
	else
		pFrame.pDocument = pFrame.contentWindow.document;
	pFrame.pWindow = pFrame.contentWindow;	

	pFrame.pDocument.open();
	pFrame.pDocument.write('<html><head><style>BODY{margin:0px; padding:0px; border:0px;}</style></head><body id="C"></body></html>');
	pFrame.pDocument.close();
	
	if(pFrame.pDocument.addEventListener)
		pFrame.pDocument.addEventListener('keydown', dialog_OnKeyDown, false);
	else if (pFrame.pDocument.attachEvent)
		pFrame.pDocument.body.attachEvent('onpaste', dialog_OnPaste);

	if(BXIsIE())
	{
		document.getElementById("ff").style.display = 'none';
		pFrame.pDocument.body.contentEditable = true;
		pFrame.pDocument.execCommand('Paste');
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
				case 86:	// V
				case 118:	// v
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
	var removeFonts = document.getElementById('removeFonts').checked;
	var removeStyles = document.getElementById('removeStyles').checked;
	var removeIndents = document.getElementById('removeIndents').checked;
	clenedHtml = pMainObj.CleanWordText(pFrame.pDocument.body.innerHTML,[removeFonts,removeStyles,removeIndents]);
	//clenedHtml = pMainObj.CleanWordText(clipboardHTML,[removeFonts,removeStyles,removeIndents]);
	dialog_showClenedHtml(clenedHtml);	
}

function dialog_showClenedHtml(html)
{
	taSourse = document.getElementById('sourse');
	taSourse.value = html;
}

function OnSave()
{
	var removeFonts = document.getElementById('removeFonts').checked;
	var removeStyles = document.getElementById('removeStyles').checked;
	var removeIndents = document.getElementById('removeIndents').checked;
	pMainObj.PasteWord(pFrame.pDocument.body.innerHTML,[removeFonts,removeStyles,removeIndents]);
}
</script>

<table width="100%" id="t1" border="0">
	<tr id="ff">
		<td><?echo GetMessage("FILEMAN_ED_FF")?> "<?echo GetMessage("FILEMAN_ED_SAVE")?>":</td>
	</tr>
	<tr>
		<td><iframe id="text" src="about:blank" style="width:100%; height:150px; border:1px solid #CCCCCC;"></iframe></td>
	</tr>
	<tr>
		<td><?echo GetMessage("FILEMAN_ED_HTML_AFTER_CLEANING")?></td>
	</tr>
	<tr>
		<td><textarea id="sourse" style="width:100%; height:100px; border:1px solid #CCCCCC;" disabled="disabled"></textarea></td>
	</tr>
	<tr>
		<td>
			<input id="removeFonts" type="checkbox" checked="checked" onclick="dialog_cleanAndShow();"><label for="removeFonts"><?echo GetMessage("FILEMAN_ED_REMOVE_FONTS")?></label><br>
			<input id="removeStyles" type="checkbox" checked="checked" onclick="dialog_cleanAndShow();"> <label for="removeStyles"><?echo GetMessage("FILEMAN_ED_REMOVE_STYLES")?></label><br>
			<input id="removeIndents" type="checkbox" checked="checked" onclick="dialog_cleanAndShow();"> <label for="removeIndents"><?echo GetMessage("FILEMAN_ED_REMOVE_INDENTS")?></label>
		</td>
	</tr>
	
</table>		

<?elseif($name=="asksave"):?>

<script>
function OnLoad()
{
	document.getElementById("b1").focus();
}

function OnSave(t)
{
	if(t=='save')
	{
		if(window.dialogArguments.savetype == 'saveas')
			window.dialogArguments.window._BXSaveAs();
		else
		{
			pMainObj.SaveContent(true);
			pMainObj.pForm.submit();
		}
	}
	else if(t=='exit')
	{
		if(pMainObj.arConfig["sBackUrl"])
			window.dialogArguments.window.location = pMainObj.arConfig["sBackUrl"];
	}

	window.close();
}
</script>

<table height="100%" width="100%" id="t1" border="0">
	<tr height="60%">
		<td colspan="3">
			<table height="100%" width="100%" id="t1" border="0" style="border: 1px solid #000000; font-size:14px; background-color:#EFEFEF">
			<tr>
			<td></td>
			<td><?=GetMessage("FILEMAN_DIALOG_EXIT_ACHTUNG")?></td>
			</tr>
			</table>
		</td>
	</tr>
	<tr height="40%" valign="bottom">
		<td align="center" valign="bottom"><input style="font-size:12px; height: 25px; width: 180px;" type="button" id="b1" value="<?echo GetMessage("FILEMAN_DIALOG_SAVE_BUT")?>" onclick="OnSave('save')"></td>
		<td align="center" valign="bottom"><input style="font-size:12px; height: 25px; width: 180px;" type="button" value="<?echo GetMessage("FILEMAN_DIALOG_EXIT_BUT")?>" onclick="OnSave('exit')"></td>
		<td align="center" valign="bottom"><input style="font-size:12px; height: 25px; width: 180px;" type="button" value="<?echo GetMessage("FILEMAN_DIALOG_EDIT_BUT")?>" onclick="OnSave()"></td>
	</tr>
</table>

<?elseif($name=="pageprops"):?>

<script>
var finput = false;
function OnLoad()
{
	var eddoc = window.dialogArguments.document;
	document.getElementById('title').value = eddoc.getElementById('title').value;

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
	var tbl = document.getElementById('t1');

	var cnt = parseInt(document.getElementById("maxind").value)+1;
	var r = tbl.insertRow(tbl.rows.length-1);
	var c = r.insertCell(-1);
	c.align="right";
	if(name)
		c.innerHTML = '<input type="hidden" id="CODE_'+cnt+'" name="CODE_'+cnt+'" value="'+bxhtmlspecialchars(code)+'">'+bxhtmlspecialchars(name)+':';
	else
	{
		c.innerHTML = '<input type="text" id="CODE_'+cnt+'" name="CODE_'+cnt+'" value="'+bxhtmlspecialchars(code)+'" size="30">:';
		if(!finput)
			finput = document.getElementById('CODE_'+cnt);
	}

	c = r.insertCell(-1);
	c.innerHTML = '<input type="text" name="VALUE_'+cnt+'" id="VALUE_'+cnt+'" value="'+bxhtmlspecialchars(value)+'" size="60">';

	if(!finput)
		finput = document.getElementById('VALUE_'+cnt);

	document.getElementById("maxind").value = cnt;
}

function OnSave()
{
	var eddoc = window.dialogArguments.document;

	var edcnt = parseInt(eddoc.getElementById("maxind").value);
	var cnt = parseInt(document.getElementById("maxind").value);

	for(var i=0; i<=edcnt; i++)
	{
		if(eddoc.getElementById("CODE_"+i).value != document.getElementById("CODE_"+i).value)
			eddoc.getElementById("CODE_"+i).value = document.getElementById("CODE_"+i).value;
		if(eddoc.getElementById("VALUE_"+i).value != document.getElementById("VALUE_"+i).value)
			eddoc.getElementById("VALUE_"+i).value = document.getElementById("VALUE_"+i).value;
	}

	for(i = edcnt+1; i<=cnt; i++)
	{
		window.dialogArguments.window._MoreRProps(document.getElementById("CODE_"+i).value, document.getElementById("VALUE_"+i).value);
	}

	eddoc.getElementById("maxind").value = cnt;
	eddoc.getElementById('title').value = document.getElementById('title').value;

	pMainObj.bNotSaved = true;

	return iNoOnSelectionChange;
}
</script>
<div style="width:100%; height:200px; overflow:-moz-scrollbars-vertical; overflow-y:scroll;">
<table width="100%" id="t1" border="0">
	<tr>
		<td width="40%" align="right"><b><?echo GetMessage("FILEMAN_DIALOG_TITLE")?></b></td>
		<td width="60%"><input type="text" id="title" value="" size="30"></td>
	</tr>
	<tr>
		<td width="40%" align="right"></td>
		<td width="60%"><input type="button" value="<?echo GetMessage("FILEMAN_DIALOG_MORE_PROP")?>" onclick="AppendRow('', '')"></td>
	</tr>
</table>
</div>
<input type="hidden" value="-1" id="maxind">


<?elseif($name=="spellcheck"):?>

<script>
var pElement = null;
function OnLoad()
{
	//alert("<?echo GetMessage("FILEMAN_JS_TestPhrase")?>");
	pElement = window.dialogArguments.pMainObj.GetSelectionObject();
	
	var cancelBut = document.getElementById("cancelBut");
	cancelBut.style.display = "none";
	var saveBut = document.getElementById("saveBut");
	saveBut.style.display = "none";	
	
	wordBox = document.getElementById("wordBox");
	suggestionsBox = document.getElementById("suggestionsBox");
	
	BXLang = window.dialogArguments.BXLang;
	root = window.dialogArguments.root;
	usePspell = window.dialogArguments.usePspell;
	useCustomSpell = window.dialogArguments.useCustomSpell;
	
	initSpellCheck();
	processChildren(root,handleNodeValue);
	spellCheck();
}

function showResult() 
{
	if (showResult.arguments[0]=='error') {
		alert('<?echo GetMessage("FILEMAN_ED_DIC_ISNT_INSTALED")?>');
		closeDialog();
	}
	var waitWin = document.getElementById("waitWin");
	var spellResultWin = document.getElementById("spellResultWin");
	waitWin.style.display = "none";
	if (spellResult.length > 0) 
	{
		spellResultWin.style.display = "block";
		pasteFirstWordInDialog();
	} 
	else 
	{
		okMessWin.style.display = "block";
	}	
}

// Take first(0) element in spellResult array and paste it in dialog
function pasteFirstWordInDialog() 
{
	highlightWord();
	wordBox.value=spellResult[0].word;
	suggestionsBox.innerHTML="";
	for (var i in spellResult[0].suggestions) 
	{
		var suggestionOpt = document.createElement("option");
		if (i==0)
		{
			suggestionOpt.selected="selected";
		}
		suggestionOpt.innerHTML=spellResult[0].suggestions[i];
		suggestionOpt.value=spellResult[0].suggestions[i];
		suggestionsBox.appendChild(suggestionOpt);
	}
}


function skipWord() 
{
	spellResult.splice(0,1);
	if (spellResult.length > 0) 
	{		
		pasteFirstWordInDialog();
	} 
	else 
	{
		closeDialog();
	}
}


function closeDialog() 
{
	clearSelections();
	window.close();
}


function changeReplacementValue() 
{
	if (suggestionsBox.length > 0) 
	{
		wordBox.value = suggestionsBox[suggestionsBox.selectedIndex].value;
	}
	
}

// Replace word in document to value of wordBox
// Can take one or two arguments: 	arguments[0] - index of element in spellResult array (default - 0)
//									arguments[1] - replacement value (default - wordBox.value)
function replaceWord() 
{
	var ind = (arguments[0]) ? arguments[0] : 0;
	//run changeReplacementValue() if user click 'Replace' or 'Replace All' button before clickin' to some value in 
	//suggestionsBox. (4 ex. if user want to replace word to 1st suggestion)
	if (wordBox.value == spellResult[ind].word) 
	{		
		changeReplacementValue();
	}	
	//var newValue = (arguments[1]) ? arguments[1] : suggestionsBox[suggestionsBox.selectedIndex].value;
	var newValue = (arguments[1]) ? arguments[1] : wordBox.value;
	var oldValueRE = new RegExp(spellResult[ind].word,"ig");
	spellResult[ind].obj.nodeValue = spellResult[ind].obj.nodeValue.replace(oldValueRE,newValue);
}

//Realize funtionality of 'Replace All' and 'Skip All' operations:
//Find all similar to spelling words and replace them (if mode='replace') or simply remove from spellResult array
function findSimilarWords(mode) 
{
	var ind = 1;
	//replacing 1st word
	if (mode=="replace") 
	{
		//var newValue = suggestionsBox[suggestionsBox.selectedIndex].value;
		if (wordBox.value == spellResult[ind].word) 
		{
			changeReplacementValue();
		}	
		var newValue = wordBox.value;
		replaceWord(0,newValue);		
	}
	//[replacin' and] deletin' from spellResult similar words
	while (ind < spellResult.length) 
	{
		if (spellResult[ind].word == spellResult[0].word) 
		{
			if (mode=="replace") 
			{
				replaceWord(ind,newValue);
			}
			spellResult.splice(ind,1);
		} 
		else 
		{
			ind++;
		}
	}
	//deletion' 1st word from result and 'refreshing' dialog	
	skipWord();
}

// Add word to user's dictionary
function addWord() 
{
	var word = wordBox.value;
	var postData = "word="+encodeURIComponent(word);
	var url = "/bitrix/admin/fileman_spell_addWord.php?BXLang="+BXLang+"&useCustomSpell="+useCustomSpell+"&usePspell="+usePspell;
	var callBackFunction = "";
	ajaxConnect(url, postData, callBackFunction,true);
	findSimilarWords("skip");
}

//Highlight spelling word using selection
function highlightWord() 
{
	var word = spellResult[0].word;
	var amount = word.length;
	var value = new RegExp(word,"i");
	var d = spellResult[0].obj.parentNode;
	var textData = (d.innerText) ? d.innerText : d.textContent;	
	
	if (pMainObj.pEditorDocument.createRange) 
	{
		//FF, Opera
		var ind = spellResult[0].obj.nodeValue.search(value);
		oRange = pMainObj.pEditorDocument.createRange();				
		oRange.setStart(spellResult[0].obj,ind);
		oRange.setEnd(spellResult[0].obj,ind+amount);
		//Now highlight using Mozilla style selections
		var wordSelection = pMainObj.pEditorWindow.getSelection();
		wordSelection.removeAllRanges();
		wordSelection.addRange(oRange);
	} 
	else 
	{
		//IE
		var ind =textData.search(value);
		pMainObj.pEditorDocument.selection.empty();
		oRange = pMainObj.pEditorDocument.selection.createRange();
		oRange.moveToElementText(d);
		oRange.moveStart("character", ind);
		oRange.moveEnd("character", amount - oRange.text.length);
		oRange.select();
		d.focus();
	}
}


function clearSelections() 
{
	if (pMainObj.pEditorWindow.getSelection) 
	{
		//FF,Opera
		pMainObj.pEditorWindow.getSelection().removeAllRanges();
	} 
	else 
	{
		//IE
		pMainObj.pEditorDocument.selection.empty();
	}
}
</script>
	<div id="waitWin" style="display: block; text-align: center; vertical-align: middle;">
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
	<div id="okMessWin" style="display: none;">
		<table border="0" width="100%" height="100%">
			<tr>
				<td align="center">					
					<span style="vertical-align: middle;"><?echo GetMessage("FILEMAN_ED_SPELL_FINISHED")?></span>
					<br><br>
					<input id="butClose" type="button" value="<?echo GetMessage("FILEMAN_ED_CLOSE")?>" style="width:150" onClick="closeDialog()">
				</td>
			</tr>
		</table>	
	</div>
	<div id="spellResultWin" style="display: none">
	<table width="380" border="0" align="center" cellpadding="0" cellspacing="0">
		<tr><td colspan="4" height="5"></td></tr>
		<tr>
			<td width="224" valign="top"><input id="wordBox" type="text" style="width:100%;"></td>
			<td width="8"></td>
			<td width="140" valign="top"><input id="butSkip" type="button" value="<?echo GetMessage("FILEMAN_ED_SKIP")?>" style="width:100%;" onClick="skipWord()"></td>
			<td width="8"></td>
		</tr>
		<tr><td colspan="4" height="7"></td></tr>
		<tr>
			<td rowspan="9" valign="top"><select id="suggestionsBox" size="8" style="width:100%;" onClick="changeReplacementValue()"></select></td>
			<td></td>
			<td><input id="butSkipAll" type="button" value="<?echo GetMessage("FILEMAN_ED_SKIP_ALL")?>" style="width:100%;" onClick="findSimilarWords('skip')"></td>
			<td></td>
		</tr>
		<tr height="5"><td colspan="2" height="5"></td></tr>
		<tr>
			<td></td>
			<td><input id="butReplace" type="button" value="<?echo GetMessage("FILEMAN_ED_REPLACE")?>" style="width:100%;" onClick="replaceWord(); skipWord()"></td>
			<td></td>
		</tr>
		<tr height="5"><td colspan="2" height="5"></td></tr>
		<tr>
			<td></td>
			<td><input id="butReplaceAll" type="button" value="<?echo GetMessage("FILEMAN_ED_REPLACE_ALL")?>" style="width:100%;" onClick="findSimilarWords('replace')"></td>
			<td></td>
		</tr>
		<tr height="5"><td colspan="2" height="5"></td></tr>
		<tr>
			<td></td>
			<td><input id="butAdd" type="button" value="<?echo GetMessage("FILEMAN_ED_ADD")?>" style="width:100%;" onClick="addWord()"></td>
			<td></td>
		</tr>
		<tr height="5"><td colspan="2" height="5"></td></tr>
		<tr>
			<td></td>
			<td><input id="butClose" type="button" value="<?echo GetMessage("FILEMAN_ED_CLOSE")?>" style="width:100%;" onClick="closeDialog()"></td>
			<td></td>
		</tr>
	</table>
	</div>


<?elseif($name=="specialchar"):?>

<script>
var pElement = null;
function OnLoad()
{
	pElement = window.dialogArguments.pMainObj.GetSelectionObject();
	
	var cancelBut = document.getElementById("cancelBut");
	cancelBut.style.display = "none";
	var saveBut = document.getElementById("saveBut");
	saveBut.style.display = "none";	
	
	arEntities = ['&iexcl;','&cent;','&pound;','&curren;','&yen;','&brvbar;','&sect;','&uml;','&copy;','&ordf;','&laquo;','&not;','&reg;','&macr;','&deg;','&plusmn;','&sup2;','&sup3;','&acute;','&micro;','&para;','&middot;','&cedil;','&sup1;','&ordm;','&raquo;','&frac14;','&frac12;','&frac34;','&iquest;','&Agrave;','&Aacute;','&Acirc;','&Atilde;','&Auml;','&Aring;','&AElig;','&Ccedil;','&Egrave;','&Eacute;','&Ecirc;','&Euml;','&Igrave;','&Iacute;','&Icirc;','&Iuml;','&ETH;','&Ntilde;','&Ograve;','&Oacute;','&Ocirc;','&Otilde;','&Ouml;','&times;','&Oslash;','&Ugrave;','&Uacute;','&Ucirc;','&Uuml;','&Yacute;','&THORN;','&szlig;','&agrave;','&aacute;','&acirc;','&atilde;','&auml;','&aring;','&aelig;','&ccedil;','&egrave;','&eacute;','&ecirc;','&euml;','&igrave;','&iacute;','&icirc;','&iuml;','&eth;','&ntilde;','&ograve;','&oacute;','&ocirc;','&otilde;','&ouml;','&divide;','&oslash;','&ugrave;','&uacute;','&ucirc;','&uuml;','&yacute;','&thorn;','&yuml;','&OElig;','&oelig;','&Scaron;','&scaron;','&Yuml;','&circ;','&tilde;','&ensp;','&emsp;','&thinsp;','&zwnj;','&zwj;','&lrm;','&rlm;','&ndash;','&mdash;','&lsquo;','&rsquo;','&sbquo;','&ldquo;','&rdquo;','&bdquo;','&dagger;','&Dagger;','&permil;','&lsaquo;','&rsaquo;','&euro;','&Alpha;','&Beta;','&Gamma;','&Delta;','&Epsilon;','&Zeta;','&Eta;','&Theta;','&Iota;','&Kappa;','&Lambda;','&Mu;','&Nu;','&Xi;','&Omicron;','&Pi;','&Rho;','&Sigma;','&Tau;','&Upsilon;','&Phi;','&Chi;','&Psi;','&Omega;','&alpha;','&beta;','&gamma;','&delta;','&epsilon;','&zeta;','&eta;','&theta;','&iota;','&kappa;','&lambda;','&mu;','&nu;','&xi;','&omicron;','&pi;','&rho;','&sigmaf;','&sigma;','&tau;','&upsilon;','&phi;','&chi;','&psi;','&omega;','&thetasym;','&upsih;','&piv;','&bull;','&hellip;','&prime;','&Prime;','&oline;','&frasl;','&weierp;','&image;','&real;','&trade;','&alefsym;','&larr;','&uarr;','&rarr;','&darr;','&harr;','&crarr;','&lArr;','&uArr;','&rArr;','&dArr;','&hArr;','&forall;','&part;','&exist;','&empty;','&nabla;','&isin;','&notin;','&ni;','&prod;','&sum;','&minus;','&lowast;','&radic;','&prop;','&infin;','&ang;','&and;','&or;','&cap;','&cup;','&int;','&there4;','&sim;','&cong;','&asymp;','&ne;','&equiv;','&le;','&ge;','&sub;','&sup;','&nsub;','&sube;','&supe;','&oplus;','&otimes;','&perp;','&sdot;','&lceil;','&rceil;','&lfloor;','&rfloor;','&lang;','&rang;','&loz;','&spades;','&clubs;','&hearts;','&diams;'];
	
	drawTable();	
	pMainObj = window.dialogArguments.pMainObj;	
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
		pMainObj.insertHTML(arEntities[entInd]);
		window.close();
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
	<div id="charCont" style="width: 455; position: absolute; top: 5px; left: 5px">
	</div>
	<div id="charPrev" style="background-color: #FFFFFF; width: 120px; height: 120px; position: absolute; top: 5px; left: 465px"></div>
	<div id="entityName" style="font-size: 14; text-align: center; background-color: #FFFFFF; width: 120px; height: 20px; position: absolute; top: 130px; left: 465px"></div>
	<div id="saveBut_div" style="width: 120px; height: 20px; position: absolute; top: 258px; left: 465px;">
	<input type="button" value="<?echo GetMessage("FILEMAN_ED_CANC")?>" onclick="window.close();"></div>
<? endif ?>


</td></tr>
<?if($not_use_default!='Y'):?>
<tr height="10%"><td align="center" valign="bottom">
	<input id="saveBut" type="button" value="<?echo GetMessage("FILEMAN_ED_SAVE")?>" onclick="__OnSave();">
	<input id="cancelBut" type="button" value="<?echo GetMessage("FILEMAN_ED_CANC")?>" onclick="window.close();">
</td></tr>
<?endif?>
</table>
</form>
</BODY>
</HTML>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>
