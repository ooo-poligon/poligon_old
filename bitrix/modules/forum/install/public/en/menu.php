<?
// $FID - forum code
// $TID - topic code
// $MID - message code
?>
<table width="100%" border="0" cellspacing="0" cellpadding="2" class="forumtopmenubody">
  <tr>
	<td width="100%" height="22">
		<nobr>
		&nbsp;&nbsp;<a href="index.php" title="Forums list" class="forumtopmenu"><img src="/bitrix/images/forum/icon_flist_d.gif" width="16" height="16" border="0" alt="Forums list" name="search" align="absmiddle"> Forums</a>
		<?
		if (strtoupper(basename($APPLICATION->GetCurPage(), ".php"))=="LIST"
			|| strtoupper(basename($APPLICATION->GetCurPage(), ".php"))=="READ"):
			?>
			&nbsp;&nbsp;<a href="list.php?FID=<?echo $FID; ?>" title="Topics list" class="forumtopmenu"><img src="/bitrix/images/forum/icon_tlist_d.gif" width="16" height="16" border="0" alt="Topics list" name="search" align="absmiddle"> Topics</a>
			<?
		endif;

		if (CModule::IncludeModule("search")):
			?>
			&nbsp;&nbsp;<a href="search.php" title="Search" class="forumtopmenu"><img src="/bitrix/images/forum/icon_search_d.gif" width="16" height="16" border="0" alt="Search" name="search" align="absmiddle"> Search</a>
			<?
		endif;
		?>
		&nbsp;&nbsp;<a href="help.php" title='Help' class="forumtopmenu" target="_blank"><img src="/bitrix/images/forum/icon_help_d.gif" width="16" height="16" border="0" alt="Help" name="help" align="absmiddle"> Help</a>
		<?
		if ($USER->IsAuthorized()):
			?>
			&nbsp;&nbsp;<a href="profile.php" title="Profile" class="forumtopmenu"><img src="/bitrix/images/forum/icon_profile_d.gif" width="15" height="15" border="0" alt="Profile" name="profile" align="absmiddle"> Profile</a>
			&nbsp;&nbsp;<a href="<?echo $APPLICATION->GetCurPageParam("logout=yes", array("login", "logout", "register", "forgot_password", "change_password"));?>" title="Logout" class="forumtopmenu"><img src="/bitrix/images/forum/icon_logout_d.gif" width="16" height="16" border="0" alt="Logout" name="logout" align="absmiddle"> Logout</a>
			<?
		else:
			?>
			&nbsp;&nbsp;<a href="forum_auth.php?back_url=<?echo urlencode($APPLICATION->GetCurPageParam("a", array("login", "logout", "register", "forgot_password", "change_password")));?>" title="Login" class="forumtopmenu"><img src="/bitrix/images/forum/icon_login_d.gif" width="16" height="16" border="0" alt="Login" name="login" align="absmiddle"> Login</a>
			&nbsp;&nbsp;<a href="forum_auth.php?register=yes&back_url=<?echo urlencode($APPLICATION->GetCurPageParam("a", array("login", "register", "logout", "forgot_password", "change_password")));?>" title="Register" class="forumtopmenu"><img src="/bitrix/images/forum/icon_reg_d.gif" width="16" height="16" border="0" alt="Register" name="logout" align="absmiddle"> Register</a>
			<?
		endif;

		if ($USER->IsAuthorized() 
			&&
			(strtoupper(basename($APPLICATION->GetCurPage(), ".php"))=="LIST"
			|| strtoupper(basename($APPLICATION->GetCurPage(), ".php"))=="READ")):
			?>
			&nbsp;&nbsp;<a href="<?echo $APPLICATION->GetCurPageParam("FID=".$FID."&ACTION=FORUM_SUBSCRIBE", array("FID", "ACTION", "login", "register", "logout"));?>" title="Subscribing to the forum messages" class="forumtopmenu"><img src="/bitrix/images/forum/icon_subscribe_d.gif" width="16" height="16" border="0" alt="Subscribing to the forum messages" name="profile" align="absmiddle"> Subscribe</a>&nbsp;&nbsp;
			<?
		endif;
		?>
		</nobr>
	</td>
	<td nowrap>&nbsp;</td>
  </tr>
</table>
<br>