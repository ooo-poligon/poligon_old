<?php

/////////////
class Filter{
	const PAGE_LIMIT = 10;
	public $devicesArr = array();
	public $page = 0;
	protected $fieldsArr = array();
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
	 * �������� ������� ��������
	 * @param int $page
	 * @return int $this->page;
	 */
	public function setPage($page = 0){
		return $this->page = (int) $page;	
	}

	/**
	 * 
	 * ����� ������� ��� ����� �������
	 * @param $propName
	 * @param $table
	 * @param $all
	 */
	function addField($propName, $table, $all = null){
		global $db;
		// �����-������� id ������� ���������
		$propData = $db->select_row($this->getTableName($table, 0), array('name' => $propName));
		 // ���������� ������ � ������� � ��������� ��� ����������� �������������
		$this->fieldsArr[$this->iterator] = $propData;
		$this->fieldsArr[$this->iterator]['name'] = $propName;
		$this->fieldsArr[$this->iterator]['id'] = $propData['id'];
		$this->fieldsArr[$this->iterator++]['table'] = $table; // ���������� ������� ��� ����� ����
		 
		// ��� ��������� �������� ��� ��������� � ���� ������
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
		// �������������� ��������� � ���� ����������? 
//		$db->select_array($this->getTableName($table));
		//
	}

	/**
	 * 
	 * ������� html � ������������ � �����������
	 * @param string $html - ��� ����������?  
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
 * TODO ������
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
	 * ��������� �����, ���������� ajax'�
	 */
	function getFiltredDevices($fieldsArr = array(), $out = 'array'){
		// ����, ��� ���� �������, ���������� -- ���;
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
		// ���� ��� ������� �����������, �� �������� ����� ��� ���� ��������� ��� �������
		if($some_condition){
			$start = $this->page*self::PAGE_LIMIT;
			$query = "SELECT SQL_CALC_FOUND_ROWS DISTINCT(devices.id), devices.name /*{$select_column}*/ FROM `rp_devices` AS devices\n
			{$joinTbl}
			WHERE 1
			{$where}
			LIMIT  {$start}, ".self::PAGE_LIMIT;
			$filterDeviceArr = $db->select_custom_array($query, 0);
//			var_dump($query);
			// ������� ��������� ����� ������� (��� ���������)
			$query = "SELECT FOUND_ROWS() AS found";
			$found = array_shift(array_shift($db->select_custom_array($query)));
			/**
			 * ������ �� �������:
			 * ��� �� ����������, � ��� ��������� �������������:
			 * ��� (��� ����� ������ FILTER
			 * ��� � js?
			 * �� ������ ���������� ���������� ������: 
			 * ������ ��������� � ���������
			 */
			
//			var_dump($found);
			switch ($out){
				case 'array': return $filterDeviceArr;
					break;
				case 'json': return json_encode($filterDeviceArr);
					break;
				case 'html':
					foreach ($filterDeviceArr as $num => $device){
						$html = $this->getDeviceCard($device, $num);
						print $html;
						//var_dump($html);
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
	//////�������������///////
	//////////////////////////
	/**
	 * 
	 * ��������� "��������", ����� ����� ��� getFiltredDevices � getAllDevices
	 * @param array $deviceData - ������ � ����������� ����������
	 * @param int $num - number for list
	 * @return html
	 */
	function getDeviceCard($deviceData, $num){
		global $db;
		$num = (int) $num + ($this->page*self::PAGE_LIMIT) + 1;
		$html = null;
		$html .= "<li>[{$num}<!--|{$deviceData['id']}-->] {$deviceData['name']}";
			$html .= "<ul>";
			// ������� � �������� ��������, ������ �� ���������� � ������ 
			// ��������. ���� ����� ���, �� �� � �������, � ������ � �������������,
			// �� ����� ������� ���� �������� (4-�� �������� � addField();) 
			// TODO �������� ����������� templates
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
	 * ���������
	 * @param int $found
	 */
	function getPagination($found){
		$html = "<p>����� �������: $found</p>";
		$html .= "<ul id='fltrPagination'>";
		for ($p = 0; $p<((int) $found/self::PAGE_LIMIT); $p++){
			$page = $p+1; // ��������� 1 ��� ��������
			if($p == $this->page)
				$html .= "<li><u>{$page}</u></li>";
			else
				$html .= "<li>{$page}</li>";
		}
		$html .= "</ul>";
		return $html;
	}
	
	////////////////
	/////�������////
	////////////////
	private function getTableName($tblName, $subj = 0){
		switch ((int) $subj){
			case -1: return "devices_props_{$tblName}";
			case 0: return "props_{$tblName}";
			case 1: return "props_{$tblName}_values";
		}
	}
	/**
	 * 
	 * ���������� ��������� ������� 
	 * ��� ������������� ����� �������
	 * ����������: http://forum.htmlbook.ru/index.php?showtopic=39201&pid=276665
	 */
	function getSettinds(){
		
	}
	
	////////////////////////////
	/////XXX �� �������� XXX////
	////////////////////////////
	/**
	 * XXX ����������
	 * Enter description here ...
	 * @param $selectionArr
	 */
	function ____getFiltredDevices($selectionArr = array()){
		// ���� ��� ���������� ��� ����������, �� ���������� ��� ����������
		if(count($selectionArr) == 0){
			return $this->getAllDevices();
		}
		global $db;
		$joinTbl = $where = null;
		$i = 0;
		$query = "SELECT SQL_CALC_FOUND_ROWS devices.id, devices.name FROM `rp_devices` AS devices\n";
		// TODO ���� �������� ������ �� ������������ �������� (ajax-�� ����� ��������� �������)
		// FIXED: ��� �������� ���������� �� GET � ����������, ��������� ���������� �����
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
		// ����� ��� ��������� ���-�� �������� ��� ������
		$foundDevices = $db->select_custom_array("SELECT FOUND_ROWS()", 0);
//		var_dump($filterDeviceArr);
		if(count($filterDeviceArr))
			foreach($filterDeviceArr as $device){
				print $this->getDeviceCard($device);
			}
		else // TODO � ������� ������ ����������� ��������� ����� �������, ����� �� ��������� ������� ����������
			die("������ �� �������!");
		
	}
	
}
