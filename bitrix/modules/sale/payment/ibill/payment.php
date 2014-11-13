<table border="0" width="100%" cellpadding="2" cellspacing="2">
<FORM METHOD="POST" ACTION="https://secure.ibill.com/cgi-win/ccard/ccard.exe">
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
		$strLangCode = 1;
		if (LANGUAGE_ID == "ES")
			$strLangCode = 3;
		elseif (LANGUAGE_ID == "FR")
			$strLangCode = 4;
		elseif (LANGUAGE_ID == "DE")
			$strLangCode = 5;
		else
			$strLangCode = 1;
		?>
		<INPUT TYPE="HIDDEN" NAME="LANGUAGE" VALUE="<?echo $strLangCode ?>">
		<INPUT TYPE="HIDDEN" NAME="HELLOPAGE" VALUE="Default">
		<INPUT TYPE="HIDDEN" NAME="REQTYPE" VALUE="secure">
		<INPUT TYPE="hidden" NAME="RevSharerID" VALUE="">
		<INPUT TYPE="HIDDEN" NAME="ACCOUNT" VALUE="<?= htmlspecialcharsEx(CSalePaySystemAction::GetParamValue("ACCOUNT_NUM")) ?>">
		<input type="submit" name="submit" value="Pay" class="but2">
	</td>
  </tr>
</FORM>
</table>
