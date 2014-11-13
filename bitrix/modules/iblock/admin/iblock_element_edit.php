<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/iblock.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");

IncludeModuleLangFile(__FILE__);
$strWarning = "";
$bVarsFromForm = false;
$ID = IntVal($ID);	//ID of the persistent record
$bCopy = ($action == "copy");

if($ID<=0 && IntVal($PID)>0)
	$ID = IntVal($PID);

$PREV_ID = intval($PREV_ID);

$WF_ID = $ID; 		//This is ID of the current copy

$bWorkflow = CModule::IncludeModule("workflow") && (CIBlock::GetArrayByID($IBLOCK_ID, "WORKFLOW") != "N");

if(($ID <= 0 || $bCopy) && $bWorkflow)
	$WF = "Y";
elseif(!$bWorkflow)
	$WF = "N";
$urlSectionAdminPage = COption::GetOptionString("iblock","combined_list_mode")=="Y"?"iblock_list_admin.php":"iblock_section_admin.php";
$urlElementAdminPage = COption::GetOptionString("iblock","combined_list_mode")=="Y"?"iblock_list_admin.php":"iblock_element_admin.php";

function _ShowStringPropertyField($name, $property_fields, $values, $bInitDef = false, $bVarsFromForm = false)
{
	global $bCopy;
	$start = 0;

	echo '<table cellpadding="0" cellspacing="0" border="0" class="nopadding" width="100%" id="tb'.md5($name).'">';

	$rows = $property_fields["ROW_COUNT"];
	$cols = $property_fields["COL_COUNT"];

	if($property_fields["WITH_DESCRIPTION"]=="Y")
		$strAddDesc = "[VALUE]";
	else
		$strAddDesc = "";

	if(!is_array($values)) $values = Array();
	foreach($values as $key=>$val)
	{
		if($bCopy)
		{
			$key = "n".$start;
			$start++;
		}
		echo '<tr><td>';
		$val_description = "";
		if(is_array($val) && is_set($val, "VALUE"))
		{
			$val_description = $val["DESCRIPTION"];
			$val = $val["VALUE"];
		}
		if($rows>1)
			echo '<textarea name="'.$name.'['.$key.']'.$strAddDesc.'" cols="'.$cols.'" rows="'.$rows.'">'.htmlspecialcharsex($val).'</textarea>';
		else
			echo '<input name="'.$name.'['.$key.']'.$strAddDesc.'" value="'.htmlspecialcharsex($val).'" size="'.$cols.'" type="text">';

		if($property_fields["WITH_DESCRIPTION"]=="Y")
			echo ' <span title="'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC").'">'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC_1").'<input name="'.$name.'['.$key.'][DESCRIPTION]" value="'.htmlspecialcharsex($val_description).'" size="18" type="text" id="'.$name.'['.$key.'][DESCRIPTION]"></span>';

		echo "<br>";
		echo '</td></tr>';

		if($property_fields["MULTIPLE"]!="Y")
		{
			$bVarsFromForm = true;
			break;
		}
	}

	if(!$bVarsFromForm)
	{
		$val_description = "";
		$MULTIPLE_CNT = IntVal($property_fields["MULTIPLE_CNT"]);
		$cnt = ($property_fields["MULTIPLE"]=="Y"? ($MULTIPLE_CNT>0 && $MULTIPLE_CNT<=30 ? $MULTIPLE_CNT : 5) + ($bInitDef && strlen($property_fields["DEFAULT_VALUE"])>0?1:0) : 1);
		for($i=0; $i<$cnt;$i++)
		{
			echo '<tr><td>';
			if($i==0 && $bInitDef && strlen($property_fields["DEFAULT_VALUE"])>0)
				$val = $property_fields["DEFAULT_VALUE"];
			else
				$val = "";

			if($rows>1)
				echo '<textarea name="'.$name.'[n'.($start + $i).']'.$strAddDesc.'" cols="'.$cols.'" rows="'.$rows.'">'.htmlspecialcharsex($val).'</textarea>';
			else
				echo '<input name="'.$name.'[n'.($start + $i).']'.$strAddDesc.'" value="'.htmlspecialcharsex($val).'" size="'.$cols.'" type="text">';

			if($property_fields["WITH_DESCRIPTION"]=="Y")
				echo ' <span title="'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC").'">'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC_1").'<input name="'.$name.'[n'.($start + $i).'][DESCRIPTION]" value="'.htmlspecialcharsex($val_description).'" size="18" type="text"></span>';

			echo "<br>";
			echo '</td></tr>';
		}
	}
	if($property_fields["MULTIPLE"]=="Y")
	{
		echo '<tr><td><input type="button" value="'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_ADD").'" onClick="addNewRow(\'tb'.md5($name).'\')"></td></tr>';
	}

	echo '</table>';
}

function _ShowGroupPropertyField($name, $property_fields, $values)
{
	if(!is_array($values)) $values = Array();

	$res = "";
	$bWas = false;
	$sections = CIBlockSection::GetTreeList(Array("IBLOCK_ID"=>$property_fields["LINK_IBLOCK_ID"]));
	while($ar = $sections->GetNext())
	{
		$res .= '<option value="'.$ar["ID"].'"';
		if(in_array($ar["ID"], $values))
		{
			$bWas = true;
			$res .= ' selected';
		}
		$res .= '>'.str_repeat(" . ", $ar["DEPTH_LEVEL"]).$ar["NAME"].'</option>';
	}
	echo '<select name="'.$name.'[]" size="'.$property_fields["MULTIPLE_CNT"].'" '.($property_fields["MULTIPLE"]=="Y"?"multiple":"").'>';
	echo '<option value=""'.(!$bWas?' selected':'').'>'.GetMessage("IBLOCK_ELEMENT_EDIT_NOT_SET").'</option>';
	echo $res;
	echo '</select>';
}

function _ShowElementPropertyField($name, $property_fields, $values, $bVarsFromForm = false)
{
	global $bCopy;
	$start = 0;

	if(!is_array($values)) $values = Array();
	//echo '<IFRAME TABINDEX=100 name="hfpn_'.md5($name).'" src="" width=0 height=0></IFRAME>';
	echo '<table cellpadding="0" cellspacing="0" border="0" class="nopadding" width="100%" id="tb'.md5($name).'">';
	$max_val = -1;
	foreach($values as $key=>$val)
	{
		if($bCopy)
		{
			$key = "n".$start;
			$start++;
		}
		if(is_array($val) && is_set($val, "VALUE"))
			$val = $val["VALUE"];
		$db_res = CIBlockElement::GetByID($val);
		$ar_res = $db_res->GetNext();
		echo '<tr><td>'.
			'<input name="'.$name.'['.$key.']" id="'.$name.'['.$key.']" value="'.htmlspecialcharsex($val).'" size="5" type="text">'.
			'<input type="button" value="..." onClick="jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang='.LANG.'&amp;IBLOCK_ID='.$property_fields["LINK_IBLOCK_ID"].'&amp;n='.$name.'&amp;k='.$key.'\', 600, 500);">'.
			'&nbsp;<span id="sp_'.md5($name).'_'.$key.'" >'.$ar_res['NAME'].'</span>'.
			'</td></tr>';
		if(substr($key, -1, 1)=='n' && $max_val < intval(substr($key, 1)))
			$max_val = intval(substr($key, 1));
		if($property_fields["MULTIPLE"]!="Y") {$bVarsFromForm = true; break;}
	}

	if(!$bVarsFromForm)
	{
		$MULTIPLE_CNT = IntVal($property_fields["MULTIPLE_CNT"]);
		$cnt = ($property_fields["MULTIPLE"]=="Y"? ($MULTIPLE_CNT>0 && $MULTIPLE_CNT<=30 ? $MULTIPLE_CNT : 5) : 1);
		for($i=$max_val+1; $i<$max_val+1+$cnt; $i++)
		{
			$val = "";
			$key = "n".($start + $i);
    		echo '<tr><td>'.
    			'<input name="'.$name.'['.$key.']" id="'.$name.'['.$key.']" value="'.htmlspecialcharsex($val).'" size="5" type="text">'.
    			'<input type="button" value="..." onClick="jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang='.LANG.'&amp;IBLOCK_ID='.$property_fields["LINK_IBLOCK_ID"].'&amp;n='.$name.'&amp;k='.$key.'\', 600, 500);">'.
    			'&nbsp;<span id="sp_'.md5($name).'_'.$key.'"></span>'.
    			'</td></tr>';
		}
		$max_val += $cnt;
	}

	if($property_fields["MULTIPLE"]=="Y")
	{
		echo '<tr><td>'.
			'<input type="button" value="'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_ADD").'..." onClick="jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang='.LANG.'&amp;IBLOCK_ID='.$property_fields["LINK_IBLOCK_ID"].'&amp;n='.$name.'&amp;m=y&amp;k='.$key.'\', 600, 500);">'.
			'<span id="sp_'.md5($name).'_'.$key.'" ></span>'.
			'</td></tr>';
	}

	echo '</table>';
	echo "<script type=\"text/javascript\">\r\n";
	echo "var MV_".md5($name)." = ".($max_val+1).";\r\n";
	echo "function InS".md5($name)."(id, name){ \r\n";
	echo "	oTbl=document.getElementById('tb".md5($name)."');\r\n";
	echo "	oRow=oTbl.insertRow(oTbl.rows.length-1); \r\n";
	echo "	oCell=oRow.insertCell(-1); \r\n";
	echo "	oCell.innerHTML=".
		"'<input name=\"".$name."[n'+MV_".md5($name)."+']\" value=\"'+id+'\" size=\"5\" type=\"text\">'+\r\n".
		"'<input type=\"button\" value=\"...\" '+\r\n".
		"'onClick=\"jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang=".LANG."&amp;IBLOCK_ID=".$property_fields["LINK_IBLOCK_ID"]."&amp;n=".$name."&amp;k='+MV_".md5($name)."+'\', '+\r\n".
		"' 600, 500);\">'+".
		"'&nbsp;<span id=\"sp_".md5($name)."_'+MV_".md5($name)."+'\" >'+name+'</span>".
		"';";
	echo 'MV_'.md5($name).'++;';
	echo '}';
	echo "\r\n</script>";
}

function _ShowFilePropertyField($name, $property_fields, $values, $max_file_size_show=50000, $bVarsFromForm = false)
{
	global $bCopy;

	$bVarsFromForm = false;
	echo '<table cellpadding="0" cellspacing="0" border="0" class="nopadding" width="100%" id="tb'.md5($name).'">';
	if(!is_array($values)) $values = Array();
	$cols = $property_fields["COL_COUNT"];

	if(!$bCopy)
	{
		foreach($values as $key=>$val)
		{
			echo '<tr><td>';
			$val_description = "";
			if(is_array($val) && is_set($val, "VALUE"))
			{
				$val_description = $val["DESCRIPTION"];
				$val = $val["VALUE"];
			}
			echo CFile::InputFile($name."[".$key."]", $cols, $val, false, 0, "");
			echo "<br>";
			echo CFile::ShowFile($val, $max_file_size_show, 400, 400, true)."<br>";
			if($property_fields["WITH_DESCRIPTION"]=="Y")
				echo ' <span title="'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC").'">'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC_1").'<input name="DESCRIPTION_'.$name.'['.$key.']" value="'.htmlspecialcharsex($val_description).'" size="18" type="text"></span>';
			echo "<br>";
			echo '</td></tr>';
			if($property_fields["MULTIPLE"]!="Y")
			{
				$bVarsFromForm = true;
				break;
			}
		}
	}

	if(!$bVarsFromForm)
	{
		$MULTIPLE_CNT = IntVal($property_fields["MULTIPLE_CNT"]);
		$cnt = ($property_fields["MULTIPLE"]=="Y"? ($MULTIPLE_CNT>0 && $MULTIPLE_CNT<=30 ? $MULTIPLE_CNT : 5) + ($bInitDef && strlen($property_fields["DEFAULT_VALUE"])>0?1:0) : 1);
		for($i=0; $i<$cnt;$i++)
		{
			echo '<tr><td>';
			$val_description = "";
			echo CFile::InputFile($name."[n".$i."]", $cols, "", false, 0, "");
			if($property_fields["WITH_DESCRIPTION"]=="Y")
				echo ' <span title="'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC").'">'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC_1").'<input name="DESCRIPTION_'.$name.'[n'.$i.']" value="'.htmlspecialcharsex($val_description).'" size="18" type="text"></span>';
			echo "<br>";
			echo '</td></tr>';
		}
	}

	if($property_fields["MULTIPLE"]=="Y")
	{
		echo '<tr><td><input type="button" value="'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_ADD").'" onClick="addNewRow(\'tb'.md5($name).'\')"></td></tr>';
	}

	echo '</table>';
}

function _ShowListPropertyField($name, $property_fields, $values, $bInitDef = false, $def_text = false)
{
	if(!is_array($values)) $values = Array();
	$id = $property_fields["ID"];
	$multiple = $property_fields["MULTIPLE"];
	$res = "";
	if($property_fields["LIST_TYPE"]=="C") //list property as checkboxes
	{
		$cnt = 0;
		$wSel = false;
		$prop_enums = CIBlockProperty::GetPropertyEnum($id);
		while($ar_enum = $prop_enums->Fetch())
		{
			$cnt++;
			if($bInitDef)
				$sel = ($ar_enum["DEF"]=="Y");
			else
				$sel = in_array($ar_enum["ID"], $values);
			if($sel)
				$wSel = true;

			$uniq = md5(uniqid(rand(), true));
			if($multiple=="Y") //multiple
				$res .= '<input type="checkbox" name="'.$name.'[]" value="'.htmlspecialchars($ar_enum["ID"]).'"'.($sel?" checked":"").' id="'.$uniq.'"><label for="'.$uniq.'">'.htmlspecialcharsex($ar_enum["VALUE"]).'</label><br>';
			else //if(MULTIPLE=="Y")
				$res .= '<input type="radio" name="'.$name.'[]" id="'.$uniq.'" value="'.htmlspecialchars($ar_enum["ID"]).'"'.($sel?" checked":"").'><label for="'.$uniq.'">'.htmlspecialcharsex($ar_enum["VALUE"]).'</label><br>';

			if($cnt==1)
				$res_tmp = '<input type="checkbox" name="'.$name.'[]" value="'.htmlspecialchars($ar_enum["ID"]).'"'.($sel?" checked":"").' id="'.$uniq.'"><br>';
		}


		$uniq = md5(uniqid(rand(), true));

		if($cnt==1)
			$res = $res_tmp;
		elseif($multiple!="Y")
			$res = '<input type="radio" name="'.$name.'[]" value=""'.(!$wSel?" checked":"").' id="'.$uniq.'"><label for="'.$uniq.'">'.htmlspecialcharsex(($def_text ? $def_text : GetMessage("IBLOCK_ELEMENT_PROP_NO") )).'</label><br>'.$res;

		if($multiple=="Y" || $cnt==1)
			$res = '<input type="hidden" name="'.$name.'" value="">'.$res;
	}
	else //list property as list
	{
		$bNoValue = true;
		$prop_enums = CIBlockProperty::GetPropertyEnum($id);
		while($ar_enum = $prop_enums->Fetch())
		{
			if($bInitDef)
				$sel = ($ar_enum["DEF"]=="Y");
			else
				$sel = in_array($ar_enum["ID"], $values);
			if($sel)
				$bNoValue = false;
			$res .= '<option value="'.htmlspecialchars($ar_enum["ID"]).'"'.($sel?" selected":"").'>'.htmlspecialcharsex($ar_enum["VALUE"]).'</option>';
		}

		if($property_fields["MULTIPLE"]=="Y" && IntVal($property_fields["ROW_COUNT"])<2)
			$property_fields["ROW_COUNT"] = 5;
		if($property_fields["MULTIPLE"]=="Y")
			$property_fields["ROW_COUNT"]++;
		$res = '<select name="'.$name.'[]" size="'.$property_fields["ROW_COUNT"].'" '.($property_fields["MULTIPLE"]=="Y"?"multiple":"").'>'.
				'<option value=""'.($bNoValue?' selected':'').'>'.htmlspecialcharsex(($def_text ? $def_text : GetMessage("IBLOCK_ELEMENT_PROP_NA") )).'</option>'.
				$res.
				'</select>';
	}
	echo $res;
}
function _ShowUserPropertyField($name, $property_fields, $values, $bInitDef = false, $bVarsFromForm = false, $max_file_size_show=50000, $form_name = "form_element")
{
	global $bCopy;
	$start = 0;

	if(!is_array($property_fields["~VALUE"]))
		$values = Array();
	else
		$values = $property_fields["~VALUE"];
	unset($property_fields["VALUE"]);
	unset($property_fields["~VALUE"]);

	$html = '<table cellpadding="0" cellspacing="0" border="0" class="nopadding" width="100%" id="tb'.md5($name).'">';
	$arUserType = CIBlockProperty::GetUserType($property_fields["USER_TYPE"]);
	$max_val = -1;
	if(($arUserType["PROPERTY_TYPE"] !== "F") || (!$bCopy))
	{
		foreach($values as $key=>$val)
		{
			if($bCopy)
			{
				$key = "n".$start;
				$start++;
			}

			if(!is_array($val) || !array_key_exists("VALUE",$val))
				$val = array("VALUE"=>$val, "DESCRIPTION"=>"");

			$html .= '<tr><td>';
			if(array_key_exists("GetPropertyFieldHtml", $arUserType))
				$html .= call_user_func_array($arUserType["GetPropertyFieldHtml"],
					array(
						$property_fields,
						$val,
						array(
							"VALUE"=>'PROP['.$property_fields["ID"].']['.$key.'][VALUE]',
							"DESCRIPTION"=>'PROP['.$property_fields["ID"].']['.$key.'][DESCRIPTION]',
							"FORM_NAME"=>$form_name,
							"MODE"=>"FORM_FILL"
						),
					));
			else
				$html .= '&nbsp;';
			$html .= '</td></tr>';

			if(substr($key, -1, 1)=='n' && $max_val < intval(substr($key, 1)))
				$max_val = intval(substr($key, 1));
			if($property_fields["MULTIPLE"] != "Y")
			{
				$bVarsFromForm = true;
				break;
			}
		}
	}

	if(!$bVarsFromForm)
	{
		$MULTIPLE_CNT = IntVal($property_fields["MULTIPLE_CNT"]);
		$cnt = ($property_fields["MULTIPLE"]=="Y"? ($MULTIPLE_CNT>0 && $MULTIPLE_CNT<=30 ? $MULTIPLE_CNT : 5) : 1);
		for($i=$max_val+1; $i<$max_val+1+$cnt; $i++)
		{
			$val = array("VALUE"=>$property_fields["DEFAULT_VALUE"], "DESCRIPTION"=>"");
			$key = "n".($start + $i);

			$html .= '<tr><td>';
			if(array_key_exists("GetPropertyFieldHtml", $arUserType))
				$html .= call_user_func_array($arUserType["GetPropertyFieldHtml"],
					array(
						$property_fields,
						$val,
						array(
							"VALUE"=>'PROP['.$property_fields["ID"].']['.$key.'][VALUE]',
							"DESCRIPTION"=>'PROP['.$property_fields["ID"].']['.$key.'][DESCRIPTION]',
							"FORM_NAME"=>$form_name,
							"MODE"=>"FORM_FILL"
						),
					));
			else
				$html .= '&nbsp;';
			$html .= '</td></tr>';
		}
		$max_val += $cnt;
	}
	if($property_fields["MULTIPLE"]=="Y")
	{
		$html .= '<tr><td><input type="button" value="'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_ADD").'" onClick="addNewRow(\'tb'.md5($name).'\')"></td></tr>';
	}
	$html .= '</table>';
	echo $html;
}

function _ShowPropertyField($name, $property_fields, $values, $bInitDef = false, $bVarsFromForm = false, $max_file_size_show = 50000, $form_name = "form_element")
{
	$type = $property_fields["PROPERTY_TYPE"];
	if($property_fields["USER_TYPE"]!="")
		_ShowUserPropertyField($name, $property_fields, $values, $bInitDef, $bVarsFromForm, $max_file_size_show, $form_name);
	elseif($type=="L") //list property
		_ShowListPropertyField($name, $property_fields, $values, $bInitDef);
	elseif($type=="F") //file property
		_ShowFilePropertyField($name, $property_fields, $values, $max_file_size_show, $bVarsFromForm);
	elseif($type=="G") //section link
	{
		if(function_exists("_ShowGroupPropertyField_custom"))
			_ShowGroupPropertyField_custom($name, $property_fields, $values, $bVarsFromForm);
		else
			_ShowGroupPropertyField($name, $property_fields, $values, $bVarsFromForm);
	}
	elseif($type=="E") //element link
		_ShowElementPropertyField($name, $property_fields, $values, $bVarsFromForm);
	else
		_ShowStringPropertyField($name, $property_fields, $values, $bInitDef, $bVarsFromForm);
}


class _CIBlockError
{
	var $err_type, $err_text, $err_level;
	function _CIBlockError($err_level, $err_type, $err_text)
	{
		$this->err_type = $err_type;
		$this->err_text = $err_text;
		$this->err_level = $err_level;
	}
}

$error = false;

$IBLOCK_ID = IntVal($IBLOCK_ID); //information block ID
$WF = ($WF=="Y") ? "Y" : "N";	//workflow mode
$view = ($view=="Y") ? "Y" : "N"; //view mode

if(strlen($return_url)>0 && strtolower(substr($return_url, strlen($APPLICATION->GetCurPage())))==strtolower($APPLICATION->GetCurPage()))
	$return_url = "";
if(strlen($return_url)<=0 && $from=="iblock_section_admin")
	$return_url = $urlSectionAdminPage."?lang=".LANG."&type=".$type."&IBLOCK_ID=".$IBLOCK_ID."&find_section_section=".intval($find_section_section);


do{ //one iteration loop

	$arIBTYPE = CIBlockType::GetByIDLang($type, LANG);
	if($arIBTYPE===false)
	{
		$error = new _CIBlockError(1, "BAD_IBLOCK_TYPE", GetMessage("IBLOCK_BAD_BLOCK_TYPE_ID"));
		break;
	}

	$bBadBlock = true;
	$arIBlock = CIBlock::GetArrayByID($IBLOCK_ID);
	if($arIBlock)
	{
		$BlockPerm = CIBlock::GetPermission($IBLOCK_ID);
		if($BlockPerm>="W")
			$bBadBlock=false;
		elseif(($WF=="Y" || $view=="Y") && $BlockPerm>="U" && $bWorkflow)
			$bBadBlock = false;
	}

	if($bBadBlock)
	{
		$error = new _CIBlockError(1, "BAD_IBLOCK", GetMessage("IBLOCK_BAD_IBLOCK"));
		$APPLICATION->SetTitle(/*$arIBTYPE["NAME"].": ".*/$arIBTYPE["ELEMENT_NAME"].": ".GetMessage("IBLOCK_EDIT_TITLE"));
		break;
	}

	$bTab2 = ($arIBTYPE["SECTIONS"]=="Y");
	$bTab4 = $bWorkflow;

	$aTabs = array();
	$aTabs[] = array("DIV" => "edit1", "TAB" => $arIBlock["ELEMENT_NAME"], "ICON"=>"iblock_element", "TITLE"=>$arIBlock["ELEMENT_NAME"]);
	$aTabs[] = array("DIV" => "edit5", "TAB" => GetMessage("IBEL_E_TAB_PREV"), "ICON"=>"iblock_element", "TITLE"=>GetMessage("IBEL_E_TAB_PREV_TITLE"));
	$aTabs[] = array("DIV" => "edit6", "TAB" => GetMessage("IBEL_E_TAB_DET"), "ICON"=>"iblock_element", "TITLE"=>GetMessage("IBEL_E_TAB_DET_TITLE"));
	if($bTab2) $aTabs[] = array("DIV" => "edit2", "TAB" => $arIBlock["SECTIONS_NAME"], "ICON"=>"iblock_element_section", "TITLE"=>$arIBlock["SECTIONS_NAME"]);
	$aTabs[] = array("DIV" => "edit3", "TAB" => GetMessage("IBLOCK_EL_TAB_MO"), "ICON"=>"iblock_element_params", "TITLE"=>GetMessage("IBLOCK_EL_TAB_MO_TITLE"));
	if($bTab4) $aTabs[] = array("DIV" => "edit4", "TAB" => GetMessage("IBLOCK_EL_TAB_WF"), "ICON"=>"iblock_element_wf", "TITLE"=>GetMessage("IBLOCK_EL_TAB_WF_TITLE"));

	$bCustomForm = 	(strlen($arIBlock["EDIT_FILE_AFTER"])>0 && is_file($_SERVER["DOCUMENT_ROOT"].$arIBlock["EDIT_FILE_AFTER"]))
		|| (strlen($arIBTYPE["EDIT_FILE_AFTER"])>0 && is_file($_SERVER["DOCUMENT_ROOT"].$arIBTYPE["EDIT_FILE_AFTER"]));

	$tabControl = new CAdminForm($bCustomForm? "tabControl": "form_element_".$IBLOCK_ID, $aTabs);

	if($ID>0)
	{
		$rsElement = CIBlockElement::GetList(Array(), Array("ID" => $ID, "IBLOCK_ID" => $IBLOCK_ID, "SHOW_HISTORY"=>"Y"), false, false, Array("ID"));
		if(!$rsElement->Fetch())
		{
			$error = new _CIBlockError(1, "BAD_ELEMENT", GetMessage("IBLOCK_BAD_ELEMENT"));
			$APPLICATION->SetTitle(/*$arIBTYPE["NAME"].": ".*/$arIBTYPE["ELEMENT_NAME"].": ".GetMessage("IBLOCK_EDIT_TITLE"));
			break;
		}
	}

	$customTabber = new CAdminTabEngine("OnAdminIBlockElementEdit", array("ID" => $ID, "IBLOCK"=>$arIBlock, "IBLOCK_TYPE"=>$arIBTYPE));

	// workflow mode
	if($ID>0 && $WF=="Y")
	{
		// get ID of the last record in workflow
		$WF_ID = CIBlockElement::WF_GetLast($ID);

		// check for edit permissions
		$STATUS_ID = CIBlockElement::WF_GetCurrentStatus($WF_ID, $STATUS_TITLE);
		$STATUS_PERMISSION = CIBlockElement::WF_GetStatusPermission($STATUS_ID);

		if($STATUS_ID>1 && $STATUS_PERMISSION<2)
		{
			$error = new _CIBlockError(1, "ACCESS_DENIED", GetMessage("IBLOCK_ACCESS_DENIED_STATUS"));
			break;
		}
		elseif($STATUS_ID==1)
		{
			$WF_ID = $ID;
			$STATUS_ID = CIBlockElement::WF_GetCurrentStatus($WF_ID, $STATUS_TITLE);
			$STATUS_PERMISSION = CIBlockElement::WF_GetStatusPermission($STATUS_ID);
		}

		// check if document is locked
		if(CIBlockElement::WF_IsLocked($ID, $locked_by, $date_lock))
		{
			$str = str_replace("#ID#", "$locked_by", GetMessage("IBLOCK_DOCUMENT_LOCKED"));
			$str = str_replace("#DATE#", "$date_lock", $str);
			$error = new _CIBlockError(2, "BLOCKED", $strWarning);
			break;
		}
	}

	//transpose files array
	// [property id] [value id] = file array (name, type, tmp_name, error, size)
	$PROP = $_POST['PROP'];
	if (is_array($PROP) && count($PROP) > 0)
	{
		foreach ($PROP as $k1=>$val1)
		{
			if(is_array($val1))
			{
				foreach($val1 as $k2=>$val2)
				{
					if (is_set($_POST, preg_replace("/([^a-z0-9])/is", "_", "PROP[".$k1."][".$k2."][VALUE][TEXT]")))
					{
						$PROP[$k1][$k2] = array();
						$PROP[$k1][$k2]["VALUE"]["TEXT"] = $_POST[preg_replace("/([^a-z0-9])/is", "_", "PROP[".$k1."][".$k2."][VALUE][TEXT]")];
						$PROP[$k1][$k2]["VALUE"]["TYPE"] = $_POST[preg_replace("/([^a-z0-9])/is", "_", "PROP[".$k1."][".$k2."][VALUE][TYPE]")];
					}
				}
			}
		}
	}
	$files = $_FILES["PROP"];
	if(is_array($files))
	{
		foreach($files as $k1=>$val1)
		{
			foreach($val1 as $k2=>$val2)
			{
				if(!is_array($PROP[$k2]))
					$PROP[$k2] = Array();
				foreach($val2 as $k3=>$val3)
				{
					if(!is_array($PROP[$k2][$k3]))
						$PROP[$k2][$k3] = Array();
					$PROP[$k2][$k3][$k1] = $val3;
				}
			}
		}
	}

	if(is_array($PROP_del))
	{
		foreach($PROP_del as $k1=>$val1)
			foreach($val1 as $k2=>$val2)
				$PROP[$k1][$k2]["del"]=$val2;
	}

	$DESCRIPTION_PROP = $_POST["DESCRIPTION_PROP"];
	if(is_array($DESCRIPTION_PROP))
	{
		foreach($DESCRIPTION_PROP as $k1=>$val1)
		{
			foreach($val1 as $k2=>$val2)
			{
				if(is_set($PROP[$k1], $k2) && is_array($PROP[$k1][$k2]) && is_set($PROP[$k1][$k2], "DESCRIPTION"))
					$PROP[$k1][$k2]["DESCRIPTION"] = $val2;
				else
					$PROP[$k1][$k2] = Array("VALUE"=>$PROP[$k1][$k2], "DESCRIPTION"=>$val2);
			}
		}
	}

	if(strlen($arIBlock["EDIT_FILE_BEFORE"])>0 && is_file($_SERVER["DOCUMENT_ROOT"].$arIBlock["EDIT_FILE_BEFORE"]))
	{
		include($_SERVER["DOCUMENT_ROOT"].$arIBlock["EDIT_FILE_BEFORE"]);
	}
	elseif(strlen($arIBTYPE["EDIT_FILE_BEFORE"])>0 && is_file($_SERVER["DOCUMENT_ROOT"].$arIBTYPE["EDIT_FILE_BEFORE"]))
	{
		include($_SERVER["DOCUMENT_ROOT"].$arIBTYPE["EDIT_FILE_BEFORE"]);
	}


	if($REQUEST_METHOD=="POST" && strlen($Update)>0 && $view!="Y" && (!$error) && empty($dontsave))
	{

		$DB->StartTransaction();

		if(!(check_bitrix_sessid() || $_SESSION['IBLOCK_CUSTOM_FORM']===true))
		{
			$strWarning .= GetMessage("IBLOCK_WRONG_SESSION")."<br>";
			$error = new _CIBlockError(2, "BAD_SAVE", $strWarning);
			$bVarsFromForm = true;
		}
		elseif($WF=="Y" && $bWorkflow && intval($_POST["WF_STATUS_ID"])<=0)
			$strWarning .= GetMessage("IBLOCK_WRONG_WF_STATUS")."<br>";
		elseif($WF=="Y" && $bWorkflow && CIBlockElement::WF_GetStatusPermission($_POST["WF_STATUS_ID"])<1)
			$strWarning .= GetMessage("IBLOCK_ACCESS_DENIED_STATUS")." [".$_POST["WF_STATUS_ID"]."]."."<br>";
		else
		{
			if(!$customTabber->Check())
			{
				if($ex = $APPLICATION->GetException())
					$strWarning .= $ex->GetString();
				else
					$strWarning .= "Error. ";
			}
			else
			{
				$bCatalog = false;
				if (
					CModule::IncludeModule("catalog")
					&& CCatalog::GetByID($IBLOCK_ID)
					&& file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/admin/templates/product_edit_validator.php")
				)
				{
					$bCatalog = true;
					// errors'll be appended to $strWarning;
					include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/admin/templates/product_edit_validator.php");
				}

				if (strlen($strWarning) <= 0)
				{
					$bs = new CIBlockElement;

					if(
						$arIBlock["FIELDS"]["PREVIEW_PICTURE"]["DEFAULT_VALUE"]["FROM_DETAIL"] === "Y"
						&& (
							$_FILES["PREVIEW_PICTURE"]["size"] <= 0
						)
						&& $_FILES["DETAIL_PICTURE"]["size"] > 0
					)
					{
						if($ID > 0 && ${"PREVIEW_PICTURE_del"} !== "Y")
						{
							if($WF == "Y")
								$LAST_ID = CIBlockElement::WF_GetLast($ID);
							else
								$LAST_ID = $ID;
							$rsElement = CIBlockElement::GetList(Array("ID" => "DESC"), Array("ID" => $LAST_ID, "IBLOCK_ID" => $IBLOCK_ID, "SHOW_HISTORY"=>"Y"), false, false, Array("ID", "PREVIEW_PICTURE"));
							$arOldElement = $rsElement->Fetch();
						}
						else
						{
							$arOldElement = false;
						}

						if(!$arOldElement || !$arOldElement["PREVIEW_PICTURE"])
						{
							if(copy($_FILES["DETAIL_PICTURE"]["tmp_name"], $_FILES["DETAIL_PICTURE"]["tmp_name"]."~"))
							{
								$_FILES["PREVIEW_PICTURE"] = $_FILES["DETAIL_PICTURE"];
								$_FILES["PREVIEW_PICTURE"]["tmp_name"] .= "~";
							}
						}
					}

					if($arIBlock["FIELDS"]["PREVIEW_PICTURE"]["DEFAULT_VALUE"]["SCALE"] === "Y")
					{
						$arPREVIEW_PICTURE = CIBlock::ResizePicture($_FILES["PREVIEW_PICTURE"], $arIBlock["FIELDS"]["PREVIEW_PICTURE"]["DEFAULT_VALUE"]);
						if(!is_array($arPREVIEW_PICTURE))
						{
							if($arIBlock["FIELDS"]["PREVIEW_PICTURE"]["DEFAULT_VALUE"]["IGNORE_ERRORS"] === "Y")
								$arPREVIEW_PICTURE = $_FILES["PREVIEW_PICTURE"];
							else
							{
								$strWarning .= GetMessage("IBLOCK_FIELD_PREVIEW_PICTURE").": ".$arPREVIEW_PICTURE."<br>";
								$arPREVIEW_PICTURE = array(
									"name" => false,
									"type" => false,
									"tmp_name" => false,
									"error" => 4,
									"size" => 0,
								);
							}
						}
					}
					else
					{
						$arPREVIEW_PICTURE = $_FILES["PREVIEW_PICTURE"];
					}
					$arPREVIEW_PICTURE["del"] = ${"PREVIEW_PICTURE_del"};
					$arPREVIEW_PICTURE["description"] = ${"PREVIEW_PICTURE_descr"};

					if($arIBlock["FIELDS"]["DETAIL_PICTURE"]["DEFAULT_VALUE"]["SCALE"] === "Y")
					{
						$arDETAIL_PICTURE = CIBlock::ResizePicture($_FILES["DETAIL_PICTURE"], $arIBlock["FIELDS"]["DETAIL_PICTURE"]["DEFAULT_VALUE"]);
						if(!is_array($arDETAIL_PICTURE))
						{
							if($arIBlock["FIELDS"]["DETAIL_PICTURE"]["DEFAULT_VALUE"]["IGNORE_ERRORS"] === "Y")
								$arDETAIL_PICTURE = $_FILES["DETAIL_PICTURE"];
							else
							{
								$strWarning .= GetMessage("IBLOCK_FIELD_DETAIL_PICTURE").": ".$arDETAIL_PICTURE."<br>";
								$arDETAIL_PICTURE = array(
									"name" => false,
									"type" => false,
									"tmp_name" => false,
									"error" => 4,
									"size" => 0,
								);
							}
						}
					}
					else
					{
						$arDETAIL_PICTURE = $_FILES["DETAIL_PICTURE"];
					}
					$arDETAIL_PICTURE["del"] = ${"DETAIL_PICTURE_del"};
					$arDETAIL_PICTURE["description"] = ${"DETAIL_PICTURE_descr"};

					$arFields = Array(
						"ACTIVE" => $_POST["ACTIVE"],
						"MODIFIED_BY" => $USER->GetID(),
						"IBLOCK_SECTION" => $_POST["IBLOCK_SECTION"],
						"IBLOCK_ID" => $IBLOCK_ID,
						"ACTIVE_FROM" => $_POST["ACTIVE_FROM"],
						"ACTIVE_TO" => $_POST["ACTIVE_TO"],
						"SORT" => $_POST["SORT"],
						"NAME" => $_POST["NAME"],
						"CODE" => $_POST["CODE"],
						"TAGS" => $_POST["TAGS"],
						"PREVIEW_PICTURE" => $arPREVIEW_PICTURE,
						"PREVIEW_TEXT" => $_POST["PREVIEW_TEXT"],
						"PREVIEW_TEXT_TYPE" => $_POST["PREVIEW_TEXT_TYPE"],
						"DETAIL_PICTURE" => $arDETAIL_PICTURE,
						"DETAIL_TEXT" => $_POST["DETAIL_TEXT"],
						"DETAIL_TEXT_TYPE" => $_POST["DETAIL_TEXT_TYPE"],
						"PROPERTY_VALUES" => $PROP,
						);
					if(COption::GetOptionString("iblock", "show_xml_id", "N")=="Y" && is_set($_POST, "XML_ID"))
						$arFields["XML_ID"] = $_POST["XML_ID"];

					if ($bWorkflow)
					{
						$arFields["WF_COMMENTS"] = $_POST["WF_COMMENTS"];
						if(intval($_POST["WF_STATUS_ID"])>0)
							$arFields["WF_STATUS_ID"] = $_POST["WF_STATUS_ID"];
					}

					if($ID>0)
						$res = $bs->Update($ID, $arFields, $WF=="Y");
					else
					{
						$ID = $bs->Add($arFields, $bWorkflow);
						$res = ($ID>0);
						$PARENT_ID = $ID;
					}

					if(substr($_FILES["PREVIEW_PICTURE"]["tmp_name"] , -1) === "~")
						@unlink($_FILES["PREVIEW_PICTURE"]["tmp_name"]);

					if(!$res)
						$strWarning .= $bs->LAST_ERROR."<br>";
					else
						CIBlockElement::RecalcSections($ID);

					if(($bCatalog || CModule::IncludeModule("catalog") && CCatalog::GetByID($IBLOCK_ID)) && strlen($strWarning)<=0)
					{
						include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/admin/templates/product_edit_action.php");
					}
				} // if ($strWarning)
			}//if(!$customTabber->Check())
		}

		if(strlen($strWarning)<=0)
		{
			if(!$customTabber->Action())
			{
				if ($ex = $APPLICATION->GetException())
					$strWarning .= $ex->GetString();
				else
					$strWarning .= "Error. ";
			}
		}

		if(strlen($strWarning)>0)
		{
			$error = new _CIBlockError(2, "BAD_SAVE", $strWarning);
			$bVarsFromForm = true;
			$DB->Rollback();
		}
		else
		{
			if($bWorkflow)
				CIBlockElement::WF_UnLock($ID);

			$arFields['ID'] = $ID;
			if(function_exists('BXIBlockAfterSave'))
				BXIBlockAfterSave($arFields);

			$DB->Commit();

			if(strlen($apply)<=0)
			{
				if(strlen($return_url)>0)
					LocalRedirect($return_url);
				else
					LocalRedirect("/bitrix/admin/".$urlElementAdminPage."?lang=".LANG. "&type=".htmlspecialchars($type)."&IBLOCK_ID=".$IBLOCK_ID."&find_section_section=".intval($find_section_section));
			}
			else
				LocalRedirect("/bitrix/admin/iblock_element_edit.php?ID=".$ID.($WF=="Y"?"&WF=Y":"")."&lang=".LANG. "&type=".htmlspecialchars($type)."&".$tabControl->ActiveTabParam()."&IBLOCK_ID=".$IBLOCK_ID."&find_section_section=".intval($find_section_section).(strlen($return_url)>0?"&return_url=".UrlEncode($return_url):""));
		}
	}

	if(!empty($dontsave))
	{
		if($bWorkflow)
			CIBlockElement::WF_UnLock($ID);

		if(strlen($return_url)>0)
			LocalRedirect($return_url);
		else
			LocalRedirect("/bitrix/admin/".$urlElementAdminPage."?lang=".LANG. "&type=".htmlspecialchars($type)."&IBLOCK_ID=".$IBLOCK_ID."&find_section_section=".intval($find_section_section));
	}
}while(false);

if($error && $error->err_level==1)
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	CAdminMessage::ShowOldStyleError($error->err_text);
}
else
{
	if(!$arIBlock["ELEMENT_NAME"])
		$arIBlock["ELEMENT_NAME"] = $arIBTYPE["ELEMENT_NAME"]? $arIBTYPE["ELEMENT_NAME"]: GetMessage("IBEL_E_IBLOCK_ELEMENT");
	if(!$arIBlock["SECTIONS_NAME"])
		$arIBlock["SECTIONS_NAME"] = $arIBTYPE["SECTION_NAME"]? $arIBTYPE["SECTION_NAME"]: GetMessage("IBEL_E_IBLOCK_SECTIONS");

	$str_ACTIVE="Y";
	$str_SORT="500";
	$str_DETAIL_TEXT_TYPE="html";
	$str_PREVIEW_TEXT_TYPE="html";

	if(!$error && $bWorkflow && $view!="Y")
	{
		if(!$bCopy)
			CIBlockElement::WF_Lock($ID);
		else
			CIBlockElement::WF_UnLock($ID);
	}

	if($view=="Y")
	{
		$WF_ID = $ID;
		$ID = CIBlockElement::GetRealElement($ID);

		if($PREV_ID)
		{
			$prev_result = CIBlockElement::GetByID($PREV_ID);
			$prev_arElement = $prev_result->ExtractFields("str_prev_");
			if(!$prev_arElement)
				$PREV_ID = 0;
		}
	}

	$str_IBLOCK_ELEMENT_SECTION = Array();
	$str_ACTIVE = $arIBlock["FIELDS"]["ACTIVE"]["DEFAULT_VALUE"] === "N"? "N": "Y";
	$str_NAME = htmlspecialchars($arIBlock["FIELDS"]["NAME"]["DEFAULT_VALUE"]);
	if($arIBlock["FIELDS"]["ACTIVE_FROM"]["DEFAULT_VALUE"] === "=now")
		$str_ACTIVE_FROM = ConvertTimeStamp(false, "FULL");
	elseif($arIBlock["FIELDS"]["ACTIVE_FROM"]["DEFAULT_VALUE"] === "=today")
		$str_ACTIVE_FROM = ConvertTimeStamp(false, "SHORT");

	if(intval($arIBlock["FIELDS"]["ACTIVE_TO"]["DEFAULT_VALUE"]) > 0)
		$str_ACTIVE_TO = ConvertTimeStamp(time() + intval($arIBlock["FIELDS"]["ACTIVE_TO"]["DEFAULT_VALUE"])*24*60*60, "FULL");

	$str_PREVIEW_TEXT_TYPE = $arIBlock["FIELDS"]["PREVIEW_TEXT_TYPE"]["DEFAULT_VALUE"] !== "html"? "text": "html";
	$str_PREVIEW_TEXT = htmlspecialchars($arIBlock["FIELDS"]["PREVIEW_TEXT"]["DEFAULT_VALUE"]);
	$str_DETAIL_TEXT_TYPE = $arIBlock["FIELDS"]["DETAIL_TEXT_TYPE"]["DEFAULT_VALUE"] !== "html"? "text": "html";
	$str_DETAIL_TEXT = htmlspecialchars($arIBlock["FIELDS"]["DETAIL_TEXT"]["DEFAULT_VALUE"]);

	$result = CIBlockElement::GetByID($WF_ID);

	if($arElement = $result->ExtractFields("str_"))
	{
		if($str_IN_SECTIONS=="N")
			$str_IBLOCK_ELEMENT_SECTION[] = 0;
		else
		{
			$result = CIBlockElement::GetElementGroups($WF_ID);
			while($ar = $result->Fetch())
				$str_IBLOCK_ELEMENT_SECTION[] = $ar["ID"];
		}
	}
	else
	{
		$WF_ID=0;
		$ID=0;
		if($IBLOCK_SECTION_ID>0)
			$str_IBLOCK_ELEMENT_SECTION[] = $IBLOCK_SECTION_ID;
	}

	if($ID > 0 && !$bCopy)
	{
		if($view=="Y")
			$APPLICATION->SetTitle($arIBlock["NAME"].": ".$arIBlock["ELEMENT_NAME"].": ".$arElement["NAME"]." - ".GetMessage("IBLOCK_ELEMENT_EDIT_VIEW"));
		else
			$APPLICATION->SetTitle($arIBlock["NAME"].": ".$arIBlock["ELEMENT_NAME"].": ".$arElement["NAME"]." - ".GetMessage("IBLOCK_EDIT_TITLE"));
	}
	else
	{
		$APPLICATION->SetTitle($arIBlock["NAME"].": ".$arIBlock["ELEMENT_NAME"].": ".GetMessage("IBLOCK_NEW_TITLE"));
	}

	//$adminChain->AddItem(array("TEXT"=>htmlspecialcharsex($arIBTYPE["NAME"]), "LINK"=>"iblock_admin.php?type=".htmlspecialchars($type)."&lang=".LANG

	$last_nav = '';
	if($arIBTYPE["SECTIONS"]=="Y")
	{
		$adminChain->AddItem(array("TEXT"=>htmlspecialcharsex($arIBlock["NAME"]), "LINK"=>$urlSectionAdminPage.'?type='.htmlspecialchars($type).'&amp;lang='.LANG.'&amp;IBLOCK_ID='.$IBLOCK_ID.'&amp;find_section_section=0'));
		if(intval($find_section_section)>0)
		{
			$nav = CIBlockSection::GetNavChain($IBLOCK_ID, IntVal($find_section_section));
			while($nav->ExtractFields("nav_"))
			{
				$last_nav = $urlSectionAdminPage."?lang=".LANG."&type=".$type."&IBLOCK_ID=".$IBLOCK_ID."&find_section_section=".$nav_ID;
				$adminChain->AddItem(array("TEXT"=>$nav_NAME, "LINK"=>$last_nav));
			}
		}
	}
	else
	{
		$adminChain->AddItem(array("TEXT"=>htmlspecialcharsex($arIBlock["NAME"]), "LINK"=>$urlElementAdminPage."?type=".htmlspecialchars($type)."&lang=".LANG."&IBLOCK_ID=".$IBLOCK_ID."&find_section_section=".intval($find_section_section)));
	}

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	if($bVarsFromForm)
	{
		$DB->InitTableVarsForEdit("b_iblock_element", "", "str_");
		$str_IBLOCK_ELEMENT_SECTION = $IBLOCK_SECTION;
	}

	$arPROP_tmp = Array();
	$properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$IBLOCK_ID));
	while($prop_fields = $properties->Fetch())
	{
		$prop_values = Array();
		$prop_values_with_descr = Array();
		if($bVarsFromForm)
		{
			if($prop_fields["PROPERTY_TYPE"]=="F")
			{
				$db_prop_values = CIBlockElement::GetProperty($IBLOCK_ID, $WF_ID, "id", "asc", Array("ID"=>$prop_fields["ID"], "EMPTY"=>"N"));
				while($res = $db_prop_values->Fetch())
				{
					$prop_values[$res["PROPERTY_VALUE_ID"]] = $res["VALUE"];
					$prop_values_with_descr[$res["PROPERTY_VALUE_ID"]] = array("VALUE"=>$res["VALUE"],"DESCRIPTION"=>$res["DESCRIPTION"]);
				}
			}
			else
			{
				if(is_set($PROP, $prop_fields["ID"]))
					$prop_values = $PROP[$prop_fields["ID"]];
				else
					$prop_values = $PROP[$prop_fields["CODE"]];
				$prop_values_with_descr = $prop_values;
			}
		}
		else
		{
			if($ID>0)
			{
				$db_prop_values = CIBlockElement::GetProperty($IBLOCK_ID, $WF_ID, "id", "asc", Array("ID"=>$prop_fields["ID"], "EMPTY"=>"N"));
				while($res = $db_prop_values->Fetch())
				{
					if($res["WITH_DESCRIPTION"]=="Y")
						$prop_values[$res["PROPERTY_VALUE_ID"]] = Array("VALUE"=>$res["VALUE"], "DESCRIPTION"=>$res["DESCRIPTION"]);
					else
						$prop_values[$res["PROPERTY_VALUE_ID"]] = $res["VALUE"];
					$prop_values_with_descr[$res["PROPERTY_VALUE_ID"]] = Array("VALUE"=>$res["VALUE"], "DESCRIPTION"=>$res["DESCRIPTION"]);
				}
			}
		}
		$prop_fields["VALUE"] = $prop_values;
		$prop_fields["~VALUE"] = $prop_values_with_descr;
		if(strlen(trim($prop_fields["CODE"]))>0)
			$arPROP_tmp[$prop_fields["CODE"]] = $prop_fields;
		else
			$arPROP_tmp[$prop_fields["ID"]] = $prop_fields;
	}
	$PROP = $arPROP_tmp;

	$aMenu = array(
		array(
			"TEXT"=>$arIBlock["ELEMENTS_NAME"],
			"LINK"=>$urlElementAdminPage."?type=".htmlspecialchars($type)."&lang=".LANG."&IBLOCK_ID=".$IBLOCK_ID."&find_section_section=".intval($find_section_section),
			"ICON"=>"btn_list",
		)
	);

	if($ID > 0 && !$bCopy)
	{
		$aMenu[] = array("SEPARATOR"=>"Y");
		$aMenu[] = array(
			"TEXT"=>$arIBlock["ELEMENT_ADD"],
			"LINK"=>"iblock_element_edit.php?type=".htmlspecialchars($type)."&lang=".LANG."&IBLOCK_ID=".$IBLOCK_ID."&find_section_section=".intval($find_section_section),
			"ICON"=>"btn_new",
		);
		$aMenu[] = array(
			"TEXT"=>GetMessage("IBEL_E_COPY_ELEMENT"),
			"TITLE"=>GetMessage("IBEL_E_COPY_ELEMENT_TITLE"),
			"LINK"=>"iblock_element_edit.php?type=".htmlspecialchars($type)."&lang=".LANG."&IBLOCK_ID=".$IBLOCK_ID."&find_section_section=".intval($find_section_section)."&action=copy&ID=".$ID,
			"ICON"=>"btn_copy",
		);

		if($BlockPerm >="W")
		{
			$aMenu[] = array(
				"TEXT"=>$arIBlock["ELEMENT_DELETE"],
				"LINK"=>"javascript:if(confirm('".GetMessage("IBLOCK_ELEMENT_DEL_CONF")."'))window.location='".$urlElementAdminPage."?ID=".(COption::GetOptionString("iblock","combined_list_mode")=="Y"?"E":"").IntVal($ID)."&action=delete&".bitrix_sessid_get()."&lang=".LANG."&type=".htmlspecialchars($type)."&IBLOCK_ID=".$IBLOCK_ID."&find_section_section=".intval($find_section_section)."';",
				"ICON"=>"btn_delete",
			);
		}
	}

	if($from=="iblock_section_admin" && strlen($last_nav)>0)
	{
		$aMenu[] = array(
			"TEXT"=>GetMessage("IBEL_E_BACK_TO_SECT"),
			"LINK"=>$last_nav,
		);
	}

	if(!$bCustomForm)
	{
		$link = DeleteParam(array("mode"));
		$link = $GLOBALS["APPLICATION"]->GetCurPage()."?mode=settings".($link <> ""? "&".$link:"");
		$aMenu[] = array(
			"TEXT"=>GetMessage("admin_lib_context_sett"),
			"TITLE"=>GetMessage("admin_lib_context_sett_title"),
			"LINK"=>"javascript:".$tabControl->GetName().".ShowSettings('".htmlspecialchars(CUtil::addslashes($link))."')",
			"ICON"=>"btn_settings",
		);
	}
	$context = new CAdminContextMenu($aMenu);
	$context->Show();

	if($error)
		CAdminMessage::ShowOldStyleError($error->err_text);

//START of the custom form
if(strlen($arIBlock["EDIT_FILE_AFTER"])>0 && is_file($_SERVER["DOCUMENT_ROOT"].$arIBlock["EDIT_FILE_AFTER"])):
	include($_SERVER["DOCUMENT_ROOT"].$arIBlock["EDIT_FILE_AFTER"]);
	$_SESSION['IBLOCK_CUSTOM_FORM']	= true;
elseif(strlen($arIBTYPE["EDIT_FILE_AFTER"])>0 && is_file($_SERVER["DOCUMENT_ROOT"].$arIBTYPE["EDIT_FILE_AFTER"])):
	include($_SERVER["DOCUMENT_ROOT"].$arIBTYPE["EDIT_FILE_AFTER"]);
	$_SESSION['IBLOCK_CUSTOM_FORM']	= true;
else:
	//We have to explicitly call calendar and editor functions because
	//first output may be discarded by form settings
	$tabControl->BeginPrologContent();
	echo CAdminCalendar::ShowScript();
	if(COption::GetOptionString("iblock", "use_htmledit", "Y")=="Y" && CModule::IncludeModule("fileman"))
	{
		//TODO:This dirty hack will be replaced by special method like calendar do
		echo '<div style="display:none">';
		CFileMan::AddHTMLEditorFrame(
			"SOME_TEXT",
			"",
			"SOME_TEXT_TYPE",
			"text",
			array(
				'height' => 450,
				'width' => '100%'
			),
			"N",
			0,
			"",
			"",
			$arIBlock["LID"]
		);
		echo '</div>';
	}
	$tabControl->EndPrologContent();

	$tabControl->BeginEpilogContent();
?>

<script language="JavaScript">
<!--
function addNewRow(tableID)
{
	var tbl = document.getElementById(tableID);
	var cnt = tbl.rows.length;
	var oRow = tbl.insertRow(cnt-1);
	var oCell = oRow.insertCell(0);
	var sHTML=tbl.rows[cnt-2].cells[0].innerHTML;
	var p = 0;
	while(true)
	{
		var s = sHTML.indexOf('[n',p);
		if(s<0)break;
		var e = sHTML.indexOf(']',s);
		if(e<0)break;
		var n = parseInt(sHTML.substr(s+2,e-s));
		sHTML = sHTML.substr(0, s)+'[n'+(++n)+']'+sHTML.substr(e+1);
		p=s+1;
	}
	var p = 0;
	while(true)
	{
		var s = sHTML.indexOf('__n',p);
		if(s<0)break;
		var e = sHTML.indexOf('__',s+2);
		if(e<0)break;
		var n = parseInt(sHTML.substr(s+3,e-s));
		sHTML = sHTML.substr(0, s)+'__n'+(++n)+'__'+sHTML.substr(e+2);
		p=e+2;
	}
	var p = 0;
	while(true)
	{
		var s = sHTML.indexOf('xxn',p);
		if(s<0)break;
		var e = sHTML.indexOf('xx',s+2);
		if(e<0)break;
		var n = parseInt(sHTML.substr(s+3,e-s));
		sHTML = sHTML.substr(0, s)+'xxn'+(++n)+'xx'+sHTML.substr(e+2);
		p=e+2;
	}
	oCell.innerHTML = sHTML;

	var s = sHTML;
	var code = [];
	var start, end;
	while((start = s.indexOf('<'+'script'+'>')) != -1)
	{
		var end = s.indexOf('<'+'/'+'script'+'>', start);
		if(end == -1)
			break;
		code[code.length] = s.substr(start+8, end-start-8);
		s = s.substr(0, start) + s.substr(end+9);
	}
	for(var i = 0, cnt = code.length; i < cnt; i++)
		if(code[i] != '')
			jsUtils.EvalGlobal(code[i]);
}
//-->
</script>

<?=bitrix_sessid_post()?>
<?echo GetFilterHiddens("find_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="from" value="<?echo htmlspecialchars($from)?>">
<input type="hidden" name="WF" value="<?echo htmlspecialchars($WF)?>">
<input type="hidden" name="return_url" value="<?echo $return_url?>">
<?if($ID>0 && !$bCopy):?>
	<input type="hidden" name="ID" value="<?echo $ID?>">
<?endif;?>
<input type="hidden" name="IBLOCK_SECTION_ID" value="<?echo IntVal($IBLOCK_SECTION_ID)?>">

<?
$tabControl->EndEpilogContent();

$customTabber->SetErrorState($bVarsFromForm);
$tabControl->AddTabs($customTabber);

$tabControl->Begin(array(
	"FORM_ACTION" => "/bitrix/admin/iblock_element_edit.php?type=".urlencode($type)."&lang=".LANG."&IBLOCK_ID=".$IBLOCK_ID."&find_section_section=".intval($find_section_section),
));

$tabControl->BeginNextFormTab();
?>
	<?
	if($ID > 0 && !$bCopy):
		$p = CIblockElement::GetByID($ID);
		$pr = $p->ExtractFields("prn_");
	endif;
$tabControl->AddCheckBoxField("ACTIVE", GetMessage("IBLOCK_ACTIVE"), false, "Y", $str_ACTIVE=="Y");
$tabControl->BeginCustomField("ACTIVE_FROM", GetMessage("IBLOCK_FIELD_ACTIVE_PERIOD_FROM"), $arIBlock["FIELDS"]["ACTIVE_FROM"]["IS_REQUIRED"] === "Y");
?>
	<tr>
		<td><?echo $tabControl->GetCustomLabelHTML()?>:<br>(<?echo CLang::GetDateFormat("SHORT");?> / <?echo CLang::GetDateFormat("FULL");?>)</td>
		<td><?echo CAdminCalendar::CalendarDate("ACTIVE_FROM", $str_ACTIVE_FROM, 19, true)?></td>
	</tr>
<?
$tabControl->EndCustomField("ACTIVE_FROM", '<input type="hidden" id="ACTIVE_FROM" name="ACTIVE_FROM" value="'.$str_ACTIVE_FROM.'">');
$tabControl->BeginCustomField("ACTIVE_TO", GetMessage("IBLOCK_FIELD_ACTIVE_PERIOD_TO"), $arIBlock["FIELDS"]["ACTIVE_TO"]["IS_REQUIRED"] === "Y");
?>
	<tr>
		<td><?echo $tabControl->GetCustomLabelHTML()?>:<br>(<?echo CLang::GetDateFormat("SHORT");?> / <?echo CLang::GetDateFormat("FULL");?>)</td>
		<td><?echo CAdminCalendar::CalendarDate("ACTIVE_TO", $str_ACTIVE_TO, 19, true)?></td>
	</tr>
<?
$tabControl->EndCustomField("ACTIVE_TO", '<input type="hidden" id="ACTIVE_TO" name="ACTIVE_TO" value="'.$str_ACTIVE_TO.'">');
$tabControl->AddEditField("NAME", GetMessage("IBLOCK_NAME"), true, array("size" => 50, "maxlength" => 255), $str_NAME);

if(count($PROP)>0):
	$tabControl->AddSection("IBLOCK_ELEMENT_PROP_VALUE", GetMessage("IBLOCK_ELEMENT_PROP_VALUE"));
	foreach($PROP as $prop_code=>$prop_fields):
		$prop_values = $prop_fields["VALUE"];
		$tabControl->BeginCustomField("PROPERTY_".$prop_fields["ID"], $prop_fields["NAME"], $prop_fields["IS_REQUIRED"]==="Y");
		?>
		<tr>
			<td valign="top"><?echo $tabControl->GetCustomLabelHTML();?>:</td>
			<td><?_ShowPropertyField('PROP['.$prop_fields["ID"].']', $prop_fields, $prop_fields["VALUE"], ((!$bVarsFromForm) && ($ID<=0)), $bVarsFromForm, 50000, $tabControl->GetFormName());?></td>
		</tr>
		<?
			$hidden = "";
			if(!is_array($prop_fields["~VALUE"]))
				$values = Array();
			else
				$values = $prop_fields["~VALUE"];
			$start = 1;
			foreach($values as $key=>$val)
			{
				if($bCopy)
				{
					$key = "n".$start;
					$start++;
				}
				if(!is_array($val) || !array_key_exists("VALUE",$val))
					$val = array("VALUE"=>$val, "DESCRIPTION"=>"");
				if(is_array($val["VALUE"]))
				{
					foreach($val["VALUE"] as $k=>$v)
						$hidden .= '<input type="hidden" name="PROP['.$prop_fields["ID"].']['.$key.'][VALUE]['.htmlspecialchars($k).']" value="'.htmlspecialchars($v).'">';
				}
				else
				{
					$hidden .= '<input type="hidden" name="PROP['.$prop_fields["ID"].']['.$key.'][VALUE]" value="'.htmlspecialchars($val["VALUE"]).'">';
				}
				$hidden .= '<input type="hidden" name="PROP['.$prop_fields["ID"].']['.$key.'][DESCRIPTION]" value="'.htmlspecialchars($val["DESCRIPTION"]).'">';
			}
		$tabControl->EndCustomField("PROPERTY_".$prop_fields["ID"], $hidden);
		endforeach;?>
	<?endif?>

	<?
	if ($view!="Y" && CModule::IncludeModule("catalog") && CCatalog::GetByID($IBLOCK_ID))
	{
		$tabControl->BeginCustomField("CATALOG", GetMessage("IBLOCK_TCATALOG"), true);
		include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/admin/templates/product_edit.php");
		$tabControl->EndCustomField("CATALOG", "");
	}
	$rsLinkedProps = CIBlockProperty::GetList(array(), array(
		"PROPERTY_TYPE" => "E",
		"LINK_IBLOCK_ID" => $IBLOCK_ID,
		"ACTIVE" => "Y",
		"FILTRABLE" => "Y",
	));
	$arLinkedProp = $rsLinkedProps->GetNext();
	if($arLinkedProp)
	{
		$tabControl->BeginCustomField("LINKED_PROP", GetMessage("IBLOCK_ELEMENT_EDIT_LINKED"));
		?>
		<tr class="heading">
			<td colspan="2"><?echo $tabControl->GetCustomLabelHTML();?></td>
		</tr>
		<?
		do {
			$elements_name = CIBlock::GetArrayByID($arLinkedProp["IBLOCK_ID"], "ELEMENTS_NAME");
			if(strlen($elements_name) <= 0)
				$elements_name = GetMessage("IBLOCK_ELEMENT_EDIT_ELEMENTS");
		?>
		<tr>
			<td colspan="2"><a href="<?echo $urlElementAdminPage?>?type=<?echo CIBlock::GetArrayByID($arLinkedProp["IBLOCK_ID"], "IBLOCK_TYPE_ID")?>&amp;IBLOCK_ID=<?echo urlencode($arLinkedProp["IBLOCK_ID"])?>&amp;lang=<?echo LANG?>&amp;set_filter=Y&amp;find_el_property_<?echo $arLinkedProp["ID"]?>=<?echo $ID?>"><?echo CIBlock::GetArrayByID($arLinkedProp["IBLOCK_ID"], "NAME").": ".$elements_name?></a></td>
		</tr>
		<?
		} while ($arLinkedProp = $rsLinkedProps->GetNext());
		$tabControl->EndCustomField("LINKED_PROP", "");
	}
	?>
<?

$tabControl->BeginNextFormTab();
$tabControl->BeginCustomField("PREVIEW_PICTURE", GetMessage("IBLOCK_FIELD_PREVIEW_PICTURE"), $arIBlock["FIELDS"]["PREVIEW_PICTURE"]["IS_REQUIRED"] === "Y");
if($bVarsFromForm && !array_key_exists("PREVIEW_PICTURE", $_REQUEST) && $arElement)
	$str_PREVIEW_PICTURE = intval($arElement["PREVIEW_PICTURE"]);
?>
	<tr>
		<td nowrap valign="top" width="40%"><?echo $tabControl->GetCustomLabelHTML()?></td>
		<td width="60%">
			<?if($ID > 0 && !$bCopy):?>
				<?echo CFile::InputFile("PREVIEW_PICTURE", 20, $str_PREVIEW_PICTURE, false, 0, "IMAGE", "", 40);?><br>
				<?echo CFile::ShowImage($str_PREVIEW_PICTURE, 200, 200, "border=0", "", true)?>
			<?else:?>
				<?echo CFile::InputFile("PREVIEW_PICTURE", 20, "", false, 0, "IMAGE", "", 40);?><br>
				<?echo CFile::ShowImage("", 200, 200, "border=0", "", true)?>
			<?endif?>
		</td>
	</tr>
<?
$tabControl->EndCustomField("PREVIEW_PICTURE", "");
$tabControl->BeginCustomField("PREVIEW_TEXT", GetMessage("IBLOCK_FIELD_PREVIEW_TEXT"), $arIBlock["FIELDS"]["PREVIEW_TEXT"]["IS_REQUIRED"] === "Y");
?>
	<tr class="heading">
		<td colspan="2"><?echo $tabControl->GetCustomLabelHTML()?></td>
	</tr>
	<?if($ID && $PREV_ID && $bWorkflow):?>
	<tr>
		<td colspan="2">
			<div style="width:95%;background-color:white;border:1px solid black;padding:5px">
				<?echo getDiff($prev_arElement["PREVIEW_TEXT"], $arElement["PREVIEW_TEXT"])?>
			</div>
		</td>
	</tr>
	<?elseif(COption::GetOptionString("iblock", "use_htmledit", "Y")=="Y" && CModule::IncludeModule("fileman")):?>
	<tr>
		<td colspan="2" align="center">
			<?CFileMan::AddHTMLEditorFrame(
			"PREVIEW_TEXT",
			$str_PREVIEW_TEXT,
			"PREVIEW_TEXT_TYPE",
			$str_PREVIEW_TEXT_TYPE,
			//300,
			array(
					'height' => 450,
					'width' => '100%'
				),
			"N",
			0,
			"",
			"",
			$arIBlock["LID"]
			);?>
		</td>
	</tr>
	<?else:?>
	<tr>
		<td><?echo GetMessage("IBLOCK_DESC_TYPE")?></td>
		<td><input type="radio" name="PREVIEW_TEXT_TYPE" id="PREVIEW_TEXT_TYPE_text" value="text"<?if($str_PREVIEW_TEXT_TYPE!="html")echo " checked"?>> <label for="PREVIEW_TEXT_TYPE_text"><?echo GetMessage("IBLOCK_DESC_TYPE_TEXT")?></label> / <input type="radio" name="PREVIEW_TEXT_TYPE" id="PREVIEW_TEXT_TYPE_html" value="html"<?if($str_PREVIEW_TEXT_TYPE=="html")echo " checked"?>> <label for="PREVIEW_TEXT_TYPE_html"><?echo GetMessage("IBLOCK_DESC_TYPE_HTML")?></label></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<textarea cols="60" rows="10" name="PREVIEW_TEXT" style="width:100%"><?echo $str_PREVIEW_TEXT?></textarea>
		</td>
	</tr>
	<?endif;
$tabControl->EndCustomField("PREVIEW_TEXT",
	'<input type="hidden" name="PREVIEW_TEXT" value="'.$str_PREVIEW_TEXT.'">'.
	'<input type="hidden" name="PREVIEW_TEXT_TYPE" value="'.$str_PREVIEW_TEXT_TYPE.'">'
);
$tabControl->BeginNextFormTab();
$tabControl->BeginCustomField("DETAIL_PICTURE", GetMessage("IBLOCK_FIELD_DETAIL_PICTURE"), $arIBlock["FIELDS"]["DETAIL_PICTURE"]["IS_REQUIRED"] === "Y");
if($bVarsFromForm && !array_key_exists("DETAIL_PICTURE", $_REQUEST) && $arElement)
	$str_DETAIL_PICTURE = intval($arElement["DETAIL_PICTURE"]);
?>
	<tr>
		<td valign="top" width="40%"><?echo $tabControl->GetCustomLabelHTML()?></td>
		<td width="60%">
			<?if($ID > 0 && !$bCopy):?>
				<?echo CFile::InputFile("DETAIL_PICTURE", 20, $str_DETAIL_PICTURE, false, 0, "IMAGE", "", 40);?><br>
				<?echo CFile::ShowImage($str_DETAIL_PICTURE, 200, 200, "border=0", "", true)?>
			<?else:?>
				<?echo CFile::InputFile("DETAIL_PICTURE", 20, "", false, 0, "IMAGE", "", 40);?><br>
				<?echo CFile::ShowImage("", 200, 200, "border=0", "", true)?>
			<?endif?>
		</td>
	</tr>
<?
$tabControl->EndCustomField("DETAIL_PICTURE", "");
$tabControl->BeginCustomField("DETAIL_TEXT", GetMessage("IBLOCK_FIELD_DETAIL_TEXT"), $arIBlock["FIELDS"]["DETAIL_TEXT"]["IS_REQUIRED"] === "Y");
?>
	<tr class="heading">
		<td colspan="2"><?echo $tabControl->GetCustomLabelHTML()?></td>
	</tr>
	<?if($ID && $PREV_ID && $bWorkflow):?>
	<tr>
		<td colspan="2">
			<div style="width:95%;background-color:white;border:1px solid black;padding:5px">
				<?echo getDiff($prev_arElement["DETAIL_TEXT"], $arElement["DETAIL_TEXT"])?>
			</div>
		</td>
	</tr>
	<?elseif(COption::GetOptionString("iblock", "use_htmledit", "Y")=="Y" && CModule::IncludeModule("fileman")):?>
	<tr>
		<td colspan="2" align="center">
			<?CFileMan::AddHTMLEditorFrame(
				"DETAIL_TEXT",
				$str_DETAIL_TEXT,
				"DETAIL_TEXT_TYPE",
				$str_DETAIL_TEXT_TYPE,
				array(
						'height' => 450,
						'width' => '100%'
					),
					"N",
					0,
					"",
					"",
					$arIBlock["LID"]);
		?></td>
	</tr>
	<?else:?>
	<tr>
		<td><?echo GetMessage("IBLOCK_DESC_TYPE")?></td>
		<td><input type="radio" name="DETAIL_TEXT_TYPE" id="DETAIL_TEXT_TYPE_text" value="text"<?if($str_DETAIL_TEXT_TYPE!="html")echo " checked"?>> <label for="DETAIL_TEXT_TYPE_text"><?echo GetMessage("IBLOCK_DESC_TYPE_TEXT")?></label> / <input type="radio" name="DETAIL_TEXT_TYPE" id="DETAIL_TEXT_TYPE_html" value="html"<?if($str_DETAIL_TEXT_TYPE=="html")echo " checked"?>> <label for="DETAIL_TEXT_TYPE_html"><?echo GetMessage("IBLOCK_DESC_TYPE_HTML")?></label></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<textarea cols="60" rows="20" name="DETAIL_TEXT" style="width:100%"><?echo $str_DETAIL_TEXT?></textarea>
		</td>
	</tr>
	<?endif?>
<?
$tabControl->EndCustomField("DETAIL_TEXT",
	'<input type="hidden" name="DETAIL_TEXT" value="'.$str_DETAIL_TEXT.'">'.
	'<input type="hidden" name="DETAIL_TEXT_TYPE" value="'.$str_DETAIL_TEXT_TYPE.'">'
);
?>

<?if($bTab2):
	$tabControl->BeginNextFormTab();
	$tabControl->BeginCustomField("SECTIONS", GetMessage("IBLOCK_SECTION"), $arIBlock["FIELDS"]["IBLOCK_SECTION"]["IS_REQUIRED"] === "Y");
	?>
	<tr>
	<?if($arIBlock["SECTION_CHOOSER"] != "D" && $arIBlock["SECTION_CHOOSER"] != "P"):?>

		<?$l = CIBlockSection::GetTreeList(Array("IBLOCK_ID"=>$IBLOCK_ID));?>
		<td valign="top" width="40%"><?echo $tabControl->GetCustomLabelHTML()?></td>
		<td width="60%">
		<select name="IBLOCK_SECTION[]" size="14" multiple>
			<option value="0"<?if(is_array($str_IBLOCK_ELEMENT_SECTION) && in_array(0, $str_IBLOCK_ELEMENT_SECTION))echo " selected"?>><?echo GetMessage("IBLOCK_CONTENT")?></option>
		<?
			while($l->ExtractFields("l_")):
				?><option value="<?echo $l_ID?>"<?if(is_array($str_IBLOCK_ELEMENT_SECTION) && in_array($l_ID, $str_IBLOCK_ELEMENT_SECTION))echo " selected"?>><?echo str_repeat(" . ", $l_DEPTH_LEVEL)?><?echo $l_NAME?></option><?
			endwhile;
		?>
		</select>
		</td>

	<?elseif($arIBlock["SECTION_CHOOSER"] == "D"):?>

		<td>
			<table id="sections">
			<?
			if(is_array($str_IBLOCK_ELEMENT_SECTION))
			{
				$i = 0;
				foreach($str_IBLOCK_ELEMENT_SECTION as $section_id)
				{
					$rsChain = CIBlockSection::GetNavChain($IBLOCK_ID, $section_id);
					$strPath = "";
					while($arChain = $rsChain->Fetch())
						$strPath .= $arChain["NAME"]."&nbsp;/&nbsp;";
					if(strlen($strPath) > 0)
					{
						?><tr>
							<td><?echo $strPath?></td>
							<td>
							<input type="button" value="<?echo GetMessage("IBLOCK_DELETE")?>" OnClick="deleteRow(this)">
							<input type="hidden" name="IBLOCK_SECTION[]" value="<?echo intval($section_id)?>">
							</td>
						</tr><?
					}
					$i++;
				}
			}
			?>
			<tr>
				<td>
				<script>
				function deleteRow(button)
				{
					var my_row = button.parentNode.parentNode;
					var table = document.getElementById('sections');
					if(table)
					{
						for(var i=0; i<table.rows.length; i++)
						{
							if(table.rows[i] == my_row)
							{
								table.deleteRow(i);
							}
						}
					}
				}
				function addPathRow()
				{
					var table = document.getElementById('sections');
					if(table)
					{
						var section_id = 0;
						var html = '';
						var lev = 0;
						var oSelect;
						while(oSelect = document.getElementById('select_IBLOCK_SECTION_'+lev))
						{
							if(oSelect.value < 1)
								break;
							html += oSelect.options[oSelect.selectedIndex].text+'&nbsp;/&nbsp;';
							section_id = oSelect.value;
							lev++;
						}
						if(section_id > 0)
						{
							var cnt = table.rows.length;
							var oRow = table.insertRow(cnt-1);

							var i=0;
							var oCell = oRow.insertCell(i++);
							oCell.innerHTML = html;

							var oCell = oRow.insertCell(i++);
							oCell.innerHTML =
								'<input type="button" value="<?echo GetMessage("IBLOCK_DELETE")?>" OnClick="deleteRow(this)">'+
								'<input type="hidden" name="IBLOCK_SECTION[]" value="'+section_id+'">';
						}
					}
				}
				function find_path(item, value)
				{
					if(item.id==value)
					{
						var a = Array(1);
						a[0] = item.id;
						return a;
					}
					else
					{
						for(var s in item.children)
						{
							if(ar = find_path(item.children[s], value))
							{
								var a = Array(1);
								a[0] = item.id;
								return a.concat(ar);
							}
						}
						return null;
					}
				}
				function find_children(level, value, item)
				{
					if(level==-1 && item.id==value)
						return item;
					else
					{
						for(var s in item.children)
						{
							if(ch = find_children(level-1,value,item.children[s]))
								return ch;
						}
						return null;
					}
				}
				function change_selection(name_prefix, prop_id,value,level,id)
				{
					//alert(prop_id+','+value+','+level);
					var lev = level+1;
					var oSelect;
					while(oSelect = document.getElementById(name_prefix+lev))
					{
						for(var i=oSelect.length-1;i>-1;i--)
							oSelect.remove(i);
						var newoption = new Option('(<?echo GetMessage("MAIN_NO")?>)', '0', false, false);
						oSelect.options[0]=newoption;
						lev++;
					}
					oSelect = document.getElementById(name_prefix+(level+1))
					if(oSelect && (value!=0||level==-1))
					{
						var item = find_children(level,value,window['sectionListsFor'+prop_id]);
						var i=1;
						for(var s in item.children)
						{
							obj = item.children[s];
							var newoption = new Option(obj.name, obj.id, false, false);
							oSelect.options[i++]=newoption;
						}
					}
					if(document.getElementById(id))
						document.getElementById(id).value = value;
				}
				function init_selection(name_prefix, prop_id, value, id)
				{
					var a = find_path(window['sectionListsFor'+prop_id], value);
					//alert(a);
					change_selection(name_prefix, prop_id, 0, -1, id);
					for(var i=1;i<a.length;i++)
					{
						if(oSelect = document.getElementById(name_prefix+(i-1)))
						{
							for(var j=0;j<oSelect.length;j++)
							{
								if(oSelect[j].value==a[i])
								{
									oSelect[j].selected=true;
									break;
								}
							}
						}
						change_selection(name_prefix, prop_id, a[i], i-1, id);
					}
				}
				var sectionListsFor0 = {id:0,name:'',children:Array()};

				<?
				$rsItems = CIBlockSection::GetTreeList(Array("IBLOCK_ID"=>$IBLOCK_ID));
				$depth = 0;
				$max_depth = 0;
				$arChain = array();
				while($arItem = $rsItems->GetNext())
				{
					if($max_depth < $arItem["DEPTH_LEVEL"])
					{
						$max_depth = $arItem["DEPTH_LEVEL"];
					}
					if($depth < $arItem["DEPTH_LEVEL"])
					{
						$arChain[]=$arItem["ID"];

					}
					while($depth > $arItem["DEPTH_LEVEL"])
					{
						array_pop($arChain);
						$depth--;
					}
					$arChain[count($arChain)-1] = $arItem["ID"];
					echo "sectionListsFor0";
					foreach($arChain as $i)
						echo ".children['".intval($i)."']";

					echo " = { id : ".$arItem["ID"].", name : '".AddSlashes($arItem["NAME"])."', children : Array() };\n";
					$depth = $arItem["DEPTH_LEVEL"];
				}
				?>
				</script>
				<?
				for($i = 0; $i < $max_depth; $i++)
					echo '<select id="select_IBLOCK_SECTION_'.$i.'" onchange="change_selection(\'select_IBLOCK_SECTION_\',  0, this.value, '.$i.', \'IBLOCK_SECTION[n'.$key.']\')"><option value="0">('.GetMessage("MAIN_NO").')</option></select>&nbsp;';
				echo '<br><input type="button" value="'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_ADD").'" onClick="addPathRow()">';
				?>
				<script>
					init_selection('select_IBLOCK_SECTION_', 0, '', 0);
				</script>
				</td>
				<td>&nbsp;</td>
			</tr>
			</table>
		</td>

	<?else:?>

		<td>
			<table id="sections">
			<?
			if(is_array($str_IBLOCK_ELEMENT_SECTION))
			{
				$i = 0;
				foreach($str_IBLOCK_ELEMENT_SECTION as $section_id)
				{
					$rsChain = CIBlockSection::GetNavChain($IBLOCK_ID, $section_id);
					$strPath = "";
					while($arChain = $rsChain->Fetch())
						$strPath .= $arChain["NAME"]."&nbsp;/&nbsp;";
					if(strlen($strPath) > 0)
					{
						?><tr>
							<td><?echo $strPath?></td>
							<td>
							<input type="button" value="<?echo GetMessage("IBLOCK_DELETE")?>" OnClick="deleteRow(this)">
							<input type="hidden" name="IBLOCK_SECTION[]" value="<?echo intval($section_id)?>">
							</td>
						</tr><?
					}
					$i++;
				}
			}
			?>
			<tr>
				<td>
				<script>
				function deleteRow(button)
				{
					var my_row = button.parentNode.parentNode;
					var table = document.getElementById('sections');
					if(table)
					{
						for(var i=0; i<table.rows.length; i++)
						{
							if(table.rows[i] == my_row)
							{
								table.deleteRow(i);
							}
						}
					}
				}
				function InS<?echo md5("input_IBLOCK_SECTION")?>(section_id, html)
				{
					var table = document.getElementById('sections');
					if(table)
					{
						if(section_id > 0 && html)
						{
							var cnt = table.rows.length;
							var oRow = table.insertRow(cnt-1);

							var i=0;
							var oCell = oRow.insertCell(i++);
							oCell.innerHTML = html;

							var oCell = oRow.insertCell(i++);
							oCell.innerHTML =
								'<input type="button" value="<?echo GetMessage("IBLOCK_DELETE")?>" OnClick="deleteRow(this)">'+
								'<input type="hidden" name="IBLOCK_SECTION[]" value="'+section_id+'">';
						}
					}
				}
				</script>
				<input name="input_IBLOCK_SECTION" id="input_IBLOCK_SECTION" type="hidden">
				<input type="button" value="<?echo GetMessage("IBLOCK_ELEMENT_EDIT_PROP_ADD")?>..." onClick="jsUtils.OpenWindow('/bitrix/admin/iblock_section_search.php?lang=<?echo LANG?>&amp;IBLOCK_ID=<?echo $IBLOCK_ID?>&amp;n=input_IBLOCK_SECTION&amp;m=y', 600, 500);">
				</td>
				<td>&nbsp;</td>
			</tr>
			</table>
		</td>

	<?endif;?>
	</tr>
	<?
	$hidden = "";
	if(is_array($str_IBLOCK_ELEMENT_SECTION))
		foreach($str_IBLOCK_ELEMENT_SECTION as $section_id)
			$hidden .= '<input type="hidden" name="IBLOCK_SECTION[]" value="'.intval($section_id).'">';
	$tabControl->EndCustomField("SECTIONS", $hidden);
endif;

$tabControl->BeginNextFormTab();
$tabControl->AddEditField("SORT", GetMessage("IBLOCK_SORT"), $arIBlock["FIELDS"]["SORT"]["IS_REQUIRED"] === "Y", array("size" => 7, "maxlength" => 10), $str_SORT);

if(COption::GetOptionString("iblock", "show_xml_id", "N")=="Y")
	$tabControl->AddEditField("XML_ID", GetMessage("IBLOCK_EXTERNAL_CODE"), $arIBlock["FIELDS"]["XML_ID"]["IS_REQUIRED"] === "Y", array("size" => 20, "maxlength" => 255), $str_XML_ID);

$tabControl->AddEditField("CODE", GetMessage("IBLOCK_CODE"), $arIBlock["FIELDS"]["CODE"]["IS_REQUIRED"] === "Y", array("size" => 20, "maxlength" => 255), $str_CODE);
$tabControl->BeginCustomField("TAGS", GetMessage("IBLOCK_TAGS"), $arIBlock["FIELDS"]["TAGS"]["IS_REQUIRED"] === "Y");
?>
	<tr>
		<td><?echo $tabControl->GetCustomLabelHTML()?><br><?echo GetMessage("IBLOCK_ELEMENT_EDIT_TAGS_TIP")?></td>
		<td>
			<?if(CModule::IncludeModule('search')):
				$arLID = array();
				$rsSites = CIBlock::GetSite($IBLOCK_ID);
				while($arSite = $rsSites->Fetch())
					$arLID[] = $arSite["LID"];
				echo InputTags("TAGS", htmlspecialcharsback($str_TAGS), $arLID, 'size="55"');
			else:?>
				<input type="text" size="20" name="TAGS" maxlength="255" value="<?echo $str_TAGS?>">
			<?endif?>
		</td>
	</tr>
<?
$tabControl->EndCustomField("TAGS",
	'<input type="hidden" name="TAGS" value="'.$str_TAGS.'">'
);

if($bTab4):?>
<?
	$tabControl->BeginNextFormTab();
	$tabControl->BeginCustomField("WORKFLOW_PARAMS", GetMessage("IBLOCK_EL_TAB_WF_TITLE"));
	if(strlen($pr["DATE_CREATE"])>0):
	?>
		<tr>
			<td width="40%"><?echo GetMessage("IBLOCK_CREATED")?></td>
			<td width="60%"><?echo $pr["DATE_CREATE"]?><?
			if (intval($pr["CREATED_BY"])>0):
			?>&nbsp;&nbsp;&nbsp;[<a href="user_edit.php?lang=<?=LANG?>&amp;ID=<?=$pr["CREATED_BY"]?>"><?echo $pr["CREATED_BY"]?></a>]&nbsp;<?=htmlspecialcharsex($pr["CREATED_USER_NAME"])?><?
			endif;
			?></td>
		</tr>
	<?endif;?>
	<?if(strlen($str_TIMESTAMP_X) > 0 && !$bCopy):?>
	<tr>
		<td><?echo GetMessage("IBLOCK_LAST_UPDATE")?></td>
		<td><?echo $str_TIMESTAMP_X?><?
		if (intval($str_MODIFIED_BY)>0):
		?>&nbsp;&nbsp;&nbsp;[<a href="user_edit.php?lang=<?=LANG?>&amp;ID=<?=$str_MODIFIED_BY?>"><?echo $str_MODIFIED_BY?></a>]&nbsp;<?=$str_USER_NAME?><?
		endif;
		?></td>
	</tr>
	<?endif?>
	<?if($WF=="Y" && strlen($prn_WF_DATE_LOCK)>0):?>
	<tr>
		<td nowrap><?echo GetMessage("IBLOCK_DATE_LOCK")?></td>
		<td nowrap><?echo $prn_WF_DATE_LOCK?><?
		if (intval($prn_WF_LOCKED_BY)>0):
		?>&nbsp;&nbsp;&nbsp;[<a href="user_edit.php?lang=<?=LANG?>&amp;ID=<?=$prn_WF_LOCKED_BY?>"><?echo $prn_WF_LOCKED_BY?></a>]&nbsp;<?=$prn_LOCKED_USER_NAME?><?
		endif;
		?></td>
	</tr>
	<?endif;
	$tabControl->EndCustomField("WORKFLOW_PARAMS", "");
	if ($WF=="Y" || $view=="Y"):
	$tabControl->BeginCustomField("WF_STATUS_ID", GetMessage("IBLOCK_WF_STATUS"));
	?>
	<tr>
		<td><?echo $tabControl->GetCustomLabelHTML()?></td>
		<td nowrap>
			<?if($ID > 0 && !$bCopy):?>
				<?echo SelectBox("WF_STATUS_ID", CWorkflowStatus::GetDropDownList("N", "desc"), "", $str_WF_STATUS_ID);?>
			<?else:?>
				<?echo SelectBox("WF_STATUS_ID", CWorkflowStatus::GetDropDownList("N", "desc"), "", "");?>
			<?endif?>
		</td>
	</tr>
	<?
	if($ID > 0 && !$bCopy)
		$hidden = '<input type="hidden" name="WF_STATUS_ID" value="'.$str_WF_STATUS_ID.'">';
	else
		$hidden = '<input type="hidden" name="WF_STATUS_ID" value="">';
	$tabControl->EndCustomField("WF_STATUS_ID", $hidden);
	endif;
	$tabControl->BeginCustomField("WF_COMMENTS", GetMessage("IBLOCK_COMMENTS"));
	?>
	<tr class="heading">
		<td colspan="2"><b><?echo $tabControl->GetCustomLabelHTML()?></b></td>
	</tr>
	<tr>
		<td colspan="2">
			<?if($ID > 0 && !$bCopy):?>
				<textarea name="WF_COMMENTS" style="width:100%" rows="10"><?echo $str_WF_COMMENTS?></textarea>
			<?else:?>
				<textarea name="WF_COMMENTS" style="width:100%" rows="10"><?echo ""?></textarea>
			<?endif?>
		</td>
	</tr>
	<?
	$tabControl->EndCustomField("WF_COMMENTS", '<input type="hidden" name="WF_COMMENTS" value="'.$str_WF_COMMENTS.'">');
endif;
if (!defined('BX_PUBLIC_MODE') || BX_PUBLIC_MODE != 1):
	ob_start();
	?>
	<input <?if ($view=="Y" || $prn_LOCK_STATUS=="red") echo "disabled";?> type="submit" class="button" name="save" value="<?echo GetMessage("IBLOCK_EL_SAVE")?>">
	<input <?if ($view=="Y" || $prn_LOCK_STATUS=="red") echo "disabled";?> class="button" type="submit" name="apply" value="<?echo GetMessage('IBLOCK_APPLY')?>">
	<input <?if ($view=="Y" || $prn_LOCK_STATUS=="red") echo "disabled";?> type="submit" class="button" name="dontsave" value="<?echo GetMessage("IBLOCK_EL_CANC")?>">
	<?
	$buttons_add_html = ob_get_contents();
	ob_end_clean();
	$tabControl->Buttons(false, $buttons_add_html);
else:
	$tabControl->Buttons(array('disabled' => ($view=="Y" || $prn_LOCK_STATUS=="red")));
endif;

$tabControl->Show();

if((!defined('BX_PUBLIC_MODE') || BX_PUBLIC_MODE != 1) && $BlockPerm >= "X")
{
	echo
		BeginNote(),
		GetMessage("IBEL_E_IBLOCK_MANAGE_HINT"),
		' <a href="iblock_edit.php?type='.htmlspecialchars($type).'&amp;lang='.LANG.'&amp;ID='.$IBLOCK_ID.'&amp;admin=Y&amp;return_url='.urlencode("iblock_element_edit.php?ID=".$ID.($WF=="Y"?"&WF=Y":"")."&lang=".LANG. "&type=".htmlspecialchars($type)."&IBLOCK_ID=".$IBLOCK_ID."&find_section_section=".intval($find_section_section).(strlen($return_url)>0?"&return_url=".UrlEncode($return_url):"")).'">',
		GetMessage("IBEL_E_IBLOCK_MANAGE_HINT_HREF"),
		'</a>',
		EndNote()
	;
}

endif; //END of the custom form

}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
