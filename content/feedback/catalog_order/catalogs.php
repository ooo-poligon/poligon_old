<?
// убираем халявщиков
if(stristr($_SERVER['HTTP_REFERER'], 'freedisk.ru')){
	header('Location: http://ya.ru');
}

if($_GET['action'] == 'order'
	&& $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){
$officeMails = array('gnato@poligon.info', 'popova@poligon.info');

foreach ($_POST as $key => $value)
	if($value == '')
		$_POST[$key] = 'не указано';
	else
		$_POST[$key] = iconv('UTF-8', 'WINDOWS-1251', $value);
		
$officeMsg  = "
<h1 style='font-size: 14px;'>Заполнена заявка на сайте http://poligon.info/</h1>
<dl>
	<dt>заказ: </dt>
	<dd><pre>{$_POST['order']}</pre></dd>
	<dt>имя: </dt>
	<dd>{$_POST['name']}</dd>
	<dt>компания: </dt>
	<dd>{$_POST['company']}</dd>	
	<dt>телефон: </dt>
	<dd>{$_POST['telephone']}</dd>
	<dt>адрес: </dt>
	<dd>{$_POST['address']}</dd>
	<dt>Заказаны следующие каталоги:</dt> 
	<dd>{$_POST['cat']}</dd>
</dl>";

$subject = 'заказ каталогов на http://poligon.info';

/* Для отправки HTML-почты вы можете установить шапку Content-type. */
$headers= "MIME-Version: 1.0\n";
$headers .= "Content-type: text/html; charset=windows-1251\n";

/* дополнительные шапки */
$headers .= "From: poligon.info <website@poligon.info>\n";

foreach($officeMails as $officeMail)
	mail($officeMail, $subject, $officeMsg, $headers);

if($_POST['email'] != '')
	mail($_POST['email'], $subject, $officeMsg, $headers);

die(1);	
}	


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("keywords", "каталоги");
$APPLICATION->SetPageProperty("description", "Печатный каталог продукции по почте");
$APPLICATION->SetTitle("Заказ доставки каталогов TELE, Comat/Releco, Graesslin, Benedict");
?>
<style>
#catalogsOrder{
	border: 1px black solid;
	#border-radius: 6px;
	width: 500px;
	float: left;
}

#catalogsOrder h2{
	margin: 0px 5px;
}

#catalogsOrder h3{
	margin: 0px;
}

#catalogsOrder form {
	height: 140px;
}
#catalogsOrder form div{
	margin: 5px;
	float: left;
}

#catalogsOrder form textarea{
	height: 36px;
}

label.pic {
	display: inline-block;
	width: 32px;
	vertical-align: top;
}

label.pic+*{
	width: 200px;
}
label.text{
	width: 180px;
	display: inline-block;
	font-weight: bold;
	cursor: pointer;
}

label[for=name] {
	background: url('/images/icons/user_suit.png') center no-repeat;
}
label[for=company] {
	background: url('/images/icons/application_home.png') center no-repeat;
}
label[for=telephone] {
	background: url('/images/icons/phone.png') center no-repeat;
}

label[for=address]{
	background: url('/images/icons/house.png') center no-repeat;	
}

#catalogsOrder input[type=submit]{
	float: right;
}
label[for=tele]{
	background: rgb(122, 181, 28);
}
label[for=releco]{
	background: rgb(35, 127, 185);
}
label[for=graesslin]{
	background: #B0BEB9;/*rgb(197, 218, 167);*/
}
label[for=benedict]{
	background: #2e8b57;/*#808B9F;*/
}

#catalogsThumbs{
	float: left;
	width: 175px;
	text-align: center;
	vertical-align: middle;
}

#catalogsThumbs img{
	/*width: 125px;*/
	height: 150px;
	display: none;
}
#catalogsOrder img{
	width: 120px;
	padding: 1px;
	display: none;	
}
</style>


<script>
<!--
$(function(){
	$('#catalogsOrder input[type=checkbox]').bind('click', function(){
		$('#catalogsThumbs img').hide();
		if($(this).is(":checked") == false){
			$('#catalogsThumbs img[alt='+$(this).attr('id')+']').hide();
		}
		else{
			$('#catalogsThumbs img[alt='+$(this).attr('id')+']').show('slow');
		}
	});
	
	
	$('#catalogsOrder form').bind('submit', function(){
		if($('#catalogsOrder textarea').val() == ''){
			$('label[for=address]').css('border', '1px red solid');
			$('#catalogsOrder textarea').css('border', '1px red solid');
			return false;
		}else{
			$('#catalogsOrder textarea').css('border', 'none');
		}
		if($('input[type=checkbox]:checked').length == 0){
			alert('Выберите каталоги, которые вы хотели бы получить');
			return false;	
		}else{
			var cat = new Array();
			$('input:checked').each(function(i){
				cat.push($(this).val());
			});
			//alert(cat.join(', '));
			
			$.post('/content/feedback/catalog_order/catalogs.php?action=order', {
					name: $('#name').val(),
					company: $('#company').val(),
					address: $('#address').val(),
					telephone: $('#telephone').val(),
					cat: cat.join(', ')
				 }, function(data){
				$('<p style="margin: 24px;">Ваша заявка принята. Каталоги будут отправлены по указанному адресу. </p>').replaceAll('#catalogsOrder form');
				$('#catalogsThumbs').hide();
				$('#catalogsOrder').append($('#catalogsThumbs').html());
				//$('#catalogsOrder img').hide();
				$(cat).each(function(i){
				//	alert(this);
					$('img[alt="'+this+'"]').show('slow');
				});
			});	
		}
		return false;
	});	
});
//-->
</script>

<p>Вы можете бесплатно заказать интересующие вас печатные каталоги.</p>

<div id="catalogsOrder" >
<h2>Заказ каталогов: </h2>
<form method="post">
<div style=" margin:auto; width: 250px; ">
	<h3>Ваши данные: </h3>
	<label for="name" class="pic" title="Введите своё имя">&nbsp;</label><input type="text" placeholder="Имя" name="name" id="name"/><br/>
	<label for="company" class="pic" title="Укажите название компании">&nbsp;</label><input type="text" placeholder="Компания" name="company" id="company"/><br/>
	<label for="telephone" class="pic" title="Введите контактный телефон">&nbsp;</label><input type="text" placeholder="Телефон" name="telephone" id="telephone"/><br/>
	<label for="address" class="pic" title="Укажите адрес">&nbsp;</label><textarea placeholder="Адрес" name="address" id="address"></textarea><br/>
</div>
<div style="width: 193px; margin:auto;">
	<h3>Выберите каталоги: </h3>
	<label for="tele" class="text">TELE</label><input type="checkbox" name="cat[]" value="tele" id="tele"/><br/>
	<label for="releco" class="text">Comat/Releco</label><input type="checkbox" name="cat[]" value="releco" id="releco"/><br/>
	<label for="graesslin" class="text">Graesslin</label><input type="checkbox" name="cat[]" value="graesslin" id="graesslin"/><br/>
	<label for="benedict" class="text">Benedict</label><input type="checkbox" name="cat[]" value="benedict" id="benedict"/><br/>
	<label for="cd" class="text">Компакт диск с pdf-файлами</label><input type="checkbox" name="cat[]" value="cd" id="cd"/><br/>
	<input type="submit" value="Заказать"/>
</div>
</form>
</div>

<div id="catalogsThumbs">

</div>

<div id="catalogs" style="clear: left;">
<br><br><br><br>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>