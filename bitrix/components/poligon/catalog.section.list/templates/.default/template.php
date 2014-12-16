<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div id="catalog-section-list">
<table style="width: 100%; border-spacing: 0px; padding: 0px;" ><tr><td style="vertical-align: top; width:50%;"><ul>
<?
$i=0;
$CURRENT_DEPTH=$arResult["SECTION"]["DEPTH_LEVEL"]+1;
foreach($arResult["SECTIONS"] as $arSection):
	if($CURRENT_DEPTH<$arSection["DEPTH_LEVEL"])
		echo "<ul>";
	elseif($CURRENT_DEPTH>$arSection["DEPTH_LEVEL"])
		echo str_repeat("</ul><br/>", $CURRENT_DEPTH - $arSection["DEPTH_LEVEL"]);
	$CURRENT_DEPTH = $arSection["DEPTH_LEVEL"];
	$img_alt='';
	$sec_preview='';

	if($arSection["DEPTH_LEVEL"]==$arResult["SECTION"]["DEPTH_LEVEL"]+1)
	{
		if ($i==6&&$arResult["SECTION"]["DEPTH_LEVEL"]<1) echo '</ul></td><td style="vertical-align: top;"><ul>';
		$rsSect = CIBlockSection::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>$arSection["IBLOCK_ID"], "ID"=>$arSection["ID"]), false, array("UF_*"));
		if ($arSect = $rsSect->GetNext())
		{ 
			$img_alt = $arSect["UF_SECT"];
			$sec_preview = $arSect["UF_SECT_PREVIEW"];
		}
                echo '<li><table><tr>';
		echo '<td><a href="'.$arSection["SECTION_PAGE_URL"].'"><b>'.$arSection["NAME"].'</b>';
		echo '</a><br/><b>'.$sec_preview.'</b></td></tr></table></li>';
		$i++;
	?>
      <?}else{?>
	<li><a href="<?=$arSection["SECTION_PAGE_URL"]?>"><?=$arSection["NAME"]?>
<?if ($arResult["SECTION"]["DEPTH_LEVEL"]>=1)
{
		$rsSect = CIBlockSection::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>$arSection["IBLOCK_ID"], "ID"=>$arSection["ID"]), false, array("UF_*"));
		if ($arSect = $rsSect->GetNext())
		{ 
			$sec_preview = $arSect["UF_SECT_PREVIEW"];
		}
}
?>
</a><?if ($sec_preview) echo '&nbsp;&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp;&nbsp;&nbsp;'.$sec_preview;?></li>
<?}?>
<?endforeach?>
</ul></td></tr></table>
</div>



<div class="catalog-books">
<style type="text/css">
<!-- Вставляем стиль, необходимый для формы-->
.goMessage {
	color: #FF0000;
	font-family:Tahoma;
	font-size:14px;
}
.goFormsInputAndTextarea {
	border: 1px solid #000000;
}
.goTitles {
	margin-left:0px;
	font-family:Tahoma;
	font-size:12px;
}
.goAttensionTitle {
	font-family:Tahoma;
	font-size:12px;
}
.goAttensionError {
	color:#FF0000;
	font-family:Tahoma;
	font-size:12px;
}
.goButtonSend {border:solid 1px;}
.goButtonClaer {border:solid 1px;}
.goFormsCheckBox	{margin:0 10px 0 0;}
</style>

  
<div id="catalogs"> 	 
  <div> 	  
    <ul> 	 		 
		<li class="first-pdf">
			<a href="/PDF/TELE/TELE_Main_Catalogue_2011-2012_RU.pdf">
				<img src="/images/covers/tele.jpg" alt="Обложка каталога TELE" class="cover" />
			</a>
			<a class="pdf" href="/PDF/TELE/TELE_Main_Catalogue_2011-2012_RU.pdf">
				Основной каталог TELE 2011/2012
			</a> 		 
				Все товары TELE. Реле времени, реле контроля, устройства сопряжения, устройства управления, плавный пуск и торможение, промежуточные реле, таймеры, счетчики часов.
		</li>
     
 		 
      <li><a href="/PDF/TELE/TELE_-_Components_for_automation_RU.pdf"><img src="/images/covers/pre/TELE_-_Components_for_automation_RU.jpg" alt="Обложка буклета «Компоненты автоматизации»" class="cover" /></a> 		<a href="/PDF/TELE/TELE_-_Components_for_automation_RU.pdf" class="pdf">Компоненты автоматизации</a> 		 
        <br />
       Обзор номенклатуры: реле времени + реле контроля + устройства сопряжения. Содержит краткую информацию по функциям реле времени, фотографии серий устройств и таблицы изделий. </li>
     		 		 		 
      <li><a href="/PDF/TELE/TELE_pumpenfolder_v2_rus.pdf"><img src="/images/covers/pre/pumpenfolder.jpg" alt="Обложка буклета «Контроль и оптимизация насосов»" class="cover" /></a> 		<a class="pdf" href="/PDF/TELE/TELE_pumpenfolder_v2_rus.pdf">Буклет «Контроль и оптимизация насосов без датчиков с помощью реле контроля мощности»</a> 		 
        <br />
       Буклет содержит информацию о составе оборудования TELE для защиты и оптимизации работы насосов + способ контроля насосов, позволяющий <b>без датчиков</b> отслеживать ситуации сухого хода, засорения фильтра, перегрузки и проч.</li>
     	 		 		 
      <li><a href="/PDF/TELE/TELE_-_Fan_and_Compressor_monitoring_&amp;_optimisation_en.pdf"><img src="/images/covers/pre/TELE_-_Fan_and_Compressor_monitoring_&amp;_optimisation_en.jpg" alt="Обложка буклета «Контроль и оптимизация насосов»" class="cover" /></a> 		<a class="pdf" href="/PDF/TELE/TELE_-_Fan_and_Compressor_monitoring_&amp;_optimisation_en.pdf">Буклет «Контроль и оптимизация вентиляторов и компрессоров»</a> 		 
        <br />
       Буклет содержит информацию о составе оборудования TELE для контроля и оптимизации работы компрессоров и вентиляционных систем.</li>
     		 
      <li><a href="/PDF/TELE/TELE_12_time_and_monitoring_relays_ru.pdf"><img src="/images/covers/pre/12_time_and_monitoring_relays_ru.jpg" alt="Обложка буклета «12 реле времени и контроля»" class="cover" /></a> 		<a href="/PDF/TELE/TELE_12_time_and_monitoring_relays_ru.pdf" class="pdf">Буклет «12 реле времени и контроля»</a> 		 
        <br />
       Буклет позволяет подобрать реле времени или реле контроля по примерам применения. 12 реле времени и контроля с типичными примерами их использования.</li>
     		 
      <li><a href="/PDF/TELE/TELE_WatchDog_pro_rus.pdf"><img src="/images/covers/TELE_WatchDog_pro_rus.jpg" alt="Обложка Реле времени TELE" class="cover" /></a> 		<a class="pdf" href="/PDF/TELE/TELE_WatchDog_pro_rus.pdf">Модульная система контроля WatchDog pro</a> 		 
        <br />
       WatchDog pro - модульная система контроля, объединяющая в себе функции ПЛК + функции контроля электрических величин + протоколы и технологии связи (Modbus, Profibus, Ethernet, GSM...) + возможность подключения сенсорной панели + журнал регистрации + простую систему программирования</li>
     	 		 
      <li> 			<a href="/PDF/TELE/TELE_Safety_Relais_Leaflet.pdf"><img src="/images/covers/pre/TELE_Safety_Relais_Leaflet.jpg" alt="Обложка буклета «Компоненты автоматизации»" class="cover" /></a> 		 			<a href="/PDF/TELE/TELE_Safety_Relais_Leaflet.pdf">Реле безопасности (анг. )</a> 			 
        <br />
       Обзор модульной системы для контроля аварийного отключения и сигнализации. 		</li>
     
      <li> 			<a href="http://poligon.info/PDF/TELE/Folder_VEO_rus.pdf"><img src="/images/covers/VEO_cover2014.JPG" alt="Обложка буклета «Компоненты автоматизации»" class="cover" /></a> 		 			<a href="/PDF/TELE/Folder_VEO_rus.pdf">Буклет Tele VEO на русском языке</a> 			 
        <br />
       Данный буклет представляет новую серию реле времени и реле контроля лидера австрийского рынка, компании TELE. Реле VEO пришли на смену серии DELTA, что было обусловлено улучшением целого ряда эксплуатационных характеристик новой серии, таких как увеличение диапазона рабочих температур, точности измерений и, кроме того, ощутимого ускорения работы реле VEO. Время реакции сокращено с 250ms до 150ms, то есть, почти вдвое.</li>
     		</ul>
   </div>
 
  <div> 	 
    <h2>Benedict</h2>
   
    <p>90 лет разработки и производства контакторов, коммутационной аппаратуры. Лидер в производстве контакторов. Компактные и надежные контакторы из Австрии.</p>
   	 
    <ul> 	 		 
      <li class="first-pdf"><a href="/PDF/BENEDICT/Benedict_2012-Main_catalog_rus.pdf"><img src="/images/covers/benedict.jpg" alt="обложка Контакторы и пускатели Benedict" class="cover" /></a> 		 		<a class="pdf" href="/PDF/BENEDICT/Benedict_2012-Main_catalog_rus.pdf">Контакторы и пускатели</a> 		 
        <br />
       Промышленные контакторы, мини-контакторы, реле-контакторы. Тепловые реле и мотор-автоматы. Модульные контакторы.</li>
     	 		 
      <li><a href="/PDF/BENEDICT/Benedict%20-%20Cam%20Switches.pdf"><img class="cover" alt="обложка каталога Переключатели Benedict" src="/images/covers/pre/benedict-switches.jpg" /></a> 		<a href="/PDF/BENEDICT/Benedict%20-%20Cam%20Switches.pdf">Переключатели</a> 		 
        <br />
       Кулачковые (пакетные) переключатели с металлической ручкой. Возможность подобрать выключатель под ТУ. Главные выключатели и выключатели-разъединители в компактном исполнении для рационального использования пространства. Клеммы для кабелей с большим поперечным сечением. 		</li>
     		 		 
      <li><a href="/PDF/BENEDICT/M4_full.pdf"><img class="cover" alt="обложка каталога Автоматические выключатели Benedict" src="/images/covers/pre/benedict-cirtcuit-breakers.jpg" /></a> 		<a href="/PDF/BENEDICT/M4_full.pdf">Автоматические выключатели </a> 		 
        <br />
       Установка от 0,16 A до 100 A. Широкий диапазон аксессуаров. Могут быть использованы как Комби - контроллеры двигателя типа Е. Отключающая способность при к.з. 100 kA</li>
     		 		 
      <li><a href="/PDF/BENEDICT/Benedict%20-%20Capacitor%20Switching%20Contactors.pdf"><img src="/images/covers/pre/benedict-capacitor-switching-contactrors.jpg" alt="обложка каталога Ёмкостные контакторы Benedict" class="cover" /></a> 		<a href="/PDF/BENEDICT/Benedict%20-%20Capacitor%20Switching%20Contactors.pdf">Емкостные контакторы</a> 		 
        <br />
       Для использования с реактивными и не реактивными банками конденсаторов.</li>
     		 		 
      <li><a href="/PDF/BENEDICT/Benedict%20Manual%20Motor%20Starter.pdf"><img src="/images/covers/pre/benedict-manual-motorstarters.jpg" alt="обложка каталога Пускатели кнопочные Benedict" class="cover" /></a> 		<a href="/PDF/BENEDICT/Benedict%20Manual%20Motor%20Starter.pdf">Пускатели кнопочные</a> 		 
        <br />
       Установка 0,16-32 А с защитой от перегрузок и мгновенной защитой от короткого замыкания. </li>
     		 
      <li><a href="/PDF/BENEDICT/Benedict%20-%20Main%20Switches.pdf"><img src="/images/covers/pre/benedict-main-switches.jpg" alt="обложка каталога Выключатели главные Benedict" class="cover" /></a> 		<a href="/PDF/BENEDICT/Benedict%20-%20Main%20Switches.pdf">Выключатели главные</a> 		 
        <br />
       Выключатели нагрузки/Выключатели-разъединители LT(S).. 20–160 A. Главные выключатели, Выключатели нагрузки и Кулачковые переключатели для двигателей в компактном исполнении.</li>
     		 
      <li><a href="/PDF/BENEDICT/Benedict%20-%20Push%20Buttons.pdf"><img class="cover" alt="обложка каталога Кнопки Benedict" src="/images/covers/pre/benedict-push-buttons.jpg" /></a> 		<a href="/PDF/BENEDICT/Benedict%20-%20Push%20Buttons.pdf">Кнопки</a> 		 
        <br />
       Кнопки, лампы, переключатели шириной 22,5 мм и 30,5 мм, степень защиты: IP67.</li>
     		 
      <li><a href="/PDF/BENEDICT/Benedict%20-%20Modular%20Contactors%20and%20Push%20Puttons.pdf"><img class="cover" alt="обложка каталога Модульные контакторы и переключатели Benedict" src="/images/covers/pre/benedict-modular-contactors.jpg" /></a> 		<a href="/PDF/BENEDICT/Benedict%20-%20Modular%20Contactors%20and%20Push%20Puttons.pdf">Модульные контакторы</a> 		 
        <br />
       Модульные контакторы и переключатели. 		</li>
     	 	</ul>
   </div>
 
  <div> 	 
    <h2>Vemer</h2>
   
    <p>Компоненты &quot;умного&quot; дома</p>
   	 
    <ul> 	 		 
      <li class="first-pdf"><a target="_blank" href="/PDF/VEMER/VEMER_main.pdf"><img src="/images/covers/VEMER_main.jpg" alt="обложка Главного каталога Vemer" class="cover" /></a> 		 		<a target="_blank" class="pdf" href="/PDF/VEMER/VEMER_main.pdf">Каталог продукции Vemer</a> 		 
        <br />
       Перечень товаров Vemer. Механические, электронные, программируемые термостаты, электромеханические, цифровые, астрономические таймеры, датчики движения, датчики газа, различные терморегуляторы, измерительные электроприборы и многое другое.</li>
     	</ul>
   </div>
 
  <div> 	 
    <h2>CITEL</h2>
   	 
    <p>Грозозащита и УЗИП.</p>
   	 
    <ul> 	 		 
      <li class="first-pdf"><a target="_blank" href="/PDF/CITEL/citel-catalog-rus.pdf"><img class="cover" src="/images/covers/citel.jpg" alt="Обложка каталога Citel" /></a> 		<a target="_blank" href="/PDF/CITEL/citel-catalog-rus.pdf">Citel - основной каталог продукции</a> 		 
        <br />
       Устройства молниезащиты и защиты от импульсного перенапряжения (УЗИП). Модули защиты на DIN-рейку. Защита сетей электропитания, интерфейсов передачи данных и телекоммуникации, защита ВЧ-техники. Газоразрядники. </li>
     		 		 
      <li><a target="_blank" href="/PDF/CITEL/CITEL_2013.pdf"><img class="cover" alt="Обложка каталога Surge protection 9th edition" src="/images/covers/pre/CITEL_2013.jpg" /></a> 		<a target="_blank" href="/PDF/CITEL/CITEL_2013.pdf">Surge protection 9<sup>th</sup> edition</a> 		 
        <br />
       Основной каталог продукции CITEL на английском языке. 2013 год. <a href="images/CITEL/2.jpg"> </a></li>
     <a href="images/CITEL/2.jpg"> </a></ul>
   <a href="images/CITEL/2.jpg"> </a></div>
 <a href="images/CITEL/2.jpg"> </a> 
  <div>
  <a href="images/CITEL/2.jpg"> 	 
      <h2>RELECO</h2>
     	 
      <p>Relays: That is what we know and stand for. </p>
     </a> 
    <ul><a href="images/CITEL/2.jpg"> </a> 
      <li class="first-pdf"><a href="images/CITEL/2.jpg"></a><a target="_blank" href="/PDF/RELECO/RELECO_CATALOGUE_2012_eng.pdf"><img class="cover" src="/images/covers/RELECO_CATALOGUE_2012_eng_1.jpg" alt="Обложка каталога comat/RELECO" /></a> 		<a target="_blank" href="/PDF/RELECO/RELECO_CATALOGUE_2012_eng.pdf">Releco/Comat - каталог продукции 2012-2013</a> 		 
        <br />
       Промежуточные реле, реле времени, реле контроля (на английском). </li>
     		 		 
      <li><a target="_blank" href="/PDF/RELECO/RELECO_CATALOG.pdf"><img class="cover" src="/images/covers/pre/releco.jpg" alt="Обложка каталога RELECO" /></a>	<a target="_blank" class="pdf" href="/PDF/RELECO/RELECO_CATALOG.pdf">Releco - основной каталог продукции на русском</a> 		 
        <br />
       Промежуточные реле: промышленные, миниатюрные, интерфейсные и полупроводниковые.</li>
     		 		 
      <li><a target="_blank" href="/PDF/RELECO/RELECO_Ka_Railway_2012e.pdf"><img class="cover" alt="Обложка каталога Comat Releco" src="/images/covers/pre/Ka_Railway_2012e.jpg" /></a> 		<a target="_blank" href="/PDF/RELECO/RELECO_Ka_Railway_2012e.pdf">Устройства для железнодорожного транспорта. </a> 		 
        <br />
       Широкий ассортимент реле, устройств контроля и мониторинга разработанных Comat Releco в соответствии со стандартами использования на транспорте. </li>
     		 	</ul>
   </div>
 
  <div> 	 
    <h2>Graesslin</h2>
   	 
    <p>Интеллектуальные решения для эффективного распределения времени, тепла и света. Технологии энергосбережения. Устройства автоматизации в быту. </p>
   	 
    <ul> 	 		 
      <li class="first-pdf"><a href="/PDF/GRAESSLIN/Trade_Program_2014-2015_EN.pdf"><img class="cover" alt="Обложка каталога Graesslin" src="/images/covers/Graeslin_catalog_2014-2015_EN.jpg" /></a> 		 		<a href="/PDF/GRAESSLIN/Trade_Program_2014-2015_EN.pdf" class="pdf">Graesslin - новый каталог продукции на 2014-2015гг. (на английском языке).</a> 		 
        <br />
       Таймеры механические и цифровые, астрономические таймеры, промышленные таймеры, фотореле, термостаты, хронотермостаты, розеточные таймеры. Все товары Graesslin. </li>
     	 	 	 
      <li><a href="/PDF/GRAESSLIN/Catalogue_Graesslin_ru.pdf"><img class="cover" alt="Обложка каталога Graesslin" src="/images/covers/graesslin.jpg" /></a> 		 		<a href="/PDF/GRAESSLIN/Catalogue_Graesslin_ru.pdf" class="pdf">Graesslin - предыдущая версия каталога продукции (2010-2011гг.)</a> 		 
        <br />
       Таймеры механические и цифровые, астрономические таймеры, промышленные таймеры, фотореле, термостаты, хронотермостаты, розеточные таймеры. Все товары Graesslin. </li>
     		 
      <li><a href="/PDF/GRAESSLIN/graesslin-review-ru.pdf"><img class="cover" alt="Обложка буклета" src="/images/covers/pre/graesslin-review-ru.jpg" /></a> 		<a href="/PDF/GRAESSLIN/graesslin-review-ru.pdf" class="pdf">Graesslin - краткий обзор устройств (буклет)</a> 		 
        <br />
       Все изделия Graesslin на одном развороте. Компоненты бытовой автоматизации: управление временем, теплом и светом.</li>
     	</ul>
   </div>
 
  <div> 	 
    <h2>EMKO</h2>
   	 
    <p>Температурные датчики и контроллеры, устройства учёта и контроля для производства</p>
   	 
    <ul> 		 
      <li class="first-pdf"><a target="_blank" href="/PDF/EMKO/emko-2011-rus.pdf"><img class="cover" src="/images/covers/emko-2011-rus.jpg" alt="Обложка русского каталога EMKO" /></a> 		<a target="_blank" href="/PDF/EMKO/emko-2011-rus.pdf">Основной каталог продукции на русском языке</a> 		 
        <br />
       Температурные датчики, температурные контроллеры, технологические контроллеры, счётчики и таймеры, клиент-ориентированные контроллеры для промышленного применения.</li>
     	</ul>
   </div>
 
  <div> 	 
    <h2>CBI</h2>
   	 
    <p>Автоматические выключатели для промышленности и транспорта</p>
   	 
    <ul> 		 
      <li class="first-pdf"><a target="_blank" href="/PDF/CBI/CBI-circuit_breaker.en.pdf"><img class="cover" src="/images/covers/CBI.jpg" alt="Обложка каталога CBI" /></a> 		<a target="_blank" href="/PDF/CBI/CBI-circuit_breaker.en.pdf">Автоматы выключатели CBI (англ.). </a> </li>
     	 </ul>
   </div>
 
  <div> 	 
    <h2>RELEQUICK</h2>
   	 
    <p>Испанский производитель промышленных реле, интерфейсных реле, полупроводниковых реле, электронных цифровых модулей, реле времени и реле контроля, оборудования для возобновляемой энергии, SMS-реле.</p>
   
    <ul> 
      <li class="first-pdf"><a target="_blank" href="/PDF/RELEQUICK/Catalogue.pdf"><img class="cover" src="/images/covers/RELEQUICK_сatalog2014.jpg" alt="Обложка каталога RELEQUICK" /></a> 		<a target="_blank" href="/PDF/RELEQUICK/Catalogue.pdf">Основной каталог продукции на английском языке</a> </li>
     </ul>
   </div>
 </div>
</div>

<table width="100%" border="0" cellspacing="0" cellpadding="0">	
<!-- <tr>
		<td align="left" valign="top">
			<a href="/content/feedback/"><b>Форма обратной связи</b></a><br />
		</td>
	</tr> -->
  <tr>
    <td align="center" valign="top">
	<h2>Форма заказа CD, каталога</h2>
	<div style="text-align:left; width:320px;">
	



<?php
	//include требуемых библиотек
	include("/content/feedback/func.inc");
	include("/content/feedback/useragent.inc");
	
	
	
	// ниже приведенный список используется для удопства.
	$goInput = 'input'; // задаем HTML поле input
	$goCheckBox = 'checkbox';
	$goTextArea = 'textarea';
	$goSixeInput = ' type="text"  size="50"  maxlength="100" '; // здесь меняем TAG input
	$goSixeCheckBox =' type="checkbox"';
	$goSixeTextArea  = ' cols="57" rows="5" '; // здесь меняем TAG textarea
	$goValueInputStart  = 'value="';
	$goValueInputEnd  = '">';
	$goValueTextAreaStart  = '>';
	$goValueTextAreaEnd  = '</textarea>';
	$goAttensionStart = '<span class="goAttensionError" >Обнаружены следующие ошибки:<br>';
	$goAttensionSuffix = 'не заполнено поле ';
	$goAttensionSuffixNotCorrect = 'не корректно заполнено поле ';
	$goAttensionEnd = '</span></p>';
	$goMessageWasSend = '<span class="goMessage">Спасибо за отправку Вашего сообщения!</span>';	
	$goMessageForCheckbox ='<b>Выберите интересующие Вас пункты:</b><br />';

//***************************************** Здесь меняем и вносим требуемые условия (начало) *****************************************//		
	$goSend[To]='web-site-mailbox@poligon.info'; // направляем письмо в указанный ящик
	$goSend[Subject] = 'Заказ каталога/CD с сайта poligon.info'; // тема письма (иногда бывает отправляют вопрос, вместо заказа)
	$goIdOfName = 1; // Уакажите название компании/организации. Это обязателное значение и будет фигурировать в заголовке письма.
	$goIdOfEmail = 6; // укажите номер индификатора для проверки Email, в случае отсутствия указать значение 0
	$goIdOfPhone = 5; // укажите номер индификатора для проверки Телефона, в случае отсутствия указать значение 0	
	

//массив $goReqParam[], может принимать только значения true или false (Поле обязательное или необязательное для заполнения); 

	$goTitle [1]='Название организации:';		$goTypeHTML [1]=$goInput; $goName[1]='name'; 	$goReqParam[1]=true; 
	$goTitle [2]='Вид деятельности:';		$goTypeHTML [2]=$goInput; $goName[2]='kind'; 	$goReqParam[2]=true; 
	$goTitle [3]='Контактное лицо:';	$goTypeHTML [3]=$goInput; $goName[3]='face';	$goReqParam[3]=true; 
	$goTitle [4]='Должность:';		$goTypeHTML [4]=$goInput; $goName[4]='doljnost'; 	$goReqParam[4]=true; 
	$goTitle [5]='Тел./факс:';	$goTypeHTML [5]=$goInput; $goName[5]='phone1';	$goReqParam[5]=true; 
	$goTitle [6]='E-mail адрес:';			$goTypeHTML [6]=$goInput; $goName[6]='email';	$goReqParam[6]=true; 
	$goTitle [7]='Почтовый адрес:';			$goTypeHTML [7]=$goTextArea; $goName[7]='mail1';	$goReqParam[7]=true; 
	$goTitle [8]='Комментарий:';			$goTypeHTML [8]=$goTextArea; $goName[8]='comment';	$goReqParam[8]=false; 
	//Здесь наполняем необходимые чекбоксы
		$arSelect = Array("ID", "NAME", "PREVIEW_PICTURE","PREVIEW_TEXT");
		$arFilter = Array("IBLOCK_ID"=>7, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
		$res = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);
		$pci=9;
		while($arFields = $res->GetNext())
		{
			$goTitle [$pci] = $arFields["NAME"];
			$goTypeHTML [$pci]=$goCheckBox; $goName[$pci]='checkbox'.$pci;	$goReqParam[$pci]=false;
			$goPicture[$pci] = CFile::ShowImage($arFields["PREVIEW_PICTURE"], 0, 0, "border=1", "", true);
			$pci++;
		}


//***************************************** Здесь меняем и вносим требуемые условия (конец) *****************************************//

//***************************************** Все, что ниже менять не рекомендуется *****************************************//

	$goKolichestvoElementov = count($goTitle); // не трогать, и неперемещать. Эта строчка должна быть внизу.
	$goDefaultSendFrom = $goSendTo; // Ящик от которого будет приходить сообщение, в случае отсутствия E-Mail отправителя в формах.
	$goCheck[email]=$goName[$goIdOfEmail];
	$goCheck[phone]=$goName[$goIdOfPhone];
	$tempWeHaveGotError = false;
	$goAttensionSuffixNotCorrectEmail = $goAttensionSuffixNotCorrect.''.$goTitle [$goIdOfEmail].'<br>';
	$goAttensionSuffixNotCorrectPhone = $goAttensionSuffixNotCorrect.''.$goTitle [$goIdOfPhone].'<br>';
	
	$boolenMessageWasSend = false;
	//Обнуляем счётчик чекбоксов
	$countOfCheckbox = 0;
	for ($i = 1; $i <= $goKolichestvoElementov; $i++) 
	{
    		switch ($goTypeHTML [$i]) 
			{
				case ($goTextArea):
    				$goSixe[$i]=$goSixeTextArea;
					$goValueStart[$i] =	$goValueTextAreaStart ;
					$goValueEnd[$i] = $goValueTextAreaEnd ;						
    			break;
				case ($goInput):
    				$goSixe[$i]=$goSixeInput;
					$goValueStart[$i] =	$goValueInputStart ;
					$goValueEnd[$i] = $goValueInputEnd ;	
				break;
				case ($goCheckBox):
					++$countOfCheckbox;
					$goTypeHTML [$i] = $goInput;
    				$goSixe[$i]=$goSixeCheckBox;
					$goValueStart[$i] =	$goValueInputStart ;
					$goValueEnd[$i] = $goValueInputEnd ;	
    			break;
			}	
	}
	
?>
<form method="post" action="<?PHP_SELF?>">
  <p>
<?  
		$tempAttensionStart = '';
		$tempAttensionStart = '';
		//УДАЛЯЕМ кол-во чекбоксов, так как если они не выделены, даже пустая переменная не создаётся.
		for ($i = 1; $i <= ($goKolichestvoElementov-$countOfCheckbox); $i++) 
		{
			$tempMyNameIs=$goName[$i];
			$tempContentForm=$_POST[($tempMyNameIs)];
			
			switch ($goReqParam [$i]) 
			{
				case (true): //Если поле обязателное для заполнения, то делаем проверку
				if (isset ($_POST [($tempMyNameIs)] ) ) // Если страницу закрузили только что, то проверка не производиться
				{
					if ($_POST[($tempMyNameIs)] =='') // Если данные отправлены, а поля не заполнены, то сообщаем.
					{
						$tempContentFormErro[$i] = true;
						$tempAttensionStart = $goAttensionStart;
						$tempAttensionSuffix = $goAttensionSuffix;
						$tempAttensionEnd = $goAttensionEnd;
						$tempWeHaveGotError = true;
					 } else { // Если данные отправлены, но среди них есть поля для проверки на валидность, то производим проверку
						if (($_POST [($goCheck[email])]) == '') // Проверяем E-MAIL на корректность заполнения
						{ 
						$tempContentFormErro[$goIdOfEmail] = true;
						}else{
							if (validEmail($_POST [($goCheck[email])]) == true)
							{
								$tempCheckedEmail='';
							}else{
								$tempCheckedEmail= $goAttensionSuffixNotCorrectEmail;
								$tempAttensionStart = $goAttensionStart;
								$tempAttensionEnd = $goAttensionEnd;
								$tempWeHaveGotError = true;
							}
						}
						
						
						if (($_POST [($goCheck[phone])]) == '') // Проверяем Телефон на корректность заполнения
						{ 
						$tempContentFormErro[$goIdOfPhone] = true;
						}else{
							if (isPhoneNumber($_POST [($goCheck[phone])]) == true)
							{
								$tempCheckedPhone='';
							}else{
								$tempCheckedPhone= $goAttensionSuffixNotCorrectPhone;
								$tempAttensionStart = $goAttensionStart;
								$tempAttensionEnd = $goAttensionEnd;
								$tempWeHaveGotError = true;
							}
						}
								

						
					
					
					}
				}
				break;
				case (false): //Если поле не является обязательным, то пропускаем проверку.
						$tempContentFormErro[$i] = false;
									
				break;
	
			}
			

		}	
if (isset ($_POST [($tempMyNameIs)] ) )
{
	if ($tempAttensionStart != $goAttensionStart) // ошибок нет, отправляем письмо.
	{
	if (!isset($HTTP_X_FORWARDED_FOR))
	{
	$HTTP_X_FORWARDED_FOR = "";
	}
	if	($HTTP_X_FORWARDED_FOR)
	{
		$ip = getenv("HTTP_X_FORWARDED_FOR");
		$proxy = getenv("REMOTE_ADDR");
		$host = gethostbyaddr($REMOTE_ADDR);
	}else {
		$ip = getenv("REMOTE_ADDR");
		$host = gethostbyaddr($REMOTE_ADDR);
		$proxy = "";
	}
		
	$userAgent = $HTTP_USER_AGENT;
	$browser = getBrowser($arrBrowser,$userAgent);
	$system = getSystem($arrSystem,$userAgent);
	$server = $HTTP_HOST;
		
	$goGetName=($_POST [($goName[$goIdOfName])]);
	if ($goIdOfEmail != 0) 
	{
		$goGetEmail = ($_POST [($goCheck[email])]);
	}else{
		$goGetEmail = $goDefaultSendFrom;
	}
	
	
	
	for ($i = 1; $i <= ($goKolichestvoElementov-$countOfCheckbox); $i++) 
	{
		$tempMyNameIs=$goName[$i];
		$tempContentForm=$_POST[($tempMyNameIs)];
		$goMailBody = $goMailBody.$goTitle [$i]."	".$tempContentForm."\n" ;
	};
	for ($i = ($goKolichestvoElementov-$countOfCheckbox+1); $i <= $goKolichestvoElementov; $i++) 
	{
		if ($goSixe[$i] == $goSixeCheckBox && isset($_POST[($goName[$i])]))
			{
				$tempContentForm=$_POST[($goName[$i])];
				$goMailBody = $goMailBody.$goTitle [$i]." - выбран\n" ;
			};
	};
	
	
	$goMailBody = $goMailBody."\n\nДата: [".getFullDate(time()).", ".getQuestionTime(time())."]\n--------------------\n\n";
	$tempSendMeFrom = 'From: '.$goGetName.'<'.$goGetEmail.'>'."\nReply-To: ".$goGetEmail."\nContent-Type: text/plain; charset=windows-1251\nContent-Transfer-Encoding: 8bit" ;
	$tempSendMe = $goSend[To]."\r\n".$goSend[Subject]."\r\n".$goMailBody."\r\n".$tempSendMeFrom."\r\n \r\n";
			/**
		 *	Send email with message to admin
		 */
	@mail($goSend[To], $goSend[Subject], $goMailBody, $tempSendMeFrom);
	//echo $tempSendMe;
	#writeDataInFile ($tempSendMe);
	
	//Сообщаем, что все отправлено
	echo $goMessageWasSend.'<br><br>';	
	$boolenMessageWasSend = true;


	
	}
}
//Печатаем сообщение об ошибке				
echo $tempAttensionStart;
	for ($i = 1; $i <= ($goKolichestvoElementov-$countOfCheckbox); $i++) 
	{
		if ($tempContentFormErro[$i] == true) 
		{
			echo $tempAttensionSuffix.$goTitle[$i].'<br>';
		}
	}


echo $tempCheckedEmail.$tempCheckedPhone.$tempAttensionEnd;

//Печатаем список полей и кнопки
$flag = 1; //флаг для вывода сообщения  "Выберите интересующие Вас пункты:" всего 1 раз в цикле
if ($boolenMessageWasSend == false)
{
	echo '	<p><span class="goAttensionTitle"><font color="#FF0000">Внимание! </font>Все поля являются обязательными для заполнения, кроме "Комментарий"</span></p>';
		for ($i = 1; $i <= ($goKolichestvoElementov-$countOfCheckbox); $i++) 
		{
			//Выводим все поля кроме чек-боксов
			if ($goSixe[$i] != $goSixeCheckBox)	{
				$tempMyNameIs=$goName[$i];
				$tempContentForm=$_POST[($tempMyNameIs)];
				echo '<font class="goTitles">'.$goTitle[$i].'</font><br>
				<'.$goTypeHTML[$i].' name="'.$goName[$i].'" class="goFormsInputAndTextarea"	id="'.$goName[$i].'"	'.$goSixe[$i].$goValueStart[$i].$tempContentForm.$goValueEnd[$i].'
				';
			}
		};
		for ($i = $goKolichestvoElementov-$countOfCheckbox; $i <= $goKolichestvoElementov; $i++)
		{
			//выводим чекбоксы
			if ($goSixe[$i] == $goSixeCheckBox)	{
				if ($flag == 1) 
					echo  $goMessageForCheckbox; 
				$flag = 0;
				$tempMyNameIs2=$goName[$i];
				echo $tempContentForm;
				$tempContentForm=$_POST[($tempMyNameIs2)];

				echo '<table><tr><Td width="100" align="center" valign="middle">'.$goPicture[$i].'</td><td>
				<'.$goTypeHTML[$i].' name="'.$goName[$i].'" class="goFormsCheckBox"	id="'.$goName[$i].'"	'.$goSixe[$i].$goValueStart[$i].$tempContentForm.$goValueEnd[$i].'<font class="goTitles">'.$goTitle[$i].'</font></td></tr></table>';
			};
		}
		echo '    <br>
				<!-- Печатаем кнопки-->
				<input class="goButtonSend" type="submit" name="submit">
				<input class="goButtonClaer" type="reset" value="Очистить">
			</p>';
}
		
if ($boolenMessageWasSend == true)
{
		for ($i = 1; $i <= ($goKolichestvoElementov-$countOfCheckbox+1); $i++) 
		{
			//Выводим все поля кроме чек-боксов
			if ($goSixe[$i] != $goSixeCheckBox)	{
				$tempMyNameIs=$goName[$i];
				$tempContentForm=$_POST[($tempMyNameIs)];
				echo '<font class="goTitles">'.$goTitle[$i].' — '.$tempContentForm.'</font><br>';
			}
		};
		for ($i = ($goKolichestvoElementov-$countOfCheckbox+1); $i <= $goKolichestvoElementov; $i++)
		{
			if ($flag==1 AND isset($_POST[($goName[$i])]))	{
				echo '<b>Выбранные каталоги и диски:</b><br />';
				$flag=0;
			};
			if ($goSixe[$i] == $goSixeCheckBox AND isset($_POST[($goName[$i])]))	{
				echo '<font class="goTitles">'.$goTitle[$i].'</font><br>';
			};
		};
}
?>




</form>
</div>
	</td>
  </tr>
</table>