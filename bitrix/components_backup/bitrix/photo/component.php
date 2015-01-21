<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if($arParams["USE_FILTER"]=="Y")
{
	if(strlen($arParams["FILTER_NAME"])<=0 || !ereg("^[A-Za-z_][A-Za-z01-9_]*$", $arParams["FILTER_NAME"]))
		$arParams["FILTER_NAME"] = "arrFilter";
}
else
	$arParams["FILTER_NAME"] = "";

$arDefaultUrlTemplates404 = array(
	"sections_top" => "",
	"section" => "#SECTION_ID#/",
	"detail" => "#SECTION_ID#/#ELEMENT_ID#/",
);

$arDefaultVariableAliases404 = Array(
	"sections_top"=>array(),
	"section"=>array("SECTION_ID" => "SECTION_ID","SECTION_CODE" => "SECTION_CODE"),
	"detail"=>array("ELEMENT_ID"=>"ELEMENT_ID","ELEMENT_CODE"=>"ELEMENT_CODE"),
);

$arComponentVariables = Array(
	"SECTION_ID","SECTION_CODE",
	"ELEMENT_ID","ELEMENT_CODE",
);

$arDefaultVariableAliases = Array(
	"SECTION_ID" => "SECTION_ID",
	"ELEMENT_ID" => "ELEMENT_ID",
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
		$componentPage = "sections_top";

	CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);
	$arResult = array(
			"FOLDER" => $arParams["SEF_FOLDER"],
			"URL_TEMPLATES" => $arUrlTemplates,
			"VARIABLES" => $arVariables,
			"ALIASES" => $arVariableAliases,
	);
	$this->IncludeComponentTemplate($componentPage);
}
else
{
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);
	CComponentEngine::InitComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);

	$componentPage = "";

	if(isset($arVariables["ELEMENT_ID"]) && intval($arVariables["ELEMENT_ID"]) > 0)
		$componentPage = "detail";
	elseif(isset($arVariables["ELEMENT_CODE"]) && strlen($arVariables["ELEMENT_CODE"]) > 0)
		$componentPage = "detail";
	elseif(isset($arVariables["SECTION_ID"]) && intval($arVariables["SECTION_ID"]) > 0)
		$componentPage = "section";
	elseif(isset($arVariables["SECTION_CODE"]) && strlen($arVariables["SECTION_CODE"]) > 0)
		$componentPage = "section";
	else
		$componentPage = "sections_top";

	$arResult = array(
			"FOLDER" => "",
			"URL_TEMPLATES" => Array(
				"section" => htmlspecialchars($APPLICATION->GetCurPage())."?".$arVariableAliases["SECTION_ID"]."=#SECTION_ID#",
				"detail" => htmlspecialchars($APPLICATION->GetCurPage())."?".$arVariableAliases["SECTION_ID"]."=#SECTION_ID#"."&".$arVariableAliases["ELEMENT_ID"]."=#ELEMENT_ID#",
			),
			"VARIABLES" => $arVariables,
			"ALIASES" => $arVariableAliases
		);
	$this->IncludeComponentTemplate($componentPage);
}
?>