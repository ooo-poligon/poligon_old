<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
*/
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");

$ext_html_editor = COption::GetOptionString("fileman", "ext_html_editor", "");
if($ext_html_editor=="always" || ($ext_html_editor=="not_admin" && !$USER->IsAdmin()))
	LocalRedirect("/bitrix/admin/fileman_fck_edit.php?".$_SERVER["QUERY_STRING"]);

$addUrl = 'lang='.LANGUAGE_ID.($logical == "Y"?'&logical=Y':'');

//$DEBUG = true;
if ($DEBUG)
{
	echo "WF_CONVERT = ".$WF_CONVERT."<br>";
	echo "DOCUMENT_ID = ".$DOCUMENT_ID."<br>";
	echo "WF_PATH = ".$WF_PATH."<br>";
}

if($light!="Y")
{
	if (!($USER->CanDoOperation('fileman_admin_files') || $USER->CanDoOperation('fileman_edit_existent_files')))
		$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
IncludeModuleLangFile(__FILE__);

$strWarning = "";

if(!is_set($_REQUEST, "path") && is_set($_REQUEST, "WF_PATH"))
	$path = $WF_PATH;

while(($l=strlen($path))>0 && $path[$l-1]=="/")
 	$path = substr($path, 0, $l-1);

//если новый файл и задано новое имя
if(strlen($new)>0 && strlen($filename)>0)
	$path = $path."/".$filename; //присвоим нашему пути это новое имя

$path = Rel2Abs("/", $path);

$site = CFileMan::__CheckSite($site);
if(!$site)
	$site = CSite::GetSiteByFullPath($_SERVER["DOCUMENT_ROOT"].$path);

$ar_templ = false;
$db_t = CSite::GetTemplateList($site);
while($ar_t = $db_t->Fetch())
{
	if(strlen($ar_t["CONDITION"])<=0)
	{
		$ar_templ = $ar_t["TEMPLATE"];
		break;
	}
}

$arScripts = Array();
$DOC_ROOT = CSite::GetSiteDocRoot($site);
$arParsedPath = CFileMan::ParsePath(Array($site, $path), true, false, "", $logical == "Y");
$abs_path = $DOC_ROOT.$path;

if(strlen($back_url)<=0)
{
	if(strlen($new)>0 && strlen($filename)<=0)
		$back_url = "/bitrix/admin/fileman_admin.php?".$addUrl."&site=".$site."&path=".UrlEncode($path);
	else
		$back_url = "/bitrix/admin/fileman_admin.php?".$addUrl."&site=".$site."&path=".UrlEncode($arParsedPath["PREV"]);
}

$bCheckExecFile = !$USER->IsAdmin() && (strlen($new)>0 || in_array(CFileman::GetFileExtension($path), CFileMan::GetScriptFileExt()));
$bVarsFromForm = false;//флаг, указывающий, откуда брать контент из файла или из запостченой формы

$arPHPScript = Array();
if($light!="Y")
{
	//проверим права на доступ в этот файл
	if(($FILE_ACCESS = $APPLICATION->GetFileAccessPermission(Array($site, $path))) <= "U")
		$strWarning = GetMessage("ACCESS_DENIED");
	else
	{
		$bEdit=true;
		if(strtoupper($new)=="Y")
			$bEdit=false;
		elseif(is_dir($abs_path))
			$strWarning = GetMessage("FILEMAN_HTMLEDIT_NAME_OF_FOLDER");
		elseif(!file_exists($abs_path))
		{
			$bEdit = false;
			$path = $arParsedPath["PREV"];
			$filename = $arParsedPath["LAST"];
			$arParsedPath = CFileMan::ParsePath(Array($site, $path), true, false, "", $logical == "Y");
			$abs_path = $DOC_ROOT.$path;
		}
	}

	if(strlen($strWarning)<=0)
	{
		if($bEdit)
			$filesrc_tmp = $APPLICATION->GetFileContent($abs_path);
		else
		{
			$arTemplates = CFileman::GetFileTemplates(LANG, Array($ar_templ));
			$filesrc_tmp = CFileman::GetTemplateContent($arTemplates[0]["file"], LANG, Array($ar_templ));
			if(strlen($template)>0)
			{
				if(substr($template, 0, 1)=="/")
					$filesrc_tmp = $APPLICATION->GetFileContent($DOC_ROOT.$template);
				else
				{
					for($i=0; $i<count($arTemplates); $i++)
						if($arTemplates[$i]["file"] == $template)
						{
							$filesrc_tmp = CFileman::GetTemplateContent($arTemplates[$i]["file"], LANG, Array($ar_templ));
							break;
						}
				}
			}
		}

		if($REQUEST_METHOD=="POST" && strlen($save)>0  && check_bitrix_sessid())
		{
			if($bCheckExecFile)
			{
				if(CFileman::IsPHP($filesrc))
				{
					$strWarning = GetMessage("FILEMAN_HTMLEDIT_PHP_ACCESS");
					$bVarsFromForm = true;
					if(strlen($new)>0 && strlen($filename)>0)
					{
						$bEdit = false;
						$path = Rel2Abs("/", $arParsedPath["PREV"]);
						$arParsedPath = CFileMan::ParsePath(Array($site, $path), true, false, "", $logical == "Y");
						$abs_path = $DOC_ROOT.$path;
					}
				}
				else // if(CFileman::IsPHP($filesrc))
				{
					$res = CFileman::ParseFileContent($filesrc_tmp);
					$prolog = CFileman::SetTitle($res["PROLOG"], $title);
					$arPropsVals = preg_split("/#~@/is", $propsvals);
					$i = 0;
					while ($i < count($arPropsVals))
					{
						if (strlen(Trim($arPropsVals[$i]))>0)
							$prolog = CFileman::SetProperty($prolog, Trim($arPropsVals[$i]), Trim($arPropsVals[$i+1]));
						else
							$prolog = CFileman::SetProperty($prolog, Trim($arPropsVals[$i]), "");
						$i = $i + 2;
					}
					$epilog = $res["EPILOG"];
					$filesrc_for_save = $prolog.$filesrc.$epilog;
				} // if(CFileman::IsPHP($filesrc))
			}
			else // if($bCheckExecFile)
			{
				$prolog = CFileman::SetTitle($prolog, $title);
				$arPropsVals = preg_split("/#~@/is", $propsvals);
				$i = 0;
				while ($i < count($arPropsVals))
				{
					if (strlen(Trim($arPropsVals[$i]))>0)
						$prolog = CFileman::SetProperty($prolog, Trim($arPropsVals[$i]), Trim($arPropsVals[$i+1]));
					else
						$prolog = CFileman::SetProperty($prolog, Trim($arPropsVals[$i]), "");
					$i = $i + 2;
				}



function __PreparePHP($str)
{
	if(substr($str, 0, 2)=="={" && substr($str, -1, 1)=="}" && strlen($str)>3)
		return substr($str, 2, -1);

	return '"'.EscapePHPString($str).'"';
}

function __ReturnPHPStr($arVals, $arParams)
{
	$res = "";
	$un = md5(uniqid(""));
	$i=0;
	foreach($arVals as $key=>$val)
	{
		$i++;
		$comm = (strlen($arParams[$key]["NAME"])>0?"$un|$i|// ".$arParams[$key]["NAME"]:"");
		$res .= "\r\n\t\"".$key."\"\t=>\t";
		if(is_array($val) && count($val)>1)
			$res .= "Array(".$comm."\r\n";

		if(is_array($val) && count($val)>1)
		{
			$zn = '';
			foreach($val as $p)
			{
				if($zn!='') $zn.=",\r\n";
				$zn .= "\t\t\t\t\t".__PreparePHP($p);
			}
			$res .= $zn."\r\n\t\t\t\t),";
		}
		elseif(is_array($val))
		{
			$res .= "Array(".__PreparePHP($val[0])."),".$comm;
		}
		else
			$res .= __PreparePHP($val).",".$comm;
	}

	$max = 0;
	$lngth = Array();
	for($j=1; $j<=$i; $j++)
	{
		$p = strpos($res, "$un|$j|");
		$pn = strrpos(substr($res, 0, $p), "\n");
		$l = ($p-$pn);
		$lngth[$j] = $l;
		if($max<$l)
			$max = $l;
	}

	for($j=1; $j<=$i; $j++)
		$res = str_replace($un."|$j|", str_repeat("\t", intval(($max-$lngth[$j]+7)/8)), $res);

	return Trim($res, " \t,\r\n");
}

				// заменим вставки PHP
				function __b_fileman_replphp($matches)
				{
					preg_match("' scraction[^=]*=([^ \t>]+)'is", $matches[0], $m);
					$type = trim($m[1], '"');
					if($type != 'standart')
						$type = 'phpscript';
					if($type == 'standart')
					{
						preg_match("' scrid[^=]*=([^ \t>]+)'is", $matches[0], $m);
						$scrid = trim($m[1], '"');
						if(strlen($scrid)>0)
						{
							$arParams = Array();
							$arTemplates = CTemplates::GetByID($scrid);
							if($arTemplates)
								$arParams = $arTemplates["PARAMS"];

							parse_str($_POST["$matches[1]"], $arVals);
							UnQuoteArr($arVals);
							$ret = __ReturnPHPStr($arVals, $arParams);
							if(strlen($ret)>0)
								return "<?\$APPLICATION->IncludeFile(\"$scrid\", Array(\r\n\t".$ret."\r\n\t)\r\n);?>";

							return "<?\$APPLICATION->IncludeFile(\"$scrid\");?>";
						}
						return '';
					}
					return $_POST["$matches[1]"];
				}
				$filesrc = preg_replace_callback("'<img[^>]*?id[^>=]*=[ \r\n\t\"\']*(\{#PHPSCRIPT[0-9]+#\})[^>]*>'is", "__b_fileman_replphp", $filesrc);
				$filesrc_for_save = $prolog.$filesrc.$epilog;
			} // if($bCheckExecFile)
			
			if(strlen($strWarning) <= 0)
			{
				$APPLICATION->ResetException();
				if (!CFileMan::CheckOnAllowedComponents($filesrc_for_save))
				{
					$str_err = $APPLICATION->GetException();
					if($str_err && ($err = $str_err ->GetString()))
						$strWarning .= $err;
					$bVarsFromForm = true;
				}
			}

			if(strlen($strWarning) <= 0)
			{
				if(substr($arParsedPath["LAST"], 0, 1)==".")
					$strWarning = GetMessage("FILEMAN_HTMLEDIT_NAME_NOTBEG_COMMA");
				else
				{
					if(!$APPLICATION->SaveFileContent($abs_path, $filesrc_for_save))
						$strWarning = GetMessage("FILEMAN_FILE_SAVE_ERROR");
					else
					{
						$bEdit = true;
						$filesrc_tmp = $filesrc_for_save;

						if(strlen($menutype)>0)
						{
							$menu_path = $arParsedPath["PREV"]."/.".$menutype.".menu.php";
							if($APPLICATION->GetFileAccessPermission(Array($site, $menu_path)) > "R")
							{
								$res = CFileMan::GetMenuArray($DOC_ROOT.$menu_path);
								$aMenuLinksTmp = $res["aMenuLinks"];
								$sMenuTemplateTmp = $res["sMenuTemplate"];

								$menuitem = IntVal($menuitem);
								if($menuitem>0) //значит в существующий пункт
								{
									$menuitem = $menuitem - 1;
									if($menuitem < count($aMenuLinksTmp)) //номер пункта должен быть в пределах количества текущего меню
										$aMenuLinksTmp[$menuitem][2][] = $path;
								}
								else //иначе в новый
								{
									$menuitem = $newitempos-1;
									if($menuitem < 0 || $menuitem >= count($aMenuLinksTmp)) //номер пункта выходит за пределы количества текущего меню
										$menuitem = count($aMenuLinksTmp);

									for($i=count($aMenuLinksTmp)-1; $i>=$menuitem; $i--)//сдвинем вправо все пункты > нашего
										$aMenuLinksTmp[$i+1] = $aMenuLinksTmp[$i];
									$aMenuLinksTmp[$menuitem] = Array($newitemname, $path, Array(), Array(), "");
								}

								CFileMan::SaveMenu(Array($site, $menu_path), $aMenuLinksTmp, $sMenuTemplateTmp);
							}
						}
					}
				}

				if($backnewurl=="Y")
					LocalRedirect($back_url.(strpos($back_url, "?")===false?"?":"&")."path=".UrlEncode($path)."&site=".$site);
				elseif($gotonewpage=="Y")
					LocalRedirect($path);
				if(strlen($strWarning)<=0 && $apply!="Y")
					LocalRedirect($back_url);
				elseif(strlen($strWarning)<=0 && $apply=="Y")
					LocalRedirect("/bitrix/admin/fileman_html_edit.php?".$addUrl."&site=".$site."&path=".UrlEncode($path)."&back_url=".urlencode($back_url));
			}
		}
	}

	$bAskSaveAs = !$bEdit;
	if(strlen($strWarning)>0)
	{
		if($REQUEST_METHOD=="POST" && strlen($new)>0 && strlen($save)>0)
		{
			$path = $arParsedPath["PREV"];
			$filename = $arParsedPath["LAST"];
		}
	}
	else
	{
		if(!$bVarsFromForm)
		{
			if(!$bEdit && strlen($filename)<=0)
				$filename = "untitled.php";

			$filesrc = $filesrc_tmp;
			$res = CFileman::ParseFileContent($filesrc);
			$filesrc = $res["CONTENT"];
			$title = $res["TITLE"];
			$pprops = $res["PROPERTIES"];
			$propsvals = "";
			foreach ($pprops as $key => $value)
			{
				if (strlen($propsvals)>0) $propsvals .= "#~@";
				$propsvals .= $key."#~@".$value;
			}
			$prolog = $res["PROLOG"];
			$epilog = $res["EPILOG"];
			if($bCheckExecFile && CFileman::IsPHP($filesrc))
			{
				$strWarning=GetMessage("FILEMAN_HTMLEDIT_PHP_CHANGE");
				$filesrc = "";
			}
		}
	}

	if(strlen($strWarning)<=0)
	{
		$p = 0;
		while(($p = strpos($filesrc, "<?", $p))!==false)
		{
			$i = $p+2;
			$bSlashed = false;
			$bInString = false;
			while($i<strlen($filesrc)-1)
			{
				$i++;
				$ch = substr($filesrc, $i, 1);
				if(!$bInString)
				{
					//проверяем что не начинается комментарий
					if($ch == "/" && $i+1<strlen($filesrc))
					{
						//найдем позицию окончания php
						$posnext = strpos($filesrc, "?>", $i);
						if($posnext===false)
						{
							//окончания нет - значит скрипт незакончен
							$p = strlen($filesrc);
							break;
						}
						$posnext += 2;

						$ti = 0;
						if(substr($filesrc, $i+1, 1)=="*" && ($ti = strpos($filesrc, "*/", $i+2))!==false)
							$ti += 2;
						elseif(substr($filesrc, $i+1, 1)=="/" && ($ti = strpos($filesrc, "\n", $i+2))!==false)
							$ti += 1;

						if($ti)
						{
							// нашли начало($i) и конец комментария ($ti)
							// проверим что раньше конец скрипта или конец комментария (например в одной строке "//comment ? >")
							if($ti>$posnext && substr($filesrc, $i+1, 1)!="*")
							{
								// скрипт закончился раньше комментария
								// вырежем скрипт
								$arScripts[] = Array($p, $posnext, substr($filesrc, $p, $posnext-$p));
								$p = $posnext;
								break;
							}
							else
							{
								// комментарий закончился раньше скрипта
								$i = $ti;
							}
						}
						continue;
					} // if($ch == "/" && $i+1<strlen($filesrc))

					if($ch == "?" && $i+1<strlen($filesrc) && substr($filesrc, $i+1, 1)==">")
					{
						$i = $i+2;
						$arScripts[] = Array($p, $i, substr($filesrc, $p, $i-$p));
						$p = $i+1;
						break;
					}
				} // if(!$bInString)

				if($bInString && $ch == "\\" && !$bSlashed)
				{
					$bSlashed = true;
					continue;
				}

				if($ch == "\"" || $ch == "'")
				{
					if($bInString)
					{
						if(!$bSlashed && $quote_ch == $ch)
							$bInString = false;
					}
					else
					{
						$bInString = true;
						$quote_ch = $ch;
					}
				}
				elseif($bInString && $ch == "\\")
					$bSlashed = true;

				$bSlashed = false;
			} // while($i<strlen($filesrc)-1)
			if($i>=strlen($filesrc))
				break;
			$p = $i;
		} // while(($p = strpos("<?", $filesrc))!==false)
	}

function __ReplString($str, $arAllStr)
{
	if(preg_match("'^\x01([0-9]+)\x02$'s", $str))
		return preg_replace("'\x01([0-9]+)\x02'es", "\$arAllStr['\\1']", $str);
	elseif($str=="")
		return "";
	else
		return "={".preg_replace("'\x01([0-9]+)\x02'es", "'\"'.\$arAllStr['\\1'].'\"'", $str)."}";
}

function __GetParams($params)
{
	$arParams = Array();
	$sk = 0;
	$param_tmp = "";
	for($i=0; $i<strlen($params); $i++)
	{
		$ch = substr($params, $i, 1);
		if($ch=="(")
			$sk++;
		elseif($ch==")")
			$sk--;
		elseif($ch=="," && $sk==0)
		{
			$arParams[] = $param_tmp;
			$param_tmp = "";
			continue;
		}

		if($sk<0)
			break;

		$param_tmp .= $ch;
	}
	if($param_tmp!="")
		$arParams[] = $param_tmp;
	return $arParams;
}

function __CheckForComponent($str)
{
	if(substr($str, 0, 5)=="<?php")
		$str = substr($str, 5);
	else
		$str = substr($str, 2);

	$str = substr($str, 0, -2);

	$bSlashed = false;
	$bInString = false;
	$arAllStr = Array();
	$new_str = "";
	$i=-1;
	while($i<strlen($str)-1)
	{
		$i++;
		$ch = substr($str, $i, 1);
		if(!$bInString)
		{
			if($string_tmp!="")
			{
				$arAllStr[] = $string_tmp;
				$string_tmp = "";
				$new_str .= chr(1).(count($arAllStr)-1).chr(2);
			}

			//проверяем что не начинается комментарий
			if($ch == "/" && $i+1<strlen($str))
			{
				$ti = 0;
				if(substr($str, $i+1, 1)=="*" && ($ti = strpos($str, "*/", $i+2))!==false)
					$ti += 2;
				elseif(substr($str, $i+1, 1)=="/" && ($ti = strpos($str, "\n", $i+2))!==false)
					$ti += 1;

				if($ti)
					$i = $ti;

				continue;
			} // if($ch == "/" && $i+1<strlen($filesrc))

			if($ch == " " || $ch == "\r" || $ch == "\n" || $ch == "\t")
				continue;
		} // if(!$bInString)

		if($bInString && $ch == "\\" && !$bSlashed)
		{
			$bSlashed = true;
			continue;
		}

		if($ch == "\"" || $ch == "'")
		{
			if($bInString)
			{
				if(!$bSlashed && $quote_ch == $ch)
				{
					$bInString = false;
					continue;
				}
			}
			else
			{
				$bInString = true;
				$quote_ch = $ch;
				continue;
			}
		}
		elseif($bInString && $ch == "\\")
			$bSlashed = true;

		$bSlashed = false;
		if($bInString)
		{
			$string_tmp .= $ch;
			continue;
		}

		$new_str .= $ch;
	} // while($i<strlen($filesrc)-1)

	if($pos = strpos($new_str, "("))
	{
		$func_name = substr($new_str, 0, $pos+1);
		$func_name = preg_replace("'\\\$GLOBALS\[(\"|\')(.+?)(\"|\')\]'s", "\$\\2", $func_name);
		switch(strtoupper($func_name))
		{
		case '$APPLICATION->INCLUDEFILE(':
			$params = substr($new_str, $pos+1);
			$arParams = __GetParams($params);
			$arIncludeParams = Array();

			if(strtolower(substr($arParams[1], 0, 6))=='array(')
			{
				$arParams2 = __GetParams(substr($arParams[1], 6));
				for($i=0; $i<count($arParams2); $i++)
				{
					$el = $arParams2[$i];
					$p = strpos($el, "=>");
					$el_ind = __ReplString(substr($el, 0, $p), $arAllStr);
					$el_val = substr($el, $p+2);
					if(strtolower(substr($el_val, 0, 6))=='array(')
					{
						$res_ar = Array();
						$arParamsN = __GetParams(substr($el_val, 6));
						for($j = 0; $j<count($arParamsN); $j++)
							$res_ar[] = __ReplString($arParamsN[$j], $arAllStr);

						$arIncludeParams[$el_ind] = $res_ar;
					}
					else
						$arIncludeParams[$el_ind] = __ReplString($el_val, $arAllStr);
				}
			}

			return Array(
					"SCRIPT_NAME"=>__ReplString($arParams[0], $arAllStr),
					"PARAMS"=>$arIncludeParams
					);
		}
	}

	return false;
}

function __ParamsToStr($ar)
{
	$res = "";
	foreach($ar as $k=>$v)
	{
		if(is_array($v))
		{
			foreach($v as $tv)
			{
				if($res!="")$res.="&";
				$res .= $k.'[]='.urlencode($tv);
			}
		}
		else
		{
			if($res!="")$res.="&";
			$res .= $k.'='.urlencode($v);
		}
	}
	return $res;
}

	if(count($arScripts)>0)
	{
		$newfilesrc = "";
		$plast = 0;
		for($i=0; $i<count($arScripts); $i++)
		{
			$arScript = $arScripts[$i];
			if(($arRes = __CheckForComponent($arScript[2])) && ($arTemplates = CTemplates::GetByID($arRes["SCRIPT_NAME"])))
			{
				if(strlen($arTemplates['ICON'])<=0 || !is_file($_SERVER["DOCUMENT_ROOT"].$arTemplates['ICON']))
					$arTemplates['ICON'] = "/bitrix/images/fileman/htmledit/component.gif";
				$newfilesrc .= substr($filesrc, $plast, $arScript[0]-$plast).'<img src="'.$arTemplates['ICON'].'" border="0" scraction="standart"'.(strlen($arTemplates['PATH_EDIT'])>0?' scredit="'.$arTemplates['PATH_EDIT'].'"':'').' scrid="'.$arRes["SCRIPT_NAME"].'" id="{#PHPSCRIPT'.$i.'#}">';
				$arPHPScript[$i] = __ParamsToStr($arRes["PARAMS"]);
			}
			else
			{
				$arPHPScript[$i] = $arScript[2];
				$newfilesrc .= substr($filesrc, $plast, $arScript[0]-$plast).'<img src="/bitrix/images/fileman/htmledit/php.gif" border="0" scraction="phpscript" id="{#PHPSCRIPT'.$i.'#}">';
			}
			$plast = $arScript[1];
		}
		$filesrc = $newfilesrc.substr($filesrc, $plast);
	}
}

if(count($arScripts)<=0 && $ext_html_editor=="not_php")
	LocalRedirect("/bitrix/admin/fileman_fck_edit.php?".$_SERVER["QUERY_STRING"]);



if(file_exists($DOC_ROOT.BX_PERSONAL_ROOT."/php_interface/".$site."/styles.css"))
	$styles = $APPLICATION->GetFileContent($DOC_ROOT.BX_PERSONAL_ROOT."/php_interface/".$site."/styles.css");
elseif(file_exists($DOC_ROOT.BX_PERSONAL_ROOT."/php_interface/styles.css"))
	$styles = $APPLICATION->GetFileContent($DOC_ROOT.BX_PERSONAL_ROOT."/php_interface/styles.css");
elseif($ar_templ && file_exists($DOC_ROOT.BX_PERSONAL_ROOT."/templates/".$ar_templ."/styles.css"))
	$styles = $APPLICATION->GetFileContent($DOC_ROOT.BX_PERSONAL_ROOT."/templates/".$ar_templ."/styles.css");
else
	$styles = $APPLICATION->GetFileContent($DOC_ROOT.BX_PERSONAL_ROOT."/templates/.default/styles.css");

if(file_exists($DOC_ROOT.BX_PERSONAL_ROOT."/php_interface/".$site."/editor.css"))
	$styles .= $APPLICATION->GetFileContent($DOC_ROOT.BX_PERSONAL_ROOT."/php_interface/".$site."/editor.css");
elseif(file_exists($DOC_ROOT.BX_PERSONAL_ROOT."/php_interface/editor.css"))
	$styles .= $APPLICATION->GetFileContent($DOC_ROOT.BX_PERSONAL_ROOT."/php_interface/editor.css");

?>
<html>
<head>
<style>
BODY{MARGIN: 0; BORDER: 0; BACKGROUND-COLOR: buttonface; }
.button{BORDER-BOTTOM: buttonshadow solid 1px; BORDER-LEFT: buttonhighlight solid 1px; BORDER-RIGHT: buttonshadow solid 1px; BORDER-TOP:  buttonhighlight solid 1px;}
.buttonDown{BACKGROUND-COLOR: buttonface;BORDER-BOTTOM: buttonhighlight solid 1px;BORDER-LEFT: buttonshadow solid 1px;BORDER-RIGHT: buttonhighlight solid 1px;BORDER-TOP:  buttonshadow solid 1px;}
.buttonChecked{BACKGROUND-COLOR: gainsboro;BORDER-BOTTOM: buttonhighlight solid 1px;BORDER-LEFT: buttonshadow solid 1px;BORDER-RIGHT: buttonhighlight solid 1px;BORDER-TOP:  buttonshadow solid 1px;}
.tb{BORDER-BOTTOM: buttonface solid 1px;BORDER-LEFT: buttonface solid 1px;BORDER-RIGHT: buttonface solid 1px;BORDER-TOP: buttonface solid 1px;}
.tbsmall{BORDER-BOTTOM: buttonface solid 1px;BORDER-LEFT: buttonface solid 1px;BORDER-RIGHT: buttonface solid 1px;BORDER-TOP: buttonface solid 1px;}
.buttonsmall{BORDER-BOTTOM: buttonshadow solid 1px; BORDER-LEFT: buttonhighlight solid 1px; BORDER-RIGHT: buttonshadow solid 1px; BORDER-TOP:  buttonhighlight solid 1px;}
.separator{BORDER-LEFT: buttonshadow solid 1px;BORDER-RIGHT: buttonhighlight solid 1px;FONT-SIZE: 0px;TOP: 0px;HEIGHT: 23px;WIDTH: 0px;BORDER-BOTTOM: buttonface solid 1px;BORDER-TOP: buttonface solid 0px;}
.toolbarbord{BORDER-BOTTOM: buttonshadow solid 1px;BORDER-LEFT: buttonhighlight solid 1px;BORDER-RIGHT: buttonshadow solid 1px;BORDER-TOP:  buttonhighlight solid 1px;TOP:0;LEFT:0;}
.toolbar{BACKGROUND-COLOR: buttonface;BORDER-BOTTOM: buttonshadow solid 1px;BORDER-LEFT: buttonhighlight solid 1px;BORDER-RIGHT: buttonshadow solid 1px;BORDER-TOP:  buttonhighlight solid 1px;HEIGHT: 22px;WIDTH: 0px;TOP:0;LEFT:0;}
</style>
<meta http-equiv="Content-Type" content="text/html; charset=<?echo LANG_CHARSET?>">
<title><?echo GetMessage("FILEMAN_HTMLEDIT_TITLE")?> <?echo (strlen($title)>0?htmlspecialchars($title):GetMessage("FILEMAN_HTMLEDIT_UNTITLED"))?></title>
<script LANGUAGE="JavaScript" SRC="/bitrix/admin/dhtmled.js"></script>
<script language="JavaScript">
<!--
<?if($light=="Y"):?>
var s1 = "<?=addcslashes(
                str_replace('"', '\"',
                        str_replace("\\", "\\\\",
                                (strlen($styles)>0?"<head>\n<style>\n".$styles."\n</style>\n</head>\n":"")
                        )
                ),
        "\0..\37");
        ?>";
//var s1 = "";
<?else:?>
var s1 = "<?echo eregi_replace('<SCRIPT', '<S"+"CRIPT',
				eregi_replace('</SCRIPT', '</S"+"CRIPT',
					addcslashes(
						str_replace('"', '\"',
							str_replace("\\", "\\\\",
								(strlen($styles)>0?"<head>\n<style>\n".$styles."\n</style>\n</head>\n":"")."<body>".$filesrc."</body>"
							)
						),
					"\0..\37")));?>";
<?endif?>
var s2 = "<?echo "http://".$HTTP_HOST.$path?>";
var strDirPath = "<?echo ($bEdit?$arParsedPath["PREV"]:$path)?>";
var strSite = "<?echo $HTTP_HOST?>";
var is_new = <?echo ($bAskSaveAs?"true":"false")?>;
var bDocumentChanged = false;
var tbbuttons = Array();
var iTemplateselect=0;
var bInComponent = false;
ContextMenu = Array();


function window_onload()
{
	<?if($light!="Y" && strlen($strWarning)<=0):?>
		<?if(false && CFileman::IsPHP($filesrc)):?>
			if(confirm("<?echo GetMessage("FILEMAN_HTMLEDIT_PHP_FOUND")?>"))
				window.location="/bitrix/admin/fileman_file_edit.php?lang=<?echo LANG?>&site=<?=$site?>&path=<?echo UrlEncode($path)?><?if(strlen($template)>0)echo "&template=".urlencode($template);if(strlen($new)>0)echo "&new=".urlencode($new)?>";
		<?endif?>
	<?else:?>
		try{eval("parent.<?echo $fieldname?>_OnLoad();");}catch(e){}
	<?endif?>
	var cnt = 0;
	var t = Array(toolbar);
	for(j=0; j<t.length; j++)
	{
		elements = t[j].all;
		for (i=0; i<elements.length; i++)
		{
			if (elements[i].tagName != "IMG")
				continue;

			if(elements[i].className=="tb")
			{
				elements[i].onmouseover = MouseOver;
				elements[i].onmouseout = MouseOut;
				elements[i].onmousedown = MouseDown;
				elements[i].onmouseup = MouseOver;
				elements[i].onclick = MouseClick;
				tbbuttons[cnt++] = elements[i];
			}
		}
	}

	tbContentElement.SourceCodePreservation = true;
	tbContentElement.DocumentHTML = s1;
	tbContentElement.BaseUrl = s2;
	//tbContentElement.Appearance = 0;
	//tbContentElement.ScrollbarAppearance = 0;
	<?if($htmledshowbord=="Y"):?>
	TBSetState(document.all("tbborders"), "checked");
	tbContentElement.ShowBorders = true;
	<?endif?>
	<?if($htmledshowdet=="Y"):?>
	TBSetState(document.all("tbdetails"), "checked");
	tbContentElement.ShowDetails = true;
	<?endif?>
	//try{tbContentElement.focus();}catch(e){};
	<?if($light!="Y"):?>
		<?if(strlen($strWarning)>0):?>
			alert('<?echo GetMessage("FILEMAN_HTMLEDIT_ERROR")?> <?echo AddSlashes($strWarning)?>');
			<?if($REQUEST_METHOD=="POST" && strlen($save)>0):?>
				SaveAs(<?echo ($apply=="Y"?"false":"true")?>);
			<?else:?>
				window.location="<?echo $back_url?>";
			<?endif?>
		<?endif?>
	<?endif?>
}

function Exit()
{
	window.location="<?echo $back_url?>";
}

function Exit_onclick()
{
	if((tbContentElement.IsDirty || bDocumentChanged) && confirm("<?echo GetMessage("FILEMAN_HTMLEDIT_PAGE_NOT_SAVED")?>"))
	{
		if(is_new)
			SaveAs(true);
		else
			Save(true);
	}
	else
		Exit();
}

function GetCurElement()
{
	var sel=tbContentElement.DOM.selection;
	var s=sel.createRange();
	if(sel.type=="Control")
		return s.commonParentElement();
	else
		return s.parentElement();
}


function OnStyleChange()
{
	var strStyle = stylelist[stylelist.selectedIndex].value;
	var sel=tbContentElement.DOM.selection;
	var stmp=sel.createRange();
	var s = stmp;
	if(s.length>0)
		s = s.item(0);
//ss.collapse(false);
	var el=null;
	try{el=s.parentElement();}catch(e){el=s.parentElement;};

//var p = s.htmlText;
//s.pasteHTML("<font class='"+strStyle+"'>" + p + "</font>");
//A("AA");

	while(el && el.tagName!="FONT" && el.tagName!="P")
		el = el.parentElement;

	if(el==null || el.outerHTML!=s.htmlText)
	{
		var str = s.htmlText;
		s.pasteHTML("<font class='"+strStyle+"'>" + s.text + "</font>");
	}
	else
	{
//A("AA");
		if(strStyle.length<=0)
			el.outerHTML = el.innerHTML;
		else
			el.className = strStyle;
	}
}

function InsertImage(imgUrl, iW, iH)
{
	if(typeof(imgUrl)=="string")
	{
		<?if($WF_CONVERT=="Y"):?>
		imgUrl = "/bitrix/admin/workflow_get_file.php?did=<?echo $DOCUMENT_ID?>&site=<?=$site?>&wf_path=<?=$WF_PATH?>&fname="+imgUrl;
		<?endif;?>

		<?if ($DEBUG):?>
		alert('imgUrl - '+imgUrl+' iW - '+iW+' iH - '+iH);
		<?endif;?>

		tbContentElement.ExecCommand(DECMD_IMAGE, OLECMDEXECOPT_DONTPROMPTUSER, imgUrl);
		var ce = GetCurElement();
		if(ce.tagName=="IMG")
		{
			ce.width=iW;
			ce.height=iH;
			ce.border=0;
		}
	}
	tbContentElement.focus();
}

function ImageProperty()
{
	if(contextElement && contextElement.tagName=="IMG")
	{
		var args = new Array();
		var arr = null;
		var src;
		var srctmp = contextElement.src;

		if(strSite.length+7<=srctmp.length && srctmp.substring(0, strSite.length+7)=="http://"+strSite)
			srctmp = srctmp.substring(strSite.length+7);
		<?if(!$DEBUG && $WF_CONVERT=="Y"):?>
		if (srctmp.indexOf("workflow_get_file.php")>0)
		{
			srctmp = srctmp.substring(srctmp.indexOf("fname")+6, srctmp.length);
		}
		<?endif;?>
		args["src"] = srctmp;
		args["alt"] = contextElement.alt;
		args["border"] = contextElement.border;
		args["height"] = contextElement.height;
		args["width"] = contextElement.width;
		args["hspace"] = contextElement.hspace;
		args["vspace"] = contextElement.vspace;
		args["align"] = contextElement.align;
		arr = showModalDialog("fileman_dialog.php?dtype=imgprop&lang=<?echo LANG?>", args, "dialogWidth:460px; dialogHeight:250px");
		if (arr != null)
		{
			src = arr["src"];
			<?if($WF_CONVERT=="Y"):?>
				if (src.indexOf("workflow_get_file.php")<=0)
					src = "/bitrix/admin/workflow_get_file.php?did=<?echo $DOCUMENT_ID?>&site=<?=$site?>&wf_path=<?=$WF_PATH?>&fname="+src;
			<?endif?>
			contextElement.src = src;
			contextElement.alt = arr["alt"];
			contextElement.border = arr["border"];
			contextElement.height = arr["height"];
			contextElement.width = arr["width"];
			contextElement.hspace = arr["hspace"];
			contextElement.vspace = arr["vspace"];
			contextElement.align = arr["align"];
			contextElement.style.cssText = "";
		}
	}

	tbContentElement.focus();
}

function TableProperty()
{
	if(contextElement && contextElement.tagName=="TABLE")
	{
		var args = new Array();
		var arr = null;
		args["cellpadding"] = contextElement.cellPadding;
		args["cellspacing"] = contextElement.cellSpacing;
		args["border"] = contextElement.border;
		args["width"] = contextElement.width;
		args["align"] = contextElement.align;
		arr = null;
		arr = showModalDialog("fileman_dialog.php?dtype=tableprop&lang=<?echo LANG?>", args, "dialogWidth:460px; dialogHeight:190px");
		if (arr != null)
		{
			contextElement.cellPadding = arr["cellpadding"];
			contextElement.cellSpacing = arr["cellspacing"];
			contextElement.border = arr["border"];
			contextElement.width = arr["width"];
			contextElement.align = arr["align"];
			contextElement.style.cssText = "";
		}
	}
	tbContentElement.focus();
}

function tbContentElement_DisplayChanged()
{
	var s;
	for(var i=0; i<tbbuttons.length; i++)
	{
		var b = tbbuttons[i];
		var cmd = b.tbcmd;
		if("_"+cmd != "_undefined")
		{
			var ctmp = "_" + cmd;
			if(ctmp.substring(0, 7)!="_DECMD_" || ctmp=="_DECMD_EDITSRC" || ctmp=="_DECMD_SHOWDETAILS" || ctmp=="_DECMD_VISIBLEBORDERS" || ctmp=="_DECMD_ADDIMG")
				cmd=0
			else
			{
				ctmp = cmd;
				if(ctmp=="DECMD_BOLD") cmd = 5000;
				else if(ctmp=="DECMD_COPY") cmd = 5002;
				else if(ctmp=="DECMD_CUT") cmd = 5003;
				else if(ctmp=="DECMD_DELETE") cmd = 5004;
				else if(ctmp=="DECMD_DELETECELLS") cmd = 5005;
				else if(ctmp=="DECMD_DELETECOLS") cmd = 5006;
				else if(ctmp=="DECMD_DELETEROWS") cmd = 5007;
				else if(ctmp=="DECMD_FINDTEXT") cmd = 5008;
				else if(ctmp=="DECMD_FONT") cmd = 5009;
				else if(ctmp=="DECMD_GETBACKCOLOR") cmd = 5010;
				else if(ctmp=="DECMD_GETBLOCKFMT") cmd = 5011;
				else if(ctmp=="DECMD_GETBLOCKFMTNAMES") cmd = 5012;
				else if(ctmp=="DECMD_GETFONTNAME") cmd = 5013;
				else if(ctmp=="DECMD_GETFONTSIZE") cmd = 5014;
				else if(ctmp=="DECMD_GETFORECOLOR") cmd = 5015;
				else if(ctmp=="DECMD_HYPERLINK") cmd = 5016;
				else if(ctmp=="DECMD_IMAGE") cmd = 5017;
				else if(ctmp=="DECMD_INDENT") cmd = 5018;
				else if(ctmp=="DECMD_INSERTCELL") cmd = 5019;
				else if(ctmp=="DECMD_INSERTCOL") cmd = 5020;
				else if(ctmp=="DECMD_INSERTROW") cmd = 5021;
				else if(ctmp=="DECMD_INSERTTABLE") cmd = 5022;
				else if(ctmp=="DECMD_ITALIC") cmd = 5023;
				else if(ctmp=="DECMD_JUSTIFYCENTER") cmd = 5024;
				else if(ctmp=="DECMD_JUSTIFYLEFT") cmd = 5025;
				else if(ctmp=="DECMD_JUSTIFYRIGHT") cmd = 5026;
				else if(ctmp=="DECMD_LOCK_ELEMENT") cmd = 5027;
				else if(ctmp=="DECMD_MAKE_ABSOLUTE") cmd = 5028;
				else if(ctmp=="DECMD_MERGECELLS") cmd = 5029;
				else if(ctmp=="DECMD_ORDERLIST") cmd = 5030;
				else if(ctmp=="DECMD_OUTDENT") cmd = 5031;
				else if(ctmp=="DECMD_PASTE") cmd = 5032;
				else if(ctmp=="DECMD_REDO") cmd = 5033;
				else if(ctmp=="DECMD_REMOVEFORMAT") cmd = 5034;
				else if(ctmp=="DECMD_SELECTALL") cmd = 5035;
				else if(ctmp=="DECMD_SEND_BACKWARD") cmd = 5036;
				else if(ctmp=="DECMD_BRING_FORWARD") cmd = 5037;
				else if(ctmp=="DECMD_SEND_BELOW_TEXT") cmd = 5038;
				else if(ctmp=="DECMD_BRING_ABOVE_TEXT") cmd = 5039;
				else if(ctmp=="DECMD_SEND_TO_BACK") cmd = 5040;
				else if(ctmp=="DECMD_BRING_TO_FRONT") cmd = 5041;
				else if(ctmp=="DECMD_SETBACKCOLOR") cmd = 5042;
				else if(ctmp=="DECMD_SETBLOCKFMT") cmd = 5043;
				else if(ctmp=="DECMD_SETFONTNAME") cmd = 5044;
				else if(ctmp=="DECMD_SETFONTSIZE") cmd = 5045;
				else if(ctmp=="DECMD_SETFORECOLOR") cmd = 5046;
				else if(ctmp=="DECMD_SPLITCELL") cmd = 5047;
				else if(ctmp=="DECMD_UNDERLINE") cmd = 5048;
				else if(ctmp=="DECMD_UNDO") cmd = 5049;
				else if(ctmp=="DECMD_UNLINK") cmd = 5050;
				else if(ctmp=="DECMD_UNORDERLIST") cmd = 5051;
				else if(ctmp=="DECMD_PROPERTIES") cmd = 5052;
				//cmd = eval(cmd);
			}
			if(parseInt(cmd)>=5000)
			{
				s = tbContentElement.QueryStatus(cmd);
				if(s == DECMDF_DISABLED || s == DECMDF_NOTSUPPORTED)
					TBSetState(tbbuttons[i], "gray");
				else if (s == DECMDF_ENABLED  || s == DECMDF_NINCHED)
					TBSetState(tbbuttons[i], "unchecked");
				else
					TBSetState(tbbuttons[i], "checked");
			}
		}
	}

	var stylelist = document.all("stylelist");

	s = tbContentElement.QueryStatus(DECMD_GETBLOCKFMT);
	if (s == DECMDF_DISABLED || s == DECMDF_NOTSUPPORTED)
		ParagraphStyle.disabled = true;
	else
	{
		ParagraphStyle.disabled = false;
		ParagraphStyle.value = tbContentElement.ExecCommand(DECMD_GETBLOCKFMT, OLECMDEXECOPT_DODEFAULT);
	}

	s = tbContentElement.QueryStatus(DECMD_GETFONTNAME);
	if (s == DECMDF_DISABLED || s == DECMDF_NOTSUPPORTED)
	{
		FontName.disabled = true;
		FontSize.disabled = true;
		if(stylelist)
			stylelist.disabled = true;
	}
	else
	{
		FontName.disabled = false;
		FontSize.disabled = false;

		if(stylelist)
			stylelist.disabled = false;

		var v = tbContentElement.ExecCommand(DECMD_GETFONTNAME, OLECMDEXECOPT_DODEFAULT);
		if(v!=FontName.value)
			FontName.value=v;
		v = tbContentElement.ExecCommand(DECMD_GETFONTSIZE, OLECMDEXECOPT_DODEFAULT);
		if(v!=FontSize.value)
			FontSize.value = v;

		if(stylelist)
		{
			try
			{
				var sel=tbContentElement.DOM.selection;
				var s=sel.createRange();
				var el=s.parentElement();
				while(el && (el.tagName!="FONT" || el.tagName!="P") && el.className=="")
					el = el.parentElement;
				if(el)
				{
					if(stylelist.value != el.className)
						stylelist.value = el.className;
				}
				else
					stylelist.selectedIndex = 0;
			}catch(e){}
		}
	}
}

function Locked()
{
	Lock=true;
	document.body.style.cursor = "wait";
}

function UnLock()
{
	Lock=false;
	document.body.style.cursor = "default";
}

function HTMLFilter(strDoc)
{
	if(strDoc.length>0)
		strDoc = tbContentElement.FilterSourceCode(strDoc);
	else
		strDoc = "";

	strDoc = strDoc.replace(/\xC4/g, "&Auml;");
	strDoc = strDoc.replace(/\xE4/g, "&auml;");
	strDoc = strDoc.replace(/\xCB/g, "&Euml;");
	strDoc = strDoc.replace(/\xEB/g, "&euml;");
	strDoc = strDoc.replace(/\xCF/g, "&Iuml;");
	strDoc = strDoc.replace(/\xEF/g, "&iuml;");
	strDoc = strDoc.replace(/\xD6/g, "&Ouml;");
	strDoc = strDoc.replace(/\xF6/g, "&ouml;");
	strDoc = strDoc.replace(/\xDC/g, "&Uuml;");
	strDoc = strDoc.replace(/\xFC/g, "&uuml;");
	strDoc = strDoc.replace(/\xA8/g, "&uml;");
	strDoc = strDoc.replace(/\xFF/g, "&yuml;");
	strDoc = strDoc.replace(/<\?xml[^>]+>/gi, "");
	//strDoc = strDoc.replace(/<o:p>/gi, "");
	//strDoc = strDoc.replace(/<\/o:p>/gi, "");
	strDoc = strDoc.replace(/<\/?[a-zA-Z]+:[^>]*>/g, "");
	//strDoc = strDoc.replace(/<!-*>/g, "");
	strDoc = strDoc.replace(/page-break-after[^;]*;/gi, "");
	strDoc = strDoc.replace(/ style=['"]tab-interval:[^'"]*['"]/gi, "");
	strDoc = strDoc.replace(/ class=MsoNormalTable/gi, "");
	strDoc = strDoc.replace(/ class=MsoNormal/gi, "");
	strDoc = strDoc.replace(/mso-[^:]*:"[^"]*";/gi, "");
	strDoc = strDoc.replace(/mso-[^;'"]*;*(\n|\r)*/gi, "");

	return strDoc;
}

function GetContent()
{
	var str = "";
	if (!EditMode)
		str = HTMLFilter(tbContentElement.DOM.body.innerHTML);
	else
		str = TEXTed.value;

	///////////workflow
	<?if($WF_CONVERT=="Y"):?>
		<?if($DEBUG):?>
		alert ('GetContent WF -> PATH');
		<?endif;?>
		var rewf = new RegExp("(<img[^>]+src\\s*=\\s*\")/bitrix/admin/workflow_get_file.php\\?did=<?echo $DOCUMENT_ID?>(&|&amp;)site=<?=$site?>(&|&amp;)wf_path=<?=$WF_PATH?>&[^=]+=([^>]+)(\"[^>]*>)","gi");
		str = str.replace(rewf, "$1$4$5");
	//alert(str);
	<?endif?>
	<?if($DEBUG):?>
	alert (str);
	<?endif;?>
	return str;
}

function SetContent(str)
{
	///////////workflow
	<?if($WF_CONVERT=="Y"):?>
	if(!EditMode)
	{
		<?if($DEBUG):?>
		alert ('SetContent PATH -> WF');
		<?endif;?>
		var rewf = new RegExp("(<img[^>]+?src\\s*=\\s*\")(\\S+)(\"[^>]*>)","gi");
		str = str.replace(rewf, "$1/bitrix/admin/workflow_get_file.php?did=<?echo $DOCUMENT_ID?>&site=<?=$site?>&wf_path=<?=$WF_PATH?>&fname=$2$3");
		<?if($DEBUG):?>
		alert (str);
		<?endif;?>
	}
	<?endif?>
	tbContentElement.DOM.body.innerHTML = str;
}


function SetTitle_onclick()
{
	TBSetState(document.all("edittitle"), "checked");

	var args = new Array();
	var arr = null;
	args["title"] = document.UploadForm.title.value;
	arr = null;
	arr = showModalDialog("fileman_dialog.php?dtype=title&lang=<?echo LANG?>", args, "dialogWidth:440px; dialogHeight:120px");
	if (arr != null)
	{
		if(document.UploadForm.title.value != arr["title"])
		{
			document.UploadForm.title.value = arr["title"];
			bDocumentChanged = true;
		}
	}

	TBSetState(document.all("edittitle"), "unchecked");

	//tbContentElement.focus();
}

function SetProperties_onclick()
{
	TBSetState(document.all("editprops"), "checked");

	var args = new Array();
	var arr = null;
	args["props"] = document.UploadForm.propsvals.value;
	args["props_types"] = document.UploadForm.props_types.value;
	arr = null;
	arr = showModalDialog("fileman_dialog.php?dtype=properties&lang=<?echo LANG?>", args, "dialogWidth:650px; dialogHeight:350px; resizable:yes; status:no;");
	if (arr != null)
	{
		if (document.UploadForm.propsvals.value != arr["props"])
		{
			document.UploadForm.propsvals.value = arr["props"];
			bDocumentChanged = true;
		}
	}

	TBSetState(document.all("editprops"), "unchecked");

	tbContentElement.focus();
}

function SaveAs(bExit)
{
	var args = new Array();
	var arr = null;
	args["title"] = document.UploadForm.title.value;
	args["filename"] = document.UploadForm.filename.value;
	arr = null;
	arr = showModalDialog("fileman_dialog.php?dtype=saveas&lang=<?echo LANG?>&site=<?=$site?>&path="+strDirPath, args, "dialogWidth:550px; dialogHeight:400px");
	if (arr != null)
	{
		document.all("new").value = "Y";
		document.UploadForm.title.value = arr["title"];
		document.UploadForm.path.value = arr["path"];
		document.UploadForm.filename.value = arr["filename"];
		document.UploadForm.menutype.value = "";
		if(arr["menutype"])
		{
			document.UploadForm.menutype.value = arr["menutype"];
			document.UploadForm.menuitem.value = arr["menuitem"];
			if(arr["menuitem"]<=0)
			{
				document.UploadForm.newitemname.value = arr["newitemname"];
				document.UploadForm.newitempos.value = arr["newitempos"];
			}
		}

		Save(bExit);
	}
	else
		tbContentElement.focus();
}

function ConvPhpTagToImg(src)
{
	var re = new RegExp("{{#PHPSCRIPT([0-9]+)#}}","i");
	while((arr = re.exec(src)) != null)
	{
		var str = arAllScr[RegExp.$1];
		src = src.substr(0, arr.index) + str + src.substr(arr.lastIndex);
	}
	return src;
}

var arAllScr = Array();
function ConvImgToPhpTag(src)
{
	var i=0;
	arAllScr = Array();
	var re = new RegExp("(<img[^>]+scraction=[^>]+>)","i");
	while((arr = re.exec(src)) != null)
	{
		arAllScr[i] = RegExp.$1;
		src = src.substr(0, arr.index) + '{{#PHPSCRIPT'+i+'#}}' + src.substr(arr.lastIndex);
		i++;
	}

	return src;
}

function Save(bExit)
{
	if(!bExit)
		UploadForm.apply.value = "Y";

	if (!EditMode)
		UploadForm.filesrc.value = HTMLFilter(tbContentElement.DOM.body.innerHTML);
	else
		UploadForm.filesrc.value = ConvPhpTagToImg(TEXTed.value);

	UploadForm.submit();
}

<?
//если новый файл и задано новое имя
if($bEdit)
	$fpath = '&path="+escape(strDirPath)+"&new=Y';
else
	$fpath = '&path='.urlencode($path."/".$filename);
?>

function NewPage_onclick(id)
{
	if((tbContentElement.IsDirty || bDocumentChanged) && confirm("<?echo GetMessage("FILEMAN_HTMLEDIT_PAGE_NOT_SAVED")?>"))
	{
		UploadForm.back_url.value = "/bitrix/admin/fileman_html_edit.php?lang=<?echo LANG?>&site=<?=$site?>&path="+escape(strDirPath)+"&new=Y&back_url=<?echo UrlEncode($back_url)?>"+(id?"&template="+escape(id):"");
		if(is_new)
			SaveAs(true);
		else
			Save(true);
	}
	else
		window.location="/bitrix/admin/fileman_html_edit.php?lang=<?echo LANG?>&site=<?=$site?><?=$fpath?>&back_url=<?=UrlEncode($back_url)?>"+(id?"&template="+escape(id):"");
}

function TemlpateSelectTmp()
{
	document.onmousedown = TemlpateSelect;
	document.onmouseup = "";
}

function TemlpateSelect()
{
	if(iTemplateselect==0)
	{
		iTemplateselect=1;
		Templateselect.style.left=document.all("tbnewdoc").getBoundingClientRect().left-2;
		Templateselect.style.top=document.all("tbnewdoc").getBoundingClientRect().bottom;
		Templateselect.style.display="block";
		Templateselect.style.visibility = "visible";
		tbnewdoc.className='buttonChecked';
		tbnewdocex.className='buttonChecked';
		//TBSetState(document.all(id), "checked");
		document.onmouseup = TemlpateSelectTmp;
	}
	else
	{
		iTemplateselect = 0;
		Templateselect.style.display="none";
		tbnewdoc.className='tbsmall';
		tbnewdocex.className='tbsmall';
		document.onmousedown = "";
	}
}

function SaveAs_onclick()
{
	TBSetState(document.all("tbsaveas"), "checked");
	SaveAs(false);
	TBSetState(document.all("tbsaveas"), "unchecked");
}

function Save_onclick()
{
	if(is_new)
		SaveAs(false);
	else
		Save(false);
}
////////////////

var Lock=false;
var EditMode = false;
function DECMD_EDITSRC_onclick()
{
	if (!Lock)
	{
		Locked();
		if (!EditMode)
		{
			//TXT
			var sel=tbContentElement.DOM.selection;
			var rSelection;
			rSelection=sel.createRange();
			if(sel.type=="Control")
			{
				var el = rSelection.commonParentElement();
				var rSelection = tbContentElement.DOM.body.createTextRange();
				rSelection.moveToElementText(el);
			}
			var rSelection2=rSelection.duplicate();
			rSelection.collapse(false);
			rSelection2.collapse(true);

			var tmpid = 'x' + Math.round(Math.random()*1000000) + 'x';
			rSelection2.pasteHTML("<font id='begsel"+tmpid+"'>|</font>");
			rSelection.pasteHTML("<font id='endsel"+tmpid+"'>|</font>");

			var p1 = -1, p2 = 0;
		    var arr;
			var str;

			str = ConvImgToPhpTag(GetContent());

			document.propbar["phpcode"].style.display = "none";
			document.getElementById("postprop").style.display = "none";
			document.getElementById("propbartr").style.height = "0px";

			var re = new RegExp("<font\\s+id\\s*=[^b>]*begsel"+tmpid+"[^>]*>[^<]+</font>","i");
		    if((arr = re.exec(str)) != null)
			{
				p1 = arr.index;
				str = str.substr(0, p1) + str.substr(arr.lastIndex);
			}
			re = new RegExp("<font\\s+id\\s*=[^b>]*endsel"+tmpid+"[^>]*>[^<]+</font>","i");
		    if((arr = re.exec(str)) != null)
			{
				p2 = arr.index;
				str = str.substr(0, p2) + str.substr(arr.lastIndex);
			}
			if(p1==-1)p1=p2;

			TEXTed.value = str;

			//TEXTed.value = HTMLFilter(tbContentElement.DOM.body.innerHTML);
			tbContentElement.style.display="none";
			TEXTed.style.display= "block";
			vis.style.display="none";
			styles.style.display="none";
			TBSetState(document.all("editsrc"), "checked");
			EditMode = 1;

			var rng = TEXTed.createTextRange();
			rng.collapse();
			var l1 = p1;
			var l2 = p2;
			for(i=0; i<l2; i++)
			{
				var ch = str.charAt(i);
				if(ch=="\n"/* || ch=="\r"*/)
				{
					if(i<l1) p1--;
					p2--;
				}
			}
			//alert(p1+"|"+p2);
			rng.moveEnd("character", p2);
			rng.moveStart("character", p1);
			rng.select();

			UnLock();
		}
		else
		{
			//HTML
			TBSetState(document.all("editsrc"), "unchecked");
			tbContentElement.style.display="block";
			tbContentElement.focus();
			var str = ConvPhpTagToImg(TEXTed.value);
			EditMode = 0;
			SetContent(str);
			TEXTed.style.display= "none";
			vis.style.display="inline";
			styles.style.display="block";
			UnLock();
		}
	}
}

function DECMD_ADDIMG_onclick()
{
	but = document.all("tbimage");
	TBSetState(but, "checked");
	var args = new Array();
	var arr = null;
	arr = showModalDialog("fileman_dialog.php?dtype=insimg&lang=<?=LANG?>&site=<?=$site?>&path="+escape(strDirPath)<?if($WF_CONVERT=="Y"):?>+"&WF_CONVERT=Y&DOCUMENT_ID=<?=$DOCUMENT_ID?>"<?endif;?>, args, "dialogWidth:550px; dialogHeight:450px; status:no; help:no;");
	if (arr != null)
	{
		tbContentElement.ExecCommand(DECMD_IMAGE, OLECMDEXECOPT_DONTPROMPTUSER, arr["path"]);
		var ce = GetCurElement(tbContentElement);
		if(ce.tagName=="IMG")
		{
			ce.width=arr["width"];
			ce.height=arr["height"];
			ce.border=0;
		}
	}
	TBSetState(but, "unchecked");
}

function SpellCheck_onclick()
{
	TBSetState(document.all("spellcheck"), "checked");
	SpellCheck();
	TBSetState(document.all("spellcheck"), "unchecked");
	tbContentElement.focus();
}


function SpellCheckTxt()
{
    try {
        var Word = new ActiveXObject("Word.Application");
        var Doc = Word.Documents.Add();
        var Uncorrected = "asdxsdc skdhc kjh test sj";//myObj.value;
        var Corrected = null;
        var wdDialogToolsSpellingAndGrammar     = 828;
        var wdDoNotSaveChanges             = 0;

        Word.Selection.Text = Uncorrected;
        Word.Dialogs(wdDialogToolsSpellingAndGrammar).Show();

        if (Word.Selection.Text.length != 1)
			Corrected = Word.Selection.Text;
        else
			Corrected = Uncorrected;

        //myObj.value = Corrected;

        Doc.Close(wdDoNotSaveChanges);
        Word.Quit();
    }
    catch(exception) {
        throw exception;
    }
}

var bCanceled = false;
function SpellCheck()
{
	var Word;
    try
	{
		Word = new ActiveXObject("Word.Application");
	}
	catch(e)
	{
		alert("<?echo GetMessage("FILEMAN_HTMLED_MSWORD_ERR")?>");
		return false;
	}
	Word.Quit(0);
	Word = new ActiveXObject("Word.Application");
	/*
	alert(Word.CheckSpelling(w));
	var sugg = Doc.GetSpellingSuggestions(w);
	for(i=1; i<=sugg.count; i++)
	{
		alert(sugg.item(i));
	}
	*/
	Word.Visible = false;
	//Word.Visible = true;
	var Doc = Word.Documents.Add();
	var prevpos = Word.Top;
	var prevstate = Word.WindowState;
	var prevstats = Word.Options.ShowReadabilityStatistics;
	Word.Options.ShowReadabilityStatistics = false;
	Word.WindowState = 0;
	Word.Top = -3000;
	SpellCheckTag(Word, tbContentElement.DOM.body);
	window.focus();
	Doc.Close(0);
	Word.Top = prevpos;
	Word.WindowState = prevstate;
	Word.Options.ShowReadabilityStatistics = prevstats;
	Word.NormalTemplate.Saved = true;
	Word.Quit(0);
	alert("<?echo GetMessage("FILEMAN_HTMLED_CHECK_END")?>");
}

function TimeOutChkSpell()
{

}

function SpellCheckTag(Word, Tag)
{
	if(Tag.nodeType == 3 && Tag.nodeValue != "")
	{
		var txt = Tag.nodeValue;
		Word.Selection.Text = txt;
		var res = Word.Dialogs(828).Show();
		Word.ActiveWindow.Visible = false;
		if(res==0)
			return false;
		if(res==-1)
			return true;
		if(Word.Selection.Text!=txt)
			Tag.nodeValue = Word.Selection.Text;
	}
	else
	{
		var childs = Tag.childNodes;
		var l = childs.length;
		for(var i=0; i<l; i++)
			if(!SpellCheckTag(Word, childs[i]))
				return false;
	}
	return true;
}

function DECMD_VISIBLEBORDERS_onclick()
{
	tbContentElement.ShowBorders = !tbContentElement.ShowBorders;
	if(tbContentElement.ShowBorders)
		TBSetState(document.all("tbborders"), "checked");
	else
		TBSetState(document.all("tbborders"), "unchecked");

	document.cookie = "htmledshowbord="+escape(tbContentElement.ShowBorders?"Y":"N")+"; expires=Fri, 31 Dec 2009 23:59:59 GMT;";

	tbContentElement.focus();
}

function DECMD_SHOWDETAILS_onclick()
{
	tbContentElement.ShowDetails = !tbContentElement.ShowDetails;

	if(tbContentElement.ShowDetails)
		TBSetState(document.all("tbdetails"), "checked");
	else
		TBSetState(document.all("tbdetails"), "unchecked");

	document.cookie = "htmledshowdet="+escape(tbContentElement.ShowDetails?"Y":"N")+"; expires=Fri, 31 Dec 2009 23:59:59 GMT;";

	tbContentElement.focus();
}

var bTableInsWin = false;
var iColorInsWin = 0;
function TABLE_INSERTTABLEEX_OK(pRow, pCol)
{
	var pVar = ObjTableInfo;
	ObjTableInfo.NumRows = pRow;
	ObjTableInfo.NumCols = pCol;
	ObjTableInfo.TableAttrs = 'border="0" width="100%" cellpadding="3" cellspacing="3"';
	tbContentElement.ExecCommand(DECMD_INSERTTABLE,OLECMDEXECOPT_DODEFAULT, pVar);
	TableProp.style.display="none";
	TableInsertClick();
	tbContentElement.focus();
}

function OnColorSelect(clr)
{
	if(iColorInsWin=="fgcolor")
		tbContentElement.ExecCommand(DECMD_SETFORECOLOR, OLECMDEXECOPT_DODEFAULT, clr);
	if(iColorInsWin=="bgcolor")
		tbContentElement.ExecCommand(DECMD_SETBACKCOLOR, OLECMDEXECOPT_DODEFAULT, clr);
	ColorPicker("");
	tbContentElement.focus();
}

function component_dropped(id)
{
	var el = tbContentElement.DOM.getElementById(id);
	if(el)
	{
		el.id = el.real_id;
		el.ondragstart=null;
		el.ondragend=null;
		component_select(el);
	}
}

function component_select(el)
{
	if(el.scraction)
	{
		if(el.scraction == 'standart' || el.scraction == 'phpscript')
		{
			var imgid = el.id;
			if(imgid.length<=11 || imgid.substring(0, 11)!="{#PHPSCRIPT")
			{
				var MAX_PHPSCRIPT = parseInt(document.UploadForm.MAX_PHPSCRIPT.value);
				document.UploadForm.MAX_PHPSCRIPT.value = MAX_PHPSCRIPT+1;
				var new_el=document.createElement("INPUT");
				new_el.type = "hidden";
				imgid = "{#PHPSCRIPT"+MAX_PHPSCRIPT+"#}";
				new_el.value = "<"+"??"+">";
				new_el.id = imgid;
				new_el.name = imgid;
				document.UploadForm.appendChild(new_el);
				el.id = imgid;
			}
			if(el.scraction == 'phpscript')
			{
				document.getElementById("postprop").style.display = "none";
				document.propbar["phpcode"].style.display = "block";
				document.getElementById("propbartr").style.height = "150px";
				document.propbar["phpcode"].value = document.UploadForm[imgid].value;
				document.propbar["phpcode"].curscript = document.UploadForm[imgid];
			}
			if(el.scraction == 'standart')
			{
				document.propbar["phpcode"].style.display = "none";
				document.getElementById("postprop").style.backgoundColor = "#000000";
				document.getElementById("postprop").style.display = "block";
				document.getElementById("propbartr").style.height = "150px";
				document.getElementById("phpstandart").field_name.value=imgid;
				document.getElementById("phpstandart").scrid.value = el.scrid;
				if(el.scredit)
					document.getElementById("phpstandart").scredit.value = el.scredit;
				document.getElementById("phpstandart").values.value=document.UploadForm[imgid].value;
				document.getElementById("phpstandart").action = "/bitrix/admin/fileman_html_phpstandart.php";
				document.getElementById("phpstandart").submit();
			}
			bInComponent = true;
		}
	}
}

function tbContentElement_onclick()
{
	var cur_el = GetCurElement();
	if(cur_el.tagName=="IMG" && cur_el.scraction)
	{
		component_select(cur_el);
	}
	else if(document.propbar["phpcode"].value!='')
	{
		document.propbar["phpcode"].curscript = null;
		document.propbar["phpcode"].value = '';
		document.propbar["phpcode"].style.display = "none";
		document.getElementById("propbartr").style.height = "0px";
		bInComponent = false;
	}
	else
	{
		document.propbar["phpcode"].style.display = "none";
		document.getElementById("postprop").style.display = "none";
		document.getElementById("propbartr").style.height = "0px";
		bInComponent = false;
	}
}

function tbContentElement_mousedown()
{
	if(bTableInsWin)
		TableInsertClick();
	if(iColorInsWin!=0)
		ColorPicker(iColorInsWin);
	if(iTemplateselect!=0)
		TemlpateSelect();
}

function TableInsertClick()
{
	if(!bTableInsWin)
	{
		TableProp.style.left=document.all("tbinstable").getBoundingClientRect().left;
		TableProp.style.top=document.all("tbinstable").getBoundingClientRect().bottom;
		TableProp.style.display="block";
		TableProp.style.visibility = "visible";
		TableProp.SetObj(document);
		TBSetState(document.all("tbinstable"), "checked");
		document.onmousedown = TableInsertClick;
	}
	else
	{
		TableProp.style.display="none";
		TBSetState(document.all("tbinstable"), "unchecked");
		document.onmousedown = "";
		tbContentElement.focus();
	}

	bTableInsWin = !bTableInsWin;
}

/////////////////////////////////////////////////////////
function ColorPicker(id, clr)
{
	if(iColorInsWin==0)
	{
		iColorInsWin = id;
		ColorPick.style.left=document.all(id).getBoundingClientRect().left-175;
		ColorPick.style.top=document.all(id).getBoundingClientRect().bottom;
		ColorPick.style.display="block";
		ColorPick.style.visibility = "visible";
		ColorPick.SetColor(clr);
		TBSetState(document.all(id), "checked");
		document.onmousedown = ColorPicker;
	}
	else
	{
		ColorPick.style.display="none";
		TBSetState(document.all(iColorInsWin), "unchecked");
		iColorInsWin = 0;
		document.onmousedown = "";
	}
}

/////////////////////////////////////////////////////////
function DECMD_HYPERLINK_onclick()
{
	TBSetState(document.all("tblink"), "checked");
	var sel=tbContentElement.DOM.selection;
	var stmp=sel.createRange();
	var s = stmp;
	if(s.length>0)
		s = s.item(0);
	var el=null;
	try{el=s.parentElement();}catch(e){el=s.parentElement;};

	var url = "http://";
	while(el && el.tagName!="A" && el.tagName!="BODY")
		el = el.parentElement;

	if(el && el.tagName=="A")
		url = el.href;

	if(url.indexOf(s2+"#")==0)
		url = url.substring(s2.length+1);

	if(strSite.length+7<=url.length && url.substring(0, strSite.length+7)=="http://"+strSite)
		url = url.substring(strSite.length+7);

	var args = new Array();
	var arr = null;
	args["url"] = url;
	arr = null;
	arr = showModalDialog("fileman_dialog.php?dtype=addurl&site=<?echo $site?>&lang=<?echo LANG?>", args, "dialogWidth:440px; dialogHeight:130px");
	if (arr != null)
	{
		url = arr["url"];
		if(url.length>0 && url!="http://")
			tbContentElement.ExecCommand(DECMD_HYPERLINK, OLECMDEXECOPT_DONTPROMPTUSER, url);
		else
			if(el && el.tagName=="A")
				el.outerHTML = el.innerHTML;
	}
	TBSetState(document.all("tblink"), "unchecked");
	tbContentElement.focus();
}

//////////////////////////////////////////////////////////
// Toolbar CLICK!
//////////////////////////////////////////////////////////
function DECMD_COMMAND_onclick(id)
{
	switch(id)
	{
	case DECMD_SETFORECOLOR:
		var clr = tbContentElement.ExecCommand(DECMD_GETFORECOLOR, OLECMDEXECOPT_DODEFAULT);
		ColorPicker("fgcolor", clr);
		break;
	case DECMD_SETBACKCOLOR:
		var clr = tbContentElement.ExecCommand(DECMD_GETBACKCOLOR, OLECMDEXECOPT_DODEFAULT);
		ColorPicker("bgcolor", clr);
		break;
	case DECMD_FINDTEXT:
		TBSetState(document.all("tbfind"), "checked");
		tbContentElement.ExecCommand(DECMD_FINDTEXT,OLECMDEXECOPT_PROMPTUSER);
		TBSetState(document.all("tbfind"), "unchecked");
		break;
	case DECMD_INSERTTABLE:
		TableInsertClick();
		return false;
	case DECMD_HYPERLINK:
		DECMD_HYPERLINK_onclick();
		break;
	default:
		tbContentElement.ExecCommand(id, OLECMDEXECOPT_DODEFAULT);
	}
	tbContentElement.focus();
}
/////////////////////////////////////////////////////////

function ParagraphStyle_onchange()
{
  tbContentElement.ExecCommand(DECMD_SETBLOCKFMT, OLECMDEXECOPT_DODEFAULT, ParagraphStyle.value);
  tbContentElement.focus();
}

function FontName_onchange()
{
	tbContentElement.ExecCommand(DECMD_SETFONTNAME, OLECMDEXECOPT_DODEFAULT, FontName.value);
	tbContentElement.focus();
}

function FontSize_onchange()
{
	tbContentElement.ExecCommand(DECMD_SETFONTSIZE, OLECMDEXECOPT_DODEFAULT, parseInt(FontSize.value));
	tbContentElement.focus();
}

/*********************
CONTEXT MENU
**********************/
var DECMD_TABLEPROPERTIES = 400;
var DECMD_IMGPROPERTIES = 401;
var contextElement = null;
function tbContentElement_ShowContextMenu()
{
	var menuStrings = new Array();
	var menuStates = new Array();
	var state;
	var i
	var idx = 0;

	ContextMenu.length = 0;

	contextElement=GetCurElement();

	if(contextElement.scraction)
		return false;

	ContextMenu[idx++] = Array("Cut", DECMD_CUT);
	ContextMenu[idx++] = Array("Copy", DECMD_COPY);
	ContextMenu[idx++] = Array("Paste", DECMD_PASTE);
	if(contextElement.tagName=="IMG")
	{
		ContextMenu[idx++] = Array("", 0);
		ContextMenu[idx++] = Array("Image Properties", DECMD_IMGPROPERTIES);
	}

	if(tbContentElement.QueryStatus(DECMD_INSERTROW) != DECMDF_DISABLED)
	{
		ContextMenu[idx++] = Array("", 0);
		ContextMenu[idx++] = Array("Insert Row", DECMD_INSERTROW);
		ContextMenu[idx++] = Array("Delete Rows", DECMD_DELETEROWS);
		ContextMenu[idx++] = Array("", 0);
		ContextMenu[idx++] = Array("Insert Column", DECMD_INSERTCOL);
		ContextMenu[idx++] = Array("Delete Columns", DECMD_DELETECOLS);
		ContextMenu[idx++] = Array("", 0);
		ContextMenu[idx++] = Array("Insert Cell", DECMD_INSERTCELL);
		ContextMenu[idx++] = Array("Delete Cells", DECMD_DELETECELLS);
		ContextMenu[idx++] = Array("Merge Cells", DECMD_MERGECELLS);
		ContextMenu[idx++] = Array("Split Cell", DECMD_SPLITCELL);
	}

	if(contextElement.tagName=="TABLE")
	{
		ContextMenu[idx++] = Array("", 0);
		ContextMenu[idx++] = Array("Table Properties", DECMD_TABLEPROPERTIES);
	}

	for (i=0; i<ContextMenu.length; i++)
	{
		menuStrings[i] = ContextMenu[i][0];

		if (menuStrings[i] != "" && ContextMenu[i][1]>=5000)
			state = tbContentElement.QueryStatus(ContextMenu[i][1]);
		else
			state = DECMDF_ENABLED;

		if (state == DECMDF_DISABLED || state == DECMDF_NOTSUPPORTED)
			menuStates[i] = OLE_TRISTATE_GRAY;
		else if (state == DECMDF_ENABLED || state == DECMDF_NINCHED)
			menuStates[i] = OLE_TRISTATE_UNCHECKED;
		else
			menuStates[i] = OLE_TRISTATE_CHECKED;
	}

	tbContentElement.SetContextMenu(menuStrings, menuStates);
}

function tbContentElement_ContextMenuAction(itemIndex)
{
	if (ContextMenu[itemIndex][1] == DECMD_TABLEPROPERTIES)
		TableProperty();
	else if (ContextMenu[itemIndex][1] == DECMD_IMGPROPERTIES)
		ImageProperty();
	else
		tbContentElement.ExecCommand(ContextMenu[itemIndex][1], OLECMDEXECOPT_DODEFAULT);
}

/*********************
TOOLBAR
**********************/
function MouseOver()
{
	if(this.gray!=true && this.checked!=true)
		this.className='button';
}

function MouseOut()
{
	if(this.gray!=true && this.checked!=true)
		this.className='tb';
}

function MouseDown()
{
	if(this.gray!=true && this.checked!=true)
		this.className='buttonDown';
}

function MouseClick()
{
	if(this.gray==true)
		return;
	var cmdtmp = this.tbcmd;
	if("_"+cmdtmp != "_undefined")
	{
		try{cmd = eval(cmdtmp);}catch(e){cmd=0;}
		if(parseInt(cmd)>0)
			DECMD_COMMAND_onclick(parseInt(cmd));
		else
			eval(cmdtmp+"_onclick()");
	}
}

function TBSetState(el, st)
{
	if(st=="checked")
	{
		if(el.gray==false && el.checked==true) return;
		el.gray = false;
		el.checked = true;
		el.style.filter="";
		el.className='buttonChecked';
	}
	else if(st=="unchecked")
	{
		if(el.gray==false && el.checked==false) return;
		el.checked = false;
		el.gray = false;
		el.style.filter="";
		el.className='tb';
	}
	else if(st=="gray")
	{
		if(el.gray==true) return;
		el.gray = true;
		el.style.filter="alpha(opacity=25)";
	}
	else
	{
		if(el.gray==false && el.style.filter=="") return;
		el.gray = false;
		el.style.filter="";
	}
}
//-->
</script>
</head>
<body onload="return window_onload()" scroll="NO">
<table border="0" cellpadding="0" cellspacing="1" width="100%" height="100%">
<tr height="0%">
<td id="toolbar" width="100%" colspan="2"><font style="font-size:0px;">
<?if($light!="Y"):?>
<img class="tb" id="tbexit" height="22" width="23" src="/bitrix/images/fileman/htmledit/exit.gif" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_EXIT")?>" tbcmd="Exit">
<nobr>
<img src="/bitrix/images/1.gif" class="separator">
<img class="tbsmall"
	onMouseOut="if(iTemplateselect==0){this.className='tbsmall';tbnewdocex.className='tbsmall';}"
	onMouseOver="if(iTemplateselect==0){this.className='button';tbnewdocex.className='button';}"
	onMouseDown="if(iTemplateselect==0){this.className='buttonDown';}"
	onClick="NewPage_onclick()"
	onMouseUp="if(iTemplateselect==0){this.className='buttonsmall';tbnewdocex.className='button';}" id="tbnewdoc" height="22" width="19" src="/bitrix/images/fileman/htmledit/newdoc.gif" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_ADD")?>"
	tbcmd="NewPage"><img id="tbnewdocex" class="tbsmall" height="22" width="11"
	onMouseOut="if(iTemplateselect==0){this.className='tbsmall';tbnewdoc.className='tbsmall';}"
	onMouseOver="if(iTemplateselect==0){this.className='button';tbnewdoc.className='button';}"
	onMouseDown="if(iTemplateselect==0){this.className='buttonDown';tbnewdoc.className='buttonDown';}TemlpateSelect();"
	onMouseUp="if(iTemplateselect==0){this.className='buttonsmall';tbnewdoc.className='button';}" src="/bitrix/images/fileman/htmledit/str.gif" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_ADD")?>">
<img class="tb" id="tbsave" height="22" width="23" src="/bitrix/images/fileman/htmledit/save.gif" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_SAVE")?>" tbcmd="Save">
<img class="tb" id="tbsaveas" height="22" width="23" src="/bitrix/images/fileman/htmledit/save_as.gif" tbcmd="SaveAs" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_SAVE_AS")?>">
<img src="/bitrix/images/1.gif" class="separator">
<img class="tb" id="edittitle" height="22" width="23" src="/bitrix/images/fileman/htmledit/title.gif" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_TITLE")?>" tbcmd="SetTitle">
<img class="tb" id="editprops" height="22" width="23" src="/bitrix/images/fileman/htmledit/property.gif" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_PROPERTY")?>" tbcmd="SetProperties">
</nobr>
<?endif?>
<img src="/bitrix/images/1.gif" class="separator">
<img class="tb" id="editsrc" height="22" width="23" src="/bitrix/images/fileman/htmledit/text.gif" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_SRC")?>" tbcmd="DECMD_EDITSRC">
<span id="vis">
<nobr>
<img src="/bitrix/images/1.gif" class="separator">
<img class="tb" id="tbcut" height="22" width="23" src="/bitrix/images/fileman/htmledit/cut.gif" tbcmd="DECMD_CUT" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_CUT")?>">
<img class="tb" id="tbcopy" height="22" width="23" src="/bitrix/images/fileman/htmledit/copy.gif" tbcmd="DECMD_COPY" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_COPY")?>">
<img class="tb" id="tbpaste" height="22" width="23" src="/bitrix/images/fileman/htmledit/paste.gif" tbcmd="DECMD_PASTE" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_PASTE")?>">
</nobr>
<nobr>
<img src="/bitrix/images/1.gif" class="separator">
<img class="tb" id="tbundo" height="22" width="23" src="/bitrix/images/fileman/htmledit/undo.gif" tbcmd="DECMD_UNDO" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_UNDO")?>">
<img class="tb" id="tbredo" height="22" width="23" src="/bitrix/images/fileman/htmledit/redo.gif" tbcmd="DECMD_REDO" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_REDO")?>">
</nobr>
<nobr>
<img src="/bitrix/images/1.gif" class="separator">
<img class="tb" id="tbdetails" height="22" width="23" src="/bitrix/images/fileman/htmledit/details.gif" tbcmd="DECMD_SHOWDETAILS" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_SHOW_DETAILS")?>">
<img class="tb" id="tbborders" height="22" width="23" src="/bitrix/images/fileman/htmledit/borders.gif" tbcmd="DECMD_VISIBLEBORDERS" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_SHOW_BORDERS")?>">
</nobr>
<nobr>
<img src="/bitrix/images/1.gif" class="separator">
<img class="tb" id="spellcheck" height="22" width="23" src="/bitrix/images/fileman/htmledit/spellcheck.gif" alt="<?echo GetMessage("FILEMAN_HTMLED_CHECK")?>" tbcmd="SpellCheck">
<img class="tb" id="tbfind" height="22" width="23" src="/bitrix/images/fileman/htmledit/find.gif" tbcmd="DECMD_FINDTEXT" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_FIND")?>">
<img class="tb" id="tbinstable" height="22" width="23" src="/bitrix/images/fileman/htmledit/instable.gif" tbcmd="DECMD_INSERTTABLE" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_ADD_TABLE")?>">
<img class="tb" id="tblink" height="22" width="23" src="/bitrix/images/fileman/htmledit/link.gif" tbcmd="DECMD_HYPERLINK" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_URL")?>">
<img class="tb" id="tbimage" height="22" width="23" src="/bitrix/images/fileman/htmledit/image.gif" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_PIC")?>" tbcmd="DECMD_ADDIMG">
</nobr>
<nobr>
<img src="/bitrix/images/1.gif" class="separator">
<img class="tb" id="bgcolor" height="22" width="23" src="/bitrix/images/fileman/htmledit/bgcolor.gif" tbcmd="DECMD_SETBACKCOLOR" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_BACKGROUND")?>">
<img class="tb" id="fgcolor" height="22" width="23" src="/bitrix/images/fileman/htmledit/fgcolor.gif" tbcmd="DECMD_SETFORECOLOR" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_FOREGROUND")?>">
<img src="/bitrix/images/1.gif" height="25" width="1">
</nobr>
<nobr>
<img src="/bitrix/images/1.gif" class="separator">
<img class="tb" height="22" width="23" src="/bitrix/images/fileman/htmledit/insrow.gif" tbcmd="DECMD_INSERTROW" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_ADD_ROW")?>">
<img class="tb" height="22" width="23" src="/bitrix/images/fileman/htmledit/delrow.gif" tbcmd="DECMD_DELETEROWS" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_DEL_ROW")?>">
<img class="tb" height="22" width="23" src="/bitrix/images/fileman/htmledit/inscol.gif" tbcmd="DECMD_INSERTCOL" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_ADD_COL")?>">
<img class="tb" height="22" width="23" src="/bitrix/images/fileman/htmledit/delcol.gif" tbcmd="DECMD_DELETECOLS" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_DEL_COL")?>">
<img class="tb" height="22" width="23" src="/bitrix/images/fileman/htmledit/inscell.gif" tbcmd="DECMD_INSERTCELL" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_ADD_CELL")?>">
<img class="tb" height="22" width="23" src="/bitrix/images/fileman/htmledit/delcell.gif" tbcmd="DECMD_DELETECELLS" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_DEL_CELL")?>">
<img class="tb" height="22" width="23" src="/bitrix/images/fileman/htmledit/mrgcell.gif" tbcmd="DECMD_MERGECELLS" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_MERGE_CELL")?>">
<img class="tb" height="22" width="23" src="/bitrix/images/fileman/htmledit/spltcell.gif" tbcmd="DECMD_SPLITCELL" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_SPLIT_CELL")?>">
</nobr>
<nobr>
<img src="/bitrix/images/1.gif" class="separator">
<img class="tb" height="22" width="23" src="/bitrix/images/fileman/htmledit/bold.gif" tbcmd="DECMD_BOLD" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_BOLD")?>">
<img class="tb" height="22" width="23" src="/bitrix/images/fileman/htmledit/italic.gif" tbcmd="DECMD_ITALIC" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_ITALIC")?>">
<img class="tb" height="22" width="23" src="/bitrix/images/fileman/htmledit/under.gif" tbcmd="DECMD_UNDERLINE" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_UNDERLINE")?>">
</nobr>
<nobr>
<img src="/bitrix/images/1.gif" class="separator">
<img class="tb" height="22" width="23" src="/bitrix/images/fileman/htmledit/left.gif" tbcmd="DECMD_JUSTIFYLEFT" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_LEFT")?>">
<img class="tb" height="22" width="23" src="/bitrix/images/fileman/htmledit/center.gif" tbcmd="DECMD_JUSTIFYCENTER" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_CENTER")?>">
<img class="tb" height="22" width="23" src="/bitrix/images/fileman/htmledit/right.gif" tbcmd="DECMD_JUSTIFYRIGHT" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_RIGHT")?>">
</nobr>
<nobr>
<img src="/bitrix/images/1.gif" class="separator">
<img class="tb" height="22" width="23" src="/bitrix/images/fileman/htmledit/numlist.gif" tbcmd="DECMD_ORDERLIST" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_ORDERLIST")?>">
<img class="tb" height="22" width="23" src="/bitrix/images/fileman/htmledit/bullist.gif" tbcmd="DECMD_UNORDERLIST" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_UNORDERLIST")?>">
<img class="tb" height="22" width="23" src="/bitrix/images/fileman/htmledit/deindent.gif" tbcmd="DECMD_OUTDENT" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_OUTDENT")?>">
<img class="tb" height="22" width="23" src="/bitrix/images/fileman/htmledit/inindent.gif" tbcmd="DECMD_INDENT" alt="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_INDENT")?>">
</nobr>
</span>
</font>
</td></tr>
<tr id="styles"><td colspan="2" width="100%" >
		  <select ID="ParagraphStyle" class="tbGeneral" TITLE="<?=GetMessage("FILEMAN_HTMLED_HS");?>" LANGUAGE="javascript" onchange="return ParagraphStyle_onchange()">
		    <option value="Normal"><?=GetMessage("FILEMAN_HTMLED_H_NO")?></option>
		    <option value="Heading 1"><?=GetMessage("FILEMAN_HTMLED_HEAD")?> 1</option>
		    <option value="Heading 2"><?=GetMessage("FILEMAN_HTMLED_HEAD")?> 2</option>
		    <option value="Heading 3"><?=GetMessage("FILEMAN_HTMLED_HEAD")?> 3</option>
		    <option value="Heading 4"><?=GetMessage("FILEMAN_HTMLED_HEAD")?> 4</option>
		    <option value="Heading 5"><?=GetMessage("FILEMAN_HTMLED_HEAD")?> 5</option>
		    <option value="Heading 6"><?=GetMessage("FILEMAN_HTMLED_HEAD")?> 6</option>
		  </select>
		<?
		$arStyles = Array();
		function bhtml_style_tmp($matches, $matches2)
		{
			global $arStyles;
			$matches2 = trim($matches2);
			if(strlen($matches2)>0)
				$arStyles[] = Array($matches, substr($matches2, 2, -2));
			return "\n";
		}
		$tmp_styles = "\n".$styles."\n";
		preg_replace("'\n\.([A-Z0-9_]+).*?{.*?}.*?((\r\n)|(/\*.*?[\r\n]))?'ies", "bhtml_style_tmp('\\1', '\\2')", $tmp_styles);

		if(count($arStyles)>0):
		?>
		<select name="stylelist" onChange="OnStyleChange()" title="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_STYLE")?>">
			<option value=""><?echo GetMessage("FILEMAN_HTMLEDIT_TB_STYLE_WITHOUT")?></option>
			<?
			for($i=0; $i<count($arStyles); $i++):
				$arSt = $arStyles[$i];
			?>
			<option value="<?echo $arSt[0]?>" class="<?echo $arSt[0]?>"><?echo $arSt[1]?></option>
			<?endfor;?>
		</select>
		<?endif?>
		<select name="FontName" onchange="return FontName_onchange()" title="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_STYLE_FONT_FACE")?>">
		    <option value="Arial">Arial</option>
		    <option value="Tahoma">Tahoma</option>
		    <option value="Verdana">Verdana</option>
		    <option value="Courier New">Courier New</option>
		    <option value="Times New Roman">Times New Roman</option>
		    <option value="Wingdings">Wingdings</option>
		</select>
		<select ID="FontSize" onchange="return FontSize_onchange()" title="<?echo GetMessage("FILEMAN_HTMLEDIT_TB_STYLE_FONT_SIZE")?>"><?for($i=1;$i<=7;$i++):?><option value="<?echo $i?>"><?echo $i?></option><?endfor?></select>
</td></tr>
<tr height="100%">
<td width="100%">
	<table height="100%" width="100%" cellpadding="0" cellspacing="1">
	<tr><td>
	<script LANGUAGE="JavaScript" SRC="/bitrix/admin/htmleditor2/old.js"></script>
	<script LANGUAGE="javascript" FOR="tbContentElement" EVENT="DisplayChanged"> return tbContentElement_DisplayChanged() </script>
	<script LANGUAGE="JavaScript" FOR="tbContentElement" EVENT="onmousedown"> tbContentElement_mousedown(); </script>
	<script LANGUAGE="JavaScript" FOR="tbContentElement" EVENT="onclick"> tbContentElement_onclick(); </script>
	<script LANGUAGE="JavaScript" FOR="tbContentElement" EVENT="ondrop"> tbContentElement_ondrop(); </script>
	<script LANGUAGE="JavaScript" FOR="tbContentElement" EVENT="onkeyup"> if(bInComponent) tbContentElement_onclick(); </script>
	<script LANGUAGE="javascript" FOR="tbContentElement" EVENT="ShowContextMenu">return tbContentElement_ShowContextMenu()</script>
	<script LANGUAGE="javascript" FOR="tbContentElement" EVENT="ContextMenuAction(itemIndex)">return tbContentElement_ContextMenuAction(itemIndex)</script>
	<TEXTAREA id="TEXTed" style="display:none; width:100%; height:100%; z-index:1; font-family:'Courier New,Arial'"></TEXTAREA>
	</td></tr>
	<tr id="propbartr" style="height:0px;"><td valign="top">
		<iframe style="display:none; width:100%; border:0px; height:100%; font-family:'Courier New,Arial';font-size: 11px;" name="postprop" id="postprop"></iframe>
		<table width="100%" cellpadding="0" cellspacing="0" border="0">
			<form target="postprop" id="phpstandart" method="POST" action="">
			<tr>
				<td><input type="hidden" name="field_name" value=""><input type="hidden" name="lang" value="<?=LANG?>"><input type="hidden" name="values" value=""><input type="hidden" name="site" value="<?=$site?>"><input type="hidden" name="scrid" value=""><input type="hidden" name="scredit" value=""></td>
			</tr>
			</form>
		</table>
		<table width="100%" cellpadding="0" cellspacing="0" border="0">
			<form name="propbar">
			<tr>
				<td><textarea style="display:none; width:100%; height:145; font-family:'Courier New,Arial';font-size:	11px;" wrap="off" name="phpcode" onchange="if(this.curscript)this.curscript.value=this.value;bDocumentChanged=true;"></textarea></td>
			</tr>
			</form>
		</table>
	</td></tr>
	</table>
</td>
<td id="toolbox" valign="top">
<?if($USER->IsAdmin() && !$bCheckExecFile && $light!="Y"):?>
	<script>
	function HideToolbox()
	{
		document.getElementById('tooldiv').style.display='none';
		document.getElementById('tooldivh').style.display='block';
		document.getElementById('toolbox').style.width='100px';
	}
	function ShowToolbox()
	{
		document.getElementById('tooldiv').style.display='block';
		document.getElementById('tooldivh').style.display='none';
		document.getElementById('toolbox').style.width='100px';
	}
	</script>

	<div id='tooldivh' style='display:none;'>
	<table cellpadding="0" cellspacing="0" border="0" bgcolor="buttonshadow">
	<tr><td align="center" style="padding:4px;"><a href="javascript:void(0)" onClick="ShowToolbox();return false;"><img src="/bitrix/images/fileman/htmledit/butin.gif" width="11" height="11" border="0"></a></td></tr>
	<tr><td align="left" style="padding:4px; padding-right:0px; font-size:12px; color:white; font-family:Arial; cursor:pointer; line-height:11px;" onclick="ShowToolbox()"><b>
	<?
	$rt = GetMessage("FILEMAN_HTMLED_COMPON");
	for($i=0; $i<strlen($rt); $i++)
		echo substr($rt, $i, 1)."<br>";
	?></b></td></tr>
	</table>
	</div>
	<div id='tooldiv'>
	<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
	<td width="0%" bgcolor="buttonshadow" style="padding-left:4px"><a href="javascript:void(0)" onClick="HideToolbox();return false;"><img src="/bitrix/images/fileman/htmledit/but.gif"" width="11" height="11" border="0"></a></td>
	<td width="0%" bgcolor="buttonshadow" ><img src="/bitrix/images/1.gif" width="3" height="3"></td>
	<td width="100%" bgcolor="buttonshadow" onDblClick="HideToolbox();" style="font-size:12px; color:white; padding:2px; font-family:Arial;"><b><?=GetMessage("FILEMAN_HTMLED_COMPON");?></b></td>
	</tr>
	<tr>
	<td colspan="3"><img src="/bitrix/images/1.gif" width="180" height="3"></td>
	</tr>
	</table>
	<select name='component_type' onChange="comp_frame.location='/bitrix/admin/fileman_html_components.php?lang=<?=LANG?>&template=<?=$ar_templ?>&site=<?=$site?>&component_type='+this.value">
	<?
	if(!is_set($_REQUEST, 'component_type'))
		$component_type = $_SESSION['FILEMAN_COMPONENT_TYPE'];
	else
		$component_type = $_REQUEST["component_type"];

	$_SESSION['FILEMAN_COMPONENT_TYPE'] = $component_type;

	$sInit = false;
	$arRes = CTemplates::GetFolderList();
	foreach($arRes as $fold=>$name):
		if(!$sInit)
			$sInit = $fold;
	?>
		<option value="<?=htmlspecialchars($fold)?>" <?if($fold==$component_type){$sInit=$fold;echo " selected";}?>><?=htmlspecialcharsex($name)?></option>
	<?endforeach?>
	</select>
	<iframe style="border:0px;" id="comp_frame" src="/bitrix/admin/fileman_html_components.php?lang=<?=LANG?>&template=<?=$ar_templ?>&site=<?=$site?>&component_type=<?=$sInit?>" height="90%" width="100%"></iframe>
	</div>
<?endif?>
</td>
</tr>
</table>


<object id="TableProp" data="fileman_dialog.php?dtype=tableinsert&lang=<?echo LANG?>" border="0" width="0" height="0" type="text/x-scriptlet" style="position:absolute; left:-5000;top:-2000;display:none;"></object>
<SCRIPT  LANGUAGE="JavaScript" FOR=TableProp EVENT="onscriptletevent(eventName, eventObject)">
	if(eventName=="OnTableSelect")
		TABLE_INSERTTABLEEX_OK(eventObject[0], eventObject[1]);
</SCRIPT>

<object id="ColorPick" data="fileman_dialog.php?dtype=colorpick&lang=<?echo LANG?>" border="0" type="text/x-scriptlet" style="position:absolute; left:-5000;top:-2000; width=200px; height=155px; display:none;"></object>
<SCRIPT  LANGUAGE="JavaScript" FOR=ColorPick EVENT="onscriptletevent(eventName, eventObject)">
	if(eventName=="OnColorSelect")
		OnColorSelect(eventObject);
</SCRIPT>

<?if($light!="Y"):?>
<object id="Templateselect" data="fileman_dialog.php?dtype=templatesel&lang=<?echo LANG?>&template=<?=urlencode($ar_templ)?>" border="0" type="text/x-scriptlet" style="position:absolute; left:-5000;top:-2000; width=200px; height=155px; display:none;"></object>
<SCRIPT LANGUAGE="JavaScript" FOR=Templateselect EVENT="onscriptletevent(eventName, eventObject)">
	if(eventName=="OnTemplateSelect")
	{
		TemlpateSelect();
		NewPage_onclick(eventObject);
	}
	else if(eventName=="setsize")
	{
		Templateselect.style.width=eventObject[0];
		Templateselect.style.height=eventObject[1];
	}

</SCRIPT>
<?endif?>
<object ID="ObjTableInfo" CLASSID="clsid:47B0DFC7-B7A3-11D1-ADC5-006008A5848C" VIEWASTEXT style="position:absolute; left:-5000;top:-2000;display:none;"></object>
<table style="display:none">
<form NAME="UploadForm" action="fileman_html_edit.php?lang=<?=LANG?>" method="POST" enctype="multipart/form-data">
<input type="hidden" name="logical" value="<?=htmlspecialchars($logical)?>">
<tr><td>
<?=bitrix_sessid_post()?>
<input type="hidden" name="back_url" value="<?echo $back_url;?>">
<input type="hidden" name="title" value="<?echo htmlspecialchars($title)?>">

<input type="hidden" name="propsvals" value="<?echo htmlspecialchars($propsvals)?>">
<input type="hidden" name="props_types" value="<?echo htmlspecialchars(COption::GetOptionString("fileman", "propstypes", "", $site));?>">

<input type="hidden" name="tonew" value="">
<input type="hidden" name="save" value="Y">
<input type="hidden" name="site" value="<?=$site?>">
<input type="hidden" name="filesrc" value="">
<input type="hidden" name="apply" value="N">
<input type="hidden" name="menutype" value="">
<input type="hidden" name="menuitem" value="">
<input type="hidden" name="newitemname" value="">
<input type="hidden" name="newitempos" value="">
<input type="hidden" name="template" value="<?echo htmlspecialchars($template)?>">
<?if($gotonewpage=="Y"):?><input type="hidden" name="gotonewpage" value="Y"><?endif?>
<?if($backnewurl=="Y"):?><input type="hidden" name="backnewurl" value="Y"><?endif?>
<?if($bEdit):?>
	<input type="hidden" name="new" value="N">
	<input type="hidden" name="filename" value="<?echo $arParsedPath["LAST"]?>">
	<input type="hidden" name="old_filename" value="<?echo $arParsedPath["LAST"]?>">
	<input type="hidden" name="path" value="<?echo $arParsedPath["PREV"];?>">
	<input type="hidden" name="old_path" value="<?echo $arParsedPath["PREV"];?>">
<?else:?>
	<input type="hidden" name="new" value="Y">
	<input type="hidden" name="filename" value="<?echo htmlspecialchars($filename)?>">
	<input type="hidden" name="old_filename" value="<?echo htmlspecialchars($filename)?>">
	<input type="hidden" name="path" value="<?echo $path?>">
	<input type="hidden" name="old_path" value="<?echo $path?>">
<?endif?>
<?if(!$bCheckExecFile):?>
<input type="hidden" name="prolog" value="<?echo htmlspecialchars($prolog);?>">
<input type="hidden" name="epilog" value="<?echo htmlspecialchars($epilog);?>">
<?for($i=0; $i<count($arPHPScript); $i++):?>
<input type="hidden" name="{#PHPSCRIPT<?=$i?>#}" value="<?echo htmlspecialchars($arPHPScript[$i]);?>">
<?endfor?>
<input type="hidden" name="MAX_PHPSCRIPT" value="<?=count($arPHPScript)?>">
<?endif?>
</td></tr>
</form>
</table>
</body>
</html>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php")?>
