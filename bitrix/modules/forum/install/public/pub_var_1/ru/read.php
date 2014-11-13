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

	$arTopic = CForumTopic::GetByIDEx($TID);
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
// Now $FID and $TID (and $MID if needed) have actual and coordinated values

// Let's check if current user can can view this topic
if (!CForumTopic::CanUserViewTopic($TID, $USER->GetUserGroupArray()))
	$APPLICATION->AuthForm("Для просмотра темы введите ваши логин и пароль");

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
		"AUTHOR_EMAIL" => $AUTHOR_EMAIL,
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
		LocalRedirect("/bitrix/admin/ticket_edit.php?ID=".intval($SuID)."&lang=".LANG);
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
	$db_Message->NavStart($FORUM_MESSAGES_PER_PAGE, false, CForumMessage::GetMessagePage($MID, $FORUM_MESSAGES_PER_PAGE, $USER->GetUserGroupArray()));
else
	$db_Message->NavStart($FORUM_MESSAGES_PER_PAGE, false);
?>

<p class="text"><?
//Otherwise we can not move through the pages...
unset($_GET["MID"]);
unset($HTTP_GET_VARS["MID"]);
echo $db_Message->NavPrint("Сообщения");
?></p>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="forumtitle">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr valign="top">
<?if (strlen($arTopic["IMAGE"])>0):?>
		<td width="0%">
			<img src="/bitrix/images/forum/icon/<?echo $arTopic["IMAGE"];?>" alt="<?echo $arTopic["IMAGE_DESCR"];?>" border="0" width="15" height="15" vspace="0"><br>
		</td>
		<td class="forumtitletext" width="0%">&nbsp;</td>
<?endif;?>		
		<td class="forumtitletext" width="99%">Тема: <b><?echo htmlspecialcharsEx($arTopic["TITLE"]);?></b><?
		if (strlen($arTopic["DESCRIPTION"])>0)
		{
			echo ", ".htmlspecialcharsEx($arTopic["DESCRIPTION"]);
		}
		?></td>
		<td nowrap width="1%" align="right" class="forumtitletext">&nbsp;Просмотров: <?echo $arTopic["VIEWS"]?></td>
		</tr>
		</table>
</td>
	</tr>
</table>
<font style="font-size:4px;">&nbsp;<br></font>

<table width="100%" border="0" cellspacing="0" cellpadding="5">

<?
$n = 0;
while ($db_Message->NavNext(true, "f_", false)):
?>
<?if($n>0):?>
  <tr class="forumpostsep">
	<td colspan="2"><!-- --></td>
  </tr>
<?endif?>
  <tr valign="top" class="forumbody">
	<td align="left" rowspan="2" width="140" class="forumbrd" style="border-right:none;"><a name="message<?echo $f_ID;?>"></a>
<font class="forumbodytext"><b><?
			echo htmlspecialcharsEx($f_AUTHOR_NAME);
		?></b>
		<?
		if (IntVal($f_AUTHOR_ID)>0)
		{
			$arMessageUserGroups = CUser::GetUserGroup($f_AUTHOR_ID);
			$arMessageUserGroups[] = 2;
			$strMessageUserPerms = CForumNew::GetUserPermission($FID, $arMessageUserGroups);
			if ($strMessageUserPerms=="Q") echo "<br><font class=\"forumheadcolor\">Модератор</font>";
			elseif ($strMessageUserPerms=="U") echo "<br><font class=\"forumheadcolor\">Редактор</font>";
			elseif ($strMessageUserPerms=="Y") echo "<br><font class=\"forumheadcolor\">Администратор</font>";
			elseif (IntVal($f_RANK_ID)>0)
			{
				$arRank = CForumRank::GetLangByID($f_RANK_ID, LANG);
				echo "<br><font class=\"forumheadcolor\">".$arRank["NAME"]."</font>";
			}
		}
		else
		{
			echo "<br><font class=\"forumheadcolor\"><i>Гость</i></font>";
		}
		?>
		<br>
		<?if (strlen($f_AVATAR)>0):?>
		<a href="view_profile.php?UID=<?echo $f_AUTHOR_ID ?>" title="Профиль автора сообщения"><?echo CFile::ShowImage($f_AVATAR, 90, 90, "border=0 vspace=5", "", true)?></a><br>
		<?endif;?>
		<?if (strlen($f_DESCRIPTION)>0):?>
			<i><?echo htmlspecialcharsEx($f_DESCRIPTION);?></i><br>
		<?endif;?>
		<font style="font-size:8px;">&nbsp;<br></font>
		<?if (IntVal($f_NUM_POSTS)>0):?>
			<font class="forumheadcolor">Всего сообщений:</font> <?echo $f_NUM_POSTS;?><br>
		<?endif;?>
		<?if (strlen($f_DATE_REG)>0):?>
			<font class="forumheadcolor">Дата регистрации:</font> <?echo $f_DATE_REG;?><br>
		<?endif;?>
		</font>
	</td>
	<td class="forumbrd forumbrd1" style="border-bottom:none;">
		<table border="0" cellspacing="0" cellpadding="0" width="100%">
		  <tr>
		  	<td width="100%"><font class="forumbodytext">
<font class="forumheadcolor">Создано:</font> <nobr><?echo $f_POST_DATE;?></nobr><br>
</font></td>
<?if(ForumCurrUserPermissions($FID)>="I" && $arTopic["STATE"]=="Y"):?>
			<td nowrap class="forummessbutton"><a class="forummessbuttontext" title="Вставить в ответ имя" href="javascript:reply2author('<?echo str_replace("'", "\'", htmlspecialchars($f_AUTHOR_NAME))?>,')">Имя</a></td>
			<td><div class="forummessbuttonsep"></div></td>
			<td nowrap class="forummessbutton"><a href="javascript:quoteMessageEx('<?echo htmlspecialcharsEx($f_AUTHOR_NAME) ?>')" title="Для вставки цитаты в форму ответа выделите ее и нажмите сюда" class="forummessbuttontext">Цитировать</a></td>
<?endif;?>
		  </tr>
		</table>

<font style="font-size:5px;">&nbsp;<br></font>

<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td class="forumhr"><img src="/bitrix/images/1.gif" width="1" height="1" alt=""></td></tr></table>

<font style="font-size:8px;">&nbsp;<br></font>
<font class="forumbodytext">
		<?
		$arAllow["SMILES"] = $arForum["ALLOW_SMILES"];
		if ($f_USE_SMILES!="Y") $arAllow["SMILES"] = "N";
		echo $parser->convert($f_POST_MESSAGE, $arAllow);

		if (IntVal($f_ATTACH_IMG)>0)
		{
			echo "<br><br>";
			if ($arForum["ALLOW_UPLOAD"]=="Y" || $arForum["ALLOW_UPLOAD"]=="F" || $arForum["ALLOW_UPLOAD"]=="A")
			{
				echo CFile::ShowFile($f_ATTACH_IMG, 0, 300, 300, true, "border=0", false);
			}
		}

		if(strlen($f_SIGNATURE)>0)
		{
			echo "<br><br><font class=\"forumsigntext\">";
			$arAllow["SMILES"] = "N";
			echo $parser->convert($f_SIGNATURE, $arAllow);
			echo "</font>";
		}
		?>
		</font>
	</td>
  </tr>
  <tr>
	<td valign="bottom" class="forumbody forumbrd forumbrd1" style="border-top:none;">
<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td class="forumhr"><img src="/bitrix/images/1.gif" width="1" height="1" alt=""></td></tr></table>
<font style="font-size:5px;">&nbsp;<br></font>


<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr valign="top">
	<td>
<?if(IntVal($f_AUTHOR_ID)>0):?>
		<table border="0" cellspacing="0" cellpadding="0">
		  <tr>
			<td nowrap class="forummessbutton"><a href="view_profile.php?UID=<?echo $f_AUTHOR_ID ?>" title="Профиль автора сообщения" class="forummessbuttontext">Профиль</a></td>
			<td><div class="forummessbuttonsep"></div></td>
<?if (strlen($f_EMAIL)>0):?>
			<td nowrap class="forummessbutton"><a href="send_message.php?TYPE=MAIL&UID=<?echo $f_AUTHOR_ID; ?>" title="Отправить письмо на E-Mail автора сообщения" class="forummessbuttontext">E-Mail</a></td>
			<td><div class="forummessbuttonsep"></div></td>
<?endif;?>
<?if (strlen($f_PERSONAL_ICQ)>0):?>
			<td nowrap class="forummessbutton"><a href="send_message.php?TYPE=ICQ&UID=<?echo $f_AUTHOR_ID; ?>" title="Отправить письмо на номер ICQ автора сообщения" class="forummessbuttontext">ICQ</a></td>
			<td><div class="forummessbuttonsep"></div></td>
<?endif;?>
		  </tr>
		</table>
<?endif;?>
<?
if(
	ForumCurrUserPermissions($FID)>="Q"
	|| (ForumCurrUserPermissions($FID)>="U" 
		|| $iLAST_TOPIC_MESSAGE == IntVal($f_ID) 
		&& $USER->IsAuthorized() 
		&& IntVal($f_AUTHOR_ID) == IntVal($USER->GetParam("USER_ID")))
	|| $bCanUserDeleteMessages
):
?>
<?if(IntVal($f_AUTHOR_ID)>0):?>
<font style="font-size:4px;">&nbsp;<br></font>
<?endif;?>
		<table border="0" cellspacing="0" cellpadding="0">
		<tr>
<?if ($f_APPROVED=="Y" && ForumCurrUserPermissions($FID)>="Q"):?>
			<td nowrap class="forummessbutton"><a href="read.php?FID=<?echo $FID; ?>&TID=<?echo $TID; ?>&MID=<?echo $f_ID ?>&ACTION=HIDE" title="Скрыть сообщение" class="forummessbuttontext">Скрыть</a></td>
			<td><div class="forummessbuttonsep"></div></td>
<?elseif (ForumCurrUserPermissions($FID)>="Q"):?>
			<td nowrap class="forummessbutton"><a href="read.php?FID=<?echo $FID; ?>&TID=<?echo $TID; ?>&MID=<?echo $f_ID ?>&ACTION=SHOW" title="Показать сообщение" class="forummessbuttontext">Показать</a></td>
			<td><div class="forummessbuttonsep"></div></td>
<?endif;?>
<?
if (ForumCurrUserPermissions($FID)>="U"
	|| $iLAST_TOPIC_MESSAGE == IntVal($f_ID)
	&& $USER->IsAuthorized()
	&& IntVal($f_AUTHOR_ID) == IntVal($USER->GetParam("USER_ID"))):
?>
			<td nowrap class="forummessbutton"><a href="new_topic.php?FID=<?echo $FID; ?>&TID=<?echo $TID; ?>&MID=<?echo $f_ID ?>&MESSAGE_TYPE=EDIT" title="Редактировать сообщение" class="forummessbuttontext">Редактировать</a></td>
			<td><div class="forummessbuttonsep"></div></td>
<?endif;?>
<?if ($bCanUserDeleteMessages):?>
			<td nowrap class="forummessbutton"><a href="read.php?FID=<?echo $FID; ?>&TID=<?echo $TID; ?>&MID=<?echo $f_ID ?>&ACTION=DEL" title="Удалить сообщение" class="forummessbuttontext">Удалить</a></td>
			<td><div class="forummessbuttonsep"></div></td>
	<?if(IntVal($f_AUTHOR_ID)>0 && CModule::IncludeModule("support")):?>
			<td nowrap class="forummessbutton"><a href="read.php?FID=<?echo $FID; ?>&TID=<?echo $TID; ?>&MID=<?echo $f_ID ?>&ACTION=FORUM_MESSAGE2SUPPORT" title="Перенести в техподдержку" class="forummessbuttontext">В техподдержку</a></td>
			<td><div class="forummessbuttonsep"></div></td>
	<?endif;?>
<?endif;?>
				</font>
			</td>
		</tr>
		</table>
<?endif?>
	</td>
	<td align="right">
		<table border="0" cellspacing="0" cellpadding="0">
			<td nowrap class="forummessbutton" style="padding-left:2px; padding-right:2px;"><a href="javascript:scroll(0,0);" title="Наверх"><img src="/bitrix/images/forum/button_top.gif" width="17" height="12" border="0" alt="Наверх"></a></td>
		  </tr>
		</table>
	</td>
</tr>
</table>
		<?if(ForumCurrUserPermissions($FID)>="Q"):?>
<font class="forumbodytext">
		<font style="font-size:5px;">&nbsp;<br></font>
			<?
			$bIP = False;
			if (ereg("^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$", $f_AUTHOR_IP)) $bIP = True;
			if ($bIP) $f_AUTHOR_IP = GetWhoisLink($f_AUTHOR_IP);

			$bIP = False;
			if (ereg("^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$", $f_AUTHOR_REAL_IP)) $bIP = True;
			if ($bIP) $f_AUTHOR_REAL_IP =  GetWhoisLink($f_AUTHOR_REAL_IP);
			?>
			<font class="forumheadcolor">IP<?if($f_AUTHOR_IP <> $f_AUTHOR_REAL_IP):?> / реальный<?endif;?>: </font><?echo $f_AUTHOR_IP;?><?if($f_AUTHOR_IP <> $f_AUTHOR_REAL_IP):?>&nbsp;/ <?echo $f_AUTHOR_REAL_IP;?><?endif?><br>
		<?if(CModule::IncludeModule("statistic") && IntVal($f_GUEST_ID)>0 && $APPLICATION->GetGroupRight("statistic")!="D"):?>
			<font class="forumheadcolor">ID посетителя: </font><a href="/bitrix/admin/guest_list.php?lang=<?=LANGUAGE_ID?>&find_id=<?=$f_GUEST_ID?>&set_filter=Y"><?echo $f_GUEST_ID;?></a><br>
		<?endif;?>
</font>
		<?endif;?>

	</td>
  </tr>
<?
	$n++;
endwhile;
?>
</table>

<p class="text"><?echo $db_Message->NavPrint("Сообщения")?></p>

<?
if($arTopic["STATE"]=="Y")
{
	$MESSAGE_TYPE = "REPLY";
	include("post_form.php");
}

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