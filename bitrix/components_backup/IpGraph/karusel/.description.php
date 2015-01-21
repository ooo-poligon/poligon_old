<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("ELEMENT_TEMPLATE_NAME"),
	"DESCRIPTION" => GetMessage("ELEMENT_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/cat_detail.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 40,
	"PATH" => array(
		"ID" => "IpGraph",
		"CHILD" => array(
			"ID" => "Carusel",
			"NAME" => GetMessage("DESC_CATALOG"),
			"SORT" => 30,
		),
	),
);

?>
