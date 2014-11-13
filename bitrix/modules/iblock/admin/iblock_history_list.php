<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
*/

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/iblock.php");
IncludeModuleLangFile(__FILE__);

if(!CModule::IncludeModule("workflow")) die();

$arIBTYPE = CIBlockType::GetByIDLang($type, LANG);
if($arIBTYPE==false) die();

$IBLOCK_ID = intval($IBLOCK_ID);
$ELEMENT_ID = intval($ELEMENT_ID);

$iblock = CIBlock::GetByID($IBLOCK_ID);
if($arIBlock=$iblock->Fetch())
{
	$BlockPerm = CIBlock::GetPermission($IBLOCK_ID);
	if ($BlockPerm<"U")
	{
		$APPLICATION->SetTitle(str_replace("#ID#","$ELEMENT_ID",GetMessage("IBLOCK_HISTORY_PAGE_TITLE")));
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
		echo ShowError(GetMessage("IBLOCK_BAD_IBLOCK"));
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
		die();
	}
}


//$LAST_PUBLISHED_ID = CIblockElement::WF_GetLastPublished($ELEMENT_ID);
$LAST_ID = CIBlockElement::WF_GetLast($ELEMENT_ID);
$z = CIblockElement::GetByID($LAST_ID);
if(!$zr=$z->Fetch())
{
	$APPLICATION->SetTitle(str_replace("#ID#","$ELEMENT_ID",GetMessage("IBLOCK_HISTORY_PAGE_TITLE")));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	?><a href="/bitrix/admin/iblock_admin.php?type=<?echo htmlspecialchars($type)?>&amp;lang=<?echo LANG?>"><?echo htmlspecialcharsex($arIBTYPE["NAME"])?></a> - <a href="iblock_element_admin.php?type=<?echo htmlspecialchars($type)?>&amp;lang=<?echo LANG?>&amp;IBLOCK_ID=<?echo $IBLOCK_ID?>"><?echo htmlspecialchars($arIBlock["NAME"])?></a><?
	echo ShowError(GetMessage("IBLOCK_INCORRECT_ELEMENT_ID"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

// this is uniqu ajax id
$sTableID = "tbl_iblock_history";
// sort init
$oSort = new CAdminSorting($sTableID, "ID", "desc");
// list init
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = Array(
	"find_id",
	"find_timestamp_from",
	"find_timestamp_to",
	"find_modified_by",
	"find_modified_user_id",
	"find_name",
	"find_status",
	"find_status_id"
	);

$lAdmin->InitFilter($arFilterFields);

$arFilter = Array(
	"ID"				=> $find_id,
	"TIMESTAMP_FROM"	=> $find_timestamp_from,
	"TIMESTAMP_TO"		=> $find_timestamp_to,
	"MODIFIED_BY"		=> $find_modified_by,
	"MODIFIED_USER_ID"	=> $find_modified_user_id,
	"NAME"				=> $find_name,
	"STATUS"			=> $find_status,
	"STATUS_ID"			=> $find_status_id
	);

// action handlers
if(($arID = $lAdmin->GroupAction()))
{
	if($_REQUEST['action_target']=='selected')
	{
		$arID = Array();
		$rsData = CIBlockElement::WF_GetHistoryList($ELEMENT_ID, $by, $order, $arFilter, $is_filtered);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if(strlen($ID)<=0)
			continue;
	   	$ID = IntVal($ID);
		switch($_REQUEST['action'])
		{
		case "delete":
			$d = CIBlockElement::GetByID($ID);
			if($dr = $d->Fetch())
			{
				if(strlen($dr["WF_PARENT_ELEMENT_ID"])>0)
				{
					$DB->StartTransaction();
					if(!CIBlockElement::Delete(intval($ID)))
					{
						$lAdmin->AddGroupError(GetMessage("IBLOCK_DELETE_ERROR"), $ID);
						$DB->Rollback();
					}
					else
						$DB->Commit();
				}
			}
			break;
		}
	}
}


// dataset
$rsData = CIBlockElement::WF_GetHistoryList($ELEMENT_ID, $by, $order, $arFilter, $is_filtered);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// navigation
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("IBLOCK_HISTORY_PAGES")));


// list headers
$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", 	"sort"=>"s_id", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage("IBLOCK_ELEMENT_ADMIN_NAME"),  "sort"=>"s_name", "default"=>true),
	array("id"=>"WF_STATUS_ID",	"content"=>GetMessage("IBLOCK_STATUS"), "sort"=>"s_status",	"default"=>true),
	array("id"=>"MODIFIED_BY", "content"=>GetMessage("IBLOCK_MODIFIED_BY"),	"sort"=>"s_modified_by", "default"=>true),
	array("id"=>"TIMESTAMP_X","content"=>GetMessage("IBLOCK_ELEMENT_ADMIN_TIMESTAMP"), "sort"=>"s_timestamp_x", "default"=>true),
));

// list
while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	if($f_MODIFIED_BY>0)
		$row->AddViewField("MODIFIED_BY", '[<a href="user_edit.php?lang='.LANG.'&ID='.$f_MODIFIED_BY.'">'.$f_MODIFIED_BY.'</a>] '.$f_USER_NAME.'</a>');

	$row->AddViewField("WF_STATUS_ID", '[<a href="workflow_status_edit.php?ID='.$f_WF_STATUS_ID.'&lang='.LANG.'">'.$f_WF_STATUS_ID.'</a>] '.htmlspecialcharsex(CIBlockElement::WF_GetStatusTitle($f_WF_STATUS_ID)));

	$arActions = Array();
	$arActions[] = array(
		"ICON"=>"view",
		"DEFAULT"=>true,
		"TEXT"=>GetMessage("IBLOCK_VIEW_HISTORY"),
		"TITLE"=>GetMessage("IBLOCK_VIEW_HISTORY_ALT"),
		"ACTION"=>$lAdmin->ActionRedirect('iblock_element_edit.php?type='.$type.'&ID='.$f_ID.'&lang='.LANG.'&IBLOCK_ID='.$IBLOCK_ID.'&view=Y&find_section_section='.intval($find_section_section))
		);

	$arActions[] = array("SEPARATOR"=>true);
	$arActions[] = array(
		"ICON"=>"delete",
		"TEXT"=>GetMessage('IBLOCK_DELETE'),
		"TITLE"=>GetMessage("IBLOCK_DELETE_ALT"),
		"ACTION"=>"if(confirm('".GetMessage("IBLOCK_CONFIRM_DEL_HISTORY")."')) ".$lAdmin->ActionDoGroup($f_ID, "delete", 'type='.htmlspecialchars($type).'&ELEMENT_ID='.$ELEMENT_ID.'&IBLOCK_ID='.$IBLOCK_ID.'&find_section_section='.intval($find_section_section))
		);

	$row->AddActions($arActions);
}

// footer
$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);


// actions buttins
$lAdmin->AddGroupActionTable(Array(
	"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
		array(
			"action" => "Diff()",
			"value" => "compare",
			"type" => "button",
			"name" => GetMessage("FLOW_COMPARE"),
		),
	));


// context menu
$aContext = array(
	array(
		"TEXT"=>GetMessage("IBHIST_A_ORIG"),
		"LINK"=>"iblock_element_edit.php?WF=Y&ID=".$ELEMENT_ID."&type=".htmlspecialchars($type)."&lang=".LANG."&IBLOCK_ID=".$IBLOCK_ID."&find_section_section=".$find_section_section,
		"TITLE"=>GetMessage("IBHIST_A_ORIG_TITLE")
	),
);

$lAdmin->AddAdminContextMenu($aContext);

/////
$chain = $lAdmin->CreateChain();

if($arIBTYPE["SECTIONS"]=="Y")
{
	$chain->AddItem(array("TEXT"=>htmlspecialcharsex($arIBlock["NAME"]), "LINK"=>'iblock_section_admin.php?type='.htmlspecialchars($type).'&amp;lang='.LANG.'&amp;IBLOCK_ID='.$IBLOCK_ID.'&amp;find_section_section=0'));
	$sLastFolder = '';
	if(intval($find_section_section)>0)
	{
		$sLastFolder = 'iblock_section_admin.php?type='.htmlspecialchars($type).'&amp;lang='.LANG.'&amp;IBLOCK_ID='.$IBLOCK_ID.'&amp;find_section_section=0';
		$nav = CIBlockSection::GetNavChain($IBLOCK_ID, $find_section_section);
		while($nav->ExtractFields("nav_"))
			$chain->AddItem(array("TEXT"=>$nav_NAME, "LINK"=>$sLastFolder));
	}
}

$chain->AddItem(array("TEXT"=>htmlspecialcharsex($zr["NAME"]), "LINK"=>"iblock_element_edit.php?WF=Y&ID=".$ELEMENT_ID."&type=".htmlspecialchars($type)."&lang=".LANG."&IBLOCK_ID=".$IBLOCK_ID));

$lAdmin->ShowChain($chain);


$lAdmin->CheckListMode();

$APPLICATION->SetTitle(str_replace("#ID#","$ELEMENT_ID",GetMessage("IBLOCK_HISTORY_PAGE_TITLE")));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<script language="JavaScript">
<!--
function Diff()
{
	var selection = new Array();
	var j = 0;

	var l = document.form_tbl_iblock_history.elements['ID[]'].length;
	for(var i=0; i<l; i++)
	{
		var a = document.form_tbl_iblock_history.elements['ID[]'][i].checked;
		if (a == true)
		{
			selection[j] = document.form_tbl_iblock_history.elements['ID[]'][i].value;
			j++;
		}
	}
	if(j < 2 || j > 2)
	{
		alert('<?echo GetMessage("FLOW_COMPARE_ALERT")?>');
	}
	else
	{
		window.location='iblock_element_edit.php?type=<?echo urlencode($type)?>&lang=<?echo urlencode(LANG)?>&IBLOCK_ID=<?echo urlencode($IBLOCK_ID)?>&view=Y&find_section_section=<?echo $find_section_section?>&ID='+selection[0]+'&PREV_ID='+selection[1];
	}
}
//-->
</script>

<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("IBHIST_A_F_ID"),
		GetMessage("IBHIST_A_F_TS"),
		GetMessage("IBHIST_A_F_BY"),
		GetMessage("IBHIST_A_F_STATUS"),
	)
);

$oFilter->Begin();
?>
<tr>
	<td><font class="tableheadtext"><b><?=GetMessage("IBLOCK_F_NAME")?></b></td>
	<td><input type="text" name="find_name" value="<?echo (strlen($find_name)>0) ? htmlspecialchars($find_name) : ""?>" size="38"></td>
</tr>
<tr>
	<td><?=GetMessage("IBLOCK_F_ID")?></td>
	<td><input type="text" name="find_id" size="38" value="<?echo htmlspecialchars($find_id)?>"></td>
</tr>
<tr>
	<td><?echo GetMessage("IBLOCK_F_TIMESTAMP")." (".CLang::GetDateFormat("SHORT")."):"?></td>
	<td><?echo CalendarPeriod("find_timestamp_from", htmlspecialchars($find_timestamp_from), "find_timestamp_to", htmlspecialchars($find_timestamp_to), "find_form")?></font></td>
</tr>
<tr>
	<td><?=GetMessage("IBLOCK_F_MODIFIED_BY")?></td>
	<td><input type="text" name="find_modified_user_id" value="<?echo htmlspecialchars($find_modified_user_id)?>" size="3">&nbsp;<?
		$gr_res = CIBlock::GetGroupPermissions($IBLOCK_ID);
		$res = Array(1);
		foreach($gr_res as $gr=>$perm)
			if($perm>"R")
				$res[] = $gr;
		$res = CUser::GetList($byx="NAME", $orderx="ASC", Array("GROUP_MULTI"=>$res));
		?><select name="find_modified_by">
		<option value=""><?echo GetMessage("IBLOCK_VALUE_ANY")?></option><?
		while($arr = $res->Fetch())
			echo "<option value='".$arr["ID"]."'".($find_modified_by==$arr["ID"]?" selected":"").">(".htmlspecialchars($arr["LOGIN"].") ".$arr["NAME"]." ".$arr["LAST_NAME"])."</option>";
		?></select>
	</td>
</tr>
<tr>
	<td><?=GetMessage("IBLOCK_F_STATUS")?></td>
	<td><input type="text" name="find_status_id" value="<?echo htmlspecialchars($find_status_id)?>" size="3">&nbsp;<?
	echo SelectBox("find_status", CWorkflowStatus::GetDropDownList("Y"), GetMessage("IBLOCK_ALL"), htmlspecialchars($find_status));
	?></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage()."?type=".htmlspecialchars($type)."&IBLOCK_ID=".intval($IBLOCK_ID)."&ELEMENT_ID=".$ELEMENT_ID, "form"=>"find_form"));
$oFilter->End();
?>
</form>
<?echo ShowError($strError);
// here is list displayed
$lAdmin->DisplayList();
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
