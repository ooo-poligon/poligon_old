<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<script type="text/javascript" src="/bitrix/templates/poligon/js/ajax.js"></script>
<div class="catalog-section">
<?if($arParams["DISPLAY_TOP_PAGER"]):?>
	<?=$arResult["NAV_STRING"]?><br />
<?endif;?>
<?if (count($arResult["ITEMS"])):?>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
	<th width="40%">Ќаименование</th>
	<th width="300">÷ена</th>
	<th width="100">ѕроизводитель</th>
	<th width="50" style="text-align:center">PDF</th>
	<th width="50" style="text-align:center">—клад</th>
	<th width="50"></th>
</tr>
	<?foreach($arResult["ITEMS"] as $cell=>$arElement):?>
		<tr>
			<td valign="top"><a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><?=$arElement["NAME"]?></a>
				<?/*foreach($arElement["DISPLAY_PROPERTIES"] as $pid=>$arProperty):?>
					<?=$arProperty["NAME"]?>:&nbsp;<?
					if(is_array($arProperty["DISPLAY_VALUE"]))
						echo implode("&nbsp;/&nbsp;", $arProperty["DISPLAY_VALUE"]);
					else
						echo $arProperty["DISPLAY_VALUE"];?><br />
				<?endforeach*/?>
				<br />
				<?=$arElement["PREVIEW_TEXT"]?>
			</td>
			<?foreach($arElement["PRICES"] as $code=>$arPrice):?>
			<td>
				<?if($arPrice["CAN_ACCESS"]):?>
					<p><?=$arResult["PRICES"][$code]["TITLE"];?>:&nbsp;&nbsp;
					<?if($arPrice["DISCOUNT_VALUE"] < $arPrice["VALUE"]):?>
						<s><?=$arPrice["PRINT_VALUE"]?></s> <span class="catalog-price"><?=$arPrice["PRINT_DISCOUNT_VALUE"]?></span>
					<?else:?><span class="catalog-price"><?=$arPrice["PRINT_VALUE"]?></span><?endif;?>
					</p>
				<?endif;?>
			</td>
			<?endforeach;?>
			<?if(is_array($arElement["PRICE_MATRIX"])):?>
			<td>
				<table cellpadding="0" cellspacing="0" border="0" width="200" class="data-table">
				<thead>
				<tr>
					<?if(count($arElement["PRICE_MATRIX"]["ROWS"]) >= 1 && ($arElement["PRICE_MATRIX"]["ROWS"][0]["QUANTITY_FROM"] > 0 || $arElement["PRICE_MATRIX"]["ROWS"][0]["QUANTITY_TO"] > 0)):?>
						<td valign="top" nowrap><?= GetMessage("CATALOG_QUANTITY") ?></td>
					<?endif?>
					<?foreach($arElement["PRICE_MATRIX"]["COLS"] as $typeID => $arType):?>
						<td valign="top" nowrap><?= $arType["NAME_LANG"] ?></td>
					<?endforeach?>
				</tr>
				</thead>
				<?foreach ($arElement["PRICE_MATRIX"]["ROWS"] as $ind => $arQuantity):?>
				<tr>
					<?if(count($arElement["PRICE_MATRIX"]["ROWS"]) > 1 || count($arElement["PRICE_MATRIX"]["ROWS"]) == 1 && ($arElement["PRICE_MATRIX"]["ROWS"][0]["QUANTITY_FROM"] > 0 || $arElement["PRICE_MATRIX"]["ROWS"][0]["QUANTITY_TO"] > 0)):?>
						<td nowrap class="qu"><?
							if (IntVal($arQuantity["QUANTITY_FROM"]) > 0 && IntVal($arQuantity["QUANTITY_TO"]) > 0)
								echo str_replace("#FROM#", $arQuantity["QUANTITY_FROM"], str_replace("#TO#", $arQuantity["QUANTITY_TO"], GetMessage("CATALOG_QUANTITY_FROM_TO")));
							elseif (IntVal($arQuantity["QUANTITY_FROM"]) > 0)
								echo str_replace("#FROM#", $arQuantity["QUANTITY_FROM"], GetMessage("CATALOG_QUANTITY_FROM"));
							elseif (IntVal($arQuantity["QUANTITY_TO"]) > 0)
								echo str_replace("#TO#", $arQuantity["QUANTITY_TO"], GetMessage("CATALOG_QUANTITY_TO"));
						?></td>
					<?endif?>
					<?foreach($arElement["PRICE_MATRIX"]["COLS"] as $typeID => $arType):?>
						<td class="qu"><?
							if($arElement["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["DISCOUNT_PRICE"] < $arElement["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["PRICE"]):?>
								<s><?=FormatCurrency($arElement["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["PRICE"], $arElement["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["CURRENCY"])?></s><span class="catalog-price"><?=FormatCurrency($arElement["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["DISCOUNT_PRICE"], $arElement["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["CURRENCY"]);?></span>
							<?else:?>
								<span class="catalog-price"><?=FormatCurrency($arElement["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["PRICE"], $arElement["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["CURRENCY"]);?></span>
							<?endif?>&nbsp;
						</td>
					<?endforeach?>
				</tr>
				<?endforeach?>
				</table></td>
			<?endif?>
			<td align="center">
				<?=$arElement["DISPLAY_PROPERTIES"]["producer_full"]["DISPLAY_VALUE"]?>
				<?$res = CIBlockSection::GetByID($arElement["IBLOCK_SECTION_ID"]);
					if($ar_res = $res->GetNext())
					{
					  $section_name = $ar_res['NAME'];
					  if ($ar_res["IBLOCK_SECTION_ID"])
					  {
					  		$res1 = CIBlockSection::GetByID($ar_res["IBLOCK_SECTION_ID"]);
							if($ar_res1 = $res1->GetNext()) $section_name2 = $ar_res1['NAME'];
							$APPLICATION->SetTitle($section_name2.' > '.$section_name.' > '.$arElement["DISPLAY_PROPERTIES"]["producer_full"]["DISPLAY_VALUE"]);
							
					  }
					  else $APPLICATION->SetTitle($section_name.' > '.$arElement["DISPLAY_PROPERTIES"]["producer_full"]["DISPLAY_VALUE"]);
					}
				?>
			</td>
			<td>
				<?if($arElement["DISPLAY_PROPERTIES"]["pdf"]["DISPLAY_VALUE"]){?>				
				<a href="<?=$arElement["DISPLAY_PROPERTIES"]["pdf"]["DISPLAY_VALUE"]?>"><img src="/images/pdf.jpg" height="20"></	a>	<?}?>				
			</td>
			<td align="center">
				<?$db_res = CCatalogProduct::GetList(
					array(),
					array("ID" => $arElement["ID"]),
					false,
					array()
				    );
				if ($ar_res = $db_res->Fetch())
				{
				    if (!$ar_res["QUANTITY"]){
					if (!$arElement["DISPLAY_PROPERTIES"]["srok"]["DISPLAY_VALUE"])
						echo '<img src="/images/red.gif" alt="оЌƒ √ё…ё√" title="оЌƒ √ё…ё√">';
					else echo $arElement["DISPLAY_PROPERTIES"]["srok"]["DISPLAY_VALUE"]; 					
					}
				    else echo '<img src="/images/green.gif" alt="ея–Ё ћё я… ёƒ≈" title="ея–Ё ћё я… ёƒ≈">';
				}?>			
			</td>
			<td width="30">
			<?if($arElement["CAN_BUY"]):?>
			<center>
				<a href="javascript:void(0)<?//echo $arElement['ADD_URL']?>" onclick="run(<?=$arElement['ID']?>)"><img src="/bitrix/templates/poligon/images/basket.gif"><?//echo GetMessage("CATALOG_ADD")?></a>
			</center>
			<?elseif((count($arResult["PRICES"]) > 0) || is_array($arElement["PRICE_MATRIX"])):?>
				<?=GetMessage("CATALOG_NOT_AVAILABLE")?>
			<?endif?>
			</td>
			&nbsp;
		</td>
	</tr>
	<?endforeach; // foreach($arResult["ITEMS"] as $arElement):?>
</table>
<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
	<br /><?=$arResult["NAV_STRING"]?>
<?endif;?>
<?endif;?>
</div>
