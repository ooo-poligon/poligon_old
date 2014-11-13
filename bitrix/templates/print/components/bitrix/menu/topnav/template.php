<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (!empty($arResult)):?>
<?$i=0;?>
<?foreach($arResult as $arItem):?>
	<?if ($arItem["PERMISSION"] > "D"):?>
		<div class="nav_point<?=$i++;?>"><a href="<?=$arItem["LINK"]?>"><nobr><?=$arItem["TEXT"]?></nobr></a><?if($i==2){echo '&nbsp;прайс-лист';}?></div>
	<?endif?>

<?endforeach?>
<?endif?>