<?php
/**
	функции, потребовавшиеся для реализации функционала 
	и инструментов, которые удалось реализовать 
	средствами систему Битрикс. Этот файл подключается в файле
	/home/p/poliinfo/public_html/bitrix/header.php
	@Николай, samizdam.net
	ver 1.02 от 29/09/2011 
	ver 1.03 30/09/2011
	ver 1.04 24/04/2012	-- add function parseForDynamicContent()
**/

require_once "{$_SERVER['DOCUMENT_ROOT']}/classes/Mysql.class.php";

$mysql = new Mysql();

//if(is_class('Mysql'))
//	print 'Mysql';


/**
	функция для показа "похожих товаров". 
	принимает массив наименований (уникальных имён),
	информация (кол=во на складе, производитель, артикул, кр. описание, ссылка) о которых выводиться. 
**/

function relatedElementsByNames($elArr = array())
{
	if(count($elArr))
		$relatedElements = GetIBlockElementList(4, false, Array("SORT"=>"ASC"), 100, array("NAME" => $elArr));
	else 
		return 0;
	if(count($relatedElements)){
		print "<h3>Смотрите также товары: </h3>";
		print "<ul>";
		while($element = $relatedElements->GetNext())
		{
			$props = CCatalogProduct::GetByID($element["ID"]);
			print "<li><a href='/catalog/index.php?ELEMENT_ID={$element["ID"]}'>{$element["NAME"]}</a></li>";
		}
		print "</ul>";
	}
}


/**
	Функция для вывода "похожих товаров".
	принимает массив артиклей товаров,
	информация (кол=во на складе, производитель, артикул, кр. описание, ссылка) о которых выводиться. 
	функция может как вызываться как в произольном месте 
	страницы с указанными параментрами, так и стандартно
	в шаблоне карточки товара, если с товаром связаны другие. 
**/
function relatedElementsByArticles($articleArr = array())
{
	//global $mysql;
	//$mysql = $GLOBALS['mysql'];
	$articleString = null;
	foreach($articleArr as $article)
		$articleString .= "'$article', ";
	$query = "SELECT 
				*,
				`prod_short`.`VALUE` as `producer_short`,
				`prod_full`.`VALUE` as `producer_full`,
				`pdf`.`VALUE` as `pdf_link`,			
				`img`.`VALUE` as `img`,
				`pr`.`VALUE` as `article`				
				FROM `b_iblock_element_property` pr
				LEFT JOIN b_iblock_element el ON el.ID = pr.`IBLOCK_ELEMENT_ID`
				LEFT JOIN b_catalog_product prod ON prod.ID = pr.`IBLOCK_ELEMENT_ID`
				LEFT JOIN b_iblock_element_property prod_full ON prod_full.`IBLOCK_ELEMENT_ID` = el.ID
				LEFT JOIN b_iblock_element_property prod_short ON prod_short.`IBLOCK_ELEMENT_ID` = el.ID
				LEFT JOIN b_iblock_element_property pdf ON pdf.`IBLOCK_ELEMENT_ID` = el.ID
				LEFT JOIN `b_iblock_element_property` img ON img.`IBLOCK_ELEMENT_ID` = el.ID
				WHERE 1
				AND pr.`VALUE` IN (".substr($articleString, 0, -2).") 
				AND pr.`IBLOCK_PROPERTY_ID` = 16
				AND prod_short.IBLOCK_PROPERTY_ID = 21
				AND prod_full.IBLOCK_PROPERTY_ID = 20
				AND pdf.IBLOCK_PROPERTY_ID = 19
				AND `img`.`IBLOCK_PROPERTY_ID` = 18
				";
	//var_dump($mysql);
	$mysql = new Mysql();
	$elementsArr = $mysql->select_array($query);
	
	if(count($elementsArr))
	{
	
		print "
		<div class='catalog-section'>
		<table class='zebra p100'>
		<caption>См. также похожие товары</caption>
			<tr>
				<th style='width: 75%'>Наименование </th>
				<th style='width: 50px'>Произв. </th>
				<th style='width: 50px'>PDF </th>
				<th style='width: 50px'>Склад</th>
			</tr>";
		foreach($elementsArr as $element)
		{
			print "<tr id='{$element["IBLOCK_ELEMENT_ID"]}'>
					<td class='name'>
						<div class='hideImage'>
							<a href='/catalog/index.php?SECTION_ID={$element["IBLOCK_SECTION_ID"]}&ELEMENT_ID={$element["IBLOCK_ELEMENT_ID"]}' target='_blank'>
								<img src='http://poligon.info/images/{$element['img']}' alt='{$element['NAME']}' />
							</a>
							<p>{$element['PREVIEW_TEXT']}</p>
						</div>
						<a href='/catalog/index.php?SECTION_ID={$element["IBLOCK_SECTION_ID"]}&ELEMENT_ID={$element["IBLOCK_ELEMENT_ID"]}'><b>{$element['NAME']}</b></a><br />
							<span>{$element['PREVIEW_TEXT']}</span>
						</td>
						<td>".($element["producer_short"]?$element["producer_short"]:$element["producer_full"])."</td>
						<td><a href='/PDF/{$element['pdf_link']}' target='_blank'><img src='/images/pdf_doc.gif' alt='pdf'/></a></td>
						<td>
						".($element['QUANTITY']>0?
						"<img src='/images/green.gif' alt='Есть на складе' title='Есть на складе'/>":
						"<img src='/images/grey.gif' alt='Нет данных' title='Нет данных'/>"
						)."
						</td>
					</tr>";
		}
		print "</table>
		</div>";
	}
	
}
/**
	интеррактивный фильтр реле. в настоящее время выводит
	таблицу для реле времени теле, но возможно расширение 
*/
function releTable($type = 'реле времени')
{
	$mysql = new Mysql();
	$query = "SELECT * FROM `rele_functions` 
			WHERE 1
			AND `type` = '".mysql_real_escape_string($type)."'";
	$functionsArr = $mysql->select_array($query);
	print "<fieldset  style='width: 97%'>
	<legend style='font-size: 24px;'>Выберите функции</legend>";
	foreach($functionsArr as $function)
	{
		print "<div class='function'>";
		print "<input type='checkbox' id='_{$function['code']}_' />";
		print "<label for='_{$function['code']}_'>
			<p><b>{$function['code']}</b><br /><span>{$function['title']}</span></p>
			<img src='http://poligon.info/images/TELE/functions/time/pre/tele_{$function['code']}.jpg' alt='{$function['title']}' title='{$function['about']}'/>
		</label>";
		print "</div>";
	}
	print "
	<fieldset class='f-right p50' style='margin-top: 12px;'> 
	<legend>Напряжение питания</legend>
	<label for='acdc'>Введите напряжение</label>
	<input type='number' id='acdc'
		min='12'
		max='500'
		step='2'
		value='0'
		size='4'
		style='text-align: right;' /> V
	<p>Выберите тип тока: 
	<input type='radio' name='acdc' value='AC' id='ac'/>
	<label for='ac'>AC</label>
	<input type='radio' name='acdc' value='DC' id='dc'/>
	<label for='dc'>DC</label>
	<span style='float: right;'><a href='#table'>результат</a></span></p>
	</fieldset>
	</fieldset>";
	// достали все релющки
	$query = "SELECT
		DISTINCT(`prop`.`VALUE`),  
		`rele`.`id`,			
		`rele`.*,
		`el`.`NAME`, 
		`el`.`PREVIEW_TEXT`,
		`pdf`.`VALUE` as `pdf_link`,
		`img`.VALUE as `img`,
		`el`.`IBLOCK_SECTION_ID`,
		`prop`.`IBLOCK_ELEMENT_ID`,
		`prod`.`QUANTITY`
		FROM `rele_ac_dc` `rele`
			LEFT JOIN `b_iblock_element_property` `prop` ON `prop`.`VALUE` = `rele`.`article`
			LEFT JOIN `rele_functions_sect` `fs` ON `rele`.`id` = `fs`.`rele_id` 
			LEFT JOIN `b_iblock_element` `el` ON `el`.`ID` = `prop`.`IBLOCK_ELEMENT_ID`
			LEFT JOIN `b_iblock_element_property` pdf ON pdf.`IBLOCK_ELEMENT_ID` = el.ID
			LEFT JOIN `b_iblock_element_property` img ON img.`IBLOCK_ELEMENT_ID` = el.ID
			LEFT JOIN `b_catalog_product` prod ON prod.ID = prop.`IBLOCK_ELEMENT_ID`
			WHERE 1
			AND `fs`.function_id IN(SELECT id FROM `rele_functions` WHERE `type` = '".mysql_real_escape_string($type)."')
			AND `prop`.`IBLOCK_PROPERTY_ID` = 16
			AND `pdf`.`IBLOCK_PROPERTY_ID` = 19
			AND `img`.`IBLOCK_PROPERTY_ID` = 18
			ORDER BY `rele`.`ord` 
			";
//	print $query;
	$elementsArr = $mysql->select_array($query);
	print "<table class='zebra p100' id='table'>
		<caption>Подходящие реле</caption>
		<tr>
			<th style='width: 30%'>Тип реле времени</th>
			<th style='width: 25%'>Функция </th>				
			<th style='width: 25%'>Напряжение питания</th>>
			<th style='width: 10%'>PDF </th>
			<th style='width: 10%'>Склад</th>
		</tr>";
	foreach ($elementsArr as $element)
	{
		// присоединяем функции к реле
		$query = "SELECT `code` FROM `rele_functions` 
			WHERE 1 
			AND id IN(SELECT `function_id` FROM `rele_functions_sect`
				WHERE 1
				AND `rele_id` = {$element['id']})";
		$elementFunctions = $mysql->select_array($query);
		$functionsString = $functionsStringClass = null;
		foreach ($elementFunctions as $func)
			$functionsString .= "{$func['code']} ";
		foreach ($elementFunctions as $func)
			$functionsStringClass .= "_{$func['code']}_ ";

		// присоединяем напряжение питания
		$acdcString = null;		
		// вариант 1. если оба вида тока имеют одинаковый диапазон значений
		if($element['ac_min'] < $element['ac_max'] && 
			$element['dc_min'] == $element['ac_min'] &&
			$element['dc_max'] == $element['ac_max'])
			{
				$acdcString .= "<span class='acdc_min'>{$element['ac_min']}</span>—<span class='acdc_max'>{$element['ac_max']}</span> AC/DC";
			}
		// вариант 2, если есть диапазон напряжений только для одного типа тока
		// сперва АС
		if($element['ac_min'] < $element['ac_max'] &&
			$element['dc_min'] == 0 &&
			$element['dc_max'] == 0	)
			{
				if($acdcString != null)
					$acdcString .= ", ";
				$acdcString .= "<span class='ac_min'>{$element['ac_min']}</span>—<span class='ac_max'>{$element['ac_max']}</span> AC";
			}
		// теперь DC
		if($element['dc_min'] < $element['dc_max'] &&
			$element['ac_min'] == 0 &&
			$element['ac_max'] == 0	)
		{
			if($acdcString != null)
				$acdcString .= ", ";
			$acdcString .= "<span class='dcmin'>{$element['dc_min']}</span>—<span class='dcmax'>{$element['dc_max']}</span> DC";
		}
		// вариант 4, если есть фиксированное переменное напряжение
		if($element['ac_fix'] > 0) 
		{
			if($acdcString != null)
				$acdcString .= ", ";
			$acdcString .= "<span class='ac_fix'>{$element['ac_fix']}</span> AC";
		}
		// вариант 5, если есть ификсированное постоянно енапряжение
		if($element['dc_fix'] > 0)
		{
			if($acdcString != null)
				$acdcString .= ", ";
			$acdcString .= "<span class='dc_fix'>{$element['dc_fix']}</span> DC";
		}	
		// если нужен модуль питания отдельно, то вместо то добавляем ссылку
		if($element['PowerModule'] != '0'){
			$acdcString .= ' <a href="#tr2" class="PMtr2">*</a>';	
		}

		print "<tr class='{$functionsStringClass}' id='{$element['article']}' rel='row'>
			<td class='name'>
			<div class='hideImage'>
			<a href='/catalog/index.php?SECTION_ID={$element["IBLOCK_SECTION_ID"]}&ELEMENT_ID={$element["IBLOCK_ELEMENT_ID"]}' target='_blank'>
				<img src='http://poligon.info/images/{$element['img']}' alt='{$element['NAME']}' />
			</a>
			<p>{$element['PREVIEW_TEXT']}</p>
			</div>
			<a href='/catalog/index.php?SECTION_ID={$element["IBLOCK_SECTION_ID"]}&ELEMENT_ID={$element["IBLOCK_ELEMENT_ID"]}'><b>{$element['NAME']}</b></a>
			</td>
			<td>{$functionsString}</td>
			<td class='voltage'>{$acdcString}</td>
			<td><a href='/PDF/{$element['pdf_link']}' target='_blank'><img src='/images/pdf_doc.gif' alt='pdf'/></a></td>
			<td>
			".($element['QUANTITY']>0?
			"<img src='/images/green.gif' alt='Есть на складе' title='Есть на складе'/>":
			"<img src='/images/grey.gif' alt='Нет данных' title='Нет данных'/>"
			)."
			</td>
		</tr>";
	}
	print "</table>";
}

/**
	выводит информацию по функциям
*/

function releFunctions($type = 'реле времени')
{
	$mysql = new Mysql();
	$query = "SELECT * FROM `rele_functions`
				WHERE 1
				AND `type` = '".mysql_real_escape_string($type)."'
				";
	$functions = $mysql->select_array($query);
	foreach($functions as $function)
	{
		print "<h2 id='func_{$function['code']}'>Реле времени с функцией <u>".mb_strtolower($function['title'], 'Windows-1251')."</u> ({$function['code']})</h2>";
		print "<div>
		<div style='text-align: right;'>
			<img style='width: 250px; height: auto;' src='http://poligon.info/images/TELE/functions/time/tele_{$function['code']}.gif'
				alt='{$function['title']}' />
		</div>
		<p>{$function['description']}</p>
		<div>{$function['about']}</div>
		</div>";
		$query = "SELECT
		`rele`.*,
		`el`.*,
		`el`.`IBLOCK_SECTION_ID`,
		`prop`.`IBLOCK_ELEMENT_ID`,		
		`prod`.`QUANTITY`,
		`pdf`.`VALUE` AS `pdf_link`,
		`img`.`VALUE` AS `img`
		FROM `rele_ac_dc` `rele`
			LEFT JOIN `b_iblock_element_property` `prop` ON `prop`.`VALUE` = `rele`.`article`			
			LEFT JOIN `rele_functions_sect` `fs` ON `rele`.`id` = `fs`.`rele_id` 
			LEFT JOIN `b_iblock_element` `el` ON `el`.`ID` = `prop`.`IBLOCK_ELEMENT_ID`
			LEFT JOIN `b_iblock_element_property` `pdf` ON `pdf`.`IBLOCK_ELEMENT_ID` = `el`.`ID`
			LEFT JOIN `b_catalog_product` prod ON prod.ID = prop.`IBLOCK_ELEMENT_ID`
			LEFT JOIN `b_iblock_element_property` `img` ON `img`.`IBLOCK_ELEMENT_ID` = `prop`.`IBLOCK_ELEMENT_ID`
			WHERE 1
			AND `fs`.function_id = {$function['id']}
			AND `prop`.`IBLOCK_PROPERTY_ID` = 16
			AND `pdf`.`IBLOCK_PROPERTY_ID` = 19
			AND `img`.`IBLOCK_PROPERTY_ID` = 18";
//	print $query;
		$elementsArr = $mysql->select_array($query);
		if(count($elementsArr))
		{
			print '<p>';
			print '<a href="#" rel="'.$function['code'].'" class="open">показать</a>';
			print '<a href="#" rel="'.$function['code'].'" class="close none">скрыть</a>';
			print ' подходящие реле времени</p>';
			print "<table class='p100 zebra none' id='{$function['code']}'>";
			print "<caption>Реле обладающие функцией {$function['title']}:</caption>";
			foreach($elementsArr as $element)
			{
				print "<tr id='{$element['article']}'>
					<td class='name'>
					<div class='hideImage'>
					<a href='/catalog/index.php?SECTION_ID={$element["IBLOCK_SECTION_ID"]}&ELEMENT_ID={$element["IBLOCK_ELEMENT_ID"]}' target='_blank'>
						<img src='/images/{$element['img']}' alt='{$element['NAME']}' />
					</a>
					<p>{$element['PREVIEW_TEXT']}</p>
					</div>
					<a href='/catalog/index.php?SECTION_ID={$element["IBLOCK_SECTION_ID"]}&ELEMENT_ID={$element["IBLOCK_ELEMENT_ID"]}'>
					<b>{$element['name']}</b></a><br />
					<span>{$element['PREVIEW_TEXT']}</span>
					</td>
					<td>".($element["producer_short"]?$element["producer_short"]:$element["producer_full"])."</td>
					<td><a href='/PDF/{$element['pdf_link']}' target='_blank'><img src='/images/pdf_doc.gif' alt='pdf'/></a></td>
					<td>
					".($element['QUANTITY']>0?
					"<img src='/images/green.gif' alt='Есть на складе' title='Есть на складе'/>":
					"<img src='/images/grey.gif' alt='Нет данных' title='Нет данных'/>"
					)."
					</td>
				</tr>";
			}
			print "</table>";
		}
		print '<hr class="px400"/>';
	}
}


/**
 * 
 * функция для отрисовки таблицы с реле времени серии Enya
 * основываясь на данных в базе. Написать её просто быстрее,
 * чем верстать из буклета, да если что, можно доработать под полезный функционал
 */
function enyaTable(){
	global $mysql;
	// надо узнать какие функции присуще этой серии
	// для начала извлекаем все реле
	$query = "SELECT 
	`img`.`VALUE` AS `img`,
	`r`.`article`,
	`r`.`name`,
	`r`.`id`,
	`el`.`ID` AS `IBLOCK_ELEMENT_ID`,
	`el`.`PREVIEW_TEXT`,
	`el`.`IBLOCK_SECTION_ID` AS `IBLOCK_SECTIOIN_ID`
FROM `rele_ac_dc` `r`
		LEFT JOIN `b_iblock_element_property` `prop` ON `prop`.`VALUE` = `r`.`article`
		LEFT JOIN `b_iblock_element_property` `img` ON `img`.`IBLOCK_ELEMENT_ID` = `prop`.`IBLOCK_ELEMENT_ID`
		LEFT JOIN `b_iblock_element` `el` ON `el`.`ID` = `prop`.`IBLOCK_ELEMENT_ID`
 WHERE 1 
		 AND `r`.`name` LIKE 'E%'
		 AND `prop`.`IBLOCK_PROPERTY_ID` = 16
		 AND `img`.`IBLOCK_PROPERTY_ID` = 18	
	";
	$enyaRele = $mysql->select_array($query);
	
	$query = "SELECT 
			f.title,
			f.id,
			f.code
		 FROM `rele_functions_sect` s
			LEFT JOIN `rele_functions` f ON f.id = s.function_id
					WHERE 1
					AND `rele_id` IN (SELECT id FROM `rele_ac_dc` WHERE 1 AND `name` LIKE 'E%')
					AND f.`type` = 'реле времени'
					GROUP BY id";
	$funcArr = $mysql->select_array($query);


	print '
	<table style="border: solid 1px black;" class="zebra">
	<tbody>
	<tr>
		<th>Быстрый выбор реле</th>
		<!--
		<th rowspan="2">12-240V AC/DC</th>
		<th rowspan="2">24-240V AC/DC</th>

		<th rowspan="2">Выходная цепь ПК (переключающий контакт)</th>
		<th colspan="2">Номер для заказа</th>
		-->
		<th rowspan="2">Артикул</th>
		<th colspan="15">Функции</th>
	</tr>
	<tr>
		<th>Тип</th>
	<!--
		<th>Кол-во в уп.: 1</th>
		<th>Кол-во в уп.: 10</th>
	-->	
		';
	foreach ($funcArr as $func){
		print "<th><abbr title='{$func['title']}'>{$func['code']}</abbr></th>";
	}
	
	print '</tr>
	';
	foreach ($enyaRele as $element){
		print '<tr id="'.$element['article'].'">';
		print "<td class='name'>
			<!--
			<div class='hideImage'>
			<a target='_parent' href='/catalog/index.php?SECTION_ID={$element["IBLOCK_SECTION_ID"]}&ELEMENT_ID={$element["IBLOCK_ELEMENT_ID"]}' target='_blank'>
				<img src='http://poligon.info/images/{$element['img']}' alt='{$element['NAME']}' />
			</a>
			<p>{$element['PREVIEW_TEXT']}</p>
			</div>
			-->
		<a target='_parent' href='/catalog/index.php?SECTION_ID={$element["IBLOCK_SECTION_ID"]}&ELEMENT_ID={$element["IBLOCK_ELEMENT_ID"]}'>
					<b>{$element['name']}</b></a>
		</td>
		<td>{$element['article']}</td>
		";
		$query = "SELECT `function_id` FROM `rele_functions_sect`
			WHERE 1
			AND `rele_id` = {$element['id']}";
		$releFuncs = $mysql->select_array($query);
		$funcs = array(); 
		foreach ($releFuncs as $relefunc){
			$funcs[] = $relefunc['function_id']; 
		}
//		print_r($releFuncs);
		foreach ($funcArr as $func){
			if(in_array($func['id'], $funcs))
				print "<td class='center'>+</td>";	
			else
				print "<td class='center'>-</td>";
				//var_dump($releFuncs);
					
			
		}		
		print '</tr>';
	}
	print '</tbody>
	</table>';
}
/**
 * ver 1.0 (since 30.11.11)
 * функция для расчёта цены в карточке товара
 * парсит курс евро с яндекса
 * умножает на переданную цену и возвращает значение 
 * в рублях +2%
 * ver 1.1 (update 13.12.12)
 * добавлено форматировнаие выводной строки
 */
function getPrice($price = 0, $format = array()){
	$rateFile = $_SERVER['DOCUMENT_ROOT'].'/upload/course.euro';
	// Если нет файла с курсом, создаём его 
	if(!file_exists($rateFile)){
		$date = date('d/m/Y');
		$cbrXml = file_get_contents('http://www.cbr.ru/scripts/XML_daily.asp?date_req='.$date);
		$xml = simplexml_load_string($cbrXml);
		$currentRate = array_shift($xml->xpath("/ValCurs/Valute[@ID='R01239']/Value"));		
		file_put_contents($rateFile, str_ireplace(',', '.', $currentRate)) or (print '????');
	}else{
		$currentRate = file_get_contents($rateFile);
	}
//	print $currentRate*$price*1.02.' = ';
	/*
	print '<!-- calc';
	print $currentRate."\n";
	print str_ireplace(',', '.', $price)."\n";
	print $price = $currentRate*str_ireplace(',', '.', $price)."\n";
	print $price*1.02;
	print ' -->';
	*/
	//$price = $currentRate*str_ireplace(',', '.', $price);
	// число может содержать пробелы
	//return round($currentRate*str_ireplace(array(',', ' '), array('.', ''), $price)*1.02);
	$cost = round($currentRate*str_ireplace(array(',', ' '), array('.', ''), $price)*1.05);
	// для разных форматов используем значения массива-аргумента $format
	// порядок тот же что и number_format()
	switch(count($format)){
		case 0: $display_cost = $cost; break;
		case 1: $display_cost = number_format($cost, array_shift($format)); break;
		case 2: $display_cost = number_format($cost, array_shift($format), array_shift($format)); break;
		case 3: $display_cost = number_format($cost, array_shift($format), array_shift($format), array_shift($format)); break;
		default: $display_cost = $cost; break;
	}
	return $display_cost;
	//call_user_func_array('number_format', array_unshift($format, $cost);
	//number_format($cost, 0, ',', ' ');
}


/**
* @ver 1.0
* @since 24.04.2012
* парсит входной html на предмет спецтегов <entity />, заменяет их на данные из БД
* атрибут prop - какое свойство возвращаем (quantity = кол-во на складе, (b_catalog_product.QUANTITY), article = артикул, link = ссылка в карточку товара, onStoreImage - наличие на складе в виде иконки)
* @name - имя элемента (b_iblock_element.NAME)
#* работает с буфферизацией в файле #/public_html/bitrix/templates/poligon_ew/components/bitrix/catalog/catalog_noprice/bitrix/catalog.section.list/.default/template.php
* @ver 1.1 14.05.2012
* теперь буфер включен в /bitrix/header.php и сбрасывается в /bitrix/footer.php	
* @ver 1.2 16.05.2012
* - исправлен баг, не позволявший использовать русские символы в значениях атрибутов из-за виндовой кодировки сайта
* - добавлено значение @prop='jpg', для вывода картинки без логотипа
* - введён @title -- присоединяется к тегам, где это возможно (замена @text, он теперь только внутренний текст) 
* - для изображений (@prop=img|jpg) можно указывать @alt и @title. alt, т.к. обязателен, если отсутвует будет заполнен @name
* - поддержка инлайн стилей css во всех генерируемых html-элементах. 
* @ver 1.3 01.08.2012
* - добавлено значение @prop=showimage_link  -- ссылка на элемент с вертикальной картинки, генерируемой в /classes/showimage.php
* @ver 1.4 04.12.2012
* - добавлен атрибут forn для указания номера шрифта для @prop=showimage_link
DTD
<!ATTLIST entity name CDATA #REQUIRED 
	entity prop (quantity | article | link | section_link | img | jpg | pdf_link | onStoreImage | price | showimage_link) #REQUIRED
	entity text CDATA #IMPLIED
	entity title CDATA #IMPLIED
	entity alt CDATA #IMPLIED
	entity style CDATA #IMPLIED
	entity perc CDATA #IMPLIED
	entity class CDATA #IMPLIED>
*/


function parseForDynamicContent($content = null){
	$mysql = new Mysql();
	//$content = iconv("cp1251", "utf8", $content);
	// извлечём все теги для замены
	preg_match_all('|<entity.*?/>{1}|', $content, $matches, PREG_PATTERN_ORDER);
	//var_dump($matches);
	// перебираем найденные теги
	$data = count($matches[0]);
	foreach($matches[0] as $tag){
	
	//var_dump($tag);
		//$content .= $tag;
		//$tag = iconv("cp1251", "utf-8", $tag);
		// т.к. сайт в убогой cp1251, а xml должен быть utf-8, проиводим перевод
		$_xml = simplexml_load_string(iconv("cp1251", "utf-8", $tag));

		// скидываем в массив все атрибуты, заодно перекодируя их для вывода на сайте
		$xml = array();
		foreach($_xml->attributes() as $attr => $value)
			$xml[$attr] = iconv('utf-8', 'cp1251', $value);
		
		// нужное свойство $xml['prop']; 
		// имя элемента $xml['name'];
		// $xml['text'] -- вставляемый в генерируемую ссылку текст
		$sql = "SELECT 
				el.ID AS el_id,
				prod.QUANTITY AS quantity, 
				el.IBLOCK_SECTION_ID AS section_id,
				art.VALUE AS article,
				img.VALUE AS img,
				pdf.VALUE AS pdf_link,
				price.VALUE AS price
			FROM `b_catalog_product` prod
			LEFT JOIN `b_iblock_element` el ON el.ID = prod.ID
			LEFT JOIN `b_iblock_element_property` art ON art.IBLOCK_ELEMENT_ID = el.ID AND art.IBLOCK_PROPERTY_ID = 16
			LEFT JOIN `b_iblock_element_property` img ON img.IBLOCK_ELEMENT_ID = el.ID AND img.IBLOCK_PROPERTY_ID = 18
			LEFT JOIN `b_iblock_element_property` pdf ON pdf.IBLOCK_ELEMENT_ID = el.ID AND pdf.IBLOCK_PROPERTY_ID = 19
			LEFT JOIN `b_iblock_element_property` price ON price.IBLOCK_ELEMENT_ID = el.ID AND price.IBLOCK_PROPERTY_ID = 69
			WHERE 1 
			AND el.NAME = '".mysql_real_escape_string($xml['name'])."'
			LIMIT 0, 1"; // запрос 

				//$xml['text'] = iconv('utf-8', 'cp1251', $xml['text']);
				//$xml['text'] = mb_convert_encoding($xml['text'], 'cp1251', 'utf-8');
			
		//$data .= "<!-- tag: $tag (@name={$xml['name']}, @text = {$xml['text']}); sql: $sql\n -->";
		$elementData = $mysql->select_array($sql);

		if(count($elementData))
			$elementData = array_shift($elementData);
		
		switch($xml['prop']){
		
			case 'quantity': 
				$content = str_replace($tag, $elementData['quantity'], $content);
				break;
			case 'article': 
				$content = str_replace($tag, $elementData['article'], $content);;
				break;
			case 'link': {
				if(!empty($elementData['el_id']))
					$link = "<a href='/catalog/index.php?SECTION_ID={$elementData['section_id']}&ELEMENT_ID={$elementData['el_id']}'  title='".(isset($xml['title'])?$xml['title']:'')."' style='".(empty($xml['style'])?"":$xml['style'])."'>".(empty($xml['text'])?$xml['name']:$xml['text'])."</a>";
				else 
					$link = $xml['name'];
				$content = str_replace($tag, $link, $content);
			}
				break;
			case 'showimage_link' :{
				if(!empty($xml['font'])){
					$fontParam = "&font={$xml['font']}";
				}
				if($elementData['el_id'])				
					$link = "<a href='/catalog/index.php?SECTION_ID={$elementData['section_id']}&ELEMENT_ID={$elementData['el_id']}'  title='{$xml['title']}' style='{$xml['style']}'><img src='http://poligon.info/classes/showimage.php?".($xml['text']?$xml['text']:$xml['name'])."{$fontParam}' alt='".($xml['alt']?$xml['alt']:$xml['name'])."'/></a>";
				else 
					$link = "<img src='http://poligon.info/classes/showimage.php?".($xml['text']?$xml['text']:$xml['name'])."{$fontParam}' alt='".($xml['alt']?$xml['alt']:$xml['name'])."'/>";
					
				$content = str_replace($tag, $link, $content);
			}
				break;
			case 'section_link': {
				$link = "<a href='/catalog/index.php?SECTION_ID={$elementData['section_id']}' title='{$xml['title']}' style='{$xml['style']}'>".($xml['text']?$xml['text']:$xml['name'])."</a>";
				$content = str_replace($tag, $link, $content);
			}
				break;
			case 'img':{
				$img = "<img src='/images/{$elementData['img']}.img' class='show' alt='".($xml['alt']?$xml['alt']:$xml['name'])."' title='{$xml['title']}' style='{$xml['style']}'/>";
				$content = str_replace($tag, $img, $content);
			}
				break;			
			case 'jpg':{
				$img = "<img src='/images/{$elementData['img']}' class='show' alt='".($xml['alt']?$xml['alt']:$xml['name'])."' title='{$xml['title']}' style='{$xml['style']}'/>";
				$content = str_replace($tag, $img, $content);
			}
				break;
			case 'pdf_link':{
				$link = "<a href='/PDF/{$elementData['pdf_link']}' title='{$xml['title']}' style='{$xml['style']}'>{$xml['text']}</a>";
				$content = str_replace($tag, $link, $content);
			}
				break;
			case 'onStoreImage':{
				if($elementData['quantity']>0){
					$src = 'green';
					$alt = 'Есть на складе';
				}
				else{
					$src = 'grey';
					$alt = 'Нет на складе';
				}
					
				$img = "<img src='/images/{$src}.gif' alt='{$alt}' title='{$alt}' style='width: 10px; height: 10px;' title='".(empty($xml['title'])?"":$xml['title'])."'/>";
				$content = str_replace($tag, $img, $content);
			}
				break;
			case 'price':{
				$price = getPrice($elementData['price']);
				if($xml['perc']){
					$price = $price * $xml['perc'];
				}
				$content = str_replace($tag, round($price), $content);
			}
				break;
			case 'hideImage':{
				$id = md5($elementData['img']);
				$hideImage = "<a href='/catalog/index.php?SECTION_ID={$elementData['section_id']}&ELEMENT_ID={$elementData['el_id']}'  title='{$xml['title']}' style='{$xml['style']}' class='hideImageWrapper' data-image-id='{$id}'>
				".($xml['text']?$xml['text']:$xml['name'])."
				<img src='/images/{$elementData['img']}' id='{$id}' alt='".($xml['alt']?$xml['alt']:$xml['name'])."' class='hideImage'/>
				</a>";
				$content = str_replace($tag, $hideImage, $content);
			}
				break;

			default: break;
		}
	}
	//$content = iconv("utf8", "cp1251", $content);
	$it_is_non_magic_number = '01.04';
	if(date('d.m') == $it_is_non_magic_number){
		$content = firstAprilJoke($content);
	}
	return $content;
}
/**
 * @since 22.03.2013
 * @author Nikolai Gnato aka samizdam
 */
function firstAprilJoke($subject){
	$search = array('аталог');
	$replace = array('ата&#x301;лог');
	return str_replace($search, $replace, $subject);
}






