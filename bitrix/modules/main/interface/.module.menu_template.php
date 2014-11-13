<?
global $MESS;
IncludeModuleLangFile($GLOBALS["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/.module.menu_template.php");

global $SELECTED_SECTION, $bShowModuleMenu;

$sMenuProlog = 
	'<select class="titlemenu" name="menulinks" onchange="if(this[this.selectedIndex].value!=\'\') window.location=this[this.selectedIndex].value;">'.
	'<option value="">'.GetMessage("select_section_menu").'</option>';
if($PARAMS["SEPARATOR"]=="Y")
{
	$bShowModuleMenu = ($PARAMS["SECTION_ID"] == $SELECTED_SECTION);
	$sMenuBody = "";
}
elseif($bShowModuleMenu)
{
	$sMenuBody = 
		'<option value="'.htmlspecialcharsex($LINK).'"'.($SELECTED? ' class="titlemenuselected" selected':'').'>'.$TEXT.'</option>';
}
else
{
	$sMenuBody = "";
}
$sMenuEpilog = 
	'</select>';
?>