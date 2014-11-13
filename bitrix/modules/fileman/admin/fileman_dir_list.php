<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");

if (!$USER->CanDoOperation('fileman_view_file_structure'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

if ($APPLICATION->GetGroupRight("fileman")=="D") 
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
$APPLICATION->SetAdditionalCSS("/bitrix/modules/fileman/fileman_admin.css");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
IncludeModuleLangFile(__FILE__);

$strWarning = "";
$path = Rel2Abs("/", $path);

$site = CFileMan::__CheckSite($site);
$DOC_ROOT = CSite::GetSiteDocRoot($site);

$arParsedPath = CFileMan::ParsePath(Array($site, $path), false, "/bitrix/admin/fileman_dir_list.php", "input_name=".urlencode($input_name));
$abs_path = $DOC_ROOT.$path;
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?echo LANG_CHARSET?>">
<title><?echo GetMessage("FILEMAN_T_CHOOSE_DIR")?></title>
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
<body class="sitebody">
<?
if(CSite::IsDistinctDocRoots() && strlen($site)<=0):
	$path = Rel2Abs("/", $path);

	$arParsedPath = CFileMan::ParsePath(Array($site, $path));
	?>
	<font class="text"><?echo $arParsedPath["HTML"];?></font><br><br>

	<table border="0" cellspacing="1" width="100%">
	<tr>
		<td valign="middle" align="left" class="tablehead2"><font class="tableheadtext">Sites</font></td>
	</tr>
	<?
	$db_sites = CSite::GetList($b="NAME", $o="asc");
	while($ar = $db_sites->GetNext())
	{
		?>
		<tr>
		<td class="tablebodysm">
			<table cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td align="left"><font class="tablebodytext"><a href="fileman_dir_list.php?lang=<?echo LANG?>&site=<?=Urlencode($ar["ID"])?>&path=<?echo UrlEncode($arParsedPath["PREV"])?>&input_name=<?echo urlencode($input_name)?>"><IMG SRC="/bitrix/images/fileman/folder.gif" WIDTH="17" HEIGHT="15" BORDER=0 ALT=""></a></font></td>
					<td align="left" nowrap><font class="tablebodytext">&nbsp;<a class="tablebodylink" href="fileman_dir_list.php?lang=<?echo LANG?>&site=<?=Urlencode($ar["ID"])?>&path=<?echo UrlEncode($arParsedPath["PREV"])?>&input_name=<?echo urlencode($input_name)?>"><?=$ar["NAME"];?></a></font></td>
				</tr>
			</table>
		</td>
		</tr>
		<?
	}
	?>
	</table>
	<?
else: //if(CSite::IsDistinctDocRoots() && strlen($site)<=0):

//проверим права на доступ в эту папку.
if(($FILE_ACCESS = $APPLICATION->GetFileAccessPermission(Array($site, $path))) < "R"):
	ShowError($arParsedPath["HTML"].'<br><br><img src="/bitrix/images/fileman/deny.gif" width="28" height="28" border="0" align="left" alt="">'.GetMessage("ACCESS_DENIED"));
else: //if($APPLICATION->GetFileAccessPermission($path)<"R"):
	CFileMan::GetDirList(Array($site, $path), $arDirs, $arFiles, Array("EXTENSIONS"=>"gif,jpg,jpeg,bmp,png"), Array("name"=>"asc"));
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

DoEvent("OnLoad('<?echo AddSlashes($path);?>')");

function OpenFile(fileencode, path)
{
	DoEvent("OnFileSelect('"+path+'/'+fileencode+"')");
}

function okfilename_OnClick()
{
	fileencode = document.fform.actfile_name.value;
	OpenFile(fileencode, '<?echo $path?>');
}
//-->
</script>
<font class="notetext"><?=GetMessage("FILEMAN_FILE_SELECTED_FOLDER");?></font><br>
<font class="text"><?echo $arParsedPath["HTML"];?></font><br><br>

<table width="100%" border="0" cellpadding="1" cellspacing="1">
	<tr>
		<td class="tablehead1" align="center"><font class="tableheadtext"><?echo GetMessage('FILEMAN_FILE_NAME')?></font></td>
		<td class="tablehead3" align="center"><font class="tableheadtext"><?echo GetMessage('FILEMAN_FILE_TIMESTAMP')?></font></td>
	</tr>
	<?if(strlen($path)>0):?>
	<tr>
		<td class="tablebodysm" colspan="2"><font class="tablebodytext"><a href="fileman_dir_list.php?lang=<?echo LANG?>&path=<?echo UrlEncode($arParsedPath["PREV"])?>&input_name=<?echo urlencode($input_name)?>"><IMG SRC="/bitrix/images/fileman/folder_up.gif" WIDTH="17" HEIGHT="15" BORDER=0 alt="<?=GetMessage("FILEMAN_UP")?>"></a>&nbsp;<a class="tablebodylink" href="fileman_dir_list.php?lang=<?echo LANG?>&site=<?=$site?>&path=<?echo UrlEncode($arParsedPath["PREV"])?>&input_name=<?echo urlencode($input_name)?>">..</a></font></td>
	</tr>
	<?endif;?>
	<?
	$i=0;
	foreach($arDirs as $Dir):
		$i++;
	?>
	<tr valign="top">
	<?if(strpos($Dir["NAME"], "image")!==false):?>
		<td class="tablebodysm">
			<table cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td align="left"><font class="tablebodytext"><a href="fileman_dir_list.php?lang=<?echo LANG?>&site=<?=$site?>&path=<?echo UrlEncode($path."/".$Dir["NAME"])?>&input_name=<?echo urlencode($input_name)?>"><IMG SRC="/bitrix/images/fileman/folder.gif" WIDTH="17" HEIGHT="15" BORDER=0 ALT=""></a></font></td>
				<td align="left" nowrap><font class="tablebodytext">&nbsp;<a class="tablebodylink" href="fileman_dir_list.php?lang=<?echo LANG?>&site=<?=$site?>&path=<?echo UrlEncode($path."/".$Dir["NAME"])?>&input_name=<?echo urlencode($input_name)?>"><?echo htmlspecialchars($Dir["NAME"])?></a></font></td>
			</tr>
			</table>
		</td>
	<?else:?>
		<td class="tablebodysm">
			<table cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td align="left"><font class="tablebodytext"><a href="fileman_dir_list.php?lang=<?echo LANG?>&site=<?=$site?>&path=<?echo UrlEncode($path."/".$Dir["NAME"])?>&input_name=<?echo urlencode($input_name)?>"><IMG SRC="/bitrix/images/fileman/folder.gif" WIDTH="17" HEIGHT="15" BORDER=0 ALT=""></a></font></td>
				<td align="left" nowrap><font class="tablebodytext">&nbsp;<a class="tablebodylink" href="fileman_dir_list.php?lang=<?echo LANG?>&site=<?=$site?>&path=<?echo UrlEncode($path."/".$Dir["NAME"])?>&input_name=<?echo urlencode($input_name)?>"><?echo htmlspecialchars($Dir["NAME"])?></a></font></td>
			</tr>
			</table>
		</td>
	<?endif?>
		<td align="center" class="tablebody" nowrap><font class="tablebodytext"><?echo $Dir["DATE"];?></font></td>
	</tr>
	<?endforeach;?>
</table>
<p align="left"><input type="button" class="button" value="&nbsp;&nbsp;&nbsp;OK&nbsp;&nbsp;&nbsp;" onclick="<?if(CSite::IsDistinctDocRoots() && $site):?>opener.document.<?=(empty($site_input_name)?'fileman_form.site_copy_to':$site_input_name)?>.value='<?=$site?>';<?endif?>opener.document.<?=(empty($input_name)?'fileman_form.copy_to':$input_name)?>.value='<?echo $path?>/';window.close()">
<input type="button" class="button" value="<?=GetMessage("FILEMAN_CANCEL")?>" onclick="window.close()"></p>
<?endif?>
</body>
</html>
<?
endif; //if(CSite::IsDistinctDocRoots() && strlen($site)<=0):

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php")
?>
