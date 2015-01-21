<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = array(
	"NAME" => GetMessage("PM_READ"), 
	"DESCRIPTION" => GetMessage("PM_READ_DESCRIPTION"), 
	"ICON" => "/images/icon.gif",
	"PATH" => array(
		"ID" => "communication", 
		"CHILD" => array(
			"ID" => "forum",
			"NAME" => GetMessage("FORUM"),
			"CHILD" => array(
				"ID" => "pm",
				"NAME" => GetMessage("PM"),
			)
		)
	),
);
?>