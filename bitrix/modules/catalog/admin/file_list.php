<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
$FM_RIGHT = $APPLICATION->GetGroupRight("fileman");
if ($FM_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
include(GetLangFileName($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/lang/", "/file_list.php"));

if (!CModule::IncludeModule("fileman"))
{
	die("FileMan module is not installed");
}

$strWarning = "";
$path = Rel2Abs("/", $path);
$arParsedPath = CFileMan::ParsePath($path);
$abs_path = $DOCUMENT_ROOT.$path;
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?echo LANG_CHARSET?>">
<style>
<?
if (false && file_exists($DOCUMENT_ROOT."/bitrix/php_interface/admin_styles.css"))
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/admin_styles.css");
}
else
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/interface/admin_styles.css");
}

if (strlen($APPLICATION->GetAdditionalCSS())>0)
{
	require($_SERVER["DOCUMENT_ROOT"].$APPLICATION->GetAdditionalCSS());
}
?>
</style>
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<?
//проверим права на доступ в эту папку.
if (($FILE_ACCESS = $APPLICATION->GetFileAccessPermission($path)) < "R"):
	ShowError($arParsedPath["HTML"].'<br><br><img src="/bitrix/images/fileman/deny.gif" width="28" height="28" border="0" align="left" alt=""> Folder Access Denied.');
else:
	CFileMan::GetDirList($path, $arDirs, $arFiles, Array("EXTENSIONS"=>((strtoupper($datafiletype)=="CSV")?"dat,csv":"dat,xml,bml")), Array("name"=>"asc"));
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

	DoEvent('OnLoad(\'<?=CUtil::JSEscape($path);?>\')');

	function OpenFile(fileencode, path)
	{
		DoEvent("OnFileSelect('"+path+'/'+fileencode+"')");
	}

	function okfilename_OnClick()
	{
		fileencode = document.fform.actfile_name.value;
		OpenFile(fileencode, '<?=CUtil::JSEscape($path)?>');
	}
	//-->
	</script>

	<table width="100%" border="0" cellpadding="1" cellspacing="1">
		<tr>
			<td class="tablehead1" align="center"><font class="tableheadtext"><?echo GetMessage("C_FILE_FILENAME");?></font></td>
			<td class="tablehead2" align="center"><font class="tableheadtext"><?echo GetMessage("C_FILE_SIZE");?></font></td>
			<td class="tablehead3" align="center"><font class="tableheadtext"><?echo GetMessage("C_FILE_DATE");?></font></td>
		</tr>
		<?if(strlen($path)>0):?>
			<tr>
				<td class="tablebody4" colspan="3" align="left">
					<table cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td align="left"><font class="tablebodytext"><a href="<?echo $sDocPath?>?datafiletype=<?echo $datafiletype ?>&lang=<?echo LANG?>&path=<?echo UrlEncode($arParsedPath["PREV"])?>"><img src="/bitrix/images/fileman/folder_up.gif" width="17" height="15" border="0"></a></font></td>
						<td align="left" nowrap><font class="tablebodytext"><a href="<?echo $sDocPath?>?datafiletype=<?echo $datafiletype ?>&lang=<?echo LANG?>&path=<?echo UrlEncode($arParsedPath["PREV"])?>">..</a></font></td>
					</tr>
					</table>
				</td>
			</tr>
		<?endif;?>
		<?
		$i=0;
		foreach($arDirs as $Dir):
			$i++;
			?>
			<tr valign="top">
			<?if(strpos($Dir["NAME"], "image")!==false):?>
				<td class="tablebody1">
					<table cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td align="left"><font class="tablebodytext"><a href="<?echo $sDocPath?>?datafiletype=<?echo $datafiletype ?>&lang=<?echo LANG?>&path=<?echo UrlEncode($path."/".$Dir["NAME"])?>"><img src="/bitrix/images/fileman/folder.gif" width="17" height="15" border="0"></a></font></td>
						<td align="left" nowrap>
							<font class="tablebodytext"><a href="<?echo $sDocPath?>?datafiletype=<?echo $datafiletype ?>&lang=<?echo LANG?>&path=<?echo UrlEncode($path."/".$Dir["NAME"])?>"><?echo htmlspecialchars($Dir["NAME"])?></a></font>
						</td>
					</tr>
					</table>
				</td>
			<?else:?>
				<td class="tablebody1">
					<table cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td align="left"><font class="tablebodytext"><a href="<?echo $sDocPath?>?datafiletype=<?echo $datafiletype ?>&lang=<?echo LANG?>&path=<?echo UrlEncode($path."/".$Dir["NAME"])?>"><img src="/bitrix/images/fileman/folder.gif" width="17" height="15" border="0"></a></font></td>
						<td align="left" nowrap>
							<font class="tablebodytext"><a href="<?echo $sDocPath?>?datafiletype=<?echo $datafiletype ?>&lang=<?echo LANG?>&path=<?echo UrlEncode($path."/".$Dir["NAME"])?>"><?echo htmlspecialchars($Dir["NAME"])?></a></font>
						</td>
					</tr>
					</table>
				</td>
			<?endif?>
				<td align="right" class="tablebody2" nowrap>
					<font class="tablebodytext">&nbsp;</font>
				</td>
				<td align="center" class="tablebody3" nowrap>
					<font class="tablebodytext"><?echo $Dir["DATE"];?></font>
				</td>
			</tr>
		<?endforeach;?>
		<?foreach($arFiles as $File):
			$i++;
			?>
			<tr valign="top">
				<td class="tablebody1">
					<table cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td align="left"><a href="javascript:OpenFile('<?echo $File["NAME"]?>','<?echo $path?>')"><img src="/bitrix/images/fileman/file.gif" width="15" height="18" BORDER="0"></a></td>
						<td class="tablebody" align="left"><font class="tablebodytext"><a href="javascript:OpenFile('<?echo $File["NAME"]?>','<?echo $path?>')"><? echo $File["NAME"]; ?></a></font></td>
					</tr>
					</table>
				</td>
				<td align="right" class="tablebody2" nowrap>
					<font class="tablebodytext"><?echo $File["SIZE"];?></font>
				</td>
				<td align="center" class="tablebody3" nowrap>
					<font class="tablebodytext"><?echo $File["DATE"];?></font>
				</td>
			</tr>
		<?endforeach;?>
	</table>
<?endif?>
</body>
</html>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php")
?>