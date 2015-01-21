<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arComponentParameters = Array(
	"GROUPS" => array(
		"URL_TEMPLATES" => array(
			"NAME" => GetMessage("F_URL_TEMPLATES"),
		),
	),
	"PARAMETERS" => Array(
	
		"SET_TITLE" => Array(),
		
		"CACHE_TIME" => Array(),
		
		"SET_NAVIGATION" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_SET_NAVIGATION"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"DEFAULT" => "Y"
		),
		
		"URL_TEMPLATES_PM_LIST" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PM_LIST_TEMPLATE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "pm_list.php?FID=#FID#",
			"COLS" => 25
		),

		"URL_TEMPLATES_PM_FOLDER" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PM_FOLDER_TEMPLATE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "pm_folder.php",
			"COLS" => 25
		),
	)
);
?>
