<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "������� - ����������� ����������, ��������, �������, ����, �������� ����������, ����������, ����������, ������������ � ����");
$APPLICATION->SetPageProperty("NOT_SHOW_NAV_CHAIN", "Y");
$APPLICATION->SetTitle("�������");
?> 
<table width="960px"> 
  <tbody> 
    <tr> <td width="660px" style="-moz-border-top-colors: none; -moz-border-right-colors: none; -moz-border-bottom-colors: none; -moz-border-left-colors: none; border-image: none;"> 
        <h1 align="center">������������� ��������</h1>
       
        <p>�������� &quot;�������&quot; ���������������� � ������� �������� <a href="/special/farnell.php">������������ ������������</a> � <a href="/catalog/">������������������� ���������</a>. �� ������������ �� ���������� ���������������:</p>
       
        <p><strong>TELE </strong>� ������������ ������������� � <a href="/catalog/index.php?SECTION_ID=157">���� ��������</a> (����, ���������� � �������� � 1- � 3-������ �����, �����������, ������ ��������, �������), <a href="/catalog/index.php?SECTION_ID=159">���� �������</a> (������������������� � �������; �������� ���������, �������� ����������, ����������� ����), ��������� ������� �������� WatchDog pro, ���������� �������� ����� � ���������� ����������, �������� �������, �������� ���������, ������� ����������, ���� (�������������, ������������), �������������� ���� � ����. </p>
       
        <p><strong>BENEDICT</strong> � ����������, ���������, �����-��������, �������� ����, ���������� �������������, ������, ��������� ���������� � ��������� ����������. </p>
       
        <p><strong>CITEL</strong> � ����������� (������������) � ���������� ������ �� ����������� �������������� (����), ������������ ����� ���������������, ������ ����� ������ � ����������������, ������ ������������� � �������-�������� ��������� (������������ ������), ������������� ����������������, ��������������. </p>
       
        <p><strong>Vemer</strong> � ���������� ������ ����, ���������� ����������, ������� ���� � ���������������� ��������, ����������� ����� �������� �� ����������� ��� ��������/��������; �������� � ������������ �������, ������������; �������� ����������, ����������, �����������, ����������� ����. ��������, ��������� �������, ������� �������.</p>
       
        <p><strong>Graesslin</strong> � ����� ��� � <a href="/content/articles/control-lighting.php">���������� ����������</a> � �������� � ������������ �������, �������� ������� ���������, ��������� ���������� � ���������������, ���������� �������, ��������. </p>
       
        <p><strong>SONDER</strong> �<strong> </strong>���������� ��� �������� ����������� �� ������������ � � ����. </p>
       
        <p><strong>Relequick</strong> � ������������� ����, ������������ ����, ��������������� ����, ����������������� ����, �������, ����������.</p>
       
        <p><strong>RELECO</strong> �<strong> </strong>������������� ���� � ������������ ����, ������������ ����, ����������������� ����, �������, ������� � ����������; </p>
       
        <p><strong>EMKO</strong> �<strong> </strong> ������������� � ������������ ����������� � �������. </p>
       
        <p><strong>OBSTA</strong> �<strong> </strong>�������� ����������. </p>
       
        <p><strong>CBI</strong> �<strong> </strong>�������������� ����������� ��� ����������������� �������������, ��� ����� ������������� ������� ������������� � �����. </p>
       
        <p><strong>HUBER+SUHNER</strong> �<strong> </strong>��������������� ������������ �������.<strong> </strong>���������� ����������. ������ � ������. �������. </p>
       
        <p><strong>FarnellInOne</strong> � ����� 250000 ������������ �� 2000 ��������������. ����������. ����������� ����������, ������������� �������. </p>
       
        <p>������������� ������-������������.</p>
       </td> 	 	 <td width="300px" style="-moz-border-top-colors: none; -moz-border-right-colors: none; -moz-border-bottom-colors: none; -moz-border-left-colors: none; border-image: none;"> 
        <div id="news"> 
          <br />
         
          <br />
         
          <br />
         
          <br />
         
          <br />
         
          <br />
         
          <br />
         
          <br />
         
          <br />
         
          <br />
         
          <br />
<br />
         
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
          <br />
         
          <br />
         
          <div align="center"> <b class="subscribetitle">����������� � ��������</b> </div>
         
          <br />
         
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
         
          <br />
         
          <br />
         
          <br />
         	 </div>
       
        <br />
       
        <br />
       
        <br />
       
        <br />
       
        <br />
       
        <br />
       
        <br />
       
        <br />
       
        <br />
       </td> </tr>
   </tbody>
 </table>
 <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>