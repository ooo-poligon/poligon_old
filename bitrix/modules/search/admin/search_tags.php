<?
define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
if(CModule::IncludeModule("search"))
{
	CUtil::decodeURIComponent($_REQUEST);
	if(!empty($_REQUEST["search"]))
	{
		$arResult = array();
		$order = CUserOptions::GetOption("search_tags", "order", "CNT");
		if($_REQUEST["order_by"]=="NAME")
		{
			$arOrder = array("NAME"=>"ASC");
			if($order != "NAME")
				CUserOptions::SetOption("search_tags", "order", "NAME");
		}
		else
		{
			$arOrder = array("CNT"=>"DESC", "NAME"=>"ASC");
			if($order != "CNT")
				CUserOptions::SetOption("search_tags", "order", "CNT");
		}
		$db_res = CSearchTags::GetList(
			array("NAME", "CNT"),
			array("TAG"=>$_REQUEST["search"], "SITE_ID"=>$_REQUEST["site_id"]),
			$arOrder,
		10);
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
	}
}
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_js.php");
?>