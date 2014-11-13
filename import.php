<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?if ($USER->IsAdmin()):?>
<script src="/jquery.js"></script>
<script>
function request(file,step)
{
	$.ajax({
	  url: "import_ajax.php",
      type: "POST",
 	  data: "file="+file+"&step="+step,
	  success: function(html){
		if (html==1) finish(html);
		else request(file,step);
	  }
	});
	$("#loading").html('<table><tR><td><img src="/loading.gif"></td><td>��������...</td></tr></table>');
}

function run()
{
	if (!document.getElementById('file').value||!document.getElementById('step').value)
		alert('������� ��� ��������!');	
	else request(document.getElementById('file').value,document.getElementById('step').value)
}

function finish(html)
{
	$("#loading").html('������!');
}

</script>
<div id="loading"></div>
<table id="params" width="400">
    <tr>
        <th width="200">����: </th>
        <td>    
            <input class="inputtext" id="file" type="text" value="<?=$_REQUEST["file"]?>">
        </td>
    </tr>
    <tr>
        <th>������� ���: </th>
        <td>
            <input class="inputtext" id="step" type="text" name="step" value="1" size="2" maxlength="3"> �.
        </td>
    </tr>   
	<tr>
		<td colspan=2><input type="button" id="but" value="������" onclick="run()"></td>
	</tr>
	<tr>
		<td id="result">
			<?=$_SESSION['pointer']=''?>
		</td>
	</tr>
</table>
<?endif;?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
