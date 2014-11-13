<?
//*****************************************************************************************************************
//	Управление темами - административная часть - список
//************************************!****************************************************************************
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/include.php");
	$forumModulePermissions = $APPLICATION->GetGroupRight("forum");
	if ($forumModulePermissions == "D")
		$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	IncludeModuleLangFile(__FILE__);
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/prolog.php");
//************************************Выборка форумов**************************************************************
	$db_Forum = CForumNew::GetListEx(array("SORT"=>"ASC", "NAME"=>"ASC"));
	
	$arr = array();	
	$arr["reference_id"][] = "";
	$arr["reference"][] = "";
	$arrForum = array(); 
	$arrSelect = "";
	while($dbForum = $db_Forum->Fetch())
	{
		$arrForum[$dbForum["ID"]] = htmlspecialcharsex($dbForum["NAME"]);
		$arrSelect .= "<option value='".$dbForum["ID"]."'>".htmlspecialcharsex($dbForum["NAME"])."</option>";
		$arr["reference_id"][] = $dbForum["ID"];
		$arr["reference"][] = htmlspecialcharsex($dbForum["NAME"]);
	}
//************************************!Инициализация фильтра*******************************************************
	$sTableID = "tbl_topic";
	$oSort = new CAdminSorting($sTableID, "ID", "asc");
	$lAdmin = new CAdminList($sTableID, $oSort);
	$lAdmin->InitFilter(array("FORUM_ID", "DATE_FROM", "DATE_TO", "CREATE_DATE_FROM", "CREATE_DATE_TO"));
//************************************!Проверка фильтров **********************************************************
	$arMsg = array();	
	$err = false;
	
	$date1_create_stm = "";
	$date1_create_stm = "";
	$date1_stm = "";
	$date2_stm = "";
	
	$CREATE_DATE_FROM = trim($CREATE_DATE_FROM); 
	$CREATE_DATE_TO = trim($CREATE_DATE_TO);
	$CREATE_DATE_FROM_DAYS_TO_BACK = intval($CREATE_DATE_FROM_DAYS_TO_BACK);
	if (strlen($CREATE_DATE_FROM)>0 || strlen($CREATE_DATE_TO)>0 || $CREATE_DATE_FROM_DAYS_TO_BACK>0)
	{
		$date1_create_stm = MkDateTime(ConvertDateTime($CREATE_DATE_FROM,"D.M.Y"),"d.m.Y");
		$date2_create_stm = MkDateTime(ConvertDateTime($CREATE_DATE_TO,"D.M.Y")." 23:59","d.m.Y H:i");

		if ($CREATE_DATE_FROM_DAYS_TO_BACK > 0)
		{
			$date1_create_stm = time()-86400*$CREATE_DATE_FROM_DAYS_TO_BACK;
			$date1_create_stm = GetTime($date1_create_stm);
		}
		if (!$date1_create_stm) 
			$arMsg[] = array("id"=>">=START_DATE", "text"=> GetMessage("FM_WRONG_DATE_CREATE_FROM"));
	
		if (!$date2_create_stm && strlen($CREATE_DATE_TO)>0) 
			$arMsg[] = array("id"=>"<=START_DATE", "text"=> GetMessage("FM_WRONG_DATE_CREATE_FROM"));
		elseif ($date1_create_stm && $date2_create_stm && ($date2_create_stm <= $date1_create_stm))
			$arMsg[] = array("id"=>"find_date_create_timestamp2", "text"=> GetMessage("SUP_FROM_TILL_DATE_TIMESTAMP"));
	}
	
	// LAST TOPIC
	$DATE_FROM = trim($DATE_FROM); 
	$DATE_TO = trim($DATE_TO);
	$DATE_FROM_DAYS_TO_BACK = intval($DATE_FROM_DAYS_TO_BACK);
	if (strlen($DATE_FROM)>0 || strlen($DATE_TO)>0 || $DATE_FROM_DAYS_TO_BACK>0)
	{
		$date1_stm = MkDateTime(ConvertDateTime($DATE_FROM,"D.M.Y"),"d.m.Y");
		$date2_stm = MkDateTime(ConvertDateTime($DATE_TO,"D.M.Y")." 23:59","d.m.Y H:i");

		if ($DATE_FROM_DAYS_TO_BACK > 0)
		{
			$date1_stm = time()-86400*$DATE_FROM_DAYS_TO_BACK;
			$date1_stm = GetTime($date1_stm);
		}
		if (!$date1_stm) 
			$arMsg[] = array("id"=>">=LAST_POST_DATE", "text"=> GetMessage("FM_WRONG_DATE_CREATE_FROM"));
	
		if (!$date2_stm && strlen($DATE_TO)>0) 
			$arMsg[] = array("id"=>"<=LAST_POST_DATE", "text"=> GetMessage("FM_WRONG_DATE_CREATE_FROM"));
		elseif ($date1_stm && $date2_stm && ($date2_stm <= $date1_stm))
			$arMsg[] = array("id"=>"find_date_timestamp2", "text"=> GetMessage("SUP_FROM_TILL_DATE_TIMESTAMP"));
	}
	
	$arFilter = array();
	$FORUM_ID = intval($FORUM_ID);
	if ($FORUM_ID>0)
		$arFilter = array("FORUM_ID" => $FORUM_ID);			
		
	if (strlen($date1_create_stm)>0)
		$arFilter = array_merge($arFilter, array(">=START_DATE" => $CREATE_DATE_FROM));
	if (strlen($date2_create_stm)>0)
		$arFilter = array_merge($arFilter, array("<=START_DATE"	=> $CREATE_DATE_TO));
		
	if (strlen($date1_stm)>0)
		$arFilter = array_merge($arFilter, array(">=LAST_POST_DATE" => $DATE_FROM));
	if (strlen($date2_stm)>0)
		$arFilter = array_merge($arFilter, array("<=LAST_POST_DATE"	=> $DATE_TO));
		
	if (!empty($arMsg))
	{
		$err = new CAdminException($arMsg);
		$lAdmin->AddFilterError($err->GetString()); 
	}

	
//************************************!Редактирование**************************************************************

if ($lAdmin->EditAction() && $forumModulePermissions >= "W")
{
	
	foreach ($FIELDS as $ID => $arFields)
	{
		$DB->StartTransaction();
		$ID = IntVal($ID);

		if (!$lAdmin->IsUpdated($ID))
			continue;

		if (!CForumTopic::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$lAdmin->AddUpdateError($ex->GetString(), $ID);
			else
				$lAdmin->AddUpdateError(GetMessage("FM_WRONG_UPDATE"), $ID);
			$DB->Rollback();
		}
		$DB->Commit();
	}
}
//************************************!Oбработка действий групповых и одиночных************************************
if($arID = $lAdmin->GroupAction())
{
	$candelete = false;
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = CForumTopic::GetList(array($by=>$order), $arFilter);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}
	if(check_bitrix_sessid())
	{
		foreach($arID as $ID)
		{
			if(strlen($ID)<=0)
				continue;
			$ID = intval($ID);
			
			switch($_REQUEST['action'])
			{
				case "delete": 
					$candelete = $USER->IsAdmin() ? true : CForumTopic::CanUserDeleteTopic($ID, $USER->GetUserGroupArray(), $USER->GetID());
	 				if ($candelete)
						CForumTopic::Delete($ID);
					else 
						$lAdmin->AddFilterError(GetMessage("FM_WRONG_NOT_RIGHT")); 	
					break;
				case "move": 
					if (IntVal($_REQUEST['move_to'])>0)
					{
						$DB->StartTransaction();
						if (CForumTopic::CanUserUpdateTopic($ID, $USER->GetUserGroupArray(), $USER->GetID()))
						{
							if (!CForumTopic::MoveTopic2Forum($ID, IntVal($_REQUEST['move_to'])))
							{
								if ($ex = $APPLICATION->GetException())
									$lAdmin->AddUpdateError($ex->GetString(), $ID);
								else
									$lAdmin->AddUpdateError(GetMessage("FM_WRONG_UPDATE"), $ID);
								$DB->Rollback();
							}
						}
						else 
							$lAdmin->AddFilterError(GetMessage("FM_WRONG_NOT_RIGHT")); 	
						$DB->Commit();
					}
					else 
						$lAdmin->AddFilterError(GetMessage("FM_WRONG_FORUM_ID")); 	
					break;
			}
		}
	}
}
	
	$rsData = CForumTopic::GetListEx(array($by=>$order), $arFilter);
	$rsData = new CAdminResult($rsData, $sTableID);
	$rsData->NavStart();
	$lAdmin->NavText($rsData->GetNavPrint(GetMessage("FM_TOPICS")));
	
//************************************!Заголовки*******************************************************************
	$lAdmin->AddHeaders(array(
		array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
		array("id"=>"TITLE", "content"=>GetMessage("FM_TITLE_NAME"), "sort"=>"TITLE", "default"=>true),
		array("id"=>"START_DATE","content"=>GetMessage("FM_TITLE_DATE_CREATE"), "sort"=>"START_DATE", "default"=>true),
		array("id"=>"USER_START_NAME","content"=>GetMessage("FM_TITLE_AUTHOR"), "sort"=>"USER_START_NAME", "default"=>true),
		array("id"=>"POSTS", "content"=>GetMessage("FM_TITLE_MESSAGES"),	"sort"=>"POSTS", "default"=>false),
		array("id"=>"VIEWS", "content"=>GetMessage("FM_TITLE_VIEWS"),  "sort"=>"VIEWS", "default"=>false),
		array("id"=>"FORUM_ID", "content"=>GetMessage("FM_TITLE_FORUM"),  "sort"=>"FORUM_NAME", "default"=>true),
		array("id"=>"LAST_POST_DATE", "content"=>GetMessage("FM_TITLE_LAST_MESSAGE"),  "sort"=>"LAST_POST_DATE", "default"=>false),
		));
//************************************!Построение списка***********************************************************
while ($arForum = $rsData->NavNext(true, "t_"))
{
	$row =& $lAdmin->AddRow($t_ID, $arForum);
	$bCanUpdateForum = CForumTopic::CanUserUpdateTopic($t_ID, $USER->GetUserGroupArray(), $USER->GetID());
	$bCanDeleteForum = CForumTopic::CanUserDeleteTopic($t_ID, $USER->GetUserGroupArray(), $USER->GetID());
	if (!$bCanUpdateForum && !$bCanDeleteForum)
		$row->bReadOnly = True;
	$row->AddField("ID", $t_ID);
	$row->AddInputField("TITLE", array("size" => "35"));
	$row->AddInputField("START_DATE", array("size" => "35"));
	$row->AddInputField("USER_START_NAME", array("size" => "35"));
	$row->AddField("POSTS", $t_POSTS);
	$row->AddInputField("VIEWS", array("size" => "35"));
	$row->AddSelectField("FORUM_ID", $arrForum);
	$row->AddInputField("LAST_POST_DATE", array("size" => "35"));
}
//************************************!"Подвал" списка*************************************************************
	$lAdmin->AddFooter(
		array(
			array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value" => $rsData->SelectedRowsCount()),
			array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
		)
	);
	$lAdmin->AddGroupActionTable(
		array(
			"delete" => GetMessage("FM_ACT_DELETE"),
			"move" => GetMessage("FM_ACT_MOVE"),
			"space" => array(
				"type" => "html",
				"value" => "&nbsp;"),
			"move_to" => array(
				"type" => "html",
				"value" => 
					"<select name=\"move_to\" id=\"move_to\" disabled>".$arrSelect."</select>".
					"<input type=\"hidden\" name=\"copy_to_site\" value=\"\">"
			)
		),
		array("select_onchange"=>"this.form.move_to.disabled=this.form.action.value=='move'? false : true;")
	);
	
		$lAdmin->AddAdminContextMenu();

//************************************!Проверка на вывод только списка (в случае списка, скрипт дальше выполняться не будет)
	$lAdmin->CheckListMode();
//************************************!Вывод страницы**************************************************************
	$APPLICATION->SetTitle(GetMessage("FORUM_TOPICS"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	$oFilter = new CAdminFilter(
		$sTableID."_filter",
		array(
			GetMessage("FM_TITLE_DATE_CREATE"),
			GetMessage("FM_TITLE_DATE_LAST_POST")
		)
	);
	?>
	<form name="form1" method="get" action="<?=$APPLICATION->GetCurPage()?>?">
	<?$oFilter->Begin();?>
	<tr valign="center">
		<td><b><?=GetMessage("FM_TITLE_FORUM")?>:</b></td>
		<td><?echo SelectBoxFromArray("FORUM_ID", $arr, $FORUM_ID)?></td>
	</tr>
	<tr valign="center">
		<td><?echo GetMessage("FM_TITLE_DATE_CREATE")." (".CLang::GetDateFormat("SHORT")."):"?></td>
		<td><?echo CalendarPeriod("CREATE_DATE_FROM", $CREATE_DATE_FROM, "CREATE_DATE_TO", $CREATE_DATE_TO, "form1","Y")?></td>
	</tr>
	<tr valign="center">
		<td><?echo GetMessage("FM_TITLE_DATE_LAST_POST")." (".CLang::GetDateFormat("SHORT")."):"?></td>
		<td><?echo CalendarPeriod("DATE_FROM", $DATE_FROM, "DATE_TO", $DATE_TO, "form1","Y")?></td>
	</tr>
	
	<?
	$oFilter->Buttons(array("table_id" => $sTableID,"url" => $APPLICATION->GetCurPage(),"form" => "find_form"));
	$oFilter->End();
	?>
	</form>
	<script language="JavaScript">
		function Select_Move()
		{
			var form = document.getElementById('form_tbl_topic');
			if (form.action == 'move')
				form.move_to.disabled = false;
			else 
				form.move_to.disabled = true;
			return;
		}
	</script>
	<?
	$lAdmin->DisplayList();
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>