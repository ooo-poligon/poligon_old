<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("������ �� ������������� � ������������������� ������������");?><h1>���������� �� ������������� � ������������������� ������������</h1>



<table>
<tbody>
<tr>
<td width="50%">
<div position="relative" top="1px">

<h2>������</h2>
 
<ul class="mark"> 
  <li><a href="/content/articles/sonder_ta.php">��������� ���������� SONDER - ������� ������ � ����� �������.</a></li>
 	 
  <li><a href="/content/articles/graesslin_mc.php">���������� ������ ����� �������� �������� ������������ &quot;Graesslin&quot;, 
      <br />
     ��� ��� ��������� ����� � ������� ����������� ��������� &quot;����� ���&quot;</a></li>
 
  <li><a href="/content/articles/tele-rele-control.php">��������� ���� �������� ��� ABB CM-PVS.41 � TELE E1YM400VS10</a></li>
 	 
  <li><a href="/content/articles/dry_run_protection.php">������ ������ �� ������� ����: ���������������� ��� ������</a></li>
 	 
  <li><a href="/content/articles/control-lighting.php">���������� ����������</a></li>
 	 
  <li><a href="/content/articles/outdoor-lighting.php">���������� ������� ����������</a></li>
 	 
  <li><a href="/content/articles/tsg.php">������������� <abbr title="���������� �������� �����">���</abbr> ��� �������� �������� ����� ��� ������� ������</a></li>
 	 
  <li><a href="/content/articles/Benedict-overload-relay.php">�������� ���� Benedict</a></li>
 	 
  <li><a href="/content/articles/loadmonitors.php">���� �������� �������� � cos &#966;: ��������� ��� ������</a></li>
 </ul>
 
<h2>������</h2>
 
<ul class="mark"> 	 
  <li><a href="/content/articles/enya_range.php">������������ � ����� ������������ ����� ENYA</a></li>
 	 
  <li><a href="/content/articles/gas-discharge-tube.php">������� ���������� CITEL: ������� ������ � ����������� �����������</a></li>
 	 
  <li><a href="/content/articles/graesslin-review.php">����� ��������� Graesslin</a> (�������, ����������, ��������)</li>
 	 
  <li><a href="/content/reviews/time-relay-tele.php">����� ���� �������</a></li>
 	 
  <li><a href="/content/articles/review-of-current-monitoring-relays.php">����� ���� �������� ����</a></li>
 	 
  <li><a href="/content/reviews/tele-phase-monitor-review.php">����� ���� �������� ���</a></li>
 	 
  <li><a href="/rele/daily-time-switch.php">�������� ���� �������</a></li>
 </ul>

</div>

</td>
<td>
<div position="relative" top="1px">
 
<h2>�������������� ����������</h2>
 
<ul class="mark"> 	 
  <li><a href="/content/articles/benedict-motor-full-load-currents.php">������� �������� ���� ����������� �������� ���������</a></li>
 	 
  <li><a href="/content/articles/contactors-for-lighting.php">������� ������� ��������� ����������� ��� ���������� ����</a></li>
 	 
  <li><a href="/content/benedict_conttable.php">������� ��� ������ ���������� Benedict �� ��������</a></li>
 	 
  <li><a href="/content/articles/citel-history.php">������� �������� CITEL</a></li>
 	 
  <li><a href="/content/about/citel.php">� �������� CITEL</a></li>
 	 
  <li><a href="/content/articles/graesslin-interactive.php">������������� ������ �������� � ����������� Graesslin</a> (flash-�����������)</li>
 	 
  <li><a href="/content/articles/programming-timer.php">���������������� ��������� ������� talento pro</a></li>
 </ul>
 
<h2>�����</h2>
 <span></span> 
<ul class="mark"> 
  <li><a target="_self" href="/content/articles/video_links/citel1.php">���� CITEL ��� ������ ������� ������������� ������������.</a></li>
 
  <li><a target="_self" href="/content/articles/video_links/citel2.php">���� CITEL ��� ������ ������ ������������.</a></li>
 
  <li><a target="_self" href="/content/articles/video_links/citel3.php">���� CITEL ��� ������ ������������ ��������.</a></li>
 
  <li><a target="_self" href="/content/articles/video_links/citel4.php">���� CITEL ��� ������ ����������������� ������� �� ���������� ��������������.</a></li>
 
  <li><a target="_self" href="/content/articles/video_links/citel5.php">���� CITEL ��� ������ ����������������� ������ (������� �������������).</a></li>
 
  <li><a target="_self" href="/content/articles/video_links/citel6.php">���� CITEL ��� ����������������� ������ ������������ � ������������ ����������.</a></li>
 
  <li><a target="_self" href="/content/articles/video_links/citel7.php">CITEL � ������� �� ������ �� ���������� ��������������.</a></li>
 

</ul>
</div>


<br />
<br />
<br />
<br />



</td>
</tr>
</tbody>
</table>
 <?
/*
// �����, ���� ���� �� �������� ��� �����. �, �.�. �������� ������ (������ ����������) �� ������������ �������� ���� � ������ ��������, ��
// �������� ���������� �������� ������������� ��� ����������. ��� ����, ����� ������� � ��������� ��� ��������. 
$APPLICATION->IncludeComponent("bitrix:news", ".default", Array(
	"IBLOCK_TYPE"	=>	"articles",
	"IBLOCK_ID"	=>	"2",
	"NEWS_COUNT"	=>	"10",
	"USE_SEARCH"	=>	"Y",
	"USE_RSS"	=>	"Y",
	"NUM_NEWS"	=>	"20",
	"NUM_DAYS"	=>	"360",
	"YANDEX"	=>	"N",
	"USE_RATING"	=>	"Y",
	"MAX_VOTE"	=>	"5",
	"VOTE_NAMES"	=>	array(
		0	=>	"",
		1	=>	"1",
		2	=>	"2",
		3	=>	"3",
		4	=>	"4",
		5	=>	"5",
		6	=>	"",
	),
	"USE_CATEGORIES"	=>	"Y",
	"CATEGORY_IBLOCK"	=>	array(
		0	=>	"",
		1	=>	"2",
	),
	"CATEGORY_CODE"	=>	"THEMES",
	"CATEGORY_ITEMS_COUNT"	=>	"5",
	"CATEGORY_THEME_"	=>	"list",
	"CATEGORY_THEME_2"	=>	"list",
	"USE_REVIEW"	=>	"Y",
	"MESSAGES_PER_PAGE"	=>	"25",
	"USE_CAPTCHA"	=>	"Y",
	"PATH_TO_SMILE"	=>	"/bitrix/images/forum/smile/",
	"FORUM_ID"	=>	"1",
	"URL_TEMPLATES_READ"	=>	"/communication/forum/index.php?PAGE_NAME=read&FID=#FORUM_ID#&TID=#TOPIC_ID#",
	"SHOW_LINK_TO_FORUM"	=>	"N",
	"USE_FILTER"	=>	"N",
	"SORT_BY1"	=>	"ACTIVE_FROM",
	"SORT_ORDER1"	=>	"DESC",
	"SORT_BY2"	=>	"SORT",
	"SORT_ORDER2"	=>	"ASC",
	"SEF_MODE"	=>	"N",
	"SEF_FOLDER"	=>	"",
	"CACHE_TYPE"	=>	"A",
	"CACHE_TIME"	=>	"3600",
	"CACHE_FILTER"	=>	"N",
	"DISPLAY_PANEL"	=>	"N",
	"SET_TITLE"	=>	"Y",
	"INCLUDE_IBLOCK_INTO_CHAIN"	=>	"N",
	"ADD_SECTIONS_CHAIN"	=>	"Y",
	"USE_PERMISSIONS"	=>	"N",
	"META_KEYWORDS"	=>	"KEYWORDS",
	"META_DESCRIPTION"	=>	"-",
	"DETAIL_ACTIVE_DATE_FORMAT"	=>	"d.m.Y",
	"DETAIL_FIELD_CODE"	=>	array(
		0	=>	"SHOW_COUNTER",
		1	=>	"",
	),
	"DETAIL_PROPERTY_CODE"	=>	array(
		0	=>	"AUTHOR",
		1	=>	"rating",
		2	=>	"",
	),
	"PREVIEW_TRUNCATE_LEN"	=>	"0",
	"LIST_ACTIVE_DATE_FORMAT"	=>	"d.m.Y",
	"LIST_FIELD_CODE"	=>	array(
		0	=>	"",
		1	=>	"",
	),
	"LIST_PROPERTY_CODE"	=>	array(
		0	=>	"",
		1	=>	"",
	),
	"HIDE_LINK_WHEN_NO_DETAIL"	=>	"N",
	"DETAIL_DISPLAY_TOP_PAGER"	=>	"N",
	"DETAIL_DISPLAY_BOTTOM_PAGER"	=>	"Y",
	"DETAIL_PAGER_TITLE"	=>	"��������",
	"DETAIL_PAGER_TEMPLATE"	=>	"",
	"DISPLAY_TOP_PAGER"	=>	"N",
	"DISPLAY_BOTTOM_PAGER"	=>	"Y",
	"PAGER_TITLE"	=>	"������",
	"PAGER_SHOW_ALWAYS"	=>	"N",
	"PAGER_TEMPLATE"	=>	"",
	"PAGER_DESC_NUMBERING"	=>	"Y",
	"PAGER_DESC_NUMBERING_CACHE_TIME"	=>	"36000",
	"VARIABLE_ALIASES"	=>	array(
		"SECTION_ID"	=>	"SECTION_ID",
		"ELEMENT_ID"	=>	"article",
	)
	)
);
//releByFunctions();
*/
?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>