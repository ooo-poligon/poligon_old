<?
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2007 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
@set_time_limit(10000);
ini_set("track_errors", "1");
//ignore_user_abort(true);
$message = null;
error_reporting(E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR|E_PARSE);

////////////////////////////////////////////////////////////////////////
//////////   PARAMS   //////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////
$SYSTEM_min_avail_memory = 12;		// Min & recomended memory in Mb
$SYSTEM_rec_avail_memory = 32;

$SYSTEM_min_avail_disk = 20;		// Min disk size in Mb
$SYSTEM_min_avail_disk_tmp = 5;		// Min tmp disk size in Mb

$PHP_vercheck_min = "4.1.2";
$PHP_vercheck_max = "";

$Apache_vercheck_min = "1.3.0";
$Apache_vercheck_max = "";

$IIS_vercheck_min = "5.0.0";
$IIS_vercheck_max = "";

$MySql_vercheck_min = "4.0.18";
$MySql_vercheck_max = "";

$Oracle_vercheck_min = "9.0";
$Oracle_vercheck_max = "";

$MSSQL_vercheck_min = "8.0";
$MSSQL_vercheck_max = "";
////////////////////////////////////////////////////////////////////////
//////////   END PARAMS   //////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/prolog.php");
define("HELP_FILE", "utilities/site_checker.php");

if(!$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

@set_time_limit(10000);
ini_set("max_execution_time", "172800");

////////////////////////////////////////////////////////////////////////
//////////   FUNCTIONS   ///////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////
if (!function_exists("ob_get_clean"))
{
	function ob_get_clean()
	{
		$ob_contents = ob_get_contents();
		ob_end_clean();
		return $ob_contents;
	}
}

function GetPHPSetting($val)
{
	return ((ini_get($val) == "1") ? "ON" : "OFF");
}

function ShowResult($strRes, $strType = "OK")
{
	if (strlen($strRes)>0)
	{
		if (strtoupper($strType) == "ERROR" || strtoupper($strType) == "E")
			echo "<span style=\"color:red;\"><b>".$strRes."</b></span>";
		elseif (strtoupper($strType) == "NOTE" || strtoupper($strType) == "N")
			echo "<b>".$strRes."</b>";
		else
			echo "<span style=\"color:green;\"><b>".$strRes."</b></span>";
	}
}

function CheckVersionMinimax($strCurver, $strMinver, $strMaxver)
{
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

function CheckGetModuleInfo($path)
{
	include_once($path);

	$arr = explode("/", $path);
	$i = array_search("modules", $arr);
	$class_name = $arr[$i+1];

	return new $class_name;
}

function CheckFiles()
{
	if(!function_exists(RecursDiskAccessCheck))
	{
		function RecursDiskAccessCheck($start_dir, &$arError, $arFilter = array())
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
						RecursDiskAccessCheck($start_dir."/".$file, $arError, $arFilter);

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
	}
	?><table cellpadding="0" cellspacing="0" border="0" width="100%" class="<?echo ($send_tiket!="Y")?"internal":"list-table"?>">
		<tr class="<?echo ($send_tiket!="Y")?"heading":"head"?>">
			<td align="center"><b><?=GetMessage("SC_PARAM")?></b></td>
			<td align="center"><b><?=GetMessage("SC_VALUE")?></b></td>
		</tr>
		<tr>
			<td valign="top"><?=GetMessage("SC_DISK_BITRIX")?> <i><?=$_SERVER["DOCUMENT_ROOT"]."/bitrix"?></i></td>
			<td valign="top"><?
					$arError = array();
					$bResCheckAccess = RecursDiskAccessCheck($_SERVER["DOCUMENT_ROOT"]."/bitrix", $arError, array($_SERVER["DOCUMENT_ROOT"]."/upload"));
					if (count($arError) > 0)
					{
						for ($i = 0; $i < count($arError); $i++)
						{
							echo ($arError[$i][1]=="Y") ? GetMessage("SC_CATALOG")." " : GetMessage("SC_FILE")." ";
							echo substr($arError[$i][0], strlen($_SERVER["DOCUMENT_ROOT"]));
							if ($arError[$i][2]!="Y")
								ShowResult(" ".GetMessage("SC_CAN_NOT_WRITE"), "E");
							if ($arError[$i][2]!="Y" && $arError[$i][3]!="Y")
								echo " ".GetMessage("SC_AND");
							if ($arError[$i][3]!="Y")
								ShowResult(" ".GetMessage("SC_CAN_NOT_READ"), "E");
							echo "<br>";
						}

						if (!$bResCheckAccess)
						{
							echo "<br>";
							ShowResult(str_replace("#NUM#", count($arError), GetMessage("SC_DISK_MORE_20")), "E");
						}
					}
					else
						ShowResult(GetMessage("SC_DISK_AVAIL_READ_WRITE"), "O");
					?></td>
		</tr>
		<tr>
			<td valign="top"><?=GetMessage("SC_DISK_UPLOAD")?> <i><?=$_SERVER["DOCUMENT_ROOT"]."/upload"?></i></td>
			<td valign="top"><?
					$arError = array();
					$bResCheckAccess = RecursDiskAccessCheck($_SERVER["DOCUMENT_ROOT"]."/upload", $arError, array($_SERVER["DOCUMENT_ROOT"]."/upload"));
					if (count($arError) > 0)
					{
						for ($i = 0; $i < count($arError); $i++)
						{
							echo ($arError[$i][1]=="Y") ? GetMessage("SC_CATALOG")." " : GetMessage("SC_FILE")." ";
							echo substr($arError[$i][0], strlen($_SERVER["DOCUMENT_ROOT"]));
							if ($arError[$i][2]!="Y")
								ShowResult(" ".GetMessage("SC_CAN_NOT_WRITE"), "E");
							if ($arError[$i][2]!="Y" && $arError[$i][3]!="Y")
								echo " ".GetMessage("SC_AND");
							if ($arError[$i][3]!="Y")
								ShowResult(" ".GetMessage("SC_CAN_NOT_READ"), "E");
							echo "<br>";
						}

						if (!$bResCheckAccess)
						{
							echo "<br>";
							ShowResult(str_replace("#NUM#", count($arError), GetMessage("SC_DISK_MORE_20")), "E");
						}
					}
					else
						ShowResult(GetMessage("SC_DISK_AVAIL_READ_WRITE1"), "O");
					?></td>
		</tr>
		<tr>
			<td valign="top"><?=GetMessage("SC_DISK_PUBLIC")?> <i><?=$_SERVER["DOCUMENT_ROOT"]?></i></td>
			<td valign="top"><?
					$arError = array();
					$bResCheckAccess = RecursDiskAccessCheck($_SERVER["DOCUMENT_ROOT"]."/bitrix", $arError, array($_SERVER["DOCUMENT_ROOT"]."/upload"));
					if (count($arError) > 0)
					{
						for ($i = 0; $i < count($arError); $i++)
						{
							echo ($arError[$i][1]=="Y") ? GetMessage("SC_CATALOG")." " : GetMessage("SC_FILE")." ";
							echo substr($arError[$i][0], strlen($_SERVER["DOCUMENT_ROOT"]));
							if ($arError[$i][2]!="Y")
								ShowResult(" ".GetMessage("SC_CAN_NOT_WRITE"), "E");
							if ($arError[$i][2]!="Y" && $arError[$i][3]!="Y")
								echo " ".GetMessage("SC_AND");
							if ($arError[$i][3]!="Y")
								ShowResult(" ".GetMessage("SC_CAN_NOT_READ"), "E");
							echo "<br>";
						}

						if (!$bResCheckAccess)
						{
							echo "<br>";
							ShowResult(str_replace("#NUM#", count($arError), GetMessage("SC_DISK_MORE_20")), "E");
						}
					}
					else
						ShowResult(GetMessage("SC_DISK_AVAIL_READ_WRITE2"), "O");
					?></td>
		</tr>
		</table>
<?
}

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("SC_TAB_1"), "ICON" => "site_check", "TITLE" => GetMessage("SC_SUBTITLE_REQUIED")),
	array("DIV" => "edit2", "TAB" => GetMessage("SC_TAB_2"), "ICON" => "site_check", "TITLE" => GetMessage("SC_SUBTITLE_DISK")),
	array("DIV" => "edit3", "TAB" => GetMessage("SC_TAB_3"), "ICON" => "site_check", "TITLE" => GetMessage("SC_SUBTITLE_RECOMMEND")),
	array("DIV" => "edit4", "TAB" => GetMessage("SC_TAB_4"), "ICON" => "site_check", "TITLE" => GetMessage("SC_SUBTITLE_SITE_MODULES")),
	array("DIV" => "edit5", "TAB" => GetMessage("SC_TAB_5"), "ICON" => "site_check", "TITLE" => GetMessage("SC_TIK_TITLE")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

////////////////////////////////////////////////////////////////////////
//////////   END FUNCTIONS   ///////////////////////////////////////////
////////////////////////////////////////////////////////////////////////

$APPLICATION->SetTitle(GetMessage("SC_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
 ?>
<?

if ($send_tiket=="Y")
	ob_start(); 

echo BeginNote();
	echo GetMessage("SC_NOTES1");
echo EndNote();
?>

<?
if($send_tiket!="Y")
{
	$tabControl->Begin();
	$tabControl->BeginNextTab();
}
else
	echo "<table cellspacing='0' cellpadding='5' border='0' width='100%'>";
?>
	<tr>
		<td colspan="2"><?echo ($send_tiket=="Y")?"<p>":"";?><?=GetMessage("SC_SUBTITLE_REQUIED_DESC")?><?echo ($send_tiket=="Y")?"</p>":"";?></td>
	</tr>
	<tr>
	<td colspan="2">
	<table cellpadding="0" cellspacing="0" border="0" width="100%" class="<?echo ($send_tiket!="Y")?"internal":"list-table"?>">
	<tr class="<?echo ($send_tiket!="Y")?"heading":"head"?>">
		<td align="center"><b><?=GetMessage("SC_PARAM")?></b></td>
		<td align="center"><b><?=GetMessage("SC_REQUIED")?></b></td>
		<td align="center"><b><?=GetMessage("SC_CURRENT")?></b></td>
	</tr>
	<?
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
		<td valign="top"><?=str_replace("#SERVER#", ((strlen($strWebServer)>0) ? $strWebServer : GetMessage("SC_UNKNOWN")), GetMessage("SC_SERVER_VERS"))?></td>
		<td valign="top"><?
				if (strlen($vercheck_min)>0)
					echo str_replace("#VER#", $vercheck_min, GetMessage("SC_VER_VILKA1"));
				if (strlen($vercheck_min)>0 && strlen($vercheck_max)>0)
					echo GetMessage("SC_VER_VILKA2");
				if (strlen($vercheck_max)>0)
					echo str_replace("#VER#", $vercheck_max, GetMessage("SC_VER_VILKA3"));
				?></td>
		<td valign="top"><?
				if (strlen($strWebServerVersion)>0)
					ShowResult($strWebServerVersion, ((CheckVersionMinimax($strWebServerVersion, $vercheck_min, $vercheck_max)) ? "O" : "E"));
				else
					ShowResult(GetMessage("SC_UNKNOWN1"), "E");
				?></td>
	</tr>

	<?
	if (strtolower($DB->type)=="oracle")
	{
		$vercheck_min = $Oracle_vercheck_min;
		$vercheck_max = $Oracle_vercheck_max;
	}
	elseif (strtolower($DB->type)=="mssql")
	{
		$vercheck_min = $MSSQL_vercheck_min;
		$vercheck_max = $MSSQL_vercheck_max;
	}
	else
	{
		$vercheck_min = $MySql_vercheck_min;
		$vercheck_max = $MySql_vercheck_max;
	}
	?>
	<tr>
		<td valign="top"><?=str_replace("#DB#", $DB->type, GetMessage("SC_DB_VERS"))?></td>
		<td valign="top"><?
				if (strlen($vercheck_min)>0)
					echo str_replace("#VER#", $vercheck_min, GetMessage("SC_VER_VILKA1"));
				if (strlen($vercheck_min)>0 && strlen($vercheck_max)>0)
					echo GetMessage("SC_VER_VILKA2");
				if (strlen($vercheck_max)>0)
					echo str_replace("#VER#", $vercheck_max, GetMessage("SC_VER_VILKA3"));
				?>&nbsp;</td>
		<td valign="top"><?
				if ($version = $DB->GetVersion())
				{
					if (preg_match("#^([\d]+\.[\d]+(\.[\d]+)?)#i", $version, $arVers))
						ShowResult($arVers[1], ((CheckVersionMinimax($arVers[1], $vercheck_min, $vercheck_max)) ? "O" : "E"));
					else
						ShowResult(GetMessage("SC_UNKNOWN1"), "E");
				}
				else
					ShowResult(GetMessage("SC_ERR_QUERY_VERS"), "E");
				?></td>
	</tr>
	<tr>
		<td valign="top"><?=GetMessage("SC_PHP_VERS")?></td>
		<td valign="top"><?
				if (strlen($PHP_vercheck_min)>0)
					echo str_replace("#VER#", $PHP_vercheck_min, GetMessage("SC_VER_VILKA1"));
				if (strlen($PHP_vercheck_min)>0 && strlen($PHP_vercheck_max)>0)
					echo GetMessage("SC_VER_VILKA2");
				if (strlen($PHP_vercheck_max)>0)
					echo str_replace("#VER#", $PHP_vercheck_max, GetMessage("SC_VER_VILKA3"));
				?>&nbsp;</td>
		<td valign="top"><?ShowResult(phpversion(), (!CheckVersionMinimax(phpversion(), $PHP_vercheck_min, $PHP_vercheck_max) ? "E" : "O"))?></td>
	</tr>
	<tr>
		<td colspan="3"><b><?=GetMessage("SC_PHP_SETTINGS")?></b></td>
	</tr>
	<tr>
		<td valign="top">&nbsp; - safe mode</td>
		<td valign="top"><?=GetMessage("SC_TURN_OFF")?></td>
		<td valign="top"><?=((GetPHPSetting("safe_mode")=="ON") ? ShowResult(GetMessage("SC_TURN_ON"), "E") : ShowResult(GetMessage("SC_TURN_OFF"), "O"))?></td>
	</tr>
	<tr>
		<td valign="top">&nbsp; - <?=GetMessage("SC_SESS_PATH")?></td>
		<td valign="top"><?=GetMessage("SC_SETTED")?></td>
		<td valign="top"><?
				$sp = session_save_path();
//				$sp = ini_get('session.save_path');
				if (strlen($sp)>0)
				{
					ShowResult($sp, "O");
					echo " ";
					if (file_exists($sp))
					{
						if (is_writable($sp))
							ShowResult(GetMessage("SC_CAN_WRITE"), "O");
						else
							ShowResult(GetMessage("SC_CAN_NOT_WRITE"), "E");
					}
					else
						ShowResult(GetMessage("SC_NOT_EXISTS"), "E");
				}
				else
					ShowResult(GetMessage("SC_NOT_SETTED"), "E");
				?></td>
	</tr>
	<tr>
		<td colspan="3"><b><?=GetMessage("SC_REQUIED_PHP_MODS")?></b></td>
	</tr>
	<tr>
		<td valign="top">&nbsp; - <a title="<?=GetMessage("SC_EXTINSION_VIEW")?>" href="http://www.php.net/manual/en/ref.xml.php" target="_blank"><?=GetMessage("SC_MOD_XML")?></a></td>
		<td valign="top"><?=GetMessage("SC_SETTED")?></td>
		<td valign="top"><?=((extension_loaded('xml') && function_exists("xml_parser_create")) ? ShowResult(GetMessage("SC_SETTED"), "O") : ShowResult(GetMessage("SC_NOT_SETTED"), "E"))?></td>
	</tr>
	<?if (strtolower($DB->type)=="oracle"):?>
		<tr>
			<td valign="top">&nbsp; - <a title="<?=GetMessage("SC_EXTINSION_VIEW")?>" href="http://www.php.net/manual/en/ref.oci8.php" target="_blank"><?=GetMessage("SC_MOD_ORACLE")?></a></td>
			<td valign="top"><?=GetMessage("SC_SETTED")?></td>
			<td valign="top"><?=(function_exists("OCILogon") ? ShowResult(GetMessage("SC_SETTED"), "O") : ShowResult(GetMessage("SC_NOT_SETTED"), "E"))?></td>
		</tr>
	<?elseif (strtolower($DB->type)=="mysql"):?>
		<tr>
			<td valign="top">&nbsp; - <a title="<?=GetMessage("SC_EXTINSION_VIEW")?>" href="http://www.php.net/manual/en/ref.mysql.php" target="_blank"><?=GetMessage("SC_MOD_MYSQL")?></a></td>
			<td valign="top"><?=GetMessage("SC_SETTED")?></td>
			<td valign="top"><?=(function_exists("mysql_connect") ? ShowResult(GetMessage("SC_SETTED"), "O") : ShowResult(GetMessage("SC_NOT_SETTED"), "E"))?></td>
		</tr>
	<?endif;?>
	<tr>
		<td valign="top">&nbsp; - <a title="<?=GetMessage("SC_EXTINSION_VIEW")?>" href="http://www.php.net/manual/en/ref.regex.php" target="_blank"><?=GetMessage("SC_MOD_POSIX_REG")?></a></td>
		<td valign="top"><?=GetMessage("SC_SETTED")?></td>
		<td valign="top"><?=(function_exists("eregi") ? ShowResult(GetMessage("SC_SETTED"), "O") : ShowResult(GetMessage("SC_NOT_SETTED"), "E"))?></td>
	</tr>
	<tr>
		<td valign="top">&nbsp; - <a title="<?=GetMessage("SC_EXTINSION_VIEW")?>" href="http://www.php.net/manual/en/ref.pcre.php" target="_blank"><?=GetMessage("SC_MOD_PERL_REG")?></a></td>
		<td valign="top"><?=GetMessage("SC_SETTED")?></td>
		<td valign="top"><?=(function_exists("preg_match") ? ShowResult(GetMessage("SC_SETTED"), "O") : ShowResult(GetMessage("SC_NOT_SETTED"), "E"))?></td>
	</tr>
	<tr>
		<td valign="top">&nbsp; - <a title="<?=GetMessage("SC_EXTINSION_VIEW")?>" href="http://ru.php.net/manual/en/function.imagettftext.php" target="_blank">FreeType library</a></td>
		<td valign="top"><?=GetMessage("SC_SETTED")?></td>
		<td valign="top"><?=(function_exists("imagettftext") ? ShowResult(GetMessage("SC_SETTED"), "O") : ShowResult(GetMessage("SC_NOT_SETTED"), "E"))?></td>
	</tr>
	</table>
	</td>
	</tr>
<?flush();
if($send_tiket!="Y")
{

	$tabControl->BeginNextTab();?>
		<tr>
			<td colspan="2"><?echo GetMessage("SC_SUBTITLE_DISK_DESC");?></td>
		</tr>
		<tr>
			<td colspan="2"><?if($check_files=="Y") 
								CheckFiles(); 
							else{
								echo CAdminMessage::ShowMessage(Array("MESSAGE"=>GetMessage("SC_CHECK_FILES_ATTENTION"), "TYPE"=>"ERROR","DETAILS"=>GetMessage("SC_CHECK_FILES_WARNING")));
								?><a href="<?echo $APPLICATION->GetCurPageParam("check_files=Y&tabControl_active_tab=edit2", array("check_files"));?>" title="<?=GetMessage("SC_CHECK_FILES_TITLE")?>"><?=GetMessage("SC_CHECK_FILES")?></a>
							<?}?></td>
		</tr>
	<?
$tabControl->BeginNextTab();
}
?>
	<tr>
		<td colspan="2"><?echo ($send_tiket=="Y")?"<p>":"";?><?=GetMessage("SC_SUBTITLE_RECOMMEND_DESC")?><?echo ($send_tiket=="Y")?"</p>":"";?></td>
	</tr>
	<tr>
	<td colspan="2">
	<table cellpadding="0" cellspacing="0" border="0" width="100%" class="<?echo ($send_tiket!="Y")?"internal":"list-table"?>">
	<tr class="<?echo ($send_tiket!="Y")?"heading":"head"?>">
		<td align="center"><b><?=GetMessage("SC_PARAM")?></b></td>
		<td align="center"><b><?=GetMessage("SC_RECOMMEND")?></b></td>
		<td align="center"><b><?=GetMessage("SC_CURRENT")?></b></td>
	</tr>
	<?
	$Update_server_url = COption::GetOptionString("main", "update_site", "www.bitrixsoft.com");
	$Update_server_port = 80;
	?>
	<tr>
		<td valign="top"><?=GetMessage("SC_UPDATE_ACCESS")?> <i><?=$Update_server_url?></i></td>
		<td valign="top"><?=GetMessage("SC_HAVE")?></td>
		<td valign="top"><?
				$FP = @fsockopen($Update_server_url, IntVal($Update_server_port), $errno, $errstr, 120);
				if ($FP)
				{
					$strRequest = "POST /bitrix/updates/bit_sysserver.php HTTP/1.0\r\n";
					$strRequest.= "User-Agent: BitrixSMUpdater\r\n";
					$strRequest.= "Accept: */*\r\n";
					$strRequest.= "Host: $Update_server_url\r\n";
					$strRequest.= "Accept-Language: en\r\n";
					$strRequest.= "Content-type: application/x-www-form-urlencoded\r\n";
					$strRequest.= "Content-length: 0\r\n\r\n";
					$strRequest.= "\r\n";

					fputs($FP, $strRequest);

					while (($line = fgets($FP, 4096)) && $line!="\r\n");

					$content = "";
					while ($line = fread($FP, 4096))
						$content .= $line;
					fclose($FP);

					if (strlen($content)<=0)
						ShowResult(GetMessage("SC_UPDATE_EMPTY"), "E");
					else
						ShowResult(GetMessage("SC_UPDATE_SUCCESS"), "O");
				}
				else
					ShowResult(GetMessage("SC_UPDATE_ERROR").": [".$errno."] ".$errstr, "E");
				?></td>
	</tr>
	<tr>
		<td valign="top"><?=GetMessage("SC_AVAIL_DISK_SPACE")?></td>
		<td valign="top"><?
				if (IntVal($SYSTEM_min_avail_disk)>0)
					echo str_replace("#SIZE#", $SYSTEM_min_avail_disk, GetMessage("SC_AVAIL_DISK_SPACE_SIZE"));
				?>&nbsp;</td>
		<td valign="top"><?
				$real_avail_disk = @disk_free_space($_SERVER["DOCUMENT_ROOT"]);
				$real_avail_disk = $real_avail_disk * 1.0 / 1000000.0;
				ShowResult(Round($real_avail_disk, 1)." Mb", (($real_avail_disk>$SYSTEM_min_avail_disk) ? "O" : "E" ));
				?></td>
	</tr>
	<?
	$tmp_folder = ini_get("upload_tmp_dir");
	if (strlen($tmp_folder)>0 && file_exists($tmp_folder))
	{
	?>
		<tr>
			<td valign="top"><?=GetMessage("SC_AVAIL_DISK_SPACE_TMP")?> <i><?=$tmp_folder?></i></td>
			<td valign="top"><?
					if (IntVal($SYSTEM_min_avail_disk_tmp)>0)
						echo str_replace("#SIZE#", $SYSTEM_min_avail_disk_tmp, GetMessage("SC_AVAIL_DISK_SPACE_SIZE"));
					?>&nbsp;</td>
			<td valign="top"><?
					$real_avail_disk = @disk_free_space($tmp_folder);
					$real_avail_disk = $real_avail_disk * 1.0 / 1000000.0;
					ShowResult(Round($real_avail_disk, 1)." Mb", (($real_avail_disk>$SYSTEM_min_avail_disk_tmp) ? "O" : "E" ));
					?></td>
		</tr>
	<?
	}
	?>
	<tr>
		<td colspan="3"><b><?=GetMessage("SC_RECOM_PHP_SETTINGS")?></b></td>
	</tr>
	<tr>
		<td valign="top">&nbsp; - <?=GetMessage("SC_AVAIL_MEMORY")?></td>
		<td valign="top"><?
				if (IntVal($SYSTEM_min_avail_memory)>0)
					echo str_replace("#SIZE#", $SYSTEM_min_avail_memory, GetMessage("SC_AVAIL_MEMORY_MIN"));
				if (IntVal($SYSTEM_min_avail_memory)>0 && IntVal($SYSTEM_rec_avail_memory)>0)
					echo ", ";
				if (IntVal($SYSTEM_rec_avail_memory)>0)
					echo str_replace("#SIZE#", $SYSTEM_rec_avail_memory, GetMessage("SC_AVAIL_MEMORY_REC"));
				?></td>
		<td valign="top"><?
				$memory_limit = ini_get('memory_limit');
				if (!$memory_limit || strlen($memory_limit)<=0)
					$memory_limit = get_cfg_var('memory_limit');

				$memory_limit = IntVal(Trim($memory_limit));
				echo (($memory_limit < $SYSTEM_min_avail_memory) ? ShowResult($memory_limit." Mb", "E") : ShowResult($memory_limit." Mb", "O") );
				?></td>
	</tr>
	<tr>
		<td valign="top">&nbsp; - <?=GetMessage("SC_ALLOW_UPLOAD")?> (file_uploads)</td>
		<td valign="top"><?=GetMessage("SC_TURN_ON1")?></td>
		<td valign="top"><?
				if (GetPHPSetting("file_uploads")=="ON")
				{
					ShowResult(GetMessage("SC_TURN_ON1"), "O");
					echo ", ";
					$sp = ini_get("upload_tmp_dir");
					if (strlen($sp)>0)
					{
						echo GetMessage("SC_TEMP_FOLDER")." <b>".$sp."</b> ";
						if (file_exists($sp))
						{
							if (is_writable($sp))
								ShowResult(GetMessage("SC_CAN_WRITE1"), "O");
							else
								ShowResult(GetMessage("SC_CAN_NOT_WRITE1"), "E");
						}
						else
							ShowResult(GetMessage("SC_NOT_EXISTS"), "E");
					}
					else
						ShowResult(GetMessage("SC_NO_TEMP_FOLDER"), "E");
				}
				else
					ShowResult(GetMessage("SC_TURN_OFF1"), "E");
				?></td>
	</tr>
	<tr>
		<td valign="top">&nbsp; - <?=GetMessage("SC_SHOW_ERRORS")?> (display_errors)</td>
		<td valign="top"><?=GetMessage("SC_TURN_ON1")?></td>
		<td valign="top"><?= ((GetPHPSetting("display_errors")=="ON") ? ShowResult(GetMessage("SC_TURN_ON1"), "O") : ShowResult(GetMessage("SC_TURN_OFF1"), "E"))?></td>
	</tr>
	<tr>
		<td colspan="3"><b><?=GetMessage("SC_RECOM_PHP_MODULES")?></b></td>
	</tr>
	<tr>
		<td valign="top">&nbsp; - <a title="<?=GetMessage("SC_EXTINSION_VIEW")?>" href="http://www.php.net/manual/en/ref.zlib.php" target="_blank">Zlib Compression</a></td>
		<td valign="top"><?=GetMessage("SC_SETTED")?></td>
		<td valign="top"><?= ((extension_loaded('zlib') && function_exists("gzcompress")) ? ShowResult(GetMessage("SC_SETTED"), "O") : ShowResult(GetMessage("SC_NOT_SETTED"), "E") )?></td>
	</tr>
	<tr>
		<td valign="top">&nbsp; - <a title="<?=GetMessage("SC_EXTINSION_VIEW")?>" href="http://www.php.net/manual/en/ref.image.php" target="_blank"><?=GetMessage("SC_MOD_GD")?></a></td>
		<td valign="top"><?=GetMessage("SC_SETTED")?></td>
		<td valign="top"><?= (function_exists("imagecreate") ? ShowResult(GetMessage("SC_SETTED"), "O") : ShowResult(GetMessage("SC_NOT_SETTED"), "E"))?></td>
	</tr>
	</table></td>
	</tr>
<?
flush();
if($send_tiket!="Y")
	$tabControl->BeginNextTab();
?>
	<tr>
		<td colspan="2"><?echo ($send_tiket=="Y")?"<p>":"";?><?=GetMessage("SC_SUBTITLE_SITE_MODULES_DESC")?><?echo ($send_tiket=="Y")?"</p>":"";?></td>
	</tr>
	<tr>
	<td colspan="2">
	<table cellpadding="0" cellspacing="0" border="0" width="100%" class="<?echo ($send_tiket!="Y")?"internal":"list-table"?>">
	<tr class="<?echo ($send_tiket!="Y")?"heading":"head"?>">
		<td><b><?=GetMessage("SC_SITE_MODULE")?></b></td>
		<td><b><?=GetMessage("SC_SETTED")?></b></td>
		<td><b><?=GetMessage("SC_SITE_MOD_VERS")?></b></td>
	</tr>
	<?
	if ($handle = @opendir($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules"))
	{
		while (false !== ($dir = readdir($handle)))
		{
			if (is_dir($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$dir)
				&& $dir!="." && $dir!="..")
			{
				$strModuleID = $dir;
				$strModuleName = "";
				$strModuleVers = "";
				$strModuleInst = "";
				$strModuleErrr = "";

				if ($dir=="main")
				{
					$strModuleName = "Main";
					$strModuleVers = SM_VERSION;
					$strModuleInst = "Y";
				}
				else
				{
					if (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$strModuleID."/install/index.php"))
					{
						$info = CheckGetModuleInfo($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$strModuleID."/install/index.php");
						$strModuleName = $info->MODULE_NAME;

						if ($info->MODULE_ID != $strModuleID)
							$strModuleErrr .= GetMessage("SC_ERROR_MOD_DESCR").". ";
						elseif (!isset($info->MODULE_VERSION) || strlen($info->MODULE_VERSION)<=0)
							$strModuleErrr .= GetMessage("SC_ERROR_MOD_VERS").". ";
						else
							$strModuleVers = $info->MODULE_VERSION;

						if ($info->IsInstalled())
							$strModuleInst = "Y";
						else
							$strModuleInst = "N";
					}
					else
						$strModuleErrr .= GetMessage("SC_ERROR_MOD_NOT").". ";
				}
				?>
				<tr>
					<td valign="top"><?= $strModuleName ?> (<?=$strModuleID?>)</td>
					<td valign="top"><?
							if ($strModuleInst=="Y")
								echo GetMessage("SC_INSTALLED");
							elseif ($strModuleInst=="N")
								echo GetMessage("SC_NOT_INSTALLED");
							else
								echo "&nbsp;";
							?></td>
					<td valign="top"><?
							if (strlen($strModuleErrr)>0)
								ShowResult($strModuleErrr, "E");
							if (strlen($strModuleVers)>0)
								ShowResult($strModuleVers, "N");
							?></td>
				</tr>
				<?
			}
		}
		closedir($handle);
	}
	?>
			</table></td>
	</tr>
<?flush();?>
<?
if ($send_tiket=="Y")
{	
	echo "</table>";
	$strMailCheckerPage =
		"<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1251\">".
		"<link href=\"http://mysql.smn/bitrix/themes/.default/adminstyles.css\" rel=\"stylesheet\" type=\"text/css\">".
		"<title>".GetMessage("SC_TITLE")."</title>".
		"</head><body>".
		ob_get_contents().
		"</body></html>";

	ob_end_flush();
}
if($send_tiket!="Y")
	$tabControl->BeginNextTab();

if(!isset($strTiketError))
	$strTiketError = "";
if ($send_tiket=="Y")
{
	if (strlen($tiket_email)<=0)
	{
		$strTiketError .= GetMessage("SC_TIK_NO_EMAIL").". ";
		$aMsg[] = array("id"=>"tiket_email", "text"=>GetMessage("SC_TIK_NO_EMAIL"));
	}
	elseif (!check_email($tiket_email))
	{
		$strTiketError .= GetMessage("SC_TIK_EMAIL_ERR").". ";
		$aMsg[] = array("id"=>"tiket_email", "text"=>GetMessage("SC_TIK_EMAIL_ERR"));
	}

	if (strlen($tiket_text)<=0)
	{
		$strTiketError .= GetMessage("SC_TIK_NO_DESCR").". ";
		$aMsg[] = array("id"=>"tiket_text", "text"=>GetMessage("SC_TIK_NO_DESCR"));
	}

	if (strlen($strTiketError)<=0)
	{
		// E-Mail address
		if (defined("THIS_SITE_SUPPORT_EMAIL") && strlen(THIS_SITE_SUPPORT_EMAIL)>0)
			$strMailEMailTo = THIS_SITE_SUPPORT_EMAIL;
		else
		{
			if ($lang=="ru")
				$strMailEMailTo = "support@bitrixsoft.ru";
			else
				$strMailEMailTo = "support@bitrixsoft.com";
		}

		if (defined("THIS_SITE_SUPPORT_CHARSET") && strlen(THIS_SITE_SUPPORT_CHARSET)>0)
			$sCharset = THIS_SITE_SUPPORT_CHARSET;
		else
		{
			if ($lang=="ru")
				$sCharset = "windows-1251";
			else
				$sCharset = "iso-8859-1";
		}

		/*
		if ($lang=="ru")
		{
			$strMailEMailTo = "support@bitrixsoft.ru";
			$sCharset = "windows-1251";
		}
		else
		{
			$strMailEMailTo = "support@bitrixsoft.com";
			$sCharset = "iso-8859-1";
		}
		*/

		// Subject
		$strMailSubject = "";
		if (strlen($tiket_number)>0)
		{
			$tiket_number = preg_replace("/[\D]+/i", "", $tiket_number);
			if ($lang=="ru")
				$strMailSubject .= "[TID#".$tiket_number."] www.bitrixsoft.ru: ".GetMessage("SC_RUS_L");
			else
				$strMailSubject .= "[TID#".$tiket_number."] www.bitrixsoft.com: Changes in request";
		}
		else
		{
			if ($lang=="ru")
				$strMailSubject = GetMessage("SC_RUS_L1")." ".$_SERVER["SERVER_NAME"];
			else
				$strMailSubject = "Request from ".$_SERVER["SERVER_NAME"];
		}

		// Body
		$sMimeBoundary = '==Multipart_Boundary_X'.md5(time()).'X';
		//$sAltBoundary = '==Alternative_Boundary_X'.md5(time()).'X';

		$strMailHeader =
			"From: $tiket_email\n".
			"Reply-To: $tiket_email\n".
			"X-Priority: 3 (Normal)\n".
			"Content-Transfer-Encoding: 8bit\n".
			"Content-Type: multipart/mixed;\n boundary=\"".$sMimeBoundary."\"\n";
			"MIME-Version: 1.0";

		//$strMailText = "This is a multi-part message in MIME format.\n";
		$strMailText = "";

		// Body text
		$strMailText .= "--".$sMimeBoundary."\n";
		//$strMailText .= "Content-Type: multipart/alternative;\n boundary=\"".$sAltBoundary."\"\n\n";
		//$strMailText .= "--".$sAltBoundary."\n";
		$strMailText .= "Content-Type: text/plain; charset=".$sCharset."\nContent-Transfer-Encoding: 8bit\n\n";

		$strMailText .= rtrim($tiket_text);
		if (strlen($_REQUEST["last_error_query"])>0)
			$strMailText .= "\n\nLast query error:\n".$_REQUEST["last_error_query"];

		$strMailText .= "\n\nLicense key: ".(LICENSE_KEY == "DEMO"? "DEMO" : md5("BITRIX".LICENSE_KEY."LICENCE"));
		$strMailText .= "\n\nVersion: ".(defined("DEMO") ? "DEMO" : (defined("ENCODE") ? "ENCODE" : "FULL"));

		$strMailText .= "\n\n\$_SERVER array content:\n<code>".print_r($_SERVER, True);
		$strMailText .= "</code>\n\n\$_ENV array content:\n<code>".print_r($_ENV, True);
		$strMailText .= "</code>\n\nCurrent time: ".date("Y-m-d H:i:s");

		$strMailText .= "\n";

		//$strMailText .= "--".$sAltBoundary."--\n\n";

		// Body attachment 1
		if ($tiket_phpinfo=="Y")
		{
			ob_start();
			phpinfo();
			$PHPinfo = ob_get_clean();

			$PHPinfo = chunk_split(base64_encode($PHPinfo));
			$strMailText .= "--".$sMimeBoundary."\n";
			$strMailText .= "Content-Type: text/html;\n name=\"phpinfo.html\"\n";
			$strMailText .= "Content-Transfer-Encoding: base64\n";
			$strMailText .= "Content-Disposition: attachment;\n filename=\"phpinfo.html\"\n\n";
			$strMailText .= $PHPinfo;
		}

		// Body attachment 2
		$strMailCheckerPage = chunk_split(base64_encode($strMailCheckerPage));
		$strMailText .= "--".$sMimeBoundary."\n";
		$strMailText .= "Content-Type: text/html;\n name=\"data.html\"\n";
		$strMailText .= "Content-Transfer-Encoding: base64\n";
		$strMailText .= "Content-Disposition: attachment;\n filename=\"data.html\"\n\n";
		$strMailText .= $strMailCheckerPage;

		$strMailText .= "--".$sMimeBoundary."--\n";

		// Mail
		$php_errormsg = "";
		if (@mail($strMailEMailTo, $strMailSubject, $strMailText, $strMailHeader))
		{
			LocalRedirect("/bitrix/admin/site_checker.php?lang=".LANGUAGE_ID."&ticket_sent=Y&tabControl_active_tab=edit5&tiket_email=".$tiket_email);
		}
		else
		{
			$strTiketError .= GetMessage("SC_TIK_SEND_ERROR");
			if (strlen($php_errormsg)>0)
				$strTiketError .= ": ".$php_errormsg;
			$strTiketError .= ". ";
		}
	}
	LocalRedirect("/bitrix/admin/site_checker.php?lang=".LANGUAGE_ID."&tabControl_active_tab=edit5&strTiketError=".urlencode($strTiketError)."&ticket_sent=N&tiket_text=".urlencode($tiket_text)."&tiket_email=".urlencode($tiket_email));
}
?>
<tr><td colspan="2"><?
	if(isset($ticket_sent))
	{
		if(!empty($aMsg))
		{
			$e = new CAdminException($aMsg);
			$APPLICATION->ThrowException($e);
			if($e = $APPLICATION->GetException())
			{
				$message = new CAdminMessage(GetMessage("SC_ERROR"), $e);
				if($message)
					echo $message->Show();
			}
		}

		if(strlen($strTiketError)>0 && !$message)
			CAdminMessage::ShowMessage($strTiketError);
		elseif(!$message)
			CAdminMessage::ShowNote(str_replace("#EMAIL#", $tiket_email, GetMessage("SC_TIK_SEND_SUCCESS")));
	}
		?></td>
</tr>
<form method="POST" action="site_checker.php" name="fticket">
<input type="hidden" name="tabControl_active_tab" value="edit5">
<tr>
	<td valign="top"><?=GetMessage("SC_TIK_NUMBER")?> <sup>1</sup></td>
	<td valign="top"><input type="text" name="tiket_number" value="<?= htmlspecialchars($tiket_number)?>" size="10"></td>
</tr>
<tr>
	<td valign="top"><span class="required">*</span><?=GetMessage("SC_TIK_EMAIL")?><sup>2</sup></td>
	<td valign="top"><?
			if (strlen($tiket_email)<=0)
				$tiket_email = COption::GetOptionString("main", "email_from", "admin@".$SERVER_NAME);?>
			<input type="text" name="tiket_email" value="<?= htmlspecialchars($tiket_email)?>" size="30"></td>
</tr>
<tr>
	<td valign="top"><span class="required">*</span><?=GetMessage("SC_TIK_DESCR")?><br>
			<small><?=GetMessage("SC_TIK_DESCR_DESCR")?></small></td>
	<td valign="top"><textarea name="tiket_text" rows="3" cols="40"><?= htmlspecialchars($tiket_text)?></textarea></td>
</tr>
<tr>
	<td valign="top"><label for="tiket_phpinfo"><?=GetMessage("SC_TIK_ADD_PHPINFO")?></label></td>
	<td valign="top"><input type="checkbox" id="tiket_phpinfo" name="tiket_phpinfo" value="Y" checked></td>
</tr>
<?if (strlen($_REQUEST["last_error_query"])>0):?>
	<tr>
		<td valign="top"><?=GetMessage("SC_TIK_LAST_ERROR")?></td>
		<td valign="top"><?=GetMessage("SC_TIK_LAST_ERROR_ADD")?>
			<input type="hidden" name="last_error_query" value="<?= htmlspecialchars($_REQUEST["last_error_query"])?>"></td>
	</tr>
<?endif;?>
<tr>
	<td colspan="2" align="center"><input type="hidden" name="send_tiket" value="Y">
		<input type="submit" value="<?=GetMessage("SC_TIK_SEND_MESS")?>"></td>
</tr>
</form>
<tr>
	<td colspan="2">
		<?echo BeginNote();?>
			<sup>1</sup> <?=GetMessage("SC_TIK_HELP1")?><br>
			<sup>2</sup> <?=GetMessage("SC_TIK_HELP2")?>
		 <?echo EndNote();?>
	</td>
</tr>
<?
//$tabControl->Buttons();
$tabControl -> End();
$tabControl->ShowWarnings("fticket", $message);
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
