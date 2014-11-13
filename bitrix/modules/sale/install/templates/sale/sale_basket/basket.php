<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
IncludeTemplateLangFile(__FILE__);
if (CModule::IncludeModule("sale")):
//*******************************************************

$ORDER_PAGE = Trim($ORDER_PAGE);
if (strlen($ORDER_PAGE) <= 0)
	$ORDER_PAGE = $GLOBALS["ORDER_PAGE"];
if (strlen($ORDER_PAGE) <= 0)
	$ORDER_PAGE = "order.php";

if (!isset($COLUMNS_LIST) || !is_array($COLUMNS_LIST) || count($COLUMNS_LIST) <= 0)
	$COLUMNS_LIST = array("NAME", "PRICE", "TYPE", "QUANTITY", "DELETE", "DELAY", "WEIGHT");

$HIDE_COUPON = (($HIDE_COUPON == "Y") ? "Y" : "N");
if ($HIDE_COUPON != "Y")
{
	if (!CModule::IncludeModule("catalog"))
		$HIDE_COUPON = "Y";
}

$GLOBALS["APPLICATION"]->SetTitle(GetMessage("STB_TITLE"));

if (strlen($_REQUEST["BasketRefresh"]) > 0 || strlen($_REQUEST["BasketOrder"]) > 0)
{
	if ($HIDE_COUPON != "Y")
	{
		$COUPON = Trim($_REQUEST["COUPON"]);
		if (strlen($COUPON) > 0)
			CCatalogDiscount::SetCoupon($COUPON);
		else
			CCatalogDiscount::ClearCoupon();
	}

	$dbBasketItems = CSaleBasket::GetList(
			array("NAME" => "ASC"),
			array(
					"FUSER_ID" => CSaleBasket::GetBasketUserID(),
					"LID" => SITE_ID,
					"ORDER_ID" => "NULL"
				),
			false,
			false,
			array("ID", "CALLBACK_FUNC", "MODULE", "PRODUCT_ID", "QUANTITY", "DELAY", "CAN_BUY")
		);
	while ($arBasketItems = $dbBasketItems->Fetch())
	{
		if (strlen($arBasketItems["CALLBACK_FUNC"])>0)
		{
			CSaleBasket::UpdatePrice($arBasketItems["ID"], $arBasketItems["CALLBACK_FUNC"], $arBasketItems["MODULE"], $arBasketItems["PRODUCT_ID"], $arBasketItems["QUANTITY"]);
			$arBasketItems = CSaleBasket::GetByID($arBasketItems["ID"]);
		}

		$quantityTmp = IntVal($_REQUEST["QUANTITY_".$arBasketItems["ID"]]);
		$deleteTmp = (($_REQUEST["DELETE_".$arBasketItems["ID"]] == "Y") ? "Y" : "N");
		$delayTmp = (($_REQUEST["DELAY_".$arBasketItems["ID"]] == "Y") ? "Y" : "N");

		if ($deleteTmp == "Y" && in_array("DELETE", $COLUMNS_LIST))
		{
			CSaleBasket::Delete($arBasketItems["ID"]);
		}
		elseif ($arBasketItems["DELAY"] == "N" && $arBasketItems["CAN_BUY"] == "Y")
		{
			UnSet($arFields);
			$arFields = array();
			if (in_array("QUANTITY", $COLUMNS_LIST))
				$arFields["QUANTITY"] = $quantityTmp;
			if (in_array("DELAY", $COLUMNS_LIST))
				$arFields["DELAY"] = $delayTmp;

			if (count($arFields) > 0
				&&
					($arBasketItems["QUANTITY"] != $arFields["QUANTITY"] && in_array("QUANTITY", $COLUMNS_LIST)
						|| $arBasketItems["DELAY"] != $arFields["DELAY"] && in_array("DELAY", $COLUMNS_LIST)
					)
				)
			{
				CSaleBasket::Update($arBasketItems["ID"], $arFields);
			}
		}
		elseif ($arBasketItems["DELAY"] == "Y" && $arBasketItems["CAN_BUY"] == "Y")
		{
			UnSet($arFields);
			$arFields = array();
			if (in_array("DELAY", $COLUMNS_LIST))
				$arFields["DELAY"] = $delayTmp;

			if (count($arFields) > 0
				&& 
					($arBasketItems["DELAY"] != $arFields["DELAY"] && in_array("DELAY", $COLUMNS_LIST))
				)
			{
				CSaleBasket::Update($arBasketItems["ID"], $arFields);
			}
		}
	}

	if (strlen($_REQUEST["BasketOrder"]) > 0)
	{
		LocalRedirect($ORDER_PAGE);
	}
	else
	{
		if (strlen($_SERVER["REQUEST_URI"]) > 0)
			LocalRedirect($_SERVER["REQUEST_URI"]);

		unset($_REQUEST["BasketRefresh"]);
		unset($_REQUEST["BasketOrder"]);
	}
}


$arBasketItems = array();

$dbBasketItems = CSaleBasket::GetList(
		array(
				"NAME" => "ASC",
				"ID" => "ASC"
			),
		array(
				"FUSER_ID" => CSaleBasket::GetBasketUserID(),
				"LID" => SITE_ID,
				"ORDER_ID" => "NULL"
			),
		false,
		false,
		array("ID", "NAME", "CALLBACK_FUNC", "MODULE", "PRODUCT_ID", "QUANTITY", "DELAY", "CAN_BUY", "PRICE", "WEIGHT", "DETAIL_PAGE_URL", "NOTES")
	);
while ($arItems = $dbBasketItems->Fetch())
{
	if (strlen($arItems["CALLBACK_FUNC"]) > 0)
	{
		CSaleBasket::UpdatePrice($arItems["ID"], $arItems["CALLBACK_FUNC"], $arItems["MODULE"], $arItems["PRODUCT_ID"], $arItems["QUANTITY"]);
		$arItems = CSaleBasket::GetByID($arItems["ID"]);
	}

	$arBasketItems[] = $arItems;
}

$bShowReady = False;
$bShowDelay = False;
$bShowNotAvail = False;
for ($i = 0; $i < count($arBasketItems); $i++)
{
	if ($arBasketItems[$i]["DELAY"] == "N" && $arBasketItems[$i]["CAN_BUY"] == "Y")
		$bShowReady = True;
	elseif ($arBasketItems[$i]["DELAY"] == "Y" && $arBasketItems[$i]["CAN_BUY"] == "Y")
		$bShowDelay = True;
	elseif ($arBasketItems[$i]["CAN_BUY"] == "N")
		$bShowNotAvail = True;
}


if (count($arBasketItems) > 0)
{
	?>
	<form method="POST" action="<?= $APPLICATION->GetCurPage() ?>">
		<?
		if ($bShowReady)
		{
			?>
			<font class="tabletitletext">
			<?= GetMessage("STB_ORDER_PROMT") ?>
			</font><br><br>
			<table width="100%">
				<tr>
					<td align="left" valign="top" width="30%">
						<input type="submit" value="<?= GetMessage("SALE_REFRESH")?>" name="BasketRefresh" class="inputbuttonflat">
					</td>
					<td align="right" valign="top" width="40%">&nbsp;</td>
					<td align="right" valign="top" width="30%">
						<input type="submit" value="<?= GetMessage("SALE_ORDER")?>" name="BasketOrder" class="inputbuttonflat">
					</td>
				</tr>
			</table>
			<br>

			<table cellpadding="0" cellspacing="0" border="0" width="100%" class="tableborder"><tr><td>
			<table cellpadding="2" cellspacing="1" border="0" width="100%">
				<tr>
					<?if (in_array("NAME", $COLUMNS_LIST)):?>
						<td class="tablehead" align="center"><font class="tableheadtext"><?= GetMessage("SALE_NAME")?></font></td>
					<?endif;?>
					<?if (in_array("PRICE", $COLUMNS_LIST)):?>
						<td class="tablehead" align="center"><font class="tableheadtext"><?= GetMessage("SALE_PRICE")?></font></td>
					<?endif;?>
					<?if (in_array("TYPE", $COLUMNS_LIST)):?>
						<td class="tablehead" align="center"><font class="tableheadtext"><?= GetMessage("SALE_PRICE_TYPE")?></font></td>
					<?endif;?>
					<?if (in_array("QUANTITY", $COLUMNS_LIST)):?>
						<td class="tablehead" align="center"><font class="tableheadtext"><?= GetMessage("SALE_QUANTITY")?></font></td>
					<?endif;?>
					<?if (in_array("DELETE", $COLUMNS_LIST)):?>
						<td class="tablehead" align="center"><font class="tableheadtext"><?= GetMessage("SALE_DELETE")?></font></td>
					<?endif;?>
					<?if (in_array("DELAY", $COLUMNS_LIST)):?>
						<td class="tablehead" align="center"><font class="tableheadtext"><?= GetMessage("SALE_OTLOG")?></font></td>
					<?endif;?>
					<?if (in_array("WEIGHT", $COLUMNS_LIST)):?>
						<td class="tablehead" align="center"><font class="tableheadtext"><?= GetMessage("SALE_WEIGHT")?></font></td>
					<?endif;?>
				</tr>
				<?
				$allSum = 0;
				$allWeight = 0;
				$allCurrency = CSaleLang::GetLangCurrency(SITE_ID);
				for ($i = 0; $i < count($arBasketItems); $i++)
				{
					if ($arBasketItems[$i]["DELAY"] == "N" && $arBasketItems[$i]["CAN_BUY"] == "Y")
					{
						$allSum += ($arBasketItems[$i]["PRICE"] * $arBasketItems[$i]["QUANTITY"]);
						$allWeight += ($arBasketItems[$i]["WEIGHT"] * $arBasketItems[$i]["QUANTITY"]);
						?>
						<tr>
							<?if (in_array("NAME", $COLUMNS_LIST)):?>
								<td class="tablebody" align="left"><font class="tablebodytext"><?
								if (strlen($arBasketItems[$i]["DETAIL_PAGE_URL"])>0):
									?><a href="<?= $arBasketItems[$i]["DETAIL_PAGE_URL"] ?>"><?
								endif;
								?><b><?= $arBasketItems[$i]["NAME"] ?></b><?
								if (strlen($arBasketItems[$i]["DETAIL_PAGE_URL"])>0):
									?></a><?
								endif;
								?></font></td>
							<?endif;?>
							<?if (in_array("PRICE", $COLUMNS_LIST)):?>
								<td class="tablebody" align="right"><font class="tablebodytext"><?= SaleFormatCurrency($arBasketItems[$i]["PRICE"], $arBasketItems[$i]["CURRENCY"]) ?></font></td>
							<?endif;?>
							<?if (in_array("TYPE", $COLUMNS_LIST)):?>
								<td class="tablebody" align="left"><font class="tablebodytext"><?= $arBasketItems[$i]["NOTES"]?></font></td>
							<?endif;?>
							<?if (in_array("QUANTITY", $COLUMNS_LIST)):?>
								<td class="tablebody" align="center"><font class="tablebodytext"><input type="text" class="inputtext" name="QUANTITY_<?= $arBasketItems[$i]["ID"] ?>" value="<?= $arBasketItems[$i]["QUANTITY"]?>" size="3"></font></td>
							<?endif;?>
							<?if (in_array("DELETE", $COLUMNS_LIST)):?>
								<td class="tablebody" align="center"><font class="tablebodytext"><input type="checkbox" class="inputcheckbox" name="DELETE_<?= $arBasketItems[$i]["ID"] ?>" value="Y"></font></td>
							<?endif;?>
							<?if (in_array("DELAY", $COLUMNS_LIST)):?>
								<td class="tablebody" align="center"><font class="tablebodytext"><input type="checkbox" class="inputcheckbox" name="DELAY_<?= $arBasketItems[$i]["ID"] ?>" value="Y"></font></td>
							<?endif;?>
							<?if (in_array("WEIGHT", $COLUMNS_LIST)):?>
								<td class="tablebody" align="right"><font class="tablebodytext"><?= $arBasketItems[$i]["WEIGHT"] ?> <?= GetMessage("SALE_WEIGHT_G")?></font></td>
							<?endif;?>
						</tr>
						<?
					}
				}
				?>
				<tr>
					<?if (in_array("NAME", $COLUMNS_LIST)):?>
						<td class="tablebody" align="right"><font class="tablebodytext"><b><?= GetMessage("SALE_ITOGO")?>:</b></font></td>
					<?endif;?>
					<?if (in_array("PRICE", $COLUMNS_LIST)):?>
						<td class="tablebody" align="right"><font class="tablebodytext"><?= SaleFormatCurrency($allSum, $allCurrency) ?></font></td>
					<?endif;?>
					<?if (in_array("TYPE", $COLUMNS_LIST)):?>
						<td class="tablebody">&nbsp;</td>
					<?endif;?>
					<?if (in_array("QUANTITY", $COLUMNS_LIST)):?>
						<td class="tablebody">&nbsp;</td>
					<?endif;?>
					<?if (in_array("DELETE", $COLUMNS_LIST)):?>
						<td class="tablebody">&nbsp;</td>
					<?endif;?>
					<?if (in_array("DELAY", $COLUMNS_LIST)):?>
						<td class="tablebody">&nbsp;</td>
					<?endif;?>
					<?if (in_array("WEIGHT", $COLUMNS_LIST)):?>
						<td class="tablebody" align="right"><font class="tablebodytext"><?= $allWeight ?> <?= GetMessage("SALE_WEIGHT_G")?></font></td>
					<?endif;?>
				</tr>
			</table>
			</td></tr></table>

			<br>
			<table width="100%">
				<?if ($HIDE_COUPON != "Y"):?>
					<tr>
						<td align="left" valign="top" colspan="3">
							<font class="tablebodytext">
							<?= GetMessage("STB_COUPON_PROMT") ?>
							<?
							$arCoupons = CCatalogDiscount::GetCoupons();
							$COUPON = "";
							if (count($arCoupons) > 0)
								$COUPON = $arCoupons[0];
							?>
							<input type="text" name="COUPON" value="<?= htmlspecialchars($COUPON) ?>" class="inputtext" size="20">
							</font>
							<br><br>
						</td>
					</tr>
				<?endif;?>
				<tr>
					<td align="left" valign="top" width="30%">
						<input type="submit" value="<?echo GetMessage("SALE_REFRESH")?>" name="BasketRefresh" class="inputbuttonflat"><br>
						<font class="smalltext"><?echo GetMessage("SALE_REFRESH_DESCR")?><br></font>
					</td>
					<td align="right" valign="top" width="40%">&nbsp;</td>
					<td align="right" valign="top" width="30%">
						<input type="submit" value="<?echo GetMessage("SALE_ORDER")?>" name="BasketOrder" class="inputbuttonflat"><br>
						<font class="smalltext"><?echo GetMessage("SALE_ORDER_DESCR")?><br></font>
					</td>
				</tr>
			</table>
			<br>
			<?
		}

		if ($bShowDelay || $bShowNotAvail)
		{
			echo "<br><br>";
		}

		if ($bShowDelay)
		{
			?>
			<font class="subtitletext"><?= GetMessage("SALE_OTLOG_TITLE")?></font><br><br>
			<table cellpadding="0" cellspacing="0" border="0" width="100%" class="tableborder"><tr><td>
			<table cellpadding="2" cellspacing="1" border="0" width="100%">
				<tr>
					<?if (in_array("NAME", $COLUMNS_LIST)):?>
						<td class="tablehead" align="center"><font class="tableheadtext"><?= GetMessage("SALE_NAME")?></font></td>
					<?endif;?>
					<?if (in_array("PRICE", $COLUMNS_LIST)):?>
						<td class="tablehead" align="center"><font class="tableheadtext"><?= GetMessage("SALE_PRICE")?></font></td>
					<?endif;?>
					<?if (in_array("TYPE", $COLUMNS_LIST)):?>
						<td class="tablehead" align="center"><font class="tableheadtext"><?= GetMessage("SALE_PRICE_TYPE")?></font></td>
					<?endif;?>
					<?if (in_array("QUANTITY", $COLUMNS_LIST)):?>
						<td class="tablehead" align="center"><font class="tableheadtext"><?= GetMessage("SALE_QUANTITY")?></font></td>
					<?endif;?>
					<?if (in_array("DELETE", $COLUMNS_LIST)):?>
						<td class="tablehead" align="center"><font class="tableheadtext"><?= GetMessage("SALE_DELETE")?></font></td>
					<?endif;?>
					<?if (in_array("DELAY", $COLUMNS_LIST)):?>
						<td class="tablehead" align="center"><font class="tableheadtext"><?= GetMessage("SALE_OTLOG")?></font></td>
					<?endif;?>
					<?if (in_array("WEIGHT", $COLUMNS_LIST)):?>
						<td class="tablehead" align="center"><font class="tableheadtext"><?= GetMessage("SALE_WEIGHT")?></font></td>
					<?endif;?>
				</tr>
				<?
				for ($i = 0; $i<count($arBasketItems); $i++)
				{
					if ($arBasketItems[$i]["DELAY"]=="Y" && $arBasketItems[$i]["CAN_BUY"]=="Y")
					{
						?>
						<tr>
							<?if (in_array("NAME", $COLUMNS_LIST)):?>
								<td class="tablebody" align="left"><font class="tablebodytext"><?
								if (strlen($arBasketItems[$i]["DETAIL_PAGE_URL"])>0):
									?><a href="<?echo $arBasketItems[$i]["DETAIL_PAGE_URL"] ?>"><?
								endif;
								?><b><?echo $arBasketItems[$i]["NAME"]?></b><?
								if (strlen($arBasketItems[$i]["DETAIL_PAGE_URL"])>0):
									?></a><?
								endif;
								?></font></td>
							<?endif;?>
							<?if (in_array("PRICE", $COLUMNS_LIST)):?>
								<td class="tablebody" align="right"><font class="tablebodytext"><?echo SaleFormatCurrency($arBasketItems[$i]["PRICE"], $arBasketItems[$i]["CURRENCY"]) ?></font></td>
							<?endif;?>
							<?if (in_array("TYPE", $COLUMNS_LIST)):?>
								<td class="tablebody" align="left"><font class="tablebodytext"><?echo $arBasketItems[$i]["NOTES"]?></font></td>
							<?endif;?>
							<?if (in_array("QUANTITY", $COLUMNS_LIST)):?>
								<td class="tablebody" align="center"><font class="tablebodytext"><?echo $arBasketItems[$i]["QUANTITY"]?></font></td>
							<?endif;?>
							<?if (in_array("DELETE", $COLUMNS_LIST)):?>
								<td class="tablebody" align="center"><font class="tablebodytext"><input type="checkbox" class="inputcheckbox" name="DELETE_<?echo $arBasketItems[$i]["ID"] ?>" value="Y"></font></td>
							<?endif;?>
							<?if (in_array("DELAY", $COLUMNS_LIST)):?>
								<td class="tablebody" align="center"><font class="tablebodytext"><input type="checkbox" class="inputcheckbox" name="DELAY_<?echo $arBasketItems[$i]["ID"] ?>" value="Y" checked></font></td>
							<?endif;?>
							<?if (in_array("WEIGHT", $COLUMNS_LIST)):?>
								<td class="tablebody" align="right"><font class="tablebodytext"><?echo $arBasketItems[$i]["WEIGHT"] ?> <?echo GetMessage("SALE_WEIGHT_G")?></font></td>
							<?endif;?>
						</tr>
						<?
					}
				}
				?>
			</table>
			</td></tr></table>

			<br>
			<table width="100%">
				<tr>
					<td align="left" valign="top" width="30%">
						<input type="submit" value="<?= GetMessage("SALE_REFRESH")?>" name="BasketRefresh" class="inputbuttonflat"><br>
						<font class="smalltext"><?= GetMessage("SALE_REFRESH_DESCR")?><br></font>
					</td>
					<td align="right" valign="top" width="70%">&nbsp;</td>
				</tr>
			</table>
			<br>
			<?
		}

		if ($bShowNotAvail)
		{
			?>
			<font class="subtitletext"><?= GetMessage("SALE_UNAVAIL_TITLE")?></font><br><br>
			<table cellpadding="0" cellspacing="0" border="0" width="100%" class="tableborder"><tr><td>
			<table cellpadding="2" cellspacing="1" border="0" width="100%">
				<tr>
					<?if (in_array("NAME", $COLUMNS_LIST)):?>
						<td class="tablehead" align="center"><font class="tableheadtext"><?echo GetMessage("SALE_NAME")?></font></td>
					<?endif;?>
					<?if (in_array("PRICE", $COLUMNS_LIST)):?>
						<td class="tablehead" align="center"><font class="tableheadtext"><?echo GetMessage("SALE_PRICE")?></font></td>
					<?endif;?>
					<?if (in_array("TYPE", $COLUMNS_LIST)):?>
						<td class="tablehead" align="center"><font class="tableheadtext"><?echo GetMessage("SALE_PRICE_TYPE")?></font></td>
					<?endif;?>
					<?if (in_array("QUANTITY", $COLUMNS_LIST)):?>
						<td class="tablehead" align="center"><font class="tableheadtext"><?echo GetMessage("SALE_QUANTITY")?></font></td>
					<?endif;?>
					<?if (in_array("DELETE", $COLUMNS_LIST)):?>
						<td class="tablehead" align="center"><font class="tableheadtext"><?echo GetMessage("SALE_DELETE")?></font></td>
					<?endif;?>
					<?if (in_array("WEIGHT", $COLUMNS_LIST)):?>
						<td class="tablehead" align="center"><font class="tableheadtext"><?echo GetMessage("SALE_WEIGHT")?></font></td>
					<?endif;?>
				</tr>
				<?
				for ($i = 0; $i < count($arBasketItems); $i++)
				{
					if ($arBasketItems[$i]["CAN_BUY"]=="N")
					{
						?>
						<tr>
							<?if (in_array("NAME", $COLUMNS_LIST)):?>
								<td class="tablebody" align="left"><font class="tablebodytext"><?
								if (strlen($arBasketItems[$i]["DETAIL_PAGE_URL"])>0):
									?><a href="<?= $arBasketItems[$i]["DETAIL_PAGE_URL"] ?>"><?
								endif;
								?><b><?= $arBasketItems[$i]["NAME"]?></b><?
								if (strlen($arBasketItems[$i]["DETAIL_PAGE_URL"])>0):
									?></a><?
								endif;
								?></font>
								</td>
							<?endif;?>
							<?if (in_array("PRICE", $COLUMNS_LIST)):?>
								<td class="tablebody" align="right"><font class="tablebodytext"><?= SaleFormatCurrency($arBasketItems[$i]["PRICE"], $arBasketItems[$i]["CURRENCY"]) ?></font></td>
							<?endif;?>
							<?if (in_array("TYPE", $COLUMNS_LIST)):?>
								<td class="tablebody" align="left"><font class="tablebodytext"><?= $arBasketItems[$i]["NOTES"]?></font></td>
							<?endif;?>
							<?if (in_array("QUANTITY", $COLUMNS_LIST)):?>
								<td class="tablebody" align="center"><font class="tablebodytext"><?= $arBasketItems[$i]["QUANTITY"]?></font></td>
							<?endif;?>
							<?if (in_array("DELETE", $COLUMNS_LIST)):?>
								<td class="tablebody" align="center"><font class="tablebodytext"><input type="checkbox" class="inputcheckbox" name="DELETE_<?echo $arBasketItems[$i]["ID"] ?>" value="Y"></font></td>
							<?endif;?>
							<?if (in_array("WEIGHT", $COLUMNS_LIST)):?>
								<td class="tablebody" align="right"><font class="tablebodytext"><?= $arBasketItems[$i]["WEIGHT"] ?> <?echo GetMessage("SALE_WEIGHT_G")?></font></td>
							<?endif;?>
						</tr>
						<?
					}
				}
				?>
			</table>
			</td></tr></table>

			<br>
			<table width="100%">
				<tr>
					<td align="left" valign="top" width="30%">
						<input type="submit" value="<?= GetMessage("SALE_REFRESH") ?>" name="BasketRefresh" class="inputbuttonflat"><br>
						<font class="smalltext"><?= GetMessage("SALE_REFRESH_DESCR") ?><br></font>
					</td>
					<td align="right" valign="top" width="70%">&nbsp;</td>
				</tr>
			</table>
			<?
		}
		?>
	</form>
	<?
}
else
{
	?><font class="text"><?= GetMessage("SALE_EMPTY_BASKET") ?></font><?
}

//*******************************************************
else:
	?>
	<font class="text"><b><?echo GetMessage("SALE_NO_MODULE_X")?></b></font>
	<?
endif;
?>