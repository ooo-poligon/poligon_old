<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (!empty($arResult)):?>
<?$i=0;?>
<?foreach($arResult as $arItem):?>
<//?if ($i!=0&&$i!=2&&$i!=4){?>
	<?if ($arItem["PERMISSION"] > "D"):?>
	<?if ($arItem["LINK"]=="/content/price/") $i=1;
	elseif ($arItem["LINK"]=="/content/feedback/catalog_order/") $i=5;
	elseif ($arItem["LINK"]=="/content/feedback/quick_order/") $i=3;
	else $i=1000;
	?>
		<div class="nav_point<?=$i;?>"><a href="<?=$arItem["LINK"]?>"><nobr><?=$arItem["TEXT"]?></nobr></a><?//if($i==2){echo '&nbsp;прайс-лист';}?></div>
	<?endif?>
<?//}else $i++;?>
<?endforeach?>
<?endif?>