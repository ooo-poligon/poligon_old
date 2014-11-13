<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/bx_root.php");

function repair_db()
{
	@include($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/dbconn.php");
	if($DBType == "mysql")
	{
		@set_time_limit(0);
		function microtime_float()
		{
			   list($usec, $sec) = explode(" ", microtime());
			   return ((float)$usec + (float)$sec);
		}

		$start = microtime_float();
		 if(DBPersistent)
			$link_rdb = mysql_pconnect($DBHost, $DBLogin, $DBPassword);
		else
			$link_rdb = mysql_connect($DBHost, $DBLogin, $DBPassword);
			
		if (!$link_rdb)
		   die('<span style="color:red;">'.GetMessage("RDB_CONNECT_ERROR").': '. mysql_error().'</span>');
		
		$db_selected = mysql_select_db($DBName, $link_rdb);
		
		$result = mysql_query('show table status');
		?>
		<table cellspacing="0" cellpadding="0" border="0" class="list-table" width="0%">
			<tr class="head">
				<td><?=GetMessage("RDB_TABLE_NAME")?></td>
				<td><?=GetMessage("RDB_ROWS_COUNT")?></td>
				<td><?=GetMessage("RDB_TABLE_SIZE")?></td>
				<td><?=GetMessage("RDB_CHECK_RESULT")?></td>
				<td><?=GetMessage("RDB_REPAIR_RESULT")?></td>
			</tr>
		
		<?
		while($arResult = mysql_fetch_array($result))
		{
			echo "<tr>";
			echo "<td>".$arResult["Name"]."</td>";
			echo "<td align='right'>".$arResult["Rows"]."</td>";
			echo "<td align='right'>".number_format($arResult["Data_length"], 0, ',', ' ')."</td>";

			if(strtoupper($arResult["Type"])==strtoupper("MyISAM") || strtoupper($arResult["Engine"])==strtoupper("MyISAM") || (empty($arResult["Type"]) && strlen($arResult["Comment"])>0 && empty($arResult["Engine"])))
			{
				echo "<td>";
				$query = 'CHECK TABLE '.$arResult["Name"];
				$status = mysql_query($query);
				$i=0;
				$toRepair = "";
				while($arStatus = mysql_fetch_array($status))
				{
					if($i>0) echo "<br>";
					$i++;
					echo "[".$arStatus["Msg_type"]."]&nbsp;";
					if($arStatus["Msg_type"]=="status" || $arStatus["Msg_type"]=="info")
						echo "<span style='color:green;'>";
					else
						echo "<span style='color:red;'>";
					echo $arStatus["Msg_text"]."</span>";
					flush();
					
					if($arStatus["Msg_type"]=="error" || $arStatus["Msg_type"]=="warning")
						$toRepair = $arStatus["Table"];
				}
				echo "</td>";
				if(!empty($toRepair))
				{
					echo "<td>";
					$j=0;

					$queryR = 'REPAIR TABLE '.$toRepair;
					$repair = mysql_query($queryR);
					$toCheck = "";
					while($arRepair = mysql_fetch_array($repair))
					{
						if($j>0) echo "<br>";
						echo "[Repair&nbsp;".$arRepair["Msg_type"]."]&nbsp;";
						if($arRepair["Msg_type"]=="status" || $arRepair["Msg_type"]=="info")
							echo "<span style='color:green;'>";
						else
							echo "<span style='color:red;'>";
						echo $arRepair["Msg_text"]."</span>";
						$j++;
						flush();
						$toCheck = $arRepair["Table"];
					}
					if(!empty($toCheck))
					{
						$queryC = 'CHECK TABLE '.$toCheck;
						$statusC = mysql_query($queryC);
						while($arStatusC = mysql_fetch_array($statusC))
						{
							echo "<br>";
							echo "[Check&nbsp;".$arStatusC["Msg_type"]."]&nbsp;";
							if($arStatusC["Msg_type"]=="status" || $arStatusC["Msg_type"]=="info")
								echo "<span style='color:green;'>";
							else
								echo "<span style='color:red;'>";
							echo $arStatusC["Msg_text"]."</span>";
							flush();
						}
					}
					echo "</td>";
				}
				else
					echo "<td>&nbsp;</td>";
				flush();
			}
			else
			{
				if(!empty($arResult["Type"]))
					echo "<td>".$arResult["Type"]."</td>";
				else
					echo "<td>".$arResult["Engine"]."</td>";
				echo "<td>&nbsp;</td>";
			}
			echo "</tr>";
		}
		?>
		<tr class="head"><td colspan="5"><?echo "<b>".GetMessage("RDB_EXEC_TIME")." </b>".round((microtime_float()-$start),5).GetMessage("RDB_SEC");?></td></tr>
		</table>
		<?
		
	}
	else
		echo "<span style='color:red;'>".GetMessage('RDB_DATABASE_ERROR')."</span>";
}

function show_tip()
{
	?>
	<form name="check" action="">
	<input type="submit" value="<?=GetMessage("RDB_CHECK_TABLES")?>">
	<input type="hidden" value="Y" name="check_tables">
	<?
	if(isset($_REQUEST["login"]))
		echo '<input type="hidden" value="'.$_REQUEST["login"].'" name="login">';
	if(isset($_REQUEST["password"]))
		echo '<input type="hidden" value="'.$_REQUEST["password"].'" name="password">';
	if(isset($_REQUEST["lang"]))
		echo '<input type="hidden" value="'.$_REQUEST["lang"].'" name="lang">';
}

if(isset($_REQUEST["login"]) && isset($_REQUEST["password"]))
{
	@include($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/dbconn.php");

	if($_REQUEST["login"]==$DBLogin && $_REQUEST["password"]==$DBPassword) 
	{
		$lang = ($lang== "ru")?"ru":"en";
		
		@include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/lang/".$lang."/admin/repair_db.php");
		function GetMessage($code)
		{
			global $MESS;
			return $MESS[$code];
		}
		?>
		<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
		<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
		<title><?=GetMessage("RDB_REPAIR_DATABASE")?></title>
		<link rel="stylesheet" type="text/css" href="/bitrix/themes/.default/adminstyles.css">
		</head>
		<body>
		<div style="padding:10px;">
		<?
		if($DBType == "mysql")
		{
			if($_REQUEST["check_tables"]=="Y")
			{
				repair_db();
			}
			else
			{
				?>
				<p><?=GetMessage("RDB_TIP_1")?></p>
				<p><span style="color:red;"><?=GetMessage("RDB_TIP_2")?><br><?=GetMessage("RDB_TIP_3")?></span></p>
				<?
				show_tip();
			}
		}
		else	
			echo "<span style='color:red;'>".GetMessage('RDB_DATABASE_ERROR')."</span>";
		
		?>
		</div>
		</body></html>
<?
	}
}
else
{
	require_once(dirname(__FILE__)."/../include/prolog_admin_before.php");
	define("HELP_FILE", "utilities/repair_db.php");
	IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/admin/".basename(__FILE__));

	if(!$USER->CanDoOperation('edit_php'))
		$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	else
	{
		$APPLICATION->SetTitle(GetMessage("RDB_REPAIR_DATABASE"));
		require_once(dirname(__FILE__)."/../include/prolog_admin_after.php");
		if($DBType == "mysql")
		{
			if($_REQUEST["check_tables"]=="Y")
			{
				repair_db();
			}
			else
			{
				?>
				<p><?=GetMessage("RDB_TIP_1")?></p>
				<?echo CAdminMessage::ShowMessage(Array("MESSAGE"=>GetMessage("RDB_TIP_2"), "TYPE"=>"ERROR", "HTML"=>true, "DETAILS"=>GetMessage("RDB_TIP_3")));
				show_tip();
			}
		}
		else
			echo CAdminMessage::ShowMessage(Array("MESSAGE"=>GetMessage("RDB_DATABASE_ERROR"), "TYPE"=>"ERROR"));
		require_once(dirname(__FILE__)."/../include/epilog_admin.php");
	}
}
?>