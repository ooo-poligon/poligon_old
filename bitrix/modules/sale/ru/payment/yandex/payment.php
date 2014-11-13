<?
$Sum = CSalePaySystemAction::GetParamValue("SHOULD_PAY");
$ShopID = CSalePaySystemAction::GetParamValue("SHOP_ID");
$orderNumber = CSalePaySystemAction::GetParamValue("ORDER_ID");
$customerNumber = CSalePaySystemAction::GetParamValue("USER_ID");
$orderDate = CSalePaySystemAction::GetParamValue("ORDER_DATE");
?>
<font class="tablebodytext">
Вы хотите оплатить через систему <b>Яндекс.Деньги</b>.<br /><br />
Сумма к оплате по счету: <b><?=$Sum?> р.</b><br />
<br />
<?if(strlen(CSalePaySystemAction::GetParamValue("IS_TEST")) > 0):
	?>
	<form name="ShopForm" action="http://demomoney.yandex.ru/select-wallet.xml" method="post" target="_blank">
	<input name="TargetCurrency" value="10643" type="hidden">
	<input name="CurrencyID" value="10643" type="hidden">
	<input name="wbp_InactivityPeriod" value="2" type="hidden">
	<input name="wbp_ShopAddress" value="democpp.paycash.ru:8528" type="hidden">
	<input name="wbp_ShopEncryptionKey" value="hAAAEicBAA05OBb9TaKVU87ecbcFhFuIzxM8SWKmo1uLUo7WNDUdu+DtFKWEIbYSqdLiEWp1FmjsTar8Slhv2e3wDoPztTbXV8pm1k8V+o92yatuRkKa85ftIDLqqDNuS1k1OK15hDQpJP79NNd9rZz/BHCH6Kz8g1IcMuFfArJ5HQSz6QmB" type="hidden">
	<input name="wbp_ShopKeyID" value="1541657491" type="hidden">
	<input name="wbp_Version" value="1.0" type="hidden">
	<input name="wbp_CorrespondentID" value="CC872881BE278F86D72E1708E16FC84A5B0DE78D" type="hidden">
	<input name="BankID" value="1003" type="hidden">
	<input name="TargetBankID" value="1003-10643" type="hidden">
	<input name="PaymentTypeCD" value="PC" type="hidden">
	<input name="ShowCaseID" value="1010" type="hidden">
<?else:
	?>
	<form name="ShopForm" action="http://money.yandex.ru/select-wallet.xml" method="post">
	<input type="hidden" name="CurrencyID" value="643">
	<input type="hidden" name="wbp_InactivityPeriod" value="2">
	<input type="hidden" name="wbp_ShopAddress" value="wn1.yamoney.ru:8828">
	<input type="hidden" name="wbp_ShopAdditionalAddresses" value="wn2.yamoney.ru:8828">
	<input type="hidden" name="wbp_ShopEncryptionKey" 
	value="hAAAEicBAHV6wr3pySqE3thhKHbjvyf4XCMxKc2nSj2u8K46i0dMIP8Wd2KJHkZuhGMWZGmYAp6wsb3XqZW5HKVpamQt+t9rwGNsSaVfeZb9DM5aodCpIMHhLA8gGPDIiG4+Q15X/7Zm3MJNGavZ8+eWAnlvS1M7c6eeLTNJ0CKIYd1yHXfU">
	<input type="hidden" name="wbp_ShopKeyID" value="4060341894">
	<input type="hidden" name="wbp_Version" value="1.0">
	<input type="hidden" name="wbp_CorrespondentID" 
	value="8994748E663DE6B3C68D2D9931B079C74789D4B4">
	<input type="hidden" name="BankID" value="1001">
	<input type="hidden" name="TargetBankID" value="1001">
	<input type="hidden" name="PaymentTypeCD" value="PC">
	<input type="hidden" name="ShowCaseID" value="1010">
<?endif;?>

<input name="ShopID" value="<?=$ShopID?>" type="hidden">
<input name="customerNumber" value="<?=$customerNumber?>" type="hidden">
<input name="orderNumber" value="<?=$orderNumber?>" type="hidden">
<input name="Sum" value="<?=$Sum?>" type="hidden">
<br />
Детали заказа:<br />
<input name="OrderDetails" value="заказ №<?=$orderNumber?> (<?=$orderDate?>)" type="hidden">
<br />
<input name="BuyButton" value="Оплатить" type="submit">

</font><p><font class="tablebodytext"><b>ВНИМАНИЕ!</b> Возврат средств по платежной системе Яндекс.Деньги - невозможен, пожалуйста, будьте внимательны при оплате заказа.</font></p>
</form>