<?
include(dirname(__FILE__)."/common.php");

$PF_HOST = CSalePaySystemAction::GetParamValue("PAYFLOW_URL");
$PF_PORT = CSalePaySystemAction::GetParamValue("PAYFLOW_PORT");
$PF_USER = CSalePaySystemAction::GetParamValue("PAYFLOW_USER");
$PF_PWD = CSalePaySystemAction::GetParamValue("PAYFLOW_PASSWORD");
$PF_PARTNER = CSalePaySystemAction::GetParamValue("PAYFLOW_PARTNER");
$strExePath = CSalePaySystemAction::GetParamValue("PAYFLOW_EXE_PATH");
$PFPRO_CERT_PATH = CSalePaySystemAction::GetParamValue("PAYFLOW_CERT_PATH");

$ORDER_ID = IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]);
?>
<table border="0" width="100%" cellpadding="2" cellspacing="2">
  <tr>
	<td class="but2" align="center">
		<?
		//***************************************************
		//***  START ACTION  ********************************
		//***************************************************
		$strErrorTmp = "";
		$bNonePay = True;
		if (strlen($_POST["GetPayRes"]) > 0)
		{
			$cardnum = Trim($_POST["cardnum"]);
			$cardnum = preg_replace("#[\D]#i", "", $cardnum);
			if (strlen($cardnum) <=0 )
				$strErrorTmp.= "Please fill in \"Credit Card Number\" field. ";

			$cvv2 = Trim($_POST["cvv2"]);
			if (strlen($cvv2) <= 0)
				$strErrorTmp.= "Please fill in \"CVV2\" field. ";

			$cardexp1 = IntVal($_POST["cardexp1"]);
			$cardexp2 = IntVal($_POST["cardexp2"]);
			if ($cardexp1 < 1 || $cardexp1 > 12)
				$strErrorTmp.= "Please fill in \"Expiration Date\" field. ";
			elseif ($cardexp2 < 4 || $cardexp2 > 99)
				$strErrorTmp.= "Please fill in \"Expiration Date\" field. ";
			else
			{
				$cardexp1 = ((strlen($cardexp1) < 2) ? "0".$cardexp1 : $cardexp1);
				$cardexp2 = ((strlen($cardexp2) < 2) ? "0".$cardexp2 : $cardexp2);
			}

			$noc = Trim($_POST["noc"]);
			if (strlen($noc) <= 0)
				$strErrorTmp.= "Please fill in \"Cardholder\" field. ";

			$address1 = Trim($_POST["address1"]);
			if (strlen($address1) <= 0)
				$strErrorTmp.= "Please fill in \"Address\" field. ";

			$zipcode = Trim($_POST["zipcode"]);
			if (strlen($zipcode) <= 0)
				$strErrorTmp.= "Please fill in \"Zip\" field. ";

			if (strlen($strErrorTmp) <= 0)
			{
				$ret_var = "";

				$AMT = $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"];
				if ($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"] != "USD")
				{
					$AMT = CCurrencyRates::ConvertCurrency($AMT, $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"], "USD");

					$additor = 1;
					for ($i = 0; $i < SALE_VALUE_PRECISION; $i++)
						$additor = $additor / 10;

					$AMT_tmp = round($AMT, SALE_VALUE_PRECISION);
					while ($AMT_tmp < $AMT)
						$AMT_tmp = round($AMT_tmp + $additor, SALE_VALUE_PRECISION);

					$AMT = $AMT_tmp;
				}
				$AMT = str_replace(",", ".", $AMT);
				$cardExp = $cardexp1.$cardexp2;

				$parms  = "ACCT=".urlencode($cardnum);	// Credit card number
				$parms .= "&CVV2=".urlencode($cvv2);		// CVV2
				$parms .= "&AMT=".urlencode($AMT);						// Amount (US Dollars)
				$parms .= "&EXPDATE=".$cardExp;			// Expiration date
				$parms .= "&PARTNER=".urlencode($PF_PARTNER);		// Partner
				$parms .= "&PWD=".urlencode($PF_PWD);					// Password
				$parms .= "&TENDER=C";						// ...
				$parms .= "&TRXTYPE=S";						// Kind of transaction: Sale
				$parms .= "&USER=".urlencode($PF_USER);				// Login ID
				$parms .= "&VENDOR=".urlencode($PF_USER);			// Vendor ID
				$parms .= "&ZIP=".urlencode($zipcode);	// Zip
				$parms .= "&STREET=".urlencode($address1);	// Address
				$parms .= "&COMMENT1=".$ORDER_ID;
				$parms .= "&COMMENT2=".urlencode($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT"]);

				$ret_com = "$strExePath $PF_HOST $PF_PORT \"$parms\" 30";

				putenv("PFPRO_CERT_PATH=".$PFPRO_CERT_PATH);

				exec($ret_com, $arOutput, $ret_var);

				$strOutput = $arOutput[0];
				parse_str($strOutput, $arResult);

				if (is_array($arResult) && strlen($arResult["RESULT"])>0)
				{
					$arFields = array(
							"PS_STATUS" => (($arResult["RESULT"]==0) ? "Y" : "N"),
							"PS_STATUS_CODE" => $arResult["RESULT"],
							"PS_STATUS_DESCRIPTION" => $arResult["RESPMSG"]." - ".$arResult["PREFPSMSG"],
							"PS_STATUS_MESSAGE" => $arResult["PNREF"],
							"PS_RESPONSE_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG)))
						);

					CSaleOrder::Update($ORDER_ID, $arFields);

					$arResult["RESULT"] = IntVal($arResult["RESULT"]);
					if ($arResult["RESULT"]==0)
						$bNonePay = False;
					else
					{
						if ($arResult["RESULT"]<0)
						{
							$strErrorTmp.= "Communication Error: [".$arResult["RESULT"]."] ".$arResult["RESPMSG"]." - ".$arResult["PREFPSMSG"].". ";
						}
						elseif ($arPaySysRes_tmp["RESULT"]==125)
						{
							$strErrorTmp.= "Your payment is declined by Fraud Service. Please contact us to make payment. ";
						}
						elseif ($arResult["RESULT"]==126)
						{
							$strErrorTmp.= "Your payment is under review by Fraud Service. We contact you in 48 hours to get more specific information. ";
						}
						elseif (is_set($arErrorCodes, $arResult["RESULT"]))
						{
							$strErrorTmp.= $arErrorCodes[$arResult["RESULT"]].". ";
						}
						else
						{
							$strErrorTmp.= "Unknown error. ";
						}
					}
				}
				else
					$strErrorTmp.= "Response error. ";
			}
		}
		//***************************************************
		//***  END ACTION  **********************************
		//***************************************************

		if (!$bNonePay)
		{
			?>
			<font class="text" color="#009900"><b>
			Thank you for purchasing!<br>
			You have been billed <?echo SaleFormatCurrency($AMT, "USD") ?></b>
			</font>
			<?
		}
		else
		{
			if (strlen($strErrorTmp) > 0)
				echo "<font color=\"#FF0000\"><b>".$strErrorTmp."</b></font><br>";

			$noc_def = CSalePaySystemAction::GetParamValue("NOC");
			$address1_def = CSalePaySystemAction::GetParamValue("ADDRESS");
			$zipcode_def = CSalePaySystemAction::GetParamValue("ZIP");
			?>
			<form method="post" action="">
				<input type="hidden" name="CurrentStep" value="<?= IntVal($GLOBALS["CurrentStep"]) ?>">
				<input type="hidden" name="ORDER_ID" value="<?= $ORDER_ID ?>">
				<input type="hidden" name="GetPayRes" value="YES">
				<table>
					<tr>
						<td><font class="text">Credit Card Number</font></td>
						<td><input class="inputtext" type="text" name="cardnum" value="<?= htmlspecialchars($cardnum) ?>" size="35"></td>
					</tr>
					<tr>
						<td><font class="text">CVV2</font></td>
						<td><input type="text" class="inputtext" name="cvv2" value="<?= htmlspecialchars($cvv2) ?>" size="5"></td>
					</tr>
					<tr>
						<td><font class="text">Expiration Date&nbsp;&nbsp;(MM/YY)</font></td>
						<td>
							<select name="cardexp1" class="inputselect">
								<option value=""> </option>
								<?
								for ($i = 1; $i <= 12; $i++)
								{
									$val = (($i < 10) ? "0" : "").$i;
									?>
									<option value="<?= $val ?>" <?if ($cardexp1 == $val) echo "selected";?>><?= $val ?></option>
									<?
								}
								?>
							</select>
							<select name="cardexp2" class="inputselect">
								<option value=""> </option>
								<?
								for ($i = 4; $i <= 11; $i++)
								{
									$val = (($i < 10) ? "0" : "").$i;
									?>
									<option value="<?= $val ?>" <?if ($cardexp2 == $val) echo "selected";?>><?= $val ?></option>
									<?
								}
								?>
							</SELECT>
						</td>
					</tr>
					<tr>
						<td><font class="text">Cardholder</font></td>
						<td><input type="text" class="inputtext" name="noc" value="<?echo (strlen($noc) > 0) ? $noc : $noc_def ?>"></td>
					</tr>
					<tr>
						<td><font class="text">Address</font></td>
						<td><input type="text" class="inputtext" name="address1" value="<?echo (strlen($address1) > 0) ? $address1 : $address1_def ?>"></td>
					</tr>
					<tr>
						<td><font class="text">Zip</font></td>
						<td><input type="text" class="inputtext" name="zipcode" value="<?echo (strlen($zipcode) > 0) ? $zipcode : $zipcode_def ?>"></td>
					</tr>
				</table>
				<input type="submit" value="Proceed" class="inputbutton">
			</form>
			<?
		}
		?>
	</td>
  </tr>
</table>