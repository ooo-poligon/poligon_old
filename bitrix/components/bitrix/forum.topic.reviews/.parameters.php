<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum") || !CModule::IncludeModule("iblock"))
	return;
$arForum = array();
$db_res = CForumNew::GetList(array(), array());
if ($db_res && ($res = $db_res->GetNext()))
{
	do 
	{
		$arForum[intVal($res["ID"])] = $res["NAME"];
	}while ($res = $db_res->GetNext());
}

$arComponentParameters = Array(
	"PARAMETERS" => Array(
		"FORUM_ID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_FORUM_ID"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"VALUES" => $arForum),
		"ELEMENT_ID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_ELEMENT_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["ELEMENT_ID"]}'),
		"POST_FIRST_MESSAGE" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_POST_FIRST_MESSAGE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),
		"POST_FIRST_MESSAGE_TEMPLATE" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_POST_FIRST_MESSAGE_TEMPLATE"),
			"TYPE" => "STRING",
			"ROWS" => 4,
			"DEFAULT" => "#IMAGE#\n[url=#LINK#]#TITLE#[/url]\n#BODY#"),

		
		"URL_TEMPLATES_READ" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_READ_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => ""),
		"URL_TEMPLATES_DETAIL" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_DETAIL_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => ""),
			
		"MESSAGES_PER_PAGE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_MESSAGES_PER_PAGE"),
			"TYPE" => "STRING",
			"DEFAULT" => intVal(COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10"))),
		"PAGE_NAVIGATION_TEMPLATE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_PAGE_NAVIGATION_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => ""),
		"DATE_TIME_FORMAT" => CForumParameters::GetDateTimeFormat(GetMessage("F_DATE_TIME_FORMAT"), "ADDITIONAL_SETTINGS"),
		"PATH_TO_SMILE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_PATH_TO_SMILE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/bitrix/images/forum/smile/"),
		"USE_CAPTCHA" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_USE_CAPTCHA"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),
		"PREORDER" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_PREORDER"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),
		"DISPLAY_PANEL" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_DISPLAY_PANEL"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N"),

		"CACHE_TIME" => Array(),
	)
);
?>
