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
		<link href="/bitrix/templates/poligon_ibx_sonder/template_styles.css" rel="stylesheet" type="text/css" media="all" />
		<link href="/bitrix/templates/poligon_i/css/resolutions/style_1600.css" rel="stylesheet" type="text/css" media="(max-width: 1599px)" />
		<link href="/bitrix/templates/poligon_i/css/resolutions/style_1400.css" rel="stylesheet" type="text/css" media="(max-width: 1399px)" />
		<link href="/bitrix/templates/poligon_i/css/resolutions/style_1280.css" rel="stylesheet" type="text/css" media="(max-width: 1279px)" />
		<link href="/bitrix/templates/poligon_i/css/resolutions/style_1024.css" rel="stylesheet" type="text/css" media="(max-width: 1023px)" />
		<link href="/bitrix/templates/poligon_i/css/resolutions/style_800.css"  rel="stylesheet" type="text/css" media="(max-width: 799px)"  />
		<link href="/bitrix/templates/poligon_i/css/resolutions/style_640.css"  rel="stylesheet" type="text/css" media="(max-width: 639px)"  />
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
		<script type="text/javascript" src="/bitrix/templates/poligon_i/js/jquery.min.js"></script>
		<script type="text/javascript" src="/bitrix/templates/poligon_i/js/jquery.localscroll.js"></script>
		<script type="text/javascript" src="/bitrix/templates/poligon_i/js/jquery.scrollto.js"></script>
		<script type="text/javascript" src="/bitrix/templates/poligon_i/js/height.js"></script>
		<script type="text/javascript" src="/bitrix/templates/poligon_i/js/screen.js"></script>
		<script type="text/javascript" src="/bitrix/templates/poligon_i/js/poligon_i_scripts.js"></script>
		
		<link href="/css/jquery.lightbox-0.5.css" rel="stylesheet" type="text/css" />		
		<script type="text/javascript" src="/js/jquery.lightbox-0.5.pack.js"></script>
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
</head>
<!--#################################################################################################################-->
	<body>	
	<?php $APPLICATION->IncludeFile("/bitrix/templates/poligon_i/header_for_all.php"); ?>
					<br>
			<div id="print"><a href='<?=$APPLICATION->GetCurPageParam("print=Y")?>'><img src="/images/printer.gif" width="16" style="vertical-align: top" alt="print" />Версия для печати</a></div>
		<br>
			<div class="breadcrumbs">
				<?$APPLICATION->IncludeComponent( "bitrix:breadcrumb", "", Array( "START_FROM" => "3", "PATH" => "", "SITE_ID" => "-" ), false);?>
			</div>
			<br><br>
			<!--#################################################################################################################-->
			<section id="work_area">						