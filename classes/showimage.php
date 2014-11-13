<?php
/**
@since 27/10/2011
@autor Nikolay Gnato
@ver 1.0
@ver 1.1 при болшем шрифте добавляется некоторое кол-во пикселей по вертикали, иначе не влезает надпись
скрипт отдаёт вертикальное изображение с надписью, переданной гет-параметром
	TODO довести до ума: 
	1. кэширование
	2. поддержка русского языка
 */

$text = str_ireplace('_', ' ', key($_GET));
if($text == 'text')
	$text = $_GET['text'];

$x = 14; 
if($_GET['x'])
	$x = (int) $_GET['x'];

$y = strlen($text)*8;
if($_GET['y'])
	$y = (int) $_GET['y'];

$font = 3;
if($_GET['font']){
	$font = (int) $_GET['font'];
	// add in ver 1.1
	$y += $font*3;
}

$newImg = imagecreate($x, $y) or die ("Cannot Initialize new GD image stream");
$background_color = imagecolorallocatealpha ($newImg, 255, 255, 255, 127);
$text_color = imagecolorallocate ($newImg, 0, 0, 0);
imagestringup($newImg, $font, 0, $y-2, $text, $text_color);
//$newImg = imagerotate($newImg, 90, $background_color);
header ("Content-type: image/png");
imagepng($newImg);
