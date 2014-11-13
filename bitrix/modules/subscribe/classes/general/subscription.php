<?
IncludeModuleLangFile(__FILE__);

class CSubscriptionGeneral
{
	var $LAST_ERROR="";
	var $LAST_MESSAGE="";

	function GetList($aSort=Array(), $arFilter=Array())
	{
		global $DB;
		$arSqlSearch = Array();
		$strSqlSearch = "";
		if (is_array($arFilter))
		{
			foreach($arFilter as $key => $val)
			{
				if(!is_array($val))
				{
					if( (strlen($val) <= 0) || ($val === "NOT_REF") )
						continue;
				}
				switch(strtoupper($key))
				{
				case "ID":
					$arSqlSearch[] = GetFilterQuery("S.ID",$val,"N");
					break;
				case "EMAIL":
					$arSqlSearch[] = GetFilterQuery("S.EMAIL",$val,"Y",array("@", ".", "_"));
					break;
				case "UPDATE_1":
					$arSqlSearch[] = "S.DATE_UPDATE>=".$DB->CharToDateFunction($val);
					break;
				case "UPDATE_2":
					$arSqlSearch[] = "S.DATE_UPDATE<=".$DB->CharToDateFunction($val." 23:59:59");
					break;
				case "INSERT_1":
					$arSqlSearch[] = "S.DATE_INSERT>=".$DB->CharToDateFunction($val);
					break;
				case "INSERT_2":
					$arSqlSearch[] = "S.DATE_INSERT<=".$DB->CharToDateFunction($val." 23:59:59");
					break;
				case "USER":
					$arSqlSearch[] = GetFilterQuery("U.ID, U.LOGIN, U.NAME, U.LAST_NAME",$val);
					break;
				case "USER_ID":
					$arSqlSearch[] = GetFilterQuery("S.USER_ID",$val,"N");
					break;
				case "ANONYMOUS":
					$arSqlSearch[] = ($val=="Y") ? "(length(S.USER_ID)<=0 or S.USER_ID is null)" : "length(S.USER_ID)>0";
					break;
				case "CONFIRMED":
					$arSqlSearch[] = ($val=="Y") ? "S.CONFIRMED='Y'" : "S.CONFIRMED='N'";
					break;
				case "ACTIVE":
					$arSqlSearch[] = ($val=="Y") ? "S.ACTIVE='Y'" : "S.ACTIVE='N'";
					break;
				case "FORMAT":
					$arSqlSearch[] = ($val=="text") ? "S.FORMAT='text'" : "S.FORMAT='html'";
					break;
				case "RUBRIC":
				case "RUBRIC_MULTI":
				case "DISTRIBUTION":
					if(is_array($val))
						$val = implode(" | ",$val);
					if(strlen($val)>0)
					{
						$from1 = " INNER JOIN b_subscription_rubric SR ON (SR.SUBSCRIPTION_ID=S.ID) ";
						$arSqlSearch[] = GetFilterQuery("SR.LIST_RUBRIC_ID", $val, "N");
					}
					else
					{
						$arSqlSearch[] = " 1=0 ";
					}
					break;
				}
			}
		}

		$sOrder = "";
		$sort_keys = array_keys($aSort);
		for ($i=0; $i<count($sort_keys); $i++)
		{
			$ord = (strtoupper($aSort[$sort_keys[$i]]) <> "ASC"? "DESC":"ASC");
			switch (strtoupper($sort_keys[$i]))
			{
				case "ID":		$sOrder .= ", S.ID ".$ord; break;
				case "DATE_INSERT":	$sOrder .= ", S.DATE_INSERT ".$ord; break;
				case "DATE_UPDATE":	$sOrder .= ", S.DATE_UPDATE ".$ord; break;
				case "DATE_CONFIRM":	$sOrder .= ", S.DATE_CONFIRM ".$ord; break;
				case "ACT":		$sOrder .= ", S.ACTIVE ".$ord; break;
				case "CONF":		$sOrder .= ", S.CONFIRMED ".$ord; break;
				case "EMAIL":		$sOrder .= ", S.EMAIL ".$ord; break;
				case "FMT":		$sOrder .= ", S.FORMAT ".$ord; break;
				case "USER":		$sOrder .= ", S.USER_ID ".$ord; break;
				case "CONFIRM_CODE":	$sOrder .= ", S.CONFIRM_CODE ".$ord; break;
			}
		}
		if (strlen($sOrder)<=0)
		{
			$sOrder = "S.ID DESC";
		}
		$strSqlOrder = " ORDER BY ".TrimEx($sOrder,",");

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT
				S.ID, S.USER_ID, S.ACTIVE, S.EMAIL, S.FORMAT, S.CONFIRM_CODE, S.CONFIRMED,
				".$DB->DateToCharFunction("S.DATE_UPDATE")."	DATE_UPDATE,
				".$DB->DateToCharFunction("S.DATE_INSERT")."	DATE_INSERT,
				".$DB->DateToCharFunction("S.DATE_CONFIRM")."	DATE_CONFIRM,
				U.LOGIN											USER_LOGIN,
				U.NAME											USER_NAME,
				U.LAST_NAME										USER_LAST_NAME
			FROM
				b_subscription S
			$from1
			LEFT JOIN b_user U ON (S.USER_ID=U.ID)
			WHERE
			$strSqlSearch
			GROUP BY
				S.ID, S.USER_ID, S.ACTIVE, S.EMAIL, S.FORMAT, S.CONFIRM_CODE, S.CONFIRMED, S.DATE_CONFIRM, S.DATE_UPDATE, S.DATE_INSERT, U.LOGIN, U.NAME, U.LAST_NAME
			$strSqlOrder
			";
		//echo $strSql;
		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$res->is_filtered = (IsFiltered($strSqlSearch));
		return $res;
	}

	//list of subscribed categories
	function GetRubricList($ID)
	{
		global $DB;
		$ID = intval($ID);
		$strSql =
			"SELECT R.ID, R.NAME, R.SORT, R.LID, R.ACTIVE, R.VISIBLE ".
			"FROM b_list_rubric R, b_subscription_rubric SR ".
			"WHERE R.ID=SR.LIST_RUBRIC_ID AND SR.SUBSCRIPTION_ID='".$ID."' ".
			"ORDER BY R.LID, R.SORT, R.NAME ";
		return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}

	//array of subscribed categories
	function GetRubricArray($ID)
	{
		$ID = intval($ID);
		$aSubscrRub = array();
		if($ID>0)
		{
			$subscr_rub = CSubscription::GetRubricList($ID);
			while($subscr_rub_arr = $subscr_rub->Fetch())
				$aSubscrRub[] = $subscr_rub_arr["ID"];
		}
		return $aSubscrRub;
	}

	//subscription of current user from cookies
	function GetUserSubscription()
	{
		global $USER;
		$email_cookie = COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_SUBSCR_EMAIL";

		$subscr_EMAIL = (strlen($_COOKIE[$email_cookie]) > 0? $_COOKIE[$email_cookie] : $USER->GetParam("EMAIL"));
		if($subscr_EMAIL <> "")
		{
			$subscr = CSubscription::GetByEmail($subscr_EMAIL);
			if(($subscr_arr = $subscr->Fetch()))
				return $subscr_arr;
		}
		return array("ID"=>0, "EMAIL"=>"");
	}

	//get by ID
	function GetByID($ID)
	{
		global $DB;
		$ID = intval($ID);

		$strSql =
			"SELECT S.*, ".
			"	".$DB->DateToCharFunction("S.DATE_UPDATE", "FULL")." AS DATE_UPDATE, ".
			"	".$DB->DateToCharFunction("S.DATE_INSERT", "FULL")." AS DATE_INSERT, ".
			"	".$DB->DateToCharFunction("S.DATE_CONFIRM", "FULL")." AS DATE_CONFIRM ".
			"FROM b_subscription S ".
			"WHERE S.ID='".$ID."' ";

		return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}

	// deletion
	function Delete($ID)
	{
		global $DB;
		$ID = intval($ID);

		$DB->StartTransaction();

		if($DB->Query("DELETE FROM b_subscription_rubric WHERE SUBSCRIPTION_ID='".$ID."'", false, "File: ".__FILE__."<br>Line: ".__LINE__) &&
		($res = $DB->Query("DELETE FROM b_subscription WHERE ID='".$ID."' ", false, "File: ".__FILE__."<br>Line: ".__LINE__)))
		{
			$DB->Commit();
			return $res;
		}

		$DB->Rollback();
		return false;
	}

	//check fields before writing
	function CheckFields($arFields, $ID)
	{
		global $DB;

		$this->LAST_ERROR = "";
		$aMsg = array();

		if(is_set($arFields, "EMAIL"))
		{
			if(strlen($arFields["EMAIL"]) == 0 || !check_email($arFields["EMAIL"]))
				$aMsg[] = array("id"=>"EMAIL", "text"=>GetMessage("class_subscr_addr"));
			else
			{
				$res = $DB->Query("SELECT 'x' FROM b_subscription WHERE UPPER(EMAIL)=UPPER('".$DB->ForSql($arFields["EMAIL"])."') AND ID <> ".intval($ID), false, "File: ".__FILE__."<br>Line: ".__LINE__);
				if($res->Fetch())
					$aMsg[] = array("id"=>"EMAIL", "text"=>GetMessage("class_subscr_addr2"));
			}
		}
		if(is_set($arFields, "USER_ID"))
		{
			if(intval($arFields["USER_ID"]) > 0)
			{
				$res = $DB->Query("SELECT 'x' FROM b_user WHERE ID = ".intval($arFields["USER_ID"]), false, "File: ".__FILE__."<br>Line: ".__LINE__);
				if(!$res->Fetch())
					$aMsg[] = array("id"=>"USER_ID", "text"=>GetMessage("class_subscr_user"));
			}
		}

		if(!empty($aMsg))
		{
			$e = new CAdminException($aMsg);
			$GLOBALS["APPLICATION"]->ThrowException($e);
			$this->LAST_ERROR = $e->GetString();
			return false;
		}
		return true;
	}

	//link with categories
	function UpdateRubrics($ID, $aRubric, $SITE_ID=false)
	{
		global $DB;
		$ID = intval($ID);

		if($SITE_ID!==false)
		{
			$strSql = "
			SELECT sr.SUBSCRIPTION_ID,sr.LIST_RUBRIC_ID
			FROM
				b_subscription_rubric sr
				INNER JOIN b_list_rubric lr ON lr.ID=sr.LIST_RUBRIC_ID
			WHERE
				sr.SUBSCRIPTION_ID='".$ID."'
				AND lr.LID='".$DB->ForSql($SITE_ID)."'
			";
			$rs = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			while($ar=$rs->Fetch())
			{
				$strSql = "
				DELETE FROM b_subscription_rubric
				WHERE SUBSCRIPTION_ID=".intval($ar["SUBSCRIPTION_ID"])."
				AND LIST_RUBRIC_ID=".intval($ar["LIST_RUBRIC_ID"])."
				";
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
		}
		else
		{
			$DB->Query("DELETE FROM b_subscription_rubric WHERE SUBSCRIPTION_ID='".$ID."'", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		$sID = "0";
		if(is_array($aRubric))
			foreach($aRubric as $rub)
				$sID .= ",".intval($rub);
		$DB->Query(
			"INSERT INTO b_subscription_rubric (SUBSCRIPTION_ID, LIST_RUBRIC_ID) ".
			"SELECT '".$ID."', ID ".
			"FROM b_list_rubric ".
			"WHERE ID IN (".$sID.") "
			, false, "File: ".__FILE__."<br>Line: ".__LINE__
		);
	}

	//adding
	function Add($arFields, $SITE_ID=SITE_ID)
	{
		global $DB;

		if(!$this->CheckFields($arFields, 0))
			return false;

		$arFields["CONFIRM_CODE"] = randString(8);
		$arFields["~DATE_INSERT"]  = $DB->CurrentTimeFunction();
		$arFields["~DATE_CONFIRM"] = $DB->CurrentTimeFunction();

		$ID = $DB->Add("b_subscription", $arFields);

		if($ID > 0)
		{
			if(is_set($arFields,"ALL_SITES") && $arFields["ALL_SITES"]=="Y")
				$this->UpdateRubrics($ID, $arFields["RUB_ID"]);
			else
				$this->UpdateRubrics($ID, $arFields["RUB_ID"], $SITE_ID);

			if($arFields["SEND_CONFIRM"] <> "N")
				$this->ConfirmEvent($ID, $SITE_ID);
		}
		return $ID;
	}

	//Updating record
	function Update($ID, $arFields, $SITE_ID=SITE_ID)
	{
		global $DB;
		$ID = intval($ID);
		$this->LAST_MESSAGE = "";

		if(!$this->CheckFields($arFields, $ID))
			return false;

		//Check whether email changed. If changed, we must to generate new confirm code.
		$strSql =
			"SELECT EMAIL, CONFIRM_CODE, CONFIRMED FROM b_subscription ".
			"WHERE ID='".$ID."' ";
		$db_check = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if(!($db_check_arr = $db_check->Fetch()))
		{
			$this->LAST_ERROR = GetMessage("class_subscr_perm")."<br>";
			return false;
		}

		$CONFIRM_CODE = $arFields["CONFIRM_CODE"];
		unset($arFields["CONFIRM_CODE"]);
		if(!is_set($arFields, "EMAIL") || strtoupper($db_check_arr["EMAIL"]) == strtoupper($arFields["EMAIL"]))
		{
			//the same email - check confirm code
			if(strlen($CONFIRM_CODE)>0 && $db_check_arr["CONFIRMED"] <> "Y")
			{
				if($CONFIRM_CODE == $db_check_arr["CONFIRM_CODE"])
				{
					//let's confirm the subscription
					$arFields["CONFIRMED"] = "Y";
					$this->LAST_MESSAGE = "CONF";
				}
				else
				{
					$this->LAST_ERROR = GetMessage("class_subscr_conf")."<br>";
					return false;
				}
			}
		}
		else
		{
			//new email - new confirm code
			$arFields["CONFIRM_CODE"] = randString(8);
			if($arFields["CONFIRMED"]<>"Y")
				$arFields["CONFIRMED"] = "N";
		}

		$strUpdate = $DB->PrepareUpdate("b_subscription", $arFields);
		if (strlen($strUpdate)>0)
		{
			$strSql =
				"UPDATE b_subscription SET ".
				$strUpdate.", ".
				"	DATE_UPDATE=".$DB->GetNowFunction()." ".
				(strlen($arFields["CONFIRM_CODE"])>0? ",".
				"	DATE_CONFIRM=".$DB->GetNowFunction()." "
				:"").
				"WHERE ID=".$ID;
			if(!$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
				return false;
		}

		//subscription categories
		if(is_set($arFields, "RUB_ID"))
		{
			if(is_set($arFields,"ALL_SITES") && $arFields["ALL_SITES"]=="Y")
				$this->UpdateRubrics($ID, $arFields["RUB_ID"]);
			else
				$this->UpdateRubrics($ID, $arFields["RUB_ID"], $SITE_ID);
		}
		//send confirmation code if needed
		if($arFields["SEND_CONFIRM"] <> "N" && strlen($arFields["CONFIRM_CODE"])>0)
		{
			$this->ConfirmEvent($ID, $SITE_ID);
			$this->LAST_MESSAGE = "SENT";
		}

		return true;
	}

	//message with subscription confirmation
	function ConfirmEvent($ID, $SITE_ID=SITE_ID)
	{
		static $SITE_DIR_CACHE = array();
		$subscr = CSubscription::GetByID($ID);
		if($subscr_arr = $subscr->Fetch())
		{
			if(!array_key_exists($SITE_ID, $SITE_DIR_CACHE))
			{
				$db_lang = CLang::GetByID($SITE_ID);
				if($ar_lang = $db_lang->Fetch())
					$SITE_DIR_CACHE[$SITE_ID] = $ar_lang["DIR"];
				else
					$SITE_DIR_CACHE[$SITE_ID] = LANG_DIR;
			}


			$subscr_arr["USER_NAME"] = "";
			$subscr_arr["USER_LAST_NAME"] = "";
			if(intval($subscr_arr["USER_ID"]) > 0)
			{
				$rsUser = CUser::GetByID($subscr_arr["USER_ID"]);
				if($arUser = $rsUser->Fetch())
				{
					$subscr_arr["USER_NAME"] = $arUser["NAME"];
					$subscr_arr["USER_LAST_NAME"] = $arUser["LAST_NAME"];
				}
			}

			$arFields = Array(
				"ID" => $subscr_arr["ID"],
				"EMAIL" => $subscr_arr["EMAIL"],
				"CONFIRM_CODE" => $subscr_arr["CONFIRM_CODE"],
				"USER_NAME" => $subscr_arr["USER_NAME"]." ".$subscr_arr["USER_LAST_NAME"],
				"DATE_SUBSCR" => ($subscr_arr["DATE_UPDATE"] <> ""? $subscr_arr["DATE_UPDATE"]: $subscr_arr["DATE_INSERT"]),
				"SUBSCR_SECTION" => str_replace(
					array("#SITE_DIR#", "#LANG_DIR#"),
					array($SITE_DIR_CACHE[$SITE_ID], $SITE_DIR_CACHE[$SITE_ID]),
					COption::GetOptionString("subscribe", "subscribe_section")
				),
			);
			CEvent::Send("SUBSCRIBE_CONFIRM", $SITE_ID, $arFields);
			return true;
		}
		return false;
	}

	//checks and set user authorization
	function Authorize($ID, $CONFIRM_CODE=false)
	{
		global $USER;

		$subscr = CSubscription::GetByID($ID);
		if(($subscr_arr = $subscr->Fetch()))
		{
			//unconditional auth
			if($CONFIRM_CODE===false)
			{
				$_SESSION["SESS_SUBSCR_AUTH"][$ID] = "YES";
				return true;
			}
			//anonymous subscription
			if($subscr_arr["CONFIRM_CODE"] == $CONFIRM_CODE)
			{
				$_SESSION["SESS_SUBSCR_AUTH"][$ID] = "YES";
				return true;
			}
			//user account subscription
			if(intval($subscr_arr["USER_ID"]) > 0)
			{
				if($USER->IsAuthorized())
				{
					//user is already authorized
					if($USER->GetID()==$subscr_arr["USER_ID"])
					{
						$_SESSION["SESS_SUBSCR_AUTH"][$ID] = "YES";
						return true;
					}
				}
			}
		}
		$_SESSION["SESS_SUBSCR_AUTH"][$ID] = "NO";
		return false;
	}

	//retuns user's subscription authorization
	function IsAuthorized($ID)
	{
		return ($_SESSION["SESS_SUBSCR_AUTH"][$ID] == "YES");
	}

	//*****************************
	// Events
	//*****************************

	//user deletion event
	function OnUserDelete($user_id)
	{
		//clear user subscriptions on user deletion
		global $DB;
		$user_id = intval($user_id);

		$strSql = "SELECT ID FROM b_subscription WHERE USER_ID = ".$user_id;
		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$arIn = array();
		while($res_arr = $res->Fetch())
			$arIn[] = intval($res_arr["ID"]);

		if(count($arIn) > 0)
		{
			$sIn = implode(",",$arIn);
			if(
				$DB->Query("DELETE FROM b_subscription_rubric WHERE SUBSCRIPTION_ID IN (".$sIn.")", false, "File: ".__FILE__."<br>Line: ".__LINE__) &&
				$DB->Query("DELETE FROM b_subscription WHERE ID IN (".$sIn.")", false, "File: ".__FILE__."<br>Line: ".__LINE__)
			)
				return true;
			else
				return false;
		}
		else
		{
			return true;
		}
	}

	//user logout event
	function OnUserLogout($user_id)
	{
		//let's reset subscriptions authorization on user logout
		global $DB;
		$user_id = intval($user_id);
		if($user_id>0)
		{
			$strSql = "SELECT ID FROM b_subscription WHERE USER_ID=".$user_id;
			$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			while($res_arr = $res->Fetch())
				$_SESSION["SESS_SUBSCR_AUTH"][$res_arr["ID"]] = "NO";
		}
		return true;
	}

	//*****************************
	// Agents
	//*****************************

	//delete unconfirmed subscriptions
	function CleanUp()
	{
		//must be inherited
		return "CSubscription::CleanUp();";
	}
}
?>