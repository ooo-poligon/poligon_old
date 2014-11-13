<?php
require_once 'Mysql.class.php';

$db = new Mysql();
$res = $db->select_array(
		"SELECT 
ce.`LAST_USE`
FROM b_catalog_export ce
WHERE 1
AND ce.ID = ".(int) $_GET['ID']);
echo $res[0]['LAST_USE'];