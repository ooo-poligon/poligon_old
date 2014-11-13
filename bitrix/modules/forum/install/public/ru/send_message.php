<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (!CModule::IncludeModule("forum")):
	LocalRedirect("index.php");
	die();
endif;

$UID = IntVal($UID);

ForumSetLastVisit();
define("FORUM_MODULE_PAGE", "SEND_MESSAGE");
$db_userX = CUser::GetByID($UID);
if ($db_userX->ExtractFields("x_", True))
{
	$bUserFound = True;
}

if ($TYPE!="ICQ") $TYPE = "MAIL";

if ($TYPE=="ICQ") $strTextType = "ICQ";
else $strTextType = "E-Mail";

if ($bUserFound)
{
	$ShowName = $x_NAME." ".$x_LAST_NAME;
	if (strlen($ShowName)<=0) $ShowName = $x_LOGIN;
}

if ($USER->IsAuthorized())
{
	$ShowMyName = $USER->GetFullName();
	if (strlen(Trim($ShowMyName))<=0) $ShowMyName = $USER->GetLogin();

	$db_userY = CUser::GetByID($USER->GetID());
	$db_userY->ExtractFields("y_", True);
}

$strErrorMessage = "";
$strOKMessage = "";

if ($REQUEST_METHOD=="POST" && $ACTION=="SEND" && $bUserFound)
{
	if ($USER->IsAuthorized())
	{
		$NAME = $ShowMyName;
		$EMAIL = ($TYPE=="ICQ") ? $y_PERSONAL_ICQ : $y_EMAIL;
	}
	if (strlen($NAME)<=0)
		$strErrorMessage .= "Укажите Ваше имя. \n";

	if (strlen($EMAIL)<=0)
		$strErrorMessage .= "Укажите Ваш ".(($TYPE=="ICQ") ? "номер ICQ" : "E-Mail адрес").". \n";
	elseif ($TYPE!="ICQ" && !check_email($EMAIL))
		$strErrorMessage .= "E-Mail адрес не верен. \n";

	if (strlen($SUBJECT)<=0)
		$strErrorMessage .= "Укажите тему сообщения. \n";
	if (strlen($MESSAGE)<=0)
		$strErrorMessage .= "Введите текст сообщения. \n";
	if ($TYPE=="ICQ" && strlen($x_PERSONAL_ICQ)<=0)
		$strErrorMessage .= "Не задан номер ICQ адресата. \n";
	if ($TYPE=="MAIL" && strlen($x_EMAIL)<=0)
		$strErrorMessage .= "Не задан E-Mail адрес адресата. \n";

	if (strlen($strErrorMessage)<=0)
	{
		if ($TYPE=="ICQ")
		{
			$body   = "From ".$NAME." (UIN ".$EMAIL.")\n";
			$body  .= "<br>-----<br>\n";
			$body  .= $SUBJECT."\n";
			$body  .= "<br>-----<br>\n";
			$body  .= $MESSAGE."\n";
			$from   = $NAME;
			$headers  = "Content-Type: text/plain; charset=windows-1254\n";
			$headers .= "From: $from\nX-Mailer: System33r";
			@mail($x_PERSONAL_ICQ."@pager.mirabilis.com", $SUBJECT, $body, $headers);
			$strOKMessage = "Сообщение отправлено. \n";
		}
		else
		{
			$event = new CEvent;
			$arFields = Array(
				"FROM_NAME" => $NAME,
				"FROM_EMAIL" => $EMAIL,
				"TO_NAME" => $ShowName,
				"TO_EMAIL" => $x_EMAIL,
				"SUBJECT" => $SUBJECT,
				"MESSAGE" => $MESSAGE,
				"MESSAGE_DATE" => date("d.m.Y H:i:s")
			);
			$event->Send("NEW_FORUM_PRIV", LANG, $arFields);
			$strOKMessage = "Сообщение отправлено. \n";
		}
	}
}

$APPLICATION->AddChainItem($ShowName, "view_profile.php?UID=".$UID);
$APPLICATION->SetTitle($strTextType);
$APPLICATION->SetAdditionalCSS("/bitrix/php_interface/".LANG."/forum.css");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_after.php");

$path2curdir = str_replace("\\\\", "/", dirname(__FILE__)."/");
if (file_exists($path2curdir."menu.php"))
	include($path2curdir."menu.php");
elseif (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/install/public/".LANG."/menu.php"))
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/install/public/".LANG."/menu.php");
else
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/install/public/en/menu.php");

if (!$bUserFound)
{
	$strErrorMessage .= "Пользователь с кодом $UID не найден. \n";
}
?>

<?echo ShowMessage(array("MESSAGE" => $strErrorMessage, "TYPE" => "ERROR"));?>
<?echo ShowMessage(array("MESSAGE" => $strOKMessage, "TYPE" => "OK"));?>

<?
if ($bUserFound):
	?>
	<br>
	<form action="send_message.php" method="POST" name="REPLIER">
	<input type="hidden" name="ACTION" value="SEND">
	<input type="hidden" name="TYPE" value="<?echo $TYPE; ?>">
	<input type="hidden" name="UID" value="<?echo $UID; ?>">

	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="forumborder"><tr><td>

	<table border="0" cellpadding="1" cellspacing="1" width="100%">
		<tr>
			<td colspan="2" class="forumhead">
				<font class="forumheadtext">&nbsp;<b><?echo $strTextType; ?> кому</b></font>
			</td>
		</tr>
		<tr>
			<td class="forumbody"><font class="forumheadtext">&nbsp;Имя</font></td>
			<td class="forumbody">
				<font class="forumbodytext"><?echo $ShowName; ?></font>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="forumhead">
				<font class="forumheadtext">&nbsp;<b><?echo $strTextType; ?> от</b></font>
			</td>
		</tr>
		<tr>
			<td class="forumbody"><font class="forumheadtext">&nbsp;Имя</font></td>
			<td class="forumbody"><font class="forumbodytext">
				<?if ($USER->IsAuthorized()):?>
					<?echo $ShowMyName; ?>
				<?else:?>
					<input type="text" name="NAME" value="<?echo htmlspecialchars($NAME); ?>" size="25">
				<?endif;?></font>
			</td>
		</tr>
		<tr>
			<td class="forumbody">
				<font class="forumheadtext"><nobr>&nbsp;<?
				if ($TYPE=="ICQ") echo "Номер ICQ";
				else echo "E-Mail адрес";
				?> </nobr></font>
			</td>
			<td class="forumbody"><font class="forumbodytext">
				<?
				if ($USER->IsAuthorized() && ($TYPE=="ICQ" && strlen($y_PERSONAL_ICQ)>0 || $TYPE=="MAIL" && strlen($y_EMAIL)>0)):
					if ($TYPE=="ICQ") echo $y_PERSONAL_ICQ;
					else echo $y_EMAIL;
				else:
					?><input type="text" name="EMAIL" value="<?echo htmlspecialchars($EMAIL); ?>" size="25"><?
				endif;
				?></font>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="forumhead">
				<font class="forumheadtext">&nbsp;<b><?echo GetMessage("MESSAGE_CONTENT");?></b></font>
			</td>
		</tr>
		<tr>
			<td class="forumbody" valign="top">
				<font class="forumheadtext">&nbsp;Тема</font>
			</td>
			<td class="forumbody">
				<input type="text" name="SUBJECT" value="<?echo htmlspecialchars($SUBJECT); ?>" size="50" maxlength="50">
			</td>
		</tr>
		<tr>
			<td class="forumbody" valign="top">
				<font class="forumheadtext">&nbsp;Сообщение</font>
			</td>
			<td class="forumbody">
				<textarea cols="45" rows="12" wrap="soft" name="MESSAGE"><?echo htmlspecialchars($MESSAGE); ?></textarea>
			</td>
		</tr>
		<tr>
			<td  class="forumbody" align="center" colspan="2">
				<input type="submit" value="Отправить <?echo $strTextType; ?>">
			</td>
		</tr>
	</table>

	</td></tr>
	</table>
	</form>
	<?
endif;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog.php");
?>