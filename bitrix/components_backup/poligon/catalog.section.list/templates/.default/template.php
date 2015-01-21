<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div id="catalog-section-list">
<table style="width: 100%; border-spacing: 0px; padding: 0px;" ><tr><td style="vertical-align: top; width:50%;"><ul>
<?
$i=0;
$CURRENT_DEPTH=$arResult["SECTION"]["DEPTH_LEVEL"]+1;
foreach($arResult["SECTIONS"] as $arSection):
	if($CURRENT_DEPTH<$arSection["DEPTH_LEVEL"])
		echo "<ul>";
	elseif($CURRENT_DEPTH>$arSection["DEPTH_LEVEL"])
		echo str_repeat("</ul><br/>", $CURRENT_DEPTH - $arSection["DEPTH_LEVEL"]);
	$CURRENT_DEPTH = $arSection["DEPTH_LEVEL"];
	$img_alt='';
	$sec_preview='';

	if($arSection["DEPTH_LEVEL"]==$arResult["SECTION"]["DEPTH_LEVEL"]+1)
	{
		if ($i==6&&$arResult["SECTION"]["DEPTH_LEVEL"]<1) echo '</ul></td><td style="vertical-align: top;"><ul>';
		$rsSect = CIBlockSection::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>$arSection["IBLOCK_ID"], "ID"=>$arSection["ID"]), false, array("UF_*"));
		if ($arSect = $rsSect->GetNext())
		{ 
			$img_alt = $arSect["UF_SECT"];
			$sec_preview = $arSect["UF_SECT_PREVIEW"];
		}
                echo '<li><table><tr>';
		echo '<td><a href="'.$arSection["SECTION_PAGE_URL"].'"><b>'.$arSection["NAME"].'</b>';
		echo '</a><br/><b>'.$sec_preview.'</b></td></tr></table></li>';
		$i++;
	?>
      <?}else{?>
	<li><a href="<?=$arSection["SECTION_PAGE_URL"]?>"><?=$arSection["NAME"]?>
<?if ($arResult["SECTION"]["DEPTH_LEVEL"]>=1)
{
		$rsSect = CIBlockSection::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>$arSection["IBLOCK_ID"], "ID"=>$arSection["ID"]), false, array("UF_*"));
		if ($arSect = $rsSect->GetNext())
		{ 
			$sec_preview = $arSect["UF_SECT_PREVIEW"];
		}
}
?>
</a><?if ($sec_preview) echo '&nbsp;&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp;&nbsp;&nbsp;'.$sec_preview;?></li>
<?}?>
<?endforeach?>
</ul></td></tr></table>
</div>
