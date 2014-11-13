<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Статьи по автоматизации и электротехническому оборудованию");?><h1>Публикации по автоматизации и электротехническому оборудованию</h1>



<table>
<tbody>
<tr>
<td width="50%">
<div position="relative" top="1px">

<h2>Статьи</h2>
 
<ul class="mark"> 
  <li><a href="/content/articles/sonder_ta.php">Комнатные термостаты SONDER - принцип работы и обзор моделей.</a></li>
 	 
  <li><a href="/content/articles/graesslin_mc.php">Достаточно полный обзор датчиков движения производства &quot;Graesslin&quot;, 
      <br />
     или еще несколько строк о типовых компонентах концепции &quot;Умный Дом&quot;</a></li>
 
  <li><a href="/content/articles/tele-rele-control.php">Сравнение реле контроля фаз ABB CM-PVS.41 и TELE E1YM400VS10</a></li>
 	 
  <li><a href="/content/articles/dry_run_protection.php">Защита насоса от «сухого хода»: электродвигатель как датчик</a></li>
 	 
  <li><a href="/content/articles/control-lighting.php">Управление освещением</a></li>
 	 
  <li><a href="/content/articles/outdoor-lighting.php">Управление уличным освещением</a></li>
 	 
  <li><a href="/content/articles/tsg.php">Использование <abbr title="устройство плавного пуска">УПП</abbr> для снижения пусковых токов при запуске насоса</a></li>
 	 
  <li><a href="/content/articles/Benedict-overload-relay.php">Тепловые реле Benedict</a></li>
 	 
  <li><a href="/content/articles/loadmonitors.php">Реле контроля мощности и cos &#966;: двигатель как датчик</a></li>
 </ul>
 
<h2>Обзоры</h2>
 
<ul class="mark"> 	 
  <li><a href="/content/articles/enya_range.php">Преимущества и обзор ассортимента серии ENYA</a></li>
 	 
  <li><a href="/content/articles/gas-discharge-tube.php">Газовые разрядники CITEL: принцип работы и технические особенности</a></li>
 	 
  <li><a href="/content/articles/graesslin-review.php">Обзор продукции Graesslin</a> (таймеры, термостаты, фотореле)</li>
 	 
  <li><a href="/content/reviews/time-relay-tele.php">Обзор реле времени</a></li>
 	 
  <li><a href="/content/articles/review-of-current-monitoring-relays.php">Обзор реле контроля тока</a></li>
 	 
  <li><a href="/content/reviews/tele-phase-monitor-review.php">Обзор реле контроля фаз</a></li>
 	 
  <li><a href="/rele/daily-time-switch.php">Суточные реле времени</a></li>
 </ul>

</div>

</td>
<td>
<div position="relative" top="1px">
 
<h2>Дополнительная информация</h2>
 
<ul class="mark"> 	 
  <li><a href="/content/articles/benedict-motor-full-load-currents.php">Таблица значений тока номинальной нагрузки двигателя</a></li>
 	 
  <li><a href="/content/articles/contactors-for-lighting.php">Таблица подбора модульных контакторов для коммутации ламп</a></li>
 	 
  <li><a href="/content/benedict_conttable.php">Таблица для выбора контактора Benedict по аналогам</a></li>
 	 
  <li><a href="/content/articles/citel-history.php">История концерна CITEL</a></li>
 	 
  <li><a href="/content/about/citel.php">О компании CITEL</a></li>
 	 
  <li><a href="/content/articles/graesslin-interactive.php">Интерактивные модели таймеров и термостатов Graesslin</a> (flash-презентации)</li>
 	 
  <li><a href="/content/articles/programming-timer.php">Программирование цифрового таймера talento pro</a></li>
 </ul>
 
<h2>Видео</h2>
 <span></span> 
<ul class="mark"> 
  <li><a target="_self" href="/content/articles/video_links/citel1.php">УЗИП CITEL для защиты системы светодиодного оборудования.</a></li>
 
  <li><a target="_self" href="/content/articles/video_links/citel2.php">УЗИП CITEL для защиты систем безопасности.</a></li>
 
  <li><a target="_self" href="/content/articles/video_links/citel3.php">УЗИП CITEL для защиты промышленных объектов.</a></li>
 
  <li><a target="_self" href="/content/articles/video_links/citel4.php">УЗИП CITEL для защиты фотоэлектрических станций от импульсных перенапряжений.</a></li>
 
  <li><a target="_self" href="/content/articles/video_links/citel5.php">УЗИП CITEL для защиты фотоэлектрических систем (частное использование).</a></li>
 
  <li><a target="_self" href="/content/articles/video_links/citel6.php">УЗИП CITEL для фотоэлектрических систем промышленных и общественных сооружений.</a></li>
 
  <li><a target="_self" href="/content/articles/video_links/citel7.php">CITEL – эксперт по защите от импульсных перенапряжений.</a></li>
 

</ul>
</div>


<br />
<br />
<br />
<br />



</td>
</tr>
</tbody>
</table>
 <?
/*
// нафиг, один хрен не работает эта штука. А, т.к. собирать статьи (вернее публикации) по историческим причинам надо в разных разделах, то
// штатными средствами битрикса реализовывать это гемморойно. Код ниже, пусть остаётся в назидание для потомков. 
$APPLICATION->IncludeComponent("bitrix:news", ".default", Array(
	"IBLOCK_TYPE"	=>	"articles",
	"IBLOCK_ID"	=>	"2",
	"NEWS_COUNT"	=>	"10",
	"USE_SEARCH"	=>	"Y",
	"USE_RSS"	=>	"Y",
	"NUM_NEWS"	=>	"20",
	"NUM_DAYS"	=>	"360",
	"YANDEX"	=>	"N",
	"USE_RATING"	=>	"Y",
	"MAX_VOTE"	=>	"5",
	"VOTE_NAMES"	=>	array(
		0	=>	"",
		1	=>	"1",
		2	=>	"2",
		3	=>	"3",
		4	=>	"4",
		5	=>	"5",
		6	=>	"",
	),
	"USE_CATEGORIES"	=>	"Y",
	"CATEGORY_IBLOCK"	=>	array(
		0	=>	"",
		1	=>	"2",
	),
	"CATEGORY_CODE"	=>	"THEMES",
	"CATEGORY_ITEMS_COUNT"	=>	"5",
	"CATEGORY_THEME_"	=>	"list",
	"CATEGORY_THEME_2"	=>	"list",
	"USE_REVIEW"	=>	"Y",
	"MESSAGES_PER_PAGE"	=>	"25",
	"USE_CAPTCHA"	=>	"Y",
	"PATH_TO_SMILE"	=>	"/bitrix/images/forum/smile/",
	"FORUM_ID"	=>	"1",
	"URL_TEMPLATES_READ"	=>	"/communication/forum/index.php?PAGE_NAME=read&FID=#FORUM_ID#&TID=#TOPIC_ID#",
	"SHOW_LINK_TO_FORUM"	=>	"N",
	"USE_FILTER"	=>	"N",
	"SORT_BY1"	=>	"ACTIVE_FROM",
	"SORT_ORDER1"	=>	"DESC",
	"SORT_BY2"	=>	"SORT",
	"SORT_ORDER2"	=>	"ASC",
	"SEF_MODE"	=>	"N",
	"SEF_FOLDER"	=>	"",
	"CACHE_TYPE"	=>	"A",
	"CACHE_TIME"	=>	"3600",
	"CACHE_FILTER"	=>	"N",
	"DISPLAY_PANEL"	=>	"N",
	"SET_TITLE"	=>	"Y",
	"INCLUDE_IBLOCK_INTO_CHAIN"	=>	"N",
	"ADD_SECTIONS_CHAIN"	=>	"Y",
	"USE_PERMISSIONS"	=>	"N",
	"META_KEYWORDS"	=>	"KEYWORDS",
	"META_DESCRIPTION"	=>	"-",
	"DETAIL_ACTIVE_DATE_FORMAT"	=>	"d.m.Y",
	"DETAIL_FIELD_CODE"	=>	array(
		0	=>	"SHOW_COUNTER",
		1	=>	"",
	),
	"DETAIL_PROPERTY_CODE"	=>	array(
		0	=>	"AUTHOR",
		1	=>	"rating",
		2	=>	"",
	),
	"PREVIEW_TRUNCATE_LEN"	=>	"0",
	"LIST_ACTIVE_DATE_FORMAT"	=>	"d.m.Y",
	"LIST_FIELD_CODE"	=>	array(
		0	=>	"",
		1	=>	"",
	),
	"LIST_PROPERTY_CODE"	=>	array(
		0	=>	"",
		1	=>	"",
	),
	"HIDE_LINK_WHEN_NO_DETAIL"	=>	"N",
	"DETAIL_DISPLAY_TOP_PAGER"	=>	"N",
	"DETAIL_DISPLAY_BOTTOM_PAGER"	=>	"Y",
	"DETAIL_PAGER_TITLE"	=>	"Страница",
	"DETAIL_PAGER_TEMPLATE"	=>	"",
	"DISPLAY_TOP_PAGER"	=>	"N",
	"DISPLAY_BOTTOM_PAGER"	=>	"Y",
	"PAGER_TITLE"	=>	"Статьи",
	"PAGER_SHOW_ALWAYS"	=>	"N",
	"PAGER_TEMPLATE"	=>	"",
	"PAGER_DESC_NUMBERING"	=>	"Y",
	"PAGER_DESC_NUMBERING_CACHE_TIME"	=>	"36000",
	"VARIABLE_ALIASES"	=>	array(
		"SECTION_ID"	=>	"SECTION_ID",
		"ELEMENT_ID"	=>	"article",
	)
	)
);
//releByFunctions();
*/
?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>