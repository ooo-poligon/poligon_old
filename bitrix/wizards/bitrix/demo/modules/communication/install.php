<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

$pathToService = str_replace("\\", "/", dirname(__FILE__));

//Public files
CopyDirFiles(
	$pathToService."/".LANGUAGE_ID, 
	$_SERVER["DOCUMENT_ROOT"]."/communication/",
	$rewrite = false,
	$recursive = true
);

//Top menu
DemoSiteUtil::AddMenuItem("/.top.menu.php", Array(
	"Общение",
	"/communication/",
	Array(),
	Array(),
	"",
));

?>