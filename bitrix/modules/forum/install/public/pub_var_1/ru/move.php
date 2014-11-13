<?
define("NEED_AUTH", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$FID = IntVal($FID);
$TID = IntVal($TID);
$newFID = IntVal($newFID);

$arTopic = false;
if (CModule::IncludeModule("forum")):
	if ($SHOW_FORUM_DEBUG_INFO) $prexectime = getmicrotime();
	$arTopic = CForumTopic::GetByID($TID);
endif;

if (!$arTopic)
{
	LocalRedirect("list.php?FID=".$FID);
	die();
}

$FID = IntVal($arTopic["FORUM_ID"]);
$arForum = CForumNew::GetByID($FID);
if (!$arForum)
{
	LocalRedirect("index.php");
	die();
}

if (ForumCurrUserPermissions($FID)<"Q")
	$APPLICATION->AuthForm("� ��� �� ���������� ���� ��� ����������� ���� ����");

if ($SHOW_FORUM_DEBUG_INFO) $arForumDebugInfo[] = "<br><font color=\"#FF0000\">Initializing Variables: ".Round(getmicrotime()-$prexectime, 3)." sec</font>";

if ($SHOW_FORUM_DEBUG_INFO) $prexectime = getmicrotime();
$strErrorMessage = "";
$strOKMessage = "";
$bVarsFromForm = false;
if ($REQUEST_METHOD=="POST" && $newFID>0 && $action=="move")
{
	if (ForumCurrUserPermissions($newFID)<"Q")
		$strErrorMessage .= "� ��� �� ���������� ���� �� ����� ����������. \n";

	if (strlen($strErrorMessage)<=0)
	{
		$res = CForumTopic::MoveTopic2Forum($TID, $newFID);
		if (!$res)
			$strErrorMessage .= "������ ����������� ����. \n";
	}

	if (strlen($strErrorMessage)>0)
	{
		$bVarsFromForm = true;
	}
	else
	{
		if (!$SHOW_FORUM_DEBUG_INFO)
			LocalRedirect("read.php?FID=".$newFID."&TID=".$TID);
	}
}
if ($SHOW_FORUM_DEBUG_INFO) $arForumDebugInfo[] = "<br><font color=\"#FF0000\">Actions: ".Round(getmicrotime()-$prexectime, 3)." sec</font>";

$APPLICATION->AddChainItem($arForum["NAME"], "read.php?FID=".$FID."&TID=".$TID);
$APPLICATION->SetTitle("����������� ���� � ������ �����");
$APPLICATION->SetAdditionalCSS("/bitrix/php_interface/".LANG."/forum.css");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_after.php");

include("menu.php");
?>

<?echo ShowMessage(array("MESSAGE" => $strErrorMessage, "TYPE" => "ERROR"));?>
<?echo ShowMessage(array("MESSAGE" => $strOKMessage, "TYPE" => "OK"));?>

<table width="99%" border="0" cellspacing="1" cellpadding="0" align="center" class="forumborder">
<tr><td>
	<table width="100%" border="0" cellspacing="1" cellpadding="4">
		<form method="POST">
		<tr>
			<td class="forumhead" colspan="2" align="center">
				<font class="forumheadtext"><b>������� ���� � ������ �����</b></font>
			</td>
		</tr>
		<tr>
			<td class="forumbody" align="right" width="40%">
				<font class="forumheadtext">��������� ���� � �����</font>
			</td>
			<td class="forumbody" align="left" width="60%">
				<font class="forumbodytext">
					<select name="newFID">
						<?
						$arFilter = array();
						if (!$USER->IsAdmin())
						{
							$arFilter["PERMS"] = array($USER->GetGroups(), 'M');
							$arFilter["ACTIVE"] = "Y";
						}
						$db_Forum = CForumNew::GetListEx(array("NAME"=>"ASC"), $arFilter);
						while ($db_Forum->NavNext(true, "f_")):
							if (IntVal($f_ID)!=$FID)
							{
								?><option value="<?echo $f_ID; ?>" <?if ($newFID==IntVal($f_ID)) echo "selected";?>><?echo $f_NAME; ?></option><?
							}
						endwhile;
						?>
					</select>
					<input type="hidden" name="action" value="move">
					<input type="hidden" name="TID" value="<?echo $TID; ?>">
					<input type="hidden" name="FID" value="<?echo $FID; ?>">
				</font>
			</td>
		</tr>
		<tr>
			<td class="forumhead" colspan="2" align="center">
				<font class="forumheadtext"><input type="submit" value="���������"></font>
			</td>
		</tr>
		</form>
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