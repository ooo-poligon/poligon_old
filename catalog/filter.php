<?php
// TODO проверка на метод запроса -- эта страница должна доступна только для ajax
require_once $_SERVER['DOCUMENT_ROOT']."/classes/Mysql2.class.php";
$db = new Mysql2();
require_once $_SERVER['DOCUMENT_ROOT']."/classes/Filter.class.php";

ini_set('display_errors',1);
session_start();
//require_once "{$_SERVER['DOCUMENT_ROOT']}/config.php";

$id = isset($_GET['section'])?((int) $_GET['section']): 1;
$filter = $_SESSION['fltr_obj'];
//$filter = unserialize($_SESSION['fltr_obj']);
//var_dump($filter);
$page = isset($_GET['page'])?((int) $_GET['page']): 0;
$filter->setPage($page);
// передаём только параметры
$filter->getFiltredDevices(array_filter($_GET, 'only_numeric'), 'html');

function only_numeric($key_or_array){
	if(is_array($key_or_array))
		return true;
}