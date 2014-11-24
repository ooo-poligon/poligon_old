<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "ПОЛИГОН - электронные компоненты, фотореле, таймеры, реле, релейная автоматика, термостаты, контакторы, молниезащита и УЗИП");
$APPLICATION->SetPageProperty("NOT_SHOW_NAV_CHAIN", "Y");
$APPLICATION->SetTitle("Главная");
?><div id="news">
<div align="center"> <b class="newstitle">Новости компании</b> </div>
         <?$APPLICATION->IncludeComponent(
	"bitrix:news.line",
	"main_page",
	Array(
		"IBLOCK_TYPE" => "news", 
		"IBLOCKS" => array(0=>"3",), 
		"NEWS_COUNT" => "8", 
		"SORT_BY1" => "ACTIVE_FROM", 
		"SORT_ORDER1" => "DESC", 
		"SORT_BY2" => "SORT", 
		"SORT_ORDER2" => "ASC", 
		"DETAIL_URL" => "/content/news/index.php?news=#ELEMENT_ID#", 
		"CACHE_TYPE" => "A", 
		"CACHE_TIME" => "300" 
	)
);?> 
         
          <div align="center"> <b class="subscribetitle">Уведомления о новинках</b> </div>
         
         
          <p class="spacer2">Для подписки на новинки нашего каталога введите свою эл. почту.</p>
         
          <div align="center"> <?$APPLICATION->IncludeComponent(
	"bitrix:subscribe.form",
	"subscribe",
	Array(
		"USE_PERSONALIZATION" => "Y", 
		"PAGE" => "#SITE_DIR#personal/subscribe/subscr_edit.php", 
		"SHOW_HIDDEN" => "N", 
		"CACHE_TYPE" => "A", 
		"CACHE_TIME" => "3600" 
	)
);?> </div>
</div><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>