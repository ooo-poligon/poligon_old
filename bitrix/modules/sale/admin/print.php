<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
$SALE_RIGHT = $APPLICATION->GetGroupRight("sale");
if ($SALE_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$ORDER_ID = intval($ORDER_ID);

function GetRealPath2Report($rep_name)
{
	$rep_file_name = $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/reports/".$rep_name;
	if (!file_exists($rep_file_name))
	{
		$rep_file_name = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/reports/".$rep_name;
		if (!file_exists($rep_file_name))
		{
			return "";
		}
	}
	return $rep_file_name;
}

if (CModule::IncludeModule("sale"))
{
	$db_order = CSaleOrder::GetList(Array("DATE_UPDATE"=>"DESC"), Array("ID"=>$ORDER_ID));
	if ($arOrder = $db_order->Fetch())
	{
		$rep_file_name = GetRealPath2Report($doc.".php");
		if (strlen($rep_file_name)<=0)
		{
			ShowError("PRINT TEMPLATE NOT FOUND");
			die();
		}
		$arOrderProps = array();
		$db_order_props = CSaleOrderPropsValue::GetList(($b="NAME"), ($o="ASC"), Array("ORDER_ID"=>$ORDER_ID));
		while ($ar_order_props = $db_order_props->Fetch())
		{
			$curKey = $ar_order_props["CODE"];
			if (strlen($curKey)<=0) $curKey = $ar_order_props["ID"];
			$arOrderProps[$curKey] = $ar_order_props["VALUE"];
		}
		$arBasketIDs = array();
		$arQuantities = array();
		$arBasketIDs_tmp = Split(",", $BASKET_IDS);
		$arQuantities_tmp = Split(",", $QUANTITIES);
		if (count($arBasketIDs_tmp)!=count($arQuantities_tmp)) die("INVALID PARAMS");
		for ($i = 0; $i < count($arBasketIDs_tmp); $i++)
		{
			if (IntVal($arBasketIDs_tmp[$i])>0 && IntVal($arQuantities_tmp[$i])>0)
			{
				$arBasketIDs[] = IntVal($arBasketIDs_tmp[$i]);
				$arQuantities[] = IntVal($arQuantities_tmp[$i]);
			}
		}
		include($rep_file_name);
	}
}
else
	ShowError("SALE MODULE IS NOT INSTALLED");
?>