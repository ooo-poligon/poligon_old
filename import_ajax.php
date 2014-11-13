<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
<?php
header("Content-type: text/plain; charset=windows-1251");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);

$start_time = time();
$iblock = 5;
$finish=0;
$fp = fopen($_SERVER['DOCUMENT_ROOT']."/upload/".$_REQUEST['file'], "r");    
if (!$_SESSION["pointer"]) 
{
	fgets($fp); //пропускаем первую строку в файле
	
	$cols = explode(';',fgets($fp));   
 	for ($i=0;$i<count($cols);$i++)
    {
            if (substr_count($cols[$i],'IP_PRICE')&&!substr_count($cols[$i],'IP_PRICED'))
            {
                    $num[]=$i;
                    $p=explode('IP_PRICE',$cols[$i]);
                    $num_name[$i]=$p[1];
            }
    }
	$_SESSION['num'] = $num;
	$_SESSION['num_name'] = $num_name;
	//fgets($fp);//пропускаем 2 строку в файле
	$_SESSION['pointer'] = ftell($fp); 
}

fseek($fp, $_SESSION["pointer"]);

$i=0; //счетчик массива данных файла
$end=0; //разница времени начала и после каждого действия
if ($_REQUEST) $step = $_REQUEST["step"]-1;
while($end<=$step){
    if (ftell($fp)==filesize($_SERVER['DOCUMENT_ROOT']."/upload/".$_REQUEST['file']))
    { 
	   $finish=1;
       echo '1';
	   $end=$step+100;
    }
    else
    {
		$i=0; //счетчик массива данных файла
                    $data[$i] = fgets($fp); //строка данных из файла
                    $g=0; //счетчик групп 
                    $gd=0; //диссчетчик групп
                    $temp = explode(";",$data[$i]); //понятно
                    //Записываем значение из файла в переменные
                    $name = trim($temp[2]);
                    //echo '<pre>';
                    //var_dump($temp);
                    //echo '</pre>';
            $p=0;
            $price=array();
            for ($i=$_SESSION['num'][0];$i<=$_SESSION['num'][count($_SESSION['num'])-1];$i++)
            {
                    if (trim($temp[$i]))
                    {		
                            $price[$p]["PRICE"] = str_replace(",", ".", $temp[$i]);
                            $price[$p]["PRICE"] = str_replace(" ", "", $price[$p]["PRICE"]);
                            $price[$p]["QUANTITY"] = $num_name[$i];
                            //echo $num_name[$i].' - '.$price[$i].'<br>';
                            $p++;
                    }
            }
			//var_dump($price);
            //Поиск элемента в базе
            if(CModule::IncludeModule("iblock"))
            {
                    $arSelect = Array("ID", "NAME");
                    $arFilter = Array("IBLOCK_ID"=>4, "NAME"=>$name);
                    $res = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);
                    if ($ob = $res->Fetch())
                    {
							for($i=0;$i<$p;$i++)
                            {	
                                    if ($i!=$p){
                                            if ($i==0)	
											{
													$PRODUCT_ID = $ob["ID"];
													$PRICE_TYPE_ID = 1;
													$qu =$price[$i+1]["QUANTITY"]-1;
													$arFields = Array(
														"PRODUCT_ID" => $PRODUCT_ID,
														"CATALOG_GROUP_ID" => $PRICE_TYPE_ID,
														"PRICE" => $price[$i]["PRICE"],
														"CURRENCY" => "EUR",
														"QUANTITY_FROM" => $price[$i]["QUANTITY"],
														"QUANTITY_TO" => $qu
													);

													$res = CPrice::GetList(
															array(),
															array(
																	"PRODUCT_ID" => $PRODUCT_ID,
																	"CATALOG_GROUP_ID" => $PRICE_TYPE_ID,
																	"QUANTITY_FROM" => $price[$i]["QUANTITY"],
																	"QUANTITY_TO" => $qu
																)
														);

													if ($arr = $res->Fetch())
													{
														//CPrice::Delete($arr["ID"]);
														CPrice::Update($arr["ID"], $arFields);
														//CPrice::Add($arFields);
														//echo $PRODUCT_ID;
													}
													else
													{
														CPrice::Add($arFields);
													}
											}
                                            else
                                            {
													$PRODUCT_ID = $ob["ID"];
													$PRICE_TYPE_ID = 1;
													$qu =$price[$i]["QUANTITY"];
													$qw =$price[$i+1]["QUANTITY"]-1;
													$arFields = Array(
														"PRODUCT_ID" => $PRODUCT_ID,
														"CATALOG_GROUP_ID" => $PRICE_TYPE_ID,
														"PRICE" => $price[$i]["PRICE"],
														"CURRENCY" => "EUR",
														"QUANTITY_FROM" => $qu,
														"QUANTITY_TO" => $qw
													);

													$res = CPrice::GetList(
															array(),
															array(
																	"PRODUCT_ID" => $PRODUCT_ID,
																	"CATALOG_GROUP_ID" => $PRICE_TYPE_ID,
																	"QUANTITY_FROM" => $qu,
																	"QUANTITY_TO" => $qw
																)
														);

													if ($arr = $res->Fetch())
													{
														//CPrice::Delete($arr["ID"]);
														CPrice::Update($arr["ID"], $arFields);
														//echo $PRODUCT_ID;
													}
													else
													{
														CPrice::Add($arFields);
													}
                                            }
                                    }			
                                    else
									{
													$PRODUCT_ID = $ob["ID"];
													$PRICE_TYPE_ID = 1;
													$qu =$price[$i]["QUANTITY"];
													$arFields = Array(
														"PRODUCT_ID" => $PRODUCT_ID,
														"CATALOG_GROUP_ID" => $PRICE_TYPE_ID,
														"PRICE" => $price[$i]["PRICE"],
														"CURRENCY" => "EUR",
														"QUANTITY_FROM" => $price[$i]["QUANTITY"],
														"QUANTITY_TO" => ""
													);

													$res = CPrice::GetList(
															array(),
															array(
																	"PRODUCT_ID" => $PRODUCT_ID,
																	"CATALOG_GROUP_ID" => $PRICE_TYPE_ID,
																	"QUANTITY_FROM" => $price[$i]["QUANTITY"],
																	"QUANTITY_TO" => ""
																)
														);

													if ($arr = $res->Fetch())
													{
													//CPrice::Delete($arr["ID"]);
														CPrice::Update($arr["ID"], $arFields);
													//echo $PRODUCT_ID;
													}
													else
													{
														CPrice::Add($arFields);
													}
									}
                            }
                    }	
            }
        $i++;
        $time = time();
        $end = $time - $start_time;
	}
}
$_SESSION['pointer'] = ftell($fp); 
fclose($fp);
if ($finish==1) {
$_SESSION['pointer']='';
$_SESSION['num']='';
$_SESSION['num_name']='';
}
  ?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>

