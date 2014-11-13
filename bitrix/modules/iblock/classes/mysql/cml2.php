<?
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/classes/general/cml2.php");

class CIBlockXMLFile extends CAllIBlockXMLFile
{
	function CreateTemporaryTables()
	{
		global $DB;
		if(defined("MYSQL_TABLE_TYPE") && strlen(MYSQL_TABLE_TYPE) > 0)
		{
			$DB->Query("SET table_type = '".MYSQL_TABLE_TYPE."'", true);
		}
		return  $DB->Query("create table b_xml_tree
		(
			ID int(11) not null auto_increment,
			PARENT_ID int(11),
			LEFT_MARGIN int(11),
			RIGHT_MARGIN int(11),
			DEPTH_LEVEL int(11),
			NAME varchar(255),
			VALUE text,
			ATTRIBUTES text,
			PRIMARY KEY (ID)
		)
		");
	}

	function Add($arFields)
	{
		global $DB;

		$strSql1 = "PARENT_ID, LEFT_MARGIN, RIGHT_MARGIN, DEPTH_LEVEL, NAME";
		$strSql2 = $arFields["~PARENT_ID"].", ".$arFields["~LEFT_MARGIN"].", ".$arFields["~RIGHT_MARGIN"].", ".$arFields["~DEPTH_LEVEL"].", '".$DB->ForSQL($arFields["NAME"], 255)."'";

		if(array_key_exists("ATTRIBUTES", $arFields))
		{
			$strSql1 .= ", ATTRIBUTES";
			$strSql2 .= ", '".$DB->ForSQL($arFields["ATTRIBUTES"])."'";
		}

		if(array_key_exists("VALUE", $arFields))
		{
			$strSql1 .= ", VALUE";
			$strSql2 .= ", '".$DB->ForSQL($arFields["VALUE"])."'";
		}

		$strSql = "INSERT INTO b_xml_tree (".$strSql1.") VALUES (".$strSql2.")";

		$rs = $DB->Query($strSql);

		return $DB->LastID();
	}
}

?>