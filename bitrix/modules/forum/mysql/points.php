<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/general/points.php");

/**********************************************************************/
/************** POINTS ************************************************/
/**********************************************************************/
class CForumPoints extends CAllForumPoints
{
	function Add($arFields)
	{
		global $DB;

		if (!CForumPoints::CheckFields("ADD", $arFields))
			return false;

		$arInsert = $DB->PrepareInsert("b_forum_points", $arFields);

		$strSql =
			"INSERT INTO b_forum_points(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$ID = IntVal($DB->LastID());

		for ($i = 0; $i<count($arFields["LANG"]); $i++)
		{
			$arInsert = $DB->PrepareInsert("b_forum_points_lang", $arFields["LANG"][$i]);
			$strSql =
				"INSERT INTO b_forum_points_lang(POINTS_ID, ".$arInsert[0].") ".
				"VALUES(".$ID.", ".$arInsert[1].")";
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $ID;
	}

	function Update($ID, $arFields)
	{
		global $DB;
		$ID = IntVal($ID);
		if ($ID<=0) return False;

		if (!CForumPoints::CheckFields("UPDATE", $arFields, $ID))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_forum_points", $arFields);
		$strSql = "UPDATE b_forum_points SET ".$strUpdate." WHERE ID = ".$ID;
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if (is_set($arFields, "LANG"))
		{
			$DB->Query("DELETE FROM b_forum_points_lang WHERE POINTS_ID = ".$ID."");

			for ($i = 0; $i<count($arFields["LANG"]); $i++)
			{
				$arInsert = $DB->PrepareInsert("b_forum_points_lang", $arFields["LANG"][$i]);
				$strSql =
					"INSERT INTO b_forum_points_lang(POINTS_ID, ".$arInsert[0].") ".
					"VALUES(".$ID.", ".$arInsert[1].")";
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
		}

		return $ID;
	}
}



/**********************************************************************/
/************** POINTS2POST *******************************************/
/**********************************************************************/
class CForumPoints2Post extends CAllForumPoints2Post
{
	// Пересчет баллов пользователей не производится
	function Add($arFields)
	{
		global $DB;

		if (!CForumPoints2Post::CheckFields("ADD", $arFields))
			return false;

		$arInsert = $DB->PrepareInsert("b_forum_points2post", $arFields);

		$strSql =
			"INSERT INTO b_forum_points2post(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$ID = IntVal($DB->LastID());

		return $ID;
	}
}


/**********************************************************************/
/************** FORUM USER POINTS *************************************/
/**********************************************************************/
class CForumUserPoints extends CAllForumUserPoints
{
	function Add($arFields)
	{
		global $DB;

		if (!CForumUserPoints::CheckFields("ADD", $arFields))
			return false;

		$arInsert = $DB->PrepareInsert("b_forum_user_points", $arFields);

		$strDatePostField = "";
		$strDatePostValue = "";
		if (!is_set($arFields, "DATE_UPDATE"))
		{
			$strDatePostField .= ", DATE_UPDATE";
			$strDatePostValue .= ", ".$DB->GetNowFunction()."";
		}

		$strSql =
			"INSERT INTO b_forum_user_points(".$arInsert[0].$strDatePostField.") ".
			"VALUES(".$arInsert[1].$strDatePostValue.")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		// Обновим баллы у посетителя
		if (IntVal($arFields["TO_USER_ID"])>0)
		{
			$arUserFields = array();
			$arUserFields["POINTS"] = CForumUser::CountUserPoints($arFields["TO_USER_ID"]);

			$arUser = CForumUser::GetByUSER_ID($arFields["TO_USER_ID"]);
			if ($arUser)
			{
				$ID_tmp = IntVal($arUser["ID"]);
				CForumUser::Update($ID_tmp, $arUserFields);
			}
			else
			{
				$arUserFields["USER_ID"] = $arFields["TO_USER_ID"];
				$ID_tmp = CForumUser::Add($arUserFields);
			}
		}

		return True;
	}
}
?>