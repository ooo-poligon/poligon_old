<?php
//set_time_limit(0);
//ini_set( 'error_reporting', E_ERROR );
//ini_set( 'display_errors', 1 );

require( $_SERVER['DOCUMENT_ROOT'] . "/bitrix/php_interface/dbconn.php" );
mysql_connect( $DBHost, $DBLogin, $DBPassword );
mysql_select_db( $DBName );

$sql = "SET CHARACTER_SET_RESULTS=cp1251";
$query = mysql_query($sql);

//$f = fopen( $_SERVER['DOCUMENT_ROOT'] . "/export/{$filename}.xml", "w+" );
$str = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\n";
$str.= "<catalog>\n";
$str .= "\t<sections>\n";
/*выбираем секции каталога*/
$sql_cib = "SELECT * FROM b_catalog_iblock";
$query_cib = mysql_query($sql_cib);
$num_cib = mysql_num_rows ($query_cib);
for($i=0;$i<$num_cib;$i++)
{
$fetch_cib = mysql_fetch_assoc($query_cib);
$sql_sec = "SELECT * FROM b_iblock_section WHERE iblock_id = ".$fetch_cib["IBLOCK_ID"];
$query_sec = mysql_query($sql_sec);
while($fetch_sec = mysql_fetch_assoc($query_sec)){
    $sql_ib = "SELECT * FROM b_iblock WHERE id = {$fetch_cib["IBLOCK_ID"]}";
    $query_ib = mysql_query($sql_ib);
    $fetch_ib = mysql_fetch_assoc($query_ib);
    $fetch_ib["NAME"] = htmlspecialchars($fetch_ib["NAME"]);
    $str .= "\t\t<section iblock_name=\"{$fetch_ib["NAME"]}\" id=\"{$fetch_sec["ID"]}\">";
    $str .= htmlspecialchars($fetch_sec["NAME"]);
    $str .= "</section>\n";
    $sections[] = $fetch_sec["ID"];
    $sections_num ++;
}
//
$sql_etc_page = "SELECT * FROM b_iblock WHERE id = {$fetch_cib['IBLOCK_ID']}";
$query_etc_page = mysql_query($sql_etc_page);
$fetch_etc_page = mysql_fetch_assoc($query_etc_page);

$sql_el = "SELECT * FROM b_iblock_element WHERE iblock_id = {$fetch_cib['IBLOCK_ID']} AND active = 'Y'";
$query_el = mysql_query($sql_el) or die (mysql_error());
$fetch_el = mysql_fetch_assoc($query_el);

$result_det = str_replace('#SITE_DIR#', 'http://www.'.$_SERVER["HTTP_HOST"], $fetch_etc_page['DETAIL_PAGE_URL']);
$result_temp = $result_det;
$result_det = str_replace('#SECTION_ID#', $fetch_el['IBLOCK_SECTION_ID'], $result_det);
$result_det = str_replace('#IBLOCK_ID#', $fetch_el['IBLOCK_ID'], $result_det);
$result_det = str_replace('#ID#', $fetch_el['ID'], $result_det);
$check_det = @fopen("$result_det", "r");

if(!$check_det)
{
    $result_sec = str_replace('#SITE_DIR#', 'http://www.'.$_SERVER["HTTP_HOST"], $fetch_etc_page['SECTION_PAGE_URL']);
    $result_sec = str_replace('#SECTION_ID#', $fetch_el['IBLOCK_SECTION_ID'], $result_sec);
    $result_sec = str_replace('#IBLOCK_ID#', $fetch_el['IBLOCK_ID'], $result_sec);
    $check_sec = @fopen("$result_sec", "r");
    if(!$check_sec) $result_temp = '';
        else
            $result_temp = str_replace('#SITE_DIR#', 'http://www.'.$_SERVER["HTTP_HOST"], $fetch_etc_page['SECTION_PAGE_URL']);
}
else
{
    $result_temp = str_replace('#SITE_DIR#', 'http://www.'.$_SERVER["HTTP_HOST"], $fetch_etc_page['DETAIL_PAGE_URL']);
}
    $etc_url[$fetch_cib['IBLOCK_ID']] = $result_temp;
    unset($result_temp);
    
}
$str .= "\t</sections>\n";
$str .= "\t<catalog_elements>\n";
/*выбираем элементы*/
for($i=0; $i<$sections_num; $i++)
{
$sql_el = "SELECT * FROM b_iblock_element WHERE iblock_section_id = {$sections[$i]} AND active = 'Y'";
$query_el = mysql_query($sql_el) or die (mysql_error());
$num_el = mysql_num_rows($query_el);
for($k=0; $k<$num_el; $k++)
{
$fetch_el = mysql_fetch_assoc($query_el);

$sql_el_pic ="SELECT * FROM b_file WHERE id = {$fetch_el["DETAIL_PICTURE"]}";
$query_el_pic = mysql_query($sql_el_pic);
$fetch_el_pic = @mysql_fetch_assoc($query_el_pic);

$sql_el_pic_pre ="SELECT * FROM b_file WHERE id = {$fetch_el["PREVIEW_PICTURE"]}";
$query_el_pic_pre = mysql_query($sql_el_pic_pre);
$fetch_el_pic_pre = @mysql_fetch_assoc($query_el_pic_pre);

$sql_el_price = "SELECT * FROM b_catalog_price WHERE product_id = {$fetch_el["ID"]}";
$query_el_price = mysql_query($sql_el_price);
$fetch_el_price = mysql_fetch_assoc($query_el_price);

$fetch_el_name = htmlspecialchars($fetch_el["NAME"]);
$fetch_el_ptext = htmlspecialchars($fetch_el["PREVIEW_TEXT"]);
$fetch_el_dtext = htmlspecialchars($fetch_el["DETAIL_TEXT"]);

if($fetch_el_pic["FILE_NAME"] != null) {$picture = '/upload/'.$fetch_el_pic["SUBDIR"].'/'.$fetch_el_pic["FILE_NAME"];}else {$picture = '';}

if($fetch_el_pic_pre["FILE_NAME"] != null) {$picture_pre = '/upload/'.$fetch_el_pic_pre["SUBDIR"].'/'.$fetch_el_pic_pre["FILE_NAME"];}else {$picture_pre = '';}

$str .= "\t\t<element section_id=\"{$fetch_el["IBLOCK_SECTION_ID"]}\">\n";
$str .= "\t\t\t<id>{$fetch_el["ID"]}</id>\n";
$str .= "\t\t\t<name>{$fetch_el_name}</name>\n";
$str .= "\t\t\t<picture>{$picture}</picture>\n";
$str .= "\t\t\t<picture_pre>{$picture_pre}</picture_pre>\n";
$str .= "\t\t\t<price type=\"RUR\">{$fetch_el_price["PRICE"]}</price>\n";
$str .= "\t\t\t<preview_text>{$fetch_el_ptext}</preview_text>\n";
$str .= "\t\t\t<detail_text>{$fetch_el_dtext}</detail_text>\n";
$str .= "\t\t\t<property>\n";
/*собираем свойства элемента*/
$sql_el_prop = "SELECT * FROM b_iblock_element_property WHERE iblock_element_id = {$fetch_el["ID"]}";
$query_el_prop = mysql_query($sql_el_prop);
$num_el_prop = mysql_num_rows($query_el_prop);
for($l=0; $l<$num_el_prop; $l++)
{
$fetch_el_prop = mysql_fetch_assoc($query_el_prop);
$sql_el_prop_val = "SELECT * FROM b_iblock_property WHERE id = {$fetch_el_prop["IBLOCK_PROPERTY_ID"]}";
$query_el_prop_val = mysql_query($sql_el_prop_val);
$fetch_el_prop_val = mysql_fetch_assoc($query_el_prop_val);
$fetch_el_prop_value =  htmlspecialchars($fetch_el_prop["VALUE"]);
$str .= "\t\t\t\t<prop type=\"{$fetch_el_prop["VALUE_TYPE"]}\" code=\"{$fetch_el_prop_val["CODE"]}\" name=\"{$fetch_el_prop_val["NAME"]}\">{$fetch_el_prop_value}</prop>\n";
}

$etc_url[$fetch_el["IBLOCK_ID"]] = str_replace('#SECTION_ID#', $fetch_el['IBLOCK_SECTION_ID'], $etc_url[$fetch_el["IBLOCK_ID"]]);
$etc_url[$fetch_el["IBLOCK_ID"]] = str_replace('#IBLOCK_ID#', $fetch_el['IBLOCK_ID'], $etc_url[$fetch_el["IBLOCK_ID"]]);
$etc_url[$fetch_el["IBLOCK_ID"]] = str_replace('#ID#', $fetch_el['ID'], $etc_url[$fetch_el["IBLOCK_ID"]]);
$etc_url[$fetch_el["IBLOCK_ID"]] = $etc_url[$fetch_el["IBLOCK_ID"]];
$str .= "\t\t\t\t<prop type=\"text\" code=\"etcurl\" name=\"Детальная страница\">".htmlspecialchars($etc_url[$fetch_el["IBLOCK_ID"]], ENT_NOQUOTES)."</prop>\n";

$str .= "\t\t\t</property>\n";
$str .= "\t\t</element>\n";
}
}
$str.= "\t</catalog_elements>\n";
$str.= "</catalog>";

header("Content-Type: application/x-gzip");
header("Content-Encoding: gzip");

//упаковываем строку
$zlib = gzcompress( $str, 9 );
print ($zlib);
//print ($str);
?>
