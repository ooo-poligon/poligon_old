<?
if($_GET['action'] == 'order'
	&& $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'
){
	
$officeMails = array('elcomp@poligon.info', 'it@poligon.info', 'dolgova@poligon.info', 'gnato@poligon.info');

foreach ($_POST as $key => $value)
	if($value == '')
		$_POST[$key] = 'не указано';
	else
		$_POST[$key] = iconv('UTF-8', 'WINDOWS-1251', $value);
		
$officeMsg  = "
<h1 style='font-size: 14px;'>Заполнена заявка на сайте http://poligon.info/</h1>
<h2 style='font-size: 14px;'>Указанные данные:</h2> 
<dl>
	<dt>заказ: </dt>
	<dd><pre>{$_POST['order']}</pre></dd>
	<dt>компания: </dt>
	<dd>{$_POST['company']}</dd>	
	<dt>имя: </dt>
	<dd>{$_POST['name']}</dd>
	<dt>email: </dt>
	<dd>{$_POST['email']}</dd>
	<dt>телефон: </dt>
	<dd>{$_POST['telephone']}</dd>
</dl>";

$subject = 'заказ электронных компонентов на http://poligon.info';

/* Для отправки HTML-почты вы можете установить шапку Content-type. */
$headers= "MIME-Version: 1.0\n";
$headers .= "Content-type: text/html; charset=windows-1251\n";

/* дополнительные шапки */
$headers .= "From: poligon.info <website@poligon.info>\n";
foreach($officeMails as $officeMail)
	mail($officeMail, $subject, $officeMsg, $headers);
	
if($_POST['email'] != ''){
	mail($_POST['email'], $subject, $officeMsg, $headers);
}
die(1);	
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Электронные компоненты FARNELL");
$APPLICATION->SetPageProperty("description", "Заказ электронных компонентов по каталогу FARNELL, срока доставки от 3 недёель");
?><style type="text/css">
.style12 {font-size: 14px; font-weight: bold; color: #FFFFFF; }

#farnell h3{
	margin: 6px 0px	0px	8px;
	font-size: 15px;
}
#farnell{
	background-color: silver;
	border: 1px black solid;
	#border-radius: 8px;
	width: 500px;
	height: 210px;
}
#farnell form textarea {
	width: 274px;
	height: 150px;
}
label {
	display: inline-block;
	width: 32px;
}
label[for=order]{
	float: left;
	display: block;
	width: 10em;
}
label[for=company] {
	background: url("/images/icons/application_home.png") center no-repeat;
}

label[for=name] {
	background: url('/images/icons/user_suit.png') center no-repeat;
}
label[for=email] {
	background: url('/images/icons/email.png') center no-repeat;
	border: 1px dotted black;
	margin: -1px;
}
label[for=telephone] {
	background: url('/images/icons/phone.png') center no-repeat;
}

#farnell p {
	margin: 0px;
}
#farnell input {
	margin: 6px;
	width: 142px;
	height: 16px;
}
#farnell input[type=image]{
	width: 150px;
	height: 24px;
	margin-left: 36px;
}

</style>
<script>
<!--
$(function(){
	$('#farnell form').bind('submit', function(){
		if($('#farnell textarea').val() == ''){
			$('label[for=order]').css({'background': 'red'});
			$('#farnell textarea').css('border', '1px red solid');
			return false;
		}else{
			$('label[for=order]').css({'background': 'silver'});
			$('#farnell textarea').css('border', 'none');
		}
		if($('input#email').val() == ''){
			$('label[for=email]').css('background-color', 'red');
			return false;	
		}else{
			$.post('/special/farnell.php?action=order', {
					order: $('#order').val(),
					name: $('#name').val(),
					company: $('#company').val(),
					email: $('#email').val(),
					telephone: $('#telephone').val()
				 }, function(data){
				$('<p style="margin: 24px;">Ваша заявка отправлена. Время работы ответственного сотрудника: с&nbsp;9.30-18.00 по рабочим дням, пятница с 9.30-17.00. Заявка будет обработана и мы свяжемся с вами.</p>').replaceAll('#farnell form');
			});			
		}
		return false;		
	});
});
//-->
</script>

<?php 
/**
 * обработка формы
 */ 

?>
<img src="/images/farnell_logo.gif" alt="Логотип Farnell" style="float: right;"/>
<h3><strong>Оперативная поставка изделий по каталогу FARNELL</strong></h3>
<p><a href="/upload/FARNELL_current_stock.xls"><i>Текущие остатки по складу.</i></a></p>
<p>(услуга предоставляется только юридическим лицам)</p>
<img style="float: right; width: 114px; height: 180px;" src="/images/farnell_catalogue.gif" alt="Каталог электронных компонентов Farnell"/>
<div id="farnell">
<h3>Моментальный заказ Farnell</h3>
<form method="post">
<div style="float: left; margin: 0px 8px; width: 274px;">
<label for="order">хочу заказать: </label><br/>
<textarea rows="10" cols="32" name="order" id="order"></textarea>
</div>
<div style="width: 210px; float: left;">
<p>связаться со мной: </p>
<label for="company">&nbsp;</label><input type="text" name="company" id="company" placeholder="Компания"/><br/>
<label for="name">&nbsp;</label><input type="text" name="name" id="name" placeholder="Имя"/><br/>
<label for="email">&nbsp;</label><input type="email" name="email" id="email" placeholder="email*" required="true"/><br/>
<label for="telephone">&nbsp;</label><input type="text" name="phone" id="telephone" placeholder="телефон"/><br/>
<input type="image" name="image" src="/images/farnell-order.png" />
</div>
</form>
</div>
<p><strong>При оформлении заявки просим указывать:</strong></p>
<ul>
	<li> <strong>Наименование изделия</strong>, ордер-код Farnell (если известен), <strong>требуемое количество</strong>;</li>
	<li> <strong>Способ доставки</strong> (самовывоз, Автотрейдинг, экспресс-почта);</li>
	<li> <strong>Реквизиты для выставления счета</strong>;</li>
	<li> <strong>Адрес доставки и контактные данные</strong>. </li>
</ul>

<p><i><a href="/content/program/delivery.php">Способы доставки</a>: "Автотрейдинг", "ПОНИ-Экспресс".</i></p>
<table width="100%" border="0" cellpadding="0" cellspacing="3">
<tr>
<td background="/images/bg_circuit_fw.gif" bgcolor="#000000">
	<div align="center"><span class="style12">Поставка электронных компонентов и электротехники по каталогу Farnell</span>
	</div>
</td>
</tr>
</table>
<br/>
<table border="0" cellspacing="0" cellpadding="0">
<tr>
	<td>
		<p>Компания Farnell, основанная в 1939 году, является мировым лидером в области поставок 
		<a href="/catalog/index.php?SECTION_ID=13">электронных компонентов</a>, электротехнического, промышленного оборудования, продукции для технического обслуживания и ремонта. 
		Farnell является ведущим <strong>мировым дистрибьютором электронных компонентов</strong> с собственным складом.</p>
		<p>Более <b>510 000</b> изделий от <b>800 поставщиков</b>постоянно поддерживаются на складах Farnell. 
		Номенклатура постоянно расширяется (более 50 000 наименований в год).
		В состав Farnell так же входят компании Newark и CPC, обладающие собственными складами.
		<br /><img src="/images/cpc_logo.gif" alt="CPC logo" width="80" height="49"><img src="/images/newark_logo.gif" alt="Newark logo" width="101" height="37"></p>
	</td>
</tr>
</table>
<br/>
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td height="38" align="center" background="/images/bg_circuit_fw.gif" bgcolor="#000000" class="style12"><strong>Компания ПОЛИГОН более 14 лет поставляет продукцию со складов Farnell</strong></td>
	</tr>
	<tr>
		<td>
			<ul>
				<li>срок поставки - 3-4 недели</li>
				<li>работа отлажена, поставки производятся регулярно</li>
				<li>контроль целостности упаковки и количества изделий </li>
				<li>оперативная <a href="/content/program/delivery.php">доставка по России</a> (Автотрейдинг, экспресс-почта)</li>
			</ul>
		</td>
</tr>
</table>
<br/>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr bgcolor="#000000" background="/images/bg_circuit_fw.gif">
		<td height="38" colspan="2" align="center"><span class="style12"><strong>Список электронных компонентов и изделий, поставляемых по каталогу Farnell: </strong></span></td>
	</tr>
	<tr>
	<td>
		<ul class="mark px400">
			<li><a href="/catalog/index.php?SECTION_ID=5232">Микросхемы импортные</a></li>
			<li><a href="/catalog/index.php?SECTION_ID=5205">Диоды, транзисторы, тиристоры, силовые модули</a></li>
			<li>Преобразователи сигналов</li>
			<li>Резисторы, конденсаторы, индуктивности</li>
			<li>Кварцы, генераторы, резонаторы</li>
			<li>Соединители, разъемы, ВЧ-разъемы</li>
			<li>Кабель, провод, кабельные сборки</li>
			<li>Оптоэлектронные устройства</li>
			<li>Источники питания, трансформаторы </li>
			<li>Светотехника, светодиоды, оптика</li>
			<li>Дисплеи, индикаторы, средства отображения</li>
			<li>Датчики, средства измерения</li>
			<li>Реле, контакторы, коммутационные устройства </li>
			<li>Контроллеры, средства автоматизации </li>
			<li>Выключатели, переключатели, кнопки</li>
			<li>Корпуса, кожухи</li>
			<li>Электродвигатели, насосы </li>
			<li>Лабораторное и тестовое оборудование </li>
			<li>Инструмент</li>
			<li>Маркировка</li>
			<li>Расходные материалы </li>
			<li>прочее</li>
		</ul>
	</td>
	<td valign="top"><img src="/images/farnell_elcomp.jpg" alt="Электронные компоненты по каталогу Farnell" width="200" height="229">
	<img src="/images/farnell-2.gif" width="60" height="68"> <img src="/images/farnell-3.gif" width="60" height="68"><img src="/images/farnell-4.gif" width="60" height="68"><img src="/images/farnell-5.gif" width="60" height="68"><img src="/images/farnell-6.gif" width="60" height="68">
	<img src="/images/farnell-7.gif" width="60" height="68"><img src="/images/farnell-8.gif" width="60" height="68"><img src="/images/farnell-9.gif" width="60" height="68"><img src="/images/farnell-10.gif" width="60" height="68">
	<img src="/images/farnell-11.gif" width="60" height="68"><img src="/images/farnell-12.gif" width="60" height="68"><img src="/images/farnell-13.gif" width="60" height="68"></td>
	</tr>
</table>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td height="38" align="center" background="/images/bg_circuit_fw.gif" bgcolor="#000000"><span class="style12"><strong>Чтобы заказать изделия по каталогу Farnell или узнать о возможности поставки изделия:</strong></span></td>
	</tr>
	<tr>
	<td>
		<ol>
			<li>Позвоните по многоканальному телефону/факсу (812) 3254220, 3256420 (Елена Михайловна) </li>
			<li>Заполните <a href="#">форму заявки</a> на сайте (сообщение мгновенно поступит в ящик эл. почты ответственного менеджера)</li>
		</ol>

		<p>Мы оперативно обработаем на Вашу заявку, выставим счет,осуществим поставку.</p>
	</td>
</tr>
</table>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>