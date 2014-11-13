<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
IncludeTemplateLangFile(__FILE__);
if (CModule::IncludeModule("sale")):
//*******************************************************

$GLOBALS["APPLICATION"]->SetTitle(GetMessage("SPO_ORDERS_LIST"));

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
if ($ID>0 && $_REQUEST["ORDER_COPY"]=="Y")
{
	$db_order = CSaleOrder::GetList(Array("ID"=>"DESC"), Array("ID"=>$ID, "USER_ID"=>IntVal($USER->GetID())));
	if ($ar_order = $db_order->Fetch())
	{
		$db_basket = CSaleBasket::GetList(($b="NAME"), ($o="ASC"), array("ORDER_ID"=>$ar_order["ID"]));
		while ($arBasket = $db_basket->Fetch())
		{
			UnSet($arFields);
			$arFields = array(
				"PRODUCT_ID"			=> $arBasket["PRODUCT_ID"],
				"PRODUCT_PRICE_ID"	=> $arBasket["PRODUCT_PRICE_ID"],
				"PRICE"					=> $arBasket["PRICE"],
				"CURRENCY"				=> $arBasket["CURRENCY"],
				"WEIGHT"					=> $arBasket["WEIGHT"],
				"QUANTITY"				=> $arBasket["QUANTITY"],
				"LID"						=> $arBasket["LID"],
				"DELAY"					=> "N",
				"CAN_BUY"				=> "Y",
				"NAME"					=> $arBasket["NAME"],
				"CALLBACK_FUNC"		=> $arBasket["CALLBACK_FUNC"],
				"MODULE"					=> $arBasket["MODULE"],
				"NOTES"					=> $arBasket["NOTES"],
				"ORDER_CALLBACK_FUNC"=> $arBasket["ORDER_CALLBACK_FUNC"],
				"DETAIL_PAGE_URL"		=> $arBasket["DETAIL_PAGE_URL"],
				"CATALOG_XML_ID" => $arBasket["CATALOG_XML_ID"],
				"PRODUCT_XML_ID" => $arBasket["PRODUCT_XML_ID"]
				);
			CSaleBasket::Add($arFields);
		}
		LocalRedirect($PATH_TO_BASKET);
	}
}


if (strlen($GLOBALS["del_filter"])>0)
	DelFilter(Array("filter_id", "filter_date_from", "filter_date_to", "filter_status", "filter_payed", "filter_canceled", "filter_history"));
else
	InitFilter(Array("filter_id", "filter_date_from", "filter_date_to", "filter_status", "filter_payed", "filter_canceled", "filter_history"));
?>
<table border="0" cellspacing="1" cellpadding="0" class="tableborder" width="0%">
	<form method="GET" action="<?= $PATH_TO_COPY ?>" name="bfilter">
	<tr valign="top">
		<td>
			<table border="0" cellspacing="0" cellpadding="3" width="100%">
				<tr>
					<td valign="middle" colspan="2" align="center" nowrap class="tablehead"><font class="tableheadtext"><?echo GetMessage("SALE_F_FILTER")?></font></td>
				</tr>
				<tr valign="middle">
					<td align="right" class="tablebody" nowrap><font class="tablebodytext"><?echo GetMessage("SALE_F_ID");?>:</font></td>
					<td align="left" class="tablebody" nowrap><input type="text" name="filter_id" value="<?echo (IntVal($GLOBALS["filter_id"])>0)?IntVal($GLOBALS["filter_id"]):""?>" size="10" class="inputtext"></td>
				</tr>
				<tr valign="middle">
					<td align="right" class="tablebody" nowrap><font class="tablebodytext"><?echo GetMessage("SALE_F_DATE");?>:</font></td>
					<td align="left" class="tablebody" nowrap><?echo CalendarPeriod("filter_date_from", $GLOBALS["filter_date_from"], "filter_date_to", $GLOBALS["filter_date_to"], "bfilter", "N", "", "class='inputtext'")?></td>
				</tr>
				<tr valign="middle">
					<td align="right" class="tablebody" nowrap><font class="tablebodytext"><?echo GetMessage("SALE_F_STATUS")?>:</font></td>
					<td align="left" class="tablebody" nowrap><select name="filter_status" class="inputselect">
							<option value=""><?echo GetMessage("SALE_F_ALL")?></option>
							<?
							$db_res = CSaleStatus::GetList(($b="SORT"), ($o="ASC"), LANGUAGE_ID);
							while ($ar_res = $db_res->Fetch())
							{
								if ($ar_res["ID"]!="F")
								{
									?><option value="<?echo $ar_res["ID"]?>"<?if ($GLOBALS["filter_status"]==$ar_res["ID"]) echo " selected"?>>[<?echo $ar_res["ID"] ?>] <?echo $ar_res["NAME"]?></option><?
								}
							}
							?>
					</select></td>
				</tr>
				<tr valign="middle">
					<td align="right" class="tablebody" nowrap><font class="tablebodytext"><?echo GetMessage("SALE_F_PAYED")?>:</font></td>
					<td align="left" class="tablebody" nowrap><select name="filter_payed" class="inputselect">
							<option value=""><?echo GetMessage("SALE_F_ALL")?></option>
							<option value="Y"<?if ($GLOBALS["filter_payed"]=="Y") echo " selected"?>><?echo GetMessage("SALE_YES")?></option>
							<option value="N"<?if ($GLOBALS["filter_payed"]=="N") echo " selected"?>><?echo GetMessage("SALE_NO")?></option>
					</select></td>
				</tr>
				<tr valign="middle">
					<td align="right" class="tablebody" nowrap><font class="tablebodytext"><?echo GetMessage("SALE_F_CANCELED")?>:</font></td>
					<td align="left" class="tablebody" nowrap>
						<select name="filter_canceled" class="inputselect">
							<option value=""><?echo GetMessage("SALE_F_ALL")?></option>
							<option value="Y"<?if ($GLOBALS["filter_canceled"]=="Y") echo " selected"?>><?echo GetMessage("SALE_YES")?></option>
							<option value="N"<?if ($GLOBALS["filter_canceled"]=="N") echo " selected"?>><?echo GetMessage("SALE_NO")?></option>
						</select>
					</td>
				</tr>
				<tr valign="middle">
					<td align="right" class="tablebody" nowrap><font class="tablebodytext"><?echo GetMessage("SALE_F_HISTORY")?>:</font></td>
					<td align="left" class="tablebody" nowrap>
						<select name="filter_history" class="inputselect">
							<option value="N"<?if ($GLOBALS["filter_history"]=="N") echo " selected"?>><?echo GetMessage("SALE_NO")?></option>
							<option value="Y"<?if ($GLOBALS["filter_history"]=="Y") echo " selected"?>><?echo GetMessage("SALE_YES")?></option>
						</select>
					</td>
				</tr>
				<tr>
					<td valign="center" colspan="2" align="center" nowrap class="tablehead">
						<input type="submit" name="filter" value="<?echo GetMessage("SALE_F_SUBMIT")?>" class="inputbodybutton">&nbsp;&nbsp;
						<input type="submit" name="del_filter" value="<?echo GetMessage("SALE_F_DEL")?>" class="inputbodybutton">
					</td>
				</tr>
			</table>
		</td>
	</tr>
	</form>
</table>
<br>

<?
echo ShowError($strWarning);

$arFilter = Array();
$arFilter["USER_ID"] = IntVal($USER->GetID());
if (IntVal($GLOBALS["filter_id"])>0) $arFilter["ID"] = IntVal($GLOBALS["filter_id"]);
if (strlen($GLOBALS["filter_date_from"])>0) $arFilter["DATE_FROM"] = Trim($GLOBALS["filter_date_from"]);
if (strlen($GLOBALS["filter_date_to"])>0) $arFilter["DATE_TO"] = Trim($GLOBALS["filter_date_to"]);
if (strlen($GLOBALS["filter_status"])>0) $arFilter["STATUS_ID"] = Trim($GLOBALS["filter_status"]);
if (strlen($GLOBALS["filter_payed"])>0) $arFilter["PAYED"] = Trim($GLOBALS["filter_payed"]);
if (strlen($GLOBALS["filter_canceled"])>0) $arFilter["CANCELED"] = Trim($GLOBALS["filter_canceled"]);
if ($GLOBALS["filter_history"]!="Y") $arFilter["!STATUS_ID"] = "F";

//InitSorting($APPLICATION->GetCurPage());

$db_sales = CSaleOrder::GetList(array($_REQUEST["by"] => $_REQUEST["order"]), $arFilter);

$db_sales->NavStart(20);
?>
<p><?echo $db_sales->NavPrint(GetMessage("SALE_PRLIST"))?></p>
<form method="POST" action="<?= $PATH_TO_COPY ?>">
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<table border="0" cellspacing="0" cellpadding="0" width="100%"  class="tableborder">
	<tr>
		<td>
			<table border="0" cellspacing="1" cellpadding="2" width="100%">
				<tr>
					<td valign="top" align="center" class="tablehead" nowrap><font class="tableheadtext"><?echo GetMessage("P_ID")?><br><?echo SortingEx("ID")?></font></td>
					<td valign="top" align="center" class="tablehead" nowrap><font class="tableheadtext"><?echo GetMessage("P_PRICE")?><br><?echo SortingEx("PRICE")?></font></td>
					<td valign="top" align="center" class="tablehead" nowrap><font class="tableheadtext"><?echo GetMessage("P_STATUS")?><br><?echo SortingEx("STATUS_ID")?></font></td>
					<td valign="top" align="center" class="tablehead" nowrap><font class="tableheadtext"><?echo GetMessage("P_BASKET")?><br></font></td>
					<td valign="top" align="center" class="tablehead" nowrap><font class="tableheadtext"><?echo GetMessage("P_PAYED")?><br><?echo SortingEx("PAYED")?></font></td>
					<td valign="top" align="center" class="tablehead" nowrap><font class="tableheadtext"><?echo GetMessage("P_CANCELED")?><br><?echo SortingEx("CANCELED")?></font></td>
					<td valign="top" align="center" class="tablehead"><font class="tableheadtext"><?echo GetMessage("P_PAY_SYS")?><br></font></td>
					<td valign="top" align="center" class="tablehead" nowrap><font class="tableheadtext"><?echo GetMessage("SALE_ACTION")?></font></td>
				</tr>
			<?
			while ($ar_sales = $db_sales->Fetch()):
				$arStatus = CSaleStatus::GetByID($ar_sales["STATUS_ID"]);
				$arPaySys = CSalePaySystem::GetByID($ar_sales["PAY_SYSTEM_ID"]);
				$arDeliv = CSaleDelivery::GetByID($ar_sales["DELIVERY_ID"]);
				?>
				<tr valign="top">
					<td align="center" class="tablebody" nowrap><font class="tablebodytext"><b><?echo $ar_sales["ID"]?></b><br><?echo GetMessage("SALE_FROM")?> <?echo $ar_sales["DATE_INSERT_FORMAT"];?></font></td>
					<td class="tablebody" align="right"><font class="tablebodytext"><?echo SaleFormatCurrency($ar_sales["PRICE"], $ar_sales["CURRENCY"])?></font></td>
					<td align="center" class="tablebody"><font class="tablebodytext"><?echo $arStatus["NAME"]?><br><?echo $ar_sales["DATE_STATUS_FORMAT"];?></font></td>
					<td align="center" class="tablebody"><font class="tablebodytext"><?
						$db_basket = CSaleBasket::GetList(($b="NAME"), ($o="ASC"), array("ORDER_ID"=>$ar_sales["ID"]));
						$bNeedComa = False;
						while ($arBasket = $db_basket->Fetch())
						{
							if ($bNeedComa) echo ", ";
							if (strlen($arBasket["DETAIL_PAGE_URL"])>0) echo "<a href=\"".$arBasket["DETAIL_PAGE_URL"]."\">";
							echo $arBasket["NAME"];
							if (strlen($arBasket["DETAIL_PAGE_URL"])>0) echo "</a>";
							$bNeedComa = True;
						}
					?></td>
					<td align="center" class="tablebody"><font class="tablebodytext"><?echo ( ($ar_sales["PAYED"]=="Y") ? GetMessage("SALE_YES") : GetMessage("SALE_NO") ) ?></font></td>
					<td align="center" class="tablebody"><font class="tablebodytext"><?echo ( ($ar_sales["CANCELED"]=="Y") ? GetMessage("SALE_YES") : GetMessage("SALE_NO") ) ?></font></td>
					<td align="left" class="tablebody"><font class="tablebodytext"><?echo $arPaySys["NAME"]?> / <?echo $arDeliv["NAME"]?></font></td>
					<td align="left" class="tablebody"><font class="tablebodytext"><a title="<?echo GetMessage("SALE_DETAIL_DESCR")?>" href="<?echo $PATH_TO_DETAIL ?>?ID=<?echo $ar_sales["ID"]?>&<?echo GetFilterParams("filter_");?>#tb"><?echo GetMessage("SALE_DETAIL")?></a><br>
						<a title="<?echo GetMessage("SALE_COPY_ORDER_DESCR")?>" href="<?echo $PATH_TO_COPY ?>?ID=<?echo $ar_sales["ID"]?>&ORDER_COPY=Y&<?echo GetFilterParams("filter_");?>#tb"><?echo GetMessage("SALE_COPY_ORDER")?></a><br>
						<?if ($ar_sales["CANCELED"]!="Y" && $ar_sales["STATUS_ID"]!="F" && $ar_sales["PAYED"]!="Y"):?>
						<a title="<?echo GetMessage("SALE_DELETE_DESCR")?>" href="<?echo $PATH_TO_CANCEL ?>?ID=<?echo $ar_sales["ID"]?>&<?echo GetFilterParams("filter_");?>#tb"><?echo GetMessage("SALE_DELETE")?></a>
						<?endif;?>
					</font></td>
				</tr>
				<?endwhile;?>
			</table>
		</td>
	</tr>
</table>
<p><?echo $db_sales->NavPrint(GetMessage("SALE_PRLIST"))?></p>
</form>

<?
//*******************************************************
else:
	?>
	<font class="text"><b><?echo GetMessage("SALE_NO_MODULE_X")?></b></font>
	<?
endif;
?>