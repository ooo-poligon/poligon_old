<?
if(!IsModuleInstalled('translate') || $APPLICATION->GetGroupRight("translate")<="D")
	return;

if(strtoupper($_GET["show_lang_files"])=="Y" || strtoupper($_GET["show_lang_files"])=="N")
	$_SESSION["SHOW_LANG_FILES"] = strtoupper($_GET["show_lang_files"]);

if($_SESSION["SHOW_LANG_FILES"]!="Y") 
	return;
?>
<p class="text">
<table cellpadding="2">
<?
global $ALL_LANG_FILES;
$NEW_LANGS = $ALL_LANG_FILES;
$NEW_LANGS_1 = Array();
$NEW_LANGS_2 = Array();
for($i=0; $i<count($NEW_LANGS); $i++)
{
	$p = substr($NEW_LANGS[$i], strlen($_SERVER["DOCUMENT_ROOT"]));
	if(substr($p, 0, 1)!="/")
		$p = "/".$p;

	if(
		(strpos($p, "/menu")!==false)
		|| (strpos($p, "/classes")!==false)
		|| (strpos($p, "tools.")!==false)
		|| (strpos($p, "/include.")!==false)
		|| (strpos($p, "menu_template.php")!==false)
		|| (strpos($p, ".menu.")!==false)
		|| (strpos($p, "/top_panel.php")!==false)
		|| (strpos($p, "prolog_main_admin.php")!==false)
		|| (strpos($_SERVER["REQUEST_URI"], "/iblock_")===false && strpos($p, "/modules/iblock/lang/")!==false)
	)
		$NEW_LANGS_1[] = $p;
	else
		$NEW_LANGS_2[] = $p;
}

$NEW_LANGS_1 = array_unique($NEW_LANGS_1);
$NEW_LANGS_2 = array_unique($NEW_LANGS_2);

asort($NEW_LANGS_1); 
reset($NEW_LANGS_1); 

$NEW_LANGS_2 = array_reverse($NEW_LANGS_2, TRUE); 

$NEW_LANGS = array_merge($NEW_LANGS_2, $NEW_LANGS_1);
foreach($NEW_LANGS as $i=>$vvv):
	$stf = "";
	if(strlen($NEW_LANGS[$i])>0):
		if(strlen($_REQUEST["srchlngfil"])>0)
		{
			$MESS_t = $MESS;
			$MESS = Array();
			$bFound = false;
			@include($_SERVER["DOCUMENT_ROOT"].$NEW_LANGS[$i]);
			$stf = "";
			foreach($MESS as $k=>$v)
			{
				if(strpos($v, $_REQUEST["srchlngfil"])!==false)
				{
					$bFound = true;
					$k = "#$k";
					$stf .= ' <a href="/bitrix/admin/translate_edit.php?lang='.LANG.'&file='.$NEW_LANGS[$i].$k.'">'.htmlspecialchars($v)."</a><br>";
				}
			}
			$MESS = $MESS_t;
			if(!$bFound)
				continue;
			
		}
?>
<tr>
<td valign="top"><font class="text">
<a href="/bitrix/admin/translate_edit.php?lang=<?=LANG?>&file=<?=$NEW_LANGS[$i]?>"><?=$NEW_LANGS[$i]?></a> 
</font>
</td>
<td valign="top"><font class="text"><?=$stf?></font></td>
</tr>
<?
	endif;
endforeach;

global $APPLICATION;
?>
</table>
<form method="<?=$_SERVER["REQUEST_METHOD"]?>" 
	action="<?
		echo $APPLICATION->GetCurPage();
		if($_SERVER["REQUEST_METHOD"]=="POST")
		{
			$s = DeleteParam(array("srchlngfil", "srchlngfilb"));
			if(strlen($s)>0) echo "?".$s;
		}
		?>"
>
	<?
	if($_SERVER["REQUEST_METHOD"]=="POST")
		$v = $_POST;
	else
		$v = $_GET;
	?>
	<?
	foreach($v as $vname=>$vvalue):
		if($vname=="srchlngfilb" || $vname=="srchlngfil") continue;
	?>
	<input type="hidden" name="<?echo htmlspecialchars($vname)?>" value="<?echo htmlspecialchars($vvalue)?>">
	<?endforeach?>
	<input type="text" size="30" class="typeinput" name="srchlngfil"  value="<?=$_REQUEST["srchlngfil"]?>">
	<input type="submit" class="button" name="srchlngfilb" value="OK">
</form>
</p>
