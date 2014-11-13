<?
##############################################
# Bitrix Site Manager Forum                  #
# Copyright (c) 2002-2007 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
IncludeModuleLangFile(__FILE__);

class CAllFilterDictionary
{
	function CheckFields($arFields = array(), $ACTION = "ADD")
	{
		global $APPLICATION, $DB;
		$strError = "";

		if ((!is_set($arFields, "TITLE")) || (strlen(trim($arFields["TITLE"]))<=0))
			$strError .= GetMessage("FLT_ERR_TITLE_MISSED");
		if ($ACTION != "UPDATE" && empty($arFields["TYPE"]))
			$strError .= GetMessage("FLT_ERR_TYPE_MISSED");
		if (strlen($strError) <= 0)
			return true;
		$APPLICATION->ThrowException($strError);
		return false;
	}
	function Add($arFields)
	{
		global $DB;
		$arFields["TITLE"] = trim($arFields["TITLE"]);
		$arFields["TYPE"] = strtoupper(trim($arFields["TYPE"]));
		if ($arFields["TYPE"] != "T")
			$arFields["TYPE"] = "W";
		if(CFilterDictionary::CheckFields($arFields))
			return $DB->Add("b_forum_dictionary", $arFields);
		return false;
	}
	function Update($ID, $arFields)
	{
		global $DB;
		$ID = intval($ID);
		if(is_set($arFields, "TITLE"))
			$arFields["TITLE"]=trim($arFields["TITLE"]);
		if(is_set($arFields, "TYPE"))
		{
			$arFields["TYPE"] = strtoupper(trim($arFields["TYPE"]));
			if ($arFields["TYPE"] != "T")
				$arFields["TYPE"] = "W";
		}
		if (($ID>0) && (CFilterDictionary::CheckFields($arFields, "UPDATE")))
		{
			$strUpdate = $DB->PrepareUpdate("b_forum_dictionary", $arFields);
			$res = $DB->Query("UPDATE b_forum_dictionary SET ".$strUpdate." WHERE ID=".$ID);
			return $res;
		}
		return false;
	}
	function Delete($ID)
	{
		global $DB, $USER;
		
		$ID = IntVal($ID);
		$Dictionary = ""; 
		$Dictionary = CFilterDictionary::GetList(array(), array("ID"=>$ID));
		$Dictionary = $Dictionary->Fetch();
		$res = false;
		$DB->StartTransaction();
			if ($Dictionary["TYPE"] == "T")
				$res = $DB->Query("DELETE FROM b_forum_letter WHERE DICTIONARY_ID=".$ID);
			else 
				$res = $DB->Query("DELETE FROM b_forum_filter WHERE DICTIONARY_ID=".$ID);
			if ($res)		
				$res = $DB->Query("DELETE FROM b_forum_dictionary WHERE ID=".$ID);
		if ($res)
			$DB->Commit();
		else 
			$DB->Rollback();
		return $res;
	}
	
	function GetFilterOperation($key)
	{
		$strNegative = "N";
		if (substr($key, 0, 1)=="!")
		{
			$key = substr($key, 1);
			$strNegative = "Y";
		}

		if (substr($key, 0, 2)==">=")
		{
			$key = substr($key, 2);
			$strOperation = ">=";
		}
		elseif (substr($key, 0, 1)==">")
		{
			$key = substr($key, 1);
			$strOperation = ">";
		}
		elseif (substr($key, 0, 2)=="<=")
		{
			$key = substr($key, 2);
			$strOperation = "<=";
		}
		elseif (substr($key, 0, 1)=="<")
		{
			$key = substr($key, 1);
			$strOperation = "<";
		}
		elseif (substr($key, 0, 1)=="@")
		{
			$key = substr($key, 1);
			$strOperation = "IN";
		}
		elseif (substr($key, 0, 1)=="%")
		{
			$key = substr($key, 1);
			$strOperation = "LIKE";
		}
		else
		{
			$strOperation = "=";
		}
		return array("FIELD"=>$key, "NEGATIVE"=>$strNegative, "OPERATION"=>$strOperation);
	}
}

class CAllFilterLetter
{
	function CheckFields($arFields = array())
	{
		global $APPLICATION, $DB;
		$strError = "";

		if ((!is_set($arFields, "LETTER")) || (strlen(trim($arFields["LETTER"]))<=0))
			$strError .= GetMessage("FLT_ERR_SIMBOL_MISSED");
		if ((!is_set($arFields, "DICTIONARY_ID")) || (intVal(trim($arFields["DICTIONARY_ID"]))<=0))
			$strError .= GetMessage("FLT_ERR_DICTIONARY_MISSED");
		if (strlen($strError) <= 0)
			return true;
		$APPLICATION->ThrowException($strError);
		return false;
	}
	
	function Add($arFields)
	{
		global $DB, $APPLICATION;
		$arFields["LETTER"] = trim($arFields["LETTER"]);
		$arFields["REPLACEMENT"] = trim($arFields["REPLACEMENT"]);
		$arFields["DICTIONARY_ID"] = intVal($arFields["DICTIONARY_ID"]);
		$db_res = CFilterLetter::GetList(array(), array("DICTIONARY_ID"=>$arFields["DICTIONARY_ID"], "LETTER"=>trim($arFields["LETTER"])));
		$db_res = $db_res->Fetch();
		if ($db_res["ID"]<=0)
		{
			if(CFilterLetter::CheckFields($arFields))
				return $DB->Add("b_forum_letter", $arFields);
		}
		else 
			$APPLICATION->ThrowException(GetMessage("FLT_ALREADY_EXIST"));
		return false;
	}
	
	function Update($ID, $arFields)
	{
		global $DB, $APPLICATION;
		$ID = intval($ID);
		$letter = false; $dictionary_id = false; $update = false;
		if (is_set($arFields, "LETTER"))
		{
			$arFields["LETTER"] = trim($arFields["LETTER"]);		
			$letter = true;
		}
		if (is_set($arFields, "REPLACEMENT"))
			$arFields["REPLACEMENT"] = trim($arFields["REPLACEMENT"]);		
		if (is_set($arFields, "DICTIONARY_ID"))
		{
			$arFields["DICTIONARY_ID"] = intVal($arFields["DICTIONARY_ID"]);
			$dictionary_id = true;
		}
			
		if ($letter || $dictionary_id)
		{
			$ID1 = CFilterLetter::GetByID($ID);
			$request = array();
			if ($letter && !$dictionary_id)
				$request = array("DICTIONARY_ID"=>$ID1["DICTIONARY_ID"], "LETTER"=>$arFields["LETTER"]);
			elseif (!$letter && $dictionary_id)
				$request = array("DICTIONARY_ID"=>$arFields["DICTIONARY_ID"], "LETTER"=>$ID1["LETTER"]);
			elseif ($letter && $dictionary_id)
				$request = array("DICTIONARY_ID"=>$arFields["DICTIONARY_ID"], "LETTER"=>$arFields["LETTER"]);
			$db_res = CFilterLetter::GetList(array(), $request);
			$db_res = $db_res->Fetch();
			if ((intVal($db_res["ID"])<=0) || (intVal($db_res["ID"]) == $ID))
				$update = true;
		}
		if (!$update)
			$APPLICATION->ThrowException(GetMessage("FLT_ALREADY_EXIST"));
		if (($ID>0) && (CFilterLetter::CheckFields($arFields)) && ($update))
		{
			$strUpdate = $DB->PrepareUpdate("b_forum_letter", $arFields);
			$res = $DB->Query("UPDATE b_forum_letter SET ".$strUpdate." WHERE ID=".$ID);
			return $res;
		}
		return false;
	}
	
	function Delete($ID)
	{
		global $DB, $USER;
		$ID = IntVal($ID);
		return $DB->Query("DELETE FROM b_forum_letter WHERE ID=".$ID);
	}
	
	function GetByID($ID)
	{
		$res = array();
		$res = CFilterLetter::GetList(array(), array("ID"=>$ID));
		if ($res)
			return $res->GetNext();
		else 
			return false;
	}
}

class CAllFilterUnquotableWords
{
	function CheckFields($action = "INSERT", $ID = false, $arFields = array())
	{
		global $APPLICATION, $DB;
		$strError = false;
		$words_err = false; 
		$pattern_err = false;
		
		if (!is_set($arFields, "WORDS") || (is_set($arFields, "WORDS")&&(strlen(trim($arFields["WORDS"])) <= 0)))
			$words_err = true;
		if (!is_set($arFields, "PATTERN") || (is_set($arFields, "PATTERN")&&(strlen(trim($arFields["PATTERN"]))<=0)))
			$pattern_err = true;
		if (($action == "INSERT") && $words_err && $pattern_err)
			$strError .= GetMessage("FLT_ERR_DICT_PATT_MISSED");
		if ((is_set($arFields, "DICTIONARY_ID")) && (intVal($arFields["DICTIONARY_ID"])<=0))
			$strError .=  GetMessage("FLT_ERR_DICTIONARY_MISSED");
		
		if (strlen(trim($strError))<=0)
		{
			if ($words_err)
				return true;
			else 
			{
				$arFieldsEx = CFilterUnquotableWords::GetList(array(), array("WORDS"=>trim($arFields["WORDS"])));
				$arFieldsEx = $arFieldsEx->Fetch();
				if ((!$arFieldsEx["ID"]) || (($action=="UPDATE") && ($ID==$arFieldsEx["ID"]) && ($ID>0)))
					return true;
				else 
					$strError .= GetMessage("FLT_ALREADY_EXIST");
			}
			
			if (($action == "UPDATE") && !$ID)
				$strError .= GetMessage("FLT_ERR_ID_NOT_ENTER");
		}
		$APPLICATION->ThrowException($strError);
		return false;
	}
	
	function Add($arFields)
	{
		global $DB;
		$arFields["DICTIONARY_ID"] = intVal($arFields["DICTIONARY_ID"]);
		if (!$arFields["PATTERN_CREATE"] && $arFields["WORDS"])
			$arFields["PATTERN_CREATE"] = "TRNSL";
		elseif(!$arFields["PATTERN_CREATE"] && !$arFields["WORDS"])
			$arFields["PATTERN_CREATE"] = "PTTRN";
		$arFields["WORDS"] = trim($arFields["WORDS"]);
		
		if ($arFields["PATTERN_CREATE"] == "TRNSL")
			$arFields["WORDS"] = strToLower($arFields["WORDS"]);
		$arFields["PATTERN"] = trim($arFields["PATTERN"]);
		$arFields["REPLACEMENT"] = trim($arFields["REPLACEMENT"]);
		$arFields["DESCRIPTION"] = trim($arFields["DESCRIPTION"]);
		if($arFields["USE_IT"] != "Y") $arFields["USE_IT"] = "N";
		$arFields["PATTERN_CREATE"] = strToUpper(trim($arFields["PATTERN_CREATE"]));
		
		if(CFilterUnquotableWords::CheckFields("INSERT", false, $arFields))
			return $DB->Add("b_forum_filter", $arFields, Array("PATTERN","DESCRIPTION"));
		return false;
	}
	
	function Update($ID, $arFields)
	{
		global $DB;
		$ID = intval($ID);
		if(is_set($arFields, "DICTIONARY_ID"))
			$arFields["DICTIONARY_ID"] = intVal($arFields["DICTIONARY_ID"]);
		if(is_set($arFields, "PATTERN_CREATE"))
		{
			$arFields["PATTERN_CREATE"] = strToUpper(trim($arFields["PATTERN_CREATE"]));
			if (strLen($arFields["PATTERN_CREATE"])<=0)
			{
				if ($arFields["WORDS"])
					$arFields["PATTERN_CREATE"] = "TRNSL";
				elseif($arFields["PATTERN_CREATE"])
					$arFields["PATTERN_CREATE"] = "PTTRN";
			}
		}	
		if(is_set($arFields, "WORDS"))
		{
			$arFields["WORDS"] = trim($arFields["WORDS"]);
			if ($arFields["PATTERN_CREATE"] == "TRNSL")
				$arFields["WORDS"] = strToLower($arFields["WORDS"]);
		}
			
		if(is_set($arFields, "PATTERN"))
			$arFields["PATTERN"] = trim($arFields["PATTERN"]);
		if(is_set($arFields, "REPLACEMENT"))
			$arFields["REPLACEMENT"] = trim($arFields["REPLACEMENT"]);
		if(is_set($arFields, "DESCRIPTION"))
			$arFields["DESCRIPTION"] = trim($arFields["DESCRIPTION"]);
		if(is_set($arFields, "USE_IT") && $arFields["USE_IT"]!="Y")
			$arFields["USE_IT"]="N";
			
		if (($ID>0) && (CFilterUnquotableWords::CheckFields("UPDATE", $ID, $arFields)))
		{
			$strUpdate = $DB->PrepareUpdate("b_forum_filter", $arFields);
			$strSql = "UPDATE b_forum_filter SET ".$strUpdate." WHERE ID=".$ID;
			$res = $DB->QueryBind($strSql, Array("PATTERN"=>$arFields["PATTERN"], "DESCRIPTION"=>$arFields["DESCRIPTION"]), false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
			return $res;
		}
		return false;
	}
	
	function Delete($ID)
	{
		global $DB, $USER;
		$ID = IntVal($ID);
		return $DB->Query("DELETE FROM b_forum_filter WHERE ID=".$ID);
	}
	
	function GetById($ID)
	{
		$ID = intval($ID);
		$res = CFilterUnquotableWords::GetList(array(), array("ID"=>$ID));
		return $res->Fetch();
	}
	
	function GetFilterOperation($key)
	{
		$strNegative = "N";
		if (substr($key, 0, 1)=="!")
		{
			$key = substr($key, 1);
			$strNegative = "Y";
		}

		if (substr($key, 0, 2)==">=")
		{
			$key = substr($key, 2);
			$strOperation = ">=";
		}
		elseif (substr($key, 0, 1)==">")
		{
			$key = substr($key, 1);
			$strOperation = ">";
		}
		elseif (substr($key, 0, 2)=="<=")
		{
			$key = substr($key, 2);
			$strOperation = "<=";
		}
		elseif (substr($key, 0, 1)=="<")
		{
			$key = substr($key, 1);
			$strOperation = "<";
		}
		elseif (substr($key, 0, 1)=="@")
		{
			$key = substr($key, 1);
			$strOperation = "IN";
		}
		elseif (substr($key, 0, 1)=="%")
		{
			$key = substr($key, 1);
			$strOperation = "LIKE";
		}
		else
		{
			$strOperation = "=";
		}
		return array("FIELD"=>$key, "NEGATIVE"=>$strNegative, "OPERATION"=>$strOperation);
	}
	
	function GenPattern($ID=false, $DICTIONARY_ID_T=0)
	{
		$DICTIONARY_ID_T = intVal($DICTIONARY_ID_T);
		if (!$DICTIONARY_ID_T)
			$DICTIONARY_ID_T = (COption::GetOptionString("forum", "FILTER_DICT_T", '', LANG));
		$ID = intVal($ID);
		if ($ID):
			$res = CFilterUnquotableWords::GetByID($ID);
			if ((strlen(trim($res["WORDS"]))>0) && ($res["PATTERN_CREATE"] == "TRNSL")):
				$pattern = CFilterUnquotableWords::CreatePattern(trim($res["WORDS"]), $DICTIONARY_ID_T);
				if ($pattern && CFilterUnquotableWords::Update($ID, array("PATTERN"=>$pattern)))
					return true;
			endif;
		endif;
		return false;
	}
	
	function GenPatternAll($DICTIONARY_ID_W=0, $DICTIONARY_ID_T=0)
	{
		$DICTIONARY_ID_W = intVal($DICTIONARY_ID_W);
		$DICTIONARY_ID_T = intVal($DICTIONARY_ID_T);
		if (!$DICTIONARY_ID_W)
			$DICTIONARY_ID_W = (COption::GetOptionString("forum", "FILTER_DICT_W", '', LANG));
		if (!$DICTIONARY_ID_T)
			$DICTIONARY_ID_T = (COption::GetOptionString("forum", "FILTER_DICT_T", '', LANG));
		if ($DICTIONARY_ID_W):
			$db_res = CFilterUnquotableWords::GetList(array(), array("DICTIONARY_ID"=>$DICTIONARY_ID_W));
			while ($res = $db_res->Fetch()):
				if ((strlen(trim($res["WORDS"]))>0) && ($res["PATTERN_CREATE"] == "TRNSL")):
					$pattern = CFilterUnquotableWords::CreatePattern(trim($res["WORDS"]), $DICTIONARY_ID_T);
					if ($pattern)
						CFilterUnquotableWords::Update($res["ID"], array("PATTERN"=>$pattern));
				endif;
			endwhile;
			return true;
		endif;
		return false;
	}

	function CreatePattern($pattern="", $DICTIONARY_ID=0)
	{
		$res = "";
		$NotWord = "\s.,;:!?\#\-\*\|\[\]\(\)";
		$word_separator = "[".$NotWord."]";
		$pattern = strtolower(trim($pattern));
		$DICTIONARY_ID = intval($DICTIONARY_ID);
		
		if (strlen($pattern) <= 0)
			return false;
			
		if ($DICTIONARY_ID == 0)
			$DICTIONARY_ID = (COption::GetOptionString("forum", "FILTER_DICT_T", '', LANG));
		elseif ($DICTIONARY_ID < 0)
			$DICTIONARY_ID = 0;
			
		$lettPatt = array();
		$lettersPatt = array();
		
		$letters = CFilterLetter::GetList(array(), array("DICTIONARY_ID"=>$DICTIONARY_ID));
		while ($lett = $letters->Fetch())
		{
			$space = false;
			$arrRes = array();
			$arrRepl = array();
			
			$arrRepl = explode(",", $lett["REPLACEMENT"]);
			// ќбработка букв.
			for ($ii = 0; $ii < count($arrRepl); $ii++)
			{
				$arrRepl[$ii] = trim($arrRepl[$ii]);
				if (strLen($lett["LETTER"])==1)
				{
					if (strLen($arrRepl[$ii]) == 1 )
						$arrRes[$ii] = $arrRepl[$ii]."+";
					elseif (strLen($arrRepl[$ii]) > 1 )
						$arrRes[$ii] = "[".$arrRepl[$ii]."]+";
					else 
						$space = true;
				}
				else 
				{
					if (strLen($arrRepl[$ii]) > 0)
						$arrRes[$ii] = $arrRepl[$ii];
				}
			}
			
			if (strLen($lett["LETTER"])==1)
			{
				if ($space)
					$arrRes[] = "";
//					$lettPatt[$lett["LETTER"]] = str_replace("+", "*", $lettPatt[$lett["LETTER"]]);
				$lettPatt[$lett["LETTER"]] = implode("|", $arrRes);
			}
			else 
			{
				$lettersPatt["/".$lett["LETTER"]."/is"] = "(".implode("|", $arrRes).")";
			}
		}
		// буквосочетани€
		foreach ($lettersPatt as $key => $val)
			$pattern = preg_replace($key.BX_UTF_PCRE_MODIFIER, $val, $pattern);
		// просто буквы
		for ($ii = 0; $ii < strLen($pattern); $ii++)
		{
			$sPattern = substr($pattern, $ii, 1);
			if (is_set($lettPatt, substr($pattern, $ii, 1)))
				$res .= "(".$lettPatt[substr($pattern, $ii, 1)].")";
			else 
			{
				$ord = ord(substr($pattern, $ii, 1));
				if ((48>$ord) || ((64>$ord) and ($ord>57)) || ((97>$ord) and ($ord>90)) || ((127>$ord) and ($ord>122)))
				{
					if ($ord == 42)
						$res .= "[^".$NotWord."]*";
					elseif ($ord == 43)
						$res .= "[^".$NotWord."]+";
					elseif ($ord == 63)
						$res .= ".?";
					else
						$res .= substr($pattern, $ii, 1);
				}
				else 
					$res .= substr($pattern, $ii, 1)."+";
			}
			$res .= $separator;
		}
		$res = "/(?<=".$word_separator.")(".$res.")(?=".$word_separator.")/is".BX_UTF_PCRE_MODIFIER;
		return $res;
	}
	
	
	function FilterPerm()
	{
		global $USER, $APPLICATION;
		if($USER->IsAdmin() || $APPLICATION->GetGroupRight("forum")>="W")
			return true;
		return false;
	}
	
	function Filter($message)
	{
		global $USER, $DB, $arFilterPattern;
		$filter = array();
		$pattern = array();
		$replacement = array();

		if (!empty($arFilterPattern))
		{
			$pattern = $arFilterPattern["pattern"];
			$replacement = $arFilterPattern["replacement"];
		}
		else 
		{
			$db_res = CFilterUnquotableWords::GetList(array(), array("USE_IT"=>"Y", "DICTIONARY_ID"=>COption::GetOptionInt("forum", "FILTER_DICT_W", false, LANG)));
			$replace = COption::GetOptionString("forum", "FILTER_RPL", "*");
			while ($res = $db_res->Fetch())
			{
				if (strlen(trim($res["PATTERN"])) > 0 )
				{
					$pattern[] = trim($res["PATTERN"]);
					$replacement[] = strlen($res["REPLACEMENT"]) > 0? " ".$res["REPLACEMENT"]." " : " ".$replace." ";
				}
			}
			$arFilterPattern["pattern"] = $pattern;
			$arFilterPattern["replacement"] = $replacement;
		}
		
		ksort($pattern); ksort($replacement); 
		$message = '  '.$message.'  ';
		switch (COption::GetOptionString("forum", "FILTER_ACTION", "rpl"))
		{
			case "rpl": 
				$message = preg_replace($pattern, $replacement, $message);
				break;
			case "del": 
				$message = preg_replace($pattern, '', $message);
				break;
		}
		return trim($message);
	}
}
?>