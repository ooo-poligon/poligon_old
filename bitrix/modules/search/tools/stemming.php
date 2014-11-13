<?
function stemming($sText, $sLang="ru")
{
	static $arLangFunc=array();
	static $arStopFunc=array();
	static $WORD_CACHE=array();
	static $STOP_CACHE=array();
	if(!isset($arLangFunc[$sLang]))
	{
		$stemming_function_suf = $sLang;

		if(!function_exists("stemming_".$sLang))
		{
			$strFileName=$_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/".$sLang."/search/stemming.php";
			if(file_exists($strFileName))
				@include($strFileName);
			if(!function_exists("stemming_".$sLang))
			{
				$strFileName=$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/search/tools/".$sLang."/stemming.php";
				if(file_exists($strFileName))
					@include($strFileName);
				if(!function_exists("stemming_".$sLang))
				{
					$stemming_function_suf = "default";
				}
			}
		}

		$stemming_stop_function = "stemming_stop_".$sLang;
		if(!function_exists($stemming_stop_function))
			$stemming_stop_function = "stemming_stop_default";

		$arLangFunc[$sLang] = "stemming_".$stemming_function_suf;
		$arStopFunc[$sLang] = $stemming_stop_function;
	}

	$stem_function = $arLangFunc[$sLang];
	$stop_function = $arStopFunc[$sLang];

	if(!defined("STEMMING_TOK_".$sLang))
	{
		$letters = stemming_letter_default();
		$stemming_letter_function = "stemming_letter_".$sLang;
		if(function_exists($stemming_letter_function))
			$letters .= $stemming_letter_function();
		$letters .= COption::GetOptionString("search", "letters");

		$letters = str_replace(
			array("\\"  , "-"  , "^"  , "]"  ),
			array("\\\\", "\\-", "\\^", "\\]"),
			$letters
		);

		define("STEMMING_TOK_".$sLang, $letters);
	}
	else
	{
		$letters = constant("STEMMING_TOK_".$sLang);
	}

	$word_cache = &$WORD_CACHE[$sLang];
	$stop_cache = &$STOP_CACHE[$sLang];
	//uppercase and remove punctuation marks

	$stems = array();

	$tok = " ";
	$sText = preg_replace("/[^".$letters."]+/".BX_UTF_PCRE_MODIFIER, $tok, ToUpper($sText));

	$word = strtok($sText, $tok);
	while($word !== false)
	{
		$word = substr($word, 0, 50);
		if(isset($word_cache[$word]))
			$stem = $word_cache[$word];
		else
			$stem = $word_cache[$word] = $stem_function($word);
		if(!isset($stop_cache[$stem]))
			$stop_cache[$stem] = $stop_function($stem);
		if($stop_cache[$stem])
			$stems[$stem]++;
		$word = strtok($tok);
	}

	return $stems;
}
function stemming_default($sText)
{
	return $sText;
}
function stemming_stop_default($sWord)
{
	if(strlen($sWord) < 2)
		return false;
	else
		return true;
}
function stemming_letter_default()
{
	return "qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM0123456789";
}
?>
