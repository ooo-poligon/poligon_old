</section>
<br>
<br>
<br>

<div class="search_container">

       <b>����� �� ��������:</b> <?$APPLICATION->IncludeComponent(
	"bitrix:search.page",
	"search_noprice",
	Array(
		"RESTART" => "Y", 
		"CHECK_DATES" => "N", 
		"USE_TITLE_RANK" => "Y", 
		"arrWHERE" => array(0=>"iblock_news",1=>"",), 
		"arrFILTER" => array(0=>"iblock_catalog",), 
		"arrFILTER_iblock_catalog" => array(0=>"4",1=>"",), 
		"SHOW_WHERE" => "N", 
		"PAGE_RESULT_COUNT" => "10", 
		"AJAX_MODE" => "N", 
		"AJAX_OPTION_SHADOW" => "Y", 
		"AJAX_OPTION_JUMP" => "Y", 
		"AJAX_OPTION_STYLE" => "Y", 
		"AJAX_OPTION_HISTORY" => "Y", 
		"CACHE_TYPE" => "A", 
		"CACHE_TIME" => "3600", 
		"PAGER_TITLE" => "���������� ������", 
		"PAGER_SHOW_ALWAYS" => "Y", 
		"PAGER_TEMPLATE" => "", 
		"TAGS_SORT" => "NAME", 
		"TAGS_PAGE_ELEMENTS" => "150", 
		"TAGS_PERIOD" => "", 
		"TAGS_URL_SEARCH" => "", 
		"TAGS_INHERIT" => "Y", 
		"FONT_MAX" => "50", 
		"FONT_MIN" => "10", 
		"COLOR_NEW" => "000000", 
		"COLOR_OLD" => "C8C8C8", 
		"PERIOD_NEW_TAGS" => "", 
		"SHOW_CHAIN" => "Y", 
		"COLOR_TYPE" => "Y", 
		"WIDTH" => "50%" 
	)
);?> 
        <br />
       <b>����� �� �����:</b> <?$APPLICATION->IncludeComponent(
	"bitrix:search.page",
	"search",
	Array(
		"RESTART" => "Y", 
		"CHECK_DATES" => "N", 
		"USE_TITLE_RANK" => "Y", 
		"arrWHERE" => array(0=>"iblock_news",1=>"",), 
		"arrFILTER" => array(0=>"main",1=>"",), 
		"arrFILTER_main" => array(0=>"/content",), 
		"SHOW_WHERE" => "N", 
		"PAGE_RESULT_COUNT" => "10", 
		"AJAX_MODE" => "Y", 
		"AJAX_OPTION_SHADOW" => "Y", 
		"AJAX_OPTION_JUMP" => "Y", 
		"AJAX_OPTION_STYLE" => "Y", 
		"AJAX_OPTION_HISTORY" => "Y", 
		"CACHE_TYPE" => "A", 
		"CACHE_TIME" => "3600", 
		"PAGER_TITLE" => "���������� ������", 
		"PAGER_SHOW_ALWAYS" => "Y", 
		"PAGER_TEMPLATE" => "", 
		"TAGS_SORT" => "NAME", 
		"TAGS_PAGE_ELEMENTS" => "150", 
		"TAGS_PERIOD" => "", 
		"TAGS_URL_SEARCH" => "", 
		"TAGS_INHERIT" => "Y", 
		"FONT_MAX" => "50", 
		"FONT_MIN" => "10", 
		"COLOR_NEW" => "000000", 
		"COLOR_OLD" => "C8C8C8", 
		"PERIOD_NEW_TAGS" => "", 
		"SHOW_CHAIN" => "Y", 
		"COLOR_TYPE" => "Y", 
		"WIDTH" => "50%" 
	)
);?> 
        <br />
       <b>����� �� ��������:</b> <?$APPLICATION->IncludeComponent(
	"bitrix:search.page",
	"search",
	Array(
		"RESTART" => "Y", 
		"CHECK_DATES" => "N", 
		"USE_TITLE_RANK" => "Y", 
		"arrWHERE" => array(0=>"iblock_news",1=>"",), 
		"arrFILTER" => array(0=>"iblock_news",1=>"",), 
		"arrFILTER_iblock_news" => array(0=>"3",), 
		"SHOW_WHERE" => "N", 
		"PAGE_RESULT_COUNT" => "10", 
		"AJAX_MODE" => "Y", 
		"AJAX_OPTION_SHADOW" => "Y", 
		"AJAX_OPTION_JUMP" => "Y", 
		"AJAX_OPTION_STYLE" => "Y", 
		"AJAX_OPTION_HISTORY" => "Y", 
		"CACHE_TYPE" => "A", 
		"CACHE_TIME" => "3600", 
		"PAGER_TITLE" => "���������� ������", 
		"PAGER_SHOW_ALWAYS" => "Y", 
		"PAGER_TEMPLATE" => "", 
		"TAGS_SORT" => "NAME", 
		"TAGS_PAGE_ELEMENTS" => "150", 
		"TAGS_PERIOD" => "", 
		"TAGS_URL_SEARCH" => "", 
		"TAGS_INHERIT" => "Y", 
		"FONT_MAX" => "50", 
		"FONT_MIN" => "10", 
		"COLOR_NEW" => "000000", 
		"COLOR_OLD" => "C8C8C8", 
		"PERIOD_NEW_TAGS" => "", 
		"SHOW_CHAIN" => "Y", 
		"COLOR_TYPE" => "Y", 
		"WIDTH" => "50%" 
	)
);?> <?
if ($no==3)
{
	//echo '�� ������ �������� ��� ������ ���������������� ������ ������� ������.<br>';
//	include ('quick_order/index.php');
}
?>
</div>
<!--#################################################################################################################-->
<footer class="footer_container">
		<ul id="footer_list">

			<li><a href="/content/about/">� ��������</a></li>
	
			<li><a href="#" id="trigger_1">���������� ����������</a></li>
	
			<li><a href="/map.php">����� �����</a></li>
	
			<li><a href="/content/links/">����� ��������</a></li>
		</ul>
		<p>� 2014 ��� "�������"<br>��� ����� ��������</p>
</footer>
<!-- RedHelper -->
<script id="rhlpscrtg" type="text/javascript" charset="utf-8" async="async" src="https://web.redhelper.ru/service/main.js?c=poligon"></script>
<!--/Redhelper -->
<!--#################################################################################################################-->
</body>
</html>