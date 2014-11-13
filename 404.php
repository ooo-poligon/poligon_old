<?	//header("HTTP/1.1 404 Not Found");	//header("Status: 404 Not Found");
include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/urlrewrite.php');

@define("ERROR_404","Y");header("Status: 404 Not Found");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");CHTTP::SetStatus("404 Not Found");
$APPLICATION->SetTitle("404 Not Found");?> 
<p>Извините, такой страницы не существует, или она временно не доступна. Вы можете связаться с нами с помощью <a href="http://www.poligon.info/content/feedback/">формы обратной связи</a>.</p>
<p><font face="Arial, Helvetica, sans-serif" size="2"><b>Адрес:</b> 197376, Санкт-Петербург, ул. Льва Толстого, д. 7, офис 501(300м от ст.м. Петроградская) 
    <br />
   <b>e-mail:</b> <a href="mailto:elcomp@poligon.info">elcomp@poligon.info</a> 
    <br />
   <b>Сайт:</b> <a href="../../">http://www.poligon.info/</a></font><font face="Arial, Helvetica, sans-serif" size="2"> 
    <br />
   <b>Контактные телефоны(факс):</b> +7 (812) 325-4220, 325-6420</font></p>
<div align="center"><img height="188" width="291" src="../../images/map.gif" /></div>
<p><a href="http://www.poligon.info/content/feedback/"></a></p>
 <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>