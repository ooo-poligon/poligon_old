<?
define("STOP_STATISTICS", true);

define('DLSERVER', 'www.1c-bitrix.ru');
define('DLPORT', 80);
define('DLPATH', '/download/files/locations/');
define('DLMETHOD', 'GET');

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/wizard.php");

CheckDirPath($_SERVER["DOCUMENT_ROOT"]."/bitrix/wizards/bitrix/sale.locations/upload/");

set_time_limit(600);

$lang = $_REQUEST['lang'];
if (preg_match('/^[a-z0-9_]{2}$/i', $lang) <= 0) $lang = 'en';

$wizard =  new CWizard("bitrix:sale.locations");
$wizard->IncludeWizardLang("scripts/loader.php", $lang);

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
{
	echo GetMessage('WSL_LOADER_ERROR_ACCESS_DENIED');
	require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
	die();
}

$STEP = intval($_REQUEST['STEP']);
$CSVFILE = $_REQUEST["CSVFILE"];
$bLoadLoc = $_REQUEST["LOADLOC"] == 'Y' ? 'Y' : 'N';
$bLoadZip = $_REQUEST["LOADZIP"] == 'Y' ? 'Y' : 'N';

if (!in_array($CSVFILE, array('ussr', 'usa', 'cntr')))
{
	echo GetMessage('WSL_LOADER_ERROR_FILES');
}
else
{
	$ZIPFILE = 'zip_'.$CSVFILE.'.csv';
	$CSVFILE = 'loc_'.$CSVFILE.'.csv';

	if ($STEP == 1 && $bLoadLoc != 'Y') 
	{
		if ($bLoadZip == 'Y') $STEP = 2;
		else $STEP = 3;
	}

	switch($STEP)
	{
		case 0:
			echo GetMessage('WSL_LOADER_LOADING');
			echo "<script>Run(1)</script>";
		break;

		case 1:
			$file_url = DLPATH.$CSVFILE;
			
			$data = QueryGetData(
				DLSERVER, 
				DLPORT,
				$file_url,
				'',
				$error_number = 0,
				$error_text = "",
				DLMETHOD
			);
			
			if (strlen($data) > 0)
			{
				$data = $APPLICATION->ConvertCharset($data, 'windows-1251', LANG_CHARSET);
			
				$fp = fopen('../upload/'.$CSVFILE, 'w');
				fwrite($fp, $data);
				fclose($fp);

				$sizetext = '';
				$a = array("b", "Kb", "Mb", "Gb");
				$pos = 0;
				$size = strlen($data);
				while($size>=1024) {$size /= 1024; $pos++;}
				$sizetext = round($size,2)." ".$a[$pos];
				
				echo GetMessage('WSL_LOADER_FILE_LOADED').' '.$CSVFILE.' ('.$sizetext.')';
				echo '<script>Run('.($bLoadZip == "Y" ? 2 : 3).')</script>';
			}
			else
			{
				echo GetMessage('WSL_LOADER_FILE_ERROR').' '.$CSVFILE;
				echo '<script>RunError()</script>';
			}
		
		break;
		
		case 2:
			$file_url = DLPATH.$ZIPFILE;
			
			$data = QueryGetData(
				DLSERVER, 
				DLPORT,
				$file_url,
				'',
				$error_number = 0,
				$error_text = "",
				DLMETHOD
			);
			
			if (strlen($data) > 0)
			{
				$data = $APPLICATION->ConvertCharset($data, 'windows-1251', LANG_CHARSET);
			
				$fp = fopen('../upload/'.$ZIPFILE, 'w');
				fwrite($fp, $data);
				fclose($fp);

				$sizetext = '';
				$a = array("b", "Kb", "Mb", "Gb");
				$pos = 0;
				$size = strlen($data);
				while($size>=1024) {$size /= 1024; $pos++;}
				$sizetext = round($size,2)." ".$a[$pos];				
				
				echo GetMessage('WSL_LOADER_FILE_LOADED').' '.$ZIPFILE.' ('.$sizetext.')';
				echo '<script>Run(3)</script>';		
			}
			else
			{
				echo GetMessage('WSL_LOADER_FILE_ERROR').' '.$ZIPFILE;
				echo '<script>RunError()</script>';
			}
			
		break;

		case 3:
			echo GetMessage('WSL_LOADER_ALL_LOADED');
			echo '<script>EnableButton();</script>';
		break;
	}
}

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
?>