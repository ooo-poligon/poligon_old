<?
if(!defined("FROMDIALOGS") || FROMDIALOGS!==true)
	die("");

IncludeModuleLangFile(__FILE__);

$site = CFileMan::__CheckSite($site);
$DOC_ROOT = CSite::GetSiteDocRoot($site);

$path = Rel2Abs("/", $path);
$abs_path = $DOC_ROOT.$path;
$bUploaded = false;
if($REQUEST_METHOD=="POST" && strlen($saveimg)>0)
{
	if($APPLICATION->GetFileAccessPermission(Array($site, $path))<"W")
    	$strWarning = GetMessage('FILEMAN_CAN_NOT_WRITE')."<br>";
	else
	{
		$file_name = CFileman::GetFileName($path);
		if(strlen($file_name)<=0 || $file_name=="none")
			$strWarning .= GetMessage("FILEMAN_D_INSIMG_UPLOAD_FILENOTEXISTS");
		elseif(!$USER->IsAdmin() && (in_array(CFileman::GetFileExtension($file_name), CFileMan::GetScriptFileExt()) || $file_name[0]=="."))
			$strWarning .= GetMessage("FILEMAN_D_INSIMG_UPLOAD_BAD_TYPE");
		elseif(file_exists($abs_path))
	    	$strWarning = GetMessage("FILEMAN_FILE_EXIST");
		else
		{
			CheckDirPath($abs_path);
			if(copy($_FILES["imgfile"]["tmp_name"], $abs_path))
			{
				$bUploaded=true;
				@chmod($abs_path, BX_FILE_PERMISSIONS);
			}
		}
	}
}
?>
<!DOCTYPE HTML PUBLIC "-//IETF//DAREA HTML//EN">
<HTML><HEAD>
<STYLE TYPE="text/css">
BODY {margin-left:0; margin-top:0; font-family:Arial; font-size:12px; background:menu}
BUTTON {width:5em}
TABLE  {font-family:Arial; font-size:12px}
</STYLE>
<meta http-equiv="Content-Type" content="text/html; charset=<?echo LANG_CHARSET?>">
</HEAD>
<BODY  scroll="NO">
<script>
<!--
<?if($bUploaded):?>
	parent.OK("<?echo $path?>");
<?else:?>
	alert("<?echo $strWarning?>");
	parent.InitF();
<?endif?>
//-->
</script>
</body>
</html>
