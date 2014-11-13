<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/include.php");
$CATALOG_RIGHT = $APPLICATION->GetGroupRight("catalog");
if ($CATALOG_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

include(GetLangFileName($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/lang/", "/cat_currencies_rates.php"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");

$strWarning = "";
if ($REQUEST_METHOD == "POST" && strlen($Update)>0 && $CATALOG_RIGHT=="W")
{
	for ($i=0; strlen(${"ID_".$i}) > 0; $i++)
	{
		$strWarning1 = "";
		unset($arFields);
		$CR_ID = IntVal(${"ID_".$i});
		$arFields["RATE"] = DoubleVal(${"RATE_".$i});
		$arFields["RATE_CNT"] = IntVal(${"RATE_CNT_".$i});
		$arFields["CURRENCY"] = Trim(${"CURRENCY_".$i});
		$arFields["DATE_RATE"] = Trim(${"DATE_RATE_".$i});
		$del = ${"DELETE_".$i};
		$do_query = ${"QUERY_".$i};

		if ($CR_ID <= 0)
		{
			if ($arFields["DATE_RATE"] == "" || !$DB->IsDate($arFields["DATE_RATE"]) || ($arFields["RATE"] <= 0.00 && $do_query!="Y"))
				continue;

			if ($do_query=="Y")
			{
				//$strUrl = "http://www.cbr.ru/scripts/XML_daily.asp?date_req=".$DB->FormatDate($arFields["DATE_RATE"], CLang::GetDateFormat("SHORT", $lang), "D.M.Y");
				$QUERY_STR = "date_req=".$DB->FormatDate($arFields["DATE_RATE"], CLang::GetDateFormat("SHORT", $lang), "D.M.Y");
				$strQueryText = QueryGetData("www.cbr.ru", 80, "/scripts/XML_daily.asp", $QUERY_STR, $errno, $errstr);
				if (strlen($strQueryText)<=0)
				{
					if (IntVal($errno)>0 || strlen($errstr)>0)
						$strWarning1 .= GetMessage("ERROR_QUERY_RATE")." (".$arFields["DATE_RATE"].", ".$arFields["CURRENCY"]."). ".$errno.": ".$errstr."<br>";
					else
						$strWarning1 .= GetMessage("ERROR_QUERY_RATE")." (".$arFields["DATE_RATE"].", ".$arFields["CURRENCY"]."). ".GetMessage("ERROR_EMPTY_ANSWER")."<br>";
				}
				if (strlen($strWarning1)<=0)
				{
					$strQueryText = eregi_replace("<!DOCTYPE[^>]{1,}>", "", $strQueryText);
					$strQueryText = eregi_replace("<"."\?XML[^>]{1,}\?".">", "", $strQueryText);
					$arData = xmlize_xmldata($strQueryText);

					if (is_array($arData) && count($arData["ValCurs"]["#"]["Valute"])>0)
					{
						for ($j1 = 0; $j1<count($arData["ValCurs"]["#"]["Valute"]); $j1++)
						{
							if ($arData["ValCurs"]["#"]["Valute"][$j1]["#"]["CharCode"][0]["#"]==$arFields["CURRENCY"])
							{
								$arFields["RATE_CNT"] = IntVal($arData["ValCurs"]["#"]["Valute"][$j1]["#"]["Nominal"][0]["#"]);
								$arCurrValue = str_replace(",", ".", $arData["ValCurs"]["#"]["Valute"][$j1]["#"]["Value"][0]["#"]);
								$arFields["RATE"] = DoubleVal($arCurrValue);
								break;
							}
						}
					}
				}
			}

			if (strlen($strWarning1)<=0)
			{
				if ($arFields["RATE"] <= 0.00)
					$strWarning1 .= GetMessage("ERROR_SAVING_RATE")." (".$arFields["DATE_RATE"].", ".$arFields["CURRENCY"].", ".$arFields["RATE"]."). ".GetMessage("ERROR_SAVING_RATE1")."<br>";
				if ($arFields["RATE_CNT"] <= 0)
					$strWarning1 .= GetMessage("ERROR_SAVING_RATE")." (".$arFields["DATE_RATE"].", ".$arFields["CURRENCY"].", ".$arFields["RATE"]."). ".GetMessage("ERROR_SAVING_RATE2")."<br>";
			}

			if (strlen($strWarning1)<=0)
			{
				if (!CCurrencyRates::Add($arFields))
					$strWarning1 .= GetMessage("ERROR_ADD_REC")." (".$arFields["DATE_RATE"].", ".$arFields["CURRENCY"].", ".$arFields["RATE"]."). ".GetMessage("ERROR_ADD_REC2")."<br>";
			}
		}
		elseif ($del == "Y")
		{
			CCurrencyRates::Delete($CR_ID);
		}
		else
		{
			if ($arFields["DATE_RATE"] == "" || !$DB->IsDate($arFields["DATE_RATE"]))
				$strWarning1 .= GetMessage("ERROR_DATE_RATE")." (".$arFields["DATE_RATE"].", ".$arFields["CURRENCY"].", ".$arFields["RATE"].").<br>";

			if ($arFields["RATE"] <= 0.00)
				$strWarning1 .= GetMessage("ERROR_SAVING_RATE1")." (".$arFields["DATE_RATE"].", ".$arFields["CURRENCY"].", ".$arFields["RATE"].").<br>";

			if ($arFields["RATE_CNT"] <= 0)
				$strWarning1 .= GetMessage("ERROR_SAVING_RATE2")." (".$arFields["DATE_RATE"].", ".$arFields["CURRENCY"].", ".$arFields["RATE"].").<br>";

			if (strlen($strWarning1)<=0)
			{
				if (!CCurrencyRates::Update($CR_ID, $arFields))
					$strWarning .= GetMessage("ERROR_UPDATE_REC")." (".$CR_ID.", ".$arFields["DATE_RATE"].", ".$arFields["CURRENCY"].", ".$arFields["RATE"].").<br>";
			}
		}
		$strWarning .= $strWarning1;
	}
}
$APPLICATION->SetTitle(GetMessage("CURRENCY_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
?>

<p><font class="text">
<a href="cat_currencies.php?lang=<?echo LANG ?>"><?echo GetMessage("curr_rates_list")?></a>
</font></p>

<?
$FilterArr = array("filter_currency", "filter_period_from", "filter_period_to");

if (strlen($set_filter)>0 || strlen($setfilter)>0)
	InitFilterEx($FilterArr, "CATALOG_CURRENCY_RATE", "set"); 
else
	InitFilterEx($FilterArr, "CATALOG_CURRENCY_RATE", "get");

if (strlen($del_filter)>0)
	DelFilterEx($FilterArr, "CATALOG_CURRENCY_RATE");

$arFilter = array();
if (strlen($filter_currency)>0) $arFilter["CURRENCY"] = $filter_currency;
if (strlen($filter_period_from)>0) $arFilter["DATE_RATE"] = $filter_period_from;
if (strlen($filter_period_to)>0) $arFilter["!DATE_RATE"] = $filter_period_to;
$bFilterIsSet = (strlen($filter_currency)>0 || strlen($filter_period_from)>0 || strlen($filter_period_to)>0);
?>

<?echo BeginFilter("SALE_CURR_RATES", $bFilterIsSet);?>
<form method="get" action="<?echo $sDocPath?>" name="skform">
	<tr> 
		<td class="tablebody"><font class="tablefieldtext"><?echo GetMessage("curr_rates_curr")?></font></td>
		<td class="tablebody">
			<font class="tablebodytext">
			<?echo CCurrency::SelectBox("filter_currency", $filter_currency, GetMessage("curr_rates_all"), True, "", "class='typeselect'") ?>
			</font>
		</td>
	</tr>
	<tr>
		<td class="tablebody"><font class="tablefieldtext"><?echo GetMessage("curr_rates_flt_date")?></font></td>
		<td class="tablebody">
			<font size="-1"> 
			<?echo CalendarPeriod("filter_period_from", $filter_period_from, "filter_period_to", $filter_period_to, "skform", "Y")?>
			</font>
		</td>
	</tr>
	<tr> 
		<td colspan="2" class="tablebody">
			<table border="0" cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<td width="0%">
						<font class="tablebodytext">
						<input type="hidden" name="lang" value="<?echo LANG?>">
						<input type="hidden" name="setfilter" value="Filter">
						<input type="submit" class="button" name="bfilter" value="<?echo GetMessage("curr_rates_flt_btn")?>">
						</font>
					</td>
					<td width="0%"><font class="tablebodytext">&nbsp;</font></td>
					<td width="100%" align="left">
						<font class="tablebodytext">
						<input class="button" type="submit" name="del_filter" value="<?echo GetMessage("CATALOG_F_DEL")?>">
						</font>
					</td>
					<td width="0%"><?ShowAddFavorite(false, "set_filter", "catalog")?></td>
				</tr>
			</table>
		</td>
	</tr>
</form>
<?echo EndFilter();?>
<br>

<?
InitSorting($APPLICATION->GetCurPage());
if ($by!="curr" && $by!="rate")
{
	$by = "date"; 
}
$order = strtolower($order);
if ($order != "asc") $order = "desc";

$db_rate = CCurrencyRates::GetList($by, $order, $arFilter);
$db_rate->NavStart(20);
?>

<SCRIPT LANGUAGE="JavaScript">
	var arCurrencies = new Array();
	var arCurrenciesNom = new Array();
	<?
	$db_curr = CCurrency::GetList(($by1="sort"), ($order1="asc"));
	$num_currencies = 0;
	while ($curr = $db_curr->Fetch())
	{
		echo "arCurrencies[".$num_currencies."]='".$curr["CURRENCY"]."';";
		$db_currate = CCurrencyRates::GetList(($by5="DATE_RATE"), ($order5="DESC"), array("CURRENCY"=>$curr["CURRENCY"]));
		if ($currate = $db_currate->Fetch())
			echo "arCurrenciesNom[".$num_currencies."]=".$currate["RATE_CNT"].";";
		else
			echo "arCurrenciesNom[".$num_currencies."]=".$curr["AMOUNT_CNT"].";";
		$num_currencies++;
	}
	?>

	function ChangeCurr(codID)
	{
		CUR_SELECT_BOX = eval("document.curform.CURRENCY_" + codID);
		CUR_RATE_CNT = eval("document.curform.RATE_CNT_" + codID);

		for (i = 0; i < arCurrencies.length; i++)
		{
			if (CUR_SELECT_BOX.options[CUR_SELECT_BOX.selectedIndex].value == arCurrencies[i])
			{
				CUR_RATE_CNT.value = arCurrenciesNom[i];
				break;
			}
		}
	}

</SCRIPT>

<?ShowError($strWarning)?>

<form action="<?echo $sDocPath?>" method="POST" name="curform">
<p><?$db_rate->NavPrint(GetMessage("curr_rates_nav"));?></p>
<table border="0" cellspacing="1" cellpadding="3">
	<tr valign="top" align="center"> 
		<td class="tablehead1"><font class="tableheadtext"><?echo GetMessage("curr_rates_date1")?><br><?echo SortingEx("date")?></font></td>
		<td class="tablehead2"><font class="tableheadtext"><?echo GetMessage("curr_rates_curr1")?><br><?echo SortingEx("curr")?></font></td>
		<td class="tablehead2"><font class="tableheadtext"><?echo GetMessage("curr_rates_rate_cnt")?><br></font></td>
		<td class="tablehead2"><font class="tableheadtext"><?echo GetMessage("curr_rates_rate")?><br><?echo SortingEx("rate")?></font></td>
		<td class="tablehead2"><font class="tableheadtext"><?echo GetMessage("curr_rates_del")?></font></td>
		<td class="tablehead3"><font class="tableheadtext"><?echo GetMessage("curr_rates_query")?></font></td>
	</tr>
	<?for($i=0; $i<$num_currencies; $i++):?>
		<tr valign="top">
			<td class="tablebody1">
				<input type="text" class="typeinput" name="DATE_RATE_<?echo $i?>" size="12">
				<?echo Calendar("DATE_RATE_".$i, "curform")?>
			</td>
			<td class="tablebody2">
				<?echo CCurrency::SelectBox("CURRENCY_".$i, "", "", True, "ChangeCurr(".$i.")", "class='typeselect'")?>
			</td>
			<td class="tablebody2">
				<input type="text" class="typeinput" name="RATE_CNT_<?echo $i?>" size="3">
			</td>
			<td class="tablebody2">
				<input type="text" class="typeinput" name="RATE_<?echo $i?>" size="12">
				<input type="hidden" name="ID_<?echo $i?>" value="new">
			</td>
			<td class="tablebody2"><font class="tablebodytext">&nbsp;</font></td>
			<td class="tablebody3">
				<font class="tablebodytext">
				<input type="checkbox" name="QUERY_<?echo $i?>" value="Y">
				</font>
			</td>
		</tr>
		<SCRIPT LANGUAGE="JavaScript">
			ChangeCurr(<?echo $i ?>);
		</SCRIPT>
	<?endfor;?>
	<?
	while ($db_rate_arr = $db_rate->NavNext(true, "f_")):
		?>
		<tr valign="top">
			<td class="tablebody1">
				<input type="text" class="typeinput" name="DATE_RATE_<?echo $i?>" size="12" value="<?echo $f_DATE_RATE?>">
				<?echo Calendar("DATE_RATE_".$i, "curform")?>
			</td>
			<td class="tablebody2"><?echo CCurrency::SelectBox("CURRENCY_".$i, $db_rate_arr["CURRENCY"], "", True, "", "class='typeselect'")?></td>
			<td class="tablebody2">
				<input type="text" class="typeinput" name="RATE_CNT_<?echo $i?>" size="3" value="<?echo $f_RATE_CNT ?>">
			</td>
			<td class="tablebody2">
				<input type="text" class="typeinput" name="RATE_<?echo $i?>" size="12" value="<?echo number_format($f_RATE, 4)?>">
				<input type="hidden" name="ID_<?echo $i?>" value="<?echo $f_ID?>">
			</td>
			<td class="tablebody2">
				<font class="tablebodytext">
				<input type="checkbox" name="DELETE_<?echo $i?>" value="Y">
				</font>
			</td>
			<td class="tablebody3">&nbsp; </td>
		</tr>
		<?
		$i++;
	endwhile;
	?>
</table>
<p><font class="tablebodytext">
<input type="hidden" name="lang" value="<?echo $lang ?>">
<input type="submit" class="button" name="Update" <?if ($CATALOG_RIGHT<"W") echo "disabled" ?> value="<?echo GetMessage("curr_rates_upd")?>">
<input type="Reset" class="button" value="<?echo GetMessage("curr_rates_reset")?>">
</font></p>
<p><?$db_rate->NavPrint(GetMessage("curr_rates_nav"));?></p>
</form>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>