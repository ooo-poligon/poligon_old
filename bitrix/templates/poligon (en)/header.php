<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<?$APPLICATION->ShowHead()?>
<?$APPLICATION->AddHeadScript();?>
<title><?$APPLICATION->ShowTitle()?></title>
<!--[if lte IE 7]><link href="iehack.css" rel="stylesheet" type="text/css" /><![endif]-->
<script type="text/javascript" src="/bitrix/templates/poligon/js/height.js"></script>
</head>
<body id="main_main">




<?$APPLICATION->ShowPanel();?>
<div id="overlay"></div>
<div id="ajax_container1"><table id="title" width="100%"><tr><td id="tit">poligon.ru</td><Td valign="top" align="right"><a align="text_top" href="javascript:void(0)" onclick="Close()"><img src="/bitrix/images/fileman/htmledit2/taskbarx.gif"></a></td></table>
	<div id="ajax_container" align="left"></div>
</div>




<table id="site_container">
<tbody>
<tr>
<td>




<div id="main_div">
	<div id="logo">
		<a href="/index.php" id="logo_link">&nbsp;</a>
		<div id="lang">
			<?$APPLICATION->IncludeFile(
				$APPLICATION->GetTemplatePath("include_areas/langs.php"),
				Array(),
				Array("MODE"=>"html")
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
			<?$APPLICATION->IncludeComponent("bitrix:search.form", "form", Array(
	"PAGE"	=>	"#SITE_DIR#search/index.php"
	)
);?>

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
			<div id="navigation12">
			<?$APPLICATION->IncludeComponent("bitrix:menu", "topnav_new", Array(
	"ROOT_MENU_TYPE"	=>	"topnav_new",
	"MAX_LEVEL"	=>	"1",
	"CHILD_MENU_TYPE"	=>	"left",
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
	  <div id="right">
		<div id="partners">
			<b class="top_right_corner"><!-- --></b>
			<b class="bottom_right_corner"><!-- --></b>
			<b class="bottom_left_corner"><!-- --></b>
			<a href="/content/partners/">Наши партнеры</a>	
			<?$APPLICATION->IncludeFile(
				$APPLICATION->GetTemplatePath("include_areas/partners.php"),
				Array(),
				Array("MODE"=>"html")
			);?>
		</div>
		<div id="right_space">&nbsp;</div>
		<div id="special">
			<b class="top_right_corner"><!-- --></b>
			<b class="bottom_right_corner"><!-- --></b>
			<b class="bottom_left_corner"><!-- --></b>
					<?$APPLICATION->IncludeFile(
						$APPLICATION->GetTemplatePath("include_areas/special.php"),
						Array(),
						Array("MODE"=>"html")
					);?>
			</div>
		</div>
		<div id="catalog_list">
			<div id="content_div">
				<div id="bread_crumbs">
				<?$APPLICATION->IncludeComponent("bitrix:breadcrumb", "nav", Array(
					"START_FROM"	=>	"1",
					"PATH"	=>	"",
					"SITE_ID"	=>	"-"
					)
				);?>
					<div class="spacer3"><!-- 1111111111111111111 --></div>
				</div>
			<div id="content">