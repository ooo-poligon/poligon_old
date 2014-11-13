<?
include(GetLangFileName(dirname(__FILE__)."/", "/assist.php"));

$SERVER_NAME_tmp = "";
if (defined("SITE_SERVER_NAME"))
	$SERVER_NAME_tmp = SITE_SERVER_NAME;
if (strlen($SERVER_NAME_tmp)<=0)
	$SERVER_NAME_tmp = COption::GetOptionString("main", "server_name", "");
?>
<FORM ACTION="https://secure.assist.ru/shops/cardpayment.cfm" METHOD="POST" target="_blank">
<font class="tablebodytext">
<?echo GetMessage("SASP_PROMT")?><br>
<?echo GetMessage("SASP_ACCOUNT_NO")?> <?= IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]).GetMessage("SASP_ORDER_FROM").htmlspecialcharsEx($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT"]) ?><br>
<?echo GetMessage("SASP_ORDER_SUM")?> <b><?echo SaleFormatCurrency($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"], $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"]) ?></b><br>
<br>
<INPUT TYPE="HIDDEN" NAME="Shop_IDP" VALUE="<?= htmlspecialchars(CSalePaySystemAction::GetParamValue("SHOP_IDP")) ?>">
<INPUT TYPE="HIDDEN" NAME="Order_IDP" VALUE="<?= IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]) ?>">
<INPUT TYPE="HIDDEN" NAME="Subtotal_P" VALUE="<?= htmlspecialchars(str_replace(",", ".", $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"])) ?>">
<INPUT TYPE="HIDDEN" NAME="Delay" VALUE="0">
<INPUT TYPE="HIDDEN" NAME="Language" VALUE="0">
<INPUT TYPE="HIDDEN" NAME="URL_RETURN_OK" VALUE="http://<?echo $SERVER_NAME_tmp ?>">
<INPUT TYPE="HIDDEN" NAME="URL_RETURN_NO" VALUE="http://<?echo $SERVER_NAME_tmp ?>">
<INPUT TYPE="HIDDEN" NAME="Currency" VALUE="<?=(($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"] == "RUB") ? "RUR" :htmlspecialchars($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"])) ?>">
<INPUT TYPE="HIDDEN" NAME="Comment" VALUE="Invoice <?= htmlspecialchars($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]." (".$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT"].")") ?>">
<INPUT TYPE="HIDDEN" NAME="LastName" VALUE="<?= htmlspecialchars(CSalePaySystemAction::GetParamValue("LAST_NAME")) ?>">
<INPUT TYPE="HIDDEN" NAME="FirstName" VALUE="<?= htmlspecialchars(CSalePaySystemAction::GetParamValue("FIRST_NAME")) ?>">
<INPUT TYPE="HIDDEN" NAME="MiddleName" VALUE="<?= htmlspecialchars(CSalePaySystemAction::GetParamValue("MIDDLE_NAME")) ?>">
<INPUT TYPE="HIDDEN" NAME="Email" VALUE="<?= htmlspecialchars(CSalePaySystemAction::GetParamValue("EMAIL")) ?>">
<INPUT TYPE="HIDDEN" NAME="Address" VALUE="<?= htmlspecialchars(CSalePaySystemAction::GetParamValue("ADDRESS")) ?>">
<INPUT TYPE="HIDDEN" NAME="Phone" VALUE="<?= htmlspecialchars(CSalePaySystemAction::GetParamValue("PHONE")) ?>">

<INPUT TYPE="HIDDEN" NAME="IsFrame" VALUE="0">

<?if ($valTmp = CSalePaySystemAction::GetParamValue("DEMO")):?>
<INPUT TYPE="HIDDEN" NAME="DemoResult" VALUE="<?= htmlspecialchars($valTmp) ?>">
<?endif;?>

<INPUT TYPE="HIDDEN" NAME="CardPayment" VALUE="<?echo (IntVal(CSalePaySystemAction::GetParamValue("PAYMENT_CardPayment")) == 1) ? 1 : 0?>">
<INPUT TYPE="HIDDEN" NAME="WalletPayment" VALUE="<?echo (IntVal(CSalePaySystemAction::GetParamValue("PAYMENT_WalletPayment")) == 1) ? 1 : 0?>">
<INPUT TYPE="HIDDEN" NAME="WebMoneyPayment" VALUE="<?echo (IntVal(CSalePaySystemAction::GetParamValue("PAYMENT_WebMoneyPayment")) == 1) ? 1 : 0?>">
<INPUT TYPE="HIDDEN" NAME="EPortPayment" VALUE="<?echo (IntVal(CSalePaySystemAction::GetParamValue("PAYMENT_EPortPayment")) == 1) ? 1 : 0?>">
<INPUT TYPE="HIDDEN" NAME="KreditPilotPayment" VALUE="<?echo (IntVal(CSalePaySystemAction::GetParamValue("PAYMENT_KreditPilotPayment")) == 1) ? 1 : 0?>">
<INPUT TYPE="HIDDEN" NAME="PayCashPayment" VALUE="<?echo (IntVal(CSalePaySystemAction::GetParamValue("PAYMENT_PayCashPayment")) == 1) ? 1 : 0?>">

<INPUT TYPE="SUBMIT" NAME="Submit" VALUE="<?echo GetMessage("SASP_ACTION")?>">
</font>
</form>

<p align="justify"><font class="tablebodytext"><b><?echo GetMessage("SASP_NOTES_TITLE")?></b></font></p>
<p align="justify"><font class="tablebodytext"><?echo GetMessage("SASP_NOTES")?></font></p>
<p align="justify"><font class="tablebodytext"><b><?echo GetMessage("SASP_NOTES_TITLE1")?></b></font></p>
<p align="justify"><font class="tablebodytext"><?echo GetMessage("SASP_NOTES1")?></font></p>
