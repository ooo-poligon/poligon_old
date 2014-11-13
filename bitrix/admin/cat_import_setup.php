<?
//ini_set('display_errors', FALSE);
//error_reporting(0);
ini_set('max_execution_time', 20);
print "Alert! Here in ".__FILE__." set <br/>
ini_set('display_errors', FALSE);<br/>

fix it please!<br/>
";
print ini_get('max_execution_time');
print "<br/>";
print ini_get('memory_limit');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/admin/import_setup.php");

?>