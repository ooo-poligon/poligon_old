<?
//$strSql = "DELETE FROM b_event_type";
//$DB->Query($strSql);

$I=0;
function UET($EVENT_NAME, $NAME, $LID, $DESCRIPTION)
{
	global $DB, $I;
    $I++;
	$et = new CEventType;
	$et->Add(
			Array(
			"LID" => $LID,
			"EVENT_NAME" => $EVENT_NAME,
			"NAME" => $NAME,
			"DESCRIPTION" => $DESCRIPTION,
			"SORT" => $I
			)
		);
}
//-------------------------------------------------------------------
UET(
"USER_INFO","Информация о пользователе","ru",
"
#USER_ID# - ID пользователя
#STATUS# - Статус логина
#MESSAGE# - Сообщение пользователю
#LOGIN# - Логин
#CHECKWORD# - Контрольная строка для смены пароля
#NAME# - Имя
#LAST_NAME# - Фамилия
#EMAIL# - E-Mail пользователя
"
);
UET(
"USER_INFO","Account Information","en",
"
#USER_ID# - User ID
#STATUS# - Account status
#MESSAGE# - Message for user
#LOGIN# - Login
#CHECKWORD# - Check string for password change
#NAME# - Name
#LAST_NAME# - Last Name
#EMAIL# - User E-Mail
"
);
//-------------------------------------------------------------------
UET(
"NEW_USER","Зарегистрировался новый пользователь","ru",
"
#USER_ID# - ID пользователя
#LOGIN# - Логин
#EMAIL# - EMail
#NAME# - Имя
#LAST_NAME# - Фамилия
#USER_IP# - IP пользователя
#USER_HOST# - Хост пользователя
"
);
UET(
"NEW_USER","New user was registered","en",
"
#USER_ID# - User ID
#LOGIN# - Login
#EMAIL# - EMail
#NAME# - Name
#LAST_NAME# - Last Name
#USER_IP# - User IP
#USER_HOST# - User Host
"
);
?>