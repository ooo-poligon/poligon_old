<?

$productProps = CCatalogProduct::GetByID($arResult["ID"]);
//var_dump($arResult["ID"]);
if($productProps["QUANTITY"] == 0 && $arResult["PROPERTIES"]["srok"]["VALUE"])
	$onStore = "Срок поставки: <br/>{$arResult["PROPERTIES"]["srok"]["VALUE"]}";
elseif($productProps["QUANTITY"]<10 && $productProps["QUANTITY"] > 0)
	$onStore = "На складе: &lt; 10 шт.";
elseif($productProps["QUANTITY"]<100  && $productProps["QUANTITY"] >= 10)
	$onStore = "На складе: &lt; 100 шт.";
elseif($productProps["QUANTITY"]<1000  && $productProps["QUANTITY"] >= 100)
	$onStore = "На складе: &lt; 1000 шт.";
elseif($productProps["QUANTITY"]>=1000 && $productProps["QUANTITY"] >= 100)
	$onStore = "На складе: {$productProps["QUANTITY"]} шт.";
else
	$onStore = "Уточните наличие и сроки поставки по телефону <br/>(812)325-42-20";

$PRICE_1 = getPrice($arResult["PROPERTIES"]["BASE"]["VALUE"], array(0, ',', ' '));

$PRICE_10 = getPrice($arResult["PROPERTIES"]["RETAIL"]["VALUE"], array(0, ',', ' '));
$PRICE_50 = getPrice($arResult["PROPERTIES"]["WHOLESALE"]["VALUE"], array(0, ',', ' '));
$PRICE_SPECIAL = getPrice($arResult["PROPERTIES"]["SPECIAL"]["VALUE"], array(0, ',', ' '));


$price = "<span>Базовая: <strong>{$PRICE_1}</strong></span><br/>";

if($PRICE_SPECIAL > 0) {
	$price = "<span><strike>Базовая: <strong>{$PRICE_1}</strong></strike></span><br/><span>Специальная: <strong>{$PRICE_SPECIAL}</strong></span><br/>";
}

$order = "<br/><a href='#orderPopup' style='float: center; color: red;'>заказать</a>";
if($price || $onStore){
	print '
	'.$price;
}