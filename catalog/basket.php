<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<script>
function add2b()
{
	if (document.getElementById('min_quantity'))
	{
		if (parseInt(document.getElementById('min_quantity').value) > parseInt(document.getElementById('qua').value))
			alert('����������� ���-�� ��� �������: '+document.getElementById('min_quantity').value+'��.');
		else {
		document.getElementById('basketform').submit();
		//self.close();
		}
	}	
	else
	{
		document.getElementById('basketform').submit();
		//self.close();
	}
	return false;
}
</script>
<form id="basketform" action="/catalog/basket.php?ELEMENT_ID=<?=$_REQUEST["ELEMENT_ID"]?>" method="POST">
<table cellpadding=5 cellspacing=0 id="_table" width="380">
	<tr>
		<td colspan="3">���������� � ������� ����������: </td>
	</tr>
	<tr>
		<td colspan="3"><b>
			<?if(CModule::IncludeModule("iblock"))
			{
				$res = CIBlockElement::GetByID($_REQUEST["ELEMENT_ID"]);
				if($obRes = $res->GetNextElement()){
					
					$props = $obRes->GetProperties();
					$ar_res = $obRes->GetFields();
					echo $ar_res['NAME'].'<br>'.$ar_res['PREVIEW_TEXT'];
					$min_quan = $props["min_quan"]["VALUE"];
				}

			}?></b>
			<br>
			<?
				if ($min_quan)
					echo '����������� ���-�� ��� �������: '.$min_quan.' �� <input type="hidden" id="min_quantity" value='.$min_quan.'>'?>

		</td>
	</tr>
	<tr>
		<Td width=100>����������*:</td><td width=70><input id="qua" name="quantity" type="text" size="4" value=""></td><td><input type="submit" onClick="add2b()" value="��������"><input type="button" value="X" onClick="self.close()"></td>
	</tr>
	<tr>
		<Td colspan="2">������� �� ������:</td><td><b>
			<?
				$db_res1 = CCatalogProduct::GetList(
					array(),
					array("ID" => $ar_res['ID']),
					false,
					array()
				    );
				if ($ar_res1 = $db_res1->Fetch())
				{
				if (!$ar_res1["QUANTITY"]){
					if (!$props["srok"]["VALUE"])
						echo '���';
					else echo $props["srok"]["VALUE"]; 					
					}
				    else echo '��';
				}?>				
			</b>		
		</td><td></td>
	</tr>
</table>
<?$APPLICATION->SetTitle('���������� � �������. '.$ar_res["PREVIEW_TEXT"])?>
<br>

<?/*$APPLICATION->IncludeFile(
	$APPLICATION->GetTemplatePath("/catalog/basket_text.php"),
	Array(),
	Array("MODE"=>"html")
);*/?>
<?
if ($min_quan){
echo '<div style="border-top:1px solid #000; width:150px;height:1px;"></div>
<div style="font-size:10px">';					
echo '* ������ ����� ����� ������ ������ � ���������� �� '.$min_quan.' ��';
echo '</div>';
}
?>
</form>
<?if (intval($_REQUEST["ELEMENT_ID"])&&intval($_REQUEST["quantity"])){
	if (CModule::IncludeModule("sale")&&CModule::IncludeModule("catalog")&&$_REQUEST["quantity"]>=$min_quan)
	{	  
		Add2BasketByProductID(
                $_REQUEST["ELEMENT_ID"],
                $_REQUEST["quantity"],array());
	}
echo '<script>self.close()</script>';
}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
