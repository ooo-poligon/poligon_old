<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
		<Tr>
			<Td><div style="width:400px;"><b class="name"><?=$arResult["NAME"]?> <? if ($arResult["PROPERTIES"]["article"]["VALUE"]) echo '('.$arResult["PROPERTIES"]["article"]["VALUE"].')';?></b><br><?//var_dump($arResult["PRICES"]);?><b><?=$arResult["PREVIEW_TEXT"]?></b><br><small><?=$section_name?></small></div></td>
		</tr>
		<tr>
			<td>�������������: <B><?=$arResult["PROPERTIES"]["producer_full"]["VALUE"]?></b><br><br></td>
		</tr>
		<tr>
			<td valign="top">
			<table><tr><Td valign="top">		
			<?=CFile::ShowImage('/images/'.$arResult["PROPERTIES"]["link"]["VALUE"], 200, 200, "border=0 alt='".$img_alt."' style='vertical-align:text-top; margin-right:10px'", "", false);?></td><td valign="top">
			
			    <?$APPLICATION->IncludeFile(
						$APPLICATION->GetTemplatePath("/bitrix/templates/poligon/include_areas/element_text.php"),
						Array(),
						Array("MODE"=>"html")
					);?><br><?=$arResult["~DETAIL_TEXT"]?></td>
			</tr></table>
		</tr>
		<?if($arResult["PROPERTIES"]["pdf"]["VALUE"]){
				echo '<tr><td><table><tr><td>����������� ����������:</td><Td> <a href="'.$arResult['PROPERTIES']['pdf']['VALUE'].'"><img src="/images/pdf_doc.gif"></a></td></tr></table></td></tr>';?>
<?}?>
	</table>
	<?if(count($arResult["LINKED_ELEMENTS"])>0):?>
		<br /><b><?=$arResult["LINKED_ELEMENTS"][0]["IBLOCK_NAME"]?>:</b>
		<ul>
	<?foreach($arResult["LINKED_ELEMENTS"] as $arElement):?>
		<li><a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><?=$arElement["NAME"]?></a></li>
	<?endforeach;?>
		</ul>
	<?endif?>
	<?
	// additional photos
	$LINE_ELEMENT_COUNT = 2; // number of elements in a row
	if(count($arResult["MORE_PHOTO"])>0):?>
		<a name="more_photo"></a>
		<?foreach($arResult["MORE_PHOTO"] as $PHOTO):?>
			<img border="0" src="<?=$PHOTO["SRC"]?>" width="<?=$PHOTO["WIDTH"]?>" height="<?=$PHOTO["HEIGHT"]?>" alt="<?=$PHOTO["ALT"]?>" title="<?=$arResult["NAME"]?>" /><br />
		<?endforeach?>
	<?endif?>
	<?if(is_array($arResult["SECTION"])):?>
		<br /><a href="<?=$arResult["SECTION"]["SECTION_PAGE_URL"]?>"><?=GetMessage("CATALOG_BACK")?></a>
	<?endif?>
		<br><Br><br><br>
</div>
