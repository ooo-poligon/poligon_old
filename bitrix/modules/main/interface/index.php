<?
require_once(dirname(__FILE__)."/../include/prolog_admin_before.php");
IncludeModuleLangFile(__FILE__);

$adminPage->Init();
$adminMenu->Init($adminPage->aModules);

if(empty($adminMenu->aGlobalMenu))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

if(isset($_REQUEST["show_mode"]))
{
	$_SESSION["ADMIN_I_SHOW_MODE"] = $_REQUEST["show_mode"];
	CUserOptions::SetOption("view_mode", "index", $_SESSION["ADMIN_I_SHOW_MODE"]);
}
elseif(!isset($_SESSION["ADMIN_I_SHOW_MODE"]))
	$_SESSION["ADMIN_I_SHOW_MODE"] = CUserOptions::GetOption("view_mode", "index");

if(!in_array($_SESSION["ADMIN_I_SHOW_MODE"], array("icon", "list")))
	$_SESSION["ADMIN_I_SHOW_MODE"] = "icon";

$APPLICATION->SetAdditionalCSS("/bitrix/themes/".ADMIN_THEME_ID."/index.css");
$APPLICATION->SetTitle(GetMessage("admin_index_title"));
if($_REQUEST["mode"] <> "list"):
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	
	$vendor = COption::GetOptionString("main", "vendor", "1c_bitrix");
?>

<?echo BeginNote('width="100%"');?>
<?echo GetMessage("admin_index_project")?><?if(($s = COption::GetOptionString("main", "site_name", "")) <> "") echo " &quot;<b>".$s."</b>&quot;"?>.<br>
<div class="empty" style="height:4px"></div>
<?echo GetMessage("admin_index_product")?><?echo " &quot;".GetMessage("admin_index_product_name_".$vendor); if($adminPage->userMainRight >= "R") echo " ".SM_VERSION?>&quot;.<br>
<?echo EndNote();?>

<?
	echo '<div id="index_page_result_div">';
else:
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
endif; //$_REQUEST["mode"] <> "list"

$page = $GLOBALS["APPLICATION"]->GetCurPage();
$param = DeleteParam(array("show_mode", "mode"));
$aContext = array(
	array(
		"TEXT"=>GetMessage("admin_lib_index_view"),
		"TITLE"=>GetMessage("admin_lib_index_view_title"),
		"MENU"=>array(
			array(
				"ICON"=>($_SESSION["ADMIN_I_SHOW_MODE"] == "icon"? "checked":""),
				"TEXT"=>GetMessage("admin_lib_index_view_icon"),
				"TITLE"=>GetMessage("admin_lib_index_view_icon_title"),
				"ACTION"=>"jsUtils.LoadPageToDiv('".$page."?show_mode=icon&mode=list".($param<>""? "&".$param:"")."', 'index_page_result_div');"
			),
			array(
				"ICON"=>($_SESSION["ADMIN_I_SHOW_MODE"] == "list"? "checked":""),
				"TEXT"=>GetMessage("admin_lib_index_view_list"),
				"TITLE"=>GetMessage("admin_lib_index_view_list_title"),
				"ACTION"=>"jsUtils.LoadPageToDiv('".$page."?show_mode=list&mode=list".($param<>""? "&".$param:"")."', 'index_page_result_div');"
			),
		),
	),
);
$context = new CAdminContextMenu($aContext);
$context->Show();
?>

<table cellpadding="0" cellspacing="0" border="0" width="100%">
<?
$i=0;
foreach($adminMenu->aGlobalMenu as $menu):
?>
<?if($i>0):?>
	<tr>
		<td><div class="section-line">&nbsp;</div></td>
		<td></td>
	</tr>
<?endif;?>
	<tr valign="top">
		<td align="center" class="section-container">
			<a href="<?echo $menu["url"]?>" title="<?echo $menu["title"]?>">
				<div class="section-icon" id="<?echo $menu["index_icon"]?>"></div>
				<div class="section-text"><?echo $menu["text"]?></div>
			</a>
		</td>
		<td class="items-container">
<?
foreach($menu["items"] as $submenu):
	if($_SESSION["ADMIN_I_SHOW_MODE"] == "list"):
?>
<div class="item-container">
<?if($submenu["url"] <> ""):?>
	<a href="<?echo $submenu["url"]?>" title="<?echo $submenu["title"]?>"><div class="item-icon" id="<?echo $submenu["icon"]?>"></div></a>
	<div class="item-block"><a href="<?echo $submenu["url"]?>" title="<?echo $submenu["title"]?>"><?echo $submenu["text"]?></a></div>
<?else:?>
	<div class="item-icon" id="<?echo $submenu["icon"]?>"></div>
	<div class="item-block"><?echo $submenu["text"]?></div>
<?endif?>
</div>
<?
	else: //icon
?>
<div class="icon-container" align="center">
<?if($submenu["url"] <> ""):?>
	<a href="<?echo $submenu["url"]?>" title="<?echo $submenu["title"]?>">
		<div class="icon-icon" id="<?echo $submenu["page_icon"]?>"></div>
		<div class="icon-text"><?echo $submenu["text"]?></div>
	</a>
<?else:?>
		<div class="icon-icon" id="<?echo $submenu["page_icon"]?>"></div>
		<div class="icon-text"><?echo $submenu["text"]?></div>
<?endif;?>
</div>
<?
	endif;
endforeach;
?>
		</td>
	</tr>
<?
	$i++;
endforeach;
?>
</table>
<?
if($_REQUEST["mode"] <> "list")
	echo '</div>';
else
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
?>
<br>
<?
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
?>