<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<ul id="news_list">
	<?foreach($arResult["ITEMS"] as $arItem):?>
		<li><a href="<?echo $arItem["DETAIL_PAGE_URL"]?>"><span><?echo $arItem["DISPLAY_ACTIVE_FROM"]?></span><br /><?echo $arItem["NAME"]?></a></li>
	<?endforeach;?>
</ul>	
