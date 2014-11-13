<?php 
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
if (PHP_SAPI == 'cli'){
	$DBType = "mysql";
	$DBHost = "localhost";
	$DBLogin = "poliinfo_bitrix";
	$DBPassword = "Y2Gd75q";
	$DBName = "poliinfo_bitrix";
	mysql_connect($DBHost, $DBLogin, $DBPassword); 
	mysql_selectdb($DBName);
	$query = "SELECT `file`, `page`, `datetime`, COUNT(`file`) AS cnt FROM `download_pdf`
		WHERE `datetime` > '".date('Y-m-d', time()-24*60*60*7)."' GROUP BY `file`";
	$result_select = mysql_query($query);
//	print $query;
	$fp = fopen('/var/www/poligon/data/www/poligon.info/pdf/test.csv', 'w');

	while ($line = mysql_fetch_assoc($result_select)) {

	fputcsv($fp, $line, ';');

	}

	fclose($fp);

}else{
	die;
}