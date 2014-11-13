<?
if ($bDoPayAction)
{
	$arErrorCodes = array(
		1 => "User authentication failed",
		2 => "Invalid tender. Your merchant bank account does not support the following credit card type that was submitted",
		3 => "Invalid transaction type. Transaction type is not appropriate for this transaction. For example, you cannot credit an authorization-only transaction",
		4 => "Invalid amount",
		5 => "Invalid merchant information. Processor does not recognize your merchant account information. Contact your bank account acquirer to resolve this problem",
		7 => "Field format error. Invalid information entered",
		8 => "Not a transaction server",
		9 => "Too many parameters or invalid stream",
		10 => "Too many line items",
		11 => "Client time-out waiting for response",
		12 => "Declined. Check the credit card number and transaction information to make sure they were entered correctly. If this does not resolve the problem, have the customer call the credit card issuer to resolve",
		13 => "Referral. Transaction was declined but could be approved with a verbal authorization from the bank that issued the card. Submit a manual Voice Authorization transaction and enter the verbal auth code",
		19 => "Original transaction ID not found. The transaction ID you entered for this transaction is not valid",
		20 => "Cannot find the customer reference number",
		22 => "Invalid ABA number",
		23 => "Invalid account number. Check credit card number and re-submit",
		24 => "Invalid expiration date. Check and re-submit",
		25 => "Transaction type not mapped to this host",
		26 => "Invalid vendor account",
		27 => "Insufficient partner permissions",
		28 => "Insufficient user permissions",
		50 => "Insufficient funds available",
		99 => "General error",
		100 => "Invalid transaction returned from host",
		101 => "Time-out value too small",
		102 => "Processor not available",
		103 => "Error reading response from host",
		104 => "Timeout waiting for processor response. Try your transaction again",
		105 => "Credit error. Make sure you have not already credited this transaction, or that this transaction ID is for a creditable transaction. (For example, you cannot credit an authorization)",
		106 => "Host not available",
		107 => "Duplicate suppression time-out",
		108 => "Void error. Make sure the transaction ID entered has not already been voided. If not, then look at the Transaction Detail screen for this transaction to see if it has settled. (The Batch field is set to a number greater than zero if the transaction has been settled). If the transaction has already settled, your only recourse is a reversal (credit a payment or submit a payment for a credit)",
		109 => "Time-out waiting for host response",
		111 => "Capture error. Only authorization transactions can be captured",
		112 => "Failed AVS check. Address and ZIP code do not match. An authorization may still exist on the cardholder's account",
		113 => "Cannot exceed sales cap. For ACH transactions only",
		114 => "CVV2 Mismatch. An authorization may still exist on the cardholder's account",
		1000 => "Generic host error"
		);

	$strPaySysError = "";
	$strPaySysWarning = "";

	$cc_cardnum = Trim($cc_cardnum);
	$cc_cardnum = preg_replace("#[\D]#i", "", $cc_cardnum);
	if (strlen($cc_cardnum)<=0)
		$strPaySysError .= "\"Credit Card Number\" field is required.<br>";
	if (strlen($cc_cvv2)<=0)
		$strPaySysError .= "\"CVV2\" field is required.<br>";
	if (strlen($cc_cardexp1)<=0 || strlen($cc_cardexp2)<=0)
		$strPaySysError .= "\"Expiration Date\" field is required.<br>";
	if (strlen($cc_noc)<=0)
		$strPaySysError .= "\"Cardholder\" field is required.<br>";

	$db_order_props = CSaleOrderProps::GetList(($b="NAME"), ($o="ASC"), Array("PERSON_TYPE_ID"=>$PERSON_TYPE));
	while ($ar_order_props = $db_order_props->Fetch())
	{
		// These codes should be changed to match the read codes
		// --->
		if ($ar_order_props["CODE"]=="street_address_billing")
			$cc_address = ${"ORDER_PROP_".$ar_order_props["ID"]};
		elseif ($ar_order_props["CODE"]=="zip_billing")
			$cc_zipcode = ${"ORDER_PROP_".$ar_order_props["ID"]};
		// <---
	}

	if (strlen($cc_address)<=0)
		$strPaySysError .= "\"Address\" field is required.<br>";
	if (strlen($cc_zipcode)<=0)
		$strPaySysError .= "\"Zip\" field is required.<br>";

	if (strlen($strPaySysError)<=0)
	{
		$ret_var = "";

		// These variables should be changed to match real parameters
		// --->
		$PF_HOST = "test-payflow.verisign.com";
		$PF_PORT = 443;
		$PF_USER = "user";
		$PF_PWD = "password";
		$PF_PARTNER = "VeriSign";
		$strExePath = $_SERVER["DOCUMENT_ROOT"]."/verisign/win32/bin/pfpro.exe";
		$PFPRO_CERT_PATH = $_SERVER["DOCUMENT_ROOT"]."/verisign/win32/certs/";
		// <---

		$AMT = $ORDER_PRICE+$TAX_PRICE-$DISCOUNT_PRICE;
		if ($BASE_LANG_CURRENCY!="USD")
		{
			$AMT = Round(CCurrencyRates::ConvertCurrency($AMT, $BASE_LANG_CURRENCY, "USD"), 2);
		}
		$AMT = str_replace(",", ".", $AMT);
		$cc_cardExp = $cc_cardexp1.$cc_cardexp2;

		$parms  = "ACCT=".$cc_cardnum;			// Credit card number
		$parms .= "&CVV2=".$cc_cvv2;				// CVV2
		$parms .= "&AMT=".$AMT;						// Amount (US Dollars)
		$parms .= "&EXPDATE=".$cc_cardExp;		// Expiration date
		$parms .= "&PARTNER=".$PF_PARTNER;		// Partner
		$parms .= "&PWD=".$PF_PWD;					// Password
		$parms .= "&TENDER=C";						// ...
		$parms .= "&TRXTYPE=S";						// Kind of transaction: Sale
		$parms .= "&USER=".$PF_USER;				// Login ID
		$parms .= "&VENDOR=".$PF_USER;			// Vendor ID
		$parms .= "&ZIP=".$cc_zipcode;			// Zip
		$parms .= "&STREET=".$cc_address;		// Address

		$ret_com = "$strExePath $PF_HOST $PF_PORT \"$parms\" 30";

		putenv("PFPRO_CERT_PATH=".$PFPRO_CERT_PATH);

		$arOutput = array();
		exec($ret_com, $arOutput, $ret_var);

		$strOutput = $arOutput[0];
		$arPaySysRes_tmp = array();
		parse_str($strOutput, $arPaySysRes_tmp);

		if (is_array($arPaySysRes_tmp) && strlen($arPaySysRes_tmp["RESULT"])>0)
		{
			$arPaySysRes_tmp["RESULT"] = IntVal($arPaySysRes_tmp["RESULT"]);
			$arPaySysResult = array(
				"STATUS" => ($arPaySysRes_tmp["RESULT"]==0) ? "Y" : "N",
				"CODE" => $arPaySysRes_tmp["RESULT"],
				"DESCRIPTION" => $arPaySysRes_tmp["RESPMSG"]." - ".$arPaySysRes_tmp["PREFPSMSG"],
				"MESSAGE" => $arPaySysRes_tmp["PNREF"],
				"SUM" => $AMT,
				"CURRENCY" => "USD",
				"RESPONSE_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG)))
				);

			if ($arPaySysRes_tmp["RESULT"]!=0)
			{
				if ($arPaySysRes_tmp["RESULT"]<0)
				{
					$strPaySysError .= "Communication Error: [".$arPaySysRes_tmp["RESULT"]."] ".$arPaySysRes_tmp["RESPMSG"]." - ".$arPaySysRes_tmp["PREFPSMSG"].". ";
				}
				elseif ($arPaySysRes_tmp["RESULT"]==125)
				{
					$strPaySysError .= "Your payment is declined by Fraud Service. Please contact us to make payment. ";
				}
				elseif ($arPaySysRes_tmp["RESULT"]==126)
				{
					$strPaySysWarning .= "Your payment is under review by Fraud Service. We contact you in 48 hours to get more specific information. ";
				}
				elseif (is_set($arErrorCodes, $arPaySysRes_tmp["RESULT"]))
				{
					$strPaySysError .= $arErrorCodes[$arPaySysRes_tmp["RESULT"]].". ";
				}
				else
				{
					$strPaySysError .= "Unknown error. ";
				}
			}
		}
		else
		{
			$strPaySysError .= "Response error. ";
		}
	}
}
else
{
	$cc_noc_def_first_name = "";
	$cc_noc_def_last_name = "";
//	$cc_address_def = "";
//	$cc_zipcode_def = "";

	$db_order_props = CSaleOrderProps::GetList(($b="NAME"), ($o="ASC"), Array("PERSON_TYPE_ID"=>$PERSON_TYPE));
	while ($ar_order_props = $db_order_props->Fetch())
	{
		// These codes should be changed to match the read codes
		// --->
		if ($ar_order_props["CODE"]=="first_name")
			$cc_noc_def_first_name = ${"ORDER_PROP_".$ar_order_props["ID"]};
		elseif ($ar_order_props["CODE"]=="last_name")
			$cc_noc_def_last_name = ${"ORDER_PROP_".$ar_order_props["ID"]};
//		elseif ($ar_order_props["CODE"]=="street_address")
//			$cc_address_def = ${"ORDER_PROP_".$ar_order_props["ID"]};
//		elseif ($ar_order_props["CODE"]=="zip")
//			$cc_zipcode_def = ${"ORDER_PROP_".$ar_order_props["ID"]};
		// <---
	}

	$cc_noc_def = $cc_noc_def_first_name;
	if (strlen($cc_noc_def)>0 && strlen($cc_noc_def_last_name)>0) $cc_noc_def .= " ";
	$cc_noc_def .= $cc_noc_def_last_name;
	?>

	<tr valign="middle">
		<td class="tablebody" align="right">
			<font class="tablebodytext"><font color="#FF0000">*</font>Credit Card Number:</font>
		</td>
		<td class="tablebody">
			<input type="text" name="cc_cardnum" value="<?echo htmlspecialchars($cc_cardnum) ?>" size="35">
		</td>
	</tr>
	<tr>
		<td class="tablebody" align="right">
			<font class="tablebodytext">CVV2:</font>
		</td>
		<td class="tablebody">
			<input type="text" name="cc_cvv2" value="<?echo htmlspecialchars($cc_cvv2) ?>" size="5">
		</td>
	</tr>
	<tr>
		<td class="tablebody" align="right">
			<nobr><font class="tablebodytext"><font color="#FF0000">*</font>Expiration Date&nbsp;&nbsp;(MM/YY):</font></nobr>
		</td>
		<td class="tablebody">
			<SELECT NAME="cc_cardexp1">
				<option value=""> </option>
				<?
				for ($i = 1; $i<=12; $i++)
				{
					$val = (($i<10)?"0":"").$i;
					?>
					<option value="<?echo $val ?>" <?if ($cc_cardexp1==$val) echo "selected";?>><?echo $val ?></option>
					<?
				}
				?>
			</SELECT>
			<SELECT NAME="cc_cardexp2">
				<option value=""> </option>
				<?
				for ($i = 4; $i<=11; $i++)
				{
					$val = (($i<10)?"0":"").$i;
					?>
					<option value="<?echo $val ?>" <?if ($cc_cardexp2==$val) echo "selected";?>><?echo $val ?></option>
					<?
				}
				?>
			</SELECT>
		</td>
	</tr>
	<tr>
		<td class="tablebody" align="right">
			<font class="tablebodytext"><font color="#FF0000">*</font>Cardholder:</font>
		</td>
		<td class="tablebody">
			<input type="text" name="cc_noc" size="35" value="<?echo (strlen($cc_noc)>0) ? htmlspecialchars($cc_noc) : htmlspecialchars($cc_noc_def) ?>">
		</td>
	</tr>

	<!--<tr>
		<td class="tablehead" colspan="2">
			<font class="tableheadtext">Please enter the cardholder's address on the form if the cardholder's name is different from the name entered on the previous page.</font>
		</td>
	</tr>
	<tr>
		<td class="tablebody" align="right">
			<font class="tablebodytext">Address:</font>
		</td>
		<td class="tablebody">
			<input type="text" name="cc_address" value="<?echo (strlen($cc_address)>0) ? htmlspecialchars($cc_address) : htmlspecialchars($cc_address_def) ?>">
		</td>
	</tr>
	<tr>
		<td class="tablebody" align="right">
			<font class="tablebodytext">Zip:</font>
		</td>
		<td class="tablebody">
			<input type="text" name="cc_zipcode" value="<?echo (strlen($cc_zipcode)>0) ? htmlspecialchars($cc_zipcode) : htmlspecialchars($cc_zipcode_def) ?>">
		</td>
	</tr>-->
	<?
}
?>