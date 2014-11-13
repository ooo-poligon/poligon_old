<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
IncludeTemplateLangFile(__FILE__);

$REGISTER_PAGE = Trim($REGISTER_PAGE);
if (StrLen($REGISTER_PAGE) <= 0)
	$REGISTER_PAGE = $GLOBALS["REGISTER_PAGE"];
if (StrLen($REGISTER_PAGE) <= 0)
	$REGISTER_PAGE = "register.php";

if (CModule::IncludeModule("sale"))
{
	$APPLICATION->SetTitle(GetMessage("SPCA_AFFILIATE_ACCOUNT"));

	$arTransactTypes = array(
		"AFFILIATE_IN" => GetMessage("SPCA_AFFILIATE_PAY"),
		"AFFILIATE_ACCT" => GetMessage("SPCA_AFFILIATE_TRANSF"),
		"AFFILIATE_CLEAR" => GetMessage("SPCA_AFFILIATE_CLEAR"),
	);

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
				$affiliateCurrency = CSaleLang::GetLangCurrency(SITE_ID);

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
									<td valign="middle" colspan="2" align="center" nowrap class="tablehead"><font class="tableheadtext"><?echo GetMessage("SPCA_FILTER")?></font></td>
								</tr>
								<tr valign="middle">
									<td align="right" class="tablebody" nowrap><font class="tablebodytext"><?echo GetMessage("SPCA_PERIOD")?></font></td>
									<td align="left" class="tablebody" nowrap><?echo CalendarPeriod("filter_date_from", $GLOBALS["filter_date_from"], "filter_date_to", $GLOBALS["filter_date_to"], "bfilter", "N", "", "class='inputtext'")?></td>
								</tr>
								<tr>
									<td valign="center" colspan="2" align="center" nowrap class="tablehead">
										<input type="submit" name="filter" value="<?echo GetMessage("SPCA_SET")?>" class="inputbodybutton">&nbsp;&nbsp;
										<input type="submit" name="del_filter" value="<?echo GetMessage("SPCA_UNSET")?>" class="inputbodybutton">
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
					"AFFILIATE_ID" => $arAffiliate["ID"]
				);
				if (StrLen($GLOBALS["filter_date_from"]) > 0)
					$arFilter[">=TRANSACT_DATE"] = Trim($GLOBALS["filter_date_from"]);
				if (StrLen($GLOBALS["filter_date_to"]) > 0)
					$arFilter["<=TRANSACT_DATE"] = Trim($GLOBALS["filter_date_to"]);

				$dbTransactList = CSaleAffiliateTransact::GetList(
					array("TRANSACT_DATE" => "ASC"),
					$arFilter,
					false,
					false,
					array("ID", "TRANSACT_DATE", "AMOUNT", "CURRENCY", "DEBIT", "DESCRIPTION")
				);
				?>

				<table border="0" cellspacing="0" cellpadding="0" class="tableborder" width="100%">
					<tr valign="top">
						<td>
							<table border="0" cellspacing="1" cellpadding="3" width="100%">
								<tr>
									<td align="center" nowrap class="tablehead"><font class="tableheadtext"><?echo GetMessage("SPCA_DATE")?></font></td>
									<td align="center" nowrap class="tablehead"><font class="tableheadtext"><?echo GetMessage("SPCA_INCOME")?></font></td>
									<td align="center" nowrap class="tablehead"><font class="tableheadtext"><?echo GetMessage("SPCA_OUTCOME")?></font></td>
									<td align="center" nowrap class="tablehead"><font class="tableheadtext"><?echo GetMessage("SPCA_COMMENT")?></font></td>
								</tr>
								<?
								if ($arTransactList = $dbTransactList->Fetch())
								{
									do
									{
										?>
										<tr>
											<td class="tablebody"><font class="tablebodytext"><?= $arTransactList["TRANSACT_DATE"] ?></font></td>
											<td align="right" class="tablebody"><font class="tablebodytext"><?= (($arTransactList["DEBIT"] == "Y") ? SaleFormatCurrency($arTransactList["AMOUNT"], $arTransactList["CURRENCY"]) : "&nbsp;") ?></font></td>
											<td align="right" class="tablebody"><font class="tablebodytext"><?= (($arTransactList["DEBIT"] != "Y") ? SaleFormatCurrency($arTransactList["AMOUNT"], $arTransactList["CURRENCY"]) : "&nbsp;") ?></font></td>
											<td class="tablebody"><font class="tablebodytext"><?= (array_key_exists($arTransactList["DESCRIPTION"], $arTransactTypes) ? $arTransactTypes[$arTransactList["DESCRIPTION"]] : "&nbsp;") ?></font></td>
										</tr>
										<?
									}
									while ($arTransactList = $dbTransactList->Fetch());
								}
								else
								{
									?>
									<tr>
										<td class="tablebody" colspan="4"><font class="tablebodytext"><?echo GetMessage("SPCA_NO_ACT")?></font></td>
									</tr>
									<?
								}
								?>
								<tr>
									<td class="tablehead"><font class="tablebodytext"><?echo GetMessage("SPCA_ON_ACCT")?> <?= date($DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")), time()) ?></font></td>
									<td align="right" class="tablehead"><font class="tablebodytext"><?= (($arAffiliate["PAID_SUM"] >= 0) ? SaleFormatCurrency($arAffiliate["PAID_SUM"], $affiliateCurrency) : "&nbsp;") ?></font></td>
									<td align="right" class="tablehead"><font class="tablebodytext"><?= (($arAffiliate["PAID_SUM"] < 0) ? SaleFormatCurrency(-$arAffiliate["PAID_SUM"], $affiliateCurrency) : "&nbsp;") ?></font></td>
									<td class="tablehead"><font class="tablebodytext">&nbsp;</font></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
				<?
			}
			else
			{
				?><font class="text"><b><?echo GetMessage("SPCA_UNACTIVE_AFF")?></b></font><?
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
	<font class="text"><b><?echo GetMessage("SPCA_NO_SHOP")?></b></font>
	<?
}
?>