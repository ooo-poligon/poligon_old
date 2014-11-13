<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = array(
	"NAME" => GetMessage("FORUM_PROFILE"), 
	"DESCRIPTION" => GetMessage("FORUM_PROFILE_DESCRIPTION"), 
	"ICON" => "/images/icon.gif",
	"PATH" => array(
		"ID" => "communication", 
		"CHILD" => array(
			"ID" => "forum",
			"NAME" => GetMEssage("FORUM")
		)
	),
);
?>