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
	<li><a href="/rele/time-functions.php" title="����� ���� ������� �� ��������� �������">������� ���� �������</a></li>
	<li><a href="/rele/timerele.php" title="����� ���� ������� �� ���������� �������">������ ���� �������</a></li>
</ul>