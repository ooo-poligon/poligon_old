<?
$arr["EVENT_NAME"] = "NEW_USER";
$arr["LID"] = "ru";
$arr["EMAIL_FROM"] = "#DEFAULT_EMAIL_FROM#";
$arr["EMAIL_TO"] = "#DEFAULT_EMAIL_FROM#";
$arr["SUBJECT"] = "#SITE_NAME#: Зарегистрировался новый пользователь";
$arr["MESSAGE"] = "
Информационное сообщение сайта #SITE_NAME#
------------------------------------------

На сайте #SERVER_NAME# успешно зарегистрирован новый пользователь.

Данные пользователя:
ID пользователя: #USER_ID#

Имя: #NAME# 
Фамилия: #LAST_NAME#
E-Mail: #EMAIL# 

Login: #LOGIN#

Письмо сгенерировано автоматически. 
";
$arTemplates[] = $arr;

$arr["EVENT_NAME"] = "NEW_USER";
$arr["LID"] = "en";
$arr["EMAIL_FROM"] = "#DEFAULT_EMAIL_FROM#";
$arr["EMAIL_TO"] = "#DEFAULT_EMAIL_FROM#";
$arr["SUBJECT"] = "#SITE_NAME#: New user has been registered on the site";
$arr["MESSAGE"] = "
Informational message from #SITE_NAME#
---------------------------------------

New user has been successfully registered on the site #SERVER_NAME#.

User details:
User ID: #USER_ID#

Name: #NAME# 
Last Name: #LAST_NAME#
User's E-Mail: #EMAIL# 

Login: #LOGIN#

Automatically generated message.
";
$arTemplates[] = $arr;

$arr["EVENT_NAME"] = "USER_INFO";
$arr["LID"] = "ru";
$arr["EMAIL_FROM"] = "#DEFAULT_EMAIL_FROM#";
$arr["EMAIL_TO"] = "#EMAIL#";
$arr["SUBJECT"] = "#SITE_NAME#: Регистрационная информация";
$arr["MESSAGE"] = "
Информационное сообщение сайта #SITE_NAME#
------------------------------------------
#NAME# #LAST_NAME#,

#MESSAGE#

Ваша регистрационная информация:

ID пользователя: #USER_ID#
Статус бюджета: #STATUS#
Login: #LOGIN#

Для смены пароля перейдите по следующей ссылке:
http://#SERVER_NAME#/bitrix/admin/index.php?change_password=yes&lang=ru&USER_CHECKWORD=#CHECKWORD#

Сообщение сгенерировано автоматически.
";
$arTemplates[] = $arr;

$arr["EVENT_NAME"] = "USER_INFO";
$arr["LID"] = "en";
$arr["EMAIL_FROM"] = "#DEFAULT_EMAIL_FROM#";
$arr["EMAIL_TO"] = "#EMAIL#";
$arr["SUBJECT"] = "#SITE_NAME#: Registration info";
$arr["MESSAGE"] = "
Informational message from #SITE_NAME#
---------------------------------------

#NAME# #LAST_NAME#,

#MESSAGE#

Your registration info:

User ID: #USER_ID#
Account status: #STATUS#
Login: #LOGIN#

To change your password please visit the link below:
http://#SERVER_NAME#/bitrix/admin/index.php?change_password=yes&lang=en&USER_CHECKWORD=#CHECKWORD#

Automatically generated message.
";
$arTemplates[] = $arr;
?>