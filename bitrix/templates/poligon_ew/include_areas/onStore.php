<?
$price = $onStore = null;
$productProps = CCatalogProduct::GetByID($arResult["ID"]);
var_dump($arResult["PROPERTIES"]["BASE"]["VALUE"]);
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

/* ��� ������������� */	
/*if($PRICE_1 == 0 && $PRICE_10 > 0){ // �������� ������ ������� �������� �� 10 ����. ������� ������� ������ 
	$singleArt = substr($arResult["PROPERTIES"]["article"]["VALUE"], 0, -1);
	$singleArr = CIBlockElement::GetList(
		array(), 
		array(
			"PROPERTY_article" => $singleArt));
	$singleItem = $singleArr->GetNext();
}elseif($PRICE_1 > 0){ // ���� ������� ����, ������ ����� �������� ������������� �� 10
	$isSingle = true;
	$tennerArt = $arResult["PROPERTIES"]["article"]["VALUE"]."%";
	$tennerArr = CIBlockElement::GetList(
		array("name"=>"desc"), 
		array(
			"PROPERTY_article" => $tennerArt));
	$tennerItem = $tennerArr->GetNext();
	//if($tennerItem["PROPERTY_ARTICLE_VALUE"] != $arResult["PROPERTIES"]["article"]["VALUE"]){ // ������� ������������� 10
	//$tennerItemProps = GetIBlockElement($singleItem["ID"]);
}
$banner_for_50 = "<span>���� �� 50+: <b>�������</b></span><br/>";

if($singleItem){ // ������������� ������, � ���� ������� ������
	$singleProps = GetIBlockElement($singleItem["ID"]);
	if($singleProps["PROPERTIES"]["BASE"]["VALUE"] > 0)
		$price .= "<span>���� �� 1+: <strong>".getPrice($singleProps["PROPERTIES"]["BASE"]["VALUE"])."</strong>
	(���. <a title='������������� 1 �����' href='{$singleProps["DETAIL_PAGE_URL"]}'>{$singleProps["PROPERTIES"]["article"]["VALUE"]}</a>)</span><br/>";
	if($PRICE_10 > 0)
		$price .= "<span>���� �� 10+: <strong>{$PRICE_10}</strong> (��. 10 ����)</span><br/>";
	if($PRICE_50 > 0)
		$price .= $banner_for_50;
}elseif($tennerItem){ // ������� ��������, � ���� ������ 10
	$tennerProps = GetIBlockElement($tennerItem["ID"]);
	$price .= "<span>���� �� 1+: <strong>{$PRICE_1}</strong></span><br/>";
	//if($PRICE_10 > 0){
		//$price .= "<span>���� �� 10+: {$PRICE_10}</span><br/>";
	if($tennerProps["PROPERTIES"]["article"]["VALUE"] != $arResult["PROPERTIES"]["article"]["VALUE"]){ // ������� ������������� 10
		$price .= "<span>���� �� 10+: <strong>".getPrice($tennerProps["PROPERTIES"]["RETAIL"]["VALUE"])."</strong> (���. <a title='������������� 10 ����' href='{$tennerProps["DETAIL_PAGE_URL"]}'>{$tennerProps["PROPERTIES"]["article"]["VALUE"]}</a>)</span><br/>";
	}elseif($PRICE_10 > 0){
		$price .= "<span>���� �� 10+: <strong>{$PRICE_10}</strong></span><br/>";
	}
	if($PRICE_50 > 0)
		$price .= $banner_for_50;
}elseif($isSingle){ // ������ ������� �������
	$price .= "<span>���� �� 1+: <strong>{$PRICE_1}</strong></span><br/>";
	if($PRICE_10 > 0)
		$price .= "<span>���� �� 10+: <strong>{$PRICE_10}</strong></span><br/>";
	if($PRICE_50 > 0)
		$price .= $banner_for_50;
}elseif(!$isSingle){ // ����. 10, ��� �������� �������, ������ �! 
	if($PRICE_10 > 0)
		$price .= "<span>���� �� 10+: <strong>{$PRICE_10}</strong> (��. 10 ����)</span><br/>";
	if($PRICE_50 > 0)
		$price .= $banner_for_50;
}
*/

$price = "<span>�������: <strong>{$PRICE_1}</strong></span><br/>";

if($PRICE_SPECIAL > 0) {
	$price = "<span><strike>�������: <strong>{$PRICE_1}</strong></strike></span><br/><span>�����������: <strong>{$PRICE_SPECIAL}</strong></span><br/>";
}

$order = "<br/><a href='#orderPopup' style='float: center; color: red;'>��������</a>";
if($price || $onStore){
	print '
	'.$price.'
	'.$onStore.'
	'.$order;
}