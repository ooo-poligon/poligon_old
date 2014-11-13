<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("forum"))
	return;
$arComponentParameters = Array(
	"GROUPS" => array(
		"URL_TEMPLATES" => array(
			"NAME" => GetMessage("F_URL_TEMPLATES"),
		),
	),
	
	"PARAMETERS" => Array(
		"FID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_FID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["FID"]}'),
		"TID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_TID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["TID"]}'),
	
		"URL_TEMPLATES_INDEX" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_INDEX_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "index.php"),
		"URL_TEMPLATES_LIST" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_LIST_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "list.php?FID=#FID#"),
		"URL_TEMPLATES_READ" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_READ_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "read.php?FID=#FID#&TID=#TID#&MID=#MID#"),
		"URL_TEMPLATES_PROFILE_VIEW" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PROFILE_VIEW_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "profile_view.php?UID=#UID#"),
		
		"MESSAGES_PER_PAGE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_MESSAGES_PER_PAGE"),
			"TYPE" => "STRING",
			"DEFAULT" => COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10")),
		"PATH_TO_SMILE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_DEFAULT_PATH_TO_SMILE"),
			"TYPE" => "STRING",
			"DEFAULT" => "/bitrix/images/forum/smile/"),
		"PAGE_NAVIGATION_TEMPLATE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_PAGE_NAVIGATION_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => ""),
		"SET_NAVIGATION" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_SET_NAVIGATION"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),
		"WORD_LENGTH" => CForumParameters::GetWordLength(),
		
		"SET_TITLE" => Array(),
	)
);
?>
