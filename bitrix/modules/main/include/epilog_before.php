<?
/*
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2007 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
*/

/* Draw edit menu for whole content */
global $BX_GLOBAL_AREA_EDIT_ICON; //set in prolog_after.php
if($BX_GLOBAL_AREA_EDIT_ICON == true)
{
	IncludeModuleLangFile(__FILE__);

	if(isset($_SERVER["REAL_FILE_PATH"]) && $_SERVER["REAL_FILE_PATH"] != "")
		$currentFilePath = $_SERVER["REAL_FILE_PATH"];
	else
		$currentFilePath = $GLOBALS['APPLICATION']->GetCurPage();

	$encCurrentFilePath = urlencode($currentFilePath);
	$encUri = urlencode($_SERVER["REQUEST_URI"]);

	$arIcons = array(
		array(
			"TITLE"=>GetMessage("main_epilog_before_menu_edit"),
			"ALT"=>GetMessage("main_epilog_before_menu_edit_title"),
			"ICON"=>"panel-edit-visual",
			"URL"=> 'javascript:'.$GLOBALS['APPLICATION']->GetPopupLink(Array(
				"URL"=> "/bitrix/admin/public_file_edit.php?lang=".LANGUAGE_ID."&path=".$encCurrentFilePath."&site=".SITE_ID."&back_url=".$encUri, 
				"PARAMS"=>array("width"=>770, "height"=>570, "resize"=>false))
			),
			"DEFAULT"=>true,
		),
		array(
			"TITLE"=>GetMessage("main_epilog_before_menu_edit_html"),
			"ALT"=>GetMessage("main_epilog_before_menu_edit_html_title"),
			"ICON"=>"panel-edit-text",
			"URL"=>'javascript:'.$GLOBALS['APPLICATION']->GetPopupLink(Array(
				"URL"=>"/bitrix/admin/public_file_edit.php?lang=".LANGUAGE_ID."&noeditor=Y&path=".$encCurrentFilePath."&site=".SITE_ID."&back_url=".$encUri, 
				"PARAMS"=>array("width"=>770, "height"=>570, "resize"=>true))
			),
		),
		array('SEPARATOR'=>true),
		array(
			"TITLE"=>GetMessage("main_epilog_before_menu_prop"),
			"ALT"=>GetMessage("main_epilog_before_menu_prop_title"),
			"ICON"=>"panel-file-props",
			"URL"=> 'javascript:'.$APPLICATION->GetPopupLink(Array(
				"URL"=>"/bitrix/admin/public_file_property.php?lang=".LANGUAGE_ID."&site=".SITE_ID."&path=".$encCurrentFilePath."&back_url=".$encUri,
				"PARAMS" => Array("min_width"=>450, "min_height" => 250, "resize"=>true))
			),
		),
	);
	echo $GLOBALS['APPLICATION']->IncludeStringAfter($arIcons, array('TOOLTIP'=>GetMessage("main_epilog_before_menu_title"), 'ICON'=>'edit-icon'));
}

$GLOBALS["APPLICATION"]->ShowSpreadCookieHTML();

if (isset($_GET['bx_template_preview_mode']) && $_GET['bx_template_preview_mode'] == 'Y' && $USER->CanDoOperation('edit_other_settings'))
	@include_once($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/tmp/templates/__bx_preview/footer.php");
else
	include_once($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".SITE_TEMPLATE_ID."/footer.php");
?>