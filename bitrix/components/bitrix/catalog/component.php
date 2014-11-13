<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if($arParams["USE_FILTER"]=="Y")
{
	if(strlen($arParams["FILTER_NAME"])<=0 || !ereg("^[A-Za-z_][A-Za-z01-9_]*$", $arParams["FILTER_NAME"]))
		$arParams["FILTER_NAME"] = "arrFilter";
}
else
	$arParams["FILTER_NAME"] = "";

$arDefaultUrlTemplates404 = array(
	"sections" => "",
	"section" => "#SECTION_ID#/",
	"element" => "#SECTION_ID#/#ELEMENT_ID#/",
	"compare" => "compare.php?action=COMPARE",
);

$arDefaultVariableAliases404 = Array(
	"sections"=>array(),
	"section"=>array("SECTION_ID" => "SECTION_ID"),
	"element"=>array("ELEMENT_ID"=>"ELEMENT_ID"),
	"compare"=>array(),
);

$arComponentVariables = Array(
	"SECTION_ID",
	"ELEMENT_ID",
	"action",
);

$arDefaultVariableAliases = Array(
	"SECTION_ID" => "SECTION_ID",
	"ELEMENT_ID" => "ELEMENT_ID",
	"action" => "action",
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
		$componentPage = "sections";

	if($componentPage)
	{
		CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);
		$arResult = array(
				"FOLDER" => $arParams["SEF_FOLDER"],
				"URL_TEMPLATES" => $arUrlTemplates,
				"VARIABLES" => $arVariables,
				"ALIASES" => $arVariableAliases
			);
		$this->IncludeComponentTemplate($componentPage);
	}
}
else
{
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);
	CComponentEngine::InitComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);

	$componentPage = "";

	$arCompareCommands = array(
		"COMPARE",
		"DELETE_FEATURE",
		"ADD_FEATURE",
		"DELETE_FROM_COMPARE_RESULT",
		"ADD_TO_COMPARE_RESULT",
		"COMPARE_BUY",
		"COMPARE_ADD2BASKET",
	);

	if(isset($arVariables["action"]) && in_array($arVariables["action"], $arCompareCommands))
		$componentPage = "compare";
	elseif(isset($arVariables["ELEMENT_ID"]) && intval($arVariables["ELEMENT_ID"]) > 0)
		$componentPage = "element";
	elseif(isset($arVariables["SECTION_ID"]) && intval($arVariables["SECTION_ID"]) > 0)
		$componentPage = "section";
	else
		$componentPage = "sections";

	$arResult = array(
			"FOLDER" => "",
			"URL_TEMPLATES" => Array(
				"section" => htmlspecialchars($APPLICATION->GetCurPage())."?".$arVariableAliases["SECTION_ID"]."=#SECTION_ID#",
				"element" => htmlspecialchars($APPLICATION->GetCurPage())."?".$arVariableAliases["SECTION_ID"]."=#SECTION_ID#"."&".$arVariableAliases["ELEMENT_ID"]."=#ELEMENT_ID#",
				"compare" => htmlspecialchars($APPLICATION->GetCurPage())."?".$arVariableAliases["action"]."=COMPARE",
			),
			"VARIABLES" => $arVariables,
			"ALIASES" => $arVariableAliases
		);
	$this->IncludeComponentTemplate($componentPage);
}
?>