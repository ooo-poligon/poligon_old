<?
$strPaySysError = "";

if (!function_exists("LocalParceCSVLineString"))
{
	function LocalParceCSVLineString($str_data)
	{
		$bInString = false;
		$str = "";
		$res_r = Array();
		$iFileLength = strlen($str_data);
		$iCurPos = 0;
		while ($iCurPos < $iFileLength)
		{
			$ch = $str_data[$iCurPos];
			if ($ch == "\"")
			{
				if (!$bInString)
				{
					$bInString = true;
					$iCurPos++;
					continue;
				}
				else
				{
					if ($str_data[$iCurPos+1]=="\"")
						$iCurPos++;
					else
					{
						$bInString = false;
						$iCurPos++;
						continue;
					}
				}
			}
			elseif ($ch == ",")
			{
				if (!$bInString)
				{
					$res_r[] = $str;
					$str = "";
					$iCurPos++;
					continue;
				}
			}

			$iCurPos++;
			$str .= $ch;
		}
		if (strlen($str)>0)
		{
			$res_r[] = $str;
		}
		return $res_r;
	}
}

if ($bDoPayAction)
{
	$ORDER_ID = IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]);

	$cardnum = Trim($_REQUEST["cardnum"]);
	if (!isset($cardnum) || strlen($cardnum) <= 0)
		$strPaySysError .= "Please enter valid credit card number".". ";

	$cardnum = preg_replace("/[\D]+/", "", $cardnum);
	if (strlen($cardnum) <= 0)
		$strPaySysError .= "Please enter valid credit card number".". ";

	$cardexp1 = IntVal($_REQUEST["cardexp1"]);
	if ($cardexp1 < 1 || $cardexp1 > 12)
		$strPaySysError .= "Please enter valid credit card expiration month".". ";
	elseif (strlen($cardexp1) < 2)
		$cardexp1 = "0".$cardexp1;

	$cardexp2 = IntVal($_REQUEST["cardexp2"]);
	if ($cardexp2 < 5 || $cardexp2 > 50)
		$strPaySysError .= "Please enter valid credit card expiration year".". ";
	elseif (strlen($cardexp2) < 2)
		$cardexp2 = "0".$cardexp2;

	$noc = Trim($_REQUEST["noc"]);
	if (!isset($noc) || strlen($noc) <= 0)
		$strPaySysError .= "Please enter valid credit card holder address".". ";

	if (strlen($strPaySysError) <= 0)
	{
		$sHost = "secure.ibill.com";
		$sUrl = "/cgi-win/ccard/tpcard.exe";

		$sVars  = "reqtype=authorize";
		$sVars .= "&account=".urlencode(CSalePaySystemAction::GetParamValue("SHOP_ACCOUNT"))."";
		$sVars .= "&password=".urlencode(CSalePaySystemAction::GetParamValue("SHOP_PASSWORD"))."";
		$sVars .= "&saletype=sale";
		$sVars .= "&cardnum=".urlencode($cardnum)."";
		$sVars .= "&cardexp=".urlencode($cardexp1.$cardexp2)."";
		$sVars .= "&noc=".urlencode($noc)."";
		$sVars .= "&address1=".urlencode(CSalePaySystemAction::GetParamValue("ADDRESS"))."";
		$sVars .= "&zipcode=".urlencode(CSalePaySystemAction::GetParamValue("ZIP"))."";
		$sVars .= "&amount=".urlencode($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"])."";
		$sVars .= "&crefnum=".urlencode("Invoice_".$ORDER_ID."_".preg_replace("#[\D]+#", "_", $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT"]));
		//echo $sVars;

		$sResult = QueryGetData($sHost, 80, $sUrl, $sVars, $errno, $errstr, "POST");

		$arResCSV = LocalParceCSVLineString($sResult);
		if (is_array($arResCSV))
		{
			$arPaySysResult = array(
					"PS_STATUS" => (($arResCSV[0] != "declined") ? "Y" : "N"),
					"PS_STATUS_CODE" => $arResCSV[0],
					"PS_STATUS_DESCRIPTION" => $arResCSV[1],
					"PS_STATUS_MESSAGE" => $arResCSV[4],
					"PS_SUM" => $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"],
					"PS_CURRENCY" => $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
					"PS_RESPONSE_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", SITE_ID))),
					"USER_CARD_TYPE" => false,
					"USER_CARD_NUM" => $cardnum,
					"USER_CARD_EXP_MONTH" => $cardexp1,
					"USER_CARD_EXP_YEAR" => "20".$cardexp2,
					"USER_CARD_CODE" => ""
				);

			if ($arResCSV[0] != "authorized")
				$strPaySysError .= "Error while processing transaction: ".$arResCSV[1].". ";
		}
		else
			$strPaySysError .= "Error while processing transaction".". ";
	}
}
else
{
	?>
	<table border="0" width="100%" cellpadding="2" cellspacing="2">
		<tr>
			<td align="right" class="tablebody" width="40%">
				<font class="tablebodytext">Credit card number:</font>
			</td>
			<td class="tablebody" width="60%">
				<input type="text" name="cardnum" size="30" class="inputtext" value="<?= htmlspecialchars($_REQUEST["cardnum"]) ?>">
			</td>
		</tr>
		<tr>
			<td align="right" class="tablebody" width="40%">
				<font class="tablebodytext">Date on which the credit card expires (MM/YY):</font>
			</td>
			<td class="tablebody" width="60%">
				<select name="cardexp1" class="inputselect">
					<option value=""> </option>
					<?
					for ($i = 1; $i <= 12; $i++)
					{
						$val = (($i < 10) ? "0" : "").$i;
						?>
						<option value="<?= $val ?>" <?if (IntVal($_REQUEST["cardexp1"]) == $val) echo "selected";?>><?= $val ?></option>
						<?
					}
					?>
				</select>
				<select name="cardexp2" class="inputselect">
					<option value=""> </option>
					<?
					for ($i = 5; $i <= 11; $i++)
					{
						$val = (($i < 10) ? "0" : "").$i;
						?>
						<option value="<?= $val ?>" <?if (IntVal($_REQUEST["cardexp2"]) == $val) echo "selected";?>><?= $val ?></option>
						<?
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td align="right" class="tablebody" width="40%">
				<font class="tablebodytext">Card Holder:</font>
			</td>
			<td class="tablebody" width="60%">
				<input type="text" name="noc" size="40" class="inputtext" value="<?= htmlspecialchars($_REQUEST["noc"]) ?>">
			</td>
		</tr>
	</table>
	<?
}
?>