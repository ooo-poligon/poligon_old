<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title><?echo $APPLICATION->GetTitle()?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?echo LANG_CHARSET?>">
<script language="JavaScript">
<!--
function KeyPress()
{
	if(window.event.keyCode == 27)
		window.close();
}
//-->
</script>
<style type="text/css">
<?
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/admin_styles.css");
if (strlen($APPLICATION->GetAdditionalCSS())>0)
{
	require($_SERVER["DOCUMENT_ROOT"].$APPLICATION->GetAdditionalCSS());
}
?>
</style>
</head>
<body bgcolor="#FFFFFF" text="#000000" style="padding:10px;" link="#0487D6" alink="#FF0000" vlink="#0487D6" onKeyPress="KeyPress()"">