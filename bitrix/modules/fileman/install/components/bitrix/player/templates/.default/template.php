<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if ($arResult["PLAYER_TYPE"] == "flv"): // Attach Flash Player?>
<div id="<?=$arResult["ID"]?>" style="display:none">
<embed
	src="/bitrix/components/bitrix/player/mediaplayer/player.swf"
	width="<?=$arResult['WIDTH']?>"
	height="<?=$arResult['HEIGHT']?>"
	allowscriptaccess="always"
	allowfullscreen="true"
	menu="<?=$arResult['MENU']?>"
	wmode="<?=$arResult['WMODE']?>"
	flashvars="<?=$arResult['FLASH_VARS']?>"
/>
</div>
<?$GLOBALS['APPLICATION']->AddHeadString('<script type="text/javascript" src="/bitrix/components/bitrix/player/mediaplayer/flvscript.js?v='.filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/player/mediaplayer/flvscript.js').'"></script>', true);?>
<script>
showFLVPlayer('<?=$arResult["ID"]?>', "<?=GetMessage('INSTALL_FLASH_PLAYER')?>");
</script><noscript><?=GetMessage('ENABLE_JAVASCRIPT')?></noscript>
<?elseif ($arResult["PLAYER_TYPE"] == "wmv"): // Attach WMV Player?>
<?
$GLOBALS['APPLICATION']->AddHeadString('<script type="text/javascript" src="/bitrix/components/bitrix/player/wmvplayer/silverlight.js?v='.filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/player/wmvplayer/silverlight.js').'"></script>', true);
$GLOBALS['APPLICATION']->AddHeadString('<script type="text/javascript" src="/bitrix/components/bitrix/player/wmvplayer/wmvplayer.js?v='.filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/player/wmvplayer/wmvplayer.js').'"></script>', true);
if ($arResult['USE_JS_PLAYLIST'])
{
	?><script>var JSMESS = {ClickToPLay : "<?=GetMessage('JS_CLICKTOPLAY')?>",Link : "<?=GetMessage('JS_LINK')?>", PlayListError: "<?=GetMessage('JS_PLAYLISTERROR')?>"};</script><?
	$GLOBALS['APPLICATION']->AddHeadString('<script type="text/javascript" src="/bitrix/components/bitrix/player/templates/.default/wmvscript_playlist.js?v='.filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/player/templates/.default/wmvscript_playlist.js').'"></script>', true);
	$GLOBALS['APPLICATION']->AddHeadString('<link rel="stylesheet" type="text/css" href="/bitrix/components/bitrix/player/templates/.default/wmvplaylist.css?v='.filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/player/templates/.default/wmvplaylist.css').'"></script>', true);
}
else
{
	$GLOBALS['APPLICATION']->AddHeadString('<script type="text/javascript" src="/bitrix/components/bitrix/player/wmvplayer/wmvscript.js?v='.filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/player/wmvplayer/wmvscript.js').'"></script>', true);
}
?>

<div id="<?=$arResult["ID"]?>"></div>
<script>
showWMVPlayer('<?=$arResult["ID"]?>', <?=$arResult['WMV_CONFIG']?>, <?=($arResult['PLAYLIST_CONFIG'] ? $arResult['PLAYLIST_CONFIG'] : '{}')?>);
</script><noscript><?=GetMessage('ENABLE_JAVASCRIPT')?></noscript>
<?endif;?>