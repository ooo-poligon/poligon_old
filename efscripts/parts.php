<?php
/*
 * Скрипт выводит список компонент для соответствующей категории
 */

$host = 'localhost';      
$user = 'poliinfo_bitrix';
$pass = 'Y2Gd75q';
$base = 'poliinfo_bitrix';

function getSubCategories($cat_id)
{
    global $categories;
    
    $sql = "SELECT id FROM b_iblock_section WHERE iblock_section_id = '" . mysql_escape_string($cat_id) . "' AND active = 'Y'";
    $result = mysql_query($sql);
    if(mysql_num_rows($result) == 0){
        return;
    }else{
        while($resarr = mysql_fetch_assoc($result)){
            $categories[] = $resarr['id'];
            getSubCategories($resarr['id']);
        }
    }
}

// Соединение с базой данных
if($lnk = mysql_connect($host, $user, $pass)){
    if(!isset($_GET['section_id'])){
        exit('Не указан section_id'); // section_id - обязательный параметр
    }
    // Установка текущей базы данных
    mysql_select_db($base, $lnk);
    
    // Получаем подкатегории для данной категории
    $categories = array($_GET['section_id']);
    getSubCategories($_GET['section_id']);
    // Теперь получаем товары для полученных категорий
    $parts = array();
    
    // Подготавливаем листинг
    $on_page = 980; // Почему именно 980? Видимо как-то связано с золотым сечением
    $page_num = isset($_GET['p']) ? $_GET['p'] : 1;
    $limit = 'LIMIT ' . strval($on_page * ($page_num - 1)) . ', ' . strval($on_page);
    
    $sql = "SELECT
                SQL_CALC_FOUND_ROWS bsc.item_id,
                IF(biep_artnum.value IS NULL, bsc.title, CONCAT(bsc.title, ' (', biep_artnum.value, ')')) as name
            FROM 
                b_search_content bsc
                LEFT JOIN b_iblock_element AS bie ON (bie.id = bsc.item_id)
                LEFT JOIN b_iblock_element_property AS biep_artnum ON (biep_artnum.iblock_element_id = bsc.item_id AND biep_artnum.iblock_property_id = '16')
            WHERE
                bsc.module_id = 'iblock'
                AND param1 = 'catalog'
                AND param2 = 4
                AND bie.iblock_section_id IN ('" . implode('\',\'', $categories) . "')
            $limit";

    $result = mysql_query($sql);
    while($resarr = mysql_fetch_assoc($result)){
        $parts[] = $resarr;
    }
    
    // Общее количество компонент в категориях (для листинга)
    $result = mysql_query("SELECT FOUND_ROWS()");
    $total = mysql_result($result, 0);
    
    // Теперь выводим полученные компоненты странным образом - в виде таблицы с 7-ю столбцами по 140 штук в каждом
    $parts_html = '
        <table style="border:0px;font-size:14px" cellspacing="15">
            <tr>
                <th valign="top" align="left">Mftrs. List No.';
                    foreach($parts as $key => $part){
                        if($key > 0 && $key % 140 == 0){
                            $parts_html .= '</th><th align="left" valign="top">Mftrs. List No.';
                        }
                    }
    $parts_html .= '
                </th>
            </tr>
            <tr>
                <td valign="top">';
    foreach($parts as $key => $part){
        if($key > 0 && $key % 140 == 0){
            $parts_html .= '</td><td valign="top">';
        }
        $parts_html .= $part['name'] . '<br>';
    }
    $parts_html .= '
                </td>
            </tr>
        </table>';
    
    // Ссылки для листинга
    $listing_html = '';
    if($total > 980){
        for($i = 1; $i <= ceil($total / $on_page); $i++){
            if($i == $page_num){
                $listing_html .= $i . ' ';
            }else{
                $listing_html .= '<a href="http://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?section_id=' . $_GET['section_id'] . '&p=' . $i . '">' . $i . '</a> ';
            }
        }
    }
    
    mysql_close($lnk);
}else{
    header("HTTP/1.1 500 Internal Server Error");
    print "<h1>500 Internal Server Error</h1>" . "Could not connect to database";
    exit;
}
?>
<html>
<head>
    <title>Каталог</title>
</head>
<body>
    <?=$parts_html?>
    <div style="margin:0 0 10px 14px;"><?=$listing_html?></div>
</body>
</html>
