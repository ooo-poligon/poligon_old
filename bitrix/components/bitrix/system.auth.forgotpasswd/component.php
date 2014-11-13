<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

if(defined("AUTH_404"))
{
	$arResult["AUTH_URL"] = SITE_DIR."auth.php";	
}
else 
{
	$arResult["AUTH_URL"] = $APPLICATION->GetCurPageParam("forgot_password=yes", array(
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

$arResult["LAST_LOGIN"] = htmlspecialchars($_COOKIE[COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_LOGIN"]);
	
$this->IncludeComponentTemplate();
?>
