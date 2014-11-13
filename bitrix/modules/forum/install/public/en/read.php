<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// Let's init $FID (forum id), $TID (topic id) and $MID (message id)
// with actual and coordinated values
$FID = IntVal($FID);
$TID = IntVal($TID);
$MID = IntVal($MID);

$arTopic = false;
if (CModule::IncludeModule("forum")):
	if ($SHOW_FORUM_DEBUG_INFO) $prexectime = getmicrotime();
	if ($MID>0)
	{
		$arMessage = CForumMessage::GetByID($MID);
		if ($arMessage)
		{
			$TID = IntVal($arMessage["TOPIC_ID"]);
			$FID = IntVal($arMessage["FORUM_ID"]);
		}
		else
		{
			$MID = 0;
		}
	}

	$arTopic = CForumTopic::GetByID($TID);
endif;

ForumSetLastVisit();
define("FORUM_MODULE_PAGE", "READ");
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
// Now $FID and $TID (and $MID if needed) have actual and coordinated values

// Let's check if current user can can view this topic
if (!CForumTopic::CanUserViewTopic($TID, $USER->GetUserGroupArray()))
	$APPLICATION->AuthForm("Enter you login and password to view this topic");

// Let's init read labels
CForumNew::InitReadLabels($FID, $USER->GetUserGroupArray());
CForumTopic::SetReadLabels($TID, $USER->GetUserGroupArray());

if ($SHOW_FORUM_DEBUG_INFO) $arForumDebugInfo[] = "<br><font color=\"#FF0000\">Initializing Variables: ".Round(getmicrotime()-$prexectime, 3)." sec</font>";

// ACTIONS: reply, open/close topic, moderate, etc.
if ($SHOW_FORUM_DEBUG_INFO) $prexectime = getmicrotime();
$strErrorMessage = "";
$strOKMessage = "";
$bVarsFromForm = false;
if ($REQUEST_METHOD=="POST" && $MESSAGE_TYPE=="REPLY")
{
	$arFieldsG = array(
		"POST_MESSAGE" => $POST_MESSAGE,
		"AUTHOR_NAME" => $AUTHOR_NAME,
		"USE_SMILES" => $USE_SMILES,
		"ATTACH_IMG" => $_FILES["ATTACH_IMG"]
		);
	$MID = ForumAddMessage("REPLY", $FID, $TID, 0, $arFieldsG, $strErrorMessage, $strOKMessage);
	$MID = IntVal($MID);
	if ($MID>0)
	{
//		LocalRedirect("read.php?FID=".$FID."&TID=".$TID."&MID=".$MID."#message".$MID);
	}
	else
		$bVarsFromForm = true;
}
elseif ($REQUEST_METHOD=="GET" && CModule::IncludeModule("support") && $ACTION=="FORUM_MESSAGE2SUPPORT")
{
	$SuID = ForumMoveMessage2Support($MID, $strErrorMessage, $strOKMessage);
	if (IntVal($SuID)>0)
	{
		LocalRedirect("/bitrix/admin/ticket_list.php?lang=".LANG."&strNote=".urlencode("Forum message was successfully moved to the Helpdesk as a trouble ticket."));
	}
}
elseif ($REQUEST_METHOD=="GET" && ($ACTION=="FORUM_SUBSCRIBE" || $ACTION=="TOPIC_SUBSCRIBE"))
{
	if (ForumSubscribeNewMessages($FID, (($ACTION=="FORUM_SUBSCRIBE")?0:$TID), $strErrorMessage, $strOKMessage))
		LocalRedirect("subscr_list.php?FID=".$FID."&TID=".$TID);
}
elseif ($REQUEST_METHOD=="GET" && $ACTION=="HIDE")
{
	ForumModerateMessage($MID, "HIDE", $strErrorMessage, $strOKMessage);
}
elseif ($REQUEST_METHOD=="GET" && $ACTION=="SHOW")
{
	ForumModerateMessage($MID, "SHOW", $strErrorMessage, $strOKMessage);
}
elseif ($REQUEST_METHOD=="GET" && $ACTION=="SET_ORDINARY")
{
	if (ForumTopOrdinaryTopic($TID, "ORDINARY", $strErrorMessage, $strOKMessage))
		$arTopic["SORT"] = "150";
}
elseif ($REQUEST_METHOD=="GET" && $ACTION=="SET_TOP")
{
	if (ForumTopOrdinaryTopic($TID, "TOP", $strErrorMessage, $strOKMessage))
		$arTopic["SORT"] = "100";
}
elseif ($REQUEST_METHOD=="GET" && $ACTION=="DEL_TOPIC" && $TID>0)
{
	if (ForumDeleteTopic($TID, $strErrorMessage, $strOKMessage))
		LocalRedirect("list.php?FID=".$FID);
}
elseif ($REQUEST_METHOD=="GET" && $ACTION=="STATE_Y")
{
	if (ForumOpenCloseTopic($TID, "OPEN", $strErrorMessage, $strOKMessage))
		$arTopic["STATE"] = "Y";
}
elseif ($REQUEST_METHOD=="GET" && $ACTION=="STATE_N")
{
	if (ForumOpenCloseTopic($TID, "CLOSE", $strErrorMessage, $strOKMessage))
		$arTopic["STATE"] = "N";
}
elseif ($REQUEST_METHOD=="GET" && $ACTION=="DEL")
{
	if (ForumDeleteMessage($MID, $strErrorMessage, $strOKMessage))
	{
		$arTopic = CForumTopic::GetByID($TID);
		if (!$arTopic)
		{
			LocalRedirect("list.php?FID=".$FID);
		}
	}
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

$parser = new textParser(LANGUAGE_ID);

$bCanUserDeleteMessages = CForumTopic::CanUserDeleteTopicMessage($TID, $USER->GetUserGroupArray(), $USER->GetID());

$arAllow = array(
	"HTML" => $arForum["ALLOW_HTML"],
	"ANCHOR" => $arForum["ALLOW_ANCHOR"],
	"BIU" => $arForum["ALLOW_BIU"],
	"IMG" => $arForum["ALLOW_IMG"],
	"LIST" => $arForum["ALLOW_LIST"],
	"QUOTE" => $arForum["ALLOW_QUOTE"],
	"CODE" => $arForum["ALLOW_CODE"],
	"FONT" => $arForum["ALLOW_FONT"],
	"SMILES" => $arForum["ALLOW_SMILES"],
	"UPLOAD" => $arForum["ALLOW_UPLOAD"],
	"NL2BR" => $arForum["ALLOW_NL2BR"]
	);

$iLAST_TOPIC_MESSAGE = 0;
$db_res = CForumMessage::GetList(array("ID"=>"DESC"), array("TOPIC_ID"=>$TID), false, 1);
if ($ar_res = $db_res->Fetch()) $iLAST_TOPIC_MESSAGE = IntVal($ar_res["ID"]);

$arFilter = array("TOPIC_ID" => $TID);
if (ForumCurrUserPermissions($FID)<"Q") $arFilter["APPROVED"] = "Y";
$db_Message = CForumMessage::GetListEx(array("ID"=>"ASC"), $arFilter);

if ($MID>0)
	$db_Message->NavStart($FORUM_MESSAGES_PER_PAGE, true, CForumMessage::GetMessagePage($MID, $FORUM_MESSAGES_PER_PAGE, $USER->GetUserGroupArray()));
else
	$db_Message->NavStart($FORUM_MESSAGES_PER_PAGE);
?>

<table width="100%" border="0">
	<tr>
		<td align="left">
			<?
			//Otherwise we can not move through the pages...
			unset($_GET["MID"]);
			unset($HTTP_GET_VARS["MID"]);
			unset($_GET["ACTION"]);
			unset($HTTP_GET_VARS["ACTION"]);
			?>
			<?echo $db_Message->NavPrint("Posts")?>
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

<table width="99%" border="0" cellspacing="1" cellpadding="0" align="center" class="forumborder">
<tr><td>

<table width="100%" border="0" cellspacing="0" cellpadding="3" class="forumborder">
  <tr>
	<td> </td>
	<td width="100%" class="forumtitletext">
		<b>Topic &laquo;<?echo htmlspecialcharsEx($arTopic["TITLE"]);?>&raquo;</b><?
		if (strlen($arTopic["DESCRIPTION"])>0)
		{
			echo ", ".htmlspecialcharsEx($arTopic["DESCRIPTION"]);
		}
		?>
	</td>
  </tr>
</table>

</td></tr>
<tr><td>

<table width="100%" border="0" cellspacing="1" cellpadding="4">
  <tr class="forumhead">
	<td width="100%" nowrap colspan="2">

		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		  <tr>
				<td><font class="forumheadtext">
					<b>&laquo;&nbsp;<?
					list($iPREV_TOPIC, $iNEXT_TOPIC) = CForumTopic::GetNeighboringTopics($TID, $USER->GetUserGroupArray());
					if (IntVal($iPREV_TOPIC)>0):?><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $iPREV_TOPIC ?>"><?endif;?>Next Oldest<?if (IntVal($iPREV_TOPIC)>0):?></a><?endif;?> | <?
					if (IntVal($iNEXT_TOPIC)>0):?><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $iNEXT_TOPIC; ?>"><?endif;?>Next Newest<?if (IntVal($iPREV_TOPIC)>0):?></a><?endif;?>&nbsp;&raquo;</b></font>
				</td>
				<td align="right"><font class="forumheadtext">
					<?if ($arTopic["STATE"]=="Y"):?>
						<b><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $TID?>&ACTION=TOPIC_SUBSCRIBE">Subscribe</a>
					<?endif;?>
					</b></font>
				</td>
		  </tr>
		</table>

	</td>
  </tr>

<?
while ($db_Message->NavNext(true, "f_", false)):
  ?>
  <tr valign="top" class="forumbody">
	<td><font class="forumbodytext">
		<a name="message<?echo $f_ID;?>"></a>
		<?if (ForumCurrUserPermissions($FID)>="I" && $arTopic["STATE"]=="Y") echo "<a href=\"javascript:reply2author('".str_replace("'", "\'", htmlspecialchars($f_AUTHOR_NAME))."!')\">";?>
		<?echo htmlspecialcharsEx($f_AUTHOR_NAME);?>
		<?if (ForumCurrUserPermissions($FID)>="I" && $arTopic["STATE"]=="Y") echo "</a>";?>
		<?if (strlen($f_DESCRIPTION)>0) echo "<br>".htmlspecialcharsEx($f_DESCRIPTION);?>
		<?
		if (IntVal($f_AUTHOR_ID)>0)
		{
			$arMessageUserGroups = CUser::GetUserGroup($f_AUTHOR_ID);
			$arMessageUserGroups[] = 2;
			$strMessageUserPerms = CForumNew::GetUserPermission($FID, $arMessageUserGroups);
			if ($strMessageUserPerms=="Q") echo "<br><b>Moderator</b>";
			elseif ($strMessageUserPerms=="U") echo "<br><b>Editor</b>";
			elseif ($strMessageUserPerms=="Y") echo "<br><b>Administrator</b>";
			elseif (IntVal($f_RANK_ID)>0)
			{
				$arRank = CForumRank::GetLangByID($f_RANK_ID, LANG);
				echo "<br>".$arRank["NAME"];
			}
		}
		else
		{
			echo "<br><i>Guest</i>";
		}
		?>
		<br>
		<?if (strlen($f_AVATAR)>0):?>
			<center><br>
			<?echo CFile::ShowImage($f_AVATAR, 90, 90, "border=0", "", true)?>
			</center>
		<?else:?>
			<br>
		<?endif;?>
		<br><small>
		<?if (strlen($f_EMAIL)>0):?>
			<nobr><a href="send_message.php?TYPE=MAIL&UID=<?echo $f_AUTHOR_ID; ?>" target="_blank" title="Send message to user email">Send E-Mail message</a></nobr>
		<?endif;?>
		<?if (strlen($f_PERSONAL_ICQ)>0):?>
			<nobr><a href="send_message.php?TYPE=ICQ&UID=<?echo $f_AUTHOR_ID; ?>" target="_blank" title="Send ICQ message">Send ICQ message</a></nobr>
		<?endif;?>
		<?if (IntVal($f_AUTHOR_ID)>0):?>
			<a href="view_profile.php?UID=<?echo $f_AUTHOR_ID ?>" target="_blank" title="User profile">Profile</a>
		<?endif;?>
		</small><br>
		<?if (IntVal($f_NUM_POSTS)>0):?>
			<small><nobr>Posts: <?echo $f_NUM_POSTS;?></nobr><br></small>
		<?endif;?>
		<?if (strlen($f_DATE_REG)>0):?>
			<small>Joined: <?echo $f_DATE_REG;?><br></small>
		<?endif;?>
		<?if (ForumCurrUserPermissions($FID)>="Q" && CModule::IncludeModule("statistic") && IntVal($f_GUEST_ID)>0 && $APPLICATION->GetGroupRight("statistic")!="D"):?>
			<small><nobr>ID guest: <a href="/bitrix/admin/guest_list.php?lang=<?=LANG?>&find_id=<?=$f_GUEST_ID?>&set_filter=Y"><?echo $f_GUEST_ID;?></a></nobr><br></small>
		<?endif;?>
		<?if (ForumCurrUserPermissions($FID)>="Q"):?>
			<small><nobr>IP: 
			<?
			$bIP = False;
			if (ereg("^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$", $f_AUTHOR_IP)) $bIP = True;
			if ($bIP) echo GetWhoisLink($f_AUTHOR_IP);
			else echo $f_AUTHOR_IP;
			?>
			</nobr><br>
			<nobr>IP (real): 
			<?
			$bIP = False;
			if (ereg("^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$", $f_AUTHOR_REAL_IP)) $bIP = True;
			if ($bIP) echo GetWhoisLink($f_AUTHOR_REAL_IP);
			else echo $f_AUTHOR_REAL_IP;
			?>
			</nobr><br></small>
		<?endif;?>
		</font>
	</td>
	<td width="100%">
		<font class="forumbodytext">
		<?
		$arAllow["SMILES"] = $arForum["ALLOW_SMILES"];
		if ($f_USE_SMILES!="Y") $arAllow["SMILES"] = "N";
		echo $parser->convert($f_POST_MESSAGE, $arAllow);

		if (IntVal($f_ATTACH_IMG)>0)
		{
			echo "<br>";
			if ($arForum["ALLOW_UPLOAD"]=="Y" || $arForum["ALLOW_UPLOAD"]=="F" || $arForum["ALLOW_UPLOAD"]=="A")
			{
				echo CFile::ShowFile($f_ATTACH_IMG, 0, 400, 400, false, "border=0", false);
			}
		}

		if (strlen($f_SIGNATURE)>0)
		{
			echo "<br><br>";
			$arAllow["SMILES"] = "N";
			echo $parser->convert($f_SIGNATURE, $arAllow);
		}
		?>
		</font>
	</td>
  </tr>
  <tr class="forumhead">
	<td><font class="forumbodytext">
		<small><b>Posted</b> <nobr><?echo $f_POST_DATE;?></nobr></small></font>
	</td>
	<td nowrap>

		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		  <tr>
			<td align="left"><font class="forumbodytext">
				<?if ($f_APPROVED=="Y" && ForumCurrUserPermissions($FID)>="Q"):?>
					<a href="read.php?FID=<?echo $FID; ?>&TID=<?echo $TID; ?>&MID=<?echo $f_ID ?>&ACTION=HIDE" title="Hide message">Hide</a>
				<?elseif (ForumCurrUserPermissions($FID)>="Q"):?>
					<a href="read.php?FID=<?echo $FID; ?>&TID=<?echo $TID; ?>&MID=<?echo $f_ID ?>&ACTION=SHOW" title="Show message">Show</a>
				<?endif;?>
				<?if (ForumCurrUserPermissions($FID)>="U"
					|| $iLAST_TOPIC_MESSAGE == IntVal($f_ID)
					&& $USER->IsAuthorized()
					&& IntVal($f_AUTHOR_ID) == IntVal($USER->GetParam("USER_ID"))):?>
					&nbsp;|&nbsp;<a href="new_topic.php?FID=<?echo $FID; ?>&TID=<?echo $TID; ?>&MID=<?echo $f_ID ?>&MESSAGE_TYPE=EDIT" title="Modify message">Modify</a>
				<?endif;?>
				<?if ($bCanUserDeleteMessages):?>
					&nbsp;|&nbsp;<a href="read.php?FID=<?echo $FID; ?>&TID=<?echo $TID; ?>&MID=<?echo $f_ID ?>&ACTION=DEL" title="Delete message">Delete</a>
					<?if (IntVal($f_AUTHOR_ID)>0):?>
						&nbsp;|&nbsp;<a href="read.php?FID=<?echo $FID; ?>&TID=<?echo $TID; ?>&MID=<?echo $f_ID ?>&ACTION=FORUM_MESSAGE2SUPPORT" title="Move to support">To support</a>
					<?endif;?>
				<?endif;?>
				</font>
			</td>
			<td align="right"><font class="forumbodytext">
				<?if (ForumCurrUserPermissions($FID)>="I" && $arTopic["STATE"]=="Y"):?>
					<a href="javascript:quoteMessageEx('<?echo htmlspecialcharsEx($f_AUTHOR_NAME) ?>')" title="To quote the message you are replying to, select it and click here">Quote</a>
					&nbsp;|&nbsp;
				<?endif;?>
				<a href="javascript:scroll(0,0);">Top</a></font>
			</td>
		  </tr>
		</table>

	</td>
  </tr>
  <tr class="forumhead" style="height:5px">
	<td colspan="2"><!-- --></td>
  </tr>
  <?
endwhile;
?>

<?
if ($arTopic["STATE"]=="Y")
{
	$MESSAGE_TYPE = "REPLY";

	if (file_exists($path2curdir."post_form.php"))
		include($path2curdir."post_form.php");
	elseif (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/install/public/".LANG."/post_form.php"))
		include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/install/public/".LANG."/post_form.php");
	else
		include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/install/public/en/post_form.php");
}
?>

</table>

</td></tr>
</table>

<table width="100%" border="0">
	<tr>
		<td align="left">
			<?echo $db_Message->NavPrint("Posts")?>
		</td>
		<td align="center" width="0%">
		  <?if (ForumCurrUserPermissions($FID)>="Q"):?>
				<font class="forumheadtext"><a href="move.php?FID=<?echo $FID;?>&TID=<?echo $TID;?>">Move topic</a></font>
				&nbsp;|&nbsp;
				<font class="forumheadtext"><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $TID;?>&ACTION=<?echo (IntVal($arTopic["SORT"])!=150)?"SET_ORDINARY":"SET_TOP";?>"><?echo (IntVal($arTopic["SORT"])!=150)?"Unstick":"Stick";?></a></font>
				&nbsp;|&nbsp;
				<font class="forumheadtext"><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $TID;?>&ACTION=<?echo ($arTopic["STATE"]!="Y")?"STATE_Y":"STATE_N";?>"><?echo ($arTopic["STATE"]!="Y")?"Open topic":"Close topic";?></a></font>
				<?if (CForumTopic::CanUserDeleteTopic($TID, $USER->GetUserGroupArray(), $USER->GetID())):?>
					&nbsp;|&nbsp;
					<font class="forumheadtext"><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $TID;?>&ACTION=DEL_TOPIC">Delete topic</a></font>
			  <?endif;?>
		  <?endif;?>
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
<?
if ($SHOW_FORUM_DEBUG_INFO) $arForumDebugInfo[] = "<br><font color=\"#FF0000\">Making Page: ".Round(getmicrotime()-$prexectime, 3)." sec</font>";
if ($SHOW_FORUM_DEBUG_INFO)
{
	for ($i = 0; $i < count($arForumDebugInfo); $i++)
		echo $arForumDebugInfo[$i];
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog.php");
?>