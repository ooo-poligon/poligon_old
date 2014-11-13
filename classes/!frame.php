<?php
/** скрипт вызывается во вреймя, т.к. 
 * битрикс не позволяет включать php код 
 * в тексте страниц компонентов (каталога в данном случае.)
 * Возможно стоит расширить данный файл, создать спец. файл для таких фреймов. 
 * @var unknown_type
 *//*
$DBType = "mysql";
$DBHost = "localhost";
$DBLogin = "poliinfo_bitrix";
$DBPassword = "Y2Gd75q";
$DBName = "poliinfo_bitrix"; 
//require_once $_SERVER['DOCUMENT_ROOT']."/functions.php";
?>
<html>
<head>
<link href="/bitrix/templates/poligon_ew/styles.css" type="text/css" rel="stylesheet" />
<link href="/bitrix/templates/poligon_ew/template_styles.css" type="text/css" rel="stylesheet" />
<script src="http://yandex.st/jquery/1.6.4/jquery.min.js"></script>
<script src="/bitrix/templates/poligon_ew/js/screen.js"></script>
</head>
<body id="frame">

<?php 
print(date("c");
//enyaTable();
print(date("c");
?>

</body>
</html>


*/
echo date("l dS of F Y h:i:s A");