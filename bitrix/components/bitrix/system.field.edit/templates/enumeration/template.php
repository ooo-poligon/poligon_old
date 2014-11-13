<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$bWasSelect = false;

if($arUserField["SETTINGS"]["DISPLAY"]!="CHECKBOX"):
	?><select name="<?=$arParams["arUserField"]["FIELD_NAME"]?>" size="<?=$arParams["arUserField"]["SETTINGS"]["LIST_HEIGHT"]
	?>" <?
	if ($arParams["arUserField"]["MULTIPLE"]=="Y"):
	?> multiple="multiple"<?
	endif;
	?>><?
endif;
foreach ($arParams["arUserField"]["USER_TYPE"]["FIELDS"] as $key => $val)
{

	$bSelected = in_array($key, $arResult["VALUE"]) && (
		(!$bWasSelect) ||
		($arParams["arUserField"]["MULTIPLE"] == "Y")
	);
	$bWasSelect = $bWasSelect || $bSelected;

	if($arUserField["SETTINGS"]["DISPLAY"]=="CHECKBOX")
	{
		?><label><input type="radio" value="<?echo $key?>" name="<?echo $arParams["arUserField"]["FIELD_NAME"]?>"<?echo ($bSelected? " checked" : "")?><?
		if ($arParams["arUserField"]["MULTIPLE"]=="Y"):
		?> multiple="multiple"<?
		endif;
		?>><?=$val?></label><br /><?
	}
	else
	{
		?><option value="<?echo $key?>"<?echo ($bSelected? " selected" : "")?>><?echo $val?></option><?
	}
}
if($arUserField["SETTINGS"]["DISPLAY"]!="CHECKBOX"):
?></select><?
endif;?>