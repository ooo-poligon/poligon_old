<?

$productProps = CCatalogProduct::GetByID($arResult["ID"]);
//var_dump($arResult["ID"]);
if($productProps["QUANTITY"] == 0 && $arResult["PROPERTIES"]["srok"]["VALUE"])
	$onStore = "���� ��������: <br/>{$arResult["PROPERTIES"]["srok"]["VALUE"]}";
elseif($productProps["QUANTITY"]<10 && $productProps["QUANTITY"] > 0)
	$onStore = "�� ������: &lt; 10 ��.";
elseif($productProps["QUANTITY"]<100  && $productProps["QUANTITY"] >= 10)
	$onStore = "�� ������: &lt; 100 ��.";
elseif($productProps["QUANTITY"]<1000  && $productProps["QUANTITY"] >= 100)
	$onStore = "�� ������: &lt; 1000 ��.";
elseif($productProps["QUANTITY"]>=1000 && $productProps["QUANTITY"] >= 100)
	$onStore = "�� ������: {$productProps["QUANTITY"]} ��.";
else
	$onStore = "�������� ������� � ����� �������� �� �������� <br/>(812)325-42-20";

$PRICE_1 = getPrice($arResult["PROPERTIES"]["BASE"]["VALUE"], array(0, ',', ' '));

$PRICE_10 = getPrice($arResult["PROPERTIES"]["RETAIL"]["VALUE"], array(0, ',', ' '));
$PRICE_50 = getPrice($arResult["PROPERTIES"]["WHOLESALE"]["VALUE"], array(0, ',', ' '));
$PRICE_SPECIAL = getPrice($arResult["PROPERTIES"]["SPECIAL"]["VALUE"], array(0, ',', ' '));


$price = "<span>�������: <strong>{$PRICE_1}</strong></span><br/>";

if($PRICE_SPECIAL > 0) {
	$price = "<span><strike>�������: <strong>{$PRICE_1}</strong></strike></span><br/><span>�����������: <strong>{$PRICE_SPECIAL}</strong></span><br/>";
}

$order = "<br/><a href='#orderPopup' style='float: center; color: red;'>��������</a>";
if($price || $onStore){
	print '
	'.$price;
}