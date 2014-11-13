<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "ООО Полигон»");
$APPLICATION->SetPageProperty("NOT_SHOW_NAV_CHAIN", "Y");
$APPLICATION->SetTitle("Articles");
?> 		
<!--
		<div id="news">
-->
			<b class="news_top_right_corner"><!-- --></b>
			<b class="news_bottom_right_corner"><!-- --></b>
			<b class="news_bottom_left_corner"><!-- --></b>	
			<!--<b class="newstitle">Новости компании</b>
-->
			<?/*$APPLICATION->IncludeComponent("bitrix:news.line", "main_page", Array(
	"IBLOCK_TYPE"	=>	"news",
	"IBLOCKS"	=>	array(
		0	=>	"3",
	),
	"NEWS_COUNT"	=>	"5",
	"SORT_BY1"	=>	"ACTIVE_FROM",
	"SORT_ORDER1"	=>	"DESC",
	"SORT_BY2"	=>	"SORT",
	"SORT_ORDER2"	=>	"ASC",
	"DETAIL_URL"	=>	"/content/news/index.php?news=#ELEMENT_ID#",
	"CACHE_TYPE"	=>	"A",
	"CACHE_TIME"	=>	"300",
	"ACTIVE_DATE_FORMAT"	=>	"d.m.Y"
	)
);*/?>
<!--
			<b class="subscribetitle">Уведомления о новинках</b><br />
			<p class="spacer2">Для подписки на новинки нашего каталога введите свою эл. почту.</p>
-->
			<?/*$APPLICATION->IncludeComponent("bitrix:subscribe.form", "subscribe", Array(
				"USE_PERSONALIZATION"	=>	"Y",
				"PAGE"	=>	"#SITE_DIR#personal/subscribe/subscr_edit.php",
				"SHOW_HIDDEN"	=>	"N",
				"CACHE_TYPE"	=>	"A",
				"CACHE_TIME"	=>	"3600"
				)
			);*/?> 
<!--
		</div>				
-->
			<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Sed tempor nibh vitae sem. Maecenas imperdiet. Integer lectus eros, imperdiet hendrerit, sodales id, ornare eu, mauris. Cras pharetra venenatis elit. Mauris ut lacus. Maecenas at erat non elit pellentesque pulvinar. Morbi et elit non eros ullamcorper porttitor. Integer sapien eros, facilisis ac, tincidunt vitae, laoreet at, leo. In sapien. Maecenas sed purus.
</p>
<p>Aliquam metus lacus, suscipit viverra, varius sed, mollis eu, nulla. Etiam risus. Nullam elementum nisi at lectus. Cras varius neque in turpis. In porta felis et mi. Vestibulum venenatis nisl vitae mauris. Sed vel augue id justo placerat ultricies. Sed tincidunt purus et velit. Aliquam nisi leo, faucibus a, ornare ut, volutpat quis, purus. Mauris eu arcu. Vivamus vehicula condimentum quam. In blandit. Donec dui. Ut porttitor erat ut dui. Sed interdum. Quisque diam. In tristique ligula vel nibh. Sed auctor elementum lorem. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Vivamus porttitor, tellus eget dictum tincidunt, dui metus auctor est, at sagittis elit lacus in sapien.
</p>
<p>
Aenean nec risus. Sed nisl libero, feugiat sed, laoreet eu, ornare non, mi. Curabitur aliquet erat. Suspendisse pulvinar consectetuer nisi. Donec tincidunt quam in urna. Quisque leo sapien, mollis ac, convallis vel, molestie at, mi. Morbi imperdiet nibh sollicitudin purus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. In ultricies dictum risus. Suspendisse scelerisque quam convallis magna. In et diam.
</p>
<p>
Sed nec libero. Integer tempus diam in purus. Fusce dapibus scelerisque magna. Fusce id eros. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Mauris rutrum lacus ut orci. Donec pharetra ligula vitae odio volutpat luctus. Vivamus et metus pretium tortor blandit sagittis. Sed aliquam, sapien at cursus tincidunt, nulla nulla pretium nunc, at semper enim nibh ac turpis. Sed eu nibh. Nulla tristique nunc at leo. Suspendisse dui. In tincidunt, lectus condimentum posuere mattis, leo elit placerat quam, ut tincidunt augue lacus non neque. Duis iaculis. Quisque malesuada. Vestibulum tempus lacus eu nulla. Ut ut risus et nulla pharetra semper. Cras nec odio. Praesent pellentesque metus eget tortor. Nulla facilisis arcu id leo ultricies pretium.
</p>
<p>
Maecenas ante arcu, feugiat quis, mollis in, accumsan vel, nulla. Nulla a mi. Vestibulum adipiscing adipiscing sapien. Etiam mattis commodo arcu. Nunc sollicitudin. Suspendisse fringilla nunc a ipsum. Ut ac eros. Duis vitae leo. Etiam pellentesque arcu quis lorem. Maecenas vestibulum accumsan mauris. Nunc sit amet enim. Maecenas eget arcu. Phasellus turpis. Aliquam eu elit vitae libero mattis posuere. </p>
 <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
