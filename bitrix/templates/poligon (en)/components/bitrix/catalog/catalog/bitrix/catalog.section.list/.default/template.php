<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$res = CIBlockSection::GetByID($arResult["SECTION"]["IBLOCK_SECTION_ID"]);
if($ar_res = $res->GetNext())
{
	$section_name = $ar_res['NAME'];
        $APPLICATION->SetTitle($section_name.' > '.$arResult["SECTION"]["NAME"]);
}
else
{
        $APPLICATION->SetTitle($arResult["SECTION"]["NAME"]);	
}
?>
<div id="catalog-section-list">
<table width="100%" cellpadding=0 cellspacing=0><tr><td valign="top"><ul>
<?
$i=0;
$CURRENT_DEPTH=$arResult["SECTION"]["DEPTH_LEVEL"]+1;
foreach($arResult["SECTIONS"] as $arSection):
	if($CURRENT_DEPTH<$arSection["DEPTH_LEVEL"])
		echo "<ul>";
	elseif($CURRENT_DEPTH>$arSection["DEPTH_LEVEL"])
		echo str_repeat("</ul>", $CURRENT_DEPTH - $arSection["DEPTH_LEVEL"]);
	$CURRENT_DEPTH = $arSection["DEPTH_LEVEL"];
$img_alt='';
$sec_preview='';
	if($arSection["DEPTH_LEVEL"]==1)
	{
		if ($i==3) echo '</ul></td><td valign="top"><ul>';
		$rsSect = CIBlockSection::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>$arSection["IBLOCK_ID"], "ID"=>$arSection["ID"]), false, array("UF_*"));
		if ($arSect = $rsSect->GetNext())
		{ 
			$img_alt = $arSect["UF_SECT"];
			$sec_preview = $arSect["UF_SECT_PREVIEW"];
		}
		if ($arSection["PICTURE"]) echo '<img alt="'.$img_alt.'" src="'.$arSection["PICTURE"]["SRC"].'" width="80" height="80" style="float:left;" />';
		echo '<li class="pic"><a href='.$arSection["SECTION_PAGE_URL"].'><b>'.$arSection["NAME"].'</b>';
		echo '</a><br><B>'.$sec_preview.'</b></li>';
		$i++;
	?>
      <?}else{?>
	<li><a href=<?=$arSection["SECTION_PAGE_URL"]?>><?=$arSection["NAME"]?></a></li>
<?}?>
<?endforeach?>
</ul></td></tr></table>
</div>
