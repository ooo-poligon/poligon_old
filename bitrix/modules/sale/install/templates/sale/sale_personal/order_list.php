<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
IncludeTemplateLangFile(__FILE__);

if (CModule::IncludeModule("sale")):
	$GLOBALS["APPLICATION"]->SetTitle(GetMessage("STPOL_TITLE"));

	if ($GLOBALS["USER"]->IsAuthorized()):
//*******************************************************

$PATH_TO_DETAIL = Trim($PATH_TO_DETAIL);
if (strlen($PATH_TO_DETAIL) <= 0)
	$PATH_TO_DETAIL = $GLOBALS["PATH_TO_DETAIL"];
if (strlen($PATH_TO_DETAIL) <= 0)
	$PATH_TO_DETAIL = "order_detail.php";

$PATH_TO_COPY = Trim($PATH_TO_COPY);
if (strlen($PATH_TO_COPY) <= 0)
	$PATH_TO_COPY = $GLOBALS["PATH_TO_COPY"];
if (strlen($PATH_TO_COPY) <= 0)
	$PATH_TO_COPY = "index.php";

$PATH_TO_CANCEL = Trim($PATH_TO_CANCEL);
if (strlen($PATH_TO_CANCEL) <= 0)
	$PATH_TO_CANCEL = $GLOBALS["PATH_TO_CANCEL"];
if (strlen($PATH_TO_CANCEL) <= 0)
	$PATH_TO_CANCEL = "order_cancel.php";

$PATH_TO_BASKET = Trim($PATH_TO_BASKET);
if (strlen($PATH_TO_BASKET) <= 0)
	$PATH_TO_BASKET = $GLOBALS["PATH_TO_BASKET"];
if (strlen($PATH_TO_BASKET) <= 0)
	$PATH_TO_BASKET = "basket.php";


$ID = IntVal($_REQUEST["ID"]);
$showType = (($_REQUEST["SHOW_TYPE"] == "history") ? "history" : "current");

$errorMessage = "";

if ($ID > 0 && $_REQUEST["ORDER_COPY"] == "Y")
{
	$dbOrder = CSaleOrder::GetList(
			array("ID" => "DESC"),
			array(
					"ID" => $ID,
					"USER_ID" => IntVal($GLOBALS["USER"]->GetID())
				)
		);
	if ($arOrder = $dbOrder->Fetch())
	{
		$dbBasket = CSaleBasket::GetList(
				array("NAME" => "ASC"),
				array("ORDER_ID" => $arOrder["ID"])
			);
		while ($arBasket = $dbBasket->Fetch())
		{
			UnSet($arFields);
			$arFields = array(
					"PRODUCT_ID" => $arBasket["PRODUCT_ID"],
					"PRODUCT_PRICE_ID" => $arBasket["PRODUCT_PRICE_ID"],
					"PRICE" => $arBasket["PRICE"],
					"CURRENCY" => $arBasket["CURRENCY"],
					"WEIGHT" => $arBasket["WEIGHT"],
					"QUANTITY" => $arBasket["QUANTITY"],
					"LID" => $arBasket["LID"],
					"DELAY" => "N",
					"CAN_BUY" => "Y",
					"NAME" => $arBasket["NAME"],
					"CALLBACK_FUNC" => $arBasket["CALLBACK_FUNC"],
					"MODULE" => $arBasket["MODULE"],
					"NOTES" => $arBasket["NOTES"],
					"ORDER_CALLBACK_FUNC" => $arBasket["ORDER_CALLBACK_FUNC"],
					"DETAIL_PAGE_URL" => $arBasket["DETAIL_PAGE_URL"],
					"CANCEL_CALLBACK_FUNC" => $arBasket["CANCEL_CALLBACK_FUNC"],
					"PAY_CALLBACK_FUNC" => $arBasket["PAY_CALLBACK_FUNC"],
					"CATALOG_XML_ID" => $arBasket["CATALOG_XML_ID"],
					"PRODUCT_XML_ID" => $arBasket["PRODUCT_XML_ID"]
				);
			CSaleBasket::Add($arFields);
		}
		LocalRedirect($PATH_TO_BASKET);
	}
}

?>
<table border="0" cellspacing="0" cellpadding="5">
	<tr>
		<td valign="top" width="60%">
			<font class="tablebodytext">
			<?
			$bNoOrders = True;
			if ($showType == "history")
			{
				?><a href="<?= htmlspecialchars($PATH_TO_COPY) ?>?SHOW_TYPE=current"><?echo GetMessage("STPOL_CUR_ORDERS")?></a><?
			}
			else
			{
				?><a href="<?= htmlspecialchars($PATH_TO_COPY) ?>?SHOW_TYPE=history"><?echo GetMessage("STPOL_ORDERS_HISTORY")?></a><?
			}
			echo "<br><br>";

			if (strlen($errorMessage) > 0)
			{
				echo ShowError($errorMessage);
				echo "<br><br>";
			}
			echo "</font>";

			$arFilter = array("LID" => LANGUAGE_ID);
			if ($showType == "history")
				$arFilter["ID"] = "F";
			else
				$arFilter["!ID"] = "F";

			$dbStatus = CSaleStatus::GetList(
					array("SORT" => "ASC"),
					$arFilter,
					false,
					false,
					array("ID", "NAME", "DESCRIPTION")
				);
			while ($arStatus = $dbStatus->Fetch())
			{
				$dbOrderList = CSaleOrder::GetList(
						array("ID" => "DESC"),
						array(
								"USER_ID" => IntVal($GLOBALS["USER"]->GetID()),
								"STATUS_ID" => $arStatus["ID"]
							),
						false,
						false,
						array("ID", "DATE_INSERT", "PRICE", "CURRENCY", "DATE_STATUS", "CANCELED", "PAYED", "STATUS_ID")
					);

				if ($arOrderList = $dbOrderList->Fetch())
				{
					$bNoOrders = False;
					?>
					<font class="tabletitletext"><b><?echo GetMessage("STPOL_STATUS")?> "<?= $arStatus["NAME"] ?>"</b></font><br>
					<font class="tablebodytext"><small><?= $arStatus["DESCRIPTION"] ?></small></font><br><br>
					<?
					do
					{
						?>
						<table border="0" width="100%" cellspacing="0" cellpadding="1"><tr><td class="tablehead">
						<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<tr>
								<td bgcolor="#FFFFFF">
									<font class="tableheadtext">
										<b>
										<?echo GetMessage("STPOL_ORDER_NO")?>
										<a title="<?echo GetMessage("STPOL_DETAIL_ALT")?>" href="<?= htmlspecialchars($PATH_TO_DETAIL) ?>?ID=<?= $arOrderList["ID"] ?>&SHOW_TYPE=<?= $showType ?>"><?= $arOrderList["ID"] ?></a>
										<?echo GetMessage("STPOL_FROM")?>
										<?= $arOrderList["DATE_INSERT"]; ?>
										</b>
										<?
										if ($arOrderList["CANCELED"] == "Y")
											echo GetMessage("STPOL_CANCELED");
										?>
										<br>
										<b>
										<?echo GetMessage("STPOL_SUM")?>
										<?= SaleFormatCurrency($arOrderList["PRICE"], $arOrderList["CURRENCY"]) ?>
										</b>
										<?echo GetMessage("STPOL_STATUS_FROM")?>
										<?= $arOrderList["DATE_STATUS"]; ?>
									</font>
								</td>
							</tr>
							<tr>
								<td valign="top" bgcolor="#FFFFFF">
									<table width="100%" border="0" cellspacing="0" cellpadding="0">
										<tr>
											<td width="0%">&nbsp;&nbsp;&nbsp;&nbsp;</td>
											<td width="100%">
												<font class="tablebodytext">
												<b><?echo GetMessage("STPOL_CONTENT")?></b>
												</font>
											</td>
											<td width="0%">&nbsp;</td>
										</tr>

										<?
										$dbBasket = CSaleBasket::GetList(
												array("NAME" => "ASC"),
												array("ORDER_ID" => $arOrderList["ID"]),
												false,
												false,
												array("ID", "NAME", "DETAIL_PAGE_URL", "QUANTITY")
											);
										while ($arBasket = $dbBasket->Fetch())
										{
											?>
											<tr>
												<td width="0%">&nbsp;&nbsp;&nbsp;&nbsp;</td>
												<td width="100%">
													<font class="tablebodytext">
													<?
													if (strlen($arBasket["DETAIL_PAGE_URL"]) > 0)
														echo "<a href=\"".$arBasket["DETAIL_PAGE_URL"]."\">";
													echo $arBasket["NAME"];
													if (strlen($arBasket["DETAIL_PAGE_URL"]) > 0)
														echo "</a>";
													?>
													</font>
												</td>
												<td width="0%" nowrap>
													<font class="tablebodytext">
													<?= $arBasket["QUANTITY"] ?> <?echo GetMessage("STPOL_SHT")?>
													</font>
												</td>
											</tr>
											<?
										}
										?>
									</table>
								</td>
							</tr>
							<tr>
								<td  bgcolor="#FFFFFF" align="right">
									<font class="tablebodytext">
									<a title="<?= GetMessage("STPOL_DETAIL_ALT") ?>" href="<?= htmlspecialchars($PATH_TO_DETAIL) ?>?ID=<?= $arOrderList["ID"] ?>&SHOW_TYPE=<?= $showType ?>"><?= GetMessage("STPOL_DETAILS") ?></a>
									&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									<a title="<?= GetMessage("STPOL_REORDER") ?>" href="<?= htmlspecialchars($PATH_TO_COPY) ?>?ID=<?= $arOrderList["ID"] ?>&ORDER_COPY=Y&SHOW_TYPE=<?= $showType ?>"><?= GetMessage("STPOL_REORDER1") ?></a>
									<?if ($arOrderList["CANCELED"] != "Y" && $arOrderList["STATUS_ID"] != "F" && $arOrderList["PAYED"] != "Y"):?>
										&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										<a title="<?= GetMessage("STPOL_CANCEL") ?>" href="<?= htmlspecialchars($PATH_TO_CANCEL) ?>?ID=<?= $arOrderList["ID"] ?>&SHOW_TYPE=<?= $showType ?>"><?= GetMessage("STPOL_CANCEL") ?></a>
									<?endif;?>
									</font>
								</td>
							</tr>
						</table>
						</td></tr></table>
						<br>
						<?
					}
					while ($arOrderList = $dbOrderList->Fetch());
					?>
					<br>
					<?
				}
			}

			if ($bNoOrders)
			{
				?>
				<center><br><br>
				<font class="tablebodytext">
				<?echo GetMessage("STPOL_NO_ORDERS")?>
				</font>
				</center>
				<?
			}
			?>
		</td>
		<td valign="top" width="5%" rowspan="3">&nbsp;</td>
		<td valign="top" width="35%" rowspan="3">
			<font class="tablebodytext">
			<?echo GetMessage("STPOL_HINT")?><br><br>
			<?echo GetMessage("STPOL_HINT1")?>
			</font>
		</td>
	</tr>
</table>

<?
//*******************************************************
	else:
		?>
		<font class="text"><b><?= GetMessage("STPOL_NEED_AUTH") ?></b></font>
		<?
	endif;
else:
	?>
	<font class="text"><b><?= GetMessage("STPOL_NO_SALE") ?></b></font>
	<?
endif;
?>