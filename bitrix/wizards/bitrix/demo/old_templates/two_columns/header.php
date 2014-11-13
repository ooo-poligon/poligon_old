<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?=LANG_CHARSET;?>" />
<?$APPLICATION->ShowMeta("robots")?>
<?$APPLICATION->ShowMeta("keywords")?>
<?$APPLICATION->ShowMeta("description")?>
<title><?$APPLICATION->ShowTitle()?></title>
<?$APPLICATION->ShowCSS();?>
<?$APPLICATION->ShowHeadStrings()?>
<?$APPLICATION->ShowHeadScripts()?>
</head>

<body>

<div id="panel"><?$APPLICATION->ShowPanel();?></div>

<div id="container">

<table id="header">
	<tr>
		<td id="header_slogan">
			<a href="/site2/" title="На главную"><img src="/bitrix/templates/two_columns/images/company_logo.gif" width="34" height="34" alt="На главную" border="0" align="left" /></a>
			<?$APPLICATION->IncludeFile(
				$APPLICATION->GetTemplatePath("include_areas/company_name.php"),
				Array(),
				Array("MODE"=>"html")
			);?>
		</td>
		<td id="header_search">

		<?$APPLICATION->IncludeComponent("bitrix:search.form", "flat", Array("PAGE" => "/site2/search/"));?>

		</td>
		<td id="header_separator"><img src="/bitrix/templates/two_columns/images/header_sep.gif" width="2" height="79" alt="" /></td>
		<td id="header_icons">
			<?$APPLICATION->IncludeFile(
				$APPLICATION->GetTemplatePath("include_areas/header_icons.php"),
				Array(),
				Array("MODE"=>"php")
			);?>
		</td>
	</tr>
</table>


<table id="content-table">

	<tr>
		<td id="left-column">


<?$APPLICATION->IncludeComponent("bitrix:menu", "vertical_multilevel", Array(
	"ROOT_MENU_TYPE"	=>	"top",
	"MAX_LEVEL"	=>	"3",
	"CHILD_MENU_TYPE"	=>	"left",
	"USE_EXT"	=>	"N"
	)
);?>


<?$APPLICATION->IncludeComponent("bitrix:main.include", "", Array(
	"AREA_FILE_SHOW"	=>	"sect",
	"AREA_FILE_SUFFIX"	=>	"inc",
	"AREA_FILE_RECURSIVE"	=>	"N",
	"EDIT_MODE"	=>	"html",
	"EDIT_TEMPLATE"	=>	"sect_inc.php",
	"CACHE_TYPE"	=>	"A",
	"CACHE_TIME"	=>	"0"
	)
);?>

<?$APPLICATION->IncludeComponent("bitrix:main.include", "", Array(
	"AREA_FILE_SHOW"	=>	"page",
	"AREA_FILE_SUFFIX"	=>	"inc",
	"AREA_FILE_RECURSIVE"	=>	"N",
	"EDIT_MODE"	=>	"html",
	"EDIT_TEMPLATE"	=>	"page_inc.php",
	"CACHE_TYPE"	=>	"A",
	"CACHE_TIME"	=>	"0"
	)
);?>

<div class="information-block">
	<div class="information-block-head">
		<div class="information-block-head-image"></div>
		<div class="information-block-head-text">Реклама</div>
	</div>
	<div class="information-block-body" style="text-align:center;">
		<?$APPLICATION->IncludeComponent("bitrix:advertising.banner", "", Array("TYPE" =>"LEFT"));?>
	</div>
</div>


		</td>
		<td id="right-column">
			<div id="logo">
				<div id="logo_image"></div><div id="logo_bg"></div>
				<div id="logo_sites"><?$APPLICATION->IncludeComponent("bitrix:main.site.selector", ".default", Array(
	"SITE_LIST"	=>	array(
		0	=>	"*all*",
	),
	"CACHE_TYPE"	=>	"A",
	"CACHE_TIME"	=>	"3600"
	)
);?></div>
			</div>

			<div id="work-area">
			<div id="navigation"><?$APPLICATION->IncludeComponent("bitrix:breadcrumb", ".default", Array(
					"START_FROM"	=>	"1",
					"SITE_ID"	=>	"-"
					)
				);?></div>
			<h1><?$APPLICATION->ShowTitle(false)?></h1>