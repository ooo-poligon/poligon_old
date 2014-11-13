<?php 
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/functions.php");
// буферизируем для парсера-надстройки (see parseForDynamicContent in /function.php)
ob_start("parseForDynamicContent");
?>