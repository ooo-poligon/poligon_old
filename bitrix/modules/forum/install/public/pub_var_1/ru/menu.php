<?
// $FID - forum code
// $TID - topic code
// $MID - message code

$sSection = strtoupper(basename($APPLICATION->GetCurPage(), ".php"));
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="forumtoolblock">
  <tr>
	<td width="100%" class="forumtoolbar">
		<table border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td><div class="forumtoolsection"></div></td>
				<td><div class="forumtoolsection"></div></td>
				<td><a href="index.php" title="������ �������"><img src="/bitrix/images/forum/icon_flist_d.gif" width="16" height="16" border="0" alt="������ �������" hspace="4"></a></td>
				<td><a href="index.php" title="������ �������" class="forumtoolbutton">������</a></td>
<?if(CModule::IncludeModule("search")):?>
				<td><div class="forumtoolseparator"></div></td>
				<td><a href="search.php<?if($FID>0) echo "?FORUM_ID=".$FID?>" title="����� �� �������"><img src="/bitrix/images/forum/icon_search_d.gif" width="16" height="16" border="0" alt="����� �� �������" hspace="4"></a></td>
				<td><a href="search.php<?if($FID>0) echo "?FORUM_ID=".$FID?>" title="����� �� �������" class="forumtoolbutton">�����</a></td>
<?endif;?>
				<td><div class="forumtoolseparator"></div></td>
				<td><a href="help.php" title="������"><img src="/bitrix/images/forum/icon_help_d.gif" width="16" height="16" border="0" alt="������" hspace="4"></a></td>
				<td><a href="help.php" title="������" class="forumtoolbutton">������</a></td>

				<td class="forumtoolbutton">&nbsp;</td>
				<td><div class="forumtoolsection"></div></td>
				<td><div class="forumtoolsection"></div></td>
<?if ($USER->IsAuthorized()):?>
				<td><a href="profile.php" title="�������� ���� �������"><img src="/bitrix/images/forum/icon_profile_d.gif" width="15" height="15" border="0" alt="�������� ���� �������" hspace="4"></a></td>
				<td><a href="profile.php" title="�������� ���� �������" class="forumtoolbutton">�������</a></td>

				<td><div class="forumtoolseparator"></div></td>
				<td><a href="<?echo $APPLICATION->GetCurPageParam("logout=yes", array("login", "logout", "register", "forgot_password", "change_password"));?>" title="��������� �����"><img src="/bitrix/images/forum/icon_logout_d.gif" width="16" height="16" border="0" alt="��������� �����" hspace="4"></a></td>
				<td><a href="<?echo $APPLICATION->GetCurPageParam("logout=yes", array("login", "logout", "register", "forgot_password", "change_password"));?>" title="��������� �����" class="forumtoolbutton">�����</a></td>
<?else:?>
				<td><a href="forum_auth.php?back_url=<?echo urlencode($APPLICATION->GetCurPageParam("", array("login", "logout", "register", "forgot_password", "change_password")));?>" title="�������������� �� �����"><img src="/bitrix/images/forum/icon_login_d.gif" width="16" height="16" border="0" alt="�������������� �� �����" hspace="4"></a></td>
				<td><a href="forum_auth.php?back_url=<?echo urlencode($APPLICATION->GetCurPageParam("", array("login", "logout", "register", "forgot_password", "change_password")));?>" title="�������������� �� �����" class="forumtoolbutton">�����</a></td>

				<td><div class="forumtoolseparator"></div></td>
				<td><a href="forum_auth.php?register=yes&back_url=<?echo urlencode($APPLICATION->GetCurPageParam("", array("login", "register", "logout", "forgot_password", "change_password")));?>" title="����������� ������ ������������"><img src="/bitrix/images/forum/icon_reg_d.gif" width="16" height="16" border="0" alt="����������� ������ ������������" hspace="4"></a></td>
				<td><a href="forum_auth.php?register=yes&back_url=<?echo urlencode($APPLICATION->GetCurPageParam("", array("login", "register", "logout", "forgot_password", "change_password")));?>" title="����������� ������ ������������" class="forumtoolbutton">�����������</a></td>
<?endif;?>
			</tr>
		</table>
	</td>
  </tr>
<?if($sSection == "INDEX"):?>
  <tr>
	<td width="100%" class="forumtoolbar">
		<table border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td><div class="forumtoolsection"></div></td>
				<td><div class="forumtoolsection"></div></td>
				<td><a href="index.php?ACTION=SET_BE_READ" title="�������� ��� ���� ������� ��� �����������"><img src="/bitrix/images/forum/icon_read.gif" width="16" height="16" border="0" alt="�������� ��� �����������" hspace="4"></a></td>
				<td><a href="index.php?ACTION=SET_BE_READ" title="�������� ��� ���� ������� ��� �����������" class="forumtoolbutton">�������� ��� �����������</a></td>
			</tr>
		</table>
	</td>
  </tr>
<?endif;?>
<?if(!$bSimplePanel):?>
<?if($USER->IsAuthorized() && ($sSection=="LIST" || $sSection=="READ")):?>
  <tr>
	<td width="100%" class="forumtoolbar">
		<table border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td><div class="forumtoolsection"></div></td>
				<td><div class="forumtoolsection"></div></td>
				<td><a href="<?echo $APPLICATION->GetCurPageParam("FID=".$FID."&ACTION=FORUM_SUBSCRIBE", array("FID", "ACTION", "login", "register", "logout"));?>" title="����������� �� ����� ��������� ������"><img src="/bitrix/images/forum/icon_subscr_forum.gif" width="16" height="16" border="0" alt="����������� �� ����� ��������� ������" hspace="4"></a></td>
				<td><a href="<?echo $APPLICATION->GetCurPageParam("FID=".$FID."&ACTION=FORUM_SUBSCRIBE", array("FID", "ACTION", "login", "register", "logout"));?>" title="����������� �� ����� ��������� ������" class="forumtoolbutton">����������� �� �����</a></td>
<?if($sSection=="READ" && $arTopic["STATE"]=="Y"):?>
				<td><div class="forumtoolseparator"></div></td>
				<td><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $TID?>&ACTION=TOPIC_SUBSCRIBE" title="����������� �� ����� ��������� ����"><img src="/bitrix/images/forum/icon_subscr_topic.gif" width="16" height="16" border="0" alt="����������� �� ����� ��������� ����" hspace="4"></a></td>
				<td><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $TID?>&ACTION=TOPIC_SUBSCRIBE" title="����������� �� ����� ��������� ����" class="forumtoolbutton">����������� �� ����</a></td>
<?endif;?>
				<td><div class="forumtoolseparator"></div></td>
				<td><a href="subscr_list.php" title="�������� �������� �� ����������"><img src="/bitrix/images/forum/icon_subscribe_d.gif" width="16" height="16" border="0" alt="�������� �������� �� ����������" hspace="4"></a></td>
				<td><a href="subscr_list.php" title="�������� �������� �� ����������" class="forumtoolbutton">�������� ��������</a></td>
			</tr>
		</table>
	</td>
  </tr>
<?endif?>
<?endif; //simple?>

<?if($sSection=="LIST" || $sSection=="READ" || $sSection == "NEW_TOPIC"):?>
  <tr>
	<td width="100%" class="forumtoolbar">
		<table border="0" cellspacing="0" cellpadding="0">
			<tr>
<?if(CForumTopic::CanUserAddTopic($FID, $USER->GetUserGroupArray(), $USER->GetID())):?>
				<td><div class="forumtoolsection"></div></td>
				<td><div class="forumtoolsection"></div></td>
				<td><a href="new_topic.php?FID=<?echo $FID;?>" title="�������� ����� ����"><img src="/bitrix/images/forum/new_topic.gif" width="16" height="16" border="0" alt="�������� ����� ����" hspace="4"></a></td>
				<td><a href="new_topic.php?FID=<?echo $FID;?>" title="�������� ����� ����" class="forumtoolbutton"><b>����� ����</b></a></td>
				<td class="forumtoolbutton">&nbsp;</td>
<?endif;?>
<?if($sSection=="LIST"):?>
				<td><div class="forumtoolsection"></div></td>
				<td><div class="forumtoolsection"></div></td>
				<td><a href="list.php?FID=<?echo $FID; ?>&amp;ACTION=SET_BE_READ" title="�������� ��� ���� ����� ������ ��� �����������"><img src="/bitrix/images/forum/icon_read.gif" width="16" height="16" border="0" alt="�������� ��� �����������" hspace="4"></a></td>
				<td><a href="list.php?FID=<?echo $FID; ?>&amp;ACTION=SET_BE_READ" title="�������� ��� ���� ����� ������ ��� �����������" class="forumtoolbutton">�������� ��� �����������</a></td>
<?endif;?>
<?if($sSection=="READ" || $sSection == "NEW_TOPIC"):?>
				<td><div class="forumtoolsection"></div></td>
				<td><div class="forumtoolsection"></div></td>
				<td><a href="list.php?FID=<?echo $FID; ?>" title="������ ��� ������"><img src="/bitrix/images/forum/icon_tlist_d.gif" width="16" height="16" border="0" alt="������ ��� ������" hspace="4"></a></td>
				<td><a href="list.php?FID=<?echo $FID; ?>" title="������ ��� ������" class="forumtoolbutton">������ ���</a></td>
				<td class="forumtoolbutton">&nbsp;</td>
<?endif?>
<?if($sSection=="READ"):?>
<?
list($iPREV_TOPIC, $iNEXT_TOPIC) = CForumTopic::GetNeighboringTopics($TID, $USER->GetUserGroupArray());
if (IntVal($iPREV_TOPIC)>0):?>
				<td><div class="forumtoolseparator"></div></td>
				<td><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $iPREV_TOPIC ?>" title="���������� ����"><img src="/bitrix/images/forum/icon_topic_prev.gif" width="16" height="16" border="0" alt="���������� ����" hspace="2"></a></td>
				<td><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $iPREV_TOPIC ?>" title="���������� ����" class="forumtoolbutton">���������� ����</a></td>
<?endif;?>
<?if (IntVal($iNEXT_TOPIC)>0):?>
				<td><div class="forumtoolseparator"></div></td>
				<td><div style="width:4px;"></div></td>
				<td><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $iNEXT_TOPIC; ?>" title="��������� ����" class="forumtoolbutton">��������� ����</a></td>
				<td><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $iNEXT_TOPIC; ?>" title="��������� ����"><img src="/bitrix/images/forum/icon_topic_next.gif" width="16" height="16" border="0" alt="��������� ����" hspace="2"></a></td>
<?endif;?>
<?endif;?>
			</tr>
		</table>
	</td>
  </tr>
<?if($sSection == "READ" && ForumCurrUserPermissions($FID)>="Q"):?>
  <tr>
	<td width="100%" class="forumtoolbar">
		<table border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td><div class="forumtoolsection"></div></td>
				<td><div class="forumtoolsection"></div></td>
				<td><div style="width:4px;"></div></td>
				<td><a href="move.php?FID=<?echo $FID;?>&TID=<?echo $TID;?>" title="��������� ����" class="forumtoolbutton">��������� ����</a></td>
				<td><div class="forumtoolseparator"></div></td>
				<td><div style="width:4px;"></div></td>
				<td><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $TID;?>&ACTION=<?echo (IntVal($arTopic["SORT"])!=150)?"SET_ORDINARY":"SET_TOP";?>" title="<?echo (IntVal($arTopic["SORT"])!=150)?"����� ������������":"���������� c�����";?>" class="forumtoolbutton"><?echo (IntVal($arTopic["SORT"])!=150)?"����� ������������":"���������� c�����";?></a></td>
				<td><div class="forumtoolseparator"></div></td>
				<td><div style="width:4px;"></div></td>
				<td><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $TID;?>&ACTION=<?echo ($arTopic["STATE"]!="Y")?"STATE_Y":"STATE_N";?>" title="<?echo ($arTopic["STATE"]!="Y")?"������� ����":"������� ����";?>" class="forumtoolbutton"><?echo ($arTopic["STATE"]!="Y")?"������� ����":"������� ����";?></a></td>
<?if (CForumTopic::CanUserDeleteTopic($TID, $USER->GetUserGroupArray(), $USER->GetID())):?>
				<td><div class="forumtoolseparator"></div></td>
				<td><div style="width:4px;"></div></td>
				<td><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $TID;?>&ACTION=DEL_TOPIC" title="������� ����" class="forumtoolbutton">������� ����</a></td>
<?endif;?>
			</tr>
		</table>
	</td>
  </tr>
  <?endif;?>

<?
endif;
?>
</table>
