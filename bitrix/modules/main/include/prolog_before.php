<?if(!isset($USER)) {global $USER;}if(!isset($APPLICATION)) {global $APPLICATION;}if(!isset($DB)) {global $DB;}require_once(dirname(__FILE__)."/../include.php");if(COption::GetOptionString("main", "buffer_content", "Y")=="Y" && (!defined("BX_BUFFER_USED") || BX_BUFFER_USED!==true)){	ob_start(Array(&$APPLICATION, "EndBufferContent"));	$APPLICATION->buffered = true;	define("BX_BUFFER_USED", true);	register_shutdown_function(create_function('', 'while(@ob_end_flush());'));
}
$db_events = GetModuleEvents("main", "OnProlog");
while($arEvent = $db_events->Fetch())
	ExecuteModuleEvent($arEvent);
?>