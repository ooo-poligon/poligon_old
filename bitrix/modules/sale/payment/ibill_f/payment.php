<?
$ORDER_ID = IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]);
?>
<table border="0" width="100%" cellpadding="2" cellspacing="2">
  <tr>
	<td CLASS="t3">
		iBill (Internet Billing Company, Ltd.) is the premier provider of turnkey 
		e-commerce solutions for leading businesses around the world. Recently acquired 
		by InterCept, Inc. (Nasdaq: ICPT), the company provides secure transaction 
		services that enable Web merchants to accept and process real-time payments 
		for goods and services purchased over the Internet. iBill also manages all 
		back-office functions including reporting, tracking, customer service and 
		sales transactions.
	</td>
  </tr>
  <tr>
	<td class="but2" align="center">
		<?
		function ParceCSVLineString($str_data)
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

		$strErrorTmp = "";
		$bNonePay = True;
		if (strlen($GetPayRes)>0)
		{
			if (strlen($cardnum)<=0)
				$strErrorTmp.= "Please fill in \"Credit Card Number\" field. ";
			if (strlen($cardexp1)<=0 || strlen($cardexp2)<=0)
				$strErrorTmp.= "Please fill in \"Expiration Date\" field. ";
			if (strlen($noc)<=0)
				$strErrorTmp.= "Please fill in \"Cardholder\" field. ";
			if (strlen($address1)<=0)
				$strErrorTmp.= "Please fill in \"Address\" field. ";
			if (strlen($zipcode)<=0)
				$strErrorTmp.= "Please fill in \"Zip\" field. ";

			if (strlen($strErrorTmp)<=0)
			{
				$sHost = "secure.ibill.com";
				$sUrl = "/cgi-win/ccard/tpcard.exe";
				$cardexp = $cardexp1.$cardexp2;
				$cardnum = str_replace(" ", "", $cardnum);
				$strACCOUNT = CSalePaySystemAction::GetParamValue("SHOP_ACCOUNT");
				$strPASSWORD = CSalePaySystemAction::GetParamValue("SHOP_PASSWORD");
				$sVars = "reqtype=authorize&account=".urlencode($strACCOUNT)."&password=".urlencode($strPASSWORD)."&saletype=sale&cardnum=".urlencode($cardnum)."&cardexp=".urlencode($cardexp)."&noc=".urlencode($noc)."&address1=".urlencode($address1)."&zipcode=".urlencode($zipcode)."&amount=".urlencode($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"])."&crefnum=".urlencode("Invoice_".$ORDER_ID);
				$sResult = QueryGetData($sHost, 80, $sUrl, $sVars, $errno, $errstr, "POST");

				$arResCSV = ParceCSVLineString($sResult);
				if (is_array($arResCSV))
				{
					$arFields = array(
							"PS_STATUS" => (($arResCSV[0]!="declined")?"Y":"N"),
							"PS_STATUS_CODE" => $arResCSV[0],
							"PS_STATUS_DESCRIPTION" => $arResCSV[1],
							"PS_STATUS_MESSAGE" => $arResCSV[4],
							"PS_RESPONSE_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG)))
						);

					CSaleOrder::Update($ORDER_ID, $arFields);

					if ($arResCSV[0]=="authorized")
						$bNonePay = False;
					else
						$strErrorTmp.= "Response error: ".$arResCSV[1].". ";
				}
				else
					$strErrorTmp.= "Response error. ";
			}
		}

		if (!$bNonePay)
		{
			?>
			<font color="#009900"><b>
			Thank you for purchasing!<br>
			You have been billed $<?= $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"] ?>.</b>
			</font>
			<?
		}
		else
		{
			if (strlen($strErrorTmp) > 0)
				echo "<font color=\"#FF0000\"><b>".$strErrorTmp."</b></font><br>";
			?>
			<form method="post" action="">
				<input type="hidden" name="CurrentStep" value="<?= IntVal($GLOBALS["CurrentStep"]) ?>">
				<input type="hidden" name="ORDER_ID" value="<?= $ORDER_ID ?>">
				<input type="hidden" name="GetPayRes" value="YES">
				<table>
				  <tr>
					<td>Credit Card Number</td>
					<td><input type="text" name="cardnum" value="<?= htmlspecialchars($cardnum) ?>"></td>
				  </tr>
				  <tr>
					<td>Expiration Date&nbsp;&nbsp;(MM/YY)</td>
					<td>
						<select name="cardexp1">
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
						<select name="cardexp2">
							<option value=""> </option>
							<?
							for ($i = 3; $i <= 11; $i++)
							{
								$val = (($i < 10) ? "0" : "").$i;
								?>
								<option value="<?= $val ?>" <?if ($cardexp2 == $val) echo "selected";?>><?= $val ?></option>
								<?
							}
							?>
						</select>
					</td>
				  </tr>
				  <tr>
					<td>Cardholder</td>
					<td><input type="text" name="noc" value="<?= htmlspecialchars($noc) ?>"></td>
				  </tr>
				  <tr>
					<td>Address</td>
					<td><input type="text" name="address1" value="<?= htmlspecialchars($address1) ?>"></td>
				  </tr>
				  <tr>
					<td>Zip</td>
					<td><input type="text" name="zipcode" value="<?= htmlspecialchars($zipcode) ?>"></td>
				  </tr>
				</table>
				<input type=submit>
			</form>
			<?
		}
		?>
	</td>
  </tr>
</table>