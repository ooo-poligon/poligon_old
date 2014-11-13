<div class="see-also"><?
require_once "{$_SERVER['DOCUMENT_ROOT']}/bitrix/templates/poligon/include_areas/order.php";
/**
*	Файл подключается в карточке товара, перед выводом похожих товаров
*	Выводит описание "приемуществ" в зависимости от ID раздела (var $SECTION_ID)
*	since 18.04.2012
*	ver. 1.0
*	13/06/2012
*	в итоге используется и для кастомизации "похожих" товаров в карточке. ужас конечно, 
*	но из админки битрикса задавать не проще, и валидация торгового каталога не даёт редактировать 
*	позиции в ПУ. раздувать файл из-за этого тоже не вариант, простите ради Бога. 
*/
switch($SECTION_ID){
	case 160: // серия ENYA реле времени?>
	<h3>См. также: </h3>
	<ul class="mark">
	<li><a href="/content/articles/enya_range.php">Преимущества и обзор ассортимента серии модульных реле ENYA</a></li>
	<?
		switch($ELEMENT_ID){ // ссылки на другие товары в товарах серии энья. знаю, что ужас, но ничего не поделаёшь. 
		case 128: case 294: //E1ZM10 12-240VAC/DC?>
			<li><entity prop="link" name="E3ZM20 12-240VAC/DC" text="E3ZM20 12-240VAC/DC (111100) — многофункциональное реле времени с 2 перекидными контактами"/></li>
			<li><entity prop="link" name="E1ZI10 12-240VAC/DC" text="E1ZI10 12-240VAC/DC (110101) — циклическое реле с 2 независимо регулируемыми выдержками времени"/></li>
			<li><entity prop="link" name="E1ZMW10 24-240VAC/DC (VE10)" text="E1ZMW10 24-240VAC/DC (VE10) (110206A) — многофункциональное реле времени с альтернативным составом функций"/></li>
		<? break;
		case 129: case 295: //E1ZM10 24-240VAC/DC (110200)?>
			<li><entity prop="link" name="E1ZM10 12-240VAC/DC" text="E1ZM10 12-240VAC/DC (110100) — многофункциональное реле времени с питанием 12-240V AC/DC"/></li>
			<li><entity prop="link" name="E3ZM20 12-240VAC/DC" text="E3ZM20 12-240VAC/DC (111100) - многофункциональное реле времени с 2 перекидными контактами"/></li>
			<li><entity prop="link" name="E1ZI10 12-240VAC/DC" text="E1ZI10 12-240VAC/DC (110101) - циклическое реле с 2 независимо регулируемыми выдержками времени"/></li>
			<li><entity prop="link" name="E1ZMW10 24-240VAC/DC (VE10)" text="E1ZMW10 24-240VAC/DC (VE10) (110206A) — многофункциональное реле времени с альтернативным составом функций"/></li>
		<? break;
		case 1217: case 1218: //E1ZMQ10 24-240VAC/DC?>
			<li><entity prop="link" name="E1ZM10 12-240VAC/DC" text="E1ZM10 12-240VAC/DC (110100) — многофункциональное реле времени с питанием 12-240V AC/DC и 7 функциями"/></li>
			<li><entity prop="link" name="E3ZM20 12-240VAC/DC" text="E3ZM20 12-240VAC/DC (111100) - многофункциональное реле времени с 2 перекидными контактами"/></li>
			<li><entity prop="link" name="E1Z1E10 24-240VAC/DC (VE10)" text="E1Z1E10 24-240VAC/DC (VE10) (110204A) — простое реле времени с функцией задержки включения"/></li>
			<li><entity prop="link" name="E1Z1R10 24-240V AC/DC (VE10)" text="E1Z1R10 24-240V AC/DC (VE10) (110205A) — простое реле времени с функцией задержки выключения"/></li>
		<?
		break;
		case 300: //E1ZMW10 24-240VAC/DC (VE10)?>
			<li><entity prop="link" name="E1ZM10 12-240VAC/DC" text="E1ZM10 12-240VAC/DC (110100) — многофункциональное реле времени с альтернативным составом функций"/></li>
			<li><entity prop="link" name="E3ZM20 12-240VAC/DC" text="E3ZM20 12-240VAC/DC (111100) - многофункциональное реле времени с 2 перекидными контактами"/></li>
		<?
		break;
		case 299: //E1ZI10 12-240VAC/DC (110101)?>
			<li><entity prop="link" name="E3ZI20 12-240VAC/DC" text="E3ZI20 12-240VAC/DC (111101) - многофункциональное двухвременное реле времени с 2 перекидными контактами"/></li>
			<li><entity prop="link" name="E1ZM10 12-240VAC/DC" text="E1ZM10 12-240VAC/DC (110100) — многофункциональное одновременное реле времени"/></li>
		<?
		break;
		case 298: //E1Z1E10 24-240VAC/DC (VE10) (110204A)?>
			<li><entity prop="link" name="E1ZM10 12-240VAC/DC" text="E1ZM10 12-240VAC/DC (110100) — многофункциональное реле времени с питанием 12-240V AC/DC (в т.ч. задержка включения)"/></li>
			<li><entity prop="link" name="E1ZI10 12-240VAC/DC" text="E1ZI10 12-240VAC/DC (110101) - циклическое реле с 2 независимо регулируемыми выдержками времени"/></li>
			<li><entity prop="link" name="E1Z1R10 24-240V AC/DC (VE10)" text="E1Z1R10 24-240V AC/DC (VE10) (110205A) — простое реле времени с функцией задержки выключения"/></li>			
		<?
		break;
		case 72954: //E1Z1R10 24-240V AC/DC (VE10) (110205A)?>
			<li><entity prop="link" name="E1ZM10 12-240VAC/DC" text="E1ZM10 12-240VAC/DC (110100) — многофункциональное реле времени с питанием 12-240V AC/DC (в т.ч. задержка включения)"/></li>
			<li><entity prop="link" name="E1ZI10 12-240VAC/DC" text="E1ZI10 12-240VAC/DC (110101) - циклическое реле с 2 независимо регулируемыми выдержками времени"/></li>
			<li><entity prop="link" name="E1Z1E10 24-240VAC/DC (VE10)" text="E1Z1E10 24-240V AC/DC (VE10) (110204A) — простое реле времени с функцией задержки включения"/></li>
			<li><entity prop="link" name="D6A 3MIN 24-240VAC/DC" text="D6A 3MIN 24-240VAC/DC (234007)  — функция задержки выключения с управлением по питанию (true off delay)"/></li>
		<?
		break;
		case 303: //E3ZS20 12-240VAC/DC (111300)?>
			<li><entity prop="link" name="D6DS 24VAC/DC 110-240VAC" text="D6DS 24VAC/DC 110-240VAC (234070) — реле времени с функцией звезда-треугольник шириной 22,5мм"/></li>
		<?
		break;
		case 131:  // E1ZTP 230VAC (110301)?>
			<li><entity prop="link" name="E1ZTPNC 230VAC" text="E1ZTPNC 230VAC (110300) — лестничный таймер с расширенным набором функций"/></li>
		<?
		break;
		case 132: // E1ZTPNC 230VAC (110300)?>
			<li><entity prop="link" name="E1ZTP 230VAC" text="E1ZTP 230VAC (110301) — простой лестничный таймер (4 функции)"/></li>
		<?
		break;/*
		case 74260: // E1ZNT 230VAC (110500)?>
			<li>Лестничные таймеры <entity prop="link" name="E1ZTP 230VAC" text="E1ZTP 230VAC (110301) — простой лестничный таймер (4 функции)"/>
			и <entity prop="link" name="E1ZTPNC 230VAC" text="E1ZTPNC 230VAC (110300) — лестничный таймер с расширенным набором функций"/></li>
		<?break;*/
	}?>
	</ul>
	<?
		break;
	case 4988: {// РК ЭНЬЯ
		switch($ELEMENT_ID){
		case 216:
		?>
			<h3>См. также: </h3>
			<ul class="mark">
				<li><entity prop="link" name="SK1" text="Датчики уровня SK1"/></li>
				<li><entity prop="link" name="G2LM20 230VAC" text="Реле контроля жидкости в промышленном исполнении"/></li>
			</ul><?; break;
		}
	}
	case 4989: // РК КАППА
	case 4990: // РК ТРЕНД
	case 4991: // РК ОКТО
	
	
	case 158: {// реле контроля GAMMA
		switch($ELEMENT_ID){
			case 1254: break;
			#пошли РКЖ G2LM
			case 1272: ?>
			<h3>См. также: </h3>
			<ul class="mark">
				<li><entity prop="link" name="SK1" text="Датчики уровня SK1"/></li>
				<li>Аналогичные реле с питанием от <entity prop="link" name="G2LM20 24VAC" text="24V AC"/> и <entity prop="link" name="G2LM20 115VAC" text="115V AC"/></li>
				<li><entity prop="link" name="E3LM10 230VAC" text="Реле для контроля уровня жидкости в модульном исполнении"/></li>
			</ul>
			<?; break;
			case 1273:  ?>
			<h3>См. также: </h3>
			<ul class="mark">
				<li><entity prop="link" name="SK1" text="Датчики уровня SK1"/></li>
				<li>Аналогичные реле с питанием от <entity prop="link" name="G2LM20 24VAC" text="24V AC"/> и <entity prop="link" name="G2LM20 230VAC" text="230V AC"/></li>
				<li><entity prop="link" name="E3LM10 230VAC" text="Реле для контроля уровня жидкости в модульном исполнении"/></li>
			</ul>
			<?; break;
			case 1274:  ?>
			<h3>См. также: </h3>
			<ul class="mark">
				<li><entity prop="link" name="SK1" text="Датчики уровня SK1"/></li>
				<li>Аналогичные реле с питанием от <entity prop="link" name="G2LM20 115VAC" text="115V AC"/> и <entity prop="link" name="G2LM20 230VAC" text="230V AC"/></li>
				<li><entity prop="link" name="E3LM10 230VAC" text="Реле для контроля уровня жидкости в модульном исполнении"/></li>
			</ul>
			<?;  break;
			default: {
			?><h3>См. также: </h3>
			<ul class="mark">
				<li><entity prop="link" name="G2IW5A10" text="Спецпредложение на реле контроля однофазного тока в окне — G2IW5A10"/></li>
			</ul>
			<?
			} break;
		}
	?>
	<p><strong>Почему TELE?</strong> Высочайшая надежность, австрийское качество, мировой лидер в области релейной автоматики (2 место в мире по количеству выпускаемых реле времени и реле контроля), более 47 лет разработки и производства реле контроля. </p>
	<?
	}
		break;	
	case 5416: // graesslin механические таймеры?>
		<h3>См. также: </h3>
		<ul class="mark">
			<li><a href="/rele/daily-time-switch.php">Суточные реле времени и таймеры</a></li>
			<li><a href="/catalog/index.php?SECTION_ID=5417">Цифровые реле времени</a> (таймеры с цифровым интерфейсом для программирования)</li>
			<li><a href="/catalog/index.php?SECTION_ID=159">Электронные реле времени</a></li>
		</ul>
	<?
	break;
	/*
	case 5417:
	case 5418:
	case 5420:
	case 5419:
	case 5457:
	case 5458:
	case 5454:
	case 5423: // все разделы конечные разделы граслина	
	?>
<p>Graesslin - немецкий производитель устройств энергосбережения, управления освещением, фотореле, таймеров и термостатов. Компания работает с 1956 года самостоятельно разрабатывает и производит более 1500 изделий. Компания ПОЛИГОН является официальным дистрибьютором Graesslin в России.</p>	
	<?
		break;*/
	default: break;
}
?>
</div>