<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");
if(!$USER->CanDoOperation('fileman_view_file_structure'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
if(CModule::IncludeModule("compression"))
	CCompress::Disable2048Spaces();

function Set($site,$path,$view,$sort,$sort_order)
{
	$resultString = $site.';'.$path.';'.$view.';'.$sort.';'.$sort_order;
	CUserOptions::SetOption("fileman", "file_dialog_config", addslashes($resultString));
}

	
if(isset($_GET['mode']) && $_GET['mode'] == 'set')
	Set($_GET['site'],$APPLICATION->UnJSEscape($_GET['path']),$_GET['view'],$_GET['sort'],$_GET['sort_order']);
?>