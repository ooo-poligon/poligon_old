<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/include.php");

IncludeModuleLangFile(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight("subscribe");
if($POST_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$ID=intval($ID);

$APPLICATION->SetTitle(GetMessage("post_title"));

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("post_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("post_tab_title").$ID),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");

$post = CPosting::GetByID($ID);
if(($post_arr = $post->Fetch())):?>

<?
if($post_arr["ERROR_EMAIL"] <> "")
{
	$sFilterEmail = urlencode(str_replace(",", "|", $post_arr["ERROR_EMAIL"]));
	
	$aMenu = array(
		array(
			"TEXT"=>GetMessage("post_find_subscr"),
			"TITLE"=>GetMessage("post_find_subscr_title"),
			"LINK"=>"subscr_admin.php?find_email=".$sFilterEmail."&amp;set_filter=Y&amp;lang=".LANG,
			"LINK_PARAM"=>"target=\"_blank\"",
			"ICON"=>"btn_list",
		),
		array(
			"TEXT"=>GetMessage("post_find_user"),
			"TITLE"=>GetMessage("post_find_user_title"),
			"LINK"=>"user_admin.php?find_email=".$sFilterEmail."&amp;set_filter=Y&amp;lang=".LANG,
			"LINK_PARAM"=>"target=\"_blank\"",
			"ICON"=>"btn_list",
		)
	);
	$context = new CAdminContextMenu($aMenu);
	$context->Show();
}
?>

<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>

<?if($post_arr["SENT_BCC"] <> ""):?>
<tr class="heading">
	<td colspan="2">
	<?=GetMessage("post_good_addr")?>
	</td>
</tr>
<tr>
	<td colspan="2">
	<?echo htmlspecialchars(str_replace(",", ", ", $post_arr["SENT_BCC"]))?>
	</td>
</tr>
<tr>
	<td width="10%" nowrap><?echo GetMessage("post_total")?></td>
	<td width="90%"><b><?echo (substr_count($post_arr["SENT_BCC"], ",") + ($post_arr["SENT_BCC"]<>""? 1:0));?></b></td>
</tr>
<?endif;?>

<?if($post_arr["ERROR_EMAIL"] <> ""):?>
<tr class="heading">
	<td colspan="2">
	<?echo GetMessage("post_error_addr")?>
	</td>
</tr>
<tr>
	<td colspan="2">
	<?echo htmlspecialchars(str_replace(",", ", ", $post_arr["ERROR_EMAIL"]))?>
	</td>
</tr>
<tr>
	<td width="10%" nowrap><?echo GetMessage("post_total")?></td>
	<td width="90%"><b><?echo (substr_count($post_arr["ERROR_EMAIL"], ",") + ($post_arr["ERROR_EMAIL"]<>""? 1:0));?></b></td>
</tr>
<?endif;?>

<?
$tabControl->Buttons();
?>
<input type="button" name="Close" value="<?echo GetMessage("post_close")?>" OnClick="window.close()">
<?
$tabControl->End();
?>

<?else:
	CAdminMessage::ShowMessage(GetMessage("post_not_found"));
endif?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");?>