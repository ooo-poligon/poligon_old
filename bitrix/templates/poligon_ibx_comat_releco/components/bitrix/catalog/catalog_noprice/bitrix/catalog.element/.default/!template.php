<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<script type="text/javascript" src="/bitrix/templates/poligon/js/ajax.js"></script>
<?
$res = CIBlockSection::GetByID($arResult["IBLOCK_SECTION_ID"]);
if($ar_res = $res->GetNext())
  $section_name = $ar_res['NAME'];
$res1 = CIBlockSection::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>$arResult["IBLOCK_ID"], "ID"=>$ar_res["IBLOCK_SECTION_ID"]), false, array("NAME","UF_PROIZV"));
if($arSect = $res1->GetNext()){ 
	$proizv = $arSect["UF_PROIZV"];
	$section_name2 = $arSect['NAME'];
}

$title_main = $section_name2.' '.$proizv.' > '.$section_name.' > '.$arResult["NAME"];
$img_alt = $section_name2.' '.$proizv.' '.$section_name.' '.$arResult["NAME"];
$APPLICATION->SetTitle($title_main);?>
<div class="catalog-element">
	<table width="100%" border="0" cellspacing="0" cellpadding="2">
		<tr>
			<td>
				<div style="width: 50%; float: left;"><b class="name"><?=$arResult["NAME"]?> <? if ($arResult["PROPERTIES"]["article"]["VALUE"]) echo '('.$arResult["PROPERTIES"]["article"]["VALUE"].')';?></b><br/><?//var_dump($arResult["PRICES"]);?><b><?=$arResult["PREVIEW_TEXT"]?></b><br><small><?=$section_name?></small></div>
				<div style="float: right; max-width: 50%;">
				
				<?
				//
				$price = $onStore = null;
				$productProps = CCatalogProduct::GetByID($arResult["ID"]);
				if($productProps["QUANTITY"]>9)
					$onStore = "На складе: {$productProps["QUANTITY"]}шт.";
				elseif($productProps["QUANTITY"]>0)
					$onStore = "На складе: <10шт. ";
				elseif($arResult["PROPERTIES"]["srok"]["VALUE"]>0)
					$onStore = "Срок поставки: {$arResult["PROPERTIES"]["srok"]["VALUE"]}"; //"Уточните наличие и сроки поставки по телефону (812)325-42-20";

				$PRICE_1 = getPrice($arResult["PROPERTIES"]["BASE"]["VALUE"]);
				$PRICE_10 = getPrice($arResult["PROPERTIES"]["RETAIL"]["VALUE"]);
				$PRICE_50 = getPrice($arResult["PROPERTIES"]["WHOLESALE"]["VALUE"]);
				
				/* про нормоупаковку */	
				if($PRICE_1 == 0 && $PRICE_10 > 0){ // вероятно данная позиция продаётся по 10 штук. находим штучный аналог 
					$singleArt = substr($arResult["PROPERTIES"]["article"]["VALUE"], 0, -1);
					$singleArr = CIBlockElement::GetList(
						array(), 
						array(
							"PROPERTY_article" => $singleArt));
					$singleItem = $singleArr->GetNext();
				}elseif($PRICE_1 > 0){ // есть штучная цена, значит стоит поискать нормоупаковку по 10
					$isSingle = true;
					$tennerArt = $arResult["PROPERTIES"]["article"]["VALUE"]."%";
					$tennerArr = CIBlockElement::GetList(
						array("name"=>"desc"), 
						array(
							"PROPERTY_article" => $tennerArt));
					$tennerItem = $tennerArr->GetNext();
					//if($tennerItem["PROPERTY_ARTICLE_VALUE"] != $arResult["PROPERTIES"]["article"]["VALUE"]){ // нашлась нормоупаковка 10
					//$tennerItemProps = GetIBlockElement($singleItem["ID"]);
				}
				
				if($singleItem){ // нормоупаковка десять, и есть штучный аналог
					$singleProps = GetIBlockElement($singleItem["ID"]);
					if($singleProps["PROPERTIES"]["BASE"]["VALUE"] > 0)
						$price .= "<span>Цена за 1+: <strong>".getPrice($singleProps["PROPERTIES"]["BASE"]["VALUE"])."</strong>
					(арт. <a title='нормоупаковка 1 штука' href='{$singleProps["DETAIL_PAGE_URL"]}'>{$singleProps["PROPERTIES"]["article"]["VALUE"]}</a>)</span><br/>";
					if($PRICE_10 > 0)
						$price .= "<span>Цена за 10+: <strong>{$PRICE_10}</strong> (уп. 10 штук)</span><br/>";
					if($PRICE_50 > 0)
						$price .= "<span>Цена за 50+: <strong>{$PRICE_50}</strong></span><br/>";
				}elseif($tennerItem){ // штучная упаковка, и есть аналог 10
					$tennerProps = GetIBlockElement($tennerItem["ID"]);
					$price .= "<span>Цена за 1+: <strong>{$PRICE_1}</strong></span><br/>";
					//if($PRICE_10 > 0){
						//$price .= "<span>Цена за 10+: {$PRICE_10}</span><br/>";
					if($tennerProps["PROPERTIES"]["article"]["VALUE"] != $arResult["PROPERTIES"]["article"]["VALUE"]){ // нашлась нормоупаковка 10
						$price .= "<span>Цена за 10+: <strong>".getPrice($tennerProps["PROPERTIES"]["RETAIL"]["VALUE"])."</strong> (арт. <a title='нормоупаковка 10 штук' href='{$tennerProps["DETAIL_PAGE_URL"]}'>{$tennerProps["PROPERTIES"]["article"]["VALUE"]}</a>)</span><br/>";
					}elseif($PRICE_10 > 0){
						$price .= "<span>Цена за 10+: <strong>{$PRICE_10}</strong></span><br/>";
					}
					if($PRICE_50 > 0)
						$price .= "<span>Цена за 50+: <strong>{$PRICE_50}</strong></span><br/>";
				}elseif($isSingle){ // просто штучная позиция
					$price .= "<span>Цена за 1+: <strong>{$PRICE_1}</strong></span><br/>";
					if($PRICE_10 > 0)
						$price .= "<span>Цена за 10+: <strong>{$PRICE_10}</strong></span><br/>";
					if($PRICE_50 > 0)
						$price .= "<span>Цена за 50+: <strong>{$PRICE_50}</strong></span><br/>";
				}elseif(!$isSingle){ // норм. 10, без штучного аналога, едрить ё! 
					if($PRICE_10 > 0)
						$price .= "<span>Цена за 10+: <strong>{$PRICE_10}</strong> (уп. 10 штук)</span><br/>";
					if($PRICE_50 > 0)
						$price .= "<span>Цена за 50+: <strong>{$PRICE_50}</strong></span><br/>";
				}
				
				if($price || $onStore){
					print '<div id="onStore">
					'.$price.'
					'.$onStore.'
					</div>';
				}
				//
				?>
				<!-- debug
				<?
				//var_dump($tennerItem);
				//var_dump($singleProps);
				?>
				-->
				</div>
			</td>
		</tr>
		<tr>
			<td>Производитель: <b><?=($arResult["PROPERTIES"]["producer_abbr"]["VALUE"]?$arResult["PROPERTIES"]["producer_abbr"]["VALUE"]:$arResult["PROPERTIES"]["producer_full"]["VALUE"]); ?></b><br/><br/></td>
		</tr>
		<tr>
			<td valign="top">
			<table>
				<tr>
					<td valign="top">
						<img src="/images/_<?=strtolower($arResult["PROPERTIES"]["producer_full"]["VALUE"]);?>.gif" alt="" style="width: 125px; display: block; padding: 2px;"/>
						<?=CFile::ShowImage('/images/'.$arResult["PROPERTIES"]["link"]["VALUE"], 200, 200, "border=0 alt='".$img_alt."' style='vertical-align:text-top; margin-right:10px'", "", false,false);?>
						<?if($arResult["PROPERTIES"]["pdf"]["VALUE"])
							echo '<br /><a href="/PDF/'.$arResult['PROPERTIES']['pdf']['VALUE'].'" class="pdf">Техническая информация</a>';?>
					</td>
					<?
					if($arResult["~DETAIL_TEXT"])
					{
					print "<td>";
					$APPLICATION->IncludeFile(
							$APPLICATION->GetTemplatePath("/bitrix/templates/poligon/include_areas/element_text.php"),
							Array(),
							Array("MODE"=>"html")
						);
					print "{$arResult["~DETAIL_TEXT"]}</td>";
					}
					?>
					
					
				</tr>
			</table>
			</td><!-- close tags please!!!!!!!!!!! -->
		</tr>
	</table>
	<?if(count($arResult["LINKED_ELEMENTS"])>0):?>
		<br /><b><?=$arResult["LINKED_ELEMENTS"][0]["IBLOCK_NAME"]?>:</b>
		<ul>
		<?foreach($arResult["LINKED_ELEMENTS"] as $arElement):?>
			<li><a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><?=$arElement["NAME"]?></a></li>
		<?endforeach;?>
		</ul>
	<?endif?>
	<?
	// additional photos
	$LINE_ELEMENT_COUNT = 2; // number of elements in a row
	if(count($arResult["MORE_PHOTO"])>0):?>
		<a name="more_photo"></a>
		<?foreach($arResult["MORE_PHOTO"] as $PHOTO):?>
			<img src="<?=$PHOTO["SRC"]?>" width="<?=$PHOTO["WIDTH"]?>" height="<?=$PHOTO["HEIGHT"]?>" alt="<?=$PHOTO["ALT"]?>" title="<?=$arResult["NAME"]?>"/><br />
		<?endforeach?>
	<?endif?>
	<?
	// additional photos
//	$LINE_ELEMENT_COUNT = 2; // number of elements in a row
	$photosArr = array($arResult["PROPERTIES"]['img1']["VALUE"] => $arResult["PROPERTIES"]['imgTitle1']["VALUE"], 
						$arResult["PROPERTIES"]['img2']["VALUE"] => $arResult["PROPERTIES"]['imgTitle2']["VALUE"], 
						$arResult["PROPERTIES"]['img3']["VALUE"] => $arResult["PROPERTIES"]['imgTitle3']["VALUE"]);
	print '<!-- photos ';

	unset($photosArr[""]);
	//var_dump(count($photosArr));
	//var_dump($photosArr);
	print ' -->';
	
	if(count($photosArr)>0):?>
	<div class="images">
		<?foreach($photosArr as $img => $title):?>
			<img src="/images/<?=$img?>" alt="<?=$title?>" title="<?=$title?>" />
		<?endforeach?>
	</div>
	<?endif?>
	
			<?
		/* вывод элементов см. также, по артикулам */
		relatedElementsByArticles(array($arResult["PROPERTIES"]["relatedElement1"]["VALUE"],
		$arResult["PROPERTIES"]["relatedElement2"]["VALUE"],
		$arResult["PROPERTIES"]["relatedElement3"]["VALUE"],
		$arResult["PROPERTIES"]["relatedElement4"]["VALUE"],
		$arResult["PROPERTIES"]["relatedElement5"]["VALUE"]));
		?>
	<?if(is_array($arResult["SECTION"])):?>
		<br /><a href="<?=$arResult["SECTION"]["SECTION_PAGE_URL"]?>"><?=GetMessage("CATALOG_BACK")?></a>
	<?endif?>
		<br/><br/><br/><br/>
</div>