<?
IncludeModuleLangFile(__FILE__);
function tags_prepare($sText, $site_id = false)
{
	static $CACHE_SITE_LANGS = array();

	$arResult = array();
	if (!empty($sText))
	{
		if(!array_key_exists($site_id, $CACHE_SITE_LANGS))
		{
			$db_site_tmp = CSite::GetByID($site_id);
			if ($ar_site_tmp = $db_site_tmp->Fetch())
				$CACHE_SITE_LANGS[$site_id] = array(
					"LANGUAGE_ID" => $ar_site_tmp["LANGUAGE_ID"],
					"CHARSET" => $ar_site_tmp["CHARSET"],
					"SERVER_NAME" => $ar_site_tmp["SERVER_NAME"]
				);
			else
				$CACHE_SITE_LANGS[$site_id] = false;
		}

		if(is_array($CACHE_SITE_LANGS[$site_id]))
			$sLang = $CACHE_SITE_LANGS[$site_id]["LANGUAGE_ID"];
		else
			$sLang = 'en';

		stemming("", $sLang);

		$letters = constant("STEMMING_TOK_".$sLang)."@._\\s\\-";
		$tok = "\x01";
		$sText = preg_replace("/[^".$letters."]+/".BX_UTF_PCRE_MODIFIER, $tok, $sText);

		$word = strtok($sText, $tok);
		while($word !== false)
		{
			$word = substr(trim($word), 0, 255);
			if(!empty($word))
				$arResult[$word] = $word;
			$word = strtok($tok);
		}
	}
	return $arResult;
}

function TagsShowScript()
{
	static $bShown = false;
	if(!$bShown && ($_REQUEST["mode"] != 'excel'))
	{
		$bShown = true;
		stemming("", LANGUAGE_ID);
		$letters = constant("STEMMING_TOK_".LANGUAGE_ID)."@._\\s\\-";
		echo "<script type=\"text/javascript\">var STEMMING_TOK = '".CUtil::JSEscape($letters)."';</script>";
		$bOpera = (strpos($_SERVER["HTTP_USER_AGENT"], "Opera") !== false);
		echo "<script type=\"text/javascript\" src=\"/bitrix/js/search/tags.js".($bOpera? '':'?'.filemtime($_SERVER["DOCUMENT_ROOT"].'/bitrix/js/search/tags.js'))."\"></script>";
	}
}

function InputTags($sName="", $sValue="", $arSites=array(), $sHTML="", $sId="")
{
	static $arPostfix = array();
	if(!$sId)
	{
		$sPostfix = rand();
		while(array_key_exists($sPostfix, $arPostfix))
		{
			$sPostfix = rand();
		}
		$arPostfix[$sPostfix] = true;
		$sId = preg_replace("/\W/", "_", $sName).$sPostfix;
	}
	TagsShowScript();
	$order = class_exists("cuseroptions")? CUserOptions::GetOption("search_tags", "order", "CNT"): "CNT";
	return '<input name="'.htmlspecialchars($sName).'" id="'.htmlspecialchars($sId).'" type="text" autocomplete="off" value="'.htmlspecialcharsex($sValue).'" onfocus="window.oObject[this.id] = new JsTc(this, '.CUtil::PhpToJSObject($arSites).');" '.$sHTML.'/><input type="checkbox" id="ck_'.$sId.'" name="ck_'.htmlspecialchars($sName).'" '.($order=="NAME"? "checked": "").' title="'.GetMessage("SEARCH_TAGS_SORTING_TIP").'">';
}
?>