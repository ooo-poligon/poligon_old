<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

//Change site name
$obSite = new CSite();
$obSite->Update("s1", Array("NAME" => COption::GetOptionString("main", "site_name", "Сайт по умолчанию")));

//Edit profile task
$editProfileTask = false;
$dbResult = CTask::GetList(Array(), Array("NAME" => "main_change_profile"));
if ($arTask = $dbResult->Fetch())
	$editProfileTask = $arTask["ID"];

//Registered users group
$dbResult = CGroup::GetList($by, $order, Array("STRING_ID" => "REGISTERED_USERS"));
if ($dbResult->Fetch())
	return;

$group = new CGroup;
$arFields = Array(
	"ACTIVE" => "Y",
	"C_SORT" => 3,
	"NAME" => "Зарегистрированные пользователи",
	"STRING_ID" => "REGISTERED_USERS",
);

$groupID = $group->Add($arFields);
if ($groupID > 0)
{
	COption::SetOptionString("main", "new_user_registration_def_group", $groupID);
	if ($editProfileTask)
		CGroup::SetTasks($groupID, Array($editProfileTask), true);
}

//Control panel users
$dbResult = CGroup::GetList($by, $order, Array("STRING_ID" => "CONTROL_PANEL_USERS"));
if (!$dbResult->Fetch())
{
	$group = new CGroup;
	$arFields = Array(
		"ACTIVE" => "Y",
		"C_SORT" => 4,
		"NAME" => "Пользователи панели управления",
		"STRING_ID" => "CONTROL_PANEL_USERS",
	);

	$groupID = $group->Add($arFields);
	if ($groupID > 0)
	{
		DemoSiteUtil::SetFilePermission(Array("s1", "/bitrix/admin"), Array($groupID => "R"));
		if ($editProfileTask)
			CGroup::SetTasks($groupID, Array($editProfileTask), true);
	}
}

//Options
COption::SetOptionString("main", "upload_dir", "upload");
COption::SetOptionString("main", "new_license7_sign", "Y");
COption::SetOptionString("main", "component_cache_on","Y");
COption::SetOptionString("main", "server_name", $_SERVER["SERVER_NAME"]);

COption::SetOptionString("main", "save_original_file_name", "Y");
COption::SetOptionString("main", "templates_visual_editor", "Y");
COption::SetOptionString("main", "header_200", "Y");
COption::SetOptionString("main", "captcha_registration", "Y");
COption::SetOptionString("main", "use_secure_password_cookies", "Y");
COption::SetOptionString("main", "new_user_registration", "Y");
COption::SetOptionString("main", "auth_comp2", "Y");

COption::SetOptionString("main", "map_top_menu_type", "top");
COption::SetOptionString("main", "map_left_menu_type", "left");

SetMenuTypes(Array("left" => "Меню раздела", "top" => "Основное меню"),"s1");
SetMenuTypes(Array("left" => "Меню раздела", "top" => "Основное меню"),"");

COption::SetOptionString("fileman", "default_edit", "html");

//Print template
$pathToService = str_replace("\\", "/", dirname(__FILE__));
CopyDirFiles(
	$wizardPath."/misc/print_template", 
	$_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/print",
	$rewrite = true,
	$recursive = true
);
$obSite = CSite::GetByID("s1");
if (!$arSite = $obSite->Fetch())
	return;

$arTemplates = Array();
$obTemplate = CSite::GetTemplateList("s1");
while($arTemplate = $obTemplate->Fetch())
	$arTemplates[]= $arTemplate;

$arTemplates[]= Array("CONDITION" => "\$_GET['print']=='Y'", "SORT" => 150, "TEMPLATE" => "print");

$obSite = new CSite();
$obSite->Update("s1", Array("TEMPLATE" => $arTemplates, "NAME" => COption::GetOptionString("main", "site_name", $arSite["NAME"])));

?>