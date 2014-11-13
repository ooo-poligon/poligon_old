<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
IncludeTemplateLangFile(__FILE__);

if (CModule::IncludeModule("sale")):
//*******************************************************

$ID = IntVal($_REQUEST["ID"]);

$APPLICATION->SetTitle(GetMessage("SPOD_TITLE").$ID);

$PATH_TO_LIST = $GLOBALS["PATH_TO_LIST"];
if (strlen($PATH_TO_LIST)<=0) $PATH_TO_LIST = "index.php";

$PATH_TO_CANCEL = $GLOBALS["PATH_TO_CANCEL"];
if (strlen($PATH_TO_CANCEL)<=0) $PATH_TO_CANCEL = "order_cancel.php";

$PATH_TO_PAYMENT = $GLOBALS["PATH_TO_PAYMENT"];
if (strlen($PATH_TO_PAYMENT)<=0) $PATH_TO_PAYMENT = "payment.php";

if ($ID<=0) LocalRedirect($PATH_TO_LIST."?".GetFilterParams("filter_", false));

$strError = "";

$db_order = CSaleOrder::GetList(Array("ID"=>"DESC"), Array("ID"=>$ID, "USER_ID"=>IntVal($GLOBALS["USER"]->GetID())));
if ($ar_order = $db_order->Fetch()):
	?><font class="text"><a name="tb"></a>
	<a href="<?echo $PATH_TO_LIST ?>?<?echo GetFilterParams("filter_", false)?>" class="navchain"><?=GetMessage("SALE_RECORDS_LIST")?></a>
	<br><br></font>
	<table border="0" cellspacing="0" cellpadding="1" width="100%" class="tableborder">
		<tr>
			<td>
				<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<tr>
						<td valign="middle" colspan="2" align="center" class="tablebody">
							<table width="100%" border="0" cellpadding="3" cellspacing="0">
							<tr>
								<td class="tablehead"><font class="tabletitletext"><b><?echo GetMessage("P_ORDER_ID")?></b></font></td>
							</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td valign="top" align="right" class="tablebody" width="40%"><font class="tableheadtext"><?echo GetMessage("SALE_ORDER_CODE")?>:</font></td>
						<td valign="top" align="left" class="tablebody" width="60%"><font class="tablebodytext"><?echo $ID ?></font></td>
					</tr>
					<tr>
						<td valign="top" align="right" class="tablebody" width="40%"><font class="tableheadtext"><?echo GetMessage("P_ORDER_DATE")?>:</font></td>
						<td valign="top" align="left" class="tablebody" width="60%"><font class="tablebodytext"><?echo $ar_order["DATE_INSERT_FORMAT"] ?></font></td>
					</tr>
					<tr>
						<td valign="top" align="right" class="tablebody" width="40%"><font class="tableheadtext"><?echo GetMessage("P_ORDER_STATUS")?>:</font></td>
						<td valign="top" align="left" class="tablebody" width="60%"><font class="tablebodytext"><? 
						$arStatus = CSaleStatus::GetByID($ar_order["STATUS_ID"]);
							echo $arStatus["NAME"];
						?>&nbsp;&nbsp;</font><font class="tablebodytext">(<?
							echo GetMessage("P_ORDER_STATUS_DATE")?> <?echo $ar_order["DATE_STATUS_FORMAT"];
						?>)</font></td>
					</tr>
					<tr>
						<td valign="top" align="right" class="tablebody" width="40%"><font class="tableheadtext"><?echo GetMessage("P_ORDER_PRICE")?>:</font></td>
						<td valign="top" align="left" class="tablebody" width="60%"><font class="tablebodytext"><?echo SaleFormatCurrency($ar_order["PRICE"], $ar_order["CURRENCY"]) ?></font></td>
					</tr>
					<tr>
						<td valign="top" align="right" class="tablebody" width="40%"><font class="tableheadtext"><?echo GetMessage("P_ORDER_CANCELED")?>:</font></td>
						<td valign="top" align="left" class="tablebody" width="60%"><font class="tablebodytext"><?
							echo (($ar_order["CANCELED"]=="Y") ? GetMessage("SALE_YES") : GetMessage("SALE_NO") );
							if ($ar_order["CANCELED"]=="Y")
							{
								?>
								&nbsp;&nbsp;(<?echo GetMessage("P_ORDER_DATE_CANCELED")." ".$ar_order["DATE_CANCELED_FORMAT"]; ?>)<?
								if (strlen($ar_order["REASON_CANCELED"])>0)
								{
									echo "<br>";
									echo $ar_order["REASON_CANCELED"];
								}
							}
							elseif ($ar_order["CANCELED"]!="Y" && $ar_order["STATUS_ID"]!="F" && $ar_order["PAYED"]!="Y")
							{
								?>&nbsp;&nbsp;&nbsp;&nbsp;
								<span class="tablehead">
								<a href="<?echo $PATH_TO_CANCEL ?>?ID=<?echo $ID ?>&CANCEL=Y&lang=<?echo LANG ?>&<?echo GetFilterParams("filter_", false)?>"><?echo GetMessage("SALE_CANCEL_ORDER")?> &gt;&gt;</a>
								</span><?
							}
							?></font></td>
					</tr>
					<tr>
						<td valign="top" align="right" class="tablebody" colspan="2"><img src="/bitrix/images/1.gif" width="1" height="8" title=""></td>
					</tr>

					<tr>
						<td valign="middle" colspan="2" align="center" class="tablebody">
						<table width="100%" border="0" cellpadding="3" cellspacing="0">
							<tr>
								<td class="tablehead"><font class="tabletitletext"><b><?echo GetMessage("P_ORDER_USER")?></b></font></td>
							</tr>
						</table>
						</td>
					</tr>
					<tr>
						<td valign="top" align="right" class="tablebody" width="40%"><font class="tableheadtext"><?echo GetMessage("P_ORDER_PERS_TYPE") ?>:</font></td>
						<td valign="top" align="left" class="tablebody" width="60%"><font class="tablebodytext"><?
							$arPersType = CSalePersonType::GetByID($ar_order["PERSON_TYPE_ID"], SITE_ID);
							echo $arPersType["NAME"];
						?></font></td>
					</tr>
					<tr>
						<td valign="top" align="right" class="tablebody" colspan="2"><img src="/bitrix/images/1.gif" width="1" height="8" title=""></td>
					</tr>
					<?
					$db_props = CSaleOrderPropsValue::GetOrderProps($ID);
					$iGroup = -1;
					while ($arProps = $db_props->Fetch())
					{
						if ($iGroup!=IntVal($arProps["PROPS_GROUP_ID"]))
						{
							?>
							<tr>
								<td class="tablebody" colspan="2" align="center"><b><font class="tablebodytext"><?echo $arProps["GROUP_NAME"];?></font></b></td>
							</tr>
							<?
							$iGroup = IntVal($arProps["PROPS_GROUP_ID"]);
						}
						?>
						<tr valign="middle">
							<td class="tablebody" width="40%" align="right" valign="top"><font class="tableheadtext"><?echo $arProps["NAME"] ?>:</font></td>
							<td class="tablebody" width="60%" align="left"><font class="tablebodytext"><?
								if ($arProps["TYPE"]=="CHECKBOX")
								{
									if ($arProps["VALUE"]=="Y") echo GetMessage("SALE_YES");
									else echo GetMessage("SALE_NO");
								}
								elseif ($arProps["TYPE"]=="TEXT" || $arProps["TYPE"]=="TEXTAREA")
								{
									echo htmlspecialchars($arProps["VALUE"]);
								}
								elseif ($arProps["TYPE"]=="SELECT" || $arProps["TYPE"]=="RADIO")
								{
									$arVal = CSaleOrderPropsVariant::GetByValue($arProps["ORDER_PROPS_ID"], $arProps["VALUE"]);
									echo htmlspecialchars($arVal["NAME"]);
								}
								elseif ($arProps["TYPE"]=="MULTISELECT")
								{
									$curVal = split(",", $arProps["VALUE"]);
									for ($i = 0; $i<count($curVal); $i++)
									{
										$arVal = CSaleOrderPropsVariant::GetByValue($arProps["ORDER_PROPS_ID"], $curVal[$i]);
										if ($i>0) echo ", ";
										echo htmlspecialchars($arVal["NAME"]);
									}
								}
								elseif ($arProps["TYPE"]=="LOCATION")
								{
									$arVal = CSaleLocation::GetByID($arProps["VALUE"], LANGUAGE_ID);
									echo htmlspecialchars($arVal["COUNTRY_NAME"]." - ".$arVal["CITY_NAME"]);
								}
							?></font></td>
						</tr>
						<?
					}
					?>
					<?if ($iGroup>=0):?>
					<tr>
						<td valign="top" align="right" class="tablebody" colspan="2"><img src="/bitrix/images/1.gif" width="1" height="8" title=""></td>
					</tr>
					<?endif;?>
					<tr>
						<td valign="top" align="right" class="tablebody" width="40%"><font class="tableheadtext"><?echo GetMessage("P_ORDER_USER_COMMENT") ?>:</font></td>
						<td valign="top" align="left" class="tablebody" width="60%"><font class="tablebodytext"><?echo $ar_order["USER_DESCRIPTION"] ?></font></td>
					</tr>
					<tr>
						<td valign="top" align="right" class="tablebody" colspan="2"><img src="/bitrix/images/1.gif" width="1" height="8" title=""></td>
					</tr>
					<tr>
						<td valign="middle" colspan="2" align="center" class="tablebody">
						<table width="100%" border="0" cellpadding="3" cellspacing="0">
							<tr>
								<td class="tablehead"><font class="tabletitletext"><b><?echo GetMessage("P_ORDER_PAYMENT")?></b></font></td>
							</tr>
						</table>
						</td>
					</tr>
					<tr>
						<td valign="top" align="right" class="tablebody" width="40%"><font class="tableheadtext"><?echo GetMessage("P_ORDER_PAY_SYSTEM") ?>:</font></td>
						<td valign="top" align="left" class="tablebody" width="60%"><font class="tablebodytext"><?
							$arPaySys = CSalePaySystem::GetByID($ar_order["PAY_SYSTEM_ID"], $ar_order["PERSON_TYPE_ID"]);
							echo $arPaySys["PSA_NAME"];
						?></font></td>
					</tr>
					<tr>
						<td valign="top" align="right" class="tablebody" width="40%"><font class="tableheadtext"><?echo GetMessage("P_ORDER_PAYED") ?>:</font></td>
						<td valign="top" align="left" class="tablebody" width="60%"><font class="tablebodytext"><?
							echo (($ar_order["PAYED"]=="Y") ? GetMessage("SALE_YES") : GetMessage("SALE_NO") );
							if ($ar_order["PAYED"]=="Y")
								echo "&nbsp;&nbsp;(".GetMessage("P_ORDER_DATE_PAYED")." ".$ar_order["DATE_PAYED_FORMAT"].")";
						?></font></td>
					</tr>
					<?if (strlen($arPaySys["PSA_ACTION_FILE"])>0 && $ar_order["PAYED"]!="Y" && $ar_order["CANCELED"]!="Y"):?>
					<tr>
						<td valign="top" align="center" class="tablebody" colspan="2">
							<table border="0" cellspacing="0" cellpadding="1" class="tableborder"><tr><td>
							<table border="0" cellspacing="0" cellpadding="3" width="100%"><tr><td class="tablebody"><font class="tablebodytext"><?
							if ($arPaySys["PSA_NEW_WINDOW"] <> "Y"):
								$ORDER_ID = $ID;
								$PAYER_NAME = $GLOBALS["USER"]->GetFullName();
								$arOrder = array(
									"DATE_UPDATE_FORMAT" => $ar_order["DATE_INSERT_FORMAT"],
									"PRICE" => $ar_order["PRICE"],
									"CURRENCY" => $ar_order["CURRENCY"]
									);
								include($_SERVER["DOCUMENT_ROOT"].$arPaySys["PSA_ACTION_FILE"]);
							else:
								?><a href="<?echo $PATH_TO_PAYMENT ?>?ORDER_ID=<?echo $ID?>" target="_blank"><?echo GetMessage("SALE_REPEAT_PAY")?></a><?
							endif;
							?></font>
							</td></tr></table>
							</td></tr></table>
						</td>
					</tr>
					<?endif;?>
					<tr>
						<td valign="top" align="right" class="tablebody" width="40%"><font class="tableheadtext"><?echo GetMessage("P_ORDER_DELIVERY") ?>:</font></td>
						<td valign="top" align="left" class="tablebody" width="60%"><font class="tablebodytext"><?
							$arDelivery = CSaleDelivery::GetByID($ar_order["DELIVERY_ID"]);
							echo $arDelivery["NAME"];
						?></font></td>
					</tr>
					<tr>
						<td valign="top" align="right" class="tablebody" colspan="2"><img src="/bitrix/images/1.gif" width="1" height="8" title=""></td>
					</tr>
					<tr>
						<td valign="middle" colspan="2" align="center" class="tablebody">
							<table width="100%" border="0" cellpadding="3" cellspacing="0">
							<tr>
								<td class="tablehead"><font class="tabletitletext"><b><?echo GetMessage("P_ORDER_BASKET")?></b></font></td>
							</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td valign="top" class="tablebody" colspan="2">
						<?
						$db_basket = CSaleBasket::GetList(($b="NAME"), ($o="ASC"), array("ORDER_ID"=>$ID));
						?>
						<table cellpadding="0" cellspacing="0" border="0" width="100%" class="tableborder"><tr><td>
						<table cellpadding="2" cellspacing="1" border="0" width="100%">
							<tr>
								<td class="tablehead"><font class="tableheadtext"><?echo GetMessage("SALE_CONTENT_NAME")?></font></td>
								<td class="tablehead" width="20%"><font class="tableheadtext"><?echo GetMessage("SALE_CONTENT_QUANTITY")?></font></td>
							</tr>
							<?
							while ($arBasket = $db_basket->Fetch())
							{
								?>
								<tr>
									<td class="tablebody" valign="top"><font class="tablebodytext"><?
										if (strlen($arBasket["DETAIL_PAGE_URL"])>0):?><a href="<?echo $arBasket["DETAIL_PAGE_URL"] ?>"><?endif;
									?><b><?echo $arBasket["NAME"]?></b><?
									if (strlen($arBasket["DETAIL_PAGE_URL"])>0):?></a><?endif;
									?></font></td>
									<td class="tablebody" valign="top"><font class="tablebodytext"><?echo $arBasket["QUANTITY"]?></font></td>
								</tr>
								<?
							}
							?>
						</table>
						</td></tr></table>
						</td>
					</tr>
					<tr>
						<td valign="top" align="right" class="tablebody" colspan="2"><img src="/bitrix/images/1.gif" width="1" height="8" title=""></td>
					</tr>

				</table>
			</td>
		</tr>
	</table>

<?
else:
	?>
	<font class="text"><?echo GetMessage("SALE_NO_ORDER_F")?></font>
	<?
endif;


//*******************************************************
else:
	?>
	<font class="text"><b><?echo GetMessage("SALE_NO_MODULE_X")?></b></font>
	<?
endif;
?>