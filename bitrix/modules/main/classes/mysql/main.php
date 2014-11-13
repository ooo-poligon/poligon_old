<?
/*
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2007 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
*/

require_once(substr(__FILE__, 0, strlen(__FILE__) - strlen("/classes/mysql/main.php"))."/bx_root.php");

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/main.php");

class CMain extends CAllMain
{
	function __GetConditionFName()
	{
		return "`CONDITION`";
	}

	function EpilogActions()
	{
		//отправим сообщения
		CEvent::CheckEvents();
	}

	function GetLang($cur_dir=false, $cur_host=false)
	{
		global $DB, $lang, $DOCUMENT_ROOT, $MAIN_LANGS_CACHE, $MAIN_LANGS_ADMIN_CACHE;

		if($cur_dir===false)
			$cur_dir = $this->GetCurDir();
		if($cur_host===false)
			$cur_host = $_SERVER["HTTP_HOST"];

		if(substr($cur_dir, 0, strlen(BX_ROOT."/admin/")) == BX_ROOT."/admin/"
			|| substr($cur_dir, 0, strlen(BX_ROOT."/updates/")) == BX_ROOT."/updates/"
			|| (defined("ADMIN_SECTION") &&  ADMIN_SECTION==true)
			|| (defined("BX_PUBLIC_TOOLS") && BX_PUBLIC_TOOLS===true)
			) //если раздел администрирования
		{
			//путь по параметру
			if(strlen($lang)<=0)
				$lang = COption::GetOptionString("main", "admin_lid", "ru");

			$R = CLanguage::GetList($o, $b, Array("LID"=>$lang, "ACTIVE"=>$ACTIVE));
			if($res = $R->Fetch())
			{
				$MAIN_LANGS_ADMIN_CACHE[$res["LID"]]=$res;
				return $res;
			}

			//если переменная не задана - берем язык по умолчанию
			$strSql =
				"SELECT * ".
				"FROM b_language ".
				"WHERE ACTIVE='Y' ".
				"ORDER BY DEF DESC, SORT";

			$R = $DB->Query($strSql);
			if($res = $R->Fetch())
			{
				$MAIN_LANGS_ADMIN_CACHE[$res["LID"]]=$res;
				return $res;
			}
			//ну если вообще ничего не задано - вернем просто
			return array("en", "MM/DD/YYYY", "MM/DD/YYYY HH24:MI:SS");
		}
		else //все остальные папки
		{
			$arURL = parse_url("http://".$cur_host);
			if($arURL["scheme"]=="" && strlen($arURL["host"])>0)
				$CURR_DOMAIN = $arURL["host"];
			else
				$CURR_DOMAIN = $cur_host;

			if(strpos($CURR_DOMAIN, ':')>0)
				$CURR_DOMAIN = substr($CURR_DOMAIN, 0, strpos($CURR_DOMAIN, ':'));
			$CURR_DOMAIN = Trim($CURR_DOMAIN, "\t\r\n\0 .");

			//текущий язык определяем по пути
			if(CACHED_b_lang!==false && CACHED_b_lang_domain!==false)
			{
				global $CACHE_MANAGER;
				$strSql =
					"SELECT L.*, L.LID as ID, L.LID as SITE_ID ".
					"FROM b_lang L ".
					"WHERE L.ACTIVE='Y' ".
					"ORDER BY ".
					"	LENGTH(L.DIR) DESC, ".
					"	L.DOMAIN_LIMITED DESC, ".
					"	SORT ";
				if($CACHE_MANAGER->Read(CACHED_b_lang, "b_lang".md5($strSql), "b_lang"))
				{
					$arLang = $CACHE_MANAGER->Get("b_lang".md5($strSql));
				}
				else
				{
					$arLang = array();
					$R = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
					while($row = $R->Fetch())
						$arLang[]=$row;
					$CACHE_MANAGER->Set("b_lang".md5($strSql), $arLang);
				}
	
				$strSql =
					"SELECT  ".
					"LD.LID as LD_LID,LD.DOMAIN as LD_DOMAIN ".
					"FROM  ".
					"	 b_lang_domain LD  ".
					"ORDER BY ".
					"	LENGTH(LD.DOMAIN) DESC ";
				if($CACHE_MANAGER->Read(CACHED_b_lang_domain, "b_lang_domain2", "b_lang_domain"))
				{
					$arLangDomain = $CACHE_MANAGER->Get("b_lang_domain2");
				}
				else
				{
					$arLangDomain = array();
					$R = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
					while($row = $R->Fetch())
						$arLangDomain[$row["LD_LID"]][]=$row;
					$CACHE_MANAGER->Set("b_lang_domain2", $arLangDomain);
				}
	
				$arJoin = array();
				foreach($arLang as $row)
				{
					//LEFT JOIN
					$bLeft = true;
					//LEFT JOIN b_lang_domain LD ON L.LID=LD.LID
					if(array_key_exists($row["LID"], $arLangDomain))
						foreach($arLangDomain[$row["LID"]] as $dom)
							//AND '".$DB->ForSql($CURR_DOMAIN, 255)."' LIKE CONCAT('%', LD.DOMAIN)
							if(strcasecmp(substr($CURR_DOMAIN, -strlen($dom["LD_DOMAIN"])), $dom["LD_DOMAIN"]) == 0)
							{
								$arJoin[] = $row+$dom;
								$bLeft = false;
							}
					if($bLeft)
						$arJoin[] = $row+array("LD_LID"=>"","LD_DOMAIN"=>"");
				}
				$A = array();
				foreach($arJoin as $row)
				{
					//WHERE ('".$DB->ForSql($cur_dir)."' LIKE CONCAT(L.DIR, '%') OR LD.LID IS NOT NULL)
					if($row["LD_LID"]!="" || strcasecmp(substr($cur_dir, 0, strlen($row["DIR"])), $row["DIR"]) == 0)
						$A[]=$row;
				}
	
				$res=false;
				if($res===false)
					foreach($A as $row)
						if(
							(strcasecmp(substr($cur_dir, 0, strlen($row["DIR"])), $row["DIR"]) == 0)
							&& (($row["DOMAIN_LIMITED"]=="Y" && $row["LD_LID"]!="")||$row["DOMAIN_LIMITED"]!="Y")
						)
						{
							$res=$row;
							break;
						}
				if($res===false)
					foreach($A as $row)
						if(
							strncasecmp($cur_dir, $row["DIR"], strlen($cur_dir))==0
						)
						{
							$res=$row;
							break;
						}
				if($res===false)
					foreach($A as $row)
						if(
							(($row["DOMAIN_LIMITED"]=="Y" && $row["LD_LID"]!="")||$row["DOMAIN_LIMITED"]!="Y")
						)
						{
							$res=$row;
							break;
						}
				if($res===false && count($A)>0)
					$res=$A[0];
			}
			else
			{
				$strSql =
					"SELECT L.*, L.LID as ID, L.LID as SITE_ID ".
					"FROM b_lang L ".
					"	LEFT JOIN b_lang_domain LD ON L.LID=LD.LID AND '".$DB->ForSql($CURR_DOMAIN, 255)."' LIKE CONCAT('%', LD.DOMAIN) ".
					"WHERE ('".$DB->ForSql($cur_dir)."' LIKE CONCAT(L.DIR, '%') OR LD.LID IS NOT NULL)".
					"	AND L.ACTIVE='Y' ".
					"ORDER BY ".
					"	IF((L.DOMAIN_LIMITED='Y' AND LD.LID IS NOT NULL) OR L.DOMAIN_LIMITED<>'Y', ".
					"		IF('".$DB->ForSql($cur_dir)."' LIKE CONCAT(L.DIR, '%'), 3, 1), ".
					"		IF('".$DB->ForSql($cur_dir)."' LIKE CONCAT(L.DIR, '%'), 2, 0) ".
					"	) DESC, ".
					"	LENGTH(L.DIR) DESC, ".
					"	L.DOMAIN_LIMITED DESC, ".
					"	SORT, ".
					"	LENGTH(LD.DOMAIN) DESC ";
	
				$R = $DB->Query($strSql, false, "File: ".__FILE__." Line:".__LINE__);
				$res = $R->Fetch();
			}

			if($res)
			{
				$MAIN_LANGS_CACHE[$res["LID"]]=$res;
				return $res;
			}

			//если переменная не задана - берем язык по умолчанию
			$strSql =
				"SELECT L.*, L.LID as ID, L.LID as SITE_ID ".
				"FROM b_lang L ".
				"WHERE ACTIVE='Y' ".
				"ORDER BY DEF DESC, SORT";

			$R = $DB->Query($strSql);
			while($res = $R->Fetch())
			{
				$MAIN_LANGS_CACHE[$res["LID"]]=$res;
				return $res;
		   	}
		}

		return array("en", "MM/DD/YYYY", "MM/DD/YYYY HH24:MI:SS");
	}
}

class CFile extends CAllFile
{
	function Delete($ID)
	{
		if(IntVal($ID)<=0)
			return;

		global $DB;
		$res = CFile::GetByID($ID);
		if($res = $res->Fetch())
		{
			$upload_dir = COption::GetOptionString("main", "upload_dir", "upload");
			$fname = $_SERVER["DOCUMENT_ROOT"]."/".$upload_dir."/".$res["SUBDIR"]."/".$res["FILE_NAME"];
			if(file_exists($fname))
			{
				if(unlink($fname))
				{
					/****************************** QUOTA ******************************/
					if (COption::GetOptionInt("main", "disk_space") > 0)
					{
						CDiskQuota::updateDiskQuota("file", $res["FILE_SIZE"], "delete");
					}
					/****************************** QUOTA ******************************/
				}
			}
			$DB->Query("DELETE FROM b_file WHERE ID=".IntVal($ID));
			@rmdir($_SERVER["DOCUMENT_ROOT"]."/".$upload_dir."/".$res["SUBDIR"]);
			CFile::CleanCache($ID);
		}
	}

	function SaveFile($arFile, $strSavePath, $bForceMD5=false, $bSkipExt=false)
	{
		global $DB;
		$strFileName = basename($arFile["name"]);	/* filename.gif */
		$upload_dir = COption::GetOptionString("main", "upload_dir", "upload");

		if(strlen($arFile["del"]) > 0)
		{
			CFile::Delete($arFile["old_file"]);
			if(strlen($arFile["del"]) > 0 && strlen($strFileName) <= 0)
				return "NULL";
		}

		if(is_set($arFile, "content") && !is_set($arFile, "size"))
			$arFile["size"] = strlen($arFile["content"]);
			
		/****************************** QUOTA ******************************/
		if (COption::GetOptionInt("main", "disk_space") > 0)
		{
			$quota = new CDiskQuota();
			if (!$quota->checkDiskQuota($arFile))
				return false;
		}
		/****************************** QUOTA ******************************/
			
		if($bForceMD5 != true && COption::GetOptionString("main", "save_original_file_name", "N")=="Y")
		{
			if(COption::GetOptionString("main", "convert_original_file_name", "Y")=="Y")
				$strFileName = preg_replace('/([^'.BX_VALID_FILENAME_SYMBOLS.'])/e', "chr(rand(97, 122))", $strFileName);
				
			//check for double extension vulnerability
			$strFileName = RemoveScriptExtension($strFileName);

			$dir_add = "";
			$i=0;
			while(true)
			{
				$dir_add = substr(md5(uniqid(mt_rand(), true)), 0, 3);
				if(!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$upload_dir."/".$strSavePath."/".$dir_add."/".$strFileName))
					break;
				if($i>=25)
				{
					$dir_add = md5(uniqid(mt_rand(), true));
					break;
				}
				$i++;
			}
			if(substr($strSavePath, -1, 1) <> "/")
				$strSavePath .= "/".$dir_add;
			else
				$strSavePath .= $dir_add."/";

			$newName = $strFileName;
		}
		else
		{
			$strFileExt = ($bSkipExt == true? '' : strrchr($arFile["name"], "."));
			while(true)
			{
				$newName = md5(uniqid(mt_rand(), true)).$strFileExt;
				if(substr($strSavePath, -1, 1) <> "/")
					$strSavePath .= "/".substr($newName, 0, 3);
				else
					$strSavePath .= substr($newName, 0, 3)."/";
					
				if(!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$upload_dir."/".$strSavePath."/".$newName))
					break;
			}
		}

		if(strlen($arFile["name"]) <= 0 /*or intval($arFile["size"]) <= 0*/ or  strlen($arFile["type"]) <= 0)
		{
			if(is_set($arFile, "description") && intval($arFile["old_file"])>0)
			{
				$strSql =
					"UPDATE b_file SET ".
					"	DESCRIPTION='".$DB->ForSQL($arFile["description"], 255)."' ".
					"WHERE ID=".intval($arFile["old_file"]);

				$DB->Query($strSql);
				CFile::CleanCache($arFile["old_file"]);
			}
			return false;
		}

		$strDirName = $_SERVER["DOCUMENT_ROOT"]."/".$upload_dir."/".$strSavePath."/";

		CheckDirPath($strDirName);

		$strDbFileNameX = $strDirName.$newName;

		if(is_set($arFile, "content"))
		{
			$f = @fopen($strDbFileNameX, "ab");
			if(!$f)
				return false;
			if(!fwrite($f, $arFile["content"]))
				return false;
			fclose($f);
		}
		elseif(!@copy($arFile["tmp_name"], $strDbFileNameX))
		{
			CFile::Delete($arFile["old_file"]);
			return false;
		}

		CFile::Delete($arFile["old_file"]);

		@chmod($strDbFileNameX, BX_FILE_PERMISSIONS);
		$imgArray = @getimagesize($strDbFileNameX);

		if(is_array($imgArray))
		{
			$intWIDTH = $imgArray[0];
			$intHEIGHT = $imgArray[1];
		}
		else
		{
			$intWIDTH = 0;
			$intHEIGHT = 0;
		}

		if($arFile["type"]=="image/pjpeg")
			$arFile["type"]="image/jpeg";

		/****************************** QUOTA ******************************/
		if (COption::GetOptionInt("main", "disk_space") > 0)
		{
			CDiskQuota::updateDiskQuota("file", $arFile["size"], "insert");
		}
		/****************************** QUOTA ******************************/
		
		$strSql =
			"INSERT INTO b_file(HEIGHT, WIDTH, FILE_SIZE, CONTENT_TYPE, SUBDIR, FILE_NAME, MODULE_ID, ORIGINAL_NAME, DESCRIPTION) ".
			"VALUES('".$intHEIGHT."', '".$intWIDTH."', '".IntVal($arFile["size"])."', '".$DB->ForSql($arFile["type"], 255)."' , '".$DB->ForSql($strSavePath, 255)."', '".$DB->ForSQL($newName, 255)."', '".$arFile["MODULE_ID"]."', '".$DB->ForSql($strFileName, 255)."', '".$DB->ForSQL($arFile["description"], 255)."') ";
		
		$DB->Query($strSql);
		$NEW_IMAGE_ID = $DB->LastID();
		CFile::CleanCache($NEW_IMAGE_ID);
		return $NEW_IMAGE_ID;
	}
}


class CSite extends CAllSite
{
	function GetCurTemplate()
	{
		global $DB, $APPLICATION, $USER, $CACHE_MANAGER;
		if(CACHED_b_site_template===false)
		{
			$strSql = "
				SELECT
					".CMain::__GetConditionFName().",
					TEMPLATE
				FROM
					b_site_template
				WHERE
					SITE_ID = '".SITE_ID."'
				ORDER BY
					if(length(".CMain::__GetConditionFName().")>0, 1, 2), SORT
				";
			$dbr = $DB->Query($strSql);
			while($ar = $dbr->Fetch())
			{
				$strCondition = trim($ar["CONDITION"]);
				if(strlen($strCondition)>0 && (!@eval("return ".$strCondition.";")))
					continue;
				if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".$ar["TEMPLATE"]) && is_dir($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".$ar["TEMPLATE"]))
					return $ar["TEMPLATE"];
			}
		}
		else
		{
			if($CACHE_MANAGER->Read(CACHED_b_site_template, "b_site_template"))
			{
				$arSiteTemplateBySite = $CACHE_MANAGER->Get("b_site_template");
			}
			else
			{
				$dbr = $DB->Query("
					SELECT
						".CMain::__GetConditionFName().",
						TEMPLATE,
						SITE_ID
					FROM
						b_site_template
					ORDER BY
						SITE_ID, if(length(".CMain::__GetConditionFName().")>0, 1, 2), SORT
				");
				while($ar = $dbr->Fetch())
					$arSiteTemplateBySite[$ar['SITE_ID']][]=$ar;
				$CACHE_MANAGER->Set("b_site_template", $arSiteTemplateBySite);
			}
			if(is_array($arSiteTemplateBySite[SITE_ID]))
			{
				foreach($arSiteTemplateBySite[SITE_ID] as $ar)
				{
					$strCondition = trim($ar["CONDITION"]);
					if(strlen($strCondition)>0 && (!@eval("return ".$strCondition.";")))
						continue;
					if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".$ar["TEMPLATE"]) && is_dir($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".$ar["TEMPLATE"]))
						return $ar["TEMPLATE"];
				}
			}
		}

		return ".default";
	}
}

class CFilterQuery extends CAllFilterQuery
{
	function BuildWhereClause($word)
	{
		$this->cnt++;
		//if($this->cnt>10) return "1=1";

		global $DB;
		if (isset($this->m_kav[$word]))
			$word = $this->m_kav[$word];

		$this->m_words[] = $word;

		$word = $DB->ForSql($word, 100);

		$n = count($this->m_fields);
		$ret = "";
		if ($n>1) $ret = "(";
		for ($i=0; $i<$n; $i++)
		{
			$field = $this->m_fields[$i];
			if ($this->procent=="Y")
			{
				$ret.= "
					(upper($field) like upper('%$word%') and $field is not null)
					";
			}
			elseif (strpos($word, "%")!==false || strpos($word, "_")!==false)
			{
				$ret.= "
					(upper($field) like upper('$word') and $field is not null)
					";
			}
			else
			{
				$ret.= "
					($field='$word' and $field is not null)
					";

			}
			if ($i<>$n-1) $ret.= " OR ";
		}
		if ($n>1) $ret.= ")";
		return $ret;
	}
}

class CLang extends CSite
{
}
?>