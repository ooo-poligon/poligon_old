<?
global $CACHE_IBLOCK_PERMISSION, $BX_IBLOCK_DETAIL_URL;
$BX_IBLOCK_DETAIL_URL = Array();
$CACHE_IBLOCK_PERMISSION = Array();

IncludeModuleLangFile(__FILE__);

class CAllIBlock
{
	function ShowPanel($IBLOCK_ID=0, $ELEMENT_ID=0, $SECTION_ID=0, $type="news", $bGetIcons=false, $componentName="")
	{
		$arButtons = array(
			"view" => array(),
			"edit" => array(),
			"configure" => array(),
			"submenu" => array(),
		);

		global $APPLICATION, $USER;
		if(!(($USER->IsAuthorized() || $APPLICATION->ShowPanel===true) && $APPLICATION->ShowPanel!==false))
			return;
		if(CModule::IncludeModule("iblock") && (strlen($type) > 0))
		{
			if($bGetIcons)
			{
				$src_add_element	= "/bitrix/images/iblock/icons/new_element.gif";
				$src_edit_element	= "/bitrix/images/iblock/icons/edit_element.gif";
				$src_edit_iblock	= "/bitrix/images/iblock/icons/edit_iblock.gif";
				$src_history_element	= "/bitrix/images/iblock/icons/history.gif";
				$src_edit_section	= "/bitrix/images/iblock/icons/edit_section.gif";
				$src_add_section	= "/bitrix/images/iblock/icons/new_section.gif";
			}
			else
			{
				$src_add_element = (defined("PANEL_ADD_ELEMENT_BTN")) ? PANEL_ADD_ELEMENT_BTN : "/bitrix/images/iblock/icons/new_element.gif";
				$src_edit_element = (defined("PANEL_EDIT_ELEMENT_BTN")) ? PANEL_EDIT_ELEMENT_BTN : "/bitrix/images/iblock/icons/edit_element.gif";
				$src_edit_iblock = (defined("PANEL_EDIT_IBLOCK_BTN")) ? PANEL_EDIT_IBLOCK_BTN : "/bitrix/images/iblock/icons/edit_iblock.gif";
				$src_history_element = (defined("PANEL_HISTORY_ELEMENT_BTN")) ? PANEL_HISTORY_ELEMENT_BTN : "/bitrix/images/iblock/icons/history.gif";
				$src_edit_section = (defined("PANEL_EDIT_SECTION_BTN")) ? PANEL_EDIT_SECTION_BTN : "/bitrix/images/iblock/icons/edit_section.gif";
				$src_add_section = (defined("PANEL_ADD_SECTION_BTN")) ? PANEL_ADD_SECTION_BTN : "/bitrix/images/iblock/icons/new_section.gif";
			}

			$IBLOCK_ID = intval($IBLOCK_ID);
			$ELEMENT_ID = intval($ELEMENT_ID);
			$SECTION_ID = intval($SECTION_ID);

			if($ELEMENT_ID>0 && ($IBLOCK_ID<=0 || $SECTION_ID==0))
			{
				$rsIBlockElement = CIBlockElement::GetList(array(), array(
					"ID" => $ELEMENT_ID,
					"ACTIVE_DATE" => "Y",
					"ACTIVE" => "Y",
					"CHECK_PERMISSIONS" => "Y",
				), false, false, array("ID", "IBLOCK_ID", "IBLOCK_SECTION_ID"));
				if($arIBlockElement = $rsIBlockElement->Fetch())
				{
					$IBLOCK_ID = $arIBlockElement["IBLOCK_ID"];
					$SECTION_ID = $arIBlockElement["IBLOCK_SECTION_ID"];
				}
			}

			if(defined("BX_AJAX_PARAM_ID"))
				$return_url = $APPLICATION->GetCurPageParam("", array(BX_AJAX_PARAM_ID));
			else
				$return_url = $APPLICATION->GetCurPageParam();

			$iblock_permission = CIBlock::GetPermission($IBLOCK_ID);
			$arIBlock = CIBlock::GetArrayByID($IBLOCK_ID);

			$max_permission = "W";
			$bWorkflow = CModule::IncludeModule("workflow") && ($arIBlock["WORKFLOW"] !== "N");

			if($bWorkflow)
			{
				$s = "&WF=Y";
				$max_permission = "U";
			}

			if( ($IBLOCK_ID > 0) && ($iblock_permission >= $max_permission) )
			{
				$url = "/bitrix/admin/iblock_element_edit.php?type=".$type."&lang=".LANGUAGE_ID."&IBLOCK_ID=".$IBLOCK_ID."&filter_section=".$SECTION_ID."&IBLOCK_SECTION_ID=".$SECTION_ID."&return_url=".UrlEncode($return_url);
				$arButton = array(
					"TEXT" => $arIBlock["ELEMENT_ADD"],
					"TITLE" => $arIBlock["ELEMENT_ADD"],
					"IMAGE" => $src_add_element,
					"ACTION" => 'javascript:'.$APPLICATION->GetPopupLink(
						array(
							"URL" => $url."&bxpublic=Y&from_module=iblock",
							"PARAMS" => array(
								"width" => 700, 'height' => 500, 'resize' => false,
							),
						)
					),
				);
				$arButtons["edit"][] = $arButton;
				$arButtons["configure"][] = $arButton;
				$arButton["ACTION"] = "jsUtils.Redirect(arguments, '".CUtil::JSEscape($url)."')";
				$arButtons["submenu"][] = $arButton;

				if($ELEMENT_ID > 0)
				{
					$url = "/bitrix/admin/iblock_element_edit.php?type=".$type.$s."&lang=".LANGUAGE_ID."&IBLOCK_ID=".$IBLOCK_ID."&ID=".$ELEMENT_ID."&filter_section=".$SECTION_ID."&return_url=".UrlEncode($return_url);
					$arButton = array(
						"TEXT" => $arIBlock["ELEMENT_EDIT"],
						"TITLE" => $arIBlock["ELEMENT_EDIT"],
						"IMAGE" => $src_edit_element,
						"ACTION" => 'javascript:'.$APPLICATION->GetPopupLink(
							array(
								"URL" => $url."&bxpublic=Y&from_module=iblock",
								"PARAMS" => array(
									"width" => 700, 'height' => 500, 'resize' => false,
								),
							)
						),
						"DEFAULT" => ($APPLICATION->GetPublicShowMode() != 'configure'? true: false),
					);
					$arButtons["edit"][] = $arButton;
					$arButtons["configure"][] = $arButton;
					$arButton["ACTION"] = "jsUtils.Redirect(arguments, '".CUtil::JSEscape($url)."')";
					$arButtons["submenu"][] = $arButton;

					if($bWorkflow)
					{
						$url = "/bitrix/admin/iblock_history_list.php?type=". $type."&lang=".LANGUAGE_ID."&IBLOCK_ID=".$IBLOCK_ID."&ELEMENT_ID=".$ELEMENT_ID."&filter_section=".$SECTION_ID."&return_url=".UrlEncode($return_url);
						$arButton = array(
							"TEXT" => GetMessage("IBLOCK_PANEL_HISTORY_BUTTON"),
							"TITLE" => GetMessage("IBLOCK_PANEL_HISTORY_BUTTON"),
							"IMAGE" => $src_history_element,
							"ACTION" => "jsUtils.Redirect(arguments, '".CUtil::JSEscape($url)."')",
						);
						$arButtons["submenu"][] = $arButton;
					}
				}

				//Section add and edit only for those who
				//have permissions and no for detail
				if( ($iblock_permission > "U") && ($ELEMENT_ID <= 0) )
				{
					$rsIBTYPE = CIBlockType::GetByID($type);
					if(($arIBTYPE = $rsIBTYPE->Fetch()) && ($arIBTYPE["SECTIONS"] == "Y"))
					{
						if($SECTION_ID > 0)
						{
							$arButtons["edit"][] = array("SEPARATOR" => "Y", "HREF" => "");
							$arButtons["configure"][] = array("SEPARATOR" => "Y", "HREF" => "");
							$arButtons["submenu"][] = array("SEPARATOR" => "Y", "HREF" => "");

							$url = "/bitrix/admin/iblock_section_edit.php?ID=". $SECTION_ID."&type=".$type."&lang=".LANGUAGE_ID. "&IBLOCK_ID=". $IBLOCK_ID."&filter_section=".$SECTION_ID."&return_url=".UrlEncode($return_url);
							$arButton = array(
								"TEXT" => $arIBlock["SECTION_EDIT"],
								"TITLE" => $arIBlock["SECTION_EDIT"],
								"IMAGE" => $src_edit_section,
								"ACTION" => 'javascript:'.$APPLICATION->GetPopupLink(
									array(
										"URL" => $url."&bxpublic=Y&from_module=iblock",
										"PARAMS" => array(
											"width" => 700, 'height' => 500, 'resize' => false,
										),
									)
								),
								"DEFAULT" => ($APPLICATION->GetPublicShowMode() != 'configure'? true: false),
							);
							$arButtons["edit"][] = $arButton;
							$arButtons["configure"][] = $arButton;
							$arButton["ACTION"] = "jsUtils.Redirect(arguments, '".CUtil::JSEscape($url)."')";
							$arButtons["submenu"][] = $arButton;
						}
						$url = "/bitrix/admin/iblock_section_edit.php?type=". $type."&lang=".LANGUAGE_ID."&IBLOCK_ID=".$IBLOCK_ID."&IBLOCK_SECTION_ID=".$SECTION_ID."&filter_section=".$SECTION_ID."&return_url=".UrlEncode($return_url);
						$arButton = array(
							"TEXT" => $arIBlock["SECTION_ADD"],
							"TITLE" => $arIBlock["SECTION_ADD"],
							"IMAGE" => $src_add_section,
							"ACTION" => 'javascript:'.$APPLICATION->GetPopupLink(
								array(
									"URL" => $url."&bxpublic=Y&from_module=iblock",
									"PARAMS" => array(
										"width" => 700, 'height' => 500, 'resize' => false,
									),
								)
							),
						);
						$arButtons["edit"][] = $arButton;
						$arButtons["configure"][] = $arButton;
						$arButton["ACTION"] = "jsUtils.Redirect(arguments, '".CUtil::JSEscape($url)."')";
						$arButtons["submenu"][] = $arButton;
					}
				}
			}

			if($IBLOCK_ID > 0)
			{
				$arButtons["submenu"][] = array("SEPARATOR" => "Y", "HREF" => "");

				$urlElementAdminPage = COption::GetOptionString("iblock","combined_list_mode")=="Y"?"iblock_list_admin.php":"iblock_element_admin.php";
				if($SECTION_ID > 0)
					$url = "/bitrix/admin/".$urlElementAdminPage."?type=".$type."&lang=".LANGUAGE_ID."&IBLOCK_ID=".$IBLOCK_ID."&find_section_section=".$SECTION_ID;
				else
					$url = "/bitrix/admin/".$urlElementAdminPage."?type=".$type."&lang=".LANGUAGE_ID."&IBLOCK_ID=".$IBLOCK_ID."&find_el_y=Y";
				$arButton = array(
					"TEXT" => $arIBlock["ELEMENTS_NAME"],
					"TITLE" => $arIBlock["ELEMENTS_NAME"],
					"IMAGE" => "/bitrix/themes/.default/icons/iblock/mnu_iblock_el.gif",
					"ACTION" => "jsUtils.Redirect(arguments, '".CUtil::JSEscape($url)."')",
				);
				$arButtons["submenu"][] = $arButton;


				$urlSectionAdminPage = COption::GetOptionString("iblock","combined_list_mode")=="Y"?"iblock_list_admin.php":"iblock_section_admin.php";
				$url = "/bitrix/admin/".$urlSectionAdminPage."?type=".$type."&lang=".LANGUAGE_ID."&IBLOCK_ID=".$IBLOCK_ID."&find_section_section=".$SECTION_ID;
				$arButton = array(
					"TEXT" => $arIBlock["SECTIONS_NAME"],
					"TITLE" => $arIBlock["SECTIONS_NAME"],
					"IMAGE" => "/bitrix/themes/.default/icons/iblock/mnu_iblock_sec.gif",
					"ACTION" => "jsUtils.Redirect(arguments, '".CUtil::JSEscape($url)."')",
				);
				$arButtons["submenu"][] = $arButton;

				if($iblock_permission >= "X")
				{
					$url = "/bitrix/admin/iblock_edit.php?type=".$type."&lang=".LANGUAGE_ID."&ID=".$IBLOCK_ID."&return_url=".UrlEncode($return_url);
					$arButton = array(
						"TEXT" => GetMessage("IBLOCK_PANEL_EDIT_IBLOCK_BUTTON", array("#IBLOCK_NAME#"=>$arIBlock["NAME"])),
						"TITLE" => GetMessage("IBLOCK_PANEL_EDIT_IBLOCK_BUTTON", array("#IBLOCK_NAME#"=>$arIBlock["NAME"])),
						"IMAGE" => $src_edit_iblock,
						"ACTION" => "jsUtils.Redirect(arguments, '".CUtil::JSEscape($url)."')",
					);
					$arButtons["submenu"][] = $arButton;
				}
			}
		}
		$mode = $APPLICATION->GetPublicShowMode();
		if($bGetIcons)
		{
			foreach($arButtons[$mode] as $i=>$arButton)
			{
				$arButtons[$mode][$i]['URL'] = $arButton['ACTION'];
				unset($arButtons[$mode][$i]['ACTION']);
			}
			return $arButtons[$mode];
		}
		elseif(count($arButtons[$mode]) > 0)
		{
			//Try to detect component via backtrace
			if(strlen($componentName) <= 0 && function_exists("debug_backtrace"))
			{
				$arTrace = debug_backtrace();
				foreach($arTrace as $i => $arCallInfo)
				{
					if(array_key_exists("file", $arCallInfo))
					{
						$file = strtolower(str_replace("\\", "/", $arCallInfo["file"]));
						if(preg_match("#.*/bitrix/components/(.+?)/(.+?)/#", $file, $match))
						{
							$componentName = $match[1].":".$match[2];
							break;
						}
					}
				}
			}
			if(strlen($componentName))
			{
				$arComponentDescription = CComponentUtil::GetComponentDescr($componentName);
				if(is_array($arComponentDescription) && strlen($arComponentDescription["NAME"]))
					$componentName = $arComponentDescription["NAME"];
			}
			else
			{
				$componentName = GetMessage("IBLOCK_PANEL_UNKNOWN_COMPONENT");
			}
			$arButton = array(
				"SRC" => "/bitrix/images/iblock/icons/iblock.gif",
				"ALT" => $componentName,
				"TEXT" => $componentName,
				"MAIN_SORT" => 300,
				"SORT" => 30,
				"MENU" => $arButtons[$mode],
				"MODE" => $mode,
			);
			$arSubmenu = array();
			foreach($arButtons[$mode] as $i=>$button)
			{
				if($button["DEFAULT"])
					$arButton["HREF"] = $button["ACTION"];
			}
			if(count($arButtons["submenu"]) > 0)
			{
				$arButton["MENU"][] = array("SEPARATOR" => "Y");
				$arButton["MENU"][] = array(
					"SRC" => "/bitrix/images/iblock/icons/iblock.gif",
					"ALT" => GetMessage("IBLOCK_PANEL_CONTROL_PANEL_ALT"),
					"TEXT" => GetMessage("IBLOCK_PANEL_CONTROL_PANEL"),
					"MENU" => $arButtons["submenu"],
					"MODE" => $mode,
				);
			}
			$APPLICATION->AddPanelButton($arButton);
		}
	}

	function GetSite($iblock_id)
	{
		global $DB;
		$strSql = "SELECT L.*, BS.* FROM b_iblock_site BS, b_lang L WHERE L.LID=BS.SITE_ID AND BS.IBLOCK_ID=".IntVal($iblock_id);
		return $DB->Query($strSql);
	}

	///////////////////////////////////////////////////////////////////
	// Block by ID
	///////////////////////////////////////////////////////////////////
	function GetByID($ID)
	{
		return CIBlock::GetList(Array(), Array("ID"=>$ID));
	}

	function GetArrayByID($ID, $FIELD=false)
	{
		global $DB;
		$ID = intval($ID);
		$strID = "b".$ID;
		if(CACHED_b_iblock===false)
		{
			$res = $DB->Query("SELECT * from  b_iblock WHERE ID = ".$ID);
			$arResult = $res->Fetch();
			if($arResult)
			{
				$arMessages = CIBlock::GetMessages($ID);
				$arResult = array_merge($arResult, $arMessages);
				$arResult["FIELDS"] = CIBlock::GetFields($ID);
			}
		}
		else
		{
			global $stackCacheManager;
			$stackCacheManager->SetLength("b_iblock", 100);
			$stackCacheManager->SetTTL("b_iblock", CACHED_b_iblock);
			if($stackCacheManager->Exist("b_iblock", $strID))
			{
				$arResult = $stackCacheManager->Get("b_iblock", $strID);
				if($arResult && !array_key_exists("ELEMENT_DELETE", $arResult))
				{
					$arMessages = CIBlock::GetMessages($ID);
					$arResult = array_merge($arResult, $arMessages);
					$stackCacheManager->Clear("b_iblock");
				}
				if($arResult && !array_key_exists("FIELDS", $arResult))
				{
					$arResult["FIELDS"] = CIBlock::GetFields($ID);
					$stackCacheManager->Clear("b_iblock");
				}
			}
			else
			{
				$res = $DB->Query("SELECT * from  b_iblock WHERE ID = ".$ID);
				$arResult = $res->Fetch();
				if($arResult)
				{
					$arMessages = CIBlock::GetMessages($ID);
					$arResult = array_merge($arResult, $arMessages);
					$arResult["FIELDS"] = CIBlock::GetFields($ID);
					$stackCacheManager->Set("b_iblock", $strID, $arResult);
				}
			}
		}
		if($FIELD)
			return $arResult[$FIELD];
		else
			return $arResult;
	}

	///////////////////////////////////////////////////////////////////
	// Function deletes iblock by ID
	///////////////////////////////////////////////////////////////////
	function Delete($ID)
	{
		$err_mess = "FILE: ".__FILE__."<br>LINE: ";
		global $DB, $USER, $APPLICATION;

		$ID = IntVal($ID);

		$APPLICATION->ResetException();
		$db_events = GetModuleEvents("iblock", "OnBeforeIBlockDelete");
		while($arEvent = $db_events->Fetch())
			if(ExecuteModuleEvent($arEvent, $ID)===false)
			{
				$err = GetMessage("MAIN_BEFORE_DEL_ERR").' '.$arEvent['TO_NAME'];
				if($ex = $APPLICATION->GetException())
					$err .= ': '.$ex->GetString();
				$APPLICATION->throwException($err);
				return false;
			}

		$events = GetModuleEvents("iblock", "OnIBlockDelete");
		while($arEvent = $events->Fetch())
			ExecuteModuleEvent($arEvent, $ID);

		$iblocksections = CIBlockSection::GetList(Array(), Array("IBLOCK_ID"=>$ID, "DEPTH_LEVEL"=>1));
		while($iblocksection = $iblocksections->Fetch())
		{
			if(!CIBlockSection::Delete($iblocksection["ID"]))
				return false;
		}

		$iblockelements = CIBlockElement::GetList(Array(), Array("IBLOCK_ID"=>$ID, "SHOW_NEW"=>"Y"), false, false, array("IBLOCK_ID", "ID"));
		while($iblockelement = $iblockelements->Fetch())
		{
			if(!CIBlockElement::Delete($iblockelement["ID"]))
				return false;
		}

		$props = CIBlock::GetProperties($ID);
		while($property = $props->Fetch())
		{
			if(!CIBlockProperty::Delete($property["ID"])) return false;
		}

		if(!$DB->Query("DELETE FROM b_iblock_messages WHERE IBLOCK_ID = ".$ID, false, $err_mess.__LINE__))
			return false;

		if(!$DB->Query("DELETE FROM b_iblock_fields WHERE IBLOCK_ID = ".$ID, false, $err_mess.__LINE__))
			return false;

		$GLOBALS["stackCacheManager"]->Clear("b_iblock");
		$GLOBALS["USER_FIELD_MANAGER"]->OnEntityDelete("IBLOCK_".$ID."_SECTION");

		if(!$DB->Query("DELETE FROM b_iblock_group WHERE IBLOCK_ID=".$ID, false, $err_mess.__LINE__))
			return false;
		if(!$DB->Query("DELETE FROM b_iblock_rss WHERE IBLOCK_ID=".$ID, false, $err_mess.__LINE__))
			return false;
		if(!$DB->Query("DELETE FROM b_iblock_site WHERE IBLOCK_ID=".$ID, false, $err_mess.__LINE__))
			return false;
		if(!$DB->Query("DELETE FROM b_iblock WHERE ID=".$ID, false, $err_mess.__LINE__))
			return false;
		$DB->Query("DROP TABLE b_iblock_element_prop_s".$ID, true, $err_mess.__LINE__);
		$DB->Query("DROP TABLE b_iblock_element_prop_m".$ID, true, $err_mess.__LINE__);
		$DB->Query("DROP SEQUENCE sq_b_iblock_element_prop_m".$ID, true, $err_mess.__LINE__);

		/************* QUOTA *************/
		$_SESSION["SESS_RECOUNT_DB"] = "Y";
		/************* QUOTA *************/

		return true;
	}

	///////////////////////////////////////////////////////////////////
	// Check function called from Add and Update
	///////////////////////////////////////////////////////////////////
	function CheckFields(&$arFields, $ID=false)
	{
		global $APPLICATION, $DB, $USER;
		$this->LAST_ERROR = "";

		if(($ID===false || is_set($arFields, "NAME")) && strlen($arFields["NAME"])<=0)
			$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_NAME")."<br>";

		if($ID===false && !is_set($arFields, "IBLOCK_TYPE_ID"))
			$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_BLOCK_TYPE")."<br>";

		if(is_set($arFields, "IBLOCK_TYPE_ID"))
		{
			$r = CIBlockType::GetByID($arFields["IBLOCK_TYPE_ID"]);
			if(!$r->Fetch())
				$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_BLOCK_TYPE_ID")."<br>";
		}

		if(is_set($arFields, "PICTURE"))
		{
			$error = CFile::CheckImageFile($arFields["PICTURE"]);
			if (strlen($error)>0) $this->LAST_ERROR .= $error."<br>";
		}

		if(
			($ID===false && !is_set($arFields, "LID")) ||
			(is_set($arFields, "LID")
			&& (
				(is_array($arFields["LID"]) && count($arFields["LID"])<=0)
				||
				(!is_array($arFields["LID"]) && strlen($arFields["LID"])<=0)
				)
			)
		)
		{
			$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_SITE_ID_NA")."<br>";
			//echo GetMessage("IBLOCK_BAD_SITE_ID_NA");
		}
		elseif(is_set($arFields, "LID"))
		{
			if(!is_array($arFields["LID"]))
				$arFields["LID"] = Array($arFields["LID"]);

			foreach($arFields["LID"] as $v)
			{
    			$r = CSite::GetByID($v);
    			if(!$r->Fetch())
    				$this->LAST_ERROR .= "'".$v."' - ".GetMessage("IBLOCK_BAD_SITE_ID")."<br>";
			}
		}

		$APPLICATION->ResetException();
		if($ID===false)
			$db_events = GetModuleEvents("iblock", "OnBeforeIBlockAdd");
		else
		{
			$arFields["ID"] = $ID;
			$db_events = GetModuleEvents("iblock", "OnBeforeIBlockUpdate");
		}

		while($arEvent = $db_events->Fetch())
		{
			$bEventRes = ExecuteModuleEvent($arEvent, &$arFields);
			if($bEventRes===false)
			{
				if($err = $APPLICATION->GetException())
					$this->LAST_ERROR .= $err->GetString()."<br>";
				else
				{
					$APPLICATION->ThrowException("Unknown error");
					$this->LAST_ERROR .= "Unknown error.<br>";
				}
				break;
			}
		}

		/****************************** QUOTA ******************************/
		if(empty($this->LAST_ERROR) && (COption::GetOptionInt("main", "disk_space") > 0))
		{
			$quota = new CDiskQuota();
			if(!$quota->checkDiskQuota($arFields))
				$this->LAST_ERROR = $quota->LAST_ERROR;
		}
		/****************************** QUOTA ******************************/

		if(strlen($this->LAST_ERROR)>0)
			return false;

		return true;
	}


	function SetPermission($IBLOCK_ID, $arGROUP_ID)
	{
		global $DB;

		/*
		$PERM = CIBlock::GetPermission($IBLOCK_ID);
		if($PERM < "X")
			return false;
		*/

		$DB->Query("DELETE FROM b_iblock_group WHERE IBLOCK_ID=".$IBLOCK_ID);
		foreach($arGROUP_ID as $GROUP_ID => $perm)
		{
			if(
				$perm!="R" &&
				$perm!="U" &&
				$perm!="W" &&
				$perm!="X") continue;

			$strSql =
				"INSERT INTO b_iblock_group(IBLOCK_ID, GROUP_ID, PERMISSION) ".
				"SELECT ".$IBLOCK_ID.", ID, '".$perm."' ".
				"FROM b_group ".
				"WHERE ID = ".IntVal($GROUP_ID);

			$DB->Query($strSql);
		}
	}

	function SetMessages($ID, $arFields)
	{
		global $DB;
		$ID = intval($ID);
		if($ID > 0)
		{
			$arMessages = array(
				"ELEMENT_NAME",
				"ELEMENTS_NAME",
				"ELEMENT_ADD",
				"ELEMENT_EDIT",
				"ELEMENT_DELETE",
				"SECTION_NAME",
				"SECTIONS_NAME",
				"SECTION_ADD",
				"SECTION_EDIT",
				"SECTION_DELETE",
			);
			$arUpdate = array();
			foreach($arMessages as $MESSAGE_ID)
			{
				if(array_key_exists($MESSAGE_ID, $arFields))
					$arUpdate[] = $MESSAGE_ID;
			}
			if(count($arUpdate) > 0)
			{
				$res = $DB->Query("
					DELETE FROM b_iblock_messages
					WHERE IBLOCK_ID = ".$ID."
					AND MESSAGE_ID in ('".implode("', '", $arUpdate)."')
				");
				if($res)
				{
					foreach($arUpdate as $MESSAGE_ID)
					{
						$MESSAGE_TEXT = trim($arFields[$MESSAGE_ID]);
						if(strlen($MESSAGE_TEXT) > 0)
							$DB->Add("b_iblock_messages", array(
								"ID" => 1, //FAKE field for not use sequence
								"IBLOCK_ID" => $ID,
								"MESSAGE_ID" => $MESSAGE_ID,
								"MESSAGE_TEXT" => $MESSAGE_TEXT,
							));
					}
				}
			}
		}
	}

	function GetMessages($ID)
	{
		global $DB;
		$ID = intval($ID);
		$arMessages = array(
			"ELEMENT_NAME" => GetMessage("IBLOCK_MESS_ELEMENT_NAME"),
			"ELEMENTS_NAME" => "",
			"ELEMENT_ADD" => GetMessage("IBLOCK_MESS_ELEMENT_ADD"),
			"ELEMENT_EDIT" => GetMessage("IBLOCK_MESS_ELEMENT_EDIT"),
			"ELEMENT_DELETE" => GetMessage("IBLOCK_MESS_ELEMENT_DELETE"),
			"SECTION_NAME" => GetMessage("IBLOCK_MESS_SECTION_NAME"),
			"SECTIONS_NAME" => "",
			"SECTION_ADD" => GetMessage("IBLOCK_MESS_SECTION_ADD"),
			"SECTION_EDIT" => GetMessage("IBLOCK_MESS_SECTION_EDIT"),
			"SECTION_DELETE" => GetMessage("IBLOCK_MESS_SECTION_DELETE"),
		);
		$res = $DB->Query("
			SELECT
				B.IBLOCK_TYPE_ID
				,M.IBLOCK_ID
				,M.MESSAGE_ID
				,M.MESSAGE_TEXT
			FROM
				b_iblock B
				LEFT JOIN b_iblock_messages M ON B.ID = M.IBLOCK_ID
			WHERE
				B.ID = ".$ID."
		");
		$type = "";
		while($ar = $res->Fetch())
		{
			$type = $ar["IBLOCK_TYPE_ID"];
			if($ar["MESSAGE_ID"])
				$arMessages[$ar["MESSAGE_ID"]] = $ar["MESSAGE_TEXT"];
		}
		if((strlen($arMessages["ELEMENTS_NAME"]) <= 0) || (strlen($arMessages["SECTIONS_NAME"]) <= 0))
		{
			if($type)
			{
				$arType = CIBlockType::GetByIDLang($type, LANGUAGE_ID);
				if($arType)
				{
					if(strlen($arMessages["ELEMENTS_NAME"]) <= 0)
						$arMessages["ELEMENTS_NAME"] = $arType["ELEMENT_NAME"];
					if(strlen($arMessages["SECTIONS_NAME"]) <= 0)
						$arMessages["SECTIONS_NAME"] = $arType["SECTION_NAME"];
				}
			}
		}
		if(strlen($arMessages["ELEMENTS_NAME"]) <= 0)
			$arMessages["ELEMENTS_NAME"] = GetMessage("IBLOCK_MESS_ELEMENTS_NAME");
		if(strlen($arMessages["SECTIONS_NAME"]) <= 0)
			$arMessages["SECTIONS_NAME"] = GetMessage("IBLOCK_MESS_SECTIONS_NAME");
		return $arMessages;
	}

	function GetFieldsDefaults()
	{
/*************
REQ
+	IBLOCK_SECTION_ID 	int(11),
	ACTIVE 			char(1) 	not null 	default 'Y',
+	ACTIVE_FROM 		datetime,
+	ACTIVE_TO 		datetime,
	SORT 			int(11) 	not null 	default '500',
	NAME 			varchar(255)	not null,
+	PREVIEW_PICTURE 	int(18),
+	PREVIEW_TEXT 		text,
	PREVIEW_TEXT_TYPE	varchar(4) 	not null 	default 'text',
+	DETAIL_PICTURE 		int(18),
+	DETAIL_TEXT 		longtext,
	DETAIL_TEXT_TYPE 	varchar(4) 	not null 	default 'text',
+	XML_ID 			varchar(255),
+	CODE 			varchar(255),
+	TAGS 			varchar(255),
**************/
		static $res = false;
		if(!$res)
		$res = array(
			"IBLOCK_SECTION" => array(
				"NAME" => GetMessage("IBLOCK_FIELD_SECTIONS"),
				"IS_REQUIRED" => false,
			),
			"ACTIVE" => array(
				"NAME" => GetMessage("IBLOCK_FIELD_ACTIVE"),
				"IS_REQUIRED" => "Y",
			),
			"ACTIVE_FROM" => array(
				"NAME" => GetMessage("IBLOCK_FIELD_ACTIVE_PERIOD_FROM"),
				"IS_REQUIRED" => false,
			),
			"ACTIVE_TO" => array(
				"NAME" => GetMessage("IBLOCK_FIELD_ACTIVE_PERIOD_TO"),
				"IS_REQUIRED" => false,
			),
			"SORT" => array(
				"NAME" => GetMessage("IBLOCK_FIELD_SORT"),
				"IS_REQUIRED" => false,
			),
			"NAME" => array(
				"NAME" => GetMessage("IBLOCK_FIELD_NAME"),
				"IS_REQUIRED" => "Y",
			),
			"PREVIEW_PICTURE" => array(
				"NAME" => GetMessage("IBLOCK_FIELD_PREVIEW_PICTURE"),
				"IS_REQUIRED" => false,
			),
			"PREVIEW_TEXT_TYPE" => array(
				"NAME" => GetMessage("IBLOCK_FIELD_PREVIEW_TEXT_TYPE"),
				"IS_REQUIRED" => "Y",
			),
			"PREVIEW_TEXT" => array(
				"NAME" => GetMessage("IBLOCK_FIELD_PREVIEW_TEXT"),
				"IS_REQUIRED" => false,
			),
			"DETAIL_PICTURE" => array(
				"NAME" => GetMessage("IBLOCK_FIELD_DETAIL_PICTURE"),
				"IS_REQUIRED" => false,
			),
			"DETAIL_TEXT_TYPE" => array(
				"NAME" => GetMessage("IBLOCK_FIELD_DETAIL_TEXT_TYPE"),
				"IS_REQUIRED" => "Y",
			),
			"DETAIL_TEXT" => array(
				"NAME" => GetMessage("IBLOCK_FIELD_DETAIL_TEXT"),
				"IS_REQUIRED" => false,
			),
			"XML_ID" => array(
				"NAME" => GetMessage("IBLOCK_FIELD_XML_ID"),
				"IS_REQUIRED" => false,
			),
			"CODE" => array(
				"NAME" => GetMessage("IBLOCK_FIELD_CODE"),
				"IS_REQUIRED" => false,
			),
			"TAGS" => array(
				"NAME" => GetMessage("IBLOCK_FIELD_TAGS"),
				"IS_REQUIRED" => false,
			),
		);
		return $res;
	}

	function SetFields($ID, $arFields)
	{
		global $DB;
		$ID = intval($ID);
		if($ID > 0)
		{
			$arDefFields = CIBlock::GetFieldsDefaults();
			$res = $DB->Query("
				SELECT * FROM b_iblock_fields
				WHERE IBLOCK_ID = ".$ID."
			");
			if(array_key_exists("PREVIEW_PICTURE", $arFields))
			{
				if(is_array($arFields["PREVIEW_PICTURE"]["DEFAULT_VALUE"]))
				{
					$a = $arFields["PREVIEW_PICTURE"]["DEFAULT_VALUE"];
					$arFields["PREVIEW_PICTURE"]["DEFAULT_VALUE"] = serialize(array(
						"FROM_DETAIL" => $a["FROM_DETAIL"] === "Y"? "Y": "N",
						"SCALE" => $a["SCALE"] === "Y"? "Y": "N",
						"WIDTH" => intval($a["WIDTH"]) > 0? intval($a["WIDTH"]): "",
						"HEIGHT" => intval($a["HEIGHT"]) > 0? intval($a["HEIGHT"]): "",
						"IGNORE_ERRORS" => $a["IGNORE_ERRORS"] === "Y"? "Y": "N",
					));
				}
				else
				{
					$arFields["PREVIEW_PICTURE"]["DEFAULT_VALUE"] = "";
				}
			}
			if(array_key_exists("DETAIL_PICTURE", $arFields))
			{
				if(is_array($arFields["DETAIL_PICTURE"]["DEFAULT_VALUE"]))
				{
					$a = $arFields["DETAIL_PICTURE"]["DEFAULT_VALUE"];
					$arFields["DETAIL_PICTURE"]["DEFAULT_VALUE"] = serialize(array(
						"SCALE" => $a["SCALE"] === "Y"? "Y": "N",
						"WIDTH" => intval($a["WIDTH"]) > 0? intval($a["WIDTH"]): "",
						"HEIGHT" => intval($a["HEIGHT"]) > 0? intval($a["HEIGHT"]): "",
						"IGNORE_ERRORS" => $a["IGNORE_ERRORS"] === "Y"? "Y": "N",
					));
				}
				else
				{
					$arFields["DETAIL_PICTURE"]["DEFAULT_VALUE"] = "";
				}
			}
			while($ar = $res->Fetch())
			{
				if(array_key_exists($ar["FIELD_ID"], $arFields) && array_key_exists($ar["FIELD_ID"], $arDefFields))
				{
					if($arDefFields[$ar["FIELD_ID"]]["IS_REQUIRED"] === false)
						$IS_REQUIRED = $arFields[$ar["FIELD_ID"]]["IS_REQUIRED"];
					else
						$IS_REQUIRED = $arDefFields[$ar["FIELD_ID"]]["IS_REQUIRED"];
					$IS_REQUIRED = ($IS_REQUIRED === "Y"? "Y": "N");
					if(
						$ar["IS_REQUIRED"] !== $IS_REQUIRED
						|| $ar["DEFAULT_VALUE"] !== $arFields[$ar["FIELD_ID"]]["DEFAULT_VALUE"]
					)
					{
						$arUpdate = array(
							"IS_REQUIRED" => $IS_REQUIRED,
							"DEFAULT_VALUE" => $arFields[$ar["FIELD_ID"]]["DEFAULT_VALUE"],
						);
					}
					else
					{
						$arUpdate = array(
						);
					}
					unset($arDefFields[$ar["FIELD_ID"]]);
				}
				elseif(array_key_exists($ar["FIELD_ID"], $arDefFields))
				{
					$IS_REQUIRED = $arDefFields[$ar["FIELD_ID"]]["IS_REQUIRED"];
					$IS_REQUIRED = ($IS_REQUIRED === "Y"? "Y": "N");
					if($ar["IS_REQUIRED"] !== $IS_REQUIRED)
					{
						$arUpdate = array(
							"IS_REQUIRED" => $IS_REQUIRED,
							"DEFAULT_VALUE" => "",
						);
					}
					else
					{
						$arUpdate = array(
						);
					}
					unset($arDefFields[$ar["FIELD_ID"]]);
				}
				else
				{
					$DB->Query("DELETE FROM b_iblock_fields WHERE IBLOCK_ID = ".$ID." AND FIELD_ID = '".$DB->ForSQL($ar["FIELD_ID"])."'");
					$arUpdate = array(
					);
				}

				$strUpdate = $DB->PrepareUpdate("b_iblock_fields", $arUpdate);
				if($strUpdate != "")
				{
					$strSql = "UPDATE b_iblock_fields SET ".$strUpdate." WHERE IBLOCK_ID = ".$ID." AND FIELD_ID = '".$ar["FIELD_ID"]."'";
					$arBinds = array(
						"DEFAULT_VALUE" => $arUpdate["DEFAULT_VALUE"],
					);
					$DB->QueryBind($strSql, $arBinds);
				}
			}
			foreach($arDefFields as $FIELD_ID => $arDefaults)
			{
				if(array_key_exists($FIELD_ID, $arFields))
				{
					if($arDefaults["IS_REQUIRED"] === false)
						$IS_REQUIRED = $arFields[$FIELD_ID]["IS_REQUIRED"];
					else
						$IS_REQUIRED = $arDefaults["IS_REQUIRED"];
					$DEFAULT_VALUE = $arFields[$FIELD_ID]["DEFAULT_VALUE"];
				}
				else
				{
					$IS_REQUIRED = $arDefaults["IS_REQUIRED"];
					$DEFAULT_VALUE = false;
				}
				$IS_REQUIRED = ($IS_REQUIRED === "Y"? "Y": "N");
				$arAdd = array(
					"ID" => 1,
					"IBLOCK_ID" => $ID,
					"FIELD_ID" => $FIELD_ID,
					"IS_REQUIRED" => $IS_REQUIRED,
					"DEFAULT_VALUE" => $DEFAULT_VALUE,
				);
				$DB->Add("b_iblock_fields", $arAdd, array("DEFAULT_VALUE"));
			}
		}
	}

	function GetFields($ID)
	{
		global $DB;
		$ID = intval($ID);
		$arDefFields = CIBlock::GetFieldsDefaults();
		$res = $DB->Query("
			SELECT
				F.*
			FROM
				b_iblock B
				LEFT JOIN b_iblock_fields F ON B.ID = F.IBLOCK_ID
			WHERE
				B.ID = ".$ID."
		");
		while($ar = $res->Fetch())
		{
			if(array_key_exists($ar["FIELD_ID"], $arDefFields))
			{
				if($arDefFields[$ar["FIELD_ID"]]["IS_REQUIRED"] === false)
					$arDefFields[$ar["FIELD_ID"]]["IS_REQUIRED"] = $ar["IS_REQUIRED"] === "Y"? "Y": "N";
				$arDefFields[$ar["FIELD_ID"]]["DEFAULT_VALUE"] = $ar["DEFAULT_VALUE"];
			}
		}
		foreach($arDefFields as $FIELD_ID => $default)
		{
			if($default["IS_REQUIRED"] === false)
				$arDefFields[$FIELD_ID]["IS_REQUIRED"] = "N";
			if($FIELD_ID == "DETAIL_PICTURE" || $FIELD_ID == "PREVIEW_PICTURE")
				$arDefFields[$FIELD_ID]["DEFAULT_VALUE"] = strlen($arDefFields[$FIELD_ID]["DEFAULT_VALUE"]) > 0? unserialize($arDefFields[$FIELD_ID]["DEFAULT_VALUE"]): array();
		}
		return $arDefFields;
	}

	function GetProperties($ID, $arOrder=Array(), $arFilter=Array())
	{
		$props = new CIBlockProperty();
		$arFilter["IBLOCK_ID"] = $ID;
		return $props->GetList($arOrder, $arFilter);
	}

	function GetGroupPermissions($ID)
	{
		global $DB;

		$strSql =
			"SELECT * ".
			"FROM b_iblock_group ".
			"WHERE IBLOCK_ID = '".IntVal($ID)."'";

		$dbres = $DB->Query($strSql);
		$arRes = Array();
		while($res = $dbres->Fetch())
			$arRes[$res["GROUP_ID"]] = $res["PERMISSION"];

		return $arRes;
	}

	function GetPermission($IBLOCK_ID)
	{
		global $DB, $USER, $CACHE_IBLOCK_PERMISSION;

		//$this->ID = $ID;

		if($USER->IsAdmin())
			return "X";

		if(is_array($CACHE_IBLOCK_PERMISSION) && is_set($CACHE_IBLOCK_PERMISSION, $IBLOCK_ID)>0)
			return $CACHE_IBLOCK_PERMISSION[$IBLOCK_ID];

		$strSql =
			"SELECT MAX(IBG.PERMISSION) as P ".
			"FROM b_iblock_group IBG ".
			"WHERE IBG.IBLOCK_ID=".IntVal($IBLOCK_ID)." AND IBG.GROUP_ID IN (".$USER->GetGroups().")";

		$res = $DB->Query($strSql);
		if($r = $res->Fetch())
		{
			if (strlen($r['P']) > 0)
			{
				$CACHE_IBLOCK_PERMISSION[$IBLOCK_ID] = $r["P"];
				return $r["P"];
			}
		}

		$CACHE_IBLOCK_PERMISSION[$IBLOCK_ID] = "D";
		return "D";
	}

	function Search($strKeyWord)
	{
		global $DB;
		$DB->Query("SELECT FROM ");
	}

	function OnBeforeLangDelete($lang)
	{
		global $DB;
		$r = $DB->Query("SELECT 'x' FROM b_iblock_site WHERE SITE_ID='".$DB->ForSQL($lang, 2)."'");
		return ($r->Fetch()?false:true);
	}

	function OnLangDelete($lang)
	{
		global $DB;
		return true;//$DB->Query("DELETE FROM b_iblock_type_lang WHERE LID='".$DB->ForSql($lang, 2)."'", true);
	}

	function OnGroupDelete($group_id)
	{
		global $DB;
		return $DB->Query("DELETE FROM b_iblock_group WHERE GROUP_ID=".IntVal($group_id), true);
	}

	function MkOperationFilter($key)
	{
		static $triple_char = array(
			"!><"=>"NB", //not between
		);
		static $double_char = array(
			"!="=>"NI", //not Identical
			"!%"=>"NS", //not substring
			"><"=>"B",  //between
			">="=>"GE", //greater or equal
			"<="=>"LE", //less or equal
		);
		static $single_char = array(
			"="=>"I", //Identical
			"%"=>"S", //substring
			"?"=>"?", //logical
			">"=>"G", //greater
			"<"=>"L", //less
			"!"=>"N", // not field LIKE val
		);
		if(array_key_exists($op = substr($key,0,3), $triple_char))
			return Array("FIELD"=>substr($key,3), "OPERATION"=>$triple_char[$op]);
		elseif(array_key_exists($op = substr($key,0,2), $double_char))
			return Array("FIELD"=>substr($key,2), "OPERATION"=>$double_char[$op]);
		elseif(array_key_exists($op = substr($key,0,1), $single_char))
			return Array("FIELD"=>substr($key,1), "OPERATION"=>$single_char[$op]);
		else
			return Array("FIELD"=>$key, "OPERATION"=>"E"); // field LIKE val
	}

	function FilterCreate($fname, $vals, $type, $cOperationType=false, $bSkipEmpty = true)
	{
		return CIBlock::FilterCreateEx($fname, $vals, $type, $bFullJoin, $cOperationType, $bSkipEmpty);
	}

	function ForLIKE($str)
	{
		global $DB;
		return str_replace("%", "\\%", str_replace("_", "\\_", $DB->ForSQL($str)));
	}

	function FilterCreateEx($fname, $vals, $type, &$bFullJoin, $cOperationType=false, $bSkipEmpty = true)
	{
		global $DB;
 		if(!is_array($vals))
			$vals=Array($vals);

		if(count($vals)<1)
			return "";

		if(is_bool($cOperationType))
		{
			if($cOperationType===true)
				$cOperationType = "N";
			else
				$cOperationType = "E";
		}

		if($cOperationType=="E") // most req operation
			$strOperation = "=";
		elseif($cOperationType=="G")
			$strOperation = ">";
		elseif($cOperationType=="GE")
			$strOperation = ">=";
		elseif($cOperationType=="LE")
			$strOperation = "<=";
		elseif($cOperationType=="L")
			$strOperation = "<";
 		elseif($cOperationType=='B')
 			$strOperation = array('BETWEEN', 'AND');
 		elseif($cOperationType=='NB')
 			$strOperation = array('BETWEEN', 'AND');
		else
			$strOperation = "=";

		if($cOperationType=='B' || $cOperationType=='NB')
		{
			if(count($vals)==2 && !is_array($vals[0]))
				$vals = array($vals);
		}

		$bFullJoin = false;
		$bWasLeftJoin = false;

		$res = Array();
		foreach($vals as $val)
		{
			if(!$bSkipEmpty || strlen($val)>0 || (is_bool($val) && $val===false) || (is_array($strOperation) && is_array($val)))
			{
				switch ($type)
				{
				case "string_equal":
					if($cOperationType=="?")
					{
						if(strlen($val)>0)
							$res[] = GetFilterQuery($fname, $val, "N");
					}
					elseif($cOperationType=="S" || $cOperationType=="NS")
  						$res[] = ($cOperationType=="NS"?" ".$fname." IS NULL OR NOT ":"")."(".CIBlock::_Upper($fname)." LIKE ".CIBlock::_Upper("'%".CIBlock::ForLIKE($val)."%'").")";
					elseif(($cOperationType=="B" || $cOperationType=="NB") && is_array($val) && count($val)==2)
  						$res[] = ($cOperationType=="NB"?" ".$fname." IS NULL OR NOT ":"")."(".CIBlock::_Upper($fname)." ".$strOperation[0]." '".CIBlock::_Upper($DB->ForSql($val[0]))."' ".$strOperation[1]." '".CIBlock::_Upper($DB->ForSql($val[1]))."')";
					else
					{
						if(strlen($val)<=0)
							$res[] = ($cOperationType=="N"?"NOT":"")."(".$fname." IS NULL OR ".CIBlock::_Length($fname)."<=0)";
						else
							$res[] = ($cOperationType=="N"?" ".$fname." IS NULL OR NOT ":"")."(".CIBlock::_Upper($fname).$strOperation.CIBlock::_Upper("'".$DB->ForSql($val)."'").")";
					}
					break;
				case "string":
					if($cOperationType=="?")
					{
						if(strlen($val)>0)
						{
							$sr = GetFilterQuery($fname, $val, "Y", array(), ($fname=="BE.SEARCHABLE_CONTENT" || $fname=="BE.DETAIL_TEXT" ? "Y" : "N"));
							if($sr != "0")
								$res[] = $sr;
						}
					}
					elseif(($cOperationType=="B" || $cOperationType=="NB") && is_array($val) && count($val)==2)
					{
  						$res[] = ($cOperationType=="NB"?" ".$fname." IS NULL OR NOT ":"")."(".CIBlock::_Upper($fname)." ".$strOperation[0]." '".CIBlock::_Upper($DB->ForSql($val[0]))."' ".$strOperation[1]." '".CIBlock::_Upper($DB->ForSql($val[1]))."')";
					}
					elseif($cOperationType=="S" || $cOperationType=="NS")
  						$res[] = ($cOperationType=="NS"?" ".$fname." IS NULL OR NOT ":"")."(".CIBlock::_Upper($fname)." LIKE ".CIBlock::_Upper("'%".CIBlock::ForLIKE($val)."%'").")";
					else
					{
						if(strlen($val)<=0)
							$res[] = (substr($cOperationType, 0, 1)=="N"?"NOT":"")."(".$fname." IS NULL OR ".CIBlock::_Length($fname)."<=0)";
						else
							if($strOperation=="=" && $cOperationType!="I" && $cOperationType!="NI")
								$res[] = ($cOperationType=="N"?" ".$fname." IS NULL OR NOT ":"")."(".($DB->type=="ORACLE"?CIBlock::_Upper($fname)." LIKE ".CIBlock::_Upper("'".$DB->ForSqlLike($val)."'")." ESCAPE '\\'" : $fname." LIKE '".$DB->ForSqlLike($val)."'").")";
							else
								$res[] = (substr($cOperationType, 0, 1)=="N"?" ".$fname." IS NULL OR NOT ":"")."(".($DB->type=="ORACLE"?CIBlock::_Upper($fname)." ".$strOperation." ".CIBlock::_Upper("'".$DB->ForSql($val)."'")." " : $fname." ".$strOperation." '".$DB->ForSql($val)."'").")";
					}
					break;
				case "date":
					if(strlen($val)<=0)
						$res[] = ($cOperationType=="N"?"NOT":"")."(".$fname." IS NULL)";
					elseif(($cOperationType=="B" || $cOperationType=="NB") && is_array($val) && count($val)==2)
  						$res[] = ($cOperationType=='NB'?' '.$fname.' IS NULL OR NOT ':'').'('.$fname.' '.$strOperation[0].' '.$DB->CharToDateFunction($DB->ForSql($val[0]), "FULL").' '.$strOperation[1].' '.$DB->CharToDateFunction($DB->ForSql($val[1]), "FULL").')';
					else
						$res[] = (substr($cOperationType, 0, 1)=="N"?" ".$fname." IS NULL OR NOT ":"")."(".$fname." ".$strOperation." ".$DB->CharToDateFunction($DB->ForSql($val), "FULL").")";
					break;
				case "number":
					if(strlen($val)<=0)
						$res[] = ($cOperationType=="N"?"NOT":"")."(".$fname." IS NULL)";
					elseif(($cOperationType=="B" || $cOperationType=="NB") && is_array($val) && count($val)==2)
  						$res[] = ($cOperationType=='NB'?' '.$fname.' IS NULL OR NOT ':'').'('.$fname.' '.$strOperation[0].' \''.DoubleVal($val[0]).'\' '.$strOperation[1].' \''.DoubleVal($val[1]).'\')';
					else
						$res[] = (substr($cOperationType, 0, 1)=="N"?" ".$fname." IS NULL OR NOT ":"")."(".$fname." ".$strOperation." '".DoubleVal($val)."')";
					break;
				case "number_above":
					if(strlen($val)<=0)
						$res[] = ($cOperationType=="N"?"NOT":"")."(".$fname." IS NULL)";
					else
						$res[] = ($cOperationType=="N"?" ".$fname." IS NULL OR NOT ":"")."(".$fname." ".$strOperation." '".$DB->ForSql($val)."')";
					break;
				}

				if(strlen($val)>0 && substr($cOperationType, 0, 1)!="N")
					$bFullJoin = true;
				else
					$bWasLeftJoin = true;
			}
		}

		$strResult = "";
		foreach($res as $i=>$val)
		{
			if($i>0)
				$strResult .= (substr($cOperationType, 0, 1)=="N"?" AND ":" OR ");
			$strResult .= "(".$val.")";
		}
		if($strResult!="")
			$strResult = "(".$strResult.")";

		if($bFullJoin && $bWasLeftJoin && substr($cOperationType, 0, 1)!="N")
			$bFullJoin = false;

		return $strResult;
	}

	function _MergeIBArrays($iblock_id, $iblock_code)
	{
		if(!is_array($iblock_id) && (is_numeric($iblock_id) || strlen($iblock_id)>0))
			$iblock_id = Array($iblock_id);

		if(!is_array($iblock_code) && (is_numeric($iblock_code) || strlen($iblock_code)>0))
			$iblock_code = Array($iblock_code);

		if(is_array($iblock_code) && is_array($iblock_id))
			return array_merge($iblock_code, $iblock_id);

		if(is_array($iblock_code))
			return $iblock_code;

		if(is_array($iblock_id))
			return $iblock_id;

		return array();
	}

	function OnSearchGetURL($arFields)
	{
		global $DB, $BX_IBLOCK_DETAIL_URL;

		if($arFields["MODULE_ID"] !== "iblock" || substr($arFields["URL"], 0, 1) !== "=")
			return $arFields["URL"];

		$IBLOCK_ID = IntVal($arFields["PARAM2"]);
		if(array_key_exists($IBLOCK_ID, $BX_IBLOCK_DETAIL_URL))
		{
			$arr_res = $BX_IBLOCK_DETAIL_URL[$IBLOCK_ID];
		}
		else
		{
			$strSql = "SELECT DETAIL_PAGE_URL, SECTION_PAGE_URL, CODE as IBLOCK_CODE, XML_ID as IBLOCK_EXTERNAL_ID, IBLOCK_TYPE_ID FROM b_iblock WHERE ID=".$IBLOCK_ID;
			$res = $DB->Query($strSql);
			if(!($arr_res = $res->Fetch()))
				return "";
			$BX_IBLOCK_DETAIL_URL[$IBLOCK_ID] = $arr_res;
		}

		if(substr($arFields["ITEM_ID"], 0, 1)!='S')
			$url = $arr_res["DETAIL_PAGE_URL"];
		else
			$url = $arr_res["SECTION_PAGE_URL"];

		$arFields["URL"] = LTrim($arFields["URL"], " =");
		parse_str($arFields["URL"], $arr);
		$arr = $arr_res + $arr;
		$arr["LANG_DIR"]=$arFields["DIR"];
		return CIBlock::ReplaceDetailUrl($url, $arr);
	}

	function ReplaceDetailUrl($url, $arr, $server_name = false)
	{
		if($server_name)
		{
			$url = str_replace("#LANG#", $arr["LANG_DIR"], $url);
			if((defined("ADMIN_SECTION") && ADMIN_SECTION===true) || !defined("BX_STARTED"))
			{
				static $lcache;
				if(!is_array($lcache))
					$lcache = array();
				if(!is_set($lcache, $arr["LID"]))
				{
					$db_lang = CLang::GetByID($arr["LID"]);
					$arLang = $db_lang->Fetch();
					$lcache[$arr["LID"]] = $arLang;
				}
				$arLang = $lcache[$arr["LID"]];
				$url = str_replace("#SITE_DIR#", $arLang["DIR"], $url);
				$url = str_replace("#SERVER_NAME#", $arLang["SERVER_NAME"], $url);
			}
			else
			{
				/*
				if(strlen($arr["LANG_DIR"])>0)
				{
					$url = str_replace("#SITE_DIR#", $arr["LANG_DIR"], $url);
					$url = str_replace("#SERVER_NAME#", $arr["SERVER_NAME"], $url);
				}
				else
				{
					*/
					$url = str_replace("#SITE_DIR#", SITE_DIR, $url);
					$url = str_replace("#SERVER_NAME#", SITE_SERVER_NAME, $url);
				//}
			}
		}

		$url = str_replace("#SITE_DIR#", $arr["LANG_DIR"], $url);

		$url = preg_replace("'/+'s", "/", $url);

		$url = str_replace("#ID#", $arr["ID"], $url);
		$url = str_replace("#CODE#", urlencode($arr["CODE"]), $url);
		$url = str_replace("#EXTERNAL_ID#", urlencode($arr["EXTERNAL_ID"]), $url);
		$url = str_replace("#SECTION_ID#", $arr["IBLOCK_SECTION_ID"], $url);
		$url = str_replace("#IBLOCK_TYPE_ID#", urlencode($arr["IBLOCK_TYPE_ID"]), $url);
		$url = str_replace("#IBLOCK_ID#", $arr["IBLOCK_ID"], $url);
		$url = str_replace("#IBLOCK_CODE#", urlencode($arr["IBLOCK_CODE"]), $url);
		$url = str_replace("#IBLOCK_EXTERNAL_ID#", urlencode($arr["IBLOCK_EXTERNAL_ID"]), $url);

		return $url;
	}


	function OnSearchReindex($NS=Array(), $oCallback=NULL, $callback_method="")
	{
		global $DB;

		$strNSJoin1 = "";
		$strNSFilter1 = "";
		$strNSFilter2 = "";
		$strNSFilter3 = "";
		$arResult = Array();
		if($NS["MODULE"]=="iblock" && strlen($NS["ID"])>0)
		{
			$arrTmp = explode(".", $NS["ID"]);
			$strNSFilter1 = " AND B.ID>=".IntVal($arrTmp[0])." ";
			if(substr($arrTmp[1], 0, 1)!='S')
			{
				$strNSFilter2 = " AND BE.ID>".IntVal($arrTmp[1])." ";
			}
			else
			{
				$strNSFilter2 = false;
				$strNSFilter3 = " AND BS.ID>".IntVal(substr($arrTmp[1], 1))." ";
			}
		}
		if($NS["SITE_ID"]!="")
		{
			$strNSJoin1 .= " INNER JOIN b_iblock_site BS ON BS.IBLOCK_ID=B.ID ";
			$strNSFilter1 .= " AND BS.SITE_ID='".$DB->ForSQL($NS["SITE_ID"])."' ";
		}
		$strSql = "
			SELECT B.ID, B.IBLOCK_TYPE_ID, B.INDEX_ELEMENT, B.INDEX_SECTION,
				B.IBLOCK_TYPE_ID, B.CODE as IBLOCK_CODE, B.XML_ID as IBLOCK_EXTERNAL_ID
			FROM b_iblock B
			".$strNSJoin1."
			WHERE B.ACTIVE = 'Y'
				AND (B.INDEX_ELEMENT='Y' OR B.INDEX_SECTION='Y')
				".$strNSFilter1."
			ORDER BY B.ID
		";

		$dbrIBlock = $DB->Query($strSql);
		while($arIBlock = $dbrIBlock->Fetch())
		{
			$IBLOCK_ID = $arIBlock["ID"];

			$arGroups = Array();

			$strSql =
				"SELECT GROUP_ID ".
				"FROM b_iblock_group ".
				"WHERE IBLOCK_ID= ".$IBLOCK_ID." ".
				"	AND PERMISSION>='R' ".
				"	AND GROUP_ID>1 ".
				"ORDER BY GROUP_ID";

			$dbrIBlockGroup = $DB->Query($strSql);
			while($arIBlockGroup = $dbrIBlockGroup->Fetch())
			{
				$arGroups[] = $arIBlockGroup["GROUP_ID"];
				if($arIBlockGroup["GROUP_ID"]==2) break;
			}

			$arSITE = Array();
			$strSql =
				"SELECT SITE_ID ".
				"FROM b_iblock_site ".
				"WHERE IBLOCK_ID= ".$IBLOCK_ID;

			$dbrIBlockSite = $DB->Query($strSql);
			while($arIBlockSite = $dbrIBlockSite->Fetch())
				$arSITE[] = $arIBlockSite["SITE_ID"];

			if($arIBlock["INDEX_ELEMENT"]=='Y' && ($strNSFilter2 !== false))
			{
				$strSql =
					"SELECT BE.ID, BE.NAME, BE.TAGS, ".
					"	".$DB->DateToCharFunction("BE.ACTIVE_FROM")." as DATE_FROM, ".
					"	".$DB->DateToCharFunction("BE.ACTIVE_TO")." as DATE_TO, ".
					"	".$DB->DateToCharFunction("BE.TIMESTAMP_X")." as LAST_MODIFIED, ".
					"	BE.PREVIEW_TEXT_TYPE, BE.PREVIEW_TEXT, ".
					"	BE.DETAIL_TEXT_TYPE, BE.DETAIL_TEXT, ".
					"	BE.XML_ID as EXTERNAL_ID, BE.CODE, ".
					"	BE.IBLOCK_SECTION_ID ".
					"FROM b_iblock_element BE ".
					"WHERE BE.IBLOCK_ID=".$IBLOCK_ID." ".
					"	AND BE.ACTIVE='Y' ".
					CIBlockElement::WF_GetSqlLimit("BE.", "N").
					$strNSFilter2.
					"ORDER BY BE.ID ";

				//For MySQL we have to solve client out of memory
				//problem by limiting the query
				if($DB->type=="MYSQL")
				{
					$limit = 1000;
					$strSql .= " LIMIT ".$limit;
				}
				else
				{
					$limit = false;
				}

				$dbrIBlockElement = $DB->Query($strSql);
				while($arIBlockElement = $dbrIBlockElement->Fetch())
				{
					$DETAIL_URL =
							"=ID=".$arIBlockElement["ID"].
							"&EXTERNAL_ID=".$arIBlockElement["EXTERNAL_ID"].
							"&CODE=".$arIBlockElement["CODE"].
							"&IBLOCK_SECTION_ID=".$arIBlockElement["IBLOCK_SECTION_ID"].
							"&IBLOCK_TYPE_ID=".$arIBlock["IBLOCK_TYPE_ID"].
							"&IBLOCK_ID=".$IBLOCK_ID.
							"&IBLOCK_CODE=".$arIBlock["IBLOCK_CODE"].
							"&IBLOCK_EXTERNAL_ID=".$arIBlock["IBLOCK_EXTERNAL_ID"];

					$BODY =
						($arIBlockElement["PREVIEW_TEXT_TYPE"]=="html" ?
							CSearch::KillTags($arIBlockElement["PREVIEW_TEXT"]) :
							$arIBlockElement["PREVIEW_TEXT"]
						)."\r\n".
						($arIBlockElement["DETAIL_TEXT_TYPE"]=="html" ?
							CSearch::KillTags($arIBlockElement["DETAIL_TEXT"]) :
							$arIBlockElement["DETAIL_TEXT"]
						);

					$dbrProperties = CIBlockElement::GetProperty($IBLOCK_ID, $arIBlockElement["ID"], "sort", "asc", array("ACTIVE"=>"Y", "SEARCHABLE"=>"Y"));
					while($arProperties = $dbrProperties->Fetch())
					{
						$BODY .= "\r\n";
						$UserType = $arProperties["USER_TYPE"];
						if(strlen($UserType) > 0)
						{
							$UserType = CIBlockProperty::GetUserType($UserType);
							if(array_key_exists("GetPublicViewHTML", $UserType))
							{
								$BODY .= CSearch::KillTags(
									call_user_func_array($UserType["GetPublicViewHTML"],
										array(
											$arProperties['ID'],
											array("VALUE" => $arProperties["VALUE"]),
											array(),
										)
									)
								);
							}
						}
						elseif($arProperties["PROPERTY_TYPE"]=='L')
							$BODY .= $arProperties["VALUE_ENUM"];
						else
							$BODY .= $arProperties["VALUE"];
					}

					$Result = Array(
						"ID"=>$arIBlockElement["ID"],
						"LAST_MODIFIED"=>(strlen($arIBlockElement["DATE_FROM"])>0?$arIBlockElement["DATE_FROM"]:$arIBlockElement["LAST_MODIFIED"]),
						"TITLE"=>$arIBlockElement["NAME"],
						"BODY"=>$BODY,
						"TAGS"=>$arIBlockElement["TAGS"],
						"SITE_ID"=>$arSITE,
						"PARAM1"=>$arIBlock["IBLOCK_TYPE_ID"],
						"PARAM2"=>$IBLOCK_ID,
						"DATE_FROM"=>(strlen($arIBlockElement["DATE_FROM"])>0? $arIBlockElement["DATE_FROM"] : false),
						"DATE_TO"=>(strlen($arIBlockElement["DATE_TO"])>0? $arIBlockElement["DATE_TO"] : false),
						"SITE_ID"=>$arSITE,
						"PERMISSIONS"=>$arGroups,
						"URL"=>$DETAIL_URL
						);

					if($oCallback)
					{
						$res = call_user_func(array($oCallback, $callback_method), $Result);
						if(!$res)
							return $IBLOCK_ID.".".$arIBlockElement["ID"];
					}
					else
					{
						$arResult[] = $Result;
					}

					if($limit !== false)
					{
						$limit--;
						if($limit <= 0)
							return $IBLOCK_ID.".".$arIBlockElement["ID"];
					}
				}
			}

			if($arIBlock["INDEX_SECTION"]=='Y')
			{
				$strSql =
					"SELECT BS.ID, BS.NAME, ".
					"	".$DB->DateToCharFunction("BS.TIMESTAMP_X")." as LAST_MODIFIED, ".
					"	BS.DESCRIPTION_TYPE, BS.DESCRIPTION, BS.XML_ID as EXTERNAL_ID, BS.CODE, ".
					"	BS.IBLOCK_ID ".
					"FROM b_iblock_section BS ".
					"WHERE BS.IBLOCK_ID=".$IBLOCK_ID." ".
					"	AND BS.GLOBAL_ACTIVE='Y' ".
					$strNSFilter3.
					"ORDER BY BS.ID ";

				$dbrIBlockSection = $DB->Query($strSql);
				while($arIBlockSection = $dbrIBlockSection->Fetch())
				{
					$DETAIL_URL =
							"=ID=".$arIBlockSection["ID"].
							"&EXTERNAL_ID=".$arIBlockSection["EXTERNAL_ID"].
							"&CODE=".$arIBlockSection["CODE"].
							"&IBLOCK_TYPE_ID=".$arIBlock["IBLOCK_TYPE_ID"].
							"&IBLOCK_ID=".$arIBlockSection["IBLOCK_ID"].
							"&IBLOCK_CODE=".$arIBlock["IBLOCK_CODE"].
							"&IBLOCK_EXTERNAL_ID=".$arIBlock["IBLOCK_EXTERNAL_ID"];
					$BODY =
						($arIBlockSection["DESCRIPTION_TYPE"]=="html" ?
							CSearch::KillTags($arIBlockSection["DESCRIPTION"])
						:
							$arIBlockSection["DESCRIPTION"]
						);

					$Result = Array(
						"ID"=>"S".$arIBlockSection["ID"],
						"LAST_MODIFIED"=>$arIBlockSection["LAST_MODIFIED"],
						"TITLE"=>$arIBlockSection["NAME"],
						"BODY"=>$BODY,
						"SITE_ID"=>$arSITE,
						"PARAM1"=>$arIBlock["IBLOCK_TYPE_ID"],
						"PARAM2"=>$IBLOCK_ID,
						"SITE_ID"=>$arSITE,
						"PERMISSIONS"=>$arGroups,
						"URL"=>$DETAIL_URL,
						);

					if($oCallback)
					{
						$res = call_user_func(array($oCallback, $callback_method), $Result);
						if(!$res)
							return $IBLOCK_ID.".S".$arIBlockSection["ID"];
					}
					else
					{
						$arResult[] = $Result;
					}
				}
			}
			$strNSFilter2="";
			$strNSFilter3="";
		}

		if($oCallback)
			return false;

		return $arResult;
	}

	function __DebugQuery($strSql = false)
	{
		global $DB;
		if($_SERVER['REMOTE_ADDR']!='192.168.0.3')
		{
			$res = $DB->Query($strSql);
			return $res;
		}

		$strSqlCol = " ".$strSql." ";
		$strSqlCol = preg_replace("/('[^']+')/", "<font color=green>\\1</font>", $strSqlCol);
		$strSqlCol = preg_replace("'[ \r\n\t]+(SELECT|FROM|WHERE|GROUP BY|ORDER BY|LIMIT)[ \r\n\t]+'i", " <br><font color=#0000FF>\\1</font> ", $strSqlCol);
		$strSqlCol = preg_replace("'[ \r\n\t]+(INNER JOIN|LEFT JOIN)[ \r\n\t]+'i", " <br><font color=#00AAAA>&nbsp;&nbsp;&nbsp;&nbsp;\\1</font> ", $strSqlCol);
		$strSqlCol = preg_replace("'[ \r\n\t]+(ASC|DESC|AND|OR|NOT|IN|ON|LIKE|DISTINCT)[ \r\n\t]+'i", " <font color=#0000AA>\\1</font> ", $strSqlCol);
		$strSqlCol = preg_replace("'[ \r\n\t]+(DATE_FORMAT|COUNT)\('i", " <font color=blue>\\1</font>(", $strSqlCol);
		$strSqlCol = preg_replace("'[ \r\n\t]+(b_[a-z_]+)[ \r\n\t]+'", " <font color=#006600>\\1</font> ", $strSqlCol);
		echo '<span style="font-size: 11px;">'.$strSqlCol."<br>";
		$res = $DB->Query("EXPLAIN ".$strSql);
		echo '<table border="1" cellpadding="0" cellspacing="0" style="border: black solid 1px; font-size: 11px">';
		echo '<tr>';
		echo '<td>[table]</td>';
		echo '<td>[type]</td>';
		echo '<td>[possible_keys]</td>';
		echo '<td>[key]</td>';
		echo '<td>[key_len]</td>';
		echo '<td>[ref]</td>';
		echo '<td>[rows]</td>';
		echo '<td>[Extra]</td>';
		echo '</tr>';

		while($arR = $res->Fetch())
		{
			echo '<tr>';
			echo '<td>'.$arR['table'].'</td>';
			echo '<td>'.$arR['type'].'</td>';
			echo '<td>'.$arR['possible_keys'].'</td>';
			echo '<td>'.$arR['key'].'&nbsp;</td>';
			echo '<td>'.$arR['key_len'].'&nbsp;</td>';
			echo '<td>'.$arR['ref'].'&nbsp;</td>';
			echo '<td>'.$arR['rows'].'&nbsp;</td>';
			echo '<td>'.$arR['Extra'].'&nbsp;</td>';
			echo '</tr>';
		}
		echo '</table>';
		$st = getmicrotime();
		$res = $DB->Query($strSql);
		echo round(getmicrotime()-$st, 3)." sec<hr></span>";
		return $res;
	}

	function GetElementCount($BID)
	{
		global $DB;
		$strSql = "SELECT COUNT('x') as C FROM b_iblock_element BE WHERE BE.IBLOCK_ID=".IntVal($BID)." AND ((BE.WF_STATUS_ID=1 AND BE.WF_PARENT_ELEMENT_ID IS NULL) OR BE.WF_NEW='Y')";
		$res = $DB->Query($strSql);
		$ar = $res->Fetch();
		return IntVal($ar["C"]);
	}

	function ResizePicture($arFile, $arResize)
	{
		if(strlen($arFile["tmp_name"]) <= 0)
			return $arFile;

		if(array_key_exists("error", $arFile) && $arFile["error"] !== 0)
			return GetMessage("IBLOCK_BAD_FILE_ERROR");

		$file = $arFile["tmp_name"];

		if(!file_exists($file) && !is_file($file))
			return GetMessage("IBLOCK_BAD_FILE_NOT_FOUND");

		$width = intval($arResize["WIDTH"]);
		$height = intval($arResize["HEIGHT"]);

		if($width <= 0 && $height <= 0)
			return $arFile;

		$orig = @getimagesize($file);
		if(!is_array($orig))
			return GetMessage("IBLOCK_BAD_FILE_NOT_PICTURE");

		if(($width > 0 && $orig[0] > $width) || ($height > 0 && $orig[1] > $height))
		{
			$width_orig = $orig[0];
			$height_orig = $orig[1];

			if($width <= 0)
			{
				$width = ($height / $height_orig) * $width_orig;
			}

			if($height <= 0)
			{
				$height = ($width / $width_orig) * $height_orig;
			}

			if($width && ($width_orig < $height_orig))
				$width = ($height / $height_orig) * $width_orig;
			else
				$height = ($width / $width_orig) * $height_orig;

			$image_type = $orig[2];
			if($image_type == IMAGETYPE_JPEG)
				$image = imagecreatefromjpeg($file);
			elseif($image_type == IMAGETYPE_GIF)
				$image = imagecreatefromgif($file);
			elseif($image_type == IMAGETYPE_PNG)
				$image = imagecreatefrompng($file);
			else
				return GetMessage("IBLOCK_BAD_FILE_UNSUPPORTED");

			$image_p = imagecreatetruecolor($width, $height);
			if($image_type == IMAGETYPE_JPEG)
			{
				imagecopyresized($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
				imagejpeg($image_p, $file);
			}
			elseif($image_type == IMAGETYPE_GIF && function_exists("imagegif"))
			{
				imagetruecolortopalette($image_p, true, imagecolorstotal($image));
				imagepalettecopy($image_p, $image);

				//Save transparency for GIFs
				$transparentcolor = imagecolortransparent($image);
				if($transparentcolor >= 0 && $transparentcolor < imagecolorstotal($image))
				{
					$transparentcolor = imagecolortransparent($image_p, $transparentcolor);
					imagefilledrectangle($image_p, 0, 0, $width, $height, $transparentcolor);
				}

				imagecopyresized($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
				imagegif($image_p, $file);
			}
			else
			{
				//Save transparency for PNG
				$transparentcolor = imagecolorallocate($image_p, 0, 0, 0);
				$transparentcolor = imagecolortransparent($image_p, $transparentcolor);

				imagecopyresized($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
				imagepng($image_p, $file);
			}

			imagedestroy($image);
			imagedestroy($image_p);

			$arFile["size"] = filesize($file);
			return $arFile;
		}
		else
		{
			return $arFile;
		}
	}
}
?>
