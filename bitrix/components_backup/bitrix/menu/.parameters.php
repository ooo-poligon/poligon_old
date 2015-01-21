<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arMenu = GetMenuTypes("", "left=".GetMessage("FILEMAN_OPTION_LEFT_MENU_NAME").",top=".GetMessage("FILEMAN_OPTION_TOP_MENU_NAME"));

$arComponentParameters = array(
	"PARAMETERS" => array(

		"ROOT_MENU_TYPE" => Array(
			"NAME"=>GetMessage("MAIN_MENU_TYPE_NAME"),
			"TYPE" => "LIST",
			"DEFAULT"=>'left',
			"VALUES" => $arMenu, 
			"ADDITIONAL_VALUES"	=> "Y",
			"DEFAULT"=>'left',
			"PARENT" => "BASE",
			"COLS" => 45
		),

		"MAX_LEVEL" => Array(
			"NAME"=>GetMessage("MAX_LEVEL_NAME"),
			"TYPE" => "LIST",
			"DEFAULT"=>'1',
			"PARENT" => "ADDITIONAL_SETTINGS",
			"VALUES" => Array(
				1 => "1",
				2 => "2",
				3 => "3",
				4 => "4",
			),
			"ADDITIONAL_VALUES"	=> "N",
		),

		"CHILD_MENU_TYPE" => Array(
			"NAME"=>GetMessage("CHILD_MENU_TYPE_NAME"),
			"TYPE" => "LIST",
			"DEFAULT"=>'left',
			"VALUES" => $arMenu, 
			"ADDITIONAL_VALUES"	=> "Y",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"DEFAULT"=>'left',
			"COLS" => 45
		),

		"USE_EXT" => Array(
			"NAME"=>GetMessage("USE_EXT_NAME"),
			"TYPE" => "CHECKBOX",
			"DEFAULT"=>'N',
			"PARENT" => "ADDITIONAL_SETTINGS",
		),


	)
);
?>
