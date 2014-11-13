<?
define("NEED_AUTH", true);
define("ADMIN_SECTION", true);

require_once(dirname(__FILE__)."/../include.php");
if(!headers_sent())
	header("Content-type: text/html; charset=".LANG_CHARSET);

if ($_REQUEST['bxpublic'] == 'Y')
{
	define('BX_PUBLIC_MODE', 1);
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST')
		require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/init_jspopup.php");
}

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/admin_tools.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/init_admin.php");
?>
