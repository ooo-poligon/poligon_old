<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<script type="text/javascript" src="<?=$componentPath?>/js/jquery.bxslider/jquery.bxslider.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?=$componentPath?>/js/jquery.bxslider/jquery.bxslider.css"/> 
<link rel="stylesheet" type="text/css" href="<?=$componentPath?>/style.css"/> 

<div id="landing_slider" style="width:<?
if($arParams["ELEMENT_COUNT_ON_PAGE"]==0)
{
  echo '910';
}else
{
   echo ($arParams["ELEMENT_COUNT_ON_PAGE"]*(220+10)-10);
}
?>px;">
	<div class="slider1">
	<?
		foreach($arResult as $cell=>$arElement){
			echo '<a href="'.$arElement["DETAIL_PAGE_URL"].'" class="slide"><div style="height:'.$arParams["HEIGHT_WRAP"].'px;">';
			echo CFile::ShowImage($arElement["DETAIL_PICTURE"],207,290,"class=slide_img");
			echo '</div>';
			if(($arParams["USE_ELEMENT_NAME"]== "N")&&($arParams["USE_ELEMENT_PRICE"]== "N"))
			{
				echo '</a>';
			}
			else
			{
				echo '<div class="title-slide1-wrapper">';
				if($arParams["USE_ELEMENT_NAME"]== "Y")
				{
					echo '<span class="title-slide1">'.$arElement["NAME"].'</span>';
				}
				if($arParams["USE_ELEMENT_PRICE"]== "Y")
				{
					if($arParams["USE_FRACTIONAL_VALUE"] == "N")
					{
						$price=intval($arElement["PRICE"]["PRICE"]);
					}
					else
					{
						$price=$arElement["PRICE"]["PRICE"];
					}
					echo '<div id="carusel-slide-bottom" style="justify-content: flex-end;  display: flex;margin-top: 10px;">
					<div class="carusel-price">Цена:&nbsp;</div><div id="carusel-price-value">'.$price.'</div><div class="carusel-price">&nbsp;руб.</div>
					</div></div></a>';
				}
				else
				{
					echo '</div></a>';
				}
			}
			//print_r($arElement["PRICE"]["PRICE"]);//$arResult
		}
	?>
	</div>
</div>
<div style="clear:both;"></div>
<script type="text/javascript">
$(document).ready(function(){
  $('.slider1').bxSlider({
    slideWidth: 220,
    minSlides: 2,
    maxSlides: 4,
    slideMargin: 10
  });
});
</script>