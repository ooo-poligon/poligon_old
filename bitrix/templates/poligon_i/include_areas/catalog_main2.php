<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div id="secondColumnCatalog">
<?
$SECTIONS = Array(74, 5414, 5535, 5583);
$QUANTITY = Array(8, 4, 1, 3);

if(CModule::IncludeModule("iblock"))
{
	foreach ($SECTIONS as $p=>$sec)
	{
		echo '<ul>';
		$res = CIBlockSection::GetByID($sec);
		if($ar_res = $res->GetNext())
			echo '<li><a href="/catalog/index.php?SECTION_ID='.$ar_res["ID"].'"><b>'.$ar_res["NAME"].'</b></a></li>';
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
		echo '</ul>';
	}
}
?>
</div>