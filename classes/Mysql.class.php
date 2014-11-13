<?php
require_once "{$_SERVER['DOCUMENT_ROOT']}/bitrix/php_interface/dbconn.php";
/**	
	простенький интерфейс для работы с БД.
**/

class Mysql{
	private $link;
	/**
		в констракте подключение, создание ссылки на соединение
	*/
	function __contsruct()
	{
		global $DBHost, $DBLogin, $DBPassword, $DBName;
		
		$this->link = mysql_connect($DBHost, $DBLogin, $DBPassword); // or print mysql_error();
		mysql_select_db($DBName, $this->link);
		return $this->link;
	}
	/**
		выбрать массив. 
	*/
	function select_array($query)
	{
		$arrayData = array();
		$result = mysql_query($query, $this->__contsruct());
		//var_dump($result);
		while($elementsArr = mysql_fetch_assoc($result))
		{
			//print_r($elementsArr);
			$arrayData[] = $elementsArr;
		}
		return $arrayData;		
	}
	/**
	 * 
	 * вставка 
	 * @param $query - sql-запрос
	 */
	function insert($query)
	{
		$result = mysql_query($query, $this->__contsruct()) or die(mysql_error());
		return $result;	
	}
	
	function delete()
	{
		$result = mysql_query($query, $this->__contsruct()) or die(mysql_error());
		return $result;	
	}
}

