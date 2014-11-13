<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?=LANG_CHARSET;?>" />
<meta name="robots" content="noindex, follow" />
<?$APPLICATION->ShowMeta("keywords")?>
<?$APPLICATION->ShowMeta("description")?>
<title><?$APPLICATION->ShowTitle()?></title>
<?$APPLICATION->ShowCSS();?>
<?$APPLICATION->ShowHeadStrings()?>
<?$APPLICATION->ShowHeadScripts()?>
<link href="/bitrix/templates/poligon_ew/styles.css.gz" type="text/css" rel="stylesheet"/>
<link href="/css/common.css" type="text/css" rel="stylesheet"/>
</head>
<body>
<div  style="width:630px;">




<table width="630px">
<tr>
<td>
		<a href="http://www.poligon.info" ><img src='/bitrix/templates/poligon/images/logo.gif' width="285px"></a>
</td>
<td>
<div >
                        <div style="color:#3371b9; font-size:24pt;" align="right">+7(812)325-42-20<br /></div>
			<!--<?$APPLICATION->IncludeFile(
				$APPLICATION->GetTemplatePath("include_areas/phone.php"),
				Array(),
				Array("MODE"=>"html")
			);?> -->  
                 </div>

		<div id="mail" style="left:444px;top:66px;" align="right">
			<?$APPLICATION->IncludeFile(
				$APPLICATION->GetTemplatePath("include_areas/mail.php"),
				Array(),
				Array("MODE"=>"html")
		);?> 		
		</div>
</div>
</td>
</tr>
</table>



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