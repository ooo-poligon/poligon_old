<? // include ��� �� Bitrix
/**
 *	@author "������� ������" <afomich@rambler.ru>, "������ ��������" <vladimir@mashkov.com>
 *	@version 2.0.0
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "������� ������");
$APPLICATION->SetPageProperty("keywords", "��� �������");
$APPLICATION->SetPageProperty("description", "��� �������");
$APPLICATION->SetPageProperty("NOT_SHOW_NAV_CHAIN", "Y");
?>


<style type="text/css">
<!-- ��������� �����, ����������� ��� �����
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
-->
</style>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td align="left" valign="top">
			<a href="/content/feedback/"><b>����� �������� �����</b></a><br />
			<a href="/content/feedback/catalog_order/"><b>����� ��� ������ CD, ��������</b></a><br />
		</td>
	</tr>
  <tr>
    <td align="center" valign="top">
	<h2>����� ������� ������</h2>
<p><span class="goAttensionTitle"><font color="#FF0000">��������! </font> ���� ���������� * ����������� ��� ����������:</span></p>
	<div style="text-align:left; width:320px;">
	



<?php
	//include ��������� ���������
	include("func.inc");
	include("useragent.inc");
	
	
	
	// ���� ����������� ������ ������������ ��� ��������.
	$goInput = 'input'; // ������ HTML ���� input
	$goTextArea = 'textarea';
	$goSixeInput = ' type="text"  size="35"  maxlength="100" '; // ����� ������ TAG input
	$goSixeTextArea  = ' cols="40" rows="5" '; // ����� ������ TAG textarea
	$goValueInputStart  = 'value="';
	$goValueInputEnd  = '">';
	$goValueTextAreaStart  = '>';
	$goValueTextAreaEnd  = '</textarea>';
	$goAttensionStart = '<span class="goAttensionError" >���������� ��������� ������:<br>';
	$goAttensionSuffix = '�� ��������� ���� ';
	$goAttensionSuffixNotCorrect = '�� ��������� ��������� ���� ';
	$goAttensionEnd = '</span></p>';
	$goMessageWasSend = '<span class="goMessage">������� �� �������� ������ ���������!</span>';	

//***************************************** ����� ������ � ������ ��������� ������� (������) *****************************************//		
	$goSend[To]='web-site-mailbox@poligon.info'; // ���������� ������ � ��������� ����
	$goSend[Subject] = '������/������ � ����� poligon.info'; // ���� ������ (������ ������ ���������� ������, ������ ������)
	$goIdOfName = 1; // �������� �������� ��������. ��� ����������� �������� � ����� ������������ � ������������ ������.
	$goIdOfEmail = 2; // ������� ����� ������������ (��. � ������� $goName ����)  ��� �������� Email, � ������ ���������� ������� �������� 0
	$goIdOfPhone = 3; // ������� ����� ������������ ��� �������� ��������, � ������ ���������� ������� �������� 0	
	

//������ $goReqParam[], ����� ��������� ������ �������� true ��� false (���� ������������ ��� �������������� ��� ����������); 

	$goTitle [1]='�.�.�.*:';		$goTypeHTML [1]=$goInput; $goName[1]='name'; 	$goReqParam[1]=true; 
	$goTitle [2]='E-Mail:*';		$goTypeHTML [2]=$goInput; $goName[2]='email'; 	$goReqParam[2]=true; 
	$goTitle [3]='���./����*:';	$goTypeHTML [3]=$goInput; $goName[3]='phone1';	$goReqParam[3]=true; 
	$goTitle [4]='��������*:';		$goTypeHTML [4]=$goInput; $goName[4]='company'; 	$goReqParam[4]=true; 
	$goTitle [5]='���������*:';	$goTypeHTML [5]=$goInput; $goName[5]='doljnost';	$goReqParam[5]=false; 
	$goTitle [6]='������������1:';	$goTypeHTML [6]=$goInput; $goName[6]='article1';	$goReqParam[6]=false; 
	$goTitle [7]='����������:';	$goTypeHTML [7]=$goInput; $goName[7]='col1';	$goReqParam[7]=false; 
	
	if ($_POST["kol"])
	{
		$pp = 2;
		for ($p=8;$p<=$_POST["kol"];$p++){
			$goTitle[$p] = '������������'.$pp.':';
			$goTypeHTML [$p]=$goInput; $goName[$p]='col'.$p;	$goReqParam[$p]=false; 
			$p++;
			$goTitle[$p] = '����������:';
			$goTypeHTML [$p]=$goInput; $goName[$p]='col'.$p;	$goReqParam[$p]=false; 
			$pp++;
		}
		$goTitle [$_POST['kol']+1]='�����������:';			$goTypeHTML [$_POST['kol']+1]=$goTextArea; $goName[$_POST['kol']+1]='comment';	$goReqParam[$_POST['kol']+1]=false; 
	}
	else{
	/*$goTitle [8]='������������ 2:';	$goTypeHTML [8]=$goInput; $goName[8]='article2';	$goReqParam[8]=false; 
	$goTitle [9]='����������:';	$goTypeHTML [9]=$goInput; $goName[9]='col2';	$goReqParam[9]=false; 
	
	$goTitle [10]='������������ 3:';	$goTypeHTML [10]=$goInput; $goName[10]='article3';	$goReqParam[10]=false; 
	$goTitle [11]='����������:';	$goTypeHTML [11]=$goInput; $goName[11]='col3';	$goReqParam[11]=false; 
	
	$goTitle [12]='������������ 4:';	$goTypeHTML [12]=$goInput; $goName[12]='article4';	$goReqParam[12]=false; 
	$goTitle [13]='����������:';	$goTypeHTML [13]=$goInput; $goName[13]='col4';	$goReqParam[13]=false; 
	
	$goTitle [14]='������������ 5:';	$goTypeHTML [14]=$goInput; $goName[14]='article5';	$goReqParam[14]=false; 
	$goTitle [15]='����������:';	$goTypeHTML [15]=$goInput; $goName[15]='col5';	$goReqParam[15]=false; 
	*/
	$goTitle [8]='�����������:';			$goTypeHTML [8]=$goTextArea; $goName[8]='comment';	$goReqParam[8]=false; 
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
			}	
	}
	
?>
<form name="feedback" method="post" action="<?PHP_SELF?>">
  <p>
<?  
		$tempAttensionStart = '';
		$tempAttensionStart = '';
		for ($i = 1; $i <= $goKolichestvoElementov; $i++) 
		{
			$tempMyNameIs=$goName[$i];
			$tempContentForm=$_POST[($tempMyNameIs)];
			
			switch ($goReqParam [$i]) 
			{
				case (true): //���� ���� ����������� ��� ����������, �� ������ ��������
				if (isset ($_POST [($tempMyNameIs)] ) ) // ���� �������� ���������, ������ ���, �� �������� �� �������������
				{
					if ($_POST[($tempMyNameIs)] =='') // ���� ������ ����������, � ���� �� ���������, �� ��������.
					{
						$tempContentFormErro[$i] = true;
						$tempAttensionStart = $goAttensionStart;
						$tempAttensionSuffix = $goAttensionSuffix;
						$tempAttensionEnd = $goAttensionEnd;
						$tempWeHaveGotError = true;
					 } else { // ���� ������ �����������, �� ����� ��� ���� ���� ��� �������� �� ����������, �� ���������� ��������
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
				case (false): //���� ���� �� �������� ������������, �� ��������� ��������.
						$tempContentFormErro[$i] = false;
									
				break;
	
			}
			

		}	
if (isset ($_POST [($tempMyNameIs)] )&&$_POST['mail']=='go')
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
	
	
	
	for ($i = 1; $i <= $goKolichestvoElementov; $i++) 
	{
		$tempMyNameIs=$goName[$i];
		$tempContentForm=$_POST[($tempMyNameIs)];
		$goMailBody = $goMailBody.$goTitle [$i]."	".$tempContentForm."\n" ;
	}
	
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
if ($_POST["mail"]=='go'){
//�������� ��������� � ������				
echo $tempAttensionStart;
	for ($i = 1; $i <= $goKolichestvoElementov; $i++) 
	{
		if ($tempContentFormErro[$i] == true) 
		{
			echo $tempAttensionSuffix.$goTitle[$i].'<br>';
		}
	}


echo $tempCheckedEmail.$tempCheckedPhone.$tempAttensionEnd;}?>
<script>
	function run()
	{
		document.getElementById("hid").innerHTML="<input name='kol' type=hidden value=<?=$goKolichestvoElementov+1?>>";
		//document.feedback.submit();
	}
	function run2()
	{
		document.getElementById("hid").innerHTML="<input name='mail' type=hidden value='go'><input name='kol' type=hidden value=<?=$goKolichestvoElementov-1?>>";
		//document.feedback.submit();
	}
</script>
<?
//�������� ������ ����� � ������
if ($boolenMessageWasSend == false)
{
	//var_dump($_POST);
	echo '<table>';		
		for ($i = 1; $i <= $goKolichestvoElementov; $i++) 
		{
			
			$tempMyNameIs=$goName[$i];
			$tempContentForm=$_POST[($tempMyNameIs)];
			if($i==6&&intval($_POST["kol"]))
			{
				for ($i==6;$i<=intval($_POST["kol"]-1);$i++)
				{
					$tempMyNameIs=$goName[$i];
					$tempContentForm=$_POST[($tempMyNameIs)];						
					echo '<tr><td><font class="goTitles">'.$goTitle[$i].'</font></td>
				<td><'.$goTypeHTML[$i].' name="'.$goName[$i].'" class="goFormsInputAndTextarea"	id="'.$goName[$i].'" type="text"  size="10"  maxlength="100"	'.$goValueStart[$i].$tempContentForm.$goValueEnd[$i].'</td>';
					$i++;
					$tempMyNameIs=$goName[$i];
					$tempContentForm=$_POST[($tempMyNameIs)];
					echo '<td><font class="goTitles">'.$goTitle[$i].'</font></td><td><'.$goTypeHTML[$i].' name="'.$goName[$i].'" class="goFormsInputAndTextarea"	id="'.$goName[$i].'" type="text"  size="10"  maxlength="100"	'.$goValueStart[$i].$tempContentForm.$goValueEnd[$i].'';
if ($i==intval($_POST["kol"])) echo  '</td><td><input onclick="run()" type="submit"  value="���..."><div id="hid"></div>';
echo '</td></tr>';
				}
				$i--;
			}
			elseif ($i==6&&!intval($_POST["kol"]))
			{
					echo '<tr><td><font class="goTitles">'.$goTitle[$i].'</font></td>
				<td><'.$goTypeHTML[$i].' name="'.$goName[$i].'" class="goFormsInputAndTextarea"	id="'.$goName[$i].'" type="text"  size="10"  maxlength="100" '.$goValueStart[$i].$tempContentForm.$goValueEnd[$i].'</td>';
					$i++;
					echo '<td><font class="goTitles">'.$goTitle[$i].'</font></td><td><'.$goTypeHTML[$i].' name="'.$goName[$i].'" class="goFormsInputAndTextarea"	id="'.$goName[$i].'"	type="text" size="10"  maxlength="100" '.$goValueStart[$i].$tempContentForm.$goValueEnd[$i].'</td><td><input onclick="run()" type="submit"  value="���..."><div id="hid"></div></td></tr>';
			}
			elseif ($i!=6){
			echo '<tr><td><font class="goTitles">'.$goTitle[$i].'</font></td>
			<td colspan=4><'.$goTypeHTML[$i].' name="'.$goName[$i].'" class="goFormsInputAndTextarea"	id="'.$goName[$i].'"	'.$goSixe[$i].$goValueStart[$i].$tempContentForm.$goValueEnd[$i].'</td></tr>
			';
			}
//echo '<tr><Td colspan=4>'.$i.'<td></tr>';
			
			if ($_POST["kol"]) $koll = $_POST["kol"]; else $koll=7;
			//if($i == $koll) echo '<input onclick="run()" type="submit"  value="���..."><div id="hid"></div>';
			if($i == 5 OR $i == 7 OR $i == 9 OR $i == 11 OR $i == 13 OR $i == 15) {echo '';};
		}
		echo '<tr><td colspan=4><p>
				<!-- �������� ������-->
				<input class="goButtonSend" type="submit" value="��������� ������" name="submit" onclick=run2()>
				<input class="goButtonClaer" type="reset" value="��������">
			</p></td></tr></table>';
}
		
if ($boolenMessageWasSend == true)
{
			for ($i = 1; $i <= $goKolichestvoElementov; $i++) 
			{
		
			$tempMyNameIs=$goName[$i];
			$tempContentForm=$_POST[($tempMyNameIs)];
			echo '<font class="goTitles">'.$goTitle[$i].' &#8212; '.$tempContentForm.'</font><br>';
			if($i == 5 OR $i == 7 OR $i == 9 OR $i == 11 OR $i == 13 OR $i == 15) {echo '<br />';};
			}
}
?>




</form>
</div>
	</td>
  </tr>
</table>
<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
