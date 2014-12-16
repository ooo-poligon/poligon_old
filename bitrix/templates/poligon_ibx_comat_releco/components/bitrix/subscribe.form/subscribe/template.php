<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<form action="<?=$arResult["FORM_ACTION"]?>">

<input type="text" class="subscribe_inputtext" name="sf_EMAIL" size="20" value="mymail@onsomehost.ru" onfocus="this.value=''"  onblur="if (this.value == '')this.value='mymail@onsomehost.ru'" title="<?=GetMessage("subscr_form_email_title")?>" />
<input type="submit" name="OK" class="subscribe_submit" value="<?=GetMessage("subscr_form_button")?>" />
</form>
