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
		<link href="/bitrix/templates/poligon_i/css/styles.css" rel="stylesheet" type="text/css" />
		<link href="/bitrix/templates/poligon_ibx_tele/css/template_styles.css"        rel="stylesheet" type="text/css" media="(min-width: 1600px)" />
		<link href="/bitrix/templates/poligon_ibx_tele/css/resolutions/style_1600.css" rel="stylesheet" type="text/css" media="(max-width: 1599px)" />
		<link href="/bitrix/templates/poligon_ibx_tele/css/resolutions/style_1400.css" rel="stylesheet" type="text/css" media="(max-width: 1399px)" />
		<link href="/bitrix/templates/poligon_ibx_tele/css/resolutions/style_1280.css" rel="stylesheet" type="text/css" media="(max-width: 1279px)" />
		<link href="/bitrix/templates/poligon_ibx_tele/css/resolutions/style_1024.css" rel="stylesheet" type="text/css" media="(max-width: 1023px)" />
		<link href="/bitrix/templates/poligon_ibx_tele/css/resolutions/style_800.css"  rel="stylesheet" type="text/css" media="(max-width: 799px)"  />
		<link href="/bitrix/templates/poligon_ibx_tele/css/resolutions/style_640.css"  rel="stylesheet" type="text/css" media="(max-width: 639px)"  />
		<link href="/css/jquery.lightbox-0.5.css" rel="stylesheet" type="text/css" />
		<script src="/js/jquery.lightbox-0.5.pack.js"></script>
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
		<script type="text/javascript" src="/bitrix/templates/poligon_i/js/jquery.min.js"></script>
		<script type="text/javascript" src="/bitrix/templates/poligon_i/js/jquery.localscroll.js"></script>
		<script type="text/javascript" src="/bitrix/templates/poligon_i/js/jquery.scrollto.js"></script>
		<script type="text/javascript" src="/bitrix/templates/poligon_i/js/height.js"></script>
		<script type="text/javascript" src="/bitrix/templates/poligon_i/js/screen.js"></script>
		<script type="text/javascript" src="/bitrix/templates/poligon_i/js/poligon_i_scripts.js"></script>

<script>
	jQuery(function(){
	    jQuery("a.show").lightBox({
			imageLoading: '/js/images/lightbox-ico-loading.gif',
			imageBtnClose: '/js/images/lightbox-btn-close.gif',
			imageBtnPrev: '/js/images/lightbox-btn-prev.gif',
			imageBtnNext: '/js/images/lightbox-btn-next.gif',
			imageBlank: '/js/images/lightbox-blank.gif',
		});
		$("#onStore a[href='#orderPopup']").bind('click', function(){
			$('body').append('<div id="wrapperOrder"></div>');
			$("#orderPopup").show('slow');
			return false;
		});
		$("img#close").bind('click', function(){
			$("#orderPopup").hide('slow');
			$("#wrapperOrder").remove();
			return false;
		});
		$("#wrapperOrder").live('click', function(){
			$("#orderPopup").hide();
			$("#wrapperOrder").remove();
			return false;
		});
	});
</script>		
	</head>
<!--#################################################################################################################-->
<body>


<?$APPLICATION->ShowPanel();?>

		<div style="position:fixed; height:100px;"></div>
		<header class="top_bar">
			<section id="special">
				<div id="special_container">
				<?
					if(CModule::IncludeModule("iblock"))
					{
						//$i=0;
						$arFilter = Array("IBLOCK_ID"=>8, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
						$res = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);
						while($ar_res = $res->GetNext())
						{
							$mass[] = $ar_res;
						}
						echo '<ul id="special_list">';
						for ($i=0; $i<=count($mass); $i++){
							$db_props = CIBlockElement::GetProperty(8, $mass[$i]["ID"], "sort", "asc", Array("CODE"=>"picture"));
							$db_props1 = CIBlockElement::GetProperty(8, $mass[$i]["ID"], "sort", "asc", Array("CODE"=>"link"));
							echo '<li>';
							echo '<div>';
							if ($ar_props = $db_props->Fetch()){ 
								if ($ar_props["VALUE"]){
									echo '<img id="special_image" src="'.$ar_props["VALUE"].'" alt="" />';
									echo '<br>';
										if ($ar_props1 = $db_props1->Fetch()){ 
											echo '<a href="'.str_ireplace('&', '&amp;', $ar_props1["VALUE"]).'">';
										}
										echo $mass[$i]["NAME"].'</a>';
										echo '<br>';
										echo '<br>';
								}
								echo $mass[$i]["PREVIEW_TEXT"];
								echo '</div>';
								echo '</li>';
							}
						}
						echo '</ul>';
					}
				?>
				</div>
				<p class="special_hide_button">
					<img src="/bitrix/templates/poligon_i/images/special_hide.png" usemap="#special_hide_area" />
					<map name="special_hide_area">
						<area shape="rect" coords="110,10,480,40" href="#" id="special_close" alt="Закрыть панель специальных предложений">
					</map>
				</p>
			</section>

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
		<a href="/content/feedback/quick_order/"><img class="quick_logo_img" src="/bitrix/templates/poligon_i/images/quick_logo_off.png"
			onmouseover="this.src='/bitrix/templates/poligon_i/images/quick_logo_on.png';"
			onmouseout="this.src='/bitrix/templates/poligon_i/images/quick_logo_off.png';"
		/></a>
	<?$APPLICATION->IncludeComponent("bitrix:search.form", "form", Array("PAGE"	=>	"#SITE_DIR#search/index.php"));?>
		<a href="/special/"><img class="special_logo_img" src="/bitrix/templates/poligon_i/images/special_logo_off.png"
			onmouseover="this.src='/bitrix/templates/poligon_i/images/special_logo_on.png';"
			onmouseout="this.src='/bitrix/templates/poligon_i/images/special_logo_off.png';"
		/></a>
</div>
<div class="partners_pad"></div>
<div class="partners">
<table class="partners_table">
<tr>
<td>
<a href="/index.php#tele_ancor"><img class="partners_logo" src="/images/logo/logo_200/tele_grey.gif" alt="TELE" title="реле времени и контроля из Австрии"

onmouseover="this.src='/images/logo/logo_200/tele.gif';"
onmouseout="this.src='/images/logo/logo_200/tele_grey.gif';"

/></a>
</td>
<td>
<a href="/index.php#citel_ancor">  <img class="partners_logo" src="/images/logo/logo_200/citel_grey.gif" alt="CITEL" title="CITEL - устройства молниезащиты"

onmouseover="this.src='/images/logo/logo_200/citel.gif';"
onmouseout="this.src='/images/logo/logo_200/citel_grey.gif';"

/></a>
</td>
<td>
<a href="/index.php#benedict_ancor"><img class="partners_logo" src="/images/logo/logo_200/benedict_grey.gif" alt="Benedict" title="Контакторы, пускатели, защита Benedict"

onmouseover="this.src='/images/logo/logo_200/benedict.gif';"
onmouseout="this.src='/images/logo/logo_200/benedict_grey.gif';"

/></a>
</td>
<td>
<a href="/index.php#graesslin_ancor"><img class="partners_logo" src="/images/logo/logo_200/graesslin_grey.gif" alt="Graesslin" title="Graesslin - таймеры и фотореле. Сделано в Германии."

onmouseover="this.src='/images/logo/logo_200/graesslin.gif';"
onmouseout="this.src='/images/logo/logo_200/graesslin_grey.gif';"

/></a>
</td>
<td>
<a href="/index.php#sonder_ancor"><img class="partners_logo" src="/images/logo/logo_200/sonder_grey.gif" alt="SONDER" title="SONDER - термостаты"

onmouseover="this.src='/images/logo/logo_200/sonder.gif';"
onmouseout="this.src='/images/logo/logo_200/sonder_grey.gif';"

/></a>
</td>
<td>
<a href="/index.php#relequick_ancor"><img class="partners_logo" src="/images/logo/logo_200/relequick_grey.gif" alt="RELEQUICK" title="RELEQUICK - интерфейсные реле"

onmouseover="this.src='/images/logo/logo_200/relequick.gif';"
onmouseout="this.src='/images/logo/logo_200/relequick_grey.gif';"

/></a>
</td>
</tr>
<tr>
<td>
<a href="/index.php#comat_releco_ancor"> <img class="partners_logo" src="/images/logo/logo_200/comat_releco_grey.gif" alt="COMAT-RELECO" title="COMAT-RELECO - промышленные промежуточные реле"

onmouseover="this.src='/images/logo/logo_200/comat_releco.gif';"
onmouseout="this.src='/images/logo/logo_200/comat_releco_grey.gif';"

/></a>
</td>
<td>
<a href="/index.php#emko_ancor"><img class="partners_logo" src="/images/logo/logo_200/emko_grey.gif" alt="EMKO" title="EMKO - температурные датчики и и контроллеры"

onmouseover="this.src='/images/logo/logo_200/emko.gif';"
onmouseout="this.src='/images/logo/logo_200/emko_grey.gif';"

/></a>
</td>
<td>
<a href="/index.php#cbi_ancor"><img class="partners_logo" src="/images/logo/logo_200/cbi_grey.gif" alt="CBI" title="Профессиональные автоматические выключатели"

onmouseover="this.src='/images/logo/logo_200/cbi.gif';"
onmouseout="this.src='/images/logo/logo_200/cbi_grey.gif';"

/></a>
</td>
<td>
<a href="/index.php#huber_suhner_ancor"><img class="partners_logo" src="/images/logo/logo_200/huber-suhner_grey.gif" alt="HUBER+SUHNER" title="ВЧ-разъемы, оптические компоненты"

onmouseover="this.src='/images/logo/logo_200/huber-suhner.gif';"
onmouseout="this.src='/images/logo/logo_200/huber-suhner_grey.gif';"

/></a>
</td>
<td>
<a href="/index.php#farnell_ancor"><img class="partners_logo" src="/images/logo/logo_200/farnell_grey.gif" alt="FARNELL" title="мировой лидер в области поставок электронных компонентов"

onmouseover="this.src='/images/logo/logo_200/farnell.gif';"
onmouseout="this.src='/images/logo/logo_200/farnell_grey.gif';"

/></a>
</td>
<td>
<a href="/index.php#vemer_ancor"><img class="partners_logo" src="/images/logo/logo_200/vemer_grey.gif" alt="VEMER" title="производитель промышленных решений для измерения и контроля основных электрических параметров"

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
<br><br>
<div class="breadcrumbs">
<?$APPLICATION->IncludeComponent( "bitrix:breadcrumb", "", Array( "START_FROM" => "3", "PATH" => "", "SITE_ID" => "-" ), false);?>
</div>
<br><br>
<!--#################################################################################################################-->
<section id="work_area">