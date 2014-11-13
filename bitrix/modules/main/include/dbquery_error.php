<br>
<table cellpadding="1" cellspacing="0" width="35%" bgcolor="#9C9A9C">
	<tr>
		<td><table cellpadding="5" cellspacing="0" width="100%">
			<tr>
				<td bgcolor="#FFFFFF" align="center">
					<FONT face="Verdana, Arial, Helvetica, sans-serif" size="-1">
					<font color="#FF0000"><b><?echo "DB query error."?></b></font><br>
					Please try later.
					</font><br>
					<?if (is_object($GLOBALS["USER"]) && $GLOBALS["USER"]->CanDoOperation('edit_php')):?>
						</form>
						<form method="post" action="/bitrix/admin/site_checker.php#tiket_form">
							<?
							$strSupportErrorText = "";
							if (strlen($_SERVER["REQUEST_URI"])>0)
								$strSupportErrorText .= "File: ".$_SERVER["REQUEST_URI"]."\n";
							elseif (strlen($_SERVER["SCRIPT_NAME"])>0)
								$strSupportErrorText .= "File: ".$_SERVER["SCRIPT_NAME"]."\n";
							elseif (strlen($_SERVER["SCRIPT_FILENAME"])>0)
								$strSupportErrorText .= "File: ".$_SERVER["SCRIPT_FILENAME"]."\n";

							if (strlen($error_position)>0)
								$strSupportErrorText .= "[".$error_position."]\n";
							if (strlen($strSql)>0)
								$strSupportErrorText .= $strSql."\n";
							if (is_object($this) && strlen($this->db_Error)>0)
								$strSupportErrorText .= "[".$this->db_Error."]\n";
							if (function_exists("debug_backtrace"))
								$strSupportErrorText .= "debug_backtrace:\n".print_r(debug_backtrace(), True)."\n";
							?>
							<input type="hidden" name="last_error_query" value="<?= htmlspecialchars($strSupportErrorText) ?>">
							<input type="submit" value="Send error report to support">
						</form>
					<?endif;?>
				</td>
			</tr>
		</table></td>
	</tr>
</table>	
<br><br><br>