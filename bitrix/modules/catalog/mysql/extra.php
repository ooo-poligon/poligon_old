<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/general/extra.php");

class CExtra extends CAllExtra
{
	function Add($arFields)
	{
		global $DB;

		$arInsert = $DB->PrepareInsert("b_catalog_extra", $arFields);

		$strSql =
			"INSERT INTO b_catalog_extra(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		unset($GLOBALS["MAIN_EXTRA_LIST_CACHE"]);
		return true;
	}
}
?>