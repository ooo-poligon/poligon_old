<?
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2007 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

if(!is_object($GLOBALS["USER_FIELD_MANAGER"]))
	return false;

include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/include.php");
IncludeModuleLangFile(__FILE__);

function _get_elements_menu($arType, $arIBlock, $SECTION_ID)
{
	$urlElementAdminPage = COption::GetOptionString("iblock","combined_list_mode")=="Y"?"iblock_list_admin.php":"iblock_element_admin.php";

	$SECTION_ID = intval($SECTION_ID);
	if($SECTION_ID <= 0)
	{
		return array(
			"text" => htmlspecialcharsex($arIBlock["ELEMENTS_NAME"]),
			"url" => $urlElementAdminPage."?type=".$arType["ID"]."&amp;lang=".LANG."&amp;IBLOCK_ID=".$arIBlock["ID"]."&amp;find_el_y=Y",
			"more_url" => array(
				$urlElementAdminPage."?type=".$arType["ID"]."&lang=".LANG."&IBLOCK_ID=".$arIBlock["ID"]."&find_el_y=Y",
				$urlElementAdminPage."?type=".$arType["ID"]."&lang=".LANG."&IBLOCK_ID=".$arIBlock["ID"]."&find_section_section=-1",
				"iblock_element_edit.php?type=".$arType["ID"]."&lang=".LANG."&IBLOCK_ID=".$arIBlock["ID"]."&find_section_section=-1",
			),
			"title" => GetMessage("IBMENU_ALL_EL"),
			"icon" => "iblock_menu_icon_elements",
			"page_icon" => "iblock_page_icon_elements",
			"skip_chain" => true,
			"items_id" => "menu_iblock_".$arType["ID"]."_".$arIBlock["ID"],
			"module_id" => "iblock",
			"items" => array(),
		);
	}
	else
	{
		return array(
			"text" => htmlspecialcharsex($arIBlock["ELEMENTS_NAME"]),
			"url" => $urlElementAdminPage."?IBLOCK_ID=".$arIBlock["ID"]."&amp;type=".$arType["ID"]."&amp;lang=".LANG."&amp;find_section_section=".$SECTION_ID,
			"more_url" => Array(
				"iblock_element_edit.php?IBLOCK_ID=".$arIBlock["ID"]."&type=".$arType["ID"]."&lang=".LANG."&find_section_section=".$SECTION_ID,
				$urlElementAdminPage."?IBLOCK_ID=".$arIBlock["ID"]."&type=".$arType["ID"]."&lang=".LANG."&find_section_section=".$SECTION_ID,
				$urlElementAdminPage."?IBLOCK_ID=".$arIBlock["ID"]."&type=".$arType["ID"]."&lang=".LANG."&find_section_section=".$SECTION_ID,
			),
			"title" => GetMessage("IBMENU_SEC_EL"),
			"icon" => "iblock_menu_icon_elements",
			"page_icon" => "iblock_page_icon_elements",
			"skip_chain" => true,
			"items_id" => "menu_iblock_el_".$arType["ID"]."_".$arIBlock["ID"],
			"module_id" => "iblock",
			"items" => array(),
		);
	}
}

function _get_sections_menu($arType, $arIBlock, $DEPTH_LEVEL, $SECTION_ID, $arSectionsChain = false)
{

	//Determine opened sections
	if($arSectionsChain === false)
	{
		$arSectionsChain = array();
		if(isset($_REQUEST['admin_mnu_menu_id']))
		{
			$menu_id = "menu_iblock_/".$arType["ID"]."/".$arIBlock["ID"]."/";
			if(strncmp($_REQUEST['admin_mnu_menu_id'], $menu_id, strlen($menu_id)) == 0)
			{
				$rsSections = CIBlockSection::GetNavChain($arIBlock["ID"], substr($_REQUEST['admin_mnu_menu_id'], strlen($menu_id)));
				while($arSection = $rsSections->Fetch())
					$arSectionsChain[$arSection["ID"]] = $arSection["ID"];
			}
		}
		if((intval($_REQUEST["find_section_section"]) > 0) && ($_REQUEST["IBLOCK_ID"] == $arIBlock["ID"]))
		{
			$rsSections = CIBlockSection::GetNavChain($arIBlock["ID"], $_REQUEST["find_section_section"]);
			while($arSection = $rsSections->Fetch())
				$arSectionsChain[$arSection["ID"]] = $arSection["ID"];
		}
	}

	$urlSectionAdminPage = COption::GetOptionString("iblock","combined_list_mode")=="Y"?"iblock_list_admin.php":"iblock_section_admin.php";

	$arSections = Array();

	if(COption::GetOptionString("iblock","combined_list_mode") != "Y")
		$arSections[] = _get_elements_menu($arType, $arIBlock, $SECTION_ID);

	$rsSections = CIBlockSection::GetList(
		 Array("left_margin"=>"ASC"),
		 Array(
		 	"IBLOCK_ID" => $arIBlock["ID"],
			"SECTION_ID" => $SECTION_ID,
	 	)
	);
	$limit = COption::GetOptionInt("iblock", "iblock_menu_max_sections");
	while($arSection = $rsSections->Fetch())
	{
		if(($limit > 0) && (count($arSections) >= $limit))
		{
			$arSections[] = Array(
				"text" => GetMessage("IBMENU_ALL_OTH"),
				"url" => $urlSectionAdminPage."?type=".$arType["ID"]."&amp;lang=".LANG."&amp;IBLOCK_ID=".$arIBlock["ID"]."&amp;find_section_section=".IntVal($arSection["IBLOCK_SECTION_ID"]),
				"more_url" => Array(
					$urlSectionAdminPage."?type=".$arType["ID"]."&lang=".LANG."&IBLOCK_ID=".$arIBlock["ID"]."&find_section_section=".IntVal($arSection["IBLOCK_SECTION_ID"]),
					$urlSectionAdminPage."?type=".$arType["ID"]."&lang=".LANG."&IBLOCK_ID=".$arIBlock["ID"],
					"iblock_section_edit.php?type=".$arType["ID"]."&IBLOCK_ID=".$arIBlock["ID"]."&lang=".LANG,
					"iblock_element_edit.php?IBLOCK_ID=".$arIBlock["ID"]."&type=".$arType["ID"],
					"iblock_history_list.php?IBLOCK_ID=".$arIBlock["ID"]."&type=".$arType["ID"],
				),
				"title" => GetMessage("IBMENU_ALL_OTH_TITLE"),
				"icon" => "iblock_menu_icon_sections",
				"page_icon" => "iblock_page_icon_sections",
				"skip_chain" => true,
				"items_id" => "menu_iblock_/".$arType["ID"]."/".$arIBlock["ID"]."/".$arSection["ID"],
				"module_id" => "iblock",
				"items" => Array()
			);
			break;
		}
		$arSectionTmp = array(
			"text" => htmlspecialcharsex($arSection["NAME"]),
			"url" => $urlSectionAdminPage."?type=".$arType["ID"]."&amp;lang=".LANG."&amp;IBLOCK_ID=".$arIBlock["ID"]."&amp;find_section_section=".$arSection["ID"],
			"more_url" => Array(
				$urlSectionAdminPage."?type=".$arType["ID"]."&lang=".LANG."&IBLOCK_ID=".$arIBlock["ID"]."&find_section_section=".$arSection["ID"],
				"iblock_section_edit.php?type=".$arType["ID"]."&IBLOCK_ID=".$arIBlock["ID"]."&lang=".LANG."&find_section_section=".$arSection["ID"],
				"iblock_element_edit.php?IBLOCK_ID=".$arIBlock["ID"]."&type=".$arType["ID"]."&find_section_section=".$arSection["ID"],
				"iblock_history_list.php?IBLOCK_ID=".$arIBlock["ID"]."&type=".$arType["ID"]."&find_section_section=".$arSection["ID"],
			),
			"title" => htmlspecialcharsex($arSection["NAME"]),
			"icon" => "iblock_menu_icon_sections",
			"page_icon" => "iblock_page_icon_sections",
			"skip_chain" => true,
			"dynamic" =>
				(COption::GetOptionString("iblock","combined_list_mode") != "Y") ||
				(($arSection["RIGHT_MARGIN"] - $arSection["LEFT_MARGIN"]) > 1),
			"items_id" => "menu_iblock_/".$arType["ID"]."/".$arIBlock["ID"]."/".$arSection["ID"],
			"module_id" => "iblock",
			"items" => array(),
		);

		if(array_key_exists($arSection["ID"], $arSectionsChain))
		{
			$arSectionTmp["items"] = _get_sections_menu($arType, $arIBlock, $DEPTH_LEVEL+1, $arSection["ID"], $arSectionsChain);
		}
		elseif(method_exists($GLOBALS["adminMenu"], "IsSectionActive"))
		{
			if($GLOBALS["adminMenu"]->IsSectionActive("menu_iblock_/".$arType["ID"]."/".$arIBlock["ID"]."/".$arSection["ID"]))
				$arSectionTmp["items"] = _get_sections_menu($arType, $arIBlock, $DEPTH_LEVEL+1, $arSection["ID"], $arSectionsChain);
		}

		$arSections[] = $arSectionTmp;
	}
	return $arSections;
}

function _get_iblocks_menu($arType)
{
	$urlSectionAdminPage = COption::GetOptionString("iblock","combined_list_mode")=="Y"?"iblock_list_admin.php":"iblock_section_admin.php";
	$urlElementAdminPage = COption::GetOptionString("iblock","combined_list_mode")=="Y"?"iblock_list_admin.php":"iblock_element_admin.php";

	$arFilter = array(
		"TYPE" => $arType["ID"],
		"MIN_PERMISSION" => IsModuleInstalled("workflow")? "U": "W",
	);
	$rsIBlock = CIBlock::GetList(Array("SORT"=>"asc", "NAME"=>"ASC"), $arFilter);

	$arIBlocks = Array();
	while($arIBlock = $rsIBlock->Fetch())
	{

		if(!$arIBlock["ELEMENTS_NAME"])
			$arIBlock["ELEMENTS_NAME"] = $arType["ELEMENT_NAME"]? $arType["ELEMENT_NAME"]: GetMessage("IBLOCK_ELEMENTS");

		if($arType["SECTIONS"]=='Y')
		{
			$arIBlockTmp = array(
				"text" => htmlspecialcharsex($arIBlock["NAME"]),
				"url" => $urlSectionAdminPage."?type=".$arType["ID"]."&amp;lang=".LANG."&amp;IBLOCK_ID=".$arIBlock["ID"]."&amp;find_section_section=0",
				"more_url" => array(
					$urlSectionAdminPage."?type=".$arType["ID"]."&lang=".LANG."&IBLOCK_ID=".$arIBlock["ID"]."&find_section_section=0",
					$urlSectionAdminPage."?type=".$arType["ID"]."&lang=".LANG."&IBLOCK_ID=".$arIBlock["ID"]."&find_section_section=-1",
					"iblock_section_edit.php?IBLOCK_ID=".$arIBlock["ID"]."&type=".$arType["ID"]."&find_section_section=-1",
				),
				"title" => htmlspecialcharsex($arIBlock["NAME"]),
				"icon" => "iblock_menu_icon_iblocks",
				"page_icon" => "iblock_page_icon_iblocks",
				"skip_chain" => true,
				"module_id" => "iblock",
				"items_id" => "menu_iblock_/".$arType["ID"]."/".$arIBlock["ID"],
				"module_id" => "iblock",
				"dynamic" => true,
			);

			if($_REQUEST["IBLOCK_ID"] == $arIBlock["ID"])
			{
				$arIBlockTmp["items"] = _get_sections_menu($arType, $arIBlock, 1, 0);
			}
			elseif(strpos($_REQUEST['admin_mnu_menu_id'], "menu_iblock_/".$arType["ID"]."/".$arIBlock["ID"]) !== false)
			{
				$arIBlockTmp["items"] = _get_sections_menu($arType, $arIBlock, 1, 0);
			}
			elseif(method_exists($GLOBALS["adminMenu"], "IsSectionActive"))
			{
				if($GLOBALS["adminMenu"]->IsSectionActive("menu_iblock_/".$arType["ID"]."/".$arIBlock["ID"]))
					$arIBlockTmp["items"] = _get_sections_menu($arType, $arIBlock, 1, 0);
				else
					$arIBlockTmp["items"] = false;
			}
			else
			{
				$arIBlockTmp["items"] = _get_sections_menu($arType, $arIBlock, 1, 0);
			}
		}
		else
		{
			$arIBlockTmp = array(
				"text" => htmlspecialcharsex($arIBlock["NAME"]),
				"url" => $urlElementAdminPage."?type=".$arType["ID"]."&amp;lang=".LANG."&amp;IBLOCK_ID=".$arIBlock["ID"],
				"more_url" => Array(
					"iblock_element_edit.php?type=".$arType["ID"]."&lang=".LANG."&IBLOCK_ID=".$arIBlock["ID"],
					"iblock_history_list.php?type=".$arType["ID"]."&lang=".LANG."&IBLOCK_ID=".$arIBlock["ID"],
					$urlElementAdminPage."?type=".$arType["ID"]."&lang=".LANG."&IBLOCK_ID=".$arIBlock["ID"],
				),
				"title" => htmlspecialcharsex($arIBlock["NAME"]),
				"items_id" => "menu_iblock_/".$arType["ID"]."/".$arIBlock["ID"],
				"icon" => "iblock_menu_icon_iblocks",
				"page_icon" => "iblock_page_icon_iblocks",
				"skip_chain" => true,
				"module_id" => "iblock",
				"items" => Array(),
			);
		}
		$arIBlocks[] = $arIBlockTmp;
	}
	return $arIBlocks;
}

function _get_iblocks_admin_menu($arType)
{
	$arFilter = array(
		"TYPE" => $arType["ID"],
		"MIN_PERMISSION" => "X",
	);
	$rsIBlock = CIBlock::GetList(Array("SORT"=>"asc", "NAME"=>"ASC"), $arFilter);

	$arIBlocks = Array();
	while($arIBlock = $rsIBlock->Fetch())
	{

		$arIBlockTmp = array(
			"text" => htmlspecialcharsex($arIBlock["NAME"]),
			"url" => "iblock_edit.php?type=".$arType["ID"]."&amp;lang=".LANG."&amp;ID=".$arIBlock["ID"]."&amp;admin=Y",
			"more_url" => Array(
				"iblock_edit.php?type=".$arType["ID"]."&lang=".LANG."&ID=".$arIBlock["ID"]."&admin=Y",
			),
			"title" => htmlspecialcharsex($arIBlock["NAME"]),
			"items_id" => "menu_iblock_admin_/".$arType["ID"]."/".$arIBlock["ID"],
			"icon" => "iblock_menu_icon_iblocks",
			"page_icon" => "iblock_page_icon_iblocks",
			"skip_chain" => true,
			"module_id" => "iblock",
			"items" => false,
		);
		$arIBlocks[] = $arIBlockTmp;
	}
	return $arIBlocks;
}

$aMenu = Array();
$arTypes = array();

$rsTypes = CIBlockType::GetList(Array("SORT"=>"ASC"));
$obt_index = 0;
while($arType = $rsTypes->Fetch())
{
	$arType = CIBlockType::GetByIDLang($arType["ID"], LANG);
	$arIBlocksMenuItems = _get_iblocks_menu($arType);

	if($USER->IsAdmin() || count($arIBlocksMenuItems))
	{
		$ibtype = htmlspecialchars($arType["ID"]);

		$arTypeTmp = array(
			"text" => htmlspecialcharsex($arType["NAME"]),
			"url" => "iblock_admin.php?type=".$ibtype."&amp;lang=".LANG."&amp;admin=N",
			"more_url" => 	Array(
				"iblock_admin.php?type=".$ibtype."&lang=".LANG."&admin=N",
			),
			"title" => htmlspecialcharsex($arType["NAME"]),
			"parent_menu" => "global_menu_content",
			"sort" => 200 + ($obt_index++),
			"icon" => "iblock_menu_icon_types",
			"page_icon" => "iblock_page_icon_types",
			"module_id" => "iblock",
			"items_id" => "menu_iblock_/".$arType["ID"],
			"dynamic" => true,
			"items" => array(),
		);

		if($_REQUEST["type"] == $arType["ID"])
		{
			$arTypeTmp["items"] = $arIBlocksMenuItems;
		}
		elseif(strpos($_REQUEST['admin_mnu_menu_id'], "menu_iblock_/".$arType["ID"]) !== false)
		{
			$arTypeTmp["items"] = $arIBlocksMenuItems;
		}
		elseif(method_exists($GLOBALS["adminMenu"], "IsSectionActive"))
		{
			if($GLOBALS["adminMenu"]->IsSectionActive("menu_iblock_/".$arType["ID"]))
				$arTypeTmp["items"] = $arIBlocksMenuItems;
		}
		else
		{
			$arTypeTmp["items"] = $arIBlocksMenuItems;
		}

		$aMenu[] = $arTypeTmp;

		//Add ibfoblocks to admin menu
		$arTypeTmp = array(
			"text" => htmlspecialcharsex($arType["NAME"]),
			"url" => "iblock_admin.php?type=".$ibtype."&amp;lang=".LANG."&amp;admin=Y",
			"more_url" => 	Array(
				"iblock_admin.php?type=".$ibtype."&lang=".LANG."&admin=Y",
			),
			"title" => htmlspecialcharsex($arType["NAME"]),
			"parent_menu" => "global_menu_content",
			"sort" => 200 + ($obt_index++),
			"icon" => "iblock_menu_icon_types",
			"page_icon" => "iblock_page_icon_settings",
			"module_id" => "iblock",
			"items_id" => "menu_iblock_admin_/".$arType["ID"],
			"dynamic" => true,
			"items" => _get_iblocks_admin_menu($arType),
		);

		if($USER->IsAdmin() || count($arTypeTmp["items"]))
			$arTypes[] = $arTypeTmp;

	}
}


$aMenuIBRoot = array(
	"parent_menu" => "global_menu_content",
	"section" => "iblock",
	"sort" => 300,
	"text" => GetMessage("MENU_SEPARATOR"),
	"title" => GetMessage("iblock_menu_settings_title"),
	"icon" => "iblock_menu_icon_settings",
	"page_icon" => "iblock_page_icon_settings",
	"url" => "iblock_index.php?lang=".LANG,
	"items_id" => "menu_iblock",
	"module_id" => "iblock",
	"items" => array()
);

if(count($aMenu))
{
	$aMenuIBRoot["items"][] = array(
		"text" => GetMessage("IBLOCK_MENU_EXPORT"),
		"title" => GetMessage("IBLOCK_MENU_EXPORT_ALT"),
		"url" => "iblock_data_export.php?lang=".LANG,
		"items_id" => "iblock_export",
		"module_id" => "iblock",
		"items" => array(
			array(
				"text" => "CSV",
				"url" => "iblock_data_export.php?lang=".LANG,
				"module_id" => "iblock",
				"more_url" => 	Array("iblock_data_export.php"),
			),
			array(
				"text" => "XML",
				"url" => "iblock_xml_export.php?lang=".LANG,
				"module_id" => "iblock",
				"more_url" => Array("iblock_xml_export.php"),
			),
		),
	);
}

$rsIBlock = CIBlock::GetList(array(), array("MIN_PERMISSION" => "W"));

if($USER->IsAdmin() || $rsIBlock->Fetch())
{
	$aMenuTmp = array(
		"text" => GetMessage("IBLOCK_MENU_IMPORT"),
		"title" => GetMessage("IBLOCK_MENU_IMPORT_ALT"),
		"url" => "iblock_data_import.php?lang=".LANG,
		"items_id" => "iblock_import",
		"module_id" => "iblock",
		"items" => array(
			array(
				"text" => "CSV",
				"url" => "iblock_data_import.php?lang=".LANG,
				"module_id" => "iblock",
				"more_url" => 	Array("iblock_data_import.php"),
			),
		),
	);
	if($USER->IsAdmin())
	{
		$aMenuTmp["items"][] = array(
			"text" => "XML",
			"url" => "iblock_xml_import.php?lang=".LANG,
			"module_id" => "iblock",
			"more_url" => Array("iblock_xml_import.php"),
		);
	}
	$aMenuIBRoot["items"][] = $aMenuTmp;
}

if($USER->IsAdmin() || count($arTypes))
{
	$aMenuIBRoot["items"][] = array(
		"text" => GetMessage("IBLOCK_MENU_ITYPE"),
		"url" => "iblock_type_admin.php?lang=".LANG,
		"more_url" => 	Array("iblock_type_edit.php"),
		"module_id" => "iblock",
		"title" => GetMessage("IBLOCK_MENU_ITYPE_TITLE"),
		"items_id" => "iblock_admin",
		"items" => $arTypes,
	);
}

if(count($aMenuIBRoot["items"])>0)
	$aMenu[] = $aMenuIBRoot;

return $aMenu;
?>
