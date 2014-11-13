<?php
/**
 *
 * Class for mysql
 * @author samizdam
 * @since 0.1
 * @version 0.9
 * TODO все обращения к базе надо проводить через внутренний метод,
 * таким образом удобнее регистрировать запросы и логировать ошибки. 
 */
define('BKF_DB_HOST', 'localhost');
define('BKF_DB_USER', 'poliinfo_rp');
define('BKF_DB_PASS', 'adsfzcxv1234');
define('BKF_DB_NAME', 'poliinfo_rp');
define('BKF_DB_PREFIX', 'rp_');
define('BKF_DB_CHARSET', 'CP1251');
//define('BKF_DB_CHARSET', 'UTF8');
require_once 'DB.class.php';
/*global $DB;
define('BKF_DB_HOST', $DB['Host']);
define('BKF_DB_USER', $DB['Login']);
define('BKF_DB_PASS', $DB['Password']);
define('BKF_DB_NAME', $DB['Name']);
define('BKF_DB_PREFIX', 'rp_');
*/
class Mysql2 extends DB{
	protected $conn = false;
	public $qArr = array();
	public $lastQuery = null;
	public $qErr = array();

	function __construct()
	{
		if(!$this->conn)
			$this->connect();
		$this->exec("SET NAMES ".BKF_DB_CHARSET);
		$this->prefix = BKF_DB_PREFIX;
	}

	function connect()
	{
		$this->conn = mysql_connect(BKF_DB_HOST, BKF_DB_USER, BKF_DB_PASS) or print mysql_error();
		mysql_select_db(BKF_DB_NAME, $this->conn);
	}
	/**
	 * TODO для этого метода тоже можно кэширование использовать
	 * подсчёт значений в таблице
	 * @param unknown_type $col - столбец
	 * @param unknown_type $tbl - таблица без префикса
	 */
	function count_rows($table, $where = array(), $column = '*', $distinct = false)
	{
		$exp = $column;
		if($column != '*')
			$exp = "`$column`";
		if($distinct == 1)
		$exp = "DISTINCT($exp)";
		
		$query = "SELECT COUNT(".$exp.") as count FROM `{$this->get_table_name($table)}`
			{$this->where($where)}";
		$this->qArr[] = $query;
		// TODO проверку!!
		$result = mysql_query($query);
		$count = 0;
		if($result)
			$count = array_shift(mysql_fetch_assoc($result));
		return $count;
	}	
	
	function create_table($table, $columns){
		
	}
	
	
	function delete_rows($table, $where = array()){
		$query = "DELETE FROM {$this->get_table_name($table)} {$this->where($where)}";
		$this->exec($query);
		return mysql_affected_rows();
	}
	
	function exec($query)
	{
		$result = true;
		$error = false;
		$this->qArr[] = $this->lastQuery = $query;
		$result = mysql_query($query, $this->conn) or die(mysql_error().$query);//$this->qErr[] = mysql_error() . mysql_errno();// . $query;
		return $result;
	}

	function insert_array($table, $data = array(), $update = false){
		foreach ($data as $row => $array){
			$this->insert_row($table, $array, $update);
		}
		return mysql_affected_rows($this->conn);	
	}
	
	function insert_row($table, array $data, $update = false){
		$query = "INSERT INTO `{$this->get_table_name($table)}` {$this->set_values($data)}";
		if($update){
			$query .= "ON DUPLICATE KEY UPDATE `{$update}` = `{$update}` + 1";
		}
//		var_dump($data);
		$this->exec($query);
		return mysql_insert_id($this->conn);
	}
	/* проверка типов на необходимость кавычек */
	function screening_value($string){
		if(is_numeric($string))
			return $string;
		else
			return "'{$string}'";
	}

	function set_values(array $data){
		$values = "SET ";
		$count = count($data);
		if($count){
			$i = 0;
			foreach ($data as $column => $value){
				if(++$i < $count){
					$values .= "`{$column}` = {$this->screening_value($value)},\n";
				}else{ 
					$values .= "`{$column}` = {$this->screening_value($value)}\n";
				}
			}
		}
		return $values;
	}

	function select_array($table, $where = array(), $limit = null, $start = 0, $order = null, $group = null){
		$orderSql = null;
		if(isset($order)){
			$orderSql = "ORDER BY {$order}";
		}
			
		$limitSql = "LIMIT ".intval($start).", ";
		if(isset($limit)){
			$limitSql .= (int) $limit;
		}else{
			$limitSql .= $this->count_rows($table);
		}
		$query = "SELECT * FROM {$this->get_table_name($table)}
			{$this->where($where)}
			{$orderSql}
			{$limitSql}
			";
		$array = array();
		// если в системе заюзано кэширование, то пробуем достать кеш,
		if(BKF_USE_DB_CACHE){
			if($this->check_cache($query))
				return $this->read_cache($query);
			else // если нет такого, то запишем)) 
				$cache_flag = 1;
		}
		$result = $this->exec($query);
		if($result)
			if(mysql_num_rows($result))
			{
				while($row = mysql_fetch_assoc($result))
				{
					$array[] = $row;
				}
			}
		// если есть необходимость, запишем кэш
		if(!empty($cache_flag))
			$this->write_cache($query, $array);
		return $array;
	}	
	/**
	 * функция для сложных селектов, с возможностью кэширования (т.к. её не предусмотрено в exec()
	 * для запросов, которые затруднительно примести в других select-функция
	 * Костыль, блеять...
	 * использование кэша опционально=)
	 * всё как и в select_array(), только отсутвует формирование запроса -- он приходит готовый =>
	 * эта функция небезопасна, т.к. ни какой проверки корректности sql не выполняется!! 
	 * Enter description here ...
	 */
	function select_custom_array($query, $use_cache = BKF_USE_DB_CACHE){		
		if($use_cache){
			if($this->check_cache($query))
				return $this->read_cache($query);
			else // если нет такого, то запишем)) 
				$cache_flag = 1;	
		}
		$array = array();
		$result = $this->exec($query);
		
		if($result)
			if(mysql_num_rows($result))
			{
				while($row = mysql_fetch_assoc($result))
				{
					$array[] = $row;
				}
			}
		// если есть необходимость, запишем кэш
		if(!empty($cache_flag))
			$this->write_cache($query, $array);
		return $array;
	}

	/**
	 * (non-PHPdoc)
	 * @see DB::select_row()
	 */
	function select_row($table, $where = array()){
		$query = "SELECT * FROM `{$this->get_table_name($table)}` 
			{$this->where($where)}
			LIMIT 0, 1";
		// если в системе заюзано кэширование, то пробуем достать кеш,
		$array = array();
		if(BKF_USE_DB_CACHE){
			if($this->check_cache($query))
				return $this->read_cache($query);
			else // если нет такого, то запишем)) 
				$cache_flag = 1;
		}

		$result = $this->exec($query);
		
		if(mysql_num_rows($result))
			$array = mysql_fetch_assoc($result);
		// если есть необходимость, запишем кэш
		if(!empty($cache_flag))
			$this->write_cache($query, $array);		
		return $array;
	}
	
	/**
	 * @version 3.0
	 * @see public_html/classes/DB::select_join_array()
	 */
	function select_join_array($table, $on = array(), $where = array()){
		$query = $whereJoin = $joinSql = null;
		foreach ($on as $alias => $tables){
			list($fromTable, $fromColumn) = explode('.', $table);
			list($toTable, $toColumn) = explode('.', $to);
			$whereJoin .= ", \n{$this->get_table_name($fromTable)}.*"; 
			$joinSql .= "LEFT JOIN {$this->get_table_name($fromTable)} {$fromTable} ON {$fromTable}.{$fromColumn} = {$toTable}.{$toColumn}\n";
		}
		$query = "SELECT *
		FROM {$this->get_table_name($table)} AS {$table}
		{$joinSql}
		{$this->where($where)};
		";
		$this->exec($query);
	}
	
	function _____select_join_array($table, $on = array(), $where = array()){
		$query = $whereJoin = $joinSql = null;
		foreach ($on as $from => $to){
			list($fromTable, $fromColumn) = explode('.', $from);
			list($toTable, $toColumn) = explode('.', $to);
			$whereJoin .= ", \n{$this->get_table_name($fromTable)}.*"; 
			$joinSql .= "LEFT JOIN {$this->get_table_name($fromTable)} {$fromTable} ON {$fromTable}.{$fromColumn} = {$toTable}.{$toColumn}\n";
		}
		$query = "SELECT *
		FROM {$this->get_table_name($table)} AS {$table}
		{$joinSql}
		{$this->where($where)};
		";
		$this->exec($query);
	}
	
	function __old__select_join_array($tables = array(), $on = array(), $where = array()){
		$columnsArr = $tablesArr = $array = array();
		foreach ($tables as $table){
			$tablesArr[] = $this->get_table_name($table);
			$columnsArr[$table] = $this->show_columns($table);
		}
		$joinSql = null;
		// основная таблица указана первой
		$mainTable = array_shift($tablesArr);
		var_dump($on);
		foreach ($on as $key => $to){
			$table = array_shift($tablesArr);
//			print $table;
//			var_dump($tablesArr);
//			print $to;
			$joinSql .= "LEFT JOIN {$table} ON {$mainTable}.{$key} = {$table}.{$to} \n";
		}
		
		$columns = null;
		$cols = array();
		$t = 0;
		foreach ($columnsArr as $table => $tableCols){
			$t++;
			// в цикле перечисляем все столбцы для каждой таблицы
			for ($i = 0; $i <count($tableCols); $i++){
				// если это последний столбец последней таблицы, отбрасываем запятую
				if($i+1 == count($tableCols) && $t == count($tables)){
					if(in_array($tableCols[$i], $cols)){
						$columns .= "{$this->get_table_name($table)}.{$tableCols[$i]} AS {$table}_{$tableCols[$i]}\n";
					}else
						$columns .= "{$this->get_table_name($table)}.{$tableCols[$i]}\n";
				}
				else{
					if(in_array($tableCols[$i], $cols)){
						$columns .= "{$this->get_table_name($table)}.{$tableCols[$i]} AS {$table}_{$tableCols[$i]},\n";
					}else					
						$columns .= "{$this->get_table_name($table)}.{$tableCols[$i]},\n";
				}
				$cols[] = $tableCols[$i];
			}
		}
		
		$query = "SELECT {$columns} FROM {$mainTable}
			{$joinSql}
			{$this->where($where)}";
			// если в системе заюзано кэширование, то пробуем достать кеш,
			
		$array = array();
		if(BKF_USE_DB_CACHE){
			if($this->check_cache($query))
				return $this->read_cache($query);
			else // если нет такого, то запишем)) 
				$cache_flag = 1;
		}
		
//		print $query;
		$result = $this->exec($query);
		
		if($result && mysql_num_rows($result))
		{
			while($row = mysql_fetch_assoc($result))
			{
				$array[] = $row;
			}
		}
		
		// если есть необходимость, запишем кэш
		if(!empty($cache_flag)){
			$this->write_cache($query, $array);
		}
		
		return $array;
	}

	function update_rows($table, $data = array(), $where = array()){
		$query = "UPDATE {$this->get_table_name($table)}
			{$this->set_values($data)}
			{$this->where($where)}
		";
		$this->exec($query);
		return mysql_affected_rows($this->conn);
	}

	/**
	 * TODO проверка типа переменной -- строки в кавычках, числа -- нет
	 * (non-PHPdoc)
	 * @see DB::where()
	 */
	function where(array $where){
		$condition = "WHERE 1";
		// simple where conditional
		if(!array_key_exists('WHERE', $where) && 
			!array_key_exists('LIKE', $where) && 
			!array_key_exists('IN', $where) && 
			!array_key_exists('NOT_IN', $where) && 			
			count($where)){
			foreach ($where as $column => $value)
				$condition .= "\nAND `{$column}` = '{$value}'";						
		}
		// WHERE 
		if(isset($where['WHERE'])){
			if(count($where['WHERE'])){
			foreach ($where['WHERE'] as $column => $value)
				$condition .= "\nAND `{$column}` = '{$value}'";
			}			
		}
		// LIKE
		if(isset($where['LIKE'])){
			if(count($where['LIKE'])){
				foreach ($where['LIKE'] as $column => $value)
					$condition .= "\nAND `{$column}` LIKE '{$value}'";
			}			
		}
		// IN -- join array for key-column 
		if(isset($where['IN'])){
			if(count($where['IN'])){
				foreach ($where['IN'] as $column => $values)
					$condition .= "\nAND `{$column}` IN (".join(', ', $value).")";
			}					
		}
		// IN -- join array for key-column 
		if(isset($where['NOT_IN'])){
			if(count($where['NOT_IN'])){
				foreach ($where['NOT_IN'] as $column => $values)
					$condition .= "\nAND `{$column}` NOT IN (".join(', ', $values).")";
			}					
		}
		return $condition; 
	}
	/**
	 * 
	 * Возвращает массив с полями указанной таблицы
	 * @param $table
	 */
	function show_columns($table)
	{
		$columns = array();
		$query = "SHOW columns FROM ".$this->get_table_name($table);
		$this->qArr[] = $query;

		// если в системе заюзано кэширование, то пробуем достать кеш,
		if(BKF_USE_DB_CACHE){
			if($this->check_cache($query))
				return $this->read_cache($query);
			else // если нет такого, то запишем)) 
				$cache_flag = 1;
		}
		
		$result = mysql_query($query);
		if($result)
			while ($column = mysql_fetch_assoc($result))
				$columns[] = $column['Field'];
		// если есть необходимость, запишем кэш
		if(!empty($cache_flag)){
			$this->write_cache($query, $columns);
		}		
		return $columns;
	}
}