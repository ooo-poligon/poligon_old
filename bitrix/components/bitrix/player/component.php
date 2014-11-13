<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$player_type = $arParams['PLAYER_TYPE'];
$fp = $arParams['PATH'];
if ($player_type == 'auto')
{
	if (strlen($fp) > 0 && strpos($fp, '.') !== false)
	{
		$ext = strtolower(GetFileExtension($fp));
		$player_type = (in_array($ext, array('wmv', 'wma'))) ? 'wmv' : 'flv';
	}
	else
	{
		$player_type = 'flv';
	}
}

if (!function_exists(escapeFlashvar))
{
	function escapeFlashvar($str)
	{
		$str = str_replace('?', '%3F', $str);
		$str = str_replace('=', '%3D', $str);
		$str = str_replace('&', '%26', $str);
		return $str;
	}

	function isYes($str)
	{
		if (strtoupper($str) == 'Y')
			return 'true';
		return 'false';
	}

	function addFlashvar(&$str, $key, $value, $default)
	{
		if (!isset($value) || $value == '' || $value == $default)
			return;
		if (strlen($str) > 0)
			$str .= '&';
		$str .= $key.'='.escapeFlashvar($value);
	}

	function addWMVJSConfig(&$str, $key, $value, $default)
	{
		if (!isset($value) || $value == '' || $value == $default)
			return;
		if ($str != '{')
			$str .= ',';
		$str .= $key.': \''.$value.'\'';
	}

	function findCorrectFile($path, &$strWarn, $warning = false)
	{
		if (strpos($path, '://') !== false)
			return $path;
		$DOC_ROOT = $_SERVER["DOCUMENT_ROOT"];
		$path = Rel2Abs("/", $path);
		if (!file_exists($DOC_ROOT.$path))
			$path = rtrim($GLOBALS['APPLICATION']->GetCurDir(), "/").$path;
		if (!file_exists($DOC_ROOT.$path))
		{
			if ($warning)
				$strWarn .= $warning."<br />";
			$path = '';
		}
		return $path;
	}
}

$warning = '';
$arResult["WIDTH"] = intval($arParams['WIDTH']);
if ($arResult["WIDTH"] <= 0)
	$arResult["WIDTH"] = 400;

$arResult["HEIGHT"] = intval($arParams['HEIGHT']);
if ($arResult["HEIGHT"] <= 0)
	$arResult["HEIGHT"] = 300;

$path = findCorrectFile($arParams['PATH'], $warning, GetMessage("INCORRECT_FILE"));
$preview = (strlen($arParams['PREVIEW'])) ? findCorrectFile($arParams['PREVIEW'], $w = '') : '';
$logo = (strlen($arParams['LOGO']) > 0) ? findCorrectFile($arParams['LOGO'], $w = '') : '';

if (strlen($warning) > 0)
{
	ShowError($warning);
	return;
}

if (intval($arParams['VOLUME']) > 100)
	$arParams['VOLUME'] = 100;
if (intval($arParams['VOLUME']) < 0)
	$arParams['VOLUME'] = 0;
$arResult["ID"] = "bx_".$player_type."_player_".rand();

if ($player_type == 'flv') // FLASH PLAYER
{
	$fv = '';
	addFlashvar($fv, 'file', $path, null);
	addFlashvar($fv, 'image', $preview, '');
	addFlashvar($fv, 'logo', $logo, '');
	addFlashvar($fv, 'fullscreen', isYes($arParams['FULLSCREEN']), 'false');
	$skin = rtrim($arParams['SKIN_PATH'], "/")."/".$arParams['SKIN'];
	if ($arParams['SKIN'] != '' && $arParams['SKIN'] != 'default' &&
	file_exists($_SERVER["DOCUMENT_ROOT"].$skin) &&
	strtolower(GetFileExtension($arParams['SKIN'])) == 'swf')
		addFlashvar($fv, 'skin', $skin, '');
	addFlashvar($fv, 'controlbar', $arParams['CONTROLBAR'], 'bottom');
	addFlashvar($fv, 'playlist', $arParams['PLAYLIST'], 'none');
	addFlashvar($fv, 'playlistsize', $arParams['PLAYLIST_SIZE'], '180');
	addFlashvar($fv, 'autostart', isYes($arParams['AUTOSTART']), 'false');
	addFlashvar($fv, 'repeat', isYes($arParams['REPEAT']), 'false');
	addFlashvar($fv, 'volume', $arParams['VOLUME'], 90);
	addFlashvar($fv, 'displayclick', $arParams['DISPLAY_CLICK'], 'play');
	addFlashvar($fv, 'mute', isYes($arParams['MUTE']), 'false');
	addFlashvar($fv, 'quality', isYes($arParams['HIGH_QUALITY']), 'true');
	addFlashvar($fv, 'shuffle', isYes($arParams['SHUFFLE']), 'false');
	addFlashvar($fv, 'item', $arParams['START_ITEM'], '0');
	addFlashvar($fv, 'bufferlength', $arParams['BUFFER_LENGTH'], '1');
	addFlashvar($fv, 'link', $arParams['DOWNLOAD_LINK'], '');
	addFlashvar($fv, 'linktarget', $arParams['DOWNLOAD_LINK_TARGET'], '_self');
	addFlashvar($fv, 'abouttext', GetMessage('ABOUT_TEXT'), '');
	addFlashvar($fv, 'aboutlink', GetMessage('ABOUT_LINK'), '');
	$arResult["FLASH_VARS"] = $fv;

	if (!$arParams['CONTROLBAR'] || $arParams['CONTROLBAR'] == 'bottom')
		$arResult["HEIGHT"] += 25;
	$arResult["WMODE"] = $arParams['WMODE'];
	$arResult["MENU"] = $arParams['HIDE_MENU'] == 'Y' ? 'false' : 'true';
}
else // WMV PLAYER
{
	$conf = "{";
	addWMVJSConfig($conf, 'file', $path, '');
	addWMVJSConfig($conf, 'image', $preview, '');
	addWMVJSConfig($conf, 'logo', $logo, '');
	addWMVJSConfig($conf, 'width', $arResult["WIDTH"], 320);
	addWMVJSConfig($conf, 'height', $arResult["HEIGHT"], 260);
	addWMVJSConfig($conf, 'backcolor', $arParams["CONTROLS_BGCOLOR"], 'FFFFFF');
	addWMVJSConfig($conf, 'frontcolor', $arParams["CONTROLS_COLOR"], '000000');
	addWMVJSConfig($conf, 'lightcolor', $arParams["CONTROLS_OVER_COLOR"], '000000');
	addWMVJSConfig($conf, 'screencolor', $arParams["SCREEN_COLOR"], '000000');
	//addWMVJSConfig($conf, 'showicons', isYes($arParams["SHOWICONS"]), 'true');
	//overstretch (false): Sets how to stretch images/movies to make them fit the display. The default stretches to fit the display. Set this to true to stretch them proportionally to fill the display, fit to stretch them disproportionally and none to keep original dimensions.
	//addWMVJSConfig($conf, 'overstretch', isYes($arParams["KEEP_PROPORTION"]), 'true');
	addWMVJSConfig($conf, 'shownavigation', isYes($arParams["SHOW_CONTROLS"]), 'true');
	addWMVJSConfig($conf, 'usefullscreen', isYes($arParams["FULLSCREEN"]), 'true');
	addWMVJSConfig($conf, 'showstop', isYes($arParams["SHOW_STOP"]), 'false');
	addWMVJSConfig($conf, 'showdigits', isYes($arParams["SHOW_DIGITS"]), 'true');
	//showdownload (false): Set this to true to show a button in the player controlbar which links to the link flashvar.
	addWMVJSConfig($conf, 'autostart', isYes($arParams["AUTOSTART"]), 'false');
	addWMVJSConfig($conf, 'repeat', isYes($arParams["REPEAT"]), 'false');
	addWMVJSConfig($conf, 'volume', $arParams['VOLUME'], 80);
	addWMVJSConfig($conf, 'bufferlength', $arParams['BUFFER_LENGTH'], 3);
	addWMVJSConfig($conf, 'link', $arParams['DOWNLOAD_LINK'], '');
	addWMVJSConfig($conf, 'linktarget', $arParams['DOWNLOAD_LINK_TARGET'], '_self');
	//linkfromdisplay (false): Set this to true to make a click on the display result in a jump to the webpage assigned to the link flashvar.
	$conf .= "}";
	$arResult["WMV_CONFIG"] = $conf;
	if ($arParams["SHOW_CONTROLS"] == 'Y')
		$arResult["HEIGHT"] += 20;

	$arResult["USE_JS_PLAYLIST"] = (($arParams["USE_PLAYLIST"] == 'Y'));
	$playlist_conf = false;
	if ($arResult["USE_JS_PLAYLIST"])
	{
		$playlist_conf = '{';
		addWMVJSConfig($playlist_conf, 'format', $arParams['PLAYLIST_TYPE'], 'xspf');
		addWMVJSConfig($playlist_conf, 'size', $arParams['PLAYLIST_SIZE'], '180');
		addWMVJSConfig($playlist_conf, 'image_height', $arParams['PLAYLIST_PREVIEW_HEIGHT'], 48);
		addWMVJSConfig($playlist_conf, 'image_width', $arParams['PLAYLIST_PREVIEW_WIDTH'], 64);
		addWMVJSConfig($playlist_conf, 'position', $arParams['PLAYLIST'] == 'right' ? 'right' : 'bottom', 'right');
		addWMVJSConfig($playlist_conf, 'path', $path, '');
		$playlist_conf .= "}";
	}
	$arResult["PLAYLIST_CONFIG"] = $playlist_conf;
}

$arResult["PLAYER_TYPE"] = $player_type;
$this->IncludeComponentTemplate();
?>