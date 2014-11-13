<?
// hosts for balancing of the downloads with probability: 70, 50
/*
$arrHOSTS = array(
	"70" => "http://download1.agnitum.com", 
	"50" => "http://download2.agnitum.com"
);
*/

// During which time interval the download events of the same user won't be registered in statistics. 
$DOWNLOAD_EVENT_INTERVAL = 21600; // sec.


function initialize_params($url)
{
	if (strpos($url,"?")>0)
	{
		$par = substr($url,strpos($url,"?")+1,strlen($url));
		$arr = explode("#",$par);
		$par = $arr[0];
		$arr1 = explode("&",$par);
		foreach ($arr1 as $pair)
		{
			$arr2 = explode("=",$pair);
			global $$arr2[0];
			$$arr2[0] = $arr2[1];
		}
	}
}

$arr1 = explode("?",$_SERVER["REQUEST_URI"]); 
$URI = $arr1[0];
$path = dirname($URI);
$file = basename($URI);
$path .= "/files/";

if(file_exists($_SERVER["DOCUMENT_ROOT"].$path.$file))
{
	set_time_limit(0);
	session_cache_limiter('');
	session_start();
	initialize_params($_SERVER["REQUEST_URI"]);

	$cur_pos = 0;
	$p = strpos($_SERVER["HTTP_RANGE"], "=");
	if($p>0)
	{
		$bytes = substr($_SERVER["HTTP_RANGE"], $p+1);
		$p = strpos($bytes, "-");
		if($p!==false) $cur_pos = intval(substr($bytes, 0, $p));
	}
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
	if (CModule::IncludeModule("statistic"))
	{
		if ($cur_pos<=0) // check if the file is being downloaded from the begining
		{
			if (strlen($event1)<=0 && strlen($event2)<=0)
			{
				$event1 = "download";
				$event2 = $file;
			}
			$e = $event1."/".$event2."/".$event3;
			if (!in_array($e, $_SESSION["DOWNLOAD_EVENTS"])) // check if there was download attempt in this session 
			{
				$w = CStatEvent::GetByEvents($event1, $event2);
				$wr = $w->Fetch();
				$z = CStatEvent::GetEventsByGuest($_SESSION["SESS_GUEST_ID"], $wr["EVENT_ID"], $event3, $DOWNLOAD_EVENT_INTERVAL);
				if (!($zr=$z->Fetch())) // check if there was download attempt by this user during tha last 6 hours
				{
					CStatistic::Set_Event($event1, $event2, $event3);
					$_SESSION["DOWNLOAD_EVENTS"][] = $e;
				}
			}
		}
	}
	ob_end_clean();
	session_write_close();

	while (list($key,$value)=each($arrHOSTS)) $max_weight += intval($key);
	mt_srand ((double) microtime() * 1000000); 
	$rand = $max_weight*(rand()/getrandmax());
	reset($arrHOSTS);
	while (list($key,$value)=each($arrHOSTS))
	{
		if($rand>=$step && $rand<=$step+intval($key))
		{
			$host=$value; 
			break;
		}
		$step += intval($key);
	}
	if (strlen($host)<=0) $host="http://".$_SERVER["HTTP_HOST"];

	header("Request-URI: ".$host.$path.$file); 
	header("Content-Location: ".$host.$path.$file); 
	header("Location: ".$host.$path.$file); 
}
else
{
	include($_SERVER["DOCUMENT_ROOT"]."/404.php");
}
?>