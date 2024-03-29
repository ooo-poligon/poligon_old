<?
define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

IncludeModuleLangFile(__FILE__);

function __GetSubmenu($menu)
{
	$aPopup = array();
	foreach($menu as $item)
	{
		if(!is_array($item))
			continue;

		$aItem = array(
			"TEXT"=>$item["text"],
			"TITLE"=>$item["title"],
			"ICON"=>$item["icon"],
		);
		if($item["url"] <> "")
		{
			$link = $item["url"];
			if(strpos($link, "/bitrix/admin/") !== 0)
				$link = "/bitrix/admin/".$link;
			$aItem["ACTION"] = "jsStartMenu.OpenURL(this, arguments, '".CUtil::addslashes($link)."');";
		}

		if(is_array($item["items"]) && count($item["items"])>0)
		{
			$aItem["MENU"] = __GetSubmenu($item["items"]);
			if($item["url"] <> "")
				$aItem["TITLE"] .= ' '.GetMessage("get_start_menu_dbl");
		}
		elseif($item["dynamic"] == true)
		{
			$aItem["MENU"] = array(
				array(
					"TEXT"=>GetMessage("get_start_menu_loading"),
					"TITLE"=>GetMessage("get_start_menu_loading_title"),
					"ICON"=>"loading",
					"AUTOHIDE"=>false,
				)
			);
			if($item["url"] <> "")
				$aItem["TITLE"] .= ' '.GetMessage("get_start_menu_dbl");
			$aItem["ONMENUPOPUP"] = "jsStartMenu.OpenDynMenu(menu, '".CUtil::addslashes($item["module_id"])."', '".CUtil::addslashes($item["items_id"])."');";
		}
		
		$aPopup[] = $aItem;
	}
	return $aPopup;
}

function __FindSubmenu($menu, $items_id)
{
	foreach($menu as $item)
	{
		if(is_array($item["items"]) && count($item["items"])>0)
		{
			if($item["items_id"] == $items_id)
				return $item["items"];
			elseif(($m = __FindSubmenu($item["items"], $items_id)) !== false)
				return $m;
		}
	}
	return false;
}

if($_REQUEST["mode"] == "save_recent")
{
	if($_REQUEST["url"] <> "")
	{
		$nLinks = 5;
		$aUserOpt = CUserOptions::GetOption("global", "settings", array());
		if($aUserOpt["start_menu_links"] <> "")
			$nLinks = intval($aUserOpt["start_menu_links"]);
		
		$aRecent = CUserOptions::GetOption("start_menu", "recent", array());

		$text = $GLOBALS["APPLICATION"]->UnJSEscape($_REQUEST["text"]);
		$title = $GLOBALS["APPLICATION"]->UnJSEscape($_REQUEST["title"]);
		$aLink = array("url"=>$_REQUEST["url"], "text"=>$text, "title"=>$title, "icon"=>$_REQUEST["icon"]);

		if(($pos = array_search($aLink, $aRecent)) !== false)
			unset($aRecent[$pos]);
		array_unshift($aRecent, $aLink);
		$aRecent = array_slice($aRecent, 0, $nLinks);

		CUserOptions::SetOption("start_menu", "recent", $aRecent);
	}
	echo "OK";
}
elseif($_REQUEST["mode"] == "dynamic")
{
	//admin menu - dynamic sections
	$adminMenu->AddOpenedSections($_REQUEST["admin_mnu_menu_id"]);
	$adminMenu->Init(array($_REQUEST["admin_mnu_module_id"]));

	$aSubmenu = __FindSubmenu($adminMenu->aGlobalMenu, $_REQUEST["admin_mnu_menu_id"]);
		
	if(!is_array($aSubmenu) || empty($aSubmenu))
		$aSubmenu = array(array("text"=>GetMessage("get_start_menu_no_data")));

	//generate JavaScript array for popup menu
	echo "menuItems={'items':".CAdminPopup::PhpToJavaScript(__GetSubmenu($aSubmenu))."}";
}
else
{
	//admin menu - all static sections
	$adminPage->Init();
	$adminMenu->Init($adminPage->aModules);

	$aPopup = array();
	foreach($adminMenu->aGlobalMenu as $menu)
	{
		$aPopup[] = array(
			"TEXT"=>$menu["text"], 
			"TITLE"=>$menu["title"].' '.GetMessage("get_start_menu_dbl"), 
			"ICON"=>$menu["icon"], 
			"ACTION"=>"jsUtils.Redirect(arguments, '".CUtil::addslashes('/bitrix/admin/'.$menu['url'])."');",
//			"DEFAULT"=>true, 
			"MENU"=>__GetSubmenu($menu["items"])
		);
	}
	
	//favorites
	if($USER->CanDoOperation('edit_own_profile') || $USER->CanDoOperation('edit_other_settings') || $USER->CanDoOperation('view_other_settings'))
	{
		$aFav = array(
			array(
				"TEXT"=>GetMessage("get_start_menu_add_fav"),
				"TITLE"=>GetMessage("get_start_menu_add_fav_title"),
				"ACTION"=>"jsUtils.Redirect(arguments, '".BX_ROOT."/admin/favorite_edit.php?lang=".CUtil::addslashes(LANGUAGE_ID)."&amp;name='+jsUtils.urlencode(document.title)+'&amp;addurl='+encodeURIComponent(window.location.href));"
			),
			array(
				"TEXT"=>GetMessage("get_start_menu_org_fav"),
				"TITLE"=>GetMessage("get_start_menu_org_fav_title"),
				"ACTION"=>"jsUtils.Redirect(arguments, '".BX_ROOT."/admin/favorite_list.php?lang=".CUtil::addslashes(LANGUAGE_ID)."');"
			),
		);
		
		$db_fav = CFavorites::GetList(array("COMMON"=>"ASC", "SORT"=>"ASC", "NAME"=>"ASC"), array("MENU_FOR_USER"=>$USER->GetID(), "LANGUAGE_ID"=>LANGUAGE_ID));
		$prevCommon = "";
		while($db_fav_arr = $db_fav->Fetch())
		{
			if($db_fav_arr["COMMON"] == "Y" && $db_fav_arr["MODULE_ID"] <> "" && $APPLICATION->GetGroupRight($db_fav_arr["MODULE_ID"]) < "R")
				continue;
			if($db_fav_arr["COMMON"] <> $prevCommon)
			{
				$aFav[] = array("SEPARATOR"=>true);
				$prevCommon = $db_fav_arr["COMMON"];
			}
		
			$sTitle = $db_fav_arr["COMMENTS"];
			$sTitle = (strlen($sTitle)>100? substr($sTitle, 0, 100)."..." : $sTitle);
			$sTitle = str_replace("\r\n", "\n", $sTitle);
			$sTitle = str_replace("\r", "\n", $sTitle);
			$sTitle = str_replace("\n", " ", $sTitle);
		
			$aFav[] = array(
				"TEXT"=>htmlspecialchars($db_fav_arr["NAME"]),
				"TITLE"=>htmlspecialchars($sTitle),
				"ICON"=>"favorites",
				"ACTION"=>"jsUtils.Redirect(arguments, '".CUtil::addslashes(htmlspecialchars($db_fav_arr["URL"]))."');",
			);
		}
		$aPopup[] = array("SEPARATOR"=>true);
		$aPopup[] = array(
			"TEXT"=>GetMessage("get_start_menu_fav"),
			"TITLE"=>GetMessage("get_start_menu_fav_title"),
			"ICON"=>"favorites",
			"MENU"=>$aFav,
		);
	}
	
	//recent urls
	$aRecent = CUserOptions::GetOption("start_menu", "recent", array());
	if(!empty($aRecent))
	{
		$aPopup[] = array("SEPARATOR"=>true);

		$nLinks = 5;
		$aUserOpt = CUserOptions::GetOption("global", "settings", array());
		if($aUserOpt["start_menu_links"] <> "")
			$nLinks = intval($aUserOpt["start_menu_links"]);

		$i = 0;
		foreach($aRecent as $recent)
		{
			$i++;
			if($i > $nLinks)
				break;
			$aPopup[] = array(
				"TEXT"=>htmlspecialchars($recent["text"]),
				"TITLE"=>htmlspecialchars($recent["title"]),
				"ICON"=>htmlspecialchars($recent["icon"]),
				"ACTION"=>"jsStartMenu.OpenURL(this, arguments, '".CUtil::addslashes($recent["url"])."');",
			);
		}
	}

	//styles of icons from modules
	$sCss = '';
	foreach($adminPage->aModules as $module)
	{
		$fname = $_SERVER["DOCUMENT_ROOT"].ADMIN_THEMES_PATH.'/'.ADMIN_THEME_ID.'/start_menu/'.$module.'/'.$module.'.css';
		if(file_exists($fname))
		{
			if($handle = fopen($fname, "r"))
			{
				$contents = fread($handle, filesize($fname));
				fclose($handle);
				$contents = preg_replace(
					"/(background-image\\s*:\\s*url\\s*\\(\\s*)([a-z].*?)(\\))/si", 
					"\\1".ADMIN_THEMES_PATH.'/'.ADMIN_THEME_ID.'/start_menu/'.$module.'/'."\\2\\3", 
					$contents);
				$sCss .= $contents."\n";
			}
		}
	}

	if(empty($aPopup))
		$aPopup[] = array("TEXT"=>GetMessage("get_start_menu_no_data"));

	//generate JavaScript array for popup menu
	echo "menuItems={'items':".CAdminPopup::PhpToJavaScript($aPopup).", 'styles':'".CUtil::JSEscape($sCss)."'}";

} //$_REQUEST["mode"] == "dynamic"

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
?>