<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/iblock.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
$SELECTED_SECTION = "iblock";
$APPLICATION->SetTitle(GetMessage("IBLOCK_INDEX_TITLE"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

IncludeModuleLangFile(__FILE__);

$arResIT = Array();
$arResIB = Array();
$db_iblock_type = CIBlockType::GetList(Array("ID"=>"ASC"));
while($arRes = $db_iblock_type->Fetch())
{
	$arFilter = Array("TYPE"=>$arRes["ID"], "MIN_PERMISSION"=>"W", "CNT_ALL"=>"Y");
	if(CModule::IncludeModule("workflow")) $arFilter["MIN_PERMISSION"] = "U";
	$iblocks = CIBlock::GetList(Array("SORT"=>"asc", "NAME"=>"ASC"), $arFilter, true);
	$arIBlock = $iblocks->Fetch();
	if(!$USER->IsAdmin() && !$arIBlock) continue;
	if($arIBType = CIBlockType::GetByIDLang($arRes["ID"], LANG))
	{
		$ibtype = $arIBType["ID"];
		$arResIT[] = $arIBType;
		$arResIB[$ibtype] = Array();
		while($arIBlock = $iblocks->Fetch())
			$arResIB[$ibtype][] = $arIBlock;
	}
}
if(false):
?>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr><td colspan="<?=count($arResIT)+1?>" align="center">
		<table cellpadding="2" cellspacing="0" border="0" width="0" style="border: 1px solid #000000; padding: 2px; width:0%;">
			<tr>
			<td align="center"><img src="/bitrix/images/iblock/tmp/icon.gif"></td>
			<td align="center" style="width:0px; font-size:10px;">Типы информационных блоков</td>
			</tr>
		</table>
	</td></tr>
<tr><td colspan="<?=count($arResIT)+1?>" align="center"><img src="/bitrix/images/iblock/tmp/vline.gif"></td></tr>
<tr>
	<td align="left" background="/bitrix/images/iblock/tmp/hline.gif"><img src="/bitrix/images/iblock/tmp/hline1.gif"></td>
	<?for($i=0; $i<count($arResIT)-1; $i++):?>
	<td background="/bitrix/images/iblock/tmp/hline.gif"><img src="/bitrix/images/iblock/tmp/hline.gif"></td>
	<?endfor?>
	<td align="right" background="/bitrix/images/iblock/tmp/hline.gif"><img src="/bitrix/images/iblock/tmp/hline2.gif"></td>
</tr>

<tr>
	<td align="left"><img src="/bitrix/images/iblock/tmp/vline.gif"></td>
	<?for($i=0; $i<count($arResIT)-1; $i++):?>
	<td align="center" align="right"><img src="/bitrix/images/iblock/tmp/vline2.gif"></td>
	<?endfor?>
	<td align="right"><img src="/bitrix/images/iblock/tmp/vline2.gif"></td>
</tr>

<tr>
	<?for($i=0; $i<count($arResIT); $i++):?>
		<td align="left" width="20%">
			<table cellpadding="2" cellspacing="0" border="0" width="0" style="border: 1px solid #000000; padding: 2px; width:0%;">
				<tr>
					<td align="center"><img src="/bitrix/images/iblock/tmp/icon.gif"></td>
					<td align="center" style="width:0px; font-size:10px;"><?=htmlspecialcharsex($arResIT[$i]["NAME"])?></td>
				</tr>
			</table>
		</td>
	<?endfor;?>
	<td align="right">
		<table cellpadding="2" cellspacing="0" border="0" width="0" style="border: 1px solid #000000; padding: 2px; width:0%;">
			<tr>
				<td align="center"><img src="/bitrix/images/iblock/tmp/icon.gif"></td>
				<td align="center" style="width:0px; font-size:10px;">Создать новый тип</td>
			</tr>
		</table>
	</td>
</tr>
<tr>
	<?for($i=0; $i<count($arResIT); $i++):
		$type = $arResIT[$i]["ID"];
		?>
		<td align="left" valign="top">
			<table cellpadding=0 cellspacing=0 border=0 width="100%">
				<tr><td background="/bitrix/images/iblock/tmp/svline.gif" width="0%"><img src="/bitrix/images/iblock/tmp/svline.gif"></td><td width="100%"></td></tr>
				<?for($j=0; $j<count($arResIB[$type]); $j++):?>
					<tr>
					<td valign="top" background="/bitrix/images/iblock/tmp/svline.gif" width="0%"><img src="/bitrix/images/iblock/tmp/svlineh.gif"></td>
					<td><div style="background-color: #FFFFDD; font-size:10px; padding:2px; margin:2px; border: 1px solid #000000;"><?=htmlspecialcharsex($arResIB[$type][$j]["NAME"])?></div></td>
					</tr>
				<?endfor;?>
				<tr>
				<td width="0%" valign="top"><img src="/bitrix/images/iblock/tmp/svlinel.gif"></td>
				<td><div style="background-color: #FFFFEE; font-size:10px; padding:2px; margin:2px; border: 1px solid #000000;">Новый...</div></td></tr>
			</table>
		</td>
	<?endfor;?>
	<td align="right"></td>
</tr>

</table>
<?endif?>

<table cellspacing="1">
<?
$db_iblock_type = CIBlockType::GetList(Array("ID"=>"ASC"));
while($arRes = $db_iblock_type->Fetch())
{
	$arFilter = Array("TYPE"=>$arRes["ID"], "MIN_PERMISSION"=>"W", "CNT_ALL"=>"Y");

	if (CModule::IncludeModule("workflow")) $arFilter["MIN_PERMISSION"] = "U";
	$iblocks = CIBlock::GetList(Array("SORT"=>"asc", "NAME"=>"ASC"), $arFilter, true);
	$arIBlock = $iblocks->Fetch();
	if(!$USER->IsAdmin() && !$arIBlock) continue;

	if($arIBType = CIBlockType::GetByIDLang($arRes["ID"], LANG))
	{
		$ibtype = htmlspecialchars($arIBType["ID"]);
		?>
		<tr>
		<td colspan="2">
			<font class="tableheadtext">
				<a href="/bitrix/admin/iblock_admin.php?type=<?=urlencode($ibtype)?>&lang=<?=LANG?>"><b><?=htmlspecialcharsex($arIBType["NAME"])?></b></a>
			</font>
		</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
			<table width="100%">
				<?if($arIBlock):?>
				<tr>
					<td class="tablehead">&nbsp;</td>
					<?if($arIBType["SECTIONS"]=="Y"):?>
					<td class="tablehead" align="center"><font class="tableheadtext"><?=htmlspecialcharsex($arIBType["SECTION_NAME"])?></font></td>
					<?endif?>
					<td class="tablehead" align="center"><font class="tableheadtext"><?=htmlspecialcharsex($arIBType["ELEMENT_NAME"])?></font></td>
				</tr>
				<?
				do
				{
				?>
					<tr>
						<td class="tablebody"><font class="tablebodytext">
							<a href="iblock_element_admin.php?IBLOCK_ID=<?=$arIBlock["ID"]?>&amp;type=<?=urlencode($ibtype)?>&amp;lang=<?=LANG?>&amp;filter_section=-1" title="<?=GetMessage("IBLOCK_INDEX_LIST_OF_ELEMENTS")?>"><?echo htmlspecialcharsex($arIBlock["NAME"])?></a>
							<?if(CIBlock::GetPermission($arIBlock["ID"])>="X"):?>
								[<a href="iblock_edit.php?ID=<?=$arIBlock["ID"]?>&amp;type=<?=urlencode($ibtype)?>&amp;lang=<?=LANG?>" title="<?=GetMessage("IBLOCK_INDEX_CHANGE_PARAMS")?>">P</a>]
							<?endif?>
						</font></td>
						<?if($arIBType["SECTIONS"]=="Y"):?>
						<td class="tablebody" align="right">
							<font class="tablebodytext">
								<?if(CIBlock::GetPermission($arIBlock["ID"])>="W"):?>
									<a href="iblock_section_admin.php?IBLOCK_ID=<?=$arIBlock["ID"]?>&amp;type=<?=urlencode($ibtype)?>&amp;lang=<?=LANG?>" title="<?=GetMessage('IBLOCK_INDEX_SHOW_LIST')?>"><?=CIBlockSection::GetCount(Array("IBLOCK_ID"=>$arIBlock["ID"]));?></a> [<a href="iblock_section_edit.php?IBLOCK_ID=<?=$arIBlock["ID"]?>&amp;type=<?=urlencode($ibtype)?>&amp;lang=ru&amp;filter=Y&amp;set_filter=Y&amp;filter_section=0&amp;from=iblock_admin" title="<?=GetMessage('IBLOCK_INDEX_ADD')?>">+</a>]
								<?else:?>
									<?=CIBlockSection::GetCount(Array("IBLOCK_ID"=>$arIBlock["ID"]));?>
								<?endif?>
							</font></td>
						</td>
						<?endif?>
						<td class="tablebody" align="right">
							<font class="tablebodytext"><a href="iblock_element_admin.php?IBLOCK_ID=<?=$arIBlock["ID"]?>&amp;type=<?=urlencode($ibtype)?>&amp;lang=<?=LANG?>&amp;filter_section=-1" title="<?=GetMessage('IBLOCK_INDEX_SHOW_LIST')?>"><?=IntVal($arIBlock["ELEMENT_CNT"])?></a> [<a href="iblock_element_edit.php?IBLOCK_ID=<?=$arIBlock["ID"]?>&amp;type=<?=urlencode($ibtype)?>&amp;lang=ru&amp;filter=Y&amp;set_filter=Y&amp;filter_section=-1" title="<?=GetMessage('IBLOCK_INDEX_ADD')?>">+</a>]</font></td>
						</td>
					</tr>
				<?
				}while($arIBlock = $iblocks->Fetch());
				?>
				<?endif; // if($arIBlock):?>
				<?if($USER->IsAdmin()):?>
				<tr>
					<td class="tablebody" colspan="<?=($arIBType["SECTIONS"]=="Y"?"3":"2")?>"><font class="tablebodytext">&nbsp;&nbsp;&nbsp;<a href="iblock_edit.php?lang=<?=LANG?>&type=<?=urlencode($ibtype)?>&Add=Y"><?=GetMessage('IBLOCK_INDEX_ADD_INFOBLOCK')?></a></font></td>
				</tr>
				<?endif?>
			</table>
			&nbsp;
			</td>
		</tr>
		<?
	}
}
?>
	</tr>
</table>
<?if(false && $USER->IsAdmin()):?>
<font class="tablebodytext">
<a href="settings.php?lang=<?=LANG?>&mid_SELECTED=yes&mid=iblock"><?=GetMessage('IBLOCK_INDEX_ADD_TYPE_INFOBLOCK')?></a>
</font>
<?endif?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
