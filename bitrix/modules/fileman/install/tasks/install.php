<?
// *******************************************************************************************************
// Install new right system: operation and tasks
// *******************************************************************************************************
// ############ FILEMAN MODULE OPERATION ###########
$arFOp = Array();
$arFOp[] = Array('fileman_view_all_settings', 'fileman', '', 'module');
$arFOp[] = Array('fileman_edit_menu_types', 'fileman', '', 'module');
$arFOp[] = Array('fileman_add_element_to_menu', 'fileman', '', 'module');
$arFOp[] = Array('fileman_edit_menu_elements', 'fileman', '', 'module');
$arFOp[] = Array('fileman_edit_existent_files', 'fileman', '', 'module');
$arFOp[] = Array('fileman_edit_existent_folders', 'fileman', '', 'module');
$arFOp[] = Array('fileman_admin_files', 'fileman', '', 'module');
$arFOp[] = Array('fileman_admin_folders', 'fileman', '', 'module');
$arFOp[] = Array('fileman_view_permissions', 'fileman', '', 'module');
$arFOp[] = Array('fileman_edit_all_settings', 'fileman', '', 'module');
$arFOp[] = Array('fileman_upload_files', 'fileman', '', 'module');
$arFOp[] = Array('fileman_view_file_structure', 'fileman', '', 'module');
$arFOp[] = Array('fileman_install_control', 'fileman', '', 'module');


// ############ FILEMAN MODULE TASKS ###########
$arTasksF = Array();
$arTasksF[] = Array('fileman_denied', 'D', 'fileman', 'Y', '', 'module');
$arTasksF[] = Array('fileman_allowed_folders', 'F', 'fileman', 'Y', '', 'module');
$arTasksF[] = Array('fileman_view_all_settings', 'R', 'fileman', 'Y', '', 'module');
$arTasksF[] = Array('fileman_full_access', 'W', 'fileman', 'Y', '', 'module');


//Operations in Tasks
$arOInT = Array();
//FILEMAN: module
$arOInT['fileman_allowed_folders'] = Array(
	'fileman_view_file_structure',
	'fileman_add_element_to_menu',
	'fileman_edit_menu_elements',
	'fileman_edit_existent_files',
	'fileman_edit_existent_folders',
	'fileman_admin_files',
	'fileman_admin_folders',
	'fileman_view_permissions',
	'fileman_upload_files'
);

$arOInT['fileman_view_all_settings'] = Array(
	'fileman_view_file_structure',
	'fileman_view_all_settings',
	'fileman_add_element_to_menu',
	'fileman_edit_menu_elements',
	'fileman_edit_existent_files',
	'fileman_edit_existent_folders',
	'fileman_admin_files',
	'fileman_admin_folders',
	'fileman_view_permissions',
	'fileman_upload_files'
);

$arOInT['fileman_full_access'] = Array(
	'fileman_view_file_structure',
	'fileman_view_all_settings',
	'fileman_edit_menu_types',
	'fileman_add_element_to_menu',
	'fileman_edit_menu_elements',
	'fileman_edit_existent_files',
	'fileman_edit_existent_folders',
	'fileman_admin_files',
	'fileman_admin_folders',
	'fileman_view_permissions',
	'fileman_edit_all_settings',
	'fileman_upload_files',
	'fileman_install_control'
);


foreach($arFOp as $ar)
	$DB->Query("
		INSERT INTO b_operation
		(NAME,MODULE_ID,DESCRIPTION,BINDING)
		VALUES
		('".$ar[0]."','".$ar[1]."','".$ar[2]."','".$ar[3]."')
	", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

foreach($arTasksF as $ar)
	$DB->Query("
		INSERT INTO b_task
		(NAME,LETTER,MODULE_ID,SYS,DESCRIPTION,BINDING)
		VALUES
		('".$ar[0]."','".$ar[1]."','".$ar[2]."','".$ar[3]."','".$ar[4]."','".$ar[5]."')
	", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

// ############ b_group_task ###########
$sql_str = "
	INSERT INTO b_group_task
	(GROUP_ID,TASK_ID)
	SELECT MG.GROUP_ID, T.ID
	FROM
		b_task T
		INNER JOIN b_module_group MG ON MG.G_ACCESS = T.LETTER
	WHERE
		T.SYS = 'Y'
		AND T.BINDING = 'module'
		AND MG.MODULE_ID = 'fileman'
		AND T.MODULE_ID = MG.MODULE_ID
";
$z = $DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

// ############ b_task_operation ###########
foreach($arOInT as $tname => $arOp)
{
	$sql_str = "
		INSERT INTO b_task_operation
		(TASK_ID,OPERATION_ID)
		SELECT T.ID, O.ID
		FROM
			b_task T
			,b_operation O
		WHERE
			T.SYS='Y'
			AND T.NAME='".$tname."'
			AND O.NAME in ('".implode("','", $arOp)."')
	";
	$z = $DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
}
?>