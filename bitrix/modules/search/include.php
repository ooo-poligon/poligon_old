<?
global $DBType;

if(!defined("CACHED_b_search_tags")) define("CACHED_b_search_tags", 3600);
if(!defined("CACHED_b_search_tags_len")) define("CACHED_b_search_tags_len", 2);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/search/classes/".$DBType."/search.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/search/classes/".$DBType."/sitemap.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/search/classes/general/customrank.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/search/classes/general/tags.php");
?>