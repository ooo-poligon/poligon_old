<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><!DOCTYPE html>
<html>
<head> 
<?
// буферизируем для парсера-надстройки (see parseForDynamicContent in /function.php)
//ob_start("parseForDynamicContent");
$APPLICATION->ShowHead()?>
<?//$APPLICATION->AddHeadScript();

?>
<title><?$APPLICATION->ShowTitle()?></title>
<!--[if lte IE 7]><link href="/iehack.css" rel="stylesheet" type="text/css" /><![endif]-->
<!--[if IE]>
<meta http-equiv="imagetoolbar" content="no" />
<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />


<!-- <link href="<?=$APPLICATION->GetTemplatePath();?>styles.css" rel="stylesheet" type="text/css" /> -->
<!-- <link href="<?=$APPLICATION->GetTemplatePath();?>template_styles.css" rel="stylesheet" type="text/css" /> -->


<script src="/bitrix/templates/poligon/js/height.js"></script>
<script src="http://yandex.st/jquery/1.6.4/jquery.min.js"></script>

<link href="/css/jquery.lightbox-0.5.css" rel="stylesheet" type="text/css" />
<script src="/js/jquery.lightbox-0.5.pack.js"></script>
<script src="/bitrix/templates/poligon_ew/js/screen.js"></script>
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

<style>
#wrapperOrder{
	background: white;
	opacity: 0.2;
	width: 100%;
	position: fixed;
	height: 100%;
	top: 0;
}
#orderPopup {
	background-color: white;
	display: none;
	position: fixed;
	z-index: 100;
	font-family: Sans;
	width: 38%;
	height: auto;
	border-radius: 24px;
	border-color: blue;
	border-width: 1%;
	border-style: solid;
	top: 30%;
	left: 30%;}
#orderPopup p{
	padding: 0 2%;
	font-size: 12pt;
}
#orderPopup img#close{
	cursor: pointer;
	float: right;
}
#orderPopup h1{
	margin: 0;
	padding: 0 2%;
	font-size: 100%;
	background-color: blue;
	color: white;
	border-radius: 24px 24px 0px 0px;
}
</style>
</head>
<body id="main_main">










<!-- Rating@Mail.ru counter -->
<script type="text/javascript">
var _tmr = _tmr || [];
_tmr.push({id: "299343", type: "pageView", start: (new Date()).getTime()});
(function (d, w) {
   var ts = d.createElement("script"); ts.type = "text/javascript"; ts.async = true;
   ts.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//top-fwz1.mail.ru/js/code.js";
   var f = function () {var s = d.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ts, s);};
   if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); }
})(document, window);
</script><noscript><div style="position:absolute;left:-10000px;">
<img src="//top-fwz1.mail.ru/counter?id=299343;js=na" style="border:0;" height="1" width="1" alt="Рейтинг@Mail.ru" />
</div></noscript>
<!-- //Rating@Mail.ru counter -->










<?$APPLICATION->ShowPanel();?>
<div id="overlay"></div>
<div id="ajax_container1">
	<table id="title" style="width: 100%;">
		<tr>
			<td id="tit">poligon.info</td>
			<td style="text-align: right; vertical-align: top;"><a href="javascript:void(0)" onclick="Close()"><img src="/bitrix/images/fileman/htmledit2/taskbarx.gif" alt="" /></a></td>
		</tr>
	</table>
	<div id="ajax_container" style="text-align: left;"></div>
</div>




<table id="site_container">
<tbody>
<tr>
<td>




<div id="main_div">


	<div id="logo">
		<a href="/index.php" id="logo_link"><img src="/bitrix/templates/poligon/images/logo.gif" alt="Логотип ООО ПОЛИГОН" width="300px"/></a>
		<!--
		<div id="lang">
			<?//$APPLICATION->IncludeFile($APPLICATION->GetTemplatePath("include_areas/langs.php"),Array(),Array("MODE"=>"html"));?>
		</div>
		-->




		<div id="special" style="position:absolute; top:5px; left:315px; max-width:380px; min-height:60px;">
					<?php
					$APPLICATION->IncludeFile(
						$APPLICATION->GetTemplatePath("include_areas/special.php"),
						Array(),
						Array("MODE"=>"php")
					);?>
		</div>





		<div id="mail">
			<?php $APPLICATION->IncludeFile(
			$APPLICATION->GetTemplatePath("include_areas/mail.php"),
				Array(),
				Array("MODE"=>"html")
			);?> 
		</div>
		<div id="phone">
			<?php $APPLICATION->IncludeFile(
				$APPLICATION->GetTemplatePath("include_areas/phone.php"),
				Array(),
				Array("MODE"=>"html")
			);?>
		</div>

		<div id="form">
			<?php
			$APPLICATION->IncludeComponent("bitrix:search.form", "form", Array(
	"PAGE"	=>	"#SITE_DIR#search/index.php"
	)
);?>
		</div>
	</div>
	<div class="clear"><!-- --></div>
	<div id="menu">
		<?php
		$APPLICATION->IncludeComponent("bitrix:menu", "tabs", Array(
			"ROOT_MENU_TYPE"	=>	"tabs",
			"MAX_LEVEL"	=>	"1",
			"CHILD_MENU_TYPE"	=>	"left",
			"USE_EXT"	=>	"N"
			)
		);?>
		<div id="main_area">
			<div id="navigation">
			<?php 
			$APPLICATION->IncludeComponent("bitrix:menu", "topnav_new", Array(
				"ROOT_MENU_TYPE"	=>	"topnav_new",
				"MAX_LEVEL"	=>	"1",
				"CHILD_MENU_TYPE"	=>	"left",
				"USE_EXT"	=>	"N"
			)
			);?>
			</div>
		<?php 
		$APPLICATION->IncludeComponent("bitrix:menu", "main", Array(
			"ROOT_MENU_TYPE"	=>	"main",
			"MAX_LEVEL"	=>	"1",
			"CHILD_MENU_TYPE"	=>	"left",
			"USE_EXT"	=>	"N"
			)
		);?>
		</div>
	</div>
	
	



	<table width="962px">
	  <tr>
	    <td>	
				<div id="partners">
			<b class="top_right_corner"><!-- --></b>
			<b class="bottom_right_corner"><!-- --></b>
			<b class="bottom_left_corner"><!-- --></b>
			<!--<a href="/content/program/">Программа поставок</a>	-->
			<?$APPLICATION->IncludeFile(
				$APPLICATION->GetTemplatePath("include_areas/partners.php"),
				Array(),
				Array("MODE"=>"html")
			);?>
		</div>
		</td>
		</tr>
<tr>
<td>






		

		

			
		




<div id="print"><a href='<?=$APPLICATION->GetCurPageParam("print=Y")?>'><img src="/images/printer.gif" width="16" style="vertical-align: top" alt="print" />Версия для печати</a>

</div>



<div id="catalog">


		<div id="catalog_list">







				<div id="content_div">






					<div id="bread_crumbs">
					<?
					$APPLICATION->IncludeComponent("bitrix:breadcrumb", "nav", Array(
						"START_FROM"	=>	"1",
						"PATH"	=>	"",
						"SITE_ID"	=>	"-"
						)
					);?>
						<div class="spacer3"><!-- --></div>
					
                                        </div>




<div id="content">
		
<?
// буферизируем для парсера-надстройки (see parseForDynamicContent in /function.php)
//ob_start("parseForDynamicContent");
?>		
		