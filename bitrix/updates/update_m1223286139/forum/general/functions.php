<?
##############################################
# Bitrix Site Manager Forum                  #
# Copyright (c) 2002-2007 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
IncludeModuleLangFile(__FILE__);
function Error($error)
{
	global $MESS, $DOCUMENT_ROOT;
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/lang/".LANGUAGE_ID."/errors.php");
	$msg = $MESS[$error["MSG"]];
	echo "Error: ".$msg;
}

//===========================
class textParser
{
	var $smiles = array();
	var $allow_img_ext = "gif|jpg|jpeg|png";
	var $image_params = array(
		"width" => 300, 
		"height" => 300,
		"template" => "popup_image");
	var $LAST_ERROR  = "";
	var $path_to_smile  = false;
	var $quote_error = 0;
	var $quote_open = 0;
	var $quote_closed = 0;
	var $MaxStringLen = 125;
	var $code_error = 0;
	var $code_open = 0;
	var $code_closed = 0;
	var $CacheTime = false;
	var $arFontSize = array(
		0 => 40, //"xx-small"
		1 => 60, //"x-small"
		2 => 80, //"small"
		3 => 100, //"medium"
		4 => 120, //"large"
		5 => 140, //"x-large"
		7 => 160); //"xx-large"

	function textParser($strLang = False, $pathToSmile = false, $cacheTime = false, $modeCreationOBJ = false)
	{
		global $DB;
		static $arSmiles = array();
		
		$this->CacheTime = $cacheTime;
		$this->smiles = array();
		if ($strLang === False) 
			$strLang = LANGUAGE_ID;
		$this->path_to_smile = $pathToSmile;
		if (empty($modeCreationOBJ) || ($modeCreationOBJ == "full"))
		{
			$cache = new CPHPCache;
			$cache_id = "forum_simles_".$strLang.preg_replace("/\s.,;:!?\#\-\*\|\[\]\(\)\//is", "_", $pathToSmile);
			$cache_path = "/".LANGUAGE_ID."/forum/smiles/";
			
			if ($this->CacheTime > 0)
			{
				if ($this->CacheTime > 0 && $cache->InitCache($this->CacheTime, $cache_id, $cache_path))
				{
					$cache_result = $cache->GetVars();
					if (is_array($cache_result["arSmiles"]))
						$arSmiles = $cache_result["arSmiles"];
				}
			}
			
			if (!is_array($arSmiles) || (count($arSmiles) <= 0))
			{
				if ($this->CacheTime > 0)
					$cache->StartDataCache($this->CacheTime, $cache_id, $cache_path);
					
					$db_res = CForumSmile::GetListEx(array("SORT"=>"ASC"), array("TYPE"=>"S", "LID"=>$strLang));
					while ($res = $db_res->Fetch())
					{
						$tok = strtok($res['TYPING'], " ");
						while ($tok)
						{ 
							$arSmiles[] = array('TYPING' => stripslashes($tok),
													'IMAGE'  => stripslashes($res['IMAGE']),
													'DESCRIPTION'=>stripslashes($res['NAME']));
							$tok = strtok(" ");
						} 
					}
				
				if ($this->CacheTime > 0)
					$cache->EndDataCache(array("arSmiles"=>$arSmiles));
			}
			$this->smiles = $arSmiles;
		}
	}

	function convert($text, $allow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N"), $type = "html")	//, "KEEP_AMP" => "N"
	{
		global $DB;

		$text = preg_replace("#([?&;])PHPSESSID=([0-9a-zA-Z]{32})#is", "\\1PHPSESSID1=", $text);
		$type = ($type == "rss" ? "rss" : "html");

		$this->quote_error = 0;
		$this->quote_open = 0;
		$this->quote_closed = 0;
		$this->code_error = 0;
		$this->code_open = 0;
		$this->code_closed = 0;
		if ($allow["HTML"]!="Y")
		{
			if ($allow["CODE"]=="Y")
			{
				$text = str_replace(array("\001", "\002", chr(5), chr(6), "'", "\""), array("", "", "", "", chr(5), chr(6)), $text);
				$text = preg_replace(
					array(
					"#<code(\s+[^>]*>|>)(.+?)</code(\s+[^>]*>|>)#is".BX_UTF_PCRE_MODIFIER,
					"/\[code([^\]])*\]/is".BX_UTF_PCRE_MODIFIER,
					"/\[\/code([^\]])*\]/is".BX_UTF_PCRE_MODIFIER,
					"/(?<=[\001])(([^\002]+))(?=([\002]))/ise".BX_UTF_PCRE_MODIFIER,
					"/\001/",
					"/\002/"), 
					array(
					"[code]\\2[/code]", 
					"\001",
					"\002",
					"\$this->pre_convert_code_tag('\\2')",
					"[code]",
					"[/code]"), $text);
				$text = str_replace(array(chr(5), chr(6)), array("'", "\""), $text);
			}
			if ($allow["ANCHOR"]=="Y")
			{
				$text = preg_replace(
					array(
						"#<a[^>]+href\s*=\s*[\"]+(([^\"])+)[\"]+[^>]*>(.+?)</a[^>]*>#is".BX_UTF_PCRE_MODIFIER,
						"#<a[^>]+href\s*=\s*[\']+(([^\'])+)[\']+[^>]*>(.+?)</a[^>]*>#is".BX_UTF_PCRE_MODIFIER,
						"#<a[^>]+href\s*=\s*(([^\'\"\>])+)>(.+?)</a[^>]*>#is".BX_UTF_PCRE_MODIFIER), 
					"[url=\\1]\\3[/url]", $text);
			}
			if ($allow["BIU"]=="Y")
			{
				$text = preg_replace(
					array(
						"/\<b([^>]*)\>(.+?)\<\/b([^>]*)>/is".BX_UTF_PCRE_MODIFIER,
						"/\<u([^>]*)\>(.+?)\<\/u([^>]*)>/is".BX_UTF_PCRE_MODIFIER,
						"/\<i([^>]*)\>(.+?)\<\/i([^>]*)>/is".BX_UTF_PCRE_MODIFIER),
					array(
						"[b]\\2[/b]",
						"[u]\\2[/u]",
						"[i]\\2[/i]"),
					$text);
			}
			if ($allow["IMG"]=="Y")
			{
				$text = preg_replace(
					"#<img[^>]+src\s*=[\s\"']*(((http|https|ftp)://[.-_:a-z0-9@]+)*(\/[-_/=:.a-z0-9@{}&?]+)+)[\s\"']*[^>]*>#is".BX_UTF_PCRE_MODIFIER, 
					"[img]\\1[/img]", $text);
			}
			if ($allow["QUOTE"]=="Y")
			{
				//$text = preg_replace("#(<quote(.*?)>(.*)</quote(.*?)>)#is", "[quote]\\3[/quote]", $text);
				$text = preg_replace("#<(/?)quote(.*?)>#is", "[\\1quote]", $text);
			}
			if ($allow["FONT"]=="Y")
			{
				$text = preg_replace(
					array(
						"/\<font[^>]+size\s*=[\s\"']*([0-9]+)[\s\"']*[^>]*\>(.+?)\<\/font[^>]*\>/is".BX_UTF_PCRE_MODIFIER,
						"/\<font[^>]+color\s*=[\s\"']*(\#[a-f0-9]{6})[^>]*\>(.+?)\<\/font[^>]*>/is".BX_UTF_PCRE_MODIFIER,
						"/\<font[^>]+face\s*=[\s\"']*([a-z\s\-]+)[\s\"']*[^>]*>(.+?)\<\/font[^>]*>/is".BX_UTF_PCRE_MODIFIER),
					array(
						"[size=\\1]\\2[/size]",
						"[color=\\1]\\2[/color]",
						"[font=\\1]\\2[/font]"),
					$text);
			}
			if ($allow["LIST"]=="Y")
			{
				$text = preg_replace(
					array(
						"/\<ul((\s[^>]*)|(\s*))\>(.+?)<\/ul([^>]*)\>/is".BX_UTF_PCRE_MODIFIER,
						"/\<li((\s[^>]*)|(\s*))\>/is".BX_UTF_PCRE_MODIFIER),
					array(
						"[list]\\4[/list]",
						"[*]"),
					$text);
			}
			if ($allow["ANCHOR"]=="Y")
			{
				$text = preg_replace("'(^|\s)((http|https|news|ftp|aim|mailto)://[.-_:a-z0-9@]+([^\"\s\'])*)'is", "\\1[url]\\2[/url]", $text);
			}
			
			if (strLen($text)>0)
			{
				$text = str_replace(
					array("<", ">", "\""),
					array("&lt;", "&gt;", "&quot;"), 
					$text);
			}
			
			if ($allow["CODE"]=="Y")
			{
				$text = preg_replace(
					array(
					"/\[code([^\]])*\]/is".BX_UTF_PCRE_MODIFIER,
					"/\[\/code([^\]])*\]/is".BX_UTF_PCRE_MODIFIER,
					"/(\001)([^\002]+)(\002)/ies".BX_UTF_PCRE_MODIFIER,
					"/\001/",
					"/\002/"), 
					array(
					"\001",
					"\002",
					"\$this->convert_code_tag('[code]\\2[/code]', \$type)",
					"[code]",
					"[/code]"), $text);
			}
			if ($allow["QUOTE"]=="Y")
			{
				$text = preg_replace("#(\[quote(.*?)\](.*)\[/quote(.*?)\])#ies", "\$this->convert_quote_tag('\\1', \$type)", $text);
			}
			if ($allow["IMG"]=="Y")
			{
				$text = preg_replace("#\[img\](.+?)\[/img\]#ie", "\$this->convert_image_tag('\\1', \$type)", $text);
			}
			if ($allow["ANCHOR"]=="Y")
			{
				$text = preg_replace(
					array(
						"/\[url\](\S+?)\[\/url\]/ie".BX_UTF_PCRE_MODIFIER,
						"/\[url\s*=\s*(\S+?)\s*\](.*?)\[\/url\]/ie".BX_UTF_PCRE_MODIFIER
						),
					array(
						"\$this->convert_anchor_tag('\\1', '\\1', '' , \$type)",
						"\$this->convert_anchor_tag('\\1', '\\2', '', \$type)"),
					$text);
			}
			if ($allow["BIU"]=="Y")
			{
				$text = preg_replace(	
					array(
						"/\[b\](.+?)\[\/b\]/is".BX_UTF_PCRE_MODIFIER,
						"/\[i\](.+?)\[\/i\]/is".BX_UTF_PCRE_MODIFIER,
						"/\[u\](.+?)\[\/u\]/is".BX_UTF_PCRE_MODIFIER), 
					array(
						"<b>\\1</b>",
						"<i>\\1</i>",
						"<u>\\1</u>"), $text);
			}
			if ($allow["LIST"]=="Y")
			{
				$text = preg_replace(
					array(
						"/\[list\](.+?)\[\/list\]/is".BX_UTF_PCRE_MODIFIER,
						"/\[\*\]/".BX_UTF_PCRE_MODIFIER), 
					array(
						"<ul>\\1</ul>",
						"<li>"),
					$text);
			}
			if ($allow["FONT"]=="Y")
			{
				while (preg_match("/\[size\s*=\s*([^\]]+)\](.+?)\[\/size\]/is".BX_UTF_PCRE_MODIFIER, $text))
				{
					$text = preg_replace("/\[size\s*=\s*([^\]]+)\](.+?)\[\/size\]/ies".BX_UTF_PCRE_MODIFIER, "\$this->convert_font_attr('size', '\\1', '\\2')", $text);
				}
				while (preg_match("/\[font\s*=\s*([^\]]+)\](.*?)\[\/font\]/is".BX_UTF_PCRE_MODIFIER, $text))
				{
					$text = preg_replace("/\[font\s*=\s*([^\]]+)\](.*?)\[\/font\]/ies".BX_UTF_PCRE_MODIFIER, "\$this->convert_font_attr('font', '\\1', '\\2')", $text);
				}
				while (preg_match("/\[color\s*=\s*([^\]]+)\](.+?)\[\/color\]/is".BX_UTF_PCRE_MODIFIER, $text))
				{
					$text = preg_replace("/\[color\s*=\s*([^\]]+)\](.+?)\[\/color\]/ies".BX_UTF_PCRE_MODIFIER, "\$this->convert_font_attr('color', '\\1', '\\2')", $text);
				}
			}
			

//			$text = preg_replace("#(^|\s)((http|https|news|ftp)://[-_:A-Za-z0-9@]+(\.[-_/=:A-Za-z0-9@&?=%]+)+)#ie", "\$this->convert_anchor_tag('\\2', '\\2', '\\1')", $text);

			$text = str_replace(
				array(
					"(c)", "(C)",
					"(tm)", "(TM)", "(Tm)", "(tM)", 
					"(r)", "(R)", 
					"\n"), 
				array(
					"&copy;", "&copy;",
					"&#153;", "&#153;", "&#153;", "&#153;",
					"&reg;", "&reg;", 
					"<br />"), $text);

			if ($this->MaxStringLen>0)
			{
				$text = preg_replace("#(^|>)([^<]+)(<|$)#ies", "\$this->part_long_words('\\1', '\\2', '\\3')", $text);
			}
		}
		else
		{
			if ($allow["NL2BR"]=="Y")
			{
				$text = str_replace("\n", "<br />", $text);
			}
		}
		if ($allow["SMILES"]=="Y")
		{
			if (count($this->smiles) > 0)
			{
				$arPattern = array();
				$arReplace = array();
				foreach ($this->smiles as $a_id => $row)
				{
					$code  = str_replace("'", "\'", $row["TYPING"]);
					$image = preg_quote($row["IMAGE"]);
					$description = htmlspecialchars($row["DESCRIPTION"], ENT_QUOTES);
					$code = preg_quote($code, "/");
					$description = preg_quote($description, "/");
					$arPattern[] = "/(?<=[^\w&])$code(?=.\W|\W.|\W$)/ei".BX_UTF_PCRE_MODIFIER;
					$arReplace[] = "\$this->convert_emoticon('$code', '$image', '$description')";
				}
				if (!empty($arPattern))
					$text = preg_replace($arPattern, $arReplace, ' '.$text.' ');
			}
		}
		return trim($text);
	}

	function killAllTags($text)
	{
		$text = strip_tags($text);
		$text = preg_replace(
			array(
				"/\<(\/?)(quote|code|font|color)([^\>]*)\>/is".BX_UTF_PCRE_MODIFIER,
				"/\[(\/?)(b|u|i|list|code|quote|font|color|url|img)([^\]]*)\]/is".BX_UTF_PCRE_MODIFIER),
			"", 
			$text);
		return $text;
	}

	function convert4mail($text)
	{
		$text = Trim($text);
		if (strlen($text)<=0) return "";
		$arPattern = array();
		$arReplace = array();

		$arPattern[] = "/\[(code|quote)(.*?)\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "\n>================== \\1 ===================\n";
		
		$arPattern[] = "/\[\/(code|quote)(.*?)\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "\n>===========================================\n";
		
		$arPattern[] = "/\<WBR[\s\/]?\>/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "";
		
		$arPattern[] = "/^(\r|\n)+?(.*)$/";
		$arReplace[] = "\\2";
		
		$arPattern[] = "/\[b\](.+?)\[\/b\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "\\1";
		
		$arPattern[] = "/\[i\](.+?)\[\/i\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "\\1";
		
		$arPattern[] = "/\[u\](.+?)\[\/u\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "_\\1_";
		
		$arPattern[] = "/\[(\/?)(color|font|size)([^\]]*)\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "";
		
		$arPattern[] = "/\[url\](\S+?)\[\/url\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "(URL: \\1)";
		
		$arPattern[] = "/\[url\s*=\s*(\S+?)\s*\](.*?)\[\/url\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "\\2 (URL: \\1)";
		
		$arPattern[] = "/\[img\](.+?)\[\/img\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "(IMAGE: \\1)";
		
		$arPattern[] = "/\[(\/?)list\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "\n";
		$text = preg_replace($arPattern, $arReplace, $text);
		$text = str_replace("&shy;", "", $text);

/*		$text = str_replace("&quot;", "\"", $text);
		$text = str_replace("&#092;", "\\", $text);
		$text = str_replace("&#036;", "\$", $text);
		$text = str_replace("&#33;", "!", $text);
		$text = str_replace("&#39;", "'", $text);
		$text = str_replace("&lt;", "<", $text);
		$text = str_replace("&gt;", ">", $text);
		$text = str_replace("&nbsp;", " ", $text);
		$text = str_replace("&#124;", '|', $text);
		$text = str_replace("&amp;", "&", $text);*/

		return $text;
	}

	function convert_emoticon($code = "", $image = "", $description = "", $servername = "")
	{
		if (strlen($code)<=0 || strlen($image)<=0) return;
		$code = stripslashes($code);
		$description = stripslashes($description);
		$image = stripslashes($image);
		if ($this->path_to_smile !== false)
			return '<img src="'.$servername.$this->path_to_smile.$image.'" border="0" alt="'.$description.'" />';
		return '<img src="'.$servername.'/bitrix/images/forum/smile/'.$image.'" border="0" alt="'.$description.'" />';
	}

	function pre_convert_code_tag ($text = "")
	{
		if (strLen($text)<=0) return;
		$text = str_replace(
			array("&", "<", ">", "[", "]"), array("&amp;", "&lt;", "&gt;", "&#91;", "&#93;"), $text);
		return $text;
	}
	
	function convert_code_tag($text = "", $type = "html")
	{
		if (strLen($text)<=0) return;
		$type = ($type == "rss" ? "rss" : "html");
		$text = str_replace(array("<", ">", "\\r", "\\n", "\\"), array("&lt;", "&gt;", "&#92;r", "&#92;n", "&#92;"), $text);
		$text = stripslashes($text);
		$text = str_replace(array("  ", "\t", ), array("&nbsp;&nbsp;", "&nbsp;&nbsp;&nbsp;"), $text);
		$txt = $text;
		$txt = preg_replace(
			array(
				"/\[code\]/ie".BX_UTF_PCRE_MODIFIER,
				"/\[\/code\]/ie".BX_UTF_PCRE_MODIFIER), 
			array(
				"\$this->convert_open_tag('code', \$type)", 
				"\$this->convert_close_tag('code', \$type)"), $txt);
		if (($this->code_open==$this->code_closed) && ($this->code_error==0))
			return $txt;
		return $text;
	}

	function convert_quote_tag($text = "", $type = "html")
	{
		if (strlen($text)<=0) return;
		$txt = $text;
		$type = ($type == "rss" ? "rss" : "html");

		$txt = preg_replace(
			array(
				"/\[quote\]/ie".BX_UTF_PCRE_MODIFIER,
				"/\[\/quote\]/ie".BX_UTF_PCRE_MODIFIER), 
			array(
				"\$this->convert_open_tag('quote', \$type)", 
				"\$this->convert_close_tag('quote', \$type)"), $txt);
				
		if (($this->quote_open==$this->quote_closed) && ($this->quote_error==0))
			return $txt;
		return $text;
	}

	function convert_open_tag($marker = "quote", $type = "html")
	{
		$marker = (strToLower($marker) == "code" ? "code" : "quote");
		$type = ($type == "rss" ? "rss" : "html");
		
		$this->{$marker."_open"}++;
		if ($type == "rss")
			return "\n====".$marker."====\n";
		return "<table class='forum-".$marker."'><tr><th>".($marker == "quote" ? GetMessage("FRM_QUOTE") : GetMessage("FRM_CODE"))."</th></tr><tr><td>";
	}
	
	function convert_close_tag($marker = "quote")
	{
		$marker = (strToLower($marker) == "code" ? "code" : "quote");
		$type = ($type == "rss" ? "rss" : "html");
		
		if ($this->{$marker."_open"} == 0)
		{
			$this->{$marker."_error"}++;
			return;
		}
		$this->{$marker."_closed"}++;
		if ($type == "rss")
			return "\n=============\n";
		return "</td></tr></table>";
	}

	function convert_image_tag($url = "", $type = "html")
	{
		static $bShowedScript = false;
		if (strlen($url)<=0) return;
		$url = trim($url);
		$type = (strToLower($type) == "rss" ? "rss" : "html");
		$extension = preg_replace("/^.*\.(\S+)$/".BX_UTF_PCRE_MODIFIER, "\\1", $url);
		$extension = strtolower($extension);
		$extension = preg_quote($extension, "/");

		$bErrorIMG = False;
		if (preg_match("/[?&;]/".BX_UTF_PCRE_MODIFIER, $url)) $bErrorIMG = True;
		if (!$bErrorIMG && !preg_match("/$extension(\||\$)/".BX_UTF_PCRE_MODIFIER, $this->allow_img_ext)) $bErrorIMG = True;
		if (!$bErrorIMG && !preg_match("/^((http|https|ftp)\:\/\/[-_:.a-z0-9@]+)*(\/[-_\/=:.a-z0-9@%]+)$/i".BX_UTF_PCRE_MODIFIER, $url)) $bErrorIMG = True;
		if ($bErrorIMG)
			return "[img]".$url."[/img]";
		if ($type != "html")
			return '<img src="'.$url.'" alt="'.GetMessage("FRM_IMAGE_ALT").'" border="0" />';

		$result = $GLOBALS["APPLICATION"]->IncludeComponent(
			"bitrix:forum.interface",
			$this->image_params["template"],
			Array(
				"URL" => $url,
				
				"WIDTH"=> $this->image_params["width"],
				"HEIGHT"=> $this->image_params["height"],
				"CONVERT" => "N",
				"FAMILY" => "FORUM",
				"SINGLE" => "Y",
				"RETURN" => "Y"
			),
			null,
			array("HIDE_ICONS" => "Y"));
		
		return $result;
	}

	function convert_font_attr($attr, $value = "", $text = "")
	{
		if (strlen($text)<=0) return "";
		if (strlen($value)<=0) return $text;

		if ($attr == "size")
		{
			$count = count($this->arFontSize);
			if ($count <= 0)
				return $text;
			$value = intVal($value >= $count ? ($count - 1) : $value);
			return "<span style='font-size:".$this->arFontSize[$value]."%;'>".$text."</span>";
		}
		else if ($attr == 'color')
		{
			$value = preg_replace("/[^\w#]/", "" , $value);
			return "<font color='".$value."'>".$text."</font>";
		}
		else if ($attr == 'font')
		{
			$value = preg_replace("/[^\w]/", "" , $value);
			return "<font face='".$value."'>".$text."</font>";
		}
	}
	// Only for public using
	function wrap_long_words($text="")
	{
		if (($this->MaxStringLen > 0) && (strLen($text) > 0))
			return preg_replace("/(^|>)([^<]+)(<|$)/ies".BX_UTF_PCRE_MODIFIER, "\$this->part_long_words('\\1', '\\2', '\\3')", $text);
		return $text;
	}

	function part_long_words($str1, $str2, $str3)
	{
		$word_separator = "\s.,;:!?\#\-\*\|\[\]\(\)";
		
		if (($this->MaxStringLen > 0) && (strLen(trim($str2)) > 0))
		{
			$str2 = str_replace(
				array(chr(1), chr(2), chr(3), chr(4), chr(5), chr(6), "&amp;", "&lt;", "&gt;", "&quot;", "&nbsp;", "&copy;", "&reg;", "&trade;"), 
				array("", "", "", "", "", "", chr(5), "<", ">", chr(6), chr(1), chr(2), chr(3), chr(4)), 
				$str2);
			$str2 = preg_replace("/(?<=[".$word_separator."])(([^".$word_separator."]+))(?=[".$word_separator."])/ise".BX_UTF_PCRE_MODIFIER, "\$this->cut_long_words('\\2')", " ".$str2." ");
			$str2 = substr($str2, 1, -1);

			$str2 = str_replace(
				array(chr(5), "<", ">", chr(6), chr(1), chr(2), chr(3), chr(4), "&lt;WBR/&gt;", "&lt;WBR&gt;", "&amp;shy;"),
				array("&amp;", "&lt;", "&gt;", "&quot;", "&nbsp;", "&copy;", "&reg;", "&trade;", "<WBR/>", "<WBR/>", "&shy;"),
				$str2);
		}
		return $str1.$str2.$str3;
	}
	
	function cut_long_words($str)
	{
		if (($this->MaxStringLen > 0) && (strLen($str) > 0))
			$str = ereg_replace("([^ \n\r\t\x01]{".$this->MaxStringLen."})","\\1<WBR/>&shy;", $str);
		return $str;
	}

	function convert_anchor_tag($url, $text, $pref="")
	{
		$bCutUrl = True;

		$end = "";
		if (preg_match("/([\.,\?]|&#33;)$/".BX_UTF_PCRE_MODIFIER, $url, $match))
		{
			$end = $match[1];
			$url = preg_replace("/([\.,\?]|&#33;)$/".BX_UTF_PCRE_MODIFIER, "", $url);
			$text = preg_replace("/([\.,\?]|&#33;)$/".BX_UTF_PCRE_MODIFIER, "", $text);
		}
		if (preg_match("/\[\/(quote|code)/i", $url)) 
			return $url;
		$url = preg_replace(
			array("/&amp;/".BX_UTF_PCRE_MODIFIER, "/javascript:/i".BX_UTF_PCRE_MODIFIER), 
			array("&", "java script&#58; ") , $url);
		if (substr($url, 0, 1) != "/" && !preg_match("/^(http|news|https|ftp|aim|mailto)\:\/\//i".BX_UTF_PCRE_MODIFIER, $url))
			$url = 'http://'.$url;
		if (!preg_match("/^((http|https|news|ftp|aim):\/\/[-_:.a-z0-9@]+)*([^\"\'\s])+$/i".BX_UTF_PCRE_MODIFIER, $url))
			return $pref.$text." (".$url.")".$end;

		if (preg_match("/^<img\s+src/i".BX_UTF_PCRE_MODIFIER, $text)) 
			$bCutUrl = False;
		$text = preg_replace(
			array("/&amp;/i".BX_UTF_PCRE_MODIFIER, "/javascript:/i".BX_UTF_PCRE_MODIFIER), 
			array("&", "javascript&#58; "), $text);
		if ($bCutUrl && strlen($text) < 55) 
			$bCutUrl = False;
		if ($bCutUrl && !preg_match("/^(http|ftp|https|news):\/\//i".BX_UTF_PCRE_MODIFIER, $text)) 
			$bCutUrl = False;

		if ($bCutUrl)
		{
			$stripped = preg_replace("/^(http|ftp|https|news):\/\/(\S+)$/i".BX_UTF_PCRE_MODIFIER, "\\2", $text);
			$uri_type = preg_replace("/^(http|ftp|https|news):\/\/(\S+)$/i".BX_UTF_PCRE_MODIFIER, "\\1", $text);
			$text = $uri_type.'://'.substr($stripped, 0, 30).'...'.substr($stripped, -10);
		}

		return $pref."<a href='".$url."' target='_blank'>".$text."</a>".$end;
	}
	
	
	function convert_to_rss($text, $arImages = Array(), $arAllow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N"), $arParams = array())
	{
		global $DB;
		if (empty($arAllow))
			$arAllow = array(
				"HTML" => "N", 
				"ANCHOR" => "Y", 
				"BIU" => "Y", 
				"IMG" => "Y", 
				"QUOTE" => "Y", 
				"CODE" => "Y", 
				"FONT" => "Y", 
				"LIST" => "Y", 
				"SMILES" => "Y", 
				"NL2BR" => "N");
			
		$this->quote_error = 0;
		$this->quote_open = 0;
		$this->quote_closed = 0;
		$this->code_error = 0;
		$this->code_open = 0;
		$this->code_closed = 0;
		$bAllowSmiles = $arAllow["SMILES"];
		if ($arAllow["HTML"]!="Y")
		{
			$text = preg_replace(
				array(
					"#^(.+?)<cut[\s]*(/>|>).*?$#is".BX_UTF_PCRE_MODIFIER,
					"#^(.+?)\[cut[\s]*(/\]|\]).*?$#is".BX_UTF_PCRE_MODIFIER),
				"\\1", $text);
			$arAllow["SMILES"] = "N";
			$text = $this->convert($text, $arAllow, "rss");
		}
		else
		{
			if ($arAllow["NL2BR"]=="Y")
				$text = str_replace("\n", "<br />", $text);
		}

		if (strLen($arParams["SERVER_NAME"]) <= 0)
		{
			$dbSite = CSite::GetByID(SITE_ID);
			$arSite = $dbSite->Fetch();
			$arParams["SERVER_NAME"] = $arSite["SERVER_NAME"];
			if (strLen($arParams["SERVER_NAME"]) <=0)
			{
				if (defined("SITE_SERVER_NAME") && strlen(SITE_SERVER_NAME)>0)
					$arParams["SERVER_NAME"] = SITE_SERVER_NAME;
				else
					$arParams["SERVER_NAME"] = COption::GetOptionString("main", "server_name", "www.bitrixsoft.com");
			}
		}
		
		if ($bAllowSmiles=="Y")
		{
			if (count($this->smiles) > 0)
			{
				foreach ($this->smiles as $a_id => $row)
				{
					$code  = str_replace("'", "\'", $row["TYPING"]);
					$image = $row["IMAGE"];
					$description = htmlspecialchars($row["DESCRIPTION"], ENT_QUOTES);
					$code = preg_quote($code, "/");
					$description = preg_quote($description, "/");
					$text = preg_replace("/(?<=[^\w&])$code(?=.\W|\W.|\W$)/ei", "\$this->convert_emoticon('$code', '$image', '$description', 'http://".$arParams["SERVER_NAME"]."')", ' '.$text.' ');
				}
			}
		}
		return trim($text);
	}
}
?>