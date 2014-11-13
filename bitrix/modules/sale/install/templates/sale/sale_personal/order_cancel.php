<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
IncludeTemplateLangFile(__FILE__);

$ID = IntVal($_REQUEST["ID"]);

if (CModule::IncludeModule("sale")):
	$APPLICATION->SetTitle(GetMessage("SPOC_TITLE").$ID);

	if ($GLOBALS["USER"]->IsAuthorized()):
//*******************************************************

$PATH_TO_DETAIL = Trim($PATH_TO_DETAIL);
if (strlen($PATH_TO_DETAIL) <= 0)
	$PATH_TO_DETAIL = $GLOBALS["PATH_TO_DETAIL"];
if (strlen($PATH_TO_DETAIL) <= 0)
	$PATH_TO_DETAIL = "order_detail.php";

$PATH_TO_LIST = Trim($PATH_TO_LIST);
if (strlen($PATH_TO_LIST) <= 0)
	$PATH_TO_LIST = $GLOBALS["PATH_TO_LIST"];
if (strlen($PATH_TO_LIST) <= 0)
	$PATH_TO_LIST = "index.php";

$PATH_TO_CANCEL = Trim($PATH_TO_CANCEL);
if (strlen($PATH_TO_CANCEL) <= 0)
	$PATH_TO_CANCEL = $GLOBALS["PATH_TO_CANCEL"];
if (strlen($PATH_TO_CANCEL) <= 0)
	$PATH_TO_CANCEL = "order_cancel.php";


if ($ID > 0 && $_REQUEST["CANCEL_ORDER"] == "Y")
{
	$dbOrder = CSaleOrder::GetList(
			array("ID" => "DESC"),
			array(
					"ID" => $ID,
					"USER_ID" => IntVal($GLOBALS["USER"]->GetID())
				),
			false,
			false,
			array("ID")
		);
	if ($arOrder = $dbOrder->Fetch())
	{
		CSaleOrder::CancelOrder($arOrder["ID"], "Y", $_REQUEST["REASON_CANCELED"]);

		LocalRedirect($PATH_TO_LIST."?".GetFilterParams("filter_", false));
	}
}


if ($ID <= 0)
	LocalRedirect($PATH_TO_LIST."?".GetFilterParams("filter_", false));

?>
<font class="text">
<a name="tb"></a>
<a href="<?= htmlspecialchars($PATH_TO_LIST) ?>?<?echo GetFilterParams("filter_", false)?>" class="navchain"><?=GetMessage("SALE_RECORDS_LIST")?></a>
<br><br>
</font>
<?

$dbOrder = CSaleOrder::GetList(
		array("ID" => "DESC"),
		array(
				"ID" => $ID,
				"USER_ID" => IntVal($GLOBALS["USER"]->GetID())
			),
		false,
		false,
		array("ID", "CANCELED", "STATUS_ID", "PAYED")
	);
if ($arOrder = $dbOrder->Fetch())
{
	if ($arOrder["CANCELED"]!="Y" && $arOrder["STATUS_ID"]!="F" && $arOrder["PAYED"]!="Y")
	{
		?>
		<form method="POST" action="<?= $APPLICATION->GetCurPage() ?>">
			<input type="hidden" name="ID" value="<?= $ID ?>">
			<?= GetFilterHiddens("filter_");?>
			<font class="text">
			<?= GetMessage("SALE_CANCEL_ORDER1") ?>
			<a href="<?= htmlspecialchars($PATH_TO_DETAIL) ?>?ID=<?= $ID ?>&<?= GetFilterParams("filter_", false) ?>"><?= GetMessage("SALE_CANCEL_ORDER2") ?> N<?= $ID ?></a>?
			<b><?= GetMessage("SALE_CANCEL_ORDER3") ?></b><br><br>
			<?= GetMessage("SALE_CANCEL_ORDER4") ?>:<br>
			<textarea name="REASON_CANCELED" cols="60" rows="3" class="inputtextarea"></textarea><br><br>
			<input type="hidden" name="CANCEL_ORDER" value="Y">
			<input type="submit" value="<?= GetMessage("SALE_CANCEL_ORDER_BTN") ?>" class="inputbuttonflat">
			</font>
		</form>
		<?
	}
	else
	{
		?>
		<font class="text">
		<?echo GetMessage("SALE_CANCEL_ORDER5")?>
		</font>
		<?
	}
}
else
{
	?>
	<font class="text"><?echo GetMessage("SALE_CANCEL_NO_ORDER")?></font>
	<?
}

//*******************************************************
	else:
		?>
		<font class="text"><b><?= GetMessage("STPOC_YOU_NEED_AUTH") ?></b></font>
		<?
	endif;
else:
	?>
	<font class="text"><b><?= GetMessage("SALE_NO_MODULE_X") ?></b></font>
	<?
endif;
?>