<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "������� - ����������� ����������, ��������, �������, ����, �������� ����������, ����������, ����������, ������������ � ����");
$APPLICATION->SetPageProperty("NOT_SHOW_NAV_CHAIN", "Y");
$APPLICATION->SetTitle("�������");
?><div id="news">
<div align="center"> <b class="newstitle">������� ��������</b> </div>
         <?$APPLICATION->IncludeComponent(
	"bitrix:news.line",
	"main_page",
	Array(
		"IBLOCK_TYPE" => "news", 
		"IBLOCKS" => array(0=>"3",), 
		"NEWS_COUNT" => "8", 
		"SORT_BY1" => "ACTIVE_FROM", 
		"SORT_ORDER1" => "DESC", 
		"SORT_BY2" => "SORT", 
		"SORT_ORDER2" => "ASC", 
		"DETAIL_URL" => "/content/news/index.php?news=#ELEMENT_ID#", 
		"CACHE_TYPE" => "A", 
		"CACHE_TIME" => "300" 
	)
);?> 
         
          <div align="center"> <b class="subscribetitle">����������� � ��������</b> </div>
         
         
          <p class="spacer2">��� �������� �� ������� ������ �������� ������� ���� ��. �����.</p>
         
          <div align="center"> <?$APPLICATION->IncludeComponent(
	"bitrix:subscribe.form",
	"subscribe",
	Array(
		"USE_PERSONALIZATION" => "Y", 
		"PAGE" => "#SITE_DIR#personal/subscribe/subscr_edit.php", 
		"SHOW_HIDDEN" => "N", 
		"CACHE_TYPE" => "A", 
		"CACHE_TIME" => "3600" 
	)
);?> </div>
</div><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>