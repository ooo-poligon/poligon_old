<?php


// Creates an instance of the DOMImplementation class
$imp = new DOMImplementation;

// Creates a DOMDocumentType instance
$dtd = $imp->createDocumentType('yml_catalog', '', 'shops.dtd');

// Creates a DOMDocument instance
$dom = $imp->createDocument("", "", $dtd);

// Set other properties
$dom->encoding = 'utf-8';

// Create an empty element
$element = $dom->createElement('yml_catalog');
$element->setAttribute('date', date('Y-m-d H:i'));
// Append the element
$dom->appendChild($element);

$xml = simplexml_import_dom($dom);

$shop = $xml->addChild('shop');
$shop->addChild('name', 'ПОЛИГОН');
$shop->addChild('company', 'ПОЛИГОН, ООО');
$shop->addChild('url', 'http://poligon.info/');
$shop->addChild('platform', 'http://poligon.info/');
$currencies = $shop->addChild('currencies');
$currency = $currencies->addChild('currency');
$currency->addAttribute('id', 'RUR');
$currency->addAttribute('rate', 1);
$currency = $currencies->addChild('currency');
$currency->addAttribute('id', 'EUR');
$currency->addAttribute('rate', 'CBRF');
$currency->addAttribute('plus', '2');

$DBType = "mysql";
$DBHost = "localhost";
$DBLogin = "poliinfo_bitrix";
$DBPassword = "Y2Gd75q";
$DBName = "poliinfo_bitrix";

require_once "{$_SERVER['DOCUMENT_ROOT']}/classes/Mysql.class.php";
$mysql = new Mysql();
// привязка конечных категорий каталога к тому что есть на rlocman.ru
$sections = array('Реле времени/таймер' => array(4996, 160, 4999, 5000, 5128, 5003, 4997, 5002, 5420, 5417, 5418, 5416, 5040), // тут же и лестничные 5420 + цифровые таймеры теле
				'Термостат' => array(5457, 5458, 5536),
				'Фотореле' => array(5454),
				'Счётчик часов' => array(5423, 5041),
				'Устройство плавного пуска' => array(5030, 162),
				);
				
$categories = $shop->addChild('categories');
$parentCategory = $categories->addChild('category', 'Низковольтная аппаратура');
$parentCategory->addAttribute('id', 0);
$offers = $shop->addChild('offers');
$i = 0;

foreach($sections as $name => $subs){
	$i++;
	$sectionName = $name;
	$category = $categories->addChild('category', $name);
	//$category = 
	$category->addAttribute('id', $i);
	$category->addAttribute('parentId', 0);
	//$category = $category->addAttribute('parentId', 0);
	$mysql->insert("SET NAMES UTF8");
	$query = "SELECT 
		`elem`.`ID` AS `ID`,
		`elem`.`NAME` AS `name`,
		`elem`.`PREVIEW_TEXT`,
		`prod`.`QUANTITY`,
		REPLACE(`price`.`VALUE`, ',', '.') AS `PRICE`,
		`sectname`.`NAME` AS `section`,
		`sectname`.ID AS SECTION_ID,
		`image`.`VALUE` AS `picture`,
		`art`.`VALUE` AS `ARTICLE`,
		`produc`.`VALUE` AS `PRODUCER`
		FROM `b_iblock_element` `elem`
			LEFT JOIN `b_catalog_product` `prod` ON `elem`.`ID` = `prod`.`ID` 
			LEFT JOIN `b_iblock_section_element` `sect` ON  `sect`.`IBLOCK_ELEMENT_ID` = `elem`.`ID`
			LEFT JOIN `b_iblock_section` `sectname` ON `sectname`.ID = `sect`.IBLOCK_SECTION_ID
			LEFT JOIN `b_iblock_element_property` `image` ON (`image`.IBLOCK_ELEMENT_ID = `elem`.ID AND `image`.`IBLOCK_PROPERTY_ID` = 18)
			LEFT JOIN `b_iblock_element_property` `art` ON (`art`.IBLOCK_ELEMENT_ID = `elem`.ID AND `art`.`IBLOCK_PROPERTY_ID` = 16)
			LEFT JOIN `b_iblock_element_property` `produc` ON (`produc`.IBLOCK_ELEMENT_ID = `elem`.ID AND `produc`.`IBLOCK_PROPERTY_ID` = 20)
			LEFT JOIN `b_iblock_element_property` `price` ON (`price`.IBLOCK_ELEMENT_ID = `elem`.ID AND `price`.`IBLOCK_PROPERTY_ID` = 69)
			WHERE 1 
			AND `elem`.`IBLOCK_SECTION_ID` IN (".join(', ', $subs).")
			GROUP BY `ID`
			ORDER BY `SECTION_ID`";
			
	$offersArr = $mysql->select_array($query);
	foreach($offersArr as $item){
		$offer = $offers->addChild('offer');
		$offer->addAttribute('id', $item['ID']);
		$url = "http://poligon.info/catalog/index.php?SECTION_ID={$item['SECTION_ID']}&amp;ELEMENT_ID={$item['ID']}";
		$offer->addChild('url', $url);
		$offer->addChild('price', $item['PRICE']);
		$offer->addChild('currencyId', 'EUR');
		$offer->addChild('categoryId', $i);
		$picture = "http://{$_SERVER['SERVER_NAME']}/images/{$item['picture']}";
		$offer->addChild('picture', $picture);
		$name = "{$sectionName} {$item['name']} {$item['PRODUCER']} ({$item['section']})";
		$offer->addChild('name', $name);
		$offer->addChild('description', $item['PREVIEW_TEXT']);
		$offer->addChild('delivery', 'true');
		$offer->addChild('pickup', 'true');
		$offer->addChild('vendor', $item['PRODUCER']);
	}
}
header("Content-type: text/xml; charset=UTF-8;");
//var_dump($offersArr);

// Retrieve and print the document
echo $xml->saveXML();