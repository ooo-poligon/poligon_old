<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
IncludeTemplateLangFile(__FILE__);

$ID = IntVal($_REQUEST["ID"]);

if (CModule::IncludeModule("sale")):
	$APPLICATION->SetTitle(str_replace("#ID#", $ID, GetMessage("STPSC_TITLE")));

	if ($GLOBALS["USER"]->IsAuthorized()):
//*******************************************************

$PATH_TO_LIST = Trim($PATH_TO_LIST);
if (strlen($PATH_TO_LIST) <= 0)
	$PATH_TO_LIST = "subscribe_list.php";

$PATH_TO_CANCEL = Trim($PATH_TO_CANCEL);
if (strlen($PATH_TO_CANCEL) <= 0)
	$PATH_TO_CANCEL = "subscribe_cancel.php";


if ($ID > 0 && $_REQUEST["CANCEL_SUBSCRIBE"] == "Y")
{
	$dbRecurring = CSaleRecurring::GetList(
			array("ID" => "DESC"),
			array(
					"ID" => $ID,
					"USER_ID" => IntVal($GLOBALS["USER"]->GetID())
				),
			false,
			false,
			array("ID")
		);
	if ($arRecurring = $dbRecurring->Fetch())
	{
		CSaleRecurring::CancelRecurring($arRecurring["ID"], "Y", $_REQUEST["REASON_CANCELED"]);

		LocalRedirect($PATH_TO_LIST."?".GetFilterParams("filter_", false));
	}
}


if ($ID <= 0)
	LocalRedirect($PATH_TO_LIST."?".GetFilterParams("filter_", false));

?>
<font class="text">
<a name="tb"></a>
<a href="<?= htmlspecialchars($PATH_TO_LIST) ?>?<?echo GetFilterParams("filter_", false)?>" class="navchain"><?= GetMessage("STPSC_2LIST") ?></a>
<br><br>
</font>
<?

$dbRecurring = CSaleRecurring::GetList(
		array("ID" => "DESC"),
		array(
				"ID" => $ID,
				"USER_ID" => IntVal($GLOBALS["USER"]->GetID())
			),
		false,
		false,
		array("ID", "CANCELED", "PRODUCT_NAME")
	);
if ($arRecurring = $dbRecurring->Fetch())
{
	if ($arRecurring["CANCELED"] != "Y")
	{
		?>
		<form method="POST" action="<?= $APPLICATION->GetCurPage() ?>">
			<input type="hidden" name="ID" value="<?= $ID ?>">
			<?= GetFilterHiddens("filter_");?>
			<font class="text">
			<?= str_replace("#NAME#", $arRecurring["PRODUCT_NAME"], str_replace("#ID#", $ID, GetMessage("STPSC_CONFIRM"))); ?><br><br>
			<?= GetMessage("STPSC_REASON") ?>:<br>
			<textarea name="REASON_CANCELED" cols="60" rows="3" class="inputtextarea"></textarea><br><br>
			<input type="hidden" name="CANCEL_SUBSCRIBE" value="Y">
			<input type="submit" value="<?echo GetMessage("STPSC_ACTION")?>" class="inputbuttonflat">
			</font>
		</form>
		<?
	}
	else
	{
		?>
		<font class="text">
		<?= GetMessage("STPSC_CANT_CANCEL") ?>
		</font>
		<?
	}
}
else
{
	?>
	<font class="text"><?= GetMessage("STPSC_NO_SUBSCR") ?></font>
	<?
}

//*******************************************************
	else:
		?>
		<font class="text"><b><?= GetMessage("STPSC_NEED_AUTH") ?></b></font>
		<?
	endif;
else:
	?>
	<font class="text"><b><?= GetMessage("STPSC_NO_SALE_MODULE") ?></b></font>
	<?
endif;
?>