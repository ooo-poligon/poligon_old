<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if($arParams["USE_FILTER"]=="Y")
{
	if(strlen($arParams["FILTER_NAME"])<=0 || !ereg("^[A-Za-z_][A-Za-z01-9_]*$", $arParams["FILTER_NAME"]))
		$arParams["FILTER_NAME"] = "arrFilter";
}
else
	$arParams["FILTER_NAME"] = "";

$arParams["USE_CATEGORIES"]=$arParams["USE_CATEGORIES"]=="Y";
if($arParams["USE_CATEGORIES"])
{
	if(!is_array($arParams["CATEGORY_IBLOCK"]))
		$arParams["CATEGORY_IBLOCK"] = array();
	$ar = array();
	foreach($arParams["CATEGORY_IBLOCK"] as $key=>$value)
	{
		$value=intval($value);
		if($value>0)
			$ar[$value]=true;
	}
	$arParams["CATEGORY_IBLOCK"] = array_keys($ar);
}
$arParams["CATEGORY_CODE"]=trim($arParams["CATEGORY_CODE"]);
if(strlen($arParams["CATEGORY_CODE"])<=0)
	$arParams["CATEGORY_CODE"]="CATEGORY";
$arParams["CATEGORY_ITEMS_COUNT"]=intval($arParams["CATEGORY_ITEMS_COUNT"]);
if($arParams["CATEGORY_ITEMS_COUNT"]<=0)
	$arParams["CATEGORY_ITEMS_COUNT"]=5;

if(!is_array($arParams["CATEGORY_IBLOCK"]))
	$arParams["CATEGORY_IBLOCK"] = array();
foreach($arParams["CATEGORY_IBLOCK"] as $iblock_id)
	if($arParams["CATEGORY_THEME_".$iblock_id]!="photo")
		$arParams["CATEGORY_THEME_".$iblock_id]="list";

$arDefaultUrlTemplates404 = array(
	"news" => "",
	"search" => "search/",
	"rss" => "rss/",
	"rss_section" => "#SECTION_ID#/rss/",
	"detail" => "#ELEMENT_ID#/",
	"section" => "",
);

$arDefaultVariableAliases404 = Array(
	"news"=>array(),
	"section"=>array(),
	"detail"=>array(),
	"search"=>array(),
	"rss"=>array(),
	"rss_section"=>array(),
);

$arComponentVariables = Array(
	"SECTION_ID",
	"ELEMENT_ID",
	"q",
	"tags",
	"rss",
);

$arDefaultVariableAliases = Array(
	"SECTION_ID"=>"SECTION_ID",
	"ELEMENT_ID"=>"ELEMENT_ID",
	"q"=>"q",
	"tags"=>"tags",
	"rss"=>"rss",
);

if($arParams["SEF_MODE"] == "Y")
{
	$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);

	$componentPage = CComponentEngine::ParseComponentPath(
		$arParams["SEF_FOLDER"],
		$arUrlTemplates,
		$arVariables
	);

	if(!$componentPage)
		$componentPage = "news";

	CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);
	$arResult = array(
			"FOLDER" => $arParams["SEF_FOLDER"],
			"URL_TEMPLATES" => $arUrlTemplates,
			"VARIABLES" => $arVariables,
			"ALIASES" => $arVariableAliases
		);
}
else
{
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);
	CComponentEngine::InitComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);

	$componentPage = "";

	if(isset($arVariables["ELEMENT_ID"]) && intval($arVariables["ELEMENT_ID"]) > 0)
		$componentPage = "detail";
	elseif(isset($arVariables["SECTION_ID"]) && intval($arVariables["SECTION_ID"]) > 0)
	{
		if(isset($arVariables["rss"]) && $arVariables["rss"]=="y")
			$componentPage = "rss_section";
		else
			$componentPage = "section";
	}
	elseif(isset($arVariables["q"]) && strlen(trim($arVariables["q"])) > 0)
		$componentPage = "search";
	elseif(isset($arVariables["tags"]) && strlen(trim($arVariables["tags"])) > 0)
		$componentPage = "search";
	elseif(isset($arVariables["rss"]) && $arVariables["rss"]=="y")
		$componentPage = "rss";
	else
		$componentPage = "news";

	$arResult = array(
			"FOLDER" => "",
			"URL_TEMPLATES" => Array(
				"news" => htmlspecialchars($APPLICATION->GetCurPage()),
				"section" => htmlspecialchars($APPLICATION->GetCurPage()."?".$arVariableAliases["SECTION_ID"]."=#SECTION_ID#"),
				"detail" => htmlspecialchars($APPLICATION->GetCurPage()."?".$arVariableAliases["ELEMENT_ID"]."=#ELEMENT_ID#"),
				"search" => htmlspecialchars($APPLICATION->GetCurPage()),
				"rss" => htmlspecialchars($APPLICATION->GetCurPage()."?rss=y"),
				"rss_section" => htmlspecialchars($APPLICATION->GetCurPage()."?".$arVariableAliases["SECTION_ID"]."=#SECTION_ID#&rss=y"),
			),
			"VARIABLES" => $arVariables,
			"ALIASES" => $arVariableAliases
		);
}

if($componentPage=="search")
{
	include_once("newstools.php");
	global $BX_NEWS_DETAIL_URL;
	$BX_NEWS_DETAIL_URL = $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["detail"];
	AddEventHandler("search", "OnSearchGetURL", array("CNewsTools","OnSearchGetURL"), 200);
}
$this->IncludeComponentTemplate($componentPage);

?>