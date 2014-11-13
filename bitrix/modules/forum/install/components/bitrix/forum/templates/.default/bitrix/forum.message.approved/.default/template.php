<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
// *****************************************************************************************
	?><?=ShowError($arResult["ERROR_MESSAGE"])?><?
	?><?=ShowNote($arResult["OK_MESSAGE"])?><?
	if (!empty($arResult["NAV_STRING"])):?>
		<?=$arResult["NAV_STRING"]?>
		<div class="forum-br"></div><?
	endif;
	?><div class="forum-title">
		<input type="checkbox" name="message_all" value="Y" id="message_all" onclick="SelectAllCheckBox('FORUM_MESSAGES', 'message_id[]', 'message_all');" checked>
		<span><?=GetMessage("FMA_TITLE")?></span> 
		<?if ($arParams["TID"] > 0):?>
			&laquo;<a href="<?=$arResult["read"]?>"><?=$arResult["TOPIC"]["TITLE"]?></a>&raquo;<span>, </span>
		<?endif;?>
		<span><?=GetMessage("FMA_FORUM")?> <a href="<?=$arResult["list"]?>"><?=$arResult["FORUM"]["NAME"]?></a></span>
	</div><?
	?><div class="forum-br"></div><?
	
	if ($arResult["SHOW_RESULT"] == "Y")
	{
		?><form name="FORUM_MESSAGES" id="FORUM_MESSAGES" action="" class="forum-form">
		<input type="hidden" name="PAGE_NAME" value="message_approve">
		<?=$arResult["sessid"]?>
		<table class="forum-message" width="100%"><?
		foreach ($arResult["MESSAGE"] as $res)
		{
		?><tr valign="top">
			<td class="forum-message-user-info" rowspan="2">
				<a name="message<?=$res["ID"];?>"></a>
				<div class="forum-message-name"><?=$res["AUTHOR_NAME"]?></div>
				<?if (strlen($res["AVATAR"]) > 0):?>
					<a href="<?=$res["profile_view"]?>" title="<?=GetMessage("FMA_AUTHOR_PROFILE")?>"><?=$res["AVATAR"]?></a><br />
				<?endif;?>
				<div class="forum-message-description"><?=$res["DESCRIPTION"]?>&nbsp;</div>
				<?if (intVal($res["NUM_POSTS"]) > 0):?>
					<div class="forum-message-posts"><?=GetMessage("FMA_NUM_MESS")?></div> <?=$res["NUM_POSTS"];?><br />
				<?endif;?>
				<?if (strlen($res["~DATE_REG"]) > 0):?>
					<div class="forum-message-datereg"><?=GetMessage("FMA_DATE_REGISTER")?></div> <?=$res["DATE_REG"];?><br />
				<?endif;?>
			</td>
			<td class="border-bottom">
				<table class="clear">
					<tr>
						<td nowrap="nowrap">
							<input type="checkbox" checked="checked" name="message_id[]" value="<?=$res["ID"]?>" id="message_id_<?=$res["ID"]?>_" 
								onclick="document.getElementById('message_all').checked=false;">&nbsp;</td>
						<td width="100%" nowrap="nowrap"><div class="forum-message-datecreate"><?=GetMessage("FMA_DATE_CREATE")?></div><?=$res["POST_DATE"]?><br /></td>
					</tr>
				</table>
				<div class="forum-hr"/></div>
				<?=$res["POST_MESSAGE_TEXT"]?>
				<?if (strLen($res["ATTACH_IMG"]) > 0):?>
					<br /><br /><?=$res["ATTACH_IMG"]?>
				<?endif;?>
				<?if (strLen($res["SIGNATURE"]) > 0):?>
					<br /><br /><div class="forum-message-signature"><?=$res["SIGNATURE"]?></div>
				<?endif;?>
			</td>
		</tr>
		<tr><td class="border-top"></td></tr>
		<tr><td colspan="2" class="clear"><div class="forum-br"></div></td></tr><?
		}
	?></table>
	</form>
	<?if (!empty($arResult["NAV_STRING"])):?>
		<div class="forum-br"></div>
		<?=$arResult["NAV_STRING"]?>
	<?endif;
	}
	else
	{
	?><table class="forum-main">
		<tr>
			<th class="left" width="100%"><b><?=GetMessage("FMA_EMPTY_RESULT")?></b></th>
		</tr>
	</table><?
	}
