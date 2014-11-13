<?
$strMerchantID = CSalePaySystemAction::GetParamValue("SHOP_ACCOUNT");
$strMerchantName = CSalePaySystemAction::GetParamValue("SHOP_NAME");

$ORDER_ID = IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]);
?>
<div class="tablebodytext">
<p>
�� ������ �������� �� �����-����� &quot;�����������&quot; ����� �������������� ����� ��������� ������� <b>�����������</b>.<br><br>
C��� � <?= htmlspecialcharsEx($ORDER_ID." �� ".$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT"]) ?><br>
����� � ������ �� �����: <b><?= SaleFormatCurrency($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"], $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"])."&nbsp;"?></b>
</p>

<form method="post" action="https://www.impexbank.ru/servlets/SPCardPaymentServlet">
<input type="hidden" name="Order_ID" value="<?= $ORDER_ID ?>"><br>
<input type="hidden" name="Amount" value="<?= htmlspecialchars($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"]) ?>"><br>
<input type="hidden" name="Formtype" value="AuthForm">
<input type="hidden" name="Merchant_ID" value="<?= htmlspecialchars($strMerchantID) ?>">
<input type="hidden" name="Merchant_Name" value="<?= htmlspecialchars($strMerchantName) ?>">
<input type="hidden" name="Currency" value="<?= htmlspecialchars($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"]) ?>">
<input type="submit" value="��������">
</form>

<p>
<b>�������� ��������!</b><br><br>
��� ���������� �������� �������������� � �������������� ������ ��������� ������� �����������. 
��� ������, ����������� ��� ������������� �������, �������������� �������� ��������� �������� �����������.
</p>

</div>