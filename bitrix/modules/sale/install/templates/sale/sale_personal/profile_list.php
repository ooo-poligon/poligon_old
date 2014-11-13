<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
IncludeTemplateLangFile(__FILE__);

if (CModule::IncludeModule("sale")):
	$APPLICATION->SetTitle(GetMessage("SP_TITLE"));

	if ($GLOBALS["USER"]->IsAuthorized()):
//*******************************************************

$PATH_TO_DETAIL = Trim($PATH_TO_DETAIL);
if (strlen($PATH_TO_DETAIL) <= 0)
	$PATH_TO_DETAIL = $GLOBALS["PATH_TO_DETAIL"];
if (strlen($PATH_TO_DETAIL) <= 0)
	$PATH_TO_DETAIL = "profile_detail.php";


$errorMessage = "";

$del_id = IntVal($_REQUEST["del_id"]);
if ($del_id > 0)
{
	$dbUserProps = CSaleOrderUserProps::GetList(
			array(),
			array(
					"ID" => $del_id,
					"USER_ID" => IntVal($GLOBALS["USER"]->GetID())
				)
		);
	if ($arUserProps = $dbUserProps->Fetch())
	{
		if (!CSaleOrderUserProps::Delete($arUserProps["ID"]))
		{
			$errorMessage .= GetMessage("SALE_DEL_PROFILE")."<br>";
		}
	}
	else
	{
		$errorMessage .= GetMessage("SALE_NO_PROFILE")."<br>";
	}
}


echo ShowError($errorMessage);

$dbUserProps = CSaleOrderUserProps::GetList(
		array($_REQUEST["by"] => $_REQUEST["order"]),
		array("USER_ID" => IntVal($GLOBALS["USER"]->GetID()))
	);

$dbUserProps->NavStart(20);
?>
<p><?echo $dbUserProps->NavPrint(GetMessage("SALE_PRLIST"))?></p>
<form method="POST" action="<?= htmlspecialchars($sDocPath) ?>">
<input type="hidden" name="Update" value="Y">
<table border="0" cellspacing="0" cellpadding="0" width="100%"  class="tableborder">
	<tr>
		<td>
			<table border="0" cellspacing="1" cellpadding="2" width="100%">
				<tr>
					<td valign="top" align="center" class="tablehead" nowrap>
						<font class="tableheadtext"><?echo GetMessage("P_ID")?><br><?echo SortingEx("ID")?></font>
					</td>
					<td valign="top" align="center" class="tablehead" nowrap>
						<font class="tableheadtext"><?echo GetMessage("P_DATE_UPDATE")?><br><?echo SortingEx("DATE_UPDATE")?></font>
					</td>
					<td valign="top" align="center" class="tablehead" nowrap>
						<font class="tableheadtext"><?echo GetMessage("P_NAME")?><br><?echo SortingEx("NAME")?></font>
					</td>
					<td valign="top" align="center" class="tablehead" nowrap>
						<font class="tableheadtext"><?echo GetMessage("P_PERSON_TYPE")?><br><?echo SortingEx("PERSON_TYPE_ID")?></font>
					</td>
					<td valign="top" align="center" class="tablehead" nowrap>
						<font class="tableheadtext"><?echo GetMessage("SALE_ACTION")?></font>
					</td>
				</tr>
			<?
			while ($arUserProps = $dbUserProps->Fetch()):
				$arPersType = CSalePersonType::GetByID($arUserProps["PERSON_TYPE_ID"]);
				?>
				<tr valign="top">
					<td align="center" class="tablebody" nowrap>
						<font class="tablebodytext"><b><?= $arUserProps["ID"]?></b></font>
					</td>
					<td class="tablebody" align="left">
						<font class="tablebodytext"><?= $arUserProps["DATE_UPDATE"]?></font>
					</td>
					<td align="left" class="tablebody">
						<font class="tablebodytext"><?= htmlspecialcharsEx($arUserProps["NAME"]) ?></font>
					</td>
					<td align="left" class="tablebody">
						<font class="tablebodytext"><?= htmlspecialcharsEx($arPersType["NAME"]); ?></font>
					</td>
					<td align="left" class="tablebody">
						<font class="tablebodytext">
						<a title="<?= GetMessage("SALE_DETAIL_DESCR") ?>" href="<?= htmlspecialchars($PATH_TO_DETAIL) ?>?ID=<?= $arUserProps["ID"] ?>#tb"><?= GetMessage("SALE_DETAIL") ?></a><br>
						<a title="<?= GetMessage("SALE_DELETE_DESCR") ?>" href="javascript:if(confirm('<?= GetMessage("STPPL_DELETE_CONFIRM") ?>')) window.location='<?= htmlspecialchars($sDocPath) ?>?del_id=<?= $arUserProps["ID"] ?>'"><?= GetMessage("SALE_DELETE")?></a>
						</font>
					</td>
				</tr>
				<?endwhile;?>
			</table>
		</td>
	</tr>
</table>
<p><?echo $dbUserProps->NavPrint(GetMessage("SALE_PRLIST"))?></p>
</form>

<?
//*******************************************************
	else:
		?>
		<font class="text"><b><?= GetMessage("STPPL_NEED_AUTH") ?></b></font>
		<?
	endif;
else:
	?>
	<font class="text"><b><?= GetMessage("SALE_NO_MODULE_X") ?></b></font>
	<?
endif;
?>