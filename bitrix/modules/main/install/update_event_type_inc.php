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
"USER_INFO","���������� � ������������","ru",
"
#USER_ID# - ID ������������
#STATUS# - ������ ������
#MESSAGE# - ��������� ������������
#LOGIN# - �����
#CHECKWORD# - ����������� ������ ��� ����� ������
#NAME# - ���
#LAST_NAME# - �������
#EMAIL# - E-Mail ������������
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
"NEW_USER","����������������� ����� ������������","ru",
"
#USER_ID# - ID ������������
#LOGIN# - �����
#EMAIL# - EMail
#NAME# - ���
#LAST_NAME# - �������
#USER_IP# - IP ������������
#USER_HOST# - ���� ������������
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