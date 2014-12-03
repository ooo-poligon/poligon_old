<?
$price = $onStore = null;
$productProps = CCatalogProduct::GetByID($arResult["ID"]);
var_dump($arResult["PROPERTIES"]["BASE"]["VALUE"]);
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

/* про нормоупаковку */	
/*if($PRICE_1 == 0 && $PRICE_10 > 0){ // вероятно данная позиция продаётся по 10 штук. находим штучный аналог 
	$singleArt = substr($arResult["PROPERTIES"]["article"]["VALUE"], 0, -1);
	$singleArr = CIBlockElement::GetList(
		array(), 
		array(
			"PROPERTY_article" => $singleArt));
	$singleItem = $singleArr->GetNext();
}elseif($PRICE_1 > 0){ // есть штучная цена, значит стоит поискать нормоупаковку по 10
	$isSingle = true;
	$tennerArt = $arResult["PROPERTIES"]["article"]["VALUE"]."%";
	$tennerArr = CIBlockElement::GetList(
		array("name"=>"desc"), 
		array(
			"PROPERTY_article" => $tennerArt));
	$tennerItem = $tennerArr->GetNext();
	//if($tennerItem["PROPERTY_ARTICLE_VALUE"] != $arResult["PROPERTIES"]["article"]["VALUE"]){ // нашлась нормоупаковка 10
	//$tennerItemProps = GetIBlockElement($singleItem["ID"]);
}
$banner_for_50 = "<span>Цена за 50+: <b>звоните</b></span><br/>";

if($singleItem){ // нормоупаковка десять, и есть штучный аналог
	$singleProps = GetIBlockElement($singleItem["ID"]);
	if($singleProps["PROPERTIES"]["BASE"]["VALUE"] > 0)
		$price .= "<span>Цена за 1+: <strong>".getPrice($singleProps["PROPERTIES"]["BASE"]["VALUE"])."</strong>
	(арт. <a title='нормоупаковка 1 штука' href='{$singleProps["DETAIL_PAGE_URL"]}'>{$singleProps["PROPERTIES"]["article"]["VALUE"]}</a>)</span><br/>";
	if($PRICE_10 > 0)
		$price .= "<span>Цена за 10+: <strong>{$PRICE_10}</strong> (уп. 10 штук)</span><br/>";
	if($PRICE_50 > 0)
		$price .= $banner_for_50;
}elseif($tennerItem){ // штучная упаковка, и есть аналог 10
	$tennerProps = GetIBlockElement($tennerItem["ID"]);
	$price .= "<span>Цена за 1+: <strong>{$PRICE_1}</strong></span><br/>";
	//if($PRICE_10 > 0){
		//$price .= "<span>Цена за 10+: {$PRICE_10}</span><br/>";
	if($tennerProps["PROPERTIES"]["article"]["VALUE"] != $arResult["PROPERTIES"]["article"]["VALUE"]){ // нашлась нормоупаковка 10
		$price .= "<span>Цена за 10+: <strong>".getPrice($tennerProps["PROPERTIES"]["RETAIL"]["VALUE"])."</strong> (арт. <a title='нормоупаковка 10 штук' href='{$tennerProps["DETAIL_PAGE_URL"]}'>{$tennerProps["PROPERTIES"]["article"]["VALUE"]}</a>)</span><br/>";
	}elseif($PRICE_10 > 0){
		$price .= "<span>Цена за 10+: <strong>{$PRICE_10}</strong></span><br/>";
	}
	if($PRICE_50 > 0)
		$price .= $banner_for_50;
}elseif($isSingle){ // просто штучная позиция
	$price .= "<span>Цена за 1+: <strong>{$PRICE_1}</strong></span><br/>";
	if($PRICE_10 > 0)
		$price .= "<span>Цена за 10+: <strong>{$PRICE_10}</strong></span><br/>";
	if($PRICE_50 > 0)
		$price .= $banner_for_50;
}elseif(!$isSingle){ // норм. 10, без штучного аналога, едрить ё! 
	if($PRICE_10 > 0)
		$price .= "<span>Цена за 10+: <strong>{$PRICE_10}</strong> (уп. 10 штук)</span><br/>";
	if($PRICE_50 > 0)
		$price .= $banner_for_50;
}
*/

$price = "<span>Базовая: <strong>{$PRICE_1}</strong></span><br/>";

if($PRICE_SPECIAL > 0) {
	$price = "<span><strike>Базовая: <strong>{$PRICE_1}</strong></strike></span><br/><span>Специальная: <strong>{$PRICE_SPECIAL}</strong></span><br/>";
}

$order = "<br/><a href='#orderPopup' style='float: center; color: red;'>заказать</a>";
if($price || $onStore){
	print '
	'.$price.'
	'.$onStore.'
	'.$order;
}