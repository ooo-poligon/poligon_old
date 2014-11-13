<?
global $MESS, $SELECTED_SECTION, $CURRENT_SECTION;
IncludeModuleLangFile($GLOBALS["DOCUMENT_ROOT"]."/bitrix/modules/main/interface/.left.menu_template.php");

$sMenu='
<script language="JavaScript">
<!--
var nSection = 0;
var aSections = new Array();

function AdmMenuCookies(id)
{
	var flts = "", curval = "";
	var aCookie = document.cookie.split("; ");
	//document.cookie = "vns=X; expires=Thu, 31 Dec 1999 23:59:59 GMT; path='.BX_ROOT.'/admin/;";return "";
	for (var i=0; i < aCookie.length; i++)
	{
		var aCrumb = aCookie[i].split("=");
		if ("vns" == aCrumb[0])
		{
			if(aCrumb.length>1 && aCrumb[1].length>0)
			{
				var val = aCrumb[1];
				var arFVals = val.split("&");
				for (var j=0; j < arFVals.length; j++)
				{
					val = arFVals[j];
					if(val.length>0)
					{
						val = unescape(val);
						val = val.split("=");
						if(val.length>1 && val[1].length>0)
						{
							if(val[0] == id)
								curval = val[1];
							else
								flts = flts + escape(val[0] + "=" + val[1]) + "&";
						}
					}
				}
			}
		}

		if ("vn"+id == aCrumb[0])
			document.cookie = "vn"+id+"=N; expires=Thu, 31 Dec 1999 23:59:59 GMT; path='.BX_ROOT.'/admin/;";
	}

	return flts;
}


function DoHide(id)
{
	document.cookie = "vns=" + AdmMenuCookies(id) + escape(id + "=N")+"; expires=Thu, 31 Dec 2020 23:59:59 GMT; path='.BX_ROOT.'/admin/;";
	//document.cookie = "vn"+id+"=N; expires=Thu, 31 Dec 2020 23:59:59 GMT; path='.BX_ROOT.'/admin/;";
	document.getElementById("arr"+id).src = "'.BX_ROOT.'/images/admin/down.gif";
	document.getElementById("t"+id).style.display = "none";
}
function DoShow(id)
{
	document.cookie = "vns=" + AdmMenuCookies(id) + escape(id + "=Y")+"; expires=Thu, 31 Dec 2020 23:59:59 GMT; path='.BX_ROOT.'/admin/;";
	//document.cookie = "vn"+id+"=Y; expires=Thu, 31 Dec 2020 23:59:59 GMT; path='.BX_ROOT.'/admin/;";
	document.getElementById("arr"+id).src = "'.BX_ROOT.'/images/admin/up.gif";
	document.getElementById("t"+id).style.display = "block";
}
function showmenu(id)
{
	if(document.getElementById("t"+id).style.display!="none")
		DoHide(id);
	else
		DoShow(id);
}
function SwitchSections(bOpen)
{
	for(i=0; i<aSections.length; i++)
	{
		if(bOpen)
			DoShow(aSections[i]);
		else
			DoHide(aSections[i]);
	}
}
function ShowMenuColumn(bShow)
{
	document.cookie = "showmnucolumn="+(bShow? "Y":"N")+"; expires=Thu, 31 Dec 2020 23:59:59 GMT; path='.BX_ROOT.'/admin/;";
	document.getElementById("menutbl").style.display = (bShow? "block":"none");
//	document.getElementById("menusign").style.display = (bShow? "block":"none");
	document.getElementById("menubutton").style.display = (bShow? "none":"block");
}

var tmpImage = new Image(); 
tmpImage.src = "'.BX_ROOT.'/images/admin/down.gif";
tmpImage.src = "'.BX_ROOT.'/images/admin/up.gif";
//-->
</script>

<div id="menubutton" style="display:'.($_COOKIE["showmnucolumn"]<>"N"? "none":"block").';">
<table border="0" cellspacing="0" cellpadding="0">
<tr><td><a title="'.GetMessage("show_column").'" href="javascript:ShowMenuColumn(true);"><img src="'.BX_ROOT.'/images/admin/right.gif" width="13" height="13" border="0" hspace="2"></a></td></tr>
<tr><td align="center" title="'.GetMessage("show_column").'" onClick="ShowMenuColumn(true);" class="hiddenmenu">'.GetMessage("menu_tab").'</td></tr>
</table>
</div>

<div id="menutbl" style="display:'.($_COOKIE["showmnucolumn"]<>"N"? "block":"none").';">
<table width="150" border="0" cellspacing="0" cellpadding="0">
<tr>
<td align="right"><a title="'.GetMessage("menu_show_all").'" href="javascript:SwitchSections(true);"><img src="'.BX_ROOT.'/images/admin/plus.gif" width="13" height="13" border="0"></a><a title="'.GetMessage("menu_hide_all").'" href="javascript:SwitchSections(false);"><img src="'.BX_ROOT.'/images/admin/minus.gif" width="13" height="13" border="0" hspace="2"></a><a title="'.GetMessage("menu_hide_column").'" href="javascript:ShowMenuColumn(false);"><img src="'.BX_ROOT.'/images/admin/left.gif" width="13" height="13" border="0" hspace="4"></a></td>
</tr>
';

for($i=0; $i<count($MENU_ITEMS); $i++)
{
	$MENU_ITEM = $MENU_ITEMS[$i];
	extract($MENU_ITEM);

	if($PARAMS["SEPARATOR"]=="Y")
	{
		$CURRENT_SECTION = $PARAMS["SECTION_ID"];
		$sTitle = GetMessage("menu_section_title");

		parse_str($_COOKIE["vns"], $arVns);
		if(is_set($arVns, $PARAMS["SECTION_ID"]))
		{
			$vnval = $arVns[$PARAMS["SECTION_ID"]];
			if(is_set($_COOKIE, "vn".$PARAMS["SECTION_ID"]))
				unset($_COOKIE["vn".$PARAMS["SECTION_ID"]]);
		}
		else
			$vnval = $_COOKIE["vn".$PARAMS["SECTION_ID"]];

		$sMenuBody = str_replace(Array("#@#?1?#@#", "#@#?2?#@#"), Array("down.gif", " style='display:none;'"), $sMenuBody);

		$sMenuBody .=
			($bSepOpened?"</table></div></td>\n</tr>":"").
			"<tr>\n".
			"<td class='leftmenusepdelim'><img src='".BX_ROOT."/images/1.gif' alt='' width='1' height='1'></td>\n".
			"</tr>".
			"<tr>\n".
			"<td>\n".
			"<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">".
			"<tr>".
			"	<td width='0%' class='leftmenusepbg' valign='top'><a href='#' onclick='showmenu(\"".$PARAMS["SECTION_ID"]."\"); return false;' title='".$sTitle."'><img src='".($PARAMS["ICON"] <> ""? $PARAMS["ICON"]:BX_ROOT."/images/admin/mnu_default.gif")."' width='18' height='18' hspace='2' border='0' vspace='1' alt=''></a></td>\n".
			"	<td align='left' width='100%' valign='middle' class='leftmenusepbg'>".
			"<a href='#' onclick='showmenu(\"".$PARAMS["SECTION_ID"]."\"); return false;' title='".$sTitle."' class='leftmenusep'>".
			htmlspecialcharsex($TEXT)."</a></td>\n".
			"	<td align='left' width='0%' valign='middle' class='leftmenusepbg'>".
			"<a href='#' onclick='showmenu(\"".$PARAMS["SECTION_ID"]."\"); return false;' title='".$sTitle."' class='leftmenusep'>".
			"<img src='".BX_ROOT."/images/admin/".($vnval=="Y"? "up.gif":"#@#?1?#@#")."' width='7' height='7' border='0' alt='' hspace='2' id='arr".$PARAMS["SECTION_ID"]."'></a></td>\n".
			"</tr>".
			"</table>".
			"</td>\n".
			"</tr>\n".
			"<tr>\n".
			"<td class='leftmenusepdelim'><img src='".BX_ROOT."/images/1.gif' alt='' width='1' height='1'></td>\n".
			"</tr>".
			"<tr>\n".
			"<td><img src='".BX_ROOT."/images/1.gif' alt='' width='3' height='4'></td>\n".
			"</tr>".
			"<tr>\n".
			"<td valign='top'><script language='javascript'>aSections[nSection]=\"".$PARAMS["SECTION_ID"]."\"; nSection++;</script>".
			"<div id='t".$PARAMS["SECTION_ID"]."'".($vnval<>"Y"?" #@#?2?#@#":"").">".
			"<table border='0' cellpadding='0' cellspacing='0' width='100%'>";
		$bSepOpened = true;
	}
	else
	{
		if($SELECTED)
		{
			$SELECTED_SECTION = $CURRENT_SECTION;
			$clrtext = "leftmenuact";
			$sMenuBody = str_replace(Array("#@#?1?#@#", "#@#?2?#@#"), Array("up.gif", ""), $sMenuBody);
		}
		else
			$clrtext = "leftmenu";

		$sMenuBody .= "";
		if($ITEM_INDEX == 0)
		{
			$sMenuBody .= 
				"<tr>\n".
				"<td valign='top'>".
				"<table border='0' cellpadding='0' cellspacing='0' width='100%'>";
		}

		$sMenuBody .=
			"<tr>\n".
			"<td width='0%'><img src='".BX_ROOT."/images/1.gif' width='2' height='1' alt=''></td>\n".
			"<td width='0%' valign='top' ><img src='".BX_ROOT."/images/admin/bull.gif' width='3' height='3' vspace='6' hspace='4' alt=''></td>\n".
			"<td width='100%' valign='top' class='".$clrtext."bg'>".
			"<a href='".$LINK."' title='".$PARAMS["ALT"]."' class='".$clrtext."'>".$TEXT."</a></td>\n".
			"</tr>\n".
			"<tr>\n".
			"<td colspan='3'><img src='".BX_ROOT."/images/1.gif' alt='' width='3' height='4'></td>\n".
			"</tr>";

		if($ITEM_INDEX == 0)
		{
			$sMenuBody .= 
				"</table>\n".
				"</td>\n".
				"</tr>\n";
		}
	}
}

$sMenuBody = str_replace(Array("#@#?1?#@#", "#@#?2?#@#"), Array("down.gif", " style='display:none;'"), $sMenuBody);
$sMenu .= $sMenuBody.($bSepOpened? "</table></div></td></tr>":"")."</table></div>";
?>