<?php
/**
*	������ �����-����� ��� elec.ru � xml
*	ver 0.91 ��� ������� �����, ���������� ������������� 
*	$_GET['categoryId'] � �������� ��������������� ���������� ��������
*	�� �������� �� �����. "���������� �����", 
* 	�� ������� ������� ���� �� ���������, ������������������ �����������.
*	28/09/2011	������� ������.
*	ver 0.95 ���� ������ ������ �������. 
*	30/09/2011 ������� ������.
*	ver 1.0 ���������� ������: 
	- ����������� ���� � ����, 
	- ���������� ������ 
	- ������������ �����������
	ver 1.01
	elec �� ����� �������� ��� ���� ������� � ����, ������� ������-�� � ������.
*	03/10/2011 ������� ������.
*	ver 1.02
*	��������� ���������� ORDER BY `SEECTION_ID`
*	����� ������� ������� ���������� ����� ENYA 
*	��� ���� �������.
	05.10.2011 ������� ������
	ver 1.03
	��������������� ����, ������� ��������� ������ �� � `b_catalog_price`, 
	� ��� �������� �������� � `b_iblock_element_property`. 
	+���������� ��������, �� �������� �� ������. 
	13.12.2011
	ver. 1.04
	�������� ������� ���������, ��� ���� ����. 
**/
//error_reporting(E_ALL);
$DBType = "mysql";
$DBHost = "localhost";
$DBLogin = "poliinfo_bitrix";
$DBPassword = "Y2Gd75q";
$DBName = "poliinfo_bitrix";

require_once "{$_SERVER['DOCUMENT_ROOT']}/classes/Mysql.class.php";
$mysql = new Mysql();


// ������� ��� ������������� ��������������� ��������� �������� ����� � ��������� ����� poligon.info 
$elecToPoli = array(2368 => array(
							'sections' => array(4996, 160, 4999, 5000, 5128, 5003, 4997, 5002, 5420, 5417),
							'title' => '���� �������'), // ��� �� � ���������� 5420
					2373 => array(
							'sections' => array(5454),
							'title' => '��������'),
					2403 => array('sections' => array(5423, 5041), // �������� + ��������
							'title' => '������� ����� ���������'),
					1709 => array('sections' => array(5457, 5458), //���������� � ��������������� Graesslin
							'title' => '���������'), 
					4169 => array('sections' => array(5030, 162, 5029, 163), // ��� TELE: TSG, MSG, EUROSTART, ESG
							'title' => '���������� �������� �����'
					),
					1893 => array('sections' => array(158, 4988, 4989, 4990, 4991), // �� TELE GAMMA, ENYA, KAPPA, TREND, OCTO
							'title' => '���� ��������'
					),					
					2405 => array('sections' => array(4993), // ��� TELE GAMMA
							'title' => '���� �������� ��������'
					),
					2370  => array('sections' => array(5020, 5021, 5526, 5025), // ������������� ���� TELE: ����� RA - 2��, ����� RM - 4��, ����� RT, ����� RP
							'title' => '������������� ����'
					),				/*	
					1702  => array('sections' => array(), // CITEL
							'title' => '���������� ������ �� ���������� ��������������'
					),*/
				);


if(key_exists($_GET['categoryId'], $elecToPoli))
	$categoryId = (int) $_GET['categoryId'];
else 
	die('empty requst!');
	
$sectionId = $elecToPoli[$categoryId]['sections'];
$whereSection = null;
if(count($sectionId)>1)
	$whereSection = join(', ', $sectionId);
else 
	$whereSection = array_shift($sectionId);

//$whereSection = substr($whereSection, 0, -2);

$query = "SELECT 
	`elem`.`ID` AS `ID`,
	`elem`.`NAME` AS `NAME`,
	`elem`.`PREVIEW_TEXT`,
	`prod`.`QUANTITY`,
	`price`.`VALUE` AS `PRICE`,
	`sectname`.`NAME` AS `section`,
	`sectname`.ID AS SECTION_ID,
	`image`.`VALUE` AS `IMG`,
	`art`.`VALUE` AS `ARTICLE`,
	`produc`.`VALUE` AS `PRODUCER`
	FROM `b_iblock_element` `elem`
			LEFT JOIN `b_catalog_product` `prod` ON `elem`.`ID` = `prod`.`ID` 
			/* LEFT JOIN `b_catalog_price` `price` ON `price`.`PRODUCT_ID` = `prod`.`ID` */
			LEFT JOIN `b_iblock_section_element` `sect` ON  `sect`.`IBLOCK_ELEMENT_ID` = `elem`.`ID`
			LEFT JOIN `b_iblock_section` `sectname` ON `sectname`.ID = `sect`.IBLOCK_SECTION_ID
			LEFT JOIN `b_iblock_element_property` `image` ON (`image`.IBLOCK_ELEMENT_ID = `elem`.ID AND `image`.`IBLOCK_PROPERTY_ID` = 18)
			LEFT JOIN `b_iblock_element_property` `art` ON (`art`.IBLOCK_ELEMENT_ID = `elem`.ID AND `art`.`IBLOCK_PROPERTY_ID` = 16)
			LEFT JOIN `b_iblock_element_property` `produc` ON (`produc`.IBLOCK_ELEMENT_ID = `elem`.ID AND `produc`.`IBLOCK_PROPERTY_ID` = 20)
			LEFT JOIN `b_iblock_element_property` `price` ON (`price`.IBLOCK_ELEMENT_ID = `elem`.ID AND `price`.`IBLOCK_PROPERTY_ID` = 69)
			WHERE 1 
			/*
			AND `image`.`IBLOCK_PROPERTY_ID` = 18
			AND `art`.`IBLOCK_PROPERTY_ID` = 16
			AND `produc`.`IBLOCK_PROPERTY_ID` = 20
			AND `price`.`IBLOCK_PROPERTY_ID` = 69
			*/
			AND `elem`.`IBLOCK_SECTION_ID` IN ({$whereSection})
			GROUP BY `ID`
			ORDER BY `SECTION_ID`";


$offersArr = $mysql->select_array($query);
//print mysql_error();
$xmlContent = null;
header("Content-Type: text/xml; charset=utf-8");
//2009-09-01 20:17:57
//var_dump($offersArr);
$xmlContent .= '<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE elec_market SYSTEM "pricelist.dtd">
<elec_market date="'.date('Y-m-d H:i:s').'">
	<currencies>
		<currency id="RUR"/>
		<currency id="USD" rate="CBRF"/>
		<currency id="EUR" rate="CBRF" plus="2"/>
	</currencies>
	<categories>
		<category id="1" rubricaId="'.$categoryId.'" unit="PCE" currencyId="EUR">'.$elecToPoli[$categoryId]['title'].'</category>
	</categories>
	<offers>'."\n";
	
foreach ($offersArr as $offer)
{
	$keyword = "{$elecToPoli[$categoryId]['title']} {$offer['PRODUCER']} {$offer['SECTION_NAME']}";
	$url = "http://{$_SERVER['SERVER_NAME']}/catalog/index.php?SECTION_ID={$offer['SECTION_ID']}&amp;ELEMENT_ID={$offer['ID']}";
	$price = str_replace(',', '.', $offer['PRICE']);
	$img = "http://poligon.info/images/{$offer['IMG']}";
	$xmlContent .="	<offer id=\"{$offer['ID']}\">\n";
	$xmlContent .="		<categoryId>1</categoryId>\n";
	$xmlContent .="		<keyword>$keyword</keyword>\n";
	$xmlContent .="		<title>{$offer['NAME']}</title>\n";
	$xmlContent .="		<url>$url</url>\n";
	$xmlContent .="		<price>{$price}</price>\n";	
	$xmlContent .="		<artno>{$offer['ARTICLE']}</artno>\n";
	$xmlContent .="		<currencyId>EUR</currencyId>\n";
	$xmlContent .="		<quantity>{$offer['QUANTITY']}</quantity>\n";
	$xmlContent .="		<picture>$img</picture>\n";
	$xmlContent .="		<vendor>{$offer['PRODUCER']}</vendor>\n";
	$xmlContent .="		<tizer>{$offer['PREVIEW_TEXT']}</tizer>\n";
	$xmlContent .="	</offer>\n";
}
$xmlContent .= "	</offers>\n";
$xmlContent .= "</elec_market>";
//var_dump($xmlContent);
//var_dump(iconv('WINDOWS-1251', 'UTF-8', $xmlContent));
//var_dump();
print iconv('WINDOWS-1251', 'UTF-8', $xmlContent);
//print $xmlContent;







