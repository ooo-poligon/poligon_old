<p><font class="tableheadtext"><b>����� ��������:</b></font></p>
<p><font class="tablebodytext">
<?= htmlspecialchars(CSalePaySystemAction::GetParamValue("POST_ADDRESS")) ?>
</font></p>

<p><font class="tablebodytext"><b>���� � <?= IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]) ?> �� <?= htmlspecialchars($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_UPDATE"]) ?></b></font></p>

<p><font class="tablebodytext">����������: <?= htmlspecialcharsEx(CSalePaySystemAction::GetParamValue("PAYER_NAME")) ?><br>
����� � ������: <b><?= SaleFormatCurrency($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"], $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"]) ?></b>
</font></p>


<p><font class="tablebodytext">���� ������������ � ������� ���� ����.</font></p>
