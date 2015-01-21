<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arYesNo = Array(
	"Y" => GetMessage("SPOD_DESC_YES"),
	"N" => GetMessage("SPOD_DESC_NO"),
);

$arComponentParameters = Array(
	"PARAMETERS" => Array(
		"PATH_TO_LIST" => Array(
			"NAME" => GetMessage("SPOD_PATH_TO_LIST"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"PATH_TO_CANCEL" => Array(
			"NAME" => GetMessage("SPOD_PATH_TO_CANCEL"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"PATH_TO_PAYMENT" => Array(
			"NAME" => GetMessage("SPOD_PATH_TO_PAYMENT"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "payment.php",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"ID" => Array(
			"NAME" => GetMessage("SPOD_ID"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "={\$ID}",
			"COLS" => 25,
		),

		"SET_TITLE" => Array(),

	)
);
?>