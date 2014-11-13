<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

function MyForumShowTopicPages($nMessages, $strUrl, $pagen_var = "PAGEN_1")
{
	global $FORUM_MESSAGES_PER_PAGE;

	$res_str = "";
	if($nMessages > $FORUM_MESSAGES_PER_PAGE)
	{
		$res_str .= " Стр. ";

		$nPages = IntVal(ceil($nMessages / $FORUM_MESSAGES_PER_PAGE));
		$typeDots = true;
		for ($i = 1; $i <= $nPages; $i++)
		{
			if ($i<=3 || $i>=$nPages-2)
			{
				$res_str .= "<a href=\"".$strUrl."&".$pagen_var."=".$i."\">".$i."</a> ";
			}
			elseif ($typeDots)
			{
				$res_str .= "... ";
				$typeDots = false;
			}
		}
	}
	return $res_str;
}

// Let's init $FID (forum id) with actual and coordinated value
$FID = IntVal($FID);
$arForum = false;
if (CModule::IncludeModule("forum")):
	if ($SHOW_FORUM_DEBUG_INFO) $prexectime = getmicrotime();
	$arForum = CForumNew::GetByID($FID);
endif;

if (!$arForum)
{
	LocalRedirect("index.php");
	die();
}

// Let's check if current user can can view this forum
if (!CForumNew::CanUserViewForum($FID, $USER->GetUserGroupArray()))
	$APPLICATION->AuthForm("Для входа в закрытый форум введите ваши логин и пароль");

// Let's init read labels
CForumNew::InitReadLabels($FID, $USER->GetUserGroupArray());

if ($SHOW_FORUM_DEBUG_INFO) $arForumDebugInfo[] = "<br><font color=\"#FF0000\">Initializing Variables: ".Round(getmicrotime()-$prexectime, 3)." sec</font>";
if ($SHOW_FORUM_DEBUG_INFO) $prexectime = getmicrotime();

$strErrorMessage = "";
$strOKMessage = "";

// ACTIONS: subscribe
if($_SERVER["REQUEST_METHOD"]=="GET" && $ACTION=="FORUM_SUBSCRIBE" && IntVal($FID)>0)
{
	if (ForumSubscribeNewMessages($FID, 0, $strErrorMessage, $strOKMessage))
		LocalRedirect("subscr_list.php?FID=".$FID);
}
// ACTIONS: mark read
if($_SERVER["REQUEST_METHOD"]=="GET" && $ACTION=="SET_BE_READ" && IntVal($FID)>0)
	ForumSetAllMessagesReaded($FID);

if ($SHOW_FORUM_DEBUG_INFO) $arForumDebugInfo[] = "<br><font color=\"#FF0000\">Actions: ".Round(getmicrotime()-$prexectime, 3)." sec</font>";
// End of ACTIONS

$APPLICATION->AddChainItem($arForum["NAME"], "list.php?FID=".$FID);
$APPLICATION->SetTitle("Форум: ".$arForum["NAME"]);
$APPLICATION->SetAdditionalCSS("/bitrix/php_interface/".LANG."/forum.css");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_after.php");

$bSimplePanel = false;
include("menu.php");
?>

<?echo ShowMessage(array("MESSAGE" => $strErrorMessage, "TYPE" => "ERROR"));?>
<?echo ShowMessage(array("MESSAGE" => $strOKMessage, "TYPE" => "OK"));?>

<?
if ($SHOW_FORUM_DEBUG_INFO) $prexectime = getmicrotime();
if (strlen($ORDER_BY)<=0) $ORDER_BY = $arForum["ORDER_BY"];
if (strlen($ORDER_DIRECTION)<=0) $ORDER_DIRECTION = $arForum["ORDER_DIRECTION"];

if ($ORDER_BY=="T")
	$strOrderBy = "TITLE";
elseif ($ORDER_BY=="N")
	$strOrderBy = "POSTS";
elseif ($ORDER_BY=="A")
	$strOrderBy = "USER_START_NAME";
elseif ($ORDER_BY=="V")
	$strOrderBy = "VIEWS";
elseif ($ORDER_BY=="D")
	$strOrderBy = "START_DATE";
else
	$strOrderBy = "LAST_POST_DATE";

if (strtoupper($ORDER_DIRECTION) == "ASC")
	$strOrderDir = "ASC";
else
	$strOrderDir = "DESC";

$arOrder = array("SORT"=>"ASC", $strOrderBy=>$strOrderDir);

$arFilter = array("FORUM_ID"=>$FID);
if (ForumCurrUserPermissions($FID)<"Q")
	$arFilter["APPROVED"] = "Y";
$db_Topic = CForumTopic::GetListEx($arOrder, $arFilter);

$db_Topic->NavStart($FORUM_TOPICS_PER_PAGE, false);
?>

<p class="text"><?echo $db_Topic->NavPrint("Темы")?></p>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="forumtitle">Форум: <b><?echo $arForum["NAME"];?></b></td>
	</tr>
</table>
<font style="font-size:4px;">&nbsp;<br></font>
<table width="100%" align="center" border="0" cellspacing="0" cellpadding="1" class="forumborder">
<form action="" method="get">
  <tr>
	<td>
	  <table width="100%" border="0" cellspacing="1" cellpadding="3">
		<tr class="forumhead">
			<td align="center" nowrap class="forumheadtext">

			</td>
			<td align="center" nowrap class="forumheadtext">

			</td>
			<td nowrap class="forumheadtext" align="center">
				Заголовок темы<br>
				<?echo SortingEx("T", "", "ORDER_BY", "ORDER_DIRECTION")?><br>
			</td>
			<td align="center" nowrap class="forumheadtext">
				Автор темы<br>
				<?echo SortingEx("A", "", "ORDER_BY", "ORDER_DIRECTION")?><br>
			</td>
			<td align="center" nowrap class="forumheadtext">
				Ответы<br>
				<?echo SortingEx("N", "", "ORDER_BY", "ORDER_DIRECTION")?><br>
			</td>
<?if(false):?>
			<td align="center" nowrap class="forumheadtext">
				Прочитано<br>
				<?echo SortingEx("V", "", "ORDER_BY", "ORDER_DIRECTION")?><br>
			</td>
<?endif;?>
			<td nowrap align="center" class="forumheadtext">
				Последний ответ<br>
				<?echo SortingEx("P", "", "ORDER_BY", "ORDER_DIRECTION")?><br>
			</td>
		</tr>
	<?
	while ($db_Topic->NavNext(true, "f_", true)):
		list($FirstUnreadedTopicID, $FirstUnreadedMessageID) = CForumMessage::GetFirstUnreadEx($f_FORUM_ID, $f_ID, $USER->GetUserGroupArray());
		?>
		<tr valign="top" class="forumbody">
			<td align="center" class="forumbodytext">
				<?
				$strClosed = "";
				if ($f_STATE!="Y") $strClosed = "closed_";
				if ($f_APPROVED!="Y" && ForumCurrUserPermissions($f_FORUM_ID)>="Q")
				{
					?><font color="#FF0000"><b>NA</b></font><?
				}
				elseif ($FirstUnreadedMessageID>0)
				{
					?><a href="read.php?FID=<?echo $f_FORUM_ID;?>&amp;TID=<?echo $f_ID?>&amp;MID=<?echo $FirstUnreadedMessageID?>#message<?echo $FirstUnreadedMessageID?>"><img src="/bitrix/images/forum/f_<?echo $strClosed; ?>norm.gif" width="17" height="11" alt="Есть новые сообщения!" border="0" vspace="3"></a><?
				}
				else
				{
					?><img src="/bitrix/images/forum/f_<?echo $strClosed; ?>norm_no.gif" width="17" height="11" alt="Нет новых сообщений" border="0" vspace="3"><?
				}
				?><br>
			</td>
			<td align="center" class="forumbodytext">
				<?if (strlen($f_IMAGE)>0):?>
					<img src="/bitrix/images/forum/icon/<?echo $f_IMAGE;?>" alt="<?echo $f_IMAGE_DESCR;?>" border="0" width="15" height="15" vspace="1"><?
				endif;?><br>
			</td>
			<td class="forumbodytext">
				<?if (IntVal($f_SORT)!=150) echo "<b>Закрепленная:</b> ";?>
				<a href="read.php?FID=<?echo $f_FORUM_ID;?>&amp;TID=<?echo $f_ID?>&amp;PAGEN_1=1" title="Тема начата <?echo $f_START_DATE?>"><?echo $f_TITLE?></a><?if($FirstUnreadedMessageID>0):?><font class="forumnew"> (new)</font><?endif?><?
				$numMessages = $f_POSTS+1;
				if (ForumCurrUserPermissions($FID)>="Q")
				{
					$numMessages = CForumMessage::GetList(array(), array("TOPIC_ID"=>$f_ID), true);
				}
				echo MyForumShowTopicPages($numMessages, "read.php?FID=".$f_FORUM_ID."&amp;TID=".$f_ID."", "PAGEN_1");
				?>
				<br>
				<?echo $f_DESCRIPTION?>
			</td>
			<td class="forumbodytext">
				<?echo $f_USER_START_NAME?>
			</td>
			<td align="right" class="forumbodytext">
				<?echo $f_POSTS?>&nbsp;
			</td>
<?if(false):?>
			<td align="right" class="forumbodytext">
				<?echo $f_VIEWS?>&nbsp;
			</td>
<?endif;?>
			<td class="forumbodytext">
				<nobr><?echo $f_LAST_POST_DATE;?></nobr><br>
				<font class="forumheadcolor">автор:</font> <a href="read.php?FID=<?echo $f_FORUM_ID;?>&amp;TID=<?echo $f_ID?>&amp;MID=<?echo $f_LAST_MESSAGE_ID?>#message<?echo $f_LAST_MESSAGE_ID?>"><?echo $f_LAST_POSTER_NAME?></a>
			</td>
		</tr>
		<?
	endwhile;
	?>

	  </table>
	</td>
  </tr>
</form>
</table>

<p class="text"><?echo $db_Topic->NavPrint("Темы")?></p>

<?
$bSimplePanel = true;
include("menu.php");

if ($SHOW_FORUM_DEBUG_INFO) $arForumDebugInfo[] = "<br><font color=\"#FF0000\">Making Page: ".Round(getmicrotime()-$prexectime, 3)." sec</font>";
if ($SHOW_FORUM_DEBUG_INFO)
{
	for ($i = 0; $i < count($arForumDebugInfo); $i++)
		echo $arForumDebugInfo[$i];
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog.php");
?>