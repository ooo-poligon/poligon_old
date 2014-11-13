<?
$ARCHIVE_NAME ="install.gz"; // имя архива
$ABS_PATH = $_SERVER["DOCUMENT_ROOT"];
$DEBUG = "Y";

set_time_limit(0);
ignore_user_abort(true);
$CNT=0;
$ALL_SIZE=0;

function ReqWrite($curpath="")
{
	global $ABS_PATH, $token, $zp, $ALL_SIZE, $CNT, $ARCHIVE_NAME, $DEBUG;

	$abs_path = $ABS_PATH.$curpath;
	$handle  = @opendir($abs_path);
	while($file = @readdir($handle)) 
	{
	       if ($file == "." || $file == "..") continue;

		if(is_dir($abs_path."/".$file))
			ReqWrite($curpath."/".$file);
		else
		{
			if ($ABS_PATH."/".$ARCHIVE_NAME!=$abs_path."/".$file)
			{
				$CNT++;
				$size = filesize($abs_path."/".$file);
				$fd = fopen ($abs_path."/".$file, "rb");
				$contents = fread ($fd, $size);
				fclose($fd);

				$ALL_SIZE+=$size;

				$add_info = $size."|".$curpath."/".$file;
				$add_info_size = strlen($add_info);

				gzwrite($zp, str_pad($add_info_size, 10));
				gzwrite($zp, $add_info);
				$sz = gzwrite($zp, $contents);

				if ($DEBUG=="Y") echo $CNT.". ".$abs_path."/".$file."&nbsp;&nbsp;&nbsp;size: [".$sz."]<br>";
			}
		}
	}
	closedir($handle);
}

$zp = gzopen($ABS_PATH."/".$ARCHIVE_NAME, "wb9f");
$token = md5(uniqid(rand(),1));
ReqWrite();
gzclose($zp);
?>
-- Done --<br>
Files: <?echo $CNT;?><br>
Size: <?echo $ALL_SIZE;?>