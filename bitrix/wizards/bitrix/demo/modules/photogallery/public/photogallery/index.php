<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle(GetMessage("P_GALLERY"));
?><?$APPLICATION->IncludeComponent(
	"bitrix:photogallery",
	"",
	Array(
		"IBLOCK_TYPE" => "gallery", 
		"IBLOCK_ID" => "#IBLOCK_ID#", 
		
		"SEF_MODE" => "N", 
		
		"USE_RATING" => "Y", 
		
		"USE_COMMENTS" => "#USE_COMMENTS#", 
		"COMMENTS_TYPE" => "#COMMENTS_TYPE#", 
		"FORUM_ID" => "#FORUM_ID#",
		"BLOG_URL" => "#BLOG_URL#",
		"PATH_TO_SMILE" => "#PATH_TO_SMILE#",
		
		"SHOW_TAGS"	=>	"Y",
		"TAGS_PAGE_ELEMENTS"	=>	"150",
		"TAGS_PERIOD"	=>	"",
		"TAGS_INHERIT"	=>	"Y",
		"TAGS_FONT_MAX"	=>	"30",
		"TAGS_FONT_MIN"	=>	"10",
		"TAGS_COLOR_NEW"	=>	"3E74E6",
		"TAGS_COLOR_OLD"	=>	"C0C0C0",
		"TAGS_SHOW_CHAIN"	=>	"Y",
		
		"TEMPLATE_LIST" => ".default", 
		"PAGE_NAVIGATION_TEMPLATE" => "", 
		
		"ALBUM_PHOTO_THUMBS_SIZE" => "100", 
		"ALBUM_PHOTO_SIZE" => "100", 
		"THUMBS_SIZE" => "120", 
		"PREVIEW_SIZE" => "500", 
		"WATERMARK_COLORS" => Array("FF0000","FFFF00","FFFFFF","000000","",""), 
		"SHOW_LINK_ON_MAIN_PAGE" => Array("id","shows","rating","comments"), 
		"SHOW_ON_MAIN_PAGE" => "none", 
		"SHOW_ON_MAIN_PAGE_POSITION" => "left", 
		"SHOW_ON_MAIN_PAGE_TYPE" => "none", 
		"SHOW_ON_MAIN_PAGE_COUNT" => "", 
		"SHOW_PHOTO_ON_DETAIL_LIST" => "none", 
		"SHOW_PHOTO_ON_DETAIL_LIST_COUNT" => "500", 
		
		"CACHE_TYPE" => "A", 
		"CACHE_TIME" => "3600", 
	)
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>