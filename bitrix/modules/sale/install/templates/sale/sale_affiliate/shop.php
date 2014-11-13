<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
IncludeTemplateLangFile(__FILE__);

$REGISTER_PAGE = Trim($REGISTER_PAGE);
if (StrLen($REGISTER_PAGE) <= 0)
	$REGISTER_PAGE = $GLOBALS["REGISTER_PAGE"];
if (StrLen($REGISTER_PAGE) <= 0)
	$REGISTER_PAGE = "register.php";

if (CModule::IncludeModule("sale"))
{
	$APPLICATION->SetTitle(GetMessage("SPCAS1_PROG_REPORT"));

	if ($GLOBALS["USER"]->IsAuthorized())
	{
		$dbAffiliate = CSaleAffiliate::GetList(
			array("TRANSACT_DATE" => "ASC"),
			array(
				"USER_ID" => IntVal($GLOBALS["USER"]->GetID()),
				"SITE_ID" => SITE_ID,
			),
			false,
			false,
			array("ID", "PLAN_ID", "ACTIVE", "PAID_SUM", "APPROVED_SUM", "PENDING_SUM", "LAST_CALCULATE")
		);
		if ($arAffiliate = $dbAffiliate->Fetch())
		{
			if ($arAffiliate["ACTIVE"] == "Y")
			{
				if (strlen($GLOBALS["del_filter"])>0)
					DelFilter(Array("filter_date_from", "filter_date_to"));
				else
					InitFilter(Array("filter_date_from", "filter_date_to"));

				if (StrLen($GLOBALS["filter_date_from"]) <= 0 && StrLen($GLOBALS["filter_date_to"]) <= 0)
				{
					$GLOBALS["filter_date_from"] = date($DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")), mktime(0, 0, 0, date("m") - 3, 1, date("Y")));
					$GLOBALS["filter_date_to"] = date($DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")), mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
				}
				?>
				<table border="0" cellspacing="1" cellpadding="0" class="tableborder" width="0%">
					<form method="GET" action="<?= $APPLICATION->GetCurPage() ?>" name="bfilter">
					<tr valign="top">
						<td>
							<table border="0" cellspacing="0" cellpadding="3" width="100%">
								<tr>
									<td valign="middle" colspan="2" align="center" nowrap class="tablehead"><font class="tableheadtext"><?echo GetMessage("SPCAS1_FILTER")?></font></td>
								</tr>
								<tr valign="middle">
									<td align="right" class="tablebody" nowrap><font class="tablebodytext"><?echo GetMessage("SPCAS1_PERIOD")?></font></td>
									<td align="left" class="tablebody" nowrap><?echo CalendarPeriod("filter_date_from", $GLOBALS["filter_date_from"], "filter_date_to", $GLOBALS["filter_date_to"], "bfilter", "N", "", "class='inputtext'")?></td>
								</tr>
								<tr>
									<td valign="center" colspan="2" align="center" nowrap class="tablehead">
										<input type="submit" name="filter" value="<?echo GetMessage("SPCAS1_SET")?>" class="inputbodybutton">&nbsp;&nbsp;
										<input type="submit" name="del_filter" value="<?echo GetMessage("SPCAS1_UNSET")?>" class="inputbodybutton">
									</td>
								</tr>
							</table>
						</td>
					</tr>
					</form>
				</table>
				<br>
				<?
				$arFilter = array(
					"AFFILIATE_ID" => $arAffiliate["ID"],
					"ALLOW_DELIVERY" => "Y",
					"CANCELED" => "N",
					"LID" => SITE_ID
				);
				if (StrLen($GLOBALS["filter_date_from"]) > 0)
					$arFilter[">=DATE_ALLOW_DELIVERY"] = Trim($GLOBALS["filter_date_from"]);
				if (StrLen($GLOBALS["filter_date_to"]) > 0)
					$arFilter["<=DATE_ALLOW_DELIVERY"] = Trim($GLOBALS["filter_date_to"]);

				$dbItemsList = CSaleOrder::GetList(
					array("BASKET_MODULE" => "ASC", "BASKET_NAME" => "ASC", "BASKET_PRODUCT_ID" => "ASC"),
					$arFilter,
					array("BASKET_MODULE", "BASKET_PRODUCT_ID", "BASKET_NAME", "BASKET_PRICE", "BASKET_CURRENCY", "BASKET_DISCOUNT_PRICE", "SUM" => "BASKET_QUANTITY")
				);
				?>

				<table border="0" cellspacing="0" cellpadding="0" class="tableborder" width="100%">
					<tr valign="top">
						<td>
							<table border="0" cellspacing="1" cellpadding="3" width="100%">
								<tr>
									<td align="center" nowrap class="tablehead"><font class="tableheadtext"><?echo GetMessage("SPCAS1_NAME")?></font></td>
									<td align="center" nowrap class="tablehead"><font class="tableheadtext"><?echo GetMessage("SPCAS1_QUANTITY")?></font></td>
									<td align="center" nowrap class="tablehead"><font class="tableheadtext"><?echo GetMessage("SPCAS1_SUM")?></font></td>
								</tr>
								<?
								if ($arItemsList = $dbItemsList->Fetch())
								{
									$affiliateCurrency = CSaleLang::GetLangCurrency(SITE_ID);

									$currentBasketModule = $arItemsList["BASKET_MODULE"];
									$currentBasketProductID = $arItemsList["BASKET_PRODUCT_ID"];
									$currentBasketName = $arItemsList["BASKET_NAME"];
									$currentQuantity = 0;
									$currentSum = 0;

									$totalQuantity = 0;
									$totalSum = 0;

									do
									{
										if ($currentBasketModule != $arItemsList["BASKET_MODULE"]
											|| $currentBasketProductID != $arItemsList["BASKET_PRODUCT_ID"]
											|| $currentBasketName != $arItemsList["BASKET_NAME"])
										{
											?>
											<tr>
												<td class="tablebody"><font class="tablebodytext"><?= htmlspecialcharsex($currentBasketName) ?></font></td>
												<td align="right" class="tablebody"><font class="tablebodytext"><?= $currentQuantity ?></font></td>
												<td align="right" class="tablebody"><font class="tablebodytext"><?= SaleFormatCurrency($currentSum, $affiliateCurrency) ?></font></td>
											</tr>
											<?
											$currentBasketModule = $arItemsList["BASKET_MODULE"];
											$currentBasketProductID = $arItemsList["BASKET_PRODUCT_ID"];
											$currentBasketName = $arItemsList["BASKET_NAME"];

											$totalQuantity += $currentQuantity;
											$totalSum += $currentSum;

											$currentQuantity = 0;
											$currentSum = 0;
										}

										$currentQuantity += $arItemsList["BASKET_QUANTITY"];
										if ($affiliateCurrency != $arItemsList["BASKET_CURRENCY"])
											$currentSum += CCurrencyRates::ConvertCurrency(($arItemsList["BASKET_PRICE"] - $arItemsList["BASKET_DISCOUNT_PRICE"]) * $arItemsList["BASKET_QUANTITY"], $arItemsList["BASKET_CURRENCY"], $affiliateCurrency);
										else
											$currentSum += ($arItemsList["BASKET_PRICE"] - $arItemsList["BASKET_DISCOUNT_PRICE"]) * $arItemsList["BASKET_QUANTITY"];
									}
									while ($arItemsList = $dbItemsList->Fetch());

									if ($currentBasketModule != $arItemsList["BASKET_MODULE"]
										|| $currentBasketProductID != $arItemsList["BASKET_PRODUCT_ID"]
										|| $currentBasketName != $arItemsList["BASKET_NAME"])
									{
										?>
										<tr>
											<td class="tablebody"><font class="tablebodytext"><?= htmlspecialcharsex($currentBasketName) ?></font></td>
											<td align="right" class="tablebody"><font class="tablebodytext"><?= $currentQuantity ?></font></td>
											<td align="right" class="tablebody"><font class="tablebodytext"><?= SaleFormatCurrency($currentSum, $affiliateCurrency) ?></font></td>
										</tr>
										<?
										$totalQuantity += $currentQuantity;
										$totalSum += $currentSum;
									}
									?>
									<tr>
										<td class="tablehead"><font class="tablebodytext"><?echo GetMessage("SPCAS1_ITOG")?></font></td>
										<td align="right" class="tablehead"><font class="tablebodytext"><?= $totalQuantity ?></font></td>
										<td align="right" class="tablehead"><font class="tablebodytext"><?= SaleFormatCurrency($totalSum, $affiliateCurrency) ?></font></td>
									</tr>
									<?
								}
								else
								{
									?>
									<tr>
										<td class="tablebody" colspan="3"><font class="tablebodytext"><?echo GetMessage("SPCAS1_NO_ACT")?></font></td>
									</tr>
									<?
								}
								?>
							</table>
						</td>
					</tr>
				</table>
				<?
			}
			else
			{
				?><font class="text"><b><?echo GetMessage("SPCAS1_UNACTIVE_AFF")?></b></font><?
			}
		}
		else
		{
			LocalRedirect($REGISTER_PAGE."?REDIRECT_PAGE=".UrlEncode($APPLICATION->GetCurPage()));
			die();
		}
	}
	else
	{
		LocalRedirect($REGISTER_PAGE."?REDIRECT_PAGE=".UrlEncode($APPLICATION->GetCurPage()));
		die();
	}
}
else
{
	?>
	<font class="text"><b><?echo GetMessage("SPCAS1_NO_SHOP")?></b></font>
	<?
}
?>