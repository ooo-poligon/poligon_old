<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<script type="text/javascript" src="/bitrix/templates/poligon/js/ajax.js"></script>
<?
$res = CIBlockSection::GetByID($arResult["IBLOCK_SECTION_ID"]);
if($ar_res = $res->GetNext())
  $section_name = $ar_res['NAME'];

$res1 = CIBlockSection::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>$arResult["IBLOCK_ID"], "ID"=>$ar_res["IBLOCK_SECTION_ID"]), false, array("NAME","UF_PROIZV"));
if ($arSect = $res1->GetNext())
	{ 
		$proizv = $arSect["UF_PROIZV"];
                $section_name2 = $arSect['NAME'];
	}

/*$res = CIBlockSection::GetByID($ar_res["IBLOCK_SECTION_ID"]);
if($ar_res = $res->GetNext())
  $section_name2 = $ar_res['NAME'];
*/
/*if ($arResult["PROPERTIES"]["producer_full"]["VALUE"])
  $proizv = '('.$arResult["PROPERTIES"]["producer_full"]["VALUE"].')';
*/
/*echo '<pre>';
var_dump($arResult);
echo '</pre>';*/
$title_main = $section_name2.' '.$proizv.' > '.$section_name.' > '.$arResult["NAME"];
$img_alt = $section_name2.' '.$proizv.' '.$section_name.' '.$arResult["NAME"];
$APPLICATION->SetTitle($title_main);?>
<div class="catalog-element">
<div class="abs">
	<table align="center" class="price" border="0" cellspacing="0" cellpadding="2" width="234">
		<tr>
			<td align="center" width="100%" colspan=2 class="catalog-price-1">Цена: <b><?=$arResult["PRICES"]["BASE"]["PRINT_VALUE"]?><?=FormatCurrency($arResult["PRICE"]["DISPLAY_VALUE"], $arResult["PRICE_MATRIX"]["MATRIX"][1][0]["CURRENCY"])?></b></td>
		</tr>
		<tr>
			<td width=40 height="40" align="right"><img src="/bitrix/templates/poligon/images/basket.gif"></td><td><a href="javascript:void(0)<?//echo $arElement['ADD_URL']?>" onclick="run(<?=$arResult['ID']?>)"><?echo GetMessage("CATALOG_ADD_TO_BASKET")?></a></td>		
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
			<Td><div style="width:400px;"><b class="name"><?=$arResult["NAME"]?> <? if ($arResult["PROPERTIES"]["article"]["VALUE"]) echo '('.$arResult["PROPERTIES"]["article"]["VALUE"].')';?></b><br><?//var_dump($arResult["PRICES"]);?><b><?=$arResult["PREVIEW_TEXT"]?></b><br><small><?=$section_name?></small></div></td>
		</tr>
		<tr>
			<td>Производитель: <B><?=$arResult["PROPERTIES"]["producer_full"]["VALUE"]?></b><br><br></td>
		</tr>
		<tr>
			<td valign="top">
			<table><tr><Td valign="top">		
			<?=CFile::ShowImage('/images/'.$arResult["PROPERTIES"]["link"]["VALUE"], 200, 200, "border=0 alt='".$img_alt."' style='vertical-align:text-top; margin-right:10px'", "", false);?></td><td valign="top">
			
			    <?$APPLICATION->IncludeFile(
						$APPLICATION->GetTemplatePath("/bitrix/templates/poligon/include_areas/element_text.php"),
						Array(),
						Array("MODE"=>"html")
					);?><br><?=$arResult["DETAIL_TEXT"]?></td>
			</tr></table>
		</tr>
		<?if($arResult["PROPERTIES"]["pdf"]["VALUE"]){
				echo '<tr><td><table><tr><td>Техническая информация:</td><Td> <a href="'.$arResult['PROPERTIES']['pdf']['VALUE'].'"><img src="/images/pdf_doc.gif"></a></td></tr></table></td></tr>';?>
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
		<br><Br><br><br>
</div>

