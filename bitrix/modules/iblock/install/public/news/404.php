<?
define("AUTH_404","Y");
header($_SERVER["SERVER_PROTOCOL"]." 200 OK");
$arrPath = pathinfo($_SERVER["REQUEST_URI"]);
$params = "";
if(($p=strpos($_SERVER["REQUEST_URI"], "?"))!==false)
{
	$params = substr($_SERVER["REQUEST_URI"], $p+1);
}
parse_str($params, $_GET);
extract($_GET, EXTR_SKIP);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$arr = explode("?",$arrPath["basename"]);
$fname = $arr[0];
if (strlen(trim($arrPath["extension"]))>0)
{
	$arr = explode(".",$fname);
	$ID = intval($arr[0]);
	?>
	<?$APPLICATION->IncludeFile("iblock/news/detail.php", Array(
	"ID" => $ID,
	"IBLOCK_TYPE" => "news",
	"IBLOCK_ID" => "1",
	"arrPROPERTY_CODE" => Array(
		"AUTHOR",
		"SOURCE"
	),
	"LIST_PAGE_URL" => "#SITE_DIR#about/news/",
	"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
	"CACHE_TIME" => "0",
	"DISPLAY_PANEL" => "Y",
	)
);?><?
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>