<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");

$CATALOG_RIGHT = $APPLICATION->GetGroupRight("sale");
if ($CATALOG_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

include(GetLangFileName($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/lang/", "/cat_currencies.php"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$strWarning = "";

$by = "sort";
$order = "asc";
$db_result_lang = CLang::GetList($by, $order);

$iCount = 0;
while ($db_result_lang_array = $db_result_lang->Fetch())
{
	$arLangsLID[$iCount] = $db_result_lang_array["LID"];
	$arLangNamesLID[$iCount] = htmlspecialchars($db_result_lang_array["NAME"]);
	$iCount++;
}

if (strlen($ID)>0 and $cf=="del" && $CATALOG_RIGHT=="W")
{
	if (!CCurrency::Delete($ID))
	{
		$strWarning=GetMessage("currency_err1");
	}
}

if ($REQUEST_METHOD == "POST" && strlen($Update)>0 && $CATALOG_RIGHT=="W")
{
	$db_result = CCurrency::GetList(($by="sort"), ($order="asc"));

	while ($db_result_array = $db_result->Fetch())
	{
		unset($arFields);
		$arFields["CURRENCY"] = Trim($db_result_array["CURRENCY"]);
		$arFields["AMOUNT"] = DoubleVal(${"amount_".$arFields["CURRENCY"]});
		$arFields["AMOUNT_CNT"] = IntVal(${"amount_cnt_".$arFields["CURRENCY"]});
		$arFields["SORT"] = IntVal(${"sort_".$arFields["CURRENCY"]});

		if ($arFields["SORT"]>255 || $arFields["SORT"]<0)
			$arFields["SORT"] = 0;

		if ($arFields["SORT"]!=$db_result_array["SORT"] || $arFields["AMOUNT"]!=$db_result_array["AMOUNT"] || $arFields["AMOUNT_CNT"]!=$db_result_array["AMOUNT_CNT"])
		{
			CCurrency::Update($arFields["CURRENCY"], $arFields);
		}

		for ($i=0; $i<$iCount; $i++)
		{
			unset($arFields1);
			$arFields1["FORMAT_STRING"] = Trim(${"format_string_".$arFields["CURRENCY"]."_".$arLangsLID[$i]});
			$arFields1["FULL_NAME"] = Trim(${"full_name_".$arFields["CURRENCY"]."_".$arLangsLID[$i]});
			$arFields1["DEC_POINT"] = ${"dec_point_".$arFields["CURRENCY"]."_".$arLangsLID[$i]};
			$arFields1["THOUSANDS_SEP"] = ${"thousands_sep_".$arFields["CURRENCY"]."_".$arLangsLID[$i]};
			$arFields1["DECIMALS"] = IntVal(${"decimals_".$arFields["CURRENCY"]."_".$arLangsLID[$i]});
			$arFields1["CURRENCY"] = $arFields["CURRENCY"];
			$arFields1["LID"] = $arLangsLID[$i];

			if (strlen($arFields1["FORMAT_STRING"])<=0)
				$strWarning = GetMessage("currency_err2");
			else
			{
				$db_result_lang = CCurrencyLang::GetByID($arFields["CURRENCY"], $arLangsLID[$i]);
				if ($db_result_lang)
					CCurrencyLang::Update($arFields["CURRENCY"], $arLangsLID[$i], $arFields1);
				else
					CCurrencyLang::Add($arFields1);
			}
		}
	}

	$newcur = Trim($newcur);
	if (strlen($newcur)>0 && strlen($newamount)>0)
	{
		unset($arFields);

		$arFields["CURRENCY"] = Trim($newcur);
		$arFields["AMOUNT"] = DoubleVal($newamount);
		$arFields["AMOUNT_CNT"] = DoubleVal($newamount_cnt);
		$arFields["SORT"] = DoubleVal($newsort);

		if (CCurrency::GetByID($arFields["CURRENCY"]))
			$strWarning = GetMessage("currency_err3_1")." ".htmlspecialchars($newcur)." ".GetMessage("currency_err3_2");
		else
		{
			if ($arFields["SORT"]>255 || $arFields["SORT"]<0)
				$arFields["SORT"] = 0;

			CCurrency::Add($arFields);

			for ($i=0; $i<$iCount; $i++)
			{
				unset($arFields1);
				$arFields1["FORMAT_STRING"] = Trim(${"format_string_".$arLangsLID[$i]});
				$arFields1["FULL_NAME"] = Trim(${"full_name_".$arLangsLID[$i]});
				$arFields1["DEC_POINT"] = ${"dec_point_".$arLangsLID[$i]};
				$arFields1["THOUSANDS_SEP"] = ${"thousands_sep_".$arLangsLID[$i]};
				$arFields1["DECIMALS"] = IntVal(${"decimals_".$arLangsLID[$i]});
				$arFields1["CURRENCY"] = $arFields["CURRENCY"];
				$arFields1["LID"] = $arLangsLID[$i];

				if (strlen($arFields1["FORMAT_STRING"])<=0)
					$strWarning = GetMessage("currency_err2");
				else
					CCurrencyLang::Add($arFields1);
			}
		}
	}
}

$APPLICATION->SetTitle(GetMessage("CURRENCY_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
?>

<p><font class="text">
	<a href="cat_currencies_rates.php?lang=<?echo LANG ?>"><?echo GetMessage("currency_list")?></a>
</font></p>

<?ShowError($strWarning)?>

<?if($REQUEST_METHOD!="POST" || strlen($Add)<=0):?>
<form action="cat_currencies.php" method="post" name="form1">
	<font class="tablebodytext">
	<input type="hidden" name="lang" value="<?echo LANG ?>">
	<input type="submit" class="button" name="Add" value="<?echo GetMessage("currency_add")?>">
	</font>
</form>
<?endif;?>

<?
$db_result = CCurrency::GetList(($by="CURRENCY"), ($order="asc"));

$db_result_array = $db_result->ExtractFields("f_");

if ($db_result_array || ($REQUEST_METHOD=="POST" && strlen($Add)>0)):
	?> 
	<form action="cat_currencies.php" method="POST">
		<table border="0" cellspacing="1" cellpadding="3" width="100%">
			<tr> 
				<td valign="top" width="0%" nowrap class="tablehead1"><font class="tableheadtext"><?echo GetMessage("currency_curr")?></font></td>
				<td valign="top" nowrap class="tablehead2"><font class="tableheadtext"><?echo GetMessage("currency_rate_cnt")?></font></td>
				<td valign="top" nowrap class="tablehead2"><font class="tableheadtext"><?echo GetMessage("currency_rate")?></font></td>
				<td valign="top" nowrap class="tablehead2"><font class="tableheadtext"><?echo GetMessage("currency_sort")?></font></td>
				<?for ($i=0; $i<$iCount; $i++):?>
					<td valign="top" align="left" nowrap class="tablehead2"><font class="tableheadtext"><?echo $arLangNamesLID[$i]?></font></td>
				<?endfor;?>
				<td align="center" valign="top" width="0%" nowrap class="tablehead3"><font class="tableheadtext"><?echo GetMessage("currency_actions")?></font></td>
			</tr>

			<?if ($REQUEST_METHOD=="POST" && strlen($Add)>0):?>
				<tr align="left" valign="top">
					<td align="left" class="tablebody1">
						<font class="tablebodytext">
						<input type="text" class="typeinput" size="3" name="newcur" maxlength="3">
						</font>
					</td>
					<td align="left" class="tablebody2">
						<font class="tablebodytext">
						<input type="text" class="typeinput" size="2" name="newamount_cnt" value="">
						</font>
					</td>
					<td align="left" class="tablebody2">
						<font class="tablebodytext">
						<input type="text" class="typeinput" size="10" name="newamount" value="" maxlength="10">
						</font>
					</td>
					<td align="left" class="tablebody2">
						<font class="tablebodytext">
						<input type="text" class="typeinput" size="10" name="newsort" value="" maxlength="10">
						</font>
					</td>
					<?for($i=0;$i<$iCount;$i++):?>
						<td align="left" class="tablebody2"><font class="tablebodytext">
							<table width="0" border="0">
								<tr>
									<td align="right"><font class="tablebodytext" title="<?echo GetMessage("CURRENCY_FULL_NAME_DESC")?>">
										<?echo GetMessage("CURRENCY_FULL_NAME")?>:</font>
									</td>
									<td>
										<input class="typeinput" title="<?echo GetMessage("CURRENCY_FULL_NAME_DESC")?>" type="text" maxlength="50" size="10" name="full_name_<?echo $arLangsLID[$i]?>" value="">
									</td>
								</tr>
								<tr>
									<td align="right"><font class="tablebodytext" title="<?echo GetMessage("CURRENCY_FORMAT_DESC")?>">
										<?echo GetMessage("CURRENCY_FORMAT")?>:</font>
									</td>
									<td>
										<input class="typeinput" title="<?echo GetMessage("CURRENCY_FORMAT_DESC")?>" type="text" maxlength="50" size="10" name="format_string_<?echo $arLangsLID[$i]?>" value=""><br>
									</td>
								</tr>
								<tr>
									<td align="right"><font class="tablebodytext" title="<?echo GetMessage("CURRENCY_DEC_POINT_DESC")?>">
										<?echo GetMessage("CURRENCY_DEC_POINT")?>:</font>
									</td>
									<td>
										<input class="typeinput" title="<?echo GetMessage("CURRENCY_DEC_POINT_DESC")?>" type="text" maxlength="5" size="5" name="dec_point_<?echo $arLangsLID[$i]?>" value=""><br>
									</td>
								</tr>
								<tr>
									<td align="right"><font class="tablebodytext" title="<?echo GetMessage("THOU_SEP_DESC")?>">
										<?echo GetMessage("THOU_SEP")?>:</font>
									</td>
									<td>
										<input class="typeinput" title="<?echo GetMessage("THOU_SEP_DESC")?>" type="text" maxlength="5" size="5" name="thousands_sep_<?echo $arLangsLID[$i]?>" value=""><br>
									</td>
								</tr>
								<tr>
									<td align="right"><font class="tablebodytext" title="<?echo GetMessage("DECIMALS_DESC")?>">
										<?echo GetMessage("DECIMALS")?>:</font>
									</td>
									<td>
										<input class="typeinput" title="<?echo GetMessage("DECIMALS_DESC")?>" type="text" maxlength="5" size="5" name="decimals_<?echo $arLangsLID[$i]?>" value=""><br>
									</td>
								</tr>
							</table>
							</font>
						</td>
					<?endfor?>
					<td align="center" class="tablebody3"><font class="tablebodytext">&nbsp;</font></td>
				</tr>
			<?endif?>

			<?
			if ($db_result_array)
			{
				do
				{
					?>
					<tr align="left" valign="top">
						<td align="left" class="tablebody1"> <font class="tablebodytext"><?echo $f_CURRENCY?></font></td>
						<td align="left" class="tablebody2"> <font class="tablebodytext"><input type="text" class="typeinput" size="2" name="amount_cnt_<?echo Trim($f_CURRENCY)?>" value="<?echo IntVal($f_AMOUNT_CNT) ?>"></font></td>
						<td align="left" class="tablebody2"> <font class="tablebodytext"><input type="text" class="typeinput" size="10" name="amount_<?echo Trim($f_CURRENCY)?>" value="<?echo number_format($f_AMOUNT, 4)?>"></font></td>
						<td align="left" class="tablebody2"> <font class="tablebodytext"><input type="text" class="typeinput" size="3" maxlength="3" name="sort_<?echo Trim($f_CURRENCY)?>" value="<?echo $f_SORT?>"></font></td>
						<?
						for ($i=0; $i<$iCount; $i++)
						{
							$db_result_lang = CCurrencyLang::GetByID($f_CURRENCY, $arLangsLID[$i]);
							?>
							<td align="left" class="tablebody2"> <font class="tablebodytext">
								<table width="0" border="0">
								<tr>
									<td align="right"><font class="tablebodytext" title="<?echo GetMessage("CURRENCY_FULL_NAME_DESC")?>">
										<?echo GetMessage("CURRENCY_FULL_NAME")?>:</font>
									</td>
									<td>
										<input class="typeinput" title="<?echo GetMessage("CURRENCY_FULL_NAME_DESC")?>" type="text" maxlength="50" size="10" name="full_name_<?echo Trim($f_CURRENCY)."_".$arLangsLID[$i]?>" value="<?echo htmlspecialchars($db_result_lang["FULL_NAME"])?>">
									</td>
								</tr>
								<tr>
									<td align="right"><font class="tablebodytext" title="<?echo GetMessage("CURRENCY_FORMAT_DESC")?>">
										<?echo GetMessage("CURRENCY_FORMAT")?>:</font>
									</td>
									<td>
										<input class="typeinput" title="<?echo GetMessage("CURRENCY_FORMAT_DESC")?>" type="text" maxlength="50" size="10" name="format_string_<?echo Trim($f_CURRENCY)."_".$arLangsLID[$i]?>" value="<?echo htmlspecialchars($db_result_lang["FORMAT_STRING"])?>"><br>
									</td>
								</tr>
								<tr>
									<td align="right"><font class="tablebodytext" title="<?echo GetMessage("CURRENCY_DEC_POINT_DESC")?>">
										<?echo GetMessage("CURRENCY_DEC_POINT")?>:</font>
									</td>
									<td>
										<input class="typeinput" title="<?echo GetMessage("CURRENCY_DEC_POINT_DESC")?>" type="text" maxlength="5" size="5" name="dec_point_<?echo Trim($f_CURRENCY)."_".$arLangsLID[$i]?>" value="<?echo htmlspecialchars($db_result_lang["DEC_POINT"])?>"><br>
									</td>
								</tr>
								<tr>
									<td align="right"><font class="tablebodytext" title="<?echo GetMessage("THOU_SEP_DESC")?>">
										<?echo GetMessage("THOU_SEP")?>:</font>
									</td>
									<td>
										<input class="typeinput" title="<?echo GetMessage("THOU_SEP_DESC")?>" type="text" maxlength="5" size="5" name="thousands_sep_<?echo Trim($f_CURRENCY)."_".$arLangsLID[$i]?>" value="<?echo htmlspecialchars($db_result_lang["THOUSANDS_SEP"])?>"><br>
									</td>
								</tr>
								<tr>
									<td align="right"><font class="tablebodytext" title="<?echo GetMessage("DECIMALS_DESC")?>">
										<?echo GetMessage("DECIMALS")?>:</font>
									</td>
									<td>
										<input class="typeinput" title="<?echo GetMessage("DECIMALS_DESC")?>" type="text" maxlength="5" size="5" name="decimals_<?echo Trim($f_CURRENCY)."_".$arLangsLID[$i]?>" value="<?echo htmlspecialchars($db_result_lang["DECIMALS"])?>"><br>
									</td>
								</tr>
								</table>
								</font>
							</td>
							<?
						}
						?>
						<td align="center" class="tablebody3"><font class="tablebodytext"><a href="cat_currencies.php?ID=<?echo Trim($f_CURRENCY)?>&cf=del"><?echo GetMessage("currency_del")?></a></font></td>
					</tr>
					<?
				}
				while($db_result_array = $db_result->ExtractFields("f_"));
			}
			?>
		</table>
		<br>
		<font class="tablebodytext">
			<input type="hidden" name="lang" value="<?echo LANG?>">
			<input type="submit" class="button" name="Update" <?if ($CATALOG_RIGHT<"W") echo "disabled" ?> value="<?echo GetMessage("currency_upd")?>">
		</font>
	</form>
	<?echo BeginNote();?>
		<font class="text"><?echo GetMessage("currency_need")?></font>
	<?echo EndNote();?>
<?else:?>
	<p class="text"><?echo GetMessage("currency_empty")?></p>
<?endif;?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>