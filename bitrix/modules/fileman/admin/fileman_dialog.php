<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");
if (!($USER->CanDoOperation('fileman_admin_files') || $USER->CanDoOperation('fileman_edit_existent_files')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$APPLICATION->SetAdditionalCSS("/bitrix/modules/fileman/fileman_admin.css");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
IncludeModuleLangFile(__FILE__);

define("FROMDIALOGS", true);
$fname = "";
$no_prolog = false;
switch($dtype)
{
case "addurl":
	$fname = "fileman_addurl.php";
	$title = GetMessage("FILEMAN_DIALOGS_HREF");
	break;
case "imgprop":
	$fname = "fileman_imgprop.php";
	$title = GetMessage("FILEMAN_DIALOGS_PIC_PROP");
	break;
case "menuselect":
	$fname = "fileman_menuselect.php";
	$title = GetMessage("FILEMAN_DIALOGS_PIC_MENU_ITEM");
	break;
case "saveas":
	$fname = "fileman_saveas.php";
	$title = GetMessage("FILEMAN_DIALOGS_SAVE_AS");
	break;
case "openfile":
	$fname = "fileman_openfile.php";
	$title = GetMessage("FILEMAN_T_CHOOSE_FILE");
	break;
case "tableprop":
	$fname = "fileman_tableprop.php";
	$title = GetMessage("FILEMAN_DIALOGS_TABLE_PROP");
	break;
case "title":
	$fname = "fileman_title.php";
	$title = GetMessage("FILEMAN_DIALOGS_PAGE_TITLE");
	break;
case "properties":
	$fname = "fileman_properties.php";
	$title = GetMessage("FILEMAN_DIALOGS_PAGE_PROPS");
	break;
case "mkadvurl":
	$fname = "fileman_mkadvurl.php";
	$title = GetMessage("FILEMAN_DIALOGS_EXT_LINK");
	break;
case "tableinsert":
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/dialogs/tableprop.htm");
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
	die();
case "templatesel":
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/dialogs/templatesel.php");
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
	die();
case "colorpick":
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/dialogs/colorpick.htm");
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
	die();
case "browse_files":
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/dialogs/browse_files.php");
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
	die();
case "list_props":
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/dialogs/list_props.php");
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
	die();
case "insimg":
	$fname = "fileman_insimg.php";
	$title = GetMessage("FILEMAN_INSIMG_TITLE");
	break;
case "insimg_upload":
	$fname = "fileman_insimg_upload.php";
	$title = GetMessage("FILEMAN_UPLOAD_IMG_TITLE");
	break;
default:
	$fname = "";
	$title = "";
}
?>
<HTML>
<HEAD>
<STYLE TYPE="text/css">
BODY   {margin-left:10; font-family:Arial; font-size:12px; background:menu}
BUTTON {width:5em}
TABLE  {font-family:Arial; font-size:12px}
P      {text-align:center}
<?
if (strlen($APPLICATION->GetAdditionalCSS())>0)
{
	require($_SERVER["DOCUMENT_ROOT"].$APPLICATION->GetAdditionalCSS());
}
?>
</STYLE>
<script>
function KeyPress()
{
	if(window.event.keyCode == 27)
		window.close();
}
</script>
<title><?echo htmlspecialchars($title)?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?echo LANG_CHARSET?>">
</HEAD>
<BODY onKeyPress="KeyPress()" id="bod">
<?
if($fname=="")
	echo GetMessage("FILEMAN_DIALOGS_BAD_TYPE");
else
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/dialogs/".$fname);
}
?>
</BODY>
</HTML>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>
