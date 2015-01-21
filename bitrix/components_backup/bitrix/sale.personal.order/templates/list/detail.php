<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->IncludeComponent(
	"bitrix:sale.personal.order.detail",
	"",
	array(
		"PATH_TO_LIST" => $arResult["PATH_TO_LIST"],
		"PATH_TO_CANCEL" => $arResult["PATH_TO_CANCEL"],
		"SET_TITLE" =>$arParams["SET_TITLE"],
		"PATH_TO_PAYMENT" => $arParams["PATH_TO_PAYMENT"],
		"ID" => $arResult["VARIABLES"]["ID"],
	),
	$component
);
?>
