<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Выбор промежуточных реле RELECO");
//header("Content-type: text/html; charset=utf8;");
?>
<?php
require_once $_SERVER['DOCUMENT_ROOT']."/classes/Mysql2.class.php";
$db = new Mysql2();

require_once $_SERVER['DOCUMENT_ROOT']."/classes/Filter.class.php";

$filter = new Filter('Реле промежуточные');
//var_dump($filter);
$_SESSION['fltr_obj'] = $filter;
?>


<script>
$(function(){
	// при изменении одного из списков, перебираем все, и отсылаем текущие отмеченные параметры
	var fieldsArr = new Object;
	$("select.fltr").change(function (){
		$("select.fltr").each(function(e){
			fieldsArr[e] = {
					'value_id': $("select[name="+$(this).attr('name')+"] option:selected").val(),
					'prop_id': $(this).data('prop_id'),
					'table': $(this).data('table')
					};
				});	
		$.get('filter.php?section=<?=$filter->section;?>', fieldsArr, function(data){
//			alert(data);
			$("#fltrResult").html(data);
			});
	});
	$("#fltrPagination li").live('click', function(){
		var page = $(this).text()-1;
		$.get('filter.php?section=<?=$filter->section;?>&page='+page, fieldsArr, function(data){
//			alert(data);
			$("#fltrResult").html(data);
			});
		
		});

});
</script>
<style type="text/css">
/* список устройств */
#fltrResult{
	list-style-type: none;
}
/*	список пагинации	*/
#fltrPagination li{
	display: inline;
	padding: 4px;
	margin: 2px;
	cursor: pointer;
	border: green 1px solid;
}
</style>
<?php 
// устанавливаем контролы фильтра
$filter->addField('Назначение устройста', Filter::STRING_LIST, 'любое');
$filter->addField('Кол-во контактов', Filter::STRING_LIST, "любое");
$filter->addField('Монтаж', Filter::STRING_LIST, "любой");
$filter->addField('Номинальный ток', Filter::INT_LIST, "любой");
$filter->addField('Напряжение катушки', Filter::STRING_LIST, "любое");

//$filter->showPropInCard('Назначение устройста');

$page = isset($_GET['page'])?((int) $_GET['page']): 0;
$filter->setPage($page);
?>
<div>
<ol id="fltrResult">
<?php
$filter->getAllDevices(1, 'html');
?>
</ol>
</div>




<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>