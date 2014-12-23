<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<!DOCTYPE html>
<html>
<head>
<?$APPLICATION->ShowHead()?>
<?//$APPLICATION->AddHeadScript();?>
<title><?$APPLICATION->ShowTitle()?></title>
<!--[if lte IE 7]><link href="/iehack.css" rel="stylesheet" type="text/css" /><![endif]-->
<!--[if IE]>
<meta http-equiv="imagetoolbar" content="no" />
<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->

<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<link href="<?=$APPLICATION->GetTemplatePath();?>template_styles.css.gz" rel="stylesheet" type="text/css" />
<script src="/bitrix/templates/poligon/js/height.js"></script>

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
	<table id="title" style="width: 100%">
		<tr><td id="tit">poligon.ru</td><td style="vertical-align: top; text-align: right;"><a href="javascript:void(0)" onclick="Close()"><img src="/bitrix/images/fileman/htmledit2/taskbarx.gif" alt="" /></a></td></tr>
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
			<?//$APPLICATION->IncludeFile($APPLICATION->GetTemplatePath("include_areas/langs.php"), Array(), Array("MODE"=>"html"));?>
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
			<?$APPLICATION->IncludeFile(
				$APPLICATION->GetTemplatePath("include_areas/mail.php"),
				Array(),
				Array("MODE"=>"html")
			);?> 
		</div>
		<div id="phone">
			<?$APPLICATION->IncludeFile(
				$APPLICATION->GetTemplatePath("include_areas/phone.php"),
				Array(),
				Array("MODE"=>"html")
			);?>
		</div>
		<div id="form">
			<?$APPLICATION->IncludeComponent("bitrix:search.form", "form", Array("PAGE"	=>	"#SITE_DIR#search/index.php"));?>
		</div>
	</div>
	<div class="clear"><!-- --></div>
	<div id="menu">
		<?$APPLICATION->IncludeComponent("bitrix:menu", "tabs", Array(
			"ROOT_MENU_TYPE"	=>	"tabs",
			"MAX_LEVEL"	=>	"1",
			"CHILD_MENU_TYPE"	=>	"left",
			"USE_EXT"	=>	"N"
			)
		);?>
		<div id="main_area">
			<div id="navigation">
			<?$APPLICATION->IncludeComponent("bitrix:menu", "topnav_new", Array(
	"ROOT_MENU_TYPE"	=>	"topnav_new",
	"MAX_LEVEL"	=>	"1",
	"CHILD_MENU_TYPE"	=>	"",
	"USE_EXT"	=>	"N"
	)
);?>
			</div>
		<?$APPLICATION->IncludeComponent("bitrix:menu", "main", Array(
			"ROOT_MENU_TYPE"	=>	"main",
			"MAX_LEVEL"	=>	"1",
			"CHILD_MENU_TYPE"	=>	"left",
			"USE_EXT"	=>	"N"
			)
		);?>
		</div>
	</div>
	<div class="clear">	</div>
	<div id="catalog">
	
	
	
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
				
			<div id="content">

<br />



