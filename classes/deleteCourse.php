<?php
/**
	удаление по крону курса евро за прошлый день
*/
error_reporting(E_ALL);
ini_set('display_errors', 1);
$mode = NULL;
// запрет запуска по http
if (PHP_SAPI == 'cli'){
	$filename = getcwd().'/www/poligon.info/upload/course.euro';
	if(file_exists($filename)){
		print "Found: ";
		if(unlink($filename))
			print "File {$filename} is delete! ";
		else
			print "Fail!!";		
	}else {
		print "not found $filename";
	}
}