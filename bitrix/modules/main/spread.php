<?
header("P3P: policyref=\"/bitrix/p3p.xml\", CP=\"NON DSP COR CUR ADM DEV PSA PSD OUR UNR BUS UNI COM NAV INT DEM STA\"");
$cookie = base64_decode($_GET["s"]);
$key = $_GET["k"];
if(strlen($key)>0)
{
	$LICENSE_KEY = "";
	@include($_SERVER["DOCUMENT_ROOT"]."/bitrix/license_key.php");
	if($LICENSE_KEY=="" || strtoupper($LICENSE_KEY)=="DEMO") 
		$LICENSE_KEY = "DEMO";
	if(md5($cookie.$LICENSE_KEY)==$key)
	{
		$arr = explode(chr(2), $cookie);
		if(is_array($arr) && count($arr)>0)
		{
			foreach($arr as $str)
			{
				if(strlen($str)>0)
				{
					$ar = explode(chr(1),$str);
					setcookie($ar[0], $ar[1], $ar[2], $ar[3], $_SERVER["HTTP_HOST"], $ar[5]);
				}
			}
		}
	}
}
?>