<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
IncludeTemplateLangFile(__FILE__);

if (CModule::IncludeModule("sale")):
	$APPLICATION->SetTitle(GetMessage("STPCL_TITLE"));

	if ($GLOBALS["USER"]->IsAuthorized()):
//*******************************************************

$PATH_TO_DETAIL = Trim($PATH_TO_DETAIL);
if (strlen($PATH_TO_DETAIL) <= 0)
	$PATH_TO_DETAIL = "cc_detail.php";


$errorMessage = "";

$del_id = IntVal($_REQUEST["del_id"]);
if ($del_id > 0)
{
	$dbUserCards = CSaleUserCards::GetList(
			array(),
			array(
					"ID" => $del_id,
					"USER_ID" => IntVal($GLOBALS["USER"]->GetID())
				)
		);
	if ($arUserCards = $dbUserCards->Fetch())
	{
		if (!CSaleUserCards::Delete($arUserCards["ID"]))
		{
			if ($ex = $GLOBALS["APPLICATION"]->GetException())
				$errorMessage .= $ex->GetString();
			else
				$errorMessage .= str_replace("#ID#", $del_id, GetMessage("STPCL_ERROR_DELETING"))."<br>";
		}
	}
	else
	{
		$errorMessage .= str_replace("#ID#", $del_id, GetMessage("STPCL_NO_CARD_FOUND"))."<br>";
	}
}


echo ShowError($errorMessage);

$dbUserCards = CSaleUserCards::GetList(
		array($_REQUEST["by"] => $_REQUEST["order"]),
		array("USER_ID" => IntVal($GLOBALS["USER"]->GetID())),
		false,
		false,
		array("ID", "USER_ID", "ACTIVE", "SORT", "PAY_SYSTEM_ACTION_ID", "CURRENCY", "CARD_TYPE", "CARD_NUM", "CARD_CODE", "CARD_EXP_MONTH", "CARD_EXP_YEAR", "DESCRIPTION", "SUM_MIN", "SUM_MAX", "SUM_CURRENCY", "TIMESTAMP_X", "LAST_STATUS", "LAST_STATUS_CODE", "LAST_STATUS_DESCRIPTION", "LAST_STATUS_MESSAGE", "LAST_SUM", "LAST_CURRENCY", "LAST_DATE")
	);

$dbUserCards->NavStart(20);
?>
<form method="GET" action="<?= htmlspecialchars($PATH_TO_DETAIL) ?>">
	<input type="submit" class="inputbuttonflat" name="Add" value="<?echo GetMessage("STPCL_NEW")?>">
</form>

<p><?echo $dbUserCards->NavPrint(GetMessage("STPCL_NAV"))?></p>
<form method="POST" action="<?= htmlspecialchars($sDocPath) ?>">
<input type="hidden" name="Update" value="Y">
<table border="0" cellspacing="0" cellpadding="0" width="100%" class="tableborder">
	<tr>
		<td>
			<table border="0" cellspacing="1" cellpadding="2" width="100%">
				<tr>
					<td valign="top" align="center" class="tablehead" nowrap>
						<font class="tableheadtext"><?= "ID" ?><br><?= SortingEx("ID")?></font>
					</td>
					<td valign="top" align="center" class="tablehead" nowrap>
						<font class="tableheadtext"><?= GetMessage("STPCL_TYPE") ?><br><?= SortingEx("CARD_TYPE") ?></font>
					</td>
					<td valign="top" align="center" class="tablehead" nowrap>
						<font class="tableheadtext"><?= GetMessage("STPCL_PAY_SYS") ?><br><?= SortingEx("PAY_SYSTEM_ACTION_ID") ?></font>
					</td>
					<td valign="top" align="center" class="tablehead" nowrap>
						<font class="tableheadtext"><?= GetMessage("STPCL_CEXP") ?><br></font>
					</td>
					<td valign="top" align="center" class="tablehead" nowrap>
						<font class="tableheadtext"><?= GetMessage("STPCL_ACTIV") ?><br><?= SortingEx("ACTIVE") ?></font>
					</td>
					<td valign="top" align="center" class="tablehead" nowrap>
						<font class="tableheadtext"><?= GetMessage("STPCL_ACTIONS") ?></font>
					</td>
				</tr>
				<?
				while ($arUserCards = $dbUserCards->Fetch())
				{
					?>
					<tr valign="top">
						<td align="center" class="tablebody" nowrap>
							<font class="tablebodytext"><b><?= $arUserCards["ID"] ?></b></font>
						</td>
						<td class="tablebody" align="left">
							<font class="tablebodytext"><?= $arUserCards["CARD_TYPE"] ?></font>
						</td>
						<td align="left" class="tablebody">
							<font class="tablebodytext"><?
							$arPaySystemAction = CSalePaySystemAction::GetByID($arUserCards["PAY_SYSTEM_ACTION_ID"]);
							echo $arPaySystemAction["NAME"];
							?></font>
						</td>
						<td align="left" class="tablebody">
							<font class="tablebodytext"><?= $arUserCards["CARD_EXP_MONTH"]."/".$arUserCards["CARD_EXP_YEAR"]; ?></font>
						</td>
						<td align="left" class="tablebody">
							<font class="tablebodytext"><?= (($arUserCards["ACTIVE"] == "Y") ? GetMessage("STPCL_YES") : GetMessage("STPCL_NO")); ?></font>
						</td>
						<td align="left" class="tablebody">
							<font class="tablebodytext">
							<a title="<?= GetMessage("STPCL_UPDATE_ALT") ?>" href="<?= htmlspecialchars($PATH_TO_DETAIL) ?>?ID=<?= $arUserCards["ID"] ?>#tb"><?= GetMessage("STPCL_UPDATE") ?></a><br>
							<a title="<?= GetMessage("STPCL_DELETE_ALT") ?>" href="javascript:if(confirm('<?echo GetMessage("STPCL_DELETE_PROMT")?>')) window.location='<?= htmlspecialchars($sDocPath) ?>?del_id=<?= $arUserCards["ID"] ?>'"><?= GetMessage("STPCL_DELETE") ?></a>
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
<p><?echo $dbUserCards->NavPrint(GetMessage("STPCL_NAV"))?></p>
</form>

<?
//*******************************************************
	else:
		?>
		<font class="text"><b><?= GetMessage("STPCL_NO_AUTH") ?></b></font>
		<?
	endif;
else:
	?>
	<font class="text"><b><?= GetMessage("STPCL_NO_SALE") ?></b></font>
	<?
endif;
?>