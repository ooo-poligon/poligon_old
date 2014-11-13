<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002 Bitrix                  #
# http://www.bitrix.ru                       #
# mailto:admin@bitrix.ru                     #
##############################################
*/

define("STOP_STATISTICS", true);

if($_GET["admin_section"]=="Y")
	define("ADMIN_SECTION", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
$MAIN_RIGHT = $APPLICATION->GetGroupRight("main");
if($MAIN_RIGHT<"R") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$rsUsers = CUser::GetList($by, $order, array("ID" => $ID));
if ($arUser = $rsUsers->Fetch())
	$res = "[<a title='".GetMessage("MAIN_EDIT_USER_PROFILE")."' class='tablebodylink' href='/bitrix/admin/user_edit.php?ID=".$arUser["ID"]."&lang=".LANG."'>".$arUser["ID"]."</a>] (".htmlspecialchars($arUser["LOGIN"]).") ".htmlspecialchars($arUser["NAME"])." ".htmlspecialchars($arUser["LAST_NAME"]);
else
	$res = "&nbsp;".GetMessage("MAIN_NOT_FOUND");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");
?>
<script language="JavaScript">
<!--
window.parent.document.getElementById("div_<?echo $strName?>").innerHTML="<?echo $res?>";
//-->
</SCRIPT>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");?>