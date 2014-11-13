<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");
$FM_RIGHT = $APPLICATION->GetGroupRight("fileman");
if($FM_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");

if(CModule::IncludeModule("compression"))
	CCompress::Disable2048Spaces();

function _getSnippet($snippet_path)
{
	$APPLICATION = $GLOBALS['APPLICATION'];
	if (file_exists($snippet_path))
		$code = $APPLICATION->GetFileContent($snippet_path);
	else
		$code = '';
	
	return $code;
}

function _addUnDefinedSnippets($arSNIPPETS,$arKeys,$path,$subdir='')
{
	$handle  = @opendir($path);
	while(false !== ($file = @readdir($handle)))
	{
		if($file == "." || $file == ".." || $file == ".htaccess")
			continue;
			
		if (is_file($path."/".$file) && $file!='.content.php' && !in_array($file,$arKeys))
			_setArray(&$arSNIPPETS,(($subdir=='') ? $file : $subdir.'/'.$file),$file,_getSnippet($path.'/'.$file),"");
	}
}

function _getDirs($path)
{
	$handle  = @opendir($path);
	$arSubDirs = array();
	while(false !== ($file = @readdir($handle)))	
		if($file != "." && $file != ".." && is_dir($path."/".$file))
			$arSubDirs[] = $file;
	
	return $arSubDirs;
}

function _handleTemplateSnippets($template, $arSNIPPETS)
{
	$path = $_SERVER["DOCUMENT_ROOT"]."/bitrix/templates/".$template."/snippets";
	
	$arKeys = Array();	
	if (file_exists($path."/.content.php"))
	{
		@include($path."/.content.php");		
		setArray_rewriteContent(&$SNIPPETS,&$arSNIPPETS,$path);
		$arKeys = array_keys($SNIPPETS);
	}

	_addUnDefinedSnippets(&$arSNIPPETS,$arKeys,$path);
	/*$arSubDirs = _getDirs($path);
	foreach ($arSubDirs as $_subdir)
	{
		$s_path = $path."/".$_subdir;
		$arKeys = Array();
		if (file_exists($s_path."/.content.php"))
		{		
			@include($s_path."/.content.php");
			//setArray_rewriteContent(&$SNIPPETS,&$arSNIPPETS,$s_path);
			$arKeys = array_keys($SNIPPETS);
		}
		_addUnDefinedSnippets(&$arSNIPPETS,$arKeys,$s_path,$_subdir);	
	}*/
}

function setArray_rewriteContent($SNIPPETS,$arSNIPPETS,$path)
{
	$APPLICATION = $GLOBALS['APPLICATION'];
	$content_src = '<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>'.chr(10);
	$content_src .= '<?'.chr(10).'$SNIPPETS = Array();'.chr(10);
	
	foreach ($SNIPPETS as $k=>$_arSn)
	{
		//echo "\n >>#################################################\n";
		
		if (!file_exists($path.'/'.$k))
		{
			echo "\n//----------------------------------------------------\n";
			echo "//".$path.'/'.$k;
			unset($SNIPPETS[$k]);
			continue;
		}
		$content_src .= '$SNIPPETS["'.$k.'"] = Array("title"=>"'.addslashes($_arSn['title']).'", "description"=>"'.addslashes($_arSn['title']).'");'.chr(10);
		_setArray(&$arSNIPPETS,$k,$_arSn['title'],_getSnippet($path.'/'.$k),(($_arSn['description']) ? $_arSn['description'] : ''));
		//echo "\n >>#################################################\n";
	}
	$content_src .= '?>';
	//echo $content_src;
	$APPLICATION->SaveFileContent($path."/.content.php", $content_src);
}

function _setArray($ar,$key,$title,$code,$description)
{
	$ar[$key] = Array(
		'title' => $title,
		'code' => $code,
		'description' => $description
	);
	if (isset($_GET['target']) && $_GET['target']=='load'):
	?>

window.arSnippets['<?echo _addslashes($key);?>'] = {
'title':'<?echo _addslashes($title);?>',
'code':'<?echo _addslashes($code);?>',
'description':'<?echo _addslashes($description);?>'
};
	<?
	endif;
}

function _addslashes($str)
{
	//$pos = stripos($str, "script");
	$pos = strpos(strtolower($str), "script");
	if ($pos!==FALSE)
		$str = str_replace("script","__s__c__r__i__p__t__",$str);
		
	$pos2 = strpos(strtolower($str), "\n");
	if ($pos2!==FALSE)
	{
		$str = str_replace("\r","",$str);
		$str = str_replace("\n","\\n",$str);
	}

	
	return addslashes($str);
}



if (isset($_GET['target']) && $_GET['target']=='load')
{
	//echo '>>#################################################';
	?>
	<script>
	window.arSnippets = {};
	<?
	$arSNIPPETS = Array();
	_handleTemplateSnippets('.default', &$arSNIPPETS);
	_handleTemplateSnippets($_GET["templateID"], &$arSNIPPETS);
	?>
	</script>
	<?
	//echo '>>#################################################';
}
elseif (isset($_GET['target']) && $_GET['target']=='add')
{
	$template = $_GET['templateID'];
	$path = $_SERVER["DOCUMENT_ROOT"]."/bitrix/templates/".$template."/snippets";
	//echo $path;
	
	//echo "\n =================================================================== \n";
	//print_r($_POST);
	//echo "\n =================================================================== \n";
	
	if (!file_exists($path."/.htaccess"))
		$APPLICATION->SaveFileContent($path."/.htaccess", "Deny from All");

	$SNIPPETS = Array();
	@include($path."/.content.php");	
	
	$filesrc_for_save = '<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>'.chr(10);
	$filesrc_for_save .= '<?'.chr(10).'$SNIPPETS = Array();'.chr(10);
	
	foreach($SNIPPETS as $file => $SN)
	{
		$filesrc_for_save .= '$SNIPPETS["'.addslashes($file).'"] = Array("title"=>"'.addslashes($SN['title']).'", "description"=>"'.addslashes($SN['description']).'");'.chr(10);
	}
	
	$filename = $APPLICATION->UnJSEscape($_POST["filename"]);
	//echo "//".$filename;
	$snippet_code = $APPLICATION->UnJSEscape($_POST['code']);
	if ($APPLICATION->SaveFileContent($path."/".addslashes($filename), $snippet_code))
	{
		$title = $APPLICATION->UnJSEscape($_POST['title']);
		$description = $APPLICATION->UnJSEscape($_POST['description']);
		$filesrc_for_save .= '$SNIPPETS["'.addslashes($filename).'"] = Array("title"=>"'.addslashes($title).'", "description"=>"'.addslashes($description).'");'.chr(10);
	}
	$filesrc_for_save .= '?>';
	
	$APPLICATION->SaveFileContent($path."/.content.php", $filesrc_for_save);
}
elseif (isset($_GET['target']) && $_GET['target']=='check')
{
	$template = $_GET['templateID'];
	$filename = $APPLICATION->UnJSEscape($_GET['filename']);
	$path = $_SERVER["DOCUMENT_ROOT"]."/bitrix/templates/".$template."/snippets/".$filename;
	
	if (file_exists($path)):
		?><script>window.snippet_file_exists = true;</script><?
	else:
		?><script>window.snippet_file_exists = false;</script><?
	endif;
}
else
{
	echo '2: #################################################';
}


//_handleTemplateSnippets('.default', &$arSNIPPETS);
//_handleTemplateSnippets($_GET["templateID"], &$arSNIPPETS);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_after.php");
?>