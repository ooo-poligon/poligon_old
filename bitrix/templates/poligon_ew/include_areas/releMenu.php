<script>
$(function(){
	$('ul#releMenu li a').each(function(){
		if(document.location.pathname == $(this).attr('href')){
			$(this).parent().addClass('active');
		}
	});
});	
</script>
<ul id="releMenu">
	<li><a href="/rele/time-functions.php" title="Выбор реле времени по требуемой функции">Функции реле времени</a></li>
	<li><a href="/rele/timerele.php" title="выбор реле времени по комбинации функций">Подбор реле времени</a></li>
</ul>