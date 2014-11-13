<?
global $arADMIN_MAIN_MENU_LINKS, $SELECTED_SECTION;

$sMenuTemplate = BX_ROOT."/modules/main/interface/.module.menu_template.php";
if($SELECTED_SECTION <> "")
	$aMenuLinks = $arADMIN_MAIN_MENU_LINKS;
else
	$aMenuLinks = array();
?>