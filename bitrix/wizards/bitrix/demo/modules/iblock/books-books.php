<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

//Library
include_once(dirname(__FILE__)."/iblock_tools.php");

//Parameters
if(!is_array($arParams)) $arParams = array();
if(strlen($arParams["site_id"]) <= 0)
	$arParams["site_id"] = "s1";

$_SESSION["DEMO_IBLOCK_BOOKS"] = CIBlockCMLImport::GetIBlockByXML_ID("books-books");
//Import XML
if($_SESSION["DEMO_IBLOCK_BOOKS"] === false)
	DEMO_IBlock_ImportXML("080_books_books-books_ru.xml", $arParams["site_id"], false);
?>