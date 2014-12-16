<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div id="catalog-section-list">
<table style="width: 100%; border-spacing: 0px; padding: 0px;" ><tr><td style="vertical-align: top; width:50%;"><ul>
<?
$i=0;
$CURRENT_DEPTH=$arResult["SECTION"]["DEPTH_LEVEL"]+1;
foreach($arResult["SECTIONS"] as $arSection):
	if($CURRENT_DEPTH<$arSection["DEPTH_LEVEL"])
		echo "<ul>";
	elseif($CURRENT_DEPTH>$arSection["DEPTH_LEVEL"])
		echo str_repeat("</ul><br/>", $CURRENT_DEPTH - $arSection["DEPTH_LEVEL"]);
	$CURRENT_DEPTH = $arSection["DEPTH_LEVEL"];
	$img_alt='';
	$sec_preview='';

	if($arSection["DEPTH_LEVEL"]==$arResult["SECTION"]["DEPTH_LEVEL"]+1)
	{
		if ($i==6&&$arResult["SECTION"]["DEPTH_LEVEL"]<1) echo '</ul></td><td style="vertical-align: top;"><ul>';
		$rsSect = CIBlockSection::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>$arSection["IBLOCK_ID"], "ID"=>$arSection["ID"]), false, array("UF_*"));
		if ($arSect = $rsSect->GetNext())
		{ 
			$img_alt = $arSect["UF_SECT"];
			$sec_preview = $arSect["UF_SECT_PREVIEW"];
		}
                echo '<li><table><tr>';
		echo '<td><a href="'.$arSection["SECTION_PAGE_URL"].'"><b>'.$arSection["NAME"].'</b>';
		echo '</a><br/><b>'.$sec_preview.'</b></td></tr></table></li>';
		$i++;
	?>
      <?}else{?>
	<li><a href="<?=$arSection["SECTION_PAGE_URL"]?>"><?=$arSection["NAME"]?>
<?if ($arResult["SECTION"]["DEPTH_LEVEL"]>=1)
{
		$rsSect = CIBlockSection::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>$arSection["IBLOCK_ID"], "ID"=>$arSection["ID"]), false, array("UF_*"));
		if ($arSect = $rsSect->GetNext())
		{ 
			$sec_preview = $arSect["UF_SECT_PREVIEW"];
		}
}
?>
</a><?if ($sec_preview) echo '&nbsp;&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp;&nbsp;&nbsp;'.$sec_preview;?></li>
<?}?>
<?endforeach?>
</ul></td></tr></table>
</div>



<div class="catalog-books">
<style type="text/css">
<!-- ��������� �����, ����������� ��� �����-->
.goMessage {
	color: #FF0000;
	font-family:Tahoma;
	font-size:14px;
}
.goFormsInputAndTextarea {
	border: 1px solid #000000;
}
.goTitles {
	margin-left:0px;
	font-family:Tahoma;
	font-size:12px;
}
.goAttensionTitle {
	font-family:Tahoma;
	font-size:12px;
}
.goAttensionError {
	color:#FF0000;
	font-family:Tahoma;
	font-size:12px;
}
.goButtonSend {border:solid 1px;}
.goButtonClaer {border:solid 1px;}
.goFormsCheckBox	{margin:0 10px 0 0;}
</style>

  
<div id="catalogs"> 	 
  <div> 	  
    <ul> 	 		 
		<li class="first-pdf">
			<a href="/PDF/TELE/TELE_Main_Catalogue_2011-2012_RU.pdf">
				<img src="/images/covers/tele.jpg" alt="������� �������� TELE" class="cover" />
			</a>
			<a class="pdf" href="/PDF/TELE/TELE_Main_Catalogue_2011-2012_RU.pdf">
				�������� ������� TELE 2011/2012
			</a> 		 
				��� ������ TELE. ���� �������, ���� ��������, ���������� ����������, ���������� ����������, ������� ���� � ����������, ������������� ����, �������, �������� �����.
		</li>
     
 		 
      <li><a href="/PDF/TELE/TELE_-_Components_for_automation_RU.pdf"><img src="/images/covers/pre/TELE_-_Components_for_automation_RU.jpg" alt="������� ������� ����������� �������������" class="cover" /></a> 		<a href="/PDF/TELE/TELE_-_Components_for_automation_RU.pdf" class="pdf">���������� �������������</a> 		 
        <br />
       ����� ������������: ���� ������� + ���� �������� + ���������� ����������. �������� ������� ���������� �� �������� ���� �������, ���������� ����� ��������� � ������� �������. </li>
     		 		 		 
      <li><a href="/PDF/TELE/TELE_pumpenfolder_v2_rus.pdf"><img src="/images/covers/pre/pumpenfolder.jpg" alt="������� ������� ��������� � ����������� �������" class="cover" /></a> 		<a class="pdf" href="/PDF/TELE/TELE_pumpenfolder_v2_rus.pdf">������ ��������� � ����������� ������� ��� �������� � ������� ���� �������� ��������</a> 		 
        <br />
       ������ �������� ���������� � ������� ������������ TELE ��� ������ � ����������� ������ ������� + ������ �������� �������, ����������� <b>��� ��������</b> ����������� �������� ������ ����, ��������� �������, ���������� � ����.</li>
     	 		 		 
      <li><a href="/PDF/TELE/TELE_-_Fan_and_Compressor_monitoring_&amp;_optimisation_en.pdf"><img src="/images/covers/pre/TELE_-_Fan_and_Compressor_monitoring_&amp;_optimisation_en.jpg" alt="������� ������� ��������� � ����������� �������" class="cover" /></a> 		<a class="pdf" href="/PDF/TELE/TELE_-_Fan_and_Compressor_monitoring_&amp;_optimisation_en.pdf">������ ��������� � ����������� ������������ � ������������</a> 		 
        <br />
       ������ �������� ���������� � ������� ������������ TELE ��� �������� � ����������� ������ ������������ � �������������� ������.</li>
     		 
      <li><a href="/PDF/TELE/TELE_12_time_and_monitoring_relays_ru.pdf"><img src="/images/covers/pre/12_time_and_monitoring_relays_ru.jpg" alt="������� ������� �12 ���� ������� � ���������" class="cover" /></a> 		<a href="/PDF/TELE/TELE_12_time_and_monitoring_relays_ru.pdf" class="pdf">������ �12 ���� ������� � ���������</a> 		 
        <br />
       ������ ��������� ��������� ���� ������� ��� ���� �������� �� �������� ����������. 12 ���� ������� � �������� � ��������� ��������� �� �������������.</li>
     		 
      <li><a href="/PDF/TELE/TELE_WatchDog_pro_rus.pdf"><img src="/images/covers/TELE_WatchDog_pro_rus.jpg" alt="������� ���� ������� TELE" class="cover" /></a> 		<a class="pdf" href="/PDF/TELE/TELE_WatchDog_pro_rus.pdf">��������� ������� �������� WatchDog pro</a> 		 
        <br />
       WatchDog pro - ��������� ������� ��������, ������������ � ���� ������� ��� + ������� �������� ������������� ������� + ��������� � ���������� ����� (Modbus, Profibus, Ethernet, GSM...) + ����������� ����������� ��������� ������ + ������ ����������� + ������� ������� ����������������</li>
     	 		 
      <li> 			<a href="/PDF/TELE/TELE_Safety_Relais_Leaflet.pdf"><img src="/images/covers/pre/TELE_Safety_Relais_Leaflet.jpg" alt="������� ������� ����������� �������������" class="cover" /></a> 		 			<a href="/PDF/TELE/TELE_Safety_Relais_Leaflet.pdf">���� ������������ (���. )</a> 			 
        <br />
       ����� ��������� ������� ��� �������� ���������� ���������� � ������������. 		</li>
     
      <li> 			<a href="http://poligon.info/PDF/TELE/Folder_VEO_rus.pdf"><img src="/images/covers/VEO_cover2014.JPG" alt="������� ������� ����������� �������������" class="cover" /></a> 		 			<a href="/PDF/TELE/Folder_VEO_rus.pdf">������ Tele VEO �� ������� �����</a> 			 
        <br />
       ������ ������ ������������ ����� ����� ���� ������� � ���� �������� ������ ������������ �����, �������� TELE. ���� VEO ������ �� ����� ����� DELTA, ��� ���� ����������� ���������� ������ ���� ���������������� ������������� ����� �����, ����� ��� ���������� ��������� ������� ����������, �������� ��������� �, ����� ����, ��������� ��������� ������ ���� VEO. ����� ������� ��������� � 250ms �� 150ms, �� ����, ����� �����.</li>
     		</ul>
   </div>
 
  <div> 	 
    <h2>Benedict</h2>
   
    <p>90 ��� ���������� � ������������ �����������, �������������� ����������. ����� � ������������ �����������. ���������� � �������� ���������� �� �������.</p>
   	 
    <ul> 	 		 
      <li class="first-pdf"><a href="/PDF/BENEDICT/Benedict_2012-Main_catalog_rus.pdf"><img src="/images/covers/benedict.jpg" alt="������� ���������� � ��������� Benedict" class="cover" /></a> 		 		<a class="pdf" href="/PDF/BENEDICT/Benedict_2012-Main_catalog_rus.pdf">���������� � ���������</a> 		 
        <br />
       ������������ ����������, ����-����������, ����-����������. �������� ���� � �����-��������. ��������� ����������.</li>
     	 		 
      <li><a href="/PDF/BENEDICT/Benedict%20-%20Cam%20Switches.pdf"><img class="cover" alt="������� �������� ������������� Benedict" src="/images/covers/pre/benedict-switches.jpg" /></a> 		<a href="/PDF/BENEDICT/Benedict%20-%20Cam%20Switches.pdf">�������������</a> 		 
        <br />
       ���������� (��������) ������������� � ������������� ������. ����������� ��������� ����������� ��� ��. ������� ����������� � �����������-������������� � ���������� ���������� ��� ������������� ������������� ������������. ������ ��� ������� � ������� ���������� ��������. 		</li>
     		 		 
      <li><a href="/PDF/BENEDICT/M4_full.pdf"><img class="cover" alt="������� �������� �������������� ����������� Benedict" src="/images/covers/pre/benedict-cirtcuit-breakers.jpg" /></a> 		<a href="/PDF/BENEDICT/M4_full.pdf">�������������� ����������� </a> 		 
        <br />
       ��������� �� 0,16 A �� 100 A. ������� �������� �����������. ����� ���� ������������ ��� ����� - ����������� ��������� ���� �. ����������� ����������� ��� �.�. 100 kA</li>
     		 		 
      <li><a href="/PDF/BENEDICT/Benedict%20-%20Capacitor%20Switching%20Contactors.pdf"><img src="/images/covers/pre/benedict-capacitor-switching-contactrors.jpg" alt="������� �������� ��������� ���������� Benedict" class="cover" /></a> 		<a href="/PDF/BENEDICT/Benedict%20-%20Capacitor%20Switching%20Contactors.pdf">��������� ����������</a> 		 
        <br />
       ��� ������������� � ����������� � �� ����������� ������� �������������.</li>
     		 		 
      <li><a href="/PDF/BENEDICT/Benedict%20Manual%20Motor%20Starter.pdf"><img src="/images/covers/pre/benedict-manual-motorstarters.jpg" alt="������� �������� ��������� ��������� Benedict" class="cover" /></a> 		<a href="/PDF/BENEDICT/Benedict%20Manual%20Motor%20Starter.pdf">��������� ���������</a> 		 
        <br />
       ��������� 0,16-32 � � ������� �� ���������� � ���������� ������� �� ��������� ���������. </li>
     		 
      <li><a href="/PDF/BENEDICT/Benedict%20-%20Main%20Switches.pdf"><img src="/images/covers/pre/benedict-main-switches.jpg" alt="������� �������� ����������� ������� Benedict" class="cover" /></a> 		<a href="/PDF/BENEDICT/Benedict%20-%20Main%20Switches.pdf">����������� �������</a> 		 
        <br />
       ����������� ��������/�����������-������������� LT(S).. 20�160 A. ������� �����������, ����������� �������� � ���������� ������������� ��� ���������� � ���������� ����������.</li>
     		 
      <li><a href="/PDF/BENEDICT/Benedict%20-%20Push%20Buttons.pdf"><img class="cover" alt="������� �������� ������ Benedict" src="/images/covers/pre/benedict-push-buttons.jpg" /></a> 		<a href="/PDF/BENEDICT/Benedict%20-%20Push%20Buttons.pdf">������</a> 		 
        <br />
       ������, �����, ������������� ������� 22,5 �� � 30,5 ��, ������� ������: IP67.</li>
     		 
      <li><a href="/PDF/BENEDICT/Benedict%20-%20Modular%20Contactors%20and%20Push%20Puttons.pdf"><img class="cover" alt="������� �������� ��������� ���������� � ������������� Benedict" src="/images/covers/pre/benedict-modular-contactors.jpg" /></a> 		<a href="/PDF/BENEDICT/Benedict%20-%20Modular%20Contactors%20and%20Push%20Puttons.pdf">��������� ����������</a> 		 
        <br />
       ��������� ���������� � �������������. 		</li>
     	 	</ul>
   </div>
 
  <div> 	 
    <h2>Vemer</h2>
   
    <p>���������� &quot;������&quot; ����</p>
   	 
    <ul> 	 		 
      <li class="first-pdf"><a target="_blank" href="/PDF/VEMER/VEMER_main.pdf"><img src="/images/covers/VEMER_main.jpg" alt="������� �������� �������� Vemer" class="cover" /></a> 		 		<a target="_blank" class="pdf" href="/PDF/VEMER/VEMER_main.pdf">������� ��������� Vemer</a> 		 
        <br />
       �������� ������� Vemer. ������������, �����������, ��������������� ����������, �������������������, ��������, ��������������� �������, ������� ��������, ������� ����, ��������� ���������������, ������������� �������������� � ������ ������.</li>
     	</ul>
   </div>
 
  <div> 	 
    <h2>CITEL</h2>
   	 
    <p>����������� � ����.</p>
   	 
    <ul> 	 		 
      <li class="first-pdf"><a target="_blank" href="/PDF/CITEL/citel-catalog-rus.pdf"><img class="cover" src="/images/covers/citel.jpg" alt="������� �������� Citel" /></a> 		<a target="_blank" href="/PDF/CITEL/citel-catalog-rus.pdf">Citel - �������� ������� ���������</a> 		 
        <br />
       ���������� ������������ � ������ �� ����������� �������������� (����). ������ ������ �� DIN-�����. ������ ����� ��������������, ����������� �������� ������ � ����������������, ������ ��-�������. ��������������. </li>
     		 		 
      <li><a target="_blank" href="/PDF/CITEL/CITEL_2013.pdf"><img class="cover" alt="������� �������� Surge protection 9th edition" src="/images/covers/pre/CITEL_2013.jpg" /></a> 		<a target="_blank" href="/PDF/CITEL/CITEL_2013.pdf">Surge protection 9<sup>th</sup> edition</a> 		 
        <br />
       �������� ������� ��������� CITEL �� ���������� �����. 2013 ���. <a href="images/CITEL/2.jpg"> </a></li>
     <a href="images/CITEL/2.jpg"> </a></ul>
   <a href="images/CITEL/2.jpg"> </a></div>
 <a href="images/CITEL/2.jpg"> </a> 
  <div>
  <a href="images/CITEL/2.jpg"> 	 
      <h2>RELECO</h2>
     	 
      <p>Relays: That is what we know and stand for. </p>
     </a> 
    <ul><a href="images/CITEL/2.jpg"> </a> 
      <li class="first-pdf"><a href="images/CITEL/2.jpg"></a><a target="_blank" href="/PDF/RELECO/RELECO_CATALOGUE_2012_eng.pdf"><img class="cover" src="/images/covers/RELECO_CATALOGUE_2012_eng_1.jpg" alt="������� �������� comat/RELECO" /></a> 		<a target="_blank" href="/PDF/RELECO/RELECO_CATALOGUE_2012_eng.pdf">Releco/Comat - ������� ��������� 2012-2013</a> 		 
        <br />
       ������������� ����, ���� �������, ���� �������� (�� ����������). </li>
     		 		 
      <li><a target="_blank" href="/PDF/RELECO/RELECO_CATALOG.pdf"><img class="cover" src="/images/covers/pre/releco.jpg" alt="������� �������� RELECO" /></a>	<a target="_blank" class="pdf" href="/PDF/RELECO/RELECO_CATALOG.pdf">Releco - �������� ������� ��������� �� �������</a> 		 
        <br />
       ������������� ����: ������������, �����������, ������������ � �����������������.</li>
     		 		 
      <li><a target="_blank" href="/PDF/RELECO/RELECO_Ka_Railway_2012e.pdf"><img class="cover" alt="������� �������� Comat Releco" src="/images/covers/pre/Ka_Railway_2012e.jpg" /></a> 		<a target="_blank" href="/PDF/RELECO/RELECO_Ka_Railway_2012e.pdf">���������� ��� ���������������� ����������. </a> 		 
        <br />
       ������� ����������� ����, ��������� �������� � ����������� ������������� Comat Releco � ������������ �� ����������� ������������� �� ����������. </li>
     		 	</ul>
   </div>
 
  <div> 	 
    <h2>Graesslin</h2>
   	 
    <p>���������������� ������� ��� ������������ ������������� �������, ����� � �����. ���������� ����������������. ���������� ������������� � ����. </p>
   	 
    <ul> 	 		 
      <li class="first-pdf"><a href="/PDF/GRAESSLIN/Trade_Program_2014-2015_EN.pdf"><img class="cover" alt="������� �������� Graesslin" src="/images/covers/Graeslin_catalog_2014-2015_EN.jpg" /></a> 		 		<a href="/PDF/GRAESSLIN/Trade_Program_2014-2015_EN.pdf" class="pdf">Graesslin - ����� ������� ��������� �� 2014-2015��. (�� ���������� �����).</a> 		 
        <br />
       ������� ������������ � ��������, ��������������� �������, ������������ �������, ��������, ����������, ���������������, ���������� �������. ��� ������ Graesslin. </li>
     	 	 	 
      <li><a href="/PDF/GRAESSLIN/Catalogue_Graesslin_ru.pdf"><img class="cover" alt="������� �������� Graesslin" src="/images/covers/graesslin.jpg" /></a> 		 		<a href="/PDF/GRAESSLIN/Catalogue_Graesslin_ru.pdf" class="pdf">Graesslin - ���������� ������ �������� ��������� (2010-2011��.)</a> 		 
        <br />
       ������� ������������ � ��������, ��������������� �������, ������������ �������, ��������, ����������, ���������������, ���������� �������. ��� ������ Graesslin. </li>
     		 
      <li><a href="/PDF/GRAESSLIN/graesslin-review-ru.pdf"><img class="cover" alt="������� �������" src="/images/covers/pre/graesslin-review-ru.jpg" /></a> 		<a href="/PDF/GRAESSLIN/graesslin-review-ru.pdf" class="pdf">Graesslin - ������� ����� ��������� (������)</a> 		 
        <br />
       ��� ������� Graesslin �� ����� ���������. ���������� ������� �������������: ���������� ��������, ������ � ������.</li>
     	</ul>
   </div>
 
  <div> 	 
    <h2>EMKO</h2>
   	 
    <p>������������� ������� � �����������, ���������� ����� � �������� ��� ������������</p>
   	 
    <ul> 		 
      <li class="first-pdf"><a target="_blank" href="/PDF/EMKO/emko-2011-rus.pdf"><img class="cover" src="/images/covers/emko-2011-rus.jpg" alt="������� �������� �������� EMKO" /></a> 		<a target="_blank" href="/PDF/EMKO/emko-2011-rus.pdf">�������� ������� ��������� �� ������� �����</a> 		 
        <br />
       ������������� �������, ������������� �����������, ��������������� �����������, �������� � �������, ������-��������������� ����������� ��� ������������� ����������.</li>
     	</ul>
   </div>
 
  <div> 	 
    <h2>CBI</h2>
   	 
    <p>�������������� ����������� ��� �������������� � ����������</p>
   	 
    <ul> 		 
      <li class="first-pdf"><a target="_blank" href="/PDF/CBI/CBI-circuit_breaker.en.pdf"><img class="cover" src="/images/covers/CBI.jpg" alt="������� �������� CBI" /></a> 		<a target="_blank" href="/PDF/CBI/CBI-circuit_breaker.en.pdf">�������� ����������� CBI (����.). </a> </li>
     	 </ul>
   </div>
 
  <div> 	 
    <h2>RELEQUICK</h2>
   	 
    <p>��������� ������������� ������������ ����, ������������ ����, ����������������� ����, ����������� �������� �������, ���� ������� � ���� ��������, ������������ ��� �������������� �������, SMS-����.</p>
   
    <ul> 
      <li class="first-pdf"><a target="_blank" href="/PDF/RELEQUICK/Catalogue.pdf"><img class="cover" src="/images/covers/RELEQUICK_�atalog2014.jpg" alt="������� �������� RELEQUICK" /></a> 		<a target="_blank" href="/PDF/RELEQUICK/Catalogue.pdf">�������� ������� ��������� �� ���������� �����</a> </li>
     </ul>
   </div>
 </div>
</div>

<table width="100%" border="0" cellspacing="0" cellpadding="0">	
<!-- <tr>
		<td align="left" valign="top">
			<a href="/content/feedback/"><b>����� �������� �����</b></a><br />
		</td>
	</tr> -->
  <tr>
    <td align="center" valign="top">
	<h2>����� ������ CD, ��������</h2>
	<div style="text-align:left; width:320px;">
	



<?php
	//include ��������� ���������
	include("/content/feedback/func.inc");
	include("/content/feedback/useragent.inc");
	
	
	
	// ���� ����������� ������ ������������ ��� ��������.
	$goInput = 'input'; // ������ HTML ���� input
	$goCheckBox = 'checkbox';
	$goTextArea = 'textarea';
	$goSixeInput = ' type="text"  size="50"  maxlength="100" '; // ����� ������ TAG input
	$goSixeCheckBox =' type="checkbox"';
	$goSixeTextArea  = ' cols="57" rows="5" '; // ����� ������ TAG textarea
	$goValueInputStart  = 'value="';
	$goValueInputEnd  = '">';
	$goValueTextAreaStart  = '>';
	$goValueTextAreaEnd  = '</textarea>';
	$goAttensionStart = '<span class="goAttensionError" >���������� ��������� ������:<br>';
	$goAttensionSuffix = '�� ��������� ���� ';
	$goAttensionSuffixNotCorrect = '�� ��������� ��������� ���� ';
	$goAttensionEnd = '</span></p>';
	$goMessageWasSend = '<span class="goMessage">������� �� �������� ������ ���������!</span>';	
	$goMessageForCheckbox ='<b>�������� ������������ ��� ������:</b><br />';

//***************************************** ����� ������ � ������ ��������� ������� (������) *****************************************//		
	$goSend[To]='web-site-mailbox@poligon.info'; // ���������� ������ � ��������� ����
	$goSend[Subject] = '����� ��������/CD � ����� poligon.info'; // ���� ������ (������ ������ ���������� ������, ������ ������)
	$goIdOfName = 1; // �������� �������� ��������/�����������. ��� ����������� �������� � ����� ������������ � ��������� ������.
	$goIdOfEmail = 6; // ������� ����� ������������ ��� �������� Email, � ������ ���������� ������� �������� 0
	$goIdOfPhone = 5; // ������� ����� ������������ ��� �������� ��������, � ������ ���������� ������� �������� 0	
	

//������ $goReqParam[], ����� ��������� ������ �������� true ��� false (���� ������������ ��� �������������� ��� ����������); 

	$goTitle [1]='�������� �����������:';		$goTypeHTML [1]=$goInput; $goName[1]='name'; 	$goReqParam[1]=true; 
	$goTitle [2]='��� ������������:';		$goTypeHTML [2]=$goInput; $goName[2]='kind'; 	$goReqParam[2]=true; 
	$goTitle [3]='���������� ����:';	$goTypeHTML [3]=$goInput; $goName[3]='face';	$goReqParam[3]=true; 
	$goTitle [4]='���������:';		$goTypeHTML [4]=$goInput; $goName[4]='doljnost'; 	$goReqParam[4]=true; 
	$goTitle [5]='���./����:';	$goTypeHTML [5]=$goInput; $goName[5]='phone1';	$goReqParam[5]=true; 
	$goTitle [6]='E-mail �����:';			$goTypeHTML [6]=$goInput; $goName[6]='email';	$goReqParam[6]=true; 
	$goTitle [7]='�������� �����:';			$goTypeHTML [7]=$goTextArea; $goName[7]='mail1';	$goReqParam[7]=true; 
	$goTitle [8]='�����������:';			$goTypeHTML [8]=$goTextArea; $goName[8]='comment';	$goReqParam[8]=false; 
	//����� ��������� ����������� ��������
		$arSelect = Array("ID", "NAME", "PREVIEW_PICTURE","PREVIEW_TEXT");
		$arFilter = Array("IBLOCK_ID"=>7, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
		$res = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);
		$pci=9;
		while($arFields = $res->GetNext())
		{
			$goTitle [$pci] = $arFields["NAME"];
			$goTypeHTML [$pci]=$goCheckBox; $goName[$pci]='checkbox'.$pci;	$goReqParam[$pci]=false;
			$goPicture[$pci] = CFile::ShowImage($arFields["PREVIEW_PICTURE"], 0, 0, "border=1", "", true);
			$pci++;
		}


//***************************************** ����� ������ � ������ ��������� ������� (�����) *****************************************//

//***************************************** ���, ��� ���� ������ �� ������������� *****************************************//

	$goKolichestvoElementov = count($goTitle); // �� �������, � ������������. ��� ������� ������ ���� �����.
	$goDefaultSendFrom = $goSendTo; // ���� �� �������� ����� ��������� ���������, � ������ ���������� E-Mail ����������� � ������.
	$goCheck[email]=$goName[$goIdOfEmail];
	$goCheck[phone]=$goName[$goIdOfPhone];
	$tempWeHaveGotError = false;
	$goAttensionSuffixNotCorrectEmail = $goAttensionSuffixNotCorrect.''.$goTitle [$goIdOfEmail].'<br>';
	$goAttensionSuffixNotCorrectPhone = $goAttensionSuffixNotCorrect.''.$goTitle [$goIdOfPhone].'<br>';
	
	$boolenMessageWasSend = false;
	//�������� ������� ���������
	$countOfCheckbox = 0;
	for ($i = 1; $i <= $goKolichestvoElementov; $i++) 
	{
    		switch ($goTypeHTML [$i]) 
			{
				case ($goTextArea):
    				$goSixe[$i]=$goSixeTextArea;
					$goValueStart[$i] =	$goValueTextAreaStart ;
					$goValueEnd[$i] = $goValueTextAreaEnd ;						
    			break;
				case ($goInput):
    				$goSixe[$i]=$goSixeInput;
					$goValueStart[$i] =	$goValueInputStart ;
					$goValueEnd[$i] = $goValueInputEnd ;	
				break;
				case ($goCheckBox):
					++$countOfCheckbox;
					$goTypeHTML [$i] = $goInput;
    				$goSixe[$i]=$goSixeCheckBox;
					$goValueStart[$i] =	$goValueInputStart ;
					$goValueEnd[$i] = $goValueInputEnd ;	
    			break;
			}	
	}
	
?>
<form method="post" action="<?PHP_SELF?>">
  <p>
<?  
		$tempAttensionStart = '';
		$tempAttensionStart = '';
		//������� ���-�� ���������, ��� ��� ���� ��� �� ��������, ���� ������ ���������� �� ��������.
		for ($i = 1; $i <= ($goKolichestvoElementov-$countOfCheckbox); $i++) 
		{
			$tempMyNameIs=$goName[$i];
			$tempContentForm=$_POST[($tempMyNameIs)];
			
			switch ($goReqParam [$i]) 
			{
				case (true): //���� ���� ����������� ��� ����������, �� ������ ��������
				if (isset ($_POST [($tempMyNameIs)] ) ) // ���� �������� ��������� ������ ���, �� �������� �� �������������
				{
					if ($_POST[($tempMyNameIs)] =='') // ���� ������ ����������, � ���� �� ���������, �� ��������.
					{
						$tempContentFormErro[$i] = true;
						$tempAttensionStart = $goAttensionStart;
						$tempAttensionSuffix = $goAttensionSuffix;
						$tempAttensionEnd = $goAttensionEnd;
						$tempWeHaveGotError = true;
					 } else { // ���� ������ ����������, �� ����� ��� ���� ���� ��� �������� �� ����������, �� ���������� ��������
						if (($_POST [($goCheck[email])]) == '') // ��������� E-MAIL �� ������������ ����������
						{ 
						$tempContentFormErro[$goIdOfEmail] = true;
						}else{
							if (validEmail($_POST [($goCheck[email])]) == true)
							{
								$tempCheckedEmail='';
							}else{
								$tempCheckedEmail= $goAttensionSuffixNotCorrectEmail;
								$tempAttensionStart = $goAttensionStart;
								$tempAttensionEnd = $goAttensionEnd;
								$tempWeHaveGotError = true;
							}
						}
						
						
						if (($_POST [($goCheck[phone])]) == '') // ��������� ������� �� ������������ ����������
						{ 
						$tempContentFormErro[$goIdOfPhone] = true;
						}else{
							if (isPhoneNumber($_POST [($goCheck[phone])]) == true)
							{
								$tempCheckedPhone='';
							}else{
								$tempCheckedPhone= $goAttensionSuffixNotCorrectPhone;
								$tempAttensionStart = $goAttensionStart;
								$tempAttensionEnd = $goAttensionEnd;
								$tempWeHaveGotError = true;
							}
						}
								

						
					
					
					}
				}
				break;
				case (false): //���� ���� �� �������� ������������, �� ���������� ��������.
						$tempContentFormErro[$i] = false;
									
				break;
	
			}
			

		}	
if (isset ($_POST [($tempMyNameIs)] ) )
{
	if ($tempAttensionStart != $goAttensionStart) // ������ ���, ���������� ������.
	{
	if (!isset($HTTP_X_FORWARDED_FOR))
	{
	$HTTP_X_FORWARDED_FOR = "";
	}
	if	($HTTP_X_FORWARDED_FOR)
	{
		$ip = getenv("HTTP_X_FORWARDED_FOR");
		$proxy = getenv("REMOTE_ADDR");
		$host = gethostbyaddr($REMOTE_ADDR);
	}else {
		$ip = getenv("REMOTE_ADDR");
		$host = gethostbyaddr($REMOTE_ADDR);
		$proxy = "";
	}
		
	$userAgent = $HTTP_USER_AGENT;
	$browser = getBrowser($arrBrowser,$userAgent);
	$system = getSystem($arrSystem,$userAgent);
	$server = $HTTP_HOST;
		
	$goGetName=($_POST [($goName[$goIdOfName])]);
	if ($goIdOfEmail != 0) 
	{
		$goGetEmail = ($_POST [($goCheck[email])]);
	}else{
		$goGetEmail = $goDefaultSendFrom;
	}
	
	
	
	for ($i = 1; $i <= ($goKolichestvoElementov-$countOfCheckbox); $i++) 
	{
		$tempMyNameIs=$goName[$i];
		$tempContentForm=$_POST[($tempMyNameIs)];
		$goMailBody = $goMailBody.$goTitle [$i]."	".$tempContentForm."\n" ;
	};
	for ($i = ($goKolichestvoElementov-$countOfCheckbox+1); $i <= $goKolichestvoElementov; $i++) 
	{
		if ($goSixe[$i] == $goSixeCheckBox && isset($_POST[($goName[$i])]))
			{
				$tempContentForm=$_POST[($goName[$i])];
				$goMailBody = $goMailBody.$goTitle [$i]." - ������\n" ;
			};
	};
	
	
	$goMailBody = $goMailBody."\n\n����: [".getFullDate(time()).", ".getQuestionTime(time())."]\n--------------------\n\n";
	$tempSendMeFrom = 'From: '.$goGetName.'<'.$goGetEmail.'>'."\nReply-To: ".$goGetEmail."\nContent-Type: text/plain; charset=windows-1251\nContent-Transfer-Encoding: 8bit" ;
	$tempSendMe = $goSend[To]."\r\n".$goSend[Subject]."\r\n".$goMailBody."\r\n".$tempSendMeFrom."\r\n \r\n";
			/**
		 *	Send email with message to admin
		 */
	@mail($goSend[To], $goSend[Subject], $goMailBody, $tempSendMeFrom);
	//echo $tempSendMe;
	#writeDataInFile ($tempSendMe);
	
	//��������, ��� ��� ����������
	echo $goMessageWasSend.'<br><br>';	
	$boolenMessageWasSend = true;


	
	}
}
//�������� ��������� �� ������				
echo $tempAttensionStart;
	for ($i = 1; $i <= ($goKolichestvoElementov-$countOfCheckbox); $i++) 
	{
		if ($tempContentFormErro[$i] == true) 
		{
			echo $tempAttensionSuffix.$goTitle[$i].'<br>';
		}
	}


echo $tempCheckedEmail.$tempCheckedPhone.$tempAttensionEnd;

//�������� ������ ����� � ������
$flag = 1; //���� ��� ������ ���������  "�������� ������������ ��� ������:" ����� 1 ��� � �����
if ($boolenMessageWasSend == false)
{
	echo '	<p><span class="goAttensionTitle"><font color="#FF0000">��������! </font>��� ���� �������� ������������� ��� ����������, ����� "�����������"</span></p>';
		for ($i = 1; $i <= ($goKolichestvoElementov-$countOfCheckbox); $i++) 
		{
			//������� ��� ���� ����� ���-������
			if ($goSixe[$i] != $goSixeCheckBox)	{
				$tempMyNameIs=$goName[$i];
				$tempContentForm=$_POST[($tempMyNameIs)];
				echo '<font class="goTitles">'.$goTitle[$i].'</font><br>
				<'.$goTypeHTML[$i].' name="'.$goName[$i].'" class="goFormsInputAndTextarea"	id="'.$goName[$i].'"	'.$goSixe[$i].$goValueStart[$i].$tempContentForm.$goValueEnd[$i].'
				';
			}
		};
		for ($i = $goKolichestvoElementov-$countOfCheckbox; $i <= $goKolichestvoElementov; $i++)
		{
			//������� ��������
			if ($goSixe[$i] == $goSixeCheckBox)	{
				if ($flag == 1) 
					echo  $goMessageForCheckbox; 
				$flag = 0;
				$tempMyNameIs2=$goName[$i];
				echo $tempContentForm;
				$tempContentForm=$_POST[($tempMyNameIs2)];

				echo '<table><tr><Td width="100" align="center" valign="middle">'.$goPicture[$i].'</td><td>
				<'.$goTypeHTML[$i].' name="'.$goName[$i].'" class="goFormsCheckBox"	id="'.$goName[$i].'"	'.$goSixe[$i].$goValueStart[$i].$tempContentForm.$goValueEnd[$i].'<font class="goTitles">'.$goTitle[$i].'</font></td></tr></table>';
			};
		}
		echo '    <br>
				<!-- �������� ������-->
				<input class="goButtonSend" type="submit" name="submit">
				<input class="goButtonClaer" type="reset" value="��������">
			</p>';
}
		
if ($boolenMessageWasSend == true)
{
		for ($i = 1; $i <= ($goKolichestvoElementov-$countOfCheckbox+1); $i++) 
		{
			//������� ��� ���� ����� ���-������
			if ($goSixe[$i] != $goSixeCheckBox)	{
				$tempMyNameIs=$goName[$i];
				$tempContentForm=$_POST[($tempMyNameIs)];
				echo '<font class="goTitles">'.$goTitle[$i].' � '.$tempContentForm.'</font><br>';
			}
		};
		for ($i = ($goKolichestvoElementov-$countOfCheckbox+1); $i <= $goKolichestvoElementov; $i++)
		{
			if ($flag==1 AND isset($_POST[($goName[$i])]))	{
				echo '<b>��������� �������� � �����:</b><br />';
				$flag=0;
			};
			if ($goSixe[$i] == $goSixeCheckBox AND isset($_POST[($goName[$i])]))	{
				echo '<font class="goTitles">'.$goTitle[$i].'</font><br>';
			};
		};
}
?>




</form>
</div>
	</td>
  </tr>
</table>