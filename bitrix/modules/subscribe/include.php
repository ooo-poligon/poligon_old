<?
global $DBType, $MESS;

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/classes/general/rubric.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/classes/".$DBType."/subscription.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/classes/".$DBType."/posting.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/classes/general/template.php");
?>