<?
define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

$MESS = array();
$path = str_replace(array("\\", "//"), "/", dirname(__FILE__)."/lang/".LANGUAGE_ID."/show_file.php");
include_once($path);
$MESS1 =& $MESS;
$GLOBALS["MESS"] = $MESS1 + $GLOBALS["MESS"];

CModule::IncludeModule("forum");
// ************************* Input params***************************************************************
// ************************* BASE **********************************************************************
$arParams["FILE_ID"] = intVal($_REQUEST["fid"]);
$arParams["ACTION"] = ($_REQUEST["action"] == "download" ? "download" : "view");
// *************************/Input params***************************************************************
// ************************* Default params*************************************************************
$arResult["MESSAGE"] = array();
$arResult["FILE"] = array();
$arResult["PERMISSION"] = "A";
$arError = array();
if (intVal($arParams["FILE_ID"]) > 0)
	$arResult["FILE"] = CFile::GetFileArray($arParams["FILE_ID"]);

if (empty($arResult["FILE"]))
{
	$arError = array(
		"code" => "EMPTY FILE",
		"title" => GetMessage("F_EMPTY_FID"));
}
else
{
	$db_res = CForumMessage::GetList(array(), array("ATTACH_IMG" => $arParams["FILE_ID"]));
	if ($db_res && $res = $db_res->Fetch())
		$arResult["MESSAGE"] = $res;
	if (empty($arResult["MESSAGE"]))
	{
		$arError = array(
			"code" => "EMPTY MESSAGE",
			"title" => GetMessage("F_EMPTY_MID"));
	}
	elseif (ForumCurrUserPermissions($arResult["MESSAGE"]["FORUM_ID"]) < "E")
	{
		$arError = array(
			"code" => "NOT RIGHT",
			"title" => GetMessage("F_NOT_RIGHT"));
	}
}

if (!empty($arError))
{
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_after.php");
	echo ShowError((!empty($arError["title"]) ? $arError["title"] : $arError["code"]));
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog.php");
	die();
}
// *************************/Default params*************************************************************

// ************************* Functions *****************************************************************
function sup_view_file($arFile, $content_type=false, $specialchars=true)
{
	$filename = $_SERVER["DOCUMENT_ROOT"].$arFile["SRC"];
	$real_file_name = (empty($arFile["ORIGINAL_NAME"]) ? $arFile["FILE_NAME"] : $arFile["ORIGINAL_NAME"]);
	if ($f = fopen($filename, "rb"))
	{
		if ($content_type===false) $content_type = "text/html; charset=".LANG_CHARSET;
		header("Content-type: ".$content_type);
		header("Content-Disposition: filename=\"".$real_file_name."\"");
		header("Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0"); 
		header("Expires: 0"); 
		header("Pragma: public"); 
		if ($specialchars)
		{
			echo "<pre>";
			while ($buffer = fread($f, 4096)) echo htmlspecialchars($buffer);
			echo "</pre>";
		}
		else
		{
			while ($buffer = fread($f, 4096)) echo $buffer;
		}
		fclose ($f);
	}
}

function sup_download($arFile)
{
	$filename = $_SERVER["DOCUMENT_ROOT"].$arFile["SRC"];
	$filesize = filesize($filename);
	
	$name = (strlen($arFile["ORIGINAL_NAME"])>0 ? $arFile["ORIGINAL_NAME"] : $arFile["FILE_NAME"]);
	if (strlen($arFile["EXTENSION_SUFFIX"])>0) :
		$suffix_length = strlen($arFile["EXTENSION_SUFFIX"]);
		$name = substr($name, 0, strlen($name)-$suffix_length);
	endif;

	$sapi = (stristr(php_sapi_name(), "cgi") !== false? "cgi":"");
	$f = fopen($filename, "rb");
	$cur_pos = 0;
	$size = $filesize-1;

	$p = strpos($_SERVER["HTTP_RANGE"], "=");
	if(intval($p)>0)
	{
		$bytes = substr($_SERVER["HTTP_RANGE"], $p+1);
		$p = strpos($bytes, "-");
		if($p!==false)
		{
			$cur_pos = IntVal(substr($bytes, 0, $p));
			$size = IntVal(substr($bytes, $p+1));
			if ($size<=0) $size = $filesize - 1;
			if ($cur_pos>$size)
			{
				$cur_pos = 0;
				$size = $filesize - 1;
			}
			fseek($f, $cur_pos);
		}
	}

	if(intval($cur_pos)>0 && $_SERVER["SERVER_PROTOCOL"] == "HTTP/1.1")
	{
		if($sapi=="cgi")
			header("Status: 206 Partial Content"); 
		else
			header("HTTP/1.1 206 Partial Content");
	}
	else
	{
		session_cache_limiter('');
		session_start();
		ob_end_clean();
		session_write_close();
		if($sapi=="cgi") 
			header("Status: 200 OK"); 
		else 
			header($_SERVER["SERVER_PROTOCOL"]." 200 OK");
	}

	header("Content-Type: application/force-download; name=\"".$name."\"");
	header("Content-Disposition: attachment; filename=\"".$name."\"");
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".($size-$cur_pos+1));
	header("Accept-Ranges: bytes");
	header("Content-Range: bytes ".$cur_pos."-".$size."/".$filesize);
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
	header("Expires: 0"); 
	header("Pragma: public"); 

	$str = "";
	while($cur_pos<=$size)
	{
		$bufsize = 32768;
		if($bufsize+$cur_pos>$size)
			$bufsize = $size - $cur_pos + 1;
		$cur_pos += $bufsize;
		$p = fread($f, $bufsize);
		echo $p;
		flush();
	}
	fclose ($f);
	die();
}
// *************************/Functions *****************************************************************

set_time_limit(0);
if ($arParams["ACTION"] == "download")
{
	sup_download($arResult["FILE"]);
}
else
{
	$filename = $_SERVER["DOCUMENT_ROOT"].$arResult["FILE"]["SRC"];
	if (strpos($arResult["FILE"]["CONTENT_TYPE"], "image/")!==false && strpos($arFile["CONTENT_TYPE"], "html")===false && GetImageSize($filename))
	{
		sup_view_file($arResult["FILE"], $arResult["FILE"]["CONTENT_TYPE"], false);
	}
	else
	{
		$ct = strtolower($arResult["FILE"]["CONTENT_TYPE"]);
		if (strpos($ct, "excel") !== false)
		{
			sup_view_file($arResult["FILE"], "application/vnd.ms-excel", false);
		}
		elseif (strpos($ct, "word") !== false)
			sup_view_file($arResult["FILE"], "application/msword", false);
		elseif (strpos($ct, "flash") !== false)
			sup_view_file($arResult["FILE"], "application/x-shockwave-flash", false);
		else 
		{
			switch($ct)
			{
				case "text/xml":
				case "application/pdf":
					sup_view_file($arResult["FILE"], $ct, false);
					break;
				case "pdf":
					sup_view_file($arResult["FILE"], "application/pdf", false);
					break;							
				default:
					sup_view_file($arResult["FILE"]);
					//sup_download($arResult["FILE"]);
			}
		}
	}
	die();
}
// *****************************************************************************************
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_after.php");
echo ShowError(GetMessage("F_ATTACH_NOT_FOUND"));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog.php");
// *****************************************************************************************
?>