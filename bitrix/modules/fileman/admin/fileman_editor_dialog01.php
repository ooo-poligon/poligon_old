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
<form id="form1" name="form1">
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
}
</script>
<?echo GetMessage("FILEMAN_ED_ANCHOR_NAME")?> <input type="text" size="30" value="" id="anchor_value">

<?elseif($name=="link"):?>

<script>
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
			document.getElementById("title").value = pElement.title;

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

	pMainObj.pEditorDocument.execCommand('Unlink', false, '');
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
			SAttr(link, 'target', document.getElementById("target").value);
			SAttr(link, 'id', document.getElementById("id").value);
			SAttr(link, 'title', document.getElementById("title").value);
			SAttr(link, 'className',  document.getElementById("classname").value);
		}
	}
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
		array("PATH" => GetDirPath($str_FILENAME)),
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
		<td width="50%"><input type="text" size="30" value="" id="title"></td>
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
	var el = document.getElementById("preview");
	if(prevsrc!=document.getElementById("src").value)
	{
		document.getElementById("preview").style.display="";
		el.removeAttribute("width");
		el.removeAttribute("height");
		prevsrc=document.getElementById("src").value;
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
	if(!pElement)
	{
		var tmpid = Math.random().toString().substring(2);
		var str = '<img id="'+tmpid+'"/>';
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
	SAttr(pElement, "alt", document.getElementById("alt").value);
}

function SetUrl(url)
{
	document.getElementById("src").value = url;
	if(document.getElementById("src").onchange)
		document.getElementById("src").onchange();
}
</script>
<?
$APPLICATION->ShowFileSelectDialog(
		"OpenFileBrowserWindImage",
		array("FORM_NAME" => "form1", "FORM_ELEMENT_NAME" => "src"),
		array("PATH" => GetDirPath($str_FILENAME)),
		"image"
	);
?>

<table width="100%" id="t1" border="0">
	<tr>
		<td align="right" width="50%"><?echo GetMessage("FILEMAN_ED_IMG_PATH")?></td>
		<td width="50%">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td><input type="text" size="25" value="" id="src" name="src"></td>
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
						<option value="absbottom">absbottom</option>
						<option value="absmiddle">absmiddle</option>
						<option value="baseline">baseline</option>
						<option value="bottom">bottom</option>
						<option value="left">left</option>
						<option value="middle">middle</option>
						<option value="right">right</option>
						<option value="texttop">texttop</option>
						<option value="top">top</option>
					</select>
				</td></tr>
			</table>
		</td>
		<td width="50%"><?echo GetMessage("FILEMAN_ED_IMG_PREV")?>
		<div style="height:140px; overflow: hidden; border: 1px #999999 solid; overflow-y: scroll; color: #999999; background-color: #FFFFFF; padding: 3px">
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
				cell.innerHTML = '&nbsp;';
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

	if(BXIsIE())
	{
		document.getElementById("ff").style.display = 'none';
		pFrame.pDocument.body.contentEditable = true;
	}
	else
		pFrame.pDocument.designMode='on';


	if(BXIsIE())
		pFrame.pDocument.execCommand('Paste');
}

function OnSave()
{
	pMainObj.PasteWord(pFrame.pDocument.body.innerHTML);
}
</script>

<table width="100%" id="t1" border="0">
	<tr id="ff">
		<td><?echo GetMessage("FILEMAN_ED_FF")?> "<?echo GetMessage("FILEMAN_ED_SAVE")?>":</td>
	</tr>
	<tr>
		<td><iframe id="text" src="about:blank" style="width:100%; height:200px; border:1px solid #CCCCCC;"></iframe></td>
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
		pMainObj.SaveContent(true);
		pMainObj.pForm.submit();
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
			<td><b>Внимание!</b> Редактируемый документ был изменен. Чтобы не потерять изменения необходимо сохранить документ.</td>
			</tr>
			</table>
		</td>
	</tr>
	<tr height="40%" valign="bottom">
		<td align="center" valign="bottom"><input style="font-size:12px; height: 25px; width: 180px;" type="button" id="b1" value="Сохранить и выйти" onclick="OnSave('save')"></td>
		<td align="center" valign="bottom"><input style="font-size:12px; height: 25px; width: 180px;" type="button" value="Выйти без сохранения" onclick="OnSave('exit')"></td>
		<td align="center" valign="bottom"><input style="font-size:12px; height: 25px; width: 180px;" type="button" value="Продолжить редактирование" onclick="OnSave()"></td>
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

	eddoc.getElementById('title').value = document.getElementById('title').value;
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

	return iNoOnSelectionChange;
}
</script>
<div style="width:100%; height:200px; overflow:-moz-scrollbars-vertical; overflow-y:scroll;">
<table width="100%" id="t1" border="0">
	<tr>
		<td width="40%" align="right"></td>
		<td width="60%"><input type="button" value="Еще..." onclick="AppendRow('', '')"></td>
	</tr>
</table>
</div>
<input type="hidden" value="-1" id="maxind">

<?endif?>


</td></tr>
<?if($not_use_default!='Y'):?>
<tr height="10%"><td align="center" valign="bottom">
	<input type="button" value="<?echo GetMessage("FILEMAN_ED_SAVE")?>" onclick="__OnSave();">
	<input type="button" value="<?echo GetMessage("FILEMAN_ED_CANC")?>" onclick="window.close();">
</td></tr>
<?endif?>
</table>
</form>
</BODY>
</HTML>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>
