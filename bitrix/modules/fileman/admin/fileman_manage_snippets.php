<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");
if (!$USER->CanDoOperation('fileman_view_file_structure') && !$USER->CanDoOperation('edit_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

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

function _replacer($str)
{
	$str = preg_replace("/\.\.\//i", "", $str);
	return $str;
}

function _replacer2($str)
{
	$str = preg_replace("/[^a-zA-Z0-9_\.-\+]/is", "_", $str);
	return $str;
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


function handleTemplateSnippets($template, &$arSNIPPETS, &$arTemplateKeys)
{
	$arTemplateKeys[$template] = Array();
	__readDir($arSNIPPETS, $arTemplateKeys[$template], "", $template);
	__updateArray($arSNIPPETS,$arTemplateKeys[$template],$template);
	__rewriteContent($arSNIPPETS,$arTemplateKeys[$template],$template);
}


function __readDir(&$arSNIPPETS,&$arKeys,$path,$template,$level = 0,$parent = "")
{
	$basePath = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".$template."/snippets";
	if (!file_exists($basePath))
		return;
	$imagesPath = $basePath."/images";

	if(file_exists($imagesPath) && !file_exists($imagesPath."/.htaccess"))
		$GLOBALS['APPLICATION']->SaveFileContent($imagesPath."/.htaccess", "Allow from All");

	$bpPath = $basePath.($path == "" ? "" : "/").$path;
	$handle  = @opendir($bpPath);

	while(false !== ($file = @readdir($handle)))
	{
		if($file == "." || $file == ".." || $file == ".htaccess" || $file == ".content.php" || ($level == 0 && $file == "images"))
			continue;

		$fullPath = $bpPath."/".$file;
		if (is_dir($fullPath))
		{
			$new_path = "".$path.($path == "" ? "" : "/").$file;
			__readDir($arSNIPPETS,$arKeys,$new_path,$template,$level+1,$file);
		}
		elseif(is_file($fullPath))
		{
			$name = $file;
			$pos = strrpos($name,".");
			$f_name = ($pos !== FALSE) ? substr($name,0,$pos) : $name;
			$f_ext = ($pos !== FALSE) ? substr($name,$pos+1) : '';

			// Rename file *.* => *.snp
			if ($f_ext != 'snp')
			{
				$name = $f_name.".snp";

				if (!file_exists($bpPath."/".$name))
					rename($fullPath, $bpPath."/".$name);
				else
				{
					for ($n = 1; $n<256; $n++)
					{
						$test_f_name = $f_name."(".$n.")";
						$name = $test_f_name.".snp";
						if (!file_exists($bpPath."/".$name))
						{
							rename($fullPath, $bpPath."/".$name);
							break;
						}
					}
				}
				$f_ext = 'snp';
			}
			$imgPath = $imagesPath."/".$path;
			//Check thumbnail
			if(file_exists($imgPath."/".$f_name.".gif"))
				$thumb = $f_name.".gif";
			elseif(file_exists($imgPath."/".$f_name.".jpg"))
				$thumb = $f_name.".jpg";
			elseif(file_exists($imgPath."/".$f_name.".jpeg"))
				$thumb = $f_name.".jpeg";
			elseif(file_exists($imgPath."/".$f_name.".png"))
				$thumb = $f_name.".png";
			elseif(file_exists($imgPath."/".$f_name.".bmp"))
				$thumb = $f_name.".bmp";
			else
				$thumb = "";

			__push2Array($arSNIPPETS,$arKeys,$name,$path,$name,$thumb,_getSnippet($bpPath."/".$name),"",$template,$level,$parent);

		}
	}
}

function __push2Array(&$ar,&$arKeys,$name,$path,$title,$thumb,$code,$description,$template,$level,$parent)
{
	$key = $path.($path != '' ? '/' : '').$name;
	$ar[$key] = Array(
		'name' => $name,
		'path' => $path,
		'title' => $title,
		'thumb' => $thumb,
		'code' => $code,
		'description' => $description,
		'template' => $template,
		'level' => $level,
		'parent' => $parent
	);
	$arKeys[$key] = Array(
		'name' => $name,
		'path' => $path,
		'title' => $title,
		'description' => $description
	);
}

function __updateArray(&$ar,&$arKeys,$template)
{
	$path = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".$template."/snippets";

	if (file_exists($path."/.content.php"))
	{
		@include($path."/.content.php");
		$arK = array_keys($SNIPPETS);
		for ($i=0,$len = count($arK); $i<$len;$i++)
		{
			$name = $arK[$i];
			$pos = strrpos($name,".");
			$f_name = ($pos !== FALSE) ? substr($name,0,$pos) : $name;
			if ($ar[$f_name.".snp"])
			{
				$ar[$f_name.".snp"]['title'] = $SNIPPETS[$name]['title'];
				$ar[$f_name.".snp"]['description'] = $SNIPPETS[$name]['description'];
			}
		}
	}
}

function __rewriteContent(&$ar,&$arKeys,$template)
{
	$path = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".$template."/snippets";
	if (!file_exists($path))
		return;

	$APPLICATION = $GLOBALS['APPLICATION'];
	$content_src = '<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>'.chr(10);
	$content_src .= '<?'.chr(10).'$SNIPPETS = Array();'.chr(10);

	foreach ($ar as $k=>$_arSn)
	{
		if (!$arKeys[$k])
			continue;
		$content_src .= '$SNIPPETS[\''.CUtil::addslashes($k).'\'] = Array("title"=>\''.CUtil::addslashes($_arSn['title']).'\', "description"=>\''.CUtil::addslashes($_arSn['description']).'\');'.chr(10);
	}

	$content_src .= '?>';
	$APPLICATION->SaveFileContent($path."/.content.php", $content_src);
}


function __displayJSArray($ar)
{
	foreach ($ar as $key=>$_arSn)
	{
	?>
	window.arSnippets['<?=_addslashes($key);?>'] =
	{
		name: '<?=_addslashes($_arSn['name'])?>',
		path: '<?=_addslashes($_arSn['path'])?>',
		title: '<?=_addslashes($_arSn['title'])?>',
		thumb: '<?=_addslashes($_arSn['thumb'])?>',
		code: '<?=_addslashes($_arSn['code'])?>',
		template: '<?=_addslashes($_arSn['template'])?>',
		description: '<?=_addslashes($_arSn['description'])?>',
		level: '<?=_addslashes($_arSn['level'])?>',
		parent: '<?=_addslashes($_arSn['parent'])?>'
	};
	<?
	}
}


function __readDir2(&$arSnGroups,$path,$template,$level,$parent)
{
	$basePath = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".$template."/snippets";
	if (!file_exists($basePath))
		return;

	$bpPath = $basePath.($path == "" ? "" : "/").$path;
	$handle  = @opendir($bpPath);
	if (!$level)
		$level = 0;

	if (!$parent)
		$parent = "";

	while(false !== ($file = @readdir($handle)))
	{
		if($file == "." || $file == ".." || $file == ".htaccess" || $file == ".content.php" || ($level == 0 && $file == "images"))
			continue;

		$fullPath = $bpPath."/".$file;
		if (is_dir($fullPath))
		{
			$arSnGroups[] = Array
			(
				'path' => $path,
				'name' => $file,
				'level' => $level,
				'default_name' => _returnDefaultName($fullPath)
			);

			$new_path = "".$path.($path == "" ? "" : "/").$file;
			__readDir2($arSnGroups,$new_path,$template,$level+1,$parent);
		}
	}
}

function _returnDefaultName($path)
{
	for ($i=1; $i<=9999; $i++)
	{
		$name = 'snippet'.str_pad($i, 4, "0", STR_PAD_LEFT);
		if (!file_exists($path.'/'.$name.'.snp'))
			break;
	}
	return $name;
}

function __displayJSGroupsArray($template,$ar)
{
	?><script>
	window.arSnGroups['<?=$template?>'] = {};
	window.rootDefaultName['<?=$template?>'] = '<?=_returnDefaultName($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".$template."/snippets")?>';
	<?
	for($i=0,$len = count($ar); $i < $len; $i++)
	{
		$key = $ar[$i]['path'].($ar[$i]['path'] != '' ? '/' : '').$ar[$i]['name'];
	?>
window.arSnGroups['<?=_addslashes($template)?>']['<?=_addslashes($key);?>'] =
{
	name:'<?=_addslashes($ar[$i]['name'])?>',
	path:'<?=_addslashes($ar[$i]['path'])?>',
	level:'<?=_addslashes($ar[$i]['level'])?>',
	default_name:'<?=_addslashes($ar[$i]['default_name'])?>'
};
	<?
	}
	?></script><?
}


function _addslashes($str)
{
	$pos = strpos(strtolower($str), "script");
	if ($pos!==FALSE)
	{
		$str = str_replace("<script","&lt;script",$str);
		$str = str_replace("</script","&lt;/script",$str);
	}

	$pos2 = strpos(strtolower($str), "\n");
	if ($pos2!==FALSE)
	{
		$str = str_replace("\r","",$str);
		$str = str_replace("\n","\\n",$str);
	}
	return CUtil::addslashes($str);
}


function _load_snippets()
{
	?>
	<script>
	window.arSnippets = {};
	<?
	$template = _replacer($_GET["templateID"]);
	$arSNIPPETS = false;
	$CACHE_SNIPPETS = Array();
	
	global $CACHE_MANAGER;
	
	if (isset($_GET['clear_snippets_cache']) && $_GET['clear_snippets_cache'] == 'Y')
		_clear_cache();

	$ttl = 20*24*60*60;
	if($CACHE_MANAGER->Read($ttl, "fileman_snippet_array"))
	{
		$CACHE_SNIPPETS = $CACHE_MANAGER->Get("fileman_snippet_array");
		if (isset($CACHE_SNIPPETS[$template]))
			$arSNIPPETS = $CACHE_SNIPPETS[$template];
	}
	
	if (!$arSNIPPETS)
	{
		$arSNIPPETS = Array();
		$arTemplateKeys = Array(); //Array contain keys of snippets for each template for correct writing .content.php
		handleTemplateSnippets('.default', $arSNIPPETS, $arTemplateKeys);
		handleTemplateSnippets($template, $arSNIPPETS, $arTemplateKeys);
		$CACHE_SNIPPETS[$template] = $arSNIPPETS;
		$CACHE_MANAGER->Set("fileman_snippet_array", $CACHE_SNIPPETS);
	}
	
	__displayJSArray($arSNIPPETS);
	?>
	</script>
	<?
}

function _add_snippet()
{
	global $APPLICATION;
	_clear_cache();
	
	$template = _replacer($_GET['templateID']);
	$base_path = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".$template."/snippets";
	if (!file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".$template))
	{
		?><script>alert('Error: Incorrect template Id: <?=$template?>');</script><?
		return;
	}

	//Writing .htaccess for snippets folder
	if (!file_exists($base_path."/.htaccess"))
		$APPLICATION->SaveFileContent($base_path."/.htaccess", "Deny from All");

	$SNIPPETS = Array();
	@include($base_path."/.content.php");

	$filesrc_for_save = '<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>'.chr(10);
	$filesrc_for_save .= '<?'.chr(10).'$SNIPPETS = Array();'.chr(10);

	foreach($SNIPPETS as $file => $SN)
	{
		$filesrc_for_save .= '$SNIPPETS[\''.CUtil::addslashes($file).'\'] = Array("title"=>\''.CUtil::addslashes($SN['title']).'\', "description"=>\''.CUtil::addslashes($SN['description']).'\');'.chr(10);
	}

	$filename_ = _replacer($APPLICATION->UnJSEscape($_POST["filename"]));
	$filename = $filename_.'.snp';

	$location = _replacer($APPLICATION->UnJSEscape($_POST["location"]));
	$new_group = _replacer($APPLICATION->UnJSEscape($_POST["new_group"]));
	$add_path = '';
	if ($location != '')
		$add_path = CUtil::addslashes($location).'/';
	if($new_group)
		$add_path .= CUtil::addslashes($new_group).'/';

	$snippet_code = $APPLICATION->UnJSEscape($_POST['code']);

	if ($APPLICATION->SaveFileContent(_replacer($base_path."/".$add_path.CUtil::addslashes($filename)), $snippet_code))
	{
		$title = $APPLICATION->UnJSEscape($_POST['title']);
		$description = $APPLICATION->UnJSEscape($_POST['description']);
		$filesrc_for_save .= '$SNIPPETS[\''.$add_path.CUtil::addslashes($filename).'\'] = Array("title"=>\''.CUtil::addslashes($title).'\', "description"=>\''.CUtil::addslashes($description).'\');'.chr(10);

		// Copy Thumbnail
		$thumb = _replacer($APPLICATION->UnJSEscape($_POST['thumb']));
		if (substr($thumb,0,1)=='/')
			$thumb = substr($thumb,1);

		if ($thumb != '')
		{
			$path_from_1 = $_SERVER["DOCUMENT_ROOT"]."/".$thumb;
			$path_from = '/'.$thumb;
			if (file_exists($path_from_1))
			{
				$pos = strrpos($thumb,".");
				$f_ext = ($pos !== FALSE) ? substr($thumb,$pos+1) : '';
				$site = $_GET['site'];
				$path_to = BX_PERSONAL_ROOT."/templates/".$template."/snippets/images/".$add_path.$filename_.'.'.$f_ext;
				$strWarning_tmp = CFileMan::CopyEx(Array($site, $path_from), Array($site, $path_to));
			}
		}
	}
	$filesrc_for_save .= '?>';

	$APPLICATION->SaveFileContent($base_path."/.content.php", $filesrc_for_save);
}

function _edit_snippet()
{
	global $APPLICATION;
	_clear_cache();
	
	$template = _replacer($APPLICATION->UnJSEscape($_POST['template']));
	$name = _replacer($APPLICATION->UnJSEscape($_POST['name']));
	$path = _replacer($APPLICATION->UnJSEscape($_POST['path']));
	$site = $_GET['site'];
	$cont_path = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".$template."/snippets";
	$key = $path.($path != '' ? '/' : '').$name;

	// 1. Rewrite title & description in .content.php
	if (isset($_POST['title']) || isset($_POST['description']))
	{
		if (file_exists($cont_path."/.content.php"))
		{
			@include($cont_path."/.content.php");
			if (isset($_POST['title']))
				$SNIPPETS[$key]['title'] = $APPLICATION->UnJSEscape($_POST['title']);
			if (isset($_POST['description']))
				$SNIPPETS[$key]['description'] = $APPLICATION->UnJSEscape($_POST['description']);
		
			$content_src = '<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>'.chr(10);
			$content_src .= '<?'.chr(10).'$SNIPPETS = Array();'.chr(10);
			foreach ($SNIPPETS as $k=>$_arSn)
				$content_src .= '$SNIPPETS[\''.CUtil::addslashes($k).'\'] = Array("title"=>\''.CUtil::addslashes($_arSn['title']).'\', "description"=>\''.CUtil::addslashes($_arSn['description']).'\');'.chr(10);
			$content_src .= '?>';
			$APPLICATION->SaveFileContent($cont_path."/.content.php", $content_src);
		}
	}

	// 2. Save new snippet with new content
	if (isset($_POST['code']))
	{
		$code = $APPLICATION->UnJSEscape($_POST['code']);
		$full_path = $cont_path.'/'.$key;
		$APPLICATION->SaveFileContent($full_path, $code);
	}

	// 3. Handle thumbnail
	if (isset($_POST['thumb']))
	{
		$thumb = _replacer($APPLICATION->UnJSEscape($_POST['thumb']));
		if (substr($thumb,0,1)=='/')
			$thumb = substr($thumb,1);
		
		$pos = strrpos($name,".");
		$f_name = ($pos !== FALSE) ? substr($name,0,$pos) : $f_name;
				
		//delete existent thumbnail
		$img_path1 = BX_PERSONAL_ROOT.'/templates/'.$template.'/snippets/images/'.$path.($path == '' ?  '' : '/').$f_name;
		CFileman::DeleteFile(Array($site, $img_path1.".gif"));
		CFileman::DeleteFile(Array($site, $img_path1.".jpg"));
		CFileman::DeleteFile(Array($site, $img_path1.".jpeg"));
		CFileman::DeleteFile(Array($site, $img_path1.".png"));
		CFileman::DeleteFile(Array($site, $img_path1.".bmp"));

		// Copy Thumbnail
		if ($thumb != '')
		{
			$path_from_1 = $_SERVER["DOCUMENT_ROOT"]."/".$thumb;
			$path_from = '/'.$thumb;
			if (file_exists($path_from_1))
			{
				$pos = strrpos($thumb,".");
				$f_ext = ($pos !== FALSE) ? substr($thumb,$pos+1) : '';
				$path_to = $img_path1.'.'.$f_ext;
				$strWarning_tmp = CFileMan::CopyEx(Array($site, $path_from), Array($site, $path_to));
			}
		}
	}
}

function _delete_snippet()
{
	global $APPLICATION;
	_clear_cache();
	
	$template = _replacer($APPLICATION->UnJSEscape($_GET['templateID']));
	$base_path = BX_PERSONAL_ROOT."/templates/".$template."/snippets";
	$site = $_GET["site"];
	$name = _replacer($APPLICATION->UnJSEscape($_GET["name"]));
	$path = _replacer($APPLICATION->UnJSEscape($_GET["path"]));
	$thumb = _replacer($APPLICATION->UnJSEscape($_GET["thumb"]));

	//Delete snippet file
	$full_path = $base_path.'/'.$path.($path == '' ?  '' : '/').$name;
	CFileman::DeleteFile(Array($site, $full_path));

	//Delete thumbnail
	if ($thumb != '')
	{
		$img_path = $base_path.'/images/'.$path.($path == '' ?  '' : '/').$thumb;
		CFileman::DeleteFile(Array($site, $img_path));
	}
	?>
	<script>
	window.operation_success = true;
	</script>
	<?
}

function _check_snippet()
{

}

function _get_groups()
{
	$template = _replacer($_GET['templateID']);
	$arSnGroups = Array();
	__readDir2($arSnGroups,"",$template);
	__displayJSGroupsArray($template,$arSnGroups);
}

function _clear_cache()
{
	global $CACHE_MANAGER;
	$CACHE_MANAGER->Clean("fileman_snippet_array");
}


if (isset($_GET['target']))
{
	switch ($_GET['target'])
	{
		case "load":
			_load_snippets();
			break;
		case "add":
			if(check_bitrix_sessid())
				_add_snippet();
			break;
		case "edit":
			if(check_bitrix_sessid())
				_edit_snippet();
			break;
		case "delete":
			_delete_snippet();
			break;
		case "check":
			_check_snippet();
			break;
		case "getgroups":
			_get_groups();
			break;
	}
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_after.php");
?>