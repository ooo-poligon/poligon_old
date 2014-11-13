<?
$file = $_SERVER["DOCUMENT_ROOT"].BX_ROOT."/license_key.php";
if(file_exists($file)) 
	include($file);

if(strlen($LICENSE_KEY)<=0 || $LICENSE_KEY=="DEMO") 
	$lic = "DEMO"; 
else 
	$lic = md5("BITRIX".$LICENSE_KEY."LICENCE");

header("B-Powered-By: Bitrix SM (".$lic.")");
?>