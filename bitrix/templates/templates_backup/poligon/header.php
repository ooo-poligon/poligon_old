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
		<a href="/index.php" id="logo_link"><img src="/bitrix/templates/poligon/images/logo.gif" alt="������� ��� �������"/></a>
		<!--
		<div id="lang">
			<?//$APPLICATION->IncludeFile($APPLICATION->GetTemplatePath("include_areas/langs.php"), Array(), Array("MODE"=>"html"));?>
		</div>
		-->
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
	<div class="clear"><!-- --></div>
	<div id="catalog">
		<div id="partners">
			<b class="top_right_corner"><!-- --></b>
			<b class="bottom_right_corner"><!-- --></b>
			<b class="bottom_left_corner"><!-- --></b>
			<a href="/content/program/">��������� ��������</a>	
			<?$APPLICATION->IncludeFile(
				$APPLICATION->GetTemplatePath("include_areas/partners.php"),
				Array(),
				Array("MODE"=>"html")
			);?>
		</div>
		<div id="catalog_list">
			<div id="catalog_right_column">
				<div id="specborder"><!-- --></div>
				<div id="special">
					<?$APPLICATION->IncludeFile(
						$APPLICATION->GetTemplatePath("include_areas/special.php"),
						Array(),
						Array("MODE"=>"php")
					);?>
				</div>	
				<div id="specborder2"><!-- --></div>
					<?$APPLICATION->IncludeFile(
						$APPLICATION->GetTemplatePath("include_areas/catalog_main2.php"),
						Array(),
						Array("MODE"=>"php")
					);?>
					<?$APPLICATION->IncludeFile(
						$APPLICATION->GetTemplatePath("include_areas/catalog_main3.php"),
						Array(),
						Array("MODE"=>"php")
					);?>
			</div>	
			<div id="catalog_left_column">	
				<a href="/catalog/" class="title">������� �������</a>
					<?$APPLICATION->IncludeFile(
						$APPLICATION->GetTemplatePath("include_areas/catalog_main1.php"),
						Array(),
						Array("MODE"=>"php")
					);?>
			</div>
		</div>
	</div>
	<div class="clear"><!-- --></div>
	<div id="content_div">
			<div id="bread_crumbs">
					<?$APPLICATION->IncludeComponent("bitrix:breadcrumb", "nav", Array(
						"START_FROM"	=>	"0",
						"PATH"	=>	"",
						"SITE_ID"	=>	"-"
						)
					);?>
					<div class="spacer3"><!-- --></div>
			</div>
		<div id="content">