<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<!DOCTYPE html>
<html>
<!--#################################################################################################################-->
<head>
<?$APPLICATION->ShowHead()?>
<?//$APPLICATION->AddHeadScript();?>
<meta charset=utf-8>
<title><?$APPLICATION->ShowTitle()?></title>
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<link href="<?=$APPLICATION->GetTemplatePath();?>template_styles.css" rel="stylesheet" type="text/css" media="(min-width: 1600px)" />
<link href="<?=$APPLICATION->GetTemplatePath();?>style_1600.css" rel="stylesheet" type="text/css" media="(max-width: 1599px)"  />
<link href="<?=$APPLICATION->GetTemplatePath();?>style_1400.css" rel="stylesheet" type="text/css" media="(max-width: 1399px)"  />
<link href="<?=$APPLICATION->GetTemplatePath();?>style_1280.css" rel="stylesheet" type="text/css" media="(max-width: 1279px)"  />
<link href="<?=$APPLICATION->GetTemplatePath();?>style_1024.css" rel="stylesheet" type="text/css" media="(max-width: 1023px)"  />
<link href="<?=$APPLICATION->GetTemplatePath();?>style_800.css" rel="stylesheet" type="text/css" media="(max-width: 799px)"  />
<link href="<?=$APPLICATION->GetTemplatePath();?>style_640.css" rel="stylesheet" type="text/css" media="(max-width: 639px)"  />
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
<script type="text/javascript" src="/bitrix/templates/poligon_i/js/jquery.min.js"></script>
<script type="text/javascript" src="/bitrix/templates/poligon_i/js/jquery.localscroll.js"></script>
<script type="text/javascript" src="/bitrix/templates/poligon_i/js/jquery.scrollto.js"></script> 
<script type="text/javascript">
$(function($) {$.localScroll({duration: 1000, hash: false }); });
</script>
<!-- Скрипт плавного открытия и закрытия блока -->
<script type='text/javascript'> 
	$(document).ready(function() { 
		$("A#trigger_1").click(function() { 
		// Отображаем скрытый блок 
		$("section#contacts").slideDown();
		return false; // не производить переход по ссылке
	}); 
	}); // end of ready() 
</script>
<script type='text/javascript'> 
	$(document).ready(function() { 
		$("AREA#trigger_2").click(function() { 
		// Скрываем блок 
		$("section#contacts").slideUp(); 
		return false; // не производить переход по ссылке
	}); 
	}); // end of ready() 
</script>
</head>
<!--#################################################################################################################-->
<body>
<header class="top_bar">

<section id="contacts">
	<article>
		<header>
		Адрес: 197376, Санкт-Петербург, ул. Льва Толстого, д. 7, офис 501 <span>(300м от ст.м. Петроградская)</span>
		<!--<br>
		e-mail: <a href="mailto:elcomp@poligon.info">elcomp@poligon.info</a>-->
		</header>
	<div class="contacts_column_left">
	<p>
		ОТДЕЛ ПРОДАЖ <br><span>(выставление счетов и отгрузка <br>по всем направлениям, электронные компоненты,
		<br>
		электротехническая продукция):</span> 
		<br><br>
		Контактные телефоны: <br>(812) 325-4220, <br>(812) 325-6420
	<ul> 	 
		<li>Долгова Инна: <br><a href="mailto:dolgova@poligon.info">dolgova@poligon.info</a>, <br>тел.: (812) 325-64-20 (доб. 115)<br></li> 	 
		<li>Борисовец Елена: <br><a href="mailto:elcomp@poligon.info">elcomp@poligon.info</a>, <br>тел.: (812) 325-64-20 (доб. 114)<br></li>
	</ul>
	</p>
	</div>
	<div class="contacts_column_right">
	<p>
		ТЕХНИЧЕСКАЯ ПОДДЕРЖКА<span> 
		<br>(TELE, RELECO, BENEDICT,
		<br>CITEL, GRAESSLIN, CBI, SONDER, EMKO),
		<br>оптовая поставка электротехнической продукции:</span>
		<br><br>
		Контактные телефоны: <br>(812) 335-3-665, <br>(812) 325-4220
	<ul> 	 
		<li>Евтихиев Дмитрий: <br><a href="mailto:edn@poligon.info">edn@poligon.info</a>, <br>тел.: (812) 325-64-20 (доб. 141)<br></li> 
		<li>Крутень Евгений: <br><a href="mailto:kruten@poligon.info">kruten@poligon.info</a>, <br>тел.: (812) 325-64-20 (доб. 148)<br></li>
		<!--<li>Тихомиров Игорь: <br><a href="mailto:it@poligon.info">it@poligon.info</a>, <br>тел.: (812) 325-64-20 (доб. 145)<br></li>-->
	</ul>
	</p>
	</div>
	<div class="yandex_map"> 
		<script type="text/javascript" charset="utf-8" src="//api-maps.yandex.ru/services/constructor/1.0/js/?sid=mMcHB3adzZU56nSeZMihcmzE0Jlbq5ti&amp;width=480&amp;height=250"></script>
	</div>
	<p class="contacts_hide_button">
		<img src="/bitrix/templates/poligon_i/images/contacts_hide.png" usemap="#contacts_hide_area" />
			<map name="contacts_hide_area">
				<area shape="rect" coords="110,10,480,40" href="#" id="trigger_2" alt="Закрыть панель контактов">
			</map>
	</p>
	</article>
</section>

<section class="top_background">
	<a href="/index.php" class="site_logo">
		<img class="site_logo_image" src="/bitrix/templates/poligon_i/images/logo.gif" alt="Логотип ООО ПОЛИГОН"/>
	</a>
	<nav class="site_menu_container">
		<ul class="site_menu_ul">
			<li class="site_menu_li">
				<a href="/index.php">главная</a>
			</li>
			<li class="site_menu_li">
				<a href="/content/news/">новости</a>
			</li>
			<li class="site_menu_li">
				<a href="/catalog/">каталог</a>
			</li>
			<li class="site_menu_li">
				<a href="/content/articles/">публикации</a>
			</li>
			<li class="site_menu_li_last">
				<a href="#" id="trigger_1">контакты</a>
			</li>
		</ul>
	</nav>
	<p class="phone">
		(812) 325-42-20
	</p>
	
<div class="search">
	<?$APPLICATION->IncludeComponent("bitrix:search.form", "form", Array("PAGE"	=>	"#SITE_DIR#search/index.php"));?>
</div>
<div class="partners_pad"></div>
<div class="partners">
<table class="partners_table">
<tr>
<td>
<a href="#tele_ancor"><img class="partners_logo" src="/images/logo/logo_200/tele_grey.gif" alt="TELE" title="реле времени и контроля из Австрии"

onmouseover="this.src='/images/logo/logo_200/tele.gif';"
onmouseout="this.src='/images/logo/logo_200/tele_grey.gif';"

/></a>
</td>
<td>
<a href="#citel_ancor">  <img class="partners_logo" src="/images/logo/logo_200/citel_grey.gif" alt="CITEL" title="CITEL - устройства молниезащиты"

onmouseover="this.src='/images/logo/logo_200/citel.gif';"
onmouseout="this.src='/images/logo/logo_200/citel_grey.gif';"

/></a>
</td>
<td>
<a href="#benedict_ancor"><img class="partners_logo" src="/images/logo/logo_200/benedict_grey.gif" alt="Benedict" title="Контакторы, пускатели, защита Benedict"

onmouseover="this.src='/images/logo/logo_200/benedict.gif';"
onmouseout="this.src='/images/logo/logo_200/benedict_grey.gif';"

/></a>
</td>
<td>
<a href="#graesslin_ancor"><img class="partners_logo" src="/images/logo/logo_200/graesslin_grey.gif" alt="Graesslin" title="Graesslin - таймеры и фотореле. Сделано в Германии."

onmouseover="this.src='/images/logo/logo_200/graesslin.gif';"
onmouseout="this.src='/images/logo/logo_200/graesslin_grey.gif';"

/></a>
</td>
<td>
<a href="#sonder_ancor"><img class="partners_logo" src="/images/logo/logo_200/sonder_grey.gif" alt="SONDER" title="SONDER - термостаты"

onmouseover="this.src='/images/logo/logo_200/sonder.gif';"
onmouseout="this.src='/images/logo/logo_200/sonder_grey.gif';"

/></a>
</td>
<td>
<a href="#relequick_ancor"><img class="partners_logo" src="/images/logo/logo_200/relequick_grey.gif" alt="RELEQUICK" title="RELEQUICK - интерфейсные реле"

onmouseover="this.src='/images/logo/logo_200/relequick.gif';"
onmouseout="this.src='/images/logo/logo_200/relequick_grey.gif';"

/></a>
</td>
</tr>
<tr>
<td>
<a href="#comat_releco_ancor"> <img class="partners_logo" src="/images/logo/logo_200/comat_releco_grey.gif" alt="COMAT-RELECO" title="COMAT-RELECO - промышленные промежуточные реле"

onmouseover="this.src='/images/logo/logo_200/comat_releco.gif';"
onmouseout="this.src='/images/logo/logo_200/comat_releco_grey.gif';"

/></a>
</td>
<td>
<a href="#emko_ancor"><img class="partners_logo" src="/images/logo/logo_200/emko_grey.gif" alt="EMKO" title="EMKO - температурные датчики и и контроллеры"

onmouseover="this.src='/images/logo/logo_200/emko.gif';"
onmouseout="this.src='/images/logo/logo_200/emko_grey.gif';"

/></a>
</td>
<td>
<a href="#cbi_ancor"><img class="partners_logo" src="/images/logo/logo_200/cbi_grey.gif" alt="CBI" title="Профессиональные автоматические выключатели"

onmouseover="this.src='/images/logo/logo_200/cbi.gif';"
onmouseout="this.src='/images/logo/logo_200/cbi_grey.gif';"

/></a>
</td>
<td>
<a href="#huber_suhner_ancor"><img class="partners_logo" src="/images/logo/logo_200/huber-suhner_grey.gif" alt="HUBER+SUHNER" title="ВЧ-разъемы, оптические компоненты"

onmouseover="this.src='/images/logo/logo_200/huber-suhner.gif';"
onmouseout="this.src='/images/logo/logo_200/huber-suhner_grey.gif';"

/></a>
</td>
<td>
<a href="#farnell_ancor"><img class="partners_logo" src="/images/logo/logo_200/farnell_grey.gif" alt="FARNELL" title="мировой лидер в области поставок электронных компонентов"

onmouseover="this.src='/images/logo/logo_200/farnell.gif';"
onmouseout="this.src='/images/logo/logo_200/farnell_grey.gif';"

/></a>
</td>
<td>
<a href="#vemer_ancor"><img class="partners_logo" src="/images/logo/logo_200/vemer_grey.gif" alt="VEMER" title="производитель промышленных решений для измерения и контроля основных электрических параметров"

onmouseover="this.src='/images/logo/logo_200/vemer.gif';"
onmouseout="this.src='/images/logo/logo_200/vemer_grey.gif';"

/></a>
</td>
<td>
</tr>
</table>
</div>
</section>
</header>
<div class="intro_pad"></div>
<!--#################################################################################################################-->
<section id="work_area">
  