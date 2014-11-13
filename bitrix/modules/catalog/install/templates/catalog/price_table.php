<?
IncludeTemplateLangFile(__FILE__);
if (CModule::IncludeModule("catalog")):
//*******************************************************

// ATTENTION!
// This code is attached to price types settings (catalog module)
// This code should be changed if you have changed price types settings (catalog module)
// In this code we assume that price type #1 is base price (is shown striked
// if differs from retail price), price type #2 is retail price, price types #3,#4,... are other prices
// Also we assume that base price (#1) and retail price (#2) are available for all visitors (for anonymous)

$PRODUCT_ID = IntVal($PRODUCT_ID);
if ($PRODUCT_ID<=0)
	$PRODUCT_ID = $GLOBALS["ID"];

$BASKET_PAGE = Trim($BASKET_PAGE);

$PRICE_TYPE_OLD = IntVal($PRICE_TYPE_OLD);
$PRICE_TYPE_NEW = IntVal($PRICE_TYPE_NEW);

$CACHE_TIME = IntVal($CACHE_TIME);

// Let's add product to shopping cart if we need to do this
if (CModule::IncludeModule("sale"))
{
	if (($_REQUEST["action"] == "ADD2BASKET" || $_REQUEST["action"] == "BUY") && IntVal($_REQUEST["PRICE_ID"])>0)
	{
		Add2Basket($_REQUEST["PRICE_ID"]);
		if ($_REQUEST["action"] == "BUY")
			LocalRedirect($BASKET_PAGE);
	}
}

$cache = new CPHPCache;
$cache_id = "catalog_price_table_".$PRODUCT_ID."_".($USER->GetGroups())."_".$BASKET_PAGE."_".SITE_ID;

if ($CACHE_TIME>0 && $cache->InitCache($CACHE_TIME, $cache_id, "/".SITE_ID."/catalog/price_table.php/"))
{
	$cache->Output();
}
else
{
	if ($CACHE_TIME>0)
		$cache->StartDataCache($CACHE_TIME, $cache_id, "/".SITE_ID."/catalog/price_table.php/");
	?>

	<table border="0" cellpadding="0" cellspacing="1" class="tableborder" width="150" align="right">
		<tr>
			<td class="tablebody">
				<table border="0" cellspacing="1" width="100%" cellpadding="0">
					<tr>
						<td>
							<table border="0" class="tablebody" cellSpacing="0" width="100%" cellpadding="1">
								<?
								$arProduct = GetCatalogProduct($PRODUCT_ID);
								$arPrice = GetCatalogProductPriceList($PRODUCT_ID, "SORT", "ASC");
								$bCanBuy = False;
								
								// Let's find indexes of #1 and #2 price types
								$indPT1 = -1;
								$indPT2 = -1;
								for ($ii = 0; $ii<count($arPrice); $ii++)
								{
									if (IntVal($arPrice[$ii]["CATALOG_GROUP_ID"]) == $PRICE_TYPE_OLD)
										$indPT1 = $ii;
									if (IntVal($arPrice[$ii]["CATALOG_GROUP_ID"]) == $PRICE_TYPE_NEW)
										$indPT2 = $ii;
									if ($indPT1 >= 0 && $indPT2 >= 0)
										break;
								}
										
								// Retail price
								if ($indPT2 >= 0)
								{
									if ($arPrice[$indPT2]["CAN_BUY"] == "Y" && (IntVal($arProduct["QUANTITY"]) > 0 || $arProduct["QUANTITY_TRACE"] != "Y"))
										$bCanBuy = True;
									if ($bCanBuy)
										$PRICE_ID = $arPrice[$indPT2]["ID"];
									if (($arPrice[$indPT1]["PRICE"] != $arPrice[$indPT2]["PRICE"] || $arPrice[$indPT1]["CURRENCY"] != $arPrice[$indPT2]["CURRENCY"]) && $arPrice[$indPT1]["PRICE"] > 0)
									{
										?>
										<tr>
											<td style="padding-left: 3px;"><font class="text"><b><?echo GetMessage("CATALOG_PRICE_OLD") ?>:</b></font></td>
											<td align="left" style="padding-right: 3px;"><font class="text">&nbsp;<s><?echo FormatCurrency($arPrice[$indPT1]["PRICE"], $arPrice[$indPT1]["CURRENCY"]) ?></s></font></td>
										</tr>
										<?
									}
									?>
									<tr>
										<td style="padding-left: 3px;"><font class="text"><b><?echo GetMessage("CATALOG_PRICE") ?>:</b></font></td>
										<td align="left" style="padding-right: 3px;"><font class="text">&nbsp;<?echo FormatCurrency($arPrice[$indPT2]["PRICE"], $arPrice[$indPT2]["CURRENCY"]) ?></font></td>
									</tr>
									<?
									if (($arPrice[$indPT1]["PRICE"]!=$arPrice[$indPT2]["PRICE"] || $arPrice[$indPT1]["CURRENCY"]!=$arPrice[$indPT2]["CURRENCY"]) && $arPrice[$indPT1]["PRICE"]>0)
									{
										?>
										<tr>
											<td style="padding-left: 3px;"><font class="text"><b><?echo GetMessage("CATALOG_YOU_SAVE") ?>:</b></font></td>
											<td align="left" style="padding-right: 3px;"><font class="text">&nbsp;<?echo FormatCurrency(($arPrice[$indPT1]["PRICE"] - $arPrice[$indPT2]["PRICE"]), $arPrice[$indPT2]["CURRENCY"]) ?></font></td>
										</tr>
										<?
									}
									?>
									<tr>
										<td colspan="2"><img src="/bitrix/templates/.default/images/1.gif" title="" width="1" height="3"></td>
									</tr>
									<?
								}

								// Other price types
								for ($ii = 0; $ii<count($arPrice); $ii++)
								{
									if ($arPrice[$ii]["CAN_ACCESS"] == "Y" && $ii != $indPT1 && $ii != $indPT2)
									{
										if ($arPrice[$ii]["CAN_BUY"] == "Y" && (IntVal($arProduct["QUANTITY"]) > 0 || $arProduct["QUANTITY_TRACE"] != "Y"))
											$bCanBuy = True;
										if ($bCanBuy)
											$PRICE_ID = $arPrice[$ii]["ID"];

										// count discount
										$arDiscounts = CCatalogDiscount::GetDiscountByPrice($PRICE_ID, $GLOBALS["USER"]->GetUserGroupArray(), "N", SITE_ID);
										//$arDiscounts = CCatalogDiscount::GetDiscount($PRODUCT_ID, $iblockID, $arCatalogGroups = array(), $GLOBALS["USER"]->GetUserGroupArray(), "N", SITE_ID, $arDiscountCoupons = false)
										$PriceWDisc = CCatalogProduct::CountPriceWithDiscount($arPrice[$ii]["PRICE"], $arPrice[$ii]["CURRENCY"], $arDiscounts);
									
										if($PriceWDisc < $arPrice[$ii]["PRICE"])
										{
											?>
											<tr>
												<td style="padding-left: 3px;" valign="top"><font class="text"><b><? echo $arPrice[$ii]["CATALOG_GROUP_NAME"] ?>:</b></font></td>
												<td align="left" style="padding-right: 3px;"><font class="text">&nbsp;<s><? echo FormatCurrency($arPrice[$ii]["PRICE"], $arPrice[$ii]["CURRENCY"]) ?></s><br>
												<font color="red">&nbsp;<? echo FormatCurrency($PriceWDisc, $arPrice[$ii]["CURRENCY"]) ?></font>
											</font></td>
											</tr>
											<?
										}else{
											?>
											<tr>
												<td style="padding-left: 3px;"><font class="text"><b><? echo $arPrice[$ii]["CATALOG_GROUP_NAME"] ?>:</b></font></td>
												<td align="left" style="padding-right: 3px;"><font class="text">&nbsp;<? echo FormatCurrency($arPrice[$ii]["PRICE"], $arPrice[$ii]["CURRENCY"]) ?></font></td>
											</tr>
											<?
										}
									}
								}
								?>
							</table>
							<table border="0" class="tablebody" cellSpacing="1" width="100%" cellPadding="1">
								<?
								// If current user can buy this product
								if ($bCanBuy && CModule::IncludeModule("sale"))
								{
									?>
									<tr>
										<td colspan="2"><img src="/bitrix/templates/.default/images/1.gif" title="" width="1" height="4"></td>
									</tr>
									<tr>
										<td style="padding-left: 3px;"><a href="<?= $APPLICATION->GetCurPageParam("PRICE_ID=".$PRICE_ID."&action=ADD2BASKET", array("PRICE_ID", "action")) ?>"><img src="/bitrix/templates/.default/catalog/images/basket.gif" width="15" height="15" title="<? echo GetMessage("CATALOG_ADD_TO_BASKET"); ?>" border="0"></a></td>
										<td style="padding-right: 3px;"><nobr><a href="<?= $APPLICATION->GetCurPageParam("PRICE_ID=".$PRICE_ID."&action=ADD2BASKET", array("PRICE_ID", "action")) ?>" class="text"><? echo GetMessage("CATALOG_ADD_TO_BASKET"); ?></a></nobr></td>
									</tr>
									<tr>
										<td style="padding-left: 3px;"><a href="<?= $APPLICATION->GetCurPageParam("PRICE_ID=".$PRICE_ID."&action=BUY", array("PRICE_ID", "action")) ?>" class="text"><img src="/bitrix/templates/.default/catalog/images/buy.gif" width="15" height="15" title="<? echo GetMessage("CATALOG_BUY"); ?>" border="0"></a></td>
										<td style="padding-right: 3px;"><nobr><a href="<?= $APPLICATION->GetCurPageParam("PRICE_ID=".$PRICE_ID."&action=BUY", array("PRICE_ID", "action")) ?>" class="text"><? echo GetMessage("CATALOG_BUY"); ?></a></nobr></td>
									</tr>
									<?
								}
								?>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<?
	if ($CACHE_TIME>0)
		$cache->EndDataCache(array());

}

//*******************************************************
endif;
?>
