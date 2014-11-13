<?
/**
данный скрипт утсанавливает связь между рубрикацией портала elec.ru и разделами сайта poligon.info
*/
die;
$DBType = "mysql";
$DBHost = "localhost";
$DBLogin = "poliinfo_bitrix";
$DBPassword = "Y2Gd75q";
$DBName = "poliinfo_bitrix";

require_once "{$_SERVER['DOCUMENT_ROOT']}/classes/Mysql.class.php";
$mysql = new Mysql();
$mysql->insert("SET NAMES UTF8");

$filename = 'poligon-elec.csv';
header("Content-type: text/html; charset=utf-8;");
print "<pre>";
$data = file_get_contents($filename);
//var_dump($data);
$stringsArr = explode("\r\n", $data);
unset($stringsArr[0]);
// массив, где будут лежать id разделов: [poligon] => elec.

$newArr = array();
foreach($stringsArr as $string){
	$cells = explode(";", $string);
	//print "\ncells: \n";
	//var_dump($cells);
	$elec = array_pop($cells);
	foreach($cells as $key => $cell){
		if($cell == '')
			unset($cells[$key]);
	}
	$section = array_pop($cells);
	$p_section = array_pop($cells);
	
	$sql = "SELECT section.ID FROM b_iblock_section section
			LEFT JOIN b_iblock_section p_section ON p_section.ID = section.IBLOCK_SECTION_ID
			WHERE 1 
			AND section.NAME = '{$section}'
			AND p_section.NAME = '{$p_section}'";
			
	print "\n".$sql."\n";
	$section_id = $mysql->select_array($sql);
	var_dump($section_id);
	print $elec;
	$newArr[$section_id[0]['ID']] = $elec;
}
file_put_contents('poligon-elec.srlz', serialize($newArr));