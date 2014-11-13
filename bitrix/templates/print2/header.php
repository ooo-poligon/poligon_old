<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?=LANG_CHARSET;?>" />
<meta name="robots" content="noindex, follow" />
<?$APPLICATION->ShowMeta("keywords")?>
<?$APPLICATION->ShowMeta("description")?>
<title><?$APPLICATION->ShowTitle()?></title>
<?$APPLICATION->ShowCSS();?>
<?$APPLICATION->ShowHeadStrings()?>
<?$APPLICATION->ShowHeadScripts()?>
</head>

<body>
<div id="main_div">

<?if($APPLICATION->GetCurPage()!='/catalog/basket.php'):?>
		<div id="bread_crumbs">
			<?$APPLICATION->IncludeComponent("bitrix:breadcrumb", "nav", Array(
	"START_FROM"	=>	"0",
	"PATH"	=>	"",
	"SITE_ID"	=>	"-"
	)
);?>
			<div class="spacer3"><!-- --></div>
		</div>
<?endif;?>
<!--<h1><?//$APPLICATION->ShowTitle(false)?></h1>-->
