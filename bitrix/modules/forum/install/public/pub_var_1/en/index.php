<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->SetTitle("Forums");
$APPLICATION->SetAdditionalCSS("/bitrix/php_interface/".LANG."/forum.css");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_after.php");

if (CModule::IncludeModule("forum")):
//*******************************************************

if ($SHOW_FORUM_DEBUG_INFO) $prexectime = getmicrotime();

$bSimplePanel = false;
if(!@include("menu.php"))
	if(!@include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/install/public/".LANG."/menu.php"))
		include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/install/public/en/menu.php");

$arFilter = array();
if (!$USER->IsAdmin())
{
	$arFilter["LID"] = LANG;
	$arFilter["PERMS"] = array($USER->GetGroups(), 'A');
	$arFilter["ACTIVE"] = "Y";
}
$db_Forum = CForumNew::GetListEx(array("FORUM_GROUP_SORT"=>"ASC", "FORUM_GROUP_ID"=>"ASC", "SORT"=>"ASC", "NAME"=>"ASC"), $arFilter);
$db_Forum->NavStart($FORUMS_PER_PAGE);
?>

<p class="text"><?echo $db_Forum->NavPrint("Forums")?></p>

<table width="100%" border="0" cellspacing="1" cellpadding="0" class="forumborder">
  <tr>
	<td>
	  <table width="100%" border="0" cellspacing="1" cellpadding="4">
		<tr valign="top" class="forumhead">
			<td align="center" nowrap class="forumheadtext">
				&nbsp;
			</td>
			<td class="forumheadtext" nowrap>
				Forum title
			</td>
			<td align="center" class="forumheadtext" nowrap>
				Topics
			</td>
			<td align="center" class="forumheadtext" nowrap>
				Replies
			</td>
			<td class="forumheadtext" nowrap>
				Last Post Info
			</td>
		</tr>
	<?
	$currentGroupID = -1;
	while ($db_Forum->NavNext(true, "f_", false)):
		if ($currentGroupID != IntVal($f_FORUM_GROUP_ID))
		{
			if (IntVal($f_FORUM_GROUP_ID)>0)
			{
				$arCurForumGroup = CForumGroup::GetLangByID($f_FORUM_GROUP_ID, LANG);
				if($currentGroupID == -1):
				?>
				<tr class="forumbody">
					<td class="forumbodytext" colspan="5"><img src="/bitrix/images/1.gif" width="1" height="1" alt=""></td>
				</tr>
				<?endif;?>
				<tr class="forumgrouphead">
					<td class="forumgroupheadtext" colspan="5">
						<b><?echo htmlspecialcharsex($arCurForumGroup["NAME"]);?></b>
						<?if (strlen($arCurForumGroup["DESCRIPTION"])>0):?>
							<br><?echo htmlspecialcharsex($arCurForumGroup["DESCRIPTION"]);?>
						<?endif;?>
					</td>
				</tr>
				<?
			}
			$currentGroupID = IntVal($f_FORUM_GROUP_ID);
		}
		list($FirstUnreadedTopicID, $FirstUnreadedMessageID) = CForumMessage::GetFirstUnreadEx($f_ID, 0, $USER->GetUserGroupArray());
		?>
		<tr class="forumbody">
			<td align="center" class="forumbodytext" valign="top">
				<?
				if ($FirstUnreadedMessageID>0)
				{
					?><a href="read.php?FID=<?echo $f_ID;?>&TID=<?echo $FirstUnreadedTopicID?>&MID=<?echo $FirstUnreadedMessageID?>#message<?echo $FirstUnreadedMessageID?>"><img src="/bitrix/images/forum/f_norm.gif" width="17" height="11" alt="New posts!" border="0" vspace="3"></a><?
				}
				else
				{
					?><img src="/bitrix/images/forum/f_norm_no.gif" width="17" height="11" alt="No new posts" border="0" vspace="3"><?
				}
				?><br>
			</td>
			<td class="forumbodytext" valign="top">
				<a href="list.php?FID=<?echo $f_ID;?>"><?echo $f_NAME;?></a><?if($FirstUnreadedMessageID>0):?><font class="forumnew"> (new)</font><?endif?><br>
				<?echo $f_DESCRIPTION?>
			</td>
			<td align="right" class="forumbodytext" valign="top">
				<?echo $f_TOPICS?>
			</td>
			<td align="right" class="forumbodytext" valign="top">
				<?echo $f_POSTS?>
			</td>
			<td class="forumbodytext" valign="top">
				<?if (strlen($f_LAST_POST_DATE)>0) echo $f_LAST_POST_DATE."<br>";?>
				<?if (strlen($f_TITLE)>0):?>
					<font class="forumheadcolor">topic:</font> <a href="read.php?FID=<?echo $f_ID;?>&TID=<?echo $f_TID;?>&MID=<?echo $f_MID;?>#message<?echo $f_MID;?>"><?echo (strlen($f_TITLE)>23) ? substr($f_TITLE, 0, 20)."..." : $f_TITLE;?></a><br>
				<?endif;?>
				<?if (strlen($f_LAST_POSTER_NAME)>0):?>
					<font class="forumheadcolor">author:</font> <?echo (IntVal($f_LAST_POSTER_ID)>0)?"<a href=\"view_profile.php?UID=".$f_LAST_POSTER_ID."\">":""?><?echo $f_LAST_POSTER_NAME?><?echo (IntVal($f_LAST_POSTER_ID)>0)?"</a>":""?>
				<?endif;?>
			</td>
		</tr>
		<?
	endwhile;
	?>

	  </table>
	</td>
  </tr>
</table>

<p class="text"><?echo $db_Forum->NavPrint("Forums")?></p>

<?if(!$USER->IsAuthorized()):?>
<table border="0" cellspacing="0" cellpadding="1" class="tableborder">
<form name="form_auth" method="post" target="_top" action="/forum/forum_auth.php?back_url=%2Fforum%2Findex.php">
	<tr valign="top" align="center">
		<td>
			<table width="100%" border="0" cellspacing="0" cellpadding="3" class="tablebody">
				<tr valign="middle"> 
					<td class="tablebody" style="padding-right:0px; padding-left:5px;"><font class="text">Login:</font></td>
					<td class="tablebody">
<input type="text" name="USER_LOGIN" maxlength="50" size="13" value="<?echo htmlspecialchars(${COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_LOGIN"})?>">
					</td>
					<td class="tablebody" style="padding-right:0px; padding-left:5px;"><font class="text">Password:</font></td>
					<td class="tablebody">
<input type="password" name="USER_PASSWORD" maxlength="50" size="13">
					</td>
					<td class="tablebody">
<input type="hidden" name="AUTH_FORM" value="Y">
<input type="hidden" name="TYPE" value="AUTH">
<input type="submit" name="Login" value="Login">
					</td>
				</tr>
			</table>
		</td>
	</tr>
</form>
</table>
<?endif;?>
<?
if ($SHOW_FORUM_DEBUG_INFO):
	echo "<br><font color=\"#FF0000\">Making Page: ".Round(getmicrotime()-$prexectime, 3)." sec</font><br>";
endif;

//*******************************************************
else:
	?>
	<font class="text"><b>Forum module is not installed</b></font>
	<?
endif;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog.php");
?>