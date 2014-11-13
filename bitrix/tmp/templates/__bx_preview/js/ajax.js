function run(id)
{
	/*jsAjaxUtil.ShowLocalWaitWindow('wait_id', 'main_div');
	jsAjaxUtil.InsertDataToNode('/catalog/basket.php?ELEMENT_ID='+id, 'ajax_container','Y');
	PutData();*/
		var o = window.open('/catalog/basket.php?ELEMENT_ID='+id, null, 'height=230,width=400,toolbar=no,location=no,status=no'); 
}
/*function PutData(data)
{
	document.getElementById('overlay').style.height=(document.getElementById('main_main').offsetHeight-65)+'px';
	document.getElementById('overlay').style.width=document.getElementById('main_main').offsetWidth+'px';
	document.getElementById('overlay').style.display='block';
	document.getElementById('ajax_container1').style.display='block';
	jsAjaxUtil.CloseLocalWaitWindow('wait_id', 'main_div');
}
function SendF(form)
{
	jsAjaxUtil.SendForm(form, 'Close()');
}
function Close()
{
	document.getElementById('ajax_container1').style.display='none';
	document.getElementById('overlay').style.display='none';
	document.getElementById('ajax_container').innerHTML='';
}*/

