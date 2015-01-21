<?define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
// **************************************************************************************
if (CModule::IncludeModule("search")):
{
// **************************************************************************************
// **************************************************************************************
	if(!function_exists("__UnEscape"))
	{
		function __UnEscape(&$item, $key)
		{
			if(is_array($item))
				array_walk($item, '__UnEscape');
			else
			{
				if(strpos($item, "%u") !== false)
					$item = $GLOBALS["APPLICATION"]->UnJSEscape($item);
			}
		}
	}
// **************************************************************************************
	array_walk($_REQUEST, '__UnEscape');
	$arParams = array();
	$params = explode(",", $_REQUEST["params"]);
	foreach ($params as $param)
	{
		list($key, $val) = explode(":", $param);
		$arParams[$key] = $val;
	}
	if (intVal($arParams["pe"]) <= 0)
		$arParams["pe"] = 10;
	$arResult = array();
// **************************************************************************************
	if(!empty($_REQUEST["search"]))
	{
		if(strToLower($arParams["sort"]) == "name")
			$arOrder = array("NAME"=>"ASC", "CNT"=>"DESC");
		else
			$arOrder = array("CNT"=>"DESC", "NAME"=>"ASC");
		
		$arFilter = array("TAG"=>$_REQUEST["search"], "SITE_ID"=>SITE_ID);
		if (!empty($arParams["mid"]))
			$arFilter["MODULE_ID"] = $arParams["mid"];
		if (!empty($arParams["pm1"]))
			$arFilter["PARAM1"] = $arParams["pm1"];
		if (!empty($arParams["pm2"]))
			$arFilter["PARAM2"] = $arParams["pm2"];
			
		$db_res = CSearchTags::GetList(
			array("NAME", "CNT"),
			$arFilter,
			$arOrder,
			$arParams["pe"]);
		if($db_res && ($res = $db_res->getNext()))
		{
			do
			{
				$res = array(
					"NAME" => $res["NAME"],
					"CNT" => $res["CNT"],
				);
				$arResult[] = $res;
			} while($res = $db_res->getNext());
		}
		?><?=CUtil::PhpToJSObject($arResult)?><?
		require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
		die();
	}
}
endif;?>