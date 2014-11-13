<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2005 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
*/
global $DOCUMENT_ROOT, $MESS;
IncludeModuleLangFile(__FILE__);
define("DEBUG_FILE_MAN", false);

global $arFilemanPredifinedFileTypes;
$arFilemanPredifinedFileTypes = array(
	"css" => array(
		"exts" => array("css"),
		"gtype" => "text",
		"name" => GetMessage("MAIN_BFSD_FTYPE_CSS")
	),
	"htaccess" => array(
		"exts" => array("htaccess"),
		"gtype" => "text",
		"name" => GetMessage("MAIN_BFSD_FTYPE_SYS")
	),
	"html" => array(
		"exts" => array("html", "htm", "shtml", "shtm"),
		"gtype" => "text",
		"name" => GetMessage("MAIN_BFSD_FTYPE_HTML")
	),
	"png" => array(
		"exts" => array("png"),
		"gtype" => "image",
		"name" => GetMessage("MAIN_BFSD_FTYPE_PNG")
	),
	"gif" => array(
		"exts" => array("gif"),
		"gtype" => "image",
		"name" => GetMessage("MAIN_BFSD_FTYPE_GIF")
	),
	"jpeg" => array(
		"exts" => array("jpeg", "jpg", "jpe"),
		"gtype" => "image",
		"name" => GetMessage("MAIN_BFSD_FTYPE_JPG")
	),
	"js" => array(
		"exts" => array("js"),
		"gtype" => "text",
		"name" => "JavaScript"
	),
	"php" => array(
		"exts" => array("php", "php3", "php4", "php5", "phtml"),
		"gtype" => "text",
		"name" => GetMessage("MAIN_BFSD_FTYPE_PHP")
	),
	"txt" => array(
		"exts" => array("txt", "sql"),
		"gtype" => "text",
		"name" => GetMessage("MAIN_BFSD_FTYPE_TXT")
	),
	"xml" => array(
		"exts" => array("xml", "xsl"),
		"gtype" => "text",
		"name" => GetMessage("MAIN_BFSD_FTYPE_XML")
	),
	"csv" => array(
		"exts" => array("csv"),
		"gtype" => "text",
		"name" => GetMessage("MAIN_BFSD_FTYPE_CSV")
	),
	"flash" => array(
		"exts" => array("fla", "swf"),
		"gtype" => "file",
		"name" => GetMessage("MAIN_BFSD_FTYPE_SWF")
	),
	"file" => array(
		"exts" => array(),
		"gtype" => "file",
		"name" => GetMessage("MAIN_BFSD_FTYPE_NA")
	)
);

class CFileMan
{
	var $arFILE_TYPES;

	function OnPanelCreate()
	{
		global $APPLICATION, $REQUEST_URI;
		if($APPLICATION->GetGroupRight("fileman")<="D")
			return;

		$cur_page = $APPLICATION->GetCurPage();
		$cur_dir = $APPLICATION->GetCurDir();

		//access to curent page
		$page_permission = $APPLICATION->GetFileAccessPermission($cur_page);

		// access to current directory
		$sect_permission = $APPLICATION->GetFileAccessPermission($cur_dir);

		$main_sort = 100;

		$full_src = "";
		$default_edit = COption::GetOptionString("fileman", "default_edit", "html");
		if($default_edit == "php")
		{
			$editor_type = "file";
			$full_src = "&full_src=Y";
		}
		elseif($default_edit == "text")
			$editor_type = "file";
		else
			$editor_type = "html";

		if($APPLICATION->GetPublicShowMode() <> 'view')
		{
			$aMenuItems = array();
			if ($sect_permission>="W")
			{
				// New page
				$href = "/bitrix/admin/fileman_".$editor_type."_edit.php?lang=".LANGUAGE_ID."&site=".SITE_ID."&path=".UrlEncode($APPLICATION->GetCurDir())."&new=Y&back_url=".UrlEncode($REQUEST_URI);
				$aMenuItems[] = array(
					"TEXT" => GetMessage("fileman_panel_new_page"),
					"TITLE"=> GetMessage("fileman_panel_new_page_title"),
					"ICON"		=> "panel-new-file",
					"ACTION"	=> "jsUtils.Redirect(arguments, '".CUtil::JSEscape($href)."')",
				);
	
				//New folder
				$href = "/bitrix/admin/fileman_newfolder.php?lang=".LANGUAGE_ID."&site=".SITE_ID."&path=". UrlEncode($APPLICATION->GetCurDir())."&back_url=".UrlEncode($REQUEST_URI);
				$aMenuItems[] = array(
					"TEXT" => GetMessage("fileman_panel_new_folder"),
					"TITLE"=> GetMessage("fileman_panel_new_folder_title"),
					"ICON"		=> "panel-new-folder",
					"ACTION"	=> "jsUtils.Redirect(arguments, '".CUtil::JSEscape($href)."')",
				);
			}
	
			// Edit page
			$aMenuEditItems = array();
			if ($page_permission>="W")
			{
				$href = "/bitrix/admin/fileman_".$editor_type."_edit.php?lang=".LANGUAGE_ID."&site=".SITE_ID.$full_src."&path=".UrlEncode($_SERVER["REAL_FILE_PATH"]<>""? $_SERVER["REAL_FILE_PATH"] : $APPLICATION->GetCurPage())."&back_url=".UrlEncode($REQUEST_URI);
				$aMenuEditItems[] = array(
					"TEXT" => GetMessage("fileman_panel_edit"),
					"TITLE"=> GetMessage("fileman_panel_edit_title"),
					"ICON"		=> "panel-edit-visual",
					"ACTION"	=> "jsUtils.Redirect(arguments, '".CUtil::JSEscape($href)."')",
				);
			}
	
			// Folder properties
			$alt = GetMessage("FILEMAN_FOLDER_PROPS");
			if ($sect_permission>="W")
			{
				$href = "/bitrix/admin/fileman_folder.php?lang=".LANGUAGE_ID."&site=".SITE_ID."&path=".UrlEncode($APPLICATION->GetCurDir())."&back_url=".UrlEncode($REQUEST_URI);
				$aMenuEditItems[] = array(
					"TEXT" => GetMessage("fileman_panel_folder_prop"),
					"TITLE"=> GetMessage("fileman_panel_folder_prop_title"),
					"ICON"		=> "panel-folder-props",
					"ACTION"	=> "jsUtils.Redirect(arguments, '".CUtil::JSEscape($href)."')",
				);
			}
	
			//add menu to standard "create" button
			if(!empty($aMenuItems))
			{
				$aMenu = array(
					"TEXT" => GetMessage("fileman_panel_admin"),
					"MENU"=>$aMenuItems,
				);
				$APPLICATION->AddPanelButtonMenu("create", array("SEPARATOR"=>true));
				$APPLICATION->AddPanelButtonMenu("create", $aMenu);
			}
	
			//add menu to standard "create" button
			if(!empty($aMenuEditItems))
			{
				$aMenu = array(
					"TEXT" => GetMessage("fileman_panel_admin"),
					"MENU"=>$aMenuEditItems,
				);
				$APPLICATION->AddPanelButtonMenu("edit", array("SEPARATOR"=>true));
				$APPLICATION->AddPanelButtonMenu("edit", $aMenu);
			}
		} //$APPLICATION->GetPublicShowMode()

		//obsolete
		if(COption::GetOptionString("fileman", "show_inc_icons", "N")=="Y")
		{
			$SECT_FILENAME = $cur_dir."sect_inc.php";
			$sect_inc_permission = $APPLICATION->GetFileAccessPermission($SECT_FILENAME);
			// Included area for partition
			$alt = GetMessage("FILEMAN_EDIT_AREA_1");
			if ($sect_inc_permission>="W")
			{
				$sect_template = (defined("SECTION_TEMPLATE_NAME")) ? SECTION_TEMPLATE_NAME : "sect_inc.php";
				$href = "/bitrix/admin/fileman_".$editor_type."_edit.php?lang=".LANGUAGE_ID.$full_src."&path=".
				UrlEncode($SECT_FILENAME)."&template=".$sect_template."&back_url=".UrlEncode($REQUEST_URI);
				$src = "/bitrix/images/fileman/panel/edit_sect_inc.gif";
				$APPLICATION->AddPanelButton(array("HREF"=>$href, "SRC"=>$src, "ALT"=>$alt, "MAIN_SORT"=>$main_sort, "SORT"=>40));
			}

			$arCur_page = pathinfo($cur_page);
			$INC_FILENAME = substr($cur_page, 0, strlen($cur_page)-strlen($arCur_page["extension"])-1)."_inc.php";
			$page_inc_permission = $APPLICATION->GetFileAccessPermission($INC_FILENAME);

			//Included section for page
			$alt = GetMessage("FILEMAN_EDIT_AREA_2");
			if ($page_inc_permission>="W")
			{
				$page_template = (defined("PAGE_TEMPLATE_NAME")) ? PAGE_TEMPLATE_NAME : "sect_inc.php";
				$href = "/bitrix/admin/fileman_".$editor_type."_edit.php?lang=".LANGUAGE_ID.$full_src."&path=".
				UrlEncode($INC_FILENAME)."&template=".$page_template."&back_url=".UrlEncode($REQUEST_URI);
				$src = "/bitrix/images/fileman/panel/edit_file_inc.gif";
				$APPLICATION->AddPanelButton(array("HREF"=>$href, "SRC"=>$src, "ALT"=>$alt, "MAIN_SORT"=>$main_sort, "SORT"=>50));
			}
		}
	}

	function CFileMan()
	{
		$this->arFILE_TYPES = Array("SOURCE"=>GetMessage("FILEMAN_FILEMAN_SCRIPT_TEXT"), "IMAGE"=>GetMessage("FILEMAN_FILEMAN_PIC"), "UNKNOWN"=>GetMessage("FILEMAN_FILEMAN_UNK"));
	}

	function OnGroupDelete($group_id){return "";}

	function GetVersion()
	{
		@include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/install/version.php");
		if (!isset($arModuleVersion['VERSION']))
			return false;
		return $arModuleVersion['VERSION'];
	}

	function SaveMenu($path, $aMenuLinksTmp, $sMenuTemplateTmp)
	{
		global $APPLICATION;
		CMain::InitPathVars($site, $path);
		$DOC_ROOT = CSite::GetSiteDocRoot($site);

		$strMenuLinks = "";
		if(strlen($sMenuTemplateTmp)>0)
			$strMenuLinks .= "\$sMenuTemplate = \"".CFileMan::EscapePHPString($sMenuTemplateTmp)."\";\n";

		$strMenuLinks .= "\$aMenuLinks = Array(";
		$i=0;
		foreach($aMenuLinksTmp as $arMenuItem)
		{
			$i++;
			$strMenuLinksTmp = "";

			if($i>1)
				$strMenuLinksTmp .= ",";

			$strMenuLinksTmp .= "\n".
				"	Array(\n".
				"		\"".CFileMan::EscapePHPString($arMenuItem[0])."\", \n".
				"		\"".CFileMan::EscapePHPString($arMenuItem[1])."\", \n".
				"		Array(";

			if(is_array($arMenuItem[2]))
			{
				for($j=0; $j<count($arMenuItem[2]); $j++)
				{
					if($j>0)
						$strMenuLinksTmp .= ", ";
					$strMenuLinksTmp .= "\"".CFileMan::EscapePHPString($arMenuItem[2][$j])."\"";
				}
			}
			$strMenuLinksTmp .= "), \n";

			$strMenuLinksTmp .= "		Array(";
			if(is_array($arMenuItem[3]))
			{
				$arParams = array_keys($arMenuItem[3]);
				for($j=0; $j<count($arParams); $j++)
				{
					if($j>0)
						$strMenuLinksTmp .= ", ";
					$strMenuLinksTmp .= "\"".CFileMan::EscapePHPString($arParams[$j])."\"=>"."\"".CFileMan::EscapePHPString($arMenuItem[3][$arParams[$j]])."\"";
				}
			}

			$strMenuLinksTmp .= "), \n".
				"		\"".CFileMan::EscapePHPString($arMenuItem[4])."\" \n".
				"	)";

			$strMenuLinks .= $strMenuLinksTmp;
		}
		$strMenuLinks .= "\n);";
		$APPLICATION->SaveFileContent($DOC_ROOT.$path, "<"."?\n".$strMenuLinks."\n?".">");
		$GLOBALS["CACHE_MANAGER"]->CleanDir("menu");
	}

	function GetMenuArray($abs_path)
	{
		$aMenuLinks = Array();
		$sMenuTemplate = '';

		if (file_exists($abs_path))
			include($abs_path);

		return Array("aMenuLinks"=>$aMenuLinks, "sMenuTemplate" => $sMenuTemplate);
	}

	function GetFileName($path)
	{
		return GetFileName($path);
	}

	function CreateDir($path)
	{
		CMain::InitPathVars($site, $path);
		$DOC_ROOT = CSite::GetSiteDocRoot($site);

		if(DEBUG_FILE_MAN)echo "CreateDir($path);<br>";
		global $APPLICATION;

		$badDirs=Array();
		$path = str_replace("\\", "/", $path);

		while(strlen($path)>1 && $path[strlen($path)-1]=="/")
			$path=substr($path, 0, strlen($path)-1);
		$p=strrpos($path, "/");
		while($p!==false)
		{
			if(file_exists($DOC_ROOT.$path) && is_dir($DOC_ROOT.$path))
			{
				@chmod($DOC_ROOT.$path, BX_DIR_PERMISSIONS);
				break;
			}
			$badDirs[]=substr($path, $p+1);
			$path = substr($path, 0, $p);
			$p=strrpos($path, "/");
		}

		for($i=count($badDirs)-1; $i>=0; $i--)
		{
			$path = $path."/".$badDirs[$i];
			if($APPLICATION->GetFileAccessPermission(Array($site, $path))<"W")
				return GetMessage("FILEMAN_FILEMAN_CREATE_FOLDER_DENY")." \"".$path."\".\n";

			mkdir($DOC_ROOT.$path, BX_DIR_PERMISSIONS);
		}
	}


	//Function check if there are anything exept .access.php and if folder is empty - delete it
	function DeleteDir($path)
	{
		if(DEBUG_FILE_MAN)echo "DeleteDir(".$path.");<br>";

		CMain::InitPathVars($site, $path);
		$DOC_ROOT = CSite::GetSiteDocRoot($site);

		global $APPLICATION;
		if(strlen(trim($path))<=0)
			return GetMessage("FILEMAN_FILEMAN_TRYING_ROOT_DELETE")."\n";
		if(!is_dir($DOC_ROOT.$path))
			return GetMessage("FILEMAN_FILEMAN_FOLDER")." \"$path\" ".GetMessage("FILEMAN_FILEMAN_NOT_EXISTS")."\n";

		//check rights to write for this subfolder
		if($APPLICATION->GetFileAccessPermission(Array($site, $path))<"W")
			return GetMessage("FILEMAN_FILEMAN_FOLDER_DEL_DENY")."\" ".$path."\".\n";

		if($handle = @opendir($DOC_ROOT.$path))
		{
			while(($file = readdir($handle)) !== false)
			{
				if($file == "." || $file == "..") continue;

				if(!is_dir($DOC_ROOT.$path."/".$file) && $file!=".access.php")
					return GetMessage("FILEMAN_FILEMAN_FOLDER_DEL_ERROR")." \"".$path."\" ".GetMessage("FILEMAN_FILEMAN_FOLDER_NOT_EMPTY").".\n";
			}
		}
		@closedir($handle);

		//it's ok ... BUMP OFF!!!
		@chmod($DOC_ROOT.$path."/.access.php", BX_FILE_PERMISSIONS);
		@unlink($DOC_ROOT.$path."/.access.php");

		@chmod($DOC_ROOT.$path, BX_DIR_PERMISSIONS);
		if(DEBUG_FILE_MAN)echo "rmdir(".$path.");";
		if(!@rmdir($DOC_ROOT.$path))
			return GetMessage("FILEMAN_FILEMAN_FOLDER_DEL_ERROR")." \"".$path."\".\n";

		$APPLICATION->RemoveFileAccessPermission(Array($site, $path));
	}

	function DeleteFile($path)
	{
		global $APPLICATION;
		CMain::InitPathVars($site, $path);
		$DOC_ROOT = CSite::GetSiteDocRoot($site);

		if(!is_file($DOC_ROOT.$path))
			return GetMessage("FILEMAN_FILEMAN_FILE")." \"$path\" ".GetMessage("FILEMAN_FILEMAN_NOT_EXISTS")."\n";

		if(strlen($path)>=12 && substr($path, strlen($path) - 12)=="/.access.php")
			return;

		//check: can we delete this file
		if($APPLICATION->GetFileAccessPermission(Array($site, $path))<"W")
			return GetMessage("FILEMAN_FILEMAN_FILE_DEL_DENY")." \"".$path."\".\n";

		@chmod($DOC_ROOT.$path, BX_FILE_PERMISSIONS);

		//deleting file
		if(DEBUG_FILE_MAN)echo "unlink(".$DOC_ROOT.$path.");<br>";
		$file_size = filesize($DOC_ROOT.$path);
		if(@unlink($DOC_ROOT.$path))
		{
			$APPLICATION->RemoveFileAccessPermission(Array($site, $path));
			if(CModule::IncludeModule("search"))
				CSearch::DeleteIndex("main", $site."|".$path);

		//************************** Quota **************************//
			if(COption::GetOptionInt("main", "disk_space") > 0)
			{
				$quota = new CDiskQuota();
				$quota->updateDiskQuota("file", $file_size, "delete");
			}
		//************************** Quota **************************//
		}
	}

	function DeleteEx($path)
	{
		global $APPLICATION;

		CMain::InitPathVars($site, $path);
		$DOC_ROOT = CSite::GetSiteDocRoot($site);

		@set_time_limit(600);

		if(is_dir($DOC_ROOT.$path))//if delete folder
		{
			//check rights
			if($APPLICATION->GetFileAccessPermission(Array($site,$path))<"W")
				return GetMessage("FILEMAN_FILEMAN_FOLDER_DEL_DENY")." \"".$path."\".\n";
		}
		else //if delete file
		{
			return CFileman::DeleteFile(Array($site, $path));
		}

		//get folder content
		if($handle = @opendir($DOC_ROOT.$path))
		{
			while(($file = readdir($handle)) !== false)
			{
				if($file == "." || $file == "..") continue;

				if(is_dir($DOC_ROOT.$path."/".$file)) //if it's subflder
				{
					//recursion
					$strWarning .= CFileman::DeleteEx(Array($site, $path."/".$file));
				}
				else //it's subfile ;-)
				{
					if($file == ".access.php") continue;
					$strWarning .= CFileman::DeleteFile(Array($site, $path."/".$file));
				}
			}
		}
		@closedir($handle);

		//delete first folder
		$strWarning .= CFileMan::DeleteDir(Array($site, $path));

		return $strWarning;
	}


	function NormalizePath($path)
	{
		$res = str_replace("\\", "/", $res);

		while(strpos($path, "//")!==false)
			$path = str_replace("//", "/", $path);

		while(($l=strlen($path))>0 && $path[$l-1]=="/")
			$path = substr($path, 0, $l-1);

		return $path;
	}

	function CopyEx($path_from, $path_to, $bDeleteAfterCopy = false)
	{
		global $APPLICATION;
		CMain::InitPathVars($site_from, $path_from);
		$DOC_ROOT_FROM = CSite::GetSiteDocRoot($site_from);

		CMain::InitPathVars($site_to, $path_to);
		$DOC_ROOT_TO = CSite::GetSiteDocRoot($site_to);

		//check: if we copy to the same directory
		if(strpos($DOC_ROOT_TO.$path_to."/", $DOC_ROOT_FROM.$path_from."/")===0)
			return GetMessage("FILEMAN_LIB_BAD_FOLDER");

	//	if(!file_exists($DOCUMENT_ROOT.$path_from))
	//		return "Файл или папка \"".$path_from."\" не существует!\n";

		if(is_dir($DOC_ROOT_FROM.$path_from))//если копируем папку
		{
			//чтобы скопировать папку нужны минимум права на чтение этой папки
			if($APPLICATION->GetFileAccessPermission(Array($site_from, $path_from))<"R")
				return GetMessage("FILEMAN_FILEMAN_FOLDER_READ_DENY")." \"".$path_from."\".\n";

			//проверим - а существует ли папка, в которую мы хотим писать
			$strWarTmp = CFileMan::CreateDir(Array($site_to, $path_to));

			if(strlen($strWarTmp)>0)
				return $strWarTmp;

			$APPLICATION->CopyFileAccessPermission(Array($site_from, $path_from), Array($site_to, $path_to));
		}
		else //если нам нужно скопировать файл
		{
			//проверим можем ли мы записывать в этот файл
			if($APPLICATION->GetFileAccessPermission(Array($site_to, $path_to))<"W")
				return GetMessage("FILEMAN_FILEMAN_FILE_WRITE_DENY")." \"".$path_to."\".\n";

			//чтобы скопировать файл нужны минимум права на запись для этого файла
			if($APPLICATION->GetFileAccessPermission(Array($site_from, $path_from))<"W")
				return GetMessage("FILEMAN_FILEMAN_FILE_READ_DENY")." \"".$path_from."\".\n";

			//проверим - а сущестувет ли папка в которую мы будем копировать файл
			$p=strrpos($path_to, "/");
			$path_to_dir = substr($path_to, 0, $p);
			$strWarTmp = CFileMan::CreateDir(Array($site_to, $path_to_dir));
			if(strlen($strWarTmp)>0)
				return $strWarTmp;

			if(file_exists($DOC_ROOT_TO.$path_to))
				return GetMessage("FILEMAN_FILEMAN_FILE_WITH_NAME")." \"".$path_to."\" ".GetMessage("FILEMAN_FILEMAN_ALREADY_EXISTS")."!\n";

			$APPLICATION->CopyFileAccessPermission(Array($site_from, $path_from), Array($site_to, $path_to));

			//************************** Quota **************************//
			if(COption::GetOptionInt("main", "disk_space") > 0)
			{
				$size = filesize($DOC_ROOT_FROM.$path_from);
				$quota = new CDiskQuota();
				if (!$quota->checkDiskQuota(array("FILE_SIZE"=>$size)))
					return $quota->LAST_ERROR;
			}
			//************************** Quota **************************//

			// Copy file
			if(DEBUG_FILE_MAN)echo "copy(".$DOC_ROOT_FROM.$path_from.",".$DOC_ROOT_TO.$path_to.");<br>";
			@copy($DOC_ROOT_FROM.$path_from, $DOC_ROOT_TO.$path_to);

			//************************** Quota **************************//
			if(COption::GetOptionInt("main", "disk_space") > 0)
			{
				$quota->updateDiskQuota("file", $size, "copy");
			}
			//************************** Quota **************************//

			if(CModule::IncludeModule("search"))
			{
				$site = CSite::GetSiteByFullPath($DOC_ROOT_TO.$path_to);
				CSearch::ReIndexFile(Array($site_to, $path_to), $site);
			}

			if($bDeleteAfterCopy) // If was command "delete after copy"?
				$strWarning .= CFileMan::DeleteFile(Array($site_from, $path_from));
			
			return $strWarning;
		}

		//запросим содержимое папки
		if($handle = @opendir($DOC_ROOT_FROM.$path_from))
		{
			while(($file = readdir($handle)) !== false)
			{
				if($file == "." || $file == "..") continue;

				if(is_dir($DOC_ROOT_FROM.$path_from."/".$file)) //если это "подпапка"
				{
					//уходим в рекурсию
					$strWarning .= CFileMan::CopyEx(Array($site_from, $path_from."/".$file), Array($site_to, $path_to."/".$file), $bDeleteAfterCopy);
					//вернулись из рекурсии - внутри нашей подпапки все в порядке
					//if($bDeleteAfterCopy) //нужно удалить эту подпапку
					//	$strWarning .= CFileMan::DeleteDir($path_from."/".$file);
				}
				else //это "подфайл" :-)
				{
					if($file == ".access.php") continue;
					//проверим можно ли писать "туда"
					if($APPLICATION->GetFileAccessPermission(Array($site_to, $path_to."/".$file))<"W")
						$strWarning .= GetMessage("FILEMAN_FILEMAN_FILE_WRITE_DENY")." \"".$path_to."/".$file."\".\n";
					//проверим можно ли читать "отсюда"
					elseif($APPLICATION->GetFileAccessPermission(Array($site_from, $path_from."/".$file))<"W")
						$strWarning .= GetMessage("FILEMAN_FILEMAN_FILE_READ_DENY")." \"".$path_from."/".$file."\".\n";
					else
					{
						if(file_exists($DOC_ROOT_TO.$path_to."/".$file))
							$strWarning .= GetMessage("FILEMAN_FILEMAN_FILE_WITH_NAME")." \"".$path_to."/".$file."\" ".GetMessage("FILEMAN_FILEMAN_ALREADY_EXISTS")."!\n";
						else
						{
							//если здесь - значит можно копировать
							$APPLICATION->CopyFileAccessPermission(Array($site_from, $path_from."/".$file), Array($site_to, $path_to."/".$file));

							if(DEBUG_FILE_MAN)echo "copy(".$DOC_ROOT_FROM.$path_from."/".$file.",".$DOC_ROOT_TO.$path_to."/".$file.");<br>";
							copy($DOC_ROOT_FROM.$path_from."/".$file, $DOC_ROOT_TO.$path_to."/".$file);

							//************************** Quota **************************//
								if(COption::GetOptionInt("main", "disk_space") > 0)
								{
									$quota = new CDiskQuota();
									$quota->updateDiskQuota("file", filesize($DOC_ROOT_TO.$path_to."/".$file), "copy");
								}
							//************************** Quota **************************//

							if(CModule::IncludeModule("search"))
							{
								$site = CSite::GetSiteByFullPath($DOC_ROOT_TO, $path_to."/".$file);
								CSearch::ReindexFile($path_to."/".$file, $site);
							}

							if($bDeleteAfterCopy)
							{
								$strWarning .= CFileMan::DeleteFile(Array($site_from, $path_from."/".$file));
							}
						}
					}
				}
			}
		}
		@closedir($handle);

		//первоначальную нашу папочку тоже может быть нужно удалить
		if($bDeleteAfterCopy)
			$strWarning .= CFileMan::DeleteDir(Array($site_from, $path_from));

		return $strWarning;
	}

	function GetAllDirList(&$arDirs, $arFilter=Array(), $site=false)
	{
		CFileMan::DirsRecursive(Array($site, ""), $arDirs, $arFilter, 0);
	}

	function DirsRecursive($path, &$arDirs, $arFilter=Array(), $depth=0)
	{
		$depth++;
		CFileMan::GetDirList($path, $arDirsTmp, $arFiles, $arFilter, Array("name"=>"asc"), "D");
		for($i=0; $i<count($arDirsTmp); $i++)
		{
			$arDir = $arDirsTmp[$i];
			$arDir["DEPTH_LEVEL"] = $depth;
			$arDirs[] = $arDir;
			CFileMan::DirsRecursive($arDir["ABS_PATH"], $arDirs, $arFilter, $depth);
		}
	}

	function CompareFiles($f1, $f2, $sort=Array())
	{
		return CompareFiles($f1, $f2, $sort);
	}

	function GetDirList($path, &$arDirs, &$arFiles, $arFilter=Array(), $sort=Array(), $type="DF", $bLogical=false, $task_mode=false)
	{
		return GetDirList($path, $arDirs, $arFiles, $arFilter, $sort, $type, $bLogical,$task_mode);
	}

	function __CheckSite($site)
	{
		if($site!==false)
		{
			if(strlen($site)>0)
			{
				$res = CSite::GetByID($site);
				if(!($arSite = $res->Fetch()))
					$site = false;
			}
			else
				$site = false;
		}
		return $site;
	}

	function ParsePath($path, $bLast=false,  $url=false, $param="", $bLogical = false)
	{
		return ParsePath($path, $bLast, $url, $param, $bLogical);
	}

	function GetFileExtension($path)
	{
		return GetFileExtension($path);
	}

	function GetFileType($path)
	{
		return GetFileType($path);
	}

	function GetStrFileSize($size)
	{
		if ($size < 1024)
			return $size.' '.GetMessage('BYTE');

		$size = round($size/1024);
		if ($size < 1024)
			return $size.' K'.GetMessage('BYTE');

		$size = round($size/1024);
		return $size.' M'.GetMessage('BYTE');
	}

	function GetFileTypeEx($fileName)
	{
		global $arFilemanPredifinedFileTypes, $arFilemanPredifinedFileTypesR;

		$fileExt = GetFileExtension(strtolower($fileName));

		if (!isset($arFilemanPredifinedFileTypesR) || !is_array($arFilemanPredifinedFileTypesR))
		{
			foreach ($arFilemanPredifinedFileTypes as $key => $value)
				foreach ($value["exts"] as $ext)
					$arFilemanPredifinedFileTypesR[$ext] = $key;
		}

		if (isset($arFilemanPredifinedFileTypesR[$fileExt]))
			return $arFilemanPredifinedFileTypesR[$fileExt];

		return "file";
	}

	function EscapePHPString($str)
	{
		return EscapePHPString($str);
	}

	function UnEscapePHPString($str)
	{
		return UnEscapePHPString($str);
	}

	function AddHTMLEditorFrame(
				$strTextFieldName,
				$strTextValue,
				$strTextTypeFieldName,
				$strTextTypeValue,
				$arSize = Array("height"=>350),
				$CONVERT_FOR_WORKFLOW="N",
				$WORKFLOW_DOCUMENT_ID=0,
				$NEW_DOCUMENT_PATH="",
				$textarea_field="",
				$site = false,
				$bWithoutPHP = true,
				$arTaskbars = false,
				$arAdditionalParams = Array()
			)
	{
		global $htmled, $usehtmled;
		$usecookie = (!isset($arAdditionalParams['dontusecookie'])) ? true : !$arAdditionalParams['dontusecookie'];
		if (!$usecookie)
			$htmled = (isset($usehtmled) && $usehtmled == 'Y') ? 'Y' : 'N';

		if(is_array($arSize))
			$iHeight = $arSize["height"];
		else
			$iHeight = $arSize;

		$s = '
			<script>
			ons = document.getElementById("'.$strTextFieldName.'").form.onsubmit;

			if(ons)
			{
				ons = ons.toString();
				var fpos = ons.indexOf("function");
				var fname = ons.substring(fpos+9);
				fname = fname.substring(0, fname.indexOf("{"));
				document.write("<s"+"cript>function St'.$strTextFieldName.'(){subm'.$strTextFieldName.'();"+fname+";}</s"+"cript>");
				document.getElementById("'.$strTextFieldName.'").form.onsubmit=St'.$strTextFieldName.';
			}
			else
				document.getElementById("'.$strTextFieldName.'").form.onsubmit=subm'.$strTextFieldName.';
			</script>';

		$ext_html_editor = COption::GetOptionString("fileman", "ext_html_editor", "");
		if($ext_html_editor=="always" || ($ext_html_editor=="not_admin" && !$USER->IsAdmin()) || $ext_html_editor=="not_php" || $ext_html_editor=="not_pages")
		{
			$strTextValue = htmlspecialcharsback($strTextValue);
			include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/admin/FCKeditor/fckeditor.php");

			$oFCKeditor = new FCKeditor($strTextFieldName."_ed") ;
			$oFCKeditor->Width  = '0px' ;
			$oFCKeditor->Height = '0px' ;
			$oFCKeditor->Value = $strTextValue;
			$oFCKeditor->BasePath = '/bitrix/admin/FCKeditor/';
			$oFCKeditor->Config['AutoDetectLanguage']	= false ;
			$oFCKeditor->Config['DefaultLanguage']		= LANG;
			$oFCKeditor->Config['StylesXmlPath'] = '/bitrix/admin/fileman_fck_styles.php?lang='.LANG.'&template='.$template.'&list=y&site='.(strlen($site)<=0?LANG:$site);
			$oFCKeditor->Config['EditorAreaCSS'] = '/bitrix/admin/fileman_fck_styles.php?lang='.LANG.'&template='.$template.'&site='.(strlen($site)<=0?LANG:$site);
			$oFCKeditor->Config['LinkBrowserURL'] = '/bitrix/admin/fileman_fck_browser.php?lang='.LANG.'&site='.$site;
			$oFCKeditor->Config['LinkBrowserWindowWidth'] = '570';
			$oFCKeditor->Config['LinkBrowserWindowHeight'] = '480';
			$oFCKeditor->Config['ImageBrowserURL'] ='/bitrix/admin/fileman_fck_browser.php?lang='.LANG.'&type=image&site='.$site;
			$oFCKeditor->Config['ImageBrowserWindowWidth'] = '620';
			$oFCKeditor->Config['ImageBrowserWindowHeight'] = '480';
			$oFCKeditor->Config['FlashBrowserURL'] = '/bitrix/admin/fileman_fck_browser.php?lang='.LANG.'&type=flash&site='.$site;
			$oFCKeditor->Config['FlashBrowserWindowWidth'] = '570';
			$oFCKeditor->Config['FlashBrowserWindowHeight'] = '480';
			$oFCKeditor->Config['LinkUploadURL'] = '/bitrix/admin/fileman_fck_upload.php?lang='.LANG.'&site='.$site;
			$oFCKeditor->Config['ImageUploadURL'] = '/bitrix/admin/fileman_fck_upload.php?lang='.LANG.'&type=image&site='.$site;
			$oFCKeditor->Config['FlashUploadURL'] = '/bitrix/admin/fileman_fck_upload.php?lang='.LANG.'&type=flash&site='.$site;

			echo
				'<font class="tablefieldtext">
				'.($strTextTypeFieldName?'
					<input type="radio" onclick="cl'.$strTextFieldName.'(this)" name="'.$strTextTypeFieldName.'" id="'.$strTextTypeFieldName.'_0" disabled value="text"'.($strTextTypeValue!='html'?' checked':'').'> <label for="'.$strTextTypeFieldName.'_0">Text</label> /
					<input type="radio" onclick="cl'.$strTextFieldName.'(this)" name="'.$strTextTypeFieldName.'" id="'.$strTextTypeFieldName.'_1" disabled value="html"'.($strTextTypeValue=='html'?' checked':'').'> <label for="'.$strTextTypeFieldName.'_1">HTML</label>
				':'').'
					<input type="checkbox" id="'.$strTextFieldName.'usehtmledit" disabled onclick="cl'.$strTextFieldName.'(this);document.cookie=\'htmled=\'+(this.checked?\'Y\':\'N\')+\'; expires=Fri, 31 Dec 2019 23:59:59 GMT;\';"'.($htmled=="Y"?" checked":"").'> <label for="'.$strTextFieldName.'usehtmledit">'.GetMessage("FILEMAN_FILEMAN_USE_HTML_EDITOR").'</label>
				</font><br>
				<textarea class="typearea" style="width:100%;height:'.$iHeight.'px;" name="'.$strTextFieldName.'" id="'.$strTextFieldName.'" wrap="virtual" '.$textarea_field.'>'.$strTextValue.'</textarea>
				<script>
				function subm'.$strTextFieldName.'()
				{
					if(prev'.$strTextFieldName.'!=0)
						document.getElementById("'.$strTextFieldName.'").value = document.getElementById("'.$strTextFieldName.'_ed___Frame").contentWindow.FCK.GetXHTML();
				}
				</script>';

			$oFCKeditor->Create();

			echo
			'<script>
			var prev'.$strTextFieldName.' = 0;
			function cl'.$strTextFieldName.'(ob)
			{
				var val;
				var nn = document.getElementById("'.$strTextFieldName.'_ed___Frame");

				if('.($strTextTypeFieldName?'document.getElementById("'.$strTextTypeFieldName.'_0").checked || ':'').'!document.getElementById("'.$strTextFieldName.'usehtmledit").checked)
				{
					if(prev'.$strTextFieldName.'!=0)
					{
						prev'.$strTextFieldName.' = 0;
						document.getElementById("'.$strTextFieldName.'").value = document.getElementById("'.$strTextFieldName.'_ed___Frame").contentWindow.FCK.GetXHTML();
						document.getElementById("'.$strTextFieldName.'_ed___Frame").style.width="0px";
						document.getElementById("'.$strTextFieldName.'_ed___Frame").style.height="0px";
						document.getElementById("'.$strTextFieldName.'").style.display="block";
					}
				}
				else
				{
					if(prev'.$strTextFieldName.'==0)
					{
						prev'.$strTextFieldName.' = 1;
						nn.contentWindow.FCK.SetHTML(document.getElementById("'.$strTextFieldName.'").value);
						document.getElementById("'.$strTextFieldName.'").style.display="none";
						document.getElementById("'.$strTextFieldName.'_ed___Frame").style.width="100%";
						document.getElementById("'.$strTextFieldName.'_ed___Frame").style.height="'.$iHeight.'px";
					}
				}
			}

			function subm'.$strTextFieldName.'()
			{
				if(prev'.$strTextFieldName.'!=0)
					document.getElementById("'.$strTextFieldName.'").value = document.getElementById("'.$strTextFieldName.'_ed___Frame").contentWindow.FCK.GetXHTML();
			}

			function '.$strTextFieldName.'load()
			{
				var nn = document.getElementById("'.$strTextFieldName.'_ed___Frame");
				if(!nn.contentWindow.FCK || !nn.contentWindow.FCK.SetHTML)
				{
					setTimeout("'.$strTextFieldName.'load()", 1000);
					return;
				}

				cl'.$strTextFieldName.'();
				'.(
				$strTextTypeFieldName?'document.getElementById("'.$strTextTypeFieldName.'_0").disabled=false;
				document.getElementById("'.$strTextTypeFieldName.'_1").disabled=false;
				':'').'
				document.getElementById("'.$strTextFieldName.'usehtmledit").disabled=false;
			}
			setTimeout("'.$strTextFieldName.'load()", 1000);
			</script>';
		}
		else
		{
			if(COption::GetOptionString("fileman", "use_old_version", "N")!="Y")
			{
				$strTextValue = htmlspecialcharsback($strTextValue);
				$curHTMLEd = (($strTextTypeValue == 'html' || !$strTextTypeValue) && $htmled == "Y");
				$dontShowTA = (!isset($arAdditionalParams['dontshowta'])) ? false : $arAdditionalParams['dontshowta'];
				setEditorEventHandlers($strTextFieldName);

				?>
					<script>
						function cl<?=$strTextFieldName?>(ob)
						{
							var el = document.getElementById("<?=$strTextFieldName?>");
							var r1 = document.getElementById("<?=$strTextTypeFieldName?>_0");
							var bEd = (r1 && !r1.checked && document.getElementById("<?=$strTextFieldName?>usehtmledit").checked);
							var bEd_2 = document.getElementById("<?=$strTextFieldName?>usehtmledit").checked && (r1==null);

							if((bEd || bEd_2) && el.style.display != "none")
							{
								_start_ = function(pMainObj)
								{
									var sContent = pMainObj.PreparseHeaders(el.value);
									pMainObj.SetContent(sContent);
									pMainObj.Show(true);
									pMainObj.LoadContent();
								}

								el.style.display = "none";
								if(!el.pMainObj)
									el.pMainObj = new BXHTMLEditor("<?=$strTextFieldName?>", _start_);
								else
									_start_(el.pMainObj);

							}
							else if(!(bEd || bEd_2) && el.style.display == "none")
							{
								el.pMainObj.Show(false);
								el.pMainObj.SaveContent(true);
								el.style.display = "inline";
							}
						}
					</script>

					<font class="tablefieldtext">
					<? if($strTextTypeFieldName): ?>
						<input type="radio" onclick="cl<?=$strTextFieldName?>(this)" name="<?=$strTextTypeFieldName?>" id="<?=$strTextTypeFieldName?>_0" value="text"<? echo($strTextTypeValue!='html'?' checked':'')?>><label for="<?=$strTextTypeFieldName?>_0">Text</label> /
						<input type="radio" onclick="cl<?=$strTextFieldName?>(this)" name="<?=$strTextTypeFieldName?>" id="<?=$strTextTypeFieldName?>_1" value="html"<? echo($strTextTypeValue=='html'?' checked':'')?>> <label for="<?=$strTextTypeFieldName?>_1">HTML</label>
					<? endif;?>
					<input type="checkbox" id="<?=$strTextFieldName?>usehtmledit" name="<?=$strTextFieldName?>usehtmledit" onclick="cl<?=$strTextFieldName?>(this);<?if($usecookie):?>document.cookie='htmled='+(this.checked?'Y':'N')+'; expires=Fri, 31 Dec 2019 23:59:59 GMT;';<?endif;?>"<? echo($htmled == "Y"?" checked":"");?>><label for="<?=$strTextFieldName?>usehtmledit"><?echo GetMessage("FILEMAN_FILEMAN_USE_HTML_EDITOR");?></label>
					</font>
					<br>
					<textarea class="typearea" style="<? echo(($curHTMLEd || $dontShowTA) ? 'display:none;' : '');?>width:100%;height:<?=$iHeight?>px;" name="<?=$strTextFieldName?>" id="<?=$strTextFieldName?>" wrap="virtual" <?=$textarea_field?>><?echo htmlspecialchars($strTextValue)?></textarea>
					<?

				if ($bWithoutPHP)
					$arTaskbars = Array("BXPropertiesTaskbar", "BXSnippetsTaskbar");

				else if (!$arTaskbars)
					$arTaskbars = Array("BXPropertiesTaskbar","BXComponentsTaskbar", "BXComponents2Taskbar");


				$arParams = Array(
					"bUseOnlyDefinedStyles"=>COption::GetOptionString("fileman", "show_untitled_styles", "N")!="Y",
					"bFromTextarea" => true,
					"bDisplay"=>$curHTMLEd,
					"bWithoutPHP"=>$bWithoutPHP,
					"arTaskbars"=>$arTaskbars,
					"height"=>($iHeight<450?450:$iHeight)
					);

				$arParams['site'] = (strlen($site)<=0?LANG:$site);
				if(isset($arSize["width"]))
					$arParams["width"] = $arSize["width"];

				if (isset($arAdditionalParams))
					$arParams["arAdditionalParams"] = $arAdditionalParams;

				if (isset($arAdditionalParams['limit_php_access']))
					$arParams['limit_php_access'] = $arAdditionalParams['limit_php_access'];

				CFileman::ShowHTMLEditControl($strTextFieldName, $strTextValue, $arParams);
				$s = "";
			}
			else
			{
				if(ereg('(MSIE|Internet Explorer) ([0-9]).([0-9])+', $_SERVER['HTTP_USER_AGENT'], $version) && IntVal($version[2])>=5)
				{
					if($CONVERT_FOR_WORKFLOW=="Y" && CModule::IncludeModule("workflow"))
						$par = "&WF_CONVERT=Y&DOCUMENT_ID=". intval($WORKFLOW_DOCUMENT_ID)."&WF_PATH=".$NEW_DOCUMENT_PATH;

					echo
					'
					<font class="tablefieldtext">
					'.($strTextTypeFieldName?'
						<input type="radio" onclick="cl'.$strTextFieldName.'(this)" name="'.$strTextTypeFieldName.'" id="'.$strTextTypeFieldName.'_0" disabled value="text"'.($strTextTypeValue!='html'?' checked':'').'> <label for="'.$strTextTypeFieldName.'_0">Text</label> /
						<input type="radio" onclick="cl'.$strTextFieldName.'(this)" name="'.$strTextTypeFieldName.'" id="'.$strTextTypeFieldName.'_1" disabled value="html"'.($strTextTypeValue=='html'?' checked':'').'> <label for="'.$strTextTypeFieldName.'_1">HTML</label>
				':'').'
						<input type="checkbox" id="'.$strTextFieldName.'usehtmledit" disabled onclick="cl'.$strTextFieldName.'(this);document.cookie=\'htmled=\'+(this.checked?\'Y\':\'N\')+\'; expires=Fri, 31 Dec 2019 23:59:59 GMT;\';"'.($htmled=="Y"?" checked":"").'> <label for="'.$strTextFieldName.'usehtmledit">'.GetMessage("FILEMAN_FILEMAN_USE_HTML_EDITOR").'</label>											</font><br>
					<iframe style="width:0px;height:0px;" name="'.$strTextFieldName.'_HTML" src="/bitrix/admin/fileman_html_edit.php?lang='.LANG.'&fieldname='.$strTextFieldName. '&light=Y'.$par.'"></iframe>
					<textarea class="typearea" style="width:100%;height:'.$iHeight.'px;" name="'.$strTextFieldName.'" wrap="virtual" '.$textarea_field.'>'.$strTextValue.'</textarea>
					<script>
					var prev'.$strTextFieldName.' = 0;
					function cl'.$strTextFieldName.'(ob)
					{
						var val;
						if('.($strTextTypeFieldName?'document.all("'.$strTextTypeFieldName.'")[0].checked || ':'').'!document.all("'.$strTextFieldName.'usehtmledit").checked)
						{
							if(prev'.$strTextFieldName.'!=0)
							{
								prev'.$strTextFieldName.' = 0;
								document.all("'.$strTextFieldName.'").value = window.'.$strTextFieldName.'_HTML.GetContent();
								document.all("'.$strTextFieldName.'_HTML").style.width="0px";
								document.all("'.$strTextFieldName.'_HTML").style.height="0px";
								document.all("'.$strTextFieldName.'").style.display="block";
							}
						}
						else
						{
							if(prev'.$strTextFieldName.'==0)
							{
								prev'.$strTextFieldName.' = 1;
								window.'.$strTextFieldName.'_HTML.SetContent(
									document.all("'.$strTextFieldName.'").value);
								document.all("'.$strTextFieldName.'").style.display="none";
								document.all("'.$strTextFieldName.'_HTML").style.width="100%";
								document.all("'.$strTextFieldName.'_HTML").style.height="'.$iHeight.'px";
							}
						}
					}

					function subm'.$strTextFieldName.'()
					{
						if(prev'.$strTextFieldName.'!=0)
							document.all("'.$strTextFieldName.'").value = window.'.$strTextFieldName.'_HTML.GetContent();
					}

					function '.$strTextFieldName.'_OnLoad()
					{
						try{
							window.'.$strTextFieldName.'_HTML.tbContentElement.DOM.body.DocumentHTML= "";
						}catch(e){
							setTimeout("'.$strTextFieldName.'_OnLoad()", 300);
							return;
						}

						cl'.$strTextFieldName.'();
						'.(
						$strTextTypeFieldName?'document.all("'.$strTextTypeFieldName.'")[0].disabled=false;
						document.all("'.$strTextTypeFieldName.'")[1].disabled=false;
						':'').'
						document.all("'.$strTextFieldName.'usehtmledit").disabled=false;
					}
					</script>';
				}
				else
				{
					$s = '';
					echo '
						<font class="tablefieldtext">
							<input type="radio" name="'.$strTextTypeFieldName.'" value="text"'.($strTextTypeValue!='html'?' checked':'').' id="'.$strTextTypeFieldName.'_0"> <label for="'.$strTextTypeFieldName.'_0">Text</label> /
							<input type="radio" name="'.$strTextTypeFieldName.'" value="html"'.($strTextTypeValue=='html'?' checked':'').' id="'.$strTextTypeFieldName.'_1"> <label for="'.$strTextTypeFieldName.'_1">HTML</label>
						</font><br>
						<textarea class="typearea" style="width:100%;height:'.$iHeight.'px;" name="'.$strTextFieldName.'" wrap="virtual" '.$textarea_field.'>'.$strTextValue.'</textarea>
						';
				}
			}
		}
		echo $s;
	}

	function ShowHTMLEditControl($name, $content, $arParams=Array())
	{
		$relPath = (isset($arParams["path"])) ?	$arParams["path"] : "/";
		$site = (isset($arParams["site"])) ?	$arParams["site"] : "";
		$__path = Rel2Abs("/", $relPath);
		$site = CFileMan::__CheckSite($site);

		if (!isset($arParams["limit_php_access"]))
			$arParams["limit_php_access"] = false;

		$arParams["light_mode"] = (defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1) || (isset($arParams["light_mode"]) && $arParams["light_mode"] == 'Y');

		//$lng = CLanguage::GetByID(LANGUAGE_ID);
		//if($arRes = $lng->Fetch())
		//	$direction_rtl = ($arRes['DIRECTION'] =="N");

		$direction_rtl = false;

		if($site)
		{
			$DOC_ROOT = CSite::GetSiteDocRoot($site);
			$abs_path = $DOC_ROOT.$__path;
			if (is_file($abs_path))
			{
				$relPath = substr($relPath,0,strrpos($relPath,"/"));
				if ($relPath=="")
					$relPath = "/";
			}
		}

		static $bFirstUse;
		$site = $arParams["site"];
		$template = $arParams["templateID"];
		//Taskbars
		$arTaskbars = (isset($arParams["arTaskbars"])) ? $arParams["arTaskbars"] : Array();
		//Toolbars
		$arToolbars = (isset($arParams["arToolbars"])) ? $arParams["arToolbars"] : false;

		$arParams["use_advanced_php_parser"] = COption::GetOptionString("fileman", "use_advanced_php_parser", "Y");
		$arParams["ar_entities"] = COption::GetOptionString("fileman", "ar_entities", 'umlya,greek,other');

		if(!isset($arParams["spellCheckFirstClient"]))
			$arParams["spellCheckFirstClient"] = COption::GetOptionString("fileman", "spell_check_first_client", "Y");

		if(!isset($arParams["usePspell"]))
			$arParams["usePspell"] = COption::GetOptionString("fileman", "use_pspell", "N");

		if(!isset($arParams["useCustomSpell"]))
			$arParams["useCustomSpell"] = COption::GetOptionString("fileman", "use_custom_spell", "Y");

		$lca = COption::GetOptionString("fileman", "use_lca", "N");
		$styleList_render_style = (COption::GetOptionString("fileman", "render_styles_in_classlist", "Y") == 'Y') ? 'true' : 'false';

		$arAdditionalParams = (isset($arParams["arAdditionalParams"])) ? $arParams["arAdditionalParams"] : Array();

		if($bFirstUse!==true)
		{
			$arTemplates = Array(Array('value'=>'.default', 'name'=>GetMessage("FILEMAN_DEFTEMPL")));
			$db_site_templates = CSiteTemplate::GetList();
			while($ar_site_templates = $db_site_templates->Fetch())
				$arTemplates[] = Array('value'=>$ar_site_templates['ID'], 'name'=>$ar_site_templates['NAME']);			?>
			<script>
				var arBXTemplates = <?=JSVal($arTemplates)?>;
				var BXSite = <?=JSVal($site)?>;
				var BXLang = <?=JSVal(LANG)?>;
				var styleList_render_style = <?=$styleList_render_style?>;
				var limit_php_access = <?echo $arParams["limit_php_access"] ? 'true' : 'false';?>;
				var lca = <?echo ($lca == 'Y') ? 'true' : 'false';?>;
				var lightMode = <?echo ($arParams["light_mode"]) ? 'true' : 'false';?>;
				var rtlMode = <?echo ($direction_rtl) ? 'true' : 'false';?>;
				var spellcheck_js_v = "<?=@filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/htmleditor2/spellcheck.js')?>";
				var BX_PERSONAL_ROOT = "<?=BX_PERSONAL_ROOT?>";
			</script>
			<?if (isset($_GET['debug_mode']) && $_GET['debug_mode']=='Y'):?>
			<b style="font-size: 10px;">* * * * *  DEBUG MODE [WYSIWYG Editor: debug_mode=true] * * * * *</b>
			<script>debug_mode = true;</script>
			<?endif;?>

			<?
			$arJS = Array();
			$arCSS = Array();
			$events = GetModuleEvents("fileman", "OnBeforeHTMLEditorScriptsGet");
			while($arEvent = $events->Fetch())
			{
				$tmp = ExecuteModuleEvent($arEvent, $name, $arParams);
				if (!is_array($tmp))
					continue;

				if (is_array($tmp['JS']))
					$arJS = array_merge($arJS, $tmp['JS']);
				if (is_array($tmp['CSS']))
					$arCSS = array_merge($arCSS, $tmp['CSS']);
			}
			//$arr = Array("common.js", "controls.js", "xml.js", "parser.js", "bars.js", "editor.js", "editor_src.js", "toolbarbuttons.js", "table_operations.js");
			//if (!$arParams["light_mode"])
			//	$arr[] = "bars_ex.js";
			$arr = Array();
			// Additional JS files from event OnBeforeHtmlEditorScriptGet
			for($i = 0, $c = count($arJS); $i < $c; $i++)
			{
				$arJS[$i] = preg_replace("/[^a-zA-Z0-9_:\.]/is", "", $arJS[$i]);
				if(file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/htmleditor2/'.$arJS[$i]))
					$arr[] = $arJS[$i];
			}
			$str_taskbars = CFileman::GetHTMLEditorSettings(htmlspecialchars($name), $arParams["light_mode"], $arTaskbars);
			$str_taskbars .= '_'.CFileman::GetVersion();
			//$str_taskbars .= '_'.round(rand() * 10000).'_';
			?>
<script type="text/javascript" src="/bitrix/admin/fileman_js.php?lang=<?=LANGUAGE_ID?>&v=<?=@filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/fileman/lang/'.LANGUAGE_ID.'/admin/fileman_js.php')?>"></script>
<script type="text/javascript" src="/bitrix/admin/fileman_common_js.php?s=<?=$str_taskbars?>"></script>
			<?
			for($i = 0; $i < count($arr); $i++)
			{
				$script_filename = $arr[$i];
				?><script type="text/javascript" src="/bitrix/admin/htmleditor2/<?=$script_filename?>?v=<?=@filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/htmleditor2/'.$script_filename)?>"></script><?
			}
			?>
			<script type="text/javascript" src="/bitrix/js/main/popup_menu.js?v=<?=@filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/js/main/popup_menu.js')?>"></script>
			<link rel="stylesheet" type="text/css" href="/bitrix/admin/htmleditor2/editor.css?v=<?=@filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/htmleditor2/editor.css')?>"/>
			<?if (defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1):?>
			<script>
			for(var i = 0, l = arGlobalToolbar.length; i < l; i++)
			{
				if(arGlobalToolbar[i][1] && arGlobalToolbar[i][1].id == "Fullscreen")
				{
					arGlobalToolbar = arGlobalToolbar.slice(0, i).concat(arGlobalToolbar.slice(++i));
					break;
				}
			}
			</script>
			<?endif;
			
			for($i=0; $i<count($arCSS); $i++) // Additional CSS files from event OnBeforeHtmlEditorScriptGet
			{
				$arCSS[$i] = preg_replace("/[^a-zA-Z0-9_:\.]/is", "", $arCSS[$i]);
				if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/htmleditor2/'.$arCSS[$i]))
					continue;
				?><link rel="stylesheet" type="text/css" href="/bitrix/admin/htmleditor2/<?=$arCSS[$i]?>?v=<?=@filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/htmleditor2/'.$arCSS[$i])?>"/><?
			}

			$db_events = GetModuleEvents("fileman", "OnIncludeHTMLEditorScript");
			while($arEvent = $db_events->Fetch())
				ExecuteModuleEvent($arEvent);

			$bFirstUse = true;
		}

		$arResult = CFileman::GetAllTemplateParams($template, $site, ($arParams["bWithoutPHP"] != true),$arAdditionalParams);

		$arParams["TEMPLATE"] = $arResult;
		if($bUseOnlyDefinedStyles && !is_set($arResult, "STYLES_TITLE"))
			$bUseOnlyDefinedStyles = false;

		$strParams = str_replace('"','\\"', JSVal($arParams));
		?>
		<script>
		var relPath = "<? echo htmlspecialchars(str_replace("</script","</_script",$relPath));?>";
		var <? echo 'ar_'.htmlspecialchars($name).'_taskbars';?> = {};
		<?
		for ($k = 0; $k < count($arTaskbars); $k++)
			echo 'ar_'.htmlspecialchars($name).'_taskbars["'.$arTaskbars[$k].'"] = true;';
		if ($arToolbars !== false)
		{
			echo 'var  ar_'.htmlspecialchars($name).'_toolbars = {};';
			for ($k = 0; $k < count($arToolbars); $k++)
				echo 'ar_'.htmlspecialchars($name).'_toolbars["'.$arToolbars[$k].'"] = true;';
		}
		else
			echo 'var  ar_'.htmlspecialchars($name).'_toolbars = false;';
		?>
		var ar_<?=htmlspecialchars($name)?>_config = (<?echo _replace_br_(htmlspecialchars($strParams));?>); // editor-config
		</script>

		<table id="<? echo htmlspecialchars($name).'_object_ev';?>" cellpadding="0" cellspacing="0" width="100%" height="100%"><tr><td valign="top" height="100%">
			<div id="<? echo htmlspecialchars($name).'_object';?>"  style="width: 100%; height: 100%;">
				<table id="<?echo htmlspecialchars($name);?>_pFrame" class="bxedmainframe" cellpadding="0" cellspacing="0" width="100%" height="100%" style="display:none;">
					<tr style="height:0%; display: none;">
						<td id="<?echo htmlspecialchars($name);?>_toolBarSet0" colspan="3"></td>
					</tr>
					<tr style="height:100%">
						<td id="<?echo htmlspecialchars($name);?>_toolBarSet1" style="width:0%; display: none;">
						</td>
						<td  width="100%" vAlign="top">
							<table cellpadding="0" cellspacing="0" width="100%" height="100%">
								<tr style="height:0%; display: none;">
									<td id="<?echo htmlspecialchars($name);?>_taskBarSet0" colspan="3">
									</td>
								</tr>
								<tr>
									<td id="<?echo htmlspecialchars($name);?>_taskBarSet1" style="width:0%; display: none;"></td>
									<td id="<?echo htmlspecialchars($name);?>_cEditor" width="100%" height="100%"></td>
									<td id="<?echo htmlspecialchars($name);?>_taskBarSet2" style="width:0%; display: none;"></td>
								</tr>
								<tr style="height:0%; display: none;">
									<td id="<?echo htmlspecialchars($name);?>_taskBarSet3" colspan="3"></td>
								</tr>
							</table>
						</td>
						<td id="<?echo htmlspecialchars($name);?>_toolBarSet2" style="width:0%; display: none;"></td>
					</tr>
					<tr style="height:0%; display: none;">
						<td id="<?echo htmlspecialchars($name);?>_toolBarSet3" colspan="3"></td>
					</tr>
					<tr style="height:0%"><td colspan="3"></td></tr>
				</table>
			</div>
		</td></tr></table>

		<script>
			var el = document.getElementById("<? echo htmlspecialchars($name);?>");
			var r1 = document.getElementById("<?echo htmlspecialchars($name).'_TYPE_0';?>");
			var bEd = (r1 && !r1.checked && document.getElementById("<? echo htmlspecialchars($name).'usehtmledit';?>").checked);
			bEd = (bEd==null) ? true : bEd;
			if (!bEd)
				document.getElementById("<? echo htmlspecialchars($name).'_object';?>").style.display = "none";
		</script>
		<?
		if(!$arParams["bFromTextarea"])
			echo '<input type="hidden" name="'.htmlspecialchars($name).'" id="'.htmlspecialchars($name).'" value="'.htmlspecialchars($content).'">';
		if($arParams["bDisplay"]!==false)
		{
			setEditorEventHandlers(htmlspecialchars($name));

			?>
			<DIV id="editor_wait_window_<? echo htmlspecialchars($name);?>" class = "waitwindow"; style="position: absolute; left: 45%; top: 40%; z-index: 3000;">
			<? echo GetMessage("admin_lib_loading"); ?>
			</DIV>
			<script>
				document.getElementById("<? echo htmlspecialchars($name);?>").pMainObj  = new BXHTMLEditor("<? echo htmlspecialchars($name);?>");
			</script>
			<?
		}
	}

	function GetFileTemplates($lang = LANG, $arTemplates=Array())
	{
		return GetFileTemplates($lang, $arTemplates);
	}

	function GetTemplateContent($filename, $lang=LANG, $arTemplates=Array())
	{
		return GetTemplateContent($filename, $lang, $arTemplates);
	}

	function GetScriptFileExt()
	{
		return GetScriptFileExt();
	}

	function ParseFileContent($filesrc)
	{
		return ParseFileContent($filesrc);
	}

	function SetTitle($prolog, $title)
	{
		return SetPrologTitle($prolog, $title);
	}

	function SetProperty($prolog, $property_key, $property_val)
	{
		return SetPrologProperty($prolog, $property_key, $property_val);
	}

	function IsPHP($src)
	{
		return IsPHP($src);
	}

	function GetAllTemplateParams($templateID, $site, $findcomponent = true, $arAdditionalParams = Array())
	{
		global $APPLICATION;

		$db_templ = CSiteTemplate::GetByID($templateID);
		if(!($ar_templ = $db_templ->Fetch()))
		{
			$templateID = "";
			$db_site_templ = CSite::GetTemplateList($site);
			while($ar_site_templ = $db_site_templ->Fetch())
			{
				if(strlen($ar_site_templ["CONDITION"])<=0)
				{
					$templateID = $ar_site_templ["TEMPLATE"];
					break;
				}
			}

			if(strlen($templateID)>0)
			{
				$db_templ = CSiteTemplate::GetByID($templateID);
				$ar_templ = $db_templ->Fetch();
			}
		}

		if($ar_templ)
		{
			$arResult = Array(
				"ID" => $ar_templ["ID"],
				"NAME" => $ar_templ["NAME"]
				);

			if(is_set($ar_templ, "STYLES"))
			{
				$arResult["STYLES"] = $ar_templ["STYLES"];
				$arResult["STYLES_TITLE"] = $ar_templ["STYLES_TITLE"];
			}
		}
		else
		{
			$arResult = Array("ID" => ".default", "NAME" => GetMessage("FILEMAN_DDEF_TEMPLATE"));
			$templateID = "";
		}

		if(!is_set($arResult, "STYLES") || $arResult["STYLES"]==false)
		{
			if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/".(strlen($site)<=0?LANGUAGE_ID:$site)."/styles.css"))
			{
				$arResult["STYLES"] = $APPLICATION->GetFileContent($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/".(strlen($site)<=0?LANGUAGE_ID:$site)."/styles.css");
				$arResult["STYLES_TITLE"] = CSiteTemplate::__GetByStylesTitle($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/".(strlen($site)<=0?LANGUAGE_ID:$site)."/.styles.php");
			}
			elseif(file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/php_interface/styles.css"))
			{
				$arResult["STYLES"] = $APPLICATION->GetFileContent($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/styles.css");
				$arResult["STYLES_TITLE"] = CSiteTemplate::__GetByStylesTitle($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/.styles.php");
			}
			else
			{
				$arResult["STYLES"] = $APPLICATION->GetFileContent($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/.default/styles.css");
				$arResult["STYLES_TITLE"] = CSiteTemplate::__GetByStylesTitle($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/.default/.styles.php");
			}
		}

		if (isset($arAdditionalParams['additionalCSS']))
		{
			$additionalCSS = $arAdditionalParams['additionalCSS'];
			for ($i=0; $i<count($additionalCSS);$i++)
			{
				$css_file_path = $additionalCSS[$i];
				$arResult["STYLES"] .= "\r\n".$APPLICATION->GetFileContent($css_file_path);
			}
		}

		if(strlen($templateID)>0 && file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".$templateID."/editor.css"))
			$arResult["STYLES"] .= "\r\n".$APPLICATION->GetFileContent($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".$templateID."/editor.css");
		elseif(file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/.default/editor.css"))
			$arResult["STYLES"] .= "\r\n".$APPLICATION->GetFileContent($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/.default/editor.css");
		elseif(file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/".$site."/editor.css"))
			$arResult["STYLES"] .= "\r\n".$APPLICATION->GetFileContent($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/".(strlen($site)<=0?LANGUAGE_ID:$site)."/editor.css");
		elseif(file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/editor.css"))
			$arResult["STYLES"] .= "\r\n".$APPLICATION->GetFileContent($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/editor.css");

		return $arResult;
	}


	function GetComponents1Params($templateID)
	{
		$arCompParams = Array("FOLDERS" => Array(), "COMPONENTS" => Array());
		$arTemplateFolders = CTemplates::GetFolderList($templateID);
		foreach($arTemplateFolders as $fold=>$name)
		{
			echo '$fold = '.$fold;
			$arCompParams["FOLDERS"][] = Array("ID" => $fold, "NAME" => $name);
			$folderID = $fold;
			$arTemplates = CTemplates::GetList(Array("FOLDER"=>Array($fold)), Array(), $templateID);
			if(is_array($arTemplates))
			{
				foreach($arTemplates[$fold] as $path=>$arParams)
				{
					$arComp = array();
					if($arParams["SEPARATOR"]=="Y")
						$arComp = Array("SEPARATOR"=>true, "NAME"=>htmlspecialcharsex($arParams["NAME"]), "DESCRIPTION"=>htmlspecialcharsex($arParams["DESCRIPTION"]), "FOLDER"=>$folderID);
					else
					{
						if(strlen($arParams["ICON"])<=0 || !is_file($_SERVER["DOCUMENT_ROOT"].$arParams["ICON"]))
							$arParams["ICON"] = "/bitrix/images/fileman/htmledit/component.gif";
						if(strlen($arParams["SCRIPT"])<=0 || !is_file($_SERVER["DOCUMENT_ROOT"].$arParams["SCRIPT"]))
						$arParams["SCRIPT"] = "standart";
						$arComp["PATH"] = $path;
						$arComp["FOLDER"] = $folderID;
						$arComp["NAME"] = htmlspecialcharsex($arParams["NAME"]);
						$arComp["DESCRIPTION"] = htmlspecialcharsex($arParams["DESCRIPTION"]);
						$arComp["FULL_PATH"] = htmlspecialchars($arParams["REAL_PATH"]);
						$arComp["ICON"] = $arParams["ICON"];
						$arComp["FIELDS"] = Array();
						if(is_array($arParams["PARAMS"]))
							foreach($arParams["PARAMS"] as $k=>$v)
								$arComp["FIELDS"][$k] = $v["NAME"];
					}
					$arCompParams["COMPONENTS"][] = $arComp;
				}
			}
		}
		return $arCompParams;
	}


	function __CheckOnAllowedComponents($str)
	{
		$allowed_components = trim(COption::GetOptionString('fileman', "~allowed_components", ''));
		if ($allowed_components == '')
			return true;

		$arAC = explode("\n",$allowed_components);
		$arAC = array_unique($arAC);
		$arAllowedComponents = Array();
		foreach ($arAC as $f)
		{
			if (trim($f) == '')
				continue;

			$f = preg_replace("/\s/is", "", $f);
			$f = preg_replace("/\./is", "\\.", $f);
			$f = preg_replace("/\*/is", ".*", $f);
			$arAllowedComponents[] = '/^'.$f.'$/';
		}
		if (count($arAllowedComponents) == 0)
			return true;
		$comp_RE = '/\$application->includecomponent\(\s*?(.*?),/i';
		preg_match_all($comp_RE, $str, $matches);

		for ($i = 0, $l = count($matches[1]); $i < $l; $i++)
		{
			$name = trim($matches[1][$i]);
			$er_name = $name;
			$name = substr($name, 1, -1);
			$bx = 'bitrix:';
			$bxlen = strlen($bx);
			if (substr($name, 0, $bxlen) != $bx)
				return $er_name;
			$name = substr($name, $bxlen);
			for ($j = 0, $c = count($arAllowedComponents); $j < $c; $j++)
				if (preg_match($arAllowedComponents[$j], $name))
					continue 2;

			return $er_name;
		}
		return true;
	}

	function CheckOnAllowedComponents($str)
	{
		if (($r = CFileMan::__CheckOnAllowedComponents($str)) !== true)
		{
			$GLOBALS['APPLICATION']->ThrowException(GetMessage("FILEMAN_UNALLOWED_COMPONENTS", Array("#BAD_COMPONENT#" => $r)), "UNALLOWED_COMPONENTS");
			return false;
		}
		return true;
	}

	function GetHTMLEditorSettings($edname, $lightMode, $arTaskbars)
	{
		?>
		<script>try{<?
		$str_res = '';
		if (!$lightMode)
		{
			//Get toolbar settings
			$toolbar_settings = stripslashes(CUserOptions::GetOption("fileman", "toolbar_settings_".$edname));
			//echo 'alert(" ######### '.$toolbar_settings.' ######## ");';
			$rs_tlbrs = stripslashes(CUserOptions::GetOption("fileman", "rs_toolbar_".$edname));
			if ($toolbar_settings)
			{
				?>window.arToolbarSettings = []; window.RS_toolbars = <?echo($rs_tlbrs == "N" ? "false" : "true");?>;<?
				$res = explode("||", $toolbar_settings);
				for ($i = 0, $len = count($res); $i < $len; $i++)
				{
					$tmp = explode(":", $res[$i]);
					$tlbrname = $tmp[0];
					$tmp2 = explode(",", $tmp[1]);
					$show = $tmp2[0];
					$docked = $tmp2[1];
					$arPos = explode(";", substr($tmp2[2], 1, -1));
?>
var _ar = [];
_ar.show = <?echo($show == 'true' ? 'true' : 'false');?>;
_ar.docked = <?echo($docked=='true' ? 'true' : 'false');?>;
<?if ($docked=='true'):?>
	_ar.position = [<?echo$arPos[0];?>,<?echo$arPos[1];?>,<?echo$arPos[2];?>];
<?else:?>
	_ar.position = {
		x : '<?echo(substr($arPos[0],-2)=="px" ? substr($arPos[0],0,-2) : $arPos[0]);?>',
		y : '<?echo(substr($arPos[1],-2)=="px" ? substr($arPos[1],0,-2) : $arPos[1]);?>'
	};
<?endif;?>
window.arToolbarSettings["<?=$tlbrname?>"] = _ar;
<?
				}
			}
			$str_res = 'em'; // extended mode
		}

		//Get taskbar settings
		$taskbar_settings = stripslashes(CUserOptions::GetOption("fileman", "taskbar_settings_".$edname));
		$rs_tskbrs = stripslashes(CUserOptions::GetOption("fileman", "rs_taskbar_".$edname));
		if ($taskbar_settings)
		{
			?>window.arTaskbarSettings = [];window.RS_taskbars = <?echo($rs_tskbrs=="N" ? "false" : "true");?>;<?
			$res = explode("||", $taskbar_settings);
			for ($i = 0, $len = count($res); $i < $len; $i++)
			{
				$tmp = explode(":", $res[$i]);
				$tskbrname = $tmp[0];
				if (!in_array($tskbrname, $arTaskbars))
					continue;
				$tmp2 = explode(",", $tmp[1]);
				$show = $tmp2[0];
				$docked = $tmp2[1];
				$arPos = explode(";",substr($tmp2[2],1,-1));
				if ($show == 'true')
				{
					switch ($tskbrname)
					{
						case 'BXSnippetsTaskbar':
							$str_res .= 's';
							break;
						case 'BXComponents2Taskbar':
							$str_res .= 'c2';
							break;
						case 'BXComponentsTaskbar':
							$str_res .= 'c1';
							break;
					}
				}
?>
var _ar = [];
_ar.show = <?echo($show == 'true' ? 'true' : 'false');?>;
_ar.docked = <?echo($docked == 'true' ? 'true' : 'false');?>;
<?if ($docked=='true'):?>
	_ar.position = [<?=$arPos[0];?>,<?=$arPos[1];?>,<?=$arPos[2];?>];
<?else:?>
	_ar.position = {
		x : '<?echo substr($arPos[0],-2)=="px" ? substr($arPos[0],0,-2) : $arPos[0];?>',
		y : '<?echo substr($arPos[1],-2)=="px" ? substr($arPos[1],0,-2) : $arPos[1];?>'
	};
<?endif;?>
window.arTaskbarSettings["<?=$tskbrname;?>"] = _ar;
<?
			}
		}
		else
		{
			if (in_array('BXSnippetsTaskbar', $arTaskbars))
				$str_res .= 's';

			if (in_array('BXComponents2Taskbar', $arTaskbars))
				$str_res .= 'c2';
		}

		//Get taskbarset settings
		$taskbarset = stripslashes(CUserOptions::GetOption("fileman", "taskbarset_settings_".$edname));
		if ($taskbarset)
		{
			//getTaskbarsetSettings($taskbarset);
			?>window.arTBSetsSettings = [];<?
			$res = explode("||", $taskbarset);
			for ($i = 0, $len = count($res); $i < $len; $i++)
			{
				$tmp = explode(":", $res[$i]);
				$num = $tmp[0];
				$tmp2 = explode(",", $tmp[1]);
				$show = $tmp2[0];
				$width = $tmp2[1];
				$height = $tmp2[2];

?>window.arTBSetsSettings["<?=$num;?>"] = {show : <?echo($show=='true' ? 'true' : 'false');?>, width : '<?=$width;?>', height : '<?=$height;?>'};<?
			}
		}

		$show_tooltips = stripslashes(CUserOptions::GetOption("fileman", "show_tooltips".$edname, "Y"));
		$visualEffects = stripslashes(CUserOptions::GetOption("fileman", "visual_effects".$edname, "Y"));

		?>
	window.__show_tooltips = <?echo ($tooltips == "N" ? "false" : "true");?>;
	window.__visual_effects = <?echo ($visualEffects == "N" ? "false" : "true");?>;
}catch(e){}</script><?

		return $str_res;
	}

	function CheckFileName($str)
	{
		if (preg_match("/[^a-zA-Z0-9\s!\$&\(\)\[\]\{\}\-\.;=@\^_\~]/is", $str))
			return GetMessage("FILEMAN_NAME_ERROR");
		return true;
	}


	function GetPropstypes($site="")
	{
		$defRes = Array(
			'description' => GetMessage("FILEMAN_OPTION_PROPS_DESCR"),
			'keywords' => GetMessage("FILEMAN_OPTION_PROPS_KEYW")
		);
		$res = COption::GetOptionString('fileman', "propstypes", addslashes(serialize($defRes)), $site);
		$res = unserialize(stripslashes($res));
		return $res;
	}


	function SetPropstypes($arPT = Array(), $desc = false, $site = "")
	{
		COption::SetOptionString('fileman', "propstypes", addslashes(serialize($arPT)), $desc, $site);
	}
}


function JSEncode($str)
{
	return addcslashes(
                str_replace("'", "\'",
                        str_replace("\\", "\\\\",
                                $str
                        )
                ),
        "\0..\32");
}

function is_array_assoc($arr)
{
	$i = 0;
	foreach($arr as $k=>$val)
	{
		if("".$k!="".$i)
		{
			return true;
		}
		$i++;
	}
	return false;
}


function JSVal($var)
{
	if(is_array($var))
	{
		$res = array();
		if(is_array_assoc($var))
		{
			foreach($var as $key=>$val)
			{
				$res[] = "'".$key."': ".JSVal($val);
			}
			return "{".join(", ", $res)."}";
		}

		for($i=0; $i<count($var); $i++)
			$res[] = JSVal($var[$i]);
		return '['.join(', ', $res).']';
	}
	elseif(is_bool($var))
	{
		if($var)
			return "true";
		return "false";
	}

	return "'".JSEncode($var)."'";
}

function setEditorEventHandlers($name)
{
	?>
	<script>

	function onContextMenu_<?echo $name;?>(e){GLOBAL_pMainObj['<?echo $name;?>'].OnContextMenu(e);}
	function onClick_<?echo $name;?>(e){GLOBAL_pMainObj['<?echo $name;?>'].OnClick(e);}
	function onMouseUp_<?echo $name;?>(e){GLOBAL_pMainObj['<?echo $name;?>'].OnMouseUp(e);}
	function onDragDrop_<?echo $name;?>(e){GLOBAL_pMainObj['<?echo $name;?>'].OnDragDrop(e);}
	function onKeyPress_<?echo $name;?>(e){GLOBAL_pMainObj['<?echo $name;?>'].OnKeyPress(e);}
	function onKeyDown_<?echo $name;?>(e){GLOBAL_pMainObj['<?echo $name;?>'].OnKeyDown(e);}
	function onPaste_<?echo $name;?>(e){GLOBAL_pMainObj['<?echo $name;?>'].OnPaste(e);}

	function OnSubmit_<?echo $name;?>(e){GLOBAL_pMainObj['<?echo $name;?>'].onSubmit(e);}

	function OnDispatcherEvent_pDocument_<?echo $name;?>(e){pBXEventDispatcher.OnEvent(GLOBAL_pMainObj['<?echo $name;?>'].pDocument, e);}
	function OnDispatcherEvent_pEditorDocument_<?echo $name;?>(e){pBXEventDispatcher.OnEvent(GLOBAL_pMainObj['<?echo $name?>'].pEditorDocument, e);}
	</script>
	<?
}

function _replace_br_($str)
{
	return $str;
	$pos2 = strpos(strtolower($str), "\n");
	if ($pos2!==FALSE)
	{
		$str = str_replace("\r"," ",$str);
		$str = str_replace("\n"," ",$str);
		$str = str_replace("\\r"," ",$str);
		$str = str_replace("\\n"," ",$str);
	}
	return $str;
}
?>