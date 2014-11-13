<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(strlen($arResult["errorMessage"]) > 0)
	ShowError($arResult["errorMessage"]);

?><h3><?=GetMessage("SAP_BUY_MONEY")?></h3>
<form method="post" name="buyMoney" action="">
<?
if(strlen($arResult["CURRENT_PAGE"]) > 0)
{
	?><input type="hidden" name="CURRENT_PAGE" value="<?=$arResult["CURRENT_PAGE"]?>"><?
}
foreach($arResult["AMOUNT_TO_SHOW"] as $v)
{
	?><input type="radio" name="<?=$arParams["VAR"]?>" value="<?=$v["ID"]?>"><?=$v["NAME"]?><br /><?
}
?>
<input type="submit" name="button" value="<?=GetMessage("SAP_BUTTON")?>">
</form>