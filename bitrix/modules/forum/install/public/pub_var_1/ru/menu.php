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
				<td><a href="index.php" title="Список форумов"><img src="/bitrix/images/forum/icon_flist_d.gif" width="16" height="16" border="0" alt="Список форумов" hspace="4"></a></td>
				<td><a href="index.php" title="Список форумов" class="forumtoolbutton">Форумы</a></td>
<?if(CModule::IncludeModule("search")):?>
				<td><div class="forumtoolseparator"></div></td>
				<td><a href="search.php<?if($FID>0) echo "?FORUM_ID=".$FID?>" title="Поиск по форумам"><img src="/bitrix/images/forum/icon_search_d.gif" width="16" height="16" border="0" alt="Поиск по форумам" hspace="4"></a></td>
				<td><a href="search.php<?if($FID>0) echo "?FORUM_ID=".$FID?>" title="Поиск по форумам" class="forumtoolbutton">Поиск</a></td>
<?endif;?>
				<td><div class="forumtoolseparator"></div></td>
				<td><a href="help.php" title="Помощь"><img src="/bitrix/images/forum/icon_help_d.gif" width="16" height="16" border="0" alt="Помощь" hspace="4"></a></td>
				<td><a href="help.php" title="Помощь" class="forumtoolbutton">Помощь</a></td>

				<td class="forumtoolbutton">&nbsp;</td>
				<td><div class="forumtoolsection"></div></td>
				<td><div class="forumtoolsection"></div></td>
<?if ($USER->IsAuthorized()):?>
				<td><a href="profile.php" title="Изменить свой профиль"><img src="/bitrix/images/forum/icon_profile_d.gif" width="15" height="15" border="0" alt="Изменить свой профиль" hspace="4"></a></td>
				<td><a href="profile.php" title="Изменить свой профиль" class="forumtoolbutton">Профиль</a></td>

				<td><div class="forumtoolseparator"></div></td>
				<td><a href="<?echo $APPLICATION->GetCurPageParam("logout=yes", array("login", "logout", "register", "forgot_password", "change_password"));?>" title="Закончить сеанс"><img src="/bitrix/images/forum/icon_logout_d.gif" width="16" height="16" border="0" alt="Закончить сеанс" hspace="4"></a></td>
				<td><a href="<?echo $APPLICATION->GetCurPageParam("logout=yes", array("login", "logout", "register", "forgot_password", "change_password"));?>" title="Закончить сеанс" class="forumtoolbutton">Выйти</a></td>
<?else:?>
				<td><a href="forum_auth.php?back_url=<?echo urlencode($APPLICATION->GetCurPageParam("", array("login", "logout", "register", "forgot_password", "change_password")));?>" title="Авторизоваться на сайте"><img src="/bitrix/images/forum/icon_login_d.gif" width="16" height="16" border="0" alt="Авторизоваться на сайте" hspace="4"></a></td>
				<td><a href="forum_auth.php?back_url=<?echo urlencode($APPLICATION->GetCurPageParam("", array("login", "logout", "register", "forgot_password", "change_password")));?>" title="Авторизоваться на сайте" class="forumtoolbutton">Войти</a></td>

				<td><div class="forumtoolseparator"></div></td>
				<td><a href="forum_auth.php?register=yes&back_url=<?echo urlencode($APPLICATION->GetCurPageParam("", array("login", "register", "logout", "forgot_password", "change_password")));?>" title="Регистрация нового пользователя"><img src="/bitrix/images/forum/icon_reg_d.gif" width="16" height="16" border="0" alt="Регистрация нового пользователя" hspace="4"></a></td>
				<td><a href="forum_auth.php?register=yes&back_url=<?echo urlencode($APPLICATION->GetCurPageParam("", array("login", "register", "logout", "forgot_password", "change_password")));?>" title="Регистрация нового пользователя" class="forumtoolbutton">Регистрация</a></td>
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
				<td><a href="index.php?ACTION=SET_BE_READ" title="Пометить все темы форумов как прочитанные"><img src="/bitrix/images/forum/icon_read.gif" width="16" height="16" border="0" alt="Пометить как прочитанные" hspace="4"></a></td>
				<td><a href="index.php?ACTION=SET_BE_READ" title="Пометить все темы форумов как прочитанные" class="forumtoolbutton">Пометить как прочитанные</a></td>
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
				<td><a href="<?echo $APPLICATION->GetCurPageParam("FID=".$FID."&ACTION=FORUM_SUBSCRIBE", array("FID", "ACTION", "login", "register", "logout"));?>" title="Подписаться на новые сообщения форума"><img src="/bitrix/images/forum/icon_subscr_forum.gif" width="16" height="16" border="0" alt="Подписаться на новые сообщения форума" hspace="4"></a></td>
				<td><a href="<?echo $APPLICATION->GetCurPageParam("FID=".$FID."&ACTION=FORUM_SUBSCRIBE", array("FID", "ACTION", "login", "register", "logout"));?>" title="Подписаться на новые сообщения форума" class="forumtoolbutton">Подписаться на форум</a></td>
<?if($sSection=="READ" && $arTopic["STATE"]=="Y"):?>
				<td><div class="forumtoolseparator"></div></td>
				<td><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $TID?>&ACTION=TOPIC_SUBSCRIBE" title="Подписаться на новые сообщения темы"><img src="/bitrix/images/forum/icon_subscr_topic.gif" width="16" height="16" border="0" alt="Подписаться на новые сообщения темы" hspace="4"></a></td>
				<td><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $TID?>&ACTION=TOPIC_SUBSCRIBE" title="Подписаться на новые сообщения темы" class="forumtoolbutton">Подписаться на тему</a></td>
<?endif;?>
				<td><div class="forumtoolseparator"></div></td>
				<td><a href="subscr_list.php" title="Изменить подписку на обновления"><img src="/bitrix/images/forum/icon_subscribe_d.gif" width="16" height="16" border="0" alt="Изменить подписку на обновления" hspace="4"></a></td>
				<td><a href="subscr_list.php" title="Изменить подписку на обновления" class="forumtoolbutton">Изменить подписку</a></td>
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
				<td><a href="new_topic.php?FID=<?echo $FID;?>" title="Добавить новую тему"><img src="/bitrix/images/forum/new_topic.gif" width="16" height="16" border="0" alt="Добавить новую тему" hspace="4"></a></td>
				<td><a href="new_topic.php?FID=<?echo $FID;?>" title="Добавить новую тему" class="forumtoolbutton"><b>Новая тема</b></a></td>
				<td class="forumtoolbutton">&nbsp;</td>
<?endif;?>
<?if($sSection=="LIST"):?>
				<td><div class="forumtoolsection"></div></td>
				<td><div class="forumtoolsection"></div></td>
				<td><a href="list.php?FID=<?echo $FID; ?>&amp;ACTION=SET_BE_READ" title="Пометить все темы этого форума как прочитанные"><img src="/bitrix/images/forum/icon_read.gif" width="16" height="16" border="0" alt="Пометить как прочитанные" hspace="4"></a></td>
				<td><a href="list.php?FID=<?echo $FID; ?>&amp;ACTION=SET_BE_READ" title="Пометить все темы этого форума как прочитанные" class="forumtoolbutton">Пометить как прочитанные</a></td>
<?endif;?>
<?if($sSection=="READ" || $sSection == "NEW_TOPIC"):?>
				<td><div class="forumtoolsection"></div></td>
				<td><div class="forumtoolsection"></div></td>
				<td><a href="list.php?FID=<?echo $FID; ?>" title="Список тем форума"><img src="/bitrix/images/forum/icon_tlist_d.gif" width="16" height="16" border="0" alt="Список тем форума" hspace="4"></a></td>
				<td><a href="list.php?FID=<?echo $FID; ?>" title="Список тем форума" class="forumtoolbutton">Список тем</a></td>
				<td class="forumtoolbutton">&nbsp;</td>
<?endif?>
<?if($sSection=="READ"):?>
<?
list($iPREV_TOPIC, $iNEXT_TOPIC) = CForumTopic::GetNeighboringTopics($TID, $USER->GetUserGroupArray());
if (IntVal($iPREV_TOPIC)>0):?>
				<td><div class="forumtoolseparator"></div></td>
				<td><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $iPREV_TOPIC ?>" title="Предыдущая тема"><img src="/bitrix/images/forum/icon_topic_prev.gif" width="16" height="16" border="0" alt="Предыдущая тема" hspace="2"></a></td>
				<td><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $iPREV_TOPIC ?>" title="Предыдущая тема" class="forumtoolbutton">Предыдущая тема</a></td>
<?endif;?>
<?if (IntVal($iNEXT_TOPIC)>0):?>
				<td><div class="forumtoolseparator"></div></td>
				<td><div style="width:4px;"></div></td>
				<td><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $iNEXT_TOPIC; ?>" title="Следующая тема" class="forumtoolbutton">Следующая тема</a></td>
				<td><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $iNEXT_TOPIC; ?>" title="Следующая тема"><img src="/bitrix/images/forum/icon_topic_next.gif" width="16" height="16" border="0" alt="Следующая тема" hspace="2"></a></td>
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
				<td><a href="move.php?FID=<?echo $FID;?>&TID=<?echo $TID;?>" title="Перенести тему" class="forumtoolbutton">Перенести тему</a></td>
				<td><div class="forumtoolseparator"></div></td>
				<td><div style="width:4px;"></div></td>
				<td><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $TID;?>&ACTION=<?echo (IntVal($arTopic["SORT"])!=150)?"SET_ORDINARY":"SET_TOP";?>" title="<?echo (IntVal($arTopic["SORT"])!=150)?"Снять прикрепление":"Прикрепить cверху";?>" class="forumtoolbutton"><?echo (IntVal($arTopic["SORT"])!=150)?"Снять прикрепление":"Прикрепить cверху";?></a></td>
				<td><div class="forumtoolseparator"></div></td>
				<td><div style="width:4px;"></div></td>
				<td><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $TID;?>&ACTION=<?echo ($arTopic["STATE"]!="Y")?"STATE_Y":"STATE_N";?>" title="<?echo ($arTopic["STATE"]!="Y")?"Открыть тему":"Закрыть тему";?>" class="forumtoolbutton"><?echo ($arTopic["STATE"]!="Y")?"Открыть тему":"Закрыть тему";?></a></td>
<?if (CForumTopic::CanUserDeleteTopic($TID, $USER->GetUserGroupArray(), $USER->GetID())):?>
				<td><div class="forumtoolseparator"></div></td>
				<td><div style="width:4px;"></div></td>
				<td><a href="read.php?FID=<?echo $FID;?>&TID=<?echo $TID;?>&ACTION=DEL_TOPIC" title="Удалить тему" class="forumtoolbutton">Удалить тему</a></td>
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
