<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("forum"))
	return;
$arComponentParameters = array(
	"PARAMETERS" => array(
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
		"MID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_MID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["MID"]}'),
		"PAGE_NAME" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_PAGE_NAME"),
			"TYPE" => "STRING",
			"DEFAULT" => "message"),
		"MESSAGE_TYPE" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_MESSAGE_TYPE"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["MESSAGE_TYPE"]}'),

		"URL_TEMPLATES_MESSAGE" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_MESSAGE_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "message.php?FID=#FID#&TID=#TID#&MID=#MID#"),
		"URL_TEMPLATES_LIST" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_LIST_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "list.php?FID=#FID#"),

		"PATH_TO_SMILE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_DEFAULT_PATH_TO_SMILE"),
			"TYPE" => "STRING",
			"DEFAULT" => "/bitrix/images/forum/smile/"),
		"PATH_TO_ICON" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_DEFAULT_PATH_TO_ICON"),
			"TYPE" => "STRING",
			"DEFAULT" => "/bitrix/images/forum/icon/"),
	    "SMILE_TABLE_COLS" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
	        "NAME" => GetMessage("F_SMILE_TABLE_COLS"),
			"TYPE" => "STRING",
			"DEFAULT" => "3"),
		"AJAX_TYPE" => CForumParameters::GetAjaxType(),

		"CACHE_TIME" => Array(),
	)
);
?>
