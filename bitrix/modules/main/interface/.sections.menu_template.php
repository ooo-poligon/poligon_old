<?
global $MESS;
IncludeModuleLangFile($GLOBALS["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/.sections.menu_template.php");

global $SELECTED_SECTION;

$sMenuProlog = 
	'<select class="titlemenu" onchange="if(this[this.selectedIndex].value!=\'\') window.location=this[this.selectedIndex].value;">'.
	'<option value="">'.GetMessage("select_sections_menu").'</option>';
if($PARAMS["SEPARATOR"]=="Y")
{
	$sMenuBody = 
		'<option value="'.$PARAMS["INDEX_PAGE"].'"'.($PARAMS["SECTION_ID"] == $SELECTED_SECTION? ' class="titlemenuselected" selected':'').'>'.$TEXT.'</option>';
}
else
	$sMenuBody = "";

$sMenuEpilog = 
	'</select>';
?>