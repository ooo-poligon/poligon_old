<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?php
$fp = fopen($_SERVER['DOCUMENT_ROOT']."/upload/123.csv", "r");  
fgets($fp); //���������� ������ ������ � �����
$cols = explode(';',fgets($fp));
//������������ ���� ����������
for ($i=0;$i<count($cols);$i++)
{
	if (substr_count($cols[$i],'IP_PRICE')&&!substr_count($cols[$i],'IP_PRICED'))
	{
		$num[]=$i;
		$p=explode('IP_PRICE',$cols[$i]);
		$num_name[$i]=$p[1];
	}
}

while(!feof($fp))
{
	$i=0; //������� ������� ������ �����

		$data[$i] = fgets($fp); //������ ������ �� �����
		$g=0; //������� ����� 
		$gd=0; //���������� �����
		$temp = explode(";",$data[$i]); //�������
		//���������� �������� �� ����� � ����������
		$name = trim($temp[2]);
		//echo '<pre>';
		//var_dump($temp);
		//echo '</pre>';
	$p=0;
	$price=array();
	for ($i=$num[0];$i<=$num[count($num)-1];$i++)
	{
		if (trim($temp[$i]))
		{		
			$price[$p]["PRICE"] = trim($temp[$i]);
			$price[$p]["QUANTITY"] = $num_name[$i];
			//echo $num_name[$i].' - '.$price[$i].'<br>';
			$p++;
		}
	}
	//����� �������� � ����
	if(CModule::IncludeModule("iblock"))
	{
		$arSelect = Array("ID", "NAME");
		$arFilter = Array("IBLOCK_ID"=>4, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y", "NAME"=>$name);
		$res = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);
		if ($ob = $res->Fetch())
		{
			for($i=0;$i<$p;$i++)
			{	
				if ($i!=$p){
					if ($i==0)	
						CPrice::SetBasePrice($ob["ID"],$price[$i]["PRICE"],"EUR",$price[$i]["QUANTITY"],$price[$i+1]["QUANTITY"]);

						//echo 'CPrice::SetBasePrice('.$ob["ID"].','.$price[$i]["PRICE"].',"EUR",'.$price[$i]["QUANTITY"].','.$price[$i+1]["QUANTITY"].')';
					else
					{
						$qu =$price[$i]["QUANTITY"]+1;
						CPrice::SetBasePrice($ob["ID"],$price[$i]["PRICE"],"EUR",$qu,$price[$i+1]["QUANTITY"]);
						//echo 'CPrice::SetBasePrice('.$ob["ID"].','.$price[$i]["PRICE"].',"EUR",'.$qu.','.$price[$i+1]["QUANTITY"].')';
					}
				}			
				else
					//echo 'CPrice::SetBasePrice('.$ob["ID"].','.$price[$i]["PRICE"].',"EUR",'.$price[$i]["QUANTITY"].',"")';
					CPrice::SetBasePrice($ob["ID"],$price[$i]["PRICE"],"EUR",$price[$i]["QUANTITY"],"");
				echo '<br>';
			}
		}	
	}
}


?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
