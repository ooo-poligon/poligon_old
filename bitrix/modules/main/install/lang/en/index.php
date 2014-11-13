<?php
$MESS ['INS_FORGOT_LICENSE'] = "Please provide the license key.";
$MESS ['INS_LICENSE'] = "License key:";
$MESS ['INS_TITLE1'] = "Installing \"Bitrix Site Manager";
$MESS ['INS_TITLE2'] = "\"";
$MESS ['INS_DATABASE'] = "Database:";
$MESS ['INS_DATABASE_OR'] = "Connection string:";
$MESS ['INS_DATABASE_OR_DESC'] = "This field should contain either local Oracle instance name, or name of the record to connect to in the tnsnames.ora file. An example of local Oracle instance: (DESCRIPTION = (ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 192.168.0.1)(PORT = 1521)))(CONNECT_DATA = (SERVICE_NAME = ORCL)))";
$MESS ['INS_HOST'] = "Server:";
$MESS ['INS_USER'] = "User:";
$MESS ['INS_PASSWORD'] = "Password:";
$MESS ['INS_RESET'] = "Reset";
$MESS ['INS_COULD_NOT_CONNECT'] = "Cannot connect to the database.<br>Please check the following fields: \"server\", \"Database name\", \"User\", \"Password\".";
$MESS ['COULD_NOT_CONNECT'] = "Cannot connect to the database.<br>Please check the parameters.";
$MESS ['INS_ALL_OK'] = "Installation successfully completed. To install the required modules, please click the following link:";
$MESS ['INS_NAME'] = "Name:";
$MESS ['INS_LAST_NAME'] = "Last name:";
$MESS ['INS_EMAIL'] = "E-mail:";
$MESS ['INS_LOGIN'] = "Login (min. 3 characters):";
$MESS ['INS_ADMIN_PASSWORD'] = "Password (min. 6 characters):";
$MESS ['INS_PASSWORD_CONF'] = "Confirm password:";
$MESS ['INS_ADMIN_SETTINGS'] = "Site administrator settings";
$MESS ['INS_DATABASE_SETTINGS'] = "Database settings";

$MESS ['INS_FORGOT_LOGIN'] = "Please provide site administrator login in the \"Login\" field";
$MESS ['INS_LOGIN_MIN'] = "\"Login\" must be at least 3 characters in length";
$MESS ['INS_FORGOT_PASSWORD'] = "Please provide site administrator password in the \"Password\" field";
$MESS ['INS_PASSWORD_MIN'] = "\"Admin password\" must be at least 6 characters in length";
$MESS ['INS_WRONG_CONFIRM'] = "The \"Confirm password\" field contents does not match that of the \"Password\" field.";
$MESS ['INS_FORGOT_EMAIL'] = "Please provide site administrator e-mail in the \"E-mail\" field";
$MESS ['INS_WRONG_EMAIL'] = "Incorrect \"E-mail\"";
$MESS ['INS_FORGOT_NAME'] = "Please provide the \"Name\" field.";
$MESS ['INS_FORGOT_LASTNAME'] = "Please provide the \"Last name\" field.";

$MESS ['INS_SAFE_MODE'] = "PHP is now functioning in Safe Mode. Please switch to normal mode or install the \"Bitrix Site Manager\" system on another workstation.";
$MESS ['INS_ROOT_ACCESS'] = "Not enough rights to write to the site root folder. The system cannot be installed.";
$MESS ['INS_BITRIX_ACCESS'] = "Not enough rights to write to the /bitrix folder. The system cannot be installed.";

$MESS ['DATABASE_ALREADY_EXISTS'] = "The database of the system \"Bitrix Site Manager\" already exists. ";

$MESS ['ERR_MIN_VERSION1'] = "The \"Bitrix Site Manager\" system requires PHP version at least 4.1.0 to install. You have PHP version ";
$MESS ['ERR_MIN_VERSION2'] = ". Please upgrade your PHP to a newer version and run istallation again. ";
$MESS ['ERR_REGISTER_GLOB'] = "To function correctly, the system requires the variable \"register_globals\" to be set to \"on\" (register_globals = on) in the PHP configuration file (php.ini).";
$MESS ['ERR_CONNECT2MYSQL'] = "Error connecting to the MySql server. Please check parameters. Please check if the MySql server is started.";
$MESS ['ERR_CREATE_DB1'] = "Error creating database ";
$MESS ['ERR_CREATE_DB2'] = ".";
$MESS ['ERR_CONNECT_DB1'] = "Error connecting to the database ";
$MESS ['ERR_CONNECT_DB2'] = ". Please check parameters.";
$MESS ['ERR_EXISTS_DB1'] = "Database ";
$MESS ['ERR_EXISTS_DB2'] = "already exists. Please clear the checkbox \"Create database\" or provide another value in the \"Database\" field.";
$MESS ['ERR_INTERNAL_NODEF'] = "Internal error: database not specified.";
$MESS ['ERR_ALREADY_INST1'] = "The database ";
$MESS ['ERR_ALREADY_INST2'] = "already contains \"Bitrix Site Manager\" installation. To create a new copy, please enter another value in the \"Database\" field.";
$MESS ['ERR_ADMIN_CREATE'] = "Administrator not created: ";
$MESS ['INTERF_LANG'] = "Interface language";
$MESS ['STEP_1'] = "Step 1 (Database installation):";
$MESS ['INS_LICENSE_HEAD'] = "License Key";
$MESS ['INS_LICENSE_NOTE'] = "If you have purchased the system, please enter the license key that you have received by e-mail. If you plan to install the product for evaluation purposes, please leave the value DEMO.";
$MESS ['INS_CREATE_DB'] = "Create database:";
$MESS ['INS_INSTALL'] = "Next &gt;&gt;";
$MESS ['LOG_DB_CREATED1'] = "Database created";
$MESS ['LOG_DB_CREATED2'] = "...";
$MESS ['LOG_LICENSE_CREATED'] = "License file created...";
$MESS ['LOG_DB_CONFIG_CREATED'] = "Database connection configuration file created...";
$MESS ['LOG_DATA_LOADED'] = "Data loaded in the database...";
$MESS ['STEP_2'] = "Step 2 (Site Setup):";
$MESS ['NOTE_NOT_REFRESH'] = "Do not refresh this page or use the \"Back\" button on your browser";
$MESS ['LOG_ADMIN_CREATED'] = "Site administrator account created...";
$MESS ['LOG_MAIL_LOADED'] = "E-mail message templates loaded...";
$MESS ['LOG_FILES_COPIED'] = "Created administrative and public sections of the site...";
$MESS ['LOG_SUCCESS'] = "Installation successfully completed...";
$MESS ['LOG_LOGGED_IN'] = "You have been authorized as the site administrator...";
$MESS ['STEP_3'] = "Step 3 (Completing the Installation):";
$MESS ['GOTO_ADMIN'] = "Go to administrative section";
$MESS ['GOTO_ADMIN_NOTES'] = "Go to admitistrative section of the site to make additional setup, manage site, add news, etc. You can access the administrative section later by navigating the link in the top control panel of the public site section (the control panel is displayed upon authorization) or by directly navigating to <nobr>http://&lt;your-web-site&gt;/bitrix/</nobr>.";
$MESS ['GOTO_PUBLIC'] = "Go to public section";
$MESS ['GOTO_PUBLIC_NOTES'] = "Go to public section of the site viewed by all site visitors.";
$MESS ['GOTO_MODULES'] = "Go to module management";
$MESS ['GOTO_MODULES_NOTES'] = "Go to module management page of the administrative site section. Using this page functionality, you can install new modules or remove the existing ones.";
$MESS ['GOTO_BITRIX'] = "Go to Bitrix company site";
$MESS ['GOTO_BITRIX_NOTES'] = "Go to <a href=\"http://www.bitrixsoft.com/?r1=bsm3trial&r2=install\">Bitrix</a> company site (<a href=\"http://www.bitrixsoft.com/?r1=bsm3trial&r2=install\">http://www.bitrixsoft.com</a>). This resource provides both a <a href=\"http://www.bitrixsoft.com/support/forum/index.php?r1=bsm3trial&r2=install\">forum</a> and an automated <a href=\"http://www.bitrixsoft.com/support/?r1=bsm3trial&r2=install\">customer support service</a> so that you can ask any question on the \"Bitrix Site Manager\" software and keep yourself up-to-date with the latest news.";

$MESS ['GOTO_IF_ERROR'] = "Should you have any problems installing \"Bitrix Site Manager\", please do not hesitate to contact the <a href=\"http://www.bitrixsoft.com/support/?r1=bsm3trial&r2=install\">customer support service</a>. Highly qualified specialists of the <a href=\"http://www.bitrixsoft.com/?r1=bsm3trial&r2=install\">\"Bitrix\"</a> company are always at your service.";

$MESS ['README_LINK'] = "Installation instructions";

$MESS ['INSTALL_SUCCESS'] = "Installation successfully completed.<br>Thank you for choosing \"Bitrix Site Manager\"!";

$MESS ['GOTO_UPDATES'] = "Go to System Update";
$MESS ['GOTO_UPDATES_NOTES'] = "Go to System Update page of the administrative site section. This page allows you to register your copy of the \"Bitrix Site Manager\" and update the software modules.";

$MESS ["NO_MYSQL_SUPPORT"] = "PHP installed without MySql support.";
$MESS ["NO_ORACLE_SUPPORT"] = "PHP installed without Oracle support. Please install Oracle8 Call-Interface (OCI8). ";
$MESS ["NO_PREG_SUPPORT"] = "PHP installed without regular expression support (Regular Expression Functions (Perl-Compatible)).";
$MESS ["NO_GD_SUPPORT"] = "To utilize all of this software features, you should install the a href=\"http://www.boutell.com/gd/\">GD library</a> image library (Image functions). Otherwise, some of the software functionality will be unavailable.";
$MESS ["NO_GZ_SUPPORT"] = "Zlib archive management engine (Zlib Compression Functions) installation is recommended. Otherwise, some of the software functionality will be unavailable.";
$MESS ["NO_XML_SUPPORT"] = "XML engine (XML parser functions) installation is recommended. Otherwise, some of the software functionality will be unavailable.";
$MESS ["ERR_PERMS_ALGO"] = "Error assigning file access permissions. Please set full access rights to all files and folders of the site recursively, starting from the root ".$_SERVER["DOCUMENT_ROOT"]." or alter permisstions to execute chmod.";

$MESS ["INS_CREATE_DB_TYPE"] = "Database tables type:";
$MESS ["INS_C_DB_TYPE_STAND"] = "standard";

$MESS ["ERR_AGREE_LICENSE"] = "To continue the program installation please read the license agreement carefully. ";
$MESS ["LICENSE_SUBTITLE"] = "License agreement";
$MESS ["LICENSE_AGREE_PROMT"] = "I accept the terms in the license agreement";
?>
