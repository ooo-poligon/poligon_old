<?
if(!defined("FROMDIALOGS") || FROMDIALOGS!==true)
	die("");

IncludeModuleLangFile(__FILE__);
?>
<SCRIPT LANGUAGE=JavaScript FOR=window EVENT=onload>
<!--
	if(window.dialogArguments["menutype"]!=null)
	{
		menutype.value = window.dialogArguments["menutype"];
		newp.value = window.dialogArguments["newp"];
		if(window.dialogArguments["menuitem"]>0)
			itemtype[1].checked = true;
		else
			itemtype[0].checked = true;

		chtyp(window.dialogArguments["menuitem"], window.dialogArguments["newppos"]);
	}
	else
	{
		itemtype[0].checked = true;
		chitemtype();
	}
// -->
</SCRIPT>
<SCRIPT LANGUAGE=JavaScript FOR=Ok EVENT=onclick>
<!--
	var arr = new Array();
	arr["menutype"] = menutype.value;
	if(itemtype[0].checked)
	{
		if(newp.value.length<=0)
		{
			alert("<?echo GetMessage("FILEMAN_D_MENUSEL_ENTER_NAME")?>");
			return;
		}
		arr["menuitemname"] = newp.value;
		arr["menuitem"] = 0;
	}
	else
	{
		arr["menuitemname"] = menuitem[menuitem.selectedIndex].innerText;
		arr["menuitem"] = menuitem.value;
	}

	arr["newp"] = newp.value;
	arr["newppos"] = newppos.value;

	arr["menutypename"] = menutype[menutype.selectedIndex].innerText;

	window.returnValue = arr;
	window.close();
// -->
</script>
<?
$site = CFileMan::__CheckSite($site);
$DOC_ROOT = CSite::GetSiteDocRoot($site);

$path = Rel2Abs("/", $path);
$arParsedPath = CFileMan::ParsePath(Array($site, $path), true);
?>
<table cellspacing=3 align="center" width="100%">
<tr>
	<td width="0%" nowrap><?echo GetMessage("FILEMAN_D_MENUSEL_TYPE")?></td>
	<td width="100%" nowrap>
		<?
		$armt = GetMenuTypes($site, "left=".GetMessage("FILEMAN_D_MENUSEL_LEFT").",top=".GetMessage("FILEMAN_D_MENUSEL_TOP"));

		?>
		<select name="menutype" onChange="chtyp()">
			<?
			$arAllItems = Array();
			$strSelected = "";
			foreach($armt as $key => $title)
			{
				if($APPLICATION->GetFileAccessPermission(Array($site, $path."/.".$key.".menu.php")) < "W") continue;

				$arItems = Array();
				$res = CFileMan::GetMenuArray($DOC_ROOT.$path."/.".$t[0].".menu.php");
				$aMenuLinksTmp = $res["aMenuLinks"];
				if(!is_array($aMenuLinksTmp))
					$aMenuLinksTmp = Array();
				$itemcnt = 0;
				for($j=0; $j<count($aMenuLinksTmp); $j++)
				{
					$aMenuLinksItem = $aMenuLinksTmp[$j];
					$arItems[] = htmlspecialchars($aMenuLinksItem[0]);
				}
				$arAllItems[$t[0]] = $arItems;
				if($strSelected=="")
					$strSelected = $t[0];
				?><option value="<?echo htmlspecialchars($t[0])?>"><?echo htmlspecialchars($t[1]." [".$t[0]."]")?></option><?
			}
			?>
		</select>
	</td>
</tr>
<script language="JavaScript">
<!--
<?
$arTypes = array_keys($arAllItems);
$strTypes="";
$strItems="";
for($i=0; $i<count($arTypes); $i++)
{
	if($i>0)
	{
		$strTypes .= ",";
		$strItems .= ",";
	}
	$strTypes.="'".AddSlashes($arTypes[$i])."'";
	$arItems = $arAllItems[$arTypes[$i]];
	$strItems .= "Array(";
	for($j=0; $j<count($arItems); $j++)
	{
		if($j>0)$strItems .= ",";
		$strItems.="'".AddSlashes($arItems[$j])."'";
	}
	$strItems .= ")";
}
?>
var arTypes = Array(<?echo $strTypes?>);
var arItems = Array(<?echo $strItems?>);
function chtyp(strInitValue1, strInitValue2)
{
	var cur = document.all("menutype")[document.all("menutype").selectedIndex].value;
	for(i=0; i<arTypes.length; i++)
		if(cur==arTypes[i])
			break;
	var itms = arItems[i];

	var list = document.all("menuitem");
	while(list.length>0)list.remove(0);
	for(i=0; i<itms.length; i++)
	{
		var oOption = document.createElement("OPTION");
		list.options.add(oOption);
		oOption.innerText = itms[i];
		oOption.value = i+1;
	}

	if(strInitValue1)
		list.value=strInitValue1;
	else
		list.selectedIndex=0;

	chitemtype();

	list = document.all("newppos");
	while(list.length>0)list.remove(0);
	for(i=0; i<itms.length; i++)
	{
		var oOption = document.createElement("OPTION");
		list.options.add(oOption);
		oOption.innerText = itms[i];
		oOption.value = i+1;
	}
	var oOption = document.createElement("OPTION");
	list.options.add(oOption);
	oOption.innerText = "<?echo GetMessage("FILEMAN_D_MENUSEL_LAST")?>";
	oOption.value = 0;
	if(strInitValue2)
		list.value=strInitValue2;
	else
		list.selectedIndex=list.length-1;
}

function chitemtype()
{
	var cur = document.all("menutype")[document.all("menutype").selectedIndex].value;
	for(i=0; i<arTypes.length; i++)
		if(cur==arTypes[i])
			break;
	var itms = arItems[i];
	if(itms.length<=0)
	{
		itemtype[0].checked = true;
		itemtype[1].disabled = true;
	}
	else
		itemtype[1].disabled = false;

	var x1=document.all('0');
	var x2=document.all('1');
	if(itemtype[0].checked)
	{
		x1.style.display='block';
		x2.style.display='none';
	}
	else
	{
		x1.style.display='none';
		x2.style.display='block';
	}
}
//-->
</script>
<tr>
	<td width="0%" valign="top" nowrap align="right"><?echo GetMessage("FILEMAN_D_MENUSEL_ITEM")?></td>
	<td>
	<input type="radio" name="itemtype" value="n" onclick="chitemtype()"> <span onmousedown="itemtype[0].checked=true;chitemtype();"><?echo GetMessage("FILEMAN_D_MENUSEL_ADD_NEW")?></span><br>
	<input type="radio" name="itemtype" value="e" onclick="chitemtype()"> <span onmousedown="itemtype[1].checked=true;chitemtype();"><?echo GetMessage("FILEMAN_D_MENUSEL_INTO")?></span>
	</td>
</tr>
<tr>
	<td colspan="2">
	<div id="0" style="display:none">
		<table width="100%">
		<tr>
			<td width="0%" nowrap><?echo GetMessage("FILEMAN_D_MENUSEL_NAME")?></td>
			<td width="100%" nowrap><input type="text" style="width:100%" name="newp" value=""></td>
		</tr>
		<tr>
			<td nowrap><?echo GetMessage("FILEMAN_D_MENUSEL_INSERT_BEFORE")?></td>
			<td><select name="newppos"><?
				$arItems = $arAllItems[$strSelected];
				for($i=0; $i<count($arItems); $i++):
				?><option value="<?echo $i+1?>"><?echo $arItems[$i]?></option><?
				endfor;
				?><option value="0" selected><?echo GetMessage("FILEMAN_D_MENUSEL_LAST")?></option>
				</select></td>
		</tr>
		</table>
	</div>
	<div id="1" style="display:none">
		<table width="100%">
		<tr>
			<td width="0%" nowrap><?echo GetMessage("FILEMAN_D_MENUSEL_EXISTS")?></td>
			<td width="100%" nowrap>
				<select name="menuitem"><?
					$arItems = $arAllItems[$strSelected];
					for($i=0; $i<count($arItems); $i++):
					?><option value="<?echo $i+1?>"><?echo $arItems[$i]?></option><?
					endfor;
				?></select>
			</td>
		</tr>
		</table>
	</div>
	</td>
</tr>
</table>
<br>
<div align="center">
<BUTTON ID=Ok TYPE=SUBMIT>OK</BUTTON>&nbsp;<BUTTON ONCLICK="window.close();"><?=GetMessage("FILEMAN_CANCEL")?></BUTTON>
</div>
