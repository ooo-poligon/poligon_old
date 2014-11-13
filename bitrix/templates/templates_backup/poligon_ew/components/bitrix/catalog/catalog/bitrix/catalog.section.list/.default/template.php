<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><div id="catalog-section-list">
<?
$rsSect1 = CIBlockSection::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>4, "ID"=>intval($_REQUEST["SECTION_ID"])), false, array("UF_PROIZV"));
		if ($arSect1 = $rsSect1->GetNext())
		{ 
			$proizv = $arSect1["UF_PROIZV"];
		}

$res = CIBlockSection::GetByID($arResult["SECTION"]["IBLOCK_SECTION_ID"]);
if($ar_res = $res->GetNext())
{
	$section_name = $ar_res['NAME'];
        $APPLICATION->SetTitle($section_name.' > '.$arResult["SECTION"]["NAME"].' '.$proizv);
}
else
{
        $APPLICATION->SetTitle($arResult["SECTION"]["NAME"]);	
}
?>
<?if ($_REQUEST["SECTION_ID"]&&$arResult["SECTION"]["DESCRIPTION"]) echo $arResult["SECTION"]["DESCRIPTION"].'<hr style="text-align:center; width:50%; color:#CCC">'?>

<table width="100%" cellpadding=0 cellspacing=0><tr><td valign="top"><ul>
<?
$i=0;
$CURRENT_DEPTH=$arResult["SECTION"]["DEPTH_LEVEL"]+1;
foreach($arResult["SECTIONS"] as $arSection):
	if($CURRENT_DEPTH<$arSection["DEPTH_LEVEL"])
		echo "<ul>";
	elseif($CURRENT_DEPTH>$arSection["DEPTH_LEVEL"])
		echo str_repeat("</ul><br>", $CURRENT_DEPTH - $arSection["DEPTH_LEVEL"]);
	$CURRENT_DEPTH = $arSection["DEPTH_LEVEL"];
$img_alt='';
$sec_preview='';
	if($arSection["DEPTH_LEVEL"]==$arResult["SECTION"]["DEPTH_LEVEL"]+1)
	{
		if ($i==3&&$arResult["SECTION"]["DEPTH_LEVEL"]<1) echo '</ul></td><td valign="top"><ul>';
		$rsSect = CIBlockSection::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>$arSection["IBLOCK_ID"], "ID"=>$arSection["ID"]), false, array("UF_*"));
		if ($arSect = $rsSect->GetNext())
		{ 
			$img_alt = $arSect["UF_SECT"];
			$sec_preview = $arSect["UF_SECT_PREVIEW"];
		}
                echo '<li><table><tr>';
		echo '<td><a href='.$arSection["SECTION_PAGE_URL"].'><b>'.$arSection["NAME"].'</b>';
		echo '</a><br><B>'.$sec_preview.'</b></td></tr></table></li>';
		$i++;
	?>
      <?}else{?>
	<li><a href=<?=$arSection["SECTION_PAGE_URL"]?>><?=$arSection["NAME"]?>
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