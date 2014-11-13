<?
/**
@since 24.05.2012
@autor Nikolay Gnato for poligon.info
@ver 1.0 
файл в зависимости от гет-параметров отдаёт данные в формате xml для позиций указанных в csv файле ($_GET['file']) и в соответвии с xsl шаблоном ($_GET['scheme'])
загрузка csv осуществляется через интерфейс на странице http://poligon.info/personal/partner/

*/
error_reporting (E_ALL);
$csv_file = $_GET['file'];
$scheme = $_GET['scheme'];
require_once $_SERVER["DOCUMENT_ROOT"]."/functions.php";
$csv_file_path = $_SERVER["DOCUMENT_ROOT"]."/upload/export-files/".$csv_file;

// сперва проверяем, есть ли в кэше нужный конечный xml: 
$cache_file_path = "cache/{$csv_file}.{$scheme}.xml";
$data_file_path = "data/{$csv_file}.xml";
if(file_exists($cache_file_path)){
	print file_get_contents($cache_file_path);
	exit;
}elseif(file_exists($data_file_path)){ // раз уж не нашлось, смотрим, может есть уже эти данные в xml, чтобы обойтись преобразованием xsl? 
	$xml = simplexml_load_string($content);

	$xsl = new DOMDocument;
	$xsl->load("xsl/{$scheme}.xsl}");

	// Configure the transformer
	$proc = new XSLTProcessor;
	$proc->importStyleSheet($xsl); // attach the xsl rules

	$result = $proc->transformToDoc($xml);
	//header("Content-type: text/html; charset=UTF-8;");
	$result->save($cache_file_path);
	echo $result->saveXml();
	exit;
}else // если ничегошеньки не нашлось, то придётся всё создавать: 
$itemsNamesArr = singe_cell_csv_to_array($csv_file_path);

if(!count($itemsNamesArr))
	die("некоректный исходный csv!");
else
$DBType = "mysql";
$DBHost = "localhost";
$DBLogin = "poliinfo_bitrix";
$DBPassword = "Y2Gd75q";
$DBName = "poliinfo_bitrix";

require_once "{$_SERVER['DOCUMENT_ROOT']}/classes/Mysql.class.php";
$mysql = new Mysql();
$mysql->insert("SET NAMES UTF8");
// всё доп. свойства товаров: 
$sql = "SELECT ID, CODE FROM b_iblock_property WHERE IBLOCK_ID = 4";
$addProps = $mysql->select_array($sql);

$propSelectSql = null;
$propJoinSql = null;

foreach($addProps as $prop){
	$propSelectSql .= ",\n`prop_{$prop['CODE']}`.`VALUE` AS '{$prop['CODE']}'";
	$propJoinSql .= "\nLEFT JOIN `b_iblock_element_property` `prop_{$prop['CODE']}` ON (`prop_{$prop['CODE']}`.IBLOCK_ELEMENT_ID = `elem`.ID AND `prop_{$prop['CODE']}`.`IBLOCK_PROPERTY_ID` = {$prop['ID']})";
	
}
$names = join(",", add_quetes_to_array_elements($itemsNamesArr, "'"));
$sql = "SELECT
	`elem`.ID,
	`elem`.NAME AS NAME,
	`elem`.PREVIEW_TEXT,
	`elem`.DETAIL_TEXT,
	`section`.NAME AS SECTION_NAME,
	`section`.DESCRIPTION,
	`section`.ID AS SECTION_ID,
	`p_section`.ID AS P_SECTION_ID,
	`p_section`.NAME AS P_SECTION_NAME,
	`prod`.`QUANTITY`
	{$propSelectSql} 
	FROM `b_iblock_element` `elem` 
	LEFT JOIN `b_catalog_product` `prod` ON `elem`.`ID` = `prod`.`ID` 
	LEFT JOIN b_iblock_section section ON section.ID = elem.IBLOCK_SECTION_ID
	LEFT JOIN b_iblock_section p_section ON p_section.ID = section.IBLOCK_SECTION_ID
	{$propJoinSql} 
	WHERE 1
	AND `elem`.`NAME` IN ({$names})
	ORDER BY FIELD(`elem`.`NAME`, {$names})
	LIMIT 0, ".count($itemsNamesArr)."";
$itemsArr = $mysql->select_array($sql);

// скидываем всё это добро в xml
$dom = new extDomDocument('1.0', 'utf-8');

$catalog = $dom->appendChild($dom->createElement('catalog'));
$catalog->appendChild($dom->addAttribute('date', date('Y-m-d H:i')));
// добавляем узел категорий и товаров
$categories = $catalog->appendChild($dom->createElement('categories'));
$goods = $catalog->appendChild($dom->createElement('goods'));

$categoriesArr = array();
$imagesPath = "http://{$_SERVER['SERVER_NAME']}/images/";
$pdfPath = "http://{$_SERVER['SERVER_NAME']}/PDF/";
foreach($itemsArr as $item){
	// перебираем заоодно все разделы (категории), исключая заодно дубли из массива
	$categoriesArr[$item['SECTION_ID']] = array('NAME' => $item['SECTION_NAME'],
												'DESCRIPTION' => $item['DESCRIPTION'],
												'P_SECTION_ID' => $item['P_SECTION_ID'],
												'P_SECTION_NAME' => $item['P_SECTION_NAME']);


	$itemNode = $goods->appendChild($dom->createElement('item'));
	$itemNode->appendChild($dom->addAttribute('id', $item['ID']));
	$itemNode->appendChild($dom->addAttribute('category_id', $item['SECTION_ID']));
	$itemNode->appendChild($dom->addAttribute('name', $item['NAME']));	
	$itemNode->appendChild($dom->addAttribute('preview_text', $item['PREVIEW_TEXT']));
	$itemNode->appendChild($dom->addAttribute('article', $item['article']));
	$itemNode->appendChild($dom->addAttribute('image', $imagesPath.$item['link']));
	$itemNode->appendChild($dom->addAttribute('pdf', $pdfPath.$item['pdf']));
	$itemNode->appendChild($dom->addAttribute('producer_full', $item['producer_full']));
	$itemNode->appendChild($dom->addAttribute('producer_abbr', $item['producer_abbr']));
	$itemNode->appendChild($dom->addAttribute('srok', $item['srok']));
	$itemNode->appendChild($dom->addAttribute('base', str_replace(',', '.', $item['BASE'])));
	$itemNode->appendChild($dom->addAttribute('retail', str_replace(',', '.', $item['RETAIL'])));
	$itemNode->appendChild($dom->addAttribute('wholesale', str_replace( ',', '.', $item['WHOLESALE'])));
	$itemNode->appendChild($dom->addAttribute('dealer', str_replace(',', '.', $item['DEALER'])));
	$itemNode->appendChild($dom->addAttribute('quantity', $item['QUANTITY']));
	//$thesis = unserialize(str_replace("\n", "", $item['preview_text']));
	//var_dump($thesis);
	//die;
	//$itemNode->appendChild($dom->addAttribute('thesis', $thesis["TEXT"]));
	
	$itemNode->appendChild($dom->addAttribute('url', "http://{$_SERVER['SERVER_NAME']}/catalog.php?SECTION_ID={$item['SECTION_ID']}&amp;ELEMENT_ID={$item['ID']}"));
	
	$cdata = $dom->createCDATASection(str_replace('&', '&amp;', parseForDynamicContent($item['DETAIL_TEXT'])));
	$html = $dom->createElement('detail_text');
	$html = $itemNode->appendChild($html);
	$html->appendChild($cdata);
	
	$addImagesNode = $itemNode->appendChild($dom->createElement('add_images'));
	if($item['img1'] != null){
		$img = $addImagesNode->appendChild($dom->createElement('img'));
		$img->appendChild($dom->addAttribute('src', $imagesPath.$item['img1']));
		$img->appendChild($dom->addAttribute('alt', $item['imgTitle1']));
	}
	if($item['img2'] != null){
		$img = $addImagesNode->appendChild($dom->createElement('img'));
		$img->appendChild($dom->addAttribute('src', $imagesPath.$item['img2']));
		$img->appendChild($dom->addAttribute('alt', $item['imgTitle2']));
	}
	if($item['img3'] != null){
		$img = $addImagesNode->appendChild($dom->createElement('img'));
		$img->appendChild($dom->addAttribute('src', $imagesPath.$item['img3']));
		$img->appendChild($dom->addAttribute('alt', $item['imgTitle3']));
	}
	
	
	//$itemNode->appendChild($dom->addAttribute('detail_text', $item['DETAIL_TEXT']));
}

/* узел с категориями */

// подгружаем привязку к категориям elec'a
$elecRubArr = unserialize(file_get_contents('poligon-elec.srlz'));
//var_dump($categoriesArr);
//die;
//die;
foreach($categoriesArr as $id => $category){
	$categoryNode = $categories->appendChild($dom->createElement('category'));
	$categoryNode->appendChild($dom->addAttribute('id', $id));
	$categoryNode->appendChild($dom->addAttribute('parent_id', $category['P_SECTION_ID']));
	
	if(empty($elecRubArr[$id]))
		$elec_id = 1;
	else
		$elec_id = $elecRubArr[$id];
	$categoryNode->appendChild($dom->addAttribute('elec_id', $elec_id));
	
	$categoryNode->appendChild($dom->addAttribute('name', $category['NAME']));
	
	$cdata = $dom->createCDATASection($category['DESCRIPTION']);
	$html = $dom->createElement('description');
	$html = $categoryNode->appendChild($html);
	$html->appendChild($cdata);
}
$category = array_shift($categoriesArr);
$parentCategoryNode = $categories->appendChild($dom->createElement('category'));
$parentCategoryNode->appendChild($dom->addAttribute('id', $category['P_SECTION_ID']));
$parentCategoryNode->appendChild($dom->addAttribute('name', $category['P_SECTION_NAME']));

//var_dump($categoriesArr);
//die;
// заголовок
switch((isset($_GET['out'])?$_GET['out']:'xml')){
	case 'csv': header("Content-type: text/plain; charset=CP1251;"); break;
	case 'html': header("Content-type: text/html; charset=UTF-8;"); break;
	case 'xml': header("Content-type: text/xml; charset=UTF-8;"); break;
	default: header("Content-type: text/xml; charset=UTF-8;");
}

if($scheme == 'data'){
	echo $dom->saveXml();
	die;
}

$xsl = new DOMDocument;
$xsl->load("{$_SERVER['DOCUMENT_ROOT']}/efscripts/export/xsl/{$scheme}.xsl");

// Configure the transformer
$proc = new XSLTProcessor;
$proc->importStyleSheet($xsl); // attach the xsl rules

$result = $proc->transformToXML($dom);
//echo $dom->saveXml();
echo $result;

/**
* расширения класса DomDocument, для более лаконичных функций.
сахарок'c =)
**/
class extDomDocument extends DomDocument{
	/* добавляет аттрибут к ноде 
	$dom->appendChild($node->addAttribute('name', 'string'));
	*/
	function addAttribute($name, $value = null){
		$attr = $this->createAttribute($name);
		$attr->value = $value;
		return $attr;
	}
}

function add_quetes_to_array_elements($array, $q){
	foreach($array as $key => $value){
		$array[$key] = $q.$value.$q;
	}
	return $array;
}
/* 
	простая функция, возвращает сзначения из елинственного столбца csv файла в виде массива
*/ 
function singe_cell_csv_to_array($filename){
	$handle = fopen($filename, "r");
	$row = 0;
	$array = array();
	while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		$num = count($data);
		//echo "<p> $num полей в строке $row: <br /></p>\n";
		$row++;
		for ($c=0; $c < $num; $c++) {
			$array[] = $data[$c];
		}
	}
	fclose($handle);
	return $array;
}
?>