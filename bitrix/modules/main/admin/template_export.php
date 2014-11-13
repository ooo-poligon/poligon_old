<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");

if(!$USER->CanDoOperation('edit_other_settings') && !$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$ID = str_replace("\\", "", $ID);
$ID = str_replace("/", "", $ID);
$bUseCompression = True;
if(!extension_loaded('zlib') || !function_exists("gzcompress"))
	$bUseCompression = False;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/tar_gz.php");

CheckDirPath($_SERVER['DOCUMENT_ROOT'].BX_PERSONAL_ROOT."/tmp/templates/");
$tmpfname = $_SERVER['DOCUMENT_ROOT'].BX_PERSONAL_ROOT."/tmp/templates/".md5(uniqid(rand(), true).".tar.gz");

$HTTP_ACCEPT_ENCODING = "";

if(is_dir($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".$ID))
{
	$oArchiver = new CArchiver($tmpfname, $bUseCompression);
	$tres = $oArchiver->add("\"".$_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".$ID."\"", false, $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/");
	if($tres)
	{
		$strOK .= str_replace("#FILE#", $tmpfname, GetMessage("MAIN_T_EDIT_EXPORT_OK"));
		if(substr($tmpfname, 0, strlen($_SERVER["DOCUMENT_ROOT"]))==$_SERVER["DOCUMENT_ROOT"])
			$strOK .= str_replace("#ADDR#", substr($tmpfname, strlen($_SERVER["DOCUMENT_ROOT"])), " ".GetMessage("MAIN_T_EDIT_EXPORT_AND_EXISTS"));
		$strOK .= ".<br>";
	}
	else
	{
		$strError .= GetMessage("MAIN_T_EDIT_EXPORT_ERR");
		$arErrors = &$oArchiver->GetErrors();
		if(count($arErrors)>0)
		{
			$strError .= ":<br>";
			foreach ($arErrors as $value)
				$strError .= "[".$value[0]."] ".$value[1]."<br>";
		}
		else
			$strError .= ".<br>";
	}

	header('Pragma: public');
	header('Cache-control: private');
	header("Content-Type: application/force-download; name=\"".$ID.".tar.gz\"");
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".filesize($tmpfname));
	header("Content-Disposition: attachment; filename=\"".$ID.".tar.gz\"");
	header("Expires: 0");
	
	readfile($tmpfname);
	unlink($tmpfname);
	//	die();
}

if (strlen($strError) > 0)
{
	$APPLICATION->SetTitle(GetMessage("EXPORT_ERROR"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	CAdminMessage::ShowMessage($strError);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_before.php");
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_after.php");
?>
