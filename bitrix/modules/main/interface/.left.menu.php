<?
global $MESS, $APPLICATION, $USER;
IncludeModuleLangFile($GLOBALS["DOCUMENT_ROOT"]."/bitrix/modules/main/interface/.left.menu.php");
$MAIN_RIGHT = $APPLICATION->GetGroupRight("main");

$sMenuTemplate = "/bitrix/modules/main/interface/.left.menu_template.php";

/*
Admin menu format params:
"SEPARATOR" = "Y" - for section name
"SECTION_ID" - ID of section, use for the linking
*/

//Gets list of all modules
global $DOCUMENT_ROOT;
$aMenuLinks[] = array(GetMessage('admin_index'), "/bitrix/admin/index.php?lang=".LANG, array(), array("ALT"=>GetMessage('admin_index_alt')));
$arMenuSectionsTmp = Array();
$arMenuItems = Array();
$module_list = CModule::GetList();
$arModules = Array();
while($module = $module_list->Fetch())
	$arModules[] = $module;
for($i=0; $i<count($arModules); $i++)
{
	$module = $arModules[$i];
	$fname = $_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/".$module["ID"]."/admin/menu.php";

	//echo $fname." ";
	//trying to include file menu.php in the /admin/ folder of the current module
	//echo $fname.(file_exists($fname)?"Y":"N").$module["ID"]."<hr>";
	if(file_exists($fname) && ($module["ID"]=="main" || IsModuleInstalled($module["ID"])))
	{
		$aModuleMenuLinks = Array();
		$MENU_SECTION_ID = "";
		include($fname);
		if($module["ID"]=="main" && file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/admin/.left.menu.php"))
		{
			$aMenuLinksTmp = $aMenuLinks;
			include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/admin/.left.menu.php");
			$aModuleMenuLinks = array_merge($aMenuLinks, $aModuleMenuLinks);
			$aMenuLinks = $aMenuLinksTmp;
		}

		for($imenu=0; $imenu<count($aModuleMenuLinks); $imenu++)
		{
			$aMenuLinksTmp = $aModuleMenuLinks[$imenu];
			$aMenuParamsTmp = $aMenuLinksTmp[3];
			if($aMenuLinksTmp[3]["SEPARATOR"] == "Y")
			{
				$bNew = false;
				if(strlen($aMenuParamsTmp["SECTION_ID"])>0)
				{
					$MENU_SECTION_ID = $aMenuParamsTmp["SECTION_ID"];
					$MENU_SECTION_NAME = $aMenuLinksTmp[0];
					if(is_set($aMenuParamsTmp, "SORT"))
						$MENU_SECTION_SORT = $aMenuParamsTmp["SORT"];
				}
				else if(strlen($MENU_SECTION_ID)<=0)
				{
					if(is_set($aMenuParamsTmp, "SORT"))
						$MENU_SECTION_SORT = $aMenuParamsTmp["SORT"];
					else
						$MENU_SECTION_SORT = 150;
					$MENU_SECTION_NAME = $aMenuLinksTmp[0];
					$MENU_SECTION_ID = $module["ID"];
				}

				if(!is_set($arMenuSectionsTmp, $MENU_SECTION_ID))
				{
					$aMenuLinksTmp[3]["SECTION_ID"] = $MENU_SECTION_ID;
					$arMenuSectionsTmp[$MENU_SECTION_ID] = Array(
						"NAME"=>$MENU_SECTION_NAME,
						"SORT"=>$MENU_SECTION_SORT,
						"ITEMS"=>$aMenuLinksTmp);

					continue;
				}
			}

			if(strlen($MENU_SECTION_ID)<=0)
				$MENU_SECTION_ID = "GENERAL";

			if(strlen($aMenuParamsTmp["SECTION_ID"])>0)
			{
				if(!is_array($arMenuItems[$aMenuParamsTmp["SECTION_ID"]]))
					$arMenuItems[$aMenuParamsTmp["SECTION_ID"]] = Array();
				$arMenuItems[$aMenuParamsTmp["SECTION_ID"]][] = $aMenuLinksTmp;
			}
			else
			{
				if(!is_array($arMenuItems[$MENU_SECTION_ID]))
					$arMenuItems[$MENU_SECTION_ID] = Array();
				$arMenuItems[$MENU_SECTION_ID][] = $aMenuLinksTmp;
			}
			//$aMenuLinks[] = $aModuleMenuLinks[$imenu];
		}
   }
}

$arMenuSections = Array();
foreach($arMenuSectionsTmp as $MENU_SECTION_ID => $arSectTmp)
{
	$arSectTmp["ID"] = $MENU_SECTION_ID;
	$arMenuSections[] = $arSectTmp;
}

for($i=0; $i<count($arMenuSections)-1; $i++)
	for($j=$i+1; $j<count($arMenuSections); $j++)
		if($arMenuSections[$i]["SORT"] > $arMenuSections[$j]["SORT"])
		{
			$tmpLinks = $arMenuSections[$i];
			$arMenuSections[$i] = $arMenuSections[$j];
			$arMenuSections[$j] = $tmpLinks;
		}

for($k=0; $k<count($arMenuSections); $k++)
{
	$MENUSEC = $arMenuSections[$k];
	$SECTION_ID = $MENUSEC["ID"];
	$aMenuLinks[] = $MENUSEC["ITEMS"];
	$tmpMENUIT = $arMenuItems[$SECTION_ID];

	for($i=0; $i<count($tmpMENUIT)-1; $i++)
		for($j=$i+1; $j<count($tmpMENUIT); $j++)
		{
			if(IntVal($tmpMENUIT[$i][3]["SORT"]) > IntVal($tmpMENUIT[$j][3]["SORT"]))
			{
				$tmpLinks = $tmpMENUIT[$i];
				$tmpMENUIT[$i] = $tmpMENUIT[$j];
				$tmpMENUIT[$j] = $tmpLinks;
			}
		}

	for($i=0; $i<count($tmpMENUIT); $i++)
		$aMenuLinks[] = $tmpMENUIT[$i];
}
global $arADMIN_MAIN_MENU_LINKS;
$arADMIN_MAIN_MENU_LINKS = $aMenuLinks;
?>
