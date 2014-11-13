<?
IncludeModuleLangFile(__FILE__);

if($APPLICATION->GetGroupRight("search")!="D")
{
	$aMenu = array(
		"parent_menu" => "global_menu_settings",
		"section" => "search",
		"sort" => 200,
		"text" => GetMessage("mnu_sect"),
		"title" => GetMessage("mnu_sect_title"),
		"url" => "search_index.php?lang=".LANGUAGE_ID,
		"icon" => "search_menu_icon",
		"page_icon" => "search_page_icon",
		"items_id" => "menu_search",
		"items" => array(
			array(
				"text" => GetMessage("mnu_reindex"),
				"url" => "search_reindex.php?lang=".LANGUAGE_ID,
				"more_url" => Array("search_reindex.php"),
				"title" => GetMessage("mnu_reindex_alt")
			),
			array(
				"text" => GetMessage("mnu_sitemap"),
				"url" => "search_sitemap.php?lang=".LANGUAGE_ID,
				"more_url" => Array("search_sitemap.php"),
				"title" => GetMessage("mnu_sitemap_alt")
			),
			array(
				"text" => GetMessage("mnu_customrank"),
				"url" => "search_customrank_admin.php?lang=".LANGUAGE_ID,
				"more_url" => array("search_customrank_admin.php", "search_customrank_edit.php"),
				"title" => GetMessage("mnu_customrank_alt")
			),
		)
	);
	return $aMenu;
}
return false;
?>
