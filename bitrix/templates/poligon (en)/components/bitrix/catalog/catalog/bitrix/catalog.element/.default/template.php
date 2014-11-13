<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$res = CIBlockSection::GetByID($arResult["IBLOCK_SECTION_ID"]);
if($ar_res = $res->GetNext())
  $section_name = $ar_res['NAME'];
/*echo '<pre>';
var_dump($arResult);
echo '</pre>';*/
$APPLICATION->SetTitle($section_name.' > '.$arResult["NAME"]);?>
<div class="catalog-element">
<div class="abs">
	<table class="price" border="0" cellspacing="0" cellpadding="2" width="234">
		<tr>
			<td align="center" width="100%" colspan=2 class="catalog-price-1">����: <b><?=FormatCurrency($arResult["PRICE_MATRIX"]["MATRIX"][1][0]["PRICE"], $arResult["PRICE_MATRIX"]["MATRIX"][1][0]["CURRENCY"])?></b></td>
		</tr>
		<tr>
			<td width="40" height="40" align="right"><img src="/bitrix/templates/poligon/images/basket.gif"></td><td><a href="<?echo $arResult["ADD_URL"]?>"><?echo GetMessage("CATALOG_ADD_TO_BASKET")?></a></td>		
		</tr> 
	</table>
<br>

<?if(is_array($arResult["PRICE_MATRIX"])):?>
			<table cellpadding="0" cellspacing="0" border="0" width="164" class="data-table" align="center">
			<thead>
			<tr>
				<?if(count($arResult["PRICE_MATRIX"]["ROWS"]) >= 1 && ($arResult["PRICE_MATRIX"]["ROWS"][0]["QUANTITY_FROM"] > 0 || $arResult["PRICE_MATRIX"]["ROWS"][0]["QUANTITY_TO"] > 0)):?>
					<td><?= GetMessage("CATALOG_QUANTITY") ?></td>
				<?endif;?>
				<?foreach($arResult["PRICE_MATRIX"]["COLS"] as $typeID => $arType):?>
					<td align="center"><?= $arType["NAME_LANG"] ?></td>
				<?endforeach?>
			</tr>
			</thead>
			<?foreach ($arResult["PRICE_MATRIX"]["ROWS"] as $ind => $arQuantity):?>
			<tr>
				<?if(count($arResult["PRICE_MATRIX"]["ROWS"]) > 1 || count($arResult["PRICE_MATRIX"]["ROWS"]) == 1 && ($arResult["PRICE_MATRIX"]["ROWS"][0]["QUANTITY_FROM"] > 0 || $arResult["PRICE_MATRIX"]["ROWS"][0]["QUANTITY_TO"] > 0)):?>
					<th nowrap>
						<?if(IntVal($arQuantity["QUANTITY_FROM"]) > 0 && IntVal($arQuantity["QUANTITY_TO"]) > 0)
							echo str_replace("#FROM#", $arQuantity["QUANTITY_FROM"], str_replace("#TO#", $arQuantity["QUANTITY_TO"], GetMessage("CATALOG_QUANTITY_FROM_TO")));
						elseif(IntVal($arQuantity["QUANTITY_FROM"]) > 0)
							echo str_replace("#FROM#", $arQuantity["QUANTITY_FROM"], GetMessage("CATALOG_QUANTITY_FROM"));
						elseif(IntVal($arQuantity["QUANTITY_TO"]) > 0)
							echo str_replace("#TO#", $arQuantity["QUANTITY_TO"], GetMessage("CATALOG_QUANTITY_TO"));
						?>
					</th>
				<?endif;?>
				<?foreach($arResult["PRICE_MATRIX"]["COLS"] as $typeID => $arType):?>
					<td align="center">
						<?if($arResult["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["DISCOUNT_PRICE"] < $arResult["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["PRICE"])
							echo '<s>'.FormatCurrency($arResult["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["PRICE"], $arResult["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["CURRENCY"]).'</s> <span class="catalog-price">'.FormatCurrency($arResult["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["DISCOUNT_PRICE"], $arResult["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["CURRENCY"])."</span>";
						else
							echo '<span class="catalog-price">'.FormatCurrency($arResult["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["PRICE"], $arResult["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["CURRENCY"])."</span>";
						?>
					</td>
				<?endforeach?>
			</tr>
			<?endforeach?>
			</table>
			<?if($arParams["PRICE_VAT_SHOW_VALUE"]):?>
				<?if($arParams["PRICE_VAT_INCLUDE"]):?>
					<small><?=GetMessage('CATALOG_VAT_INCLUDED')?></small>
				<?else:?>
					<small><?=GetMessage('CATALOG_VAT_NOT_INCLUDED')?></small>
				<?endif?>
			<?endif;?><br />
		<?endif?>
</div>
	<table width="100%" border="0" cellspacing="0" cellpadding="2">
		<Tr>
			<Td><b><?=$arResult["PREVIEW_TEXT"]?></b><br><small><?=$section_name?></small></td>
		</tr>
		<tr>
			<td>�������������: <B><?=$arResult["PROPERTIES"]["producer_full"]["VALUE"]?></b><br><br></td>
		</tr>
		<tr>
			<td valign="top">
			<table><tr><Td>			
			<?=CFile::ShowImage($arResult["PROPERTIES"]["link"]["VALUE"], 200, 200, "border=0 alt='".$section_name.' '.$arResult["NAME"].' '.$arResult["PROPERTIES"]["producer_full"]["VALUE"]."' style='vertical-align:text-top;", "", true);?></td><td valign="top">
			<b><?=$arResult["NAME"]?> </b>
			    <?$APPLICATION->IncludeFile(
						$APPLICATION->GetTemplatePath("/bitrix/templates/poligon/include_areas/element_text.php"),
						Array(),
						Array("MODE"=>"html")
					);?><br><?=$arResult["DETAIL_TEXT"]?></td>
			</tr></table>
		</tr>
		<?if($arResult["PROPERTIES"]["pdf"]["VALUE"]){
				echo '<tr><td><table><tr><td>�������������� ����������:</td><Td> <a href="'.$arResult['PROPERTIES']['pdf']['VALUE'].'"><img src="/images/pdf.jpg" height="50"></a></td></tr></table></td></tr>';?>
<?}?>
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
			<img border="0" src="<?=$PHOTO["SRC"]?>" width="<?=$PHOTO["WIDTH"]?>" height="<?=$PHOTO["HEIGHT"]?>" alt="<?=$PHOTO["ALT"]?>" title="<?=$arResult["NAME"]?>" /><br />
		<?endforeach?>
	<?endif?>
	<?if(is_array($arResult["SECTION"])):?>
		<br /><a href="<?=$arResult["SECTION"]["SECTION_PAGE_URL"]?>"><?=GetMessage("CATALOG_BACK")?></a>
	<?endif?>
</div>

