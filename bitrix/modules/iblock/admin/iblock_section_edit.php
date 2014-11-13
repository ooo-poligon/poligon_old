<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/iblock.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);

$strWarning="";
$bVarsFromForm = false;
$message = false;
$ID=IntVal($ID);
$IBLOCK_SECTION_ID=IntVal($IBLOCK_SECTION_ID);

$IBLOCK_ID = IntVal($IBLOCK_ID);

$BlockPerm = CIBlock::GetPermission($IBLOCK_ID);

$arIBlock = CIBlock::GetArrayByID($IBLOCK_ID);
if($arIBlock)
	$bBadBlock=($BlockPerm < "W");
else
	$bBadBlock=true;

if(!$bBadBlock)
{
	$arIBTYPE = CIBlockType::GetByIDLang((strlen($type)>0?$type:$arIBlock['IBLOCK_TYPE_ID']), LANG);
	if($arIBTYPE===false)
		$bBadBlock = true;
	else
		$type = $arIBlock['IBLOCK_TYPE_ID'];
}

if($bBadBlock)
{
	$APPLICATION->SetTitle($arIBTYPE["NAME"]);
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	if($bBadBlock):
	?>
	<?echo ShowError(GetMessage("IBLOCK_BAD_IBLOCK"));?>
	<a href="iblock_admin.php?lang=<?=LANG?>&amp;type=<?=urlencode($type)?>"><?echo GetMessage("IBLOCK_BACK_TO_ADMIN")?></a>
	<?
	endif;
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

if(!$arIBlock["SECTION_NAME"])
	$arIBlock["SECTION_NAME"] = $arIBTYPE["SECTION_NAME"]? $arIBTYPE["SECTION_NAME"]: GetMessage("IBLOCK_SECTION");

$urlSectionAdminPage = COption::GetOptionString("iblock","combined_list_mode")=="Y"?"iblock_list_admin.php":"iblock_section_admin.php";
$urlElementAdminPage = COption::GetOptionString("iblock","combined_list_mode")=="Y"?"iblock_list_admin.php":"iblock_element_admin.php";

$aTabs = array();
$aTabs[] = array("DIV" => "edit1", "TAB" => $arIBlock["SECTION_NAME"], "ICON"=>"iblock_section", "TITLE"=>$arIBlock["SECTION_EDIT"]);
$aTabs[] = array("DIV" => "edit2", "TAB" => GetMessage("IBSEC_E_TAB2"), "ICON"=>"iblock_section", "TITLE"=>GetMessage("IBSEC_E_TAB2_TITLE"));

//Add user fields tab only when there is fields defined or user has rights for adding new field
if(
	(count($USER_FIELD_MANAGER->GetUserFields("IBLOCK_".$IBLOCK_ID."_SECTION")) > 0) ||
	($USER_FIELD_MANAGER->GetRights("IBLOCK_".$IBLOCK_ID."_SECTION") >= "W")
)
{
	$aTabs[] = $USER_FIELD_MANAGER->EditFormTab("IBLOCK_".$IBLOCK_ID."._SECTION");
}

$tabControl = new CAdminTabControl("tabControl", $aTabs);

if($REQUEST_METHOD=="POST" && strlen($Update)>0 && check_bitrix_sessid())
{
	$DB->StartTransaction();
	$bs = new CIBlockSection;

	$arPICTURE = $HTTP_POST_FILES["PICTURE"];
	$arPICTURE["del"] = ${"PICTURE_del"};
	$arPICTURE["MODULE_ID"] = "iblock";

	$arDETAIL_PICTURE = $HTTP_POST_FILES["DETAIL_PICTURE"];
	$arDETAIL_PICTURE["del"] = ${"DETAIL_PICTURE_del"};
	$arDETAIL_PICTURE["MODULE_ID"] = "iblock";

	$arFields = Array(
		"ACTIVE"=>$ACTIVE,
		"IBLOCK_SECTION_ID"=>$IBLOCK_SECTION_ID,
		"IBLOCK_ID"=>$IBLOCK_ID,
		"NAME"=>$NAME,
		"SORT"=>$SORT,
		"CODE"=>$_POST["CODE"],
		"PICTURE"=>$arPICTURE,
		"DETAIL_PICTURE"=>$arDETAIL_PICTURE,
		"DESCRIPTION"=>$DESCRIPTION,
		"DESCRIPTION_TYPE"=>$DESCRIPTION_TYPE
		);

	$USER_FIELD_MANAGER->EditFormAddFields("IBLOCK_".$IBLOCK_ID."_SECTION", $arFields);

	if(COption::GetOptionString("iblock", "show_xml_id", "N")=="Y" && is_set($_POST, "XML_ID"))
		$arFields["XML_ID"] = $_POST["XML_ID"];

	if($ID>0)
	{
		$res = $bs->Update($ID, $arFields);
	}
	else
	{
		$ID = $bs->Add($arFields);
		$res = ($ID>0);
	}

	if(!$res)
	{
		$strWarning .= $bs->LAST_ERROR;
		$bVarsFromForm = true;
		$DB->Rollback();
		if($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("admin_lib_error"), $e);
	}
	else
	{
		CIBlockSection::ReSort($IBLOCK_ID);
		$DB->Commit();
		if(strlen($apply)<=0)
		{
			if(strlen($return_url)>0)
				LocalRedirect($return_url);
			else
				LocalRedirect("/bitrix/admin/".$urlSectionAdminPage."?lang=". LANG."&type=".urlencode($type)."&IBLOCK_ID=".$IBLOCK_ID."&find_section_section=".intval($find_section_section));
		}

		LocalRedirect("/bitrix/admin/iblock_section_edit.php?lang=". LANG."&type=".urlencode($type)."&IBLOCK_ID=".$IBLOCK_ID."&find_section_section=".intval($find_section_section)."&".$tabControl->ActiveTabParam().'&ID='.$ID);
	}
}

$str_ACTIVE="Y";
$str_DESCRIPTION_TYPE="text";
$str_SORT="500";
$str_IBLOCK_SECTION_ID = $IBLOCK_SECTION_ID;

$result = CIBlockSection::GetByID($ID);
if(!$result->ExtractFields("str_"))
	$ID=0;

if($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_iblock_section", "", "str_");

if($ID>0)
	$APPLICATION->SetTitle(/*$arIBTYPE["NAME"].": ".*/$arIBlock["NAME"].": ".$arIBTYPE["SECTION_NAME"].": ".GetMessage("IBLOCK_EDIT_TITLE"));
else
	$APPLICATION->SetTitle(/*$arIBTYPE["NAME"].": ".*/$arIBlock["NAME"].": ".$arIBTYPE["SECTION_NAME"].": ".GetMessage("IBLOCK_NEW_TITLE"));


if(intval($find_section_section)>0)
{
	$adminChain->AddItem(array("TEXT"=>htmlspecialcharsex($arIBlock["NAME"]), "LINK"=>$urlSectionAdminPage.'?type='.htmlspecialchars($type).'&amp;lang='.LANG.'&amp;IBLOCK_ID='.$IBLOCK_ID.'&amp;find_section_section=0'));
	$nav = CIBlockSection::GetNavChain($IBLOCK_ID, IntVal($find_section_section));
	while($nav->ExtractFields("nav_"))
	{
		$last_nav = $urlSectionAdminPage."?lang=".LANG."&type=".$type."&IBLOCK_ID=".$IBLOCK_ID."&find_section_section=".$nav_ID;
		$adminChain->AddItem(array("TEXT"=>$nav_NAME, "LINK"=>$last_nav));
	}
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT"=>$arIBlock["SECTIONS_NAME"],
		"LINK"=>$urlSectionAdminPage."?type=".htmlspecialchars($type)."&lang=".LANG."&IBLOCK_ID=".$IBLOCK_ID."&find_section_section=".intval($find_section_section),
		"ICON"=>"btn_list",
	)
);

if($ID>0)
{
	$aMenu[] = array("SEPARATOR"=>"Y");
	$aMenu[] = array(
		"TEXT"=>$arIBlock["SECTION_ADD"],
		"LINK"=>"iblock_section_edit.php?type=".htmlspecialchars($type)."&lang=".LANG."&IBLOCK_ID=".$IBLOCK_ID."&find_section_section=".intval($find_section_section)."&IBLOCK_SECTION_ID=".htmlspecialchars($IBLOCK_SECTION_ID>0?$IBLOCK_SECTION_ID:$find_section_section),
		"ICON"=>"btn_new",
	);

	$aMenu[] = array(
		"TEXT"=>$arIBlock["SECTION_DELETE"],
		"LINK"=>"javascript:if(confirm('".GetMessage("IBLOCK_CONFIRM_DEL_MESSAGE")."'))window.location='".$urlSectionAdminPage."?ID[]=".IntVal($ID)."&".bitrix_sessid_get()."&action=delete&lang=".LANG."&type=".htmlspecialchars($type)."&IBLOCK_ID=".$IBLOCK_ID."&find_section_section=".intval($find_section_section)."';",
		"ICON"=>"btn_delete",
	);
}

$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?
if($strWarning)
	CAdminMessage::ShowOldStyleError($strWarning."<br>");
elseif($message)
	echo $message->Show();
?>
<form method="POST" action="/bitrix/admin/iblock_section_edit.php?type=<?echo $type?>&amp;lang=<?echo LANG?>&amp;IBLOCK_ID=<?echo $IBLOCK_ID?>" ENCTYPE="multipart/form-data" name="post_form">
<?=bitrix_sessid_post()?>
<?echo GetFilterHiddens("find_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="ID" value="<?echo $ID?>">
<?if(strlen($return_url)>0):?><input type="hidden" name="return_url" value="<?=htmlspecialchars($return_url)?>"><?endif?>
<?
	$tabControl->Begin();
	$tabControl->BeginNextTab();
?>
	<?if($ID>0):?>
	<tr>
		<td width="40%">ID:</td>
		<td width="60%"><?echo $str_ID?></td>
	</tr><?
	if(strlen($str_DATE_CREATE) > 0):
	?>
		<tr>
			<td width="40%"><?echo GetMessage("IBLOCK_CREATED")?></td>
			<td width="60%"><?echo $str_DATE_CREATE?><?
			if(intval($str_CREATED_BY)>0):
				?>&nbsp;&nbsp;&nbsp;[<a href="user_edit.php?lang=<?=LANG?>&amp;ID=<?=$str_CREATED_BY?>"><?echo $str_CREATED_BY?></a>]<?
				$rsUser = CUser::GetByID($str_CREATED_BY);
				$arUser = $rsUser->Fetch();
				if($arUser):
					echo "&nbsp;(".$arUser["LOGIN"].") ".$arUser["NAME"]." ".$arUser["LAST_NAME"];
				endif;
			endif;
			?></td>
		</tr>
	<?endif;?>
	<tr>
		<td width="40%"><?echo GetMessage("IBLOCK_LAST_UPDATE")?></td>
		<td width="60%"><?echo $str_TIMESTAMP_X?><?
		if(intval($str_MODIFIED_BY)>0):
			?>&nbsp;&nbsp;&nbsp;[<a href="user_edit.php?lang=<?=LANG?>&amp;ID=<?=$str_MODIFIED_BY?>"><?echo $str_MODIFIED_BY?></a>]<?
			if(intval($str_CREATED_BY) != intval($str_MODIFIED_BY))
			{
				$rsUser = CUser::GetByID($str_MODIFIED_BY);
				$arUser = $rsUser->Fetch();
			}
			if($arUser):
				echo "&nbsp;(".$arUser["LOGIN"].") ".$arUser["NAME"]." ".$arUser["LAST_NAME"];
			endif;
		endif?></td>
	</tr>
	<?endif;?>
	<tr>
		<td width="40%"><?echo GetMessage("IBLOCK_ACTIVE")?></td>
		<td width="60%">
			<input type="checkbox" name="ACTIVE" value="Y"<?if($str_ACTIVE=="Y")echo " checked"?>>
		</td>
	</tr>
	<tr>
		<td ><?echo GetMessage("IBLOCK_PARENT_SECTION")?></td>
		<td>
		<?$l = CIBlockSection::GetTreeList(Array("IBLOCK_ID"=>$IBLOCK_ID));?>
		<select name="IBLOCK_SECTION_ID" >
			<option value="0"><?echo GetMessage("IBLOCK_CONTENT")?></option>
		<?
			while($a = $l->Fetch()):
				?><option value="<?echo intval($a["ID"])?>"<?if($str_IBLOCK_SECTION_ID==$a["ID"])echo " selected"?>><?echo str_repeat(".", $a["DEPTH_LEVEL"])?><?echo htmlspecialchars($a["NAME"])?></option><?
			endwhile;
		?>
		</select>
		</td>
	</tr>
	<tr>
		<td  ><span class="required">*</span><?echo GetMessage("IBLOCK_NAME")?></td>
		<td >
			<input type="text" name="NAME" size="50"  maxlength="255" value="<?echo $str_NAME?>">
		</td>
	</tr>
	<tr>
		<td valign="top" ><?echo GetMessage("IBLOCK_PICTURE")?></td>
		<td valign="top">
			<?echo CFile::InputFile("PICTURE", 20, $str_PICTURE);?><br>
			<?echo CFile::ShowImage($str_PICTURE, "border=0", "", 200, 200, true)?>

		</td>
	</tr>
	<tr  class="heading">
		<td colspan="2"><?echo GetMessage("IBLOCK_DESCRIPTION")?></td>
	</tr>

	<?if(COption::GetOptionString("iblock", "use_htmledit", "Y")=="Y" && CModule::IncludeModule("fileman")):?>
	<tr>
		<td colspan="2" align="center">
			<?CFileMan::AddHTMLEditorFrame("DESCRIPTION", $str_DESCRIPTION, "DESCRIPTION_TYPE", $str_DESCRIPTION_TYPE, 300, "N", 0, "", "", $arIBlock["LID"]);?>
		</td>
	</tr>
	<?else:?>
	<tr>
		<td  ><?echo GetMessage("IBLOCK_DESC_TYPE")?></td>
		<td >
			<input type="radio" name="DESCRIPTION_TYPE" id="DESCRIPTION_TYPE_text" value="text"<?if($str_DESCRIPTION_TYPE!="html")echo " checked"?>> <label for="DESCRIPTION_TYPE_text"><?echo GetMessage("IBLOCK_DESC_TYPE_TEXT")?></label> /
			<input type="radio" name="DESCRIPTION_TYPE" id="DESCRIPTION_TYPE_html" value="html"<?if($str_DESCRIPTION_TYPE=="html")echo " checked"?>> <label for="DESCRIPTION_TYPE_html"><?echo GetMessage("IBLOCK_DESC_TYPE_HTML")?></label>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<textarea cols="60" rows="15"  name="DESCRIPTION" style="width:100%"><?echo $str_DESCRIPTION?></textarea>
		</td>
	</tr>
	<?endif?>
<?$tabControl->BeginNextTab();?>
	<tr>
		<td  ><?echo GetMessage("IBLOCK_SORT")?></td>
		<td >
			<input type="text" name="SORT" size="7"  maxlength="10" value="<?echo $str_SORT?>">
		</td>
	</tr>
	<?if(COption::GetOptionString("iblock", "show_xml_id", "N")=="Y"):?>
	<tr>
		<td  ><?echo GetMessage("IBLOCK_EXTERNAL_CODE")?></td>
		<td >
			<input type="text" name="XML_ID" size="20"  maxlength="255" value="<?echo $str_XML_ID?>">
		</td>
	</tr>
	<?endif?>
	<tr>
		<td  ><?echo GetMessage("IBLOCK_CODE")?></td>
		<td >
			<input type="text" name="CODE" size="20"  maxlength="255" value="<?echo $str_CODE?>">
		</td>
	</tr>
	<tr>
		<td valign="top" ><?echo GetMessage("IBLOCK_SECTION_DETAIL_PICTURE")?></td>
		<td valign="top">
			<?echo CFile::InputFile("DETAIL_PICTURE", 20, $str_DETAIL_PICTURE);?><br>
			<?echo CFile::ShowImage($str_DETAIL_PICTURE, "border=0", "", 200, 200, true)?>

		</td>
	</tr>
<?
//Add user fields tab only when there is fields defined or user has rights for adding new field
if(
	(count($USER_FIELD_MANAGER->GetUserFields("IBLOCK_".$IBLOCK_ID."_SECTION")) > 0) ||
	($USER_FIELD_MANAGER->GetRights("IBLOCK_".$IBLOCK_ID."_SECTION") >= "W")
)
{
	$tabControl->BeginNextTab();
	$USER_FIELD_MANAGER->EditFormShowTab("IBLOCK_".$IBLOCK_ID."_SECTION", $bVarsFromForm, $ID);
}
?>
<?
	if(strlen($return_url)>0)
		$bu = $return_url;
	else
		$bu = "/bitrix/admin/".$urlSectionAdminPage."?lang=". LANG."&type=".htmlspecialchars($type)."&IBLOCK_ID=".$IBLOCK_ID."&find_section_section=".intval($find_section_section);

	$tabControl->Buttons(array("disabled"=>false, "back_url"=>$bu));
	$tabControl->End();
?>
</form>
<?
$tabControl->ShowWarnings("post_form", $message);
?>
<?
if($BlockPerm >= "X" && (!defined('BX_PUBLIC_MODE') || BX_PUBLIC_MODE != 1))
{
	echo
		BeginNote(),
		GetMessage("IBSEC_E_IBLOCK_MANAGE_HINT"),
		' <a href="iblock_edit.php?type='.htmlspecialchars($type).'&amp;lang='.LANG.'&amp;ID='.$IBLOCK_ID.'&amp;admin=Y&amp;return_url='.urlencode("iblock_section_edit.php?ID=".$ID."&lang=".LANG. "&type=".htmlspecialchars($type)."&IBLOCK_ID=".$IBLOCK_ID."&find_section_section=".intval($find_section_section).(strlen($return_url)>0?"&return_url=".UrlEncode($return_url):"")).'">',
		GetMessage("IBSEC_E_IBLOCK_MANAGE_HINT_HREF"),
		'</a>',
		EndNote()
	;
}
?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
