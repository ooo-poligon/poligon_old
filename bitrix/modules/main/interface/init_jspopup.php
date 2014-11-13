<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

function JSPopupRedirectHandler()
{
	global $DB, $USER, $APPLICATION;
	ob_end_clean();
	echo '<script type="text/javascript">
top.jsPopup.CloseDialog();
var new_href = top.location.href;
var hashpos = new_href.indexOf(\'#\');
if (hashpos != -1)
	new_href = new_href.substr(0, hashpos);
	
new_href += (new_href.indexOf(\'?\') == -1 ? \'?\' : \'&\') + \'clear_cache=Y\';
top.location.href = new_href;
		
/*
if (top.location.hash == \'\' || top.location.hash == \'#\')
	top.location = top.location.href;
else
	top.location.reload();
*/
</script>';

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

AddEventHandler('main', 'OnBeforeLocalRedirect', 'JSPopupRedirectHandler');
?>