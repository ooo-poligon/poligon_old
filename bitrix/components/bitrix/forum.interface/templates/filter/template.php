<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeAJAX();
$APPLICATION->AddHeadScript('/bitrix/components/bitrix/forum.interface/templates/popup/script.js');
$GLOBALS['APPLICATION']->AddHeadString('<script src="/bitrix/js/main/utils.js"></script>', true);
?>
<script>
if (phpVars == null || typeof(phpVars) != "object")
{
	var phpVars = {
		'ADMIN_THEME_ID': '.default',
		'titlePrefix': '<?=CUtil::addslashes(COption::GetOptionString("main", "site_name", $_SERVER["SERVER_NAME"]))?> - '};
}
</script>
<form name="forum_form" id="forum_form_<?=$arResult["id"]?>" action="<?=$APPLICATION->GetCurPageParam()?>" method="get" class="forum-form">
	<?=bitrix_sessid_post()?><?
	foreach ($arResult["FIELDS"] as $key => $res):
		if ($res["TYPE"] == "HIDDEN"):
			?><input type="hidden" name="<?=$res["NAME"]?>" value="<?=$res["VALUE"]?>" /><?
			unset($arResult["FIELDS"][$key]);
		endif;
	endforeach;

?>
<table border="0" cellpadding="0" cellspacing="0" style="font-size:100%;">
	<tr><td style="font-size:100%;">
		<table border="0" cellpadding="0" cellspacing="0" class="forum-title" width="100%">
			<tr><td width="100%"><?=$arParams["HEADER"]["TITLE"]?></td><?
			
/* Filter popup*/			
if (count($arResult["FIELDS"]) > $arParams["SHOW_STRINGS"]):
?><td class="filter-more">
	<span id="switcher_<?=$arResult["id"]?>" onclick="ForumFilter.ShowFilter(this, '<?=$arResult["id"]?>');" <?
		?>title="<?=GetMessage("FMI_SHOW")?>"></span>

<div style="position:relative;">
	<div id="container_<?=$arResult["id"]?>" style="display:none; position:absolute;" class="forum-popup">
		<table cellpadding="0" cellspacing="0" border="0" class="forum-popup forum-menu-popup-table">
			<tr class="forum-popup" onmouseover="this.className='forum-popup-over'" onmouseout="this.className='forum-popup'" <?
				?> onclick="ForumFilter.CheckFilter('<?=$arResult["id"]?>', 'all')">
				<td class="forum-popup forum-menu-popup" onmouseover="this.className='forum-popup-over forum-menu-popup-hover'" <?
					?>onmouseout="this.className='forum-popup forum-menu-popup'">

	<table border="0" cellpadding="0" cellspacing="0" class="forum-popup-item" onMouseOver="this.className='forum-popup-item-over';" <?
			?>onMouseOut="this.className='forum-popup-item';">
		<tr><td><input type="checkbox" name="forum_filter[]" id="forum_filter_<?=$arResult["id"]?>_all" value="all" readonly="readonly" /></td>
			<td><?=GetMessage("FMI_SHOW_ALL_FILTER")?></td></tr>
	</table>
				</td>
			</tr><?
	$counter = 0;
	foreach ($arResult["FIELDS"] as $key => $res):
		$counter++;
		if ($arParams["SHOW_STRINGS"] >= $counter)
			continue;

			?>
			<tr class="forum-popup" onmouseover="this.className='forum-popup-over'" onmouseout="this.className='forum-popup'" <?
				?> onclick="ForumFilter.CheckFilter('<?=$arResult["id"]?>', '<?=$res["NAME"]?>'); document.getElementById('forum_filter_<?=$arResult["id"]?>_all').checked = false;">
				<td class="forum-popup forum-menu-popup" onmouseover="this.className='forum-popup-over forum-menu-popup-hover'" <?
					?>onmouseout="this.className='forum-popup forum-menu-popup'">

	<table border="0" cellpadding="0" cellspacing="0" class="forum-popup-item" onMouseOver="this.className='forum-popup-item-over';" <?
			?>onMouseOut="this.className='forum-popup-item';">
		<tr><td><input type="checkbox" name="forum_filter[]" id="forum_filter_<?=$arResult["id"]?>_<?=$res["NAME"]?>" value="<?=$res["NAME"]?>" readonly="readonly" <?=(!in_array($res["NAME"], $arResult["SHOW_FILTER"]) ? "" : " checked='checked'")?> /></td>
			<td><?=$res["TITLE"]?></td></tr>
	</table>
				</td>
			</tr><?
	endforeach;

		?></table><?
	?></div>
</div>
	</td><?

endif;
/* Filter popup*/



			?></tr></table>
		<div class="forum-br"></div>
		<table class="forum-main forum-filter" width="100%"><?
		
			$counter = 0;
			foreach ($arResult["FIELDS"] as $key => $res):
				$counter++;
				if ($arParams["SHOW_STRINGS"] < $counter):
					?><tr id="row_<?=$arResult["id"]."_".$res["NAME"]?>" <?
					?><?=(!in_array($res["NAME"], $arResult["SHOW_FILTER"]) ? " style=\"display:none;\"" : "")?> ><?
				else:
					?><tr><?
				endif;
					
				?><td align="right"><?=$res["TITLE"]?>:</td><td align="left"><?
				
				if ($arParams["SHOW_STRINGS"] < $counter):
					?><span class="filter-hide" onclick="ForumFilter.CheckFilter('<?=$arResult["id"]?>', '<?=$res["NAME"]?>');"></span><?
				endif;
				
				if ($res["TYPE"] == "SELECT"):
					if (!empty($_REQUEST["del_filter"]))
						$res["ACTIVE"] = "";
					?><select name="<?=$res["NAME"]?>"><?
					foreach ($res["VALUE"] as $key => $title) 
					{
						?><option value="<?=$key?>" <?=($res["ACTIVE"] == $key ? " selected='selected'" : "")?>><?=$title?></option><?
					}
					?></select><?
				elseif ($res["TYPE"] == "PERIOD"):
					if (!empty($_REQUEST["del_filter"]))
					{
						$res["VALUE"] = "";
						$res["VALUE_TO"] = "";
					}
					?><?$APPLICATION->IncludeComponent("bitrix:main.calendar", "",
						array(
							"SHOW_INPUT" => "Y",
							"INPUT_NAME" => $res["NAME"], 
							"INPUT_NAME_FINISH" => $res["NAME_TO"],
							"INPUT_VALUE" => $res["VALUE"], 
							"INPUT_VALUE_FINISH" => $res["VALUE_TO"],
							"FORM_NAME" => "forum_form"),
						$component,
						array(
							"HIDE_ICONS" => "Y"));?><?
				else:
					if (!empty($_REQUEST["del_filter"]))
					{
						$res["VALUE"] = "";
					}
					?><input type="text" name="<?=$res["NAME"]?>" value="<?=$res["VALUE"]?>" /><?
				endif;
				?></td></tr><?
			endforeach;
		?><tr><td colspan="2" align="center"><?
		if (empty($arResult["BUTTONS"])):
			?><input type="submit" name="set_filter" value="<?=GetMessage("FORUM_BUTTON_FILTER")?>" />&nbsp;
			<input type="submit" name="del_filter" value="<?=GetMessage("FORUM_BUTTON_RESET")?>" /><?
		else:
			foreach ($arResult["BUTTONS"] as $res):
			?><input type="submit" name="<?=$res["NAME"]?>" value="<?=$res["VALUE"]?>" /><?
			endforeach;
		endif;
		?></td></tr>
		</table>
	</td></tr>
</table>
</form><?