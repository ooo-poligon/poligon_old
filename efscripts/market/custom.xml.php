<?php
/**
@since 18.05.2012
@autor Nikolay Gnato
@copiright poligon.info
@ver 1.0
ƒанный скрипт отдаЄт товары из Ѕƒ сайта одного раздела, идентификатор которого указан в адресной строке. 
ƒоктайп: http://poligon.info/efscripts/market/custom.xml.dtd
readme: custom.xml.README
@ver 1.1 (22.05.2012)
добавлено описание раздела, изменена структура. 
@ver 1.2 (23.05.2012)
добавлены доп. изображени€. добавлены проверки параметров запроса, параментр дл€ абсолютных/относительных путей
**/
//error_reporting (E_ALL);
//$EXPORT_ERRORS = array('NOT_FOUND' => );

$DBType = "mysql";
$DBHost = "localhost";
$DBLogin = "poliinfo_bitrix";
$DBPassword = "Y2Gd75q";
$DBName = "poliinfo_bitrix";

require_once "{$_SERVER['DOCUMENT_ROOT']}/classes/Mysql.class.php";
$mysql = new Mysql();

// указан ли ID раздела? 
if(isset($_GET['SECTION_ID']) && $_GET['SECTION_ID'] > 0)
	$sectionId = (int) $_GET['SECTION_ID'];
else
	die('disable SECTION_ID');

// надо ли добавл€ть абсолютный путь? по умолчанию да.
$absolutePath = 1;
if(isset($_GET['PATH']) && $_GET['PATH'] < 1)
	$absolutePath = 0;
	
if($absolutePath > 0){
	$pdfPath = "http://poligon.info/PDF/";
	$imgPath = "http://poligon.info/images/";
}else{
	$pdfPath = "";
	$imgPath = "";
}

$mysql->insert("SET NAMES UTF8");
	
$query = "SELECT 
	`elem`.`ID` AS `ID`,
	`elem`.`NAME` AS `NAME`,
	`elem`.`PREVIEW_TEXT`,
	`elem`.`DETAIL_TEXT`,
	`prod`.`QUANTITY`,
	`pdf`.`VALUE` AS PDF,
	`thesis`.`VALUE` AS THESIS,
	REPLACE(`price_1`.`VALUE`, ',', '.') AS `PRICE_1`,
	REPLACE(`price_10`.`VALUE`, ',', '.') AS `PRICE_10`,
	REPLACE(`price_100`.`VALUE`, ',', '.') AS `PRICE_100`,
	REPLACE(`price_D`.`VALUE`, ',', '.') AS `PRICE_DILER`,
	`sectname`.`NAME` AS `SECTION`,
	`sectname`.`DESCRIPTION` AS `SECTION_DESCRIPTION`,
	`sectname`.ID AS SECTION_ID,
	`p_sect`.NAME AS PARENT_SECTION,
	`image`.`VALUE` AS `PICTURE`,
	`art`.`VALUE` AS `ARTICLE`,
	`produc`.`VALUE` AS `PRODUCER`
	
	FROM `b_iblock_element` `elem`
		LEFT JOIN `b_catalog_product` `prod` ON `elem`.`ID` = `prod`.`ID` 
		LEFT JOIN `b_iblock_section_element` `sect` ON  `sect`.`IBLOCK_ELEMENT_ID` = `elem`.`ID`
		LEFT JOIN `b_iblock_section` `sectname` ON `sectname`.ID = `sect`.IBLOCK_SECTION_ID
		LEFT JOIN `b_iblock_section` `p_sect` ON (`p_sect`.ID = (SELECT `IBLOCK_SECTION_ID` FROM `b_iblock_section` WHERE ID = {$sectionId}))
		LEFT JOIN `b_iblock_element_property` `image` ON (`image`.IBLOCK_ELEMENT_ID = `elem`.ID AND `image`.`IBLOCK_PROPERTY_ID` = 18)
		LEFT JOIN `b_iblock_element_property` `art` ON (`art`.IBLOCK_ELEMENT_ID = `elem`.ID AND `art`.`IBLOCK_PROPERTY_ID` = 16)
		LEFT JOIN `b_iblock_element_property` `thesis` ON (`thesis`.IBLOCK_ELEMENT_ID = `elem`.ID AND `thesis`.`IBLOCK_PROPERTY_ID` = 17)
		LEFT JOIN `b_iblock_element_property` `pdf` ON (`pdf`.IBLOCK_ELEMENT_ID = `elem`.ID AND `pdf`.`IBLOCK_PROPERTY_ID` = 19)
		LEFT JOIN `b_iblock_element_property` `produc` ON (`produc`.IBLOCK_ELEMENT_ID = `elem`.ID AND `produc`.`IBLOCK_PROPERTY_ID` = 20)
		LEFT JOIN `b_iblock_element_property` `price_1` ON (`price_1`.IBLOCK_ELEMENT_ID = `elem`.ID AND `price_1`.`IBLOCK_PROPERTY_ID` = 69)
		LEFT JOIN `b_iblock_element_property` `price_10` ON (`price_10`.IBLOCK_ELEMENT_ID = `elem`.ID AND `price_10`.`IBLOCK_PROPERTY_ID` = 66)
		LEFT JOIN `b_iblock_element_property` `price_100` ON (`price_100`.IBLOCK_ELEMENT_ID = `elem`.ID AND `price_100`.`IBLOCK_PROPERTY_ID` = 67)
		LEFT JOIN `b_iblock_element_property` `price_D` ON (`price_D`.IBLOCK_ELEMENT_ID = `elem`.ID AND `price_D`.`IBLOCK_PROPERTY_ID` = 68)
		
	WHERE 1 
	AND `elem`.`IBLOCK_SECTION_ID` = {$sectionId}
	GROUP BY `ID`
	ORDER BY `SECTION_ID`";
//print $query;
$offersArr = $mysql->select_array($query);

$i = 0;
if(count($offersArr)){
	$dom = new DomDocument('1.0', 'utf-8');
		
	// Creates an instance of the DOMImplementation class
	$imp = new DOMImplementation;

	// Creates a DOMDocumentType instance
	$dtd = $imp->createDocumentType('catalog', '', 'http://poligon.info/efscripts/market/custom.xml.dtd');

	// Creates a DOMDocument instance
	$dom = $imp->createDocument("", "", $dtd);

	// Set other properties
	$dom->encoding = 'utf-8';	
	
	$catalog = $dom->createElement('catalog');
	$catalog = $dom->appendChild($catalog);	
	
	$section = $dom->createElement('section');
	$catalog->appendChild($section);
	
	$section_description = $dom->createCDATASection($offersArr[0]['SECTION_DESCRIPTION']);
	$section->appendChild($section_description);
	
	$section_name = $dom->createAttribute('section_name');
	$section_name->value = $offersArr[0]['PARENT_SECTION'] ." ". $offersArr[0]['SECTION'];
	$section->appendChild($section_name);
	
	$sectionUrl = $dom->createAttribute('section_url');
	$sectionUrl->value = "http://poligon.info/catalog.php?SECTION_ID={$sectionId}";
	$section->appendChild($sectionUrl);
	
	$offers = $dom->createElement('offers');
	$catalog->appendChild($offers);
		
	foreach($offersArr as $item){
		$offer = $dom->createElement('offer');
		$offer = $offers->appendChild($offer);
		$id = $dom->createAttribute('id');
		$id->value = $item['ID'];
		$offer->appendChild($id);
		
		//$name = "{$item['name']}";
		$offer->appendChild($dom->createElement('name', $item['NAME']));
		$offer->appendChild($dom->createElement('article', $item['ARTICLE']));
		
		$url = "http://poligon.info/catalog/index.php?SECTION_ID={$item['SECTION_ID']}&amp;ELEMENT_ID={$item['ID']}";
		$offer->appendChild($dom->createElement('url', $url));

		$picture = $imgPath.$item['PICTURE'];
		$offer->appendChild($dom->createElement('picture', $picture));
		
		
		/* доп. изображени€ */
		$sql = "SELECT VALUE FROM 
			`b_iblock_element_property` prop 
		WHERE 1 
				AND prop.IBLOCK_PROPERTY_ID BETWEEN 55 AND 60
				AND prop.IBLOCK_ELEMENT_ID = {$item['ID']}
		ORDER BY prop.IBLOCK_PROPERTY_ID";
		$addImagesArr = $mysql->select_array($sql);
		if(count($addImagesArr)){
			$addImages = array();
			while($val = array_shift($addImagesArr)){
				$_val = array_shift($addImagesArr);
				$addImages[$val['VALUE']] = $_val['VALUE'];
			}
			unset($addImages[""]);
			$addImagesNode = $offer->appendChild($dom->createElement('add_images'));
			foreach($addImages as $src => $alt){
				$img = $dom->createElement('img');
				$srcAttr = $dom->createAttribute('src');
				$srcAttr->value = $imgPath.$src;
				$img->appendChild($srcAttr);
				$altAttr = $dom->createAttribute('alt');
				$altAttr->value = $alt;
				$img->appendChild($altAttr);
				$addImagesNode->appendChild($img);
			}
		}
		$pdf = $pdfPath.$item['PDF'];
		$offer->appendChild($dom->createElement('pdf', $pdf));

		$offer->appendChild($dom->createElement('price1', $item['PRICE_1']));
		$offer->appendChild($dom->createElement('price10', $item['PRICE_10']));
		$offer->appendChild($dom->createElement('price100', $item['PRICE_100']));
		$offer->appendChild($dom->createElement('price_d', $item['PRICE_DILER']));
		
		$offer->appendChild($dom->createElement('thesis', $item['THESIS']));
		$offer->appendChild($dom->createElement('short_description', $item['PREVIEW_TEXT']));
		
		$full = $dom->createCDATASection($item['DETAIL_TEXT']);
		$html = $dom->createElement('full_description');
		$html = $offer->appendChild($html);
		$html->appendChild($full);
		
		$offer->appendChild($dom->createElement('vendor', $item['PRODUCER']));
		
		/*
		$offer->addChild('html');

		$tmpDom = dom_import_simplexml($xml->shop->offers[$i]->html);
		$tmpDom->appendChild($tmpDom->ownerDocument->createCDATASection($item['DETAIL_TEXT']));
		//$offer->addChild('html', htmlspecialchars($item['DETAIL_TEXT']));

		$offer->addChild('delivery', 'true');
		$offer->addChild('pickup', 'true');
		$offer->addChild('vendor', $item['PRODUCER']);
		*/
		$i++;
	}
	header("Content-type: text/xml; charset=UTF-8;");
	//var_dump($offersArr);

	// Retrieve and print the document
	$dom->normalize();
	$out = $dom->saveXML();
	//$out = 
	echo $out;
	//var_dump($dom);	
}
else
	die("not found items!");