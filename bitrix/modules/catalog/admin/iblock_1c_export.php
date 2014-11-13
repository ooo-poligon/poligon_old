<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/iblock.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/include.php");

if ($ex = $APPLICATION->GetException())
{
	require($DOCUMENT_ROOT."/bitrix/modules/main/include/prolog_admin_after.php");
	
	$strError = $ex->GetString();
	ShowError($strError);
	
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

include(GetLangFileName($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/lang/", "/iblock_1c_export.php"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");

//$CATALOG_RIGHT = $APPLICATION->GetGroupRight("catalog");

set_time_limit(0);
$max_execution_time = 3000;
$LoadFromFile = "Y";

/*
TODO:
3. Сделать интерфейс для ввода соответствий между валютами
5. ?Соответствия между типами цен
*/

$bFileWasLoaded = False;
$strError = "";
if ($REQUEST_METHOD == "POST" && $CURRENT_ACTION == "LoadFile" && $USER->CanDoOperation('catalog_export_exec') /*$CATALOG_RIGHT=="W"*/ && check_bitrix_sessid())
{
	$DATA_FILE_NAME = "";

	$STT_GROUP_ADD = 0; $STT_GROUP_UPDATE = 0; $STT_GROUP_ERROR = 0;
	$STT_CATALOG_ADD = 0; $STT_CATALOG_UPDATE = 0; $STT_CATALOG_ERROR = 0;
	$STT_PROP_ADD = 0; $STT_PROP_UPDATE = 0; $STT_PROP_ERROR = 0;
	$STT_PRODUCT_ADD = 0; $STT_PRODUCT_UPDATE = 0; $STT_PRODUCT_ERROR = 0;

	if (is_uploaded_file($_FILES["FILE_1C"]["tmp_name"]))
		$DATA_FILE_NAME = $_FILES["FILE_1C"]["tmp_name"];

	if (strlen($DATA_FILE_NAME)<=0)
	{
		if (strlen($URL_FILE_1C)>0 && file_exists($_SERVER["DOCUMENT_ROOT"].$URL_FILE_1C) && is_file($_SERVER["DOCUMENT_ROOT"].$URL_FILE_1C))
			$DATA_FILE_NAME = $_SERVER["DOCUMENT_ROOT"].$URL_FILE_1C;
	}

	if (strlen($DATA_FILE_NAME)<=0)
		$strError .= GetMessage("C_ERROR_NO_DATAFILE")."<br>";

	if (strlen($IBLOCK_TYPE_ID)<=0)
		$strError .= GetMessage("C_ERROR_NO_IBLOCKTYPE")."<br>";

	if (strlen($strError)<=0):
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/admin/templates/iblock_1c_export.php");
	endif;
}

$APPLICATION->SetTitle(GetMessage("C_DATA_LOADING"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
ShowError($strError);
if ($bFileWasLoaded)
{
	echo "<font class=\"text\"><font color=\"#009900\">";
	echo GetMessage("C_LOAD_TIME1")." ".RoundEx(getmicrotime() - START_EXEC_TIME, 2)." ".GetMessage("C_LOAD_TIME2")."<br>";

	echo GetMessage("C_LOAD_CATALOG")." ".($STT_CATALOG_UPDATE+$STT_CATALOG_ADD)." ";
	echo "(".GetMessage("C_LOAD_NEW")." ".$STT_CATALOG_ADD.", ".GetMessage("C_LOAD_CHANGED")." ".$STT_CATALOG_UPDATE.").";
	if (IntVal($STT_CATALOG_ERROR)>0) echo " <font color=\"#FF0000\">".GetMessage("C_LOAD_ERROR")." ".$STT_CATALOG_ERROR.".</font>";
	echo "<br>";

	echo GetMessage("C_LOAD_GROUP")." ".($STT_GROUP_UPDATE+$STT_GROUP_ADD)." ";
	echo "(".GetMessage("C_LOAD_NEW")." ".$STT_GROUP_ADD.", ".GetMessage("C_LOAD_CHANGED")." ".$STT_GROUP_UPDATE.").";
	if (IntVal($STT_GROUP_ERROR)>0) echo " <font color=\"#FF0000\">".GetMessage("C_LOAD_ERROR")." ".$STT_GROUP_ERROR.".</font>";
	echo "<br>";

	echo GetMessage("C_LOAD_PROPS")." ".($STT_PROP_UPDATE+$STT_PROP_ADD)." ";
	echo "(".GetMessage("C_LOAD_NEW")." ".$STT_PROP_ADD.", ".GetMessage("C_LOAD_CHANGED")." ".$STT_PROP_UPDATE.").";
	if (IntVal($STT_PROP_ERROR)>0) echo " <font color=\"#FF0000\">".GetMessage("C_LOAD_ERROR")." ".$STT_PROP_ERROR.".</font>";
	echo "<br>";

	echo GetMessage("C_LOAD_PRODUCT")." ".($STT_PRODUCT_UPDATE+$STT_PRODUCT_ADD)." ";
	echo "(".GetMessage("C_LOAD_NEW")." ".$STT_PRODUCT_ADD.", ".GetMessage("C_LOAD_CHANGED")." ".$STT_PRODUCT_UPDATE.").";
	if (IntVal($STT_PRODUCT_ERROR)>0) echo " <font color=\"#FF0000\">".GetMessage("C_LOAD_ERROR")." ".$STT_PRODUCT_ERROR.".</font>";
	echo "<br>";

	echo "</font><br><br>";
}
?>
<form method="POST" action="<?echo $sDocPath?>?lang=<?echo LANG ?>" ENCTYPE="multipart/form-data" name="dataload">
<table border="0" cellspacing="1" cellpadding="3" width="100%" class="edittable">
	<tr>
		<td valign="middle" colspan="2" align="center" nowrap class="tablehead">
			<font class="tableheadtext"><b><?echo GetMessage("C_F_TITLE");?></b></font>
		</td>
	</tr>
	<tr>
		<td align="right" nowrap class="tablebody">
			<font class="tableheadtext"><?echo GetMessage("C_F_DATAFILE");?></font>
		</td>
		<td align="left" nowrap class="tablebody">
			<input type="hidden" name="MAX_FILE_SIZE" value="1000000000">
			<font class="tablebodytext">
			<input type="file" name="FILE_1C" class="typefile">
			</font>
		</td>
	</tr>
	<?
	if (CModule::IncludeModule("fileman")):
		$FM_RIGHT = $APPLICATION->GetGroupRight("fileman");
		if ($FM_RIGHT!="D"):
			?>
			<tr>
				<td align="right" nowrap class="tablebody" valign="top">
					<font class="tableheadtext"><?echo GetMessage("C_F_DATAFILE1");?><br><?echo GetMessage("C_F_DATAFILE1_NOTE");?></font>
				</td>
				<td align="left" nowrap class="tablebody">
					<?
					$path = Rel2Abs("/", $path);
					?>
					<script>
					<!--
					function filelist_OnLoad(strDir)
					{
						document.cookie = "xlopendir=" + escape(strDir) + ";";	// expires=Fri, 31 Dec 2009 23:59:59 GMT;";
						dataload.URL_FILE_1C.value = strDir+"/";
					}

					function filelist_OnFileSelect(strPath)
					{
						dataload.URL_FILE_1C.value = strPath;
					}
					//-->
					</script>
					<input class="typeinput" type="text" name="URL_FILE_1C" size="40" value=""><br>
					<iframe name="filelist" src="cat_file_list.php?path=<?echo urlencode(isset($xlopendir) ? $xlopendir : $path)?>&lang=<?echo LANG?>" width="350" height="250" border="0" frameBorder="0"></iframe>
				</td>
			</tr>
			<?
		endif;
	endif;
	?>
	<tr>
		<td align="right" nowrap class="tablebody">
			<font class="tableheadtext"><?echo GetMessage("C_F_IBLOCK");?></font>
		</td>
		<td align="left" nowrap class="tablebody">
			<font class="tablebodytext">
			<select name="IBLOCK_TYPE_ID" class="typeselect">
				<option value="">- <?echo GetMessage("C_F_IBLOCK_SELECT") ?> -</option>
				<?
				$iblocks = CIBlockType::GetList(Array($by=>$order));
				while ($iblocks->ExtractFields("f_"))
				{
					$ibtypelang = CIBlockType::GetByIDLang($f_ID, LANG, true);
					?><option value="<?echo $f_ID ?>"><?echo htmlspecialchars($ibtypelang["NAME"]) ?></option><?
				}
				?>
			</select>
			</font>
		</td>
	</tr>
	<tr>
		<td align="right" nowrap class="tablebody" valign="top">
			<font class="tableheadtext"><?echo GetMessage("C_F_OUTFILEACTION");?>:</font>
		</td>
		<td align="left" nowrap class="tablebody">
			<font class="tablebodytext">
			<input type="radio" name="outFileAction" value="D" <?if (strlen($outFileAction)<=0 || ($outFileAction=="D")) echo "checked";?>> <?echo GetMessage("CATI_OF_DEL") ?><br>
			<input type="radio" name="outFileAction" value="H" <?if ($outFileAction=="H") echo "checked";?>> <?echo GetMessage("CATI_OF_DEACT") ?><br>
			<input type="radio" name="outFileAction" value="F" <?if ($outFileAction=="F") echo "checked";?>> <?echo GetMessage("CATI_OF_KEEP") ?>
			</font>
		</td>
	</tr>
</table>

<P>
<?=bitrix_sessid_post()?>
<input type="hidden" name="CURRENT_ACTION" value="LoadFile">
<font class="tableheadtext">
<input type="submit" class="button" value="<?echo GetMessage("C_F_LOAD");?>" <?if (!$USER->CanDoOperation('catalog_export_exec')) echo "disabled" ?> name="submit_btn">
</font>
</P>

</form>

<?require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>