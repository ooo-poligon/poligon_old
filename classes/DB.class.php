<?php
define('BKF_USE_DB_CACHE', 0);
define('BKF_DB_CACHE_PATH', 'cache');
abstract class DB{
	protected $qArr = array(); 
	protected $prefix = '';
	function __construct(){
		$this->prefix = BKF_DB_PREFIX;
	}
	function get_table_name($table){
		return $this->prefix.$table;
	}
	abstract function connect();
	abstract function count_rows($table, $where = array(), $column = '*', $distinct = false );	
	abstract function create_table($table, $columns);
	abstract function delete_rows($table, $where = array());
	
	abstract function exec($query);
	function error($driver, $error_code, $error_message, $query){
		if(!BKF_DB_ERROR)
			return 0;
		else{
			$event = array($driver, $error_code, $error_message, $query);
			switch (BKF_DB_ERROR){
				case 1: self::log($event); break;
				case 2: ; break;
				case 3: self::log($event); Utils::show_error($event); break;
			}
		}
	}
	
	function log($event){
		var_dump($event);
	}
	/**
	 * 
	 * запись кэша на диск
	 */
	
	function write_cache($query, $array){
//		print_r(scandir(BKF_DB_CACHE_PATH));
		if(!file_exists(BKF_DB_CACHE_PATH)){
			mkdir(BKF_DB_CACHE_PATH);
		}
		$hash = md5($query);
		$filename = BKF_DB_CACHE_PATH."/{$hash}";
		$data = serialize($array);
		file_put_contents($filename, $data);
	}
	/**
	 * чтение кэша с диска
	 */	
	function read_cache($query){
		$hash = md5($query);
		$filename = BKF_DB_CACHE_PATH."/{$hash}";
		if(file_exists($filename)){
			$data = file_get_contents($filename);
			$array = unserialize($data);
		}
		return $array;
	}
	/**
	 * 
	 */
	function check_cache($query){
		$hash = md5($query);
		$filename = BKF_DB_CACHE_PATH."/{$hash}";
		if(file_exists($filename)){
			return 1;
		}
		return 0;
	}
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $table
	 * @param unknown_type $data
	 */
	abstract function insert_array($table, $data = array());
	abstract function insert_row($table, array $data);
	
	abstract function select_array($table, $where = array(), $limit = null, $start = null);
	/**
	 * 
	 * Извлекает строку таблицы в виде асс. массива
	 * @param unknown_type $table
	 * @param unknown_type $where
	 * @param unknown_type $like
	 */
	abstract function select_row($table, $where = array());
	
	abstract function select_join_array($table, $on = array(), $where = array());
	abstract function set_values(array $data);
	
	abstract function update_rows($table, $data = array(), $where = array());
	
	abstract function where(array $where);
}