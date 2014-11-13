<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$FID = IntVal($FID);
$MID = IntVal($MID);
if ($MESSAGE_TYPE!="EDIT") $MESSAGE_TYPE = "NEW";

if ($MESSAGE_TYPE=="EDIT" && $MID<=0)
{
	LocalRedirect("index.php");
	die();
}

$arForum = false;
if (CModule::IncludeModule("forum")):
	if ($SHOW_FORUM_DEBUG_INFO) $prexectime = getmicrotime();
	if ($MESSAGE_TYPE=="EDIT")
	{
		$arMessage = CForumMessage::GetByID($MID);
		if (!$arMessage)
		{
			LocalRedirect("index.php");
			die();
		}
		$FID = IntVal($arMessage["FORUM_ID"]);
		$TID = IntVal($arMessage["TOPIC_ID"]);
	}

	$arForum = CForumNew::GetByID($FID);
endif;

if (!$arForum)
{
	LocalRedirect("index.php");
	die();
}

if ($MESSAGE_TYPE=="NEW" && !CForumTopic::CanUserAddTopic($FID, $USER->GetUserGroupArray(), $USER->GetID()))
	$APPLICATION->AuthForm("You do not have enough permissions to create new topic in this forum");


if ($MESSAGE_TYPE=="EDIT" && !CForumMessage::CanUserUpdateMessage($MID, $USER->GetUserGroupArray(), IntVal($USER->GetID())))
	$APPLICATION->AuthForm("You do not have enough permissions to modify this message");

if ($SHOW_FORUM_DEBUG_INFO) $arForumDebugInfo[] = "<br><font color=\"#FF0000\">Initializing Variables: ".Round(getmicrotime()-$prexectime, 3)." sec</font>";


if ($SHOW_FORUM_DEBUG_INFO) $prexectime = getmicrotime();
$strErrorMessage = "";
$strOKMessage = "";
$bVarsFromForm = false;
if ($REQUEST_METHOD=="POST" && strlen($forum_post_action)>0)
{
	$arATTACH_IMG = $_FILES["ATTACH_IMG"];
	if ($MESSAGE_TYPE=="EDIT")
		$arATTACH_IMG["del"] = $ATTACH_IMG_del;

	$arFieldsG = array(
		"POST_MESSAGE" => $POST_MESSAGE,
		"AUTHOR_NAME" => $AUTHOR_NAME,
		"AUTHOR_EMAIL" => $AUTHOR_EMAIL,
		"USE_SMILES" => $USE_SMILES,
		"TITLE" => $TITLE,
		"DESCRIPTION" => $DESCRIPTION,
		"ICON_ID" => $ICON_ID,
		"ATTACH_IMG" => $arATTACH_IMG
		);
	$MID1 = ForumAddMessage($MESSAGE_TYPE, $FID, ($MESSAGE_TYPE=="NEW") ? 0 : IntVal($TID), ($MESSAGE_TYPE=="NEW") ? 0 : IntVal($MID), $arFieldsG, $strErrorMessage, $strOKMessage);
	$MID1 = IntVal($MID1);
	if ($MID1>0)
	{
		$MID = $MID1;
		if (!$SHOW_FORUM_DEBUG_INFO)
			LocalRedirect("read.php?FID=".$FID."&TID=".$TID."&MID=".$MID."#message".$MID);
	}
	else
		$bVarsFromForm = true;
}
if ($SHOW_FORUM_DEBUG_INFO) $arForumDebugInfo[] = "<br><font color=\"#FF0000\">Actions: ".Round(getmicrotime()-$prexectime, 3)." sec</font>";

$APPLICATION->AddChainItem($arForum["NAME"], "list.php?FID=".$FID);
$APPLICATION->SetTitle((($MESSAGE_TYPE=="NEW")?"New topic":"Modify message"));
$APPLICATION->SetAdditionalCSS("/bitrix/php_interface/".LANG."/forum.css");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_after.php");

$path2curdir = str_replace("\\\\", "/", dirname(__FILE__)."/");
if (file_exists($path2curdir."menu.php"))
	include($path2curdir."menu.php");
elseif (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/install/public/".LANG."/menu.php"))
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/install/public/".LANG."/menu.php");
else
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/install/public/en/menu.php");
?>

<?echo ShowMessage(array("MESSAGE" => $strErrorMessage, "TYPE" => "ERROR"));?>
<?echo ShowMessage(array("MESSAGE" => $strOKMessage, "TYPE" => "OK"));?>

<br>
<table width="100%" border="0" cellspacing="1" cellpadding="0" align="center" class="forumborder">
<tr><td>

<table width="100%" border="0" cellspacing="1" cellpadding="4">
<?
if (file_exists($path2curdir."post_form.php"))
	include($path2curdir."post_form.php");
elseif (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/install/public/".LANG."/post_form.php"))
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/install/public/".LANG."/post_form.php");
else
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/install/public/en/post_form.php");
?>
</table>

</td></tr>
</table>

<?
if ($SHOW_FORUM_DEBUG_INFO)
{
	for ($i = 0; $i < count($arForumDebugInfo); $i++)
		echo $arForumDebugInfo[$i];
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog.php");
?>