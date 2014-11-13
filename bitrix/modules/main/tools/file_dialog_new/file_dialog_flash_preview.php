<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");
if(!$USER->CanDoOperation('fileman_view_file_structure'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
if(CModule::IncludeModule("compression"))
	CCompress::Disable2048Spaces();

if (isset($_GET['path']))
{
	if (strtolower(substr($path, 0, 7)) != 'http://' && strtolower(substr($path, 0, 4)) != 'www.')
	{
		$path = Rel2Abs("/", $path);
		$arPath = Array($site, $path);
		if(!$USER->CanDoFileOperation('fm_view_file', $arPath))
		{
			ShowError($strWarning);
			die();
		}
	}
}
else
	$path = "javascript:''";

$width = isset($width) ? 'width="'.htmlspecialchars($width).'"' : '';
$height = isset($height) ? 'height="'.htmlspecialchars($height).'"' : '';
?>
<HTML>
<HEAD></HEAD>
<BODY id="__flash" style="margin:0px; border:0px solid red;">
<embed
id="__flash_preview"
pluginspage="http://www.macromedia.com/go/getflashplayer"
type="application/x-shockwave-flash"
name="__flash_preview"
quality="high"
<?=$width?>
<?=$height?>
src="<?=$path?>"
/>
</BODY>
</HTML>