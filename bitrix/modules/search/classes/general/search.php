<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/search/tools/stemming.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/search/tools/tags.php");
IncludeModuleLangFile(__FILE__);

if(!defined("START_EXEC_TIME"))
	define("START_EXEC_TIME", getmicrotime());

class CAllSearch extends CDBResult
{
	var $Query;
	var $strQueryText = false;
	var $errorno = 0;
	var $error = false;
	var $arParams = Array();
	var $tf_hwm = 0;

	function __construct($strQuery=false, $SITE_ID=false, $MODULE_ID=false, $ITEM_ID=false, $PARAM1=false, $PARAM2=false, $aSort=array(), $aParamsEx=array(), $bTagsCloud = false)
	{
		return $this->CSearch($strQuery, $SITE_ID, $MODULE_ID, $ITEM_ID, $PARAM1, $PARAM2, $aSort, $aParamsEx, $bTagsCloud);
	}

	function CSearch($strQuery=false, $LID=false, $MODULE_ID=false, $ITEM_ID=false, $PARAM1=false, $PARAM2=false, $aSort=array(), $aParamsEx=array(), $bTagsCloud = false)
	{
		if($strQuery===false)
			return $this;

		$arParams["QUERY"] = $strQuery;
		$arParams["SITE_ID"] = $LID;
		$arParams["MODULE_ID"] = $MODULE_ID;
		$arParams["ITEM_ID"] = $ITEM_ID;
		$arParams["PARAM1"] = $PARAM1;
		$arParams["PARAM2"] = $PARAM2;

		$this->Search($arParams, $aSort, $aParamsEx, $bTagsCloud);
	}
	//combination ($MODULE_ID, $PARAM1, $PARAM2, $PARAM3) is used to narrow search
	//returns recordset with search results
	function Search($arParams, $aSort=array(), $aParamsEx=array(), $bTagsCloud = false)
	{
		if(!is_array($arParams))
			$arParams = Array("QUERY"=>$arParams);

		if(!is_set($arParams, "SITE_ID") && is_set($arParams, "LID"))
		{
			$arParams["SITE_ID"] = $arParams["LID"];
			unset($arParams["LID"]);
		}

		if(array_key_exists("TAGS", $arParams))
		{
			$arParams["~TAGS"] = trim($arParams["TAGS"], ",");
			$arParams["TAGS"] = '"'.str_replace(',', '" "', $arParams["~TAGS"]).'"';
		}

		$strQuery = $arParams["QUERY"];
		if(empty($strQuery) && !empty($arParams["TAGS"]))
		{
			$strQuery = $arParams["TAGS"];
			$bTagsSearch = true;
		}
		else
		{
			if(!empty($arParams["TAGS"]) && ($arParams["TAGS"] != '""'))
				$strQuery .= " ".$arParams["TAGS"];
			$bTagsSearch = false;
		}

		global $USER, $DB;
		if(!array_key_exists("STEMMING", $aParamsEx))
			$aParamsEx["STEMMING"] = COption::GetOptionString("search", "use_stemming", "N")=="Y";
		$this->Query = new CSearchQuery("and", "yes", 0, $arParams["SITE_ID"]);
		$query = $this->Query->GetQueryString("sc.SEARCHABLE_CONTENT", $strQuery, $bTagsSearch, $aParamsEx["STEMMING"]);
		if(!$query || strlen(trim($query))<=0)
		{
			if($bTagsCloud)
			{
				$query = "1=1";
			}
			else
			{
				$this->error = $this->Query->error;
				$this->errorno = $this->Query->errorno;
				return;
			}
		}

		if(strlen($query)>2000)
		{
			$this->error = GetMessage("SEARCH_ERROR4");
			$this->errorno = 4;
			return;
		}

		$this->params = "";
		$db_events = GetModuleEvents("search", "OnSearch");
		while($arEvent = $db_events->Fetch())
		{
			if(strlen($this->params)>0)
				$this->params.="&";
			if($bTagsSearch)
			{
				if(strlen($arParams["~TAGS"]) > 0)
					$this->params .= ExecuteModuleEvent($arEvent, "tags:".$arParams["~TAGS"]);
			}
			else
				$this->params .= ExecuteModuleEvent($arEvent, $strQuery);
		}

		$bIncSites = false;
		$strSqlWhere = CSearch::__PrepareFilter($arParams, $bIncSites);
		if(strlen($strSqlWhere)>0)
			$strSqlWhere = " AND ".$strSqlWhere;
		$strSqlOrder = CSearch::__PrepareSort($aSort, "sc.", $bTagsCloud);
		if(is_array($aParamsEx) && count($aParamsEx)>0)
		{
			$arSqlWhere=array();
			foreach($aParamsEx as $aParamEx)
			{
				$s=CSearch::__PrepareFilter($aParamEx, $bIncSites);
				if(strlen($s)>0)
					$arSqlWhere[]=$s;
			}
			if(count($arSqlWhere)>0)
				$strSqlWhere.="\n\t\t\t\tAND (\n\t\t\t\t\t(".implode(")\n\t\t\t\t\tOR(",$arSqlWhere).")\n\t\t\t\t)";
		}

		$bStem = !$bTagsSearch && count($this->Query->m_stemmed_words)>0;
		//calculate freq of the word on the whole site_id
		if($bStem && count($this->Query->m_stemmed_words) > 0)
		{
			$arStat = $this->GetFreqStatistics($this->Query->m_lang, $this->Query->m_stemmed_words);

			//we'll make filter by it's contrast
			if(!$bTagsCloud && COption::GetOptionString("search", "use_tf_cache") == "Y")
			{
				$hwm = false;
				foreach($this->Query->m_stemmed_words as $stem)
				{
					if(!array_key_exists($stem, $arStat))
					{
						$hwm = 0;
						break;
					}
					elseif($hwm === false)
					{
						$hwm = $arStat[$stem]["TF"];
					}
					elseif($hwm > $arStat[$stem]["TF"])
					{
						$hwm = $arStat[$stem]["TF"];
					}
				}
				if($hwm > 0)
				{
					$strSqlWhere .= " AND st.TF >= ".number_format($hwm, 2, ".", "");
					$this->tf_hwm = $hwm;
				}
			}
		}

		if($bTagsCloud)
			$strSql = $this->tagsMakeSQL($query, $strSqlWhere, $strSqlOrder, $bIncSites, $bStem, $aParamsEx["LIMIT"]);
		else
			$strSql = $this->MakeSQL($query, $strSqlWhere, $strSqlOrder, $bIncSites, $bStem);
//		$tStart = getmicrotime();
		$r = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
//		echo "<pre>",$strSql,"</pre><br>",(getmicrotime()-$tStart);
		parent::CDBResult($r);
	}

	function GetFreqStatistics($lang_id, $arStem)
	{
		global $DB;

		$limit = COption::GetOptionInt("search", "max_result_size");
		if($limit < 1)
			$limit = 500;

		$arResult = array();
		foreach($arStem as $stem)
			$arResult[$stem] = array("STEM" => false, "FREQ" => 0, "TF" => 0, "STEM_COUNT" => 0, "TF_SUM" => 0);

		$strSql = "
			SELECT STEM, FREQ, TF
			FROM b_search_content_freq
			WHERE LANGUAGE_ID = '".$lang_id."'
			AND STEM in ('".implode("','", $arStem)."')
		";
		$rs = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		while($ar = $rs->Fetch())
		{
			if(strlen($ar["TF"]) > 0)
				$arResult[$ar["STEM"]] = $ar;
		}

		$arMissed = array();
		foreach($arResult as $stem => $ar)
			if(!$ar["STEM"])
				$arMissed[] = $stem;

		if(count($arMissed) > 0)
		{
			$strSql = "
				SELECT STEM, floor(TF*100) BUCKET, sum(TF) TF_SUM, count(*) STEM_COUNT
				FROM b_search_content_stem
				WHERE LANGUAGE_ID = '".$lang_id."'
				AND STEM in ('".implode("','", $arMissed)."')
				GROUP BY STEM, floor(TF*100)
				ORDER BY STEM, floor(TF*100) DESC
			";
			$rs = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			while($ar = $rs->Fetch())
			{
				$stem = $ar["STEM"];
				if($arResult[$stem]["STEM_COUNT"] < $limit)
					$arResult[$stem]["TF"] = $ar["BUCKET"]/100.0;
				$arResult[$stem]["STEM_COUNT"] += $ar["STEM_COUNT"];
				$arResult[$stem]["TF_SUM"] += $ar["TF_SUM"];
				$arResult[$stem]["DO_INSERT"] = true;
			}
		}

		foreach($arResult as $stem => $ar)
		{
			if($ar["DO_INSERT"])
			{
				$FREQ = intval(defined("search_range_by_sum_tf")? $ar["TF_SUM"]: $ar["STEM_COUNT"]);
				$strSql = "
					UPDATE b_search_content_freq
					SET FREQ=".$FREQ.", TF=".number_format($ar["TF"], 2, ".", "")."
					WHERE LANGUAGE_ID='".$lang_id."'
					AND STEM='".$stem."'
				";
				$rsUpdate = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				if($rsUpdate->AffectedRowsCount() <= 0)
				{
					$strSql = "
						INSERT INTO b_search_content_freq
						(LANGUAGE_ID, STEM, FREQ, TF)
						VALUES
						('".$lang_id."', '".$stem."', ".$FREQ.", ".number_format($ar["TF"], 2, ".", "").")
					";
					$rsInsert = $DB->Query($strSql, true);
				}
			}
		}

		return $arResult;
	}

	function Repl($strCond, $strType, $strWh)
	{
		$l=strlen($strCond);

		$strWhUpp=ToUpper($strWh);
		if($this->Query->bStemming)
		{
			$letters = constant("STEMMING_TOK_".$this->Query->m_lang);
			$strWhUpp = preg_replace("/[^".$letters."]/".BX_UTF_PCRE_MODIFIER, " ", $strWhUpp);
		}
		$strCondUpp=ToUpper($strCond);
		$pos=0;
		do
		{
			$pos = strpos($strWhUpp, $strCondUpp, $pos);

			//Check if we are in the middle of the numeric entity
			while(
				$pos !== false &&
				preg_match("/^[0-9]+;/", substr($strWh, $pos)) &&
				preg_match("/^[0-9]+#&/", strrev(substr($strWh, 0, $pos+strlen($strCond))))
			)
			{
				$pos = strpos($strWhUpp, $strCondUpp, $pos+1);
			}

			if($pos === false) break;

			if($strType=="STEM")
			{
				$lw=strlen($strWhUpp);
				for($s=$pos; $s>=0 && substr($strWhUpp, $s, 1)!=" ";$s--){}
				$s++;
				for($e=$pos; $e<$lw && substr($strWhUpp, $e, 1)!=" ";$e++){}
				$e--;
				$a = array_keys(stemming(substr($strWhUpp,$s,$e-$s+1), $this->Query->m_lang));
				if($a[0] == $strCondUpp)
				{
					$strWh = substr($strWh, 0, $pos)."%^%".substr($strWh, $pos, $l)."%/^%".substr($strWh,$pos+$l);
					$strWhUpp = substr($strWhUpp, 0, $pos+$l)."%^%%/^%".substr($strWhUpp,$pos+$l);
				}
			}
			else
			{
				$strWh = substr($strWh, 0, $pos)."%^%".substr($strWh, $pos, $l)."%/^%".substr($strWh,$pos+$l);
				$strWhUpp = substr($strWhUpp, 0, $pos+$l)."%^%%/^%".substr($strWhUpp,$pos+$l);
			}
			$pos += $l+7;
		}while($pos < strlen($strWhUpp));
		return $strWh;
	}

	function PrepareSearchResult($str)
	{
		$w=array();
		foreach($this->Query->m_words as $k=>$v)
			$w[ToUpper($v)]="KAV";
		foreach($this->Query->m_stemmed_words as $k=>$v)
			$w[ToUpper($v)]="STEM";
		$strUpp = ToUpper($str);
		if($this->Query->bStemming)
		{
			$letters = stemming("", $this->Query->m_lang);
			$letters = constant("STEMMING_TOK_".$this->Query->m_lang);
			$strUpp = preg_replace("/[^".$letters."]/".BX_UTF_PCRE_MODIFIER, " ", $strUpp);
		}
		$arPos = Array();
		foreach($w as $search=>$type)
		{
			$p = strpos($strUpp, $search."");
			while($p!==false)
			{
				//Check if we are in the middle of the numeric entity
				if(
					preg_match("/^[0-9]+;/", substr($str, $p)) &&
					preg_match("/^[0-9]+#&/", strrev(substr($str, 0, $p+strlen($search))))
				)
				{
					$p = strpos($strUpp, $search, $p+1);
				}
				elseif($type=="STEM")
				{
					$l = strlen($strUpp);
					for($s=$p; $s>=0 && substr($strUpp, $s, 1)!=" ";$s--){}
					$s++;
					for($e=$p; $e<$l && substr($strUpp, $e, 1)!=" ";$e++){}
					$e--;
					$a = array_keys(stemming(substr($strUpp,$s,$e-$s+1), $this->Query->m_lang));
					if($a[0] == $search)
					{
						$arPos[] = $p;
						$p=false;
					}
					else
						$p = strpos($strUpp, $search, $p+1);
				}
				else
				{
					$arPos[] = $p;
					$p=false;
				}
			}
		}
		if(count($arPos)<=0)
			return substr($str, 0, 500);

		sort($arPos);

		$str_result = "";
		$last_pos = -1;
		$delta = 250/count($arPos);
		$str_len = strlen($str);
		foreach($arPos as $pos_mid)
		{
			//Find where word begins
			$pos_beg = $pos_mid - $delta;
			if($pos_beg <= 0)
				$pos_beg = 0;
			while(($pos_beg > 0) && (strpos(" ,.\n\r", substr($str, $pos_beg, 1)) === false))
				$pos_beg--;

			//Find where word ends
			$pos_end = $pos_mid + $delta;
			if($pos_end > $str_len)
				$pos_end = $str_len;
			while(($pos_end < $str_len) && (strpos(" ,.\n\r", substr($str, $pos_end, 1)) === false))
				$pos_end++;

			if($pos_beg <= $last_pos)
				$arOtr[count($arOtr)-1][1] = $pos_end;
			else
				$arOtr[] = Array($pos_beg, $pos_end);

			$last_pos = $pos_end;
		}

		for($i=0; $i<count($arOtr); $i++)
			$str_result .= ($arOtr[$i][0]<=0?"":" ...").
					substr($str, $arOtr[$i][0], $arOtr[$i][1]-$arOtr[$i][0]).
					($arOtr[$i][1]>=strlen($str)?"":"... ");

		foreach($w as $search=>$type)
			$str_result=$this->repl($search, $type, $str_result);

		$str_result = str_replace("%/^%", "</b>", str_replace("%^%","<b>", $str_result));

		return $str_result;
	}

	function Fetch()
	{
		$r = parent::Fetch();
		if($r)
		{
			if(strlen($r["SITE_URL"])>0)
				$r["URL"] = $r["SITE_URL"];

			if(substr($r["URL"], 0, 1)=="=")
			{
				$events = GetModuleEvents("search", "OnSearchGetURL");
				while ($arEvent = $events->Fetch())
					$r["URL"] = ExecuteModuleEvent($arEvent, $r);
			}

			$r["URL"] = str_replace(
				array("#LANG#", "#SITE_DIR#", "#SERVER_NAME#"),
				array($r["DIR"], $r["DIR"], $r["SERVER_NAME"]),
				$r["URL"]
			);
			$r["URL"] = preg_replace("'/+'s", "/", $r["URL"]);
			$r["URL_WO_PARAMS"] = $r["URL"];

			$w = $this->Query->m_words;
			if(strlen($this->params)>0)
			{
				$p1 = strpos($r["URL"], "?");
				$p2 = strpos($r["URL"], "#", $p1);
				if($p2===false) $p2 = strlen($r["URL"]);
				$r["URL"] = substr($r["URL"], 0, $p2).($p1===false?"?":"&") . $this->params . substr($r["URL"], $p2);
			}

			$r["TITLE_FORMATED"] = $this->PrepareSearchResult(htmlspecialcharsex($r["TITLE"]));
			$r["TITLE_FORMATED_TYPE"] = "html";
			$r["TAGS_FORMATED"] = tags_prepare($r["TAGS"], SITE_ID);
			$r["BODY_FORMATED"] = $this->PrepareSearchResult(htmlspecialcharsex($r["BODY"]));
			$r["BODY_FORMATED_TYPE"] = "html";
		}

		return $r;
	}

	function CheckPath($path)
	{
		static $SEARCH_MASKS_CACHE = false;

		if(!is_array($SEARCH_MASKS_CACHE))
		{
			$arInc = array();
			$inc = str_replace(
				array("\\", ".",  "?", "*",   "'"),
				array("/",  "\.", ".", ".*?", "\'"),
				COption::GetOptionString("search", "include_mask")
			);
			$arIncTmp = explode(";", $inc);
			foreach($arIncTmp as $mask)
			{
				$mask = trim($mask);
				if(strlen($mask))
					$arInc[] = "'^".$mask."$'";
			}

			$arExc = array();
			$exc = str_replace(
				array("\\", ".",  "?", "*",   "'"),
				array("/",  "\.", ".", ".*?", "\'"),
				COption::GetOptionString("search", "exclude_mask")
			);
			$arExcTmp = explode(";", $exc);
			foreach($arExcTmp as $mask)
			{
				$mask = trim($mask);
				if(strlen($mask))
					$arExc[] = "'^".$mask."$'";
			}

			$SEARCH_MASKS_CACHE = Array("exc"=>$arExc, "inc"=>$arInc);

		}

		$file = basename($path);
		if(strncmp($file, ".", 1)==0)
			return 0;

		foreach($SEARCH_MASKS_CACHE["exc"] as $mask)
			if(preg_match($mask, $path))
				return false;

		foreach($SEARCH_MASKS_CACHE["inc"] as $mask)
			if(preg_match($mask, $path))
				return true;

		return false;
	}

	function GetGroupCached()
	{
		static $SEARCH_CACHED_GROUPS = false;

		if(!is_array($SEARCH_CACHED_GROUPS))
		{
			$SEARCH_CACHED_GROUPS = Array();
			$db_groups = CGroup::GetList($order="ID", $by="ASC");
			while($g = $db_groups->Fetch())
			{
				$group_id = intval($g["ID"]);
				if($group_id > 1)
					$SEARCH_CACHED_GROUPS[$group_id]=$group_id;
			}
		}

		return $SEARCH_CACHED_GROUPS;
	}

	function QueryMnogoSearch(&$xml)
	{
		$SITE = COption::GetOptionString("search", "mnogosearch_url", "www.mnogosearch.org");
		$PATH = COption::GetOptionString("search", "mnogosearch_path", "");
		$PORT = COption::GetOptionString("search", "mnogosearch_port", "80");

		$QUERY_STR = 'document='.urlencode($xml);

		$strRequest = "POST ".$PATH." HTTP/1.0\r\n";
		$strRequest.= "User-Agent: BitrixSM\r\n";
		$strRequest.= "Accept: */*\r\n";
		$strRequest.= "Host: $SITE\r\n";
		$strRequest.= "Accept-Language: en\r\n";
		$strRequest.= "Content-type: application/x-www-form-urlencoded\r\n";
		$strRequest.= "Content-length: ".strlen($QUERY_STR)."\r\n";
		$strRequest.= "\r\n";
		$strRequest.= $QUERY_STR;
		$strRequest.= "\r\n";

		$arAll = "";

		$FP = fsockopen($SITE, $PORT, $errno, $errstr, 120);
		if ($FP)
		{
			fputs($FP, $strRequest);

			while (($line = fgets($FP, 4096)) && $line!="\r\n");
			while ($line = fread($FP, 4096))
				$arAll .= $line;
			fclose($FP);
		}

		return $arAll;
	}

	//////////////////////////////////
	//reindex the whole server content
	//$bFull = true - no not check change_date. all index tables will be truncated
	//       = false - add new ones. update changed and delete deleted.
	function ReIndexAll($bFull = false, $max_execution_time = 0, $NS = Array())
	{
		global $APPLICATION, $DB;

		@set_time_limit(0);
		if(!is_array($NS))
			$NS = Array();
		if($max_execution_time<=0)
		{
			$NS_OLD=$NS;
			$NS=Array("CLEAR"=>"N", "MODULE"=>"", "ID"=>"", "SESS_ID"=>md5(uniqid("")));
			if($NS_OLD["SITE_ID"]!="") $NS["SITE_ID"]=$NS_OLD["SITE_ID"];
			if($NS_OLD["MODULE_ID"]!="") $NS["MODULE_ID"]=$NS_OLD["MODULE_ID"];
		}
		$NS["CNT"] = IntVal($NS["CNT"]);
		if(!$bFull && strlen($NS["SESS_ID"])!=32)
			$NS["SESS_ID"] = md5(uniqid(""));

		$p1 = getmicrotime();

		$DB->StartTransaction();
		CSearch::ReindexLock();

		if($bFull && $NS["CLEAR"]!="Y")
		{
			CSearchTags::CleanCache();
			$DB->Query("TRUNCATE TABLE b_search_content_site", false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$DB->Query("TRUNCATE TABLE b_search_content_group", false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$DB->Query("TRUNCATE TABLE b_search_content_stem", false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$DB->Query("TRUNCATE TABLE b_search_tags", false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$DB->Query("TRUNCATE TABLE b_search_content_freq", false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$DB->Query("TRUNCATE TABLE b_search_content", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		$NS["CLEAR"] = "Y";

		clearstatcache();

		if(
			($NS["MODULE"]=="" || $NS["MODULE"]=="main") &&
			($NS["MODULE_ID"]=="" || $NS["MODULE_ID"]=="main")
		)
		{
			$arLangDirs = Array();
			$arFilter = Array("ACTIVE"=>"Y");
			if($NS["SITE_ID"]!="")
				$arFilter["ID"]=$NS["SITE_ID"];
			$r = CSite::GetList($by="sort", $order="asc", $arFilter);
			while($arR = $r->Fetch())
			{
				$path = $arR["DIR"];
				while(($l=strlen($path))>0 && $path[$l-1]=="/")
					$path = substr($path, 0, $l-1);
				$arLangDirs[$arR["ABS_DOC_ROOT"]."/".$path."/"] = $arR;
			}

			//get rid of duplicates
			$dub = Array();
			foreach($arLangDirs as $path=>$arR)
			{
				foreach($arLangDirs as $path2=>$arR2)
				{
					if($path==$path2) continue;
					if(substr($path, 0, strlen($path2)) == $path2)
						$dub[] = $path;
				}
			}

			foreach($dub as $p)
				unset($arLangDirs[$p]);

			foreach($arLangDirs as $arR)
			{
				$site = $arR["ID"];
				$path = $arR["DIR"];

				while(($l=strlen($path))>0 && $path[$l-1]=="/")
					$path = substr($path, 0, $l-1);

				if($max_execution_time>0 && $NS["MODULE"]=="main" && substr($NS["ID"]."/", 0, strlen($site."|".$path."/")) != $site."|".$path."/")
					continue;

				//for every folder
				CSearch::RecurseIndex(Array($site, $path), $max_execution_time, $NS);
				if($max_execution_time>0 && strlen($NS["MODULE"])>0)
				{
					$DB->Commit();
					return $NS;
				}
			}
		}

		$p1 = getmicrotime();

		//for every who wants to reindex
		$oCallBack = new CSearchCallback;
		$oCallBack->max_execution_time = $max_execution_time;
		$db_events = GetModuleEvents("search", "OnReindex");
		while($arEvent = $db_events->Fetch())
		{
			if($NS["MODULE_ID"]!="" && $NS["MODULE_ID"]!=$arEvent["TO_MODULE_ID"]) continue;
			if($max_execution_time>0 && strlen($NS["MODULE"])>0 && $NS["MODULE"]!= "main" && $NS["MODULE"]!=$arEvent["TO_MODULE_ID"]) continue;
			//here we get recordset
			$oCallBack->MODULE = $arEvent["TO_MODULE_ID"];
			$oCallBack->CNT = &$NS["CNT"];
			$oCallBack->SESS_ID = $NS["SESS_ID"];
			$r = &$oCallBack;
			$arResult = ExecuteModuleEvent($arEvent, $NS, $r, "Index");
			if(is_array($arResult)) //old way
			{
				if(count($arResult)>0)
				{
					for($i=0; $i<count($arResult); $i++)
					{
						$arFields = $arResult[$i];
						$ID = $arFields["ID"];
						if(strlen($ID)<=0) continue;
						unset($arFields["ID"]);
						$NS["CNT"]++;
						CSearch::Index($arEvent["TO_MODULE_ID"], $ID, $arFields, false, $NS["SESS_ID"]);
					}
				}
			}
			else  //new method
			{
				if($max_execution_time>0 && $arResult!==false && strlen(".".$arResult)>1)
				{
					$DB->Commit();
					return Array(
						"MODULE"=>$arEvent["TO_MODULE_ID"],
						"CNT"=>$oCallBack->CNT,
						"ID"=>$arResult,
						"CLEAR"=>$NS["CLEAR"],
						"SESS_ID"=>$NS["SESS_ID"],
						"SITE_ID"=>$NS["SITE_ID"],
						"MODULE_ID"=>$NS["MODULE_ID"],
					);
				}
			}
			$NS["MODULE"] = "";
		}

		if(!$bFull)
		{
			CSearch::DeleteOld($NS["SESS_ID"], $NS["MODULE_ID"], $NS["SITE_ID"]);
		}

		$DB->Commit();

		return $NS["CNT"];
	}

	function ReindexModule($MODULE_ID, $bFull=false)
	{
		global $DB, $APPLICATION;
		//$DB->StartTransaction();
		//CSearch::ReindexLock();

		if($bFull)
			CSearch::DeleteForReindex($MODULE_ID);

		$NS=Array("CLEAR"=>"N", "MODULE"=>"", "ID"=>"", "SESS_ID"=>md5(uniqid("")));
		//for every who wants to be reindexed
		$db_events = GetModuleEvents("search", "OnReindex");
		while($arEvent = $db_events->Fetch())
		{
			if($arEvent["TO_MODULE_ID"]!=$MODULE_ID) continue;

			$oCallBack = new CSearchCallback;
			$oCallBack->MODULE = $arEvent["TO_MODULE_ID"];
			$oCallBack->CNT = &$NS["CNT"];
			$oCallBack->SESS_ID = $NS["SESS_ID"];
			$r = &$oCallBack;

			$arResult = ExecuteModuleEvent($arEvent, $NS, $r, "Index");
			if(is_array($arResult)) //old way
			{
				if(count($arResult)>0)
				{
					for($i=0; $i<count($arResult); $i++)
					{
						$arFields = $arResult[$i];
						$ID = $arFields["ID"];
						if(strlen($ID)<=0) continue;
						unset($arFields["ID"]);
						$NS["CNT"]++;
						CSearch::Index($arEvent["TO_MODULE_ID"], $ID, $arFields, false, $NS["SESS_ID"]);
					}
				}
			}
			else  //new way
			{
				return Array("MODULE"=>$arEvent["TO_MODULE_ID"], "CNT"=>$oCallBack->CNT, "ID"=>$arResult, "CLEAR"=>$NS["CLEAR"], "SESS_ID"=>$NS["SESS_ID"]);
			}
		}

		if(!$bFull)
			CSearch::DeleteOld($NS["SESS_ID"], $MODULE_ID, $NS["SITE_ID"]);
	}
	//index one item (forum message, news, etc.)
	//combination of ($MODULE_ID, $ITEM_ID) is used to determine the documents
	function Index($MODULE_ID, $ITEM_ID, $arFields, $bOverWrite=false, $SEARCH_SESS_ID="")
	{
		global $DB;

		$arFields["MODULE_ID"] = $MODULE_ID;
		$arFields["ITEM_ID"] = $ITEM_ID;
		$db_events = GetModuleEvents("search", "BeforeIndex");
		while($arEvent = $db_events->Fetch())
			$arFields = ExecuteModuleEvent($arEvent, $arFields);
		unset($arFields["MODULE_ID"]);
		unset($arFields["ITEM_ID"]);

		$bTitle = array_key_exists("TITLE", $arFields);
		if($bTitle)
			$arFields["TITLE"] = trim($arFields["TITLE"]);
		$bBody = array_key_exists("BODY", $arFields);
		if($bBody)
			$arFields["BODY"] = trim($arFields["BODY"]);
		$bTags = array_key_exists("TAGS", $arFields);
		if($bTags)
			$arFields["TAGS"] = trim($arFields["TAGS"]);

		if(!array_key_exists("SITE_ID", $arFields) && array_key_exists("LID", $arFields))
			$arFields["SITE_ID"] = $arFields["LID"];

		if(array_key_exists("SITE_ID", $arFields))
		{
			if(!is_array($arFields["SITE_ID"]))
				$arFields["SITE_ID"] = Array($arFields["SITE_ID"]=>"");
			else
			{
				$bNotAssoc = true;
				$i = 0;
				foreach($arFields["SITE_ID"] as $k=>$val)
				{
					if("".$k!="".$i)
					{
						$bNotAssoc=false;
						break;
					}
					$i++;
				}
				if($bNotAssoc)
				{
					$x = $arFields["SITE_ID"];
					$arFields["SITE_ID"] = Array();
					foreach($x as $val)
						$arFields["SITE_ID"][$val] = "";
				}
			}
			reset($arFields["SITE_ID"]);
			if(count($arFields["SITE_ID"])<=0)
				return 0;
			else
				list($arFields["LID"], $url) = each($arFields["SITE_ID"]);
		}
		if(is_set($arFields, "LID"))
		{
			$strSql = "
				SELECT CR.RANK
				FROM b_search_custom_rank CR
				WHERE CR.SITE_ID='".$DB->ForSQL($arFields["LID"])."'
				AND CR.MODULE_ID='".$DB->ForSQL($MODULE_ID)."'
				".(is_set($arFields, "PARAM1")?"AND (CR.PARAM1 IS NULL OR CR.PARAM1='".$DB->ForSQL($arFields["PARAM1"])."')":"")."
				".(is_set($arFields, "PARAM2")?"AND (CR.PARAM2 IS NULL OR CR.PARAM2='".$DB->ForSQL($arFields["PARAM2"])."')":"")."
				".($ITEM_ID<>""?"AND (CR.ITEM_ID IS NULL OR CR.ITEM_ID='".$DB->ForSQL($ITEM_ID)."')":"")."
				ORDER BY
					PARAM1 DESC, PARAM2 DESC, ITEM_ID DESC
			";
			$r = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$arFields["CUSTOM_RANK_SQL"]=$strSql;
			if($arResult = $r->Fetch())
				$arFields["CUSTOM_RANK"]=$arResult["RANK"];
		}
		//$DB->StartTransaction();
		if(is_set($arFields, "PERMISSIONS"))
		{
			$strGroups = "0";
			foreach($arFields["PERMISSIONS"] as $i)
				if(IntVal($i)>0)
					$strGroups .= ",".IntVal($i);
		}

		$strSqlSelect = "";
		if($bBody) $strSqlSelect .= ",BODY";
		if($bTitle) $strSqlSelect .= ",TITLE";
		if($bTags) $strSqlSelect .= ",TAGS";

		$strSql =
			"SELECT ID, ".CSearch::FormatDateString("DATE_CHANGE")." as DATE_CHANGE
			".$strSqlSelect."
			FROM b_search_content
			WHERE MODULE_ID = '".$DB->ForSQL($MODULE_ID)."'
				AND ITEM_ID = '".$DB->ForSQL($ITEM_ID)."' ";

		$r = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if($arResult = $r->Fetch())
		{
			$ID = $arResult["ID"];

			if($bTitle && $bBody && strlen($arFields["BODY"])<=0 && strlen($arFields["TITLE"])<=0)
			{
				CSearchTags::CleanCache("", $ID);
				CSearch::CleanFreqCache($ID);
				$DB->Query("DELETE FROM b_search_content_group WHERE SEARCH_CONTENT_ID = ".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$DB->Query("DELETE FROM b_search_content_site WHERE SEARCH_CONTENT_ID = ".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$DB->Query("DELETE FROM b_search_content_stem WHERE SEARCH_CONTENT_ID = ".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$DB->Query("DELETE FROM b_search_tags WHERE SEARCH_CONTENT_ID = ".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$DB->Query("DELETE FROM b_search_content WHERE ID = ".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				//$DB->Commit();
				return 0;
			}

			if(is_set($arFields, "PERMISSIONS"))
			{
				$DB->Query("DELETE FROM b_search_content_group WHERE SEARCH_CONTENT_ID = ".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$strSql = "
					INSERT INTO b_search_content_group(SEARCH_CONTENT_ID, GROUP_ID)
					SELECT ".$ID.", ID
					FROM b_group
					WHERE ID IN (".$strGroups.")";
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}

			if(is_set($arFields, "SITE_ID"))
			{
				$DB->Query("DELETE FROM b_search_content_site WHERE SEARCH_CONTENT_ID = ".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				foreach($arFields["SITE_ID"] as $site=>$url)
				{
					$strSql = "
						INSERT INTO b_search_content_site(SEARCH_CONTENT_ID, SITE_ID, URL)
						VALUES(".$ID.", '".$site."', '".$DB->ForSql($url, 2000)."')";
					$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				}
			}

			if(is_set($arFields, "LAST_MODIFIED"))
				$arFields["DATE_CHANGE"] = $DB->FormatDate($arFields["LAST_MODIFIED"], CLang::GetDateFormat(), "DD.MM.YYYY HH:MI:SS");
			if(!$bOverWrite && is_set($arFields, "DATE_CHANGE") && $arFields["DATE_CHANGE"]==$arResult["DATE_CHANGE"])
			{
				if(strlen($SEARCH_SESS_ID)>0)
					$DB->Query("UPDATE b_search_content SET UPD='".$SEARCH_SESS_ID."' WHERE ID = ".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				//$DB->Commit();
				return $ID;
			}

			unset($arFields["MODULE_ID"]);
			unset($arFields["ITEM_ID"]);

			if($bBody || $bTitle || $bTags)
			{
				if($bTitle)
					$content = $arFields["TITLE"]."\r\n";
				else
					$content = $arResult["TITLE"]."\r\n";

				if($bBody)
					$content .= $arFields["BODY"]."\r\n";
				else
					$content .= $arResult["BODY"]."\r\n";

				if($bTags)
					$content .= $arFields["TAGS"];
				else
					$content .= $arResult["TAGS"];

				$content = preg_replace ("'&#(\d+);'e", "chr(\\1)", $content);
				$arFields["SEARCHABLE_CONTENT"] = ToUpper($content);
			}

			if(strlen($SEARCH_SESS_ID)>0)
				$arFields["UPD"] = $SEARCH_SESS_ID;

			CSearch::Update($ID, $arFields);

			if(is_set($arFields, "SEARCHABLE_CONTENT"))
			{
				CSearch::CleanFreqCache($ID);
				$DB->Query("DELETE FROM b_search_content_stem WHERE SEARCH_CONTENT_ID = ".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				CSearch::StemIndex($arFields["SITE_ID"], $ID, $arFields["SEARCHABLE_CONTENT"]);
				CSearch::CleanFreqCache($ID);
			}

			if($bTags && ($arResult["TAGS"] != $arFields["TAGS"]))
			{
				$tags = preg_replace ("'&#(\d+);'e", "chr(\\1)", $arFields["TAGS"]);
				CSearchTags::CleanCache("", $ID);
				$DB->Query("DELETE FROM b_search_tags WHERE SEARCH_CONTENT_ID = ".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				CSearch::TagsIndex($arFields["SITE_ID"], $ID, $tags);
			}
		}
		else
		{
			if($bTitle && $bBody && strlen($arFields["BODY"])<=0 && strlen($arFields["TITLE"])<=0)
			{
				//$DB->Commit();
				return 0;
			}

			$arFields["MODULE_ID"] = $MODULE_ID;
			$arFields["ITEM_ID"] = $ITEM_ID;

			$content = $arFields["TITLE"]."\r\n".$arFields["BODY"]."\r\n".$arFields["TAGS"];
			$content = preg_replace ("'&#(\d+);'e", "chr(\\1)", $content);
			$arFields["SEARCHABLE_CONTENT"] = ToUpper($content);

			if($SEARCH_SESS_ID!="")
				$arFields["UPD"] = $SEARCH_SESS_ID;

			$ID = CSearch::Add($arFields);

			if(is_set($arFields, "PERMISSIONS"))
			{
				$strSql = "
					INSERT INTO b_search_content_group(SEARCH_CONTENT_ID, GROUP_ID)
					SELECT ".$ID.", ID
					FROM b_group
					WHERE ID IN (".$strGroups.")";
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}

			foreach($arFields["SITE_ID"] as $site=>$url)
			{
				$strSql = "
					INSERT INTO b_search_content_site(SEARCH_CONTENT_ID, SITE_ID, URL)
					VALUES(".$ID.", '".$site."', '".$DB->ForSql($url, 2000)."')";
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}

			CSearch::StemIndex($arFields["SITE_ID"], $ID, $arFields["SEARCHABLE_CONTENT"]);
			$tags = preg_replace ("'&#(\d+);'e", "chr(\\1)", $arFields["TAGS"]);
			CSearch::TagsIndex($arFields["SITE_ID"], $ID, $tags);
			CSearch::CleanFreqCache($ID);
		}
		//$DB->Commit();

		return $ID;
	}

	function ReindexFile($path, $SEARCH_SESS_ID="")
	{
		global $APPLICATION, $DB;

		CMain::InitPathVars($site, $path);
		$DOC_ROOT = CSite::GetSiteDocRoot($site);

		if(!file_exists($DOC_ROOT."/".$path))
			return 0;

		if(!CSearch::CheckPath($path))
			return 0;

		$max_file_size = COption::GetOptionInt("search", "max_file_size", 0);
		if($max_file_size > 0 && filesize($DOC_ROOT."/".$path) > $max_file_size*1024)
			return 0;

		if(strlen($SEARCH_SESS_ID) > 0)
		{
			$strSql = "
				SELECT ID
				FROM b_search_content
				WHERE MODULE_ID = 'main'
					AND ITEM_ID = '".$DB->ForSQL($site."|".$path)."'
					AND ".CSearch::FormatDateString("DATE_CHANGE")." = '".date("d.m.Y H:i:s", filemtime($DOC_ROOT."/".$path))."'
			";
			$r = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if($arR = $r->Fetch())
			{
				$strSql = "UPDATE b_search_content SET UPD='".$SEARCH_SESS_ID."' WHERE ID = ".$arR["ID"];
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				return $arR["ID"];
			}
		}

		$arrFile = false;
		$events = GetModuleEvents("search", "OnSearchGetFileContent");
		while($arEvent = $events->Fetch())
		{
			if($arrFile = ExecuteModuleEvent($arEvent, $DOC_ROOT."/".$path))
				break;
		}
		if(!is_array($arrFile))
			$arrFile = ParseFileContent($APPLICATION->GetFileContent($DOC_ROOT."/".$path));

		$title = CSearch::KillTags(trim($arrFile["TITLE"]));

		if(strlen($title)<=0) return 0;

		//strip out all the tags
		$filesrc = CSearch::KillTags($arrFile["CONTENT"]);

		$arGroups = CSearch::GetGroupCached();
		$arGPerm = Array();
		foreach($arGroups as $group_id)
		{
			$p = $APPLICATION->GetFileAccessPermission(Array($site, $path), Array($group_id));
			if($p >= "R")
			{
				$arGPerm[] = $group_id;
				if($group_id==2) break;
			}
		}

		$tags = COption::GetOptionString("search", "page_tag_property");

		//save to database
		$ID = CSearch::Index("main", $site."|".$path,
			Array(
				"SITE_ID" => $site,
				"DATE_CHANGE" => date("d.m.Y H:i:s", filemtime($DOC_ROOT."/".$path)+1),
				"PARAM1" => "",
				"PARAM2" => "",
				"URL" => $path,
				"PERMISSIONS" => $arGPerm,
				"TITLE" => $title,
				"BODY" => $filesrc,
				"TAGS" => array_key_exists($tags, $arrFile["PROPERTIES"])? $arrFile["PROPERTIES"][$tags]: "",
			), false, $SEARCH_SESS_ID
		);

		return $ID;
	}

	function RecurseIndex($path=Array(), $max_execution_time = 0, &$NS)
	{
		global $APPLICATION;
		CMain::InitPathVars($site, $path);
		$DOC_ROOT = CSite::GetSiteDocRoot($site);
		$abs_path = $DOC_ROOT.$path;

		if(strlen($site)<=0)
			return 0;

		if(!file_exists($abs_path) || !is_dir($abs_path))
			return 0;
		$handle  = @opendir($abs_path);
		while(false !== ($file = @readdir($handle)))
		{
			if($file == "." || $file == "..") continue;
			if(is_dir($abs_path."/".$file))
			{
				if($path."/".$file=="/bitrix")continue;
				//if($path."/".$file!="/")continue;
				//this is not first step and we had stopped here, so go on to reindex
				if($max_execution_time<=0 || strlen($NS["MODULE"])<=0 || ($NS["MODULE"]=="main" && substr($NS["ID"]."/", 0, strlen($site."|".$path."/".$file."/")) == $site."|".$path."/".$file."/"))
				{
					$prevSTEP_ID = $NS["ID"];
					$new_site = CSite::GetSiteByFullPath($DOC_ROOT.$path."/".$file);
					if(CSearch::RecurseIndex(Array($new_site, $path."/".$file), $max_execution_time, $NS)===false)
						return false;
				}
				else //all done
					continue;
			}
			else
			{
				//not the first step and we found last file from previos one
				if($max_execution_time>0
					&& strlen($NS["MODULE"])>0
					&& $NS["MODULE"]=="main"
					&& $NS["ID"] == $site."|".$path."/".$file
					)
				{
					$NS["MODULE"] = "";
				}
				elseif(strlen($NS["MODULE"])<=0)
				{
					$ID = CSearch::ReindexFile(Array($site, $path."/".$file), $NS["SESS_ID"]);
					if(IntVal($ID)>0)
					{
						$NS["CNT"] = IntVal($NS["CNT"]) + 1;
					}
				}

				if($max_execution_time>0 && (getmicrotime() - START_EXEC_TIME > $max_execution_time))
				{
					$NS["MODULE"] = "main";
					$NS["ID"] = $site."|".$path."/".$file;
					return false;
				}
			}
		}
		return true;
	}

	function KillTags($str)
	{
		while(($p1 = strpos($str, "<?"))!==false)
		{
			$p = $p1+3;
			$q = "";$bSl=false;
			while($p < strlen($str))
			{
				if($q!="" && $str[$p]=="\\")
					$bSl = true;
				else if($q!="" && $bSl)
					$bSl = false;
				else if($q!="" && !$bSl && $str[$p]==$q)
					$q = "";
				else if($q=="" && ($str[$p]=="\"" || $str[$p]=="'"))
					$q = $str[$p];
				else if($q=="" && $str[$p]==">" && $str[$p-1]=="?")
					break;
				$p++;
			}
			$str = substr($str, 0, $p1).substr($str, $p+1);
		}

		$search = array (
			"'<script[^>]*?>.*?</script>'si",  // Strip out javascript
			"'<style[^>]*?>.*?</style>'si",  // Strip out styles
			"'<select[^>]*?>.*?</select>'si",  // Strip out <select></select>
			"'<head[^>]*?>.*?</head>'si",  // Strip out <head></head>
			"'<tr[^>]*?>'",
			"'<[^>]*?>'",
			"'([\\r\\n])[\\s]+'",  // Strip out white space
			"'&(quot|#34);'i",  // Replace html entities
			"'&(amp|#38);'i",
			"'&(lt|#60);'i",
			"'&(gt|#62);'i",
			"'&(nbsp|#160);'i",
			"'[ ]+ '",
		);

		$replace = array (
			"",
			"",
			"",
			"",
			"\r\n",
			"\r\n",
			"\\1",
			"\"",
			"&",
			"<",
			">",
			" ",
			" ",
		);

		$str = preg_replace ($search, $replace, $str);

		return $str;
	}

	function OnChangeFile($path, $site)
	{
		CSearch::ReindexFile(Array($site, $path));
	}

	function OnGroupDelete($ID)
	{
		global $DB;
		$DB->Query("DELETE FROM b_search_content_group WHERE GROUP_ID=".IntVal($ID), false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}

	function __PrepareFilter($arFilter, &$bIncSites, $strSearchContentAlias="sc.")
	{
		global $DB;
		$arSql = array();
		if(!is_array($arFilter))
			$arFilter = array();
		foreach($arFilter as $field=>$val)
		{
			if($val === false)
				continue;
			$field = strtoupper($field);
			switch($field)
			{
			case "MODULE_ID":
			case "ITEM_ID":
			case "PARAM1":
			case "PARAM2":
				if(is_array($val))
				{
					$val=array_unique($val);
					if(count($val)==0)
						break;
					elseif(count($val)==1)
						$val=$val[0];
					else
					{
						$arSql[]=$strSearchContentAlias.$field." in ('".implode("','", array_map(array("CDatabase","ForSql"), $val))."')";
						break;
					}
				}
				$arSql[] =
					(
					strlen($val)>0?
						$strSearchContentAlias.$field."='".$DB->ForSql($val)."'"
					:
						"(".$strSearchContentAlias.$field."='' OR ".$strSearchContentAlias.$field." IS NULL)"
					);
				break;
			case "URL":
				if(is_array($val))
				{
					$strInc = "";
					foreach($val as $url_i)
					{
						if(strlen($strInc)>0)
							$strInc .= " OR ";
						$strInc .= "(".$strSearchContentAlias."URL LIKE '".$DB->ForSql($url_i)."' OR scsite.URL LIKE '".$DB->ForSql($url_i)."')";
					}
					if($strInc!="")
						$arSql[] = "(".$strInc.")";
				}
				else
					$arSql[] =
						"(".$strSearchContentAlias."URL LIKE '".$DB->ForSql($val)."' OR scsite.URL LIKE '".$DB->ForSql($val)."')";
				$bIncSites = true;
				break;
			//case "LID":
			case "SITE_ID":
				$arSql[] ="scsite.SITE_ID='".$DB->ForSql($val, 2)."'";
				$bIncSites = true;
				break;
			case "DATE_CHANGE":
				if(strlen($val)>0)
					$arSql[] = "(".$strSearchContentAlias."DATE_CHANGE >= ".$DB->CharToDateFunction($DB->ForSql($val), "FULL").")";
				break;
			case "CHECK_DATES":
				if($val=="Y")
					$arSql[] = "((".$strSearchContentAlias."DATE_FROM IS NULL OR ".$strSearchContentAlias."DATE_FROM <= ".$DB->CurrentTimeFunction().") AND (".$strSearchContentAlias."DATE_TO IS NULL OR ".$strSearchContentAlias."DATE_TO >= ".$DB->CurrentTimeFunction()."))";
				break;
			}
		}
		if(count($arSql)>0)
			return implode("\nAND ", $arSql);
		else
			return "";
	}

	function __PrepareSort($aSort=array(), $strSearchContentAlias="sc.", $bTagsCloud = false)
	{
		$arOrder = array();
		if(!is_array($aSort))
			$aSort=array($aSort => "ASC");

		if($bTagsCloud)
		{
			foreach($aSort as $key => $ord)
			{
				$ord = strtoupper($ord) <> "ASC"? "DESC": "ASC";
				$key = strtoupper($key);
				switch($key)
				{
					case "DATE_CHANGE":
						$arOrder[] = "DC_TMP ".$ord;
						break;
					case "NAME":
					case "CNT":
						$arOrder[] = $key." ".$ord;
						break;
				}
			}
			if(count($arOrder) == 0)
			{
				$arOrder[]= "NAME ASC";
			}
		}
		else
		{
			foreach($aSort as $key => $ord)
			{
				$ord = strtoupper($ord) <> "ASC"? "DESC": "ASC";
				$key = strtoupper($key);
				switch($key)
				{
					case "ID":
					case "MODULE_ID":
					case "ITEM_ID":
					case "LID":
					case "TITLE":
					case "PARAM1":
					case "PARAM2":
					case "UPD":
					case "DATE_FROM":
					case "DATE_TO":
					case "URL":
					case "RANK":
					case "CUSTOM_RANK":
					case "TITLE_RANK":
						$arOrder[]=$key." ".$ord;
						break;
					case "DATE_CHANGE":
						$arOrder[]=$strSearchContentAlias.$key." ".$ord;
						break;
				}
			}
			if(count($arOrder) == 0)
			{
				$arOrder[]= "CUSTOM_RANK DESC";
				$arOrder[]= "RANK DESC";
				$arOrder[]= $strSearchContentAlias."DATE_CHANGE DESC";
			}
		}

		return " ORDER BY ".implode(", ",$arOrder);
	}

	function Add($arFields)
	{
		global $DB;
		if(is_set($arFields, "LAST_MODIFIED"))
			$arFields["DATE_CHANGE"] = $arFields["LAST_MODIFIED"];
		elseif(is_set($arFields, "DATE_CHANGE"))
			$arFields["DATE_CHANGE"] = $DB->FormatDate($arFields["DATE_CHANGE"], "DD.MM.YYYY HH.MI.SS", CLang::GetDateFormat());
		return $DB->Add("b_search_content", $arFields, array("BODY", "TAGS", "SEARCHABLE_CONTENT"));
	}

	function OnChangeFilePermissions($path, $permission = array(), $old_permission = array(), $arGroups = false)
	{

		global $APPLICATION, $DB;

		CMain::InitPathVars($site, $path);
		$DOC_ROOT = CSite::GetSiteDocRoot($site);
		$path=rtrim($path, "/");

		if(!is_array($arGroups))
		{
			$arGroups = CSearch::GetGroupCached();
			//Check if anonymous permission was changed
			if(!array_key_exists(2, $permission) && array_key_exists("*", $permission))
				$permission[2] = $permission["*"];
			if(!is_array($old_permission))
				$old_permission = array();
			if(!array_key_exists(2, $old_permission) && array_key_exists("*", $old_permission))
				$old_permission[2] = $old_permission["*"];
			//And if not when will do nothing
			if(
				(array_key_exists(2, $permission)
				&& $permission[2] >= "R")
				&& array_key_exists(2, $old_permission)
				&& $old_permission[2] >= "R"
			)
			{
				return;
			}
		}

		if(file_exists($DOC_ROOT.$path))
		{
			@set_time_limit(300);
			if(is_dir($DOC_ROOT.$path))
			{
				$handle = @opendir($DOC_ROOT.$path);
				while(false !== ($file = @readdir($handle)))
				{
					if($file == "." || $file == "..")
						continue;

					$full_file = $path."/".$file;
					if($full_file == "/bitrix")
						continue;

					if(is_dir($DOC_ROOT.$full_file) || CSearch::CheckPath($full_file))
						CSearch::OnChangeFilePermissions(array($site, $full_file), array(), array(), $arGroups);
				}
			}
			else//if(is_dir($DOC_ROOT.$path))
			{
				$rs = $DB->Query("
					SELECT SC.ID, SCG.GROUP_ID
					FROM b_search_content SC
					INNER JOIN b_search_content_group SCG on SCG.SEARCH_CONTENT_ID = SC.ID
					WHERE
						MODULE_ID='main'
						AND ITEM_ID='".$DB->ForSql($site."|".$path)."'
					ORDER BY
						SCG.GROUP_ID
					", false, "File: ".__FILE__."<br>Line: ".__LINE__
				);
				if($ar = $rs->Fetch())
				{
					$SEARCH_CONTENT_ID = $ar["ID"];
					//First read database groups
					$arDBGroups = array();
					do
					{
						$arDBGroups[$ar["GROUP_ID"]] = $ar["GROUP_ID"];
					} while ($ar = $rs->Fetch());
					//Second find out actual groups
					$arNewGroups = array();
					foreach($arGroups as $group_id)
					{
						$p = $APPLICATION->GetFileAccessPermission(array($site, $path), array($group_id));
						if($p >= "R")
						{
							$arNewGroups[$group_id] = $group_id;
							if($group_id == 2)
								break;
						}
					}
					//Third: we will delete all db groups those are no more have access
					$arGroupsToDelete = array();
					foreach($arDBGroups as $db_group_id)
					{
						if(!array_key_exists($db_group_id, $arNewGroups))
							$arGroupsToDelete[] = $db_group_id;
					}
					if(count($arGroupsToDelete) > 0)
					{
						$DB->Query("
							DELETE FROM b_search_content_group
							WHERE SEARCH_CONTENT_ID=".$SEARCH_CONTENT_ID."
							AND GROUP_ID in (".implode(",", $arGroupsToDelete).")
							", false, "File: ".__FILE__."<br>Line: ".__LINE__
						);
					}
					//At last add new groups
					$arGroupsToAdd = array();
					foreach($arNewGroups as $new_group_id)
					{
						if(!array_key_exists($new_group_id, $arDBGroups))
							$arGroupsToAdd[] = $new_group_id;
					}
					if(count($arGroupsToAdd) > 0)
					{
						$DB->Query("
							INSERT INTO b_search_content_group(SEARCH_CONTENT_ID, GROUP_ID)
							SELECT ".$SEARCH_CONTENT_ID.", G.ID
							FROM b_group G
							WHERE G.ID IN (".implode(",",$arGroupsToAdd).")
							", false, "File: ".__FILE__."<br>Line: ".__LINE__
						);
					}
				}
			} //if(is_dir($DOC_ROOT.$path))
		}//if(file_exists($DOC_ROOT.$path))
	}
}

class CAllSearchQuery
{
	var $m_query;
	var $m_words;
	var $m_stemmed_words;
	var $m_fields;
	var $m_kav;
	var $default_query_type;
	var $rus_bool_lang;
	var $m_casematch;
	var $error;
	var $bTagsSearch = false;
	var $m_tags_words;
	var $bStemming = false;

	function __construct($default_query_type = "and", $rus_bool_lang = "yes", $m_casematch = 0, $site_id = "")
	{
		return $this->CSearchQuery($default_query_type, $rus_bool_lang, $m_casematch, $site_id);
	}

	function CSearchQuery($default_query_type = "and", $rus_bool_lang = "yes", $m_casematch = 0, $site_id = "")
	{
		$this->m_query  = "";
		$this->m_stemmed_words = array();
		$this->m_tags_words = array();
		$this->m_fields = "";
		$this->default_query_type = $default_query_type;
		$this->rus_bool_lang = $rus_bool_lang;
		$this->m_casematch = $m_casematch;
		$this->m_kav = array();
		$this->error = "";

		$db_site_tmp = CSite::GetByID($site_id);
		if ($ar_site_tmp = $db_site_tmp->Fetch())
			$this->m_lang=$ar_site_tmp["LANGUAGE_ID"];
		else
			$this->m_lang="en";
		stemming("", $this->m_lang);
	}

	function GetQueryString($fields, $query, $bTagsSearch = false, $bUseStemming = true)
	{
		$this->m_words = Array();
		$this->m_fields = explode(",", $fields);
		if(!is_array($this->m_fields))
			$this->m_fields=array($this->m_fields);

		$this->bTagsSearch = $bTagsSearch;
		//In case there is no masks used we'll keep list
		//of all tags in this memeber
		//to perform optimization
		$this->m_tags_words = array();

		$query = preg_replace ("'&#(\\d+);'e", "chr(\\1)", $query);
		$query = $this->CutKav($query);

		//Assume query does not have any word which can be stemmed
		$this->bStemming = false;
		if(!$this->bTagsSearch && $bUseStemming && COption::GetOptionString("search", "use_stemming", "N")=="Y")
		{
			//In case when at least one word found: $this->bStemming = true
			$stem_query = $this->StemQuery($query, $this->m_lang);
			if($this->bStemming === true)
				$query = $stem_query;
		}
		$query = $this->ParseQ($query);
		if($query == "( )" || strlen($query)<=0)
		{
			$this->error=GetMessage("SEARCH_ERROR3");
			$this->errorno=3;
			return false;
		}

		$query = $this->PrepareQuery($query);

		return $query;
	}

	function CutKav($query)
	{
		$bdcnt = 0;
		while (eregi("\"([^\"]*)\"",$query,$pt))
		{
			$res = $pt[1];
			if(strlen(trim($pt[1]))>0)
			{
				$trimpt = $bdcnt."cut5";
				$this->m_kav[$trimpt] = $res;
				$query = str_replace("\"".$pt[1]."\"", " ".$trimpt." ", $query);
			}
			else
			{
				$query = str_replace("\"".$pt[1]."\"", " ", $query);
			}
			$bdcnt++;
			if($bdcnt>100) break;
		}

		$bdcnt = 0;
		while (eregi("'([^']*)'",$query,$pt))
		{
			$res = $pt[1];
			if(strlen(trim($pt[1]))>0)
			{
				$trimpt = $bdcnt."cut6";
				$this->m_kav[$trimpt] = $res;
				$query = str_replace("'".$pt[1]."'", " ".$trimpt." ", $query);
			}
			else
			{
				$query = str_replace("'".$pt[1]."'", " ", $query);
			}
			$bdcnt++;
			if($bdcnt>100) break;
		}
		return $query;
	}

	function ParseQ($q)
	{
		$q = trim($q);
		if(strlen($q) <= 0)
			return '';

		$q = $this->ParseStr($q);

		$q = str_replace(
			array("&"   , "|"   , "~"  , "("  , ")"),
			array(" && ", " || ", " ! ", " ( ", " ) "),
			$q
		);
		$q = "( $q )";
		$q = preg_replace("/\\s+/".BX_UTF_PCRE_MODIFIER, " ", $q);

		return $q;
	}

	function ParseStr($qwe)
	{
		$qwe = str_replace("*", "%", $qwe);
		$qwe=preg_replace("/[%]{2,}/", "%", $qwe);
		$qwe=preg_replace("/[!@#$^_={};':<>?,.[\\]\"\\/]{1,}/", " ", $qwe);
		$qwe=trim($qwe);

		// query language normalizer
		if ($this->rus_bool_lang == 'yes')
		{
			$qwe=eregi_replace(" {0,}\| {0,}| {1,}or {1,}| {1,}".GetMessage("SEARCH_TERM_OR")." {1,}","|",$qwe);
			$qwe=eregi_replace(" {0,}\+ {0,}| {0,}\& {0,}| {1,}and {1,}| {1,}".GetMessage("SEARCH_TERM_AND")." {1,}","&",$qwe);
			$qwe=eregi_replace(" {0,}\~ {0,}| {1,}not {1,}| {1,}without {1,}| {1,}".GetMessage("SEARCH_TERM_NOT_1")." {1,}| {1,}".GetMessage("SEARCH_TERM_NOT_2")." {1,}","~",$qwe);
		}
		else
		{
			$qwe=eregi_replace(" {0,}\| {0,}| {1,}or {1,}","|",$qwe);
			$qwe=eregi_replace(" {0,}\+ {0,}| {0,}\& {0,}| {1,}and {1,}","&",$qwe);
			$qwe=eregi_replace(" {0,}\~ {0,}| {1,}not {1,}| {1,}without {1,}","~",$qwe);
		}

		$qwe=ereg_replace(" {0,}\( {0,}","(",$qwe);
		$qwe=ereg_replace(" {0,}\) {0,}",")",$qwe);

		// default query type is and
		if (strtolower($this->default_query_type) == 'or')
		{
			$qwe=ereg_replace(" {1,}","|",$qwe);
			$qwe=ereg_replace("\&\|{1,}","|",$qwe);
			$qwe=ereg_replace("\|\&{1,}","|",$qwe);
		}
		else
		{
			$qwe=ereg_replace(" {1,}","&",$qwe);
			$qwe=ereg_replace("\&\|{1,}","&",$qwe);
			$qwe=ereg_replace("\|\&{1,}","&",$qwe);
		}

		// remove unnesessary boolean operators
		$qwe=ereg_replace("\|{1,}","|",$qwe);
		$qwe=ereg_replace("&{1,}","&",$qwe);
		$qwe=ereg_replace("~{1,}","~",$qwe);
		$qwe=ereg_replace("\|\&\|","&",$qwe);
		$qwe=ereg_replace("[\|\&\~]{1,}$","",$qwe);
		$qwe=ereg_replace("^[\|\&]{1,}","",$qwe);

		// transform "w1 ~w2" -> "w1 default_op ~ w2"
		// ") ~w" -> ") default_op ~w"
		// "w ~ (" -> "w default_op ~("
		// ") w" -> ") default_op w"
		// "w (" -> "w default_op ("
		// ")(" -> ") default_op ("
		if(strtolower($this->default_query_type) == 'or')
		{
			$qwe=ereg_replace("([^\&\~\|\(\)]+)~([^\&\~\|\(\)]+)","\\1|~\\2",$qwe);
			$qwe=ereg_replace("\)~{1,}",")|~",$qwe);
			$qwe=ereg_replace("~{1,}\(","~|(",$qwe);
			$qwe=ereg_replace("\)([^\&\~\|\(\)]+)",")|\\1",$qwe);
			$qwe=ereg_replace("([^\&\~\|\(\)]+)\(","\\1|(",$qwe);
			$qwe=ereg_replace("\) *\(",")|(",$qwe);
		}
		else
		{
			$qwe=ereg_replace("([^\&\~\|\(\)]+)~([^\&\~\|\(\)]+)","\\1&~\\2",$qwe);
			$qwe=ereg_replace("\)~{1,}",")&~",$qwe);
			$qwe=ereg_replace("~{1,}\(","&~(",$qwe);
			$qwe=ereg_replace("\)([^\&\~\|\(\)]+)",")&\\1",$qwe);
			$qwe=ereg_replace("([^\&\~\|\(\)]+)\(","\\1&(",$qwe);
			$qwe=ereg_replace("\) *\(",")&(",$qwe);
		}

		// remove unnesessary boolean operators
		$qwe=ereg_replace("\|{1,}","|",$qwe);
		$qwe=ereg_replace("&{1,}","&",$qwe);

		// remove errornous format of query - ie: '(&', '&)', '(|', '|)', '~&', '~|', '~)'
		$qwe=ereg_replace("\(\&{1,}","(",$qwe);
		$qwe=ereg_replace("\&{1,}\)",")",$qwe);
		$qwe=ereg_replace("\~{1,}\)",")",$qwe);
		$qwe=ereg_replace("\(\|{1,}","(",$qwe);
		$qwe=ereg_replace("\|{1,}\)",")",$qwe);
		$qwe=ereg_replace("\~{1,}\&{1,}","&",$qwe);
		$qwe=ereg_replace("\~{1,}\|{1,}","|",$qwe);

		$qwe=ereg_replace("^[\|\&]{1,}","",$qwe);
		$qwe=ereg_replace("[\|\&\~]{1,}$","",$qwe);
		$qwe=ereg_replace("\|\&","&",$qwe);
		$qwe=ereg_replace("\&\|","|",$qwe);

		return($qwe);
	}

	function StemWord($w)
	{
		static $ereg_ru = false;
		$wu = ToUpper($w);
		if(ereg("^(OR|AND|NOT|WITHOUT)$", $wu))
		{
			return $w;
		}
		elseif($this->rus_bool_lang == 'yes')
		{
			if($ereg_ru === false)
				$ereg_ru = "^(".ToUpper(GetMessage("SEARCH_TERM_OR")."|".GetMessage("SEARCH_TERM_AND")."|".GetMessage("SEARCH_TERM_NOT_1")."|".GetMessage("SEARCH_TERM_NOT_2")).")$";
			if(ereg($ereg_ru, $wu))
				return $w;
		}
		if(ereg("(cut|CUT)[56]", $w))
			return $w;
		$arrStem = array_keys(stemming($w, $this->m_lang));
		if(count($arrStem) < 1)
			return " ";
		else
		{
			$this->bStemming = true;
			return $arrStem[0];
		}
	}

	function StemQuery($q, $lang="en")
	{
		$letters = constant("STEMMING_TOK_".$lang);
		return preg_replace("/([".$letters."]+)/e".BX_UTF_PCRE_MODIFIER, "CAllSearchQuery::StemWord('\$1')", $q);
	}

	function PrepareQuery($q)
	{
		$state = 0;
		$qu = "";
		$n = 0;
		$this->error = "";

		$t=strtok($q," ");

		while (($t!="") && ($this->error==""))
		{
			switch ($state)
			{
			case 0:
				if (($t=="||") || ($t=="&&") || ($t==")"))
				{
					$this->error=GetMessage("SEARCH_ERROR2")." ".$t;
					$this->errorno=2;
				}
				elseif ($t=="!")
				{
					$state=0;
					$qu="$qu NOT ";
					break;
				}
				elseif ($t=="(")
				{
					$n++;
					$state=0;
					$qu="$qu(";
				}
				else
				{
					$state=1;
					$qu="$qu ".$this->BuildWhereClause($t)." ";
				}
				break;

			case 1:
				if (($t=="||") || ($t=="&&"))
				{
					$state=0;
					if($t=='||') $qu="$qu OR ";
					else $qu="$qu AND ";
				}
				elseif ($t==")")
				{
					$n--;
					$state=1;
					$qu="$qu)";
				}
				else
				{
					$this->error=GetMessage("SEARCH_ERROR2")." ".$t;
					$this->errorno=2;
				}
				break;
			}
			$t=strtok(" ");
		}

		if (($this->error=="") && ($n != 0))
		{
			$this->error=GetMessage("SEARCH_ERROR1");
			$this->errorno=1;
		}
		if ($this->error!="") return 0;

		return $qu;
	}
}

class CSearchCallback
{
	var $MODULE="";
	var $max_execution_time=0;
	var $CNT=0;
	var $SESS_ID = "";
	function Index($arFields)
	{
		$ID = $arFields["ID"];
		if($ID=="")
			return true;
		unset($arFields["ID"]);
		CSearch::Index($this->MODULE, $ID, $arFields, false, $this->SESS_ID);
		$this->CNT = $this->CNT+1;
		if($this->max_execution_time>0 && getmicrotime() - START_EXEC_TIME > $this->max_execution_time)
			return false;
		else
			return true;
	}
}
?>
