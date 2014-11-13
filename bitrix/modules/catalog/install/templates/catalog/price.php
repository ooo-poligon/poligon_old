<?
IncludeTemplateLangFile(__FILE__);

if (CModule::IncludeModule("catalog"))
{
	global $USER, $APPLICATION;

	$ACTION_VARIABLE = Trim($ACTION_VARIABLE);
	if (StrLen($ACTION_VARIABLE) <= 0 || !ereg("^[A-Za-z_][A-Za-z01-9_]*$", $ACTION_VARIABLE))
		$ACTION_VARIABLE = "action";

	$ID_VARIABLE = Trim($ID_VARIABLE);
	if (StrLen($ID_VARIABLE) <= 0 || !ereg("^[A-Za-z_][A-Za-z01-9_]*$", $ID_VARIABLE))
		$ID_VARIABLE = "ID";

	$ID = IntVal($ID);
	if ($ID <= 0)
		if (array_key_exists($ID_VARIABLE, $_REQUEST))
			$ID = IntVal($_REQUEST[$ID_VARIABLE]);

	if (!is_array($PRICE_CODE))
		$PRICE_CODE = array($PRICE_CODE);

	$BASKET_URL = Trim($BASKET_URL);
	if (StrLen($BASKET_URL) <= 0)
		$BASKET_URL = "basket.php";

	$USE_PRICE_COUNT = (($USE_PRICE_COUNT == "Y") ? "Y" : "N");

	$SHOW_PRICE_COUNT = IntVal($SHOW_PRICE_COUNT);
	$SHOW_PRICE_COUNT = (($SHOW_PRICE_COUNT > 0) ? $SHOW_PRICE_COUNT : 1);

	$CACHE_TIME = IntVal($CACHE_TIME);

	$curPagePath = $APPLICATION->GetCurPageParam($ID_VARIABLE."=".$ID, array($ACTION_VARIABLE, $ID_VARIABLE));

	/****************************************************************************/

	if (array_key_exists($ACTION_VARIABLE, $_REQUEST) && $ID > 0)
	{
		$action = StrToUpper($_REQUEST[$ACTION_VARIABLE]);
		if ($action == "ADD2BASKET" || $action == "BUY")
		{
			if (CModule::IncludeModule("sale"))
			{
				if (Add2BasketByProductID($ID))
				{
					if ($action == "BUY")
						LocalRedirect($BASKET_URL);
					else
						LocalRedirect($curPagePath);
				}
				else
				{
					if ($ex = $GLOBALS["APPLICATION"]->GetException())
						$errorMessage .= $ex->GetString();
					else
						$errorMessage .= GetMessage("CATALOG_ERROR2BASKET").".";
				}
			}
		}
	}

	if ($ID > 0)
	{
		$cacheID = md5($ID.serialize($arParams).$USER->GetGroups());
		$cachePath = "/catalog/price/";

		$cache = new CPHPCache;
		if ($cache->InitCache($CACHE_TIME, $cacheID, $cachePath))
		{
			extract($cache->GetVars());
		}
		else
		{
			$arCatalogGroupCodesFilter = array();
			foreach ($PRICE_CODE as $key => $value)
			{
				$value = Trim($value);
				if (StrLen($value) > 0)
					$arCatalogGroupCodesFilter[] = $value;
			}

			$arCatalogGroupsFilter = array();
			$arCatalogGroups = CCatalogGroup::GetListArray();
			if (count($arCatalogGroupCodesFilter) > 0)
			{
				foreach ($arCatalogGroups as $key => $value)
				{
					if (in_array($value["NAME"], $arCatalogGroupCodesFilter))
						$arCatalogGroupsFilter[] = $key;
				}
			}

			$arPriceMatrix = CatalogGetPriceTableEx($ID, (($USE_PRICE_COUNT == "Y") ? 0 : $SHOW_PRICE_COUNT), $arCatalogGroupsFilter);

			if ($CACHE_TIME > 0)
			{
				$cache->StartDataCache($CACHE_TIME, $cacheID, $cachePath);
				$cache->EndDataCache(array("arPriceMatrix" => $arPriceMatrix));
			}
		}

		if (count($arPriceMatrix["COLS"]) > 0)
		{
			?>
			<table cellpadding="0" cellspacing="0" border="0">
				<?
				if ($USE_PRICE_COUNT == "N")
				{
					foreach ($arPriceMatrix["COLS"] as $priceTypeID => $arPriceType)
					{
						?>
						<tr>
							<td style="padding-top:2px;"><font class="text"><?=$arPriceType["NAME_LANG"]?>:&nbsp;&nbsp;<b><?
							if ($arPriceMatrix["MATRIX"][$priceTypeID][0]["DISCOUNT_PRICE"] < $arPriceMatrix["MATRIX"][$priceTypeID][0]["PRICE"])
								echo '<s>'.FormatCurrency($arPriceMatrix["MATRIX"][$priceTypeID][0]["PRICE"], $arPriceMatrix["MATRIX"][$priceTypeID][0]["CURRENCY"]).'</s> <font color="red">'.FormatCurrency($arPriceMatrix["MATRIX"][$priceTypeID][0]["DISCOUNT_PRICE"], $arPriceMatrix["MATRIX"][$priceTypeID][0]["CURRENCY"]);
							else
								echo '<font color="red">'.FormatCurrency($arPriceMatrix["MATRIX"][$priceTypeID][0]["PRICE"], $arPriceMatrix["MATRIX"][$priceTypeID][0]["CURRENCY"]); ?></b></font></font></td>
						</tr>
						<?
					}
				}
				else
				{
					?>
					<tr>
						<td>
							<table cellpadding="0" cellspacing="0" border="0"><tr><td class="tableborder">
							<table cellpadding="3" cellspacing="1" border="0" width="100%">
								<tr>
									<?
									if (count($arPriceMatrix["ROWS"]) > 1 || count($arPriceMatrix["ROWS"]) == 1 && ($arPriceMatrix["ROWS"][0]["QUANTITY_FROM"] > 0 || $arPriceMatrix["ROWS"][0]["QUANTITY_TO"] > 0))
									{
										?><td valign="top" nowrap class="tablebody"><font class="smalltext"><?= GetMessage("CATALOG_QUANTITY") ?></font></td><?
									}

									foreach ($arPriceMatrix["COLS"] as $typeID => $arType)
									{
										?><td valign="top" nowrap class="tablebody"><font class="smalltext"><?= $arType["NAME_LANG"] ?></font></td><?
									}
									?>
								</tr>
								<?
								foreach ($arPriceMatrix["ROWS"] as $ind => $arQuantity)
								{
									?>
									<tr>
										<?
										if (count($arPriceMatrix["ROWS"]) > 1 || count($arPriceMatrix["ROWS"]) == 1 && ($arPriceMatrix["ROWS"][0]["QUANTITY_FROM"] > 0 || $arPriceMatrix["ROWS"][0]["QUANTITY_TO"] > 0))
										{
											?>
											<td valign="top" nowrap class="tablebody"><font class="smalltext"><?
												if (IntVal($arQuantity["QUANTITY_FROM"]) > 0 && IntVal($arQuantity["QUANTITY_TO"]) > 0)
													echo str_replace("#FROM#", $arQuantity["QUANTITY_FROM"], str_replace("#TO#", $arQuantity["QUANTITY_TO"], GetMessage("CATALOG_QUANTITY_FROM_TO")));
												elseif (IntVal($arQuantity["QUANTITY_FROM"]) > 0)
													echo str_replace("#FROM#", $arQuantity["QUANTITY_FROM"], GetMessage("CATALOG_QUANTITY_FROM"));
												elseif (IntVal($arQuantity["QUANTITY_TO"]) > 0)
													echo str_replace("#TO#", $arQuantity["QUANTITY_TO"], GetMessage("CATALOG_QUANTITY_TO"));
											?></font></td>
											<?
										}

										foreach ($arPriceMatrix["COLS"] as $typeID => $arType)
										{
											?><td valign="top" nowrap class="tablebody"><font class="smalltext"><?
												if ($arPriceMatrix["MATRIX"][$typeID][$ind]["DISCOUNT_PRICE"] < $arPriceMatrix["MATRIX"][$typeID][$ind]["PRICE"])
													echo '<s>'.FormatCurrency($arPriceMatrix["MATRIX"][$typeID][$ind]["PRICE"], $arPriceMatrix["MATRIX"][$typeID][$ind]["CURRENCY"]).'</s> <font color="red">'.FormatCurrency($arPriceMatrix["MATRIX"][$typeID][$ind]["DISCOUNT_PRICE"], $arPriceMatrix["MATRIX"][$typeID][$ind]["CURRENCY"]);
												else
													echo '<font color="red">'.FormatCurrency($arPriceMatrix["MATRIX"][$typeID][$ind]["PRICE"], $arPriceMatrix["MATRIX"][$typeID][$ind]["CURRENCY"]);
											?></font></font></td><?
										}
										?>
									</tr>
									<?
								}
								?>
							</table>
							</td></tr></table>
						</td>
					</tr>
					<?
				}

				if (array_key_exists("CAN_BUY", $arPriceMatrix) && is_array($arPriceMatrix["CAN_BUY"]) && count($arPriceMatrix["CAN_BUY"]) > 0 && IsModuleInstalled("sale"))
				{
					?>
					<tr>
						<td style="padding-top:5px;"><font class="text"><?
							if (array_key_exists("AVAILABLE", $arPriceMatrix) && $arPriceMatrix["AVAILABLE"] == "Y")
							{
								?>&nbsp;<input class="inputbuttonflat" name="basket" type="button" value="<?= GetMessage("CATALOG_2BASKET") ?>" height="20" width="20" OnClick="window.location='<?= $curPagePath ?>&<?= $ID_VARIABLE ?>=<?= $ID ?>&<?= $ACTION_VARIABLE ?>=ADD2BASKET'">&nbsp;<input class="inputbuttonflat" name="buy" type="button" value="<?= GetMessage("CATALOG_BUY") ?>" height="20" width="20" OnClick="window.location='<?= $curPagePath ?>&<?= $ID_VARIABLE ?>=<?= $ID ?>&<?= $ACTION_VARIABLE ?>=BUY'"><?
							}
							else
							{
								?>&nbsp;&nbsp;<font class="smalltext"><?=GetMessage("CATALOG_NOT_AVAILABLE")?></font><?
							}
						?></font></td>
					</tr>
					<?
				}
				?>
			</table>
			<?
		}
	}
	else
	{
		echo GetMessage("CATALOG_NO_PRODUCT");
	}
}
else
{
	echo GetMessage("CATALOG_NO_MODULE");
}
?>