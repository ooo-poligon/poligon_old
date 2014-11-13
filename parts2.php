<?php
/*
 * Скрипт выводит список компонент для соответствующей категории
 */

$host = 'localhost';      
$user = 'poliinfo_bitrix';
$pass = 'Y2Gd75q';
$base = 'poliinfo_bitrix';

// Функция получения подкатегорий для категории (рекурсивная)
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

// Функция получения родительских категорий (рекурсивная)
function getParentCategories($cat_id)
{
    global $parents;
    
    $sql = "SELECT iblock_section_id, name FROM b_iblock_section WHERE id = '" . mysql_escape_string($cat_id) . "' AND active = 'Y'";
    $result = mysql_query($sql);
    $resarr = mysql_fetch_assoc($result);
    //var_dump($resarr);
    if($resarr['iblock_section_id'] == null){
        $parents[] = array($cat_id, $resarr['name']);
        return;
    }else{
        $parents[] = array($cat_id, $resarr['name']);
        getParentCategories($resarr['iblock_section_id']);
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
    
    // Получаем родительские категории
    $parents = array();
    getParentCategories($_GET['section_id']);
    $parents = array_reverse($parents);
    
    // Теперь получаем товары для полученных категорий
    $parts = array();
    
    // Подготавливаем листинг
	
	$on_page = 840; // Число позиций на страницу
	
	if (isset($_GET['all']) && $_GET['all']==1){ // Проверяем, надо ли выводить сразу все позиции
		$flag4all=1;
		$limit = '';
	}else{
		$flag4all=0;
		$page_num = isset($_GET['p']) ? $_GET['p'] : 1;
		$limit = 'LIMIT ' . strval($on_page * ($page_num - 1)) . ', ' . strval($on_page);
	}
	
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
                AND bie.iblock_section_id IN ('" . implode('\',\'', $categories) . "') order by name
            $limit";
    $result = mysql_query($sql);
    while($resarr = mysql_fetch_assoc($result)){
        $parts[] = $resarr;
    }
    
    // Общее количество компонент в категориях (для листинга)
    $result = mysql_query("SELECT FOUND_ROWS()");
    $total = mysql_result($result, 0);
    $parts_html ='';
	while (count($parts)>0){
    // Теперь выводим полученные компоненты странным образом - в виде таблицы с 6-ю столбцами по 140 штук в каждом
    $parts_html .= '
        <table width="100%" border=1 align="center" cellpadding="2" cellspacing="1" bordercolor="#FFFFFF" frame=box rules=all>
            <tr align="left" bordercolor="#CCCCCC">
                <td nowrap><span class="style4">Mftrs. List No.<br></span>';
					$parts_in_table=0;
                    foreach($parts as $key => $part){
                        if($parts_in_table > 0 && $parts_in_table % 140 == 0){
							$parts_html .= '</td><td nowrap><span class="style4">Mftrs. List No.<br></span>';
                        }
						$parts_in_table++;
						if ($parts_in_table >= $on_page){
							break;
						}
                    }
    $parts_html .= '
                </td>
            </tr>
            <tr valign="top" bordercolor="#CCCCCC">
                <td rowspan="148" nowrap><span class="style13">';
	$parts_in_table=0;
    while($part = array_shift($parts)){
        if($parts_in_table > 0 && $parts_in_table % 140 == 0){
            $parts_html .= '</td><td rowspan="148" nowrap><span class="style13">';
        }
        $parts_html .= $part['name'] . '<br>';
		$parts_in_table++;
		if ($parts_in_table >= $on_page){
			break;
		}
    }
    $parts_html .= '
                </td>
            </tr>
        </table><br>';
    }
    // Ссылки для листинга
    $listing_html = '';
	if ($flag4all==0){
    if($total > $on_page){
        for($i = 1; $i <= ceil($total / $on_page); $i++){
            if($i == $page_num){
                $listing_html .= $i . ' ';
            }else{
                $listing_html .= '<a style="color:#0000AA;text-decoration:underline;" href="http://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?section_id=' . $_GET['section_id'] . '&p=' . $i . '">' . $i . '</a> ';
            }
        }
    }
	}
    
    // Разбрасываем крошки..
    $breadcrumbs = '';
	$breadcrumbs2 ='';
    foreach($parents as $key => $parent){
        if($key == (sizeof($parents) - 1)){
            $breadcrumbs .= '<span>' . $parent[1] . '</span> ';
			$breadcrumbs2 .= $parent[1];
        }else{
            $breadcrumbs .= '<a style="font-weight:bold;cursor:pointer;color:#0000AA" href="http://www.poligon.info/catalog/index.php?SECTION_ID=' . $parent[0] . '">' . $parent[1] . '</a><span style="margin: 0 10px">&gt;</span>';
			$breadcrumbs2 .= $parent[1] . ' &gt; ';
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
<title><?=$breadcrumbs2?></title>
<meta name="description" content="Полигон - поставка отечественных и импортных  электронных компонентов от штуки">
<meta name="ROBOTS" content="INDEX,FOLLOW">
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
<LINK REL=STYLESHEET TYPE="text/css" HREF="/styles.css">
<SCRIPT>
<!-- ;
if(800 >= screen.width) {
	document.write("<STYLE type=\'text/css\'><!-- BODY TABLE {font-size:9pt;} --></STYLE>");
}

// end hide -->
</SCRIPT>
</head>
<body marginheight="0" marginwidth="0" topmargin="0" leftmargin="0">
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td>

<table width="100%" height="71" border="0" cellpadding="0" cellspacing="0" background="/images/soffer_bg1.jpg">
  <tr>
    <td width="50%">&nbsp;</td>

    <td><DIV class=pre><span class="style9">(812) </span><span class="style9"></span><span class="style8">335-36-65</span><A href="/content/contacts/"><br>
          <span class="style3">все контакты</span></A></DIV>
    </td>
  </tr>
</table>

 
<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" bgcolor="#FFFFFF">
  <tr> 
    <td height="15" bgcolor="#B4B4B4" colspan="4" align="left">
        <font class=m4>
            <?=$breadcrumbs?>
        </font>
    </td>
  </tr>
</table>



<?=$parts_html?>
<div style="margin:0 0 10px 14px;"><?=$listing_html?></div>
<a style="color:#00A" href="http://www.poligon.info/catalog/index.php?SECTION_ID=<?
if ($_GET['section_id']==5205 or $_GET['section_id'] == 5232){
	echo "13";
}else{
	echo $_GET['section_id'];
}
?>">Вернуться в каталог</a><br><br>


По вопросу приобретения изделий: Компания ООО «ПОЛИГОН».<br>
197376, Санкт-Петербург, ул. Льва Толстого, д.7, оф 501<br>
(812) 3353665, 3254220
</td>

  </tr>
</table>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-12562837-1");
pageTracker._trackPageview();
} catch(err) {}</script></body>
</html>
