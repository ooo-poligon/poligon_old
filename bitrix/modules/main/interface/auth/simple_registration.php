<?ShowMessage($arAuthResult);?>	
<?$s=DeleteParam(array("logout", "simple_registration"));?>

<?if(strlen(${COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_LOGIN"})>0):?>
<?require($GLOBALS["DOCUMENT_ROOT"]."/bitrix/modules/main/interface/auth/simple_reg_auth.php");?>
<?require($GLOBALS["DOCUMENT_ROOT"]."/bitrix/modules/main/interface/auth/simple_reg_reg.php");?>
<script>
<!--
document.bform.USER_PASSWORD.focus();
// -->
</script>
<?else:?>
<?require($GLOBALS["DOCUMENT_ROOT"]."/bitrix/modules/main/interface/auth/simple_reg_reg.php");?>
<?require($GLOBALS["DOCUMENT_ROOT"]."/bitrix/modules/main/interface/auth/simple_reg_auth.php");?>
<script>
<!--
document.rform.USER_EMAIL.focus();
// -->
</script>
<?endif;?>
