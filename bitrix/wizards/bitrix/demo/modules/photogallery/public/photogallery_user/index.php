<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle(GetMessage("P_GALLERY"));
?><?$APPLICATION->IncludeComponent(
	"bitrix:photogallery_user",
	"",
	Array(
		"IBLOCK_TYPE" => "gallery", 
		"IBLOCK_ID" => "#IBLOCK_ID#", 
		
		"SEF_MODE" => "N", 
		
		"GALLERY_GROUPS" => Array("11"), 
		"ONLY_ONE_GALLERY" => "Y", 
		"SECTION_SORT_BY" => "ID", 
		"SECTION_SORT_ORD" => "ASC", 
		"ELEMENT_SORT_FIELD" => "id", 
		"ELEMENT_SORT_ORDER" => "desc", 
		
		"SECTION_PAGE_ELEMENTS" => "10", 
		"ELEMENTS_PAGE_ELEMENTS" => "100", 
		"PAGE_NAVIGATION_TEMPLATE" => "", 
		"ELEMENTS_USE_DESC_PAGE" => "Y", 
		"DATE_TIME_FORMAT_SECTION" => "d.m.Y", 
		"DATE_TIME_FORMAT_DETAIL" => "d.m.Y", 
		"ALBUM_PHOTO_THUMBS_SIZE" => "100", 
		"ALBUM_PHOTO_SIZE" => "100", 
		"THUMBS_SIZE" => "120", 
		"PREVIEW_SIZE" => "500", 
		"JPEG_QUALITY1" => "95", 
		"JPEG_QUALITY2" => "95", 
		"JPEG_QUALITY" => "90", 
		"WATERMARK_MIN_PICTURE_SIZE" => "200", 
		"ADDITIONAL_SIGHTS" => Array(), 
		"UPLOAD_MAX_FILE" => "2", 
		
		"USE_RATING" => "Y", 
		"MAX_VOTE" => "5", 
		"VOTE_NAMES" => Array("0","1","2","3","4"), 
		
		"SHOW_TAGS" => "Y", 
		"TAGS_PAGE_ELEMENTS" => "50", 
		"TAGS_PERIOD" => "", 
		"TAGS_INHERIT" => "Y", 
		"TAGS_FONT_MAX" => "30", 
		"TAGS_FONT_MIN" => "14", 
		"TAGS_COLOR_NEW" => "486DAA", 
		"TAGS_COLOR_OLD" => "486DAA", 
		"TAGS_SHOW_CHAIN" => "Y", 
		
		"USE_COMMENTS" => "#USE_COMMENTS#", 
		"COMMENTS_TYPE" => "#COMMENTS_TYPE#", 
		"FORUM_ID" => "#FORUM_ID#",
		"BLOG_URL" => "#BLOG_URL#",
		"PATH_TO_SMILE" => "#PATH_TO_SMILE#",
		
		"MODERATE" => "Y", 
		"SHOW_ONLY_PUBLIC" => "Y", 
		
		"DISPLAY_PANEL" => "N", 
		"SET_TITLE" => "Y", 
		"CACHE_TYPE" => "A", 
		"CACHE_TIME" => "3600", 
		
		"WATERMARK_COLORS" => Array("FF0000","FFFF00","FFFFFF","000000","",""), 
		"TEMPLATE_LIST" => ".default", 
		"CELL_COUNT" => "0", 
		"SLIDER_COUNT_CELL" => "4", 
		
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>