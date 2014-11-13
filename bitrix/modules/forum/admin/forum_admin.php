<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/include.php");

$forumModulePermissions = $APPLICATION->GetGroupRight("forum");
if ($forumModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
IncludeModuleLangFile(__FILE__);
//include(GetLangFileName($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/lang/", "/forum_admin.php"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/prolog.php");

// идентификатор таблицы
$sTableID = "tbl_forum_forums";

// инициализация сортировки
$oSort = new CAdminSorting($sTableID, "ID", "asc");
// инициализация списка
$lAdmin = new CAdminList($sTableID, $oSort);

// инициализация параметров списка - фильтры
$arFilterFields = array(
	"filter_site_id",
	"filter_active",
	"filter_group_id",
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();

if (strlen($filter_site_id) > 0 && $filter_site_id != "NOT_REF")
	$arFilter["SITE_ID"] = $filter_site_id;
if (strlen($filter_active) > 0)
	$arFilter["ACTIVE"] = $filter_active;
if (strlen($filter_group_id) > 0)
	$arFilter["FORUM_GROUP_ID"] = $filter_group_id;

// обработка редактирования (права доступа!)
if ($lAdmin->EditAction() && $forumModulePermissions >= "R")
{
	foreach ($FIELDS as $ID => $arFields)
	{
		$DB->StartTransaction();
		$ID = IntVal($ID);

		if (!$lAdmin->IsUpdated($ID))
			continue;

		if (!CForumNew::CanUserUpdateForum($ID, $USER->GetUserGroupArray(), $USER->GetID()))
		{
			$lAdmin->AddUpdateError(GetMessage("FA_NO_PERMS2UPDATE")." ".$ID."", $ID);
			continue;
		}

		if (!CForumNew::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$lAdmin->AddUpdateError($ex->GetString(), $ID);
			else
				$lAdmin->AddUpdateError(GetMessage("FA_ERROR_UPDATE")." ".$ID."", $ID);

			$DB->Rollback();
		}

		$DB->Commit();
	}
	BXClearCache(true, "/".SITE_ID."/forum/forum/");
	BXClearCache(true, "/".SITE_ID."/forum/forums/");
}

// обработка действий групповых и одиночных
if (($arID = $lAdmin->GroupAction()) && $forumModulePermissions >= "R")
{
	BXClearCache(true, "/".SITE_ID."/forum/forum/");
	BXClearCache(true, "/".SITE_ID."/forum/forums/");
	
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = array();
		$dbResultList = CForumNew::GetList(
			array($by => $order),
			$arFilter
		);
		while ($arResult = $dbResultList->Fetch())
			$arID[] = $arResult['ID'];
	}

	foreach ($arID as $ID)
	{
		if (strlen($ID) <= 0)
			continue;

		switch ($_REQUEST['action'])
		{
			case "delete":

				if (!CForumNew::CanUserDeleteForum($ID, $USER->GetUserGroupArray(), $USER->GetID()))
				{
					$lAdmin->AddGroupError(GetMessage("FA_DELETE_NO_PERMS"), $ID);
					continue;
				}

				@set_time_limit(0);

				$DB->StartTransaction();

				if (!CForumNew::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("FA_DELETE_ERROR"), $ID);
				}

				$DB->Commit();

				break;

			case "activate":
			case "deactivate":

				if (!CForumNew::CanUserUpdateForum($ID, $USER->GetUserGroupArray(), $USER->GetID()))
				{
					$lAdmin->AddUpdateError(GetMessage("FA_NO_PERMS2UPDATE")." ".$ID."", $ID);
					continue;
				}

				$arFields = array(
					"ACTIVE" => (($_REQUEST['action']=="activate") ? "Y" : "N")
				);

				if (!CForumNew::Update($ID, $arFields))
				{
					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("FA_ERROR_UPDATE")." ".$ID."", $ID);
				}

				break;
			case "clear_html": // хотя зачем экранировать один запрос?
				$DB->StartTransaction();
				if (!CForumNew::ClearHTML($ID))
				{
					$DB->Rollback();
					$lAdmin->AddGroupError(GetMessage("FA_ERROR_UPDATE")." ".$ID."", $ID);
				}
				$DB->Commit();
				break;
		}
	}
}

$dbResultList = CForumNew::GetList(
	array($by => $order),
	$arFilter
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

// установке параметров списка
$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("NAV")));

// заголовок списка
$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage("NAME"), "sort"=>"NAME", "default"=>true),
	array("id"=>"ACTIVE","content"=>GetMessage("ACTIVE"), "sort"=>"ACTIVE", "default"=>true),
	array("id"=>"LAND", "content"=>GetMessage('LAND'), "default"=>true),
	array("id"=>"SORT", "content"=>GetMessage("SORT"),  "sort"=>"SORT", "default"=>true, "align"=>"right"),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

// построение списка
while ($arForum = $dbResultList->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arForum);

	$bCanUpdateForum = CForumNew::CanUserUpdateForum($f_ID, $USER->GetUserGroupArray(), $USER->GetID());
	$bCanDeleteForum = $bCanUpdateForum;

	if (!$bCanUpdateForum && !$bCanDeleteForum)
		$row->bReadOnly = True;

	$row->AddField("ID", $f_ID);
	$row->AddCheckField("ACTIVE", (($bCanUpdateForum || $bCanDeleteForum) ? array() : false ));

	$fieldShow = "";
	if (in_array("LAND", $arVisibleColumns))
	{
		$arForumSite_tmp = CForumNew::GetSites($f_ID);
		$i = 0;
		foreach ($arForumSite_tmp as $key => $value)
		{
			if ($i > 0)
				$fieldShow .= ", ";
			$fieldShow .= $key;
			$i++;
		}
	}
	$row->AddField("LAND", $fieldShow);

	$row->AddInputField("SORT", (($bCanUpdateForum || $bCanDeleteForum) ? array("size" => "3") : false ));
	$row->AddViewField("NAME", '<a title="'.GetMessage("FORUM_EDIT").'" href="'."forum_edit.php?ID=".$f_ID."&amp;lang=".LANG.GetFilterParams("filter_").'">'.$f_NAME.'</a>');
	$row->AddInputField("NAME", (($bCanUpdateForum || $bCanDeleteForum) ? array("size" => "30") : false ));

	$arActions = Array();
	if ($bCanUpdateForum || $forumModulePermissions >= "R")
	{
		$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("FORUM_EDIT"), "ACTION"=>$lAdmin->ActionRedirect("forum_edit.php?ID=".$f_ID."&lang=".LANG.GetFilterParams("filter_", false).""), "DEFAULT"=>true);
	}
	if ($bCanDeleteForum && $forumModulePermissions >= "W")
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("FORUM_DELETE"), "ACTION"=>"if(confirm('".GetMessage('DELETE_CONFIRM')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
	}

	$row->AddActions($arActions);
}

// "подвал" списка
$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $dbResultList->SelectedRowsCount()
		),
		array(
			"counter" => true,
			"title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
			"value" => "0"
		),
	)
);

// показ формы с кнопками добавления, ...
$lAdmin->AddGroupActionTable(
	array(
		"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
		"activate" => GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
		"deactivate" => GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
		"clear_html" => GetMessage("MAIN_ADMIN_LIST_CLEAR_HTML"),
	)
);

if ($forumModulePermissions >= "W")
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("FFAN_ADD_NEW"),
			"LINK" => "forum_edit.php?lang=".LANG,
			"TITLE" => GetMessage("FFAN_ADD_NEW_ALT"),
			"ICON" => "btn_new",
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
}

// проверка на вывод только списка (в случае списка, скрипт дальше выполняться не будет)
$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/
$APPLICATION->SetTitle(GetMessage("FORUMS"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("FFAN_ACTIVE"),
		GetMessage("FFAN_GROUP_ID"),
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><b><?= GetMessage("FFAN_SITE_ID") ?>:</b></td>
		<td>
			<?echo CSite::SelectBox("filter_site_id", $filter_site_id, "(".GetMessage("FFAN_ALL").")"); ?>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("FFAN_ACTIVE") ?>:</td>
		<td>
			<select name="filter_active">
				<option value=""><?= htmlspecialcharsex("(".GetMessage("FFAN_ALL").")") ?></option>
				<option value="Y"<?if ($filter_active=="Y") echo " selected"?>><?= htmlspecialcharsex(GetMessage("FFAN_YES")) ?></option>
				<option value="N"<?if ($filter_active=="N") echo " selected"?>><?= htmlspecialcharsex(GetMessage("FFAN_NO")) ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("FFAN_GROUP_ID") ?>:</td>
		<td>
			<select name="filter_group_id">
				<option value="">(<?echo GetMessage("FFAN_ALL");?>)</option>
				<?
				$g = CForumGroup::GetListEx(
					array("SORT" => "ASC", "ID" => "ASC"),
					array("LID" => LANG)
				);
				while ($g->ExtractFields("g_")):
					?><option value="<?echo $g_ID?>"<?if (IntVal($filter_group_id)==IntVal($g_ID)) echo " selected"?>><?echo $g_NAME ?></option><?
				endwhile;
				?>
			</select>
		</td>
	</tr>
<?
$oFilter->Buttons(
	array(
		"table_id" => $sTableID,
		"url" => $APPLICATION->GetCurPage(),
		"form" => "find_form"
	)
);
$oFilter->End();
?>
</form>

<?
$lAdmin->DisplayList();
?>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>