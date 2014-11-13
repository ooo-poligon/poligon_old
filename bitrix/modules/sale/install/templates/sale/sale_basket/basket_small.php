<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
IncludeTemplateLangFile(__FILE__);
if (CModule::IncludeModule("sale")):
//*******************************************************

$BASKET_PAGE = Trim($BASKET_PAGE);
$ORDER_PAGE = Trim($ORDER_PAGE);

$arItems = GetBasketList();
$bReady = False;
$bDelay = False;
$bNotAvail = False;
for ($i = 0; $i<count($arItems); $i++)
{
	if ($arItems[$i]["DELAY"]=="N" && $arItems[$i]["CAN_BUY"]=="Y")
		$bReady = True;
	elseif ($arItems[$i]["DELAY"]=="Y" && $arItems[$i]["CAN_BUY"]=="Y")
		$bDelay = True;
	elseif ($arItems[$i]["CAN_BUY"]=="N")
		$bNotAvail = True;
}
?>

<?if ($bReady || $bDelay || $bNotAvail):?>

	<table cellpadding="0" cellspacing="0" border="0" width="245">
	<tr>
		<td bgcolor="#3d5682" colspan="4"><img height="1" width="1" src="/bitrix/images/1.gif" border="0"></td>
	</tr>

	<?if ($bReady):?>
		<tr>
			<td align="right" valign="top" bgcolor="#3d5682"><img height="1" width="1" src="/bitrix/images/1.gif" border="0"></td>
			<td width="243" colspan="2" align="center"><div style="margin-bottom: 5px; margin-top: 5px;"><font style="font-family: Arial, Helvetica, sans-serif; font-size:11pt; color:#000000; font-weight:bold;"><?= GetMessage("TSBS_READY") ?></font></div></td>
			<td align="right" valign="top" bgcolor="#3d5682"><img height="1" width="1" src="/bitrix/images/1.gif" border="0"></td>
		</tr>
		<?
		for ($i = 0; $i<count($arItems); $i++)
		{
			if ($arItems[$i]["DELAY"]=="N" && $arItems[$i]["CAN_BUY"]=="Y")
			{
				?>
				<tr>
					<td align="right" valign="top" bgcolor="#3d5682"><IMG height="1" width="1" src="/bitrix/images/1.gif" border="0"></td>
					<td valign="top"><img src="/bitrix/templates/.default/sale/sale_pieces/images/icon_basket_white.gif" width="17" height="17" border="0" alt=""></td>
					<td width="243" valign="top">
						<div style="margin-bottom: 5px;">
						<font class="text">
						<?if (strlen($arItems[$i]["DETAIL_PAGE_URL"])>0):?>
							<a href="<?echo $arItems[$i]["DETAIL_PAGE_URL"] ?>">
						<?endif;?>
						<b><?echo $arItems[$i]["NAME"]?></b>
						<?if (strlen($arItems[$i]["DETAIL_PAGE_URL"])>0):?>
							</a>
						<?endif;?>
						<br>
						<?= GetMessage("TSBS_PRICE") ?>&nbsp;<B><?echo SaleFormatCurrency($arItems[$i]["PRICE"], $arItems[$i]["CURRENCY"]) ?></B><br>
						<?= GetMessage("TSBS_QUANTITY") ?>&nbsp;<?echo $arItems[$i]["QUANTITY"]?>
						</font>
						</div>
					</td>
					<td align="right" valign="top" bgcolor="#3d5682"><IMG height="1" width="1" src="/bitrix/images/1.gif" border="0"></td>
				</tr>
				<?
			}
		}
		?>
		<?if (strlen($BASKET_PAGE)>0):?>
			<tr>
				<td align="right" valign="top" bgcolor="#3d5682"><IMG height="1" width="1" src="../images/empty.gif" border="0"></td>
				<form method="get" action="<?= $BASKET_PAGE ?>">
					<td width="243" colspan="2" align="center">
						<div style="margin-bottom: 5px;">
						<input type="submit" value="<?= GetMessage("TSBS_2BASKET") ?>" class="inputbutton">
						</div>
					</td>
				</form>
				<td align="right" valign="top" bgcolor="#3d5682"><IMG height="1" width="1" src="../images/empty.gif" border="0"></td>
			</tr>
		<?endif;?>
		<?if (strlen($ORDER_PAGE)>0):?>
			<tr>
				<td align="right" valign="top" bgcolor="#3d5682"><IMG height="1" width="1" src="../images/empty.gif" border="0"></td>
				<form method="get" action="<?= $ORDER_PAGE ?>">
					<td width="243" colspan="2" align="center">
						<div style="margin-bottom: 5px;">
						<input type="submit" value="<?= GetMessage("TSBS_2ORDER") ?>" class="inputbutton">
						</div>
					</td>
				</form>
				<td align="right" valign="top" bgcolor="#3d5682"><IMG height="1" width="1" src="../images/empty.gif" border="0"></td>
			</tr>
		<?endif;?>

	<?endif;?>


	<?if ($bDelay):?>

		<?if ($bReady):?>
			<tr>
				<td bgcolor="#3d5682" colspan="4"><img height="1" width="1" src="/bitrix/images/1.gif" border="0"></td>
			</tr>
		<?endif;?>

		<tr>
			<td align="right" valign="top" bgcolor="#3d5682"><img height="1" width="1" src="/bitrix/images/1.gif" border="0"></td>
			<td width="243" colspan="2" align="center"><div style="margin-bottom: 5px; margin-top: 5px;"><font style="font-family: Arial, Helvetica, sans-serif; font-size:11pt; color:#000000; font-weight:bold;"><?= GetMessage("TSBS_DELAY") ?></font></div></td>
			<td align="right" valign="top" bgcolor="#3d5682"><img height="1" width="1" src="/bitrix/images/1.gif" border="0"></td>
		</tr>
		<?
		for ($i = 0; $i<count($arItems); $i++)
		{
			if ($arItems[$i]["DELAY"]=="Y" && $arItems[$i]["CAN_BUY"]=="Y")
			{
				?>
				<tr>
					<td align="right" valign="top" bgcolor="#3d5682"><IMG height="1" width="1" src="/bitrix/images/1.gif" border="0"></td>
					<td valign="top"><img src="/bitrix/templates/.default/sale/sale_pieces/images/icon_basket_white.gif" width="17" height="17" border="0" alt=""></td>
					<td width="243" valign="top">
						<div style="margin-bottom: 5px;">
						<font class="text">
						<?if (strlen($arItems[$i]["DETAIL_PAGE_URL"])>0):?>
							<a href="<?echo $arItems[$i]["DETAIL_PAGE_URL"] ?>">
						<?endif;?>
						<b><?echo $arItems[$i]["NAME"]?></b>
						<?if (strlen($arItems[$i]["DETAIL_PAGE_URL"])>0):?>
							</a>
						<?endif;?>
						<br>
						<?= GetMessage("TSBS_PRICE") ?>&nbsp;<B><?echo SaleFormatCurrency($arItems[$i]["PRICE"], $arItems[$i]["CURRENCY"]) ?></B><br>
						<?= GetMessage("TSBS_QUANTITY") ?>&nbsp;<?echo $arItems[$i]["QUANTITY"]?>
						</font>
						</div>
					</td>
					<td align="right" valign="top" bgcolor="#3d5682"><IMG height="1" width="1" src="/bitrix/images/1.gif" border="0"></td>
				</tr>
				<?
			}
		}
		?>
		<?if (strlen($BASKET_PAGE)>0):?>
			<tr>
				<td align="right" valign="top" bgcolor="#3d5682"><IMG height="1" width="1" src="../images/empty.gif" border="0"></td>
				<form method="get" action="<?= $BASKET_PAGE ?>">
					<td width="243" colspan="2" align="center">
						<div style="margin-bottom: 5px;">
						<input type="submit" value="<?= GetMessage("TSBS_2BASKET") ?>" class="inputbutton">
						</div>
					</td>
				</form>
				<td align="right" valign="top" bgcolor="#3d5682"><IMG height="1" width="1" src="../images/empty.gif" border="0"></td>
			</tr>
		<?endif;?>

	<?endif;?>



	<?if ($bNotAvail):?>

		<?if ($bReady || $bDelay):?>
			<tr>
				<td bgcolor="#3d5682" colspan="4"><img height="1" width="1" src="/bitrix/images/1.gif" border="0"></td>
			</tr>
		<?endif;?>

		<tr>
			<td align="right" valign="top" bgcolor="#3d5682"><img height="1" width="1" src="/bitrix/images/1.gif" border="0"></td>
			<td width="243" colspan="2" align="center"><div style="margin-bottom: 5px; margin-top: 5px;"><font style="font-family: Arial, Helvetica, sans-serif; font-size:11pt; color:#000000; font-weight:bold;"><?= GetMessage("TSBS_UNAVAIL") ?></font></div></td>
			<td align="right" valign="top" bgcolor="#3d5682"><img height="1" width="1" src="/bitrix/images/1.gif" border="0"></td>
		</tr>
		<?
		for ($i = 0; $i<count($arItems); $i++)
		{
			if ($arItems[$i]["CAN_BUY"]=="N")
			{
				?>
				<tr>
					<td align="right" valign="top" bgcolor="#3d5682"><IMG height="1" width="1" src="/bitrix/images/1.gif" border="0"></td>
					<td valign="top"><img src="/bitrix/templates/.default/sale/sale_pieces/images/icon_basket_white.gif" width="17" height="17" border="0" alt=""></td>
					<td width="243" valign="top">
						<div style="margin-bottom: 5px;">
						<font class="text">
						<?if (strlen($arItems[$i]["DETAIL_PAGE_URL"])>0):?>
							<a href="<?echo $arItems[$i]["DETAIL_PAGE_URL"] ?>">
						<?endif;?>
						<b><?echo $arItems[$i]["NAME"]?></b>
						<?if (strlen($arItems[$i]["DETAIL_PAGE_URL"])>0):?>
							</a>
						<?endif;?>
						<br>
						<?= GetMessage("TSBS_PRICE") ?>&nbsp;<B><?echo SaleFormatCurrency($arItems[$i]["PRICE"], $arItems[$i]["CURRENCY"]) ?></B><br>
						<?= GetMessage("TSBS_QUANTITY") ?>&nbsp;<?echo $arItems[$i]["QUANTITY"]?>
						</font>
						</div>
					</td>
					<td align="right" valign="top" bgcolor="#3d5682"><IMG height="1" width="1" src="/bitrix/images/1.gif" border="0"></td>
				</tr>
				<?
			}
		}
		?>

	<?endif;?>

	<tr>
		<td bgcolor="#3d5682" colspan="4"><img height="1" width="1" src="/bitrix/images/1.gif" border="0"></td>
	</tr>
	</table>

<?else:?>

	<font class="text"><?= GetMessage("TSBS_EMPTY") ?></font>

<?endif;?>

<?
//*******************************************************
endif;
?>