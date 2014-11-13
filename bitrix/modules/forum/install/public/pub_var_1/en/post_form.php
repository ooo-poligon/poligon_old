<?
if (!is_set($MESSAGE_TYPE) || (($MESSAGE_TYPE!="REPLY") && ($MESSAGE_TYPE!="EDIT"))) $MESSAGE_TYPE = "NEW";

$FID = IntVal($FID);
$TID = IntVal($TID);
$MID = IntVal($MID);

if (
	$MESSAGE_TYPE=="REPLY" && $TID>0 && CForumMessage::CanUserAddMessage($TID, $USER->GetUserGroupArray(), $USER->GetID())
	|| $MESSAGE_TYPE=="NEW" && $FID>0 && CForumTopic::CanUserAddTopic($FID, $USER->GetUserGroupArray(), $USER->GetID())
	|| $MESSAGE_TYPE=="EDIT" && $MID>0 && CForumMessage::CanUserUpdateMessage($MID, $USER->GetUserGroupArray(), IntVal($USER->GetID()))
	)
{
	$str_USE_SMILES = "Y";
	$str_AUTHOR_ID = IntVal($USER->GetParam("USER_ID"));

	if ($MESSAGE_TYPE=="EDIT")
	{
		$arMessage = CForumMessage::GetByID($MID);
		if ($arMessage)
		{
			$arTopic = CForumTopic::GetByID($arMessage["TOPIC_ID"]);
			$str_AUTHOR_NAME = htmlspecialchars($arMessage["AUTHOR_NAME"]);
			$str_AUTHOR_EMAIL = htmlspecialchars($arMessage["AUTHOR_EMAIL"]);
			$str_TITLE = htmlspecialchars($arTopic["TITLE"]);
			$str_DESCRIPTION = htmlspecialchars($arTopic["DESCRIPTION"]);
			$str_POST_MESSAGE = htmlspecialchars($arMessage["POST_MESSAGE"]);
			$str_ICON_ID = IntVal($arTopic["ICON_ID"]);
			$str_USE_SMILES = ($arMessage["USE_SMILES"]=="Y") ? "Y" : "N";
			$str_AUTHOR_ID = IntVal($arMessage["AUTHOR_ID"]);
			$str_ATTACH_IMG = $arMessage["ATTACH_IMG"];
		}
	}

	if ($bVarsFromForm)
	{
		$str_AUTHOR_NAME = htmlspecialchars($AUTHOR_NAME);
		$str_AUTHOR_EMAIL = htmlspecialchars($AUTHOR_EMAIL);
		$str_TITLE = htmlspecialchars($TITLE);
		$str_DESCRIPTION = htmlspecialchars($DESCRIPTION);
		$str_POST_MESSAGE = htmlspecialchars($POST_MESSAGE);
		$str_ICON_ID = IntVal($ICON_ID);
		$str_USE_SMILES = ($USE_SMILES=="Y") ? "Y" : "N";
	}
	?>
	<script language="Javascript">
	<?include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/forum_js.php")?>
	</script>
	<form action="<?echo $APPLICATION->GetCurPage();?>?FID=<?echo $FID;?>&TID=<?echo $TID;?>#postform" method="post" name="REPLIER" enctype="multipart/form-data" onsubmit="return ValidateForm(this);">
		<input type="hidden" name="MID" value="<?echo $MID;?>">
		<input type="hidden" name="MESSAGE_TYPE" value="<?echo $MESSAGE_TYPE;?>">
		<input type="hidden" name="AUTHOR_ID" value="<?echo $str_AUTHOR_ID;?>">
		<tr class="forumbody">
			<td colspan="2" align="center"><font class="forumheadtext">
				<a name="postform"></a>
				<?echo ShowMessage(array("MESSAGE" => $strErrorMessage, "TYPE" => "ERROR"));?>
				<?echo ShowMessage(array("MESSAGE" => $strOKMessage, "TYPE" => "OK"));?>
				</font>
			</td>
		</tr>
		<tr class="forumborder">
			<td colspan="2"><font class="forumheadtext"><b>
				<?
				if ($MESSAGE_TYPE=="NEW")
					echo "Create new topic in \"".$arForum["NAME"]."\" forum";
				elseif ($MESSAGE_TYPE=="REPLY")
					echo "Reply form";
				else
					echo "Modify message";
				?></b></font>
			</td>
		</tr>
		<?if (($MESSAGE_TYPE=="NEW" || $MESSAGE_TYPE=="REPLY") && !$USER->IsAuthorized()
	  		|| $MESSAGE_TYPE=="EDIT" && $str_AUTHOR_ID<=0):?>
			<tr class="forumhead">
				<td colspan="2">
					<font class="forumheadtext"><b>Unregistered User Info</b></font>
				</td>
			</tr>
			<tr valign="top" class="forumbody">
				<td>
					<font class="forumheadtext">
					<b><font color="#FF0000">*</font></b>
					Enter your Name</font>
				</td>
				<td width="100%">
					<input type="text" name="AUTHOR_NAME" size="40" maxlength="64" value="<?echo (strlen($str_AUTHOR_NAME)>0) ? $str_AUTHOR_NAME : "Guest";?>">
				</td>
			</tr>
			<?if ($arForum["ASK_GUEST_EMAIL"]=="Y"):?>
				<tr valign="top" class="forumbody">
					<td>
						<font class="forumheadtext">
						Enter your E-Mail address</font>
					</td>
					<td width="100%">
						<input type="text" name="AUTHOR_EMAIL" size="40" maxlength="64" value="<?echo (strlen($str_AUTHOR_EMAIL)>0) ? $str_AUTHOR_EMAIL : "";?>">
					</td>
				</tr>
			<?endif;?>
		<?endif;?>
		<?if ($MESSAGE_TYPE=="NEW" 
			|| $MESSAGE_TYPE=="EDIT" 
				&& CForumTopic::CanUserUpdateTopic($TID, $USER->GetUserGroupArray(), $USER->GetID())):?>
			<tr class="forumhead">
				<td colspan="2">
					<font class="forumheadtext"><b>Topic Settings</b></font>
				</td>
			</tr>
			<tr valign="top" class="forumbody">
				<td>
					<font class="forumheadtext">
					<b><font color="#FF0000">*</font></b>
					Topic Title</font>
				</td>
				<td width="100%">
					<input type="text" name="TITLE" size="50" maxlength="70" value="<?echo (strlen($str_TITLE)>0) ? $str_TITLE : "";?>">
				</td>
			</tr>
			<tr valign="top" class="forumbody">
				<td>
					<font class="forumheadtext">Topic Description</font>
				</td>
				<td width="100%">
					<input type="text" name="DESCRIPTION" size="50" maxlength="70" value="<?echo (strlen($str_DESCRIPTION)>0) ? $str_DESCRIPTION : "";?>">
				</td>
			</tr>
			<tr valign="top" class="forumbody">
				<td>
					<font class="forumheadtext">Icon</font>
				</td>
				<td width="100%">
					<?
					echo ForumPrintIconsList(7, "ICON_ID", $str_ICON_ID, "no icon", LANG);
					?>
				</td>
			</tr>
		<?endif;?>
		<tr class="forumhead">
			<td colspan="2">
				<font class="forumheadtext"><b>Enter your Post</b></font>
			</td>
		</tr>
		<tr valign="top" class="forumbody">
			<td>
				<?if ($arForum["ALLOW_SMILES"]=="Y"):?>
					<br>
					<table align="center" cellspacing="1" cellpadding="5" border="0" style="border-width:1px; border-color:#999999; border-style:solid;">
						<tr>
							<td colspan="3" align="center" style="border-bottom:1px; border-bottom-color:#999999; border-bottom-style:solid;">
								<font class="forumheadtext">Clickable Smilies</font>
							</td>
						</tr>
						<?
						echo ForumPrintSmilesList(3, LANG);
						?>
					</table>
				<?endif;?>
			</td>
			<td width="100%">



				<table cellpadding='2' cellspacing='2' width='100%' align='center'>
					<tr>
						<td nowrap width='10%'>
							<input type='button' accesskey='b' value=' B ' onClick='simpletag("B")' style="font-size: 8.5pt; font-family: verdana, helvetica, sans-serif; vertical-align: middle; font-weight:bold" name='B' title="Bold (alt + b)" onMouseOver="show_hints('bold')">
							<input type='button' accesskey='i' value=' I ' onClick='simpletag("I")' style="font-size: 8.5pt; font-family: verdana, helvetica, sans-serif; vertical-align: middle; font-style:italic" name='I' title="Italic (alt + i)" onMouseOver="show_hints('italic')">
							<input type='button' accesskey='u' value=' U ' onClick='simpletag("U")' style="font-size: 8.5pt; font-family: verdana, helvetica, sans-serif; vertical-align: middle; text-decoration:underline" name='U' title="Underlined (alt + u)" onMouseOver="show_hints('under')">

							<select name='ffont' style="font-size: 8.5pt; font-family: verdana, helvetica, sans-serif; vertical-align: middle" onchange="alterfont(this.options[this.selectedIndex].value, 'FONT')" onMouseOver="show_hints('font')">
								<option value='0'>Font</option>
								<option value='Arial' style='font-family:Arial'>Arial</option>
								<option value='Times' style='font-family:Times'>Times</option>
								<option value='Courier' style='font-family:Courier'>Courier</option>
								<option value='Impact' style='font-family:Impact'>Impact</option>
								<option value='Geneva' style='font-family:Geneva'>Geneva</option>
								<option value='Optima' style='font-family:Optima'>Optima</option>
							</select>
							<select name='fcolor' style="font-size: 8.5pt; font-family: verdana, helvetica, sans-serif; vertical-align: middle" onchange="alterfont(this.options[this.selectedIndex].value, 'COLOR')" onMouseOver="show_hints('color')">
								<option value='0'>Color</option>
								<option value='blue' style='color:blue'>Blue</option>
								<option value='red' style='color:red'>Red</option>
								<option value='gray' style='color:gray'>Grey</option>
								<option value='green' style='color:green'>Green</option>
							</select>
							&nbsp; <font class="text"><a href='javascript:closeall();' title="Close all opened tags" onMouseOver="show_hints('close')">Close all tags</a></font>
						</td>
					</tr>
					<tr>
						<td align='left'>
							<input type='button' accesskey='h' value=' http:// ' onClick='tag_url()' style="font-size: 8.5pt; font-family: verdana, helvetica, sans-serif; vertical-align: middle" name='url' title="Hyperlink (alt + h)" onMouseOver="show_hints('url')">
							<input type='button' accesskey='g' value=' IMG ' onClick='tag_image()' style="font-size: 8.5pt; font-family: verdana, helvetica, sans-serif; vertical-align: middle" name='img' title="Image attachment (alt + g)" onMouseOver="show_hints('img')">
							<input type='button' accesskey='q' value=' QUOTE ' onClick='simpletag("QUOTE")' style="font-size: 8.5pt; font-family: verdana, helvetica, sans-serif; vertical-align: middle" name='QUOTE' title="Quote (alt + q)" onMouseOver="show_hints('quote')">
							<input type='button' accesskey='p' value=' CODE ' onClick='simpletag("CODE")' style="font-size: 8.5pt; font-family: verdana, helvetica, sans-serif; vertical-align: middle" name='CODE' title="Code (alt + p)" onMouseOver="show_hints('code')">
							<input type='button' accesskey='l' value=' LIST ' onClick='tag_list()' style="font-size: 8.5pt; font-family: verdana, helvetica, sans-serif; vertical-align: middle" name="LIST" title="Create list (alt + l)" onMouseOver="show_hints('list')">
						</td>
					</tr>
					<tr>
						<td align='left' valign='middle'>
							<font class="text">
							Open Tags:&nbsp;<input type='text' name='tagcount' size='3' maxlength='3' style='font-size:10px;font-family:verdana,arial;border: 0 solid;font-weight:bold;' readonly class='forumbody' value="0">
							&nbsp;<input type='text' name='helpbox' size='50' maxlength='120' style='width:80%;font-size:10px;font-family:verdana,arial;border: 0 solid;' readonly class='forumbody' value="">
							</font>
						</td>
					</tr>
				</table>



				<textarea style="width:100%" rows="15" wrap="soft" name="POST_MESSAGE" tabindex="3" onselect="storeCaret(this);" onclick="storeCaret(this);" onkeyup="storeCaret(this);"><?echo (strlen($str_POST_MESSAGE)>0) ? $str_POST_MESSAGE: "";?></textarea>
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td colspan="2">
							<font class="forumbodytext">To quote the message you are replying to, select it and click <b>&quot;Quote&quot;</b>.<br><br>
						</td>
					</tr>
					<?if ($arForum["ALLOW_SMILES"]=="Y"):?>
						<tr>
							<td>
								<input type="checkbox" name="USE_SMILES" value="Y" <? echo ($str_USE_SMILES=="Y") ? "checked" : "";?>>
							</td>
							<td width="100%">
								<font class="forumbodytext">Do you want to <B>use</B> smileys in this message?</font>
							</td>
						</tr>
					<?endif;?>
					<?if ($arForum["ALLOW_UPLOAD"]=="Y" || $arForum["ALLOW_UPLOAD"]=="F" || $arForum["ALLOW_UPLOAD"]=="A"):?>
						<tr>
							<td colspan="2"><br>
								<font class="forumbodytext">
								Load attachment<br>
								<input name="ATTACH_IMG" size="40" type="file"><br>
								<?if ($MESSAGE_TYPE=="EDIT"):?>
									<input type="checkbox" name="ATTACH_IMG_del" value="Y"> Delete file 
								<?endif;?>
								<?if (strlen($str_ATTACH_IMG)>0):?>
									<br>
									<?echo CFile::ShowImage($str_ATTACH_IMG, 200, 200, "border=0", "", true)?>
								<?endif;?>
								</font>
							</td>
						</tr>
					<?endif;?>
				</table>
			</td>
		</tr>
		<tr class="forumbody">
			<td>&nbsp;</td>
			<td>
				<input type="hidden" name="forum_post_action" value="save">
				<input type="submit" name="submit" value="<?
				if ($MESSAGE_TYPE=="NEW")
					echo "Post New Topic";
				elseif ($MESSAGE_TYPE=="REPLY")
					echo "Reply";
				else
					echo "Save";
				?>" tabindex="4" class="forminput">
			</td>
		</tr>
	</form>
	<?
}
?>