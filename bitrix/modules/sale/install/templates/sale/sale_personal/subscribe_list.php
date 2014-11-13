<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
IncludeTemplateLangFile(__FILE__);

if (CModule::IncludeModule("sale")):
	$APPLICATION->SetTitle(GetMessage("STPSL_TITLE"));

	if ($GLOBALS["USER"]->IsAuthorized()):
//*******************************************************

$PATH_TO_CANCEL = Trim($PATH_TO_CANCEL);
if (strlen($PATH_TO_CANCEL) <= 0)
	$PATH_TO_CANCEL = "subscribe_cancel.php";


$errorMessage = "";

$del_id = IntVal($_REQUEST["del_id"]);
if (False && $del_id > 0)
{
	$dbRecurring = CSaleRecurring::GetList(
			array(),
			array(
					"ID" => $del_id,
					"USER_ID" => IntVal($GLOBALS["USER"]->GetID())
				)
		);
	if ($arRecurring = $dbRecurring->Fetch())
	{
		if (!CSaleRecurring::Delete($arRecurring["ID"]))
		{
			if ($ex = $GLOBALS["APPLICATION"]->GetException())
				$errorMessage .= $ex->GetString();
			else
				$errorMessage .= GetMessage("STPSL_ERROR_DELETING")."<br>";
		}
	}
	else
	{
		$errorMessage .= GetMessage("STPSL_NO_SUBSCRIBE")."<br>";
	}
}


echo ShowError($errorMessage);

$dbRecurring = CSaleRecurring::GetList(
		array($_REQUEST["by"] => $_REQUEST["order"]),
		array("USER_ID" => IntVal($GLOBALS["USER"]->GetID())),
		false,
		false,
		array("ID", "USER_ID", "MODULE", "PRODUCT_ID", "PRODUCT_NAME", "PRODUCT_URL", "PRODUCT_PRICE_ID", "RECUR_SCHEME_TYPE", "RECUR_SCHEME_LENGTH", "WITHOUT_ORDER", "PRICE", "CURRENCY", "ORDER_ID", "CANCELED", "CALLBACK_FUNC", "DESCRIPTION", "TIMESTAMP_X", "PRIOR_DATE", "NEXT_DATE", "REMAINING_ATTEMPTS", "SUCCESS_PAYMENT")
	);

$dbRecurring->NavStart(20);
?>
<p><?echo $dbRecurring->NavPrint(GetMessage("STPSL_PAYMENTS"))?></p>
<form method="POST" action="<?= htmlspecialchars($sDocPath) ?>">
<input type="hidden" name="Update" value="Y">
<table border="0" cellspacing="0" cellpadding="0" width="100%"  class="tableborder">
	<tr>
		<td>
			<table border="0" cellspacing="1" cellpadding="2" width="100%">
				<tr>
					<td valign="top" align="center" class="tablehead" nowrap>
						<font class="tableheadtext"><?= "ID" ?><br><?= SortingEx("ID") ?></font>
					</td>
					<td valign="top" align="center" class="tablehead" nowrap>
						<font class="tableheadtext"><?= GetMessage("STPSL_PRODUCT") ?><br><?= SortingEx("PRODUCT_NAME") ?></font>
					</td>
					<td valign="top" align="center" class="tablehead" nowrap>
						<font class="tableheadtext"><?= GetMessage("STPSL_PERIOD_TYPE") ?><br><?= SortingEx("RECUR_SCHEME_TYPE") ?></font>
					</td>
					<td valign="top" align="center" class="tablehead" nowrap>
						<font class="tableheadtext"><?= GetMessage("STPSL_PERIOD_BETW") ?><br><?= SortingEx("RECUR_SCHEME_LENGTH") ?></font>
					</td>
					<td valign="top" align="center" class="tablehead" nowrap>
						<font class="tableheadtext"><?= GetMessage("STPSL_CANCELED") ?><br><?= SortingEx("CANCELED") ?></font>
					</td>
					<td valign="top" align="center" class="tablehead" nowrap>
						<font class="tableheadtext"><?= GetMessage("STPSL_DATE_LAST") ?><br><?= SortingEx("PRIOR_DATE") ?></font>
					</td>
					<td valign="top" align="center" class="tablehead" nowrap>
						<font class="tableheadtext"><?= GetMessage("STPSL_DATE_NEXT") ?><br><?= SortingEx("NEXT_DATE") ?></font>
					</td>
					<td valign="top" align="center" class="tablehead" nowrap>
						<font class="tableheadtext"><?= GetMessage("STPSL_LAST_SUCCESS") ?><br><?= SortingEx("SUCCESS_PAYMENT") ?></font>
					</td>
					<td valign="top" align="center" class="tablehead" nowrap>
						<font class="tableheadtext"><?= GetMessage("STPSL_ACTIONS") ?></font>
					</td>
				</tr>
				<?
				while ($arRecurring = $dbRecurring->Fetch())
				{
					?>
					<tr valign="top">
						<td align="center" class="tablebody" nowrap>
							<font class="tablebodytext"><b><?= $arRecurring["ID"]?></b></font>
						</td>
						<td class="tablebody" align="left">
							<font class="tablebodytext"><?
							if (strlen($arRecurring["PRODUCT_URL"]) > 0)
								echo "<a href=\"".$arRecurring["PRODUCT_URL"]."\">";

							if (strlen($arRecurring["PRODUCT_NAME"]) > 0)
								echo htmlspecialcharsEx($arRecurring["PRODUCT_NAME"]);
							else
								echo $arRecurring["PRODUCT_ID"];

							if (strlen($arRecurring["PRODUCT_URL"]) > 0)
								echo "</a>";
							?></font>
						</td>
						<td align="left" class="tablebody">
							<font class="tablebodytext"><?
							if (array_key_exists($arRecurring["RECUR_SCHEME_TYPE"], $GLOBALS["SALE_TIME_PERIOD_TYPES"]))
								echo $GLOBALS["SALE_TIME_PERIOD_TYPES"][$arRecurring["RECUR_SCHEME_TYPE"]];
							?></font>
						</td>
						<td align="left" class="tablebody">
							<font class="tablebodytext"><?= $arRecurring["RECUR_SCHEME_LENGTH"]; ?></font>
						</td>
						<td align="left" class="tablebody">
							<font class="tablebodytext"><?= (($arRecurring["CANCELED"] == "Y") ? GetMessage("STPSL_YES") : GetMessage("STPSL_NO")); ?></font>
						</td>
						<td align="left" class="tablebody">
							<font class="tablebodytext"><?= $arRecurring["PRIOR_DATE"]; ?></font>
						</td>
						<td align="left" class="tablebody">
							<font class="tablebodytext"><?= $arRecurring["NEXT_DATE"]; ?></font>
						</td>
						<td align="left" class="tablebody">
							<font class="tablebodytext"><?= (($arRecurring["SUCCESS_PAYMENT"] == "Y") ? GetMessage("STPSL_YES") : GetMessage("STPSL_NO")); ?></font>
						</td>
						<td align="left" class="tablebody">
							<font class="tablebodytext">
							<?
							if ($arRecurring["CANCELED"] != "Y")
							{
								?>
								<a title="<?= GetMessage("STPSL_CANCEL") ?>" href="<?= htmlspecialchars($PATH_TO_CANCEL) ?>?ID=<?= $arRecurring["ID"] ?>"><?= GetMessage("STPSL_CANCEL1") ?></a>
								<?
							}
							?>
							</font>
						</td>
					</tr>
					<?
				}
				?>
			</table>
		</td>
	</tr>
</table>
<p><?echo $dbRecurring->NavPrint(GetMessage("STPSL_PAYMENTS"))?></p>
</form>

<?
//*******************************************************
	else:
		?>
		<font class="text"><b><?= GetMessage("STPSL_NEED_AUTH") ?></b></font>
		<?
	endif;
else:
	?>
	<font class="text"><b><?= GetMessage("STPSL_NO_SALE") ?></b></font>
	<?
endif;
?>