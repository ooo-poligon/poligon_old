<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(!CModule::IncludeModule('support'))
	return;

__IncludeLang(GetLangFileName(dirname(__FILE__)."/lang/", basename(__FILE__)));

$bReWriteAdditionalFiles = ($arParams["public_rewrite"] == "Y");

$pathToService = str_replace("\\", "/", dirname(__FILE__));

//Public files
CopyDirFiles(
	$pathToService."/public/".LANGUAGE_ID, 
	$_SERVER["DOCUMENT_ROOT"]."/communication/support",
	$rewrite = false,
	$recursive = true
);

//Left menu
DemoSiteUtil::AddMenuItem("/communication/.left.menu.php", Array(
	"Техподдержка",
	"/communication/support/",
	Array(),
	Array(),
	"",
));

$dbResult = CGroup::GetList($by, $order, Array("STRING_ID" => "REGISTERED_USERS"));
if ($arGroup = $dbResult->Fetch())
	$APPLICATION->SetGroupRight("support", $arGroup["ID"], "R");

//Communication section
include(dirname(__FILE__)."/../communication/install.php");
?>