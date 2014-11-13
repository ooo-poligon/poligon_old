<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

//т.к. у Формы action не меняется без клиентских скриптов, поэтому просто будем включать файл для групового изменения прав
error_reporting(E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR|E_PARSE);

if($_SERVER["REQUEST_METHOD"]=="POST" && strlen($_POST["perms"])>0 && is_array($_POST["files"]) && count($_POST["files"])>0)
{
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/fileman_access.php");
	die();
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if (!$USER->CanDoOperation('fileman_view_file_structure'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");

// идентификатор таблицы
$sTableID = "tbl_fileman_admin";

// инициализация сортировки
$oSort = new CAdminSorting($sTableID, "ID", "asc");
// инициализация списка
$lAdmin = new CAdminList($sTableID, $oSort);

// инициализация параметров списка - фильтры
$arFilterFields = Array(
	"find_name",
	"find_timestamp_1",
	"find_timestamp_2",
	"find_type"
	);

$lAdmin->InitFilter($arFilterFields);

function CheckFilter() // проверка введенных полей
{
	if (isset($_REQUEST['del_filter']) && $_REQUEST['del_filter']=='Y')
		return false;

	global $strError, $find_timestamp_1, $find_timestamp_2;
	$str = "";

	if (strlen(trim($find_timestamp_1))>0 || strlen(trim($find_timestamp_2))>0)
	{
		$date_1_ok = false;
		$date1_stm = MkDateTime(FmtDate($find_timestamp_1,"D.M.Y"),"d.m.Y");
		$date2_stm = MkDateTime(FmtDate($find_timestamp_2,"D.M.Y")." 23:59","d.m.Y H:i");
		if (!$date1_stm && strlen(trim($find_timestamp_1))>0)
			$str.= GetMessage("MAIN_WRONG_DATE_FROM")."<br>";
		else $date_1_ok = true;
		if (!$date2_stm && strlen(trim($find_timestamp_2))>0)
			$str.= GetMessage("MAIN_WRONG_DATE_TILL")."<br>";
		elseif ($date_1_ok && $date2_stm <= $date1_stm && strlen($date2_stm)>0)
			$str.= GetMessage("MAIN_FROM_TILL_DATE")."<br>";
	}
	$strError .= $str;
	if(strlen($str)>0)
	{
		global $lAdmin;
		$lAdmin->AddFilterError($str);
		return false;
	}
	return true;
}

if(CheckFilter($arFilterFields))
	$arFilter = Array(
		"NAME" => ($find!='' && $find_type == "name"? $find : $find_name),
		//"SIZE" => $find_size,
		"TIMESTAMP_1"	=> $find_timestamp_1,
		"TIMESTAMP_2"	=> $find_timestamp_2,
		"TYPE" => $find_type
	);
else
	$arFilter = Array();

$site = CFileMan::__CheckSite($site);
$documentRoot = CSite::GetSiteDocRoot($site);

$arSite = CSite::GetById($site);
$arSite = $arSite->Fetch();

while (($l = strlen($path)) > 0 && $path[$l-1] == "/")
	$path = substr($path, 0, $l-1);

$addUrl = 'lang='.LANGUAGE_ID.($logical == "Y" ? '&logical=Y' : '');

$path = Rel2Abs("/", $path);

$arParsedPath = CFileMan::ParsePath(Array($site, $path), true, false, "", $logical == "Y");
$absPath = $documentRoot.$path;
$arPath = Array($site, $path);
$arFilter["MIN_PERMISSION"] = "R";
$handle_action = true;

// обработка редактирования (права доступа!)
if ($lAdmin->EditAction() && ($USER->CanDoOperation('fileman_admin_files') || $USER->CanDoOperation('fileman_admin_folders')))
{
	foreach ($FIELDS as $ID => $arFields)
	{
		if (!$lAdmin->IsUpdated($ID))
			continue;
		$arPath1 = Array($site, $path."/".$ID);

		if (!($USER->CanDoFileOperation('fm_rename_file', $arPath1) || $USER->CanDoFileOperation('fm_rename_file', $arPath1)))
		{
			$lAdmin->AddGroupError(GetMessage("FILEMAN_RENAME_ACCESS_DENIED")." \"".$ID."\"", $ID);
			continue;
		}

		if (strlen($arFields["NAME"]) <= 0)
		{
			$lAdmin->AddGroupError(GetMessage("FILEMAN_RENAME_NEW_NAME")." \"".$ID."\"", $ID);
		}
		else
		{
			$pathto = Rel2Abs($path, $arFields["NAME"]);
			$arPath_i = Array($site, $path."/".$ID);

			if (!($USER->CanDoFileOperation('fm_rename_file', $arPath_i) || $USER->CanDoFileOperation('fm_rename_file', $arPath_i)))
				$lAdmin->AddGroupError(GetMessage("FILEMAN_RENAME_ACCESS_ERROR"), $ID);
			elseif (!$USER->CanDoOperation('edit_php') &&
			(substr(CFileman::GetFileName($ID), 0, 1)=="." ||
			substr(CFileman::GetFileName($pathto), 0, 1)=="." ||
			(!in_array(CFileman::GetFileExtension($ID), CFileMan::GetScriptFileExt()) &&
			in_array(CFileman::GetFileExtension($pathto), CFileMan::GetScriptFileExt()))))
				$lAdmin->AddGroupError(GetMessage("FILEMAN_RENAME_TOPHPFILE_ERROR"), $ID);
			elseif (!$USER->CanDoOperation('edit_php') && (in_array(CFileman::GetFileExtension($ID), CFileMan::GetScriptFileExt())) && (!in_array(CFileman::GetFileExtension($pathto), CFileMan::GetScriptFileExt())))
				$lAdmin->AddGroupError(GetMessage("FILEMAN_RENAME_FROMPHPFILE_ERROR"), $ID);
			else
			{
				$pathParsed_tmp = CFileMan::ParsePath(Array($site, $pathto));
				$strWarningTmp = CFileMan::CreateDir($pathParsed_tmp["PREV"]);

				if (strlen($strWarningTmp) > 0)
				{
					$lAdmin->AddGroupError($strWarningTmp, $ID);
				}
				else
				{
					if (($mess = CFileMan::CheckFileName(str_replace('/', '', $pathto))) !== true)
					{
						$lAdmin->AddGroupError($mess, $ID);
					}
					elseif (!file_exists($documentRoot.$path."/".$ID))
					{
						$lAdmin->AddGroupError(GetMessage("FILEMAN_RENAME_FILE")." \"".$path."/".$ID."\" ".GetMessage("FILEMAN_RENAME_NOT_FOUND"), $ID);
					}
					elseif (file_exists($documentRoot.$pathto))
					{
						$lAdmin->AddGroupError(GetMessage("FILEMAN_RENAME_ALREADY_EXIST", Array("#FILE_NAME#" => $pathto)), $ID);
					}
					elseif(!@rename($documentRoot.$path."/".$ID, $documentRoot.$pathto))
					{
						$lAdmin->AddGroupError(GetMessage("FILEMAN_RENAME_ERROR")." \"".$path."/".$ID."\" ".GetMessage("FILEMAN_RENAME_IN")." \"".$pathto."\"", $ID);
					}
					else
					{
						$APPLICATION->CopyFileAccessPermission(Array($site, $path."/".$ID), Array($site, $pathto));
						$APPLICATION->RemoveFileAccessPermission(Array($site, $path."/".$ID));
					}
				}
			}
		}
	}
	$handle_action = false;
}

// обработка действий групповых и одиночных
if (($arID = $lAdmin->GroupAction()) && ($USER->CanDoOperation('fileman_admin_files') || $USER->CanDoOperation('fileman_admin_folders')) && $handle_action)
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = array();

		if (!CSite::IsDistinctDocRoots() || strlen($site) > 0 || strlen($path) > 0)
		{
			$DOC_ROOT = CSite::GetSiteDocRoot($site);

			while (($l=strlen($path)) > 0 && $path[$l-1] == "/")
				$path = substr($path, 0, $l-1);

			$path = Rel2Abs("/", $path);
			$arParsedPath = CFileMan::ParsePath(Array($site, $path));

			$abs_path = $DOC_ROOT.$path;

			CFileMan::GetDirList(Array($site, $path), $arDirs, $arFiles, $arFilter, Array($by => $order), "DF",false,true);

			foreach ($arDirs as $Dir)
					$arID[] = $Dir["NAME"];

			foreach ($arFiles as $File)
				$arID[] = $File["NAME"];
		}
	}

	foreach ($arID as $ID)
	{
		if (strlen($ID) <= 0 || $ID == '.')
			continue;

		$arPath_i = Array($site, $path."/".$ID);
		switch ($_REQUEST['action'])
		{
			case "delete":
				if (!($USER->CanDoFileOperation('fm_delete_file',$arPath_i) || $USER->CanDoFileOperation('fm_delete_folder',$arPath_i)))
					break;
				@set_time_limit(0);
				$strWarning_tmp = CFileMan::DeleteEx(Array($site, CFileMan::NormalizePath($path."/".$ID)));

				if(strlen($strWarning_tmp) > 0)
					$lAdmin->AddGroupError($strWarning_tmp, $ID);
				break;
			case "copy":
			case "move":
				if (!($USER->CanDoFileOperation('fm_create_new_file',$arPath_i) ||
				$USER->CanDoFileOperation('fm_create_new_folder',$arPath_i)) ||
				(!($USER->CanDoFileOperation('fm_delete_file',$arPath_i) ||
				$USER->CanDoFileOperation('fm_delete_folder',$arPath_i)) &&
				$_REQUEST['action'] == 'move'))
					break;
				if (!CSite::IsDistinctDocRoots())
					$copy_to_site = $site;
				else
					$copy_to_site = CFileMan::__CheckSite($copy_to_site);

				if (($mess = CFileMan::CheckFileName(str_replace('/', '', $copy_to))) !== true)
					$lAdmin->AddGroupError($mess, $ID);
				else
					$strWarning_tmp = CFileMan::CopyEx(Array($site, CFileMan::NormalizePath($path."/".urlencode($ID))), Array($copy_to_site, CFileMan::NormalizePath($copy_to."/".$ID)), ($_REQUEST['action'] == "move" ? true : false));

				if (strlen($strWarning_tmp) > 0)
					$lAdmin->AddGroupError($strWarning_tmp, $ID);

				break;
		}
	}
}

InitSorting();

$arDirs = array();
$arFiles = array();

$title = GetMessage("FILEMAN_TITLE");

if($USER->CanDoFileOperation('fm_view_listing',$arPath))
{
	CFileMan::GetDirList(Array($site, $path), $arDirs, $arFiles, $arFilter, Array($by => $order), "DF", $logical=='Y',true);

	if(strlen($path)>0)
	{
		$dname = $path;
		if($logical=="Y")
		{
			if(file_exists($absPath."/.section.php"))
			{
				@include($absPath."/.section.php");
				if(strlen($sSectionName)<=0)
					$sSectionName = GetMessage("FILEMAN_ADM_UNTITLED");
				$dname = $sSectionName;
			}
		}

		$lAdmin->onLoadScript = "jsUtils.SetPageTitle('".$title.": ".AddSlashes($dname)."');";
		$title = $title.": ".$dname;
	}
	else
	{
		$lAdmin->onLoadScript = "jsUtils.SetPageTitle('".addslashes($title)."');";
	}
}

$arDirContent_t = array_merge($arDirs, $arFiles);
$arDirContent = Array();

for($i=0,$l = count($arDirContent_t);$i<$l;$i++)
{
	$Elem = $arDirContent_t[$i];
	$arPath = Array($site, $Elem['ABS_PATH']);
	if(($Elem["TYPE"]=="F" && !$USER->CanDoFileOperation('fm_view_file',$arPath)) ||
	($Elem["TYPE"]=="D" && !$USER->CanDoFileOperation('fm_view_listing',$arPath)) ||
	($Elem["TYPE"]=="F" && $Elem["NAME"]==".section.php"))
		continue;
	$arDirContent[] = $Elem;
}
unset($arDirContent_t);

$db_DirContent = new CDBResult;
$db_DirContent->InitFromArray($arDirContent);
$db_DirContent->sSessInitAdd = $path;
$db_DirContent = new CAdminResult($db_DirContent, $sTableID);
//$db_DirContent->bPostNavigation = true;
$db_DirContent->NavStart(20);

// установке параметров списка
$lAdmin->NavText($db_DirContent->GetNavPrint(GetMessage("PAGES")));

// заголовок списка
if($logical=='Y')
{
	$lAdmin->AddHeaders(array(
		array("id"=>"LOGIC_NAME", "content"=>GetMessage("FILEMAN_FILE_NAME"), "default"=>true),
		array("id"=>"NAME", "content"=>GetMessage("FILEMAN_REAL_FILE_NAME"), "sort"=>"name"),
		array("id"=>"SIZE","content"=>GetMessage("FILEMAN_ADMIN_FILE_SIZE"), "sort"=>"size", "default"=>true),
		array("id"=>"DATE", "content"=>GetMessage('FILEMAN_ADMIN_FILE_TIMESTAMP'), "sort"=>"timestamp", "default"=>true),
		array("id"=>"TYPE", "content"=>GetMessage('FILEMAN_ADMIN_FILE_TYPE'), "sort"=>"", "default"=>true),
		array("id"=>"PERMS", "content"=>GetMessage('FILEMAN_ADMIN_ACCESS_PERMS'), "sort"=>"", "default"=>true),
		array("id"=>"PERMS_B", "content"=>GetMessage('FILEMAN_ADMIN_ACCESS_PERMS_B'), "sort"=>"", "default"=>true),
	));
}
else
{
	$lAdmin->AddHeaders(array(
		array("id"=>"NAME", "content"=>GetMessage("FILEMAN_FILE_NAME"), "sort"=>"name", "default"=>true),
		array("id"=>"SIZE","content"=>GetMessage("FILEMAN_ADMIN_FILE_SIZE"), "sort"=>"size", "default"=>true),
		array("id"=>"DATE", "content"=>GetMessage('FILEMAN_ADMIN_FILE_TIMESTAMP'), "sort"=>"timestamp", "default"=>true),
		array("id"=>"TYPE", "content"=>GetMessage('FILEMAN_ADMIN_FILE_TYPE'), "sort"=>"", "default"=>true),
		array("id"=>"PERMS", "content"=>GetMessage('FILEMAN_ADMIN_ACCESS_PERMS'), "sort"=>"", "default"=>true),
		array("id"=>"PERMS_B", "content"=>GetMessage('FILEMAN_ADMIN_ACCESS_PERMS_B'), "sort"=>"", "default"=>true),
	));
}
if(IntVal($show_perms_for) > 0)
	$lAdmin->AddVisibleHeaderColumn("PERMS_B");

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

function GetUnixFilePermissions($file)
{
	$perms = @fileperms($file);

	if (($perms & 0xC000) == 0xC000)
		$info = 's';	// Socket
	elseif (($perms & 0xA000) == 0xA000)
		$info = 'l';	// Symbolic Link
	elseif (($perms & 0x8000) == 0x8000)
		$info = '-'; // Regular
	elseif (($perms & 0x6000) == 0x6000)
		$info = 'b'; // Block special
	elseif (($perms & 0x4000) == 0x4000)
		$info = 'd'; // Directory
	elseif (($perms & 0x2000) == 0x2000)
		$info = 'c';	// Character special
	elseif (($perms & 0x1000) == 0x1000)
		$info = 'p';	// FIFO pipe
	else
		$info = 'u';	// Unknown

	// Owner
	$info .= (($perms & 0x0100) ? 'r' : '-');
	$info .= (($perms & 0x0080) ? 'w' : '-');
	$info .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-'));

	// Group
	$info .= (($perms & 0x0020) ? 'r' : '-');
	$info .= (($perms & 0x0010) ? 'w' : '-');
	$info .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-'));

	// World
	$info .= (($perms & 0x0004) ? 'r' : '-');
	$info .= (($perms & 0x0002) ? 'w' : '-');
	$info .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-'));

	return '<span title="'.sprintf("%o", $perms&0xfff).'">'.$info.'</span>';
}

if(strlen($path) > 0 && ($logical!="Y" || rtrim($arSite["DIR"], "/") != rtrim($arParsedPath["FULL"], "/")))
{
	$row =& $lAdmin->AddRow(".", array("NAME" => GetMessage("FILEMAN_UP")));

	if($logical == "Y")
		$showField = "<a href=\"javascript:".$sTableID.".GetAdminList('fileman_admin.php?".$addUrl."&site=".$site."&path=".urlencode($arParsedPath["PREV"])."&show_perms_for=".IntVal($show_perms_for)."');\"><IMG SRC=\"/bitrix/images/fileman/types/folder_up.gif\" WIDTH=\"16\" HEIGHT=\"16\" BORDER=0 alt=\"".GetMessage("FILEMAN_UP")."\"></a>&nbsp;<a href=\"javascript:".$sTableID.".GetAdminList('fileman_admin.php?".$addUrl."&site=".$site."&path=".urlencode($arParsedPath["PREV"])."&show_perms_for=".IntVal($show_perms_for)."');\">..</a>";
	else
		$showField = "<a href=\"javascript:".$sTableID.".GetAdminList('fileman_admin.php?".$addUrl."&site=".$site."&path=".urlencode($arParsedPath["PREV"])."&show_perms_for=".IntVal($show_perms_for)."');\"><IMG SRC=\"/bitrix/images/fileman/types/folder_up.gif\" WIDTH=\"16\" HEIGHT=\"16\" BORDER=0 alt=\"".GetMessage("FILEMAN_UP")."\"></a>&nbsp;<a href=\"javascript:".$sTableID.".GetAdminList('fileman_admin.php?".$addUrl."&site=".$site."&path=".urlencode($arParsedPath["PREV"])."&show_perms_for=".IntVal($show_perms_for)."');\">..</a>";

	$row->AddField("NAME", $showField);
	$row->AddField("LOGIC_NAME", $showField);
	$row->AddField("SIZE", "");
	$row->AddField("DATE", "");
	$row->AddField("TYPE", "");
	$row->AddField("PERMS", "");
	$row->AddField("PERMS_B", "");

	$arActions = Array();

	$arActions[] = array(
		"ICON" => "",
		"TEXT" => GetMessage('FILEMAN_N_OPEN'),
		"DEFAULT" => true,
		"ACTION" => "javascript:".$sTableID.".GetAdminList('fileman_admin.php?".$addUrl."&site=".$site."&path=".urlencode($arParsedPath["PREV"])."&show_perms_for=".IntVal($show_perms_for)."');"
	);

	$row->AddActions($arActions);
}

// построение списка
while($Elem = $db_DirContent->NavNext(true, "f_"))
{
	$arPath = Array($site, $Elem['ABS_PATH']);
	$fname = $documentRoot.$path."/".$Elem["NAME"];

	$showFieldIcon = "";
	$showFieldText = "";
	if($Elem["TYPE"] == "D")
	{
		$showFieldIcon = "<a href=\"fileman_admin.php?".$addUrl."&site=".urlencode($site)."&path=".urlencode($path."/".$Elem["NAME"])."&show_perms_for=".IntVal($show_perms_for)."\" onclick=\"".$sTableID.".GetAdminList('fileman_admin.php?".$addUrl."&site=".urlencode($site)."&path=".urlencode($path."/".$Elem["NAME"])."&show_perms_for=".IntVal($show_perms_for)."');return false;\"><IMG SRC=\"/bitrix/images/fileman/types/folder.gif\" WIDTH=\"16\" HEIGHT=\"16\" BORDER=0 ALT=\"\"></a>";
		$showFieldText = "<a href=\"fileman_admin.php?".$addUrl."&site=".urlencode($site)."&path=".urlencode($path."/".$Elem["NAME"])."&show_perms_for=".IntVal($show_perms_for)."\" onclick=\"".$sTableID.".GetAdminList('fileman_admin.php?".$addUrl."&site=".urlencode($site)."&path=".urlencode($path."/".$Elem["NAME"])."&show_perms_for=".IntVal($show_perms_for)."');return false;\">".$f_NAME."</a>";
	}
	else
	{
		$curFileType = CFileMan::GetFileTypeEx($Elem["NAME"]);
		if(preg_match('/^\.(.*)?\.menu\.(php|html|php3|php4|php5|phtml)$/', $Elem['NAME'], $regs))
		{
			$showFieldIcon = "";
			$showFieldText = GetMessage("FILEMAN_ADMIN_MENU_TYPE")."&laquo;".htmlspecialchars($regs[1])."&raquo;";
		}
		else
		{
			$showFieldIcon = "<IMG SRC=\"/bitrix/images/fileman/types/".$curFileType.".gif\" WIDTH=\"16\" HEIGHT=\"16\" BORDER=0 ALT=\"\">";
			$showFieldText = $f_NAME;
		}
	}

	$showField = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td align=\"left\">".$showFieldIcon."</td><td align=\"left\">&nbsp;".$showFieldText."</td></tr></table>";

	$row =& $lAdmin->AddRow($f_NAME, $Elem);

	if($row->VarsFromForm() && $_REQUEST["FIELDS"])
		$val = $_REQUEST["FIELDS"][$f_NAME]["NAME"];
	else
		$val = $f_NAME;

	$editField = "<input type=\"text\" name=\"FIELDS[".$f_NAME."][NAME]\" value=\"".htmlspecialchars($val)."\" size=\"40\"> ";

	if($logical=='Y')
		$row->AddField("NAME", $showField);
	else
		$row->AddField("NAME", $showField, $editField);


	if($logical=='Y')
	{
		$showFieldIcon = "";
		$showFieldText = "";
		if(strlen($f_LOGIC_NAME)<=0)
			$f_LOGIC_NAME = htmlspecialchars(GetMessage("FILEMAN_ADM_UNTITLED"));

		if($Elem["TYPE"] == "D")
		{
			$showFieldIcon = "<a href=\"javascript:".$sTableID.".GetAdminList('fileman_admin.php?".$addUrl."&site=".urlencode($site)."&path=".urlencode($path."/".$Elem["NAME"])."&show_perms_for=".IntVal($show_perms_for)."');\" title=\"".htmlspecialchars($path."/".$Elem["NAME"])."\"><IMG SRC=\"/bitrix/images/fileman/types/folder.gif\" WIDTH=\"16\" HEIGHT=\"16\" BORDER=0 ALT=\"\"></a>";
			$showFieldText = "<a href=\"javascript:".$sTableID.".GetAdminList('fileman_admin.php?".$addUrl."&site=".urlencode($site)."&path=".urlencode($path."/".$Elem["NAME"])."&show_perms_for=".IntVal($show_perms_for)."');\" title=\"".htmlspecialchars($path."/".$Elem["NAME"])."\">".$f_LOGIC_NAME."</a>";
		}
		else
		{
			$curFileType = CFileMan::GetFileTypeEx($Elem["NAME"]);
			if(preg_match('/^\.(.*)?\.menu\.(php|html|php3|php4|php5|phtml)$/', $Elem['NAME'], $regs))
			{
				$showFieldIcon = "";
				$showFieldText = GetMessage("FILEMAN_ADMIN_MENU_TYPE")."&laquo;".htmlspecialchars($regs[1])."&raquo;";
			}
			else
			{
				$showFieldIcon = "<IMG SRC=\"/bitrix/images/fileman/types/".$curFileType.".gif\" WIDTH=\"16\" HEIGHT=\"16\" BORDER=0 ALT=\"\"  title=\"".htmlspecialchars($path."/".$Elem["NAME"])."\">";
				$showFieldText = $f_LOGIC_NAME;
			}
		}

		$showField = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td align=\"left\">".$showFieldIcon."</td><td align=\"left\">&nbsp;".$showFieldText."</td></tr></table>";
		$row->AddViewField("LOGIC_NAME", $showField);
	}

	$row->AddField("SIZE", (($Elem["TYPE"]=="F") ? CFileMan::GetStrFileSize($f_SIZE) : ""));
	$row->AddField("DATE", $f_DATE);

	$row->AddField("TYPE", ($Elem["TYPE"] == "D") ? GetMessage('FILEMAN_FOLDER') : htmlspecialchars($arFilemanPredifinedFileTypes[$curFileType]["name"]));

	$showField = "";
	if(in_array("PERMS", $arVisibleColumns))
	{
		if($USER->CanDoFileOperation('fm_view_permission', $arPath))
		{
			$showField .= GetUnixFilePermissions($fname);
			if(function_exists("posix_getpwuid") && function_exists("posix_getgrgid"))
			{
				$arrFileOwner = posix_getpwuid(fileowner($fname));
				$arrFileGroup = posix_getgrgid(filegroup($fname));
				$showField .= " ".$arrFileOwner['name']." ".$arrFileGroup['name'];
			}
		}
		else
			$showField = "&nbsp;";
	}
	$row->AddField("PERMS", $showField);

	$showField = "";
	if (in_array("PERMS_B", $arVisibleColumns))
	{
		$showField = "&nbsp;";
		if(($USER->CanDoOperation('fileman_view_permissions') || $USER->CanDoOperation('fileman_edit_all_settings')) && $USER->CanDoFileOperation('fm_view_permission', $arPath))
		{
			$arP = $APPLICATION->GetFileAccessPermission(Array($site, $path."/".$Elem["NAME"]), ((IntVal($show_perms_for) > 0) ? array($show_perms_for) : false), true);
			//$cur_dir_taskId = CTask::GetIdByLetter($arP[0],'main','file');
			$cur_dir_taskId = $arP[0];
			if ($cur_dir_taskId)
			{
				$z = CTask::GetById($cur_dir_taskId);
				if ($r = $z->Fetch())
				if ($r['NAME'])
				{
					$showField = GetMessage(strtoupper($r['NAME']));
					if(strlen($showField) <= 0)
						$showField = $r['NAME'];
				}
			}

			$add_tasks = $arP[1];
			$len=count($add_tasks);
			if ($len > 0)
			{
				for ($i=0;$i<$len;$i++)
				{
					$z = CTask::GetById($add_tasks[$i]);
					if ($r = $z->Fetch())
						if ($r['NAME'])
							$showField .= ', '.$r['NAME'];
				}
			}
		}
	}
	$row->AddField("PERMS_B", $showField);

	$arActions = Array();

	if ($Elem["TYPE"] == "F")
	{
		if($USER->CanDoFileOperation('fm_view_listing', $arPath))
		{
			if ($USER->CanDoOperation('fileman_edit_menu_elements') && preg_match('/^\.(.*)?\.menu\.(php|html|php3|php4|php5|phtml)$/', $Elem['NAME'], $regs) && $USER->CanDoFileOperation('fm_edit_existent_file', $arPath))
			{
				$arActions[] = array(
					"ICON" => "edit",
					"TEXT" => GetMessage("FILEMAN_ADMIN_EDIT_AS_MENU"),
					"DEFAULT" => true,
					"ACTION" => $lAdmin->ActionRedirect("fileman_menu_edit.php?path=".urlencode($path)."&site=".$site."&name=".urlencode($regs[1])."&".$addUrl."&".GetFilterParams("filter_")."")
				);
				if ($USER->CanDoOperation('edit_php') && $USER->CanDoFileOperation('fm_edit_existent_file', $arPath))
				{
					$arActions[] = array(
						"ICON" => "btn_fileman_php",
						"TEXT" => GetMessage("FILEMAN_ADMIN_EDIT_AS_PHP"),
						"DEFAULT" => false,
						"ACTION" => $lAdmin->ActionRedirect("fileman_file_edit.php?path=".urlencode($path."/".$Elem["NAME"])."&full_src=Y&site=".$site."&".$addUrl."&".GetFilterParams("filter_")."")
					);
				}
			}
			else
			{
				$curFilePreType = $arFilemanPredifinedFileTypes[$curFileType]["gtype"];

				if($curFilePreType == "text")
					$defaultEdit = COption::GetOptionString("fileman", "default_edit", "text");
				else
					$defaultEdit = "";

				if($curFilePreType == "text")
				{
					if($USER->CanDoFileOperation('fm_edit_existent_file',$arPath))
					{
						$arActions[] = array(
							"ICON" => "btn_fileman_html",
							"DEFAULT" => (($defaultEdit == "html") ? True : False),
							"TEXT" => GetMessage("FILEMAN_ADMIN_EDIT_AS_HTML"),
							"ACTION" => $lAdmin->ActionRedirect("fileman_html_edit.php?path=".urlencode($path."/".$Elem["NAME"])."&site=".$site."&".$addUrl."&".GetFilterParams("filter_")."")
						);

						$arActions[] = array(
							"ICON" => "btn_fileman_text",
							"TEXT" => GetMessage("FILEMAN_ADMIN_EDIT_AS_TEXT"),
							"DEFAULT" => (($defaultEdit == "text") ? True : False),
							"ACTION" => $lAdmin->ActionRedirect("fileman_file_edit.php?path=".urlencode($path."/".$Elem["NAME"])."&site=".$site."&".$addUrl."&".GetFilterParams("filter_")."")
						);

						if ($USER->CanDoOperation('edit_php'))
						{
							$arActions[] = array(
								"ICON" => "btn_fileman_php",
								"TEXT" => GetMessage("FILEMAN_ADMIN_EDIT_AS_PHP"),
								"DEFAULT" => (($defaultEdit == "php") ? True : False),
								"ACTION" => $lAdmin->ActionRedirect("fileman_file_edit.php?path=".urlencode($path."/".$Elem["NAME"])."&full_src=Y&site=".$site."&".$addUrl."&".GetFilterParams("filter_")."")
							);
						}
					}

					if (CModule::IncludeModule("workflow") && $USER->CanDoFileOperation('fm_edit_in_workflow',$arPath))
					{
						$arActions[] = array(
							"ICON" => "btn_fileman_galka",
							"DEFAULT" => (($Elem["PERMISSION"]=="U") ? True : False),
							"TEXT" => GetMessage("FILEMAN_EDIT_IN_WORKFLOW"),
							"ACTION" => $lAdmin->ActionRedirect(CWorkFlow::GetEditLink($path."/".$Elem["NAME"], $sid, $st))
						);
					}
				}

				if($USER->CanDoFileOperation('fm_view_file', $arPath) &&
				($USER->CanDoOperation('edit_php') || $USER->CanDoFileOperation('fm_lpa', $arPath) ||
				!(in_array(CFileman::GetFileExtension($Elem["NAME"]), CFileMan::GetScriptFileExt()) ||
				substr($Elem["NAME"], 0, 1)==".")))
				{
					$arActions[] = array(
						"ICON" => "btn_fileman_view",
						"TEXT" => GetMessage("FILEMAN_ADMIN_VIEW"),
						"DEFAULT" => (($curFilePreType != "text" && !$USER->IsAdmin()) ? True : False),
						"ACTION" => $lAdmin->ActionRedirect("fileman_file_view.php?path=".urlencode($path."/".$Elem["NAME"])."&site=".$site."&".$addUrl."&".GetFilterParams("filter_")."")
					);
				}


				if(($USER->CanDoFileOperation('fm_download_file', $arPath) &&
				!(in_array(CFileman::GetFileExtension($Elem["NAME"]), CFileMan::GetScriptFileExt()) || 
				substr($Elem["NAME"], 0, 1) == ".")) || 
				$USER->CanDoOperation('edit_php'))
				{
					$arActions[] = array(
						"ICON" => "btn_download",
						"TEXT" => GetMessage("FILEMAN_DOWNLOAD"),
						"ACTION" => $lAdmin->ActionRedirect("fileman_file_download.php?path=".urlencode($path."/".$Elem["NAME"])."&site=".$site."&".$addUrl)
					);
				}
			}
		}
	}
	else
	{
		if($USER->CanDoFileOperation('fm_view_listing',$arPath))
		{
			$arActions[] = array(
				"ICON" => "",
				"TEXT" => GetMessage('FILEMAN_N_OPEN'),
				"DEFAULT" => true,
				"ACTION" => "javascript:".$sTableID.".GetAdminList('fileman_admin.php?".$addUrl."&site=".urlencode($site)."&path=".urlencode($path."/".$Elem["NAME"])."&show_perms_for=".IntVal($show_perms_for)."');"
			);
		}

		if($USER->CanDoFileOperation('fm_edit_existent_folder', $arPath))
		{
			$arActions[] = array(
				"ICON" => "btn_fileman_prop",
				"TEXT" => GetMessage("FILEMAN_ADMIN_FOLDER_PROP"),
				"ACTION" => $lAdmin->ActionRedirect("fileman_folder.php?".$addUrl."&site=".urlencode($site)."&path=".urlencode($path."/".$Elem["NAME"])."")
			);
		}
	}

	$type = $Elem["TYPE"] == "F" ? 'file' : 'folder';
	if ($logical != "Y")
	{
		if($USER->CanDoFileOperation('fm_rename_'.$type,$arPath))
		{
			$arActions[] = array("SEPARATOR" => true);
			$arActions[] = array(
				"ICON" => "rename",
				"TEXT" => GetMessage("FILEMAN_RENAME_SAVE"),
				"ACTION" => 'setCheckbox(\''.$f_NAME.'\'); if('.$lAdmin->table_id.'.IsActionEnabled(\'edit\')){document.forms[\'form_'.$lAdmin->table_id.'\'].elements[\'action_button\'].value=\'edit\'; '.$lAdmin->ActionPost().'}'
			);
		}
		if($USER->CanDoFileOperation('fm_delete_'.$type,$arPath))
		{
			$arActions[] = array(
				"ICON" => "delete",
				"TEXT" => GetMessage("FILEMAN_ADMIN_DELETE"),
				"ACTION" => "if(confirm('".GetMessage('FILEMAN_ALERT_DELETE')."')) ".$lAdmin->ActionDoGroup(urlencode($f_NAME), "delete", $addUrl."&site=".urlencode($site)."&path=".urlencode($path)."&show_perms_for=".IntVal($show_perms_for))
			);
		}
		if ($USER->CanDoFileOperation('fm_edit_permission',$arPath))
		{
			$arActions[] = array("SEPARATOR" => true);
			$arActions[] = array(
				"ICON" => "access",
				"TEXT" => GetMessage("FILEMAN_ADMIN_ACCESS"),
				"ACTION" => "setCheckbox('".$f_NAME."'); setAccess('".str_replace("'", "\'", $site)."', '".str_replace("'", "\'", $path)."');"
			);
		}
	}
	$row->AddActions($arActions);
}
$arPath = Array($site, $path);// arPath for current folder
// "подвал" списка
$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $db_DirContent->SelectedRowsCount()
		),
		array(
			"counter" => true,
			"title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
			"value" => "0"
		),
	)
);

$strHTML =
	"<input type=\"text\" name=\"copy_to\" size=\"18\" value=\"\" disabled>".
	"<input type=\"button\" name=\"copy_to_button\" value=\"...\" onClick=\"DRList();\" disabled>".
	"<input type=\"hidden\" name=\"copy_to_site\" value=\"\">";

// показ формы с кнопками добавления, ...
$arGrActionAr = Array();
if($USER->CanDoFileOperation('fm_delete_'.$type,$arPath))
	$arGrActionAr["delete"] = GetMessage("MAIN_ADMIN_LIST_DELETE");
if($USER->CanDoFileOperation('fm_edit_permission',$arPath))
	$arGrActionAr["access"] = array(
			"action" => "setAccess('".str_replace("'", "\'", $site)."', '".str_replace("'", "\'", $path)."')",
		"value" => "access",
		"name" => GetMessage('FILEMAN_ADMIN_ACCESS_CMD')
	);
if($USER->CanDoFileOperation('fm_create_new_'.$type,$arPath))
	$arGrActionAr["copy"] = GetMessage("FILEMAN_ADM_COPY");
if($USER->CanDoFileOperation('fm_create_new_'.$type,$arPath) && $USER->CanDoFileOperation('fm_delete_'.$type,$arPath))
	$arGrActionAr["move"] = GetMessage("FILEMAN_ADM_MOVE");
if($USER->CanDoFileOperation('fm_create_new_'.$type,$arPath))
{
	$arGrActionAr["copy2"] = array(
		"type" => "html",
		"value" => "&nbsp;".GetMessage("FILEMAN_ADMIN_IN")."&nbsp;"
	);
	$arGrActionAr["copy1"] = array(
		"type" => "html",
		"value" => $strHTML
	);
}


if ($logical != "Y")
{
	$lAdmin->AddGroupActionTable(
		$arGrActionAr,
		array("select_onchange"=>"this.form.copy_to_button.disabled=this.form.copy_to.disabled=!(this[this.selectedIndex].value == 'copy' || this[this.selectedIndex].value == 'move')")
	);
}
$defaultEdit = COption::GetOptionString("fileman", "default_edit", "text");

if($USER->CanDoOperation('view_groups') && $USER->CanDoFileOperation('fm_view_permission', $arPath))
{
	$arDDMenu = array();
	$isB = false;
	$dbRes = CGroup::GetDropDownList();
	while ($arRes = $dbRes->Fetch())
	{
		if($show_perms_for == $arRes["REFERENCE_ID"])
			$isB = true;

		$arDDMenu[] = array(
			"TEXT" => $arRes["REFERENCE"],
			"ACTION" => $lAdmin->ActionAjaxReload("fileman_admin.php?".$addUrl."&site=".urlencode($site)."&path=".urlencode($path)."&show_perms_for=".$arRes["REFERENCE_ID"]).';return false;',
			"ICON" =>	($show_perms_for == $arRes["REFERENCE_ID"] ? "checked" : "" ),
		);
	}

	$arDDMenu[] = array(
		"TEXT" => GetMessage("FILEMAN_ADM_CUR_USER"),
		"ACTION" => $lAdmin->ActionAjaxReload("fileman_admin.php?".$addUrl."&site=".urlencode($site)."&path=".urlencode($path)."&show_perms_for=0").';return false;',
		"ICON" =>	(!$isB ? "checked" : "" ),
	);
}

$aContext = Array();

if($USER->CanDoOperation('fileman_admin_folders') && $USER->CanDoFileOperation('fm_create_new_folder',$arPath))
	$aContext[] = Array(
		"TEXT" => GetMessage("FILEMAN_ADMIN_NEW_FOLDER"),
		"ICON" => "btn_new_folder",
		"LINK" => "fileman_newfolder.php?".$addUrl."&site=".$site."&path=".urlencode($path)."",
		"TITLE" => GetMessage("FILEMAN_ADMIN_NEW_FOLDER")
	);

if($USER->CanDoOperation('fileman_admin_files') && $USER->CanDoFileOperation('fm_create_new_file',$arPath))
	$aContext[] = Array(
		"TEXT" => GetMessage("FILEMAN_ADMIN_NEW_FILE"),
		"ICON" => "btn_new_file",
		"LINK" =>
			($defaultEdit == 'html'?
				"fileman_html_edit.php?".$addUrl."&site=".$site."&path=".urlencode($path)."&new=y"
			:
				(
				$defaultEdit == 'php' && $USER->IsAdmin()?
					"fileman_file_edit.php?".$addUrl."&site=".$site."&full_src=Y&path=".urlencode($path)."&new=y"
				:
					"fileman_file_edit.php?".$addUrl."&site=".$site."&path=".urlencode($path)."&new=y"
				)
			),
		"TITLE" => GetMessage("FILEMAN_ADMIN_NEW_FILE")
	);
if($USER->CanDoOperation('fileman_upload_files') && $USER->CanDoFileOperation('fm_upload_file',$arPath))
	$aContext[] = Array(
		"TEXT" => GetMessage("FILEMAN_ADMIN_FILE_UPLOAD"),
		"ICON" => "btn_upload",
		"LINK" => "fileman_file_upload.php?".$addUrl."&site=".$site."&path=".urlencode($path)."",
		"TITLE" => GetMessage("FILEMAN_ADMIN_FILE_UPLOAD")
	);
if($USER->CanDoOperation('fileman_add_element_to_menu') && $USER->CanDoFileOperation('fm_add_to_menu',$arPath))
	$aContext[] = Array(
		"TEXT" => GetMessage("FILEMAN_ADMIN_MENU_ADD"),
		"ICON" => "btn_new_menu",
		"LINK" => "fileman_menu_edit.php?".$addUrl."&site=".$site."&path=".urlencode($path),
		"TITLE" => GetMessage("FILEMAN_ADMIN_MENU_ADD")
	);

if(count($aContext) > 0)
	$aContext[] = Array("NEWBAR" => true);

if($USER->CanDoOperation('fileman_edit_existent_folders') && $USER->CanDoFileOperation('fm_edit_existent_folder', $arPath))
	$aContext[] = Array(
		"TEXT" => GetMessage("FILEMAN_ADMIN_FOLDER_PROP"),
		"LINK" => "fileman_folder.php?".$addUrl."&site=".$site."&path=".urlencode($path)."",
		"ICON" => "btn_folder_prop",
		"TITLE" => GetMessage("FILEMAN_ADMIN_FOLDER_PROP")
	);

if ($USER->CanDoOperation('view_groups') && $USER->CanDoFileOperation('fm_view_permission', $arPath) && $USER->CanDoFileOperation('fm_edit_existent_folder',$arPath))
	$aContext[] = Array(
		"TEXT" => GetMessage('FILEMAN_SHOW_PRM_FOR'),
		"TITLE" => GetMessage('FILEMAN_SHOW_PRM_FOR'),
		"MENU" => $arDDMenu
	);
if(count($aContext) > 0)
	$aContext[] = Array("NEWBAR" => true);

ob_start();
?>
<table cellspacing="0">
<tr>
	<td style="padding-left:5px;"><?echo GetMessage("FILEMAN_FAST_PATH")?></td>
	<td style="padding-left:5px;"><input class="form-text" type="text" name="quick_path" id="quick_path" size="50" value="<?echo htmlspecialchars($path)?>" onkeyup="if (event.keyCode==13) <?= $sTableID ?>.GetAdminList('fileman_admin.php?<?=$addUrl?>&site=<?= urlencode($site) ?>&path='+document.getElementById('quick_path').value+'&show_perms_for=<?= IntVal($show_perms_for) ?>'); return false;"></td>
	<td style="padding-left:3px; padding-right:3px;"><input class="form-button" type="button" value="OK" title="<?echo GetMessage("FILEMAN_FAST_PATH_BUTTON")?>" OnClick="<?= $sTableID ?>.GetAdminList('fileman_admin.php?<?=$addUrl?>&site=<?= urlencode($site) ?>&path='+document.getElementById('quick_path').value+'&show_perms_for=<?= IntVal($show_perms_for) ?>')"></td>
</tr>
</table>
<?
$s = ob_get_contents();
ob_end_clean();
$aContext[] = array("HTML"=>$s);
$lAdmin->AddAdminContextMenu($aContext);
$chain = $lAdmin->CreateChain();

foreach ($arParsedPath["AR_PATH"] as $chainLevel)
{
	$chain->AddItem(
		array(
			"TEXT" => htmlspecialcharsex($chainLevel["TITLE"]),
			"LINK" => ((strlen($chainLevel["LINK"]) > 0) ? $chainLevel["LINK"] : ""),
			"ONCLICK" => ((strlen($chainLevel["LINK"]) > 0) ? $lAdmin->ActionAjaxReload($chainLevel["LINK"]).';return false;' : ""),
		)
	);
}
$lAdmin->ShowChain($chain);

// проверка на вывод только списка (в случае списка, скрипт дальше выполняться не будет)
$lAdmin->CheckListMode();

/***********  MAIN PAGE **********/
$APPLICATION->SetTitle($title);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");


//New File Dialog Init
CAdminFileDialog::ShowScript
(
	Array(
		"event" => "DRList",
		"arResultDest" => Array("FUNCTION_NAME" => "_DRListAct"),
		"arPath" => Array("SITE" => $site, "PATH" =>''),
		"select" => 'D',// F - file only, D - folder only
		"operation" => 'O',
		"showUploadTab" => false,
		"showAddToMenuTab" => false,
		"fileFilter" => '',
		"allowAllFiles" => true,
		"SaveConfig" => true
	)
);
?>
<script>
function DRListAct(filename, path, site)
{
	var val = path;
	if (val.length > 0)
		val = val + "/";
	val = val + filename;

	document.form_<?= $sTableID ?>.copy_to.value = val;
	document.form_<?= $sTableID ?>.copy_to_site.value = site;
}

function _DRListAct(filename, path, site)
{
	var val = filename;

	document.form_<?= $sTableID ?>.copy_to.value = val;
	document.form_<?= $sTableID ?>.copy_to_site.value = site;
}

function setAccess(site, path)
{
	var oForm = document.form_<?= $sTableID ?>;
	var expType = oForm.action_target.checked;

	var par = "";
	if (!expType)
	{
		var num = oForm.elements.length;
		for (var i = 0; i < num; i++)
		{
			if (oForm.elements[i].tagName.toUpperCase() == "INPUT"
				&& oForm.elements[i].type.toUpperCase() == "CHECKBOX"
				&& oForm.elements[i].name.toUpperCase() == "ID[]"
				&& oForm.elements[i].checked == true)
			{
				if (par.length > 0)
					par = par + "&";

				par = par + "files[]=" + jsUtils.urlencode(oForm.elements[i].value);
			}
		}
	}

	window.location="fileman_access.php?<?=$addUrl?>&site="+jsUtils.urlencode(site)+"&path="+jsUtils.urlencode(path)+"&"+par;
}

function setCheckbox(name)
{
	var listTable = document.getElementById("<? echo $lAdmin->table_id;?>");
	for (var row=0; row<listTable.rows.length; row++)
	{
		var oTR = listTable.rows[row];
		var oInputTD = oTR.cells[0];
		var oInput = oInputTD.firstChild;
		if (!oInput)
			continue;
		if (oInput.value == name)
		{
			oInput.checked = true;
			oInput.onclick.apply(oInput);
		}
		else
			oInput.checked = false;
	}
}
</script>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		//GetMessage('MAIN_F_SIZE'),
		GetMessage('MAIN_F_TIMESTAMP'),
		GetMessage('MAIN_F_TYPE')
	)
);

$oFilter->Begin();
?>
<tr>
	<td><b><?echo GetMessage("MAIN_F_NAME")?>:</b></td>
	<td nowrap>
		<input type="text" name="find_name" value="<?echo htmlspecialchars($find_name)?>" size="35">
	</td>
</tr>
<!--<tr>
	<td nowrap><?echo GetMessage("MAIN_F_SIZE")?>:</td>
	<td nowrap><input type="text" name="find_size" value="<?echo htmlspecialchars($find_size)?>" size="10"></td>
</tr>-->
<tr>
	<td width="0%" nowrap><?echo GetMessage("MAIN_F_TIMESTAMP")." (".CLang::GetDateFormat("SHORT")."):"?></td>
	<td width="0%" nowrap><?echo CalendarPeriod("find_timestamp_1", htmlspecialchars($find_timestamp_1), "find_timestamp_2", htmlspecialchars($find_timestamp_2), "find_form","Y")?></td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("MAIN_F_TYPE")?>:</td>
	<td nowrap><?
		$arr = array("reference"=>array(GetMessage("FILEMAN_FILE"), GetMessage("FILEMAN_FOLDER")), "reference_id"=>array("F","D"));
		echo SelectBoxFromArray("find_type", $arr, htmlspecialchars($find_type), GetMessage("MAIN_ALL"));
		?></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage().'?'.$addUrl."&site=".urlencode($site)."&path=".urlencode($path."/".$Elem["NAME"]), "form"=>"find_form"));
$oFilter->End();
?>
</form>
<?
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
