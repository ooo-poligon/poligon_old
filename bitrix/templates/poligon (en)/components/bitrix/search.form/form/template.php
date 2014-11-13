<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="search-form">
<form action="<?=$arResult["FORM_ACTION"]?>">
	<input type="text" class="inputtext" name="q" value="Enter a row for search" onfocus="this.value=''" onblur="if (this.value == '')this.value='Enter a row for search'" size="40" maxlength="50" />&nbsp;<input name="s" type="submit" class="submit" value="Search" />
</form>
</div>