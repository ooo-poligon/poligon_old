<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();


if (is_object($arParams["NAVIGATION_REF"]) &&  is_subclass_of($arParams["NAVIGATION_REF"], "CAllDBResult"))
{
	$arResult = Array();

	$arResult["NAVIGATION_REF"] = $arParams["NAVIGATION_REF"];
	$arResult["NAVIGATION_TITLE"] = $arParams["NAVIGATION_TITLE"];
	$arResult["SHOW_ALWAYS"] = $arParams["SHOW_ALWAYS"];

	$APPLICATION->IncludeComponentTemplate($componentName, $componentTemplate, $arResult, $arParams, $arParentComponent);
}
?>