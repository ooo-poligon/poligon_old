<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div id="thirdColumnCatalog">
<?
$SECTIONS = Array(4847, 5094, 13, 5650);
$QUANTITY = Array(3, 6, 4, 6);

if(CModule::IncludeModule("iblock"))
{
	foreach ($SECTIONS as $p=>$sec)
	{
		echo '<ul style="margin-left:10px">';
		$res = CIBlockSection::GetByID($sec);
		if($ar_res = $res->GetNext())
			echo '<li><a href="/catalog/index.php?SECTION_ID='.$ar_res["ID"].'"><b>'.$ar_res["NAME"].'</b></a></li>';
			// эта строка была почему-то ниже раньше... см. негодующий комментарий
			$rsSect = CIBlockSection::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>$ar_res["IBLOCK_ID"],"ID"=>$sec), false, array("UF_*"));			
			if ($arSect = $rsSect->GetNext())
				print '<li><i>'.$arSect["UF_SECT_PREVIEW"].'</i></li>';	
			$db_list = CIBlockSection::GetList(Array("sort"=>"asc"), Array("SECTION_ID"=>$ar_res["ID"],"ACTIVE"=>"Y"), false);
			$db_list->NavStart($QUANTITY[$p]);
			$i=0;			
	  	    while($ar_result = $db_list->GetNext())
			{
				$i++;
				echo '<li class="arrow"><a href="/catalog/index.php?SECTION_ID='.$ar_result["ID"].'">'.$ar_result['NAME'].'</a>';
				if ($i==$QUANTITY[$p]) echo ' <a href="/catalog/index.php?SECTION_ID='.$ar_res["ID"].'">...</a>';
				echo '</li>';

			}
/***
* було вот так вот: 
* я понятия не имею почему...
* ИТ сказал, что на самом деле должно быть наверху, и будто бы всегда было наверху. 
* наверху так на верху. хуйнул на верх сей кусок. 

if ($sec==4847||$sec==13||$sec==5094||$sec==5102) 
{
		$rsSect = CIBlockSection::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>$ar_res["IBLOCK_ID"],"ID"=>$sec), false, array("UF_*"));
		if ($arSect = $rsSect->GetNext())
		{ 
			$sec_preview = '<li><i>'.$arSect["UF_SECT_PREVIEW"].'</i></li>';
		}
echo $sec_preview;
}*/

		echo '</ul>';
	}
}
?>
</div>