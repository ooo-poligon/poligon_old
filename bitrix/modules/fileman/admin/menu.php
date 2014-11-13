<?
IncludeModuleLangFile(__FILE__);
if(!method_exists($USER, "CanDoOperation") || !$USER->CanDoOperation('fileman_view_file_structure'))
	return false;

if(!function_exists("__fileman_mnu_gen"))
{
	function __fileman_fmnu_fldr_cmp($a, $b)
	{
		return strcmp(strtoupper($a["sSectionName"]), strtoupper($b["sSectionName"]));
	}

	function __fileman_mnu_gen($bLogical, $bFullList, $site, $path, $sShowOnly, $arSiteDirs=Array(), $bCountOnly = false, $arSitesDR_=Array())
	{
		global $APPLICATION, $USER, $DB, $MESS;
		global $__tmppath;
		global $_fileman_menu_dist_dr;
		$aMenu = Array();
		$path = preg_replace("'[\\/]+'", "/", $path);

		if(!$bCountOnly && substr($sShowOnly, 0, strlen($path)) != $path)
			return Array();

		$arFldrs = Array();
		$DOC_ROOT = CSite::GetSiteDocRoot($site);
		$handle  = @opendir($DOC_ROOT.$path);
		while(false !== ($file = @readdir($handle)))
		{
			if($file == "." || $file == ".." || !is_dir($DOC_ROOT.$path."/".$file))
				continue;

			if($bLogical && $arSiteDirs[$path.'/'.$file])
				continue;

			if(!$bCountOnly && !$bFullList && $sShowOnly!=$path && substr($sShowOnly, 0, strlen($path.'/'.$file)) != $path.'/'.$file)
				continue;

			if(!$USER->CanDoFileOperation('fm_view_file',Array($site, $path."/".$file)) ||
			!$USER->CanDoFileOperation('fm_view_listing',Array($site, $path."/".$file)))
				continue;

			if($bLogical)
			{
				if(!file_exists($DOC_ROOT.$path."/".$file."/.section.php"))
					continue;

				$sSectionName = "";
				include($DOC_ROOT.$path."/".$file."/.section.php");
				if(strlen($sSectionName) <= 0)
					$sSectionName = GetMessage("FILEMAN_MNU_WN");
			}
			else
				$sSectionName = $file;

			$arFldrs[] = Array("sSectionName"=>$sSectionName, "file"=>$file);
		}

		usort($arFldrs, "__fileman_fmnu_fldr_cmp");

		for($i=0; $i<count($arFldrs); $i++)
		{
			extract($arFldrs[$i]);

			if($bCountOnly)
				return Array('');

			$dynamic = true;
			if($sShowOnly==$path || $bFullList)
			{
				$items = __fileman_mnu_gen($bLogical, $bFullList, $site, $path.'/'.$file, '', $arSiteDirs, true, $arSitesDR_);
				if(count($items)<=0)
					$dynamic = false;
			}

			$site_ = $site;
			$addUrl = "path=".urlencode($path.'/'.$file);
			$addUrl .= "&site=".$site_;
			if ($bLogical)
			{
				$addUrl .= "&logical=Y";
				if (count($arSitesDR_)>1)
				{
					$site_ = $site;
					foreach($arSitesDR_ as $k=>$s)
					{
						if ($k == substr($DOC_ROOT.$path.'/'.$file,0,strlen($k)))
							$site_ = $s;
					}
				}
			}

			$more_urls = Array(
				"fileman_admin.php?".$addUrl,
				"fileman_access.php?".$addUrl,
				"fileman_file_upload.php?".$addUrl,
				"fileman_html_edit.php?".$addUrl,
				"fileman_file_edit.php?".$addUrl,
				"fileman_fck_edit.php?".$addUrl,
				"fileman_folder.php?".$addUrl,
				"fileman_menu_edit.php?".$addUrl,
				"fileman_newfolder.php?".$addUrl,
				"fileman_rename.php?".$addUrl,
			);

			if($__tmppath == $path.'/'.$file && ((!$bLogical && $_REQUEST["logical"]!="Y") || ($bLogical && $_REQUEST["logical"]=="Y")))
			{
				$more_urls[] = "fileman_html_edit.php";
				$more_urls[] = "fileman_file_view.php";
				$more_urls[] = "fileman_file_edit.php";
				$more_urls[] = "fileman_fck_edit.php";
			}

			$aMenu[] =
				array(
					"text" => $sSectionName,
					"url" => "fileman_admin.php?lang=".LANG."&amp;".htmlspecialchars($addUrl),
					"dynamic"=>$dynamic,
					"icon"=>"fileman_menu_icon_sections",
					"skip_chain"=>true,
					"module_id"=>"fileman",
					"more_url" => $more_urls,
					"items_id" => ($bLogical ? "menu_fileman_site_".$site."_".$path."/".$file : "menu_fileman_file_".$site."_".$path."/".$file),
					"title" => $sSectionName." (".$path.'/'.$file.")",
					"items" => __fileman_mnu_gen($bLogical, $bFullList, $site, $path.'/'.$file, $sShowOnly, $arSiteDirs, false, $arSitesDR_)
				);
		}

		return $aMenu;
	}

	function __add_site_logical_structure($arSites, $oMenu, $hide_physical_struc = false)
	{
		$sShowOnly = false;
		$bFullList = false;
		if(method_exists($oMenu, "IsSectionActive") && $oMenu->IsSectionActive("menu_fileman_site_".$arSites["ID"]."_"))
			$sShowOnly = rtrim($arSites["DIR"], "/");
		if(isset($_REQUEST['admin_mnu_menu_id']))
		{
			if($_REQUEST['admin_mnu_menu_id']=="menu_fileman_site_".$arSites["ID"]."_")
				$sShowOnly = rtrim($arSites["DIR"], "/");
			elseif(substr($_REQUEST['admin_mnu_menu_id'], 0, strlen("menu_fileman_site_".$arSites["ID"]."_"))=="menu_fileman_site_".$arSites["ID"]."_")
				$sShowOnly = substr($_REQUEST['admin_mnu_menu_id'], strlen("menu_fileman_site_".$arSites["ID"]."_"));
		}
		elseif(isset($_REQUEST['path']))
		{
			if($arSites["ID"]==$site)
			{
				$sShowOnly = rtrim($_REQUEST['path'], "/");
				$bFullList = true;
			}
		}

		$SITE_DIR = rtrim($arSites["DIR"], "/");

		if ($hide_physical_struc)
		{
			return __fileman_mnu_gen(true, $bFullList, $arSites["ID"], $SITE_DIR, $sShowOnly, $arSiteDirs);
		}

		return array(
			"text" => $arSites["NAME"],
			"url" => "fileman_admin.php?lang=".LANG.'&amp;site='.$arSites["ID"].'&amp;logical=Y&amp;path='.urlencode($arSites["DIR"]),
			"dynamic"=>true,
			"module_id"=>"fileman",
			"more_url" => array(
				"fileman_admin.php?lang=".LANG.'&site='.$arSites["ID"].'&logical=Y&path='.urlencode($arSites["DIR"]),
				"fileman_access.php?site=".$arSites["ID"].'&logical=Y',
				"fileman_admin.php?logical=Y&site=".$arSites["ID"],
				"fileman_dialog.php?site=".$arSites["ID"].'&logical=Y',
				"fileman_dir_list.php?site=".$arSites["ID"].'&logical=Y',
				"fileman_file_download.php?site=".$arSites["ID"].'&logical=Y',
				"fileman_file_edit.php?site=".$arSites["ID"].'&logical=Y',
				"fileman_fck_edit.php?site=".$arSites["ID"].'&logical=Y',
				"fileman_file_list.php?site=".$arSites["ID"].'&logical=Y',
				"fileman_file_upload.php?site=".$arSites["ID"].'&logical=Y',
				"fileman_file_view.php?site=".$arSites["ID"].'&logical=Y',
				"fileman_folder.php?site=".$arSites["ID"].'&logical=Y',
				"fileman_getimage.php?site=".$arSites["ID"].'&logical=Y',
				"fileman_html_edit.php?site=".$arSites["ID"].'&logical=Y',
				"fileman_menu_edit.php?site=".$arSites["ID"].'&logical=Y',
				"fileman_newfolder.php?site=".$arSites["ID"].'&logical=Y',
				"fileman_rename.php?site=".$arSites["ID"].'&logical=Y',
			),
			"items_id" => "menu_fileman_site_".$arSites["ID"]."_",
			"title" => GetMessage("FILEMAN_MNU_STRUC").": ".$arSites["NAME"],
			"items" => ($sShowOnly!==false?__fileman_mnu_gen(true, $bFullList, $arSites["ID"], $SITE_DIR, $sShowOnly, $arSiteDirs) : Array()),
		);
	}
}

global $site;
global $_fileman_menu_dist_dr;
global $__tmppath;

$__tmppath = $_REQUEST['path'];
switch($GLOBALS["APPLICATION"]->GetCurPage())
{
	case "/bitrix/admin/fileman_file_edit.php":
	case "/bitrix/admin/fileman_fck_edit.php":
	case "/bitrix/admin/fileman_file_view.php":
	case "/bitrix/admin/fileman_html_edit.php":
		if($_REQUEST['path'] && $_REQUEST['new']!='y')
			$__tmppath = dirname($_REQUEST['path']);
		break;
}

$aMenu = array(
	"parent_menu" => "global_menu_content",
	"section" => "fileman",
	"sort" => 100,
	"text" => GetMessage("FM_MENU_TITLE"),
	"title" => GetMessage("FM_MENU_DESC"),
	"url" => "fileman_index.php?lang=".LANG,
	"icon" => "fileman_menu_icon",
	"page_icon" => "fileman_page_icon",
	"items_id" => "menu_fileman",
	"more_url" => array(
		"fileman_admin.php",
		"fileman_file_edit.php",
		"fileman_file_view.php",
		"fileman_folder.php",
		"fileman_html_edit.php",
		"fileman_fck_edit.php",
		"fileman_menu_edit.php",
		"fileman_newfolder.php",
		"fileman_rename.php"
	),
	"items" => array()
);

$arSiteDirs = Array();
$arSites = Array();
$arSitesDR = Array();
$arSitesDR_ = Array();
$dbSitesList = CSite::GetList($b = "SORT", $o = "asc");
while($arSites = $dbSitesList->GetNext())
{
	$arSite[] = $arSites;
	$arSiteDirs[rtrim($arSites["DIR"], "/")] = true;
	$arSitesDR_[$arSites["ABS_DOC_ROOT"].rtrim($arSites["DIR"], "/")] = $arSites["ID"];
	if (!isset($arSitesDR[$arSites["ABS_DOC_ROOT"]]))
		$arSitesDR[$arSites["ABS_DOC_ROOT"]] = $arSites["ID"];
}

$_fileman_menu_dist_dr = (count($arSitesDR)>1);
$hide_physical_struc = COption::GetOptionString("fileman", "hide_physical_struc", false);
$site_count = count($arSite);

if ($hide_physical_struc && $site_count==1)
{
	$aMenu["items"] = __add_site_logical_structure($arSite[$i], $this, true);
	return $aMenu;
}

for($i = 0; $i < $site_count; $i++)
	$aMenu["items"][] = __add_site_logical_structure($arSite[$i], $this);

if ($hide_physical_struc)
	return $aMenu;

$addUrl = "path=".urlencode($path.'/'.$file);
if(count($arSitesDR) > 1)
{
	$arSMenu = Array();
	foreach($arSitesDR as $k=>$site_id)
	{
		$sShowOnly = false;
		if(method_exists($this, "IsSectionActive") && $this->IsSectionActive("menu_fileman_file_".$site_id."_"))
			$sShowOnly = "";
		if(isset($_REQUEST['admin_mnu_menu_id']))
		{
			if($_REQUEST['admin_mnu_menu_id']=="menu_fileman_file_".$site_id."_")
				$sShowOnly = "";
			elseif(substr($_REQUEST['admin_mnu_menu_id'], 0, strlen("menu_fileman_file_".$site_id."_"))=="menu_fileman_file_".$site_id."_")
				$sShowOnly = substr($_REQUEST['admin_mnu_menu_id'], strlen("menu_fileman_file_".$site_id."_"));
		}
		elseif(isset($_REQUEST['path']))
		{
			if($site_id==$site)
			{
				$sShowOnly = rtrim($_REQUEST['path'], "/");
				$bFullList = true;
			}
		}
		$maxl = 18;
		$arSMenu[] = array(
				"text" => (strlen($k)<=$maxl ? $k : substr($k, 0, 3).'...'.substr($k, -($maxl-6))),
				"url" => "fileman_admin.php?lang=".LANG.'&amp;site='.$site_id.'&amp;'.$addUrl,
				"more_url" => array(
					"fileman_admin.php?lang=".LANG.'&site='.$site_id.'&'.$addUrl,
					"fileman_access.php?site=".$site_id.'&'.$addUrl,
					"fileman_admin.php?site=".$site_id.'&'.$addUrl,
					"fileman_dialog.php?site=".$site_id.'&'.$addUrl,
					"fileman_dir_list.php?site=".$site_id.'&'.$addUrl,
					"fileman_file_download.php?site=".$site_id.'&'.$addUrl,
					"fileman_file_edit.php?site=".$site_id.'&'.$addUrl,
					"fileman_file_list.php?site=".$site_id.'&'.$addUrl,
					"fileman_file_upload.php?site=".$site_id.'&'.$addUrl,
					"fileman_file_view.php?site=".$site_id.'&'.$addUrl,
					"fileman_folder.php?site=".$site_id.'&'.$addUrl,
					"fileman_getimage.php?site=".$site_id.'&'.$addUrl,
					"fileman_html_edit.php?site=".$site_id.'&'.$addUrl,
					"fileman_fck_edit.php?site=".$site_id.'&'.$addUrl,
					"fileman_menu_edit.php?site=".$site_id.'&'.$addUrl,
					"fileman_newfolder.php?site=".$site_id.'&'.$addUrl,
					"fileman_rename.php?site=".$site_id.'&'.$addUrl
				),
				"dynamic" => true,
				"items_id" => "menu_fileman_file_".$site_id."_",
				"icon"=>"fileman_menu_icon_sections",
				"page_icon"=>"fileman_menu_page_icon_sections",
				"module_id" => "fileman",
				"title" => $k,
				"items" => ($sShowOnly!==false?__fileman_mnu_gen(false, $bFullList, $site_id, "", $sShowOnly) : Array()),
			);
	}
	
	$aMenu["items"][] = array(
		"text" => GetMessage("FILEMAN_MNU_F_AND_F"),
		"url" => "fileman_doc_roots.php?lang=".LANG,
		"items_id" => "menu_fileman_file_",
		"module_id"=> "fileman",
		"more_url" => array(
			'fileman_admin.php?lang='.LANG,
			"fileman_admin.php?lang=ru&path=%2F",
			"fileman_admin.php",
			"fileman_file_edit.php",
			"fileman_file_view.php",
			"fileman_folder.php",
			"fileman_html_edit.php",
			"fileman_fck_edit.php",
			"fileman_menu_edit.php",
			"fileman_newfolder.php",
			"fileman_rename.php"
		),
		"title" => GetMessage("FILEMAN_MNU_F_AND_F_TITLE"),
		"items" => $arSMenu
	);
}
else
{
	list($dr, $site_id) = each($arSitesDR);

	$sShowOnly = false;
	if(isset($_REQUEST['admin_mnu_menu_id']))
	{
		if($_REQUEST['admin_mnu_menu_id']=="menu_fileman_file_".$site_id."_")
			$sShowOnly = "";
		elseif(substr($_REQUEST['admin_mnu_menu_id'], 0, strlen("menu_fileman_file_".$site_id."_"))=="menu_fileman_file_".$site_id."_")
			$sShowOnly = substr($_REQUEST['admin_mnu_menu_id'], strlen("menu_fileman_file_".$site_id."_"));
	}
	elseif(isset($_REQUEST['path']))
	{
		$sShowOnly = rtrim($_REQUEST['path'], "/");
		$bFullList = true;
	}

	$aMenu["items"][] = array(
		"text" => GetMessage("FILEMAN_MNU_F_AND_F"),
		"url" => "fileman_admin.php?lang=".LANG.'&amp;'.$addUrl,
		"dynamic"=>true,
		"items_id" => "menu_fileman_file_".$site_id."_",
		"module_id"=>"fileman",
		"more_url" => array(
			"fileman_admin.php?lang=".LANG,
			"fileman_admin.php?lang=".LANG."&".$addUrl,
			"fileman_access.php?".$addUrl,
			"fileman_admin.php?".$addUrl,
			"fileman_file_download.php?".$addUrl,
			"fileman_file_edit.php?".$addUrl,
			"fileman_html_edit.php?".$addUrl,
			"fileman_fck_edit.php?".$addUrl,
			"fileman_file_upload.php?".$addUrl,
			"fileman_file_view.php?".$addUrl,
			"fileman_folder.php?".$addUrl,
			"fileman_menu_edit.php?".$addUrl,
			"fileman_newfolder.php?".$addUrl
		),
		"title" => GetMessage("FILEMAN_MNU_F_AND_F_TITLE"),
		"items" => ($sShowOnly!==false?__fileman_mnu_gen(false, $bFullList, $site_id, "", $sShowOnly, Array(),false,$arSitesDR_) : Array()),
	);
}
return $aMenu;
?>