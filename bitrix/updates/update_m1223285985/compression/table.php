<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
IncludeModuleLangFile(__FILE__);

$Contents = ob_get_contents();
?>
<div style='margin:5px;'>
<table cellpadding='1' cellspacing='0' class='tableborder'>
	<tr>
		<td>
			<table cellpadding='3' cellspacing='1' class='tablebody'>
				<tr>
					<td nowrap colspan="2" class="tablebodytext"><b><?echo GetMessage('LIBRARY')?></b></td>
				</tr>
				<tr>
					<td nowrap class="tablebodytext"><?echo GetMessage("NOT_COMPRESSED")?></td>
					<td align='right' class="tablebodytext"><font color='green'><?echo strlen($Contents)?></font></td>
				</tr>
				<tr>
					<td nowrap class="tablebodytext"><?echo GetMessage("COMPRESSED")?></td>
					<td align='right' class="tablebodytext"><?echo strlen(gzcompress($Contents,$level))?></td>
				</tr>
				<tr>
					<td nowrap class="tablebodytext"><?echo GetMessage("COEFFICIENT")?></td>
					<td align='right' class="tablebodytext"><?echo round(strlen($Contents)/strlen(gzcompress($Contents,$level)),2)?></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</div>
<div class="empty"></div>