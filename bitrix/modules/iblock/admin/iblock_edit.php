<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/iblock.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");

IncludeModuleLangFile(__FILE__);

$arIBTYPE = CIBlockType::GetByIDLang($type, LANG);

if($arIBTYPE!==false):

$strWarning="";
$bVarsFromForm = false;
$ID=IntVal($ID);

$Perm = CIBlock::GetPermission($ID);
if($Perm>="X" && $REQUEST_METHOD=="POST" && strlen($_POST["Update"])>0 && !isset($_POST["propedit"]) && check_bitrix_sessid())
{
	$DB->StartTransaction();

	$arPICTURE = $HTTP_POST_FILES["PICTURE"];
	$arPICTURE["del"] = ${"PICTURE_del"};
	$arPICTURE["MODULE_ID"] = "iblock";

	if ($VERSION != 2) $VERSION = 1;
	if ($RSS_ACTIVE != "Y") $RSS_ACTIVE = "N";
	if ($RSS_FILE_ACTIVE != "Y") $RSS_FILE_ACTIVE = "N";
	if ($RSS_YANDEX_ACTIVE != "Y") $RSS_YANDEX_ACTIVE = "N";

	$ib = new CIBlock;
	$arFields = Array(
		"ACTIVE"=>$ACTIVE,
		"NAME"=>$NAME,
		"CODE"=>$CODE,
		"LIST_PAGE_URL"=>$LIST_PAGE_URL,
		"DETAIL_PAGE_URL"=>$DETAIL_PAGE_URL,
		"INDEX_ELEMENT"=>$INDEX_ELEMENT,
		"IBLOCK_TYPE_ID"=>$type,
		"LID"=>$LID,
		"SORT"=>$SORT,
		"PICTURE"=>$arPICTURE,
		"DESCRIPTION"=>$DESCRIPTION,
		"DESCRIPTION_TYPE"=>$DESCRIPTION_TYPE,
		"EDIT_FILE_BEFORE"=>$EDIT_FILE_BEFORE,
		"EDIT_FILE_AFTER"=>$EDIT_FILE_AFTER,
		"WORKFLOW"=>$WORKFLOW,
		"SECTION_CHOOSER"=>$SECTION_CHOOSER,
		"FIELDS" => $_REQUEST["FIELDS"],
		//MESSAGES
		"ELEMENTS_NAME"=>$ELEMENTS_NAME,
		"ELEMENT_NAME"=>$ELEMENT_NAME,
		"ELEMENT_ADD"=>$ELEMENT_ADD,
		"ELEMENT_EDIT"=>$ELEMENT_EDIT,
		"ELEMENT_DELETE"=>$ELEMENT_DELETE,
		);

	if($arIBTYPE["SECTIONS"]=="Y")
	{
		$arFields["SECTION_PAGE_URL"]=$SECTION_PAGE_URL;
		$arFields["INDEX_SECTION"]=$INDEX_SECTION;
		//MESSAGES
		$arFields["SECTIONS_NAME"]=$SECTIONS_NAME;
		$arFields["SECTION_NAME"]=$SECTION_NAME;
		$arFields["SECTION_ADD"]=$SECTION_ADD;
		$arFields["SECTION_EDIT"]=$SECTION_EDIT;
		$arFields["SECTION_DELETE"]=$SECTION_DELETE;
	}

	if(COption::GetOptionString("iblock", "show_xml_id", "N")=="Y" && is_set($_POST, "XML_ID"))
		$arFields["XML_ID"] = $_POST["XML_ID"];

	if($arIBTYPE["IN_RSS"]=="Y")
	{
		$arFields = array_merge($arFields, Array(
			"RSS_ACTIVE"=>$RSS_ACTIVE,
			"RSS_FILE_ACTIVE"=>$RSS_FILE_ACTIVE,
			"RSS_YANDEX_ACTIVE"=>$RSS_YANDEX_ACTIVE,
			"RSS_FILE_LIMIT"=>$RSS_FILE_LIMIT,
			"RSS_FILE_DAYS"=>$RSS_FILE_DAYS,
			"RSS_TTL"=>$RSS_TTL)
			);
	}

	if($Perm=="X")
		$arFields["GROUP_ID"]=$GROUP;

	if($ID>0)
		$res = $ib->Update($ID, $arFields);
	else
	{
		$arFields["VERSION"]=$VERSION;
		$ID = $ib->Add($arFields);
		$res = ($ID>0);
	}

	if(!$res)
	{
		$strWarning .= $ib->LAST_ERROR."<br>";
		$bVarsFromForm = true;
	}
	else
	{
		// RSS agent creation
		if ($RSS_FILE_ACTIVE == "Y")
		{
			CAgent::RemoveAgent("CIBlockRSS::PreGenerateRSS(".$ID.", false);", "iblock");
			CAgent::AddAgent("CIBlockRSS::PreGenerateRSS(".$ID.", false);", "iblock", "N", IntVal($RSS_TTL)*60*60, "", "Y");
		}
		else
			CAgent::RemoveAgent("CIBlockRSS::PreGenerateRSS(".$ID.", false);", "iblock");

		if ($RSS_YANDEX_ACTIVE == "Y")
		{
			CAgent::RemoveAgent("CIBlockRSS::PreGenerateRSS(".$ID.", true);", "iblock");
			CAgent::AddAgent("CIBlockRSS::PreGenerateRSS(".$ID.", true);", "iblock", "N", IntVal($RSS_TTL)*60*60, "", "Y");
		}
		else
			CAgent::RemoveAgent("CIBlockRSS::PreGenerateRSS(".$ID.", true);", "iblock");

		/********************/
		$props = CIBlock::GetProperties($ID);
		while($p = $props->Fetch())
		{
			if(${"PROPERTY_".$p["ID"]."_DEL"} == "Y")
			{
				if(!CIBlockProperty::Delete($p["ID"]) && ($ex = $APPLICATION->GetException()))
				{
					$strWarning .= GetMessage("IBLOCK_PROPERTY_ERROR").": ".$ex->GetString()."<br>";
					$bVarsFromForm = true;
				}
			}
			else
			{
				$arFields = Array(
					"NAME" => ${"PROPERTY_".$p["ID"]."_NAME"},
					"ACTIVE" => ${"PROPERTY_".$p["ID"]."_ACTIVE"},
					"SORT" => ${"PROPERTY_".$p["ID"]."_SORT"},
					"DEFAULT_VALUE" => ${"PROPERTY_".$p["ID"]."_DEFAULT_VALUE"},
					"CODE" => ${"PROPERTY_".$p["ID"]."_CODE"},
					"ROW_COUNT" => ${"PROPERTY_".$p["ID"]."_ROW_COUNT"},
					"COL_COUNT" => ${"PROPERTY_".$p["ID"]."_COL_COUNT"},
					"LINK_IBLOCK_ID" => ${"PROPERTY_".$p["ID"]."_LINK_IBLOCK_ID"},
					"WITH_DESCRIPTION" => ${"PROPERTY_".$p["ID"]."_WITH_DESCRIPTION"},
					"FILTRABLE" => ${"PROPERTY_".$p["ID"]."_FILTRABLE"},
					"SEARCHABLE" => ${"PROPERTY_".$p["ID"]."_SEARCHABLE"},
					"MULTIPLE"  => ${"PROPERTY_".$p["ID"]."_MULTIPLE"},
					"MULTIPLE_CNT" => ${"PROPERTY_".$p["ID"]."_MULTIPLE_CNT"},
					"IS_REQUIRED" => ${"PROPERTY_".$p["ID"]."_IS_REQUIRED"},
					"FILE_TYPE" => ${"PROPERTY_".$p["ID"]."_FILE_TYPE"},
					"LIST_TYPE" => ${"PROPERTY_".$p["ID"]."_LIST_TYPE"},
					"IBLOCK_ID" => $ID
					);
				if(strstr(${"PROPERTY_".$p["ID"]."_PROPERTY_TYPE"}, ":")!==false)
				{
					list($arFields["PROPERTY_TYPE"],$arFields["USER_TYPE"])=explode(":", ${"PROPERTY_".$p["ID"]."_PROPERTY_TYPE"}, 2);
				}
				else
				{
					$arFields["PROPERTY_TYPE"]=${"PROPERTY_".$p["ID"]."_PROPERTY_TYPE"};
					$arFields["USER_TYPE"]=false;
				}
// 				if($arFields["USER_TYPE"]!==false
// 				&& array_key_exists("ConvertFromDB", $arUserType = CIBlockProperty::GetUserType($arFields["USER_TYPE"])))
// 				{
// 					$ar=call_user_func_array($arUserType["ConvertFromDB"],
// 						array(
// 							$arProperty,
// 							array(
// 								"VALUE"=>$arFields["DEFAULT_VALUE"],
// 								"DESCRIPTION"=>""
// 							),
// 						));
// 						$arFields["DEFAULT_VALUE"]=$ar["VALUE"];
// 				}

				if(COption::GetOptionString("iblock", "show_xml_id", "N")=="Y" && is_set($_POST, "PROPERTY_".$p["ID"]."_XML_ID"))
					$arFields["XML_ID"] = $_POST["PROPERTY_".$p["ID"]."_XML_ID"];

				if(isset($_POST["PROPERTY_".$p["ID"]."_VALUES"]))
				{
					$arFields["VALUES"] = Array();
					$arDEFS = ${"PROPERTY_".$p["ID"]."_VALUES_DEF"};
					if(!is_array($arDEFS))
						$arDEFS = Array();
					$arSORTS = ${"PROPERTY_".$p["ID"]."_VALUES_SORT"};
					if(!is_array($arSORTS))
						$arSORTS = Array();
					$arXML = ${"PROPERTY_".$p["ID"]."_VALUES_XML"};
					if(!is_array($arXML))
						$arXML = Array();
					foreach(${"PROPERTY_".$p["ID"]."_VALUES"} as $key=>$val)
					{
						$arFields["VALUES"][$key] = Array(
							"VALUE" => $val,
							"DEF" => (in_array($key, $arDEFS)?"Y":"N")
						);
						if(IntVal($arSORTS[$key])>0)
							$arFields["VALUES"][$key]["SORT"] = IntVal($arSORTS[$key]);
						if(strlen($arXML[$key])>0)
							$arFields["VALUES"][$key]["XML_ID"] = $arXML[$key];
					}
				}
				$ibp = new CIBlockProperty;
				$res = $ibp->Update($p["ID"], $arFields);
				if(!$res)
				{
					$strWarning .= GetMessage("IBLOCK_PROPERTY_ERROR").": ".$ibp->LAST_ERROR."<br>";
					$bVarsFromForm = true;
				}
			}
		}

		for($i=0; $i<5; $i++)
		{
			if(strlen(${"PROPERTY_n".$i."_NAME"})<=0) continue;

			$arFields = Array(
				"NAME" 			=> ${"PROPERTY_n".$i."_NAME"},
				"ACTIVE" 		=> ${"PROPERTY_n".$i."_ACTIVE"},
				"SORT" 			=> ${"PROPERTY_n".$i."_SORT"},
				"DEFAULT_VALUE" => ${"PROPERTY_n".$i."_DEFAULT_VALUE"},
				"CODE" 			=> ${"PROPERTY_n".$i."_CODE"},
				"ROW_COUNT" 	=> ${"PROPERTY_n".$i."_ROW_COUNT"},
				"COL_COUNT" 	=> ${"PROPERTY_n".$i."_COL_COUNT"},
				"LINK_IBLOCK_ID"=> ${"PROPERTY_n".$i."_LINK_IBLOCK_ID"},
				"WITH_DESCRIPTION"=> ${"PROPERTY_n".$i."_WITH_DESCRIPTION"},
				"SEARCHABLE"	=> ${"PROPERTY_n".$i."_SEARCHABLE"},
				"FILTRABLE"		=> ${"PROPERTY_n".$i."_FILTRABLE"},
				"MULTIPLE"	 	=> ${"PROPERTY_n".$i."_MULTIPLE"},
				"MULTIPLE_CNT" 	=> ${"PROPERTY_n".$i."_MULTIPLE_CNT"},
				"IS_REQUIRED"	=> ${"PROPERTY_n".$i."_IS_REQUIRED"},
				"FILE_TYPE" 	=> ${"PROPERTY_n".$i."_FILE_TYPE"},
				"LIST_TYPE" 	=> ${"PROPERTY_n".$i."_LIST_TYPE"},
				"IBLOCK_ID" 	=> $ID
			);
			if(strstr(${"PROPERTY_n".$i."_PROPERTY_TYPE"}, ":")!==false)
			{
				list($arFields["PROPERTY_TYPE"],$arFields["USER_TYPE"])=explode(":", ${"PROPERTY_n".$i."_PROPERTY_TYPE"}, 2);
			}
			else
			{
				$arFields["PROPERTY_TYPE"]=${"PROPERTY_n".$i."_PROPERTY_TYPE"};
				$arFields["USER_TYPE"]=false;
			}
// 			if($arFields["USER_TYPE"]!==false
// 			&& array_key_exists("ConvertFromDB", $arUserType = CIBlockProperty::GetUserType($arFields["USER_TYPE"])))
// 			{
// 				$ar=call_user_func_array($arUserType["ConvertFromDB"],
// 					array(
// 						$arProperty,
// 						array(
// 							"VALUE"=>$arFields["DEFAULT_VALUE"],
// 							"DESCRIPTION"=>""
// 						),
// 					));
// 					$arFields["DEFAULT_VALUE"]=$ar["VALUE"];
// 			}

			if(COption::GetOptionString("iblock", "show_xml_id", "N")=="Y" && is_set($_POST, "PROPERTY_n".$i."_XML_ID"))
				$arFields["XML_ID"] = $_POST["PROPERTY_n".$i."_XML_ID"];

			if(isset($_POST["PROPERTY_n".$i."_CNT"]))
			{
				$arFields["VALUES"] = Array();
				$arDEFS = ${"PROPERTY_n".$i."_VALUES_DEF"};
				if(!is_array($arDEFS))
					$arDEFS = Array();
				$arSORTS = ${"PROPERTY_n".$i."_VALUES_SORT"};
				if(!is_array($arSORTS))
					$arSORTS = Array();
				$arXML = ${"PROPERTY_".$p["ID"]."_VALUES_XML"};
				if(!is_array($arXML))
					$arXML = Array();
				foreach(${"PROPERTY_n".$i."_VALUES"} as $key=>$val)
				{
					$arFields["VALUES"][$key] = Array(
						"VALUE" => $val,
						"DEF" => (in_array($key, $arDEFS)?"Y":"N")
					);
					if(IntVal($arSORTS[$key])>0)
						$arFields["VALUES"][$key]["SORT"] = IntVal($arSORTS[$key]);
					if(strlen($arXML[$key])>0)
						$arFields["VALUES"][$key]["XML_ID"] = $arXML[$key];
				}
			}

			$ibp = new CIBlockProperty;
			$PropID = $ibp->Add($arFields);
			if(IntVal($PropID)<=0)
			{
				$strWarning .= $ibp->LAST_ERROR."<br>";
				$bVarsFromForm = true;
			}
		}
		/*******************************************/

		if(!$bVarsFromForm && $arIBTYPE["IN_RSS"]=="Y")
		{
			CIBlockRSS::Delete($ID);
			reset($arNodesRSS);
			while (list($key, $val) = each($arNodesRSS))
			{
				if (strlen(${"RSS_NODE_VALUE_".$key})<=0) continue;
				CIBlockRSS::Add($ID, $val, ${"RSS_NODE_VALUE_".$key});
			}
		}

		if(!$bVarsFromForm)
		{
			$DB->Commit();
			if(strlen($apply)<=0)
			{
				if(strlen($_REQUEST["return_url"])>0)
					LocalRedirect($_REQUEST["return_url"]);
				else
					LocalRedirect("/bitrix/admin/iblock_admin.php?type=".$type."&lang=".LANG."&admin=".($_REQUEST["admin"]=="Y"? "Y": "N"));
			}
			LocalRedirect("/bitrix/admin/iblock_edit.php?type=".$type."&tabControl_active_tab=".urlencode($tabControl_active_tab)."&lang=".LANG."&ID=".$ID."&admin=".($_REQUEST["admin"]=="Y"? "Y": "N").(strlen($_REQUEST["return_url"])>0? "&return_url=".urlencode($_REQUEST["return_url"]): ""));
		}
	}

	$DB->Rollback();
}

if($ID>0)
	$APPLICATION->SetTitle($arIBTYPE["NAME"].": ".GetMessage("IBLOCK_TITLE").": ".GetMessage("IBLOCK_EDIT_TITLE"));
else
	$APPLICATION->SetTitle($arIBTYPE["NAME"].": ".GetMessage("IBLOCK_TITLE").": ".GetMessage("IBLOCK_NEW_TITLE"));


$str_ACTIVE="Y";
$str_WORKFLOW="Y";
$str_SECTION_CHOOSER="L";
$str_INDEX_ELEMENT="Y";
$str_INDEX_SECTION="Y";
$str_PROPERTY_FILE_TYPE = "jpg, gif, bmp, png, jpeg";
$str_LIST_PAGE_URL="#SITE_DIR#/".$arIBTYPE["ID"]."/index.php?ID=#IBLOCK_ID#";
$str_SECTION_PAGE_URL="#SITE_DIR#/".$arIBTYPE["ID"]."/list.php?SECTION_ID=#ID#";
$str_DETAIL_PAGE_URL="#SITE_DIR#/".$arIBTYPE["ID"]."/detail.php?ID=#ID#";
$str_SORT="500";
$str_VERSION="1";
$str_RSS_ACTIVE="N";
$str_RSS_TTL="24";
$str_RSS_FILE_ACTIVE="N";
$str_RSS_FILE_LIMIT="10";
$str_RSS_FILE_DAYS="7";
$str_RSS_YANDEX_ACTIVE="N";

$ib = new CIBlock;
$ib_result = $ib->GetByID($ID);
if(!$ib_result->ExtractFields("str_"))
	$ID=0;
else
{
	$str_LID = Array();
	$db_LID = CIBlock::GetSite($ID);
	while($ar_LID = $db_LID->Fetch())
		$str_LID[] = $ar_LID["LID"];
}

if(isset($_POST["propedit"]) && is_array($_POST["propedit"]))
{
	$prop_id = array_keys($_POST["propedit"]);
	$str_PROPERTY_ID = $prop_id[0];

	if(IntVal($str_PROPERTY_ID)>0)
	{
		$db_Prop = CIBlockProperty::GetByID(IntVal($str_PROPERTY_ID));
		if(($res = $db_Prop->Fetch()) && $res["IBLOCK_ID"]==$ID)
			$str_PROPERTY_ID = IntVal($str_PROPERTY_ID);
		else
			$str_PROPERTY_ID = "";
	}
}

if(IntVal($str_PROPERTY_ID)>0 || (strlen($str_PROPERTY_ID)>0 && $str_PROPERTY_ID[0]=="n"))
	$APPLICATION->SetTitle($arIBTYPE["NAME"].": ".$_POST["NAME"].": ".GetMessage("IBLOCK_PROP_PARAMS"));

endif; //$arIBTYPE!==false

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if($arIBTYPE!==false):

$bVarsFromForm = ($bVarsFromForm || isset($_POST["propedit"]));

if($bVarsFromForm)
{
	$str_LID = $LID;
	$ACTIVE = ($ACTIVE != "Y"? "N":"Y");
	$WORKFLOW = ($WORKFLOW != "N"? "Y": "N");
	$RSS_FILE_ACTIVE = ($RSS_FILE_ACTIVE != "Y"? "N":"Y");
	$RSS_YANDEX_ACTIVE = ($RSS_YANDEX_ACTIVE != "Y"? "N":"Y");
	$RSS_ACTIVE = ($RSS_ACTIVE != "Y"? "N":"Y");
	$VERSION = ($VERSION != 2? 1:2);
	$DB->InitTableVarsForEdit("b_iblock", "", "str_");
}

if($Perm>="X"):
	$aMenu = array(
		array(
			"TEXT"=>GetMessage("IBLOCK_BACK_TO_ADMIN"),
			"LINK"=>'iblock_admin.php?lang='.$lang.'&type='.urlencode($type).'&admin='.($_REQUEST["admin"]=="Y"? "Y": "N"),
			"ICON"=>"btn_list",
		)
	);

$context = new CAdminContextMenu($aMenu);
$context->Show();

$u = new CAdminPopup("mnu_LIST_PAGE_URL", "mnu_LIST_PAGE_URL",
	Array(
		Array(
			"TEXT"=>GetMessage("IB_E_URL_POPUP_SITE_DIR"),
			"ONCLICK" => "__SetUrlVar('#SITE_DIR#', 'LIST_PAGE_URL')",
			"TITLE"=> "#SITE_DIR# - ".GetMessage("IB_E_URL_POPUP_SITE_DIR"),
			),
		Array(
			"TEXT"=>GetMessage("IB_E_URL_POPUP_SERVER_NAME"),
			"TITLE"=>"#SERVER_NAME# - ".GetMessage("IB_E_URL_POPUP_SERVER_NAME"),
			"ONCLICK" => "__SetUrlVar('#SERVER_NAME#', 'LIST_PAGE_URL')",
			),
		Array(
			"TEXT"=>GetMessage("IB_E_URL_POPUP_IBLOCK_ID"),
			"TITLE"=>"#IBLOCK_ID# - ".GetMessage("IB_E_URL_POPUP_IBLOCK_ID"),
			"ONCLICK" => "__SetUrlVar('#IBLOCK_ID#', 'LIST_PAGE_URL')",
			),
		)
	);
$u->Show();
$u = new CAdminPopup("mnu_SECTION_PAGE_URL", "mnu_SECTION_PAGE_URL",
	Array(
		Array(
			"TEXT"=>GetMessage("IB_E_URL_POPUP_SITE_DIR"),
			"TITLE"=>"#SITE_DIR# - ".GetMessage("IB_E_URL_POPUP_SITE_DIR"),
			"ONCLICK" => "__SetUrlVar('#SITE_DIR#', 'SECTION_PAGE_URL')",
			),
		Array(
			"TEXT"=>GetMessage("IB_E_URL_POPUP_SERVER_NAME"),
			"TITLE"=>"#SERVER_NAME# - ".GetMessage("IB_E_URL_POPUP_SERVER_NAME"),
			"ONCLICK" => "__SetUrlVar('#SERVER_NAME#', 'SECTION_PAGE_URL')",
			),
		Array(
			"TEXT"=>GetMessage("IB_E_URL_POPUP_SEC_ID"),
			"TITLE"=>"#ID# - ".GetMessage("IB_E_URL_POPUP_SEC_ID"),
			"ONCLICK" => "__SetUrlVar('#ID#', 'SECTION_PAGE_URL')",
			),
		Array(
			"TEXT"=>GetMessage("IB_E_URL_POPUP_EXT_SEC_ID"),
			"TITLE"=>"#EXTERNAL_ID# - ".GetMessage("IB_E_URL_POPUP_EXT_SEC_ID"),
			"ONCLICK" => "__SetUrlVar('#EXTERNAL_ID#', 'SECTION_PAGE_URL')",
			),
		Array(
			"TEXT"=>GetMessage("IB_E_URL_POPUP_IBLOCK_ID"),
			"TITLE"=>"#IBLOCK_ID# - ".GetMessage("IB_E_URL_POPUP_IBLOCK_ID"),
			"ONCLICK" => "__SetUrlVar('#IBLOCK_ID#', 'SECTION_PAGE_URL')",
			),
		Array(
			"TEXT"=>GetMessage("IB_E_URL_POPUP_IBLOCK_CODE"),
			"TITLE"=>"#IBLOCK_CODE# - ".GetMessage("IB_E_URL_POPUP_IBLOCK_CODE"),
			"ONCLICK" => "__SetUrlVar('#IBLOCK_CODE#', 'SECTION_PAGE_URL')",
			),
		Array(
			"TEXT"=>GetMessage("IB_E_URL_POPUP_IBLOCK_EXT"),
			"TITLE"=>"#IBLOCK_EXTERNAL_ID# - ".GetMessage("IB_E_URL_POPUP_IBLOCK_EXT"),
			"ONCLICK" => "__SetUrlVar('#IBLOCK_EXTERNAL_ID#', 'SECTION_PAGE_URL')",
			),
		Array(
			"TEXT"=>GetMessage("IB_E_URL_POPUP_IBLOCK_TYPE"),
			"TITLE"=>"#IBLOCK_TYPE_ID# - ".GetMessage("IB_E_URL_POPUP_IBLOCK_TYPE"),
			"ONCLICK" => "__SetUrlVar('#IBLOCK_TYPE_ID#', 'SECTION_PAGE_URL')",
			),
		)
	);
$u->Show();
$u = new CAdminPopup("mnu_DETAIL_PAGE_URL", "mnu_DETAIL_PAGE_URL",
	Array(
		Array(
			"TEXT"=>GetMessage("IB_E_URL_POPUP_SITE_DIR"),
			"TITLE"=>"#SITE_DIR# - ".GetMessage("IB_E_URL_POPUP_SITE_DIR"),
			"ONCLICK" => "__SetUrlVar('#SITE_DIR#', 'DETAIL_PAGE_URL')",
			),
		Array(
			"TEXT"=>GetMessage("IB_E_URL_POPUP_SERVER_NAME"),
			"TITLE"=>"#SERVER_NAME# - ".GetMessage("IB_E_URL_POPUP_SERVER_NAME"),
			"ONCLICK" => "__SetUrlVar('#SERVER_NAME#', 'DETAIL_PAGE_URL')",
			),
		Array(
			"TEXT"=>GetMessage("IB_E_URL_POPUP_IBLOCK_ID"),
			"TITLE"=>"#IBLOCK_ID#".GetMessage("IB_E_URL_POPUP_IBLOCK_ID"),
			"ONCLICK" => "__SetUrlVar('#IBLOCK_ID#', 'DETAIL_PAGE_URL')",
			),
		Array(
			"TEXT"=>GetMessage("IB_E_URL_POPUP_EL_ID"),
			"TITLE"=>"#ID# - ".GetMessage("IB_E_URL_POPUP_EL_ID"),
			"ONCLICK" => "__SetUrlVar('#ID#', 'DETAIL_PAGE_URL')",
			),
		Array(
			"TEXT"=>GetMessage("IB_E_URL_POPUP_EL_CODE"),
			"TITLE"=>"#CODE# - ".GetMessage("IB_E_URL_POPUP_EL_CODE"),
			"ONCLICK" => "__SetUrlVar('#CODE#', 'DETAIL_PAGE_URL')",
			),
		Array(
			"TEXT"=>GetMessage("IB_E_URL_POPUP_EL_EXT_ID"),
			"TITLE"=>"#EXTERNAL_ID# - ".GetMessage("IB_E_URL_POPUP_EL_EXT_ID"),
			"ONCLICK" => "__SetUrlVar('#EXTERNAL_ID#', 'DETAIL_PAGE_URL')",
			),
		Array(
			"TEXT"=>GetMessage("IB_E_URL_POPUP_SEC_ID"),
			"TITLE"=>"SECTION_ID - ".GetMessage("IB_E_URL_POPUP_SEC_ID"),
			"ONCLICK" => "__SetUrlVar('#SECTION_ID#', 'DETAIL_PAGE_URL')",
			),
		)
	);
$u->Show();
?>
<script>
	function __SetUrlVar(id, el_id)
	{
		var mnu_list = eval('mnu_'+el_id);
		var obj_ta = document.getElementById(el_id);
		obj_ta.focus();
		/*
		if(obj_ta.isTextEdit)
		{
			obj_ta.focus();
			var sel = document.selection;
			var rng = sel.createRange();
			rng.colapse;
			alert(rng.text);
			if((sel.type == "Text" || sel.type == "None") && rng != null)
				rng.text = id;
		}
		else
		*/
			obj_ta.value += id;

		mnu_list.PopupHide();
		obj_ta.focus();
	}
	function __ShUrlVars(div, el_id)
	{
		var pos = jsUtils.GetRealPos(div);
		var mnu_list = eval('mnu_'+el_id);
		setTimeout(function(){mnu_list.PopupShow(pos)}, 10);
	}
</script>

<form method="POST" name="frm" id="frm" action="iblock_edit.php?type=<?echo htmlspecialchars($type)?>&amp;lang=<?echo LANG?>&amp;admin=<?echo ($_REQUEST["admin"]=="Y"? "Y": "N")?>"  ENCTYPE="multipart/form-data">
<?=bitrix_sessid_post()?>
<?echo GetFilterHiddens("find_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="ID" value="<?echo $ID?>">
<?if(strlen($_REQUEST["return_url"])>0):?><input type="hidden" name="return_url" value="<?=htmlspecialchars($_REQUEST["return_url"])?>"><?endif?>
<?CAdminMessage::ShowOldStyleError($strWarning);?>
<?
function show_post_var($vname, $vvalue, $var_stack=array())
{
	if(is_array($vvalue))
	{
		foreach($vvalue as $key=>$value)
			show_post_var($key, $value, array_merge($var_stack ,array($vname)));
	}
	else
	{
		if(count($var_stack)>0)
		{
			$var_name=$var_stack[0];
			for($i=1; $i<count($var_stack);$i++)
				$var_name.="[".$var_stack[$i]."]";
			$var_name.="[".$vname."]";
		}
		else
			$var_name=$vname;
		?><input type="hidden" name="<?echo htmlspecialchars($var_name)?>" value="<?echo htmlspecialchars($vvalue)?>"><?
	}
}

if(IntVal($str_PROPERTY_ID)>0 || (strlen($str_PROPERTY_ID)>0 && $str_PROPERTY_ID[0]=="n")):

	foreach($_POST as $key => $value)
	{
		if($key!="propedit" && substr($key, 0, strlen("PROPERTY_".$str_PROPERTY_ID."_")) != "PROPERTY_".$str_PROPERTY_ID."_")
		{
			show_post_var($key, $value);
		}
	}

	${"PROPERTY_MULTIPLE_".$str_PROPERTY_ID} = (${"PROPERTY_".$str_PROPERTY_ID."_MULTIPLE"}!="Y"?"N":"Y");
	${"PROPERTY_IS_REQUIRED_".$str_PROPERTY_ID} = (${"PROPERTY_".$str_PROPERTY_ID."_IS_REQUIRED"}!=="Y"?"N":"Y");
	${"PROPERTY_ACTIVE_".$str_PROPERTY_ID} = (${"PROPERTY_".$str_PROPERTY_ID."_ACTIVE"}!="Y"?"N":"Y");
	${"PROPERTY_DEL_".$str_PROPERTY_ID} = (${"PROPERTY_".$str_PROPERTY_ID."_DEL"}!="Y"?"N":"Y");
	if(substr(":", ${"PROPERTY_".$str_PROPERTY_ID."_PROPERTY_TYPE"})!==false)
	{
		list(${"PROPERTY_".$str_PROPERTY_ID."_PROPERTY_TYPE"},${"PROPERTY_".$str_PROPERTY_ID."_USER_TYPE"})=explode(":", ${"PROPERTY_".$str_PROPERTY_ID."_PROPERTY_TYPE"}, 2);
	}
	else
		${"PROPERTY_".$str_PROPERTY_ID."_USER_TYPE"}="";
	$tmp_PROP_ID = $str_PROPERTY_ID;
	$DB->InitTableVarsForEdit("b_iblock_property", "PROPERTY_".$str_PROPERTY_ID."_", "str_PROPERTY_");
	$str_PROPERTY_ID = $tmp_PROP_ID;

	$aTabs = array(
		array(
			"DIV" => $_REQUEST["tabControl_active_tab"],
			"TAB" => GetMessage("IB_E_TAB"),
			"ICON"=>"iblock_property",
			"TITLE"=>GetMessage("IB_E_TAB"),
		),
	);
	$tabControl = new CAdminTabControl("tabControl", $aTabs);
	$tabControl->Begin();
	$tabControl->BeginNextTab();
	?>
		<tr>
			<td width="40%">ID:</td>
			<td width="60%"><?echo ($str_PROPERTY_ID>0?$str_PROPERTY_ID:GetMessage("IBLOCK_PROP_NEW"))?></td>
		</tr>
		<tr>
			<td ><?echo GetMessage("IBLOCK_PROP_CODE_DET")?></td>
			<td><input type="text" size="20" maxlength="20"  name="PROPERTY_<?echo $str_PROPERTY_ID?>_CODE" value="<?echo $str_PROPERTY_CODE?>"></td>
		</tr>
		<?if(COption::GetOptionString("iblock", "show_xml_id", "N")=="Y"):?>
		<tr>
			<td><?echo GetMessage("IBLOCK_EXTERNAL_CODE")?></td>
			<td><input type="text" size="30" maxlength="50"  name="PROPERTY_<?echo $str_PROPERTY_ID?>_XML_ID" value="<?echo $str_PROPERTY_XML_ID?>"></td>
		</tr>
		<?endif?>
		<tr>
			<td><?echo GetMessage("IBLOCK_PROP_NAME_DET")?></td>
			<td ><input type="text" size="30" maxlength="50"  name="PROPERTY_<?echo $str_PROPERTY_ID?>_NAME" value="<?echo $str_PROPERTY_NAME?>"></td>
		</tr>
		<tr>
			<td><label for="PROPERTY_<?echo $str_PROPERTY_ID?>_ACTIVE"><?echo GetMessage("IBLOCK_PROP_ACT")?></label></td>
			<td><input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_ACTIVE" value="N">
			<input type="checkbox" id="PROPERTY_<?echo $str_PROPERTY_ID?>_ACTIVE" name="PROPERTY_<?echo $str_PROPERTY_ID?>_ACTIVE" value="Y"<?if($str_PROPERTY_ACTIVE=="Y")echo " checked"?>></td>
		</tr>
		<tr>
			<td ><?echo GetMessage("IBLOCK_PROP_TYPE")?></td>
			<td><select name="PROPERTY_<?echo $str_PROPERTY_ID?>_PROPERTY_TYPE" >
				<option value="S" <?if($str_PROPERTY_PROPERTY_TYPE=="S" && !$str_PROPERTY_USER_TYPE)echo " selected"?>><?echo GetMessage("IBLOCK_PROP_TYPE_S")?></option>
				<option value="N" <?if($str_PROPERTY_PROPERTY_TYPE=="N" && !$str_PROPERTY_USER_TYPE)echo " selected"?>><?echo GetMessage("IBLOCK_PROP_TYPE_N")?></option>
				<option value="L" <?if($str_PROPERTY_PROPERTY_TYPE=="L" && !$str_PROPERTY_USER_TYPE)echo " selected"?>><?echo GetMessage("IBLOCK_PROP_TYPE_L")?></option>
				<option value="F" <?if($str_PROPERTY_PROPERTY_TYPE=="F" && !$str_PROPERTY_USER_TYPE)echo " selected"?>><?echo GetMessage("IBLOCK_PROP_TYPE_F")?></option>
				<option value="G" <?if($str_PROPERTY_PROPERTY_TYPE=="G" && !$str_PROPERTY_USER_TYPE)echo " selected"?>><?echo GetMessage("IBLOCK_LINK_TO_SECTION")?></option>
				<option value="E" <?if($str_PROPERTY_PROPERTY_TYPE=="E" && !$str_PROPERTY_USER_TYPE)echo " selected"?>><?echo GetMessage("IBLOCK_LINK_TO_ELEMENTS")?></option>
				<?foreach(CIBlockProperty::GetUserType() as  $ar):?>
					<option value="<?=htmlspecialchars($ar["PROPERTY_TYPE"].":".$ar["USER_TYPE"])?>" <?if($str_PROPERTY_PROPERTY_TYPE==$ar["PROPERTY_TYPE"] && $str_PROPERTY_USER_TYPE==$ar["USER_TYPE"])echo " selected"?>><?=htmlspecialchars($ar["DESCRIPTION"])?></option>
				<?endforeach;?>
			</select>
			</td>
		</tr>
		<tr>
			<td><label for="PROPERTY_<?echo $str_PROPERTY_ID?>_MULTIPLE"><?echo GetMessage("IBLOCK_PROP_MULTIPLE")?></label></td>
			<td>
			<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_MULTIPLE" value="N">
			<input type="checkbox" id="PROPERTY_<?echo $str_PROPERTY_ID?>_MULTIPLE" name="PROPERTY_<?echo $str_PROPERTY_ID?>_MULTIPLE" value="Y"<?if($str_PROPERTY_MULTIPLE=="Y")echo " checked"?>>
			</td>
		</tr>
		<tr>
			<td><label for="PROPERTY_<?echo $str_PROPERTY_ID?>_IS_REQUIRED"><?echo GetMessage("IBLOCK_PROP_IS_REQUIRED")?></label></td>
			<td>
			<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_IS_REQUIRED" value="N">
			<input type="checkbox" id="PROPERTY_<?echo $str_PROPERTY_ID?>_IS_REQUIRED" name="PROPERTY_<?echo $str_PROPERTY_ID?>_IS_REQUIRED" value="Y"<?if($str_PROPERTY_IS_REQUIRED==="Y")echo " checked"?>>
			</td>
		</tr>
		<?if($str_PROPERTY_PROPERTY_TYPE!="L" && $str_PROPERTY_MULTIPLE=="Y"):?>
		<tr>
			<td ><?echo GetMessage("IBLOCK_PROP_MULTIPLE_CNT")?></td>
			<td><input type="text" name="PROPERTY_<?echo $str_PROPERTY_ID?>_MULTIPLE_CNT"  value="<?echo $str_PROPERTY_MULTIPLE_CNT?>" size="3"></td>
		</tr>
		<?endif?>
		<tr>
			<td ><?echo GetMessage("IBLOCK_PROP_SORT_DET")?></td>
			<td><input type="text" size="3" maxlength="10"  name="PROPERTY_<?echo $str_PROPERTY_ID?>_SORT" value="<?echo $str_PROPERTY_SORT?>"></td>
		</tr>

		<?if($str_PROPERTY_PROPERTY_TYPE=="L"):?>
		<tr>
			<td ><?echo GetMessage("IBLOCK_PROP_L_APPEARANCE")?></td>
			<td>
				<select name="PROPERTY_<?echo $str_PROPERTY_ID?>_LIST_TYPE" >
					<option value="L"<?if($str_PROPERTY_LIST_TYPE!="C")echo " selected"?>><?echo GetMessage("IBLOCK_PROP_TYPE_L")?></option>
					<option value="C"<?if($str_PROPERTY_LIST_TYPE=="C")echo " selected"?>><?echo GetMessage("IBLOCK_PROP_L_APPEARANCE_CHECKBOX")?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td ><?echo GetMessage("IBLOCK_PROP_L_ROW_CNT")?></td>
			<td><input type="text"   size="2" maxlength="10" name="PROPERTY_<?echo $str_PROPERTY_ID?>_ROW_COUNT" value="<?echo $str_PROPERTY_ROW_COUNT?>"></td>
		</tr>
		<tr>
			<td><label id="PROPERTY_<?echo $str_PROPERTY_ID?>_SEARCHABLE"><?echo GetMessage("IBLOCK_PROP_SEARCHABLE")?></label></td>
			<td>
			<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_SEARCHABLE" value="N">
			<input type="checkbox" id="PROPERTY_<?echo $str_PROPERTY_ID?>_SEARCHABLE" name="PROPERTY_<?echo $str_PROPERTY_ID?>_SEARCHABLE" value="Y" <?if($str_PROPERTY_SEARCHABLE=="Y")echo " checked"?>>
			</td>
		</tr>
		<tr>
			<td><label for="PROPERTY_<?echo $str_PROPERTY_ID?>_FILTRABLE"><?echo GetMessage("IBLOCK_PROP_FILTRABLE")?></label></td>
			<td>
			<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_FILTRABLE" value="N">
			<input type="checkbox" id="PROPERTY_<?echo $str_PROPERTY_ID?>_FILTRABLE" name="PROPERTY_<?echo $str_PROPERTY_ID?>_FILTRABLE" value="Y" <?if($str_PROPERTY_FILTRABLE=="Y")echo " checked"?>>
			</td>
		</tr>
		<tr>
			<td  valign="top"><?echo GetMessage("IBLOCK_PROP_L_VALUES")?></td>
			<td>
			<table cellpadding="1" cellspacing="0" border="0">
			<tr>
			<td>ID</td>
			<td>XML_ID</td>
			<td><?echo GetMessage("IBLOCK_PROP_L_VALUE")?></td>
			<td><?echo GetMessage("IBLOCK_PROP_L_SORT")?></td>
			<td><?echo GetMessage("IBLOCK_PROP_L_DEFAULT")?></td>
			</tr>
			<?
			if(!isset($_POST["PROPERTY_".$str_PROPERTY_ID."_CNT"]))
			{
				$MAX_NEW_ID = 0;
				$arPROPERTY_VALUES = Array();
				$arPROPERTY_VALUES_DEF = Array();
				$arPROPERTY_VALUES_SORT = Array();
				$arPROPERTY_VALUES_XML = Array();
				if(IntVal($str_PROPERTY_ID)>0)
				{
					$props = CIBlockProperty::GetPropertyEnum($str_PROPERTY_ID);
					while($res = $props->Fetch())
					{
						$arPROPERTY_VALUES[$res["ID"]] = $res["VALUE"];
						$arPROPERTY_VALUES_SORT[$res["ID"]] = $res["SORT"];
						$arPROPERTY_VALUES_XML[$res["ID"]] = $res["XML_ID"];
						if($res["DEF"]=="Y")
							$arPROPERTY_VALUES_DEF[] = $res["ID"];
					}
				}
			}
			else
			{
				$MAX_NEW_ID = IntVal(${"PROPERTY_".$str_PROPERTY_ID."_CNT"});
				$arPROPERTY_VALUES = ${"PROPERTY_".$str_PROPERTY_ID."_VALUES"};
				$arPROPERTY_VALUES_DEF = ${"PROPERTY_".$str_PROPERTY_ID."_VALUES_DEF"};
				$arPROPERTY_VALUES_SORT = ${"PROPERTY_".$str_PROPERTY_ID."_VALUES_SORT"};
				$arPROPERTY_VALUES_XML = ${"PROPERTY_".$str_PROPERTY_ID."_VALUES_XML"};
				if(!is_array($arPROPERTY_VALUES))
					$arPROPERTY_VALUES = Array();
				if(!is_array($arPROPERTY_VALUES_DEF))
					$arPROPERTY_VALUES_DEF = Array();
				if(!is_array($arPROPERTY_VALUES_SORT))
					$arPROPERTY_VALUES_SORT = Array();
				if(!is_array($arPROPERTY_VALUES_XML))
					$arPROPERTY_VALUES_XML = Array();
			}
			?>
			<?if($str_PROPERTY_MULTIPLE!="Y"):?>
			<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td colspan="2"><?echo GetMessage("IBLOCK_PROP_L_DEFAULT_NO")?></td>
			<td><input type="radio" name="PROPERTY_<?echo $str_PROPERTY_ID?>_VALUES_DEF[]" value="0" <?if(in_array(0, $arPROPERTY_VALUES_DEF) || count($arPROPERTY_VALUES_DEF)<=0)echo " checked"?>> </td>
			</tr>
			<?endif?>
			<tr>
			<?
			$arPV_Keys = array_keys($arPROPERTY_VALUES);
			for($i=0; $i<count($arPV_Keys); $i++):
				if(strlen($arPROPERTY_VALUES[$arPV_Keys[$i]])<=0)
					continue;
			?>
			<tr>
			<td><?=(intval($arPV_Keys[$i])>0?htmlspecialchars($arPV_Keys[$i]):"&nbsp;")?></td>
			<td><input type="text"  name="PROPERTY_<?echo $str_PROPERTY_ID?>_VALUES_XML[<?echo htmlspecialchars($arPV_Keys[$i])?>]" value="<?echo htmlspecialchars($arPROPERTY_VALUES_XML[$arPV_Keys[$i]])?>" size="15" maxlength="200"></td>
			<td><input type="text"  name="PROPERTY_<?echo $str_PROPERTY_ID?>_VALUES[<?echo htmlspecialchars($arPV_Keys[$i])?>]" value="<?echo htmlspecialchars($arPROPERTY_VALUES[$arPV_Keys[$i]])?>" size="35" maxlength="255"></td>
			<td><input type="text"  name="PROPERTY_<?echo $str_PROPERTY_ID?>_VALUES_SORT[<?echo htmlspecialchars($arPV_Keys[$i])?>]" value="<?echo htmlspecialchars($arPROPERTY_VALUES_SORT[$arPV_Keys[$i]])?>" size="5" maxlength="11"></td>
			<td><input type="<?=($str_PROPERTY_MULTIPLE!="Y"?"radio":"checkbox")?>" name="PROPERTY_<?echo $str_PROPERTY_ID?>_VALUES_DEF[]" value="<?echo htmlspecialchars($arPV_Keys[$i])?>" <?if(in_array($arPV_Keys[$i], $arPROPERTY_VALUES_DEF))echo " checked"?>></td>
			</tr>
			<?endfor?>
			<?for($i=$MAX_NEW_ID; $i<$MAX_NEW_ID+5; $i++):?>
			<tr>
			<td>&nbsp;</td>
			<td><input type="text"  name="PROPERTY_<?echo $str_PROPERTY_ID?>_VALUES_XML[n<?=$i?>]" size="15" maxlength="200"></td>
			<td><input type="text"  name="PROPERTY_<?echo $str_PROPERTY_ID?>_VALUES[n<?=$i?>]" size="35" maxlength="255"></td>
			<td><input type="text"  name="PROPERTY_<?echo $str_PROPERTY_ID?>_VALUES_SORT[n<?=$i?>]" size="5" maxlength="15" value="500"></td>
			<td><input type="<?=($str_PROPERTY_MULTIPLE!="Y"?"radio":"checkbox")?>" name="PROPERTY_<?echo $str_PROPERTY_ID?>_VALUES_DEF[]" value="n<?=$i?>"></td>
			</tr>
			<?endfor?>
			</table>
			<input type="hidden" name="PROPERTY_<?=htmlspecialchars($str_PROPERTY_ID)?>_CNT" value="<?echo ($MAX_NEW_ID+5)?>">
			<input type="submit"  name="propedit[<?echo $str_PROPERTY_ID?>]" value="<?echo GetMessage("IBLOCK_PROP_L_MORE")?>">

			</td>
		</tr>
		<?elseif($str_PROPERTY_PROPERTY_TYPE=="F"):?>
			<tr>
				<td ><?echo GetMessage("IBLOCK_PROP_F_TYPES")?></td>
				<td>
					<input type="text"  size="30" maxlength="255" name="PROPERTY_<?=$str_PROPERTY_ID?>_FILE_TYPE" value="<?echo $str_PROPERTY_FILE_TYPE?>">

					<select  onchange="if(this.selectedIndex!=0) document.frm.PROPERTY_<?=$str_PROPERTY_ID?>_FILE_TYPE.value=this[this.selectedIndex].value">
						<option value="-"></option>
						<option value=""<?if($str_PROPERTY_FILE_TYPE=="")echo " selected"?>><?echo GetMessage("IBLOCK_PROP_F_TYPES_ANY")?></option>
						<option value="jpg, gif, bmp, png, jpeg"<?if($str_PROPERTY_FILE_TYPE=="jpg, gif, bmp, png, jpeg")echo " selected"?>><?echo GetMessage("IBLOCK_PROP_F_TYPES_PIC")?></option>
						<option value="mp3, wav, midi, snd, au, wma"<?if($str_PROPERTY_FILE_TYPE=="mp3, wav, midi, snd, au, wma")echo " selected"?>><?echo GetMessage("IBLOCK_PROP_F_TYPES_SOUND")?></option>
						<option value="mpg, avi, wmv, mpeg, mpe"<?if($str_PROPERTY_FILE_TYPE=="mpg, avi, wmv, mpeg, mpe")echo " selected"?>><?echo GetMessage("IBLOCK_PROP_F_TYPES_VIDEO")?></option>
						<option value="doc, txt, rtf"<?if($str_PROPERTY_FILE_TYPE=="doc, txt, rtf")echo " selected"?>><?echo GetMessage("IBLOCK_PROP_F_TYPES_DOCS")?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td ><?echo GetMessage("IBLOCK_PROP_F_TYPES_COL_CNT")?></td>
				<td><input type="text" size="2" maxlength="10" name="PROPERTY_<?echo $str_PROPERTY_ID?>_COL_COUNT" value="<?echo $str_PROPERTY_COL_COUNT?>"></td>
			</tr>
			<tr>
				<td><label for="PROPERTY_<?echo $str_PROPERTY_ID?>_WITH_DESCRIPTION"><?echo GetMessage("IBLOCK_PROP_WITH_DESC")?></label></td>
				<td>
				<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_WITH_DESCRIPTION" value="N">
				<input type="checkbox" id="PROPERTY_<?echo $str_PROPERTY_ID?>_WITH_DESCRIPTION" name="PROPERTY_<?echo $str_PROPERTY_ID?>_WITH_DESCRIPTION" value="Y" <?if($str_PROPERTY_WITH_DESCRIPTION=="Y")echo " checked"?>>
				</td>
			</tr>
		<?elseif($str_PROPERTY_PROPERTY_TYPE=="G" || $str_PROPERTY_PROPERTY_TYPE=="E"):?>
			<tr>
				<td ><?echo GetMessage("IBLOCK_PROP_IBLOCK")?></td>
				<td>
					<select  name="PROPERTY_<?=$str_PROPERTY_ID?>_LINK_IBLOCK_ID">
						<?
						if($str_PROPERTY_PROPERTY_TYPE=="G"):
							$b_f = Array("!ID"=>$ID);
						else:
							?><option value=""><?echo GetMessage("IBLOCK_PROP_IBLOCK_ANY")?></option><?
							$b_f = Array();
						endif;
						$db_iblocks = CIBlock::GetList(Array("NAME"=>"ASC"), $b_f);
						while($db_iblocks->ExtractFields("str_iblock_")):
						?>
						<option value="<?=$str_iblock_ID?>"<?if($str_PROPERTY_LINK_IBLOCK_ID==$str_iblock_ID)echo " selected"?>><?=$str_iblock_NAME?> [<?=$str_iblock_LID?>] (<?=$str_iblock_ID?>)</option>
						<?endwhile?>
					</select>
				</td>
			</tr>
			<tr>
				<td><label for="PROPERTY_<?echo $str_PROPERTY_ID?>_FILTRABLE"><?echo GetMessage("IBLOCK_PROP_FILTRABLE")?></label></td>
				<td>
				<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_FILTRABLE" value="N">
				<input type="checkbox" id="PROPERTY_<?echo $str_PROPERTY_ID?>_FILTRABLE" name="PROPERTY_<?echo $str_PROPERTY_ID?>_FILTRABLE" value="Y" <?if($str_PROPERTY_FILTRABLE=="Y")echo " checked"?>>
				</td>
			</tr>
		<?else:?>
			<tr>
				<td ><?echo GetMessage("IBLOCK_PROP_DEFAULT")?></td>
				<td>
				<?if($str_PROPERTY_USER_TYPE!="" && array_key_exists("GetPropertyFieldHtml", $arUserType = CIBlockProperty::GetUserType($str_PROPERTY_USER_TYPE))):
					$arFieldList = $DB->GetTableFieldsList("b_iblock_property");
					$arProperty = array();
					foreach($arFieldList as $strFieldName)
						$arProperty[$strFieldName]=${"PROPERTY_".$str_PROPERTY_ID."_".$strFieldName};
					$arProperty["ID"] = $str_PROPERTY_ID;
					$arProperty["WITH_DESCRIPTION"] = "N";
					echo call_user_func_array($arUserType["GetPropertyFieldHtml"],
						array(
							$arProperty,
							array(
								"VALUE"=>${"PROPERTY_".$str_PROPERTY_ID."_DEFAULT_VALUE"},
								"DESCRIPTION"=>""
							),
							array(
								"VALUE"=>"PROPERTY_".$str_PROPERTY_ID."_DEFAULT_VALUE",
								"DESCRIPTION"=>"",
								"MODE" => "EDIT_FORM",
								"FORM_NAME" => "frm"
							),
						));
				else:?>
					<input type="text"  size="40" maxlength="255" name="PROPERTY_<?echo $str_PROPERTY_ID?>_DEFAULT_VALUE" value="<?echo $str_PROPERTY_DEFAULT_VALUE?>">
				<?endif;?>
				</td>
			</tr>
			<tr>
				<td ><?echo GetMessage("IBLOCK_PROP_SIZE")?></td>
				<td>

					<input type="text"  size="2" maxlength="10" name="PROPERTY_<?echo $str_PROPERTY_ID?>_ROW_COUNT" value="<?echo $str_PROPERTY_ROW_COUNT?>"> x <input type="text"  size="2" maxlength="10" name="PROPERTY_<?echo $str_PROPERTY_ID?>_COL_COUNT" value="<?echo $str_PROPERTY_COL_COUNT?>">

				</td>
			</tr>
			<tr>
				<td><label for="PROPERTY_<?echo $str_PROPERTY_ID?>_SEARCHABLE"><?echo GetMessage("IBLOCK_PROP_SEARCHABLE")?></label></td>
				<td>
				<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_SEARCHABLE" value="N">
				<input type="checkbox" id="PROPERTY_<?echo $str_PROPERTY_ID?>_SEARCHABLE" name="PROPERTY_<?echo $str_PROPERTY_ID?>_SEARCHABLE" value="Y" <?if($str_PROPERTY_SEARCHABLE=="Y")echo " checked"?>>
				</td>
			</tr>
			<tr>
				<td><label for="PROPERTY_<?echo $str_PROPERTY_ID?>_FILTRABLE"><?echo GetMessage("IBLOCK_PROP_FILTRABLE")?></label></td>
				<td>
				<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_FILTRABLE" value="N">
				<input type="checkbox" id="PROPERTY_<?echo $str_PROPERTY_ID?>_FILTRABLE" name="PROPERTY_<?echo $str_PROPERTY_ID?>_FILTRABLE" value="Y" <?if($str_PROPERTY_FILTRABLE=="Y")echo " checked"?>>
				</td>
			</tr>
			<tr>
				<td><label for="PROPERTY_<?echo $str_PROPERTY_ID?>_WITH_DESCRIPTION"><?echo GetMessage("IBLOCK_PROP_WITH_DESC")?></label></td>
				<td>
				<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_WITH_DESCRIPTION" value="N">
				<input type="checkbox" id="PROPERTY_<?echo $str_PROPERTY_ID?>_WITH_DESCRIPTION" name="PROPERTY_<?echo $str_PROPERTY_ID?>_WITH_DESCRIPTION" value="Y" <?if($str_PROPERTY_WITH_DESCRIPTION=="Y")echo " checked"?>>
				</td>
			</tr>
		<?endif?>
	<?
	$tabControl->Buttons();
	?>
	<input type="submit"  name="propedit[x]" value="<?echo GetMessage("IBLOCK_PROP_MORE")?>" title="<?echo GetMessage("IBLOCK_PROP_BACK")?>">
	<input type="reset"  name="reset" value="<?echo GetMessage("IBLOCK_PROP_RESET")?>">
	<?
	$tabControl->End();
	?>
<?
else: //if(IntVal($str_PROPERTY_ID)>0 || (strlen($str_PROPERTY_ID)>0 && $str_PROPERTY_ID[0]=="n")):
?>
<?
$bTab3 = ($arIBTYPE["IN_RSS"]=="Y");
$bTab4 = CModule::IncludeModule("workflow");

$aTabs = array();
$aTabs[] = array("DIV" => "edit1", "TAB" => GetMessage("IB_E_TAB2"), "ICON"=>"iblock", "TITLE"=>GetMessage("IB_E_TAB2_T"));
$aTabs[] = array("DIV" => "edit6", "TAB" => GetMessage("IB_E_TAB6"), "ICON"=>"iblock_fields", "TITLE"=>GetMessage("IB_E_TAB6_T"));
$aTabs[] = array("DIV" => "edit2", "TAB" => GetMessage("IB_E_TAB3"), "ICON"=>"iblock_props", "TITLE"=>GetMessage("IB_E_TAB3_T"));
if($bTab3) $aTabs[] = array("DIV" => "edit3", "TAB" => "RSS", "ICON"=>"iblock_rss", "TITLE"=>GetMessage("IBLOCK_RSS_PARAMS"));
$aTabs[] = array("DIV" => "edit4", "TAB" => GetMessage("IB_E_TAB4"), "ICON"=>"iblock_access", "TITLE"=>GetMessage("IB_E_TAB4_T"));
$aTabs[] = array("DIV" => "edit5", "TAB" => GetMessage("IB_E_TAB5"), "ICON"=>"iblock", "TITLE"=>GetMessage("IB_E_TAB5_T"));

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
	<?if($ID>0):?>
	<tr>
		<td valign="top" width="40%">ID:</td>
		<td valign="top" width="60%"><?echo $str_ID?></td>
	</tr>
	<tr>
		<td valign="top" width="40%"><?=GetMessage("IB_E_PROPERTY_STORAGE")?></td>
		<td valign="top" width="60%">
			<input type="hidden" name="VERSION" value="<?=$str_VERSION?>">
			<?if($str_VERSION==1)echo GetMessage("IB_E_COMMON_STORAGE")?>
			<?if($str_VERSION==2)echo GetMessage("IB_E_SEPARATE_STORAGE")?>
			<br><a href="iblock_convert.php?lang=<?=LANG?>&amp;IBLOCK_ID=<?echo $str_ID?>"><?=$str_LAST_CONV_ELEMENT>0?"<span class=\"required\">".GetMessage("IB_E_CONVERT_CONTINUE"):GetMessage("IB_E_CONVERT_START")."</span>"?></a>
		</td>
	</tr>
	<tr>
		<td valign="top" ><?echo GetMessage("IBLOCK_LAST_UPDATE")?></td>
		<td valign="top"><?echo $str_TIMESTAMP_X?></td>
	</tr>
	<? else: ?>
	<tr>
		<td valign="top" width="40%"><?=GetMessage("IB_E_PROPERTY_STORAGE")?></td>
		<td valign="top" width="60%">
				<label><input type="radio" name="VERSION" value="1" <?if($str_VERSION==1)echo " checked"?>><?=GetMessage("IB_E_COMMON_STORAGE")?></label><br>
				<label><input type="radio" name="VERSION" value="2" <?if($str_VERSION==2)echo " checked"?>><?=GetMessage("IB_E_SEPARATE_STORAGE")?></label>
		</td>
	</tr>
	<? endif; ?>
	<tr>
		<td valign="top"><label for="ACTIVE"><?echo GetMessage("IBLOCK_ACTIVE")?></label></td>
		<td valign="top">
			<input type="hidden" name="ACTIVE" value="N">
			<input type="checkbox" id="ACTIVE" name="ACTIVE" value="Y"<?if($str_ACTIVE=="Y")echo " checked"?>>
			<span style="display:none;"><input type="submit" name="save" value="Y" style="width:0px;height:0px"></span>
		</td>
	</tr>
	<tr>
		<td valign="top"  width="40%"><? echo GetMessage("IBLOCK_CODE")?></td>
		<td valign="top" width="60%">
			<input type="text" name="CODE" size="20" maxlength="40" value="<?echo $str_CODE?>" >
		</td>
	</tr>

	<tr valign="top">
		<td><span class="required">*</span><?echo GetMessage("IBLOCK_EDIT_LID")?></td>
		<td><?=CLang::SelectBoxMulti("LID", $str_LID);?></td>
	</tr>

	<tr>
		<td valign="top" ><span class="required">*</span><? echo GetMessage("IBLOCK_NAME")?></td>
		<td valign="top">
			<input type="text" name="NAME" size="40" maxlength="255"  value="<?echo $str_NAME?>">
		</td>
	</tr>
	<tr>
		<td valign="top" ><? echo GetMessage("IBLOCK_SORT")?></td>
		<td valign="top">
			<input type="text" name="SORT" size="10"  maxlength="10" value="<?echo $str_SORT?>">
		</td>
	</tr>
	<?if(COption::GetOptionString("iblock", "show_xml_id", "N")=="Y"):?>
	<tr>
		<td valign="top" ><?echo GetMessage("IBLOCK_EXTERNAL_CODE")?></td>
		<td valign="top">
			<input type="text" name="XML_ID"  size="20" maxlength="50" value="<?echo $str_XML_ID?>">
		</td>
	</tr>
	<?endif?>
	<tr>
		<td valign="top" ><?echo GetMessage("IBLOCK_LIST_PAGE_URL")?></td>
		<td valign="top">
			<input type="text" name="LIST_PAGE_URL" id="LIST_PAGE_URL" size="40" maxlength="255" value="<?echo $str_LIST_PAGE_URL?>">
			<input type="button" onclick="__ShUrlVars(this, 'LIST_PAGE_URL')" value='...'>
		</td>
	</tr>
	<?if($arIBTYPE["SECTIONS"]=="Y"):?>
	<tr>
		<td valign="top" ><?echo GetMessage("IBLOCK_SECTION_PAGE_URL")?></td>
		<td valign="top">
			<input type="text" name="SECTION_PAGE_URL" id="SECTION_PAGE_URL" size="40" maxlength="255" value="<?echo $str_SECTION_PAGE_URL?>">
			<input type="button" onclick="__ShUrlVars(this, 'SECTION_PAGE_URL')" value='...'>
		</td>
	</tr>
	<?endif?>
	<tr>
		<td valign="top" ><?echo GetMessage("IBLOCK_DETAIL_PAGE_URL")?></td>
		<td valign="top">
			<input type="text" name="DETAIL_PAGE_URL" id="DETAIL_PAGE_URL" size="40" maxlength="255" value="<?echo $str_DETAIL_PAGE_URL?>">
			<input type="button" onclick="__ShUrlVars(this, 'DETAIL_PAGE_URL')" value='...'>
		</td>
	</tr>

	<?if($arIBTYPE["SECTIONS"]=="Y"):?>
	<tr>
		<td valign="top"><label for="INDEX_SECTION"><?echo GetMessage("IBLOCK_INDEX_SECTION")?></label></td>
		<td valign="top">
			<input type="hidden" name="INDEX_SECTION" value="N">
			<input type="checkbox" id="INDEX_SECTION" name="INDEX_SECTION" value="Y"<?if($str_INDEX_SECTION=="Y")echo " checked"?>>
		</td>
	</tr>
	<?endif?>
	<tr>
		<td valign="top"><label for="INDEX_ELEMENT"><?echo GetMessage("IBLOCK_INDEX_ELEMENT")?></label></td>
		<td valign="top">
			<input type="hidden" name="INDEX_ELEMENT" value="N">
			<input type="checkbox" id="INDEX_ELEMENT" name="INDEX_ELEMENT" value="Y"<?if($str_INDEX_ELEMENT=="Y")echo " checked"?>>
		</td>
	</tr>
	<?if(IsModuleInstalled("workflow")):?>
	<tr>
		<td valign="top"><label for="WORKFLOW"><?echo GetMessage("IBLOCK_WORKFLOW")?></label></td>
		<td valign="top">
			<input type="hidden" name="WORKFLOW" value="N">
			<input type="checkbox" id="WORKFLOW" name="WORKFLOW" value="Y"<?if($str_WORKFLOW=="Y")echo " checked"?>>
		</td>
	</tr>
	<?endif?>
	<tr>
		<td valign="top"><?echo GetMessage("IB_E_SECTION_CHOOSER")?>:</td>
		<td valign="top">
			<select name="SECTION_CHOOSER">
			<option value="L"<?if($str_SECTION_CHOOSER=="L")echo " selected"?>><?echo GetMessage("IB_E_SECTION_CHOOSER_LIST")?></option>
			<option value="D"<?if($str_SECTION_CHOOSER=="D")echo " selected"?>><?echo GetMessage("IB_E_SECTION_CHOOSER_DROPDOWNS")?></option>
			<option value="P"<?if($str_SECTION_CHOOSER=="P")echo " selected"?>><?echo GetMessage("IB_E_SECTION_CHOOSER_POPUP")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td>
		<?
		CAdminFileDialog::ShowScript
		(
			Array(
				"event" => "BtnClick",
				"arResultDest" => array("FORM_NAME" => "frm", "FORM_ELEMENT_NAME" => "EDIT_FILE_BEFORE"),
				"arPath" => array("PATH" => GetDirPath($str_EDIT_FILE_BEFORE)),
				"select" => 'F',// F - file only, D - folder only
				"operation" => 'O',// O - open, S - save
				"showUploadTab" => true,
				"showAddToMenuTab" => false,
				"fileFilter" => 'php',
				"allowAllFiles" => true,
				"SaveConfig" => true,
			)
		);
		?>
		<?echo GetMessage("IBLOCK_OPTION_FILE_BEFORE")?></td>
		<td><input type="text" name="EDIT_FILE_BEFORE" size="50"  maxlength="255" value="<?echo $str_EDIT_FILE_BEFORE?>">&nbsp;<input type="button" name="browse" value="..." onClick="BtnClick()"></td>
	</tr>
	<tr>
		<td>
		<?
		CAdminFileDialog::ShowScript
		(
			Array(
				"event" => "BtnClick2",
				"arResultDest" => array("FORM_NAME" => "frm", "FORM_ELEMENT_NAME" => "EDIT_FILE_AFTER"),
				"arPath" => array("PATH" => GetDirPath($str_EDIT_FILE_AFTER)),
				"select" => 'F',// F - file only, D - folder only
				"operation" => 'O',// O - open, S - save
				"showUploadTab" => true,
				"showAddToMenuTab" => false,
				"fileFilter" => 'php',
				"allowAllFiles" => true,
				"SaveConfig" => true,
			)
		);
		?>
		<?echo GetMessage("IBLOCK_OPTION_FILE_AFTER")?></td>
		<td><input type="text" name="EDIT_FILE_AFTER" size="50"  maxlength="255" value="<?echo $str_EDIT_FILE_AFTER?>">&nbsp;<input type="button" name="browse" value="..." onClick="BtnClick2()"></td>
	</tr>

	<tr class="heading">
		<td colspan="2"><?echo GetMessage("IBLOCK_DESCRIPTION")?></td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("IBLOCK_PICTURE")?></td>
		<td valign="top">
			<?echo CFile::InputFile("PICTURE", 20, $str_PICTURE);?><br>
			<?echo CFile::ShowImage($str_PICTURE, "border=0", "", 200, 200, true)?>
		</td>
	</tr>
	<?if(COption::GetOptionString("iblock", "use_htmledit", "Y")=="Y" && CModule::IncludeModule("fileman")):?>
	<tr>
		<td valign="top" colspan="2" align="center">
			<?CFileMan::AddHTMLEditorFrame("DESCRIPTION", $str_DESCRIPTION, "DESCRIPTION_TYPE", $str_DESCRIPTION_TYPE, 250);?>
		</td>
	</tr>
	<?else:?>
	<tr>
		<td ><?echo GetMessage("IBLOCK_DESC_TYPE")?></td>
		<td >
			<input type="radio" name="DESCRIPTION_TYPE" id="DESCRIPTION_TYPE1" value="text"<?if($str_DESCRIPTION_TYPE!="html")echo " checked"?>><label for="DESCRIPTION_TYPE1"> <?echo GetMessage("IBLOCK_DESC_TYPE_TEXT")?></label> /
			<input type="radio" name="DESCRIPTION_TYPE" id="DESCRIPTION_TYPE2" value="html"<?if($str_DESCRIPTION_TYPE=="html")echo " checked"?>><label for="DESCRIPTION_TYPE2"> <?echo GetMessage("IBLOCK_DESC_TYPE_HTML")?></label>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<textarea cols="60" rows="15" name="DESCRIPTION" style="width:100%;"><?echo $str_DESCRIPTION?></textarea>
		</td>
	</tr>
	<?endif?>
<?
$tabControl->BeginNextTab();
?>
	<tr>
		<td valign="top" colspan="2">
			<table border="0" cellspacing="0" cellpadding="0" class="internal" align="center">
				<tr class="heading">
					<td nowrap><?echo GetMessage("IB_E_FIELD_NAME")?></td>
					<td nowrap><?echo GetMessage("IB_E_FIELD_IS_REQUIRED")?></td>
					<td nowrap><?echo GetMessage("IB_E_FIELD_DEFAULT_VALUE")?></td>
				</tr>
				<?
				if($bVarsFromForm)
					$arFields = $_REQUEST["FIELDS"];
				else
					$arFields = CIBlock::GetFields($ID);
				$arDefFields = CIBlock::GetFieldsDefaults();
				foreach($arDefFields as $FIELD_ID => $arField):?>
					<tr valign="top">
						<td nowrap><?echo $arDefFields[$FIELD_ID]["NAME"]?></td>
						<td nowrap align="center">
							<input type="hidden" value="N" name="FIELDS[<?echo $FIELD_ID?>][IS_REQUIRED]">
							<input type="checkbox" value="Y" name="FIELDS[<?echo $FIELD_ID?>][IS_REQUIRED]" <?if($arFields[$FIELD_ID]["IS_REQUIRED"]==="Y" || $arDefFields[$FIELD_ID]["IS_REQUIRED"]!==false) echo "checked"?> <?if($arDefFields[$FIELD_ID]["IS_REQUIRED"]!==false) echo "disabled"?>>
						</td>
						<td nowrap>
						<?
						switch($FIELD_ID)
						{
							case "ACTIVE":
								?>
								<select name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]" height="1">
									<option value="Y" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]==="Y") echo "selected"?>><?echo GetMessage("MAIN_YES")?></option>
									<option value="N" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]==="N") echo "selected"?>><?echo GetMessage("MAIN_NO")?></option>
								</select>
								<?
								break;
							case "ACTIVE_FROM":
								?>
								<select name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]" height="1">
									<option value="" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]==="") echo "selected"?>><?echo GetMessage("IB_E_FIELD_ACTIVE_FROM_EMPTY")?></option>
									<option value="=now" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]==="=now") echo "selected"?>><?echo GetMessage("IB_E_FIELD_ACTIVE_FROM_NOW")?></option>
									<option value="=today" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]==="=today") echo "selected"?>><?echo GetMessage("IB_E_FIELD_ACTIVE_FROM_TODAY")?></option>
								</select>
								<?
								break;
							case "ACTIVE_TO":
								?>
								<table border="0" cellspacing="2" cellpadding="0">
								<tr><td><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]"><?echo GetMessage("IB_E_FIELD_ACTIVE_TO")?></label></td></tr>
								<tr><td><input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]" type="text" value="<?echo htmlspecialchars($arFields[$FIELD_ID]["DEFAULT_VALUE"])?>" size="5"></td></tr>
								</table>
								<?
								break;
							case "NAME":
								?>
								<input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]" type="text" value="<?echo htmlspecialchars($arFields[$FIELD_ID]["DEFAULT_VALUE"])?>" size="60">
								<?
								break;
							case "DETAIL_TEXT_TYPE":
							case "PREVIEW_TEXT_TYPE":
								?>
								<select name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]" height="1">
									<option value="text" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]==="text") echo "selected"?>>text</option>
									<option value="html" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]==="html") echo "selected"?>>html</option>
								</select>
								<?
								break;
							case "DETAIL_TEXT":
							case "PREVIEW_TEXT":
								?>
								<textarea name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]" rows="5" cols="47"><?echo htmlspecialchars($arFields[$FIELD_ID]["DEFAULT_VALUE"])?></textarea>
								<?
								break;
							case "PREVIEW_PICTURE":
								?>
								<table border="0" cellspacing="2" cellpadding="0">
								<tr><td><input type="checkbox" value="Y" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][FROM_DETAIL]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][FROM_DETAIL]" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["FROM_DETAIL"]==="Y") echo "checked"?>><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][FROM_DETAIL]"><?echo GetMessage("IB_E_FIELD_PREVIEW_PICTURE_FROM_DETAIL")?></label></td></tr>
								<tr><td><input type="checkbox" value="Y" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][SCALE]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][SCALE]" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y") echo "checked"?>><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][SCALE]"><?echo GetMessage("IB_E_FIELD_PICTURE_SCALE")?></label></td></tr>
								<tr><td><?echo GetMessage("IB_E_FIELD_PICTURE_WIDTH")?>:&nbsp;<input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WIDTH]" type="text" value="<?echo htmlspecialchars($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WIDTH"])?>" size="7"></td></tr>
								<tr><td><?echo GetMessage("IB_E_FIELD_PICTURE_HEIGHT")?>:&nbsp;<input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][HEIGHT]" type="text" value="<?echo htmlspecialchars($arFields[$FIELD_ID]["DEFAULT_VALUE"]["HEIGHT"])?>" size="7"></td></tr>
								<tr><td><input type="checkbox" value="Y" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS]" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["IGNORE_ERRORS"]==="Y") echo "checked"?>><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS]"><?echo GetMessage("IB_E_FIELD_PICTURE_IGNORE_ERRORS")?></label></td></tr>
								</table>
								<?
								break;
							case "DETAIL_PICTURE":
								?>
								<table border="0" cellspacing="2" cellpadding="0">
								<tr><td><input type="checkbox" value="Y" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][SCALE]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][SCALE]" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y") echo "checked"?>><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][SCALE]"><?echo GetMessage("IB_E_FIELD_PICTURE_SCALE")?></label></td></tr>
								<tr><td><?echo GetMessage("IB_E_FIELD_PICTURE_WIDTH")?>:&nbsp;<input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WIDTH]" type="text" value="<?echo htmlspecialchars($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WIDTH"])?>" size="7"></td></tr>
								<tr><td><?echo GetMessage("IB_E_FIELD_PICTURE_HEIGHT")?>:&nbsp;<input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][HEIGHT]" type="text" value="<?echo htmlspecialchars($arFields[$FIELD_ID]["DEFAULT_VALUE"]["HEIGHT"])?>" size="7"></td></tr>
								<tr><td><input type="checkbox" value="Y" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS]" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["IGNORE_ERRORS"]==="Y") echo "checked"?>><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS]"><?echo GetMessage("IB_E_FIELD_PICTURE_IGNORE_ERRORS")?></label></td></tr>
								</table>
								<?
								break;
							default:
								?>
								<input type="hidden" value="" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]">&nbsp;
								<?
								break;
						}
						?>
						</td>
					</tr>
				<?endforeach?>
			</table>
		</td>
	</tr>
<?
$tabControl->BeginNextTab();
?>
	<tr>
		<td valign="top" colspan="2">
			<table border="0" cellspacing="0" cellpadding="0" class="internal" align="center">
				<tr class="heading">
					<td>ID</td>
					<td><?echo GetMessage("IBLOCK_PROP_NAME")?></td>
					<td><?echo GetMessage("IBLOCK_PROP_TYP")?></td>
					<td><?echo GetMessage("IBLOCK_PROP_MULT")?></td>
					<td><?echo GetMessage("IBLOCK_PROP_IS_REQ")?></td>
					<td><?echo GetMessage("IBLOCK_PROP_SORT")?></td>
					<td><?echo GetMessage("IBLOCK_PROP_CODE")?></td>
					<td><?echo GetMessage("IBLOCK_PROP_CHNG")?></td>
					<td><?echo GetMessage("IBLOCK_PROP_DEL")?></td>
				</tr>
				<?
				$props = CIBlock::GetProperties($ID, Array("sort"=>"asc"));
				$i=0;
				function _GetOldAndNew($props)
				{
					global $i;
					if($i==0 && ($tmp = $props->ExtractFields("str_PROPERTY_")))
						return $tmp;

					global $str_PROPERTY_ID, $str_PROPERTY_NAME, $str_PROPERTY_IS_REQUIRED, $str_PROPERTY_DEFAULT_VALUE, $str_PROPERTY_CODE;
					global $str_PROPERTY_SORT, $str_PROPERTY_MULTIPLE_CNT, $str_PROPERTY_XML_ID, $str_PROPERTY_ROW_COUNT,
					$str_PROPERTY_COL_COUNT, $str_PROPERTY_LINK_IBLOCK_ID, $str_PROPERTY_MULTIPLE, $str_PROPERTY_IS_REQUIRED, $str_PROPERTY_PROPERTY_TYPE;
					global $str_PROPERTY_WITH_DESCRIPTION, $str_PROPERTY_ACTIVE;
					global $str_PROPERTY_SEARCHABLE, $str_PROPERTY_FILTRABLE;
					global $str_PROPERTY_USER_TYPE;

					if($i>4) return false;

					$str_PROPERTY_ID = "n".$i;
					$str_PROPERTY_NAME = "";
					$str_PROPERTY_ACTIVE = "Y";
					$str_PROPERTY_MULTIPLE = "N";
					$str_PROPERTY_MULTIPLE_CNT = "5";
					$str_PROPERTY_IS_REQUIRED = "N";
					$str_PROPERTY_DEFAULT_VALUE = "";
					$str_PROPERTY_XML_ID = "";
					$str_PROPERTY_PROPERTY_TYPE = "S";
					$str_PROPERTY_USER_TYPE = "";
					$str_PROPERTY_CODE = "";
					$str_PROPERTY_SORT = "500";
					$str_PROPERTY_ROW_COUNT = "1";
					$str_PROPERTY_COL_COUNT = "30";
					$str_PROPERTY_LINK_IBLOCK_ID = "";
					$str_PROPERTY_WITH_DESCRIPTION = "";
					$str_PROPERTY_FILTRABLE = "";
					$str_PROPERTY_SEARCHABLE = "";

					$i++;

					return true;
				}

				while($r = _GetOldAndNew($props)):

					if($bVarsFromForm)
					{
						${"PROPERTY_MULTIPLE_".$str_PROPERTY_ID} = (${"PROPERTY_".$str_PROPERTY_ID."_MULTIPLE"}!="Y"?"N":"Y");
						${"PROPERTY_IS_REQUIRED_".$str_PROPERTY_ID} = (${"PROPERTY_".$str_PROPERTY_ID."_IS_REQUIRED"}!=="Y"?"N":"Y");
						${"PROPERTY_IS_REQUIRED_".$str_PROPERTY_ID} = (${"PROPERTY_".$str_PROPERTY_ID."_IS_REQUIRED"}!="Y"?"N":"Y");
						${"PROPERTY_DEL_".$str_PROPERTY_ID} = (${"PROPERTY_".$str_PROPERTY_ID."_DEL"}!="Y"?"N":"Y");
						if(substr(":", ${"PROPERTY_".$str_PROPERTY_ID."_PROPERTY_TYPE"})!==false)
						{
							list(${"PROPERTY_".$str_PROPERTY_ID."_PROPERTY_TYPE"},${"PROPERTY_".$str_PROPERTY_ID."_USER_TYPE"})=explode(":", ${"PROPERTY_".$str_PROPERTY_ID."_PROPERTY_TYPE"}, 2);
						}
						else
							${"PROPERTY_".$str_PROPERTY_ID."_USER_TYPE"}="";

						echo ${"PROPERTY_$str_PROPERTY_ID_TYPE"};
						$tmp_PROP_ID = $str_PROPERTY_ID;

						$DB->InitTableVarsForEdit("b_iblock_property", "PROPERTY_".$str_PROPERTY_ID."_", "str_PROPERTY_");
						$str_PROPERTY_ID = $tmp_PROP_ID;
						if(is_array(${"PROPERTY_".$str_PROPERTY_ID."_DEFAULT_VALUE"}))
							$str_PROPERTY_DEFAULT_VALUE = ${"PROPERTY_".$str_PROPERTY_ID."_DEFAULT_VALUE"};
					}
				?>
					<tr>
						<td><?echo ($str_PROPERTY_ID>0?$str_PROPERTY_ID:"")?></td>
						<td>
							<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_FILE_TYPE" value="<?echo $str_PROPERTY_FILE_TYPE?>">
							<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_LIST_TYPE" value="<?echo $str_PROPERTY_LIST_TYPE?>">
							<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_ROW_COUNT" value="<?echo $str_PROPERTY_ROW_COUNT?>">
							<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_COL_COUNT" value="<?echo $str_PROPERTY_COL_COUNT?>">
							<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_LINK_IBLOCK_ID" value="<?echo $str_PROPERTY_LINK_IBLOCK_ID?>">
<?if(is_array($str_PROPERTY_DEFAULT_VALUE)):?>
	<?foreach($str_PROPERTY_DEFAULT_VALUE as $key=>$value):?>
		<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_DEFAULT_VALUE[<?=htmlspecialchars($key)?>]" value="<?=htmlspecialchars($value)?>">
	<?endforeach?>
<?else:?>
	<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_DEFAULT_VALUE" value="<?echo $str_PROPERTY_DEFAULT_VALUE?>">
<?endif?>
							<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_WITH_DESCRIPTION" value="<?echo $str_PROPERTY_WITH_DESCRIPTION?>">
							<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_SEARCHABLE" value="<?echo $str_PROPERTY_SEARCHABLE?>">
							<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_FILTRABLE" value="<?echo $str_PROPERTY_FILTRABLE?>">
							<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_ACTIVE" value="<?echo $str_PROPERTY_ACTIVE?>">
							<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_MULTIPLE_CNT" value="<?echo $str_PROPERTY_MULTIPLE_CNT?>">
							<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_XML_ID" value="<?echo $str_PROPERTY_XML_ID?>">
							<?
							if($str_PROPERTY_PROPERTY_TYPE=="L")
							{
								$arPROPERTY_VALUES = ${"PROPERTY_".$str_PROPERTY_ID."_VALUES"};
								if(is_array($arPROPERTY_VALUES))
								{
									foreach($arPROPERTY_VALUES as $key=>$value)
									{
										if(strlen($value)<=0)
											continue;
										?><input type="hidden" name="PROPERTY_<?=$str_PROPERTY_ID?>_VALUES[<?=$key?>]" value="<?=htmlspecialchars($value)?>"><?
									}
								}

								$arPROPERTY_VALUES_DEF = ${"PROPERTY_".$str_PROPERTY_ID."_VALUES_DEF"};
								if(is_array($arPROPERTY_VALUES_DEF))
								{
									foreach($arPROPERTY_VALUES_DEF as $key=>$value)
									{
										if(strlen($value)<=0)
											continue;
										?><input type="hidden" name="PROPERTY_<?=$str_PROPERTY_ID?>_VALUES_DEF[<?=$key?>]" value="<?=htmlspecialchars($value)?>"><?
									}
								}

								$arPROPERTY_VALUES_XML = ${"PROPERTY_".$str_PROPERTY_ID."_VALUES_XML"};
								if(is_array($arPROPERTY_VALUES_XML))
								{
									foreach($arPROPERTY_VALUES_XML as $key=>$value)
									{
										if(strlen($value)<=0)
											continue;
										?><input type="hidden" name="PROPERTY_<?=$str_PROPERTY_ID?>_VALUES_XML[<?=$key?>]" value="<?=htmlspecialchars($value)?>"><?
									}
								}

								$arPROPERTY_VALUES_SORT = ${"PROPERTY_".$str_PROPERTY_ID."_VALUES_SORT"};
								if(is_array($arPROPERTY_VALUES_SORT))
								{
									foreach($arPROPERTY_VALUES_SORT as $key=>$value)
									{
										if(strlen($value)<=0)
											continue;
										?><input type="hidden" name="PROPERTY_<?=$str_PROPERTY_ID?>_VALUES_SORT[<?=$key?>]" value="<?=htmlspecialchars($value)?>"><?
									}
								}

								if(IntVal(${"PROPERTY_".$str_PROPERTY_ID."_CNT"})>0):
									?><input type="hidden" name="PROPERTY_<?=$str_PROPERTY_ID?>_CNT" value="<?=IntVal(${"PROPERTY_".$str_PROPERTY_ID."_CNT"})?>"><?
								endif;
							}
							?>
							<input type="text" size="20"  maxlength="50" name="PROPERTY_<?echo $str_PROPERTY_ID?>_NAME" value="<?echo $str_PROPERTY_NAME?>">
						</td>
						<td>
						<select name="PROPERTY_<?echo $str_PROPERTY_ID?>_PROPERTY_TYPE" >
							<option value="S" <?if($str_PROPERTY_PROPERTY_TYPE=="S" && !$str_PROPERTY_USER_TYPE)echo " selected"?>><?echo GetMessage("IBLOCK_PROP_TYPE_S")?></option>
							<option value="N" <?if($str_PROPERTY_PROPERTY_TYPE=="N" && !$str_PROPERTY_USER_TYPE)echo " selected"?>><?echo GetMessage("IBLOCK_PROP_TYPE_N")?></option>
							<option value="L" <?if($str_PROPERTY_PROPERTY_TYPE=="L" && !$str_PROPERTY_USER_TYPE)echo " selected"?>><?echo GetMessage("IBLOCK_PROP_TYPE_L")?></option>
							<option value="F" <?if($str_PROPERTY_PROPERTY_TYPE=="F" && !$str_PROPERTY_USER_TYPE)echo " selected"?>><?echo GetMessage("IBLOCK_PROP_TYPE_F")?></option>
							<option value="G" <?if($str_PROPERTY_PROPERTY_TYPE=="G" && !$str_PROPERTY_USER_TYPE)echo " selected"?>><?echo GetMessage("IBLOCK_LINK_TO_SECTION")?></option>
							<option value="E" <?if($str_PROPERTY_PROPERTY_TYPE=="E" && !$str_PROPERTY_USER_TYPE)echo " selected"?>><?echo GetMessage("IBLOCK_LINK_TO_ELEMENTS")?></option>
							<?foreach(CIBlockProperty::GetUserType() as  $ar):?>
								<option value="<?=htmlspecialchars($ar["PROPERTY_TYPE"].":".$ar["USER_TYPE"])?>" <?if($str_PROPERTY_PROPERTY_TYPE==$ar["PROPERTY_TYPE"] && $str_PROPERTY_USER_TYPE==$ar["USER_TYPE"])echo " selected"?>><?=htmlspecialchars($ar["DESCRIPTION"])?></option>
							<?endforeach;?>
						</select>
						</td>
						<td align="center">
						<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_MULTIPLE" value="N">
						<input type="checkbox" name="PROPERTY_<?echo $str_PROPERTY_ID?>_MULTIPLE" value="Y"<?if($str_PROPERTY_MULTIPLE=="Y")echo " checked"?>>
						</td>
						<td align="center">
							<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_IS_REQUIRED" value="N">
							<input type="checkbox" name="PROPERTY_<?echo $str_PROPERTY_ID?>_IS_REQUIRED" value="Y"<?if($str_PROPERTY_IS_REQUIRED=="Y")echo " checked"?>>
						</td>
						<td>
							<input type="text" size="3" maxlength="10"  name="PROPERTY_<?echo $str_PROPERTY_ID?>_SORT" value="<?echo $str_PROPERTY_SORT?>">
						</td>
						<td><input type="text" size="15" maxlength="20"  name="PROPERTY_<?echo $str_PROPERTY_ID?>_CODE" value="<?echo $str_PROPERTY_CODE?>"></td>
						<td><input type="submit" title="<?echo GetMessage("IB_E_EDITPROP")?>" name="propedit[<?echo $str_PROPERTY_ID?>]"  value="..."></td>
						<td><?if(intval($str_PROPERTY_ID)>0):?><input type="checkbox" name="PROPERTY_<?echo $str_PROPERTY_ID?>_DEL" value="Y"><?endif?></td>
					</tr>
				<?endwhile;?>
			</table>
		</td>
	</tr>
<?
if($bTab3):
	$tabControl->BeginNextTab();
	?>
	<tr>
		<td valign="top"  width="40%"><label for="RSS_ACTIVE"><?echo GetMessage("IBLOCK_RSS_ACTIVE")?></label></td>
		<td valign="top" width="60%">
			<input type="hidden" name="RSS_ACTIVE" value="N">
			<input type="checkbox" id="RSS_ACTIVE" name="RSS_ACTIVE" value="Y"<?if($str_RSS_ACTIVE=="Y")echo " checked"?>>
		</td>
	</tr>
	<tr>
		<td valign="top" ><? echo GetMessage("IBLOCK_RSS_TTL")?></td>
		<td valign="top">
			<input type="text" name="RSS_TTL" size="20"  maxlength="40" value="<?echo $str_RSS_TTL?>">
		</td>
	</tr>

	<tr>
		<td valign="top"><label for="RSS_FILE_ACTIVE"><?echo GetMessage("IBLOCK_RSS_FILE_ACTIVE")?></label></td>
		<td valign="top">
			<input type="hidden" name="RSS_FILE_ACTIVE" value="N">
			<input type="checkbox" id="RSS_FILE_ACTIVE" name="RSS_FILE_ACTIVE" value="Y"<?if($str_RSS_FILE_ACTIVE=="Y")echo " checked"?>>
		</td>
	</tr>
	<tr>
		<td valign="top"  ><? echo GetMessage("IBLOCK_RSS_FILE_LIMIT")?></td>
		<td valign="top"  >
			<input type="text" name="RSS_FILE_LIMIT"  size="20" maxlength="40" value="<?echo $str_RSS_FILE_LIMIT?>">
		</td>
	</tr>
	<tr>
		<td valign="top" ><? echo GetMessage("IBLOCK_RSS_FILE_DAYS")?></td>
		<td valign="top">
			<input type="text" name="RSS_FILE_DAYS"  size="20" maxlength="40" value="<?echo $str_RSS_FILE_DAYS?>">
		</td>
	</tr>
	<tr>
		<td valign="top"><label for="RSS_YANDEX_ACTIVE"><?echo GetMessage("IBLOCK_RSS_YANDEX_ACTIVE")?></label></td>
		<td valign="top">
			<input type="hidden" name="RSS_YANDEX_ACTIVE" value="N">
			<input type="checkbox" id="RSS_YANDEX_ACTIVE" name="RSS_YANDEX_ACTIVE" value="Y"<?if($str_RSS_YANDEX_ACTIVE=="Y")echo " checked"?>>
		</td>
	</tr>

	<tr class="heading">
		<td colspan="2"><?echo GetMessage("IBLOCK_RSS_TITLE")?>:</td>
	</tr>
	<tr>
		<td valign="top"  colspan="2" align="center">
			<table>
				<tr class="heading">
					<td><?echo GetMessage("IBLOCK_RSS_FIELD")?></td>
					<td><?echo GetMessage("IBLOCK_RSS_TEMPL")?></td>
				</tr>
				<?
				$arCurNodesRSS = CIBlockRSS::GetNodeList(IntVal($ID));
				reset($arNodesRSS);
				while (list($key, $val) = each($arNodesRSS)):
					if($bVarsFromForm)
						$DB->InitTableVarsForEdit("b_iblock_rss", "RSS_", "str_RSS_", "_".$key);
					?>
					<tr>
						<td>
							<input type="text"  size="15" readonly maxlength="50" name="RSS_NODE_<?echo $key?>" value="<?echo $val?>">
						</td>
						<td><input type="text"  name="RSS_NODE_VALUE_<?echo $key?>" value="<?echo $arCurNodesRSS[$val]?>"></td>
					</tr>
				<?endwhile;?>
			</table>
		</td>
	</tr>
	<?
endif;

$tabControl->BeginNextTab();
?>
	<?
	if (CModule::IncludeModule("workflow")) :
		$arPermType = Array(
			"D"=>GetMessage("IBLOCK_ACCESS_D"),
			"R"=>GetMessage("IBLOCK_ACCESS_R"),
			"U"=>GetMessage("IBLOCK_ACCESS_U"),
			"W"=>GetMessage("IBLOCK_ACCESS_W"),
			"X"=>GetMessage("IBLOCK_ACCESS_X"));
	else :
		$arPermType = Array(
			"D"=>GetMessage("IBLOCK_ACCESS_D"),
			"R"=>GetMessage("IBLOCK_ACCESS_R"),
			"W"=>GetMessage("IBLOCK_ACCESS_W"),
			"X"=>GetMessage("IBLOCK_ACCESS_X"));
	endif;
	$perm = $ib->GetGroupPermissions($ID);
	if(!array_key_exists(1, $perm))
		$perm[1] = "X";
	?>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("IB_E_DEFAULT_ACCESS_TITLE")?></td>
	</tr>
	<tr>
		<td valign="top" nowrap width="40%"><?echo GetMessage("IB_E_EVERYONE")?> [<a class="tablebodylink" href="/bitrix/admin/group_edit.php?ID=2&amp;lang=<?=LANGUAGE_ID?>">2</a>]:</td>
		<td valign="top" width="60%">

				<select name="GROUP[2]" id="group_2">
				<?
				if($bVarsFromForm)
					$strSelected = $GROUP[2];
				else
					$strSelected = $perm[2];
				foreach($arPermType as $key => $val):
				?>
					<option value="<?echo $key?>"<?if($strSelected == $key)echo " selected"?>><?echo htmlspecialcharsex($val)?></option>
				<?endforeach?>
				</select>

				<script language="JavaScript">
				function OnGroupChange(control, message)
				{
					var all = document.getElementById('group_2');
					var msg = document.getElementById(message);
					if(all && all.value >= control.value && control.value != '')
					{
						if(msg) msg.innerHTML = '<?echo CUtil::JSEscape(GetMessage("IB_E_ACCESS_WARNING"))?>';
					}
					else
					{
						if(msg) msg.innerHTML = '';
					}
				}
				</script>

		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("IB_E_GROUP_ACCESS_TITLE")?></td>
	</tr>
	<?
	$groups = CGroup::GetList($by="sort", $order="asc", Array("ID"=>"~2"));
	while($r = $groups->ExtractFields("g_")):
		if($bVarsFromForm)
			$strSelected = $GROUP[$g_ID];
		else
			$strSelected = $perm[$g_ID];

		if($strSelected=="U" && !CModule::IncludeModule("workflow"))
			$strSelected="R";

		if($strSelected!="R" &&
			$strSelected!="U" &&
			$strSelected!="W" &&
			$strSelected!="X" &&
			$ID>0 && !$bVarsFromForm)
				$strSelected="";
		?>
	<tr>
		<td valign="top" nowrap width="40%"><?echo $g_NAME?> [<a class="tablebodylink" href="/bitrix/admin/group_edit.php?ID=<?=$g_ID?>&lang=<?=LANGUAGE_ID?>"><?=$g_ID?></a>]:</td>
		<td valign="top" width="60%">

				<select name="GROUP[<?echo $g_ID?>]" OnChange="OnGroupChange(this, 'spn_group_<?echo $g_ID?>');">
					<option value=""><?echo GetMessage("IB_E_DEFAULT_ACCESS")?></option>
				<?
				foreach($arPermType as $key => $val):
				?>
					<option value="<?echo $key?>"<?if($strSelected == $key)echo " selected"?>><?echo htmlspecialcharsex($val)?></option>
				<?endforeach?>
				</select>
				<span id="spn_group_<?echo $g_ID?>"></span>
		</td>
	</tr>
	<?endwhile?>
	<?
$tabControl->BeginNextTab();
	$arMessages = CIBlock::GetMessages($ID);
	if($bVarsFromForm)
	{
		foreach($arMessages as $MESSAGE_ID => $MESSAGE_TEXT)
			$arMessages[$MESSAGE_ID] = $_REQUEST[$MESSAGE_ID];
	}
	if($arIBTYPE["SECTIONS"]=="Y"):?>
	<tr>
		<td valign="top"><?echo GetMessage("IB_E_SECTIONS_NAME")?></td>
		<td valign="top">
			<input type="text" name="SECTIONS_NAME" size="20" maxlength="100" value="<?echo htmlspecialchars($arMessages["SECTIONS_NAME"])?>">
		</td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("IB_E_SECTION_NAME")?></td>
		<td valign="top">
			<input type="text" name="SECTION_NAME" size="20" maxlength="100" value="<?echo htmlspecialchars($arMessages["SECTION_NAME"])?>">
		</td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("IB_E_SECTION_ADD")?></td>
		<td valign="top">
			<input type="text" name="SECTION_ADD" size="20" maxlength="100" value="<?echo htmlspecialchars($arMessages["SECTION_ADD"])?>">
		</td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("IB_E_SECTION_EDIT")?></td>
		<td valign="top">
			<input type="text" name="SECTION_EDIT" size="20" maxlength="100" value="<?echo htmlspecialchars($arMessages["SECTION_EDIT"])?>">
		</td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("IB_E_SECTION_DELETE")?></td>
		<td valign="top">
			<input type="text" name="SECTION_DELETE" size="20" maxlength="100" value="<?echo htmlspecialchars($arMessages["SECTION_DELETE"])?>">
		</td>
	</tr>
	<?endif?>
	<tr>
		<td valign="top"><?echo GetMessage("IB_E_ELEMENTS_NAME")?></td>
		<td valign="top">
			<input type="text" name="ELEMENTS_NAME" size="20" maxlength="100" value="<?echo htmlspecialchars($arMessages["ELEMENTS_NAME"])?>">
		</td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("IB_E_ELEMENT_NAME")?></td>
		<td valign="top">
			<input type="text" name="ELEMENT_NAME" size="20" maxlength="100" value="<?echo htmlspecialchars($arMessages["ELEMENT_NAME"])?>">
		</td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("IB_E_ELEMENT_ADD")?></td>
		<td valign="top">
			<input type="text" name="ELEMENT_ADD" size="20" maxlength="100" value="<?echo htmlspecialchars($arMessages["ELEMENT_ADD"])?>">
		</td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("IB_E_ELEMENT_EDIT")?></td>
		<td valign="top">
			<input type="text" name="ELEMENT_EDIT" size="20" maxlength="100" value="<?echo htmlspecialchars($arMessages["ELEMENT_EDIT"])?>">
		</td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("IB_E_ELEMENT_DELETE")?></td>
		<td valign="top">
			<input type="text" name="ELEMENT_DELETE" size="20" maxlength="100" value="<?echo htmlspecialchars($arMessages["ELEMENT_DELETE"])?>">
		</td>
	</tr>
	<?
	$tabControl->Buttons(array("disabled"=>false, "back_url"=>'iblock_admin.php?lang='.$lang.'&type='.urlencode($type).'&admin='.($_REQUEST["admin"]=="Y"? "Y": "N")));
	$tabControl->End();
	?>
<?endif //if(IntVal($str_PROPERTY_ID)>0 || (strlen($str_PROPERTY_ID)>0 && $str_PROPERTY_ID[0]=="n")):?>
</form>

<?else: //if($Perm<="X"):?>
<br>
<?echo ShowError(GetMessage("IBLOCK_BAD_IBLOCK"));?>

<?
endif;

else: //if($arIBTYPE!==false):?>
<br>
<?echo ShowError(GetMessage("IBLOCK_BAD_BLOCK_TYPE_ID"));?>

<?
endif;// if($arIBTYPE!==false):

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
