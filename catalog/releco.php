<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("����� ������������� ���� RELECO");
//header("Content-type: text/html; charset=utf8;");
?>
<?php
require_once $_SERVER['DOCUMENT_ROOT']."/classes/Mysql2.class.php";
$db = new Mysql2();

require_once $_SERVER['DOCUMENT_ROOT']."/classes/Filter.class.php";

$filter = new Filter('���� �������������');
//var_dump($filter);
$_SESSION['fltr_obj'] = $filter;
?>


<script>
$(function(){
	// ��� ��������� ������ �� �������, ���������� ���, � �������� ������� ���������� ���������
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
/* ������ ��������� */
#fltrResult{
	list-style-type: none;
}
/*	������ ���������	*/
#fltrPagination li{
	display: inline;
	padding: 4px;
	margin: 2px;
	cursor: pointer;
	border: green 1px solid;
}
</style>
<?php 
// ������������� �������� �������
$filter->addField('���������� ���������', Filter::STRING_LIST, '�����');
$filter->addField('���-�� ���������', Filter::STRING_LIST, "�����");
$filter->addField('������', Filter::STRING_LIST, "�����");
$filter->addField('����������� ���', Filter::INT_LIST, "�����");
$filter->addField('���������� �������', Filter::STRING_LIST, "�����");

//$filter->showPropInCard('���������� ���������');

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