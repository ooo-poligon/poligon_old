<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

$cur_page = $GLOBALS["APPLICATION"]->GetCurPage();
if(defined("AUTH_404"))
{
	$arResult["AUTH_URL"] = SITE_DIR."auth.php";	
}
else 
{
	$arResult["AUTH_URL"] = $APPLICATION->GetCurPageParam("change_password=yes", array(
		"login",
	    "logout",
	    "register",
	    "forgot_password",
	    "change_password"
	));
}

$arResult["BACKURL"] = $APPLICATION->GetCurPageParam("", array(
	"login",
	"logout",
	"register",
	"forgot_password",
	"change_password"
));

$arResult["AUTH_AUTH_URL"] = $APPLICATION->GetCurPageParam("login=yes", array(
	"login",
	"logout",
	"register",
	"forgot_password",
	"change_password"
));

foreach ($arResult as $key => $value)
{
	if (!is_array($value)) $arResult[$key] = htmlspecialchars($value);
}

$arRequestParams = array(
	"USER_CHECKWORD",
	"USER_PASSWORD",
	"USER_CONFIRM_PASSWORD",
);

foreach ($arRequestParams as $param)
{
	$arResult[$param] = strlen($_REQUEST[$param]) > 0 ? $_REQUEST[$param] : "";
	$arResult[$param] = htmlspecialchars($arResult[$param]);
}

$arResult["LAST_LOGIN"] = htmlspecialchars($_COOKIE[COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_LOGIN"]);

$this->IncludeComponentTemplate();
?>
