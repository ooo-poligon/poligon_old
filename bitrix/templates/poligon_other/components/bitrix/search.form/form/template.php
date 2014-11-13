<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="search-form">
<form action="<?=$arResult["FORM_ACTION"]?>">
	<input type="text" class="inputtext" name="q" value="Введите строку для поиска" onfocus="this.value=''" onblur="if (this.value == '')this.value='Введите строку для поиска'" size="40" maxlength="50" />&nbsp;<input name="s" type="submit" class="submit" value="Найти" />
</form>
</div>