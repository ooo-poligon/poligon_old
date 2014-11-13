<?
IncludeTemplateLangFile(__FILE__);
if (CModule::IncludeModule("catalog")):
//*******************************************************

$ID = IntVal($ID);
if ($ID <= 0)
	$ID = $GLOBALS["ID"];

$BASKET_PAGE_TEMPLATE = Trim($BASKET_PAGE_TEMPLATE);
if (strlen($BASKET_PAGE_TEMPLATE) <= 0)
	$BASKET_PAGE_TEMPLATE = "basket.php";

$ACTION_VARIABLE = Trim($ACTION_VARIABLE);
if (strlen($ACTION_VARIABLE) <= 0)
	$ACTION_VARIABLE = "action";

$PRODUCT_ID_VARIABLE = Trim($PRODUCT_ID_VARIABLE);
if (strlen($PRODUCT_ID_VARIABLE) <= 0)
	$PRODUCT_ID_VARIABLE = "PRODUCT_ID";

$CACHE_TIME = IntVal($CACHE_TIME);

$errorMessage = "";

// Let's add product to shopping cart if we need to do this
if (CModule::IncludeModule("sale"))
{
	if (($_REQUEST[$ACTION_VARIABLE] == "ADD2BASKET" || $_REQUEST[$ACTION_VARIABLE] == "BUY")
		&& IntVal($_REQUEST[$PRODUCT_ID_VARIABLE]) > 0)
	{
		if (Add2BasketByProductID($_REQUEST[$PRODUCT_ID_VARIABLE]))
		{
			if ($_REQUEST[$ACTION_VARIABLE] == "BUY")
				LocalRedirect($BASKET_PAGE_TEMPLATE);
			else
				LocalRedirect($APPLICATION->GetCurPageParam("", array($PRODUCT_ID_VARIABLE, $ACTION_VARIABLE)));
		}
		else
		{
			if ($ex = $GLOBALS["APPLICATION"]->GetException())
				$errorMessage .= $ex->GetString();
			else
				$errorMessage .= GetMessage("CTMP_CANT_BASKET").".";
		}
	}
}

$cache = new CPHPCache;
$cache_id = "catalog_price_table_".$ID."_".($GLOBALS["USER"]->GetGroups())."_".$BASKET_PAGE_TEMPLATE."_".SITE_ID."_".$ACTION_VARIABLE."_".$PRODUCT_ID_VARIABLE;

if ($CACHE_TIME > 0 && $cache->InitCache($CACHE_TIME, $cache_id, "/".SITE_ID."/catalog/multi_price_table.php/"))
{
	$cache->Output();
}
else
{
	if ($arProduct = CCatalogProduct::GetByID($ID))
	{
		if ($CACHE_TIME > 0)
			$cache->StartDataCache($CACHE_TIME, $cache_id, "/".SITE_ID."/catalog/multi_price_table.php/");

		$arPriceTable = CatalogGetPriceTable($ID);

		if ($arPriceTable
			&& is_array($arPriceTable)
			&& array_key_exists("COLS", $arPriceTable)
			&& is_array($arPriceTable["COLS"])
			&& count($arPriceTable["COLS"]) > 0)
		{
			?>
			<table border="0" cellSpacing="1" width="150" cellPadding="0" class="tableborder"><tr><td class="tablebody">
			<table border="0" cellSpacing="1" width="100%" cellPadding="2" class="tablebody">
				<tr>
					<?
					if ($arPriceTable["MULTI_QUANTITY"] == "Y")
					{
						?><td class="tablebody"><font class="tableheadtext"><?=GetMessage("CTMP_QUANTITY")?></font></td><?
					}

					$bCanBuy = False;
					foreach ($arPriceTable["COLS"] as $key => $value)
					{
						if ($value["CAN_BUY"] == "Y")
							$bCanBuy = True;
						?>
						<td class="tablebody" align="center"><font class="tableheadtext">
						<?= $value["NAME_LANG"]; ?>
						</font></td>
						<?
					}
					?>
				</tr>

				<?
				for ($i = 0; $i < count($arPriceTable["MATRIX"]); $i++)
				{
					?>
					<tr>
						<?
						if ($arPriceTable["MULTI_QUANTITY"] == "Y")
						{
							?>
							<td class="tablebody" nowrap>
								<font class="tablefieldtext"><?
								if (IntVal($arPriceTable["MATRIX"][$i]["QUANTITY_FROM"]) > 0
									|| IntVal($arPriceTable["MATRIX"][$i]["QUANTITY_TO"]) > 0)
								{
									if (IntVal($arPriceTable["MATRIX"][$i]["QUANTITY_FROM"]) <= 0)
										echo str_replace("#NUM#", $arPriceTable["MATRIX"][$i]["QUANTITY_TO"], GetMessage("CTMP_TILL_NUM"));
									elseif (IntVal($arPriceTable["MATRIX"][$i]["QUANTITY_TO"]) <= 0)
										echo str_replace("#NUM#", $arPriceTable["MATRIX"][$i]["QUANTITY_FROM"], GetMessage("CTMP_NUM_MORE"));
									else
										echo str_replace("#NUM_FROM#", $arPriceTable["MATRIX"][$i]["QUANTITY_FROM"], str_replace("#NUM_TO#", $arPriceTable["MATRIX"][$i]["QUANTITY_TO"], GetMessage("CTMP_NUM_FROM_TO")));
								}
								?></font>
							</td>
							<?
						}

						foreach ($arPriceTable["COLS"] as $key => $value)
						{
							$groupID = IntVal($value["ID"]);
							?>
							<td class="tablebody" nowrap align="right">
								<font class="tablebodytext">
								<?
								if (!array_key_exists($groupID, $arPriceTable["MATRIX"][$i]["PRICE"])
									|| $arPriceTable["MATRIX"][$i]["PRICE"][$groupID] == false)
								{
									echo "&nbsp;";
								}
								else
								{
									if ($value["CAN_BUY"] != "Y")
										echo "<font color=\"#AAAAAA\">";

									$arPrice = $arPriceTable["MATRIX"][$i]["PRICE"][$groupID];

									if ($arPrice["DISCOUNT_PRICE"] != $arPrice["PRICE"])
										echo "<s>";

									if ($arPrice["PRICE"] > 0)
										echo FormatCurrency($arPrice["PRICE"], $arPrice["CURRENCY"]);
									else
										echo GetMessage("CTMP_FREE");

									if ($arPrice["DISCOUNT_PRICE"] != $arPrice["PRICE"])
									{
										echo "</s><br><font color=\"#FF0000\">";

										if ($arPrice["DISCOUNT_PRICE"] > 0)
											echo FormatCurrency($arPrice["DISCOUNT_PRICE"], $arPrice["CURRENCY"]);
										else
											echo GetMessage("CTMP_FREE");

										echo "</font>";
									}

									if ($value["CAN_BUY"] != "Y")
										echo "</font>";
								}
								?>
								</font>
							</td>
							<?
						}
						?>
					</tr>
					<?
				}
				?>
			</table>

			<table border="0" class="tablebody" cellSpacing="1" width="100%" cellPadding="0">
				<?
				if ($bCanBuy && CModule::IncludeModule("sale"))
				{
					?>
					<tr>
						<td valign="top"><a href="<?= $APPLICATION->GetCurPageParam($PRODUCT_ID_VARIABLE."=".$ID."&".$ACTION_VARIABLE."=ADD2BASKET", array($PRODUCT_ID_VARIABLE, $ACTION_VARIABLE)) ?>"><img src="/bitrix/templates/.default/images/icons/basket.gif" width="15" height="15" alt="<?= GetMessage("CTMP_ADD2BASKET_ALT") ?>" border="0"></a></td>
						<td valign="top"><font class="text"><nobr><a href="<?= $APPLICATION->GetCurPageParam($PRODUCT_ID_VARIABLE."=".$ID."&".$ACTION_VARIABLE."=ADD2BASKET", array($PRODUCT_ID_VARIABLE, $ACTION_VARIABLE)) ?>"><?= GetMessage("CTMP_ADD2BASKET") ?></a></nobr></font></td>
					</tr>
					<tr>
						<td><a href="<?= $APPLICATION->GetCurPageParam($PRODUCT_ID_VARIABLE."=".$ID."&".$ACTION_VARIABLE."=BUY", array($PRODUCT_ID_VARIABLE, $ACTION_VARIABLE)) ?>"><img src="/bitrix/templates/.default/images/icons/buy.gif" width="15" height="15" alt="<?= GetMessage("CTMP_BUY_ALT") ?>" border="0"></a></td>
						<td><font class="text"><nobr><a href="<?= $APPLICATION->GetCurPageParam($PRODUCT_ID_VARIABLE."=".$ID."&".$ACTION_VARIABLE."=BUY", array($PRODUCT_ID_VARIABLE, $ACTION_VARIABLE)) ?>"><?= GetMessage("CTMP_BUY") ?></a></nobr></font></td>
					</tr>
					<?
				}
				?>
			</table>

			</td></tr></table>
			<?
		}
		if ($CACHE_TIME>0)
			$cache->EndDataCache(array());
	}
	else
	{
		?>
		<font class="text"><?= GetMessage("CTMP_NO_PRODUCT") ?></font>
		<?
	}

}

//*******************************************************
endif;
?>
