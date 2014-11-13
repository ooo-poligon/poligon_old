<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (!CModule::IncludeModule("forum"))
{
	LocalRedirect("index.php");
	die();
}

$UID = IntVal($UID);

$bUserFound = False;
$db_userX = CUser::GetByID($UID);
if ($db_userX->ExtractFields("f_", True))
{
	$bUserFound = True;
}

$ShowName = "";
if ($bUserFound)
{
	if (strlen($ShowName)<=0 && $f_SHOW_NAME=="Y")
		$ShowName = $f_NAME." ".$f_LAST_NAME;
	if (strlen(Trim($ShowName))<=0) $ShowName = $f_LOGIN;
}
if (strlen($ShowName)<=0) $ShowName = $UID;

$APPLICATION->SetTitle($ShowName);
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
	$strErrorMessage .= "User #$UID is not found. \n";
}
?>

<?echo ShowMessage(array("MESSAGE" => $strErrorMessage, "TYPE" => "ERROR"));?>
<?echo ShowMessage(array("MESSAGE" => $strOKMessage, "TYPE" => "OK"));?>

<?
if ($bUserFound):
	$db_res = CForumUser::GetList(array(), array("USER_ID"=>$UID));
	$db_res->ExtractFields("fu_", True);
	?>
	<br>
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="forumborder"><tr><td>
	<table border="0" cellpadding="1" cellspacing="1" width="100%">
		<tr>
		  <td class="forumhead" colspan="2" align="center" height="25" valign="middle">
			<font class="forumheadtext"><b>Profile</b></font>
		  </td>
		</tr>
		<tr>
		  <td class="forumbody"><font class="forumheadtext">&nbsp;Name:</font></td>
		  <td class="forumbody"><font class="forumbodytext"><?echo $ShowName; ?></font></td>
		</tr>
		<tr>
		  <td class="forumbody"><font class="forumheadtext">&nbsp;Description:</font></td>
		  <td class="forumbody"><font class="forumbodytext"><?echo $fu_DESCRIPTION; ?></font></td>
		</tr>
		<?if (strlen($f_EMAIL)>0):?>
			<tr>
				<td class="forumbody"><font class="forumheadtext">&nbsp;E-Mail address:</font></td>
				<td class="forumbody"><font class="forumbodytext"><a href="send_message.php?TYPE=MAIL&UID=<?echo $UID; ?>">Send message</a></font></td>
			</tr>
		<?endif;?>
		<?if (strlen($f_PERSONAL_ICQ)>0):?>
			<tr>
				<td class="forumbody"><font class="forumheadtext">&nbsp;ICQ number:</font></td>
				<td class="forumbody"><font class="forumbodytext"><a href="send_message.php?TYPE=ICQ&UID=<?echo $UID; ?>">Send message</a></font></td>
			</tr>
		<?endif;?>
		<tr>
			<td class="forumbody"><font class="forumheadtext">&nbsp;Sex:</font></td>
			<td class="forumbody">
				<font class="forumbodytext"><?if ($f_PERSONAL_GENDER=="M") echo "Male"; elseif ($f_PERSONAL_GENDER=="F") echo "Female";?></font>
			</td>
		</tr>
		<tr>
			<td class="forumbody"><font class="forumheadtext">&nbsp;Location:</font></td>
			<td class="forumbody">
				<font class="forumbodytext">
				<?
				echo GetCountryByID($f_PERSONAL_COUNTRY);
				if (IntVal($f_PERSONAL_COUNTRY)>0 && strlen($f_PERSONAL_CITY)>0)
					echo ", ";
				echo $f_PERSONAL_CITY;
				?>
				</font>
			</td>
		</tr>
		<tr>
		  <td class="forumbody"><font class="forumheadtext">&nbsp;Web-site:</font></td>
		  <td class="forumbody"><font class="forumbodytext">
			  <?
			  if (strlen($f_PERSONAL_WWW)>0)
			  {
					?><a href="<?echo $f_PERSONAL_WWW;?>" target="_blank"><?echo $f_PERSONAL_WWW;?></a><?
			  }
			  ?></font></td>
		</tr>
		<tr>
		  <td class="forumbody"><font class="forumheadtext">&nbsp;Occupation:</font></td>
		  <td class="forumbody"><font class="forumbodytext"><?echo $f_PERSONAL_PROFESSION; ?></font></td>
		</tr>
		<tr>
		  <td class="forumbody"><font class="forumheadtext">&nbsp;Hobby:</font></td>
		  <td class="forumbody"><font class="forumbodytext"><?echo $fu_INTERESTS; ?></font></td>
		</tr>
		<tr>
		  <td class="forumbody"><font class="forumheadtext">&nbsp;Date of birth:</font></td>
		  <td class="forumbody"><font class="forumbodytext"><?echo $f_PERSONAL_BIRTHDATE; ?></font></td>
		</tr>
		<tr>
		  <td class="forumbody"><font class="forumheadtext">&nbsp;Avatar:</font></td>
		  <td class="forumbody"><font class="forumbodytext">
			<?if (strlen($fu_AVATAR)>0):?>
				<?echo CFile::ShowImage($fu_AVATAR, 90, 90, "border=0", "", true)?>
			<?endif;?>
		  </font></td>
		</tr>
		<tr>
		  <td class="forumbody"><font class="forumheadtext">&nbsp;Photo:</font></td>
		  <td class="forumbody"><font class="forumbodytext">
			<?if (strlen($f_PERSONAL_PHOTO)>0):?>
				<?echo CFile::ShowImage($f_PERSONAL_PHOTO, 200, 200, "border=0", "", true)?>
			<?endif;?>
		  </font></td>
		</tr>

		<tr>
		  <td class="forumbody" colspan="2" height="28">&nbsp;</td>
		</tr>
		<tr>
		  <td class="forumhead" colspan="2" align="center" height="25" valign="middle">
			<font class="forumheadtext"><b>Statistic</b></font>
		  </td>
		</tr>
		<?
		if (IntVal($fu_LAST_POST)>0)
		{
			$arMessage = CForumMessage::GetByID(IntVal($fu_LAST_POST));
			if (!$arMessage)
			{
				CForumUser::SetStat($UID);
				$db_res = CForumUser::GetList(array(), array("USER_ID"=>$UID));
				$db_res->ExtractFields("fu_", True);
			}
		}
		?>
		<tr>
		  <td class="forumbody"><font class="forumheadtext">&nbsp;Posts:</font></td>
		  <td class="forumbody"><font class="forumbodytext"><?echo $fu_NUM_POSTS; ?></font></td>
		</tr>
		<tr>
		  <td class="forumbody"><font class="forumheadtext">&nbsp;Joined:</font></td>
		  <td class="forumbody"><font class="forumbodytext"><?echo $fu_DATE_REG; ?></font></td>
		</tr>
		<tr>
		  <td class="forumbody"><font class="forumheadtext">&nbsp;Last post date:</font></td>
		  <td class="forumbody"><font class="forumbodytext"><?echo $fu_LAST_VISIT; ?></font></td>
		</tr>
		<?
		if (IntVal($fu_LAST_POST)>0):
			?>
			<tr>
			  <td class="forumbody"><font class="forumheadtext">&nbsp;Last post:</font></td>
			  <td class="forumbody"><font class="forumbodytext"><a href="read.php?TID=<?echo $arMessage["TOPIC_ID"]; ?>&MID=<?echo $fu_LAST_POST; ?>#message<?echo $fu_LAST_POST; ?>">Here</a></font></td>
			</tr>
		<?endif;?>
	</table>
	<td><tr></table>
	<?
endif;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog.php");
?>