<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

Header('Content-Type: text/html; charset='.LANG_CHARSET);

ob_start();

if(strlen($APPLICATION->GetTitle())<=0)
	$APPLICATION->SetTitle(GetMessage("MAIN_PROLOG_ADMIN_TITLE"));

$obJSPopup = new CJSPopup($APPLICATION->GetTitle(false, true));

$adminPage = new CAdminPage();
echo $adminPage->ShowPopupCSS();
echo $adminPage->ShowScript();

$obJSPopup->ShowTitlebar();
?>
<div id="bx_admin_form">
