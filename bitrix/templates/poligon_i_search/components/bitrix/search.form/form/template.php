<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="search-form">
<form action="<?=$arResult["FORM_ACTION"]?>">
	<input type="text" class="inputtext" name="q" value="<?if ($_REQUEST['q']) echo $_REQUEST['q']; else echo '������� ������ ��� ������';?>" onfocus="this.value=''" onblur="if (this.value == '')this.value='������� ������ ��� ������'" size="30" maxlength="50" />
</form>
</div>
