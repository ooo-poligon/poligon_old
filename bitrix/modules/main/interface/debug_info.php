<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
IncludeModuleLangFile(__FILE__);

// ************************************************************************
// $main_exec_time, $bShowTime, $bShowStat MUST be defined before include
// ************************************************************************

global $APPLICATION;

if($bShowTime || $bShowStat)
	echo '<div class="bx-component-debug bx-debug-summary">';

if($bShowTime)
	echo GetMessage("debug_info_cr_time").' '.$main_exec_time.' '.GetMessage("debug_info_sec").'<br>';

if($bShowStat):
	$totalQueryCount = $GLOBALS["DB"]->cntQuery;
	$totalQueryTime = $GLOBALS["DB"]->timeQuery;
	foreach($APPLICATION->arIncludeDebug as $i=>$arIncludeDebug)
	{
		$totalQueryCount += $arIncludeDebug["QUERY_COUNT"];
		$totalQueryTime += $arIncludeDebug["QUERY_TIME"];
	}
	echo GetMessage("debug_info_total_queries")." ".intval($totalQueryCount)."<br>";
	echo GetMessage("debug_info_total_time")." ".round($totalQueryTime, 4)." ".GetMessage("debug_info_sec")."<br>";
	echo '<a title="'.GetMessage("debug_info_query_title").'" href="javascript:jsDebugWindow.Show(\'BX_DEBUG_INFO_'.count($APPLICATION->arIncludeDebug).'\')">'.GetMessage("debug_info_query_stat").'</a><br>';
	$APPLICATION->arIncludeDebug[]=array(
		"PATH"=>$APPLICATION->GetCurPage(),
		"QUERY_COUNT"=>intval($GLOBALS["DB"]->cntQuery),
		"QUERY_TIME"=>round($GLOBALS["DB"]->timeQuery, 4),
		"QUERIES"=>$GLOBALS["DB"]->arQueryDebug,
		"TIME"=>$main_exec_time,
	);
?>
<div id="BX_DEBUG_WINDOW" class="bx-debug-window">
	<div class="bx-debug-title">
	<table cellspacing="0" style="width:100% !important;">
		<tr>
			<td class="bx-debug-title-text" onmousedown="jsFloatDiv.StartDrag(arguments[0], document.getElementById('BX_DEBUG_WINDOW'));"><?echo GetMessage("debug_info_title")?></td>
			<td width="0%"><a class="bx-debug-close" href="javascript:jsDebugWindow.Close();" title="<?echo GetMessage("debug_info_close")?>"></a></td>
		</tr>
	</table>
	</div>
<?
foreach($APPLICATION->arIncludeDebug as $i=>$arIncludeDebug):
?>
<div id="BX_DEBUG_INFO_<?=$i?>" style="display:none">
	<div class="bx-debug-description"><div class="bx-debug-info"></div>
		<p><?echo GetMessage("debug_info_path")?> <?=$arIncludeDebug["PATH"]?></p>
		<p><?echo GetMessage("debug_info_time")?> <?=$arIncludeDebug["TIME"]?> <?echo GetMessage("debug_info_sec")?></p>
		<p><?echo GetMessage("debug_info_queries")?> <?=$arIncludeDebug["QUERY_COUNT"]?>, <?echo GetMessage("debug_info_time1")?> <?=$arIncludeDebug["QUERY_TIME"]?> <?echo GetMessage("debug_info_sec")?><?if($arIncludeDebug["TIME"] > 0):?> (<?=round($arIncludeDebug["QUERY_TIME"]/$arIncludeDebug["TIME"]*100, 2)?>%)<?endif?></p>

		
	</div>
<?if(count($arIncludeDebug["QUERIES"])>0):?>
	<div class="bx-debug-content bx-debug-content-table">
<?
			$arQueries = array();
			foreach($arIncludeDebug["QUERIES"] as $j=>$arQueryDebug)
			{
				$strSql = $arQueryDebug["QUERY"];
				$arQueries[$strSql]["COUNT"]++;
				$arQueries[$strSql]["CALLS"][] = array(
					"TIME"=>$arQueryDebug["TIME"],
					"TRACE"=>$arQueryDebug["TRACE"]
				);
			}
			?>
<table cellpadding="0" cellspacing="0" border="0" style="width:100% !important;">
<?$j=1;foreach($arQueries as $strSql=>$query):?>
	<tr>
		<td align="right" valign="top"><?echo $j?></td>
		<td><a href="javascript:jsDebugWindow.ShowDetails('BX_DEBUG_INFO_<?=$i."_".$j?>')"><?echo htmlspecialchars(substr($strSql, 0, 100))."..."?></a>&nbsp;(<?echo $query["COUNT"]?>) </td>
		<td align="right" valign="top"><?
		$t = 0.0;
		foreach($query["CALLS"] as $call)
			$t += $call["TIME"];
		echo number_format($t/$query["COUNT"], 5);
		?></td>
	</tr>
<?$j++;endforeach;?>
</table>
	</div>

	<div class="bx-debug-content bx-debug-content-details">
<?$j=1;foreach($arQueries as $strSql=>$query):?>
		<div id="BX_DEBUG_INFO_<?=$i."_".$j?>" style="display:none">
			<b><?echo GetMessage("debug_info_query")?> <?echo $j?>:</b>
			<br><br>
			<?
			$strSql = preg_replace("/[\\n\\r\\t\\s ]+/", " ", $strSql);
			$strSql = preg_replace("/^ +/", "", $strSql);
			$strSql = preg_replace("/ (INNER JOIN|OUTER JOIN|LEFT JOIN|SET|LIMIT) /i", "\n\\1 ", $strSql);
			$strSql = preg_replace("/(INSERT INTO [A-Z_0-1]+?)\\s/i", "\\1\n", $strSql);
			$strSql = preg_replace("/(INSERT INTO [A-Z_0-1]+?)([(])/i", "\\1\n\\2", $strSql);
			$strSql = preg_replace("/([\\s)])(VALUES)([\\s(])/i", "\\1\n\\2\n\\3", $strSql);
			$strSql = preg_replace("/ (FROM|WHERE|ORDER BY|GROUP BY|HAVING) /i", "\n\\1\n", $strSql);
			echo str_replace(
				array("\n"),
				array("<br>"),
				htmlspecialchars($strSql)
			);
			?>
			<br><br>
			<b><?echo GetMessage("debug_info_query_from")?></b>
<?
$k=1;
foreach($query["CALLS"] as $call):
	$back_trace = $call["TRACE"];
?>
<?if(is_array($back_trace)):
	foreach($back_trace as $n=>$tr):
?>
		<br><br>
		<b>(<?echo $k.".".($n+1)?>)</b>
<?
		echo $tr["file"].":".$tr["line"]."<br><nobr>".htmlspecialchars($tr["class"].$tr["type"].$tr["function"]);
		if($n == 0)
			echo "(...)</nobr>";
		else
			echo "</nobr>(".htmlspecialchars(print_r($tr["args"], true)).")";
		if($n>1)
			break;
	endforeach;
?>
<?
else: //is_array($back_trace)
?>
					<br><br>
					<b>(<?echo $k?>)</b> <?echo GetMessage("debug_info_query_from_unknown")?>
<?
endif
?>
					<br><br>
					<?echo GetMessage("debug_info_query_time")?> <?echo round($call["TIME"], 5)?> <?echo GetMessage("debug_info_sec")?>
<?
	$k++;
endforeach;
?>
		</div>
<?$j++;endforeach;?>
	</div>
<?
endif; //if(count($arIncludeDebug["QUERIES"])>0)
?>
</div>
<?endforeach;?>
<div class="bx-debug-buttons">
	<input type="button" value="<?echo GetMessage("debug_info_close1")?>" onclick="jsDebugWindow.Close()" title="<?echo GetMessage("debug_info_close")?>">
</div>
</div>
<?
endif; //$bShowStat

if($bShowTime || $bShowStat)
	echo '</div><div class="empty"></div>';
?>