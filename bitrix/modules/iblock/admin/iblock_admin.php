<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/iblock.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);

$urlSectionAdminPage = COption::GetOptionString("iblock","combined_list_mode")=="Y"?"iblock_list_admin.php":"iblock_section_admin.php";
$urlElementAdminPage = COption::GetOptionString("iblock","combined_list_mode")=="Y"?"iblock_list_admin.php":"iblock_element_admin.php";

$arIBTYPE = CIBlockType::GetByIDLang($type, LANG);
if($arIBTYPE===false)
	LocalRedirect("/bitrix/admin/iblock_type_admin.php?lang=".LANG);

if($_REQUEST["admin"] == "Y")
	$sTableID = "tbl_iblock_admin_".md5($type);
else
	$sTableID = "tbl_iblock_".md5($type);

$oSort = new CAdminSorting($sTableID, "TIMESTAMP_X", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = Array(
	"find_id",
	"find_name",
	"find_lang",
	"find_active",
	"find_code",
	);

$lAdmin->InitFilter($arFilterFields);

$arFilter = Array(
	"ID" => $find_id,
	"ACTIVE" => $find_active,
	"LID" => $find_lang,
	"?CODE" => $find_code,
	"?NAME" => $find_name,
	"TYPE" => $type,
	"MIN_PERMISSION" => "W",
	"CNT_ALL" => "Y",
);

if(CModule::IncludeModule("workflow"))
	$arFilter["MIN_PERMISSION"] = "U";

if($lAdmin->EditAction())
{
	foreach($FIELDS as $ID=>$arFields)
	{
		$DB->StartTransaction();
		$ID = IntVal($ID);

		if(!$lAdmin->IsUpdated($ID))
			continue;

		$iblock_permission = CIBlock::GetPermission($ID);
		if($iblock_permission<"X")
			continue;

		$ib = new CIBlock;
		if(!$ib->Update($ID, $arFields))
		{
			$lAdmin->AddUpdateError(GetMessage("IBLOCK_SAVE_ERROR").$ID.": ".$ib->LAST_ERROR."", $ID);
			$DB->Rollback();
		}
		$DB->Commit();
	}
}

if($arID = $lAdmin->GroupAction())
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsIBlocks = CIBlock::GetList(Array($by=>$order), $arFilter);
		while($arRes = $rsIBlocks->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if(strlen($ID)<=0)
			continue;

		switch($_REQUEST['action'])
		{
		case "delete":
			if(!$USER->IsAdmin())
				break;
			@set_time_limit(0);
			$DB->StartTransaction();
			if(!CIBlock::Delete($ID))
			{
				$DB->Rollback();
				$lAdmin->AddGroupError(GetMessage("IBLOCK_DELETE_ERROR"), $ID);
			}
			$DB->Commit();
			break;
		case "activate":
		case "deactivate":
			$ob = new CIBlock();
			$arFields = Array("ACTIVE"=>($_REQUEST['action']=="activate"?"Y":"N"));
			if(!$ob->Update($ID, $arFields))
				$lAdmin->AddGroupError(GetMessage("IBLOCK_ADM_UPD_ERROR").$ob->LAST_ERROR, $ID);

			break;
		}
	}
}

$arHeader = array(
	array("id"=>"NAME", 	"content"=>GetMessage("IBLOCK_ADMIN_NAME"), "sort"=>"name",	"default"=>true),
	array("id"=>"SORT", 	"content"=>GetMessage("IBLOCK_ADMIN_SORT"),	"sort"=>"sort",	"default"=>true, "align"=>"right"),
	array("id"=>"ACTIVE", "content"=>GetMessage("IBLOCK_ADMIN_ACTIVE"),	"sort"=>"active", "default"=>true, "align"=>"center"),
	);

$arHeader[] = array("id"=>"CODE", "content"=>GetMessage("IBLOCK_ADM_HEADER_CODE"), "sort"=>"code");
$arHeader[] = array("id"=>"LIST_PAGE_URL", "content"=>GetMessage("IBLOCK_ADM_HEADER_LIST_URL"));
$arHeader[] = array("id"=>"DETAIL_PAGE_URL", "content"=>GetMessage("IBLOCK_ADM_HEADER_DETAIL_URL"));

$arHeader[] = array("id"=>"ELEMENT_CNT", "content"=>GetMessage("IBLOCK_ADM_HEADER_EL"), "default"=>true, "align"=>"right");
if($arIBTYPE["SECTIONS"]=="Y")
	$arHeader[] = array("id"=>"SECTION_CNT", "content"=>GetMessage("IBLOCK_ADM_HEADER_SECT"),  "default"=>true, "align"=>"right");

$arHeader[] = array("id"=>"LID", 	"content"=>GetMessage("IBLOCK_ADMIN_LANG"),  "sort"=>"lid", "default"=>true, "align"=>"center");
$arHeader[] = array("id"=>"INDEX_ELEMENT", "content"=>GetMessage("IBLOCK_ADM_HEADER_TOINDEX"));
if(IsModuleInstalled("workflow"))
	$arHeader[] = array("id"=>"WORKFLOW", "content"=>GetMessage("IBLOCK_ADM_HEADER_WORKFLOW"));
$arHeader[] = array("id"=>"TIMESTAMP_X","content"=>GetMessage("IBLOCK_ADMIN_TIMESTAMP"), "sort"=>"timestamp_x", "default"=>true);
$arHeader[] = array("id"=>"ID", "content"=>"ID", "sort"=>"id", 	"default"=>true, "align"=>"right");


$lAdmin->AddHeaders($arHeader);

$rsIBlocks = CIBlock::GetList(Array($by=>$order), $arFilter, false);
$rsIBlocks = new CAdminResult($rsIBlocks, $sTableID);
$rsIBlocks->NavStart();

$lAdmin->NavText($rsIBlocks->GetNavPrint($arIBTYPE["NAME"]));

while($dbrs = $rsIBlocks->NavNext(true, "f_"))
{
	if((CIBlock::GetPermission($f_ID) >= "X") && ($_REQUEST["admin"] == "Y"))
	{
		$row =& $lAdmin->AddRow($f_ID, $dbrs, 'iblock_edit.php?ID='.$f_ID.'&type='.htmlspecialchars($type).'&lang='.LANG.'&admin='.($_REQUEST["admin"]=="Y"? "Y": "N"), GetMessage("IBLOCK_ADM_TO_EDIT"));
	}
	else
	{
		if($arIBTYPE["SECTIONS"]=="Y")
			$row =& $lAdmin->AddRow($f_ID, $dbrs, $urlSectionAdminPage.'?IBLOCK_ID='.$f_ID.'&type='.htmlspecialchars($type).'&lang='.LANG.'&SECTION_ID=0', GetMessage("IBLOCK_ADM_TO_SECTLIST"));
		else
			$row =& $lAdmin->AddRow($f_ID, $dbrs, $urlElementAdminPage.'?IBLOCK_ID='.$f_ID.'&type='.htmlspecialchars($type).'&lang='.LANG.'&filter_section=-1', GetMessage("IBLOCK_ADM_TO_EL_LIST"));
	}

	if(!strlen($f_SECTIONS_NAME))
		$f_SECTIONS_NAME = $arIBTYPE["SECTION_NAME"]? htmlspecialchars($arIBTYPE["SECTION_NAME"]): GetMessage("IBLOCK_SECTIONS");
	if(!$f_ELEMENTS_NAME)
		$f_ELEMENTS_NAME = $arIBTYPE["ELEMENT_NAME"]? htmlspecialchars($arIBTYPE["ELEMENT_NAME"]): GetMessage("IBLOCK_ELEMENTS");

	$f_LID = '';
	$db_LID = CIBlock::GetSite($f_ID);
	while($ar_LID = $db_LID->Fetch())
		$f_LID .= ($f_LID!=""?" / ":"").htmlspecialchars($ar_LID["LID"]);

	$row->AddViewField("LID", $f_LID);
	if((CIBlock::GetPermission($f_ID) >= "X") && ($_REQUEST["admin"] == "Y"))
	{
		$row->AddViewField("ID", $f_ID);

		$row->AddInputField("NAME", Array("size"=>"35"));
		$row->AddViewField("NAME", '<div class="iblock_menu_icon_iblocks"></div><a href="iblock_edit.php?ID='.$f_ID.'&type='.htmlspecialchars($type).'&lang='.LANG.'&admin='.($_REQUEST["admin"]=="Y"? "Y": "N").'" title="'.GetMessage("IBLOCK_ADM_TO_EDIT").'">'.$f_NAME.'</a>');

		$row->AddInputField("SORT", Array("size"=>"3"));
		$row->AddCheckField("ACTIVE");
		$row->AddInputField("CODE");
		$row->AddInputField("LIST_PAGE_URL");
		$row->AddInputField("DETAIL_PAGE_URL");
		$row->AddCheckField("INDEX_ELEMENT");
		$row->AddCheckField("WORKFLOW");
	}
	else
	{
		if($arIBTYPE["SECTIONS"]=="Y")
			$row->AddViewField("NAME", '<div class="iblock_menu_icon_iblocks"></div><a href="'.$urlSectionAdminPage.'?IBLOCK_ID='.$f_ID.'&SECTION_ID=0&type='.htmlspecialchars($type).'&lang='.LANG.'" title="'.GetMessage("IBLOCK_ADM_TO_SECTLIST").'">'.$f_NAME.'</a>');
		else
			$row->AddViewField("NAME", '<div class="iblock_menu_icon_iblocks"></div><a href="'.$urlElementAdminPage.'?IBLOCK_ID='.$f_ID.'&type='.htmlspecialchars($type).'&lang='.LANG.'&filter_section=-1" title="'.GetMessage("IBLOCK_ADM_TO_EL_LIST").'">'.$f_NAME.'</a>');
		$row->AddCheckField("ACTIVE", false);
		$row->AddCheckField("INDEX_ELEMENT", false);
	}

	if(in_array("ELEMENT_CNT", $lAdmin->GetVisibleHeaderColumns()))
	{
		$f_ELEMENT_CNT = CIBlock::GetElementCount($f_ID);
		$row->AddViewField("ELEMENT_CNT", '<a href="'.$urlElementAdminPage.'?IBLOCK_ID='.$f_ID.'&type='.htmlspecialchars($type).'&lang='.LANG.'&filter_section=-1" title="'.GetMessage("IBLOCK_ADM_TO_ELLIST").'">'.$f_ELEMENT_CNT.'</a>');
	}

	if($arIBTYPE["SECTIONS"]=="Y" && in_array("SECTION_CNT", $lAdmin->GetVisibleHeaderColumns()))
		$row->AddViewField("SECTION_CNT", '<a href="'.$urlSectionAdminPage.'?IBLOCK_ID='.$f_ID.'&type='.htmlspecialchars($type).'&lang='.LANG.'" title="'.GetMessage("IBLOCK_ADM_TO_SECTLIST").'">'.IntVal(CIBlockSection::GetCount(Array("IBLOCK_ID"=>$f_ID))).'</a>');

	$arActions = Array();

//	if($arIBTYPE["SECTIONS"]=="Y")
//	{
//		$arActions[] = array("ICON"=>"elements", "TEXT"=>$f_SECTIONS_NAME, "ACTION"=>"window.location='".$urlSectionAdminPage."?IBLOCK_ID=".$f_ID."&type=".htmlspecialchars($type)."&lang=".LANG."';");
//	}
//
//	$arActions[] = array("ICON"=>"elements", "TEXT"=>$f_ELEMENTS_NAME, "DEFAULT"=>$_REQUEST["admin"]!="Y", "ACTION"=>"window.location='".$urlElementAdminPage."?IBLOCK_ID=".$f_ID."&type=".htmlspecialchars($type)."&lang=".LANG."&filter_section=-1';");

	if((CIBlock::GetPermission($f_ID) >= "X") && ($_REQUEST["admin"] == "Y"))
	{
		$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("MAIN_ADMIN_MENU_EDIT"), "DEFAULT"=>$_REQUEST["admin"]=="Y", "ACTION"=>"window.location='iblock_edit.php?ID=".$f_ID."&type=".htmlspecialchars($type)."&lang=".LANG."&admin=".($_REQUEST["admin"]=="Y"? "Y": "N")."';");
	}

	if($USER->IsAdmin() && ($_REQUEST["admin"] == "Y"))
	{
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("MAIN_ADMIN_MENU_DELETE"), "ACTION"=>"if(confirm('".GetMessage("IBLOCK_CONFIRM_DEL_MESSAGE")."')) ".$lAdmin->ActionDoGroup($f_ID, "delete", "&type=".htmlspecialchars($type)."&lang=".LANG."&admin=".($_REQUEST["admin"]=="Y"? "Y": "N")));
	}

	if(count($arActions))
		$row->AddActions($arActions);
}

$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsIBlocks->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

if($USER->IsAdmin() && ($_REQUEST["admin"] == "Y"))
{
	$aContext = array(
		array(
			"ICON"=>"btn_new",
			"TEXT"=>GetMessage("IBLOCK_ADM_TO_ADDIBLOCK"),
			"LINK"=>"iblock_edit.php?lang=".LANG."&admin=Y&type=".urlencode($type),
			"TITLE"=>GetMessage("IBLOCK_ADM_TO_ADDIBLOCK_TITLE")
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);

	$lAdmin->AddGroupActionTable(Array(
		"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
		"activate"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
		"deactivate"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
		));
}
else
{
	$lAdmin->AddAdminContextMenu(array());
}



$lAdmin->CheckListMode();

$APPLICATION->SetTitle($arIBTYPE["NAME"].": ".GetMessage("IBLOCK_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form method="GET" action="iblock_admin.php?type=<?=urlencode($type)?>" name="find_form">
<input type="hidden" name="admin" value="<?echo ($_REQUEST["admin"]=="Y"? "Y": "N")?>">
<input type="hidden" name="lang" value="<?echo LANG?>">
<input type="hidden" name="filter" value="Y">
<input type="hidden" name="type" value="<?echo htmlspecialchars($type)?>">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("IBLOCK_ADM_FILT_SITE"),
		GetMessage("IBLOCK_ADM_FILT_ACT"),
		"ID",
		GetMessage("IBLOCK_ADM_FILT_CODE")
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><b><?echo GetMessage("IBLOCK_ADM_FILT_NAME")?></b></td>
		<td><input type="text" name="find_name" value="<?echo htmlspecialchars($find_name)?>" size="40">&nbsp;<?=ShowFilterLogicHelp()?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBLOCK_F_LANG");?></td>
		<td>
			<select name="find_lang">
				<option value=""><?echo GetMessage("IBLOCK_ALL")?></option>
			<?
			$l = CLang::GetList($b="sort", $o="asc", Array("VISIBLE"=>"Y"));
			while($l->ExtractFields("l_")):
				?><option value="<?echo $l_LID?>"<?if($find_lang==$l_LID)echo " selected"?>><?echo $l_NAME?></option><?
			endwhile;
			?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBLOCK_F_ACTIVE")?></td>
		<td>
			<?
			$arr = array("reference"=>array(GetMessage("IBLOCK_YES"), GetMessage("IBLOCK_NO")), "reference_id"=>array("Y","N"));
			echo SelectBoxFromArray("find_active", $arr, htmlspecialcharsex($find_active), GetMessage('IBLOCK_ALL'));
			?>
		</td>
	</tr>
	<tr>
		<td>ID:</td>
		<td><input type="text" name="find_id" value="<?echo htmlspecialchars($find_id)?>" size="15"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBLOCK_ADM_FILTER_CODE")?></td>
		<td><input type="text" name="find_code" value="<?echo htmlspecialchars($find_code)?>" size="15">&nbsp;<?=ShowFilterLogicHelp()?></td>
	</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage().'?type='.urlencode($type), "form"=>"find_form"));
$oFilter->End();
?>
</form>
<?
$lAdmin->DisplayList();

if($_REQUEST["admin"]!="Y"):
	echo	BeginNote(),
		GetMessage("IBLOCK_ADM_MANAGE_HINT"),
		' <a href="iblock_admin.php?type='.htmlspecialchars($type).'&amp;lang='.LANG.'&amp;admin=Y">',
		GetMessage("IBLOCK_ADM_MANAGE_HINT_HREF"),
		'</a>.',
	EndNote();
endif;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
