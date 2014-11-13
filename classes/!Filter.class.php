<?php
/**
 * данный класс реализует практически универсальный фильтр
 * но если переводить приложение на архитектуру MVC, 
 *  придётся задействовать перелапатить его. 
 */
/////////////
class Filter{
	const PAGE_LIMIT = 10;
	public $devicesArr = array();
	public $page = 0;
	public $fieldsArr = array();
	private $iterator = 0;
	const INT_LIST = 'int_lists';
	const INT_RANGE_LIST = 'int_range_lists';
	const STRING_LIST = 'string_lists';
	public $section;
	protected $selectionArr = array();
	function __construct($section){
		global $db;
		if(is_int($section))
			$sectionData = $db->select_row('sections', array('id' => $section));
		else
			$sectionData = $db->select_row('sections', array('name' => $section));
			
		$this->section = $sectionData['id'];
//		
	}
	
	/**
	 * 
	 * Устновка текущей страницы
	 * @param int $page
	 * @return int $this->page;
	 */
	public function setPage($page = 0){
		return $this->page = (int) $page;	
	}

	/**
	 * 
	 * вывод селекта для формы фильтра
	 * @param $propName
	 * @param $table
	 * @param $all
	 */
	function addField($propName, $table, $all = null){
		global $db;
		// перво-наперво id нужного параметра
		$propData = $db->select_row($this->getTableName($table, 0), array('name' => $propName));
		 // скидывавем массив с данными о параметре для дальнейшего использования
		$this->fieldsArr[$this->iterator] = $propData;
		$this->fieldsArr[$this->iterator]['name'] = $propName;
		$this->fieldsArr[$this->iterator]['id'] = $propData['id'];
		$this->fieldsArr[$this->iterator++]['table'] = $table; // запоминаем таблицу для этого поля
		 
		// все возможные значения для параметра с этим именем
		$valuesArr = $db->select_array($this->getTableName($table, 1), 
										array('prop_id' => $propData['id']),
										null,
										0,
										'value'
										);

		print "<select name='{$table}{$propData['id']}' class='fltr' data-table='{$table}' data-prop_id='{$propData['id']}'>";
		if(isset($all))
			print "<option value='0'>{$all}</option>";
			foreach ($valuesArr as $value){
				print "<option value='{$value['id']}'>{$value['value']}</option>";
			}
		print "</select><br/>";
//		var_dump($db->lastQuery);
		// идентификаторы устройств с этим параметром? 
//		$db->select_array($this->getTableName($table));
		//
	}

	/**
	 * 
	 * Выводит html с устройствами и параметрами
	 * @param string $html - что возвращать?  
	 */
	function getAllDevices($page = 1, $out = 'html'){
		global $db;
//		$this->devicesArr = $db->select_array('devices', array('section_id' => $this->section), Filter::PAGE_LIMIT, 0);
		$start = ($this->page)*self::PAGE_LIMIT;
		$s = (int) ($this->page - 1);
//		var_dump($s * self::PAGE_LIMIT);
		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM `rp_devices` 
				WHERE `section_id` = {$this->section}
				LIMIT  {$start}, ".self::PAGE_LIMIT;
		$this->devicesArr = $db->select_custom_array($query);
		$query = "SELECT FOUND_ROWS() AS found";
		$found = array_shift(array_shift($db->select_custom_array($query)));

/**
 * TODO убрать
 */
		switch ($out){
			case 'html': 
				foreach ($this->devicesArr as $num => $device){
//			foreach ($this->fieldsArr as $field){
//				$valuesArr = $db->select_join_array(
//									array(
//										$this->getTableName($field['table'], -1), 
//										$this->getTableName($field['table'], 1)),
//									array(
//										'value_id' => 'id'
//									), 
//									array(
//										'device_id' => $device['id'],
//										'prop_id' => $field['id']
//										
//									)
//								);
//				$dataAtrArr["{$field['table']}-{$field['id']}"] = $valuesArr[0]['value'];
//				
//			}
				print $this->getDeviceCard($device, $num);
		}				
			print $this->getPagination($found);
				break;
			case 'array':
				return $this->devicesArr; break;
			case 'json': 
				return json_encode($this->devicesArr); break;
			default: return $this->devicesArr;
		}
	}
	/**
	 * 
	 * формирует ответ, посылаемый ajax'у
	 */
	function getFiltredDevices($fieldsArr = array(), $out = 'array'){
		// флаг, что есть условия, изначально -- нет;
		$some_condition = false;
		
		if(count($fieldsArr) == 0){
			return $this->getAllDevices();
		}
		global $db;
		$select_column = $joinTbl = $where = null;
		foreach ($fieldsArr as $num => $field){
			$select_column .= ", props_{$field['table']}_values{$field['prop_id']}.`id`";
			$joinTbl .= "LEFT JOIN rp_devices_props_{$field['table']} devices_props_{$field['table']}{$field['prop_id']} ON devices_props_{$field['table']}{$field['prop_id']}.device_id = devices.id\n";
			$joinTbl .= "LEFT JOIN rp_props_{$field['table']}_values props_{$field['table']}_values{$field['prop_id']} ON props_{$field['table']}_values{$field['prop_id']}.id = devices_props_{$field['table']}{$field['prop_id']}.value_id\n";
			$joinTbl .= "LEFT JOIN rp_props_{$field['table']} props_{$field['table']}{$field['prop_id']} ON props_{$field['table']}{$field['prop_id']}.id = props_{$field['table']}_values{$field['prop_id']}.prop_id\n";
			
			$where .= "AND props_{$field['table']}_values{$field['prop_id']}.`prop_id` = {$field['prop_id']}\n";
			if($field['value_id'] != 0){
				$where .= "AND props_{$field['table']}_values{$field['prop_id']}.`id` = {$field['value_id']}\n";
				$some_condition = 1;
			}
		}
		// если нет никаких ограничений, то вызываем метод для всех устройств без фильтра
		if($some_condition){
			$start = $this->page*self::PAGE_LIMIT;
			$query = "SELECT SQL_CALC_FOUND_ROWS DISTINCT(devices.id), devices.name /*{$select_column}*/ FROM `rp_devices` AS devices\n
			{$joinTbl}
			WHERE 1
			{$where}
			LIMIT  {$start}, ".self::PAGE_LIMIT;
			$filterDeviceArr = $db->select_custom_array($query, 0);
//			var_dump($query);
			// получим результат всего найдено (для пагинации)
			$query = "SELECT FOUND_ROWS() AS found";
			$found = array_shift(array_shift($db->select_custom_array($query)));
			/**
			 * вопрос на миллион:
			 * что же возвращать, и где поместить представление:
			 * тут (как метод класса FILTER
			 * или в js?
			 * не удобно возвращать рзнородные данные: 
			 * массив устройств и пагинацию
			 */
			
//			var_dump($found);
			switch ($out){
				case 'array': return $filterDeviceArr;
					break;
				case 'json': return json_encode($filterDeviceArr);
					break;
				case 'html':
					foreach ($filterDeviceArr as $num => $device){
						print $this->getDeviceCard($device, $num);
					}
					print $this->getPagination($found);
				break;
				default: return $filterDeviceArr;
			}
			
		}else{
			return $this->getAllDevices('html');
		}
		
	}
	//////////////////////////
	//////представление///////
	//////////////////////////
	/**
	 * 
	 * формирует "карточку", общий метод для getFiltredDevices и getAllDevices
	 * @param array $deviceData - массив с параметрами устройства
	 * @param int $num - number for list
	 * @return html
	 */
	function getDeviceCard($deviceData, $num){
		global $db;
		$num = (int) $num + ($this->page*self::PAGE_LIMIT) + 1;
		$html = null;
		$html .= "<li>[{$num}<!--|{$deviceData['id']}-->] {$deviceData['name']}";
			$html .= "<ul>";
			// выводим в карточке свойства, исходя из заряженных в фильтр 
			// селектов. если нужны ещё, но не в фильтре, а только в представлении,
			// то можно сделать поля скрытыми (4-ый параметр в addField();) 
			// TODO допилить подстановку templates
			foreach ($this->fieldsArr as $num => $field){
				$query ="SELECT * FROM rp_{$this->getTableName($field['table'], -1)} dpl
						LEFT JOIN rp_{$this->getTableName($field['table'], 1)} val ON val.id = dpl.value_id
						WHERE 1 
							AND `device_id` = {$deviceData['id']}
							AND `prop_id` = {$field['id']}
						";
	//			print $query;
				$propArr = array_shift($db->select_custom_array($query));
				$html .= "<li><i>{$field['name']}</i>: {$propArr['value']}</li>"; 
			}
			$html .= "</ul>";
		$html .= "</li>";
		return $html;
	}
	/**
	 * 
	 * пагинация
	 * @param int $found
	 */
	function getPagination($found){
		$html = "<p>Всего найдено: $found</p>";
		$html .= "<ul id='fltrPagination'>";
		$pages = ((int) $found/self::PAGE_LIMIT);
		//if($pages)
		for ($p = 0; $p<$pages; $p++){
			$page = $p+1; // добавляем 1 для читателя
			if($p == $this->page)
				$html .= "<li><u>{$page}</u></li>";
			else
				$html .= "<li>{$page}</li>";
		}
		$html .= "</ul>";
		return $html;
	}
	
	////////////////
	/////утилиты////
	////////////////
	private function getTableName($tblName, $subj = 0){
		switch ((int) $subj){
			case -1: return "devices_props_{$tblName}";
			case 0: return "props_{$tblName}";
			case 1: return "props_{$tblName}_values";
		}
	}
	/**
	 * TODO вынести в класс Device_model
	 * возвращает связанные устройства
	 * TODO указание типа
	 */
	private function getLinkedDevices($device_id, $type = null){
		global $db;
		$devicesArr = $db->select_array("devices_linked", array('device_id' => $device_id));
		return $devicesArr;
	}
	private function getDeviceNameById($device_id){
		$devicesArr = $db->select_array("devices", array('id' => $device_id));
	}
	/**
	 * 
	 * возвертает настройки фильтра 
	 * для инициализации копии фильтра
	 * обсуждение: http://forum.htmlbook.ru/index.php?showtopic=39201&pid=276665
	 */
//	function __sleep(){
//		return array('section', 'fieldsArr');
//	}
//	function __wakeup(){
//		$this->__construct($section);
//		$this->section = 
//	}
	
	////////////////////////////
	/////XXX на удаление XXX////
	////////////////////////////
	/**
	 * XXX переписана
	 * Enter description here ...
	 * @param $selectionArr
	 */
	function ____getFiltredDevices($selectionArr = array()){
		// если нет параметров для фильтрации, то возвращаем все устройства
		if(count($selectionArr) == 0){
			return $this->getAllDevices();
		}
		global $db;
		$joinTbl = $where = null;
		$i = 0;
		$query = "SELECT SQL_CALC_FOUND_ROWS devices.id, devices.name FROM `rp_devices` AS devices\n";
		// TODO надо проверку данных от пользователя провести (ajax-ом могут подсунуть инекцию)
		// FIXED: при переносе параметров из GET в анргументы, просходит превеление типов
		foreach ($selectionArr as $selection => $element){
			$joinTbl .= "LEFT JOIN rp_devices_props_{$element[0]} devices_props_{$element[0]}{$i} ON devices_props_{$element[0]}{$i}.device_id = devices.id\n";
			$joinTbl .= "LEFT JOIN rp_props_{$element[0]}_values props_{$element[0]}_values{$i} ON props_{$element[0]}_values{$i}.id = devices_props_{$element[0]}{$i}.value_id\n";
			$joinTbl .= "LEFT JOIN rp_props_{$element[0]} props_{$element[0]}{$i} ON props_{$element[0]}{$i}.id = props_{$element[0]}_values{$i}.prop_id\n";
			
			$where .= "AND props_{$element[0]}_values{$i}.`prop_id` = {$element[1]}\n";
			$where .= "AND props_{$element[0]}_values{$i}.`id` = {$element[2]}\n";
			
			$i++;
		}
		$query .= "{$joinTbl}
		WHERE 1
		{$where}
		LIMIT  {$this->page}, ".Filter::PAGE_LIMIT;
		$filterDeviceArr = $db->select_custom_array($query);
		// узнаём для пагинации кол-во найденны без лимита
		$foundDevices = $db->select_custom_array("SELECT FOUND_ROWS()", 0);
//		var_dump($filterDeviceArr);
		if(count($filterDeviceArr))
			foreach($filterDeviceArr as $device){
				print $this->getDeviceCard($device);
			}
		else // TODO в фильтре должны сокращаться параметры таким образом, чтобы не оказалось пустого результата
			die("ничего не найдено!");
		
	}	
}
