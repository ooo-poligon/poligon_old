<?php
/**
 * обработка аяксов
 */
$DBType = "mysql";
$DBHost = "localhost";
$DBLogin = "poliinfo_bitrix";
$DBPassword = "Y2Gd75q";
$DBName = "poliinfo_bitrix";
mysql_connect($DBHost, $DBLogin, $DBPassword); 
mysql_selectdb($DBName);
if(isset($_POST['file']) && isset($_POST['page']))
{
	$post = array();
	foreach ($_POST as $key => $val){
		$post[$key] = mysql_real_escape_string(urldecode($val)); 
	}

	$query = "INSERT INTO `download_pdf` SET
		`file` = '{$post['file']}',
		`page` = '{$post['page']}',
		`datetime` = NOW()";
	mysql_query($query);
	print mysql_error();
}