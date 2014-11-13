<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("forum"))
	return;

$arTemplateParameters = array(
    "TMPLT_SHOW_ADDITIONAL_MARKER" => array(
		"NAME" => GetMessage("F_TMPLT_SHOW_ADDITIONAL_MARKER"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
	),
	"TMPLT_SHOW_TOP" => array(
		"NAME" => GetMessage("F_TMPLT_SHOW_TOP"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
		"ROWS" => "5",
	),
	"TMPLT_SHOW_BOTTOM" => array(
		"NAME" => GetMessage("F_TMPLT_SHOW_BOTTOM"),
		"TYPE" => "LIST",
		"VALUES" => array("SET_BE_READ" => GetMessage("F_SET_BE_READ"),),
		"DEFAULT" => "SET_BE_READ",
		"ADDITIONAL_VALUES" => "Y",
	),
	"WORD_WRAP_CUT" => CForumParameters::GetWordWrapCut(false, false),
);
?>