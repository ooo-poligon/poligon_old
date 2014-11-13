<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (CModule::IncludeModule("forum")):
	if ($REQUEST_METHOD=="GET" && $ACTION=="SET_BE_READ")
	{
		ForumSetAllMessagesReaded(false);
	}
	ForumSetLastVisit();
endif;

define("FORUM_MODULE_PAGE", "INDEX");
$APPLICATION->SetTitle("Форумы");
$APPLICATION->SetAdditionalCSS("/bitrix/php_interface/".LANG."/forum.css");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_after.php");

if (CModule::IncludeModule("forum")):
//*******************************************************

if ($SHOW_FORUM_DEBUG_INFO) $prexectime = getmicrotime();
$path2curdir = str_replace("\\\\", "/", dirname(__FILE__)."/");
if (file_exists($path2curdir."menu.php"))
	include($path2curdir."menu.php");
elseif (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/install/public/".LANGUAGE_ID."/menu.php"))
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/install/public/".LANGUAGE_ID."/menu.php");
else
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

<p><font class="text"><?echo $db_Forum->NavPrint("Форумы")?></font></p>

<table width="99%" align="center" border="0" cellspacing="1" cellpadding="0" class="forumborder">
  <tr>
	<td>
	  <table width="100%" border="0" cellspacing="0" cellpadding="3" class="forumborder">
		<tr>
			<td width="100%" class="forumtitletext">&nbsp;<b>Список форумов</b></td>
		</tr>
	  </table>
	</td>
  </tr>
  <tr>
	<td>
	  <table width="100%" border="0" cellspacing="1" cellpadding="4">
		<tr class="forumhead">
			<td align="center" nowrap class="forumheadtext">

			</td>
			<td width="45%" nowrap class="forumheadtext">
				Название форума
			</td>
			<td width="14%" align="center" nowrap class="forumheadtext">
				Тем
			</td>
			<td width="7%" align="center" nowrap class="forumheadtext">
				Сообщений
			</td>
			<td width="27%" nowrap class="forumheadtext">
				Последнее сообщение
			</td>
		</tr>
	<?
	$currentGroupID = -1;
	while ($db_Forum->NavNext(true, "f_", false)):
		if ($currentGroupID != IntVal($f_FORUM_GROUP_ID))
		{
			if (IntVal($f_FORUM_GROUP_ID)>0)
			{
				$arCurForumGroup = CForumGroup::GetLangByID($f_FORUM_GROUP_ID, LANGUAGE_ID);
				?>
				<tr class="forumbody">
					<td class="forumbodytext" colspan="5">
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
					?><a href="read.php?FID=<?echo $f_ID;?>&TID=<?echo $FirstUnreadedTopicID?>&MID=<?echo $FirstUnreadedMessageID?>#message<?echo $FirstUnreadedMessageID?>"><img src="/bitrix/images/forum/f_norm.gif" width="18" height="12" alt="Есть новые сообщения!" border="0"></a><?
				}
				else
				{
					?><img src="/bitrix/images/forum/f_norm_no.gif" width="18" height="12" alt="Нет новых сообщений" border="0"><?
				}
				?>
			</td>
			<td class="forumbodytext" valign="top">
				<a href="list.php?FID=<?echo $f_ID;?>"><?echo $f_NAME;?></a><br>
				<?echo $f_DESCRIPTION?>
			</td>
			<td align="center" class="forumbodytext" valign="top">
				<?echo $f_TOPICS?>
			</td>
			<td align="center" class="forumbodytext" valign="top">
				<?echo $f_POSTS?>
			</td>
			<td class="forumbodytext" valign="top">
				<?if (strlen($f_LAST_POST_DATE)>0) echo $f_LAST_POST_DATE."<br>";?>
				<?if (strlen($f_TITLE)>0):?>
					тема: <a href="read.php?FID=<?echo $f_ID;?>&TID=<?echo $f_TID;?>&MID=<?echo $f_MID;?>#message<?echo $f_MID;?>"><?echo (strlen($f_TITLE)>23) ? substr($f_TITLE, 0, 20)."..." : $f_TITLE;?></a><br>
				<?endif;?>
				<?if (strlen($f_LAST_POSTER_NAME)>0):?>
					автор: <?echo (IntVal($f_LAST_POSTER_ID)>0)?"<a href=\"view_profile.php?UID=".$f_LAST_POSTER_ID."\">":""?><?echo $f_LAST_POSTER_NAME?><?echo (IntVal($f_LAST_POSTER_ID)>0)?"</a>":""?>
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

<table width="100%" border="0">
	<tr>
		<td align="left">
			<font class="text">
			<?echo $db_Forum->NavPrint("Форумы")?>
			</font>
		</td>
	</tr>
</table>
<br>

<!-- Покажем список пользователей, которые сейчас на сайте -->
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="forumborder"><tr><td>
<table border="0" cellpadding="1" cellspacing="1" width="100%">
	<tr class="forumhead">
		<td valign="top" class="forumtitletext">
			Сейчас на форуме пользователи
		</td>
	</tr>
	<tr class="forumbody">
		<td valign="top" class="forumbodytext">
		<?
		$boundary_time = 10*60;
		$boundary_date = Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG)), time()-$boundary_time);
		$db_cur_users = CForumUser::GetList(array("LAST_VISIT" => "DESC"), array(">=LAST_VISIT" => $boundary_date, "HIDE_FROM_ONLINE" => "N"));
		$b_need_comma = False;
		while ($ar_cur_users = $db_cur_users->Fetch())
		{
			if ($b_need_comma)
				echo ", ";

			$str_cur_name = "";
			if ($ar_cur_users["SHOW_NAME"]=="Y")
			{
				$str_cur_name = Trim($ar_cur_users["NAME"]);
				if (strlen($ar_cur_users["LAST_NAME"])>0)
				{
					if (strlen($str_cur_name)>0)
						$str_cur_name .= " ";
					$str_cur_name .= Trim($ar_cur_users["LAST_NAME"]);
				}
			}

			if (strlen($str_cur_name)<=0)
				$str_cur_name = $ar_cur_users["LOGIN"];

			?><a href="view_profile.php?UID=<?echo $ar_cur_users["USER_ID"] ?>" title="Профиль пользователя"><?
			echo $str_cur_name;
			?></a><?
			$b_need_comma = True;
		}
		if (!$b_need_comma)
		{
			?>нет<?
		}
		?>
		</td>
	</tr>
</table>
</td></tr></table>
<!-- Конец списока пользователей, которые сейчас на сайте -->

<br>

<!-- Покажем список пользователей, у которых сегодня день рождения -->
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="forumborder"><tr><td>
<table border="0" cellpadding="1" cellspacing="1" width="100%">
	<tr class="forumhead">
		<td valign="top" class="forumtitletext">
			Сегодня отмечают день рождения
		</td>
	</tr>
	<tr class="forumbody">
		<td valign="top" class="forumbodytext">
		<?
		$boundary_date = Date("m-d");
		$db_cur_users = CForumUser::GetList(array(), array("PERSONAL_BIRTHDAY_DATE" => $boundary_date, ">=USER_ID" => 1));
		$b_need_comma = False;
		while ($ar_cur_users = $db_cur_users->Fetch())
		{
			if ($b_need_comma)
				echo ", ";

			$str_cur_name = "";
			if ($ar_cur_users["SHOW_NAME"]=="Y")
			{
				$str_cur_name = Trim($ar_cur_users["NAME"]);
				if (strlen($ar_cur_users["LAST_NAME"])>0)
				{
					if (strlen($str_cur_name)>0)
						$str_cur_name .= " ";
					$str_cur_name .= Trim($ar_cur_users["LAST_NAME"]);
				}
			}

			if (strlen($str_cur_name)<=0)
				$str_cur_name = $ar_cur_users["LOGIN"];

			?><a href="view_profile.php?UID=<?echo $ar_cur_users["USER_ID"] ?>" title="Профиль пользователя"><?
			echo $str_cur_name;
			?></a><?
			$b_need_comma = True;
		}
		if (!$b_need_comma)
		{
			?>нет<?
		}
		?>
		</td>
	</tr>
</table>
</td></tr></table>
<!-- Конец списока пользователей, у которых сегодня день рождения -->

<br>
<center><font class="text">
<a href="index.php?ACTION=SET_BE_READ" title="Пометить все темы форумов как прочитанные">[Пометить как прочитанные]</a>
</font></center>

<?
if ($SHOW_FORUM_DEBUG_INFO):
	echo "<br><font color=\"#FF0000\">Making Page: ".Round(getmicrotime()-$prexectime, 3)." sec</font><br>";
endif;

//*******************************************************
else:
	?>
	<font class="text"><b>Модуль форума не установлен</b></font>
	<?
endif;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog.php");
?>