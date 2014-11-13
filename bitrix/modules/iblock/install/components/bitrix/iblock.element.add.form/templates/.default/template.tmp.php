<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
?>
<?
//echo "<pre>Template arParams: "; print_r($arParams); echo "</pre>";
echo "<pre>Template arResult: "; print_r($arResult); echo "</pre>";
?>
<form action="?edit" method="POST">
	<?=bitrix_sessid_post()?>
	<?if ($arParams["ID"] > 0):?><input type="hidden" name="CODE" value="<?=$arParams["ID"]?>" /><?endif?>
	<table class="data-table">
		<thead>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
		</thead>
		<?if (is_array($arParams["PROPERTY_CODES"])):?>
		<tbody>
			<?foreach ($arParams["PROPERTY_CODES"] as $property):?>
			<tr>
				<td><?if (intval($property) > 0):?><?=$arResult["PROPERTIES_FULL"][$property]["NAME"]?><?else:?><?=GetMessage("IBLOCK_FORM_TITLE_".$property)?><?endif?></td>
				<td>
				<?
				//if (intval($property) > 0) $value = $arResult["ELEMENT_PROPERTIES"][$property]["VALUE"];
				//else $value = $arResult["ELEMENT"][$property];
				
				switch ($arResult["PROPERTIES_FULL"][$property]["PROPERTY_TYPE"])
				{
					case "L":
						if ($arParams["PROPERTIES"][$property]["LIST_TYPE"] == "C")
							$type = $arParams["PROPERTIES"][$property]["MULTIPLE"] == "Y" ? "checkbox" : "radio";
						else
							$type = $arParams["PROPERTIES"][$property]["MULTIPLE"] == "Y" ? "multiselect" : "dropdown";

						$arListItems = array();
						foreach ($arResult["ELEMENT_PROPERTIES"] as $key => $arProperty)
						{
							if ($arProperty["ID"] == $property) $arListItems[] = $arProperty;
						}

						switch ($type)
						{
							case "checkbox":
							
								foreach ($arListItems as $key => $arItem)
								{
								?>
									<input type="checkbox" name="PROPERTIES[<?=$property?>][<?=$key?>]" value="<?=$key?>" id="property_<?=$key?>" /><label for="property_<?=$key?>"><?=$arResult["PROPERTIES_FULL"][$property]["ENUM"][$key]["VALUE"]?></label><br />
								<?
								}
							
							break;
						
						
						}
						/*
						foreach ($arResult["PROPERTIES_FULL"][$property]["ENUM"] as $key => $arEnum)
						{
							$name = $type == "checkbox" ? "[".$key."]" : "";
							?>
					<input type="<?=$type?>" name="PROPERTIES[<?=$property?>]<?=$name?>" value="<?=$key?>" id="property_<?=$key?>" /><label for="property_<?=$key?>"><?=$arResult["PROPERTIES_FULL"][$property]["ENUM"][$key]["VALUE"]?></label><br />
							<?
						}
						*/
					break;
					case "T":
					?>

					<textarea name="PROPERTIES[<?=$property?>]"></textarea>
					<?
					break;
					case "S":
					case "N":
					?>
					
					<input type="text" name="PROPERTIES[<?=$property?>]" value="" />
					<?
					break;
					
					case "F":
					?>
					
					<input type="file" name="PROPERTIES[<?=$property?>]" value="" />
					<?if ($property == "PREVIEW_PICTURE"):?>
					<br /><input type="checkbox" name="preview_auto" id="preview_auto" /><label for="preview_auto"><?=GetMessage('IBLOCK_FORM_TITLE_PREVIEW_PICTURE_AUTO')?></label><?endif?>
					<?
					break;
				}
				?></td>
			</tr>
			<?endforeach?>
		</tbody>
		<?endif?>
		<tfoot>
			<tr>
				<td colspan="2">
					<input type="submit" name="submit" value="<?=GetMessage("IBLOCK_FORM_SUBMIT")?>" />
					<input type="submit" name="apply" value="<?=GetMessage("IBLOCK_FORM_APPLY")?>" />
					<input type="reset" value="<?=GetMessage("IBLOCK_FORM_RESET")?>" />
				</td>
			</tr>
		</tfoot>
	</table>
</form>