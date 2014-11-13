<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");
if (!$USER->CanDoOperation('fileman_view_file_structure'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
$APPLICATION->SetAdditionalCSS("/bitrix/modules/fileman/fileman_admin.css");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
IncludeModuleLangFile(__FILE__);
?>
<HTML>
<HEAD>
<STYLE TYPE="text/css">
BODY   {margin:0px; padding:0px; font-family:Arial; font-size:9px; background:buttonface; border:0px;}
TD.text {font-size:3px; }
<?
if (strlen($APPLICATION->GetAdditionalCSS())>0)
	require($_SERVER["DOCUMENT_ROOT"].$APPLICATION->GetAdditionalCSS());
?>
</STYLE>
<meta http-equiv="Content-Type" content="text/html; charset=<?echo LANG_CHARSET?>">
</HEAD>
<BODY>
<table width="100%" border="0" cellpadding="1">
<?
if(is_set($_REQUEST, 'component_type'))
	$_SESSION['FILEMAN_COMPONENT_TYPE'] = $_REQUEST['component_type'];

$arTemplateFolders = CTemplates::GetFolderList($template);
$arTemplates = CTemplates::GetList(Array("FOLDER"=>Array($_REQUEST['component_type'])), Array(), $template);
$templates = $arTemplates[$_REQUEST['component_type']];
if(is_array($templates))
{
	foreach($templates as $path=>$arParams)
	{
		if($arParams["SEPARATOR"]=="Y"):
			?>
			<tr valign="top">
				<td style="font-size:11px;" colspan="2"><b><span title="<?=htmlspecialcharsex($arParams["DESCRIPTION"])?>"><?=htmlspecialcharsex($arParams["NAME"])?></span></b></td>
			</tr>
			<?
		else:
			if(strlen($arParams["ICON"])<=0 || !is_file($_SERVER["DOCUMENT_ROOT"].$arParams["ICON"]))
				$arParams["ICON"] = "/bitrix/images/fileman/htmledit/component.gif";
			if(strlen($arParams["SCRIPT"])<=0 || !is_file($_SERVER["DOCUMENT_ROOT"].$arParams["SCRIPT"]))
				$arParams["SCRIPT"] = "standart";
			?>
			<tr valign="top">
				<td><img src="<?=$arParams["ICON"]?>" alt="<?=htmlspecialcharsex($arParams["DESCRIPTION"])?>" scraction="<?=$arParams["SCRIPT"]?>" scredit="<?=htmlspecialchars($arParams["PATH_EDIT"])?>" scrid="<?=$path?>" style="cursor:hand;" ondragstart="this.id=Math.random()" ondragend="parent.component_dropped(this.id)"></td>
				<td style="font-size:11px;"><span title="<?=htmlspecialcharsex($arParams["DESCRIPTION"])?>"><?=htmlspecialcharsex($arParams["NAME"])?></span></td>
			</tr>
			<?
		endif;
	}
}
?>
<tr>
	<td colspan="2">
		<hr size="1">
	</td>
</tr>
<tr>
	<td><img src="/bitrix/images/fileman/htmledit/php.gif" scraction="phpscript" style="cursor:hand;" ondragstart="this.id=Math.random()" ondragend="parent.component_dropped(this.id)"></td>
	<td style="font-size:11px;"><?echo GetMessage("FILEMAN_HTML_COMP_PHP")?></td>
</tr>
</table>
</BODY>
</HTML>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>
