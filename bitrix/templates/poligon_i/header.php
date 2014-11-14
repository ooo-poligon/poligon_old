<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<!DOCTYPE html>
<html>
<!--#################################################################################################################-->
<head>
<?$APPLICATION->ShowHead()?>
<?//$APPLICATION->AddHeadScript();?>
<title><?$APPLICATION->ShowTitle()?></title>
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<link href="<?=$APPLICATION->GetTemplatePath();?>template_styles.css.gz" rel="stylesheet" type="text/css" />
</head>
<!--#################################################################################################################-->
<body>
<!--#################################################################################################################-->
<header>
    <div id="site_logo">
        <a href="/index.php"><img src="/bitrix/templates/poligon_i/images/logo.gif" alt="Логотип ООО ПОЛИГОН"/></a>
    </div>
    <div id="telephon_header">
        <img src="/bitrix/templates/poligon_i/images/telephon.gif" alt="(812)325-42-20"/>
    </div>
<div id="header_container">
<nav>
<ul id="site_menu">
  <li><img src="/bitrix/templates/poligon_i/images/menu_main.gif" alt="Главная"/></li>
  <li><img src="/bitrix/templates/poligon_i/images/menu_catalogue.gif" alt="Каталог"/></li>
  <li><img src="/bitrix/templates/poligon_i/images/menu_articles.gif" alt="Публикации"/></li>
  <li><img src="/bitrix/templates/poligon_i/images/menu_sertificates.gif" alt="Сертификаты"/></li>
  <li><img src="/bitrix/templates/poligon_i/images/menu_contacts.gif" alt="Контакты"/></li>
</ul>
</nav>
    <div id="search_form">
	<?$APPLICATION->IncludeComponent("bitrix:search.form", "form", Array("PAGE"	=>	"#SITE_DIR#search/index.php"));?>
    </div>
</div>
</header>
<!--#################################################################################################################-->
<article>
<div>
    <div id="content_pad"></div>	
	<table id="content_table">
	    <tr>
	    <td>	
                    <div id="partners">
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
	    	<div>
			