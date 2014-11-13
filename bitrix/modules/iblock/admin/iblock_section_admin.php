<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/iblock.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);

$arIBTYPE = CIBlockType::GetByIDLang($type, LANG);
if($arIBTYPE===false)
	$APPLICATION->AuthForm(GetMessage("IBLOCK_BAD_BLOCK_TYPE_ID"));

$IBLOCK_ID = IntVal($IBLOCK_ID);
$arIBlock = CIBlock::GetArrayByID($IBLOCK_ID);
$BlockPerm = '';
if($arIBlock)
{
	$BlockPerm = CIBlock::GetPermission($IBLOCK_ID);
	if(CModule::IncludeModule("workflow"))
		$bBadBlock=($BlockPerm<"U");
	else
		$bBadBlock=($BlockPerm<"W");
}
else
	$bBadBlock = true;

if($bBadBlock)
{
	$APPLICATION->SetTitle($arIBTYPE["NAME"]);
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	?>
	<?echo ShowError(GetMessage("IBLOCK_BAD_IBLOCK"));?>
	<a href="iblock_admin.php?lang=<?echo LANG?>&amp;type=<?echo htmlspecialchars($type)?>"><?echo GetMessage("IBLOCK_BACK_TO_ADMIN")?></a>
	<?
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

//This enables readonly mode
$bReadOnly = $BlockPerm < "W";

$entity_id = "IBLOCK_".$IBLOCK_ID."_SECTION";

$sTableID = "tbl_iblock_section_".md5($type.".".$IBLOCK_ID);
$oSort = new CAdminSorting($sTableID, "timestamp_x", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = Array(
	"find_section_id",
	"find_section_timestamp_1",
	"find_section_timestamp_2",
	"find_section_modified_by",
	"find_section_date_create_1",
	"find_section_date_create_2",
	"find_section_created_by",
	"find_section_name",
	"find_section_active",
	"find_section_section",
	"find_section_code",
	"find_section_external_id"
);
$USER_FIELD_MANAGER->AdminListAddFilterFields($entity_id, $arFilterFields);

//We have to handle current section in a special way
$section_id = strlen($find_section_section) > 0? intval($find_section_section): "";
$lAdmin->InitFilter($arFilterFields);
$find_section_section = $section_id;

//This is all parameters needed for proper navigation
$sThisSectionUrl = '&type='.urlencode($type).'&lang='.LANG.'&IBLOCK_ID='.$IBLOCK_ID.'&find_section_section='.intval($find_section_section);

$arFilter = Array(
	"IBLOCK_ID"	=> $IBLOCK_ID,
	"?NAME"		=> $find_section_name,
	"SECTION_ID"	=> $find_section_section,
	"ID"		=> $find_section_id,
	">=TIMESTAMP_X"	=> $find_section_timestamp_1,
	"<=TIMESTAMP_X"	=> $find_section_timestamp_2,
	"MODIFIED_BY"	=> $find_section_modified_user_id? $find_section_modified_user_id: $find_section_modified_by,
	">=DATE_CREATE"	=> $find_section_date_create_1,
	"<=DATE_CREATE"	=> $find_section_date_create_2,
	"CREATED_BY"	=> $find_section_created_user_id? $find_section_created_user_id: $find_section_created_by,
	"ACTIVE"	=> $find_section_active,
	"CODE"		=> $find_section_code,
	"EXTERNAL_ID"	=> $find_section_external_id,
	);
$USER_FIELD_MANAGER->AdminListAddFilter($entity_id, $arFilter);

if($find_section_section === "")
	unset($arFilter["SECTION_ID"]);

// Edititng handling (do not forget rights check!)
$bIsUpdate = false;
if(!$bReadOnly && $lAdmin->EditAction()) //save button pressed
{
	$bIsUpdate = false;
	foreach($FIELDS as $ID=>$arFields)
	{
		$USER_FIELD_MANAGER->AdminListPrepareFields($entity_id, $arFields);
		$arFields["IBLOCK_ID"] = $IBLOCK_ID;
		if(!$lAdmin->IsUpdated($ID))
			continue;
		$ID = IntVal($ID);
		$bIsUpdate = true;
		$ib = new CIBlockSection;
		$DB->StartTransaction();
		if(!$ib->Update($ID, $arFields))
		{
			if($e = $APPLICATION->GetException())
				$message = $e->GetString();
			else
				$message = $ib->LAST_ERROR;
			$lAdmin->AddUpdateError(GetMessage("IBLOCK_SAVE_ERROR").$ID.": ".$message, $ID);
			$DB->Rollback();
		}
		$DB->Commit();
	}
}

// action handler
if(!$bReadOnly && ($arID = $lAdmin->GroupAction()))
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = CIBlockSection::GetList(Array($by=>$order), $arFilter);
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
			@set_time_limit(0);
			$DB->StartTransaction();
			if(!CIBlockSection::Delete($ID))
			{
				if($e = $APPLICATION->GetException())
					$message = $e->GetString();
				else
					$message = GetMessage("IBSEC_A_DELERR_REFERERS");
				$lAdmin->AddGroupError(GetMessage("IBSEC_A_DELERR", array("#ID#"=>$ID)).$message, $ID);
				$DB->Rollback();
			}
			$DB->Commit();
			$bIsUpdate = true;
			break;
		case "activate":
		case "deactivate":
			$ob = new CIBlockSection();
			$arFields = Array("ACTIVE"=>($_REQUEST['action']=="activate"?"Y":"N"));
			if(!$ob->Update($ID, $arFields))
				$lAdmin->AddGroupError(GetMessage("IBSEC_A_UPDERR").$ob->LAST_ERROR, $ID);

			$bIsUpdate = true;
			break;
		}
	}
}

if($bIsUpdate)
{
	$DB->StartTransaction();
	CIBlockSection::ReSort($IBLOCK_ID);
	$DB->Commit();
}

// list header
$arHeaders = array(
	array(
		"id" => "NAME",
		"content" => GetMessage("IBSEC_A_NAME"),
		"sort" => "name",
		"default" => true,
	),
	array(
		"id" => "ACTIVE",
		"content" => GetMessage("IBSEC_A_ACTIVE"),
		"sort" => "active",
		"default" => true,
		"align" => "center",
	),
	array(
		"id" => "SORT",
		"content" => GetMessage("IBSEC_A_SORT"),
		"sort" => "sort",
		"default" => true,
		"align" => "right",
	),
	array(
		"id" => "CODE",
		"content" => GetMessage("IBSEC_A_CODE"),
		"sort" => "code",
	),
	array(
		"id" => "XML_ID",
		"content" => GetMessage("IBSEC_A_XML_ID"),
	),
	array(
		"id" => "ELEMENT_CNT",
		"content" => GetMessage("IBSEC_A_ELEMENT_CNT"),
		"sort" => "element_cnt",
		"default" => true,
		"align" => "right",
	),
	array(
		"id" => "SECTION_CNT",
		"content" => GetMessage("IBSEC_A_SECTION_CNT"),
		"default" => true,
		"align" => "right",
	),
	array(
		"id" => "TIMESTAMP_X",
		"content" => GetMessage("IBSEC_A_TIMESTAMP"),
		"sort" => "timestamp_x",
		"default" => true,
	),
	array(
		"id" => "MODIFIED_BY",
		"content" => GetMessage("IBSEC_A_MODIFIED_BY"),
		"sort" => "modified_by",
	),
	array(
		"id" => "DATE_CREATE",
		"content" => GetMessage("IBSEC_A_DATE_CREATE"),
		"sort" => "date_create",
	),
	array(
		"id" => "CREATED_BY",
		"content" => GetMessage("IBSEC_A_CREATED_BY"),
		"sort" => "created_by",
	),
	array(
		"id" => "ID",
		"content" => GetMessage("IBSEC_A_ID"),
		"sort" => "id",
		"default" => true,
		"align" => "right",
	),
);
$USER_FIELD_MANAGER->AdminListAddHeaders($entity_id, $arHeaders);
$lAdmin->AddHeaders($arHeaders);

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();
$arVisibleColumnsMap = array();
foreach($arVisibleColumns as $value)
	$arVisibleColumnsMap[$value] = true;

if(array_key_exists("ELEMENT_CNT", $arVisibleColumnsMap))
{
	$arFilter["CNT_ALL"] = "Y";
	$arFilter["ELEMENT_SUBSECTIONS"] = "N";
	$rsData = CIBlockSection::GetList(Array($by=>$order), $arFilter, true, $arVisibleColumns);
}
else
	$rsData = CIBlockSection::GetList(Array($by=>$order), $arFilter, false, $arVisibleColumns);

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint($arIBlock["SECTIONS_NAME"]));

$arUsersCache = array();

while($arRes = $rsData->NavNext(true, "f_"))
{
	$el_list_url = 'iblock_element_admin.php?IBLOCK_ID='.$IBLOCK_ID.'&amp;type='.htmlspecialchars($type).'&amp;lang='.LANG.'&amp;find_section_section='.$f_ID;
	$el_add_url = 'iblock_element_edit.php?IBLOCK_SECTION_ID='.$f_ID.'&amp;from=iblock_section_admin_inc'.$sThisSectionUrl;
	$sec_list_url = 'iblock_section_admin.php?IBLOCK_ID='.$IBLOCK_ID.'&amp;type='.htmlspecialchars($type).'&amp;lang='.LANG.'&amp;find_section_section='.$f_ID;
	$sec_add_url = 'iblock_section_edit.php?IBLOCK_SECTION_ID='.$f_ID.'&from=iblock_section_admin'.$sThisSectionUrl;

	$row =& $lAdmin->AddRow($f_ID, $arRes, $sec_list_url, GetMessage("IBSEC_A_LIST"));

	$USER_FIELD_MANAGER->AddUserFields($entity_id, $arRes, $row);

	if($bReadOnly)
	{
		$row->AddCheckField("ACTIVE", false);
		$row->AddInputField("NAME", false);
		$row->AddInputField("SORT", false);
		$row->AddInputField("CODE", false);
		$row->AddInputField("EXTERNAL_ID", false);
	}
	else
	{
		$row->AddCheckField("ACTIVE");
		$row->AddInputField("NAME", Array('size'=>'35'));
		$row->AddInputField("SORT", Array('size'=>'3'));
		$row->AddInputField("CODE");
		$row->AddInputField("EXTERNAL_ID");
	}

	$edit_url = 'iblock_section_edit.php?ID='.$f_ID.$sThisSectionUrl;

	$row->AddViewField("ID", '<a href="'.$edit_url.'" title="'.GetMessage("IBSEC_A_EDIT").'">'.$f_ID.'</a>');
	$row->AddViewField("NAME", '<div class="iblock_menu_icon_sections"></div><a href="'.$sec_list_url.'" title="'.GetMessage("IBSEC_A_LIST").'">'.$f_NAME.'</a>');

	if(array_key_exists("ELEMENT_CNT", $arVisibleColumnsMap))
		$row->AddViewField("ELEMENT_CNT", '<a href="'.$el_list_url.'&find_el_subsections=N" title="'.GetMessage("IBSEC_A_ELLIST").'">'.$f_ELEMENT_CNT.'</a>('.'<a href="'.$el_list_url.'&find_el_subsections=Y" title="'.GetMessage("IBSEC_A_ELLIST_TITLE").'">'.IntVal(CIBlockSection::GetSectionElementsCount($f_ID, Array("CNT_ALL"=>"Y"))).'</a>) [<a href="'.$el_add_url.'" title="'.GetMessage("IBSEC_A_ELADD_TITLE").'">+</a>]');

	if(array_key_exists("SECTION_CNT", $arVisibleColumnsMap))
	{
		$arFilter = Array("IBLOCK_ID"=>$IBLOCK_ID, "SECTION_ID"=>$f_ID);
		$row->AddViewField("SECTION_CNT", '<a href="'.$sec_list_url.'" onclick="'.$lAdmin->ActionAjaxReload($sec_list_url).'; return false;" title="'.GetMessage("IBSEC_A_LIST").'">'.IntVal(CIBlockSection::GetCount($arFilter)).'</a> [<a href="'.$sec_add_url.'" title="'.GetMessage("IBSEC_A_SECTADD_TITLE").'">+</a>]');
	}

	if(array_key_exists("MODIFIED_BY", $arVisibleColumnsMap) && intval($f_MODIFIED_BY) > 0)
	{
		if(!array_key_exists($f_MODIFIED_BY, $arUsersCache))
		{
			$rsUser = CUser::GetByID($f_MODIFIED_BY);
			$arUsersCache[$f_MODIFIED_BY] = $rsUser->Fetch();
		}
		if($arUser = $arUsersCache[$f_MODIFIED_BY])
			$row->AddViewField("MODIFIED_BY", '[<a href="user_edit.php?lang='.LANG.'&ID='.$f_MODIFIED_BY.'" title="'.GetMessage("IBSEC_A_USERINFO").'">'.$f_MODIFIED_BY."</a>]&nbsp;(".$arUser["LOGIN"].") ".$arUser["NAME"]." ".$arUser["LAST_NAME"]);
	}

	if(array_key_exists("CREATED_BY", $arVisibleColumnsMap) && intval($f_CREATED_BY) > 0)
	{
		if(!array_key_exists($f_CREATED_BY, $arUsersCache))
		{
			$rsUser = CUser::GetByID($f_CREATED_BY);
			$arUsersCache[$f_CREATED_BY] = $rsUser->Fetch();
		}
		if($arUser = $arUsersCache[$f_MODIFIED_BY])
			$row->AddViewField("CREATED_BY", '[<a href="user_edit.php?lang='.LANG.'&ID='.$f_CREATED_BY.'" title="'.GetMessage("IBSEC_A_USERINFO").'">'.$f_CREATED_BY."</a>]&nbsp;(".$arUser["LOGIN"].") ".$arUser["NAME"]." ".$arUser["LAST_NAME"]);
	}

	$arActions = Array();

	$arActions[] = array("ICON"=>"list", "TEXT"=>htmlspecialcharsex($arIBlock["SECTIONS_NAME"]), "ACTION"=>$lAdmin->ActionRedirect($sec_list_url), "DEFAULT"=>"Y");
	$arActions[] = array("ICON"=>"list", "TEXT"=>htmlspecialcharsex($arIBlock["ELEMENTS_NAME"]), "ACTION"=>$lAdmin->ActionRedirect($el_list_url."&&find_el_subsections=N"));

	if(!$bReadOnly)
	{
		$arActions[] = array("SEPARATOR"=>true);
		$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("IBSEC_A_CHANGE"), "ACTION"=>$lAdmin->ActionRedirect($edit_url));
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("IBSEC_A_DELETE"), "ACTION"=>"if(confirm('".GetMessage("IBLOCK_CONFIRM_DEL_MESSAGE")."')) ".$lAdmin->ActionDoGroup($f_ID, "delete", $sThisSectionUrl));
	}

	$row->AddActions($arActions);
}

$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

if(!$bReadOnly)
{
	$lAdmin->AddGroupActionTable(Array(
		"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
		"activate"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
		"deactivate"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
	));
}

$aContext = array();
$aContext[] = array(
	"TEXT"=>$arIBlock["ELEMENTS_NAME"],
	"ICON"=>"btn_list",
	"LINK"=>'iblock_element_admin.php?from=iblock_section_admin'.$sThisSectionUrl,
	"TITLE"=>GetMessage("IBSEC_A_LISTEL_TITLE")
);

if(!$bReadOnly)
{
	$aContext[] = array(
		"TEXT"=>$arIBlock["SECTION_ADD"],
		"ICON"=>"btn_new",
		"LINK"=>'iblock_section_edit.php?IBLOCK_SECTION_ID='.urlencode($find_section_section).'&from=iblock_section_admin'.$sThisSectionUrl,
		"TITLE"=>GetMessage("IBSEC_A_SECTADD_PRESS")
	);
}

$aContext[] = array(
	"TEXT"=>$arIBlock["ELEMENT_ADD"],
	"ICON"=>"btn_new",
	"LINK"=>'iblock_element_edit.php?IBLOCK_SECTION_ID='.urlencode($find_section_section).'&from=iblock_section_admin'.$sThisSectionUrl,
	"TITLE"=>GetMessage("IBSEC_A_ADDEL_TITLE")
);

$lAdmin->AddAdminContextMenu($aContext);

$chain = $lAdmin->CreateChain();
if(intval($find_section_section)>0)
{
	$chain->AddItem(array("TEXT"=>htmlspecialcharsex($arIBlock["NAME"]), "LINK"=>'iblock_section_admin.php?type='.htmlspecialchars($type).'&amp;lang='.LANG.'&amp;IBLOCK_ID='.$IBLOCK_ID.'&amp;find_section_section=0', "ONCLICK"=>$lAdmin->ActionAjaxReload('iblock_section_admin.php?type='.htmlspecialchars($type).'&lang='.LANG.'&IBLOCK_ID='.$IBLOCK_ID.'&find_section_section=0').';return false;'));
	$nav = CIBlockSection::GetNavChain($IBLOCK_ID, $find_section_section);
	while($nav->ExtractFields("nav_"))
	{
		if($find_section_section==$nav_ID)
			$chain->AddItem(array("TEXT"=>$nav_NAME));
		else
			$chain->AddItem(array("TEXT"=>$nav_NAME, "LINK"=>'iblock_section_admin.php?type='.htmlspecialchars($type).'&amp;lang='.LANG.'&amp;IBLOCK_ID='.$IBLOCK_ID.'&amp;find_section_section='.$nav_ID,  "ONCLICK"=>$lAdmin->ActionAjaxReload('iblock_section_admin.php?type='.htmlspecialchars($type).'&lang='.LANG.'&IBLOCK_ID='.$IBLOCK_ID.'&find_section_section='.$nav_ID).';return false;'));
	}
}
else
	$chain->AddItem(array("TEXT"=>htmlspecialcharsex($arIBlock["NAME"])));

$lAdmin->ShowChain($chain);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle($arIBlock["NAME"].": ".$arIBlock["SECTIONS_NAME"]);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form method="GET" name="find_section_form" action="<?echo $APPLICATION->GetCurPage()?>">
<?
$arFindFields = Array(
	"parent" => GetMessage("IBSEC_A_PARENT"),
	"id" => GetMessage("IBSEC_A_ID"),
	"timestamp_x" => GetMessage("IBSEC_A_TIMESTAMP"),
	"modified_by" => GetMessage("IBSEC_A_MODIFIED_BY"),
	"date_create" => GetMessage("IBSEC_A_DATE_CREATE"),
	"created_by" => GetMessage("IBSEC_A_CREATED_BY"),
	"code" => GetMessage("IBSEC_A_CODE"),
	"xml_id" => GetMessage("IBSEC_A_XML_ID"),
	"active" => GetMessage("IBSEC_A_ACTIVE"),
);
$USER_FIELD_MANAGER->AddFindFields($entity_id, $arFindFields);

$oFilter = new CAdminFilter($sTableID."_filter", $arFindFields);

$oFilter->Begin();
?>
	<tr>
		<td><b><?echo GetMessage("IBSEC_A_NAME")?>:</b></td>
		<td><input type="text" name="find_section_name" value="<?echo htmlspecialcharsex($find_section_name)?>" size="47">&nbsp;<?=ShowFilterLogicHelp()?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBSEC_A_SECTION")?>:</td>
		<td>
			<select name="find_section_section" >
				<option value=""><?echo GetMessage("IBLOCK_ALL")?></option>
				<option value="0"<?if($find_section_section=="0")echo" selected"?>><?echo GetMessage("IBSEC_A_ROOT_SECTION")?></option>
				<?
				$bsections = CIBlockSection::GetTreeList(Array("IBLOCK_ID"=>$IBLOCK_ID));
				while($bsections->ExtractFields("s_")):
					?><option value="<?echo $s_ID?>"<?if($s_ID==$find_section_section)echo " selected"?>><?echo str_repeat("&nbsp;.&nbsp;", $s_DEPTH_LEVEL)?><?echo $s_NAME?></option><?
				endwhile;
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBSEC_A_ID")?>:</td>
		<td><input type="text" name="find_section_id" size="47" value="<?echo htmlspecialchars($find_section_id)?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBSEC_A_TIMESTAMP")." (".CLang::GetDateFormat("SHORT")."):"?></td>
		<td><?echo CalendarPeriod("find_section_timestamp_1", htmlspecialchars($find_section_timestamp_1), "find_section_timestamp_2", htmlspecialchars($find_section_timestamp_2), "find_section_form","Y")?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBSEC_A_MODIFIED_BY")?>:</td>
		<td><input type="text" name="find_section_modified_user_id" value="<?echo htmlspecialcharsex($find_section_modified_by)?>" size="3">&nbsp;<?
		$gr_res = CIBlock::GetGroupPermissions($IBLOCK_ID);
		$res = Array(1);
		foreach($gr_res as $gr=>$perm)
			if($perm>"R")
				$res[] = $gr;
			$res = CUser::GetList($byx="NAME", $orderx="ASC", Array("GROUP_MULTI"=>$res));
		?><select name="find_section_modified_by">
		<option value=""><?echo GetMessage("IBLOCK_ALL")?></option><?
		while($arr = $res->Fetch())
			echo "<option value='".$arr["ID"]."'".($find_section_modified_by==$arr["ID"]?" selected":"").">(".htmlspecialcharsex($arr["LOGIN"].") ".$arr["NAME"]." ".$arr["LAST_NAME"])."</option>";
		?></select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBSEC_A_DATE_CREATE")." (".CLang::GetDateFormat("SHORT")."):"?></td>
		<td><?echo CalendarPeriod("find_section_date_create_1", htmlspecialcharsex($find_section_date_create_1), "find_section_date_create_2", htmlspecialcharsex($find_section_date_create_2), "find_section_form")?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBSEC_A_CREATED_BY")?>:</td>
		<td><input type="text" name="find_section_created_user_id" value="<?echo htmlspecialcharsex($find_section_created_by)?>" size="3">&nbsp;<?
		$gr_res = CIBlock::GetGroupPermissions($IBLOCK_ID);
		$res = Array(1);
		foreach($gr_res as $gr=>$perm)
			if($perm>"R")
				$res[] = $gr;
		$res = CUser::GetList($byx="NAME", $orderx="ASC", Array("GROUP_MULTI"=>$res));
		?><select name="find_section_created_by">
		<option value=""><?echo GetMessage("IBLOCK_ALL")?></option><?
		while($arr = $res->Fetch())
			echo "<option value='".$arr["ID"]."'".($find_section_created_by==$arr["ID"]?" selected":"").">(".htmlspecialcharsex($arr["LOGIN"].") ".$arr["NAME"]." ".$arr["LAST_NAME"])."</option>";
		?></select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBSEC_A_CODE")?>:</td>
		<td><input type="text" name="find_section_code" size="47" value="<?echo htmlspecialchars($find_section_code)?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBSEC_A_XML_ID")?>:</td>
		<td><input type="text" name="find_section_external_id" size="47" value="<?echo htmlspecialchars($find_section_external_id)?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBSEC_A_ACTIVE")?>:</td>
		<td>
			<select name="find_section_active" >
				<option value=""><?=htmlspecialcharsex(GetMessage('IBLOCK_ALL'))?></option>
				<option value="Y"<?if($find_section_active=="Y")echo " selected"?>><?=htmlspecialcharsex(GetMessage("IBLOCK_YES"))?></option>
				<option value="N"<?if($find_section_active=="N")echo " selected"?>><?=htmlspecialcharsex(GetMessage("IBLOCK_NO"))?></option>
			</select>
		</td>
	</tr>
<?
$USER_FIELD_MANAGER->AdminListShowFilter($entity_id);
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage().'?type='.$type.'&IBLOCK_ID='.$IBLOCK_ID, "form"=>"find_section_form"));
$oFilter->End();
?>
</form>
<?
$lAdmin->DisplayList();
?>
<?
if($BlockPerm >= "X")
{
	echo
		BeginNote(),
		GetMessage("IBSEC_A_IBLOCK_MANAGE_HINT"),
		' <a href="iblock_edit.php?type='.htmlspecialchars($type).'&amp;lang='.LANG.'&amp;ID='.$IBLOCK_ID.'&amp;admin=Y&amp;return_url='.urlencode("iblock_section_admin.php?".$sThisSectionUrl).'">',
		GetMessage("IBSEC_A_IBLOCK_MANAGE_HINT_HREF"),
		'</a>',
		EndNote()
	;
}
?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
