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
				<td><a href="index.php" title="Forums list"><img src="/bitrix/images/forum/icon_flist_d.gif" width="16" height="16" border="0" alt="Forums list" hspace="4"></a></td>
				<td><a href="index.php" title="Forums list" class="forumtoolbutton">Forums</a></td>
<?if(CModule::IncludeModule("search")):?>
				<td><div class="forumtoolseparator"></div></td>
				<td><a href="search.php<?if($FID>0) echo "?FORUM_ID=".$FID?>" title="Search"><img src="/bitrix/images/forum/icon_search_d.gif" width="16" height="16" border="0" alt="Search" hspace="4"></a></td>
				<td><a href="search.php<?if($FID>0) echo "?FORUM_ID=".$FID?>" title="Search" class="forumtoolbutton">Search</a></td>
<?endif;?>
				<td><div class="forumtoolseparator"></div></td>
				<td><a href="help.php" title="Help"><img src="/bitrix/images/forum/icon_help_d.gif" width="16" height="16" border="0" alt="Help" hspace="4"></a></td>
				<td><a href="help.php" title="Help" class="forumtoolbutton">Help</a></td>

				<td class="forumtoolbutton">&nbsp;</td>
				<td><div class="forumtoolsection"></div></td>
				<td><div class="forumtoolsection"></div></td>
<?if ($USER->IsAuthorized()):?>
				<td><a href="profile.php" title="Profile"><img src="/bitrix/images/forum/icon_profile_d.gif" width="15" height="15" border="0" alt="Profile" hspace="4"></a></td>
				<td><a href="profile.php" title="Profile" class="forumtoolbutton">Profile</a></td>

				<td><div class="forumtoolseparator"></div></td>
				<td><a href="<?echo $APPLICATION->GetCurPageParam("logout=yes", array("login", "logout", "register", "forgot_password", "change_password"));?>" title="Logout"><img src="/bitrix/images/forum/icon_logout_d.gif" width="16" height="16" border="0" alt="Logout" hspace="4"></a></td>
				<td><a href="<?echo $APPLICATION->GetCurPageParam("logout=yes", array("login", "logout", "register", "forgot_password", "change_password"));?>" title="Logout" class="forumtoolbutton">Logout</a></td>
<?else:?>
				<td><a href="forum_auth.php?back_url=<?echo urlencode($APPLICATION->GetCurPageParam("", array("login", "logout", "register", "forgot_password", "change_password")));?>" title="Login"><img src="/bitrix/images/forum/icon_login_d.gif" width="16" height="16" border="0" alt="Login" hspace="4"></a></td>
				<td><a href="forum_auth.php?back_url=<?echo urlencode($APPLICATION->GetCurPageParam("", array("login", "logout", "register", "forgot_password", "change_password")));?>" title="Login" class="forumtoolbutton">Login</a></td>

				<td><div class="forumtoolseparator"></div></td>
				<td><a href="forum_auth.php?register=yes&back_url=<?echo urlencode($APPLICATION->GetCurPageParam("", array("login", "register", "logout", "forgot_password", "change_password")));?>" title="Register"><img src="/bitrix/images/forum/icon_reg_d.gif" width="16" height="16" border="0" alt="Register" hspace="4"></a></td>
				<td><a href="forum_auth.php?register=yes&back_url=<?echo urlencode($APPLICATION->GetCurPageParam("", array("login", "register", "logout", "forgot_password", "change_password")));?>" title="Register" class="forumtoolbutton">Register</a></td>
<?endif;?>
			</tr>
		</table>
	</td>
  </tr>
<?if(!$bSimplePanel):?>
<?if($USER->IsAuthorized() && ($sSection=="LIST" || $sSection=="READ")):?>
  <tr>
	<td width="100%" class="forumtoolbar">
		<table border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td><div class="forumtoolsection"></div></td>
				<td><div class="forumtoolsection"></div></td>
				<td><a href="<?echo $APPLICATION->GetCurPageParam("FID=".$FID."&ACTION=FORUM_SUBSCRIBE", array("FID", "ACTION", "login", "register", "logout"));?>" title="Subscribing to the forum messages"><img src="/bitrix/images/forum/icon_subscr_forum.gif" width="16" height="16" border="0" alt="Subscribing to the forum messages" hspace="4"></a></td>
				<td><a href="<?echo $APPLICATION->GetCurPageParam("FID=".$FID."&ACTION=FORUM_SUBSCRIBE", array("FID", "ACTION", "login", "register", "logout"));?>" title="Subscribing to the forum messages" class="forumtoolbutton">Subscribe to the forum messages</a></td>
<?if($sSection=="READ" && $arTopic["STATE"]=="Y"):?>
				<td><div class="forumtoolseparator"></div></td>
				<td><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $TID?>&ACTION=TOPIC_SUBSCRIBE" title="Subscribing to the topic messages"><img src="/bitrix/images/forum/icon_subscr_topic.gif" width="16" height="16" border="0" alt="Subscribing to the topic messages" hspace="4"></a></td>
				<td><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $TID?>&ACTION=TOPIC_SUBSCRIBE" title="Subscribing to the topic messages" class="forumtoolbutton">Subscribe to the topic messages</a></td>
<?endif;?>
				<td><div class="forumtoolseparator"></div></td>
				<td><a href="subscr_list.php" title="Change subscription to the new messages"><img src="/bitrix/images/forum/icon_subscribe_d.gif" width="16" height="16" border="0" alt="Change subscription to the new messages" hspace="4"></a></td>
				<td><a href="subscr_list.php" title="Change subscription to the new messages" class="forumtoolbutton">Change subscription</a></td>
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
				<td><a href="new_topic.php?FID=<?echo $FID;?>" title="Add new topic"><img src="/bitrix/images/forum/new_topic.gif" width="16" height="16" border="0" alt="Add new topic" hspace="4"></a></td>
				<td><a href="new_topic.php?FID=<?echo $FID;?>" title="Add new topic" class="forumtoolbutton"><b>New topic</b></a></td>
				<td class="forumtoolbutton">&nbsp;</td>
<?endif;?>
<?if($sSection=="READ" || $sSection == "NEW_TOPIC"):?>
				<td><div class="forumtoolsection"></div></td>
				<td><div class="forumtoolsection"></div></td>
				<td><a href="list.php?FID=<?echo $FID; ?>" title="Topics list"><img src="/bitrix/images/forum/icon_tlist_d.gif" width="16" height="16" border="0" alt="Topics list" hspace="4"></a></td>
				<td><a href="list.php?FID=<?echo $FID; ?>" title="Topics list" class="forumtoolbutton">Topics</a></td>
				<td class="forumtoolbutton">&nbsp;</td>
<?endif?>
<?if($sSection=="READ"):?>
<?
list($iPREV_TOPIC, $iNEXT_TOPIC) = CForumTopic::GetNeighboringTopics($TID, $USER->GetUserGroupArray());
if (IntVal($iPREV_TOPIC)>0):?>
				<td><div class="forumtoolseparator"></div></td>
				<td><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $iPREV_TOPIC ?>" title="Next Oldest Topic"><img src="/bitrix/images/forum/icon_topic_prev.gif" width="16" height="16" border="0" alt="Next Oldest Topic" hspace="2"></a></td>
				<td><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $iPREV_TOPIC ?>" title="Next Oldest Topic" class="forumtoolbutton">Next Oldest Topic</a></td>
<?endif;?>
<?if (IntVal($iNEXT_TOPIC)>0):?>
				<td><div class="forumtoolseparator"></div></td>
				<td><div style="width:4px;"></div></td>
				<td><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $iNEXT_TOPIC; ?>" title="Next Newest Topic" class="forumtoolbutton">Next Newest Topic</a></td>
				<td><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $iNEXT_TOPIC; ?>" title="Next Newest Topic"><img src="/bitrix/images/forum/icon_topic_next.gif" width="16" height="16" border="0" alt="Next Newest Topic" hspace="2"></a></td>
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
				<td><a href="move.php?FID=<?echo $FID;?>&TID=<?echo $TID;?>" title="Move topic" class="forumtoolbutton">Move topic</a></td>
				<td><div class="forumtoolseparator"></div></td>
				<td><div style="width:4px;"></div></td>
				<td><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $TID;?>&ACTION=<?echo (IntVal($arTopic["SORT"])!=150)?"SET_ORDINARY":"SET_TOP";?>" title="<?echo (IntVal($arTopic["SORT"])!=150)?"Unstick":"Stick";?>" class="forumtoolbutton"><?echo (IntVal($arTopic["SORT"])!=150)?"Unstick":"Stick";?></a></td>
				<td><div class="forumtoolseparator"></div></td>
				<td><div style="width:4px;"></div></td>
				<td><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $TID;?>&ACTION=<?echo ($arTopic["STATE"]!="Y")?"STATE_Y":"STATE_N";?>" title="<?echo ($arTopic["STATE"]!="Y")?"Open topic":"Close topic";?>" class="forumtoolbutton"><?echo ($arTopic["STATE"]!="Y")?"Open topic":"Close topic";?></a></td>
<?if (CForumTopic::CanUserDeleteTopic($TID, $USER->GetUserGroupArray(), $USER->GetID())):?>
				<td><div class="forumtoolseparator"></div></td>
				<td><div style="width:4px;"></div></td>
				<td><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $TID;?>&ACTION=DEL_TOPIC" title="Delete topic" class="forumtoolbutton">Delete topic</a></td>
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
