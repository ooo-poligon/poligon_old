<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
echo ShowError($arResult["ERROR_MESSAGE"]);
echo GetMessage("STB_ORDER_PROMT"); ?>
<br /><br />
<table class="sale_basket_basket data-table">
	<tr>
		<?if (in_array("NAME", $arParams["COLUMNS_LIST"])):?>
			<th><?= GetMessage("SALE_NAME")?></th>
		<?endif;?>
		<?if (in_array("PRICE", $arParams["COLUMNS_LIST"])):?>
			<th><?= GetMessage("SALE_PRICE")?></th>
		<?endif;?>
		<?if (in_array("TYPE", $arParams["COLUMNS_LIST"])):?>
			<th><?= GetMessage("SALE_PRICE_TYPE")?></th>
		<?endif;?>
		<?if (in_array("DISCOUNT", $arParams["COLUMNS_LIST"])):?>
			<th><?= GetMessage("SALE_DISCOUNT")?></th>
		<?endif;?>
		<?if (in_array("QUANTITY", $arParams["COLUMNS_LIST"])):?>
			<th><?= GetMessage("SALE_QUANTITY")?></th>
		<?endif;?>
			<th>Сумма</th>
		<?if (in_array("DELETE", $arParams["COLUMNS_LIST"])):?>
			<th><?= GetMessage("SALE_DELETE")?></th>
		<?endif;?>
		<?if (in_array("DELAY", $arParams["COLUMNS_LIST"])):?>
			<th><?= GetMessage("SALE_OTLOG")?></th>
		<?endif;?>
		<?if (in_array("WEIGHT", $arParams["COLUMNS_LIST"])):?>
			<th><?= GetMessage("SALE_WEIGHT")?></th>
		<?endif;?>
	</tr>
	<?
	$i=0;
	foreach($arResult["ITEMS"]["AnDelCanBuy"] as $arBasketItems)
	{
			if(CModule::IncludeModule("catalog"))
			{
				$arBasketItems["PRICE_MATRIX"] = CatalogGetPriceTableEx($arBasketItems["PRODUCT_ID"]);
				foreach($arBasketItems["PRICE_MATRIX"]["COLS"] as $keyColumn=>$arColumn)
					$arBasketItems["PRICE_MATRIX"]["COLS"][$keyColumn]["NAME_LANG"] = htmlspecialchars($arColumn["NAME_LANG"]);
			}
			else
			{
				$arBasketItems["PRICE_MATRIX"] = false;
			}
			/*echo '<pre>';
			var_dump($arBasketItems);
			echo '</pre>';*/
$db_props = CIBlockElement::GetProperty(4, $arBasketItems["PRODUCT_ID"], "sort", "asc", Array("CODE"=>"article"));
if($ar_props = $db_props->Fetch())
        $article = $ar_props["VALUE"];

$body = '';
$old_price = '';
$count = 0;
			foreach ($arBasketItems["PRICE_MATRIX"]["ROWS"] as $ind => $arQuantity):
				if ($ind) $body .= '';
					if(count($arBasketItems["PRICE_MATRIX"]["ROWS"]) > 1 || count($arBasketItems["PRICE_MATRIX"]["ROWS"]) == 1 && ($arElement["PRICE_MATRIX"]["ROWS"][0]["QUANTITY_FROM"] > 0 || $arBasketItems["PRICE_MATRIX"]["ROWS"][0]["QUANTITY_TO"] > 0)):
							if (IntVal($arQuantity["QUANTITY_FROM"])>$arBasketItems["QUANTITY"]&&$count==0){
								if (IntVal($arQuantity["QUANTITY_FROM"]) > 0 && IntVal($arQuantity["QUANTITY_TO"]) > 0)
									$body .= 'При покупке от '.$arQuantity["QUANTITY_FROM"].' шт. до '. $arQuantity["QUANTITY_TO"];
								elseif (IntVal($arQuantity["QUANTITY_FROM"]) > 0)
									$body .= 'При покупке от '.$arQuantity["QUANTITY_FROM"].' шт.';
								elseif (IntVal($arQuantity["QUANTITY_TO"]) > 0)
									$body .= 'При покупке от '.$arQuantity["QUANTITY_TO"].' шт. и более';
								$count++;
								$ind2=$ind;
							}
						if ($arBasketItems["QUANTITY"]>$arQuantity["QUANTITY_FROM"]) $s=1;
						else $s=0;
					endif;
					foreach($arBasketItems["PRICE_MATRIX"]["COLS"] as $typeID => $arType):
							if ($ind2&&$ind==$ind2){
								if($arBasketItems["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["DISCOUNT_PRICE"] < $arBasketItems["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["PRICE"]):
									$body .= ' цена за штуку составит <s>'.FormatCurrency($arElement["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["PRICE"], $arElement["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["CURRENCY"]).'</s><span class="catalog-price">'.FormatCurrency($arBasketItems["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["DISCOUNT_PRICE"], $arElement["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["CURRENCY"]).'</span>';
								else:
									$body .= ' цена за штуку составит <span class="catalog-price">'.FormatCurrency($arBasketItems["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["PRICE"], $arElement["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["CURRENCY"]).'</span>';
								endif;
							}
							if ($s==1&&$arBasketItems["PRICE"]!=$arBasketItems["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["PRICE"]) $old_price .= '<br>'.$arBasketItems["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["PRICE"].$arBasketItems["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["CURRENCY"];
							
					endforeach;
			endforeach;?>

		<tr>
			<?if (in_array("NAME", $arParams["COLUMNS_LIST"])):?>
				<td><?
				if (strlen($arBasketItems["DETAIL_PAGE_URL"])>0):
					?><a href="<?=$arBasketItems["DETAIL_PAGE_URL"] ?>"><?
				endif;
				?><b><?=$arBasketItems["NAME"] ?> (<?=$article?>)</b><?
				if (strlen($arBasketItems["DETAIL_PAGE_URL"])>0):
					?></a><?
				endif;
				?><small><?=$body?></small></td>
			<?endif;?>
			<?if (in_array("PRICE", $arParams["COLUMNS_LIST"])):?>
				<td align="right"><small class="price"><?=$old_price?></small><?=$arBasketItems["PRICE_FORMATED"]?></td>
			<?endif;?>
			<?if (in_array("TYPE", $arParams["COLUMNS_LIST"])):?>
				<td><?=$arBasketItems["NOTES"]?></td>
			<?endif;?>
			<?if (in_array("DISCOUNT", $arParams["COLUMNS_LIST"])):?>
				<td><?=$arBasketItems["DISCOUNT_PRICE_PERCENT_FORMATED"]?></td>
			<?endif;?>
			<?if (in_array("QUANTITY", $arParams["COLUMNS_LIST"])):?>
				<td align="center"><input maxlength="18" type="text" name="QUANTITY_<?=$arBasketItems["ID"] ?>" value="<?=$arBasketItems["QUANTITY"]?>" size="3" ></td>
			<?endif;?>
			<td><?=$arBasketItems["PRICE"]*$arBasketItems["QUANTITY"]?> EUR</td>
			<?if (in_array("DELETE", $arParams["COLUMNS_LIST"])):?>
				<td align="center"><input type="checkbox" name="DELETE_<?=$arBasketItems["ID"] ?>" id="DELETE_<?=$i?>" value="Y"></td>
			<?endif;?>
			<?if (in_array("DELAY", $arParams["COLUMNS_LIST"])):?>
				<td align="center"><input type="checkbox" name="DELAY_<?=$arBasketItems["ID"] ?>" value="Y"></td>
			<?endif;?>
			<?if (in_array("WEIGHT", $arParams["COLUMNS_LIST"])):?>
				<td align="right"><?=$arBasketItems["WEIGHT"] ?> <?=(strlen($arParams["WEIGHT_UNIT"]) > 0 ? $arParams["WEIGHT_UNIT"] : GetMessage("SALE_WEIGHT_G"))?></td>
			<?endif;?>
		</tr>
		<?
		$i++;
	}
	?>
	<script>
	function sale_check_all(val)
	{
		for(i=0;i<=<?=count($arResult["ITEMS"]["AnDelCanBuy"])-1?>;i++)
		{
			if(val)
				document.getElementById('DELETE_'+i).checked = true;
			else
				document.getElementById('DELETE_'+i).checked = false;
		}
	}
	</script>
	<tr>
		<?if (in_array("NAME", $arParams["COLUMNS_LIST"])):?>
			<td align="right" nowrap>
				<?if ($arParams['PRICE_VAT_SHOW_VALUE'] == 'Y'):?>
					<b><?echo GetMessage('SALE_VAT_INCLUDED')?></b><br />
				<?endif;?>
				<?
				if (doubleval($arResult["DISCOUNT_PRICE"]) > 0)
				{
					?><b><?echo GetMessage("SALE_CONTENT_DISCOUNT")?><?
					if (strLen($arResult["DISCOUNT_PERCENT_FORMATED"])>0)
						echo " (".$arResult["DISCOUNT_PERCENT_FORMATED"].")";?>:</b><br /><?
				}
				?>
				<b><?= GetMessage("SALE_ITOGO")?>:</b>
			</td>
		<?endif;?>
		<?if (in_array("PRICE", $arParams["COLUMNS_LIST"])):?>
			<td align="right" nowrap>
				<?if ($arParams['PRICE_VAT_SHOW_VALUE'] == 'Y'):?>
					<?=$arResult["allVATSum_FORMATED"]?><br />
				<?endif;?>
				<?
				if (doubleval($arResult["DISCOUNT_PRICE"]) > 0)
				{
					echo $arResult["DISCOUNT_PRICE_FORMATED"]."<br />";
				}
				?>
				<?=$arResult["allSum_FORMATED"]?><br />
			</td>
		<?endif;?>
		<?if (in_array("TYPE", $arParams["COLUMNS_LIST"])):?>
			<td>&nbsp;</td>
		<?endif;?>
		<?if (in_array("DISCOUNT", $arParams["COLUMNS_LIST"])):?>
			<td>&nbsp;</td>
		<?endif;?>
		<?if (in_array("QUANTITY", $arParams["COLUMNS_LIST"])):?>
			<td>&nbsp;</td>
		<?endif;?>
		<td>&nbsp;</td>
		<?if (in_array("DELETE", $arParams["COLUMNS_LIST"])):?>
			<td align="center"><input type="checkbox" name="DELETE" value="Y" onClick="sale_check_all(this.checked)"></td>
		<?endif;?>
		<?if (in_array("DELAY", $arParams["COLUMNS_LIST"])):?>
			<td>&nbsp;</td>
		<?endif;?>
		<?if (in_array("WEIGHT", $arParams["COLUMNS_LIST"])):?>
			<td align="right"><?=$arResult["allWeight"] ?> <?=(strlen($arParams["WEIGHT_UNIT"]) > 0 ? $arParams["WEIGHT_UNIT"] : GetMessage("SALE_WEIGHT_G"))?></td>
		<?endif;?>
	</tr>
</table>

<br />
<table width="100%">
	<?if ($arParams["HIDE_COUPON"] != "Y"):?>
		<tr>
			<td colspan="3">
				
				<?= GetMessage("STB_COUPON_PROMT") ?>
				<input type="text" name="COUPON" value="<?=$arResult["COUPON"]?>" size="20">
				<br /><br />
			</td>
		</tr>
	<?endif;?>
	<tr>
		<td width="30%">
			<input type="submit" value="<?echo GetMessage("SALE_REFRESH")?>" name="BasketRefresh"><br />
			<small><?echo GetMessage("SALE_REFRESH_DESCR")?></small><br />
		</td>
		<td align="right" width="40%">&nbsp;</td>
		<td align="right" width="30%">
			<input type="submit" value="<?echo GetMessage("SALE_ORDER")?>" name="BasketOrder"><br />
			<small><?echo GetMessage("SALE_ORDER_DESCR")?></small><br />
		</td>
	</tr>
</table>
<br />
<?
