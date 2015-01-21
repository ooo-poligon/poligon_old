<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?php if (!empty($arResult)):?>
<?php $i=0;?><ul class="topnav_menu">
<?php foreach($arResult as $arItem):?>
	<?php if ($arItem["PERMISSION"] > "D"):?>
	<?php if ($arItem["LINK"]=="/content/price/") $i=1;
	elseif ($arItem["LINK"]=="/content/feedback/catalog_order/catalogs.php") $i=5;
	elseif ($arItem["LINK"]=="/content/feedback/quick_order/") $i=3;		elseif ($arItem["LINK"]=="/pdf/") $i = ' pdf';
	elseif ($arItem["LINK"]=="/content/vacancies/") $i = ' vacancies';
	else $i=1000;
	?>		<li>
			<a class="nav_point<?=$i;?>" href="<?=$arItem["LINK"]?>" style="white-space: nowrap;">
				<?=$arItem["TEXT"]?>
			</a>
		</li>	<?php endif?><?php endforeach?></ul>
<?php endif?>