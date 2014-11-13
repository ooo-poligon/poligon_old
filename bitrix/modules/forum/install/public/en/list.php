<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

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

ForumSetLastVisit();
define("FORUM_MODULE_PAGE", "LIST");
// Let's check if current user can can view this forum
if (!CForumNew::CanUserViewForum($FID, $USER->GetUserGroupArray()))
	$APPLICATION->AuthForm("Enter your login and password to access this forum");

// Let's init read labels
CForumNew::InitReadLabels($FID, $USER->GetUserGroupArray());

if ($SHOW_FORUM_DEBUG_INFO) $arForumDebugInfo[] = "<br><font color=\"#FF0000\">Initializing Variables: ".Round(getmicrotime()-$prexectime, 3)." sec</font>";


// ACTIONS: subscribe
if ($SHOW_FORUM_DEBUG_INFO) $prexectime = getmicrotime();
$strErrorMessage = "";
$strOKMessage = "";
if ($REQUEST_METHOD=="GET" && $ACTION=="FORUM_SUBSCRIBE" && IntVal($FID)>0)
{
	if (ForumSubscribeNewMessages($FID, 0, $strErrorMessage, $strOKMessage))
		LocalRedirect("subscr_list.php?FID=".$FID);
}
elseif ($REQUEST_METHOD=="GET" && $ACTION=="SET_BE_READ" && IntVal($FID)>0)
{
	ForumSetAllMessagesReaded($FID);
}
if ($SHOW_FORUM_DEBUG_INFO) $arForumDebugInfo[] = "<br><font color=\"#FF0000\">Actions: ".Round(getmicrotime()-$prexectime, 3)." sec</font>";
// End of ACTIONS

$APPLICATION->AddChainItem($arForum["NAME"], "list.php?FID=".$FID);
$APPLICATION->SetTitle("Forum &laquo;".$arForum["NAME"]."&raquo;");
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

$db_Topic->NavStart($FORUM_TOPICS_PER_PAGE);
?>
<table width="100%" border="0">
	<tr>
		<td align="left">
			<?echo $db_Topic->NavPrint("Topics")?>
		</td>
		<td align="right">
			<?
			if (CForumTopic::CanUserAddTopic($FID, $USER->GetUserGroupArray(), $USER->GetID())):
				$strIcoPath = "/bitrix/images/forum/t_new_".LANG.".gif";
				if (!file_exists($_SERVER["DOCUMENT_ROOT"].$strIcoPath)) $strIcoPath = "/bitrix/images/forum/t_new.gif";
				?>
				<a href="new_topic.php?FID=<?echo $FID;?>"><img src="<?echo $strIcoPath ?>" width="93" height="19" alt="Add new topic" border="0"></a>
				<?
			endif;
			?>
		</td>
	</tr>
</table>

<table width="99%" align="center" border="0" cellspacing="1" cellpadding="0" class="forumborder">
<form action="" method="get">
  <tr>
	<td>
	  <table width="100%" border="0" cellspacing="0" cellpadding="3" class="forumborder">
		<tr>
			<td> </td>
			<td width="100%" class="forumtitletext"><b><?echo $arForum["NAME"];?></b></td>
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
			<td align="center" nowrap class="forumheadtext">

			</td>
			<td width="45%" nowrap class="forumheadtext" align="center">
				Topic Title<br>
				<?echo SortingEx("T", "", "ORDER_BY", "ORDER_DIRECTION")?>
			</td>
			<td width="14%" align="center" nowrap class="forumheadtext">
				Topic Starter<br>
				<?echo SortingEx("A", "", "ORDER_BY", "ORDER_DIRECTION")?>
			</td>
			<td width="7%" align="center" nowrap class="forumheadtext">
				Replies<br>
				<?echo SortingEx("N", "", "ORDER_BY", "ORDER_DIRECTION")?>
			</td>
			<td width="7%" align="center" nowrap class="forumheadtext">
				Views<br>
				<?echo SortingEx("V", "", "ORDER_BY", "ORDER_DIRECTION")?>
			</td>
			<td width="27%" nowrap align="center" class="forumheadtext">
				Last Post Info<br>
				<?echo SortingEx("P", "", "ORDER_BY", "ORDER_DIRECTION")?>
			</td>
		</tr>
	<?
	while ($db_Topic->NavNext(true, "f_", true)):
		list($FirstUnreadedTopicID, $FirstUnreadedMessageID) = CForumMessage::GetFirstUnreadEx($f_FORUM_ID, $f_ID, $USER->GetUserGroupArray());
		?>
		<tr class="forumbody">
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
					?><a href="read.php?FID=<?echo $f_FORUM_ID;?>&TID=<?echo $f_ID?>&MID=<?echo $FirstUnreadedMessageID?>#message<?echo $FirstUnreadedMessageID?>"><img src="/bitrix/images/forum/f_<?echo $strClosed; ?>norm.gif" width="18" height="12" alt="New posts!" border="0"></a><?
				}
				else
				{
					?><img src="/bitrix/images/forum/f_<?echo $strClosed; ?>norm_no.gif" width="18" height="12" alt="No new posts" border="0"><?
				}
				?>
			</td>
			<td align="center" class="forumbodytext">
				<?if (strlen($f_IMAGE)>0):?>
					<img src="/bitrix/images/forum/icon/<?echo $f_IMAGE;?>" alt="<?echo $f_IMAGE_DESCR;?>" border="0" width="15" height="15">
				<?endif;?>
			</td>
			<td class="forumbodytext">
				<?if (IntVal($f_SORT)!=150) echo "<b>Pinned:</b> ";?>
				<a href="read.php?FID=<?echo $f_FORUM_ID;?>&TID=<?echo $f_ID?>" title="Topic started <?echo $f_START_DATE?>"><?echo $f_TITLE?></a>
				<?
				$numMessages = $f_POSTS+1;
				if (ForumCurrUserPermissions($FID)>="Q")
				{
					$numMessages = CForumMessage::GetList(array(), array("TOPIC_ID"=>$f_ID), true);
				}
				echo ForumShowTopicPages($numMessages, "read.php?FID=".$f_FORUM_ID."&TID=".$f_ID."", "PAGEN_1");
				?>
				<br>
				<?echo $f_DESCRIPTION?>
			</td>
			<td align="center" class="forumbodytext">
				<?echo $f_USER_START_NAME?>
			</td>
			<td align="center" class="forumbodytext">
				<?echo $f_POSTS?>
			</td>
			<td align="center" class="forumbodytext">
				<?echo $f_VIEWS?>
			</td>
			<td class="forumbodytext">
				<?echo $f_LAST_POST_DATE?><br>
				<a href="read.php?FID=<?echo $f_FORUM_ID;?>&TID=<?echo $f_ID?>&MID=<?echo $f_LAST_MESSAGE_ID?>#message<?echo $f_LAST_MESSAGE_ID?>">Author:</a>
				<b><a href="read.php?FID=<?echo $f_FORUM_ID;?>&TID=<?echo $f_ID?>&MID=<?echo $f_LAST_MESSAGE_ID?>#message<?echo $f_LAST_MESSAGE_ID?>"><?echo $f_LAST_POSTER_NAME?></a></b>
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

<table width="100%" border="0">
	<tr>
		<td align="left">
			<?echo $db_Topic->NavPrint("Topics")?>
		</td>
		<td align="right">
			<?
			if (CForumTopic::CanUserAddTopic($FID, $USER->GetUserGroupArray(), $USER->GetID())):
				$strIcoPath = "/bitrix/images/forum/t_new_".LANG.".gif";
				if (!file_exists($_SERVER["DOCUMENT_ROOT"].$strIcoPath)) $strIcoPath = "/bitrix/images/forum/t_new.gif";
				?>
				<a href="new_topic.php?FID=<?echo $FID;?>"><img src="<?echo $strIcoPath ?>" width="93" height="19" alt="Add new topic" border="0"></a>
				<?
			endif;
			?>
		</td>
	</tr>
</table>

<br>
<center><font class="text">
<a href="list.php?FID=<?echo $FID; ?>&ACTION=SET_BE_READ" title="Mark all messages of this forum as read">[Mark as read]</a>
</font></center>

<?
if ($SHOW_FORUM_DEBUG_INFO) $arForumDebugInfo[] = "<br><font color=\"#FF0000\">Making Page: ".Round(getmicrotime()-$prexectime, 3)." sec</font>";
if ($SHOW_FORUM_DEBUG_INFO)
{
	for ($i = 0; $i < count($arForumDebugInfo); $i++)
		echo $arForumDebugInfo[$i];
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog.php");
?>