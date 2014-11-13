<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(!CModule::IncludeModule('blog'))
	return;
__IncludeLang(GetLangFileName(dirname(__FILE__)."/lang/", "/".basename(__FILE__)));

$siteID = $arParams["site_id"];
if(strlen($arParams["site_id"]) <= 0)
	$siteID = "s1";

$dbSite = CSite::GetByID($siteID);
if($arSite = $dbSite -> Fetch())
	$LID = $arSite["LANGUAGE_ID"];
if(strlen($LID) <= 0)
	$LID = "ru";
	
$dbGroup = CBlogGroup::GetList(Array("ID" => "ASC"));
if(!$dbGroup->Fetch())
{
	COption::SetOptionString('blog','avatar_max_size','30000');
	COption::SetOptionString('blog','avatar_max_width','100');
	COption::SetOptionString('blog','avatar_max_height','100');
	COption::SetOptionString('blog','image_max_width','600');
	COption::SetOptionString('blog','image_max_height','600');
	COption::SetOptionString('blog','allow_alias','Y');
	COption::SetOptionString('blog','block_url_change','Y');
	COption::SetOptionString('blog','GROUP_DEFAULT_RIGHT','D');
	COption::SetOptionString('blog','show_ip','Y');
	COption::SetOptionString('blog','enable_trackback','N');
	COption::SetOptionString('blog','allow_html','N');

	$groupID = CBlogGroup::Add(Array("SITE_ID" => $siteID, "NAME" => GetMessage("BLOG_DEMO_GROUP_1")));
	CBlogGroup::Add(Array("SITE_ID" => $siteID, "NAME" => GetMessage("BLOG_DEMO_GROUP_2")));
	CBlogGroup::Add(Array("SITE_ID" => $siteID, "NAME" => GetMessage("BLOG_DEMO_GROUP_3")));
	CBlogGroup::Add(Array("SITE_ID" => $siteID, "NAME" => GetMessage("BLOG_DEMO_GROUP_4")));
	CBlogGroup::Add(Array("SITE_ID" => $siteID, "NAME" => GetMessage("BLOG_DEMO_GROUP_5")));
	CBlogGroup::Add(Array("SITE_ID" => $siteID, "NAME" => GetMessage("BLOG_DEMO_GROUP_6")));
	CBlogGroup::Add(Array("SITE_ID" => $siteID, "NAME" => GetMessage("BLOG_DEMO_GROUP_7")));

	$blogID = CBlog::Add(
			Array(
			    "NAME" => GetMessage("BLOG_DEMO_BLOG_NAME"),
			    "DESCRIPTION" => GetMessage("BLOG_DEMO_BLOG_NAME"),
			    "GROUP_ID" => $groupID,
			    "ENABLE_IMG_VERIF" => 'Y',
			    "EMAIL_NOTIFY" => 'Y',
			    "ENABLE_RSS" => "Y",
			    "URL" => "admin-blog",
			    "ACTIVE" => "Y",
			    "=DATE_CREATE" => $DB->GetNowFunction(),
				"=DATE_UPDATE" => $DB->GetNowFunction(),
			    "OWNER_ID" => 1,
				"PERMS_POST" => Array("1" => BLOG_PERMS_READ, "2" => BLOG_PERMS_READ), 
				"PERMS_COMMENT" => array("1" => BLOG_PERMS_WRITE , "2" => BLOG_PERMS_WRITE),
			)
		);
	
	$friends = CBlogUserGroup::Add(array(
	    "NAME" => GetMessage("BLOG_DEMO_FRIENDS"),
	    "BLOG_ID" => $blogID
	));

	CBlogUserGroupPerms::Add(
			Array(
				"BLOG_ID" => $blogID,
				"USER_GROUP_ID" => $friends,
				"PERMS_TYPE" => "P",
				"PERMS" => "I",
				"AUTOSET" => "N",
			)
		);
	CBlogUserGroupPerms::Add(
			Array(
				"BLOG_ID" => $blogID,
				"USER_GROUP_ID" => $friends,
				"PERMS_TYPE" => "C",
				"PERMS" => "P",
				"AUTOSET" => "N",
			)
		);

	$categoryID[] = CBlogCategory::Add(Array("BLOG_ID" => $blogID, "NAME" => GetMessage("BLOG_DEMO_CATEGORY_1")));
	$categoryID[] = CBlogCategory::Add(Array("BLOG_ID" => $blogID, "NAME" => GetMessage("BLOG_DEMO_CATEGORY_2")));

	$postID = CBlogPost::Add(
			Array(
			    "TITLE" => GetMessage("BLOG_DEMO_MESSAGE_TITLE_1"),
			    "DETAIL_TEXT" => GetMessage("BLOG_DEMO_MESSAGE_BODY_1"),
				"DETAIL_TEXT_TYPE" => "text",
			    "BLOG_ID" => $blogID,
			    "AUTHOR_ID" => 1,
				"=DATE_CREATE" => $DB->GetNowFunction(),
			    "=DATE_PUBLISH" => $DB->GetNowFunction(),
			    "PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH,
			    "ENABLE_TRACKBACK" => 'N',
			    "ENABLE_COMMENTS" => 'Y',
			    "CATEGORY_ID" =>  implode(",", $categoryID),
			    "PERMS_P" => Array(1 => BLOG_PERMS_READ, 2 => BLOG_PERMS_READ),
			    "PERMS_C" => Array(1 => BLOG_PERMS_WRITE, 2 => BLOG_PERMS_WRITE)
			)
		);
	foreach($categoryID as $v)
		CBlogPostCategory::Add(Array("BLOG_ID" => $blogID, "POST_ID" => $postID, "CATEGORY_ID"=>$v));
	CBlogComment::Add(Array(
		    "TITLE" => GetMessage("BLOG_DEMO_COMMENT_TITLE"),
		    "POST_TEXT" => GetMessage("BLOG_DEMO_COMMENT_BODY"),
		    "BLOG_ID" => $blogID,
		    "POST_ID" => $postID,
		    "PARENT_ID" => 0,
		    "AUTHOR_ID" => 1,
		    "DATE_CREATE" => ConvertTimeStamp(false, "FULL"), 
		    "AUTHOR_IP" => "192.168.0.108",
		));

	CBlogSitePath::Add(Array("SITE_ID" => $siteID, "PATH" => "/communication/blog/index.php?page=blog&blog=#blog#", "TYPE" => "B"));
	CBlogSitePath::Add(Array("SITE_ID" => $siteID, "PATH" => "/communication/blog/index.php?page=post&blog=#blog#&post_id=#post_id#", "TYPE" => "P"));
	CBlogSitePath::Add(Array("SITE_ID" => $siteID, "PATH" => "/communication/blog/index.php?page=user&user_id=#user_id#", "TYPE" => "U"));
}


DemoSiteUtil::AddMenuItem("/communication/.left.menu.php", Array(
	GetMessage("BLOG_DEMO_LEFT_MENU_1"), 
	"/communication/blog/", 
	Array(), 
	Array(), 
	"" 
));
DemoSiteUtil::AddMenuItem("/communication/blog/.left.menu.php", Array(
	GetMessage("BLOG_DEMO_LEFT_MENU_2"), 
	"/communication/blog/index.php", 
	Array(), 
	Array(), 
	"" 
));	
DemoSiteUtil::AddMenuItem("/communication/blog/.left.menu.php", Array(
	GetMessage("BLOG_DEMO_LEFT_MENU_3"), 
	"/communication/blog/weblogs/", 
	Array(), 
	Array(), 
	"" 
));

$source_base = dirname(__FILE__);
CopyDirFiles($source_base."/public/".$LID, $_SERVER["DOCUMENT_ROOT"]."/communication/blog", false, true);
	
if (!function_exists("file_get_contents"))
{
	function file_get_contents($filename)
	{
		$fd = fopen("$filename", "rb");
		$content = fread($fd, filesize($filename));
		fclose($fd);
		return $content;
	}
}

$file = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/communication/blog/blog_sef.php");
if ($file)
{
	$file = str_replace("#SEF_FOLDER#", "/communication/blog/weblogs/", $file);
	if ($f = fopen($_SERVER["DOCUMENT_ROOT"]."/communication/blog/blog_sef.php", "w"))
	{
		@fwrite($f, $file);
		@fclose($f);
	}
}

$arFields = array(
	"CONDITION" => "#^/communication/blog/weblogs/#",
	"RULE" => "",
	"ID" => "bitrix:blog",
	"PATH" => "/communication/blog/blog_sef.php"
);
CUrlRewriter::Add($arFields);


//Communication section
@include(dirname(__FILE__)."/../communication/install.php");

return true;
?>