<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "ПОЛИГОН - электронные компоненты, фотореле, таймеры, реле, релейная автоматика, термостаты, контакторы, молниезащита и УЗИП");
$APPLICATION->SetPageProperty("NOT_SHOW_NAV_CHAIN", "Y");
$APPLICATION->SetTitle("Главная");
?> 
<table width="960px"> 
  <tbody> 
    <tr> <td width="660px" style="-moz-border-top-colors: none; -moz-border-right-colors: none; -moz-border-bottom-colors: none; -moz-border-left-colors: none; border-image: none;"> 
        <h1 align="center">Специализация компании</h1>
       
        <p>Компания &quot;ПОЛИГОН&quot; специализируется в оптовой торговле <a href="/special/farnell.php">электронными компонентами</a> и <a href="/catalog/">электротехническими изделиями</a>. Мы сотрудничаем со следующими производителями:</p>
       
        <p><strong>TELE </strong>— промышленная автоматизация — <a href="/catalog/index.php?SECTION_ID=157">реле контроля</a> (тока, напряжения и мощности в 1- и 3-фазных сетях, температуры, уровня жидкости, частоты), <a href="/catalog/index.php?SECTION_ID=159">реле времени</a> (многофункциональные и простые; задержка включения, задержка выключения, циклические реле), модульная система контроля WatchDog pro, устройства плавного пуска и торможения двигателей, цифровые таймеры, счетчики моточасов, системы управления, реле (промежуточные, безопасности), трансформаторы тока и проч. </p>
       
        <p><strong>BENEDICT</strong> — контакторы, пускатели, мотор-автоматы, тепловые реле, кулачковые переключатели, кнопки, модульные контакторы и модульные устройства. </p>
       
        <p><strong>CITEL</strong> — грозозащита (молниезащита) — устройства защиты от импульсного перенапряжения (УЗИП), молниезащита сетей энергоснабжения, защита сетей данных и телекоммуникаций, защита радиоприемных и антенно-фидерных устройств (коаксиальная защита), бесперебойное электроснабжение, газоразрядники. </p>
       
        <p><strong>Vemer</strong> — компоненты умного дома, управление освещением, датчики газа с электромагнитным клапаном, ограничение пиков мощности по приоритетам для коттеджа/квартиры; цифровые и механические таймеры, астротаймеры; цифровые вольтметры, амперметры, частотомеры, анализаторы сети. фотореле, недельные таймеры, годовые таймеры.</p>
       
        <p><strong>Graesslin</strong> — умный дом и <a href="/content/articles/control-lighting.php">управление освещением</a> — цифровые и механические таймеры, счётчики времени наработки, комнатные термостаты и хронотермостаты, лестничные таймеры, фотореле. </p>
       
        <p><strong>SONDER</strong> —<strong> </strong>термостаты для контроля температуры на производстве и в быту. </p>
       
        <p><strong>Relequick</strong> — промежуточные реле, интерфейсные реле, программируемые реле, полупроводниковые реле, колодки, аксессуары.</p>
       
        <p><strong>RELECO</strong> —<strong> </strong>промежуточные реле — промышленные реле, интерфейсные реле, полупроводниковые реле, колодки, таймеры и аксессуары; </p>
       
        <p><strong>EMKO</strong> —<strong> </strong> температурные и промышленные контроллеры и датчики. </p>
       
        <p><strong>OBSTA</strong> —<strong> </strong>световое ограждение. </p>
       
        <p><strong>CBI</strong> —<strong> </strong>автоматические выключатели для профессионального использования, для любых температурных условий использования и токов. </p>
       
        <p><strong>HUBER+SUHNER</strong> —<strong> </strong>высокочастотные коаксиальные разъемы.<strong> </strong>Оптические компоненты. Кабель и сборки. Антенны. </p>
       
        <p><strong>FarnellInOne</strong> — более 250000 наименований от 2000 производителей. Микросхемы. Электронные компоненты, комплектующие изделия. </p>
       
        <p>Отечественные заводы-изготовители.</p>
       </td> 	 	 <td width="300px" style="-moz-border-top-colors: none; -moz-border-right-colors: none; -moz-border-bottom-colors: none; -moz-border-left-colors: none; border-image: none;"> 
        <div id="news"> 
          <br />
         
          <br />
         
          <br />
         
          <br />
         
          <br />
         
          <br />
         
          <br />
         
          <br />
         
          <br />
         
          <br />
         
          <br />
<br />
         
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
          <br />
         
          <br />
         
          <div align="center"> <b class="subscribetitle">Уведомления о новинках</b> </div>
         
          <br />
         
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
         
          <br />
         
          <br />
         
          <br />
         	 </div>
       
        <br />
       
        <br />
       
        <br />
       
        <br />
       
        <br />
       
        <br />
       
        <br />
       
        <br />
       
        <br />
       </td> </tr>
   </tbody>
 </table>
 <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>