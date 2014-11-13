<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/condition.php");

if(!$USER->CanDoOperation('fileman_edit_menu_elements'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
IncludeModuleLangFile(__FILE__);

$addUrl = 'lang='.LANGUAGE_ID.($logical == "Y"?'&logical=Y':'');

if(($extended=="Y" || $extended=="N") && $extended != ${COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_menumode"})
	setcookie(COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_menumode", $extended, time()+60*60*24*30*60 ,'/');
else
	$extended = ${COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_menumode"};

$strWarning = "";
$menufilename = "";

while(($l=strlen($path))>0 && $path[$l-1]=="/")
	$path = substr($path, 0, $l-1);

$path = Rel2Abs("/", $path);
$dbSitesList = CSite::GetList($b = "SORT", $o = "asc");
while($arSite = $dbSitesList->GetNext())
{
	$dir = rtrim($arSite["DIR"], "/");
	if ($dir != '' && substr($path, 0, strlen($dir)) == $dir)
	{
		$site = $arSite["ID"];
		break;
	}
}

$site = CFileMan::__CheckSite($site);
$DOC_ROOT = CSite::GetSiteDocRoot($site);

$arParsedPath = CFileMan::ParsePath(Array($site, $path), true, false, "", $logical == "Y");
$menufilename = $path;

$bExists = false;
$arTypes = Array();
$armt = GetMenuTypes($site, "left=".GetMessage("FILEMAN_MENU_EDIT_LEFT_MENU").",top=".GetMessage("FILEMAN_MENU_EDIT_TOP_MENU"));

foreach($armt as $key => $title)
{
	if(!$USER->CanDoFileOperation('fm_edit_existent_file', Array($site, $path."/.".$key.".menu.php")))
		continue;
		
	$arTypes[] = array($key, $title);
	if($key == $name)
		$bExists = true;
}
if(!$bExists) 
	$name = $arTypes[0][0];

if(strlen($name)>0)
	$menufilename = $path."/.".$name.".menu.php";

$abs_path = $DOC_ROOT.$menufilename;

if(file_exists($abs_path) && strlen($new)<=0)
	$bEdit = true;
else
	$bEdit = false;

if($extended=="Y")
	$bSimple = false;
else
	$bSimple = true;
$arPath_m = Array($site, $menufilename);
$only_edit = (!$USER->CanDoOperation('fileman_add_element_to_menu') || !$USER->CanDoFileOperation('fm_create_new_file',$arPath_m));

//проверим права на доступ в эту папку
if(!$USER->CanDoOperation('fileman_edit_existent_files') || !$USER->CanDoFileOperation('fm_edit_existent_file', $arPath_m) ||
(!$bEdit && $only_edit))
	$strWarning = GetMessage("ACCESS_DENIED");
else
{
	if($REQUEST_METHOD=="POST" && strlen($save)>0 && is_array($ids) && check_bitrix_sessid())
	{
		$sMenuTemplateTmp = "";
		if(strlen($template)>0 && $template!=GetMessage("FILEMAN_MENU_EDIT_DEF"))
			$sMenuTemplateTmp = Rel2Abs("/", $template);

		$res = CFileMan::GetMenuArray($abs_path);
		if($bSimple)
		{
			$aMenuLinksTmp = $res["aMenuLinks"];
			$sMenuTemplateTmp = $res["sMenuTemplate"];
		}
		else
		{
			$aMenuLinksTmp = $res["aMenuLinks"];
			$aMenuLinksTmp_ = Array();
		}

		//соберем $aMenuLinksTmp из того что пришло с формы
		$aMenuSort = Array();
		for($i=0; $i<count($ids); $i++)
		{
			$num = $ids[$i];
			if (!isset($aMenuLinksTmp[$num-1]) && $only_edit)
				continue;
			
			if($bSimple)
			{
				if((${"del_".$num}=="Y" || (strlen(${"text_".$num})<=0 && strlen(${"link_".$num})<=0)) && !$only_edit)
				{
					unset($aMenuLinksTmp[$num-1]);
					continue;
				}

				
				$aMenuLinksTmp[$num-1][0] = ${"text_".$num};
				$aMenuLinksTmp[$num-1][1] = ${"link_".$num};
			}
			else
			{	
				if(${"del_".$num}=="Y" && !$only_edit)
					continue;

				$aMenuItem = Array(${"text_".$num}, ${"link_".$num});

				$arAddLinks = Array();
				$additional_link = ${"additional_link_".$num};
				$arAddLinksTmp = explode("\n", $additional_link);
				for($j=0; $j<count($arAddLinksTmp); $j++)
				{
					if(strlen(trim($arAddLinksTmp[$j]))>0)
						$arAddLinks[] = trim($arAddLinksTmp[$j]);
				}
				$aMenuItem[] = $arAddLinks;

				$arParams = Array();
				$param_cnt = IntVal(${"param_cnt_".$num});
				for($j=1; $j<=IntVal($param_cnt); $j++)
				{
					$param_name = trim(${"param_name_".$num."_".$j});
					$param_value = trim(${"param_value_".$num."_".$j});
					if(strlen($param_name)>0 || strlen($param_value)>0)
						$arParams[$param_name]=$param_value;
				}
				$aMenuItem[] = $arParams;

				if ($USER->CanDoOperation('edit_php') || $_REQUEST['selected_type'][$num] != 'php')
					$aMenuItem[] = ConditionCompose(${"condition_$num"}, $num);
				else
					$aMenuItem[] = $res["aMenuLinks"][$num-1][4];
				
				$aMenuLinksTmp_[] = $aMenuItem;
			}
			$aMenuSort[] = IntVal(${"sort_".$num});
		}
		if(!$bSimple)
			$aMenuLinksTmp = $aMenuLinksTmp_;

		for($i=0; $i<count($aMenuSort)-1; $i++)
			for($j=$i+1; $j<count($aMenuSort); $j++)
				if($aMenuSort[$i]>$aMenuSort[$j])
				{
					$tmpSort = $aMenuLinksTmp[$i];
					$aMenuLinksTmp[$i] = $aMenuLinksTmp[$j];
					$aMenuLinksTmp[$j] = $tmpSort;

					$tmpSort = $aMenuSort[$i];
					$aMenuSort[$i] = $aMenuSort[$j];
					$aMenuSort[$j] = $tmpSort;
				}
		//теперь $aMenuLinksTmp прямо в таком готовом виде, что хоть меню рисуй :-)
	}

	if($REQUEST_METHOD=="POST" && strlen($save)>0 && strlen($name)<=0 && check_bitrix_sessid())
	{
		$strWarning = GetMessage("FILEMAN_MENU_EDIT_ENTER_TYPE");
	}
	elseif(strlen($new)>0 && strlen($name)>0 && file_exists($abs_path) && check_bitrix_sessid())
	{
		$strWarning = GetMessage("FILEMAN_MENU_EDIT_EXISTS_ERROR");
		$bEdit = false;
		$abs_path = $DOC_ROOT.$path;
	}

	if(strlen($strWarning)<=0)
	{
		if($REQUEST_METHOD=="POST" && strlen($save)>0 && is_array($ids) && check_bitrix_sessid())
		{
			CFileMan::SaveMenu(Array($site, $menufilename), $aMenuLinksTmp, $sMenuTemplateTmp);
			$bEdit = true;

			if(strlen($apply)<=0)
			{
				if(strlen($back_url)>0)
					LocalRedirect("/".ltrim($back_url, "/"));
				else
					LocalRedirect("/bitrix/admin/fileman_admin.php?".$addUrl."&site=".$site."&path=".UrlEncode($path));
			}
			else
				LocalRedirect("/bitrix/admin/fileman_menu_edit.php?".$addUrl."&site=".$site."&path=".UrlEncode($path)."&name=".$name);
		}
	}
}

if($bEdit)
	$APPLICATION->SetTitle(GetMessage("FILEMAN_MENU_EDIT_TITLE"));
else
	$APPLICATION->SetTitle(GetMessage("FILEMAN_MENU_EDIT_TITLE_ADD"));

foreach ($arParsedPath["AR_PATH"] as $chainLevel)
{
	$adminChain->AddItem(
		array(
			"TEXT" => htmlspecialcharsex($chainLevel["TITLE"]),
			"LINK" => ((strlen($chainLevel["LINK"]) > 0) ? $chainLevel["LINK"] : ""),
		)
	);
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT" => GetMessage("FILEMAN_BACK"),
		"LINK" => "fileman_admin.php?".$addUrl."&site=".$site."&path=".UrlEncode($path)
	)
);

if(strlen($strWarning)<=0):

$aMenu[] = array("SEPARATOR"=>"Y");

if ($bSimple)
{
	$aMenu[] = array(
		"TEXT" => GetMessage("FILEMAN_MENU_EDIT_EXT"),
		"LINK" => "fileman_menu_edit.php?path=".UrlEncode($path)."&site=".$site."&".$addUrl."&".($bEdit ? "name=".$name : "new=y")."&extended=Y&back_url=".urlencode($back_url)
	);
}
else
{
	$aMenu[] = array(
		"TEXT" => GetMessage("FILEMAN_MENU_EDIT_SIMPLE"),
		"LINK" => "fileman_menu_edit.php?path=".UrlEncode($path)."&site=".$site."&".$addUrl."&".($bEdit ? "name=".$name : "new=y")."&extended=N&back_url=".urlencode($back_url)
	);
}

if ($bEdit && !$only_edit)
{
	if ($USER->CanDoOperation('edit_php'))
	{
		$aMenu[] = array("SEPARATOR"=>"Y");

		$aMenu[] = array(
			"TEXT" => GetMessage("FILEMAN_MENU_EDIT_AS_TEXT"),
			"LINK" => "fileman_file_edit.php?path=".UrlEncode($path."/.".$name.".menu.php")."&site=".$site."&full_src=Y&".$addUrl
		);
	}

	$aMenu[] = array("SEPARATOR"=>"Y");
	$aMenu[] = array(
		"TEXT" => GetMessage("FILEMAN_MENU_EDIT_DELETE"),
		"LINK" => "javascript:if(confirm('".GetMessage("FILEMAN_DEL_CONF")."')) window.location='fileman_admin.php?path=".UrlEncode($path)."&action=delete&ID[]=".UrlEncode(".".$name.".menu.php")."&".$addUrl."&".bitrix_sessid_get()."#tb';",
		"WARNING" => "Y"
	);
}

$context = new CAdminContextMenu($aMenu);
$context->Show();

$number_new_params = COption::GetOptionInt("fileman", "num_menu_param", 1, $site);
?>
<?endif;?>
<?CAdminMessage::ShowMessage($strWarning);?>

<?if(strlen($strWarning)<=0):?>
<?if($USER->CanDoFileOperation('fm_edit_existent_file',$arPath_m)):?>
<script>
function AddMenuItem(ob)
{
	var f = document.fmenu;
	var tbl = document.getElementById("t");
	var row = ob.parentNode.parentNode;
	var curnum = parseInt(row.id);
	var srt = 10;
	if(document.fmenu["sort_"+curnum])
		srt = parseInt(document.fmenu["sort_"+curnum].value) + 10;

	for(var i=1; i<=f.itemcnt.value; i++)
	{
		var s = document.fmenu["sort_"+i];
		if(s)
		{
			s = parseInt(s.value);
			if(s>=srt)
				document.fmenu["sort_"+i].value = s + 10;
		}
	}

	var num = row.rowIndex/2;
	var nums = parseInt(f.itemcnt.value)+1;
	var oRow = tbl.insertRow(num*2+1);
	oRow.id = nums;
	var oCell = oRow.insertCell(-1);

	oCell.className = '';
	oCell.align='right';
	oCell.colSpan="2";
	oCell.innerHTML = '<input type="button" onClick="AddMenuItem(this)" value="<?echo GetMessage("FILEMAN_MENU_EDIT_ADD_ITEM")?>">';

	oRow = tbl.insertRow(num*2+1);
	oRow.className = '';
	oRow.vAlign="top";

	oCell = oRow.insertCell(-1);
	oCell.width="50%";
	oCell.innerHTML =
		'<input type="hidden" name="ids[]" value="'+nums+'">'+
		'<table cellpadding="1" cellspacing="0" border="0" width="100%"> '+
		'<tr>'+
		'	<td valign="top" align="right" width="0%"><?echo GetMessage("FILEMAN_MENU_EDIT_NAME")?></td>'+
		'	<td valign="top" width="100%"><input type="text" size="20" name="text_'+nums+'" value="<?echo htmlspecialchars($aMenuLinksItem[0])?>"></td>'+
		'</tr>'+
		'<tr>'+
		'	<td valign="top" align="right"><?echo GetMessage("FILEMAN_MENU_EDIT_LINK")?>:</td>'+
		'	<td valign="top"><input type="text" size="20" name="link_'+nums+'" value=""></td>'+
		'</tr>'+
		'<tr>'+
		'	<td valign="top" align="right"><?echo GetMessage("FILEMAN_MENU_EDIT_SORT")?>:</td>'+
		'	<td valign="top"><input type="text" size="5" name="sort_'+nums+'" value="'+srt+'"></td>'+
		'</tr>'+
		'<tr>'+
		'	<td valign="top" align="right"><?echo GetMessage("FILEMAN_MENU_EDIT_DEL")?></td>'+
		'	<td valign="top" align="left"><input type="checkbox" name="del_'+nums+'" value="Y"></td>'+
		'</tr>'+
		'</table>';

	oCell = oRow.insertCell(-1);
	oCell.width="50%";
	oCell.innerHTML =
		'<table>'+
		'<tr>'+
		'	<td valign="top" align="right"><?echo GetMessage("FILEMAN_MENU_EDIT_ADDITIONAL_LINK")?></td>'+
		'	<td valign="top"><textarea rows="3" cols="30" name="additional_link_'+nums+'" WRAP="off"></textarea></td>'+
		'</tr>'+
		<?if($number_new_params>0):?>
		'<tr>'+
		'	<td valign="top" align="right"><?echo GetMessage("FILEMAN_MENU_EDIT_PARAMS")?></td>'+
		'	<td nowrap valign="top">'+
		'		<table cellpadding="0" cellspacing="1" border="0">'+
		'		<tr>'+
		'			<td align="center"><?echo GetMessage("FILEMAN_MENU_EDIT_PARAM_NAME")?></td>'+
		'			<td align="center"><?echo GetMessage("FILEMAN_MENU_EDIT_PARAM_VALUE")?></td>'+
		'		</tr>'+
				<?for($k=0; $k<$number_new_params; $k++):?>
		'			<tr>'+
		'			<td nowrap><input type="text" size="15"  name="param_name_'+nums+'_<?echo $k+1?>" value="">=</td>'+
		'			<td><input type="text" size="25"  name="param_value_'+nums+'_<?echo $k+1?>" value=""></td>'+
		'			</tr>'+
				<?endfor?>
		'		</table>'+
		'		<input type="hidden" name="param_cnt_'+nums+'" value="<?echo $k+1?>">'+
		'	</td>'+
		'</tr>'+
		<?endif?>
		'</table>';

	f.itemcnt.value = nums;
}
</script>


<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="fmenu">
<input type="hidden" name="logical" value="<?=htmlspecialchars($logical)?>">
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="site" value="<?= htmlspecialchars($site) ?>">
<input type="hidden" name="path" value="<?= htmlspecialchars($path) ?>">
<input type="hidden" name="lang" value="<?echo LANG ?>">
<input type="hidden" name="save" value="Y">
<input type="hidden" name="back_url" value="<?echo htmlspecialchars($back_url)?>">
<?if(!$bEdit):?><input type="hidden" name="new" value="Y"><?endif?>
<?=bitrix_sessid_post()?>

<?
$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("FILEMAN_TAB1"), "ICON" => "fileman", "TITLE" => GetMessage("FILEMAN_TAB1_ALT")),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>

<?
$tabControl->BeginNextTab();
?>

	<?
	if($bEdit && strlen($strWarning)<=0)
	{
		$res = CFileMan::GetMenuArray($abs_path);
		$aMenuLinksTmp = $res["aMenuLinks"];
		$sMenuTemplateTmp = $res["sMenuTemplate"];
	}
	if(!is_array($aMenuLinksTmp))
		$aMenuLinksTmp = Array();
	?>

	<tr>
		<td valign="top"><?echo GetMessage("FILEMAN_MENU_EDIT_TYPE")?></td>
		<td valign="top">
			<script>
			function ChType(ob)
			{
				window.location = "<?echo $APPLICATION->GetCurPage()?>?lang=<?echo LANG?>&site=<?=$site?>&path=<?echo $path?><?if(strlen($back_url)>0)echo "&back_url=".URlEncode($back_url);?>&name="+ob[ob.selectedIndex].value;
			}
			</script>
			<select name="name" onChange="ChType(this)">
			<?
			$bExists=false;
			for($i=0; $i<count($arTypes); $i++)
			{
				$t = $arTypes[$i];
				?><option value="<?echo htmlspecialchars($t[0])?>"<?if($name == $t[0]){$bExists=true;echo " selected";}?>>
					<?echo htmlspecialchars("[".$t[0]."] ".$t[1])?>
				</option><?
			}
			?>
			</select>
		</td>
	</tr>

	<?if(!$bSimple):?>
	<tr>
		<td valign="top"><?echo GetMessage("FILEMAN_MENU_EDIT_TEMPLATE")?></td>
		<td valign="top">
			<input type="text" name="template" size="50" maxlength="255" value="<?if(strlen($sMenuTemplateTmp)>0) echo htmlspecialchars($sMenuTemplateTmp); else echo GetMessage("FILEMAN_MENU_EDIT_DEF");?>"
			OnFocus="if(this.value=='<?echo GetMessage("FILEMAN_MENU_EDIT_DEF")?>')this.value=''"
			onfocusout="if(this.value=='')this.value='<?echo GetMessage("FILEMAN_MENU_EDIT_DEF")?>';">
		</td>
	</tr>
	<?endif//if(!$bSimple):?>

	<tr>
		<td colspan="2">

	<?
	if($bSimple):?>
		<table border="0" cellpadding="2" cellspacing="1" width="100%" class="internal">
		<tr class="heading">
		<td align="center"><?echo GetMessage("FILEMAN_MENU_EDIT_PARAM_NAME")?></td>
		<td align="center"><?echo GetMessage("FILEMAN_MENU_EDIT_LINK")?></td>
		<td align="center"><?echo GetMessage("FILEMAN_MENU_EDIT_SORT")?></td>
		<td align="center"><?echo GetMessage("FILEMAN_MENU_EDIT_DEL")?></td>
		</tr>
		<?
		$itemcnt = 0;
		//echo count($aMenuLinksTmp);
		for($i=1; $i<=count($aMenuLinksTmp)+5; $i++):
			$itemcnt++;
			if($i<=count($aMenuLinksTmp))
				$aMenuLinksItem = $aMenuLinksTmp[$i-1];
			elseif($only_edit)
					continue;
			else
				$aMenuLinksItem = Array();
		?>
		<input type="hidden" name="ids[]" value="<?echo $i?>">
		<tr>
		<td align="center"><input type="text" size="30" name="text_<?echo $i?>" value="<?echo htmlspecialchars($aMenuLinksItem[0])?>"></td>
		<td align="center"><input type="text" size="35" name="link_<?echo $i?>" value="<?echo htmlspecialchars($aMenuLinksItem[1])?>"></td>
		<td align="center"><input type="text" size="4" name="sort_<?echo $i?>" value="<?echo $i*10?>"></td>
		<td align="center">
			<?if($i<=count($aMenuLinksTmp)):?>
				<input type="checkbox" name="del_<?echo $i?>" value="Y">
			<?else:?>
				&nbsp;
			<?endif?>
		</td>
		</tr>
		<?endfor?>
		<input type="hidden" name="itemcnt" value="<?echo $itemcnt?>">
		</table>
	<?else:?>
		<?ConditionJS(array('enable_false'=>true));?>
		<table border="0" cellpadding="2" cellspacing="1" width="100%" id="t" class="internal">
		<tr class="heading">
		<td valign="top" colspan="2">
			<table cellpadding="0" cellspacing="0" width="100%" border="0">
			<tr>
				<td align="left"><?echo GetMessage("FILEMAN_MENU_EDIT_ITEMS")?></td>
			</tr>
			</table>
		</tr>
		<?if(!$only_edit):?>
		<tr id="0">
			<td align="right" colspan="2"><input type="button" onClick="AddMenuItem(this)" value="<?echo GetMessage("FILEMAN_MENU_EDIT_ADD_ITEM")?>"></td>
		</tr>
		<?endif;?>
		<?
		$itemcnt = 0;
		for($i=1; $i <= count($aMenuLinksTmp); $i++):
			$itemcnt++;
			$aMenuLinksItem = $aMenuLinksTmp[$i-1];
		?>
		<input type="hidden" name="ids[]" value="<?echo $i?>">
		<tr>
		<td valign="top" width="50%">
			<table cellpadding="1" cellspacing="0" border="0" width="100%">
			<tr>
				<td valign="top" align="right" width="0%"><?echo GetMessage("FILEMAN_MENU_EDIT_NAME")?></td>
				<td valign="top" width="100%"><input type="text" size="20" name="text_<?echo $i?>" value="<?echo htmlspecialchars($aMenuLinksItem[0])?>"></td>
			</tr>
			<tr>
				<td valign="top" align="right"><?echo GetMessage("FILEMAN_MENU_EDIT_LINK")?>:</td>
				<td valign="top"><input type="text" size="20" name="link_<?echo $i?>" value="<?echo htmlspecialchars($aMenuLinksItem[1])?>"></td>
			</tr>
			<tr>
				<td valign="top" align="right"><?echo GetMessage("FILEMAN_MENU_EDIT_SORT")?>:</td>
				<td valign="top"><input type="text" size="5" name="sort_<?echo $i?>" value="<?echo $i*10?>"></td>
			</tr>
			<tr>
				<td valign="top" align="right"><?echo GetMessage("FILEMAN_MENU_EDIT_DEL")?></td>
				<td valign="top" align="left"><input type="checkbox" name="del_<?echo $i?>" value="Y"></td>
			</tr>
			</table>
		</td>
		<td valign="top" width="50%">
			<table>
			<tr>
				<td valign="top" align="right"><?echo GetMessage("FILEMAN_MENU_EDIT_ADDITIONAL_LINK")?></td>
				<td valign="top"><textarea rows="3" cols="30" name="additional_link_<?echo $i?>" WRAP="off"><?for($j=0; $j<count($aMenuLinksItem[2]); $j++)echo htmlspecialchars($aMenuLinksItem[2][$j])."\n"?></textarea></td>
			</tr>
			<tr>
				<td valign="top" align="right"><?echo GetMessage("FILEMAN_MENU_EDIT_CONDITION_TYPE")?></td>
				<td valign="top"><?ConditionParse($aMenuLinksItem[4]); ConditionSelect($i);?>
			</tr>
			<tr>
				<td valign="top" align="right"><?echo GetMessage("FILEMAN_MENU_EDIT_CONDITION")?></td>
				<td valign="top"><?
ConditionShow(array(
	"i"		=>$i,
	"field_name"	=>"condition_$i",
	"form"		=>"fmenu"
		)
	);

			?></tr>
			
			<?if($number_new_params>0 || count($aMenuLinksItem[3])>0):?>
			<tr>
				<td valign="top" align="right"><?echo GetMessage("FILEMAN_MENU_EDIT_PARAMS")?></td>
				<td nowrap valign="top">
					<table cellpadding="0" cellspacing="1" border="0">
					<tr>
						<td align="center"><?echo GetMessage("FILEMAN_MENU_EDIT_PARAM_NAME")?></td>
						<td align="center"><?echo GetMessage("FILEMAN_MENU_EDIT_PARAM_VALUE")?></td>
					</tr>
					<?
					$j=0;
					if(is_array($aMenuLinksItem[3])):
						foreach($aMenuLinksItem[3] as $key=>$value):
							$j++;
					?>
						<tr>
						<td nowrap><input type="text" size="15" name="param_name_<?echo $i?>_<?echo $j?>" value="<?echo htmlspecialchars($key)?>">=</td>
						<td><input type="text" size="25" name="param_value_<?echo $i?>_<?echo $j?>" value="<?echo htmlspecialchars($value)?>"></td>
						</tr>
					<?
						endforeach;
					endif;

					for($k=0; $k<$number_new_params; $k++):
						$j++;
					?>
						<tr>
						<td nowrap><input type="text" size="15" name="param_name_<?echo $i?>_<?echo $j?>" value="">=</td>
						<td><input type="text" size="25" name="param_value_<?echo $i?>_<?echo $j?>" value=""></td>
						</tr>
					<?endfor?>
					</table>
					<input type="hidden" name="param_cnt_<?echo $i?>" value="<?echo $j?>">
				</td>
			</tr>
			<?endif;//if($number_new_params>0 || count($aMenuLinksItem[3])>0):?>
			</table>
			</td>
		</tr>
		<?if(!$only_edit):?>
		<tr id="<?echo $i?>">
			<td align="right" colspan="2"><input type="button" onClick="AddMenuItem(this)" value="<?echo GetMessage("FILEMAN_MENU_EDIT_ADD_ITEM")?>"></td>
		</tr>
		<?endif;?>
		<?endfor?>
		<input type="hidden" name="itemcnt" value="<?echo $itemcnt?>">
		<input type="hidden" name="extended" value="Y">
		</table>
	<?endif//if($bSimple):?>

	</td>
	</tr>

	<?$tabControl->EndTab();?>

	<?
	$tabControl->Buttons(
		array(
			"disabled" => false,
			"back_url" => ((strlen($back_url)>0 && strpos($back_url, "/bitrix/admin/fileman_menu_edit.php")!==0) ? htmlspecialchars($back_url) : "/bitrix/admin/fileman_admin.php?".$addUrl."&site=".Urlencode($site)."&path=".UrlEncode($arParsedPath["PREV"]))
		)
	);
	?>

	<?$tabControl->End();?>

</form>
<?endif?>
<?echo BeginNote();?>
<span class="required"><sup>1</sup></span> - <?=GetMessage("MAIN_PERIOD_NOTE")?>
<?echo EndNote();?>
<?endif?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>