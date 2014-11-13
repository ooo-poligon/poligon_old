<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/search/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/search/prolog.php");

IncludeModuleLangFile(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight("search");
if($POST_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$res=false;
if(strlen($Reindex)>0)
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
	global $NS;
	if(strlen($Next)<=0)
	{
		$NS=Array();
		COption::SetOptionString("search", "max_execution_time", $max_execution_time);
		if($Full == "N")
		{
			if($site_id != "")
				$NS["SITE_ID"] = $site_id;
			if($module_id != "")
				$NS["MODULE_ID"] = $module_id;
		}
	}
	else
		$NS=unserialize($NS);
	$res = CSearch::ReIndexAll($Full!="N", COption::GetOptionInt("search", "max_execution_time"), $NS);
	if(is_array($res)):
		CAdminMessage::ShowMessage(array(
			"MESSAGE"=>GetMessage("SEARCH_REINDEX_IN_PROGRESS"),
			"DETAILS"=>GetMessage("SEARCH_REINDEX_TOTAL")." <b>".$res["CNT"]."</b><br>
				<a href=\"".htmlspecialchars("search_reindex.php?lang=".LANG."&Continue=Y".($Full!="N"?"":"&Full=N")."&NS=".urlencode(serialize($res)))."\">".GetMessage("SEARCH_REINDEX_NEXT_STEP")."</a>",
			"HTML"=>true,
			"TYPE"=>"OK",
		));
	?>
		<input type="hidden" id="NS" name="NS" value="<?=htmlspecialchars(serialize($res))?>">
	<?else:
		CAdminMessage::ShowMessage(array(
			"MESSAGE"=>GetMessage("SEARCH_REINDEX_COMPLETE"),
			"DETAILS"=>GetMessage("SEARCH_REINDEX_TOTAL")." <b>".$res."</b>",
			"HTML"=>true,
			"TYPE"=>"OK",
		));
	?>
		<input type="hidden" id="NSTOP" name="NSTOP" value="Y">
	<?endif;
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_js.php");
}
else
{

$APPLICATION->SetTitle(GetMessage("SEARCH_REINDEX_TITLE"));

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("SEARCH_REINDEX_TAB"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("SEARCH_REINDEX_TAB_TITLE")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<script language="JavaScript">
var savedNS;
var stop;
function StartReindex()
{
	stop=false;
	savedNS='start!';
	document.getElementById('reindex_result_div').innerHTML='';
	document.getElementById('stop_button').disabled=false;
	document.getElementById('start_button').disabled=true;
	document.getElementById('continue_button').disabled=true;
	setTimeout('DoNext()', 1000);
}
function DoNext()
{
	if(document.getElementById('NS'))
		newNS=document.getElementById('NS').value;
	else
		newNS=null;
	if(document.getElementById('NSTOP'))
	{
		EndReindex();
		return;
	}
	if(newNS!=savedNS)
	{
		queryString='lang=<?echo htmlspecialchars(LANG)?>';
		if(savedNS!='start!')
		{
			queryString+='&Next=Y';
			if(document.getElementById('NS'))
				queryString+='&NS='+document.getElementById('NS').value;
		}
		if(document.getElementById('Full').checked)
		{
			queryString+='&Full=N';
			site_id = document.fs1.LID.value;
			if(site_id!='NOT_REF')
				queryString+='&site_id='+site_id;
			module_id = document.fs1.MODULE_ID.value;
			if(module_id!='NOT_REF')
				queryString+='&module_id='+module_id;
		}
		queryString+='&max_execution_time='+document.getElementById('max_execution_time').value;
		queryString+='&Reindex=Y';
		savedNS=newNS;
		//alert(queryString);
		CHttpRequest.Action = function(result)
		{
			CloseWaitWindow();
			if(result.indexOf('input type="hidden"') == -1)
			{
				StopReindex();
				savedNS = 'timeout!';
			}
			else
			{
				document.getElementById('reindex_result_div').innerHTML = result;
			}
		}
		ShowWaitWindow();
		CHttpRequest.Send('search_reindex.php?'+queryString);
	}
	if(!stop)
		setTimeout('DoNext()', 1000);
}
function StopReindex()
{
	stop=true;
	document.getElementById('stop_button').disabled=true;
	document.getElementById('start_button').disabled=false;
	document.getElementById('continue_button').disabled=false;
}
function ContinueReindex()
{
	stop=false;
	document.getElementById('stop_button').disabled=false;
	document.getElementById('start_button').disabled=true;
	document.getElementById('continue_button').disabled=true;
	setTimeout('DoNext()', 1000);
}
function EndReindex()
{
	stop=true;
	document.getElementById('stop_button').disabled=true;
	document.getElementById('start_button').disabled=false;
	document.getElementById('continue_button').disabled=true;
}
</script>

<div id="reindex_result_div" style="margin:0px">
<?if($Continue=="Y"):?>
<input type="hidden" name="NS" id="NS" value="<?=htmlspecialchars($NS)?>">
<?endif?>
</div>

<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?echo htmlspecialchars(LANG)?>" name="fs1">
<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
	<tr>
		<td width="40%"><?echo GetMessage("SEARCH_REINDEX_REINDEX_CHANGED")?></td>
		<td width="60%"><input type="checkbox" name="Full" id="Full" value="N" checked OnClick="document.fs1.MODULE_ID.disabled=document.fs1.LID.disabled=!this.checked;"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("SEARCH_REINDEX_STEP")?></td>
		<td><input type="text" name="max_execution_time" id="max_execution_time" size="3" value="<?echo htmlspecialchars(COption::GetOptionString("search", "max_execution_time"));?>"> <?echo GetMessage("SEARCH_REINDEX_STEP_sec")?></td>
	</tr>
	<tr>
		<td><?=GetMessage("SEARCH_REINDEX_SITE")?></td>
		<td><?echo CLang::SelectBox("LID", $str_LID, GetMessage("SEARCH_REINDEX_ALL"), "");?></td>
	</tr>
	<tr>
		<td><?=GetMessage("SEARCH_REINDEX_MODULE")?></td>
		<td>
		<select name="MODULE_ID" id="MODULE_ID">
		<option value="NOT_REF"><?=GetMessage("SEARCH_REINDEX_ALL")?></option>
		<option value="main"><?=GetMessage("SEARCH_REINDEX_MAIN")?></option>
		<?if(IsModuleInstalled('iblock')):?>
			<option value="iblock"><?=GetMessage("SEARCH_REINDEX_IBLOCKS")?></option>
		<?endif;?>
		<?if(IsModuleInstalled('forum')):?>
		<option value="forum"><?=GetMessage("SEARCH_REINDEX_FORUM")?></option>
		<?endif;?>
		<?if(IsModuleInstalled('blog')):?>
		<option value="blog"><?=GetMessage("SEARCH_REINDEX_BLOG")?></option>
		<?endif;?>
		</select>
		</td>
	</tr>

<?
$tabControl->Buttons();
?>
	<input type="button" id="start_button" value="<?echo GetMessage("SEARCH_REINDEX_REINDEX_BUTTON")?>" OnClick="StartReindex();">
	<input type="button" id="stop_button" value="<?=GetMessage("SEARCH_REINDEX_STOP")?>" OnClick="StopReindex();" disabled>
	<input type="button" id="continue_button" value="<?=GetMessage("SEARCH_REINDEX_CONTINUE")?>" OnClick="ContinueReindex();" disabled>
<?
$tabControl->End();
?>
</form>
<?if($Continue=="Y"):?>
<script language="JavaScript">
	ContinueReindex();
</script>
<?endif?>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}
?>
