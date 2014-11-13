<?
if(!isset($USER))		{global $USER;}
if(!isset($APPLICATION)){global $APPLICATION;}
if(!isset($DB))			{global $DB;}

$db_events = GetModuleEvents("main", "OnEpilog");
while($arEvent = $db_events->Fetch())
	ExecuteModuleEvent($arEvent);

$arAllEvents = Array();
$db_events = GetModuleEvents("main", "OnAfterEpilog");
while($arEvent = $db_events->Fetch())
	$arAllEvents[] = $arEvent;

CMain::EpilogActions();

for($i=0; $i<count($arAllEvents); $i++)
	ExecuteModuleEvent($arAllEvents[$i]);

if(!IsModuleInstalled("compression") && !defined("ADMIN_AJAX_MODE"))
{
	$bShowTime = ($_SESSION["SESS_SHOW_TIME_EXEC"] == 'Y');
	$bShowStat = ($GLOBALS["DB"]->ShowSqlStat && $GLOBALS["USER"]->CanDoOperation('edit_php'));
	if($bShowTime || $bShowStat)
	{
		$main_exec_time = round((getmicrotime()-START_EXEC_TIME), 4);
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/interface/debug_info.php");
	}
}

$DB->Disconnect();
?>