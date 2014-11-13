<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("keywords", "");
$APPLICATION->SetPageProperty("description", "Вся продукция на одной странице, буклет производителя");
$APPLICATION->SetTitle("Интерактивные модели устройств Graesslin: цифровые таймеры, термостаты, хронотермостаты, лестничные таймеры, фотореле");
$APPLICATION->AddHeadString('<link href="/css/articles.css"  type="text/css" rel="stylesheet" />',true);
?>

<style>
div#player {
	float: left;
}	
ul#swf-index{
	float: left;
	text-align: center;
	margin-left: 24px;
}
ul#swf-index li{
	padding: 0;
	border: 1px gray solid;
	border-radius: 5px;
}
ul#swf-index li a{
	font-size: 120%;
	font-weight: bold;
	width: 100%;
	height: 100%;
	display: inline-block;
	text-decoration: none;
	border-bottom: 1px dotted blue;
}
li a.currentSwf{
	background: #f0df04;
}
div#player {
	width: 550px;
	height: 500px;

}
div#player div{
	display: none;
}
div #swf-description p {
	display: none;
	width: 120px;
	float: left;
	margin-left: 12px;
	text-align: center;
	font-style: italic;
	background: silver;
	
}
</style>
<script>
	$(function(){
		$('ul#swf-index li a').bind('click', function(){
			var value = this.hash.substr(1)
			$('div#player div').hide();
			$('div #swf-description p').hide();
			$('div#'+value).show();
			$('div #swf-description p.'+value).show('slow');
			$('ul#swf-index li a').removeClass('currentSwf');
			$(this).addClass('currentSwf');
			return false;
		});
	});
</script>
<p>Выберите устройство в списке и попробуйте управлять им. Если ролик на запустился самостоятельно, попробуйте нажать "Воспроизвести" в контекстном меню плеера. <script type="text/javascript" src="//yandex.st/share/share.js" charset="utf-8"></script>
<div class="yashare-auto-init" data-yashareL10n="ru" data-yashareType="button" data-yashareQuickServices="yaru,lj,moikrug,vkontakte,facebook,twitter,moimir"></div> 
</p>

<div id="player">
<div id="thermio703" style="display: block">
	<embed src="/upload/flash/thermio703.swf" width="550" height="467" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer">  
</div>
<div id="thermioTouch">
	<embed src="/upload/flash/thermioTouch.swf" width="550" height="467" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer"> 
</div>
<div id="thermio603">
	<embed src="/upload/flash/thermio603.swf" width="550" height="467" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer"> 
</div>
<div id="talento371mini">
	<embed src="/upload/flash/talento371mini.swf" width="550" height="467" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer">  
</div>
<div id="trealux210">
	<embed src="/upload/flash/trealux210.swf" width="550" height="467" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer">  
</div>
<div id="trealux510">
	<embed src="/upload/flash/trealux510.swf" width="550" height="467" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer">  
</div>
<!--
<div id="talento_882_pro_v1">
	<embed src="/upload/flash/talento_882_pro_v1.1.swf" width="550" height="467" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer">  
</div>
-->
<div id="feeling">
	<embed src="/upload/flash/feeling.swf" width="550" height="467" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer">  
</div>
<div id="talento_plus_top">
	<embed src="/upload/flash/talento_plus_top.swf" width="550" height="467" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" >
</div>
</div>
<h2 style="text-align: right"> Выберите  устройство: </h2>
<ul id="swf-index">
	<li><a href="#thermio703" class="currentSwf">thermio 703</a></li>
	<li><a href="#thermioTouch">thermio Touch</a></li>
	<li><a href="#thermio603">thermio 603</a></li>
	<li><a href="#talento371mini">talento 371 mini</a></li>
	<li><a href="#trealux210">trealux 210</a></li>
	<li><a href="#trealux510">trealux 510</a></li>
	<!--<li><a href="#talento_882_pro_v1">talento 892</a></li>-->
	<li><a href="#feeling">feeling</a></li>
	<li><a href="#talento_plus_top">talento plus</a></li>
</ul>
<div id="swf-description">
	<p class="thermio703" style="display: block">Комнатный термостат с электронным регулятором и ЖК дисплеем, питание от сети 220. <a href="/catalog/index.php?SECTION_ID=5457&ELEMENT_ID=72441">Полная информация о thermio 703 в карточке товара</a>. </p>
	<p class="thermioTouch">Комнатный термостат с сенсорным экраном, автомномное питание. <a href="/catalog/index.php?SECTION_ID=5457&ELEMENT_ID=72443">Полная информация о thermio Touch</a>. </p>
	<p class="thermio603">Комнатный термостат с электронным регулятором и ЖК дисплеем. Автономное питание от батарей LR06. <a href="/catalog/index.php?SECTION_ID=5457&ELEMENT_ID=72440">Полная информация о thermio 603</a>. </p>
	<p class="talento371mini">Компактный цифровой недельный таймер. Ширина 17,5 мм. Аккамулятор на три года, «холодная» пямять. <a href="/catalog/index.php?SECTION_ID=5417&ELEMENT_ID=72406">Полная информация о talento 371 mini plus</a>. </p>
	<p class="trealux210">Лестничный таймер, для автоматизации освещения и вентиляции в подъездах, коридорах общественных зданий, санузлах, гаражах и т.п. <a href="/catalog/index.php?SECTION_ID=5420&ELEMENT_ID=72417">Полная информация о trealux 210</a>. </p>
	<p class="trealux510">Лестничный таймер, для автоматизации освещения и вентиляции в подъездах, общественных зданиях и т.п. Максимальная мощность нагрузки 3600 Ватт.<a href="/catalog/index.php?SECTION_ID=5420&ELEMENT_ID=72418">Полная информация о trealux 510</a>. </p>
	<!-- 
	<p class="talento_882_pro_v1"><a href="/catalog/index.php?">Полная информация о talento 892</a>. </p>
	-->
	<p class="feeling">Цифровой хронотермостат с недельным таймером. <a href="/catalog/index.php?SECTION_ID=5458&ELEMENT_ID=72450">Полная информация о feeling</a>. </p>
	<p class="talento_plus_top">Цифровой таймер с годовой программой. <a href="/catalog/index.php?SECTION_ID=5417&ELEMENT_ID=72416">Полная информация о talento 892 plus</a>. <b>См. также:</b> <a href="/content/articles/programming-timer.php">программирование цифрового таймера talento plus</a>. </p>
</div>
<br style="clear: both;"/>
<ul class="mark">
	<li><a href="/content/program/delivery.php">Как приобрести устройства, способы доставки</a></li>
	<li><a href="/content/articles/graesslin-review.php">Краткий обзор продукции Graesslin</a></li>
	<li><a href="/PDF/GRAESSLIN/Catalogue_Graesslin_ru.pdf">Полный каталог продукции (рус.)</a></li>
</ul>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>