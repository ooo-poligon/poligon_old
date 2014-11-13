<?php 
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix             #
# http://www.bitrix.com                      #
# mailto:admin@bitrix.com                    #
##############################################
*/
@set_time_limit(3600);
@ignore_user_abort(true);
session_start();
define("DEMO_DATA", "Y");
if (!defined("MAIN_MOVER")) define("MAIN_MOVER", "5.1.2");
define("BX_ROOT", "/bitrix");
$def_lang="ru";

error_reporting(E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR|E_PARSE);
#error_reporting(E_ALL);

$arLang = array(
		"ru" => array("LANG"=>"Russian", "CHARSET"=>"Windows-1251", "SITE"=>"http://www.bitrixsoft.ru"),
		"en" => array("LANG"=>"English", "CHARSET"=>"Windows-1251", "SITE"=>"http://www.bitrixsoft.com")
	);

// To remove notices
$arVars = array("lang", "Step", "license", "dbtype", "create_database", "create_user", "user", "password", "host", "database","root_user","root_password", "file_access_perms", "folder_access_perms", "memory_limit");

foreach($arVars as $var)
	if (!isset($_REQUEST[$var]))
		$_REQUEST[$var] = "";
// We can continue

$lang = (in_array($_REQUEST["lang"], array_keys($arLang))) ? $_REQUEST["lang"] : $def_lang;
define("LANGUAGE_ID",$lang);

$Step = IntVal($_REQUEST["Step"]);
if ($Step <= 0 || $Step > 7)
	$Step = 1;

if ($Step >= 4)
{
	$dbType = Trim($_REQUEST["dbtype"]);
	if ($dbType != "mysql" && $dbType != "oracle" && $dbType != "mssql")
		$dbType = "";

	if (strlen($dbType) <= 0)
		$Step = 4;
}

$strErrorMessage = "";
$strWarningMessage = "";
$strOKMessage = "";

// First compatibility check
if (!isset($_SERVER["DOCUMENT_ROOT"])
	|| strlen($_SERVER["DOCUMENT_ROOT"])<=0
	|| !file_exists($_SERVER["DOCUMENT_ROOT"])
	|| !is_dir($_SERVER["DOCUMENT_ROOT"]))
{
	
	$strErrorMessage .= '
	<p><center><font color="#FF0000">
		<b>$_SERVER["DOCUMENT_ROOT"]</b> variable must be set to 
		the document root directory under which the current script is executing.';
}

if ($_SERVER['PHP_SELF']!="/index.php")
{
	$strErrorMessage .= '
	<p><center><font color="#FF0000">
		Bitrix site manager must be installed in web server root directory.';
}

if (!ini_get("short_open_tag"))
{
	$strErrorMessage .= '
	<p><center><font color="#FF0000">
		<b>short_open_tag</b> value must be turned on in you <b>php.ini</b> or <b>.htaccess</b> file.';
}

if ($strErrorMessage)
	die($strErrorMessage.'<br>Please modify the server\'s configuration or contact administrator of your hosting.</font></center></p>');
// End first compatibility check

include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/tools.php");
UnQuoteAll();

if ($Step > 4)
{
	if(!defined("CACHED_b_lang"))           define("CACHED_b_lang", 3600);
	if(!defined("CACHED_b_option"))         define("CACHED_b_option", 3600);
	if(!defined("CACHED_b_lang_domain"))    define("CACHED_b_lang_domain", 3600);
	if(!defined("CACHED_b_site_template"))  define("CACHED_b_site_template", 3600);
	if(!defined("CACHED_b_event"))          define("CACHED_b_event", 3600);
	if(!defined("CACHED_b_agent"))          define("CACHED_b_agent", 3660);
	if(!defined("CACHED_menu"))             define("CACHED_menu", 3600);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/module.php");
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/".$dbType."/database.php");
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/".$dbType."/main.php");
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/".$dbType."/user.php");
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/".$dbType."/option.php");
	require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/cache.php");
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/filter_tools.php");
}

include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/lang/en/install.php");
if ($lang!="en" && file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/lang/".$lang."/install.php"))
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/lang/".$lang."/install.php");


function InstallGetMessage($name)
{
	global $MESS;
	return $MESS[$name];
}

if (!function_exists("ob_get_clean"))
{
	function ob_get_clean()
	{
		$ob_contents = ob_get_contents();
		ob_end_clean();
		return $ob_contents;
	}
}

/********************************************************/
/**********   CHECKS AND ACTIONS   **********************/
/********************************************************/

//*******************************************************/
//*******************************************************/
if ($Step==3)
{
//*******************************************************/
//*******************************************************/

if ($_REQUEST["agree_license"] != "Y")
	$strErrorMessage .= InstallGetMessage("ERR_AGREE_LICENSE");

if (strlen($strErrorMessage)>0)
	$Step = 2;

//*******************************************************/
//*******************************************************/
}
elseif ($Step==4)
{
//*******************************************************/
//*******************************************************/

if (strlen($_REQUEST["license"]) <= 0)
	$_REQUEST["license"] = "demo";

if ($_REQUEST["dbtype"] != "mysql" && $_REQUEST["dbtype"] != "oracle" && $_REQUEST["dbtype"] != "mssql")
	$strErrorMessage .= InstallGetMessage("ERR_NO_DATABSEL");

if (strlen($strErrorMessage) <= 0)
{
	$license_file = $_SERVER["DOCUMENT_ROOT"]."/bitrix/license_key.php";

	// Write down license key
	$lic = "<"."? \$"."LICENSE_KEY = \"".addslashes($_REQUEST["license"])."\"; ?".">";
	if ($fp = @fopen($license_file, "wb"))
	{
		if (!fwrite($fp, $lic))
			$strErrorMessage .= str_replace("#ROOT#", $_SERVER["DOCUMENT_ROOT"], InstallGetMessage("ERR_SAVE_LICENSE"));
		@fclose($fp);
	}
	else
		$strErrorMessage .= str_replace("#ROOT#", $_SERVER["DOCUMENT_ROOT"], InstallGetMessage("ERR_SAVE_LICENSE"));
}

if (strlen($strErrorMessage) > 0)
	$Step = 3;

//*******************************************************/
//*******************************************************/
}
elseif ($Step==6 && (strlen($_REQUEST["UNIID"])<=0 || !($_SESSION[$_REQUEST["UNIID"]]=="Y")))
{
//*******************************************************/
//*******************************************************/

if (strlen($_REQUEST["user"])==0)
	$strErrorMessage .= InstallGetMessage("ERR_NO_USER");
		
if ($dbType == "mysql")
{
	if ($_REQUEST["create_database"] || $_REQUEST["create_user"])
		$db_Conn = @mysql_connect($_REQUEST["host"], $_REQUEST["root_user"], $_REQUEST["root_password"]);
	else
		$db_Conn = @mysql_connect($_REQUEST["host"], $_REQUEST["user"], $_REQUEST["password"]);

	if (!$db_Conn)
	{
		$strErrorMessage .= InstallGetMessage("ERR_CONNECT2MYSQL");
	}

	if (strlen($strErrorMessage)<=0)
	{
		if (!mysql_select_db($_REQUEST["database"], $db_Conn))
		{
			if ($_REQUEST["create_database"]=="Y")
			{
				@mysql_query("CREATE DATABASE ".$_REQUEST["database"], $db_Conn);
				if (!mysql_select_db($_REQUEST["database"], $db_Conn))
				{
					$strErrorMessage .= str_replace("#DB#", $_REQUEST["database"], InstallGetMessage("ERR_CREATE_DB1"));
				}
			}
			else
				$strErrorMessage .= str_replace("#DB#", $_REQUEST["database"], InstallGetMessage("ERR_CONNECT_DB1"));
		} else {
			if ($_REQUEST["create_database"]=="Y")
				$strErrorMessage .= str_replace("#DB#", $_REQUEST["database"], InstallGetMessage("ERR_EXISTS_DB1"));
		}
	}

	if (!$strErrorMessage && $_REQUEST["user"] != $_REQUEST["root_user"])
	{
		$hst = $_REQUEST['host'];
		if($r=strpos($hst, ":")) 
			$hst = substr($hst, 0, $r);

		if ($_REQUEST["create_user"]) {
			$Query = "GRANT ALL ON `".addslashes($_REQUEST['database'])."`.* TO '".addslashes($_REQUEST["user"])."'@'".$hst."' IDENTIFIED BY '".addslashes($_REQUEST["password"])."'";
			@mysql_query($Query, $db_Conn);

			if (mysql_error()) $strErrorMessage .= InstallGetMessage("ERR_CREATE_USER");
		} elseif ($_REQUEST["create_database"]) {
			$Query = "GRANT ALL ON `".addslashes($_REQUEST['database'])."`.* TO '".addslashes($_REQUEST["user"])."'@'".$hst."' ";
			@mysql_query($Query, $db_Conn);

			if (mysql_error()) $strErrorMessage .= InstallGetMessage("ERR_GRANT_USER");
		}
	}

	if (!$strErrorMessage && $_REQUEST['create_database_charset'])
	{
		@mysql_query("ALTER DATABASE `".$_REQUEST['database']."` CHARACTER SET ".$_REQUEST['create_database_charset']);
		if (mysql_error()) $strErrorMessage .= InstallGetMessage("ERR_ALTER_DB");
	}
}
elseif ($dbType == "mssql")
{
	if ($_REQUEST["create_database"] || $_REQUEST["create_user"])
		$db_Conn = @odbc_connect($_REQUEST["host"], $_REQUEST["root_user"], $_REQUEST["root_password"]);
	else
		$db_Conn = @odbc_connect($_REQUEST["host"], $_REQUEST["user"], $_REQUEST["password"]);

	if (!$db_Conn)
	{
		$strErrorMessage .= InstallGetMessage("ERR_CONNECT2MYSQL");
	} 
	 

	if (!$strErrorMessage && $_REQUEST["create_database"]=="Y")
	{
		$Query = 'CREATE DATABASE "'.$_REQUEST["database"].'"';
		if (!@odbc_exec($db_Conn, $Query))
		{
			$strErrorMessage .= str_replace("#DB#", $_REQUEST["database"], InstallGetMessage("ERR_CREATE_DB1"))." ".odbc_errormsg($db_Conn);

		}
	}

	if (!$strErrorMessage)
	{
		$Query = 'USE "'.$_REQUEST['database'].'"';
		if (!@odbc_exec($db_Conn, $Query))
		{
			$strErrorMessage .= str_replace("#DB#", $_REQUEST["database"], InstallGetMessage("ERR_CONNECT_DB1"))." ".odbc_errormsg($db_Conn);
		}
	}
	
	if (!$strErrorMessage && $_REQUEST["create_user"])
	{
			$Query = 'sp_addlogin "'.$_REQUEST['user'].'", "'.addslashes($_REQUEST['password']).'", "'.$_REQUEST['database'].'"';
			if (!@odbc_exec($db_Conn, $Query))
				$strErrorMessage .= InstallGetMessage("ERR_CREATE_USER")." ".odbc_errormsg($db_Conn);
	}

	if (!$strErrorMessage && ($_REQUEST['create_user'] || $_REQUEST['create_database']))
	{
		if ($_REQUEST["user"] != $_REQUEST["root_user"]) {
			$Query = 
				'sp_grantdbaccess "'.$_REQUEST['user'].'";
				EXEC sp_addrolemember  "db_owner","'.$_REQUEST['user'].'";';
			if (!@odbc_exec($db_Conn, $Query))
				$strErrorMessage .= InstallGetMessage("ERR_GRANT_USER")." ".odbc_errormsg($db_Conn);
		}
	}
}
elseif ($dbType == "oracle")
{
	if ($_REQUEST["create_user"])
		$db_Conn = @OCILogon($_REQUEST["root_user"], $_REQUEST["root_password"], $_REQUEST['database']);
	else
		$db_Conn = @OCILogon($_REQUEST["user"], $_REQUEST["password"], $_REQUEST['database']);

	if (!$db_Conn)
	{
		$strErrorMessage .= InstallGetMessage("ERR_CONNECT2MYSQL");
	}

	if (!$strErrorMessage && $_REQUEST["create_user"])
	{
//		OCIExecute(OCIParse($db_Conn, "DROP USER ".$_REQUEST['user']));
		
		$Query = "CREATE USER ".$_REQUEST['user']." IDENTIFIED BY \"".$_REQUEST['password'].'"';
		$result = @OCIParse($db_Conn, $Query);
		if ($result && @OCIExecute($result))
		{
			$Query = "GRANT connect,resource,QUERY REWRITE TO ".$_REQUEST["user"];
			$result = @OCIParse($db_Conn, $Query);
			if (!($result && @OCIExecute($result)))
			{
				$error = OCIError($result);
				$strErrorMessage .= InstallGetMessage("ERR_GRANT_USER").($error['message'] ? ": ".$error['message']." ":" ");
			}
		} else {
			$error = OCIError($result);
			$strErrorMessage .= InstallGetMessage("ERR_CREATE_USER").($error['message'] ? ": ".$error['message']." ":" ");
		}
	}
//		die($strErrorMessage);
}
else
{
	$strErrorMessage .= InstallGetMessage("ERR_INTERNAL_NODEF");
}

if (strlen($strErrorMessage) <= 0)
{
	$DB = new CDatabase;
	$DB->DebugToFile = false;
	define("DBPersistent", false);

	if (!$DB->Connect($_REQUEST["host"], $_REQUEST["database"], $_REQUEST["user"], $_REQUEST["password"]))
		$strErrorMessage .= InstallGetMessage("COULD_NOT_CONNECT");
	$DB->debug = true;
}

if (strlen($strErrorMessage) <= 0)
{
	if ($dbType == "mysql")
	{
		$bNeedCP = False;

		$dbQueryRes = $DB->Query("select VERSION() as ver", True);
		if ($arQueryRes = $dbQueryRes->Fetch())
		{
			$curMySqlVer = trim($arQueryRes["ver"]);
			$arCurMySqlVer = explode(".", $curMySqlVer);
			if (IntVal($arCurMySqlVer[0]) > 4
				|| IntVal($arCurMySqlVer[0]) == 4 && IntVal($arCurMySqlVer[1]) >= 1)
			{
				$bNeedCP = True;
			}
			elseif (IntVal($arCurMySqlVer[0]) < 4
				|| IntVal($arCurMySqlVer[0]) == 4 && IntVal($arCurMySqlVer[1]) < 0
				|| IntVal($arCurMySqlVer[0]) == 4 && IntVal($arCurMySqlVer[1]) == 0 && IntVal($arCurMySqlVer[2]) < 14)
			{
				$strErrorMessage .= InstallGetMessage("SC_DB_VERS_MYSQL_ER");
			}
		}

		if ($bNeedCP)
		{
			$mysql_code_page = "";
			$dbQueryRes = $DB->Query("SHOW VARIABLES LIKE 'character_set_server'", True);
			if ($arQueryRes = $dbQueryRes->Fetch())
			{
				$mysql_code_page = trim($arQueryRes["Value"]);
			}

			if (strlen($mysql_code_page) > 0)
			{
				$DB->Query("SET NAMES '".$DB->ForSql($mysql_code_page)."'");
#				$DB->Query("SET CHARACTER SET '".$DB->ForSql($mysql_code_page)."'");

				$after_conn_file = $_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/after_connect.php";

				$after_conn = "<"."?\n".
					"$"."DB->Query(\"SET NAMES '".$DB->ForSql($mysql_code_page)."'\");\n".
					"$"."DB->Query(\"SET CHARACTER SET '".$DB->ForSql($mysql_code_page)."'\");\n".
					"?".">";

				if ($fp = @fopen($after_conn_file, "wb"))
				{
					if (!fwrite($fp, $after_conn))
						$strErrorMessage .= str_replace("#ROOT#", $_SERVER["DOCUMENT_ROOT"], InstallGetMessage("ERR_C_SAVE_DBCONN"));

					@fclose($fp);
					if ($file_access_perms>0)
						@chmod($after_conn_file, octdec($file_access_perms));
				}
				else
					$strErrorMessage .= str_replace("#ROOT#", $_SERVER["DOCUMENT_ROOT"], InstallGetMessage("ERR_C_SAVE_DBCONN"));
			}
		}
	}
	elseif ($dbType == "oracle")
	{
		$DB->Query("alter session set NLS_LENGTH_SEMANTICS = 'CHAR'");
		$DB->Query("alter session set NLS_NUMERIC_CHARACTERS = '. '");

		$after_conn_file = $_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/after_connect.php";

		$after_conn = "<"."?\n".
			"$"."DB->Query(\"alter session set NLS_LENGTH_SEMANTICS = 'CHAR'\");\n".
			"$"."DB->Query(\"alter session set NLS_NUMERIC_CHARACTERS = '. '\");\n".
			"?".">";

		if ($fp = @fopen($after_conn_file, "wb"))
		{
			if (!fwrite($fp, $after_conn))
				$strErrorMessage .= str_replace("#ROOT#", $_SERVER["DOCUMENT_ROOT"], InstallGetMessage("ERR_C_SAVE_DBCONN"));

			@fclose($fp);
			if ($file_access_perms>0)
				@chmod($after_conn_file, octdec($file_access_perms)); 
		}
		else
			$strErrorMessage .= str_replace("#ROOT#", $_SERVER["DOCUMENT_ROOT"], InstallGetMessage("ERR_C_SAVE_DBCONN"));
	}

	if ($dbType == "mysql" && strlen($_REQUEST["create_database_type"]) > 0)
	{
		$DB->Query("SET table_type = '".$_REQUEST["create_database_type"]."'", True);
	}

	$res = $DB->Query("SELECT COUNT(ID) FROM b_user", true);
	if ($res && $res->Fetch()) $strErrorMessage .= str_replace("#DB#", $database, InstallGetMessage("ERR_ALREADY_INST1"));
}

if (strlen($strErrorMessage) <= 0)
{
	$done = false;
	$strTable_name = "b_tmp";
	while ($done == false)
	{
		$strTable_name .= "_tmp";
		if ($dbType == "mysql")
			$strSql = "SHOW TABLES LIKE '$strTable_name'";
		elseif ($dbType == "oracle")
			$strSql = "SELECT table_name FROM user_tables WHERE table_name LIKE '$strTable_name'";
		else
			$strSql = "SELECT * FROM ".$_REQUEST['database'].".INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME='$strTable_name'";

		$res = $DB->Query($strSql, true);
		if ($DB->db_Error)
		{
			$done = true;
			$strErrorMessage .= "Error execute query";
		}
		elseif (!($res->Fetch()))
			$done = true;
	}

	if ($dbType == "mysql")
		$strSql = "CREATE TABLE $strTable_name(ID INT)";
	elseif ($dbType == "oracle")
		$strSql = "CREATE TABLE $strTable_name(ID NUMBER(18))";
	else
		$strSql = "CREATE TABLE $strTable_name(ID INT)";

	$DB->Query($strSql, true);

	if (strlen($DB->db_Error) > 0)
	{
		$strErrorMessage .= InstallGetMessage("ERR_C_CREATE_TBL")." ".odbc_errormsg($DB->db_Conn);
	}
	else
	{
		if ($dbType == "mysql")
			$strSql = "ALTER TABLE $strTable_name ADD COLUMN CLMN VARCHAR(100)";
		elseif ($dbType == "oracle")
			$strSql = "ALTER TABLE $strTable_name ADD CLMN VARCHAR2(100)";
		else
			$strSql = "ALTER TABLE $strTable_name ADD CLMN VARCHAR(100)";

		$DB->Query($strSql, true);

		if (strlen($DB->db_Error) > 0)
		{
			$strErrorMessage .= InstallGetMessage("ERR_C_ALTER_TBL");
		}
		else
		{
			if ($dbType == "mysql")
				$strSql = "DROP TABLE IF EXISTS $strTable_name";
			elseif ($dbType == "oracle")
				$strSql = "DROP TABLE $strTable_name CASCADE CONSTRAINTS";
			else
				$strSql = "DROP TABLE $strTable_name";

			$DB->Query($strSql, true);

			if (strlen($DB->db_Error) > 0)
			{
				$strErrorMessage .= InstallGetMessage("ERR_C_DROP_TBL");
			}
		}
	}
}

if (strlen($strErrorMessage) <= 0)
{
	if (strlen($_REQUEST["UNIID"]) > 0)
		$_SESSION[$_REQUEST["UNIID"]] = "Y";

	$conn_file = $_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/dbconn.php";

	/*DO_NOT_REMOVE_THIS__GTRJUH*/

	// Connection params
	$conn = "<"."?\n".
		"define(\"DBPersistent\", true);\n".
		"$"."DBType = \"".$dbType."\";\n".
		"$"."DBHost = \"".$_REQUEST["host"]."\";\n".
		"$"."DBLogin = \"".$_REQUEST["user"]."\";\n".
		"$"."DBPassword = \"".str_replace('$','\$',$_REQUEST["password"])."\";\n".
		"$"."DBName = \"".$_REQUEST["database"]."\";\n".
		"$"."DBDebug = false;\n".
		"$"."DBDebugToFile = false;\n".
		"\n".
		"set_time_limit(60);\n".
		"\n";

	$file_access_perms = IntVal($_REQUEST["file_access_perms"]);
	if ($file_access_perms>0)
		$conn .= "define(\"BX_FILE_PERMISSIONS\", 0".$file_access_perms.");\n";

	$folder_access_perms = IntVal($_REQUEST["folder_access_perms"]);
	if ($folder_access_perms>0)
		$conn .= "define(\"BX_DIR_PERMISSIONS\", 0".$folder_access_perms.");\n";

	$memory_limit = IntVal($_REQUEST["memory_limit"]);
	if ($memory_limit>0)
		$conn .= "@ini_set(\"memory_limit\", \"".$memory_limit."M\");\n";

	$conn .= "?".">";

	if ($fp = @fopen($conn_file, "wb"))
	{
		if (!fwrite($fp, $conn))
			$strErrorMessage .= str_replace("#ROOT#", $_SERVER["DOCUMENT_ROOT"], InstallGetMessage("ERR_C_SAVE_DBCONN"));

		@fclose($fp);
		if ($file_access_perms>0)
			@chmod($conn_file, octdec($file_access_perms));
	}
	else
		$strErrorMessage .= str_replace("#ROOT#", $_SERVER["DOCUMENT_ROOT"], InstallGetMessage("ERR_C_SAVE_DBCONN"));
}

if (strlen($strErrorMessage) <= 0)
{
	// Loading database dump
	$file = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/".$dbType."/install_data.php";
	include($file);

	if ($dbType=="oracle")
	{
		$Query = "begin ".
			"for are in (select table_name,constraint_name from user_constraints where constraint_type='R') ".
			"loop ".
			"execute immediate 'alter table '||are.table_name||' disable constraint '||are.constraint_name; ".
			"end loop; ".
			 "end;";
		$DB->Query($Query);
	}

	$iii = 1;
	while (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/".$dbType."/install_data_".$iii.".php"))
	{
		$file = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/".$dbType."/install_data_".$iii.".php";
		include($file);
		$iii++;
	}
	
	if ($dbType=="oracle")
	{
		$Query = "begin ".   
			"for are in (select table_name,constraint_name from user_constraints where constraint_type='R') ".
			"loop ".
			"execute immediate 'alter table '||are.table_name||' enable constraint '||are.constraint_name; ".
			"end loop; ".
			"end;";
		$DB->Query($Query);
	}

	/*DO_NOT_REMOVE_THIS__DTUJHJE*/
}

if (strlen($strErrorMessage) > 0)
	$Step = 5;

//*******************************************************/
//*******************************************************/
}
elseif ($Step==7 && (strlen($_REQUEST["UNIID"])<=0 || !($_SESSION[$_REQUEST["UNIID"]]=="Y")))
{
//*******************************************************/
//*******************************************************/

$email = trim($_REQUEST["email"]);
$login = trim($_REQUEST["login"]);

if (strlen($email)<=0)
	$strErrorMessage .= InstallGetMessage("INS_FORGOT_EMAIL");
elseif (!check_email($email))
	$strErrorMessage .= InstallGetMessage("INS_WRONG_EMAIL");

if (strlen($login)<=0)
	$strErrorMessage .= InstallGetMessage("INS_FORGOT_LOGIN"); 
elseif (strlen($login)<3)
	$strErrorMessage .= InstallGetMessage("INS_LOGIN_MIN");

if (strlen($_REQUEST["admin_password"])<=0)
	$strErrorMessage .= InstallGetMessage("INS_FORGOT_PASSWORD");
else
{
	if (strlen($_REQUEST["admin_password"])<6)
		$strErrorMessage .= InstallGetMessage("INS_PASSWORD_MIN");
	elseif ($_REQUEST["admin_password"]!=$_REQUEST["admin_password_confirm"])
		$strErrorMessage .= InstallGetMessage("INS_WRONG_CONFIRM");
}

if (strlen($_REQUEST["name"])<=0)
	$strErrorMessage .= InstallGetMessage("INS_FORGOT_NAME");

if (strlen($_REQUEST["last_name"])<=0)
	$strErrorMessage .= InstallGetMessage("INS_FORGOT_LASTNAME");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/dbconn.php");

$DB = new CDatabase;
$DB->debug = $DBDebug;
$DB->DebugToFile = $DBDebugToFile;
if (!($DB->Connect($DBHost, $DBName, $DBLogin, $DBPassword)))
	$strErrorMessage .= InstallGetMessage("COULD_NOT_CONNECT");

if (strlen($strErrorMessage) <= 0)
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/after_connect.php");

	if (strlen($_REQUEST["UNIID"]) > 0)
		$_SESSION[$_REQUEST["UNIID"]] = "Y";

	$user = new CUser;
	$arFields = Array(
		"NAME"				=> $_REQUEST["name"],
		"LAST_NAME"			=> $_REQUEST["last_name"],
		"EMAIL"				=> $email,
		"LOGIN"				=> $login,
		"PASSWORD"			=> $_REQUEST["admin_password"],
		"CONFIRM_PASSWORD"=> $_REQUEST["admin_password_confirm"],
		"ACTIVE"				=> "Y",
		"GROUP_ID"			=> array(1)
		);
	$res = $user->Update(1, $arFields);
	if (!$res)
	{
		$strErrorMessage .= InstallGetMessage("ERR_ADMIN_CREATE").$user->LAST_ERROR;
	}
}

if (strlen($strErrorMessage)<=0)
{
	COption::SetOptionString("main", "email_from", $email);
	CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/public", $_SERVER["DOCUMENT_ROOT"]);
}

///!!!!!!!!!!
if(strlen($strErrorMessage) <= 0)
{
	// Delete unused db files
	function InstallDeleteDirRec($path)
	{
		$path = str_replace("\\", "/", $path);

		if (!file_exists($path))
			return;

		if (!is_dir($path))
		{
			@unlink($path);
			return;
		}

		if ($handle = opendir($path))
		{
			while (($file = readdir($handle)) !== false)
			{
				if ($file == "." || $file == "..")
					continue;

				if (is_dir($path."/".$file))
					InstallDeleteDirRec($path."/".$file);
				else
					@unlink($path."/".$file);
			}
		}
		closedir($handle);

		@rmdir($path);

		return;
	}

	function InstallClearUnUsedDBFiles($path, $usedDB)
	{
		$path = str_replace("\\", "/", $path);

		if ($usedDB == "mysql")
			$arUnUsedDB = array("oracle", "mssql");
		elseif ($usedDB == "oracle")
			$arUnUsedDB = array("mysql", "mssql");
		elseif ($usedDB == "mssql")
			$arUnUsedDB = array("mysql", "oracle");
		else
			return;

		if ($handle = opendir($path))
		{
			while (($file = readdir($handle)) !== false)
			{
				if ($file == "." || $file == "..")
					continue;

				if (is_dir($path."/".$file))
				{
					if (in_array(strtolower($file), $arUnUsedDB))
						InstallDeleteDirRec($path."/".$file);
					else
						InstallClearUnUsedDBFiles($path."/".$file, $usedDB);
				}
			}
		}
		closedir($handle);

		return;
	}

	InstallClearUnUsedDBFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules", $dbType);
}

if (strlen($strErrorMessage)>0)
	$Step = 6;

//*******************************************************/
//*******************************************************/
}
//*******************************************************/
//*******************************************************/


/********************************************************/
/**********   PRE-HTML   ********************************/
/********************************************************/

//*******************************************************/
//*******************************************************/
if ($Step == 4)
{
//*******************************************************/
//*******************************************************/

//--------------   PARAMS   ------------------------------
$SYSTEM_min_avail_memory = 12;		// Min & recomended memory in Mb
$SYSTEM_rec_avail_memory = 32;

$SYSTEM_min_avail_disk = 20;			// Min disk size in Mb
$SYSTEM_min_avail_disk_tmp = 5;		// Min tmp disk size in Mb

$PHP_vercheck_min = "4.1.0";
$PHP_vercheck_max = "";

$Apache_vercheck_min = "1.3.0";
$Apache_vercheck_max = "";

$IIS_vercheck_min = "5.0.0";
$IIS_vercheck_max = "";

$MySql_vercheck_min = "4.0.14";
$MySql_vercheck_max = "";

$Oracle_vercheck_min = "9.0";
$Oracle_vercheck_max = "";


//--------------   FUNCTIONS   ---------------------------
function GetInstallPHPSetting($val)
{
	return ((ini_get($val) == "1") ? "ON" : "OFF");
}

function ShowInstallResult($strRes, $strType = "OK")
{
	if (strlen($strRes)>0)
	{
		if (strtoupper($strType) == "ERROR" || strtoupper($strType) == "E")
			echo "<b><font color=\"#FF0000\">".$strRes."</font></b>";
		elseif (strtoupper($strType) == "NOTE" || strtoupper($strType) == "N")
			echo "<b><font color=\"#000000\">".$strRes."</font></b>";
		else
			echo "<b><font color=\"#009900\">".$strRes."</font></b>";
	}
}

function InstallVersionMinimax($strCurver, $strMinver, $strMaxver)
{
	if (!$strMaxver) $strMaxver = "0.0.0";

	$curver = explode(".", $strCurver);  for ($i = 0; $i < 3; $i++) $curver[$i] = IntVal($curver[$i]);
	$minver = explode(".", $strMinver);  for ($i = 0; $i < 3; $i++) $minver[$i] = IntVal($minver[$i]);
	$maxver = explode(".", $strMaxver);  for ($i = 0; $i < 3; $i++) $maxver[$i] = IntVal($maxver[$i]);

	if (($minver[0]>0 || $minver[1]>0 || $minver[2]>0)
		&&
		($curver[0]<$minver[0]
			|| (($curver[0]==$minver[0]) && ($curver[1]<$minver[1]))
			|| (($curver[0]==$minver[0]) && ($curver[1]==$minver[1]) && ($curver[2]<$minver[2]))
		))
		return false;
	elseif (($maxver[0]>0 || $maxver[1]>0 || $maxver[2]>0)
		&&
		($curver[0]>$maxver[0]
			|| (($curver[0]==$maxver[0]) && ($curver[1]>$maxver[1]))
			|| (($curver[0]==$maxver[0]) && ($curver[1]==$maxver[1]) && ($curver[2]>=$maxver[2]))
		))
		return false;
	else
		return true;
}

function InstallGetModuleInfo($path)
{
	include_once($path);

	$arr = explode("/", $path);
	$i = array_search("modules", $arr);
	$class_name = $arr[$i+1];

	return new $class_name;
}

//--------------   HTML REQUIED  ----------------------------
ob_start();
?>
<table border="0" cellspacing="1" cellpadding="2" width="100%">
	<tr>
		<td class="tablehead1" align="center">
			<font class="tableheadtext">
				<b><?php echo InstallGetMessage("SC_PARAM") ?></b>
			</font>
		</td>
		<td class="tablehead2" align="center">
			<font class="tableheadtext">
				<b><?php echo InstallGetMessage("SC_REQUIED") ?></b>
			</font>
		</td>
		<td class="tablehead3" align="center">
			<font class="tableheadtext">
				<b><?php echo InstallGetMessage("SC_CURRENT") ?></b>
			</font>
		</td>
	</tr>

	<?php  
	$strSERVER_SOFTWARE = $_SERVER["SERVER_SOFTWARE"];
	if (strlen($strSERVER_SOFTWARE)<=0)
		$strSERVER_SOFTWARE = $_SERVER["SERVER_SIGNATURE"];

	$strWebServer = "";
	$strWebServerVersion = "";
	$strSERVER_SOFTWARE = Trim($strSERVER_SOFTWARE);
	if (preg_match("#^([a-zA-Z-]+).*?([\d]+\.[\d]+(\.[\d]+)?)#i", $strSERVER_SOFTWARE, $arSERVER_SOFTWARE))
	{
		$strWebServer = $arSERVER_SOFTWARE[1];
		$strWebServerVersion = $arSERVER_SOFTWARE[2];

		$vercheck_min = "";
		$vercheck_max = "";
		if (strtoupper($strWebServer)=="APACHE")
		{
			$vercheck_min = $Apache_vercheck_min;
			$vercheck_max = $Apache_vercheck_max;
		}
		elseif (strtoupper($strWebServer)=="MICROSOFT-IIS")
		{
			$vercheck_min = $IIS_vercheck_min;
			$vercheck_max = $IIS_vercheck_max;
		}
	}
	?>
	<tr>
		<td class="tablebody1" valign="top">
			<font class="tablefieldtext">
				<?php echo str_replace("#SERVER#", ((strlen($strWebServer)>0) ? $strWebServer : InstallGetMessage("SC_UNKNOWN")), InstallGetMessage("SC_SERVER_VERS")) ?>
			</font>
		</td>
		<td class="tablebody2" valign="top">
			<font class="tablebodytext">
				<?php  
				if (strlen($vercheck_min)>0)
					echo str_replace("#VER#", $vercheck_min, InstallGetMessage("SC_VER_VILKA1"));
				if (strlen($vercheck_min)>0 && strlen($vercheck_max)>0)
					echo InstallGetMessage("SC_VER_VILKA2");
				if (strlen($vercheck_max)>0)
					echo str_replace("#VER#", $vercheck_max, InstallGetMessage("SC_VER_VILKA3"));
				?>
			</font>
		</td>
		<td class="tablebody3" valign="top">
			<font class="tablebodytext">
				<?php  
				if (strlen($strWebServerVersion)>0)
				{
					if (InstallVersionMinimax($strWebServerVersion, $vercheck_min, $vercheck_max))
					{
						ShowInstallResult($strWebServerVersion, "O");
					}
					else
					{
						ShowInstallResult($strWebServerVersion, "E");
						$strErrorMessage .= InstallGetMessage("SC_WEBSERVER_VER_ER");
					}
				}
				else
				{
					ShowInstallResult(InstallGetMessage("SC_UNKNOWN1"), "E");
					//$strWarningMessage .= InstallGetMessage("SC_WEBSERVER_VER_NONE");
				}
				?>
			</font>
		</td>
	</tr>

	<?php  
	if (false && $dbType == "mysql")
	{
		$vercheck_min = $MySql_vercheck_min;
		$vercheck_max = $MySql_vercheck_max;
		?>
		<tr>
			<td class="tablebody1" valign="top">
				<font class="tablefieldtext">
				<?php echo GetMessage("SC_DB_VERS_MYSQL") ?>
				</font>
			</td>
			<td class="tablebody1" valign="top">
				<font class="tablebodytext">
				<?php  
				if (strlen($vercheck_min) > 0)
					echo str_replace("#VER#", $vercheck_min, GetMessage("SC_VER_VILKA1"));
				if (strlen($vercheck_min) > 0 && strlen($vercheck_max) > 0)
					echo GetMessage("SC_VER_VILKA2");
				if (strlen($vercheck_max) > 0)
					echo str_replace("#VER#", $vercheck_max, GetMessage("SC_VER_VILKA3"));
				?>
				</font>
			</td>
			<td class="tablebody1" valign="top">
				<font class="tablebodytext">
				<?php  
				$curMySqlVer = "";
				$dbQueryRes = $DB->Query("select VERSION() as ver", True);
				if ($arQueryRes = $dbQueryRes->Fetch())
					$curMySqlVer = trim($arQueryRes["ver"]);

				if (strlen($curMySqlVer) > 0)
				{
					if (InstallVersionMinimax($curMySqlVer, $vercheck_min, $vercheck_max))
					{
						ShowInstallResult($curMySqlVer, "O");
					}
					else
					{
						ShowInstallResult($curMySqlVer, "E");
						$strErrorMessage .= InstallGetMessage("SC_DB_VERS_MYSQL_ER");
					}
				}
				else
				{
					ShowInstallResult($curMySqlVer, "E");
				}
				?>
				</font>
			</td>
		</tr>
		<?php  
	}
	?>
	<tr>
		<td class="tablebody1" valign="top">
			<font class="tablefieldtext">
				<?php echo InstallGetMessage("SC_PHP_VERS") ?>
			</font>
		</td>
		<td class="tablebody2" valign="top">
			<font class="tablebodytext">
				<?php  
				if (strlen($PHP_vercheck_min)>0)
					echo str_replace("#VER#", $PHP_vercheck_min, InstallGetMessage("SC_VER_VILKA1"));
				if (strlen($PHP_vercheck_min)>0 && strlen($PHP_vercheck_max)>0)
					echo InstallGetMessage("SC_VER_VILKA2");
				if (strlen($PHP_vercheck_max)>0)
					echo str_replace("#VER#", $PHP_vercheck_max, InstallGetMessage("SC_VER_VILKA3"));
				?>&nbsp;
			</font>
		</td>
		<td class="tablebody3" valign="top">
			<font class="tablebodytext">
				<?php  
				if (InstallVersionMinimax(phpversion(), $PHP_vercheck_min, $PHP_vercheck_max))
				{
					ShowInstallResult(phpversion(), "O");
				}
				else
				{
					ShowInstallResult(phpversion(), "E");
					$strErrorMessage .= InstallGetMessage("SC_PHP_VER_ER");
				}
				?>
			</font>
		</td>
	</tr>

	<tr>
		<td class="tablebody4" colspan="3">
			<font class="tablefieldtext">
				<b><?php echo InstallGetMessage("SC_PHP_SETTINGS") ?></b>
			</font>
		</td>
	</tr>
	<tr>
		<td class="tablebody1" valign="top">
			<font class="tablefieldtext">
				&nbsp; - safe mode
			</font>
		</td>
		<td class="tablebody2" valign="top">
			<font class="tablebodytext">
				<?php echo InstallGetMessage("SC_TURN_OFF") ?>
			</font>
		</td>
		<td class="tablebody3" valign="top">
			<font class="tablebodytext">
				<?php  
				if (GetInstallPHPSetting("safe_mode")=="ON")
				{
					ShowInstallResult(InstallGetMessage("SC_TURN_ON"), "E");
					$strErrorMessage .= InstallGetMessage("SC_SAFE_MODE_ER");
				}
				else
				{
					ShowInstallResult(InstallGetMessage("SC_TURN_OFF"), "O");
				}
				?>
			</font>
		</td>
	</tr>

	<tr>
		<td class="tablebody1" valign="top">
			<font class="tablefieldtext">
				&nbsp; - <?php echo InstallGetMessage("SC_SESS_PATH") ?>
			</font>
		</td>
		<td class="tablebody2" valign="top">
			<font class="tablebodytext">
				<?php echo InstallGetMessage("SC_SETTED") ?>
			</font>
		</td>
		<td class="tablebody3" valign="top">
			<font class="tablebodytext">
				<?php  
				$sp = session_save_path();
				if (strlen($sp)>0)
				{
					ShowInstallResult($sp, "O");
					echo " ";
					if (file_exists($sp))
					{
						if (is_writable($sp))
							ShowInstallResult(InstallGetMessage("SC_CAN_WRITE"), "O");
						else
						{
							ShowInstallResult(InstallGetMessage("SC_CAN_NOT_WRITE"), "E");
							//$strErrorMessage .= InstallGetMessage("SC_SESS_PATH_WRITE_ER");
						}
					}
					else
					{
						ShowInstallResult(InstallGetMessage("SC_NOT_EXISTS"), "E");
						//$strErrorMessage .= InstallGetMessage("SC_SESS_PATH_NO_ER");
					}
				}
				else
				{
					ShowInstallResult(InstallGetMessage("SC_NOT_SETTED"), "E");
					//$strErrorMessage .= InstallGetMessage("SC_SESS_PATH_EMP_ER");
				}
				?>
			</font>
		</td>
	</tr>

	<tr>
		<td class="tablebody4" colspan="3">
			<font class="tablefieldtext">
				<b><?php echo InstallGetMessage("SC_REQUIED_PHP_MODS") ?></b>
			</font>
		</td>
	</tr>
	<!--
	<tr>
		<td class="tablebody1" valign="top">
			<font class="tablefieldtext">
				&nbsp; - <a href="http://www.php.net/manual/en/ref.xml.php" target="_blank"><?php echo InstallGetMessage("SC_MOD_XML") ?></a>
			</font>
		</td>
		<td class="tablebody2" valign="top">
			<font class="tablebodytext">
				<?php echo InstallGetMessage("SC_SETTED") ?>
			</font>
		</td>
		<td class="tablebody3" valign="top">
			<font class="tablebodytext">
				<?php  
				/*
				if (extension_loaded('xml') && function_exists("xml_parser_create"))
				{
					ShowInstallResult(InstallGetMessage("SC_SETTED"), "O");
				}
				else
				{
					ShowInstallResult(InstallGetMessage("SC_NOT_SETTED"), "E");
					$strErrorMessage .= InstallGetMessage("SC_NO_XML_LIB_ER");
				}
				*/
				?>
			</font>
		</td>
	</tr>
	//-->
	<?php  if (strtolower($dbType)=="oracle"):?>
		<tr>
			<td class="tablebody1" valign="top">
				<font class="tablefieldtext">
					&nbsp; - <a href="http://ru.php.net/manual/en/ref.oci8.php" target="_blank"><?php echo InstallGetMessage("SC_MOD_ORACLE") ?></a>
				</font>
			</td>
			<td class="tablebody2" valign="top">
				<font class="tablebodytext">
					<?php echo InstallGetMessage("SC_SETTED") ?>
				</font>
			</td>
			<td class="tablebody3" valign="top">
				<font class="tablebodytext">
					<?php  
					if (function_exists("OCILogon"))
					{
						ShowInstallResult(InstallGetMessage("SC_SETTED"), "O");
					}
					else
					{
						ShowInstallResult(InstallGetMessage("SC_NOT_SETTED"), "E");
						$strErrorMessage .= InstallGetMessage("SC_NO_ORA_LIB_ER");
					}
					?>
				</font>
			</td>
		</tr>
	<?php  elseif (strtolower($dbType)=="mysql"):?>
		<tr>
			<td class="tablebody1" valign="top">
				<font class="tablefieldtext">
					&nbsp; - <a href="http://www.php.net/manual/en/ref.mysql.php" target="_blank"><?php echo InstallGetMessage("SC_MOD_MYSQL") ?></a>
				</font>
			</td>
			<td class="tablebody2" valign="top">
				<font class="tablebodytext">
					<?php echo InstallGetMessage("SC_SETTED") ?>
				</font>
			</td>
			<td class="tablebody3" valign="top">
				<font class="tablebodytext">
					<?php  
					if (function_exists("mysql_connect"))
					{
						ShowInstallResult(InstallGetMessage("SC_SETTED"), "O");
					}
					else
					{
						ShowInstallResult(InstallGetMessage("SC_NOT_SETTED"), "E");
						$strErrorMessage .= InstallGetMessage("SC_NO_MYS_LIB_ER");
					}
					?>
				</font>
			</td>
		</tr>
	<?php  elseif (strtolower($dbType)=="mssql"):?>
		<tr>
			<td class="tablebody1" valign="top">
				<font class="tablefieldtext">
					&nbsp; - <a href="http://www.php.net/manual/en/ref.uodbc.php" target="_blank"><?php echo InstallGetMessage("SC_ODBC_FUNCTIONS") ?></a>
				</font>
			</td>
			<td class="tablebody2" valign="top">
				<font class="tablebodytext">
					<?php echo InstallGetMessage("SC_SETTED") ?>
				</font>
			</td>
			<td class="tablebody3" valign="top">
				<font class="tablebodytext">
					<?php  
					if (function_exists("odbc_connect"))
					{
						ShowInstallResult(InstallGetMessage("SC_SETTED"), "O");
					}
					else
					{
						ShowInstallResult(InstallGetMessage("SC_NOT_SETTED"), "E");
						$strErrorMessage .= InstallGetMessage("SC_ERR_NO_ODBC");
					}
					?>
				</font>
			</td>
		</tr>
	<?php  endif;?>
	<tr>
		<td class="tablebody1" valign="top">
			<font class="tablefieldtext">
				&nbsp; - <a href="http://www.php.net/manual/en/ref.regex.php" target="_blank"><?php echo InstallGetMessage("SC_MOD_POSIX_REG") ?></a>
			</font>
		</td>
		<td class="tablebody2" valign="top">
			<font class="tablebodytext">
				<?php echo InstallGetMessage("SC_SETTED") ?>
			</font>
		</td>
		<td class="tablebody3" valign="top">
			<font class="tablebodytext">
				<?php  
				if (function_exists("eregi"))
				{
					ShowInstallResult(InstallGetMessage("SC_SETTED"), "O");
				}
				else
				{
					ShowInstallResult(InstallGetMessage("SC_NOT_SETTED"), "E");
					$strErrorMessage .= InstallGetMessage("SC_NO_EREG_LIB_ER");
				}
				?>
			</font>
		</td>
	</tr>
	<tr>
		<td class="tablebody1" valign="top">
			<font class="tablefieldtext">
				&nbsp; - <a href="http://www.php.net/manual/en/ref.pcre.php" target="_blank"><?php echo InstallGetMessage("SC_MOD_PERL_REG") ?></a>
			</font>
		</td>
		<td class="tablebody2" valign="top">
			<font class="tablebodytext">
				<?php echo InstallGetMessage("SC_SETTED") ?>
			</font>
		</td>
		<td class="tablebody3" valign="top">
			<font class="tablebodytext">
				<?php  
				if (function_exists("preg_match"))
				{
					ShowInstallResult(InstallGetMessage("SC_SETTED"), "O");
				}
				else
				{
					ShowInstallResult(InstallGetMessage("SC_NOT_SETTED"), "E");
					$strErrorMessage .= InstallGetMessage("SC_NO_PERE_LIB_ER");
				}
				?>
			</font>
		</td>
	</tr>
</table>
<?php  
$strRequiedHTMLTable = ob_get_clean();

//--------------   HTML DISK  ----------------------------
ob_start();
?>
<table border="0" cellspacing="1" cellpadding="2" width="100%">
	<tr>
		<td class="tablehead1" align="center">
			<font class="tableheadtext">
				<b><?php echo InstallGetMessage("SC_PARAM") ?></b>
			</font>
		</td>
		<td class="tablehead3" align="center">
			<font class="tableheadtext">
				<b><?php echo InstallGetMessage("SC_VALUE") ?></b>
			</font>
		</td>
	</tr>
	<?php  
	function InstallRecursDiskAccessCheck($start_dir, &$arError, $arFilter = array())
	{
		$iMaxDirs = 19;
		if (count($arError)>$iMaxDirs)
			return false;

		for ($i = 0; $i < count($arFilter); $i++)
		{
			if (strlen($arFilter[$i])<=strlen($start_dir)
				&& substr($start_dir, 0, strlen($arFilter[$i]))==$arFilter[$i])
			{
				return True;
			}
		}

		$bIsWrite = (is_writeable($start_dir) ? "Y" : "N");
		$bIsRead = (is_readable($start_dir) ? "Y" : "N");
		if ($bIsWrite!="Y" || $bIsRead!="Y")
		{
			$arError[] = array($start_dir."/".$file, "Y", $bIsWrite, $bIsRead);
			if (count($arError)>$iMaxDirs)
				return false;
		}

		if ($handle = @opendir($start_dir))
		{
			while (($file = readdir($handle)) !== false)
			{
				if ($file == "." || $file == "..") continue;

				if (is_dir($start_dir."/".$file))
				{
					InstallRecursDiskAccessCheck($start_dir."/".$file, $arError, $arFilter);

					if (count($arError)>$iMaxDirs)
						return false;
				}
				else
				{
					$bIsWrite = (is_writeable($start_dir."/".$file) ? "Y" : "N");
					$bIsRead = (is_readable($start_dir."/".$file) ? "Y" : "N");

					if ($bIsWrite!="Y" || $bIsRead!="Y")
					{
						$arError[] = array($start_dir."/".$file, "N", $bIsWrite, $bIsRead);
						if (count($arError)>$iMaxDirs)
							return false;
					}
				}
			}
			@closedir($handle);
		}

		return True;
	}
	?>
	<tr>
		<td class="tablebody1" valign="top">
			<font class="tablefieldtext">
				<?php echo InstallGetMessage("SC_DISK_BITRIX") ?> <i><?php echo $_SERVER["DOCUMENT_ROOT"]."/bitrix" ?></i>
			</font>
		</td>
		<td class="tablebody3" valign="top">
			<font class="tablebodytext">
				<?php  
				$arError = array();
				$bResCheckAccess = InstallRecursDiskAccessCheck($_SERVER["DOCUMENT_ROOT"]."/bitrix", $arError, array());
				if (count($arError) > 0)
				{
					$strErrorMessage .= InstallGetMessage("SC_FBITRIX_NOWRITE");

					for ($i = 0; $i < count($arError); $i++)
					{
						echo ($arError[$i][1]=="Y") ? InstallGetMessage("SC_CATALOG")." " : InstallGetMessage("SC_FILE")." ";
						echo substr($arError[$i][0], strlen($_SERVER["DOCUMENT_ROOT"]));
						if ($arError[$i][2]!="Y")
							ShowInstallResult(" ".InstallGetMessage("SC_CAN_NOT_WRITE"), "E");
						if ($arError[$i][2]!="Y" && $arError[$i][3]!="Y")
							echo " ".InstallGetMessage("SC_AND");
						if ($arError[$i][3]!="Y")
							ShowInstallResult(" ".InstallGetMessage("SC_CAN_NOT_READ"), "E");
						echo "<br>";
					}

					if (!$bResCheckAccess)
					{
						echo "<br>";
						ShowInstallResult(str_replace("#NUM#", count($arError), InstallGetMessage("SC_DISK_MORE_20")), "E");
					}
				}
				else
				{
					ShowInstallResult(InstallGetMessage("SC_DISK_AVAIL_READ_WRITE"), "O");
				}
				?>
			</font>
		</td>
	</tr>
	<tr>
		<td class="tablebody1" valign="top">
			<font class="tablefieldtext">
				<?php echo InstallGetMessage("SC_DISK_UPLOAD") ?> <i><?php echo $_SERVER["DOCUMENT_ROOT"]."/upload" ?></i>
			</font>
		</td>
		<td class="tablebody3" valign="top">
			<font class="tablebodytext">
				<?php  
				$arError = array();
				$bResCheckAccess = InstallRecursDiskAccessCheck($_SERVER["DOCUMENT_ROOT"]."/upload", $arError, array());
				if (count($arError) > 0)
				{
					$strWarningMessage .= InstallGetMessage("SC_FUPLOAD_NOWRITE");

					for ($i = 0; $i < count($arError); $i++)
					{
						echo ($arError[$i][1]=="Y") ? InstallGetMessage("SC_CATALOG")." " : InstallGetMessage("SC_FILE")." ";
						echo substr($arError[$i][0], strlen($_SERVER["DOCUMENT_ROOT"]));
						if ($arError[$i][2]!="Y")
							ShowInstallResult(" ".InstallGetMessage("SC_CAN_NOT_WRITE"), "E");
						if ($arError[$i][2]!="Y" && $arError[$i][3]!="Y")
							echo " ".InstallGetMessage("SC_AND");
						if ($arError[$i][3]!="Y")
							ShowInstallResult(" ".InstallGetMessage("SC_CAN_NOT_READ"), "E");
						echo "<br>";
					}

					if (!$bResCheckAccess)
					{
						echo "<br>";
						ShowInstallResult(str_replace("#NUM#", count($arError), InstallGetMessage("SC_DISK_MORE_20")), "E");
					}
				}
				else
				{
					ShowInstallResult(InstallGetMessage("SC_DISK_AVAIL_READ_WRITE1"), "O");
				}
				?>
			</font>
		</td>
	</tr>
	<tr>
		<td class="tablebody1" valign="top">
			<font class="tablefieldtext">
				<?php echo InstallGetMessage("SC_DISK_PUBLIC") ?> <i><?php echo $_SERVER["DOCUMENT_ROOT"] ?></i>
			</font>
		</td>
		<td class="tablebody3" valign="top">
			<font class="tablebodytext">
				<?php  
				$arError = array();
				$bResCheckAccess = InstallRecursDiskAccessCheck($_SERVER["DOCUMENT_ROOT"], $arError, array($_SERVER["DOCUMENT_ROOT"]."/upload", $_SERVER["DOCUMENT_ROOT"]."/bitrix"));
				if (count($arError) > 0)
				{
					$strWarningMessage .= InstallGetMessage("SC_FSITE_NOWRITE");

					for ($i = 0; $i < count($arError); $i++)
					{
						echo ($arError[$i][1]=="Y") ? InstallGetMessage("SC_CATALOG")." " : InstallGetMessage("SC_FILE")." ";
						echo substr($arError[$i][0], strlen($_SERVER["DOCUMENT_ROOT"]));
						if ($arError[$i][2]!="Y")
							ShowInstallResult(" ".InstallGetMessage("SC_CAN_NOT_WRITE"), "E");
						if ($arError[$i][2]!="Y" && $arError[$i][3]!="Y")
							echo " ".InstallGetMessage("SC_AND");
						if ($arError[$i][3]!="Y")
							ShowInstallResult(" ".InstallGetMessage("SC_CAN_NOT_READ"), "E");
						echo "<br>";
					}

					if (!$bResCheckAccess)
					{
						echo "<br>";
						ShowInstallResult(str_replace("#NUM#", count($arError), InstallGetMessage("SC_DISK_MORE_20")), "E");
					}
				}
				else
				{
					ShowInstallResult(InstallGetMessage("SC_DISK_AVAIL_READ_WRITE2"), "O");
				}
				?>
			</font>
		</td>
	</tr>
	<?php  
	if (!is_readable($_SERVER["DOCUMENT_ROOT"]."/index.php") || !is_writeable($_SERVER["DOCUMENT_ROOT"]."/index.php"))
	{
		?>
		<tr>
			<td class="tablebody1" valign="top">
				<font class="tablefieldtext">
					<?php echo InstallGetMessage("SC_FILE") ?> <i><?php echo $_SERVER["DOCUMENT_ROOT"]."/index.php" ?></i>
				</font>
			</td>
			<td class="tablebody3" valign="top">
				<font class="tablebodytext">
					<?php  
					if (!is_readable($_SERVER["DOCUMENT_ROOT"]."/index.php"))
					{
						ShowInstallResult(InstallGetMessage("SC_PF_NOT_READABLE"), "E");
						$strErrorMessage .= str_replace("#FOLDER#", $_SERVER["DOCUMENT_ROOT"]."/index.php", InstallGetMessage("SC_PF_NOT_READABLE1"));
					}
					if (!is_writeable($_SERVER["DOCUMENT_ROOT"]."/index.php"))
					{
						ShowInstallResult(InstallGetMessage("SC_PF_NOT_WRITEABLE"), "E");
						$strErrorMessage .= str_replace("#FOLDER#", $_SERVER["DOCUMENT_ROOT"]."/index.php", InstallGetMessage("SC_PF_NOT_WRITEABLE1"));
					}
					?>
				</font>
			</td>
		</tr>
		<?php  
	}
	?>
</table>
<?php  
$strDiskHTMLTable = ob_get_clean();

//--------------   HTML RECOMMENDED  ----------------------------
ob_start();
?>
<table border="0" cellspacing="1" cellpadding="2" width="100%">
	<tr>
		<td class="tablehead1" align="center">
			<font class="tableheadtext">
				<b><?php echo InstallGetMessage("SC_PARAM") ?></b>
			</font>
		</td>
		<td class="tablehead2" align="center">
			<font class="tableheadtext">
				<b><?php echo InstallGetMessage("SC_RECOMMEND") ?></b>
			</font>
		</td>
		<td class="tablehead3" align="center">
			<font class="tableheadtext">
				<b><?php echo InstallGetMessage("SC_CURRENT") ?></b>
			</font>
		</td>
	</tr>

	<tr>
		<td class="tablebody1" valign="top">
			<font class="tablefieldtext">
				<?php echo InstallGetMessage("SC_AVAIL_DISK_SPACE") ?>
			</font>
		</td>
		<td class="tablebody2" valign="top">
			<font class="tablebodytext">
				<?php  
				if (IntVal($SYSTEM_min_avail_disk)>0)
					echo str_replace("#SIZE#", $SYSTEM_min_avail_disk, InstallGetMessage("SC_AVAIL_DISK_SPACE_SIZE"));
				?>&nbsp;
			</font>
		</td>
		<td class="tablebody3" valign="top">
			<font class="tablebodytext">
				<?php  
				$real_avail_disk = @disk_free_space($_SERVER["DOCUMENT_ROOT"]);
				$real_avail_disk = $real_avail_disk * 1.0 / 1000000.0;

				if ($real_avail_disk>$SYSTEM_min_avail_disk)
				{
					ShowInstallResult(Round($real_avail_disk, 1)." Mb", "O");
				}
				else
				{
					ShowInstallResult(Round($real_avail_disk, 1)." Mb", "E" );
					//$strWarningMessage .= InstallGetMessage("SC_AVAIL_SPACE_WARN");
				}
				?>
			</font>
		</td>
	</tr>
	<?php  
	$tmp_folder = ini_get("upload_tmp_dir");
	if (strlen($tmp_folder)>0 && file_exists($tmp_folder))
	{
		?>
		<tr>
			<td class="tablebody1" valign="top">
				<font class="tablefieldtext">
					<?php echo InstallGetMessage("SC_AVAIL_DISK_SPACE_TMP") ?> <i><?php echo $tmp_folder?></i>
				</font>
			</td>
			<td class="tablebody2" valign="top">
				<font class="tablebodytext">
					<?php  
					if (IntVal($SYSTEM_min_avail_disk_tmp)>0)
						echo str_replace("#SIZE#", $SYSTEM_min_avail_disk_tmp, InstallGetMessage("SC_AVAIL_DISK_SPACE_SIZE"));
					?>&nbsp;
				</font>
			</td>
			<td class="tablebody3" valign="top">
				<font class="tablebodytext">
					<?php  
					$real_avail_disk = @disk_free_space($tmp_folder);
					$real_avail_disk = $real_avail_disk * 1.0 / 1000000.0;

					if ($real_avail_disk>$SYSTEM_min_avail_disk_tmp)
					{
						ShowInstallResult(Round($real_avail_disk, 1)." Mb", "O");
					}
					else
					{
						ShowInstallResult(Round($real_avail_disk, 1)." Mb", "E");
						//$strWarningMessage .= InstallGetMessage("SC_AVAIL_DISK_SPACE_SIZE");
					}
					?>
				</font>
			</td>
		</tr>
		<?php  
	}
	?>

	<tr>
		<td class="tablebody4" colspan="3">
			<font class="tablefieldtext">
				<b><?php echo InstallGetMessage("SC_RECOM_PHP_SETTINGS") ?></b>
			</font>
		</td>
	</tr>
	<tr>
		<td class="tablebody1" valign="top">
			<font class="tablefieldtext">
				&nbsp; - <?php echo InstallGetMessage("SC_AVAIL_MEMORY") ?>
			</font>
		</td>
		<td class="tablebody2" valign="top">
			<font class="tablebodytext">
				<?php  
				if (IntVal($SYSTEM_min_avail_memory)>0)
					echo str_replace("#SIZE#", $SYSTEM_min_avail_memory, InstallGetMessage("SC_AVAIL_MEMORY_MIN"));
				if (IntVal($SYSTEM_min_avail_memory)>0 && IntVal($SYSTEM_rec_avail_memory)>0)
					echo ", ";
				if (IntVal($SYSTEM_rec_avail_memory)>0)
					echo str_replace("#SIZE#", $SYSTEM_rec_avail_memory, InstallGetMessage("SC_AVAIL_MEMORY_REC"));
				?>
			</font>
		</td>
		<td class="tablebody3" valign="top">
			<font class="tablebodytext">
				<?php  
				$memory_limit = ini_get('memory_limit');
				if (!$memory_limit || strlen($memory_limit)<=0)
					$memory_limit = get_cfg_var('memory_limit');

				$memory_limit = IntVal(Trim($memory_limit));
				if ($memory_limit < $SYSTEM_min_avail_memory)
				{
					ShowInstallResult($memory_limit." Mb", "E");
					//$strWarningMessage .= InstallGetMessage("SC_AVAIL_MEMORY_WARN");
				}
				else
				{
					ShowInstallResult($memory_limit." Mb", "O");
				}
				?>
			</font>
		</td>
	</tr>
	<tr>
		<td class="tablebody1" valign="top">
			<font class="tablefieldtext">
				&nbsp; - <?php echo InstallGetMessage("SC_ALLOW_UPLOAD") ?> (file_uploads)
			</font>
		</td>
		<td class="tablebody2" valign="top">
			<font class="tablebodytext">
				<?php echo InstallGetMessage("SC_TURN_ON1") ?>
			</font>
		</td>
		<td class="tablebody3" valign="top">
			<font class="tablebodytext">
				<?php  
				if (GetInstallPHPSetting("file_uploads")=="ON")
				{
					ShowInstallResult(InstallGetMessage("SC_TURN_ON1"), "O");
					echo ", ";
					$sp = ini_get("upload_tmp_dir");
					if (strlen($sp)>0)
					{
						echo InstallGetMessage("SC_TEMP_FOLDER")." <b>".$sp."</b> ";
						if (file_exists($sp))
						{
							if (is_writable($sp))
								ShowInstallResult(InstallGetMessage("SC_CAN_WRITE1"), "O");
							else
							{
								ShowInstallResult(InstallGetMessage("SC_CAN_NOT_WRITE1"), "E");
								//$strWarningMessage .= InstallGetMessage("SC_UPL_PATH_WRITE_ER");
							}
						}
						else
						{
							ShowInstallResult(InstallGetMessage("SC_NOT_EXISTS"), "E");
							//$strWarningMessage .= InstallGetMessage("SC_UPL_PATH_NO_ER");
						}
					}
					else
					{
						ShowInstallResult(InstallGetMessage("SC_NO_TEMP_FOLDER"), "E");
						//$strWarningMessage .= InstallGetMessage("SC_UPL_PATH_EMP_ER");
					}
				}
				else
				{
					ShowInstallResult(InstallGetMessage("SC_TURN_OFF1"), "E");
					//$strWarningMessage .= InstallGetMessage("SC_UPL_PATH_EMP_ER");
				}
				?>
			</font>
		</td>
	</tr>
	<tr>
		<td class="tablebody1" valign="top">
			<font class="tablefieldtext">
				&nbsp; - <?php echo InstallGetMessage("SC_SHOW_ERRORS") ?> (display_errors)
			</font>
		</td>
		<td class="tablebody2" valign="top">
			<font class="tablebodytext">
				<?php echo InstallGetMessage("SC_TURN_ON1")?>
			</font>
		</td>
		<td class="tablebody3" valign="top">
			<font class="tablebodytext">
				<?php  ((GetInstallPHPSetting("display_errors")=="ON") ? ShowInstallResult(InstallGetMessage("SC_TURN_ON1"), "O") : ShowInstallResult(InstallGetMessage("SC_TURN_OFF1"), "E"))?>
			</font>
		</td>
	</tr>
	<tr>
		<td class="tablebody1" valign="top">
			<font class="tablefieldtext">
				&nbsp; - <?php echo InstallGetMessage("SC_REGISTER_GLOBALS") ?> (register_globals)
			</font>
		</td>
		<td class="tablebody2" valign="top">
			<font class="tablebodytext">
				<?php echo InstallGetMessage("SC_TURN_ON1")?>
			</font>
		</td>
		<td class="tablebody3" valign="top">
			<font class="tablebodytext">
				<?php  ((GetInstallPHPSetting("register_globals")=="ON") ? ShowInstallResult(InstallGetMessage("SC_TURN_ON1"), "O") : ShowInstallResult(InstallGetMessage("SC_TURN_OFF1"), "E"))?>
			</font>
		</td>
	</tr>

	<tr>
		<td class="tablebody4" colspan="3">
			<font class="tablefieldtext">
				<b><?php echo InstallGetMessage("SC_RECOM_PHP_MODULES") ?></b>
			</font>
		</td>
	</tr>
	<tr>
		<td class="tablebody1" valign="top">
			<font class="tablefieldtext">
				&nbsp; - <a href="http://www.php.net/manual/en/ref.zlib.php" target="_blank">Zlib Compression</a>
			</font>
		</td>
		<td class="tablebody2" valign="top">
			<font class="tablebodytext">
				<?php echo InstallGetMessage("SC_SETTED") ?>
			</font>
		</td>
		<td class="tablebody3" valign="top">
			<font class="tablebodytext">
				<?php  
				if (extension_loaded('zlib') && function_exists("gzcompress"))
				{
					ShowInstallResult(InstallGetMessage("SC_SETTED"), "O");
				}
				else
				{
					ShowInstallResult(InstallGetMessage("SC_NOT_SETTED"), "E");
					$strWarningMessage .= InstallGetMessage("SC_NO_ZLIB_LIB_ER");
				}
				?>
			</font>
		</td>
	</tr>
	<tr>
		<td class="tablebody1" valign="top">
			<font class="tablefieldtext">
				&nbsp; - <a href="http://www.php.net/manual/en/ref.image.php" target="_blank"><?php echo InstallGetMessage("SC_MOD_GD") ?></a>
			</font>
		</td>
		<td class="tablebody2" valign="top">
			<font class="tablebodytext">
				<?php echo InstallGetMessage("SC_SETTED") ?>
			</font>
		</td>
		<td class="tablebody3" valign="top">
			<font class="tablebodytext">
				<?php  
				if (function_exists("imagecreate"))
				{
					ShowInstallResult(InstallGetMessage("SC_SETTED"), "O");
				}
				else
				{
					ShowInstallResult(InstallGetMessage("SC_NOT_SETTED"), "E");
					$strWarningMessage .= InstallGetMessage("SC_NO_GD_LIB_ER");
				}
				?>
			</font>
		</td>
	</tr>
	<tr>
		<td class="tablebody1" valign="top">
			<font class="tablefieldtext">
				&nbsp; - <a href="http://www.freetype.org" target="_blank">Free Type Library</a>
			</font>
		</td>
		<td class="tablebody2" valign="top">
			<font class="tablebodytext">
				<?php echo InstallGetMessage("SC_SETTED") ?>
			</font>
		</td>
		<td class="tablebody3" valign="top">
			<font class="tablebodytext">
				<?php  
				if (function_exists("imagettftext"))
				{
					ShowInstallResult(InstallGetMessage("SC_SETTED"), "O");
				}
				else
				{
					ShowInstallResult(InstallGetMessage("SC_NOT_SETTED"), "E");
					$strWarningMessage .= InstallGetMessage("SC_NO_GD_LIB_ER");
				}
				?>
			</font>
		</td>
	</tr>
</table>
<?php  
$strRecommendedHTMLTable = ob_get_clean();

//*******************************************************/
//*******************************************************/
}


/********************************************************/
/**********   BODY   ************************************/
/********************************************************/
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?php echo str_replace("#VERS#", MAIN_MOVER, InstallGetMessage("INS_TITLE"))?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $arLang[$lang]["CHARSET"]?>">
<style type="text/css">
	.text {font-family:Verdana,Arial, Helvetica, sans-serif; font-weight:normal; font-size:12px; color:#365069;}
	.error_text {font-family: Verdana,Arial, Helvetica, sans-serif; font-size:13px; color:#FF0000; font-weight:bold;}
	.warning_text {font-family: Verdana,Arial, Helvetica, sans-serif; font-size:13px; color:#990000; font-weight:bold;}
	.ok_text {font-family: Verdana,Arial,Helvetica,sans-serif; font-size:13px; color:#00FF00; font-weight:bold;}

	.tablehead, .tablehead1, .tablehead2, .tablehead3, .tablehead4, .tablehead5 {background-color:#C2DBED; padding:3px;}
	.tablehead1, .tablehead2, .tablehead3 {}
	.tablehead1 {}
	.tablehead3 {}
	.tablehead4, .tablehead5 {}
	.tablehead5 {}

	.tablebody, .tablebody1, .tablebody2, .tablebody3, .tablebody4 {background-color:#E2EFF7; padding:5px;}
	.tablebody1 {}
	.tablebody2 {}
	.tablebody3 {}
	.tablebody4 {}

	.tablebodytext, .tableheadtext, .tablefieldtext {font-family:Verdana,Arial, Helvetica, sans-serif; font-size:12px;}
	.tableheadtext, .tablebodytext {font-family:Verdana,Arial, Helvetica, sans-serif;color:#000000}
	.tablefieldtext {font-family:Verdana,Arial, Helvetica, sans-serif;color:#365069;}

	INPUT.button {padding:2px; font-family:Tahoma; font-size:12px; cursor: pointer;}
	INPUT.typeinput {font-size:12px;}
	.typeselect {font-family:Verdana,Arial, Helvetica, sans-serif;font-size:12px;}

	h3 {font-family:Verdana,Arial, Helvetica, sans-serif; font-size:14px; font-weight: bold; color: #585858; margin-bottom: 5px;}


	.smalltext{font-family:Verdana, Arial, Helvetica, sans-serif; color:#365069; font-size:10px;}
	.version{font-family:Verdana, Arial, Helvetica, sans-serif; color:#FF9933; size:18px; font-weight:bold;}
	.bitrixtitle{font-family:Verdana, Arial, Helvetica, sans-serif; color:#4083B5; size:18px; font-weight:bold;}
	.install{font-family:Verdana, Arial, Helvetica, sans-serif; size:18px; font-weight:bold;}
	.head{font-family:Verdana, Arial, Helvetica, sans-serif; font-weight:bold; color:#365069; size:18px;}
	.headbitrix{font-family:Verdana, Arial, Helvetica, sans-serif; color:#365069; font-size:12px; font-weight:bold;}
	.title{font-family:Verdana, Arial, Helvetica, sans-serif; color:#365069; font-weight:bold; font-size:16px;}
	.menu{ background-color:#E6F1F9; font-family:Verdana, Arial, Helvetica, sans-serif; color:#B4C0D0; font-size:12px; padding-left:10px; padding-right:5px;}
	.menuact{background-color:#D8E8F4; font-family:Verdana, Arial, Helvetica, sans-serif; color:#365069; font-size:12px; padding-left:10px; padding-right:5px; font-weight:bold;}
	.text11 {font-family:Verdana,Arial, Helvetica, sans-serif; font-weight:normal; color:#365069; font-size:12px; margin-bottom: 5px;}
</style>
</head>
<body link="#6C93AE" alink="#F1555A" vlink="#a4a4a4" style="margin-top:1px; margin-bottom:10px; margin-right:10px; margin-left:10px;">

<table width="100%"  border="0" cellspacing="0" cellpadding="0">
	<tr valign="top" height="33">
		<td width="0%">&nbsp;</td>
		<td width="0%" rowspan="2"><img src="/bitrix/images/install/bg_top_2.jpg" width="66" width="84" border="0"></td>
		<td width="100%">&nbsp;</td>
	</tr>
	<tr height="50">
		<td width="0%" valign="bottom" style="border-left: 1px solid #D5E7F3; border-top: 1px solid #D5E7F3;"><img src="/bitrix/images/install/bg_top_1.jpg" width="49" height="50" border="0"></td>
		<td width="100%" align="center" background="/bitrix/images/install/bg_top_3.jpg" style="background-position:left bottom; background-repeat:no-repeat; border-right: 1px solid #D5E7F3; border-top: 1px solid #D5E7F3;"><?php echo str_replace("#VERS#", MAIN_MOVER, InstallGetMessage("INS_TITLE1"))?>&nbsp;</td>
	</tr>
</table>
<table width="100%"  border="0" cellspacing="1" cellpadding="0" bgcolor="#D5E7F3">
	<tr>
		<td><table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#F4FBFF">
				<tr>
					<td valign="top"><table width="0%" border="0" cellspacing="0" cellpadding="0">
							<tr>
								<td><img src="/bitrix/images/install/tree.jpg" width="194" height="98" border="0"></td>
							</tr>
							<tr>
								<td>
								<br>
								<table width="0%"  border="0" cellspacing="0" cellpadding="0">
									<?php  
									$arStepsArray = array(
										1 => InstallGetMessage("INS_STEP1_TITLE"),
										2 => InstallGetMessage("INS_STEP2_TITLE"),
										3 => InstallGetMessage("INS_STEP3_TITLE"),
										4 => InstallGetMessage("INS_STEP4_TITLE"),
										5 => InstallGetMessage("INS_STEP5_TITLE"),
										6 => InstallGetMessage("INS_STEP6_TITLE"),
										7 => InstallGetMessage("INS_STEP7_TITLE")
										);
									foreach ($arStepsArray as $key => $val)
									{
										$strStyle = ((IntVal($key)<=$Step) ? "menuact" : "menu");
										$strImageEnd = ((IntVal($key)<=$Step) ? "_act" : "");
										?>
										<tr>
											<td class="<?php echo $strStyle ?>" width="100%"><?php echo $val ?></td>
											<td style="padding-right: 7px;"><img src="/bitrix/images/install/menu_<?php echo $key ?><?php echo $strImageEnd ?>.jpg" width="41" height="43" border="0"></td>
										</tr>
										<tr><td><img src="/bitrix/images/install/1.gif" width="1" height="8" border="0"></td></tr>
										<?php  
									}
									?>
								</table>
								<p align="center" class="text" style="padding:7px;"><a href="readme.html" target="_blank"><?php echo InstallGetMessage("GOTO_README") ?></a></p></td>
							</tr>
						</table></td>
						<td width="0%" bgcolor="#D5E7F3" rowspan="2"><img src="/bitrix/images/install/1.gif" border="0" width="1" height="1"></td>
					<?php  
					if ($Step==1)
					{
						?>
						<td width="100%" background="/bitrix/images/install/bg_top.jpg" style="background-repeat:repeat-x; background-position:top; padding-top:25px; padding-right:20px; padding-left:20px;" align="center" valign="top"><table width="100%"  border="0" cellspacing="0" cellpadding="0" style="border-top: 1px solid #B9D2E2; border-bottom: 1px solid #B9D2E2; border-left: 1px solid #B9D2E2; border-right: 1px solid #B9D2E2;">
							<tr>
								<td width="100%" height="60" background="/bitrix/images/install/begin_head.jpg" style="background-repeat:no-repeat; background-position:right;" valign="middle"><!--<div align="center"><font class="headbitrix"><?php echo InstallGetMessage("IS1_BIT")?></font></div>--><div style="padding-left:20px; padding-right:150px;" align="left"><font class="headbitrix"><?php echo InstallGetMessage("IS1_BIT_LOZ")?></font></div></td>
							</tr>
							<tr>
								<td width="100%" height="619" valign="top" align="left" background="/bitrix/images/install/begin_footer.jpg" style="background-repeat:no-repeat; background-position:right top;" bgcolor="#DCE9F2"><div style="padding:20px;">
						<?php  
					}
					else
					{
						?>
						<td width="100%" background="/bitrix/images/install/bg_top.jpg" style="background-repeat:repeat-x; background-position:top; padding-top:25px; padding-right:20px; padding-left:20px;" align="left" valign="top">
							<font class="title"><?php echo InstallGetMessage("INS_STEP".$Step."_TITLE") ?></font>
							<p><table width="100%" cellspacing="0" cellpadding="0" border="0"><tr><td width="100%" bgcolor="#D5E7F3"><img src="/bitrix/images/install/1.gif" border="0" width="1" height="1"></td></tr></table></p>
						<?php  
					}
					?>

<?php  
if (strlen($strErrorMessage)>0)
{
	?><p><table width="100%" cellpadding="2" cellspacing="0" bgcolor="#E6F1F9" border="0" style="border-left: 1px solid #D8E8F4; border-right: 1px solid #D8E8F4; border-top: 1px solid #D8E8F4; border-bottom: 1px solid #D8E8F4; "><tr><td width="0%"><img src="/bitrix/images/install/error.gif" width="19" height="19" border="0" align="left"></td><td width="100%"><font class="error_text"><?php echo $strErrorMessage ?></font></td></tr></table></p><?php  
}

if (strlen($strWarningMessage)>0)
{
	?><table width="100%" cellpadding="2" cellspacing="0" bgcolor="#E6F1F9" border="0" style="border-left: 1px solid #D8E8F4; border-right: 1px solid #D8E8F4; border-top: 1px solid #D8E8F4; border-bottom: 1px solid #D8E8F4; "><tr><td width="0%"><img src="/bitrix/images/install/warning.gif" width="23" height="21" border="0" align="left"></td><td width="100%"><font class="warning_text"><?php echo $strWarningMessage ?></font></td></tr></table><?php  
}

if (strlen($strOKMessage)>0)
{
	?><font class="ok_text"><?php echo $strOKMessage ?><br></font><br><?php  
}

/********************************************************/
/**********   HTML AND FORMS   **************************/
/********************************************************/

//*******************************************************/
//*******************************************************/
if ($Step==1)
{
//*******************************************************/
//*******************************************************/
?>


<div style="padding-right:245px;" align="left">
<?php  if ($Step==1):?>
	<form name="form1" method="POST" style="margin:0px;">
		<font class="text11"><!--<?php echo InstallGetMessage("INS_SELECT_LANG") ?>:&nbsp;
			<?php  
			reset($arLang);
			while (list($ln, $arL) = each($arLang))
			{
				$reference_id[] = $ln;
				$reference[] = $arL["LANG"];
			}
			$arr = array("reference_id"=>$reference_id, "reference"=>$reference);
			echo SelectBoxFromArray("lang", $arr, htmlspecialchars($lang), "", "", true);
			?>//-->
		</font>
	</form>
<?php  endif;?><br>
<?php echo InstallGetMessage("FIRST_PAGE") ?>

<br><br>
<form method="post" action="index.php">
	<input type="hidden" name="Step" value="2">
	<input type="hidden" name="lang" value="<?php echo $lang ?>">
	<input type="submit" value="<?php echo InstallGetMessage("INS_INSTALL1") ?>" class="button">
</form>
</div>


<?php  
//*******************************************************/
//*******************************************************/
}
elseif ($Step==2)
{
//*******************************************************/
//*******************************************************/
?>

<form method="post" action="index.php">

	<font class="text">

	<?php  
	$license_text_file = "license-".$lang.".html";
	if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$license_text_file))
		$license_text_file = "license.html";
	?>
	<iframe name="license_text" src="<?php echo $license_text_file ?>" width="520" height="250" border="0" frameBorder="1" scrolling="yes"></iframe>

	<br><br>

	<input class="typeinput" type="checkbox" name="agree_license" value="Y" id="agree_license_id">
	&nbsp;<label for="agree_license_id"><?php echo InstallGetMessage("LICENSE_AGREE_PROMT") ?></label>

	<br><br>

	</font>

	<input type="hidden" name="Step" value="3">
	<input type="hidden" name="lang" value="<?php echo $lang ?>">
	<div align="right">
	<input type="submit" value="<?php echo InstallGetMessage("INS_INSTALL") ?>" class="button">
	</div>
</form>
<?php  
//*******************************************************/
//*******************************************************/
}
elseif ($Step==3)
{
//*******************************************************/
//*******************************************************/
?>
<br>
<form method="POST" action="index.php">
<table border="0" cellspacing="1" cellpadding="2" width="100%">
	<tr>
		<td colspan="2" valign="center" align="center" nowrap class="tablehead5">
			<font class="tableheadtext"><b><?php echo InstallGetMessage("INS_LICENSE_HEAD") ?></b></font>
		</td>
	</tr>
	<tr>
		<td nowrap align="right" width="40%" class="tablebody1" valign="top">
			<font class="tablefieldtext"><font color="#FF0000">*</font>&nbsp;<?php echo InstallGetMessage("INS_LICENSE")?></font>
		</td>
		<td width="60%" class="tablebody3" valign="top">
			<input class="typeinput" type="text" name="license" size="30" value="<?php echo (strlen($_REQUEST["license"])>0) ? htmlspecialchars($_REQUEST["license"]) : "demo";?>"><br>
			<font class="tablebodytext"><small><?php echo InstallGetMessage("INS_LICENSE_NOTE") ?><br></small></font>
		</td>
	</tr>
	<tr>
		<td colspan="2" valign="center" align="center" nowrap class="tablehead4">
			<font class="tableheadtext"><b><?php echo InstallGetMessage("INS_DB_SELECTION") ?></b></font>
		</td>
	</tr>
	<tr> 
		<td align="right" class="tablebody1" valign="top">
			<font class="tablefieldtext"><font color="#FF0000">*</font>&nbsp;<?php echo InstallGetMessage("INS_DB_PROMT") ?>:<br><small><?php echo InstallGetMessage("INS_DB_PROMT_ALT") ?><br></small></font>
		</td>
		<td class="tablebody3" valign="top">
			<font class="tablebodytext">
			<?php  
			if (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/mysql/database.php"))
			{
				$dis = "";
				if (!function_exists("mysql_connect"))
					$dis = "disabled ";
				?><input type="radio" <?php echo $dis ?>name="dbtype"<?php  if (!isset($_REQUEST["dbtype"]) || $_REQUEST["dbtype"] != "mssql" && $_REQUEST["dbtype"] != "oracle") echo " checked";?> value="mysql" id="dbtype_mysql"><label for="dbtype_mysql">&nbsp;MySQL</label><br><?php  
			}
			if (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/oracle/database.php"))
			{
				$dis = "";
				if (!function_exists("OCILogon"))
					$dis = "disabled ";
				?><input type="radio" <?php echo $dis ?>name="dbtype"<?php  if ($_REQUEST["dbtype"] == "oracle") echo " checked";?> value="oracle" id="dbtype_oracle"><label for="dbtype_oracle">&nbsp;Oracle</label><br><?php  
			}
			if (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/mssql/database.php"))
			{
				$dis = "";
				if (!function_exists("odbc_connect"))
					$dis = "disabled ";
				?><input type="radio" <?php echo $dis ?>name="dbtype"<?php  if ($_REQUEST["dbtype"] == "mssql") echo " checked";?> value="mssql" id="dbtype_mssql"><label for="dbtype_mssql">&nbsp;Microsoft SQL Server</label><br><?php  
			}
			?>
			<small><?php echo InstallGetMessage("INS_DB_PROMT_HINT") ?><br></small>
			</font>
		</td>
	</tr>
</table>

<br>

<div align="right">
	<input type="hidden" name="Step" value="4">
	<input type="hidden" name="lang" value="<?php echo $lang; ?>">
	<input type="hidden" name="UNIID" value="<?php echo md5(time());?>">
	<!--<input class="button" type="reset" value="<?php echo InstallGetMessage("INS_RESET")?>">&nbsp;&nbsp;-->
	<input class="button" type="submit" name="install" value="<?php echo InstallGetMessage("INS_INSTALL") ?>"><br>
</div>

<br><br><br>

</form>
<?php  
//*******************************************************/
//*******************************************************/
}
elseif ($Step==4)
{
//*******************************************************/
//*******************************************************/
?>
<br>

<h3><?php echo InstallGetMessage("SC_SUBTITLE_REQUIED") ?></h3>
<font class="text">
	<?php echo InstallGetMessage("SC_SUBTITLE_REQUIED_DESC") ?><br><br>
</font>

<?php echo $strRequiedHTMLTable ?>

<br><br>

<h3><?php echo InstallGetMessage("SC_SUBTITLE_DISK") ?></h3>
<font class="text">
	<?php echo InstallGetMessage("SC_SUBTITLE_DISK_DESC") ?><br><br>
</font>

<?php echo $strDiskHTMLTable ?>

<br><br>

<h3><?php echo InstallGetMessage("SC_SUBTITLE_RECOMMEND") ?></h3>
<font class="text">
	<?php echo InstallGetMessage("SC_SUBTITLE_RECOMMEND_DESC") ?><br><br>
</font>

<?php echo $strRecommendedHTMLTable ?>

<br><br>

<?php  if (strlen($strErrorMessage)<=0):?>
	<form method="post" action="index.php">
		<input type="hidden" name="Step" value="5">
		<input type="hidden" name="lang" value="<?php echo $lang ?>">
		<input type="hidden" name="dbtype" value="<?php echo htmlspecialchars($dbType) ?>">
		<div align="right">
		<input type="submit" value="<?php echo InstallGetMessage("INS_INSTALL") ?>" class="button">
		</div>
	</form>
<?php  endif;?>
<p>
<table width="100%" cellpadding="2" cellspacing="0" bgcolor="#E6F1F9" border="0" style="border-left: 1px solid #D8E8F4; border-right: 1px solid #D8E8F4; border-top: 1px solid #D8E8F4; border-bottom: 1px solid #D8E8F4; "><tr><td width="0%"><font class="text" style="size:-2">
	<?php echo InstallGetMessage("SC_NOTES1") ?>
</font>
</td></tr></table>
</p>

<?php  

//*******************************************************/
//*******************************************************/
}
elseif ($Step==5)
{
//*******************************************************/
//*******************************************************/
?>

<br>
<form method="POST" action="index.php" onsubmit="this.install.disabled=true">
<table border="0" cellspacing="1" cellpadding="2" width="100%">
	<tr>
		<td colspan="2" valign="center" align="center" nowrap class="tablehead4">
			<font class="tableheadtext"><b><?php echo InstallGetMessage("INS_DATABASE_SETTINGS")?></b></font>
		</td>
	</tr>
	<?php  if ($dbType=="oracle") {?>
	<tr> 
		<td nowrap align="right" class="tablebody1" valign="top">
			<font class="tablefieldtext"><font color="#FF0000">*</font>&nbsp;<?php echo InstallGetMessage("INS_DATABASE_OR");?></font>
		</td>
		<td class="tablebody3" valign="top">
			<font class="tablebodytext">
			<input class="typeinput" type="text" name="database" size="30" value="<?php echo (strlen($_REQUEST["database"])>0) ? htmlspecialchars($_REQUEST["database"]) : ""?>"><br>
			<small><?php echo InstallGetMessage("INS_DB_ORACLE")?><br></small>
			</font>
		</td>
	</tr>
	<?php } elseif ($dbType=="mysql") {?>
		<tr> 
			<td nowrap align="right" valign="top" width="40%" class="tablebody1">
				<font class="tablefieldtext"><font color="#FF0000">*</font>&nbsp;<?php echo InstallGetMessage("INS_HOST")?></font>
			</td>
			<td width="60%" class="tablebody3" valign="top">
				<font class="tablebodytext">
				<input class="typeinput" type="text" name="host" size="30" value="<?php echo (strlen($_REQUEST["host"])>0) ? htmlspecialchars($_REQUEST["host"]) : "localhost"?>"><br>
				<small><?php echo InstallGetMessage("INS_HOST_DESCR") ?><br></small>
				</font>
			</td>
		</tr>
	<?php  } elseif ($dbType == "mssql") {?>
		<tr> 
			<td nowrap align="right" valign="top" width="40%" class="tablebody1">
				<font class="tablefieldtext"><font color="#FF0000">*</font>&nbsp;DSN:</font>
			</td>
			<td width="60%" class="tablebody3" valign="top">
				<font class="tablebodytext">
				<input class="typeinput" type="text" name="host" size="30" value="<?php echo (strlen($_REQUEST["host"])>0) ? htmlspecialchars($_REQUEST["host"]) : ""?>"><br>
				<small><?php echo InstallGetMessage("INS_HOST_DESCR_MSSQL") ?><br></small>
				</font>
			</td>
		</tr>
	<?php }?>
	
		<tr> 
			<td align="right" class="tablebody1" valign="top">
				<font class="tablefieldtext"><?php echo InstallGetMessage("INS_CREATE_USER") ?></font>
			</td>
			<td class="tablebody3" valign="top">
				<font class="tablebodytext">
				<input class="typeinput" type="checkbox" name="create_user" onclick="CheckRoot()" id="create_user" value="Y" <?php  if ($_REQUEST["create_user"]=="Y") echo "checked";?>>
				</font>
			</td>
		</tr>
	<?php
	if ($dbType=="oracle") {
	?>
	<tr> 
		<td nowrap align="right" class="tablebody1" valign="top">
			<div id=user_exists><font class="tablefieldtext"><font color="#FF0000">*</font>&nbsp;<?php echo InstallGetMessage("INS_USER")?></font></div>
			<div id=user_new style="display:none"><font class="tablefieldtext"><font color="#FF0000">*</font>&nbsp;<?php echo InstallGetMessage("INS_USER_NEW")?></font></div>
		</td>
		<td class="tablebody3" valign="top">
			<font class="tablebodytext">
			<input class="typeinput" type="text" name="user" size="30" value="<?php echo (strlen($_REQUEST["user"])>0) ? htmlspecialchars($_REQUEST["user"]) : "";?>"><br>
			<small><?php echo InstallGetMessage("INS_USER_OR_DESCR")?><br></small>
			</font>
		</td>
	</tr>
	<tr> 
		<td nowrap align="right" class="tablebody1" valign="top">
			<font class="tablefieldtext"><?php echo InstallGetMessage("INS_PASSWORD")?></font>
		</td>
		<td class="tablebody3" valign="top">
			<font class="tablebodytext">
			<input class="typeinput" type="password" name="password" size="30" value="<?php echo (strlen($_REQUEST["password"])>0) ? htmlspecialchars($_REQUEST["password"]) : "";?>"><br>
			<small><?php echo InstallGetMessage("INS_PASSWORD_OR_DESCR")?><br></small>
			</font>
		</td>
	</tr>

	<?php  } else {?>
	<tr> 
		<td nowrap align="right" class="tablebody1" valign="top">
			<div id=user_exists><font class="tablefieldtext"><font color="#FF0000">*</font>&nbsp;<?php echo InstallGetMessage("INS_USER")?></font></div>
			<div id=user_new style="display:none"><font class="tablefieldtext"><font color="#FF0000">*</font>&nbsp;<?php echo InstallGetMessage("INS_USER_NEW")?></font></div>
		</td>
		<td class="tablebody3" valign="top">
			<font class="tablebodytext">
			<input class="typeinput" type="text" name="user" size="30" value="<?php echo (strlen($_REQUEST["user"])>0) ? htmlspecialchars($_REQUEST["user"]) : "";?>"><br>
			<small><?php echo InstallGetMessage("INS_USER_DESCR")?><br></small>
			</font>
		</td>
	</tr>
	<tr> 
		<td nowrap align="right" class="tablebody1" valign="top">
			<font class="tablefieldtext"><?php echo InstallGetMessage("INS_PASSWORD")?></font>
		</td>
		<td class="tablebody3" valign="top">
			<font class="tablebodytext">
			<input class="typeinput" type="password" name="password" size="30" value="<?php echo (strlen($_REQUEST["password"])>0) ? htmlspecialchars($_REQUEST["password"]) : "";?>"><br>
			<small><?php echo InstallGetMessage("INS_PASSWORD_DESCR")?><br></small>
			</font>
		</td>
	</tr>
		<tr> 
			<td nowrap align="right" class="tablebody1" valign="top">
				<font class="tablefieldtext"><?php echo InstallGetMessage("INS_CREATE_DB") ?></font>
			</td>
			<td class="tablebody3" valign="top">
				<font class="tablebodytext">
				<input class="typeinput" type="checkbox" name="create_database" onclick="CheckRoot()" id="create_database" value="Y" <?php  if ($_REQUEST["create_database"]=="Y") echo "checked";?>>
				</font>
			</td>
		</tr>
	<tr> 
		<td nowrap align="right" class="tablebody1" valign="top">
			<div id=db_exists><font class="tablefieldtext"><font color="#FF0000">*</font>
<?php echo InstallGetMessage("INS_DATABASE");?></font></div>
			<div id=db_new style="display:none"><font class="tablefieldtext"><font color="#FF0000">*</font>
<?php echo InstallGetMessage("INS_DATABASE_NEW");?></font></div>
		</td>
		<td class="tablebody3" valign="top">
			<font class="tablebodytext">
			<input class="typeinput" type="text" name="database" size="30" value="<?php echo (strlen($_REQUEST["database"])>0) ? htmlspecialchars($_REQUEST["database"]) : "sitemanager5"?>"><br>
			<small><?php echo InstallGetMessage("INS_DATABASE_MY_DESC") ?><br></small>
			</font>
		</td>
	</tr>
	<?php  } ?>
	<tr> 
		<td nowrap align="right" class="tablebody1" valign="top">
			<font class="tablefieldtext"><font color="#FF0000">*</font>&nbsp;<?php echo InstallGetMessage("INS_ROOT_USER")?></font>
		</td>
		<td class="tablebody3" valign="top">
			<font class="tablebodytext">
			<input class="typeinput" type="text" name="root_user" id="root_user" size="30" value="<?php echo (strlen($_REQUEST["root_user"])>0) ? htmlspecialchars($_REQUEST["root_user"]) : "";?>"><br>
			<small><?php echo InstallGetMessage("INS_ROOT_USER_DESCR")?><br></small>
			</font>
		</td>
	</tr>
	<tr> 
		<td nowrap align="right" class="tablebody1" valign="top">
			<font class="tablefieldtext"><?php echo InstallGetMessage("INS_ROOT_PASSWORD")?></font>
		</td>
		<td class="tablebody3" valign="top">
			<font class="tablebodytext">
			<input class="typeinput" type="password" name="root_password" id="root_password" size="30" value="<?php echo (strlen($_REQUEST["root_password"])>0) ? htmlspecialchars($_REQUEST["root_password"]) : "";?>"><br>
			<small><?php echo InstallGetMessage("INS_ROOT_PASSWORD_DESCR")?><br></small>
			</font>
		</td>
	</tr>
	<script language=JavaScript>
		<?php if ($dbType=="oracle") {?>
		function CheckRoot()
		{
			obj2 = document.getElementById("create_user");

			obj3 = document.getElementById("root_user");
			obj4 = document.getElementById("root_password");
			
			obj7 = document.getElementById("user_exists");
			obj8 = document.getElementById("user_new");
			
			if (obj2.checked == true)
			{
				obj7.style.display = "none";
				obj8.style.display = "inline";
				obj3.disabled = false;
				obj4.disabled = false;
			} else {
				obj8.style.display = "none";
				obj7.style.display = "inline";
				obj3.disabled = true;
				obj4.disabled = true;
			}
		}
		<?php } else {?>
		function CheckRoot()
		{
			obj1 = document.getElementById("create_database");
			obj2 = document.getElementById("create_user");
			
			obj3 = document.getElementById("root_user");
			obj4 = document.getElementById("root_password");
			
			obj5 = document.getElementById("db_exists");
			obj6 = document.getElementById("db_new");
			
			obj7 = document.getElementById("user_exists");
			obj8 = document.getElementById("user_new");
			
			if (obj1.checked == true)
			{
				obj5.style.display = "none";
				obj6.style.display = "inline";
			} else {
				obj6.style.display = "none";
				obj5.style.display = "inline";
			}
			
			if (obj2.checked == true)
			{
				obj7.style.display = "none";
				obj8.style.display = "inline";
			} else {
				obj8.style.display = "none";
				obj7.style.display = "inline";
			}
			
			if (obj1.checked == true || obj2.checked == true)
			{
				obj3.disabled = false;
				obj4.disabled = false;
			}
			else
			{
				obj3.disabled = true;
				obj4.disabled = true;
			}
		}
		<?php }?>
		CheckRoot();
	</script>
	<?php  if ($dbType=="mysql") {?>
		<tr> 
			<td nowrap align="right" valign="top" class="tablebody1">
				<font class="tablefieldtext"><?php echo InstallGetMessage("INS_DB_CHARSET") ?></font>
			</td>
			<td class="tablebody3" valign="top">
				<font class="tablebodytext">
				<select name="create_database_charset">
					<option value="" <?php  if (strlen($_REQUEST["create_database_charset"])<=0) echo "selected";?>><?php echo InstallGetMessage("INS_DB_DEFAULT") ?></option>
					<?php
					if ($db_Conn) {
						$resCharsetList = @mysql_query("SHOW CHARACTER SET");
						while($e = mysql_fetch_row($resCharsetList)) 
							echo "<option value='$e[0]'".($_REQUEST['create_database_charset']==$e[0]?' selected':'').">$e[0]</option>";
					}

					if (!$resCharsetList) {
						$arCharsetList = array('cp1251','utf8','cp850','koi8r','latin1','latin2','swe7','ascii','euckr','koi8u','gb2312','cp1250','latin5','armscii8','ucs2','cp866','keybcs2','cp852','latin7','cp1256','cp1257','binary','geostd8','cp932');
						while($e=each($arCharsetList)) echo "<option value='$e[1]'".($_REQUEST['create_database_charset']==$e[1]?' selected':'').">$e[1]</option>";
					}
					?>
				</select><br>
				<small><?php echo InstallGetMessage("INS_CREATE_DB_CHAR_NOTE") ?></small><br>
				</font>
			</td>
		</tr>
		<tr> 
			<td nowrap align="right" valign="top" class="tablebody1">
				<font class="tablefieldtext"><?php echo InstallGetMessage("INS_CREATE_DB_TYPE") ?></font>
			</td>
			<td class="tablebody3" valign="top">
				<font class="tablebodytext">
				<select name="create_database_type">
					<option value="" <?php  if (strlen($_REQUEST["create_database_type"])<=0) echo "selected";?>><?php echo InstallGetMessage("INS_C_DB_TYPE_STAND") ?></option>
					<option value="innodb" <?php  if ($_REQUEST["create_database_type"]=="innodb") echo "selected";?>>innodb</option>
				</select><br>
				<!--small><?php echo InstallGetMessage("INS_CREATE_DB_TYPE_NOTE") ?></small><br-->
				</font>
			</td>
		</tr>
	<?php } ?>
	<tr>
		<td colspan="2" valign="center" align="center" nowrap class="tablehead5">
			<font class="tableheadtext"><b><?php echo InstallGetMessage("INS_ADDITIONAL_PARAMS")?></b></font>
		</td>
	</tr>
	<tr>
		<td nowrap align="right" width="40%" class="tablebody1" valign="top">
			<font class="tablefieldtext"><?php echo InstallGetMessage("INS_AP_FAP")?>:</font>
		</td>
		<td width="60%" class="tablebody3" valign="top">
			<font class="tablebodytext">
			<input class="typeinput" type="text" name="file_access_perms" size="10" value="<?php echo (strlen($_REQUEST["file_access_perms"])>0) ? htmlspecialchars($_REQUEST["file_access_perms"]) : "0777";?>"><br>
			<small><?php echo InstallGetMessage("INS_AP_FAP_DESCR")?><br></small>
			</font>
		</td>
	</tr>
	<tr>
		<td nowrap align="right" width="40%" class="tablebody1" valign="top">
			<font class="tablefieldtext"><?php echo InstallGetMessage("INS_AP_PAP")?>:</font>
		</td>
		<td width="60%" class="tablebody3" valign="top">
			<font class="tablebodytext">
			<input class="typeinput" type="text" name="folder_access_perms" size="10" value="<?php echo (strlen($_REQUEST["folder_access_perms"])>0) ? htmlspecialchars($_REQUEST["folder_access_perms"]) : "0777";?>"><br>
			<small><?php echo InstallGetMessage("INS_AP_PAP_DESCR")?><br></small>
			</font>
		</td>
	</tr>
	<tr>
		<td nowrap align="right" width="40%" class="tablebody1" valign="top">
			<font class="tablefieldtext"><?php echo InstallGetMessage("INS_AP_MEM_LIMIT")?>:</font>
		</td>
		<td width="60%" class="tablebody3" valign="top">
			<font class="tablebodytext">
			<input class="typeinput" type="text" name="memory_limit" size="10" value="<?php echo (strlen($_REQUEST["memory_limit"])>0) ? htmlspecialchars($_REQUEST["memory_limit"]) : "64";?>"> Mb<br>
			<small><?php echo InstallGetMessage("INS_AP_MEM_LIMIT_DESC")?><br></small>
			</font>
		</td>
	</tr>
</table>

<br>

<div align="right">
	<input type="hidden" name="Step" value="6">
	<input type="hidden" name="lang" value="<?php echo $lang; ?>">
	<input type="hidden" name="dbtype" value="<?php echo htmlspecialchars($dbType) ?>">
	<input type="hidden" name="UNIID" value="<?php echo md5(time());?>">
	<input class="button" type="submit" name="install" value="<?php echo InstallGetMessage("INS_INSTALL") ?>"><br>
</div>

<br><br><br>

</form>

<?php  
//*******************************************************/
//*******************************************************/
}
elseif ($Step==6)
{
//*******************************************************/
//*******************************************************/
?>

<table width="100%" cellpadding="2" cellspacing="0" bgcolor="#E6F1F9" border="0" style="border-left: 1px solid #D8E8F4; border-right: 1px solid #D8E8F4; border-top: 1px solid #D8E8F4; border-bottom: 1px solid #D8E8F4; "><tr><td width="0%"><img src="/bitrix/images/install/warning.gif" width="23" height="21" border="0" align="left"></td><td width="100%"><font class="text"><?php echo InstallGetMessage("NOTE_NOT_REFRESH") ?>
</font></td></tr></table>

<form action="index.php" method="POST" >
	<table border="0" cellspacing="1" cellpadding="2" width="100%">
		<tr>
			<td colspan="2" valign="center" align="center" nowrap class="tablehead5">
				<font class="tableheadtext"><b><?php echo InstallGetMessage("INS_ADMIN_SETTINGS")?></b></font>
			</td>
		</tr>
		<tr>
			<td nowrap align="right" class="tablebody1">
				<font class="tablefieldtext"><font color="#FF0000">*</font>&nbsp;<?php echo InstallGetMessage("INS_NAME")?></font>
			</td>
			<td class="tablebody3">
				<input class="typeinput" type="text" name="name" size="30" value="<?php echo (strlen($_REQUEST["name"])>0) ? htmlspecialchars($_REQUEST["name"]) : "";?>">
			</td>
		</tr>
		<tr>
			<td nowrap align="right" class="tablebody1">
				<font class="tablefieldtext"><font color="#FF0000">*</font>&nbsp;<?php echo InstallGetMessage("INS_LAST_NAME")?></font>
			</td>
			<td class="tablebody3">
				<input class="typeinput" type="text" name="last_name" size="30" value="<?php echo (strlen($_REQUEST["last_name"])>0) ? htmlspecialchars($_REQUEST["last_name"]) : ""?>">
			</td>
		</tr>
		<tr>
			<td nowrap align="right" class="tablebody1">
				<font class="tablefieldtext"><font color="#FF0000">*</font>&nbsp;<?php echo InstallGetMessage("INS_EMAIL")?></font>
			</td>
			<td class="tablebody3">
				<input class="typeinput" type="text" name="email" size="30" value="<?php echo (strlen($_REQUEST["email"])>0) ? htmlspecialchars($_REQUEST["email"]) : "webmaster@ourtestsite.com"?>">
			</td>
		</tr>
		<tr>
			<td nowrap align="right" class="tablebody1">
				<font class="tablefieldtext"><font color="#FF0000">*</font>&nbsp;<?php echo InstallGetMessage("INS_LOGIN")?></font>
			</td>
			<td class="tablebody3">
				<input class="typeinput" type="text" name="login" size="30" value="<?php echo (strlen($_REQUEST["login"])>0) ? htmlspecialchars($_REQUEST["login"]) : "admin"?>">
			</td>
		</tr>
		<tr>
			<td nowrap align="right" class="tablebody1">
				<font class="tablefieldtext"><font color="#FF0000">*</font>&nbsp;<?php echo InstallGetMessage("INS_ADMIN_PASSWORD")?></font>
			</td>
			<td class="tablebody3">
				<input class="typeinput" type="password" name="admin_password" size="30" value="<?php echo (strlen($_REQUEST["admin_password"])>0) ? htmlspecialchars($_REQUEST["admin_password"]) : ""?>">
			</td>
		</tr>
		<tr> 
			<td nowrap align="right" class="tablebody1">
				<font class="tablefieldtext"><font color="#FF0000">*</font>&nbsp;<?php echo InstallGetMessage("INS_PASSWORD_CONF")?></font>
			</td>
			<td class="tablebody3">
				<input class="typeinput" type="password" name="admin_password_confirm" size="30" value="<?php echo (strlen($_REQUEST["admin_password_confirm"])>0) ? htmlspecialchars($_REQUEST["admin_password_confirm"]) : ""?>">
			</td>
		</tr>
	</table>

	<br>
	<div align="right">
		<input type="hidden" name="Step" value="7">
		<input type="hidden" name="lang" value="<?php echo $lang; ?>">
		<input type="hidden" name="dbtype" value="<?php echo htmlspecialchars($dbType) ?>">
		<input type="hidden" name="UNIID" value="<?php echo md5(time());?>">
		<input class="button" id="sb" type="submit" name="install" value="<?php echo InstallGetMessage("INS_INSTALL") ?>"><br>
	</div>
</form>

<?php  
//*******************************************************/
//*******************************************************/
}
elseif ($Step==7)
{
//*******************************************************/
//*******************************************************/
?>
<p><font class="text"><b><?php echo InstallGetMessage("GRETTINGS") ?></b></font></p>

<table width="100%" cellpadding="2" cellspacing="0" bgcolor="#E6F1F9" border="0" style="border-left: 1px solid #D8E8F4; border-right: 1px solid #D8E8F4; border-top: 1px solid #D8E8F4; border-bottom: 1px solid #D8E8F4; "><tr><td width="0%"><img src="/bitrix/images/install/warning.gif" width="23" height="21" border="0" align="left"></td><td width="100%"><font class="text"><?php echo InstallGetMessage("NOTE_NOT_REFRESH") ?>
</font></td></tr></table>
<p></p>
<font class="text"><b><a href="/bitrix/admin/sysupdate.php?lang=<?php echo $lang; ?>" target="_blank"><?php echo InstallGetMessage("GO_TO_REGISTER") ?></a></b><br><?php echo InstallGetMessage("GO_TO_REGISTER_DESCR") ?></font><br><br>

<table width="100%" cellpadding="2" cellspacing="0" border="0">
<tr>
	<td><a href="/bitrix/admin/index.php?lang=<?php echo $lang; ?>" target="_blank"><img src="/bitrix/images/install/admin.gif" width="22" height="22" border="0" title="<?php echo InstallGetMessage("GO_TO_CONTROL") ?>"></a></td>
	<td width="50%"><font class="text"><b><a href="/bitrix/admin/index.php?lang=<?php echo $lang; ?>"><?php echo InstallGetMessage("GO_TO_CONTROL") ?></a></b></font></td>
	<td><a href="/"><img border="0" src="/bitrix/images/install/public.gif" width="22" height="22" title="<?php echo InstallGetMessage("GO_TO_VIEW") ?>"></a></td>
	<td width="50%"><font class="text"><b><a href="/" target="_blank"><?php echo InstallGetMessage("GO_TO_VIEW") ?></a></b></font></td>
</tr>
</table>
		
<?php  
//*******************************************************/
//*******************************************************/
}
//*******************************************************/
//*******************************************************/
?>
					<?php  
					if ($Step==1)
					{
						?></div></td>
						</tr>
						</table>
						<?php  
					}
					else
					{
						?><?php  
					}
					?>
				</td>
				</tr>
				<tr>
					<td><p align="center" class="smalltext" style="padding:7px;"><a href="<?php echo $arLang[$lang]["SITE"] ?>/?referer1=bsm50&referer2=install"><img src="/bitrix/images/install/bitrix_logo.gif" width="47" height="41" border="0" title='<?php echo InstallGetMessage("POWERED_BY")?>'></a><br><br></p></td>
					<td valign="bottom"><table width="100%" cellspacing="0" cellpadding="5" border="0" class="text" style="border-top:1px solid #B9D2E2">
					<tr>
						<td nowrap><?php echo InstallGetMessage("COPYRIGHT")?></td>
						<td width="100%">&nbsp;</td>
						<td nowrap><?php echo InstallGetMessage("SUPPORT")?></a></td>
					</tr>
					</table></td>
				</tr>
			</table></td>
	</tr>
</table>
</body>
</html>
