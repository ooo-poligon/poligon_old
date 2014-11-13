<?
if(!defined("FROMDIALOGS") || FROMDIALOGS!==true)
	die("");

IncludeModuleLangFile(__FILE__);

$strWarning="";
$site = CFileMan::__CheckSite($site);
$DOC_ROOT = CSite::GetSiteDocRoot($site);

$path = Rel2Abs("/", $path);
$arParsedPath = CFileMan::ParsePath(Array($site, $path));
while(strlen($path)>0 && substr($path, -1, 1)=="/")
	$path = substr($path, 0, -1);

$abs_path = $DOC_ROOT.$path;
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?echo LANG_CHARSET?>">
<?echo '<link rel="stylesheet" type="text/css" href="/bitrix/themes/'.ADMIN_THEME_ID.'/compatible.css?'.SM_VERSION.'">'."\n";?>
<style>
<?
if(strlen($APPLICATION->GetAdditionalCSS())>0)
{
	require($_SERVER["DOCUMENT_ROOT"].$APPLICATION->GetAdditionalCSS());
}
?>
</style>
</head>
<body leftmargin="0" topmargin="0">
<?
//проверим права на доступ в эту папку.
if(($FILE_ACCESS = $APPLICATION->GetFileAccessPermission(Array($site, $path))) < "R"):
	ShowError($arParsedPath["HTML"].'<br><br><img src="/bitrix/images/fileman/deny.gif" width="28" height="28" border="0" alt="">'.GetMessage("ACCESS_DENIED"));
else: //if($APPLICATION->GetFileAccessPermission($path)<"R"):
	CFileMan::GetDirList(Array($site, $path), $arDirs, $arFiles, Array(), Array("name"=>"asc"));
?>
<script>
<!--
function DoEvent(str)
{
	try
	{
		eval("parent."+this.name+"_"+str);
	}
	catch(e){}
}

<?

$strFiles = "";
for($i=0; $i<count($arDirs); $i++)
	$strFiles .= ", '".AddSlashes($arDirs[$i]["NAME"])."'";
for($i=0; $i<count($arFiles); $i++)
	$strFiles .= ", '".AddSlashes($arFiles[$i]["NAME"])."'";
if($strFiles!="")
	$strFiles = substr($strFiles, 1);
?>

DoEvent("OnLoad('<?echo AddSlashes($path);?>', Array(<?echo $strFiles?>))");

function OpenFile(fileencode)
{
	DoEvent("OnFileSelect('"+fileencode+"')");
}

function okfilename_OnClick()
{
	fileencode = document.fform.actfile_name.value;
	OpenFile(fileencode, '<?echo $path?>');
}
//-->
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="1" width="100%">
	<tr>
		<td class="tablehead1" align="center"><font class="tableheadtext"><?echo GetMessage('FILEMAN_FILE_NAME')?></font></td>
		<td class="tablehead2" align="center"><font class="tableheadtext"><?echo GetMessage('FILEMAN_FILE_SIZE')?></font></td>
		<td class="tablehead3" align="center"><font class="tableheadtext"><?echo GetMessage('FILEMAN_FILE_TIMESTAMP')?></font></td>
	</tr>
	<?
	$i=0;
	foreach($arDirs as $Dir):
		$i++;
	?>
	<tr valign="top">
		<td class="tablebodysm">
			<table cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td><font class="tablebodytext"><a href="fileman_dialog.php?dtype=browse_files&site=<?=$site?>&lang=<?echo LANG?>&path=<?echo UrlEncode($path."/".$Dir["NAME"])?>"><IMG SRC="/bitrix/images/fileman/folder.gif" WIDTH="17" HEIGHT="15" BORDER=0 ALT=""></a></font></td>
				<td nowrap><font class="tablebodytext">&nbsp;<a class="tablebodylink" href="fileman_dialog.php?dtype=browse_files&site=<?=$site?>&lang=<?echo LANG?>&path=<?echo UrlEncode($path."/".$Dir["NAME"])?>"><?echo htmlspecialchars($Dir["NAME"])?></a></font></td>
			</tr>
			</table>
		</td>
		<td align="right" class="tablebodysm" nowrap><font class="tablebodytext">&nbsp;</font></td>
		<td align="center" class="tablebodysm" nowrap><font class="tablebodytext"><?echo $Dir["DATE"];?></font></td>
	</tr>
	<?endforeach;?>
	<?foreach($arFiles as $File):
		if(substr($File["NAME"], 0, 1)==".")continue;
		$i++;
	?>
	<tr valign="top">
		<td class="tablebodysm"><table cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td><a href="javascript:OpenFile('<?echo $File["NAME"]?>','<?echo $path?>')"><IMG SRC="/bitrix/images/fileman/file.gif" WIDTH="15" HEIGHT="18" BORDER=0 ALT=""></a></td>
				<td><font class="tablebodytext">&nbsp;<a class="tablebodylink" href="javascript:OpenFile('<?echo $File["NAME"]?>','<?echo $path?>')"><? echo $File["NAME"]; ?></a></font></td>
			</tr>
			</table></td>
		<td align="right" class="tablebodysm" nowrap><font class="tablebodytext"><?echo $File["SIZE"];?></font></td>
		<td align="center" class="tablebodysm" nowrap><font class="tablebodytext"><?echo $File["DATE"];?></font></td>
	</tr>
	<?endforeach;?>
</table>
<?endif?>
</body>
</html>
